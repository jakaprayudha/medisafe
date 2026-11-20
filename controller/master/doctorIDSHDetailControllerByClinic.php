<?php
/**
 * Daftar dokter + status NIK / ID IHS Satu Sehat (module/admin/doctor-idsh).
 * Faskes diambil dari SESSION (dulu dari ?id_customer= sehingga NIK dokter faskes lain bisa dibaca).
 * Superadmin boleh memilih faskes lewat ?id_customer=.
 * Simpan NIK & cari ID IHS memakai controller/master/settingSatusehatController.php (aksi=nik / aksi=ihs).
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$roles = $_SESSION['roles'] ?? '';
requireRole(['admin', 'superadmin', 'administrator']);
$idc = in_array($roles, ['superadmin', 'administrator'], true) && !empty($_GET['id_customer'])
   ? (int) $_GET['id_customer']
   : (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();
if ($idc <= 0) {
   http_response_code(422);
   echo json_encode(['status' => 'error', 'message' => 'Faskes tidak ditemukan.']);
   exit;
}

$st = $koneksi->prepare("SELECT id_doctor, doctor_name, doctor_nik, idsh, doctor_phone, doctor_status
                         FROM ms_doctor WHERE id_customer = ? ORDER BY doctor_status DESC, doctor_name ASC");
$st->bind_param('i', $idc);
$st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

// kunjungan 30 hari terakhir per nama dokter (pasien_visit.id_doctor menyimpan nama) — memakai index tanggal
$awal = date('Y-m-d', strtotime('-29 days'));
$st = $koneksi->prepare("SELECT LOWER(TRIM(id_doctor)) d, COUNT(*) n FROM pasien_visit
                         WHERE visit_date >= ? AND id_customer = ? AND (visit_status IS NULL OR visit_status <> 99)
                         GROUP BY d");
$st->bind_param('si', $awal, $idc);
$st->execute();
$kunj = [];
foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $kunj[(string) $r['d']] = (int) $r['n'];
$st->close();

$nikCount = [];
foreach ($rows as $r) {
   $n = preg_replace('/\D/', '', (string) $r['doctor_nik']);
   if ($n !== '') $nikCount[$n] = ($nikCount[$n] ?? 0) + 1;
}
$data = [];
foreach ($rows as $r) {
   $nik = trim((string) $r['doctor_nik']);
   $digit = preg_replace('/\D/', '', $nik);
   $nikValid = (bool) preg_match('/^\d{16}$/', $nik);
   $adaIhs = trim((string) $r['idsh']) !== '';
   $data[] = [
      'id_doctor' => (int) $r['id_doctor'],
      'doctor_name' => (string) $r['doctor_name'],
      'doctor_phone' => (string) $r['doctor_phone'],
      'doctor_nik' => $nik,
      'idsh' => trim((string) $r['idsh']),
      'aktif' => (int) $r['doctor_status'] === 1,
      'nik_valid' => $nikValid,
      'nik_dobel' => $digit !== '' && ($nikCount[$digit] ?? 0) > 1,
      'status' => $adaIhs ? 'lengkap' : ($nikValid ? 'cari_ihs' : 'nik'),
      'kunjungan_30' => $kunj[mb_strtolower(trim((string) $r['doctor_name']))] ?? 0,
   ];
}
echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
