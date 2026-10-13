<?php
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['uid_user']) || empty($_SESSION['id_customer'])) {
   http_response_code(401);
   exit('Session berakhir. Silakan login kembali.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
   http_response_code(405);
   exit('Method tidak diizinkan.');
}
$visit = $_GET['visit_ID'] ?? '';
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if (!is_string($visit) || $visit === '' || !$id || $id < 1) {
   http_response_code(400);
   exit('Parameter gambar penanda operasi tidak valid.');
}
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/operationMarkerStore.php';
try {
   $clinic = (int) $_SESSION['id_customer'];
   if (!operationMarkerVisit($koneksi, $visit, $clinic)) {
      http_response_code(404);
      exit('Kunjungan tidak ditemukan pada klinik ini.');
   }
   $documents = operationMarkerDocuments($koneksi, $visit, $clinic, $id);
   if (!$documents || $documents[0]['image_png'] === '') {
      http_response_code(404);
      exit('Gambar penanda operasi tidak ditemukan.');
   }
   header('Content-Type: image/png');
   echo $documents[0]['image_png'];
} catch (mysqli_sql_exception $error) {
   error_log('Operation marker image: ' . $error->getMessage());
   http_response_code(500);
   exit('Gagal memuat gambar penanda operasi. Hubungi administrator.');
}
