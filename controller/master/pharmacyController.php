<?php
include '../../database/connect.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

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
      $raw = file_get_contents("php://input");
      if (!empty($raw)) {
         parse_str($raw, $_POST);
      }
   }

   if (empty($_POST)) {
      echo json_encode(['status' => 'error', 'message' => 'Data kosong']);
      exit;
   }

   $allowedFields = [
      'pharmacy_name_generic',
      'pharmacy_name_trade',
      'pharmacy_category',
      'pharmacy_sub_category',
      'pharmcy_golongan',
      'pharmcy_jenis_drugs'
   ];

   if (!empty($_POST['pharmacy_code'])) {
      $pharmacy_number = $_POST['pharmacy_code'];
   } else {
      $pharmacy_number = generatePharmacyNumber($koneksi);
   }

   $fields = ['pharmacy_number', 'id_customer', 'id_customer_real', 'status_log'];
   $status_log = "INSERT";
   $values = [$pharmacy_number, $id_customer, $id_customer, $status_log];
   $types  = "siis";
   $types  = "siis";

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
         $types .= "s";
      }
   }

   $placeholders = implode(',', array_fill(0, count($fields), '?'));
   $columns = implode(',', $fields);

   $stmt = $koneksi->prepare("INSERT INTO ms_pharmacy ($columns) VALUES ($placeholders)");
   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
   }

   $stmt->close();
}

