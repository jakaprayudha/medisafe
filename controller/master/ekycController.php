<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

$username    = $_SESSION['fullname'] ?? ($_SESSION['username'] ?? 'system');
$roleSaya    = $_SESSION['roles'] ?? '';

/*
|--------------------------------------------------------------------------
| SCOPE FASKES
|--------------------------------------------------------------------------
| admin klinik          : hanya faskes di sesinya.
| superadmin/administrator : SEMUA faskes (module/administrator/ekyc_verify), boleh menyaring ?id_customer=.
| $id_customer === null artinya tanpa batas faskes; semua query memakai (? IS NULL OR id_customer = ?).
*/
$globalEkyc = in_array($roleSaya, ['superadmin', 'administrator'], true);
if ($globalEkyc) {
   $filterFaskes = trim((string) ($_GET['id_customer'] ?? $_POST['id_customer'] ?? ''));
   $id_customer = ctype_digit($filterFaskes) && (int) $filterFaskes > 0 ? $filterFaskes : null;
} else {
   $id_customer = (string) ($_SESSION['id_customer'] ?? '');
   if ($id_customer === '' || $id_customer === '0') $id_customer = null;
}

if (!isset($_SESSION['uid_user']) || (!$globalEkyc && $id_customer === null)) {
   http_response_code(401);
   echo json_encode([
      'status'  => 'error',
      'message' => 'Session tidak ditemukan atau telah berakhir.'
   ]);
   exit;
}
session_write_close();

$method = $_SERVER['REQUEST_METHOD'];

// support spoof PUT lewat POST + _method
if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}

/*
|--------------------------------------------------------------------------
| AKSES UBAH DATA
|--------------------------------------------------------------------------
| Simpan / upload / verifikasi / hapus hanya untuk module admin.
*/

function requireAdmin()
{
   if (!in_array($GLOBALS['roleSaya'], ['admin', 'superadmin', 'administrator'], true)) {
      http_response_code(403);
      echo json_encode([
         'status'  => 'error',
         'message' => 'Aksi ini hanya dapat dilakukan oleh admin.'
      ]);
      exit;
   }
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

   // =============================
   // 🔹 LIST DATA EKYC
   // =============================
   case 'GET':

      if (isset($_GET['id']) && $_GET['id'] !== '') {
         getID($_GET['id']);
      } else {
         getData();
      }

      break;

   // =============================
   // 🔹 TAMBAH DATA EKYC / UPLOAD
   // =============================
   case 'POST':

      $action = $_POST['action'] ?? '';

      if ($action === 'upload') {
         uploadFile();
      } else {
         createData();
      }

      break;

   // =============================
   // 🔹 TOGGLE STATUS / VERIFIKASI
   // =============================
   case 'PUT':

      parse_str(file_get_contents('php://input'), $_PUT);

      if (($_PUT['action'] ?? '') === 'verify') {
         verifyData($_PUT);
      } else {
         toggleStatus();
      }

      break;

   // =============================
   // 🔹 HAPUS DATA
   // =============================
   case 'DELETE':
      deleteData();
      break;

   default:
      http_response_code(405);
      responseJson('error', 'Method tidak diizinkan.');
}

/* ============================================================
   LIST
============================================================ */

