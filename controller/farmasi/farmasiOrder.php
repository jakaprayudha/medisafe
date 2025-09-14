<?php
include '../../database/connect.php';
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {

   case 'GET':
      getData();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

function getData()
{
   global $koneksi;
   $query = "SELECT * FROM permintaan_pharmacy INNER JOIN pasien_visit ON pasien_visit.visit_ID = permintaan_pharmacy.id_visit INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient INNER JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor LEFT JOIN ms_poli ON ms_poli.id_poli = pasien_visit.id_poli  WHERE status_permintaan = 0
             ORDER BY id_permintaan_farmasi ASC";
   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);
      return;
   }

   // Ambil semua data dalam bentuk array asosiatif
   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

   // Tutup hasil query
   mysqli_free_result($result);

   // Kirimkan data dalam format JSON
   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}
