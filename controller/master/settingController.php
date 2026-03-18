<?php
require '../../database/connect.php';

header("Content-Type: application/json");

// 🔐 VALIDASI SESSION
if (!isset($_SESSION['id_customer'])) {
   echo json_encode([
      "status" => "error",
      "message" => "Session tidak valid"
   ]);
   exit;
}

$id_customer = $_SESSION['id_customer'];
$method = $_SERVER['REQUEST_METHOD'];


// ================= GET =================
if ($method === 'GET') {

   $stmt = $koneksi->prepare(
      "SELECT * FROM setting_clinic 
       WHERE id_customer=? 
       LIMIT 1"
   );

   $stmt->bind_param("i", $id_customer);
   $stmt->execute();

   $result = $stmt->get_result();
   $data = $result->fetch_assoc();

   if ($data) {
      echo json_encode([
         "status" => "success",
         "user" => $data
      ]);
   } else {
      echo json_encode([
         "status" => "error",
         "message" => "Data tidak ditemukan"
      ]);
   }

   $stmt->close();
   exit;
}


// ================= UPDATE ONLY =================
if ($method === 'POST') {

   $namaBisnis = trim($_POST['clinic_name'] ?? '');
   $telepon    = trim($_POST['telepon'] ?? '');
   $alamat     = trim($_POST['alamat'] ?? '');

   if (!$namaBisnis || !$telepon || !$alamat) {
      echo json_encode([
         "status" => "error",
         "message" => "Semua field wajib diisi!"
      ]);
      exit;
   }

   // 🔥 UPDATE WAJIB PAKAI WHERE
   $stmt = $koneksi->prepare(
      "UPDATE setting_clinic 
       SET clinic_name=?, phone_number=?, address=? 
       WHERE id_customer=? LIMIT 1"
   );

   if (!$stmt) {
      echo json_encode([
         "status" => "error",
         "message" => "Prepare gagal"
      ]);
      exit;
   }

   $stmt->bind_param("sssi", $namaBisnis, $telepon, $alamat, $id_customer);

   if ($stmt->execute()) {

      // 🔍 cek apakah benar ada row yg kena update
      if ($stmt->affected_rows > 0) {
         echo json_encode([
            "status" => "success",
            "message" => "Data berhasil diperbarui"
         ]);
      } else {
         echo json_encode([
            "status" => "error",
            "message" => "Data tidak ditemukan / belum dibuat"
         ]);
      }
   } else {
      echo json_encode([
         "status" => "error",
         "message" => "Gagal update"
      ]);
   }

   $stmt->close();
   exit;
}


// ================= INVALID =================
echo json_encode([
   "status" => "error",
   "message" => "Invalid request"
]);
