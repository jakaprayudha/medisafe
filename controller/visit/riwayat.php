
<?php
session_start();
include '../../database/connect.php';

header('Content-Type: application/json');

// ✅ ambil id_customer dari session
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session id_customer tidak ditemukan'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id']);
      } else {
         getData();
      }
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// =======================
// 🔹 GET DATA (by RM)
// =======================
function getData()
{
   global $koneksi, $id_customer;

   $rm = isset($_GET['rm']) ? mysqli_real_escape_string($koneksi, $_GET['rm']) : '';

   if (!$rm) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Parameter RM wajib diisi'
      ]);
      return;
   }

   $query = "SELECT * 
   FROM pasien_visit 
   LEFT JOIN ms_patient 
      ON ms_patient.id_patient = pasien_visit.id_patient 
   WHERE ms_patient.nomor_rm = '$rm'
   AND pasien_visit.id_customer = '$id_customer'
   AND ms_patient.id_customer = '$id_customer'
   ORDER BY pasien_visit.visit_date ASC";

   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);
      return;
   }

   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
   mysqli_free_result($result);

   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}

// =======================
// 🔹 GET BY ID (billing)
// =======================
function getID($iduser)
{
   global $koneksi, $id_customer;

   $query = "SELECT * 
   FROM pasien_billing 
   WHERE id_billing = ? 
   AND id_customer = ?";

   if ($stmt = $koneksi->prepare($query)) {

      $stmt->bind_param("ss", $iduser, $id_customer);
      $stmt->execute();

      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
         echo json_encode([
            'status' => 'success',
            'data' => $result->fetch_assoc()
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
