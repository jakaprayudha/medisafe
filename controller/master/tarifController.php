<?php
include '../../database/connect.php';

header('Content-Type: application/json');

// 🔐 VALIDASI SESSION
if (!isset($_SESSION['id_customer'])) {
   http_response_code(401);
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak valid / expired'
   ]);
   exit;
}

$id_customer = $_SESSION['id_customer'];

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
      updateData($id_customer);
      break;

   case 'DELETE':
      deleteData($id_customer);
      break;
}

// ================= CREATE =================
function createData($id_customer)
{
   global $koneksi;

   if (empty($_POST)) {
      echo json_encode(['status' => 'error', 'message' => 'Data kosong']);
      exit;
   }

   $allowedFields = [
      'tarif_code',
      'tarif_services',
      'tarif_name',
      'tarif_amount',
      'tarif_provider'
   ];

   $fields = ['tarif_number', 'id_customer'];
   $values = [generateTarifNumber($koneksi), $id_customer];
   $types  = "si";

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
         $types .= "s";
      }
   }

   $placeholders = implode(',', array_fill(0, count($fields), '?'));
   $columns = implode(',', $fields);

   $stmt = $koneksi->prepare("INSERT INTO ms_tarif ($columns) VALUES ($placeholders)");
   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
   }

   $stmt->close();
}

// ================= GENERATE NUMBER =================
function generateTarifNumber($koneksi)
{
   $count = 0;

   do {
      $random = mt_rand(100000, 999999);
      $number = "TRX-" . $random;

      $check = $koneksi->prepare("SELECT COUNT(*) FROM ms_tarif WHERE tarif_number=?");
      $check->bind_param("s", $number);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $number;
}

// ================= READ =================
function getData($id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare(
      "SELECT ms_tarif.*, ms_provider.provider_name FROM ms_tarif LEFT JOIN ms_provider ON ms_tarif.tarif_provider = ms_provider.id_provider
       WHERE ms_tarif.id_customer=? 
       ORDER BY ms_tarif.tarif_name DESC"
   );

   $stmt->bind_param("i", $id_customer);
   $stmt->execute();

   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

   echo json_encode(['status' => 'success', 'data' => $data]);

   $stmt->close();
}

// ================= READ BY ID =================
function getID($id, $id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare(
      "SELECT * FROM ms_tarif 
       WHERE id_tarif=? AND id_customer=?"
   );

   $stmt->bind_param("ii", $id, $id_customer);
   $stmt->execute();

   $res = $stmt->get_result();

   if ($res->num_rows > 0) {
      echo json_encode([
         'status' => 'success',
         'data' => $res->fetch_assoc()
      ]);
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Tidak ditemukan']);
   }

   $stmt->close();
}

// ================= UPDATE =================
function updateData($id_customer)
{
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_tarif'])) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan'
      ]);
      return;
   }

   $id = $_PUT['id_tarif'];

   // ================= 🔥 TOGGLE STATUS =================
   if (isset($_GET['toggle_status']) && isset($_PUT['tarif_status'])) {

      $status = $_PUT['tarif_status'];

      $stmt = $koneksi->prepare(
         "UPDATE ms_tarif 
          SET tarif_status=? 
          WHERE id_tarif=? AND id_customer=?"
      );

      $stmt->bind_param("iii", $status, $id, $id_customer);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Status berhasil diupdate.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal update status.'
         ]);
      }

      $stmt->close();
      return; // ⛔ penting (stop di sini)
   }

   // ================= 🔥 UPDATE NORMAL =================
   $allowedFields = [
      'tarif_code',
      'tarif_services',
      'tarif_name',
      'tarif_amount',
      'tarif_provider'
   ];

   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_PUT[$f])) {
         $fields[] = "$f=?";
         $values[] = $_PUT[$f];
      }
   }

   if (empty($fields)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada data diupdate'
      ]);
      return;
   }

   $values[] = $id;
   $values[] = $id_customer;

   $types = str_repeat('s', count($values) - 2) . "ii";

   $query = "UPDATE ms_tarif SET " . implode(',', $fields) . " 
             WHERE id_tarif=? AND id_customer=?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Data berhasil diperbarui.'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => $stmt->error
      ]);
   }

   $stmt->close();
}

// ================= DELETE =================
function deleteData($id_customer)
{
   global $koneksi;

   $id = $_GET['id'] ?? '';

   if (!$id) {
      echo json_encode(['status' => 'error', 'message' => 'ID kosong']);
      return;
   }

   $stmt = $koneksi->prepare(
      "DELETE FROM ms_tarif 
       WHERE id_tarif=? AND id_customer=?"
   );

   $stmt->bind_param("ii", $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   } else {
      echo json_encode(['status' => 'error']);
   }

   $stmt->close();
}
