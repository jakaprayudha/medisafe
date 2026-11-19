<?php
include "../../database/connect.php";
header("Content-Type: application/json");

$id_customer = (int) ($_SESSION['id_customer'] ?? 0);
$stmt = $koneksi->prepare("SELECT kdDokter, nmDokter FROM master_doctor_bpjs WHERE id_customer=? AND status='1' ORDER BY nmDokter ASC");
$stmt->bind_param("i", $id_customer);
$stmt->execute();
$data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    "success" => true,
    "data" => $data
]);
