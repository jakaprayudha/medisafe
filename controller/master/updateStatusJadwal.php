<?php
header('Content-Type: application/json');

require_once '../../database/connect.php';
$id_customer = $_SESSION['id_customer'];
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$status = $data['status'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "ID tidak valid"]);
    exit;
}

$stmt = $koneksi->prepare("UPDATE ms_doctor_schedule SET sch_status = ? WHERE id_schedule = ? AND id_customer = ?");
$stmt->bind_param("iii", $status, $id, $id_customer);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}