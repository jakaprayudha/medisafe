<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer=$_SESSION["id_customer"];
$id=$_POST["id"] ?? "";

$query=mysqli_query($koneksi,"SELECT id_doctor, doctor_name, doctor_code FROM ms_doctor WHERE id_doctor='$id' AND id_customer='$id_customer' LIMIT 1");
if(mysqli_num_rows($query)==0){
    echo json_encode([
        "success"=>false,
        "message"=>"Data tidak ditemukan."
    ]);
    exit;
}
$row=mysqli_fetch_assoc($query);
echo json_encode([
    "success"=>true,
    "data"=>[
        "id_doctor"=>$row["id_doctor"],
        "doctor_name"=>$row["doctor_name"],
        "doctor_bpjs"=>$row["doctor_code"]
    ]
]);