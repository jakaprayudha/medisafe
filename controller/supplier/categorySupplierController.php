<?php
// Sertakan file koneksi database
include '../../database/connect.php';

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

// Handle request berdasarkan method (POST, GET, PUT, DELETE)
switch ($method) {
   case 'POST':
      // Create User
      createCategory();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         // Jika iduser ada di parameter, ambil data user berdasarkan iduser
         getCategoryID($_GET['id']);
      } else {
         // Jika tidak ada iduser, ambil semua data user
         getCategory();
      }
      break;
   case 'PUT':
      // Update User
      updateCategory();
      break;

   case 'DELETE':
      // Delete User
      deleteCategory();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create User
function createCategory()
{
   global $koneksi;

   // Ambil data dari request body
   $kategori = isset($_POST['kategori']) ? $_POST['kategori'] : '';
   $description = isset($_POST['description']) ? $_POST['description'] : '';

   if (empty($kategori)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'kategori harus diisi.'
      ]);
      exit;
   }

   // Query untuk insert data user
   $query = "INSERT INTO ms_supplier_category (category_name, category_description) VALUES (?, ?)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("ss", $kategori, $description);

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
function getCategory()
{
   global $koneksi;

   // Ambil parameter pagination dan pencarian dari request
   $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
   $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
   $search = isset($_GET['search']) && isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

   // Query dasar untuk mengambil data user
   $query = "SELECT * FROM ms_supplier_category";

   // Jika ada pencarian, tambahkan kondisi pencarian
   if ($search) {
      $query .= " WHERE category_name LIKE '%$search%'";
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
   $totalQuery = "SELECT COUNT(*) AS total FROM ms_supplier_category";
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
function getCategoryID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM ms_supplier_category WHERE id_category = ?";

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
function updateCategory()
{
   global $koneksi;

   // Ambil data dari request body
   parse_str(file_get_contents("php://input"), $_PUT);
   $id = isset($_PUT['iduser']) ? $_PUT['iduser'] : '';
   $kategori = isset($_PUT['kategori']) ? $_PUT['kategori'] : '';
   $description = isset($_PUT['deskripsi']) ? $_PUT['deskripsi'] : '';

   // Debugging input data
   if (empty($kategori) || empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID dan Kategori harus diisi.'
      ]);
      exit;
   }

   // Query untuk update data user
   $query = "UPDATE ms_supplier_category SET category_name = ?, category_description = ? WHERE id_category = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("ssi", $kategori, $description, $id);
      if ($stmt->execute()) {
         header('Content-Type: application/json');
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal memperbarui data.'
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
function deleteCategory()
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
   $query = "DELETE FROM ms_supplier_category WHERE id_category = ?";

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
