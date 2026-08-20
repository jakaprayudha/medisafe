<?php
require '../../database/connect.php';
session_start();
header("Content-Type: application/json");
$id_customer = $_SESSION['id_customer'] ?? null;
$no = $_POST['visit_id'] ?? null;

$stmt = $koneksi->prepare("SELECT p.id_patient, pv.desAlObat FROM pasien_visit AS pv INNER JOIN ms_patient AS p ON p.id_patient = pv.id_patient AND p.id_customer = pv.id_customer WHERE pv.visit_ID = ? AND pv.id_customer = ?");

$stmt->bind_param("ss", $no, $id_customer);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

echo json_encode([
   "status" => "success",
   "data" => $data
]);
