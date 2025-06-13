<?php
// Sertakan file koneksi database
include '../../database/connect.php';

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

// Handle request berdasarkan method (POST, GET, PUT, DELETE)
switch ($method) {
   case 'POST':
      // Create User
      createEmployeeOffice();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         // Jika iduser ada di parameter, ambil data user berdasarkan iduser
         getEmployeeOfficeID($_GET['id']);
      } else {
         // Jika tidak ada iduser, ambil semua data user
         getEmployeeOffice();
      }
      break;
   case 'PUT':
      // Update User
      updateEmployeeOffice();
      break;

   case 'DELETE':
      // Delete User
      deleteEmployeeOffice();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create User
function createEmployeeOffice()
{
   global $koneksi;

   // Ambil data dari request body
   $fullname = isset($_POST['nama']) ? $_POST['nama'] : '';
   $address = isset($_POST['alamat']) ? $_POST['alamat'] : '';
   $phone = isset($_POST['telepon']) ? $_POST['telepon'] : '';
   $email = isset($_POST['email']) ? $_POST['email'] : '';
   $category = "Office";
   $description = isset($_POST['deskripsi']) ? $_POST['deskripsi'] : '';

   if (empty($fullname) || empty($address) || empty($phone)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Nama Lengkap, Alamat dan No.Telepon harus diisi.'
      ]);
      exit;
   }

   // Query untuk insert data user
   $query = "INSERT INTO ms_employee (employee_category, employee_name, employee_address, employee_phone, employee_email, employee_description) VALUES (?, ?, ?, ?, ?, ?)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("ssssss", $category, $fullname, $address, $phone, $email, $description);

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
function getEmployeeOffice()
{
   global $koneksi;
   $category = "Office";
   // Ambil parameter pagination dan pencarian dari request
   $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
   $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
   $search = isset($_GET['search']) && isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

   // Query dasar untuk mengambil data user
   $query = "SELECT * FROM ms_employee WHERE employee_category = '$category'";

   // Jika ada pencarian, tambahkan kondisi pencarian
   if ($search) {
      $query .= " AND employee_name LIKE '%$search%' ";
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
   $totalQuery = "SELECT COUNT(*) AS total FROM ms_employee WHERE employee_category = '$category'";
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
function getEmployeeOfficeID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM ms_employee WHERE id_employee = ?";

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
function updateEmployeeOffice()
{
   global $koneksi;

   // Ambil data dari request body
   parse_str(file_get_contents("php://input"), $_PUT);
   $id = isset($_PUT['iduser']) ? $_PUT['iduser'] : '';
   $fullname = isset($_PUT['nama']) ? $_PUT['nama'] : '';
   $address = isset($_PUT['alamat']) ? $_PUT['alamat'] : '';
   $phone = isset($_PUT['telepon']) ? $_PUT['telepon'] : '';
   $email = isset($_PUT['email']) ? $_PUT['email'] : '';
   $description = isset($_PUT['deskripsi']) ? $_PUT['deskripsi'] : '';

   // Debugging input data
   if (empty($fullname) || empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID dan Nama harus diisi.'
      ]);
      exit;
   }

   // Query untuk update data user
   $query = "UPDATE ms_employee SET employee_name = ?, employee_address = ?, employee_phone = ?, employee_email = ?, employee_description = ? WHERE id_employee  = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("sssssi", $fullname, $address, $phone, $email, $description, $id);
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
function deleteEmployeeOffice()
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
   $query = "DELETE FROM ms_employee WHERE id_employee = ?";

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
