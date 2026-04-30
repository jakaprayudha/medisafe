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
$poli     = $_GET['poli'] ?? null;

/**
 * 🔥 BASE SQL (TANPA GROUP BY)
 */
$sql = "SELECT 
   pv.id_doctor,

   COUNT(DISTINCT pv.visit_ID) AS total_kunjungan,

   SUM(COALESCE(b.total_billing,0)) AS total_biaya,

   SUM(COALESCE(b.fee_dokter,0)) AS fee_dokter,
   SUM(COALESCE(b.fee_perawat,0)) AS fee_perawat,
   SUM(COALESCE(b.fee_admin,0)) AS fee_admin

FROM pasien_visit pv

LEFT JOIN (
   SELECT 
      pb.id_visit,
      SUM(pb.billing_price) AS total_billing,
      SUM(COALESCE(mt.tarif_share_doctor,0)) AS fee_dokter,
      SUM(COALESCE(mt.tarif_share_nurse,0)) AS fee_perawat,
      SUM(COALESCE(mt.tarif_share_admin,0)) AS fee_admin
   FROM pasien_billing pb
   LEFT JOIN ms_tarif mt 
      ON TRIM(LOWER(mt.tarif_name)) = TRIM(LOWER(pb.billing_item))
   GROUP BY pb.id_visit
) b ON b.id_visit = pv.visit_ID

WHERE pv.id_customer = ?
";

/**
 * 🔥 PARAM
 */
$params = [$id_customer];
$types  = "i";

/**
 * 🔥 FILTER (SEBELUM GROUP BY)
 */
if ($fromDate && $toDate) {
   $sql .= " AND DATE(pv.visit_date) BETWEEN ? AND ? ";
   $params[] = $fromDate;
   $params[] = $toDate;
   $types   .= "ss";
}

if (!empty($provider)) {
   $sql .= " AND pv.id_provider = ? ";
   $params[] = $provider;
   $types   .= "i";
}

if (!empty($doctor)) {
   $sql .= " AND pv.id_doctor = ? ";
   $params[] = $doctor;
   $types   .= "s";
}

if (!empty($poli)) {
   $sql .= " AND pv.id_poli = ? ";
   $params[] = $poli;
   $types   .= "s";
}

/**
 * 🔥 GROUP BY + ORDER (HANYA SEKALI)
 */
$sql .= "
GROUP BY pv.id_doctor
ORDER BY MAX(pv.visit_date) DESC
";

/**
 * 🔥 EXECUTE
 */
$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
   $data[] = $row;
}

$stmt->close();

echo json_encode([
   "status" => "success",
   "data"   => $data
]);
