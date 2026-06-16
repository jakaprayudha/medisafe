<?php
require_once '../../database/connect.php'; // sesuaikan path

header('Content-Type: application/json');
$type = $_GET['type'] ?? '';
$value = $_GET['value'] ?? '';

switch ($type) {
   // 🔹 Ambil daftar kelas unik
   case 'service_class':
      $query = $koneksi->query("SELECT DISTINCT service_class FROM ms_room WHERE room_status = 1 ORDER BY service_class ASC");
      $data = [];
      while ($row = $query->fetch_assoc()) {
         $data[] = $row['service_class'];
      }
      echo json_encode(['status' => 'success', 'data' => $data]);
      break;

   // 🔹 Ambil daftar room berdasarkan kelas
   case 'room_name':
      $stmt = $koneksi->prepare("SELECT id_room, room_name FROM ms_room WHERE service_class = ? AND room_status = 1 ORDER BY room_name ASC");
      $stmt->bind_param("s", $value);
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
      $stmt = $koneksi->prepare("SELECT id_bed, bed_name, bed_gender FROM ms_room_bed WHERE id_room = ? AND bed_status = 1 ORDER BY bed_name ASC");
      $stmt->bind_param("i", $value);
      $stmt->execute();
      $result = $stmt->get_result();
      $data = [];
      while ($row = $result->fetch_assoc()) {
         $data[] = $row;
      }
      echo json_encode(['status' => 'success', 'data' => $data]);
      break;

   default:
      echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid']);
}
