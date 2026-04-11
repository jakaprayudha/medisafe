<?php
include '../../database/connect.php';

$assemen = $_GET['kode'] ?? ''; // sebenarnya ini nama
$id_inspection = $_GET['id_inspection'] ?? 0;

if (!$assemen) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Assemen tidak ditemukan'
   ]);
   exit;
}

$query = "
SELECT 
  li.*,
  lr.hasil
FROM laboratorium_detail ld
JOIN laboratorium_item li ON li.kode = ld.kode
LEFT JOIN laboratorium_result lr 
  ON lr.id_item = li.id AND lr.id_inspection = ?
WHERE ld.assemen = ?
";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("is", $id_inspection, $assemen);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
   'status' => 'success',
   'data' => $data
]);
