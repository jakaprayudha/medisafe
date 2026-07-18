<?php
require_once __DIR__ . '/view.php';
header('Content-Type: application/json');
$kdpoli = $_GET['kdpoli'];
$tanggal = $_GET['tanggal'];
$namaHari = [
    "Sunday"    => "Minggu",
    "Monday"    => "Senin",
    "Tuesday"   => "Selasa",
    "Wednesday" => "Rabu",
    "Thursday"  => "Kamis",
    "Friday"    => "Jumat",
    "Saturday"  => "Sabtu"
];

$hari = $namaHari[(new DateTime($tanggal))->format('l')];
$sql = mysqli_query($koneksi, "SELECT d.doctor_name, d.doctor_code, j.day_of_week, j.start_time, j.end_time, db.nmDokter FROM ms_doctor AS d INNER JOIN ms_doctor_schedule AS j ON j.id_doctor = d.doctor_code INNER JOIN master_doctor_bpjs AS db ON db.kdDokter = d.doctor_code WHERE d.id_customer = '$idcustomer' AND j.id_customer = '$idcustomer' AND db.id_customer = '$idcustomer' AND j.day_of_week = '$hari' AND j.id_poli = '$kdpoli'");
$data = [];

while ($row = mysqli_fetch_assoc($sql)){
    $data[] = [
        "namadokter" => $row['doctor_name'],
        "kodedokter" => (int)$row['doctor_code'],
        "jampraktek" => $row['start_time'] . "-" . $row['end_time'],
        "nmDokterBpjs" => $row['nmDokter']
    ];
}

$response = [
    "success" => true,
    "code" => "200",
    "message" => "OK",
    "data" => $data
];

echo json_encode($response, JSON_PRETTY_PRINT);