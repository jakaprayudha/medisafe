<?php
require_once '../../database/connect.php'; // sesuaikan path

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

header('Content-Type: application/json');
$type = $_GET['type'] ?? '';
$value = $_GET['value'] ?? '';
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode(['status' => 'error', 'message' => 'Session habis']);
   exit;
}

switch ($type) {
   // 🔹 Ambil daftar kelas unik
   case 'service_class':
      $stmt = $koneksi->prepare("SELECT DISTINCT service_class FROM ms_room WHERE room_status = 1 AND id_customer = ? ORDER BY service_class ASC");
      $stmt->bind_param("i", $id_customer);
      $stmt->execute();
      $result = $stmt->get_result();
      $data = [];
      while ($row = $result->fetch_assoc()) {
         $data[] = $row['service_class'];
      }
      $stmt->close();
      echo json_encode(['status' => 'success', 'data' => $data]);
      break;

   // 🔹 Ambil daftar room berdasarkan kelas
   case 'room_name':
      $stmt = $koneksi->prepare("SELECT id_room, room_name FROM ms_room WHERE service_class = ? AND room_status = 1 AND id_customer = ? ORDER BY room_name ASC");
      $stmt->bind_param("si", $value, $id_customer);
      $stmt->execute();
      $result = $stmt->get_result();
      $data = [];
      while ($row = $result->fetch_assoc()) {
         $data[] = $row;
      }
      echo json_encode(['status' => 'success', 'data' => $data]);
      break;

   // 🔹 Ambil bed berdasarkan id_room
   case 'bed_name':
      $stmt = $koneksi->prepare("SELECT 
         b.id_bed, 
         b.bed_name, 
         b.bed_gender,
         CASE 
            WHEN EXISTS (
               SELECT 1 
               FROM permintaan_ranap r 
               LEFT JOIN pasien_visit pv
                  ON pv.visit_ID = r.visit_ID_inpatient
               WHERE r.id_bed = b.id_bed 
                 AND r.status = 'aktif'
                 AND (pv.visit_status IS NULL OR pv.visit_status NOT IN (4, 99))
            ) THEN 1
            ELSE 0
         END as is_used

      FROM ms_room_bed b
      WHERE b.id_room = ? 
        AND b.id_customer = ?
      ORDER BY b.bed_name ASC
   ");

      if (!$stmt) {
         echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query: ' . $koneksi->error]);
         exit;
      }

      $stmt->bind_param("ii", $value, $id_customer);
      $stmt->execute();
      $result = $stmt->get_result();

      $data = [];
      while ($row = $result->fetch_assoc()) {
         $data[] = $row;
      }
      $stmt->close();

      echo json_encode(['status' => 'success', 'data' => $data]);
      break;

   default:
      echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid']);
}
