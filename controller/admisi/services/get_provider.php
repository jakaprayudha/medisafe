<?php
require_once __DIR__ . '/view.php';
header('Content-Type: application/json');
$stmt = $koneksi->prepare("SELECT provider_name AS id, provider_name AS text FROM ms_provider ORDER BY provider_name ASC LIMIT 100");
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);