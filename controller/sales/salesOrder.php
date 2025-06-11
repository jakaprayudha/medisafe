<?php
// Sertakan file koneksi database
include '../../database/connect.php';

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

// Handle request berdasarkan method (POST, GET, PUT, DELETE)
switch ($method) {
   case 'POST':
      // Create User
      createSales();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         // Jika iduser ada di parameter, ambil data user berdasarkan iduser
         getSalesID($_GET['id']);
      } else {
         // Jika tidak ada iduser, ambil semua data user
         getSales();
      }
      break;
   case 'PUT':
      // Update User
      updateSales();
      break;

   case 'DELETE':
      // Delete User
      deleteSales();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create User
function createSales()
{
   global $koneksi;

   // Ambil data dari request body
   $tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : '';
   $pelanggan = isset($_POST['pelanggan']) ? $_POST['pelanggan'] : '';
   $marketing = isset($_POST['marketing']) ? $_POST['marketing'] : '';
   $catatan = isset($_POST['catatan']) ? $_POST['catatan'] : '';
   $user = $_SESSION['fullname'];

   if (empty($pelanggan)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Pelanggan harus diisi.'
      ]);
      exit;
   }

   // Format tanggal untuk faktur
   $tanggalFormat = date('Ymd', strtotime($tanggal));

   // Hitung jumlah faktur pada tanggal yang sama
   $stmtCount = $koneksi->prepare("SELECT COUNT(*) as total FROM sales_quotation WHERE tanggal = ?");
   $stmtCount->bind_param("s", $tanggal);
   $stmtCount->execute();
   $result = $stmtCount->get_result();
   $row = $result->fetch_assoc();
   $totalFakturHariIni = $row['total'] + 1;
   $stmtCount->close();

   // Buat no faktur: YYYYMMDD-XXXXXX
   $noUrut = str_pad($totalFakturHariIni, 6, '0', STR_PAD_LEFT);
   $no_faktur = $tanggalFormat . '-' . $noUrut;

   // Query insert
   $query = "INSERT INTO sales_quotation (tanggal, no_faktur, id_pelanggan, catatan, id_karyawan, user_created) VALUES (?, ?, ?, ?, ?, ?)";
   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("ssssss", $tanggal, $no_faktur, $pelanggan, $catatan, $marketing, $user);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.',
            'no_faktur' => $no_faktur
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
function getSales()
{
   global $koneksi;

   // Ambil parameter pagination dan pencarian dari request
   $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
   $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
   $search = isset($_GET['search']) && isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

   // Query dasar untuk mengambil data user
   $query = "SELECT * FROM sales_quotation INNER JOIN ms_customer ON ms_customer.id_customer = sales_quotation.id_pelanggan LEFT OUTER JOIN ms_employee ON ms_employee.id_employee = sales_quotation.id_karyawan";

   // Jika ada pencarian, tambahkan kondisi pencarian
   if ($search) {
      $query .= " WHERE no_faktur LIKE '%$search%'";
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
   $totalQuery = "SELECT COUNT(*) AS total FROM sales_quotation";
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
function getSalesID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM sales_quotation WHERE id_quotation = ?";

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
function updateSales()
{
   global $koneksi;

   // Ambil data dari request body
   parse_str(file_get_contents("php://input"), $_PUT);
   $id = isset($_PUT['iduser']) ? $_PUT['iduser'] : '';
   $tanggal = isset($_PUT['tanggal']) ? $_PUT['tanggal'] : '';
   $marketing = isset($_PUT['marketing']) ? $_PUT['marketing'] : '';
   $pelanggan = isset($_PUT['pelanggan']) ? $_PUT['pelanggan'] : '';
   $catatan = isset($_PUT['catatan']) ? $_PUT['catatan'] : '';

   // Debugging input data
   if (empty($pelanggan)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Pelanggan  harus diisi.'
      ]);
      exit;
   }

   // Query untuk update data user
   $query = "UPDATE sales_quotation SET tanggal = ?, id_karyawan = ?, id_pelanggan = ?, catatan = ?, updated_at = ?, user_updated = ? WHERE id_quotation = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $updated_at = date('Y-m-d H:i:s');
      $stmt->bind_param("ssssssi", $tanggal, $marketing, $pelanggan, $catatan, $updated_at, $_SESSION['fullname'], $id);
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
function deleteSales()
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
   $query = "DELETE FROM sales_quotation WHERE id_quotation = ?";

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
