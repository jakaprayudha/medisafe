<?php
include '../../database/connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id_visit'];
$keluhan = $data['keluhan'];
$catatan = $data['catatan'];
$kondisi_masuk  = $data['kondisi_masuk'];
$tekanan_darah = $data['tekanan_darah'];
$suhu = $data['suhu'];
$nadi = $data['nadi'];
$respirasi = $data['respirasi'];
$tinggi = $data['tinggi'];
$berat = $data['berat'];
$bmi = $data['bmi'];
$bmi_keterangan = $data['bmi_ket'];
$saturasi = $data['saturasi'];

$query = "UPDATE pasien_visit 
          SET anamnesa = ?, catatan_screening = ?, kondisi_masuk = ?, tekanan_darah = ?, suhu = ?, nadi = ?, respirasi = ?, tinggi_badan = ?, berat_badan = ?, bmi = ?, bmi_keterangan = ?, saturasi = ?
          WHERE id_visit = ?";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("ssssssssssssi", $keluhan, $catatan, $kondisi_masuk, $tekanan_darah, $suhu, $nadi, $respirasi, $tinggi, $berat, $bmi, $bmi_keterangan, $saturasi, $id);
$stmt->execute();

echo json_encode([
   'status' => 'success'
]);
