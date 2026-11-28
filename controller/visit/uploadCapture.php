<?php
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
require_once __DIR__ . '/../../utility/activityLog.php';

$data = json_decode(file_get_contents("php://input"), true);

$image = $data['image'];
$id_visit = $data['id_visit'];
// hanya kunjungan faskes sesi (dulu foto kunjungan faskes mana pun bisa ditimpa)
$kunjCap = visitGuard($koneksi, 'id_visit', $id_visit);

// remove header base64
$image = str_replace('data:image/png;base64,', '', $image);
$image = str_replace(' ', '+', $image);
$imageData = base64_decode($image);

// nama file
$filename = 'capture_' . time() . '.png';
$path = '../../uploads/foto/' . $filename;

// simpan file
file_put_contents($path, $imageData);

// simpan ke database (optional)
$query = "UPDATE pasien_visit SET foto_capture=? WHERE id_visit=? AND id_customer=?";
$stmt = $koneksi->prepare($query);
$idcCap = (int) $kunjCap['id_customer'];
$stmt->bind_param("ssi", $filename, $id_visit, $idcCap);
$stmt->execute();
logAktivitas($koneksi, 'dokumen', 'upload_file', ['tipe' => 'foto_capture', 'id' => $id_visit, 'label' => 'Foto capture pasien', 'visit_ID' => $kunjCap['visit_ID']],
   'Foto capture pasien disimpan', null, ['foto_capture' => $filename]);

echo json_encode([
   'status' => 'success',
   'file' => $filename
]);
