<?php
include "../../database/connect.php";
header("Content-Type: application/json");

$id_customer =$_SESSION['id_customer'];
$doctor_code=trim($_POST['doctor_code'] ?? '');

if($doctor_code==""){
    echo json_encode([
        "success"=>false,
        "message"=>"Kode dokter tidak ditemukan.",
        "data"=>[]
    ]);
    exit;
}

$query=mysqli_query($koneksi,"
SELECT
    s.id_schedule,
    s.day_of_week,
    s.kuota,
    p.nmPoli,
    s.start_time,
    s.end_time,
    s.sch_status
FROM ms_doctor_schedule s
LEFT JOIN master_poli p ON s.id_poli = p.kdPoli
WHERE s.id_doctor='$doctor_code'
AND s.id_customer='$id_customer'
ORDER BY FIELD(s.day_of_week,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'),s.start_time
");

$data=[];

while($row=mysqli_fetch_assoc($query)){
    $data[]=[
        "id"=>$row["id_schedule"],
        "nmHari"=>$row["day_of_week"],
        "nmPoli"=>$row["nmPoli"] ?? "-",
        "kuota"=>$row["kuota"],
        "jamMulai"=>$row["start_time"],
        "jamSelesai"=>$row["end_time"],
        "status"=>$row["sch_status"]
    ];
}

echo json_encode([
    "success"=>true,
    "data"=>$data
]);