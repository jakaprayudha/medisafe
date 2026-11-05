<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

$id_customer = $_SESSION['id_customer'] ?? null;
$username    = $_SESSION['fullname'] ?? ($_SESSION['username'] ?? 'system');

$method = $_SERVER['REQUEST_METHOD'];

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
      createData();
      break;

   case 'DELETE':
      deleteData();
      break;

   default:
      http_response_code(405);
      responseJson('error', 'Method tidak diizinkan.');
}

/* ============================================================
   LIST PER KUNJUNGAN
============================================================ */

function getData()
{
   global $koneksi, $id_customer;

   $no = mysqli_real_escape_string($koneksi, $_GET['no'] ?? '');

   $stmt = $koneksi->prepare("
      SELECT *
      FROM pasien_persalinan_anc
      WHERE id_visit = ?
      ORDER BY tanggal_kontrol DESC, id_anc DESC
   ");

   $stmt->bind_param('s', $no);
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
      SELECT * FROM pasien_persalinan_anc WHERE id_anc = ? LIMIT 1
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
   CREATE (+ upload gambar opsional)
============================================================ */

function createData()
{
   global $koneksi, $id_customer, $username;

   if (empty($_POST)) {
      responseJson('error', 'Data tidak ditemukan.');
   }

   $id_visit = trim((string) ($_POST['id_visit'] ?? ''));

   if ($id_visit === '') {
      responseJson('error', 'ID kunjungan tidak ditemukan.');
   }

   $allowedFields = [
      'nomor_rm',
      'tanggal_kontrol',
      'hpht',
      'taksiran_persalinan',
      'usia_gestasi',
      'gravida',
      'para',
      'abortus',
      'tinggi_fundus',
      'presentasi',
      'djj',
      'tekanan_darah',
      'berat_badan',
      'protein_urine',
      'edema',
      'catatan'
   ];

   $fields = ['anc_number', 'id_visit', 'id_customer', 'created_by'];
   $values = [generateAncNumber($koneksi), $id_visit, $id_customer, $username];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);
   $types = str_repeat('s', count($fields));

   $stmt = $koneksi->prepare("INSERT INTO pasien_persalinan_anc ($columns) VALUES ($placeholders)");

   if (!$stmt) {
      responseJson('error', 'Prepare gagal: ' . $koneksi->error);
   }

   $stmt->bind_param($types, ...$values);

   if (!$stmt->execute()) {
      responseJson('error', 'Gagal menyimpan: ' . $stmt->error);
   }

   $newId = $koneksi->insert_id;
   $stmt->close();

   /*
    |--------------------------------------------------------------------------
    | UPLOAD GAMBAR (OPSIONAL)
    |--------------------------------------------------------------------------
    */

   $gambarSaved = null;

   if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
      $gambarSaved = saveAncImage($newId, $_FILES['gambar']);
   }

   responseJson('success', 'Data ANC berhasil disimpan.', [
      'id_anc' => $newId,
      'gambar' => $gambarSaved
   ]);
}

/**
 * Simpan gambar ANC dan update kolom gambar.
 */
function saveAncImage($idAnc, $file)
{
   global $koneksi;

   if ($file['size'] > 5 * 1024 * 1024) {
      return null;
   }

   $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

   if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
      return null;
   }

   $finfo = finfo_open(FILEINFO_MIME_TYPE);
   $mime  = finfo_file($finfo, $file['tmp_name']);
   finfo_close($finfo);

   if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
      return null;
   }

   $uploadDir = '../../uploads/persalinan/';

   if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
   }

   $fileName = 'anc_' . $idAnc . '_' . time() . '.' . $ext;
   $target   = $uploadDir . $fileName;

   if (!move_uploaded_file($file['tmp_name'], $target)) {
      return null;
   }

   $stmt = $koneksi->prepare("
      UPDATE pasien_persalinan_anc SET gambar = ? WHERE id_anc = ?
   ");
   $stmt->bind_param('si', $fileName, $idAnc);
   $stmt->execute();
   $stmt->close();

   return $fileName;
}

/**
 * Generate anc_number unik dengan format ANC-XXXXXX
 */
function generateAncNumber($koneksi)
{
   $count = 0;
   do {
      $random = mt_rand(100000, 999999);
      $number = "ANC-" . $random;

      $check = $koneksi->prepare("SELECT COUNT(*) FROM pasien_persalinan_anc WHERE anc_number = ?");
      $check->bind_param("s", $number);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $number;
}

/* ============================================================
   DELETE (+ hapus file gambar)
============================================================ */

function deleteData()
{
   global $koneksi;

   $id = (int) ($_GET['id'] ?? 0);

   if ($id <= 0) {
      responseJson('error', 'ID tidak ditemukan.');
   }

   $stmt = $koneksi->prepare("SELECT gambar FROM pasien_persalinan_anc WHERE id_anc = ? LIMIT 1");
   $stmt->bind_param('i', $id);
   $stmt->execute();
   $row = $stmt->get_result()->fetch_assoc();
   $stmt->close();

   if ($row && !empty($row['gambar'])) {
      $old = '../../uploads/persalinan/' . basename($row['gambar']);
      if (is_file($old)) {
         @unlink($old);
      }
   }

   $del = $koneksi->prepare("DELETE FROM pasien_persalinan_anc WHERE id_anc = ?");
   $del->bind_param('i', $id);

   if (!$del->execute()) {
      responseJson('error', 'Gagal menghapus: ' . $del->error);
   }

   $del->close();

   responseJson('success', 'Data ANC berhasil dihapus.');
}
