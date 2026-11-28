<?php
// Master Ruangan Rawat Inap (module/admin/room)
//   GET                      -> daftar ruangan + ringkasan bed + daftar bed (dengan pasien aktif)
//   GET    ?id=ID            -> satu ruangan
//   POST                     -> tambah ruangan
//   PUT    (id_room, ...)    -> ubah ruangan / toggle room_status
//   PUT    ?toggle_bed=1     -> ubah status bed (id_bed, bed_status 1=kosong 0=terisi)
//   DELETE ?id=ID            -> hapus ruangan (ditolak bila masih ada bed / pasien aktif)
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';

/** Log master ruangan / bed (modul master). */
function ruangLog(mysqli $k, int $id, ?array $lama, string $ket, ?string $aksi = null): void
{
   $r = $lama ?? logAmbilBaris($k, 'ms_room', 'id_room', $id) ?? [];
   logBaris($k, 'master', 'ms_room', 'id_room', $id, $lama, ['tipe' => 'ruangan', 'label' => trim(($r['room_name'] ?? '') . ' · ' . ($r['service_class'] ?? ''), ' ·')], $ket, $aksi);
}
function bedLog(mysqli $k, int $id, ?array $lama, string $ket, ?string $aksi = null): void
{
   $r = $lama ?? logAmbilBaris($k, 'ms_room_bed', 'id_bed', $id) ?? [];
   $room = logAmbilBaris($k, 'ms_room', 'id_room', $r['id_room'] ?? null);
   logBaris($k, 'master', 'ms_room_bed', 'id_bed', $id, $lama, ['tipe' => 'bed', 'label' => trim(($room['room_name'] ?? '') . ' · ' . ($r['bed_name'] ?? ''), ' ·')], $ket, $aksi);
}
mysqli_report(MYSQLI_REPORT_OFF);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function jawab(array $data, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($data);
   exit;
}

function semua(mysqli $k, string $sql, string $types = '', array $params = []): array
{
   $stmt = $k->prepare($sql);
   if (!$stmt) {
      error_log('[roomController] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal mengambil data.'], 500);
   }
   if ($types !== '') $stmt->bind_param($types, ...$params);
   $stmt->execute();
   $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();
   return $rows;
}

function eksekusi(mysqli $k, string $sql, string $types, array $params): int
{
   $stmt = $k->prepare($sql);
   if (!$stmt) {
      error_log('[roomController] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal menyimpan data.'], 500);
   }
   $stmt->bind_param($types, ...$params);
   if (!$stmt->execute()) {
      error_log('[roomController] ' . $stmt->error);
      jawab(['status' => 'error', 'message' => 'Gagal menyimpan data.'], 500);
   }
   $n = $stmt->affected_rows;
   $stmt->close();
   return $n;
}

if (empty($_SESSION['id_customer'])) {
   jawab(['status' => 'error', 'message' => 'Session tidak valid / expired'], 401);
}
$idc = (int) $_SESSION['id_customer'];
$method = $_SERVER['REQUEST_METHOD'];

// Validasi & normalisasi input form ruangan
function inputRuangan(array $in): array
{
   $kelas = trim((string) ($in['service_class'] ?? ''));
   $nama = trim(preg_replace('/\s+/', ' ', (string) ($in['room_name'] ?? '')));
   $kap = $in['room_capacity'] ?? '';
   $desk = trim((string) ($in['room_description'] ?? ''));

   if ($kelas === '' || mb_strlen($kelas) > 100) jawab(['status' => 'error', 'message' => 'Kelas layanan wajib dipilih.'], 422);
   if ($nama === '' || mb_strlen($nama) > 150) jawab(['status' => 'error', 'message' => 'Nama ruangan wajib diisi (maks. 150 karakter).'], 422);
   if (!is_numeric($kap) || (int) $kap != $kap || (int) $kap < 0 || (int) $kap > 500) {
      jawab(['status' => 'error', 'message' => 'Kapasitas harus angka bulat 0 - 500.'], 422);
   }
   return [$kelas, $nama, (int) $kap, $desk];
}

function namaDipakai(mysqli $k, int $idc, string $nama, string $kelas, int $kecuali = 0): bool
{
   return (bool) semua(
      $k,
      "SELECT 1 FROM ms_room WHERE id_customer = ? AND LOWER(TRIM(room_name)) = LOWER(?) AND service_class = ? AND id_room <> ? LIMIT 1",
      'issi',
      [$idc, $nama, $kelas, $kecuali]
   );
}

