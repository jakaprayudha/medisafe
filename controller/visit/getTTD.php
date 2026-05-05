<?php
include '../../database/connect.php';

header('Content-Type: application/json');
session_start();
$no = $_GET['no'] ?? null;
$id_customer = $_SESSION['id_customer'];

if (!$no) {
   echo json_encode([
      'status' => 'error',
      'message' => 'ID visit tidak ditemukan'
   ]);
   exit;
}

// 🔥 QUERY AMBIL TTD
$stmt = $koneksi->prepare("
    SELECT *
    FROM pasien_visit 
    WHERE visit_ID = ? AND id_customer = ?
    LIMIT 1
");

$stmt->bind_param("si", $no, $id_customer);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
   $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
   $host = $_SERVER['HTTP_HOST'];

   $scriptPath = $_SERVER['PHP_SELF'];

   $basePathSegments = explode('/', $scriptPath);
   for ($i = 0; $i < 4; $i++) {
      array_pop($basePathSegments);
   }
   $webRootRelativePath = implode('/', $basePathSegments);

   $baseUrl = $protocol . "://" . $host . rtrim($webRootRelativePath, '/') . '/';

   $ttd_url = !empty($row['signature_path'])
      ? $baseUrl . "uploads/ttd/" . $row['signature_path']
      : null;
   echo json_encode([
      'status' => 'success',
      'data' => [
         'ttd' => $ttd_url
      ]
   ]);
} else {
   echo json_encode([
      'status' => 'success',
      'data' => null
   ]);
}

$stmt->close();
