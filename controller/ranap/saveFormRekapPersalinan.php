<?php
require '../../database/connect.php';
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
   echo json_encode(["status" => "error", "message" => "Invalid input"]);
   exit;
}

$no = $data['visit_ID'];
$rm = $data['nomor_rm'];

if (!$no || !$rm) {
   echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
   exit;
}

$fields = [
   "gravid",
   "abortus",
   "jenis_persalinan",
   "partus"
];

foreach ($fields as $f)
   $$f = $data[$f] ?? "";

// CEK SUDAH ADA
$cek = mysqli_query($koneksi, "
   SELECT id_persalinan FROM visit_persalinan 
   WHERE visit_ID='$no' AND nomor_rm='$rm'
");

if (mysqli_num_rows($cek) > 0) {
   // UPDATE
   $sql = "
      UPDATE visit_persalinan SET
         gravid='$gravid',
         abortus='$abortus',
         jenis_persalinan='$jenis_persalinan',
         partus='$partus'
      WHERE visit_ID='$no' AND nomor_rm='$rm'
   ";
   $msg = "Data berhasil diperbarui";
} else {
   // INSERT
   $sql = "INSERT INTO visit_persalinan (
         visit_ID, nomor_rm, gravid, abortus, jenis_persalinan, partus
      )
      VALUES (
         '$no', '$rm', '$gravid', '$abortus', '$jenis_persalinan', '$partus'
      );
   ";
   $msg = "Data berhasil disimpan";
}

if (mysqli_query($koneksi, $sql)) {
   echo json_encode(["status" => "success", "message" => $msg]);
} else {
   echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
