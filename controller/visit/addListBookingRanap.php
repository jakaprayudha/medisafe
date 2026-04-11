<?php
session_start();
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
   case 'PUT':
      // Update User
      updateData();
      break;

   case 'DELETE':
      // Delete User
      deleteData();
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
   $id_customer = $_SESSION['id_customer'];
   $query = "SELECT * FROM permintaan_ranap rn LEFT JOIN ms_patient mp ON rn.id_patient = mp.id_patient LEFT JOIN pasien_visit pv ON rn.visit_ID_inpatient = pv.visit_ID WHERE rn.ranap_booking=0 AND pv.id_customer = '$id_customer'  ORDER BY rn.id_ranap DESC";
   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);
      return;
   }

   // Ambil semua data dalam bentuk array asosiatif
   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

   // Tutup hasil query
   mysqli_free_result($result);

   // Kirimkan data dalam format JSON
   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}

// Function untuk Read User berdasarkan ID
function  getID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM permintaan_ranap WHERE id_ranap = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $iduser); // Bind parameter iduser
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
// 🔹 UPDATE VISIT
function updateData()
{
   global $koneksi;
   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_visit'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id         = $_PUT['id_visit'];
   $id_doctor  = $_PUT['id_doctor'] ?? null;
   $id_poli    = $_PUT['id_poli'] ?? null;
   $notes      = $_PUT['visit_notes'] ?? null;

   $query = "UPDATE pasien_visit SET id_doctor=?, id_poli=?, visit_notes=? WHERE id_visit=?";
   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("sssi", $id_doctor, $id_poli, $notes, $id);

      if ($stmt->execute()) {
         echo json_encode(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Update gagal: ' . $stmt->error]);
      }
      $stmt->close();
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $koneksi->error]);
   }
}

// 🔹 DELETE VISIT
function deleteData()
{
   global $koneksi;
   $id = $_GET['id'] ?? null;

   if (!$id) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $query = "DELETE FROM pasien_visit WHERE id_visit=?";
   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("i", $id);

      if ($stmt->execute()) {
         echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus.']);
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus: ' . $stmt->error]);
      }
      $stmt->close();
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $koneksi->error]);
   }
}
