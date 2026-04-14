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
    SELECT signature_path 
    FROM pasien_visit 
    WHERE visit_ID = ?
    LIMIT 1
");

$stmt->bind_param("s", $no);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
   $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
   $base_url .= "://" . $_SERVER['HTTP_HOST'];

   // kalau project di subfolder (medisafe)
   $base_url .= "/medisafe";

   $ttd_url = !empty($row['signature_path'])
      ? $base_url . "/uploads/ttd/" . $row['signature_path']
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
