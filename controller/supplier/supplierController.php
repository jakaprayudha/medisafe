<?php
// Sertakan file koneksi database
include '../../database/connect.php';

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

// Handle request berdasarkan method (POST, GET, PUT, DELETE)
switch ($method) {
   case 'POST':
      // Create User
      createSupplier();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         // Jika iduser ada di parameter, ambil data user berdasarkan iduser
         getSupplierID($_GET['id']);
      } else {
         // Jika tidak ada iduser, ambil semua data user
         getSupplier();
      }
      break;
   case 'PUT':
      // Update User
      updateSupplier();
      break;

   case 'DELETE':
      // Delete User
      deleteSupplier();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create User
function createSupplier()
{
   global $koneksi;

   // Ambil data dari request body
   $category = isset($_POST['kategori']) ? $_POST['kategori'] : '';
   $supplier = isset($_POST['supplier']) ? $_POST['supplier'] : '';
   $phone = isset($_POST['telepon']) ? $_POST['telepon'] : '';
   $fax = isset($_POST['fax']) ? $_POST['fax'] : '';
   $email = isset($_POST['email']) ? $_POST['email'] : '';
   $address = isset($_POST['alamat']) ? $_POST['alamat'] : '';
   $description = isset($_POST['deskripsi']) ? $_POST['deskripsi'] : '';
   if (empty($supplier || empty($category))) {
      echo json_encode([
         'status' => 'error',
         'message' => 'supplier name dan category harus diisi.'
      ]);
      exit;
   }

   // Query untuk insert data user
   $query = "INSERT INTO ms_supplier (id_category, supplier_name, supplier_address, supplier_phone, supplier_fax, supplier_email, supplier_description) VALUES (?, ?, ?, ?, ?, ?, ?)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("sssssss", $category, $supplier, $address, $phone, $fax, $email, $description);

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
function getSupplier()
{
   global $koneksi;

   // Ambil parameter pagination dan pencarian dari request
   $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
   $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
   $search = isset($_GET['search']) && isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

   // Query dasar untuk mengambil data user
   $query = "SELECT * FROM ms_supplier LEFT OUTER JOIN ms_supplier_category ON ms_supplier.id_category = ms_supplier_category.id_category";

   // Jika ada pencarian, tambahkan kondisi pencarian
   if ($search) {
      $query .= " WHERE supplier_name LIKE '%$search%'";
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
   $totalQuery = "SELECT COUNT(*) AS total FROM ms_supplier";
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
function getSupplierID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM ms_supplier WHERE id_supplier = ?";

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
function updateSupplier()
{
   global $koneksi;

   // Ambil data dari request body
   parse_str(file_get_contents("php://input"), $_PUT);
   $id = isset($_PUT['iduser']) ? $_PUT['iduser'] : '';
   $supplier = isset($_PUT['supplier']) ? $_PUT['supplier'] : '';
   $address = isset($_PUT['alamat']) ? $_PUT['alamat'] : '';
   $phone = isset($_PUT['telepon']) ? $_PUT['telepon'] : '';
   $fax = isset($_PUT['fax']) ? $_PUT['fax'] : '';
   $email = isset($_PUT['email']) ? $_PUT['email'] : '';
   $description = isset($_PUT['deskripsi']) ? $_PUT['deskripsi'] : '';
   $category = isset($_PUT['kategori']) ? $_PUT['kategori'] : '';
   // Debugging input data
   if (empty($supplier) || empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID dan Supplier Name harus diisi.'
      ]);
      exit;
   }

   // Query untuk update data user
   $query = "UPDATE ms_supplier SET supplier_name = ?, supplier_address = ?, supplier_phone = ?, supplier_fax = ?, supplier_email = ?, supplier_description = ?, id_category = ? WHERE id_supplier = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("sssssssi", $supplier, $address, $phone, $fax, $email, $description, $category, $id);
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
function deleteSupplier()
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
   $query = "DELETE FROM ms_supplier WHERE id_supplier = ?";

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
