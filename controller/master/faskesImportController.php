<?php
include '../../database/connect.php';

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

function getData()
{
   global $koneksi;

   // 🔥 QUERY SUDAH DIGABUNG (ADA COUNT)
   $query = "
      SELECT 
         f.*,

         (SELECT COUNT(*) FROM ms_patient p WHERE p.id_customer = f.id_faskes) AS total_pasien,
         (SELECT COUNT(*) FROM ms_doctor d WHERE d.id_customer = f.id_faskes) AS total_dokter,
         (SELECT COUNT(*) FROM ms_pharmacy fa WHERE fa.id_customer = f.id_faskes) AS total_farmasi,
         (SELECT COUNT(*) FROM pasien_visit v WHERE v.id_customer = f.id_faskes) AS total_visit

      FROM ms_faskes f
      WHERE f.faskes_status != 99
      ORDER BY f.faskes_name DESC
   ";

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

   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}

// 🔹 DETAIL BY ID (optional bisa ikut count juga kalau mau)
function getID($iduser)
{
   global $koneksi;

   $query = "
      SELECT 
         f.*,

         (SELECT COUNT(*) FROM pasien p WHERE p.id_faskes = f.id_faskes) AS total_pasien,
         (SELECT COUNT(*) FROM dokter d WHERE d.id_faskes = f.id_faskes) AS total_dokter,
         (SELECT COUNT(*) FROM farmasi fa WHERE fa.id_faskes = f.id_faskes) AS total_farmasi,
         (SELECT COUNT(*) FROM visit v WHERE v.id_faskes = f.id_faskes) AS total_visit

      FROM ms_faskes f
      WHERE f.id_faskes = ?
   ";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("i", $iduser);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
         $data = $result->fetch_assoc();
         echo json_encode([
            'status' => 'success',
            'data' => $data
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
