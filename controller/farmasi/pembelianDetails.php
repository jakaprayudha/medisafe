<?php
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';

/** Log item pembelian (modul stok). Tabel ini belum punya id_customer -> faskes diambil dari session. */
function pbAmbil($id): ?array
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT * FROM pharmacy_buy_detail WHERE id_buy = ? LIMIT 1");
   $st->bind_param('s', $id);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}
function pbLog(string $aksi, $id, ?array $lama, ?array $baru, string $ket): void
{
   global $koneksi;
   $r = ($baru ?? []) + ($lama ?? []);
   if ($lama && $baru) {
      [$lama, $baru] = logBeda($lama, $baru);
      if (!$baru) return;
   }
   logAktivitas($koneksi, 'stok', $aksi, ['tipe' => 'pembelian_item', 'id' => $id, 'label' => trim(($r['buy_item'] ?? '') . ' × ' . ($r['buy_qty'] ?? '') . ' (' . ($r['order_number'] ?? '') . ')', ' ·×()')], $ket, $lama, $baru);
}
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
   case 'POST':
      createData();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id']);
      } else {
         getData();
      }
      break;
   case 'PUT':
      // Update User
      updateData();
      break;

   case 'DELETE':
      // Delete User
      deleteData();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create
function createData()
{
   global $koneksi;

   if (empty($_POST)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
      exit;
   }

   // Ambil semua field yang valid untuk tabel pharmacy_buy_detail
   $allowedFields = [
      'order_number',
      'buy_category',
      'buy_item',
      'buy_qty',
      'buy_price',
      'buy_discount',
      'buy_notes'
   ];

   $fields = ['buy_number']; // tambahkan buy_number
   $values = [generateDoctorNumber($koneksi)];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   if (empty($fields)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada data yang dikirim.'
      ]);
      exit;
   }

   // Buat placeholder dan tipe untuk prepared statement
   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);
   $types = str_repeat('s', count($fields)); // semua string

   $query = "INSERT INTO pharmacy_buy_detail ($columns) VALUES ($placeholders)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         pbLog('tambah', $stmt->insert_id, null, array_combine($fields, $values), 'Item pembelian ditambahkan');
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menambahkan data: ' . $stmt->error
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query: ' . $koneksi->error
      ]);
   }
}

/**
 * Generate buy_number unik dengan format ORD-XXXXXX
 */
function generateDoctorNumber($koneksi)
{
   $count = 0; // inisialisasi supaya tidak merah
   do {
      $random = mt_rand(100000, 999999); // 6 digit angka
      $doctorNumber = "ORD-" . $random;

      // cek ke database apakah sudah ada
      $check = $koneksi->prepare("SELECT COUNT(*) FROM pharmacy_buy_detail WHERE buy_number = ?");
      $check->bind_param("s", $doctorNumber);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0); // ulang jika sudah ada

   return $doctorNumber;
}

function getData()
{
   global $koneksi;

   // baca parameter GET no
   $no = $_GET['no'] ?? '';

   // kalau ada parameter no, filter query-nya
   if (!empty($no)) {
      $query = "SELECT * FROM pharmacy_buy_detail WHERE order_number = '" . mysqli_real_escape_string($koneksi, $no) . "' ORDER BY id_buy DESC";
   } else {
      $query = "SELECT * FROM pharmacy_buy_detail ORDER BY id_buy DESC";
   }

   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);
      return;
   }

   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

   mysqli_free_result($result);

   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}

// Function untuk Read User berdasarkan ID
function  getID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM pharmacy_buy_detail WHERE id_buy = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $iduser); // Bind parameter iduser
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
         $data = $result->fetch_assoc();
         echo json_encode([
            'status' => 'success',
            'data' => $data
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Data tidak ditemukan.'
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query.'
      ]);
   }
}



function updateData()
{
   global $koneksi;
   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_buy'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_PUT['id_buy'];
   $allowedFields = [
      'buy_number',
      'buy_category',
      'buy_item',
      'buy_qty',
      'buy_price',
      'buy_discount',
      'buy_notes'
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
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada data diupdate.']);
      return;
   }

   $values[] = $id;
   $types = str_repeat('s', count($values) - 1) . "i";

   $query = "UPDATE pharmacy_buy_detail SET " . implode(',', $fields) . " WHERE id_buy=?";
   $pbLama = pbAmbil($id);
   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
         $pbBaru = [];
         foreach ($allowedFields as $f) if (isset($_PUT[$f])) $pbBaru[$f] = $_PUT[$f];
         if ($pbLama) pbLog('ubah', $id, $pbLama, $pbBaru, 'Item pembelian diubah');
         echo json_encode(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Update gagal: ' . $stmt->error]);
      }
      $stmt->close();
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $koneksi->error]);
   }
}



// Function untuk Delete User
function deleteData()
{
   global $koneksi;

   // Ambil ID user dari query parameter
   $id = isset($_GET['id']) ? $_GET['id'] : '';

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      exit;
   }

   // Query untuk menghapus data user
   $pbLama = pbAmbil($id);
   $query = "DELETE FROM pharmacy_buy_detail WHERE id_buy = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $id);

      if ($stmt->execute()) {
         if ($pbLama && $stmt->affected_rows > 0) pbLog('hapus', $id, $pbLama, null, 'Item pembelian dihapus');
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menghapus.'
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query.'
      ]);
   }
}
