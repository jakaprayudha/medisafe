<?php
include '../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');

$assemen = $_GET['kode'] ?? ''; // sebenarnya ini nama
$id_inspection = $_GET['id_inspection'] ?? 0;

if (!$assemen) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Assemen tidak ditemukan'
   ]);
   exit;
}

// hasil hanya dibaca untuk permintaan lab milik faskes yang login
$id_inspection = (int) $id_inspection;
if ($id_inspection > 0) {
   $idcLab = (string) ($_SESSION['id_customer'] ?? '');
   $cekLab = $koneksi->prepare("SELECT 1 FROM visit_inspection WHERE id_inspection = ? AND id_customer = ? LIMIT 1");
   $cekLab->bind_param('is', $id_inspection, $idcLab);
   $cekLab->execute();
   if (!$cekLab->get_result()->fetch_row()) {
      http_response_code(403);
      echo json_encode(['status' => 'error', 'message' => 'Permintaan lab tidak ditemukan untuk faskes ini.']);
      exit;
   }
   $cekLab->close();
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
ORDER BY ld.id_lab, li.id
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
