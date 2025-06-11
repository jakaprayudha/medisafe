<?php
// Sertakan file koneksi database
include '../../database/connect.php';

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

// Handle request berdasarkan method (POST, GET, PUT, DELETE)
switch ($method) {
   case 'POST':
      // Create User
      createPrice();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         // Jika iduser ada di parameter, ambil data user berdasarkan iduser
         getPriceID($_GET['id']);
      } else {
         // Jika tidak ada iduser, ambil semua data user
         getPrice();
      }
      break;
   case 'PUT':
      // Update User
      updatePrice();
      break;

   case 'DELETE':
      // Delete User
      deletePrice();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create User
function createPrice()
{
   global $koneksi;

   // Ambil data dari request body
   $satuan = isset($_POST['satuan']) ? $_POST['satuan'] : '';
   $product_price = isset($_POST['harga_jual']) ? $_POST['harga_jual'] : '';
   $description = isset($_POST['catatan']) ? $_POST['catatan'] : '';
   $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
   if (empty($satuan) || empty($product_price)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'satuan item dan harga harus diisi.'
      ]);
      exit;
   }

   // Query untuk insert data user
   $query = "INSERT INTO ms_product_price (id_product, id_unit, price, price_notes, user) VALUES (?, ?, ?, ?, ?)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("sssss", $product_id, $satuan, $product_price, $description, $_SESSION['fullname']);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menambahkan.'
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

// Function untuk Read User
function getPrice()
{
   global $koneksi;

   // Ambil parameter pagination dan pencarian dari request
   $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
   $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
   $search = isset($_GET['search']) && isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

   // Query dasar untuk mengambil data user
   $query = "SELECT * FROM ms_product_price LEFT OUTER JOIN ms_product_unit ON  ms_product_unit.id_unit = ms_product_price.id_unit";

   // Jika ada pencarian, tambahkan kondisi pencarian
   if ($search) {
      $query .= " WHERE unit_name LIKE '%$search%' or price LIKE '%$search%'";
   }

   // Ambil data sesuai dengan pagination
   $query .= " LIMIT $start, $length";

   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);
      exit;
   }

   $data = [];
   while ($row = mysqli_fetch_assoc($result)) {
      $data[] = $row;
   }

   // Query untuk menghitung total data
   $totalQuery = "SELECT COUNT(*) AS total FROM ms_product_price";
   $totalResult = mysqli_query($koneksi, $totalQuery);
   $totalData = mysqli_fetch_assoc($totalResult);
   $totalRecords = $totalData['total'];

   // Kirimkan data dalam format JSON untuk DataTables
   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
      'recordsTotal' => $totalRecords,
      'recordsFiltered' => $totalRecords // Jika Anda ingin total yang difilter, buat query terpisah untuk menghitung total hasil pencarian
   ]);
}

// Function untuk Read User berdasarkan ID
function getPriceID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM ms_product_price WHERE id_price = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $iduser); // Bind parameter iduser
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
         $user = $result->fetch_assoc();
         echo json_encode([
            'status' => 'success',
            'user' => $user
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

// Function untuk Update User
function updatePrice()
{
   header('Content-Type: application/json');
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   $id = isset($_PUT['id_price']) ? $_PUT['id_price'] : '';
   $satuan = isset($_PUT['satuan']) ? $_PUT['satuan'] : '';
   $product_price = isset($_PUT['harga_jual']) ? $_PUT['harga_jual'] : '';
   $description = isset($_PUT['catatan']) ? $_PUT['catatan'] : '';

   if (empty($satuan) || empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID dan satuan harus diisi.'
      ]);
      exit;
   }

   $query = "UPDATE ms_product_price SET id_unit = ?, price = ?, price_notes = ?, updated_at = ? WHERE id_price = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("sssss", $satuan, $product_price, $description, date('Y-m-d H:i:s'), $id);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal memperbarui data.',
            'debug' => $stmt->error
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

// Function untuk Delete User
function deletePrice()
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
   $query = "DELETE FROM ms_product_price WHERE id_price = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $id);

      if ($stmt->execute()) {
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
