<?php
header('Content-Type: application/json; charset=utf-8');

function markerResponse(int $code, array $response): void
{
   http_response_code($code);
   echo json_encode($response, JSON_UNESCAPED_UNICODE);
   exit;
}

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['uid_user'])) markerResponse(401, ['status' => 'error', 'message' => 'Session berakhir. Silakan login kembali.']);
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/operationMarkerStore.php';
$clinic = (int) ($_SESSION['id_customer'] ?? 0);
if ($clinic <= 0) markerResponse(401, ['status' => 'error', 'message' => 'Session klinik tidak ditemukan']);
$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['GET', 'POST'], true)) {
   markerResponse(405, ['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
$body = $method === 'POST' ? file_get_contents('php://input', false, null, 0, 16 * 1024 * 1024 + 1) : '';
if (strlen($body) > 16 * 1024 * 1024) markerResponse(413, ['status' => 'error', 'message' => 'Ukuran dokumen terlalu besar']);
$data = $method === 'POST' ? json_decode($body, true) : $_GET;
if (!is_array($data)) markerResponse(400, ['status' => 'error', 'message' => 'JSON tidak valid']);
$visit = $data['visit_ID'] ?? '';
if (!is_string($visit) || $visit === '') markerResponse(400, ['status' => 'error', 'message' => 'Nomor kunjungan wajib diisi']);
if ($method === 'POST' && (!is_string($data['token'] ?? null) ||
    !hash_equals($_SESSION['operation_marker_token'] ?? '', $data['token']))) {
   markerResponse(403, ['status' => 'error', 'message' => 'Token formulir tidak valid. Muat ulang halaman.']);
}

try {
   if (!operationMarkerVisit($koneksi, $visit, $clinic)) {
      markerResponse(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan pada klinik ini']);
   }
   if ($method === 'GET') {
      if (isset($data['id'])) {
         $id = filter_var($data['id'], FILTER_VALIDATE_INT);
         if (!$id || $id < 1) markerResponse(400, ['status' => 'error', 'message' => 'ID dokumen tidak valid']);
         $documents = operationMarkerDocuments($koneksi, $visit, $clinic, $id);
         if (!$documents) markerResponse(404, ['status' => 'error', 'message' => 'Dokumen tidak ditemukan']);
         $document = $documents[0];
         $document['image'] = 'data:image/png;base64,' . base64_encode($document['image_png']);
         unset($document['image_png']);
         $document['annotations'] = json_decode($document['annotations'], true, 512, JSON_THROW_ON_ERROR);
         markerResponse(200, ['status' => 'success', 'data' => $document]);
      }
      $stmt = $koneksi->prepare("SELECT id, marker_type, notes, created_by, created_at
         FROM operation_markers WHERE visit_ID = ? AND id_customer = ? ORDER BY id DESC");
      $stmt->bind_param('si', $visit, $clinic);
      $stmt->execute();
      markerResponse(200, ['status' => 'success', 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
   }
   $type = $data['marker_type'] ?? '';
   $notes = $data['notes'] ?? '';
   $annotations = $data['annotations'] ?? null;
   $image = $data['image'] ?? '';
   if (!is_string($type) || !isset(operationMarkerTypes()[$type]) ||
       !is_string($notes) || mb_strlen($notes) > 5000 ||
       !is_array($annotations) || !array_is_list($annotations) || count($annotations) < 1 || count($annotations) > 1000 ||
       !is_string($image) || strlen($image) > 12 * 1024 * 1024 ||
       !str_starts_with($image, 'data:image/png;base64,')) {
      markerResponse(400, ['status' => 'error', 'message' => 'Gambar, catatan, atau penanda tidak valid. Tambahkan coretan atau teks sebelum menyimpan.']);
   }
   foreach ($annotations as $annotation) {
      if (!is_array($annotation) || !in_array($annotation['kind'] ?? '', ['stroke', 'text'], true) ||
          !is_string($annotation['color'] ?? null) || !preg_match('/^#[0-9a-f]{6}$/i', $annotation['color']) ||
          !is_numeric($annotation['size'] ?? null) || $annotation['size'] < 1 || $annotation['size'] > 100) {
         markerResponse(400, ['status' => 'error', 'message' => 'Format penanda tidak valid']);
      }
      $points = $annotation['kind'] === 'stroke' ? ($annotation['points'] ?? null) : [[$annotation['x'] ?? null, $annotation['y'] ?? null]];
      if (!is_array($points) || !array_is_list($points) || count($points) < 1 || count($points) > 10000) {
         markerResponse(400, ['status' => 'error', 'message' => 'Koordinat penanda tidak valid']);
      }
      foreach ($points as $point) {
         if (!is_array($point) || count($point) !== 2 || !isset($point[0], $point[1]) ||
             !is_numeric($point[0]) || !is_numeric($point[1]) ||
             $point[0] < 0 || $point[0] > 1 || $point[1] < 0 || $point[1] > 1) {
            markerResponse(400, ['status' => 'error', 'message' => 'Koordinat penanda di luar gambar']);
         }
      }
      if ($annotation['kind'] === 'text' && (!is_string($annotation['text'] ?? null) ||
          trim($annotation['text']) === '' || mb_strlen($annotation['text']) > 200)) {
         markerResponse(400, ['status' => 'error', 'message' => 'Teks penanda maksimal 200 karakter']);
      }
   }
   $binary = base64_decode(substr($image, strlen('data:image/png;base64,')), true);
   $dimensions = $binary !== false ? @getimagesizefromstring($binary) : false;
   $templateFile = __DIR__ . "/../../assets/images/site-marker-$type.png";
   $template = is_file($templateFile) ? @getimagesize($templateFile) : false;
   if (!$template) {
      error_log("Operation marker template missing or invalid: $type");
      markerResponse(500, ['status' => 'error', 'message' => 'Template penanda operasi tidak tersedia. Hubungi administrator.']);
   }
   if (!$dimensions || $dimensions[2] !== IMAGETYPE_PNG || strlen($binary) > 8 * 1024 * 1024 ||
       $dimensions[0] !== $template[0] || $dimensions[1] !== $template[1]) {
      markerResponse(400, ['status' => 'error', 'message' => 'Ukuran atau format gambar tidak sesuai template']);
   }
   $decodedImage = @imagecreatefromstring($binary);
   if ($decodedImage === false) markerResponse(400, ['status' => 'error', 'message' => 'Isi gambar PNG rusak atau tidak valid']);
   unset($decodedImage);
   $json = json_encode($annotations, JSON_UNESCAPED_UNICODE);
   if (strlen($json) > 2 * 1024 * 1024) markerResponse(413, ['status' => 'error', 'message' => 'Jumlah coretan terlalu besar']);
   $authorId = (string) $_SESSION['uid_user'];
   $author = (string) ($_SESSION['fullname'] ?? $_SESSION['uid_user']);
   $createdAt = date('Y-m-d H:i:s');
   $stmt = $koneksi->prepare("INSERT INTO operation_markers
      (id_customer, visit_ID, marker_type, annotations, image_png, notes, created_by_id, created_by, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
   $stmt->bind_param('issssssss', $clinic, $visit, $type, $json, $binary, $notes, $authorId, $author, $createdAt);
   $stmt->execute();
   markerResponse(201, ['status' => 'success', 'message' => 'Versi baru penanda operasi berhasil disimpan', 'id' => $koneksi->insert_id]);
} catch (mysqli_sql_exception $error) {
   error_log('Operation marker: ' . $error->getMessage());
   markerResponse(500, ['status' => 'error', 'message' => 'Gagal mengakses dokumen penanda operasi. Pastikan migrasi database sudah dijalankan.']);
} catch (JsonException $error) {
   error_log('Operation marker annotations: ' . $error->getMessage());
   markerResponse(500, ['status' => 'error', 'message' => 'Data penanda tersimpan tidak dapat dibaca. Hubungi administrator.']);
}
