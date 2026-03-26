<?php
include '../../database/connect.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'POST' && isset($_POST['_method'])) {
   $method = $_POST['_method'];
}

switch ($method) {
   case 'GET':
      if (!isset($_GET['no'])) {
         echo json_encode(['success' => false, 'message' => 'No doctor_number']);
         exit;
      }
      $no = $_GET['no'];
      $checkid = mysqli_query($koneksi, "SELECT * FROM ms_doctor WHERE doctor_number = '$no'");
      $dataid = mysqli_fetch_array($checkid);
      $id_doctor = $dataid['id_doctor'];
      $stmt = $koneksi->prepare("SELECT * FROM ms_doctor_schedule WHERE id_doctor = ?");
      $stmt->bind_param("s", $id_doctor);
      $stmt->execute();
      $result = $stmt->get_result();
      $data = $result->fetch_all(MYSQLI_ASSOC);
      echo json_encode(['success' => true, 'data' => $data]);
      break;

   case 'POST': // Create Jadwal
      $doctor_number = $_POST['doctor_number'] ?? '';
      $day_of_week = $_POST['day_of_week'] ?? '';
      $start_time = $_POST['start_time'] ?? '';
      $end_time = $_POST['end_time'] ?? '';

      $checkid = mysqli_query($koneksi, "SELECT * FROM ms_doctor WHERE doctor_number = '$doctor_number'");
      $dataid = mysqli_fetch_array($checkid);
      $id_doctor = $dataid['id_doctor'];

      $stmt = $koneksi->prepare("INSERT INTO ms_doctor_schedule (id_doctor, day_of_week, start_time, end_time) VALUES (?,?,?,?)");
      $stmt->bind_param("ssss", $id_doctor, $day_of_week, $start_time, $end_time);
      if ($stmt->execute()) {
         echo json_encode(['success' => true]);
      } else {
         echo json_encode(['success' => false, 'message' => $stmt->error]);
      }
      break;

   case 'DELETE':
      $id = $_POST['id_schedule'] ?? 0;
      $stmt = $koneksi->prepare("DELETE FROM ms_doctor_schedule WHERE id_schedule = ?");
      $stmt->bind_param("i", $id);
      if ($stmt->execute()) {
         echo json_encode(['success' => true]);
      } else {
         echo json_encode(['success' => false, 'message' => $stmt->error]);
      }
      break;

   default:
      echo json_encode(['success' => false, 'message' => 'Invalid Method']);
      break;
}
