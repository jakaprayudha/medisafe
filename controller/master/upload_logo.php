<?php
require '../../database/connect.php'; // Koneksi ke database

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   if (!isset($_FILES['logo']) || $_FILES['logo']['error'] != UPLOAD_ERR_OK) {
      echo json_encode(["status" => "error", "message" => "Pilih file yang valid!"]);
      exit;
   }

   $file = $_FILES['logo'];
   $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
   $maxSize = 2 * 1024 * 1024; // 2MB
   $uploadDir = "../../uploads/";

   $fileName = basename($file['name']);
   $fileSize = $file['size'];
   $fileTmpName = $file['tmp_name'];
   $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

   // Validasi ekstensi file
   if (!in_array($fileExt, $allowedExtensions)) {
      echo json_encode(["status" => "error", "message" => "Hanya file JPG, PNG, GIF yang diperbolehkan."]);
      exit;
   }

   // Validasi ukuran file
   if ($fileSize > $maxSize) {
      echo json_encode(["status" => "error", "message" => "Ukuran file maksimal 2MB."]);
      exit;
   }

   // Generate nama unik
   $newFileName = uniqid("logo_", true) . "." . $fileExt;
   $targetFilePath = $uploadDir . $newFileName;

   // Pindahkan file ke folder uploads
   if (move_uploaded_file($fileTmpName, $targetFilePath)) {
      // Simpan path file ke database
      $query = "UPDATE setting_clinic SET image_clinic = ? LIMIT 1";
      $stmt = $koneksi->prepare($query);
      $stmt->bind_param("s", $newFileName);

      if ($stmt->execute()) {
         echo json_encode(["status" => "success", "message" => "Logo berhasil diupload.", "file" => $newFileName]);
      } else {
         echo json_encode(["status" => "error", "message" => "Gagal menyimpan ke database."]);
      }

      $stmt->close();
   } else {
      echo json_encode(["status" => "error", "message" => "Gagal mengunggah file."]);
   }
   exit;
}

echo json_encode(["status" => "error", "message" => "Invalid request."]);
