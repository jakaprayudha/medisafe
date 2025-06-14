<?php
// Sertakan file koneksi database
include '../../database/connect.php';

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

// Handle request berdasarkan method (POST, GET, PUT, DELETE)
switch ($method) {
   case 'POST':
      // Create User
      createTarif();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         // Jika iduser ada di parameter, ambil data user berdasarkan iduser
         getTarifID($_GET['id']);
      } else {
         // Jika tidak ada iduser, ambil semua data user
         getTarif();
      }
      break;
   case 'PUT':
      // Update User
      updateFarmasi();
      break;

   case 'DELETE':
      // Delete User
      deleteTarif();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create User
function createTarif()
{
   global $koneksi;

   // Ambil data dari request body
   $nomor_rm = $_POST['nomor_rm'] ?? '';
   $nomor_visit = $_POST['nomor_visit'] ?? '';
   $checkvisit = mysqli_query($koneksi, "SELECT * FROM pasien_visit WHERE nomor_rm='$nomor_rm' AND nomor_visit='$nomor_visit'");
   $datavisit = mysqli_fetch_array($checkvisit);
   $unit = $datavisit['sumber'];
   $dokter = $datavisit['dokter'];
   $signa = $_POST['signa'] ?? '';
   $jumlah = $_POST['qty'] ?? '';
   $catatan = $_POST['catatan'] ?? '';
   $item = $_POST['item'] ?? '';
   $checkharga = mysqli_query($koneksi, "SELECT * FROM ms_farmasi WHERE nama_barang='$item'");
   $dataharga = mysqli_fetch_array($checkharga);
   $hargaitem = $dataharga['tarif_dasar'];
   $satuan = $dataharga['satuan'];

   if (empty($item)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Nama Tarif harus diisi.'
      ]);
      exit;
   }

   // Query insert data
   $query = "INSERT INTO permintaan_farmasi (nomor_rm, nomor_visit, unit, dokter, item, qty, signa, harga, catatan_permintaan, satuan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("ssssssssss", $nomor_rm, $nomor_visit, $unit, $dokter, $item, $jumlah, $signa, $hargaitem, $catatan, $satuan);
      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.'
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
function getTarif()
{
   global $koneksi;

   // Ambil parameter pagination dan pencarian dari request
   $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
   $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
   $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

   // Ambil parameter nomor_rm dan nomor_visit
   $no = isset($_GET['no']) ? $_GET['no'] : '';
   $rm = isset($_GET['rm']) ? $_GET['rm'] : '';

   // Base query
   $query = "SELECT * FROM permintaan_farmasi WHERE 1=1";

   // Tambahkan filter berdasarkan nomor_rm dan nomor_visit
   if (!empty($rm)) {
      $query .= " AND nomor_rm = '" . mysqli_real_escape_string($koneksi, $rm) . "'";
   }

   if (!empty($no)) {
      $query .= " AND nomor_visit = '" . mysqli_real_escape_string($koneksi, $no) . "'";
   }

   // Tambahkan filter pencarian jika ada
   if (!empty($search)) {
      $query .= " AND item LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%'";
   }

   // Hitung total records (tanpa limit)
   $totalQuery = "SELECT COUNT(*) AS total FROM ($query) AS filtered";
   $totalResult = mysqli_query($koneksi, $totalQuery);
   $totalData = mysqli_fetch_assoc($totalResult);
   $totalRecords = $totalData['total'];

   // Tambahkan limit untuk pagination
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

   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
      'recordsTotal' => $totalRecords,
      'recordsFiltered' => $totalRecords
   ]);
}
// Function untuk Read User berdasarkan ID
function getTarifID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM permintaan_farmasi WHERE id = ?";

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
function updateFarmasi()
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
   $query = "UPDATE permintaan_farmasi SET product_name = ?, product_code = ?, product_price = ?, product_base = ?, id_category = ?, product_description = ?, id_unit = ? WHERE id_product = ?";

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
function deleteTarif()
{
   global $koneksi;

   // Ambil ID user dari query parameter
   $id = isset($_GET['id']) ? $_GET['id'] : '';
   $method = isset($_GET['method']) ? $_GET['method'] : '';

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      exit;
   }
   if ($method != "approve") {
      // Query untuk menghapus data user
      $query = "DELETE FROM permintaan_farmasi WHERE id = ?";

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
   } else {
      $query = "UPDATE permintaan_farmasi SET status_permintaan='1' WHERE id = ?";

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
}
