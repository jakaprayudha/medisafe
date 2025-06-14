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
   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}



// Function untuk Read User
function getTarif()
{
   global $koneksi;

   // Ambil parameter pagination dan pencarian dari request
   $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
   $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
   $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

   $rm = isset($_GET['rm']) ? $_GET['rm'] : '';

   // Base query
   $query = "SELECT * FROM pasien_visit WHERE 1=1";

   // Tambahkan filter berdasarkan nomor_rm dan nomor_visit
   if (!empty($rm)) {
      $query .= " AND nomor_rm = '" . mysqli_real_escape_string($koneksi, $rm) . "'";
   }

   // Tambahkan filter pencarian jika ada
   if (!empty($search)) {
      $query .= " AND dokter LIKE '%" . mysqli_real_escape_string($koneksi, $search) . "%'";
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
