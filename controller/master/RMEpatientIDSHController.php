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

      COALESCE(d.total_patient, 0) AS total_patient,
      COALESCE(d.total_obat, 0) AS total_obat,
      COALESCE(d.total_lab, 0) AS total_lab,
      COALESCE(d.total_diagnosa, 0) AS total_diagnosa

   FROM setting_clinic sc

   -- 🔥 AGREGASI PER VISIT (ANTI DUPLICATE)
   LEFT JOIN (
      SELECT 
         pv.id_customer,

         COUNT(DISTINCT pv.visit_ID) AS total_patient,

         COUNT(DISTINCT CASE 
            WHEN pp.id_visit IS NOT NULL 
            THEN pv.visit_ID 
         END) AS total_obat,

         COUNT(DISTINCT CASE 
            WHEN vi.id_visit IS NOT NULL 
            THEN pv.visit_ID 
         END) AS total_lab,

         COUNT(DISTINCT CASE 
            WHEN pv.diagnosa IS NOT NULL AND pv.diagnosa != '' 
            THEN pv.visit_ID 
         END) AS total_diagnosa

      FROM pasien_visit pv

      LEFT JOIN permintaan_pharmacy pp 
         ON pp.id_visit = pv.visit_ID

      LEFT JOIN visit_inspection vi 
         ON vi.id_visit = pv.visit_ID

      GROUP BY pv.id_customer
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
