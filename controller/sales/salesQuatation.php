<?php
session_start();
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

   // Format tahun-bulan untuk kode faktur
   $tahunBulan = date('Ym', strtotime($tanggal)); // Contoh: 202505

   // Ambil faktur terakhir berdasarkan tahun (reset setiap tahun)
   $tahun = date('Y', strtotime($tanggal));

   $stmt = $koneksi->prepare("
      SELECT no_faktur 
      FROM sales_quotation 
      WHERE no_faktur LIKE ? 
      ORDER BY no_faktur DESC 
      LIMIT 1
   ");
   $likePattern = "FR-$tahun%";
   $stmt->bind_param("s", $likePattern);
   $stmt->execute();
   $result = $stmt->get_result();
   $row = $result->fetch_assoc();

   if ($row) {
      // Ambil angka urut terakhir lalu tambah 1
      $lastParts = explode('-', $row['no_faktur']);
      $lastNo = (int)$lastParts[2];
      $nextNo = str_pad($lastNo + 1, 6, '0', STR_PAD_LEFT);
   } else {
      $nextNo = '000001'; // Jika belum ada, mulai dari 000001
   }

   $no_faktur = "FR-$tahunBulan-$nextNo";
   $stmt->close();

   // Insert data
   $query = "INSERT INTO sales_quotation (tanggal, no_faktur, id_pelanggan, catatan, id_karyawan, user_created) 
             VALUES (?, ?, ?, ?, ?, ?)";
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
            'message' => 'Gagal menambahkan data.'
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

   // Query utama
   $query = "
      SELECT 
         sales_quotation.*, 
         ms_customer.customer_name, 
         ms_employee.employee_name,
         COUNT(sales_order.id_order) AS total_item,

         -- Total sebelum diskon
         SUM(sales_order.qty * sales_order.harga_satuan) AS total_bayar,

         -- Total diskon
         SUM(
            (sales_order.qty * sales_order.harga_satuan) -
            (
               (sales_order.qty * sales_order.harga_satuan)
               * (1 - (sales_order.diskon_1 / 100))
               * (1 - (sales_order.diskon_2 / 100))
               * (1 - (sales_order.diskon_3 / 100))
            )
         ) AS total_diskon,

         -- Total bayar akhir = total - diskon
         SUM(
            (sales_order.qty * sales_order.harga_satuan)
            * (1 - (sales_order.diskon_1 / 100))
            * (1 - (sales_order.diskon_2 / 100))
            * (1 - (sales_order.diskon_3 / 100))
         ) AS total_bayar_akhir

      FROM sales_quotation
      INNER JOIN ms_customer ON ms_customer.id_customer = sales_quotation.id_pelanggan
      LEFT JOIN ms_employee ON ms_employee.id_employee = sales_quotation.id_karyawan
      LEFT JOIN sales_order ON sales_order.id_quotation = sales_quotation.id_quotation
   ";

   // Filter pencarian
   if ($search) {
      $query .= " WHERE sales_quotation.no_faktur LIKE '%$search%'";
   }

   $query .= " GROUP BY sales_quotation.id_quotation";
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

   // Total semua data untuk pagination
   $totalQuery = "SELECT COUNT(*) AS total FROM sales_quotation";
   $totalResult = mysqli_query($koneksi, $totalQuery);
   $totalData = mysqli_fetch_assoc($totalResult);
   $totalRecords = $totalData['total'];

   // Output format DataTables
   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
      'recordsTotal' => $totalRecords,
      'recordsFiltered' => $totalRecords
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

   // Ambil ID dari query parameter
   $id = isset($_GET['id']) ? $_GET['id'] : '';

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      exit;
   }

   // Cek apakah ada detail terkait di tabel detail
   $cekDetail = $koneksi->prepare("SELECT COUNT(*) as total FROM sales_order WHERE id_quotation = ?");
   $cekDetail->bind_param("s", $id);
   $cekDetail->execute();
   $result = $cekDetail->get_result();
   $data = $result->fetch_assoc();
   $cekDetail->close();

   if ($data['total'] > 0) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak bisa dihapus karena masih memiliki detail penjualan.'
      ]);
      exit;
   }

   // Query untuk menghapus jika tidak ada detail
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
            'message' => 'Gagal menghapus data.'
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
