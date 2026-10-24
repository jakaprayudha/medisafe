<?php

require_once '../../database/connect.php';
require_once __DIR__ . '/generalisTubuhService.php';

header('Content-Type: application/json; charset=utf-8');

set_exception_handler(static function (Throwable $error) {
   error_log('Generalis Tubuh save failed: ' . get_class($error) . ' (' . $error->getCode() . ')');
   http_response_code(500);
   $message = $error instanceof mysqli_sql_exception && $error->getCode() === 1406
      ? 'Kapasitas penyimpanan pemeriksaan belum cukup. Administrator perlu memperbarui kolom pemeriksaan_fisik menjadi TEXT.'
      : 'Pemeriksaan gagal disimpan. Silakan coba kembali atau hubungi administrator.';
   echo json_encode(['status' => 'error', 'message' => $message]);
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   http_response_code(405);
   echo json_encode(['status' => 'error', 'message' => 'Metode tidak diizinkan.']);
   exit;
}

if (!in_array($_SESSION['roles'] ?? '', ['admin', 'dokter'], true)) {
   http_response_code(403);
   echo json_encode(['status' => 'error', 'message' => 'Akses tidak diizinkan.']);
   exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$visitId = trim((string) ($input['visit_ID'] ?? ''));
$nomorRm = trim((string) ($input['nomor_rm'] ?? ''));
$customerId = (int) ($_SESSION['id_customer'] ?? 0);
$values = $input['fields'] ?? null;

if ($visitId === '' || $nomorRm === '' || $customerId <= 0 || !is_array($values)) {
   http_response_code(400);
   echo json_encode(['status' => 'error', 'message' => 'Data kunjungan atau pemeriksaan tidak lengkap.']);
   exit;
}

foreach (generalisTubuhFields() as $groupFields) {
   foreach ($groupFields as $key => $field) {
      $value = trim((string) ($values[$key] ?? ''));
      if ($value !== '' && !in_array($value, $field['options'], true)) {
         http_response_code(400);
         echo json_encode(['status' => 'error', 'message' => 'Pilihan pemeriksaan tidak valid.']);
         exit;
      }
      $values[$key] = $value;
   }
}

$find = $koneksi->prepare("
   SELECT v.pemeriksaan_fisik
   FROM pasien_visit v
   INNER JOIN ms_patient p ON p.id_patient = v.id_patient
   WHERE v.visit_ID = ?
     AND p.nomor_rm = ?
     AND v.id_customer = ?
   LIMIT 1
");
if (!$find) {
   http_response_code(500);
   echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan pencarian kunjungan.']);
   exit;
}
$find->bind_param('ssi', $visitId, $nomorRm, $customerId);
$find->execute();
$visit = $find->get_result()->fetch_assoc();
$find->close();

if (!$visit) {
   http_response_code(404);
   echo json_encode(['status' => 'error', 'message' => 'Kunjungan tidak ditemukan.']);
   exit;
}

$updatedText = replaceGeneralisTubuhSection($visit['pemeriksaan_fisik'] ?? '', $values);
$update = $koneksi->prepare("
   UPDATE pasien_visit
   SET pemeriksaan_fisik = ?
   WHERE visit_ID = ? AND id_customer = ?
");
if (!$update) {
   http_response_code(500);
   echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan penyimpanan pemeriksaan.']);
   exit;
}
$update->bind_param('ssi', $updatedText, $visitId, $customerId);

if (!$update->execute()) {
   http_response_code(500);
   echo json_encode(['status' => 'error', 'message' => 'Pemeriksaan gagal disimpan.']);
   $update->close();
   exit;
}
$update->close();

echo json_encode(['status' => 'success', 'message' => 'Pemeriksaan Generalis Tubuh berhasil disimpan.']);
