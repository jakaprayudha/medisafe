<?php
require '../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
   echo json_encode(["status" => "error", "message" => "Invalid input"]);
   exit;
}

$visit_ID = $data['visit_ID'] ?? null;
$nomor_rm = $data['nomor_rm'] ?? null;
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$visit_ID || !$nomor_rm) {
   echo json_encode(["status" => "error", "message" => "Parameter tidak lengkap"]);
   exit;
}

/* ==========================================
   FIELD TRIASE
========================================== */
$fields = [
   "tanggal_masuk",
   "jam_masuk",
   "keluhan_utama",

   "tekanan_darah",
   "nadi",
   "rr",
   "suhu",
   "spo2",

   "gcs_e",
   "gcs_v",
   "gcs_m",
   "total_gcs",

   "nyeri",

   "triase",
   "referensi_triase",
   "catatan"
];

foreach ($fields as $f) {
   $$f = mysqli_real_escape_string($koneksi, $data[$f] ?? "");
}

/* ==========================================
   CEK APAKAH DATA TRIASE SUDAH ADA
========================================== */
$cek = mysqli_query($koneksi, "SELECT id_triase FROM pasien_triase
   WHERE visit_ID = '$visit_ID'
   AND nomor_rm = '$nomor_rm'
   ORDER BY id_triase DESC
   LIMIT 1
");

if (mysqli_num_rows($cek) > 0) {

   $row = mysqli_fetch_assoc($cek);
   $id_triase = $row['id_triase'];

   $sql = "
      UPDATE pasien_triase SET
         tanggal_masuk = '$tanggal_masuk',
         jam_masuk = '$jam_masuk',
         keluhan_utama = '$keluhan_utama',

         tekanan_darah = '$tekanan_darah',
         nadi = '$nadi',
         rr = '$rr',
         suhu = '$suhu',
         spo2 = '$spo2',

         gcs_e = '$gcs_e',
         gcs_v = '$gcs_v',
         gcs_m = '$gcs_m',
         gcs_total = '$total_gcs',

         skala_nyeri = '$nyeri',

         triase = '$triase',
         referensi_triase = '$referensi_triase',
         catatan = '$catatan',
         id_customer = '$id_customer',
         updated_at = NOW()
      WHERE id_triase = '$id_triase'";

   $msg = "Data triase berhasil diperbarui";
} else {

   // INSERT BARU
   $sql = "INSERT INTO pasien_triase (
         visit_ID, nomor_rm,
         tanggal_masuk, jam_masuk, keluhan_utama,
         tekanan_darah, nadi, rr, suhu, spo2,
         gcs_e, gcs_v, gcs_m, gcs_total,
         skala_nyeri,
         triase, referensi_triase,
         catatan, id_customer
      ) VALUES (
         '$visit_ID', '$nomor_rm',
         '$tanggal_masuk', '$jam_masuk', '$keluhan_utama',
         '$tekanan_darah', '$nadi', '$rr', '$suhu', '$spo2',
         '$gcs_e', '$gcs_v', '$gcs_m', '$total_gcs',
         '$nyeri',
         '$triase', '$referensi_triase',
         '$catatan', '$id_customer'
      )
   ";

   $msg = "Data triase berhasil disimpan";
}

if (mysqli_query($koneksi, $sql)) {
   echo json_encode(["status" => "success", "message" => $msg]);
} else {
   echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
