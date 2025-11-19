<?php
require '../../../database/connect.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
$query = "SELECT * FROM pasien_visit pv INNER JOIN ms_patient mp ON pv.id_patient = mp.id_patient INNER JOIN ms_doctor dc ON pv.id_doctor = dc.id_doctor WHERE pv.visit_ID = '$no' AND mp.nomor_rm = '$rm'";
$result = $koneksi->query($query);
$data = $result->fetch_assoc();
echo json_encode($data);
