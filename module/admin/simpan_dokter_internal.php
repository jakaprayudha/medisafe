<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer = $_SESSION['id_customer'];
$doctor_name = trim($_POST['doctor_name'] ?? '');
$doctor_code = trim($_POST['doctor_code'] ?? '');
if($doctor_name==""){
    echo json_encode([
        "success"=>false,
        "message"=>"Nama dokter kosong."
    ]);
    exit;
}
if($doctor_code==""){
    echo json_encode([
        "success"=>false,
        "message"=>"Dokter BPJS belum dipilih."
    ]);
    exit;
}
$doctor_number = generateDoctorNumber($koneksi);
$sql=mysqli_query($koneksi,"
    INSERT INTO ms_doctor
    (
        doctor_code,
        doctor_name,
        id_customer,
        doctor_number
    )
    VALUES
    (
        '$doctor_code',
        '$doctor_name',
        '$id_customer',
        '$doctor_number'
    )
");
if($sql){
    echo json_encode([
        "success"=>true,
        "message"=>"Dokter internal berhasil ditambahkan."
    ]);
}else{
    echo json_encode([
        "success"=>false,
        "message"=>mysqli_error($koneksi)
    ]);
}
function generateDoctorNumber($koneksi){
    do {
        $number = "DCT-" . date('YmdHis') . "-" . substr(str_replace('.', '', microtime(true)), -6);
        $stmt = $koneksi->prepare("
            SELECT id_doctor
            FROM ms_doctor
            WHERE doctor_number = ?
        ");
        $stmt->bind_param("s", $number);
        $stmt->execute();
        $result = $stmt->get_result();
        $ada = $result->num_rows > 0;
        $stmt->close();
    } while ($ada);
    return $number;
}