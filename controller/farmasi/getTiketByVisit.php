<?php
require '../../database/connect.php';

header('Content-Type: application/json');

$no = $_GET['no'] ?? '';

if (!$no) {
   echo json_encode([
      'status' => 'error',
      'message' => 'No visit kosong'
   ]);
   exit;
}

// ==========================
// 🔥 GET SEMUA TIKET BY VISIT
// ==========================
$stmt = $koneksi->prepare("SELECT 
       *
    FROM permintaan_pharmacy
    WHERE id_visit = ? AND status_permintaan IN (1,2,3) AND id_customer = ?
    ORDER BY status_permintaan ASC, created_at DESC
");

$stmt->bind_param("ss", $no, $_SESSION['id_customer']);
$stmt->execute();

$result = $stmt->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
   $data[] = $row;
}

$stmt->close();

// ==========================
// 🔥 RESPONSE
// ==========================
echo json_encode([
   'status' => 'success',
   'data' => $data
]);
