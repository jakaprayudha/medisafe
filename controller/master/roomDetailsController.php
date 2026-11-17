<?php
// Tempat tidur per ruangan (module/admin/room_details)
//   GET    ?no=ID_ROOM            -> ruangan + daftar bed (dengan pasien rawat inap aktif)
//   GET    ?no=ID_ROOM&id=ID_BED  -> satu bed
//   POST   ?no=ID_ROOM            -> tambah bed (bed_name, bed_gender, bed_notes)
//   PUT    ?no=ID_ROOM            -> ubah bed (id_bed, ...)
//   PUT    ?no=ID_ROOM&toggle=1   -> ubah status bed (id_bed, bed_status 1=kosong 0=terisi)
//   POST   ?no=ID_ROOM&reset=1    -> kosongkan bed yang tidak ditempati pasien aktif
//   DELETE ?no=ID_ROOM&id=ID_BED  -> hapus bed (ditolak bila ada pasien aktif)
include '../../database/connect.php';
mysqli_report(MYSQLI_REPORT_OFF);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

const GENDER_BED = ['Pria', 'Wanita', 'Pria-Wanita'];

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
      error_log('[roomDetailsController] ' . $k->error);
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
      error_log('[roomDetailsController] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal menyimpan data.'], 500);
   }
   $stmt->bind_param($types, ...$params);
   if (!$stmt->execute()) {
      error_log('[roomDetailsController] ' . $stmt->error);
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

// ruangan wajib milik faskes sendiri
$idRoom = (int) ($_GET['no'] ?? 0);
$room = $idRoom > 0 ? semua($koneksi, "SELECT * FROM ms_room WHERE id_room = ? AND id_customer = ?", 'ii', [$idRoom, $idc]) : [];
if (!$room) jawab(['status' => 'error', 'message' => 'Ruangan tidak ditemukan.'], 404);
$room = $room[0];

/** pasien rawat inap aktif per bed: [id_bed => info] */
function pasienAktif(mysqli $k, array $idBeds): array
{
   if (!$idBeds) return [];
   $ids = array_map('strval', $idBeds);
   $ph = implode(',', array_fill(0, count($ids), '?'));
   $rows = semua(
      $k,
      "SELECT rn.id_bed, x.jml, COALESCE(mp.patient_name, pv.patient_name_pcare) nama, mp.nomor_rm rm,
              mp.patient_gender gender, COALESCE(pv.visit_date, rn.ranap_date) tgl_masuk
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
   $out = [];
   foreach ($rows as $r) {
      $out[(int) $r['id_bed']] = [
         'nama' => $r['nama'],
         'rm' => $r['rm'],
         'gender' => $r['gender'],
         'tgl_masuk' => $r['tgl_masuk'],
         'jml_aktif' => (int) $r['jml'],
      ];
   }
   return $out;
}

function bedMilikRuangan(mysqli $k, int $idBed, int $idRoom, int $idc): array
{
   $row = semua($k, "SELECT * FROM ms_room_bed WHERE id_bed = ? AND id_room = ? AND id_customer = ?", 'iii', [$idBed, $idRoom, $idc]);
   if (!$row) jawab(['status' => 'error', 'message' => 'Tempat tidur tidak ditemukan.'], 404);
   return $row[0];
}

function inputBed(array $in): array
{
   $nama = trim(preg_replace('/\s+/', ' ', (string) ($in['bed_name'] ?? '')));
   $gender = (string) ($in['bed_gender'] ?? '');
   $notes = trim((string) ($in['bed_notes'] ?? ''));
   if ($nama === '' || mb_strlen($nama) > 100) jawab(['status' => 'error', 'message' => 'Nama / nomor tempat tidur wajib diisi (maks. 100 karakter).'], 422);
   if (!in_array($gender, GENDER_BED, true)) jawab(['status' => 'error', 'message' => 'Pilih peruntukan tempat tidur (Pria / Wanita / Pria-Wanita).'], 422);
   if (mb_strlen($notes) > 255) jawab(['status' => 'error', 'message' => 'Catatan maksimal 255 karakter.'], 422);
   return [$nama, $gender, $notes];
}

function namaBedDipakai(mysqli $k, int $idRoom, int $idc, string $nama, int $kecuali = 0): bool
{
   return (bool) semua(
      $k,
      "SELECT 1 FROM ms_room_bed WHERE id_room = ? AND id_customer = ? AND LOWER(TRIM(bed_name)) = LOWER(?) AND id_bed <> ? LIMIT 1",
      'iisi',
      [$idRoom, $idc, $nama, $kecuali]
   );
}

// ================= GET =================
if ($method === 'GET') {
   if (isset($_GET['id'])) {
      jawab(['status' => 'success', 'data' => bedMilikRuangan($koneksi, (int) $_GET['id'], $idRoom, $idc)]);
   }
   $beds = semua(
      $koneksi,
      "SELECT id_bed, bed_name, bed_gender, bed_notes, bed_status FROM ms_room_bed WHERE id_room = ? AND id_customer = ?",
      'ii',
      [$idRoom, $idc]
   );
   $pasien = pasienAktif($koneksi, array_column($beds, 'id_bed'));
   foreach ($beds as &$b) {
      $b['id_bed'] = (int) $b['id_bed'];
      $b['bed_status'] = (int) $b['bed_status'];
      $b['pasien'] = $pasien[$b['id_bed']] ?? null;
   }
   unset($b);
   usort($beds, fn($a, $b) => strnatcasecmp((string) $a['bed_name'], (string) $b['bed_name']));

   jawab(['status' => 'success', 'room' => [
      'id_room' => (int) $room['id_room'],
      'room_name' => $room['room_name'],
      'service_class' => $room['service_class'],
      'room_capacity' => (int) $room['room_capacity'],
      'room_status' => (int) $room['room_status'],
      'room_description' => $room['room_description'],
   ], 'data' => $beds]);
}

// ================= POST =================
if ($method === 'POST') {
   // kosongkan bed tanpa pasien aktif
   if (isset($_GET['reset'])) {
      $beds = semua($koneksi, "SELECT id_bed FROM ms_room_bed WHERE id_room = ? AND id_customer = ? AND bed_status <> 1", 'ii', [$idRoom, $idc]);
      $ids = array_map('intval', array_column($beds, 'id_bed'));
      $dipakai = array_keys(pasienAktif($koneksi, $ids));
      $target = array_values(array_diff($ids, $dipakai));
      $n = 0;
      if ($target) {
         $ph = implode(',', array_fill(0, count($target), '?'));
         $n = eksekusi(
            $koneksi,
            "UPDATE ms_room_bed SET bed_status = 1 WHERE id_customer = ? AND id_room = ? AND id_bed IN ($ph)",
            'ii' . str_repeat('i', count($target)),
            array_merge([$idc, $idRoom], $target)
         );
      }
      $pesan = $n ? "$n tempat tidur dikosongkan." : 'Tidak ada tempat tidur yang perlu dikosongkan.';
      if ($dipakai) $pesan .= ' ' . count($dipakai) . ' bed tetap terisi karena masih ada pasien rawat inap aktif.';
      jawab(['status' => 'success', 'message' => $pesan, 'jumlah' => $n]);
   }

   [$nama, $gender, $notes] = inputBed($_POST);
   if (namaBedDipakai($koneksi, $idRoom, $idc, $nama)) {
      jawab(['status' => 'error', 'message' => "Tempat tidur \"$nama\" sudah ada di ruangan ini."], 409);
   }
   eksekusi(
      $koneksi,
      "INSERT INTO ms_room_bed (id_room, bed_name, bed_gender, bed_notes, bed_status, id_customer) VALUES (?, ?, ?, ?, 1, ?)",
      'isssi',
      [$idRoom, $nama, $gender, $notes, $idc]
   );
   jawab(['status' => 'success', 'message' => 'Tempat tidur berhasil ditambahkan.']);
}

// ================= PUT =================
if ($method === 'PUT') {
   parse_str(file_get_contents('php://input'), $in);
   $idBed = (int) ($in['id_bed'] ?? 0);
   bedMilikRuangan($koneksi, $idBed, $idRoom, $idc);

   if (isset($_GET['toggle'])) {
      $status = (int) ($in['bed_status'] ?? -1);
      if (!in_array($status, [0, 1], true)) jawab(['status' => 'error', 'message' => 'Status tidak valid.'], 422);
      eksekusi($koneksi, "UPDATE ms_room_bed SET bed_status = ? WHERE id_bed = ? AND id_customer = ?", 'iii', [$status, $idBed, $idc]);
      jawab(['status' => 'success', 'message' => $status === 1 ? 'Tempat tidur ditandai kosong.' : 'Tempat tidur ditandai terisi.']);
   }

   [$nama, $gender, $notes] = inputBed($in);
   if (namaBedDipakai($koneksi, $idRoom, $idc, $nama, $idBed)) {
      jawab(['status' => 'error', 'message' => "Tempat tidur \"$nama\" sudah ada di ruangan ini."], 409);
   }
   eksekusi(
      $koneksi,
      "UPDATE ms_room_bed SET bed_name = ?, bed_gender = ?, bed_notes = ? WHERE id_bed = ? AND id_customer = ?",
      'sssii',
      [$nama, $gender, $notes, $idBed, $idc]
   );
   jawab(['status' => 'success', 'message' => 'Tempat tidur berhasil diperbarui.']);
}

// ================= DELETE =================
if ($method === 'DELETE') {
   $idBed = (int) ($_GET['id'] ?? 0);
   bedMilikRuangan($koneksi, $idBed, $idRoom, $idc);
   $p = pasienAktif($koneksi, [$idBed])[$idBed] ?? null;
   if ($p) {
      jawab(['status' => 'error', 'message' => 'Tempat tidur masih ditempati pasien rawat inap aktif' . ($p['nama'] ? " ({$p['nama']})" : '') . '.'], 409);
   }
   eksekusi($koneksi, "DELETE FROM ms_room_bed WHERE id_bed = ? AND id_customer = ?", 'ii', [$idBed, $idc]);
   jawab(['status' => 'success', 'message' => 'Tempat tidur berhasil dihapus.']);
}

jawab(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
