<?php
require_once __DIR__ . '/view.php';
header('Content-Type: application/json');
$type = $_GET['type'];
$status = '1';
if ($type === 'BPJS') {
    $sql = "
        SELECT id_provider AS id, provider_name AS text
        FROM ms_provider
        WHERE provider_status = ?
        ORDER BY provider_name ASC
        LIMIT 100
    ";

} else {

    $sql = "
        SELECT id_provider AS id, provider_name AS text
        FROM ms_provider
        WHERE provider_name NOT LIKE '%BPJS%'
        AND provider_status = ?
        ORDER BY provider_name ASC
        LIMIT 100
    ";
}
$stmt = $koneksi->prepare($sql);
$stmt->bind_param('s', $status);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);