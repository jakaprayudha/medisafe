<?php
require_once __DIR__ . '/view.php';
header('Content-Type: application/json');
$search = $_GET['search'] ?? '';
$stmt = $koneksi->prepare("
    SELECT provider_name AS id, provider_name AS text 
    FROM ms_provider 
    WHERE provider_name LIKE CONCAT('%', ?, '%')
    LIMIT 100
");

$stmt->bind_param("s", $search);
$stmt->execute();
$hasil = $stmt->get_result();

$data = [];
while ($row = $hasil->fetch_assoc()){
    $data[] = $row;
}
echo json_encode($data);