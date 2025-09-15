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
   $item = $_POST['item'] ?? '';
   $checkharga = mysqli_query($koneksi, "SELECT * FROM ms_tarif WHERE nama_tarif='$item'");
   $dataharga = mysqli_fetch_array($checkharga);
   $hargaitem = $dataharga['tarif'];
   $kategori = $dataharga['keterangan'];
   $diskon = $_POST['diskon'] ?? '';
   $jumlah = $_POST['qty'] ?? '';
   $catatan = $_POST['catatan'] ?? '';

   if (empty($item)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Nama Tarif harus diisi.'
      ]);
      exit;
   }


   // Query insert data
   $query = "INSERT INTO pasien_billing (nomor_rm, nomor_visit, item, harga, qty, diskon, kategori_item, catatan_billing) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("sssiiiss", $nomor_rm, $nomor_visit, $item, $hargaitem, $jumlah, $diskon, $kategori, $catatan);
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

   // Base query
   $query = "SELECT * FROM pasien_billing WHERE 1=1";

   if (!empty($no)) {
      $query .= " AND id_visit = '" . mysqli_real_escape_string($koneksi, $no) . "'";
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
   $query = "SELECT * FROM pasien_billing WHERE id = ?";

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
   $diskon = isset($_PUT['diskon']) ? $_PUT['diskon'] : '';

   // Debugging input data
   if (empty($diskon) || empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID dan Diskon Item harus diisi.'
      ]);
      exit;
   }

   // Query untuk update data user
   $query = "UPDATE pasien_billing SET diskon = ? WHERE id = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("ii", $diskon, $id);
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

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      exit;
   }

   // Query untuk menghapus data user
   $query = "DELETE FROM pasien_billing WHERE id = ?";

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
