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

   $_POST = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);
   if (($_POST['pharmacy_name_generic'] ?? '') === '') {
      echo json_encode(['status' => 'error', 'message' => 'Nama generik wajib diisi']);
      exit;
   }
   if (($_POST['pharmacy_category'] ?? '') === '') {
      echo json_encode(['status' => 'error', 'message' => 'Kategori wajib dipilih']);
      exit;
   }
   // obat dengan nama generik & dagang yang sama sudah ada di daftar faskes
   $dup = $koneksi->prepare("SELECT p.id_pharmacy FROM ms_pharmacy p
      LEFT JOIN ms_pharmacy_parrent log ON log.parent_id = p.id_pharmacy AND log.id_customer_real = ?
      WHERE LOWER(TRIM(p.pharmacy_name_generic)) = LOWER(?) AND LOWER(TRIM(IFNULL(p.pharmacy_name_trade, ''))) = LOWER(?)
        AND (p.id_customer = ? OR (p.id_customer = 0 AND log.id IS NULL))
        AND (log.status_log IS NULL OR log.status_log != 'DELETE')
      LIMIT 1");
   $gen = $_POST['pharmacy_name_generic'];
   $trade = $_POST['pharmacy_name_trade'] ?? '';
   $idcInt = (int) $id_customer;
   $dup->bind_param('issi', $idcInt, $gen, $trade, $idcInt);
   $dup->execute();
   $sudahAda = $dup->get_result()->fetch_assoc();
   $dup->close();
   if ($sudahAda) {
      echo json_encode(['status' => 'error', 'message' => "Obat \"$gen\" dengan nama dagang tersebut sudah ada di daftar."]);
      exit;
   }

   if (!empty($_POST['pharmacy_code'])) {
      $pharmacy_number = $_POST['pharmacy_code'];
   } else {
      $pharmacy_number = generatePharmacyNumber($koneksi);
   }

   $fields = ['pharmacy_number', 'id_customer'];
   $status_log = "INSERT";
   $values = [$pharmacy_number, $id_customer];
   $types  = "si";
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

   $stmt = $koneksi->prepare("INSERT INTO ms_pharmacy ($columns) VALUES ($placeholders)");
   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'message' => 'Obat berhasil ditambahkan.']);
   } else {
      error_log('[pharmacyController] ' . $stmt->error);
      echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan obat.']);
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

   // hanya kolom yang dipakai daftar (tanpa kolom teks besar)
   $stmt = $koneksi->prepare("SELECT p.id_pharmacy, p.pharmacy_number, p.pharmacy_code, p.kfa_code,
         p.pharmacy_name_generic, p.pharmacy_name_trade, p.pharmacy_category, p.pharmacy_sub_category,
         p.pharmcy_golongan, p.pharmcy_jenis_drugs, p.pharmacy_bentuk_sediaan, p.pharmacy_unit,
         p.pharmacy_status, p.id_customer,
         IF(p.id_customer = 0, 'global', 'faskes') AS sumber
      FROM ms_pharmacy p

      LEFT JOIN ms_pharmacy_parrent log 
         ON log.parent_id = p.id_pharmacy 
         AND log.id_customer_real = ?

      WHERE 
         (
            p.id_customer = ? 

            OR (
               p.id_customer = 0
               AND log.id IS NULL
            )
         )

         -- 🔥 filter DELETE dari log table
         AND (
            log.status_log IS NULL 
            OR log.status_log != 'DELETE'
         )

         AND p.pharmacy_name_generic IS NOT NULL
         AND TRIM(p.pharmacy_name_generic) != ''

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

   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_pharmacy'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
      return;
   }

   $id = $_PUT['id_pharmacy'];

   // ambil data
   $check = $koneksi->prepare("SELECT * FROM ms_pharmacy WHERE id_pharmacy=?");
   $check->bind_param("i", $id);
   $check->execute();
   $row = $check->get_result()->fetch_assoc();
   $check->close();

   if (!$row) {
      echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
      return;
   }

   // field update
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
   // 🔥 GLOBAL → INSERT LOG UPDATE
   // =============================
   if ($row['id_customer'] == 0) {

      $cek = $koneksi->prepare("
         SELECT id FROM ms_pharmacy_parrent
         WHERE parent_id=? AND id_customer_real=?
      ");
      $cek->bind_param("ii", $id, $id_customer);
      $cek->execute();
      $exist = $cek->get_result()->fetch_assoc();
      $cek->close();

      if ($exist) {
         // update log
         $stmt = $koneksi->prepare("
            UPDATE ms_pharmacy_parrent 
            SET status_log='UPDATE'
            WHERE id=? 
         ");
         $stmt->bind_param("i", $exist['id']);
      } else {
         // insert log
         $stmt = $koneksi->prepare("
            INSERT INTO ms_pharmacy_parrent 
            (id_customer_real, parent_id, status_log)
            VALUES (?, ?, 'UPDATE')
         ");
         $stmt->bind_param("ii", $id_customer, $id);
      }

      $stmt->execute();
      $stmt->close();

      // 🔥 clone ke ms_pharmacy (data actual override)
      $columns = implode(',', $fields);
      $placeholders = implode(',', array_fill(0, count($fields), '?'));

      $query = "UPDATE ms_pharmacy SET $columns WHERE id_pharmacy=? AND id_customer=?";
      $values[] = $id;
      $values[] = $id_customer;

      $types = str_repeat('s', count($values) - 2) . "ii";

      $stmt = $koneksi->prepare($query);
      $stmt->bind_param($types, ...$values);
   }

   // =============================
   // ✏️ CUSTOMER → UPDATE DIRECT
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

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
   }

   $stmt->close();
}


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
   // 🔥 SEMUA DELETE MASUK KE LOG
   // =============================
   $cek = $koneksi->prepare("
      SELECT id FROM ms_pharmacy_parrent
      WHERE parent_id=? 
      AND id_customer_real=? 
      AND status_log='DELETE'
   ");
   $cek->bind_param("ii", $id, $id_customer);
   $cek->execute();
   $exist = $cek->get_result()->fetch_assoc();
   $cek->close();

   if (!$exist) {
      $user = (string) ($_SESSION['fullname'] ?? ($_SESSION['username'] ?? ''));
      $stmt = $koneksi->prepare("
         INSERT INTO ms_pharmacy_parrent 
         (id_customer_real, parent_id, status_log, user)
         VALUES (?, ?, 'DELETE',?)
      ");
      $stmt->bind_param("iis", $id_customer, $id, $user);
   } else {
      echo json_encode(['status' => 'success', 'message' => 'Obat sudah dihapus dari daftar.']);
      return;
   }

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'message' => 'Obat dihapus dari daftar faskes.']);
   } else {
      error_log('[pharmacyController] ' . $stmt->error);
      echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus obat.']);
   }

   $stmt->close();
}
