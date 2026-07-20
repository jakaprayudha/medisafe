<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer = $_SESSION['id_customer'];
$id_doctor = trim($_POST['id_doctor'] ?? '');
if ($id_doctor == "") {
    echo json_encode([
        "success" => false,
        "message" => "ID dokter tidak ditemukan."
    ]);
    exit;
}
mysqli_begin_transaction($koneksi);
try {
    $cek = mysqli_query($koneksi, "SELECT doctor_number FROM ms_doctor WHERE id_doctor='$id_doctor' AND id_customer='$id_customer'");
    if (mysqli_num_rows($cek) == 0) {
        throw new Exception("Data dokter tidak ditemukan.");
    }
    $dokter = mysqli_fetch_assoc($cek);
    $hapus = mysqli_query($koneksi, "DELETE FROM ms_doctor WHERE id_doctor='$id_doctor' AND id_customer='$id_customer'");
    if (!$hapus) {
        throw new Exception(mysqli_error($koneksi));
    }
    mysqli_commit($koneksi);
    echo json_encode([
        "success" => true,
        "message" => "Dokter internal berhasil dihapus."
    ]);
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}