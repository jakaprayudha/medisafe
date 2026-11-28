<?php
require '../../database/connect.php';
require_once __DIR__ . '/../socket/sendSocket.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$visit  = $data['visit'] ?? '';
$status = $data['status'] ?? '';

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$visit || !$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data tidak lengkap'
   ]);
   exit;
}

// Farmasi hanya boleh set Waiting(1) / Persiapan(2) / Selesai(3)
$status = (int) $status;
if (!in_array($status, [1, 2, 3], true)) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Status tidak valid'
   ]);
   exit;
}

try {

   $stmt = $koneksi->prepare("
      UPDATE permintaan_pharmacy 
      SET status_permintaan = ?
      WHERE id_visit = ? 
      AND id_customer = ?
      AND status_permintaan IN (1, 2)
   ");

   $stmt->bind_param("isi", $status, $visit, $id_customer);
   $stmt->execute();
   if ($stmt->affected_rows > 0) {
      require_once __DIR__ . '/../../utility/activityLog.php';
      $labelStatus = [1 => 'Waiting', 2 => 'Persiapan', 3 => 'Selesai (obat diserahkan)'][$status];
      logAktivitas($koneksi, 'farmasi', $status === 3 ? 'approve' : 'ubah_status', ['tipe' => 'resep_tiket', 'label' => $stmt->affected_rows . ' tiket kunjungan', 'visit_ID' => $visit],
         'Status tiket farmasi → ' . $labelStatus, null, ['status_permintaan' => $status, 'jumlah_tiket' => $stmt->affected_rows]);
   }

   echo json_encode([
      'status' => 'success',
      'message' => 'Status tiket yang sudah dikirim berhasil diupdate',
      'total' => $stmt->affected_rows
   ]);

   $result = farmasiupdate([
      "rs_id" => $kdRumahSakit,
      "target_role" => "display-farmasi_DISPLAY",
      "status" => $status,
      "visit_id" => $visit,
      "is_panggilan" => false
   ]);
} catch (Exception $e) {

   echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
   ]);
}
