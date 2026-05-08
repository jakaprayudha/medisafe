<?php
include '../../database/connect.php';

header('Content-Type: application/json');

$id_customer = $_GET['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'id_customer tidak ada'
   ]);
   exit;
}

$query = "
SELECT 
   pv.visit_ID,
   mp.patient_name,
   mp.patient_nik,
   mp.idsh,
   pv.diagnosa,

   -- 🔥 FLAG OBAT
   CASE 
      WHEN pp.id_visit IS NOT NULL THEN 1 
      ELSE 0 
   END AS ada_obat,

   -- 🔥 FLAG LAB
   CASE 
      WHEN vi.id_visit IS NOT NULL THEN 1 
      ELSE 0 
   END AS ada_lab

FROM pasien_visit pv

JOIN ms_patient mp 
   ON mp.id_patient = pv.id_patient

LEFT JOIN (
   SELECT DISTINCT id_visit 
   FROM permintaan_pharmacy
) pp ON pp.id_visit = pv.visit_ID

LEFT JOIN (
   SELECT DISTINCT id_visit 
   FROM visit_inspection
) vi ON vi.id_visit = pv.visit_ID

WHERE pv.id_customer = ?

ORDER BY pv.visit_ID DESC
";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_customer);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode([
   'status' => 'success',
   'data' => $data
]);
