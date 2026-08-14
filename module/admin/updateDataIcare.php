<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer = $_SESSION["id_customer"] ?? null;
if (!$id_customer) {
    echo json_encode([
        "success" => false,
        "message" => "Session customer tidak ditemukan."
    ]);
    exit;
}
$id       = $_POST['id'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
if (empty($id)) {
    echo json_encode([
        "success" => false,
        "message" => "ID dokter tidak ditemukan."
    ]);
    exit;
}
if ($username === '' || $password === '') {
    echo json_encode([
        "success" => false,
        "message" => "Username dan password wajib diisi."
    ]);
    exit;
}
$stmt = $koneksi->prepare("
    UPDATE master_doctor_bpjs
    SET 
        icare_username = ?,
        icare_password = ?
    WHERE id = ?
      AND id_customer = ?
");
$stmt->bind_param(
    "ssii",
    $username,
    $password,
    $id,
    $id_customer
);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Data iCare berhasil diperbarui."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Data tidak berubah atau dokter tidak ditemukan."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Gagal memperbarui data iCare.",
        "error" => $stmt->error
    ]);
}
$stmt->close();