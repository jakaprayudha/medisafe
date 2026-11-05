<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

/*
|--------------------------------------------------------------------------
| EKYC VERIFY - ADMINMASTER (SUPERADMIN)
|--------------------------------------------------------------------------
| Scope GLOBAL semua faskes.
| Hanya role superadmin yang boleh mengakses endpoint ini.
*/

if (($_SESSION['roles'] ?? '') !== 'superadmin') {
   http_response_code(403);
   echo json_encode([
      'status'  => 'error',
      'message' => 'Akses hanya untuk adminmaster.'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}

function responseJson($status, $message = '', $data = null)
{
   $response = [
      'status'  => $status,
      'message' => $message
   ];

   if ($data !== null) {
      $response['data'] = $data;
   }

   echo json_encode($response, JSON_UNESCAPED_UNICODE);
   exit;
}

switch ($method) {

   case 'GET':

      if (isset($_GET['id']) && $_GET['id'] !== '') {
         getID($_GET['id']);
      } else {
         getData();
      }

      break;

   case 'POST':

      if (($_POST['action'] ?? '') === 'upload') {
         uploadFile();
      } else {
         responseJson('error', 'Action tidak dikenal.');
      }

      break;

   case 'PUT':

      parse_str(file_get_contents('php://input'), $_PUT);

      if (($_PUT['action'] ?? '') === 'verify') {
         verifyData($_PUT);
      } else {
         responseJson('error', 'Action tidak dikenal.');
      }

      break;

   default:
      http_response_code(405);
      responseJson('error', 'Method tidak diizinkan.');
}

/* ============================================================
   LIST GLOBAL SEMUA FASKES
============================================================ */

function getData()
{
   global $koneksi;

   $statusFilter = trim((string) ($_GET['status_verifikasi'] ?? ''));

   $sql = "
      SELECT
         e.id_ekyc,
         e.id_customer,
         e.nama,
         e.email,
         e.nomor_hp,
         e.nik,
         e.ktp_file,
         e.face_file,
         e.status_verifikasi,
         e.verified_at,
         e.status,
         e.created_at,
         e.created_by,
         COALESCE(sc.clinic_name, '-') AS clinic_name
      FROM tte_ekyc e
      LEFT JOIN setting_clinic sc
         ON CAST(sc.id_customer AS CHAR) = CAST(e.id_customer AS CHAR)
   ";

   $params = [];
   $types  = '';

   if ($statusFilter !== '' && $statusFilter !== 'all') {
      $sql .= " WHERE e.status_verifikasi = ? ";
      $params[] = $statusFilter;
      $types .= 's';
   }

   $sql .= "
      ORDER BY
         CASE e.status_verifikasi
            WHEN 'menunggu' THEN 1
            WHEN 'ktp_menunggu' THEN 2
            WHEN 'ktp_terverifikasi' THEN 3
            WHEN 'terverifikasi' THEN 4
            WHEN 'ditolak' THEN 5
            ELSE 6
         END ASC,
         e.id_ekyc DESC
   ";

   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {
      responseJson('error', 'Prepare gagal: ' . $koneksi->error);
   }

   if ($types !== '') {
      $stmt->bind_param($types, ...$params);
   }

   $stmt->execute();
   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();

   responseJson('success', '', $data);
}

function getID($id)
{
   global $koneksi;

   $id = (int) $id;

   $stmt = $koneksi->prepare("
      SELECT
         e.*,
         COALESCE(sc.clinic_name, '-') AS clinic_name
      FROM tte_ekyc e
      LEFT JOIN setting_clinic sc
         ON CAST(sc.id_customer AS CHAR) = CAST(e.id_customer AS CHAR)
      WHERE e.id_ekyc = ?
      LIMIT 1
   ");

   $stmt->bind_param('i', $id);
   $stmt->execute();

   $data = $stmt->get_result()->fetch_assoc();
   $stmt->close();

   if (!$data) {
      responseJson('error', 'Data tidak ditemukan.');
   }

   responseJson('success', '', $data);
}

/* ============================================================
   UPLOAD KTP / LIVENESS (GLOBAL)
============================================================ */

function uploadFile()
{
   global $koneksi;

   $id   = (int) ($_POST['id_ekyc'] ?? 0);
   $type = $_POST['type'] ?? '';

   $map = [
      'ktp'      => 'ktp_file',
      'liveness' => 'face_file'
   ];

   if ($id <= 0 || !isset($map[$type])) {
      responseJson('error', 'Parameter upload tidak valid.');
   }

   if (
      !isset($_FILES['file']) ||
      $_FILES['file']['error'] !== UPLOAD_ERR_OK
   ) {
      responseJson('error', 'File tidak ditemukan atau gagal diupload.');
   }

   if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
      responseJson('error', 'Ukuran file maksimal 5 MB.');
   }

   $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

   if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
      responseJson('error', 'Format file harus JPG, JPEG, PNG, atau WEBP.');
   }

   $finfo = finfo_open(FILEINFO_MIME_TYPE);
   $mime  = finfo_file($finfo, $_FILES['file']['tmp_name']);
   finfo_close($finfo);

   if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
      responseJson('error', 'Isi file bukan gambar yang valid.');
   }

   $cek = $koneksi->prepare("
      SELECT id_ekyc, ktp_file, face_file, status_verifikasi
      FROM tte_ekyc
      WHERE id_ekyc = ?
      LIMIT 1
   ");

   $cek->bind_param('i', $id);
   $cek->execute();
   $row = $cek->get_result()->fetch_assoc();
   $cek->close();

   if (!$row) {
      responseJson('error', 'Data eKYC tidak ditemukan.');
   }

   $uploadDir = '../../uploads/ekyc/';

   if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
   }

   /*
    |--------------------------------------------------------------------------
    | TAHAPAN
    |--------------------------------------------------------------------------
    */

   $statusSekarang = $row['status_verifikasi'] ?? 'belum';

   if ($type === 'liveness' && $statusSekarang !== 'ktp_terverifikasi') {
      responseJson('error', 'Liveness wajah hanya dapat diupload setelah KTP diverifikasi.');
   }

   $column   = $map[$type];
   $fileName = 'ekyc_' . $id . '_' . $type . '_' . time() . '.' . $ext;
   $target   = $uploadDir . $fileName;

   if (!move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
      responseJson('error', 'Gagal menyimpan file ke server.');
   }

   if (!empty($row[$column])) {
      $old = $uploadDir . basename($row[$column]);
      if (is_file($old)) {
         @unlink($old);
      }
   }

   $statusBaru = $type === 'ktp' ? 'ktp_menunggu' : 'menunggu';

   $stmt = $koneksi->prepare("
      UPDATE tte_ekyc
      SET $column = ?, status_verifikasi = ?
      WHERE id_ekyc = ?
   ");

   $stmt->bind_param('ssi', $fileName, $statusBaru, $id);

   if (!$stmt->execute()) {
      responseJson('error', 'File tersimpan tetapi gagal update data: ' . $stmt->error);
   }

   $stmt->close();

   responseJson('success', 'File berhasil diupload.', [
      'file' => $fileName,
      'type' => $type
   ]);
}

/* ============================================================
   VERIFIKASI (GLOBAL)
============================================================ */

function verifyData($data)
{
   global $koneksi;

   $id     = (int) ($data['id_ekyc'] ?? 0);
   $status = $data['status_verifikasi'] ?? '';

   if ($id <= 0 || !in_array($status, ['ktp_terverifikasi', 'menunggu', 'terverifikasi', 'ditolak'], true)) {
      responseJson('error', 'Parameter verifikasi tidak valid.');
   }

   $cek = $koneksi->prepare("
      SELECT ktp_file, face_file
      FROM tte_ekyc
      WHERE id_ekyc = ?
      LIMIT 1
   ");

   $cek->bind_param('i', $id);
   $cek->execute();
   $row = $cek->get_result()->fetch_assoc();
   $cek->close();

   if (!$row) {
      responseJson('error', 'Data eKYC tidak ditemukan.');
   }

   if ($status === 'ktp_terverifikasi' && empty($row['ktp_file'])) {
      responseJson('error', 'Verifikasi KTP memerlukan upload KTP terlebih dahulu.');
   }

   if (
      $status === 'terverifikasi' &&
      (empty($row['ktp_file']) || empty($row['face_file']))
   ) {
      responseJson('error', 'Verifikasi memerlukan upload KTP dan liveness wajah terlebih dahulu.');
   }

   $verifiedAt = $status === 'terverifikasi' ? date('Y-m-d H:i:s') : null;

   $stmt = $koneksi->prepare("
      UPDATE tte_ekyc
      SET status_verifikasi = ?, verified_at = ?
      WHERE id_ekyc = ?
   ");

   $stmt->bind_param('ssi', $status, $verifiedAt, $id);

   if (!$stmt->execute()) {
      responseJson('error', 'Gagal update verifikasi: ' . $stmt->error);
   }

   $stmt->close();

   $label = [
      'ktp_terverifikasi' => 'KTP Terverifikasi',
      'menunggu'          => 'Menunggu Verifikasi',
      'terverifikasi'     => 'Terverifikasi',
      'ditolak'           => 'Ditolak'
   ][$status];

   responseJson('success', 'Status verifikasi diubah menjadi ' . $label . '.');
}
