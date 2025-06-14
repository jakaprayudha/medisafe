<?php
// Sertakan file koneksi database
include '../../database/connect.php';

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

// Handle request berdasarkan method (POST, GET, PUT, DELETE)
switch ($method) {
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
   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create User

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
   $query = "SELECT 
             permintaan_farmasi.id AS pf_id, 
             permintaan_farmasi.*, 
             ms_farmasi.kategori_barang 
          FROM permintaan_farmasi 
          INNER JOIN ms_farmasi ON ms_farmasi.nama_barang = permintaan_farmasi.item 
          WHERE 1=1 AND ms_farmasi.kategori_barang = 'Vaksin'";
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
   $catatan = isset($_PUT['catatan']) ? $_PUT['catatan'] : '';

   // Debugging input data
   if (empty($catatan) || empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID dan Catatan Item harus diisi.'
      ]);
      exit;
   }

   // Query untuk update data user
   $query = "UPDATE permintaan_farmasi SET catatan_vaksin = ? WHERE id = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("si", $catatan, $id);
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