// ================= GET =================
if ($method === 'GET') {
   if (isset($_GET['id'])) {
      $row = semua($koneksi, "SELECT * FROM ms_room WHERE id_room = ? AND id_customer = ?", 'ii', [(int) $_GET['id'], $idc]);
      if (!$row) jawab(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
      jawab(['status' => 'success', 'data' => $row[0]]);
   }

   $rooms = semua(
      $koneksi,
      "SELECT r.id_room, r.service_class, r.room_name, r.room_capacity, r.room_status, r.room_description,
              COALESCE(b.total, 0) total_bed, COALESCE(b.kosong, 0) bed_kosong, COALESCE(b.terisi, 0) bed_terisi
       FROM ms_room r
       LEFT JOIN (
          SELECT id_room, COUNT(*) total, SUM(bed_status = 1) kosong, SUM(bed_status <> 1) terisi
          FROM ms_room_bed WHERE id_customer = ? GROUP BY id_room
       ) b ON b.id_room = r.id_room
       WHERE r.id_customer = ?
       ORDER BY r.service_class, r.room_name",
      'ii',
      [$idc, $idc]
   );

   $beds = semua(
      $koneksi,
      "SELECT b.id_bed, b.id_room, b.bed_name, b.bed_gender, b.bed_notes, b.bed_status
       FROM ms_room_bed b
       WHERE b.id_customer = ?
       ORDER BY b.id_room, b.bed_name",
      'i',
      [$idc]
   );

   // pasien rawat inap aktif per bed (permintaan_ranap tanpa index -> satu query agregat)
   $pasien = [];
   if ($beds) {
      $ids = array_map(fn($b) => (string) $b['id_bed'], $beds);
      $ph = implode(',', array_fill(0, count($ids), '?'));
      $rows = semua(
         $koneksi,
         "SELECT rn.id_bed, rn.id_ranap, x.jml,
                 COALESCE(mp.patient_name, pv.patient_name_pcare) patient_name, mp.nomor_rm, mp.patient_gender,
                 COALESCE(pv.visit_date, rn.ranap_date) tgl_masuk
          FROM (
             SELECT id_bed, MAX(id_ranap) id_ranap, COUNT(*) jml
             FROM permintaan_ranap WHERE status = 'aktif' AND id_bed IN ($ph) GROUP BY id_bed
          ) x
          JOIN permintaan_ranap rn ON rn.id_ranap = x.id_ranap
          LEFT JOIN ms_patient mp ON mp.id_patient = rn.id_patient
          LEFT JOIN pasien_visit pv ON pv.visit_ID = rn.visit_ID_inpatient",
         str_repeat('s', count($ids)),
         $ids
      );
      foreach ($rows as $r) $pasien[(string) $r['id_bed']] = $r;
   }

   foreach ($beds as &$b) {
      $p = $pasien[(string) $b['id_bed']] ?? null;
      $b['bed_status'] = (int) $b['bed_status'];
      $b['pasien'] = $p ? [
         'nama' => $p['patient_name'],
         'rm' => $p['nomor_rm'],
         'gender' => $p['patient_gender'],
         'tgl_masuk' => $p['tgl_masuk'],
         'jml_aktif' => (int) $p['jml'],
      ] : null;
   }
   unset($b);

   jawab(['status' => 'success', 'data' => $rooms, 'beds' => $beds]);
}

// ================= POST (tambah) =================
if ($method === 'POST') {
   [$kelas, $nama, $kap, $desk] = inputRuangan($_POST);
   if (namaDipakai($koneksi, $idc, $nama, $kelas)) {
      jawab(['status' => 'error', 'message' => "Ruangan \"$nama\" kelas $kelas sudah ada."], 409);
   }
   eksekusi(
      $koneksi,
      "INSERT INTO ms_room (service_class, room_name, room_capacity, room_description, room_status, id_customer) VALUES (?, ?, ?, ?, 1, ?)",
      'ssisi',
      [$kelas, $nama, $kap, $desk, $idc]
   );
   ruangLog($koneksi, (int) $koneksi->insert_id, null, 'Ruangan ditambahkan');
   jawab(['status' => 'success', 'message' => 'Ruangan berhasil ditambahkan.']);
}

// ================= PUT =================
if ($method === 'PUT') {
   parse_str(file_get_contents('php://input'), $in);

   // status bed
   if (isset($_GET['toggle_bed'])) {
      $idBed = (int) ($in['id_bed'] ?? 0);
      $status = (int) ($in['bed_status'] ?? -1);
      if ($idBed <= 0 || !in_array($status, [0, 1], true)) jawab(['status' => 'error', 'message' => 'Data bed tidak valid.'], 422);
      $ada = semua($koneksi, "SELECT 1 FROM ms_room_bed WHERE id_bed = ? AND id_customer = ?", 'ii', [$idBed, $idc]);
      if (!$ada) jawab(['status' => 'error', 'message' => 'Bed tidak ditemukan.'], 404);
      $lama = logAmbilBaris($koneksi, 'ms_room_bed', 'id_bed', $idBed);
      eksekusi($koneksi, "UPDATE ms_room_bed SET bed_status = ? WHERE id_bed = ? AND id_customer = ?", 'iii', [$status, $idBed, $idc]);
      bedLog($koneksi, $idBed, $lama, $status === 1 ? 'Bed ditandai kosong (manual)' : 'Bed ditandai terisi (manual)', 'ubah_status');
      jawab(['status' => 'success', 'message' => $status === 1 ? 'Bed ditandai kosong.' : 'Bed ditandai terisi.']);
   }

   $id = (int) ($in['id_room'] ?? 0);
   if ($id <= 0) jawab(['status' => 'error', 'message' => 'ID tidak ditemukan.'], 422);
   if (!semua($koneksi, "SELECT 1 FROM ms_room WHERE id_room = ? AND id_customer = ?", 'ii', [$id, $idc])) {
      jawab(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
   }
   $lama = logAmbilBaris($koneksi, 'ms_room', 'id_room', $id);

   // status ruangan
   if (isset($in['room_status']) && !isset($in['room_name'])) {
      $status = (int) $in['room_status'] === 1 ? 1 : 0;
      eksekusi($koneksi, "UPDATE ms_room SET room_status = ? WHERE id_room = ? AND id_customer = ?", 'iii', [$status, $id, $idc]);
      ruangLog($koneksi, $id, $lama, $status ? 'Ruangan diaktifkan' : 'Ruangan dinonaktifkan', $status ? 'aktifkan' : 'nonaktifkan');
      jawab(['status' => 'success', 'message' => $status ? 'Ruangan diaktifkan.' : 'Ruangan dinonaktifkan.']);
   }

   [$kelas, $nama, $kap, $desk] = inputRuangan($in);
   if (namaDipakai($koneksi, $idc, $nama, $kelas, $id)) {
      jawab(['status' => 'error', 'message' => "Ruangan \"$nama\" kelas $kelas sudah ada."], 409);
   }
   eksekusi(
      $koneksi,
      "UPDATE ms_room SET service_class = ?, room_name = ?, room_capacity = ?, room_description = ? WHERE id_room = ? AND id_customer = ?",
      'ssisii',
      [$kelas, $nama, $kap, $desk, $id, $idc]
   );
   ruangLog($koneksi, $id, $lama, 'Ruangan diubah');
   jawab(['status' => 'success', 'message' => 'Ruangan berhasil diperbarui.']);
}

// ================= DELETE =================
if ($method === 'DELETE') {
   $id = (int) ($_GET['id'] ?? 0);
   if ($id <= 0) jawab(['status' => 'error', 'message' => 'ID tidak ditemukan.'], 422);
   if (!semua($koneksi, "SELECT 1 FROM ms_room WHERE id_room = ? AND id_customer = ?", 'ii', [$id, $idc])) {
      jawab(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
   }
   $aktif = semua($koneksi, "SELECT COUNT(*) n FROM permintaan_ranap WHERE id_room = ? AND status = 'aktif'", 's', [(string) $id])[0]['n'];
   if ($aktif > 0) {
      jawab(['status' => 'error', 'message' => "Ruangan masih memiliki $aktif pasien rawat inap aktif."], 409);
   }
   $bed = semua($koneksi, "SELECT COUNT(*) n FROM ms_room_bed WHERE id_room = ? AND id_customer = ?", 'ii', [$id, $idc])[0]['n'];
   if ($bed > 0) {
      jawab(['status' => 'error', 'message' => "Ruangan masih memiliki $bed tempat tidur. Hapus tempat tidur terlebih dahulu di menu detail, atau nonaktifkan ruangan."], 409);
   }
   $lama = logAmbilBaris($koneksi, 'ms_room', 'id_room', $id);
   eksekusi($koneksi, "DELETE FROM ms_room WHERE id_room = ? AND id_customer = ?", 'ii', [$id, $idc]);
   ruangLog($koneksi, $id, $lama, 'Ruangan dihapus');
   jawab(['status' => 'success', 'message' => 'Ruangan berhasil dihapus.']);
}

jawab(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
