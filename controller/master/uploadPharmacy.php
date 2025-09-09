<?php
include "../../database/connect.php";

$uploadDir = "../../uploads/pharmacy/";
if (!is_dir($uploadDir)) {
   mkdir($uploadDir, 0777, true);
}

$response = ["status" => "error", "message" => ""];

$allowedFiles = [
   "gambar"  => "gambar",
   "dokumen"   => "dokumen"
];

// Ambil pharmacy_number dari POST atau URL param
$pharmacy_number = $_POST['pharmacy_number']
   ?? ($_GET['pharmacy_number'] ?? null);

if (!$pharmacy_number) {
   echo json_encode([
      "status" => "error",
      "message" => "Nomor pasien tidak ditemukan."
   ]);
   exit;
}

$uploadedFiles = [];

foreach ($allowedFiles as $field => $prefix) {
   if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
      $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
      $filename = $prefix . "_" . $pharmacy_number . "." . $ext;
      $destination = $uploadDir . $filename;

      if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
         $uploadedFiles[$field] = $filename;
      }
   }
}

// Simpan info file ke database
if (!empty($uploadedFiles)) {
   $stmt = $koneksi->prepare("UPDATE ms_pharmacy 
      SET pharmacy_image=?, pharmacy_docs=?
      WHERE pharmacy_number=?
   ");
   $gambar  = $uploadedFiles['gambar']  ?? null;
   $dokumen   = $uploadedFiles['dokumen']   ?? null;

   $stmt->bind_param("sss", $gambar, $dokumen,  $pharmacy_number);

   if ($stmt->execute()) {
      $response = [
         "status" => "success",
         "message" => "Dokumen berhasil diupload.",
         "files" => $uploadedFiles
      ];
   } else {
      $response["message"] = "Gagal menyimpan data ke database: " . $stmt->error;
   }

   $stmt->close();
} else {
   $response["message"] = "Tidak ada file yang diupload.";
}

echo json_encode($response);
