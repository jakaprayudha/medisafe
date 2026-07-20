<?php
include "../../database/connect.php";
header("Content-Type: application/json");
session_start();
$id_customer = $_SESSION['id_customer'];
$data=[];
$query=mysqli_query($koneksi,"SELECT a.id_doctor,a.doctor_name,a.doctor_code,b.nmDokter, c.nmPoli FROM ms_doctor a LEFT JOIN master_doctor_bpjs b ON a.doctor_code=b.kdDokter AND b.id_customer='$id_customer' LEFT JOIN master_poli c ON a.id_poli=c.kdPoli WHERE a.id_customer='$id_customer' ORDER BY a.doctor_name ASC");
while($row=mysqli_fetch_assoc($query)){
    $data[]=$row;
}
echo json_encode([
    "success"=>true,
    "data"=>$data
]);