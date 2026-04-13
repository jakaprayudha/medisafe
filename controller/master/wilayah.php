<?php
require '../../database/connect.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$id   = $_GET['id'] ?? '';

$data = [];

if ($type == 'provinsi') {

   $q = $koneksi->query("SELECT id, provinsi as nama FROM wil_provinsi");
} elseif ($type == 'kabupaten') {

   $q = $koneksi->query("SELECT id, kab as nama FROM wil_kabupaten WHERE kodeprovinsi = '$id'");
} elseif ($type == 'kecamatan') {

   $q = $koneksi->query("SELECT id, kec as nama FROM wil_kecamatan WHERE kab_id = '$id'");
} elseif ($type == 'kelurahan') {

   $q = $koneksi->query("SELECT id, kel as nama FROM wil_kelurahan WHERE kec_id = '$id'");
} else {
   echo json_encode([]);
   exit;
}

while ($row = $q->fetch_assoc()) {
   $data[] = $row;
}

echo json_encode($data);
