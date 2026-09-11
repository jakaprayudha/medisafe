<?php
// Set header agar output dikenali sebagai JSON oleh frontend
header('Content-Type: application/json');

include '../../database/connect.php';

// Ambil data JSON dari body request
$data = json_decode(file_get_contents("php://input"), true);

// Pastikan data id_visit tersedia
if (!isset($data['id_visit']) || empty($data['id_visit'])) {
   echo json_encode(['status' => 'error', 'message' => 'ID Visit tidak ditemukan.']);
   exit;
}

$id = $data['id_visit'];
$keluhan = $data['keluhan'];
$catatan = $data['catatan'];
$kondisi_masuk  = $data['kondisi_masuk'];
$tekanan_darah = $data['tekanan_darah'];

// Tangkap data baru
$sistole = isset($data['sistole']) ? $data['sistole'] : null;
$diastole = isset($data['diastole']) ? $data['diastole'] : null;
$lingkar_perut = isset($data['lingkar_perut']) ? $data['lingkar_perut'] : null;

$suhu = $data['suhu'];
$nadi = $data['nadi'];
$respirasi = $data['respirasi'];
$tinggi = $data['tinggi'];
$berat = $data['berat'];
$bmi = $data['bmi'];
$bmi_keterangan = $data['bmi_ket'];
$saturasi = $data['saturasi'];

// Query update ditambahkan kolom sistole, diastole, dan lingkar_perut
$query = "UPDATE pasien_visit 
          SET anamnesa = ?, 
              catatan_screening = ?, 
              kondisi_masuk = ?, 
              tekanan_darah = ?, 
              sistole = ?, 
              diastole = ?, 
              suhu = ?, 
              nadi = ?, 
              respirasi = ?, 
              tinggi_badan = ?, 
              berat_badan = ?, 
              bmi = ?, 
              bmi_keterangan = ?, 
              saturasi = ?,
              lingkar_perut = ?
          WHERE id_visit = ?";

$stmt = $koneksi->prepare($query);

if ($stmt) {
   // 15 parameter string ("s") dan 1 parameter integer ("i") untuk id_visit -> total 16 parameter
   $stmt->bind_param(
      "sssssssssssssssi",
      $keluhan,
      $catatan,
      $kondisi_masuk,
      $tekanan_darah,
      $sistole,
      $diastole,
      $suhu,
      $nadi,
      $respirasi,
      $tinggi,
      $berat,
      $bmi,
      $bmi_keterangan,
      $saturasi,
      $lingkar_perut,
      $id
   );

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success'
      ]);
   } else {
      // Jika query gagal dieksekusi (misal tipe data salah)
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyimpan ke database: ' . $stmt->error
      ]);
   }

   $stmt->close();
} else {
   // Jika nama kolom di query ada yang tidak cocok dengan di tabel database
   echo json_encode([
      'status' => 'error',
      'message' => 'Kesalahan pada query SQL: ' . $koneksi->error
   ]);
}
