<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer=$_SESSION['id_customer'];
$id=$_POST['id'] ?? '';
$kuota=$_POST['kuota'] ?? '';
if($id==""){
    echo json_encode(["success"=>false,"message"=>"ID jadwal tidak ditemukan"]);
    exit;
}
if(!is_numeric($kuota) || $kuota<0){
    echo json_encode(["success"=>false,"message"=>"Kuota tidak valid"]);
    exit;
}
$sql=mysqli_query($koneksi,"
UPDATE ms_doctor_schedule
SET kuota='$kuota',
updated_at=NOW()
WHERE id_schedule='$id'
AND id_customer='$id_customer'
");

if($sql){
    echo json_encode(["success"=>true,"message"=>"Kuota berhasil diperbarui"]);
}else{
    echo json_encode(["success"=>false,"message"=>mysqli_error($koneksi)]);
}
?>