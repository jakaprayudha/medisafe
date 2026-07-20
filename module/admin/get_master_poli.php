<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer=$_SESSION["id_customer"];
$query=mysqli_query($koneksi,"SELECT id, kdPoli, nmPoli FROM master_poli ORDER BY nmPoli");
$data=[];
while($row=mysqli_fetch_assoc($query)){
    $data[]=$row;
}
echo json_encode([
    "success"=>true,
    "data"=>$data
]);