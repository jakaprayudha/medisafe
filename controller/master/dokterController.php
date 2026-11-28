<?php
// Master dokter (module/admin/doctor, juga dibaca module/admin/setting_satusehat)
//   GET                            -> daftar dokter (+ nmPoli, jml_kunjungan)
//   GET    ?id=ID                  -> satu dokter
//   GET    ?stats=1&tanggal=Y-m-d  -> jumlah pasien per dokter (hari itu, 7 hari, bulan berjalan)
//   POST                           -> tambah
//   PUT                            -> ubah | toggle status: id_doctor + doctor_status
//   DELETE ?id=ID                  -> hapus (ditolak bila sudah ada kunjungan pasien)
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
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
      error_log('[dokterController] ' . $k->error);
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
      error_log('[dokterController] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal menyimpan data.'], 500);
   }
   $stmt->bind_param($types, ...$params);
   if (!$stmt->execute()) {
      error_log('[dokterController] ' . $stmt->error);
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

/** pasien_visit.id_doctor menyimpan NAMA dokter; samakan gelar/tanda baca/huruf besar */
function kunciDokter(?string $nama): string
{
   $s = strtoupper(trim((string) $nama));
   $s = preg_replace('/,.*$/', '', $s); // buang gelar belakang (", Sp.A")
   $s = preg_replace('/^(DRG|DR)\b\.?\s*/', '', $s);
   return preg_replace('/[^A-Z0-9]/', '', $s);
}

/** daftar dokter urut prioritas pemetaan: aktif dulu, lalu id terkecil */
function dokterFaskes(mysqli $k, int $idc): array
{
   return semua($k, "SELECT id_doctor, doctor_name, doctor_status FROM ms_doctor WHERE id_customer = ? ORDER BY doctor_status DESC, id_doctor ASC", 'i', [$idc]);
}

function petaDokter(array $dokter): callable
{
   $exact = $norm = [];
   foreach ($dokter as $d) {
      $exact[strtoupper(trim((string) $d['doctor_name']))] ??= (int) $d['id_doctor'];
      $kunci = kunciDokter($d['doctor_name']);
      if ($kunci !== '') $norm[$kunci] ??= (int) $d['id_doctor'];
   }
   return fn(?string $nama) => $exact[strtoupper(trim((string) $nama))] ?? ($norm[kunciDokter($nama)] ?? null);
}

/**
 * total kunjungan pasien (semua waktu, termasuk batal) per id_doctor, untuk kunci hapus.
 * Dokter dengan nama sama (mis. terdaftar di 2 poli) sama-sama dianggap punya kunjungan.
 */
function kunjunganPerDokter(mysqli $k, int $idc): array
{
   $exact = $norm = [];
   foreach (dokterFaskes($k, $idc) as $d) {
      $exact[strtoupper(trim((string) $d['doctor_name']))][] = (int) $d['id_doctor'];
      $kunci = kunciDokter($d['doctor_name']);
      if ($kunci !== '') $norm[$kunci][] = (int) $d['id_doctor'];
   }
   $out = [];
   foreach (semua($k, "SELECT id_doctor AS nama, COUNT(*) n FROM pasien_visit WHERE id_customer = ? GROUP BY id_doctor", 'i', [$idc]) as $r) {
      $ids = $exact[strtoupper(trim((string) $r['nama']))] ?? ($norm[kunciDokter($r['nama'])] ?? []);
      foreach ($ids as $id) $out[$id] = ($out[$id] ?? 0) + (int) $r['n'];
   }
   return $out;
}

function dokterLog(mysqli $k, int $id, ?array $lama, string $ket, ?string $aksi = null): void
{
   $r = $lama ?? logAmbilBaris($k, 'ms_doctor', 'id_doctor', $id) ?? [];
   logBaris($k, 'master', 'ms_doctor', 'id_doctor', $id, $lama, ['tipe' => 'dokter', 'label' => trim(($r['doctor_name'] ?? '') . ' · ' . ($r['id_poli'] ?? ''), ' ·')], $ket, $aksi);
}

function dokterMilik(mysqli $k, int $id, int $idc): array
{
   $row = semua($k, "SELECT * FROM ms_doctor WHERE id_doctor = ? AND id_customer = ?", 'ii', [$id, $idc]);
   if (!$row) jawab(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
   return $row[0];
}

// ================= GET =================
if ($method === 'GET') {
   if (isset($_GET['stats'])) {
      $dt = DateTime::createFromFormat('!Y-m-d', (string) ($_GET['tanggal'] ?? '')) ?: new DateTime('today');
      $tgl = $dt->format('Y-m-d');
      $awalBulan = $dt->format('Y-m-01');
      $hari7 = [];
      for ($i = 6; $i >= 0; $i--) $hari7[] = (clone $dt)->modify("-$i day")->format('Y-m-d');
      $mulai = min($awalBulan, $hari7[0]);

      $dokter = dokterFaskes($koneksi, $idc);
      $cari = petaDokter($dokter);
      $kosong = fn() => ['hari_ini' => 0, 'antre' => 0, 'selesai' => 0, 'batal' => 0, 'bulan' => 0, 'tren' => array_fill(0, 7, 0)];
      $stat = [];
      foreach ($dokter as $d) $stat[(int) $d['id_doctor']] = $kosong();
      $lain = [];

      $rows = semua(
         $koneksi,
         "SELECT id_doctor AS nama, visit_date, visit_status, COUNT(*) n
          FROM pasien_visit
          WHERE id_customer = ? AND visit_date BETWEEN ? AND ?
          GROUP BY id_doctor, visit_date, visit_status",
         'iss',
         [$idc, $mulai, $tgl]
      );
      foreach ($rows as $r) {
         $id = $cari($r['nama']);
         if ($id === null) {
            $label = trim((string) $r['nama']) !== '' ? trim((string) $r['nama']) : '(belum ada dokter)';
            $lain[$label] ??= $kosong();
            $s = &$lain[$label];
         } else {
            $s = &$stat[$id];
         }
         $n = (int) $r['n'];
         $batal = (int) $r['visit_status'] === 99;
         $d = $r['visit_date'];
         if ($d === $tgl) {
            if ($batal) $s['batal'] += $n;
            else {
               $s['hari_ini'] += $n;
               if ((int) $r['visit_status'] === 4) $s['selesai'] += $n;
               else $s['antre'] += $n;
            }
         }
         if (!$batal) {
            if ($d >= $awalBulan) $s['bulan'] += $n;
            $idx = array_search($d, $hari7, true);
            if ($idx !== false) $s['tren'][$idx] += $n;
         }
         unset($s);
      }

      $lainList = [];
      foreach ($lain as $label => $s) {
         if ($s['hari_ini'] || $s['bulan'] || $s['batal']) $lainList[] = ['nama' => $label] + $s;
      }
      usort($lainList, fn($a, $b) => $b['hari_ini'] <=> $a['hari_ini'] ?: $b['bulan'] <=> $a['bulan']);

      jawab(['status' => 'success', 'tanggal' => $tgl, 'hari' => $hari7, 'data' => (object) $stat, 'lain' => $lainList]);
   }

   if (isset($_GET['id'])) {
      jawab(['status' => 'success', 'data' => dokterMilik($koneksi, (int) $_GET['id'], $idc)]);
   }

   $data = semua(
      $koneksi,
      "SELECT d.*, p.nmPoli
       FROM ms_doctor d
       LEFT JOIN master_poli p ON p.kdPoli = d.id_poli
       WHERE d.id_customer = ?
       ORDER BY d.doctor_name DESC",
      'i',
      [$idc]
   );
   $jml = kunjunganPerDokter($koneksi, $idc);
   foreach ($data as &$d) $d['jml_kunjungan'] = $jml[(int) $d['id_doctor']] ?? 0;
   unset($d);
   jawab(['status' => 'success', 'data' => $data]);
}

// ================= POST =================
if ($method === 'POST') {
   $nama = trim((string) ($_POST['doctor_name'] ?? ''));
   $kode = trim((string) ($_POST['doctor_code'] ?? ''));
   $poli = trim((string) ($_POST['id_poli'] ?? ''));
   $hp = trim((string) ($_POST['doctor_phone'] ?? ''));
   $alamat = trim((string) ($_POST['doctor_address'] ?? ''));
   if ($nama === '' || $kode === '') jawab(['status' => 'error', 'message' => 'Pilih dokter dari daftar PCare.'], 422);
   if ($poli === '') jawab(['status' => 'error', 'message' => 'Pilih poliklinik.'], 422);
   if ($hp !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $hp)) jawab(['status' => 'error', 'message' => 'Nomor handphone tidak valid.'], 422);

   $ada = semua($koneksi, "SELECT doctor_name FROM ms_doctor WHERE id_customer = ? AND doctor_code = ? AND id_poli = ? LIMIT 1", 'iss', [$idc, $kode, $poli]);
   if ($ada) jawab(['status' => 'error', 'message' => "{$ada[0]['doctor_name']} sudah terdaftar di poli ini."], 409);

   do {
      $nomor = 'DCT-' . mt_rand(100000, 999999);
   } while (semua($koneksi, "SELECT 1 FROM ms_doctor WHERE doctor_number = ? LIMIT 1", 's', [$nomor]));

   eksekusi(
      $koneksi,
      "INSERT INTO ms_doctor (doctor_number, doctor_code, doctor_name, id_poli, doctor_category, doctor_phone, doctor_address, id_customer)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
      'sssssssi',
      [$nomor, $kode, $nama, $poli, $poli, $hp, $alamat, $idc]
   );
   dokterLog($koneksi, (int) $koneksi->insert_id, null, 'Dokter ditambahkan');
   jawab(['status' => 'success', 'message' => 'Dokter berhasil ditambahkan.']);
}

// ================= PUT =================
if ($method === 'PUT') {
   parse_str(file_get_contents('php://input'), $in);
   $id = (int) ($in['id_doctor'] ?? 0);
   $lama = dokterMilik($koneksi, $id, $idc);

   if (isset($in['doctor_status'])) {
      $status = (int) $in['doctor_status'];
      if (!in_array($status, [0, 1], true)) jawab(['status' => 'error', 'message' => 'Status tidak valid.'], 422);
      eksekusi($koneksi, "UPDATE ms_doctor SET doctor_status = ? WHERE id_doctor = ? AND id_customer = ?", 'iii', [$status, $id, $idc]);
      dokterLog($koneksi, $id, $lama, $status ? 'Dokter diaktifkan' : 'Dokter dinonaktifkan', $status ? 'aktifkan' : 'nonaktifkan');
      jawab(['status' => 'success', 'message' => $status ? 'Dokter diaktifkan.' : 'Dokter dinonaktifkan.']);
   }

   $allowed = ['doctor_nik', 'doctor_name', 'id_poli', 'doctor_subspesialis', 'doctor_category', 'doctor_phone', 'doctor_mail', 'doctor_address', 'doctor_gender', 'doctor_region'];
   $set = $vals = [];
   foreach ($allowed as $f) {
      if (array_key_exists($f, $in)) {
         $set[] = "$f = ?";
         $vals[] = trim((string) $in[$f]);
      }
   }
   if (!$set) jawab(['status' => 'error', 'message' => 'Tidak ada data diupdate.'], 422);
   if (isset($in['doctor_name']) && trim((string) $in['doctor_name']) === '') jawab(['status' => 'error', 'message' => 'Nama dokter wajib diisi.'], 422);
   if (isset($in['id_poli']) && trim((string) $in['id_poli']) === '') jawab(['status' => 'error', 'message' => 'Pilih poliklinik.'], 422);
   $hp = trim((string) ($in['doctor_phone'] ?? ''));
   if ($hp !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $hp)) jawab(['status' => 'error', 'message' => 'Nomor handphone tidak valid.'], 422);

   $vals[] = $id;
   $vals[] = $idc;
   eksekusi($koneksi, "UPDATE ms_doctor SET " . implode(', ', $set) . " WHERE id_doctor = ? AND id_customer = ?", str_repeat('s', count($set)) . 'ii', $vals);
   dokterLog($koneksi, $id, $lama, 'Data dokter diubah');
   jawab(['status' => 'success', 'message' => 'Data dokter berhasil diperbarui.']);
}

// ================= DELETE =================
if ($method === 'DELETE') {
   $id = (int) ($_GET['id'] ?? 0);
   $dokter = dokterMilik($koneksi, $id, $idc);
   $jml = kunjunganPerDokter($koneksi, $idc)[$id] ?? 0;
   if ($jml > 0) {
      jawab(['status' => 'error', 'message' => "{$dokter['doctor_name']} sudah memiliki " . number_format($jml, 0, ',', '.') . " data kunjungan pasien sehingga tidak dapat dihapus. Nonaktifkan saja bila tidak bertugas."], 409);
   }
   eksekusi($koneksi, "DELETE FROM ms_doctor WHERE id_doctor = ? AND id_customer = ?", 'ii', [$id, $idc]);
   dokterLog($koneksi, $id, $dokter, 'Dokter dihapus');
   jawab(['status' => 'success', 'message' => 'Dokter berhasil dihapus.']);
}

jawab(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
