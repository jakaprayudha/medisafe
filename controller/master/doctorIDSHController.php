<?php
include '../../database/connect.php';

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

   $query = "
   SELECT 
      sc.id,
      sc.id_customer,
      sc.clinic_name,

      COALESCE(d.total_doctor, 0) AS total_doctor,
      COALESCE(d.total_nik, 0) AS total_nik,
      COALESCE(d.total_idsh, 0) AS total_idsh,
      COALESCE(d.total_kurang, 0) AS total_kurang

   FROM setting_clinic sc

   -- 🔥 AGREGASI DOKTER (AMAN, NO DUPLICATE)
   LEFT JOIN (
      SELECT 
         id_customer,
         COUNT(*) AS total_doctor,

         SUM(
            CASE 
               WHEN doctor_nik IS NOT NULL AND doctor_nik != '' 
               THEN 1 ELSE 0 
            END
         ) AS total_nik,

         SUM(
            CASE 
               WHEN idsh IS NOT NULL AND idsh != '' 
               THEN 1 ELSE 0 
            END
         ) AS total_idsh,

         SUM(
            CASE 
               WHEN (doctor_nik IS NULL OR doctor_nik = '')
                 OR (idsh IS NULL OR idsh = '')
               THEN 1 ELSE 0 
            END
         ) AS total_kurang

      FROM ms_doctor
      WHERE doctor_status = 1
      GROUP BY id_customer
   ) d ON d.id_customer = sc.id_customer

   WHERE sc.status != 99
   ORDER BY sc.id DESC
   ";

   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => mysqli_error($koneksi)
      ]);
      return;
   }

   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

   mysqli_free_result($result);

   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);
}
