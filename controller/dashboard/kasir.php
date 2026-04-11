<?php
require '../../database/connect.php';
header('Content-Type: application/json');

session_start();
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode(['status' => 'error', 'message' => 'Session tidak ada']);
   exit;
}

// ================= DATA TABLE =================
$query = "
SELECT 
  visit_ID,
  patient_name_pcare,
  source_hub,
  amount_results,
  metode_bayar,
  status_bayar,
  created_at
FROM pasien_visit
WHERE id_customer = ?
ORDER BY created_at DESC
LIMIT 10
";

$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $id_customer);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
   $data[] = $row;
}

// ================= SUMMARY =================
$summary = mysqli_fetch_assoc(mysqli_query($koneksi, "
  SELECT 
    COUNT(*) as total_transaksi,
    SUM(amount_results) as total_pendapatan,
    SUM(CASE WHEN status_bayar = 0 THEN 1 ELSE 0 END) as belum_bayar,
    SUM(CASE WHEN metode_bayar != 'Tunai' THEN 1 ELSE 0 END) as non_tunai
  FROM pasien_visit
  WHERE id_customer = '$id_customer'
  AND DATE(created_at) = CURDATE()
"));

// ================= METODE BAYAR (%) =================
$metode = [];
$qMetode = mysqli_query($koneksi, "
  SELECT metode_bayar, COUNT(*) as total
  FROM pasien_visit
  WHERE id_customer = '$id_customer'
  AND DATE(created_at) = CURDATE()
  GROUP BY metode_bayar
");

$totalAll = 0;
$tmp = [];

while ($row = mysqli_fetch_assoc($qMetode)) {
   $tmp[$row['metode_bayar']] = $row['total'];
   $totalAll += $row['total'];
}

foreach ($tmp as $key => $val) {
   $metode[$key] = $totalAll > 0 ? round(($val / $totalAll) * 100) : 0;
}

// ================= LIST BELUM BAYAR =================
$belum_bayar_list = [];
$qBelum = mysqli_query($koneksi, "
  SELECT visit_ID, patient_name_pcare, amount_results
  FROM pasien_visit
  WHERE id_customer = '$id_customer'
  AND status_bayar = 0
  AND DATE(created_at) = CURDATE()
");

while ($row = mysqli_fetch_assoc($qBelum)) {
   $belum_bayar_list[] = $row;
}

// ================= LIST SUDAH BAYAR =================
$lunas_list = [];
$qLunas = mysqli_query($koneksi, "
  SELECT visit_ID, patient_name_pcare, amount_results, metode_bayar
  FROM pasien_visit
  WHERE id_customer = '$id_customer'
  AND status_bayar = 1
  AND DATE(created_at) = CURDATE()
");

while ($row = mysqli_fetch_assoc($qLunas)) {
   $lunas_list[] = $row;
}

// ================= RESPONSE =================
echo json_encode([
   'status' => 'success',
   'data' => $data,
   'summary' => $summary,
   'metode' => $metode,
   'belum_bayar_list' => $belum_bayar_list,
   'lunas_list' => $lunas_list
]);