// ================= GENERATE NUMBER =================
function generatePharmacyNumber($koneksi)
{
   $count = 0;
   do {
      $random = mt_rand(100000, 999999);
      $number = "PHR-" . $random;

      $check = $koneksi->prepare("SELECT COUNT(*) FROM ms_pharmacy WHERE pharmacy_number=?");
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

   $stmt = $koneksi->prepare("SELECT *FROM ms_pharmacy p
WHERE 
(
    p.id_customer = ?
    OR (
        p.id_customer = 0
        AND NOT EXISTS (
    SELECT 1 
    FROM ms_pharmacy c
    WHERE c.id_customer_real = ?
      AND c.parent_id = p.id_pharmacy
)
    )
)
AND (p.status_log IS NULL OR p.status_log != 'DELETE')
ORDER BY p.pharmacy_name_generic DESC
");

   $stmt->bind_param("ii", $id_customer, $id_customer);
   $stmt->execute();

   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);

   $stmt->close();
}
// ================= READ BY ID =================
function getID($id, $id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare(
      "SELECT * FROM ms_pharmacy 
       WHERE id_pharmacy=? AND id_customer=?"
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

   // ambil input PUT
   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_pharmacy'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
      return;
   }

   $id = $_PUT['id_pharmacy'];

   // 🔍 ambil data existing
   $check = $koneksi->prepare("SELECT * FROM ms_pharmacy WHERE id_pharmacy = ?");
   $check->bind_param("i", $id);
   $check->execute();
   $row = $check->get_result()->fetch_assoc();
   $check->close();

   if (!$row) {
      echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
      return;
   }

   // field yang boleh diupdate
   $allowedFields = [
      'pharmacy_name_generic',
      'pharmacy_name_trade',
      'pharmacy_category',
      'pharmacy_sub_category',
      'pharmcy_golongan',
      'pharmcy_jenis_drugs'
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
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada perubahan']);
      return;
   }

   // =============================
   // 🔥 CASE 1: DATA GLOBAL → CLONE
   // =============================
   if ($row['id_customer'] == 0) {

      // cek sudah ada override belum
      $cekOverride = $koneksi->prepare("
         SELECT id_pharmacy 
         FROM ms_pharmacy 
         WHERE parent_id=? AND id_customer=?
      ");
      $cekOverride->bind_param("ii", $id, $id_customer);
      $cekOverride->execute();
      $exist = $cekOverride->get_result()->fetch_assoc();
      $cekOverride->close();

      if ($exist) {
         // ✅ update override yang sudah ada
         $values[] = $exist['id_pharmacy'];
         $values[] = $id_customer;

         $types = str_repeat('s', count($values) - 2) . "ii";

         $query = "UPDATE ms_pharmacy SET " . implode(',', $fields) . " 
                   WHERE id_pharmacy=? AND id_customer=?";

         $stmt = $koneksi->prepare($query);
         $stmt->bind_param($types, ...$values);
      } else {

         // 🔥 clone dari global
         $clone = $koneksi->prepare("
            INSERT INTO ms_pharmacy (
               pharmacy_name_generic,
               pharmacy_name_trade,
               pharmacy_category,
               pharmacy_sub_category,
               pharmcy_golongan,
               pharmcy_jenis_drugs,
               id_customer,
               id_customer_real,
               parent_id,
               status_log
            )
            SELECT 
               pharmacy_name_generic,
               pharmacy_name_trade,
               pharmacy_category,
               pharmacy_sub_category,
               pharmcy_golongan,
               pharmcy_jenis_drugs,
               ?, ?, id_pharmacy, 'UPDATE'
            FROM ms_pharmacy
            WHERE id_pharmacy=?
         ");

         $clone->bind_param("iii", $id_customer, $id_customer, $id);

         if (!$clone->execute()) {
            echo json_encode(['status' => 'error', 'message' => $clone->error]);
            return;
         }

         // ambil id baru hasil clone
         $newId = $clone->insert_id;
         $clone->close();

         // lanjut update field dari input user
         $values[] = $newId;
         $values[] = $id_customer;

         $types = str_repeat('s', count($values) - 2) . "ii";

         $query = "UPDATE ms_pharmacy SET " . implode(',', $fields) . " 
                   WHERE id_pharmacy=? AND id_customer=?";

         $stmt = $koneksi->prepare($query);
         $stmt->bind_param($types, ...$values);
      }
   }

   // =============================
   // ✏️ CASE 2: DATA CUSTOMER
   // =============================
   else {
      $values[] = $id;
      $values[] = $id_customer;

      $types = str_repeat('s', count($values) - 2) . "ii";

      $query = "UPDATE ms_pharmacy SET " . implode(',', $fields) . " 
                WHERE id_pharmacy=? AND id_customer=?";

      $stmt = $koneksi->prepare($query);
      $stmt->bind_param($types, ...$values);
   }

   // =============================
   // 🚀 EKSEKUSI
   // =============================
   if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
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

   // cek data
   $check = $koneksi->prepare("SELECT id_customer FROM ms_pharmacy WHERE id_pharmacy=?");
   $check->bind_param("i", $id);
   $check->execute();
   $row = $check->get_result()->fetch_assoc();
   $check->close();

   if (!$row) {
      echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
      return;
   }

   // =============================
   // GLOBAL → INSERT DELETE OVERRIDE (ANTI DUPLIKAT)
   // =============================
   if ($row['id_customer'] == 0) {

      // cek sudah pernah delete belum
      $cek = $koneksi->prepare("
         SELECT id_pharmacy FROM ms_pharmacy
         WHERE parent_id=? AND id_customer=? AND status_log='DELETE'
      ");
      $cek->bind_param("ii", $id, $id_customer);
      $cek->execute();
      $exist = $cek->get_result()->fetch_assoc();
      $cek->close();

      if (!$exist) {
         $stmt = $koneksi->prepare("
            INSERT INTO ms_pharmacy (
               id_customer, id_customer_real, parent_id, status_log
            ) VALUES (?, ?, ?, 'DELETE')
         ");
         $stmt->bind_param("iii", $id_customer, $id_customer, $id);
      } else {
         echo json_encode(['status' => 'success']);
         return;
      }
   }

   // =============================
   // CUSTOMER → SOFT DELETE
   // =============================
   else {
      $stmt = $koneksi->prepare("
         UPDATE ms_pharmacy 
         SET status_log='DELETE'
         WHERE id_pharmacy=? AND id_customer=?
      ");
      $stmt->bind_param("ii", $id, $id_customer);
   }

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
   }

   $stmt->close();
}
