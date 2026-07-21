<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer = $_SESSION['id_customer'];
$id_visit   = trim($_POST['visit_id'] ?? '');
$id_doctor  = trim($_POST['doctor_new'] ?? '');
if ($id_visit == "") {
    echo json_encode([
        "success" => false,
        "message" => "ID kunjungan tidak valid."
    ]);
    exit;
}
if ($id_doctor == "") {
    echo json_encode([
        "success" => false,
        "message" => "Silakan pilih dokter tujuan."
    ]);
    exit;
}
$stmt = $koneksi->prepare("SELECT nmDokter, kdDokter FROM master_doctor_bpjs WHERE kdDokter = ? AND id_customer = ? LIMIT 1");
$stmt->bind_param("ss", $id_doctor, $id_customer);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode([
        "success" => false,
        "message" => "Dokter tidak ditemukan."
    ]);
    exit;
}
$dokter = $result->fetch_assoc();
$stmt1 = $koneksi->prepare("UPDATE pasien_visit SET id_doctor = ?, code_doctor = ? WHERE id_visit = ? AND id_customer = ?");
$stmt1->bind_param(
    "ssss",
    $dokter['nmDokter'],
    $dokter['kdDokter'],
    $id_visit,
    $id_customer
);
if ($stmt1->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Dokter berhasil diganti."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => $stmt->error
    ]);

}