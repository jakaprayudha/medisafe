<?php
include '../../database/connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

$id_customer = $_SESSION['id_customer'] ?? null;
$no = $_GET['no'] ?? null;

if (!$id_customer || !$no) {
    echo json_encode([
        "status" => "error",
        "message" => "Parameter tidak lengkap"
    ]);
    exit;
}

// 🔥 PREPARE
$stmt = $koneksi->prepare("
    SELECT fkpp_file 
    FROM pasien_visit
    WHERE visit_ID = ? AND id_customer = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => $koneksi->error
    ]);
    exit;
}

$stmt->bind_param("si", $no, $id_customer);
$stmt->execute();

$result = $stmt->get_result();
$sep = $result->fetch_assoc();

$stmt->close();

// 🔥 HANDLE NULL
if (!$sep) {
    $sep = ["fkpp_file" => null];
}

echo json_encode([
    "status" => "success",
    "fkpp" => $sep
]);
