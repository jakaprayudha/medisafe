<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$sql = mysqli_query($koneksi, "SELECT * FROM master_poli WHERE status_poli = 1");
$data = [];

while ($row = mysqli_fetch_assoc($sql)){
    $data[] = [
        "kdPoli" => $row['kdPoli'],
        "nmPoli" => $row['nmPoli'],
        "poliSakit" => (bool)$row['poliSakit']
    ];
}

$response = [
    "success" => true,
    "code" => "200",
    "message" => "OK",
    "data" => $data
];

echo json_encode($response, JSON_PRETTY_PRINT);