<?php
session_start();
include '../../database/connect.php';
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
   case 'GET':
      getData();
      break;
   case 'PUT':
      // Update User
      updateStatus();
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
function updateStatus()
{
   global $koneksi;
   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_ranap']) || empty($_PUT['status'])) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Parameter tidak lengkap'
      ]);
      return;
   }

   $id     = $_PUT['id_ranap'];
   $id_bed = $_PUT['id_bed'] ?? null;
   $status = $_PUT['status'];

   $koneksi->begin_transaction();

   try {

      // 🔹 Ambil bed lama
      $getRanap = $koneksi->prepare("SELECT id_bed FROM permintaan_ranap WHERE id_ranap=?");
      $getRanap->bind_param("i", $id);
      $getRanap->execute();
      $resultRanap = $getRanap->get_result();
      $dataRanap = $resultRanap->fetch_assoc();
      $getRanap->close();

      $old_bed = $dataRanap['id_bed'] ?? null;

      // 🔹 Update status ranap
      $stmt = $koneksi->prepare("UPDATE permintaan_ranap SET status=? WHERE id_ranap=?");
      $stmt->bind_param("si", $status, $id);

      if (!$stmt->execute()) {
         throw new Exception($stmt->error);
      }
      $stmt->close();

      // ============================
      // 🔴 STATUS: PULANG
      // ============================
      if ($status === 'pulang') {

         if (!empty($old_bed)) {
            $stmtBed = $koneksi->prepare("UPDATE ms_room_bed SET bed_status=0 WHERE id_bed=?");
            $stmtBed->bind_param("i", $old_bed);

            if (!$stmtBed->execute()) {
               throw new Exception($stmtBed->error);
            }
            $stmtBed->close();
         }
      }

      // ============================
      // 🟢 STATUS: AKTIF
      // ============================
      else if ($status === 'aktif') {

         // 🔥 fallback ke bed lama kalau frontend tidak kirim
         if (empty($id_bed)) {
            $id_bed = $old_bed;
         }

         if (empty($id_bed)) {
            throw new Exception("Bed tidak tersedia, silakan pilih bed");
         }

         // 🔒 cek bed tersedia
         $cekBed = $koneksi->prepare("SELECT bed_status FROM ms_room_bed WHERE id_bed=?");
         $cekBed->bind_param("i", $id_bed);
         $cekBed->execute();
         $result = $cekBed->get_result();
         $dataBed = $result->fetch_assoc();
         $cekBed->close();

         if (!$dataBed) {
            throw new Exception("Bed tidak ditemukan");
         }

         if ($dataBed['bed_status'] == 1) {
            throw new Exception("Bed sudah terisi pasien lain");
         }

         // 🔹 kosongkan bed lama kalau beda
         if (!empty($old_bed) && $old_bed != $id_bed) {
            $stmtOld = $koneksi->prepare("UPDATE ms_room_bed SET bed_status=0 WHERE id_bed=?");
            $stmtOld->bind_param("i", $old_bed);

            if (!$stmtOld->execute()) {
               throw new Exception($stmtOld->error);
            }
            $stmtOld->close();
         }

         // 🔹 isi bed baru
         $stmtNew = $koneksi->prepare("UPDATE ms_room_bed SET bed_status=1 WHERE id_bed=?");
         $stmtNew->bind_param("i", $id_bed);

         if (!$stmtNew->execute()) {
            throw new Exception($stmtNew->error);
         }
         $stmtNew->close();

         // 🔹 update relasi ranap
         $stmtUpdate = $koneksi->prepare("UPDATE permintaan_ranap SET id_bed=? WHERE id_ranap=?");
         $stmtUpdate->bind_param("ii", $id_bed, $id);

         if (!$stmtUpdate->execute()) {
            throw new Exception($stmtUpdate->error);
         }
         $stmtUpdate->close();
      }

      $koneksi->commit();

      echo json_encode([
         'status' => 'success',
         'message' => 'Status & bed berhasil diupdate'
      ]);
   } catch (Exception $e) {

      $koneksi->rollback();

      echo json_encode([
         'status' => 'error',
         'message' => $e->getMessage()
      ]);
   }
}
