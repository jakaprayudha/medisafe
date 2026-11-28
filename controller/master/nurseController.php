<?php
// Master tenaga kesehatan non-dokter (module/admin/nurse)
//   GET                          -> daftar nakes faskes
//   GET    ?id=ID                -> satu nakes
//   POST                         -> tambah (nurse_name, nurse_profesi, nurse_phone, nurse_address, nurse_gender)
//   PUT                          -> ubah (id_nurse, ...)
//   PUT    ?toggle_status=1      -> ubah status (id_nurse, nurse_status)
//   DELETE ?id=ID                -> hapus
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
mysqli_report(MYSQLI_REPORT_OFF);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

const PROFESI_NAKES = ['Perawat', 'Bidan', 'Apoteker', 'Farmasi', 'Analis Laboratorium', 'Analis', 'Ahli Gizi', 'Psikolog', 'Entomolog', 'Fisioterapis', 'Konselor', 'Lainnya'];

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
      error_log('[nurseController] ' . $k->error);
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
      error_log('[nurseController] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal menyimpan data.'], 500);
   }
   $stmt->bind_param($types, ...$params);
   if (!$stmt->execute()) {
      error_log('[nurseController] ' . $stmt->error);
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

function nakesLog(mysqli $k, int $id, ?array $lama, string $ket, ?string $aksi = null): void
{
   $r = $lama ?? logAmbilBaris($k, 'ms_nurse', 'id_nurse', $id) ?? [];
   logBaris($k, 'master', 'ms_nurse', 'id_nurse', $id, $lama, ['tipe' => 'nakes', 'label' => trim(($r['nurse_name'] ?? '') . ' · ' . ($r['nurse_profesi'] ?? ''), ' ·')], $ket, $aksi);
}

function nakesMilik(mysqli $k, int $id, int $idc): array
{
   $row = semua($k, "SELECT * FROM ms_nurse WHERE id_nurse = ? AND id_customer = ?", 'ii', [$id, $idc]);
   if (!$row) jawab(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
   return $row[0];
}

/** validasi field yang dikirim; $wajib = true saat tambah */
function inputNakes(array $in, bool $wajib, ?string $profesiLama = null): array
{
   $out = [];
   if ($wajib || array_key_exists('nurse_name', $in)) {
      $nama = trim(preg_replace('/\s+/', ' ', (string) ($in['nurse_name'] ?? '')));
      if ($nama === '' || mb_strlen($nama) > 255) jawab(['status' => 'error', 'message' => 'Nama tenaga kesehatan wajib diisi.'], 422);
      $out['nurse_name'] = $nama;
   }
   if ($wajib || array_key_exists('nurse_profesi', $in)) {
      $profesi = trim((string) ($in['nurse_profesi'] ?? ''));
      // profesi lama di luar daftar tetap boleh disimpan apa adanya
      if (!in_array($profesi, PROFESI_NAKES, true) && $profesi !== $profesiLama) jawab(['status' => 'error', 'message' => 'Pilih profesi.'], 422);
      $out['nurse_profesi'] = $profesi;
   }
   if (array_key_exists('nurse_phone', $in)) {
      $hp = trim((string) $in['nurse_phone']);
      if ($hp !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $hp)) jawab(['status' => 'error', 'message' => 'Nomor handphone tidak valid.'], 422);
      $out['nurse_phone'] = $hp;
   }
   if (array_key_exists('nurse_gender', $in)) {
      $g = (string) $in['nurse_gender'];
      if (!in_array($g, ['', 'L', 'P'], true)) jawab(['status' => 'error', 'message' => 'Jenis kelamin tidak valid.'], 422);
      $out['nurse_gender'] = $g === '' ? null : $g;
   }
   foreach (['nurse_address', 'nurse_nik', 'nurse_mail', 'nurse_region', 'nurse_category', 'nurse_subspesialis'] as $f) {
      if (array_key_exists($f, $in)) $out[$f] = mb_substr(trim((string) $in[$f]), 0, 255);
   }
   return $out;
}

// ================= GET =================
if ($method === 'GET') {
   if (isset($_GET['id'])) {
      jawab(['status' => 'success', 'data' => nakesMilik($koneksi, (int) $_GET['id'], $idc)]);
   }
   jawab([
      'status' => 'success',
      'data' => semua($koneksi, "SELECT * FROM ms_nurse WHERE id_customer = ? ORDER BY nurse_name DESC", 'i', [$idc]),
      'profesi' => PROFESI_NAKES,
   ]);
}

// ================= POST =================
if ($method === 'POST') {
   $data = inputNakes($_POST, true);
   $ada = semua(
      $koneksi,
      "SELECT 1 FROM ms_nurse WHERE id_customer = ? AND LOWER(TRIM(nurse_name)) = LOWER(?) AND nurse_profesi = ? LIMIT 1",
      'iss',
      [$idc, $data['nurse_name'], $data['nurse_profesi']]
   );
   if ($ada) jawab(['status' => 'error', 'message' => "{$data['nurse_name']} ({$data['nurse_profesi']}) sudah terdaftar."], 409);

   do {
      $nomor = 'NRS-' . mt_rand(100000, 999999);
   } while (semua($koneksi, "SELECT 1 FROM ms_nurse WHERE nurse_number = ? LIMIT 1", 's', [$nomor]));

   $data = ['nurse_number' => $nomor] + $data + ['id_customer' => $idc];
   $kolom = implode(', ', array_keys($data));
   $ph = implode(', ', array_fill(0, count($data), '?'));
   eksekusi($koneksi, "INSERT INTO ms_nurse ($kolom) VALUES ($ph)", str_repeat('s', count($data) - 1) . 'i', array_values($data));
   nakesLog($koneksi, (int) $koneksi->insert_id, null, 'Tenaga kesehatan ditambahkan');
   jawab(['status' => 'success', 'message' => 'Tenaga kesehatan berhasil ditambahkan.']);
}

// ================= PUT =================
if ($method === 'PUT') {
   parse_str(file_get_contents('php://input'), $in);
   $id = (int) ($in['id_nurse'] ?? 0);
   $lama = nakesMilik($koneksi, $id, $idc);

   if (isset($_GET['toggle_status']) && isset($in['nurse_status'])) {
      $status = (int) $in['nurse_status'];
      if (!in_array($status, [0, 1], true)) jawab(['status' => 'error', 'message' => 'Status tidak valid.'], 422);
      eksekusi($koneksi, "UPDATE ms_nurse SET nurse_status = ? WHERE id_nurse = ? AND id_customer = ?", 'iii', [$status, $id, $idc]);
      nakesLog($koneksi, $id, $lama, $status ? 'Tenaga kesehatan diaktifkan' : 'Tenaga kesehatan dinonaktifkan', $status ? 'aktifkan' : 'nonaktifkan');
      jawab(['status' => 'success', 'message' => $status ? 'Tenaga kesehatan diaktifkan.' : 'Tenaga kesehatan dinonaktifkan.']);
   }

   $data = inputNakes($in, false, $lama['nurse_profesi']);
   if (!$data) jawab(['status' => 'error', 'message' => 'Tidak ada data diupdate.'], 422);
   $set = implode(', ', array_map(fn($f) => "$f = ?", array_keys($data)));
   $vals = array_merge(array_values($data), [$id, $idc]);
   eksekusi($koneksi, "UPDATE ms_nurse SET $set WHERE id_nurse = ? AND id_customer = ?", str_repeat('s', count($data)) . 'ii', $vals);
   nakesLog($koneksi, $id, $lama, 'Data tenaga kesehatan diubah');
   jawab(['status' => 'success', 'message' => 'Data tenaga kesehatan berhasil diperbarui.']);
}

// ================= DELETE =================
if ($method === 'DELETE') {
   $id = (int) ($_GET['id'] ?? 0);
   $lama = nakesMilik($koneksi, $id, $idc);
   eksekusi($koneksi, "DELETE FROM ms_nurse WHERE id_nurse = ? AND id_customer = ?", 'ii', [$id, $idc]);
   nakesLog($koneksi, $id, $lama, 'Tenaga kesehatan dihapus');
   jawab(['status' => 'success', 'message' => 'Tenaga kesehatan berhasil dihapus.']);
}

jawab(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
