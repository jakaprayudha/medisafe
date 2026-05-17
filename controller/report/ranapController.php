<?php
include '../../database/connect.php';

header("Content-Type: application/json");

session_start();

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      "status" => "error",
      "message" => "Session tidak ditemukan"
   ]);
   exit;
}

$fromDate = $_GET['fromDate'] ?? null;
$toDate   = $_GET['toDate'] ?? null;
$doctor   = $_GET['doctor'] ?? null;
$provider = $_GET['provider'] ?? null;
$status   = $_GET['status'] ?? null;

// =============================
// BASE QUERY
// =============================
$sql = "SELECT 
   pv.*,
   mp.provider_name,
   ms.patient_nik as nik,

   -- 🔥 STATUS CPPT
   CASE
      WHEN EXISTS (
         SELECT 1
         FROM visit_cppt vc
         WHERE vc.visit_ID = pv.visit_ID
      )
      THEN 1
      ELSE 0
   END as status_cppt,

   -- 🔥 STATUS PULANG
   CASE
      WHEN EXISTS (
         SELECT 1
         FROM resume_medis rm
         WHERE rm.visit_ID = pv.visit_ID
         AND rm.tanggal_pulang IS NOT NULL
         AND rm.tanggal_pulang != ''
      )
      THEN 1
      ELSE 0
   END as status_pulang

FROM pasien_visit pv

LEFT JOIN ms_provider mp 
   ON mp.id_provider = pv.id_provider
LEFT JOIN ms_patient ms 
   ON ms.id_patient = pv.id_patient
WHERE pv.id_customer = '$id_customer'
AND pv.status_rawatinap = 1
";

// =============================
// FILTER TANGGAL
// =============================
if ($fromDate && $toDate) {

   $sql .= "
      AND DATE(pv.visit_date)
      BETWEEN '$fromDate' AND '$toDate'
   ";
}

// =============================
// FILTER PROVIDER
// =============================
if (!empty($provider)) {

   $sql .= "
      AND pv.id_provider = '$provider'
   ";
}

// =============================
// FILTER DOKTER
// =============================
if (!empty($doctor)) {

   $sql .= "
      AND pv.id_doctor = '$doctor'
   ";
}

// =============================
// FILTER STATUS
// =============================
if ($status == 'Belum') {

   // 🔴 BELUM DILAYANI
   // belum ada CPPT
   $sql .= "
      AND NOT EXISTS (
         SELECT 1
         FROM visit_cppt vc
         WHERE vc.visit_ID = pv.visit_ID
      )

      AND NOT EXISTS (
         SELECT 1
         FROM resume_medis rm
         WHERE rm.visit_ID = pv.visit_ID
         AND rm.tanggal_pulang IS NOT NULL
         AND rm.tanggal_pulang != ''
      )
   ";
} elseif ($status == 'Pemeriksaan') {

   // 🟡 SUDAH PEMERIKSAAN
   // sudah ada CPPT tapi belum pulang
   $sql .= "
      AND EXISTS (
         SELECT 1
         FROM visit_cppt vc
         WHERE vc.visit_ID = pv.visit_ID
      )

      AND NOT EXISTS (
         SELECT 1
         FROM resume_medis rm
         WHERE rm.visit_ID = pv.visit_ID
         AND rm.tanggal_pulang IS NOT NULL
         AND rm.tanggal_pulang != ''
      )
   ";
} elseif ($status == 'Selesai') {

   // 🟢 SUDAH PULANG
   $sql .= "
      AND EXISTS (
         SELECT 1
         FROM resume_medis rm
         WHERE rm.visit_ID = pv.visit_ID
         AND rm.tanggal_pulang IS NOT NULL
         AND rm.tanggal_pulang != ''
      )
   ";
}

// =============================
// ORDERING
// =============================
$sql .= "
ORDER BY 
   pv.visit_date DESC,
   pv.visit_time DESC
";

// =============================
// QUERY
// =============================
$query = mysqli_query($koneksi, $sql);

if (!$query) {

   echo json_encode([
      "status" => "error",
      "message" => mysqli_error($koneksi)
   ]);

   exit;
}

$data = [];

while ($row = mysqli_fetch_assoc($query)) {

   // =============================
   // GENERATE STATUS LABEL
   // =============================
   if ($row['status_pulang'] == 1) {

      $row['status_label'] = 'Selesai';
   } elseif ($row['status_cppt'] == 1) {

      $row['status_label'] = 'Pemeriksaan';
   } else {

      $row['status_label'] = 'Belum';
   }

   $data[] = $row;
}

// =============================
// RESPONSE
// =============================
echo json_encode([
   "status" => "success",
   "data" => $data
]);
