<?php
include '../../database/connect.php';
session_start();

header('Content-Type: application/json');

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak ditemukan'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'POST':
      createData($id_customer);
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id'], $id_customer);
      } else {
         getData($id_customer);
      }
      break;
   case 'PUT':
      parse_str(file_get_contents("php://input"), $_PUT);

      if (isset($_GET['approve'])) {
         approveData($_PUT, $id_customer);
      } else {
         updateData($id_customer);
      }
      break;
   case 'DELETE':
      deleteData($id_customer);
      break;
   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}
function createData($id_customer)
{
   global $koneksi;

   $allowedFields = [
      'id_visit',
      'catatan_permintaan',
      'rck_jumlah',
      'rck_satuan',
      'rck_signa',
      'tipe_obat',
   ];

   // ❌ HAPUS generateDoctorNumber
   $fields = ['id_customer'];
   $values = [$id_customer];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);
   $types = str_repeat('s', count($fields));

   $stmt = $koneksi->prepare("
      INSERT INTO permintaan_pharmacy ($columns) 
      VALUES ($placeholders)
   ");

   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'id' => $stmt->insert_id // 🔥 penting kalau mau dipakai lanjut
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => $stmt->error
      ]);
   }
}
function getData($id_customer)
{
   global $koneksi;

   $no = $_GET['no'] ?? '';

   $stmt = $koneksi->prepare("SELECT p.*, pv.patient_name_pcare
      FROM permintaan_pharmacy p
      LEFT JOIN pasien_visit pv 
         ON p.id_visit = pv.visit_ID
      WHERE p.id_visit = ?
      AND p.id_customer = ?
      ORDER BY p.id_permintaan_farmasi ASC
   ");

   $stmt->bind_param("si", $no, $id_customer);
   $stmt->execute();

   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);
}

function getID($id, $id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare("
      SELECT * FROM permintaan_pharmacy 
      WHERE id_permintaan_farmasi = ?
      AND id_customer = ?
   ");

   $stmt->bind_param("ii", $id, $id_customer);
   $stmt->execute();

   $result = $stmt->get_result();

   if ($result->num_rows > 0) {
      echo json_encode([
         'status' => 'success',
         'data' => $result->fetch_assoc()
      ]);
   } else {
      echo json_encode([
         'status' => 'error'
      ]);
   }
}
function updateData($id_customer)
{
   global $koneksi;
   parse_str(file_get_contents("php://input"), $_PUT);

   $id = $_PUT['id_permintaan_farmasi'] ?? '';

   $stmt = $koneksi->prepare("
      UPDATE permintaan_pharmacy 
      SET catatan_permintaan = ?
      WHERE id_permintaan_farmasi = ?
      AND id_customer = ?
   ");

   $stmt->bind_param(
      "sii",
      $_PUT['catatan_permintaan'],
      $id,
      $id_customer
   );

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   }
}

function deleteData($id_customer)
{
   global $koneksi;

   $id = $_GET['id'] ?? '';

   $stmt = $koneksi->prepare("
      DELETE FROM permintaan_pharmacy 
      WHERE id_permintaan_farmasi = ?
      AND id_customer = ?
   ");

   $stmt->bind_param("ii", $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   }
}

function approveData($data, $id_customer)
{
   global $koneksi;

   $id = $data['id_permintaan_farmasi'] ?? '';

   $stmt = $koneksi->prepare("
      UPDATE permintaan_pharmacy 
      SET status_permintaan = 1
      WHERE id_permintaan_farmasi = ?
      AND id_customer = ?
   ");

   $stmt->bind_param("ii", $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   }
}
