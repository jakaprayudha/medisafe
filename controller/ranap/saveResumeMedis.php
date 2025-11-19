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
   "tindakan",
   "diagnosa",
   "pemeriksaan_penunjang",
   "obat",
   "instruksi",
   "petugas",
   "dokter"
];

foreach ($fields as $f)
   $$f = $data[$f] ?? "";

// CEK SUDAH ADA
$cek = mysqli_query($koneksi, "
   SELECT id_resume FROM resume_medis 
   WHERE visit_ID='$no' AND nomor_rm='$rm'
");

if (mysqli_num_rows($cek) > 0) {
   // UPDATE
   $sql = "
      UPDATE resume_medis SET
         tindakan='$tindakan',
         diagnosa='$diagnosa',
         pemeriksaan_penunjang='$pemeriksaan_penunjang',
         obat='$obat',
         instruksi='$instruksi',
         petugas='$petugas',
         dokter='$dokter'
      WHERE visit_ID='$no' AND nomor_rm='$rm'
   ";
   $msg = "Data berhasil diperbarui";
} else {
   // INSERT
   $sql = "INSERT INTO resume_medis (
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
