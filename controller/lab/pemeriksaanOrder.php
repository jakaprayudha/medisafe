<?php
include '../../database/connect.php';
session_start();

header('Content-Type: application/json');

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

   // 🔥 ambil session
   $id_customer = $_SESSION['id_customer'] ?? null;

   if (!$id_customer) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Session tidak ditemukan'
      ]);
      exit;
   }

   // 🔥 PREPARE QUERY (WAJIB karena pakai ?)
   $stmt = $koneksi->prepare("SELECT 
         visit_inspection.*,
         pasien_visit.visit_ID,
         pasien_visit.patient_name_pcare,
         pasien_visit.id_doctor,
         ms_patient.nomor_rm,
         ms_patient.patient_gender,
         pasien_visit.id_poli,
         ms_patient.patient_place,
         ms_patient.patient_datebirth,
         pasien_visit.source_hub
      FROM visit_inspection 
      LEFT JOIN pasien_visit 
         ON pasien_visit.visit_ID = visit_inspection.id_visit 
      LEFT JOIN ms_patient 
         ON ms_patient.id_patient = pasien_visit.id_patient  
      WHERE pasien_visit.id_customer = ? 
      GROUP BY visit_inspection.id_visit
      ORDER BY visit_inspection.id_inspection ASC
   ");

   if (!$stmt) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare failed: ' . $koneksi->error
      ]);
      return;
   }

   // 🔥 bind param
   $stmt->bind_param("i", $id_customer);

   $stmt->execute();
   $result = $stmt->get_result();

   $data = $result->fetch_all(MYSQLI_ASSOC);

   $stmt->close();

   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}
