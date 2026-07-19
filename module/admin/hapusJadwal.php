<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer=$_SESSION['id_customer'];
$id=$_POST['id'] ?? '';
if($id==""){
    echo json_encode([
        "success"=>false,
        "message"=>"ID jadwal tidak ditemukan."
    ]);
    exit;
}

$sql=mysqli_query($koneksi,"DELETE FROM ms_doctor_schedule WHERE id_schedule='$id' AND id_customer='$id_customer'");

if($sql){
    echo json_encode([
        "success"=>true,
        "message"=>"Jadwal berhasil dihapus."
    ]);
}else{
    echo json_encode([
        "success"=>false,
        "message"=>mysqli_error($koneksi)
    ]);
}