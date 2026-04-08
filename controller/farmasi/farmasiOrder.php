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
   $query = "SELECT permintaan_pharmacy.*,pasien_visit.visit_ID,pasien_visit.patient_name_pcare, pasien_visit.id_doctor, ms_patient.nomor_rm, ms_patient.patient_gender, pasien_visit.id_poli, ms_patient.patient_place, ms_patient.patient_datebirth, pasien_visit.source_hub FROM permintaan_pharmacy LEFT JOIN pasien_visit ON pasien_visit.visit_ID = permintaan_pharmacy.id_visit LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient   GROUP BY permintaan_pharmacy.id_visit
             ORDER BY id_permintaan_farmasi ASC ";
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
