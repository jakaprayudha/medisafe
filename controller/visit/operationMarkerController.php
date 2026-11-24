<?php
/**
 * Penanda operasi (module/admin/penanda_operasi). Setiap simpan = versi baru (tidak ada ubah/hapus).
 *   GET  ?visit_ID=..          -> daftar versi kunjungan (versi dihitung per jenis gambar) + status kunjungan
 *   GET  ?visit_ID=..&id=..    -> 1 dokumen (anotasi + catatan; tanpa gambar PNG — editor menggambar ulang dari anotasi)
 *   POST (JSON)                -> simpan versi baru
 * Perbaikan: buka dokumen dulu ikut mengirim PNG base64 (~2,6 MB) yang tidak dipakai; nomor versi dihitung lintas
 * jenis gambar; kunjungan batal tetap bisa disimpan; sesi tidak ditutup (request lain pengguna yang sama ikut antre).
 */
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
$sessionToken = (string) ($_SESSION['operation_marker_token'] ?? '');
$authorId = (string) $_SESSION['uid_user'];
$author = (string) ($_SESSION['fullname'] ?? $_SESSION['uid_user']);
session_write_close();
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
    $sessionToken === '' || !hash_equals($sessionToken, $data['token']))) {
   markerResponse(403, ['status' => 'error', 'message' => 'Token formulir tidak valid. Muat ulang halaman.']);
}

try {
   if (!operationMarkerVisit($koneksi, $visit, $clinic)) {
      markerResponse(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan pada klinik ini']);
   }
   $stmt = $koneksi->prepare("SELECT visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $stmt->bind_param('si', $visit, $clinic);
   $stmt->execute();
   $batal = (int) ($stmt->get_result()->fetch_row()[0] ?? 0) === 99;
   $stmt->close();
   if ($method === 'GET') {
      if (isset($data['id'])) {
         $id = filter_var($data['id'], FILTER_VALIDATE_INT);
         if (!$id || $id < 1) markerResponse(400, ['status' => 'error', 'message' => 'ID dokumen tidak valid']);
         $stmt = $koneksi->prepare("SELECT id, marker_type, annotations, notes, created_by, created_at
            FROM operation_markers WHERE id = ? AND visit_ID = ? AND id_customer = ? LIMIT 1");
         $stmt->bind_param('isi', $id, $visit, $clinic);
         $stmt->execute();
         $document = $stmt->get_result()->fetch_assoc();
         if (!$document) markerResponse(404, ['status' => 'error', 'message' => 'Dokumen tidak ditemukan']);
         $document['annotations'] = json_decode($document['annotations'], true, 512, JSON_THROW_ON_ERROR);
         markerResponse(200, ['status' => 'success', 'data' => $document]);
      }
      $stmt = $koneksi->prepare("SELECT id, marker_type, notes, created_by, created_at,
            JSON_LENGTH(annotations) AS jumlah_penanda, (OCTET_LENGTH(image_png) > 0) AS ada_gambar
         FROM operation_markers WHERE visit_ID = ? AND id_customer = ? ORDER BY id ASC");
      $stmt->bind_param('si', $visit, $clinic);
      $stmt->execute();
      $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
      $per = [];
      foreach ($rows as &$r) {
         $r['versi'] = $per[$r['marker_type']] = ($per[$r['marker_type']] ?? 0) + 1;   // versi per jenis gambar
         $r['jumlah_penanda'] = (int) $r['jumlah_penanda'];
         $r['ada_gambar'] = (bool) $r['ada_gambar'];
      }
      unset($r);
      foreach ($rows as &$r) $r['terbaru'] = $r['versi'] === $per[$r['marker_type']];
      unset($r);
      markerResponse(200, ['status' => 'success', 'data' => array_reverse($rows), 'kunjungan' => ['batal' => $batal]]);
   }
   if ($batal) markerResponse(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan — penanda operasi tidak bisa disimpan.']);
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
