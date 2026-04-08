<?php
require '../../../database/connect.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
$id_customer = $_SESSION['id_customer'];
$query = "SELECT * FROM pasien_visit pv LEFT JOIN ms_patient mp ON pv.id_patient = mp.id_patient 
LEFT JOIN permintaan_ranap pr ON pv.visit_ID = pr.visit_ID_inpatient
LEFT JOIN ms_room r ON pr.id_room = r.id_room
LEFT JOIN ms_room_bed b ON pr.id_bed = b.id_bed
 WHERE pv.visit_ID = '$no' AND pv.id_customer = '$id_customer'";
$result = $koneksi->query($query);
$data = $result->fetch_assoc();
echo json_encode($data);
