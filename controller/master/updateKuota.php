<?php
header("Content-Type: application/json");
require_once '../../database/connect.php';

$id_customer = $_SESSION['id_customer'];

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['id']) || !isset($data['kuota'])) {
    echo json_encode([
        "success" => false,
        "message" => "Data tidak lengkap"
    ]);
    exit;
}

$id = $data['id'];
$kuota = $data['kuota'];

// validasi
if (!is_numeric($kuota) || $kuota < 0) {
    echo json_encode([
        "success" => false,
        "message" => "Kuota tidak valid"
    ]);
    exit;
}

// update
$stmt = $koneksi->prepare("UPDATE ms_doctor_schedule SET kuota = ? WHERE id_schedule = ? AND id_customer = ?");
$stmt->bind_param("iii", $kuota, $id, $id_customer);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Kuota berhasil diperbarui"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal update kuota"
    ]);
}

$stmt->close();
$koneksi->close();