<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer=$_SESSION['id_customer'];
$id=$_POST['id'] ?? '';
$status=$_POST['status'] ?? '';
if($id==""){
    echo json_encode([
        "success"=>false,
        "message"=>"ID jadwal tidak ditemukan"
    ]);
    exit;
}
if($status!="0" && $status!="1"){
    echo json_encode([
        "success"=>false,
        "message"=>"Status tidak valid"
    ]);
    exit;
}
$sql=mysqli_query($koneksi,"
UPDATE ms_doctor_schedule
SET sch_status='$status',
updated_at=NOW()
WHERE id_schedule='$id'
AND id_customer='$id_customer'
");
if($sql){
    echo json_encode([
        "success"=>true,
        "message"=>"Status jadwal berhasil diperbarui"
    ]);
}else{
    echo json_encode([
        "success"=>false,
        "message"=>mysqli_error($koneksi)
    ]);
}
?>