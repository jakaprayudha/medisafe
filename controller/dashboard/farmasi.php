<?php
include '../../database/connect.php';

header('Content-Type: application/json');

session_start();
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'success' => false,
      'message' => 'Session tidak ada'
   ]);
   exit;
}


// ================== METRIC ==================

// racikan
$q_racikan = mysqli_query($koneksi, "
  SELECT COUNT(*) as total 
  FROM permintaan_pharmacy 
  WHERE id_customer='$id_customer' 
  AND tipe_obat='racikan'
   AND DATE(created_at) = CURDATE()
  AND status_permintaan > 0
");
$racikan = mysqli_fetch_assoc($q_racikan)['total'] ?? 0;

// non racikan
$q_non = mysqli_query($koneksi, "
  SELECT COUNT(*) as total 
  FROM permintaan_pharmacy 
  WHERE id_customer='$id_customer' 
  AND tipe_obat='non_racikan'
   AND DATE(created_at) = CURDATE()
  AND status_permintaan > 0
");
$non = mysqli_fetch_assoc($q_non)['total'] ?? 0;

// menunggu
$q_menunggu = mysqli_query($koneksi, "
  SELECT COUNT(*) as total 
  FROM permintaan_pharmacy 
  WHERE id_customer='$id_customer' 
   AND DATE(created_at) = CURDATE()
  AND status_permintaan = 1
");
$menunggu = mysqli_fetch_assoc($q_menunggu)['total'] ?? 0;

// selesai
$q_selesai = mysqli_query($koneksi, "
  SELECT COUNT(*) as total 
  FROM permintaan_pharmacy 
  WHERE id_customer='$id_customer' 
   AND DATE(created_at) = CURDATE()
  AND status_permintaan = 3
");
$selesai = mysqli_fetch_assoc($q_selesai)['total'] ?? 0;


// ================== LIST ANTRIAN ==================
$list = [];

$q_list = mysqli_query($koneksi, "
  SELECT 
    p.id_permintaan_farmasi,
    p.tipe_obat,
    p.status_permintaan,
    p.id_visit,
    m.patient_name_pcare
  FROM permintaan_pharmacy p
  INNER JOIN pasien_visit m ON p.id_visit = m.visit_ID
  WHERE p.id_customer='$id_customer'
   AND DATE(p.created_at) = CURDATE()
  AND p.status_permintaan > 0
  ORDER BY p.created_at DESC
");

while ($row = mysqli_fetch_assoc($q_list)) {

   // ===== TIPE OBAT =====
   if ($row['tipe_obat'] == 'racikan') {
      $jenis = '<span class="badge bg-primary">Racikan</span>';
   } else {
      $jenis = '<span class="badge bg-secondary">Non Racikan</span>';
   }

   // ===== STATUS =====
   if ($row['status_permintaan'] == 1) {
      $status = '<span class="badge bg-warning">Menunggu</span>';
   } elseif ($row['status_permintaan'] == 2) {
      $status = '<span class="badge bg-info">Diproses</span>';
   } elseif ($row['status_permintaan'] == 3) {
      $status = '<span class="badge bg-success">Selesai</span>';
   } else {
      $status = '<span class="badge bg-secondary">Unknown</span>';
   }

   $list[] = [
      "no_resep" => $row['id_visit'],
      "pasien" => $row['patient_name_pcare'] ?? '-',
      "jenis" => $jenis,
      "status" => $status
   ];
}


// ================== RESPONSE ==================
echo json_encode([
   "success" => true,
   "metric" => [
      "racikan" => $racikan,
      "non_racikan" => $non,
      "menunggu" => $menunggu,
      "selesai" => $selesai
   ],
   "antrian" => $list
]);
