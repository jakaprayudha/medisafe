<?php
// Sertakan file koneksi database
include '../../database/connect.php';

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

// Handle request berdasarkan method (POST, GET, PUT, DELETE)
switch ($method) {
   case 'POST':
      // Create User
      createRegister();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         // Jika iduser ada di parameter, ambil data user berdasarkan iduser
         getRegisterID($_GET['id']);
      } else {
         // Jika tidak ada iduser, ambil semua data user
         getRegister();
      }
      break;
   case 'PUT':
      // Update User
      updateRegister();
      break;

   case 'DELETE':
      // Delete User
      deleteRegister();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create User
function createRegister()
{
   global $koneksi;

   // Ambil data dari request body
   $id = isset($_POST['data']) ? $_POST['data'] : '';
   $checkpasien = mysqli_query($koneksi, "SELECT * FROM ms_pasien WHERE id='$id'");
   $datapasien = mysqli_fetch_array($checkpasien);
   $nomor_rm = $datapasien['nomor_rm'];
   $tanggal = date('Y-m-d');
   $tanggal_format = date('Ymd');
   $waktu = date('H:i:s');
   $dokter = isset($_POST['dokter']) ? $_POST['dokter'] : '';
   $sumber = "Poliklinik";
   $layanan = isset($_POST['layanan']) ? $_POST['layanan'] : '';
   $catatan = isset($_POST['catatan']) ? $_POST['catatan'] : '';

   if (empty($nomor_rm) || empty($dokter)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'RM dan Dokter harus diisi.'
      ]);
      exit;
   }

   // Hitung jumlah visit hari ini untuk mendapatkan urutan
   $stmt = $koneksi->prepare("SELECT COUNT(*) as total FROM pasien_visit WHERE tanggal = ?");
   $stmt->bind_param("s", $tanggal);
   $stmt->execute();
   $result = $stmt->get_result();
   $row = $result->fetch_assoc();
   $stmt->close();

   $urutan = $row['total'] + 1;
   $urutan_format = str_pad($urutan, 6, '0', STR_PAD_LEFT); // 6 digit
   $nomor_visit = "RJ-" . $tanggal_format . $urutan_format;

   // Query insert
   $query = "INSERT INTO pasien_visit (nomor_rm, nomor_visit, tanggal, waktu, dokter, sumber, layanan, catatan_khusus) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("ssssssss", $nomor_rm, $nomor_visit, $tanggal, $waktu, $dokter, $sumber, $layanan, $catatan);
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
function getRegister()
{
   global $koneksi;

   // Ambil parameter pagination dan pencarian dari request
   $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
   $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
   $search = isset($_GET['search']) && isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

   // Query dasar untuk mengambil data user
   $query = "SELECT * FROM pasien_visit INNER JOIN ms_pasien ON pasien_visit.nomor_rm = ms_pasien.nomor_rm";

   // Jika ada pencarian, tambahkan kondisi pencarian
   if ($search) {
      $query .= " WHERE nomor_rm LIKE '%$search%' or nama_pasien LIKE '%$search%'";
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
   $totalQuery = "SELECT COUNT(*) AS total FROM pasien_visit INNER JOIN ms_pasien ON pasien_visit.nomor_rm = ms_pasien.nomor_rm";
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
function getRegisterID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM pasien_visit WHERE id = ?";

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
function updateRegister()
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
   $query = "UPDATE pasien_visit SET product_name = ?, product_code = ?, product_price = ?, product_base = ?, id_category = ?, product_description = ?, id_unit = ? WHERE id_product = ?";

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
function deleteRegister()
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
   $query = "DELETE FROM pasien_visit WHERE nomor_visit = ?";

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
