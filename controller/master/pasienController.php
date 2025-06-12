<?php
// Sertakan file koneksi database
include '../../database/connect.php';

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

// Handle request berdasarkan method (POST, GET, PUT, DELETE)
switch ($method) {
   case 'POST':
      // Create User
      createPasien();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         // Jika iduser ada di parameter, ambil data user berdasarkan iduser
         getPasienID($_GET['id']);
      } else {
         // Jika tidak ada iduser, ambil semua data user
         getPasien();
      }
      break;
   case 'PUT':
      // Update User
      updatePasien();
      break;

   case 'DELETE':
      // Delete User
      deletePasien();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create User
function createPasien()
{
   global $koneksi;

   // Ambil data dari request body
   $nama_pasien = $_POST['nama_pasien'] ?? '';
   $tempat_lahir = $_POST['tempat_lahir'] ?? '';
   $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
   $gender = $_POST['gender'] ?? '';
   $telepon = $_POST['telepon'] ?? '';
   $alamat = $_POST['alamat'] ?? '';
   $catatan = $_POST['catatan'] ?? '';

   if (empty($nama_pasien)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Nama harus diisi.'
      ]);
      exit;
   }

   // Ambil nomor_rm terakhir
   $result = mysqli_query($koneksi, "SELECT nomor_rm FROM ms_pasien ORDER BY nomor_rm DESC LIMIT 1");
   if ($row = mysqli_fetch_assoc($result)) {
      // Jika ada data, ambil nomor_rm terakhir lalu +1
      $last_rm = intval($row['nomor_rm']);
      $nomor_rm = str_pad($last_rm + 1, 6, '0', STR_PAD_LEFT); // jadi format 000001 dst.
   } else {
      // Jika belum ada data
      $nomor_rm = '000001';
   }

   // Query insert data
   $query = "INSERT INTO ms_pasien (nomor_rm, nama_pasien, tempat_lahir, tanggal_lahir, gender, telepon, alamat, catatan_khusus) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("ssssssss", $nomor_rm, $nama_pasien, $tempat_lahir, $tanggal_lahir, $gender, $telepon, $alamat, $catatan);
      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.',
            'nomor_rm' => $nomor_rm
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
function getPasien()
{
   global $koneksi;

   // Ambil parameter pagination dan pencarian dari request
   $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
   $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
   $search = isset($_GET['search']) && isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

   // Query dasar untuk mengambil data user
   $query = "SELECT * FROM ms_pasien ";

   // Jika ada pencarian, tambahkan kondisi pencarian
   if ($search) {
      $query .= " WHERE nama_pasien LIKE '%$search%' or nomor_rm LIKE '%$search%'";
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
   $totalQuery = "SELECT COUNT(*) AS total FROM ms_pasien";
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
function getPasienID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM ms_pasien WHERE id = ?";

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
function updatePasien()
{
   global $koneksi;

   // Ambil data dari request body
   parse_str(file_get_contents("php://input"), $_PUT);
   $id = isset($_PUT['iduser']) ? $_PUT['iduser'] : '';
   $satuan = isset($_PUT['satuan']) ? $_PUT['satuan'] : '';
   $product = isset($_PUT['produk']) ? $_PUT['produk'] : '';
   $code = isset($_PUT['kode']) ? $_PUT['kode'] : '';
   $product_price = isset($_PUT['harga_jual']) ? $_PUT['harga_jual'] : '';
   $product_base = isset($_PUT['harga_beli']) ? $_PUT['harga_beli'] : '';
   $category = isset($_PUT['kategori']) ? $_PUT['kategori'] : '';
   $description = isset($_PUT['deskripsi']) ? $_PUT['deskripsi'] : '';

   // Debugging input data
   if (empty($product) || empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID dan Product Item harus diisi.'
      ]);
      exit;
   }

   // Query untuk update data user
   $query = "UPDATE ms_pasien SET product_name = ?, product_code = ?, product_price = ?, product_base = ?, id_category = ?, product_description = ?, id_unit = ? WHERE id_product = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("ssssssss", $product, $code, $product_price, $product_base, $category, $description, $satuan, $id);
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
function deletePasien()
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
   $query = "DELETE FROM ms_pasien WHERE id = ?";

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
