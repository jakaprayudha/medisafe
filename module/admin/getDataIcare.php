<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer=$_SESSION["id_customer"];
$query=mysqli_query($koneksi,"SELECT id, kdDokter, nmDokter, status, icare_username, icare_password FROM master_doctor_bpjs WHERE id_customer = '$id_customer' ORDER BY nmDokter ASC");
$data=[];
while($row=mysqli_fetch_assoc($query)){
    $data[]=$row;
}
echo json_encode([
    "success"=>true,
    "data"=>$data
]);