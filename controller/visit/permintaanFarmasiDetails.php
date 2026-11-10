<?php
include '../../database/connect.php';
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
      parse_str(file_get_contents("php://input"), $_PUT);

      if (isset($_GET['approve'])) {
         approveData($_PUT);
      } else {
         updateData();
      }
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

// 🔒 Tiket yang sudah Selesai (approved) tidak boleh diubah/dihapus itemnya.
// Kembalikan pesan error, atau null kalau boleh diubah.
function cekTiketTerkunci($idPermintaan = null, $idDetail = null)
{
   global $koneksi;

   $id_customer = $_SESSION['id_customer'] ?? null;
   if (!$id_customer) {
      return 'Session tidak ditemukan.';
   }

   if ($idDetail !== null) {
      $stmt = $koneksi->prepare("SELECT pp.status_permintaan 
         FROM permintaan_pharmacy_details AS pd 
         INNER JOIN permintaan_pharmacy AS pp ON pp.id_permintaan_farmasi = pd.id_permintaan_farmasi 
         WHERE pd.id_pharmacy_details = ? AND pp.id_customer = ?");
      $stmt->bind_param('ss', $idDetail, $id_customer);
   } else {
      $stmt = $koneksi->prepare("SELECT status_permintaan FROM permintaan_pharmacy 
         WHERE id_permintaan_farmasi = ? AND id_customer = ?");
      $stmt->bind_param('ss', $idPermintaan, $id_customer);
   }
   $stmt->execute();
   $tiket = $stmt->get_result()->fetch_assoc();
   $stmt->close();

   if (!$tiket) {
      return 'Data permintaan tidak ditemukan.';
   }
   if ((int)$tiket['status_permintaan'] === 3) {
      return 'Tiket sudah selesai (approved), obat tidak dapat ditambah, diubah, atau dihapus.';
   }
   return null;
}

function tolakJikaTerkunci($idPermintaan = null, $idDetail = null)
{
   $error = cekTiketTerkunci($idPermintaan, $idDetail);
   if ($error !== null) {
      http_response_code(403);
      header('Content-Type: application/json');
      echo json_encode([
         'status' => 'error',
         'message' => $error
      ]);
      exit;
   }
}

// Function untuk Create
function createData()
{
   global $koneksi;

   if (empty($_POST)) {
      $raw = file_get_contents("php://input");
      if (!empty($raw)) {
         parse_str($raw, $_POST);
      }
   }

   if (empty($_POST)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
      exit;
   }

   tolakJikaTerkunci((string)($_POST['id_permintaan_farmasi'] ?? ''));

   // ================== AMBIL HARGA DARI DB ==================
   $id_pharmacy = $_POST['id_pharmacy'] ?? null;
   $harga = 0;

   if ($id_pharmacy) {
      $getHarga = $koneksi->prepare("
         SELECT pharmacy_sale 
         FROM ms_pharmacy 
         WHERE id_pharmacy = ?
         LIMIT 1
      ");
      $getHarga->bind_param("i", $id_pharmacy);
      $getHarga->execute();
      $result = $getHarga->get_result();

      if ($row = $result->fetch_assoc()) {
         $harga = $row['pharmacy_sale'];
      }

      $getHarga->close();
   }

   // ================== FIELD VALID ==================
   $allowedFields = [
      'id_permintaan_farmasi',
      'id_pharmacy',
      'signa',
      'qty',
      'catatan',
      'created_user'
   ];

   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   // 🔥 TAMBAH HARGA DARI DB
   $fields[] = 'harga';
   $values[] = $harga;

   if (empty($fields)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada data yang dikirim.'
      ]);
      exit;
   }

   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);
   $types = str_repeat('s', count($fields));

   $query = "INSERT INTO permintaan_pharmacy_details ($columns) VALUES ($placeholders)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.',
            'id' => $stmt->insert_id
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

function getData()
{
   global $koneksi;
   // pastikan ada parameter "no" (nomor_visit)
   $no = isset($_GET['no']) ? mysqli_real_escape_string($koneksi, $_GET['no']) : '';
   $query = "SELECT 
            permintaan_pharmacy_details.*, 
            ms_pharmacy.pharmacy_name_generic, 
            ms_pharmacy.pharmacy_name_trade,
            permintaan_pharmacy.status_permintaan,
            (permintaan_pharmacy_details.qty * permintaan_pharmacy_details.harga) AS total_item
          FROM permintaan_pharmacy_details 
          LEFT JOIN ms_pharmacy 
          ON permintaan_pharmacy_details.id_pharmacy = ms_pharmacy.id_pharmacy
          LEFT JOIN permintaan_pharmacy 
          ON permintaan_pharmacy_details.id_permintaan_farmasi = permintaan_pharmacy.id_permintaan_farmasi
          WHERE permintaan_pharmacy_details.id_permintaan_farmasi = '$no'
          ORDER BY id_pharmacy_details ASC";
   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);
      return;
   }

   // Ambil semua data dalam bentuk array asosiatif
   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

   // Tutup hasil query
   mysqli_free_result($result);

   // Kirimkan data dalam format JSON
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
   $query = "SELECT * FROM permintaan_pharmacy_details WHERE id_pharmacy_details = ?";

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

   if (empty($_PUT['id_pharmacy_details'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_PUT['id_pharmacy_details'];

   tolakJikaTerkunci(null, (string)$id);

   // ================== AMBIL HARGA DARI DB ==================
   $harga = 0;

   if (!empty($_PUT['id_pharmacy'])) {

      $getHarga = $koneksi->prepare("
         SELECT pharmacy_sale 
         FROM ms_pharmacy 
         WHERE id_pharmacy = ?
         LIMIT 1
      ");

      $getHarga->bind_param("i", $_PUT['id_pharmacy']);
      $getHarga->execute();
      $result = $getHarga->get_result();

      if ($row = $result->fetch_assoc()) {
         $harga = $row['pharmacy_sale'];
      }

      $getHarga->close();
   }

   // ================== FIELD UPDATE ==================
   $allowedFields = [
      'id_pharmacy',
      'qty',
      'signa',
      'catatan',
      'created_user'
   ];

   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_PUT[$f])) {
         $fields[] = "$f=?";
         $values[] = $_PUT[$f];
      }
   }

   // 🔥 TAMBAHKAN HARGA DARI DB (OVERRIDE)
   $fields[] = "harga=?";
   $values[] = $harga;

   if (empty($fields)) {
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada data diupdate.']);
      return;
   }

   $values[] = $id;

   // semua string + id int
   $types = str_repeat('s', count($values) - 1) . "i";

   $query = "UPDATE permintaan_pharmacy_details 
             SET " . implode(',', $fields) . " 
             WHERE id_pharmacy_details=?";

   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Update gagal: ' . $stmt->error
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Query error: ' . $koneksi->error
      ]);
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

   tolakJikaTerkunci(null, (string)$id);

   // Query untuk menghapus data user
   $query = "DELETE FROM permintaan_pharmacy_details WHERE id_pharmacy_details = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $id);

      if ($stmt->execute() && $stmt->affected_rows < 1) {
         echo json_encode([
            'status' => 'error',
            'message' => 'Data obat tidak ditemukan atau sudah terhapus.'
         ]);
      } elseif ($stmt->errno === 0) {
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


function approveData($data)
{
   global $koneksi;

   if (empty($data['id_pharmacy_details'])) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID permintaan tidak ditemukan.'
      ]);
      return;
   }

   $id = $data['id_pharmacy_details'];

   $query = "UPDATE permintaan_pharmacy_details 
             SET status_item = '1'
             WHERE id_pharmacy_details = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("i", $id);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Permintaan farmasi berhasil di-approve.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Approve gagal: ' . $stmt->error
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Query gagal disiapkan.'
      ]);
   }
}
