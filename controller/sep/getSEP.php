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

// 🔥 ambil dari pasien_visit
$stmt = $koneksi->prepare("
    SELECT sep_file 
    FROM pasien_visit
    WHERE visit_ID = ? AND id_customer = ?
    LIMIT 1
");

$stmt->bind_param("si", $no, $id_customer);
$stmt->execute();

$result = $stmt->get_result();
$sep = $result->fetch_assoc();

$stmt->close();

echo json_encode([
    "status" => "success",
    "sep" => $sep
]);
