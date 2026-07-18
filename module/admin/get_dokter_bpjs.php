<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer = $_SESSION['id_customer'];
$data=[];
$query=mysqli_query($koneksi,"
    SELECT
        kdDokter,
        nmDokter
    FROM master_doctor_bpjs
    WHERE id_customer='$id_customer'
    AND status='1'
    ORDER BY nmDokter ASC
");
while($row=mysqli_fetch_assoc($query)){
    $data[]=$row;
}
echo json_encode([
    "success"=>true,
    "data"=>$data

]);