function getData()
{
   global $koneksi, $id_customer;

   $stmt = $koneksi->prepare("
      SELECT
         e.id_ekyc,
         e.id_customer,
         COALESCE(sc.clinic_name, '-') AS clinic_name,
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
         e.created_by
      FROM tte_ekyc e
      LEFT JOIN setting_clinic sc ON sc.id_customer = e.id_customer
      WHERE (? IS NULL OR e.id_customer = ?)
      ORDER BY e.id_ekyc DESC
   ");

   if (!$stmt) {
      error_log('[ekyc] ' . $koneksi->error);
      responseJson('error', 'Gagal memuat data.');
   }

   $stmt->bind_param('ss', $id_customer, $id_customer);
   $stmt->execute();

   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();

   responseJson('success', '', $data);
}

function getID($id)
{
   global $koneksi, $id_customer;

   $id = (int) $id;

   $stmt = $koneksi->prepare("
      SELECT e.*, COALESCE(sc.clinic_name, '-') AS clinic_name
      FROM tte_ekyc e
      LEFT JOIN setting_clinic sc ON sc.id_customer = e.id_customer
      WHERE e.id_ekyc = ? AND (? IS NULL OR e.id_customer = ?)
      LIMIT 1
   ");

   $stmt->bind_param('iss', $id, $id_customer, $id_customer);
   $stmt->execute();

   $data = $stmt->get_result()->fetch_assoc();
   $stmt->close();

   if (!$data) {
      responseJson('error', 'Data tidak ditemukan.');
   }

   responseJson('success', '', $data);
}

/* ============================================================
   CREATE
============================================================ */

function createData()
{
   global $koneksi, $id_customer, $username;

   requireAdmin();

   if ($id_customer === null) {
      responseJson('error', 'Pemohon baru dibuat dari akun admin klinik (atau sertakan id_customer faskes).');
   }

   if (empty($_POST)) {
      responseJson('error', 'Data tidak ditemukan.');
   }

   $nama     = trim((string) ($_POST['nama'] ?? ''));
   $email    = trim((string) ($_POST['email'] ?? ''));
   $nomor_hp = trim((string) ($_POST['nomor_hp'] ?? ''));
   $nik      = trim((string) ($_POST['nik'] ?? ''));

   /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

   if ($nama === '' || strlen($nama) < 3) {
      responseJson('error', 'Nama wajib diisi minimal 3 karakter.');
   }

   if ($nik === '' || !preg_match('/^[0-9]{16}$/', $nik)) {
      responseJson('error', 'NIK wajib 16 digit angka.');
   }

   if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      responseJson('error', 'Format email tidak valid.');
   }

   if ($nomor_hp !== '' && !preg_match('/^[0-9]{10,15}$/', $nomor_hp)) {
      responseJson('error', 'Nomor HP harus 10-15 digit angka.');
   }

   /*
    |--------------------------------------------------------------------------
    | CEK DUPLIKAT NIK
    |--------------------------------------------------------------------------
    */

   $cek = $koneksi->prepare("
      SELECT id_ekyc
      FROM tte_ekyc
      WHERE id_customer = ? AND nik = ?
      LIMIT 1
   ");

   $cek->bind_param('ss', $id_customer, $nik);
   $cek->execute();
   $exists = $cek->get_result()->fetch_assoc();
   $cek->close();

   if ($exists) {
      responseJson('error', 'NIK tersebut sudah terdaftar pada eKYC.');
   }

   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

   $stmt = $koneksi->prepare("
      INSERT INTO tte_ekyc
         (id_customer, nama, email, nomor_hp, nik, status, created_by)
      VALUES
         (?, ?, ?, ?, ?, 1, ?)
   ");

   if (!$stmt) {
      responseJson('error', 'Prepare gagal: ' . $koneksi->error);
   }

   $stmt->bind_param(
      'ssssss',
      $id_customer,
      $nama,
      $email,
      $nomor_hp,
      $nik,
      $username
   );

   if (!$stmt->execute()) {
      responseJson('error', 'Gagal menyimpan: ' . $stmt->error);
   }

   $newId = $koneksi->insert_id;
   $stmt->close();

   responseJson('success', 'Data eKYC berhasil disimpan.', [
      'id_ekyc' => $newId
   ]);
}

/* ============================================================
   TOGGLE STATUS
============================================================ */

function toggleStatus()
{
   global $koneksi, $id_customer;

   requireAdmin();

   parse_str(file_get_contents('php://input'), $_PUT);

   $id     = (int) ($_PUT['id_ekyc'] ?? 0);
   $status = (int) ($_PUT['status'] ?? 1);

   if ($id <= 0) {
      responseJson('error', 'ID tidak ditemukan.');
   }

   $stmt = $koneksi->prepare("
      UPDATE tte_ekyc
      SET status = ?
      WHERE id_ekyc = ? AND (? IS NULL OR id_customer = ?)
   ");

   $stmt->bind_param('iiss', $status, $id, $id_customer, $id_customer);

   if (!$stmt->execute()) {
      responseJson('error', 'Gagal update status: ' . $stmt->error);
   }

   $stmt->close();

   responseJson('success', 'Status berhasil diperbarui.');
}

/* ============================================================
   DELETE
============================================================ */

function deleteData()
{
   requireAdmin();

   global $koneksi, $id_customer;

   $id = (int) ($_GET['id'] ?? 0);

   if ($id <= 0) {
      responseJson('error', 'ID tidak ditemukan.');
   }

   $cekFile = $koneksi->prepare("SELECT ktp_file, face_file FROM tte_ekyc WHERE id_ekyc = ? AND (? IS NULL OR id_customer = ?) LIMIT 1");
   $cekFile->bind_param('iss', $id, $id_customer, $id_customer);
   $cekFile->execute();
   $fileLama = $cekFile->get_result()->fetch_assoc();
   $cekFile->close();
   if (!$fileLama) {
      responseJson('error', 'Data tidak ditemukan.');
   }

   $stmt = $koneksi->prepare("
      DELETE FROM tte_ekyc
      WHERE id_ekyc = ? AND (? IS NULL OR id_customer = ?)
   ");

   $stmt->bind_param('iss', $id, $id_customer, $id_customer);

   if (!$stmt->execute()) {
      error_log('[ekyc] hapus: ' . $stmt->error);
      responseJson('error', 'Gagal menghapus data.');
   }

   $stmt->close();

   // foto KTP & wajah adalah data pribadi: ikut dihapus dari server
   foreach (['ktp_file', 'face_file'] as $kolomFoto) {
      if (!empty($fileLama[$kolomFoto])) {
         $pathFoto = '../../uploads/ekyc/' . basename($fileLama[$kolomFoto]);
         if (is_file($pathFoto)) @unlink($pathFoto);
      }
   }

   responseJson('success', 'Data eKYC berhasil dihapus.');
}

/* ============================================================
   UPLOAD KTP / LIVENESS WAJAH
============================================================ */

function uploadFile()
{
   requireAdmin();

   global $koneksi, $id_customer;

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

   // batas 5 MB
   if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
      responseJson('error', 'Ukuran file maksimal 5 MB.');
   }

   $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

   if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
      responseJson('error', 'Format file harus JPG, JPEG, PNG, atau WEBP.');
   }

   // validasi mime asli
   $finfo = finfo_open(FILEINFO_MIME_TYPE);
   $mime  = finfo_file($finfo, $_FILES['file']['tmp_name']);
   finfo_close($finfo);

   if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
      responseJson('error', 'Isi file bukan gambar yang valid.');
   }

   // pastikan data milik customer
   $cek = $koneksi->prepare("
      SELECT id_ekyc, ktp_file, face_file, status_verifikasi
      FROM tte_ekyc
      WHERE id_ekyc = ? AND (? IS NULL OR id_customer = ?)
      LIMIT 1
   ");

   $cek->bind_param('iss', $id, $id_customer, $id_customer);
   $cek->execute();
   $row = $cek->get_result()->fetch_assoc();
   $cek->close();

   if (!$row) {
      responseJson('error', 'Data eKYC tidak ditemukan.');
   }

   // pemohon yang sudah terverifikasi tidak boleh ditimpa berkasnya
   if (($row['status_verifikasi'] ?? '') === 'terverifikasi') {
      responseJson('error', 'Pemohon sudah terverifikasi; berkas tidak dapat diganti.');
   }

   $uploadDir = '../../uploads/ekyc/';

   if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
   }

   $column   = $map[$type];
   $fileName = 'ekyc_' . $id . '_' . $type . '_' . time() . '.' . $ext;
   $target   = $uploadDir . $fileName;

   /*
    |--------------------------------------------------------------------------
    | TAHAPAN VERIFIKASI
    |--------------------------------------------------------------------------
    | ktp:
    |   belum / ditolak / ktp_menunggu -> ktp_menunggu
    |
    | liveness:
    |   hanya boleh setelah KTP diverifikasi admin
    |   (status_verifikasi = ktp_terverifikasi) -> menunggu
    |--------------------------------------------------------------------------
    */

   $statusSekarang = $row['status_verifikasi'] ?? 'belum';

   if ($type === 'liveness' && $statusSekarang !== 'ktp_terverifikasi') {
      responseJson('error', 'Liveness wajah hanya dapat diupload setelah KTP diverifikasi oleh admin.');
   }
   // liveness wajib lewat kamera di halaman eKYC (kedip + toleh kiri/kanan), bukan upload foto
   if ($type === 'liveness') {
      $lv = json_decode((string) ($_POST['liveness'] ?? ''), true);
      if (($_POST['sumber'] ?? '') !== 'kamera' || !is_array($lv) || empty($lv['kedip']) || empty($lv['kiri']) || empty($lv['kanan'])) {
         responseJson('error', 'Liveness wajib diambil langsung dari kamera (kedip & toleh kiri/kanan). Upload foto tidak diterima.');
      }
   }

   if (!move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
      responseJson('error', 'Gagal menyimpan file ke server.');
   }

   // hapus file lama jika ada
   if (!empty($row[$column])) {
      $old = $uploadDir . basename($row[$column]);
      if (is_file($old)) {
         @unlink($old);
      }
   }

   if ($type === 'ktp') {
      $statusBaru = 'ktp_menunggu';
   } else {
      $statusBaru = 'menunggu';
   }

   $stmt = $koneksi->prepare("
      UPDATE tte_ekyc
      SET $column = ?, status_verifikasi = ?
      WHERE id_ekyc = ? AND (? IS NULL OR id_customer = ?)
   ");

   $stmt->bind_param('ssiss', $fileName, $statusBaru, $id, $id_customer, $id_customer);

   if (!$stmt->execute()) {
      error_log('[ekyc] upload: ' . $stmt->error);
      @unlink($target);
      responseJson('error', 'File tersimpan tetapi gagal update data.');
   }

   $stmt->close();

   responseJson('success', 'File berhasil diupload.', [
      'file' => $fileName,
      'type' => $type
   ]);
}

/* ============================================================
   VERIFIKASI
============================================================ */

function verifyData($data)
{
   requireAdmin();

   global $koneksi, $id_customer;

   $id     = (int) ($data['id_ekyc'] ?? 0);
   $status = $data['status_verifikasi'] ?? '';

   // 'menunggu' hanya diisi sistem saat liveness diunggah, bukan dipilih manual
   if ($id <= 0 || !in_array($status, ['ktp_terverifikasi', 'terverifikasi', 'ditolak'], true)) {
      responseJson('error', 'Parameter verifikasi tidak valid.');
   }

   $cek = $koneksi->prepare("
      SELECT ktp_file, face_file, status_verifikasi
      FROM tte_ekyc
      WHERE id_ekyc = ? AND (? IS NULL OR id_customer = ?)
      LIMIT 1
   ");

   $cek->bind_param('iss', $id, $id_customer, $id_customer);
   $cek->execute();
   $row = $cek->get_result()->fetch_assoc();
   $cek->close();

   if (!$row) {
      responseJson('error', 'Data eKYC tidak ditemukan.');
   }

   // urutan tahap: KTP menunggu -> KTP terverifikasi -> (liveness) menunggu -> terverifikasi; tolak hanya saat proses berjalan
   $dari = (string) ($row['status_verifikasi'] ?? 'belum');
   $boleh = [
      'ktp_terverifikasi' => ['ktp_menunggu'],
      'terverifikasi'     => ['menunggu'],
      'ditolak'           => ['ktp_menunggu', 'ktp_terverifikasi', 'menunggu'],
   ];
   if (!in_array($dari, $boleh[$status], true)) {
      responseJson('error', 'Perubahan status tidak sesuai tahapan (status saat ini: ' . $dari . ').');
   }

   // verifikasi KTP wajib ada file KTP
   if ($status === 'ktp_terverifikasi' && empty($row['ktp_file'])) {
      responseJson('error', 'Verifikasi KTP memerlukan upload KTP terlebih dahulu.');
   }

   // verifikasi akhir wajib KTP + liveness wajah
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
      WHERE id_ekyc = ? AND (? IS NULL OR id_customer = ?)
   ");

   $stmt->bind_param('ssiss', $status, $verifiedAt, $id, $id_customer, $id_customer);

   if (!$stmt->execute()) {
      error_log('[ekyc] verifikasi: ' . $stmt->error);
      responseJson('error', 'Gagal memperbarui verifikasi.');
   }

   $stmt->close();

   $label = [
      'ktp_terverifikasi' => 'KTP Terverifikasi',
      'terverifikasi'     => 'Terverifikasi',
      'ditolak'           => 'Ditolak'
   ][$status];

   responseJson('success', 'Status verifikasi diubah menjadi ' . $label . '.');
}
