<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer=$_SESSION["id_customer"];
$query=mysqli_query($koneksi,"SELECT id, kdPoli, nmPoli, poliSakit FROM master_poli");
$data=[];
while($row=mysqli_fetch_assoc($query)){
    $data[]=$row;
}
echo json_encode([
    "success"=>true,
    "data"=>$data
]);