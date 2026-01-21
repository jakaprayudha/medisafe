<?php
include "../../database/connect.php";

$uploadDir = "../../uploads/patient/";
if (!is_dir($uploadDir)) {
   mkdir($uploadDir, 0777, true);
}

$response = ["status" => "error", "message" => ""];

$allowedFiles = [
   "ktp"  => "ktp",
   "kk"   => "kk",
   "bpjs" => "bpjs",
   "foto" => "foto"
];

// Ambil patient_number dari POST atau URL param
$patient_number = $_POST['patient_number']
   ?? ($_GET['patient_number'] ?? null);

if (!$patient_number) {
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
      $filename = $prefix . "_" . $patient_number . "." . $ext;
      $destination = $uploadDir . $filename;

      if (move_uploaded_file($_FILES[$field]['tmp_name'], $destination)) {
         $uploadedFiles[$field] = $filename;
      }
   }
}

// Simpan info file ke database
if (!empty($uploadedFiles)) {
   $stmt = $koneksi->prepare("
      UPDATE ms_patient 
      SET patient_ktp_file=?, patient_kk_file=?, patient_bpjs_file=?, patient_foto=? 
      WHERE patient_number=?
   ");
   $ktp  = $uploadedFiles['ktp']  ?? null;
   $kk   = $uploadedFiles['kk']   ?? null;
   $bpjs = $uploadedFiles['bpjs'] ?? null;
   $foto = $uploadedFiles['foto'] ?? null;

   $stmt->bind_param("sssss", $ktp, $kk, $bpjs, $foto, $patient_number);

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
