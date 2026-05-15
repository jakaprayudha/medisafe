<?php
include '../../database/connect.php';
while (ob_get_level()) ob_end_clean();

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

header('Content-Type: application/json');

error_reporting(0);
ini_set('display_errors', 0);


$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
   createVisit();
} else {
   echo json_encode([
      'status' => 'error',
      'message' => 'Method tidak diizinkan'
   ]);
   exit;
}

// ================= CREATE VISIT =================
function createVisit(){
   global $koneksi;
   try {
      $id_customer = $_SESSION['id_customer'] ?? null;
      $created_user = $_SESSION['fullname'] ?? null;

      if (!$id_customer) {
         echo json_encode([
            'status' => 'error',
            'message' => 'Session habis'
         ]);
         exit;
      }

      $patient_name_pcare = $_POST['patient_name_pcare'] ?? '';
      $doctor_name = $_POST['doctor_name'] ?? '';
      $poli_name = $_POST['poli_name'] ?? '';
      $id_poli = $_POST['id_poli'] ?? '';

      $id_patient = $_POST['id_patient'] ?? '';
      $visit_date = $_POST['visit_date'] ?? '';
      $visit_time = $_POST['visit_time'] ?? '';
      $id_doctor  = $_POST['id_doctor'] ?? '';
      $id_provider = $_POST['id_provider'] ?? '';
      $source_hub = !empty($_POST['source_hub']) ? $_POST['source_hub'] : 'Poliklinik';

      if (!$id_patient || !$visit_date || !$visit_time || !$id_doctor) {
         echo json_encode([
            'status' => 'error',
            'message' => 'Data tidak lengkap'
         ]);
         exit;
      }

      // 🔥 insert
      $stmt = $koneksi->prepare("
            INSERT INTO pasien_visit (
                id_patient,
                visit_ID,
                visit_date,
                visit_time,
                id_doctor,
                id_poli,
                source_hub,
                visit_status,
                created_user,
                visit_antrian,
                status_antrian,
                id_customer,
                patient_name_pcare,
                id_provider
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)
        ");


      $visit_status = 0;
      $status_antrian = 0;

      $stmt->bind_param(
         "sssssssiisiisi",
         $id_patient,
         $visit_ID,
         $visit_date,
         $visit_time,
         $doctor_name,
         $poli_name,
         $source_hub,
         $visit_status,
         $created_user,
         $visit_antrian,
         $status_antrian,
         $id_customer,
         $patient_name_pcare,
         $id_provider
      );

      if (!$stmt->execute()) {
         throw new Exception($stmt->error);
      }

      echo json_encode([
         'status' => 'success',
         'message' => 'Berhasil daftar',
         'data' => [
            'visit_ID' => $visit_ID,
            'antrian' => $visit_antrian
         ]
      ]);
      exit;
   } catch (Exception $e) {

      echo json_encode([
         'status' => 'error',
         'message' => $e->getMessage()
      ]);
      exit;
   }
}

// ================= GENERATE VISIT ID =================
function generateVisitID($koneksi)
{
   do {
      $date = date('ymd');
      $random = strtoupper(bin2hex(random_bytes(3)));
      $visitID = "VIS-" . $date . "-" . $random;
      $count = '';
      $check = $koneksi->prepare("SELECT COUNT(*) FROM pasien_visit WHERE visit_ID=?");
      $check->bind_param("s", $visitID);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $visitID;
}
