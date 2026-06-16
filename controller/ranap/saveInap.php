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
   "tanggal_masuk",
   "jam_masuk",
   "status_perkawinan",
   "penanggung_jawab",
   "alamat_pj",
   "tanggal_pindah",
   "jam_pindah",
   "ruang_rawat",
   "tanggal_keluar",
   "jam_keluar",
   "diagnosa_medik",
   "lama_dirawat",
   "diagnosa_utama",
   "diagnosa_komplikasi",
   "penyebab_keracunan",
   "nama_operasi",
   "infeksi_nosokomial",
   "penyebab_infeksi",
   "alergi",
   "radioterapi",
   "imunisasi",
   "transfusi",
   "keadaan_keluar",
   "cara_keluar",
   "dokter_merawat"
];

foreach ($fields as $f)
   $$f = $data[$f] ?? "";

// CEK SUDAH ADA
$cek = mysqli_query($koneksi, "
   SELECT id_inap FROM visit_ranap 
   WHERE visit_ID='$no' AND nomor_rm='$rm'
");

if (mysqli_num_rows($cek) > 0) {
   // UPDATE
   $sql = "
      UPDATE visit_ranap SET
         tanggal_masuk='$tanggal_masuk',
         jam_masuk='$jam_masuk',
         status_perkawinan='$status_perkawinan',
         penanggung_jawab='$penanggung_jawab',
         alamat_pj='$alamat_pj',
         tanggal_pindah='$tanggal_pindah',
         jam_pindah='$jam_pindah',
         ruang_rawat='$ruang_rawat',
         tanggal_keluar='$tanggal_keluar',
         jam_keluar='$jam_keluar',
         diagnosa_medik='$diagnosa_medik',
         lama_dirawat='$lama_dirawat',
         diagnosa_utama='$diagnosa_utama',
         diagnosa_komplikasi='$diagnosa_komplikasi',
         penyebab_keracunan='$penyebab_keracunan',
         nama_operasi='$nama_operasi',
         infeksi_nosokomial='$infeksi_nosokomial',
         penyebab_infeksi='$penyebab_infeksi',
         alergi='$alergi',
         radioterapi='$radioterapi',
         imunisasi='$imunisasi',
         transfusi='$transfusi',
         keadaan_keluar='$keadaan_keluar',
         cara_keluar='$cara_keluar',
         dokter_merawat='$dokter_merawat'
      WHERE visit_ID='$no' AND nomor_rm='$rm'
   ";
   $msg = "Data berhasil diperbarui";
} else {
   // INSERT
   $sql = "
      INSERT INTO visit_ranap (
         visit_ID, nomor_rm, tanggal_masuk, jam_masuk, status_perkawinan, 
         penanggung_jawab, alamat_pj, tanggal_pindah, jam_pindah, ruang_rawat,
         tanggal_keluar, jam_keluar, diagnosa_medik, lama_dirawat, diagnosa_utama,
         diagnosa_komplikasi, penyebab_keracunan, nama_operasi, infeksi_nosokomial,
         penyebab_infeksi, alergi, radioterapi, imunisasi, transfusi, keadaan_keluar,
         cara_keluar, dokter_merawat
      )
      VALUES (
         '$no','$rm','$tanggal_masuk','$jam_masuk','$status_perkawinan',
         '$penanggung_jawab','$alamat_pj','$tanggal_pindah','$jam_pindah','$ruang_rawat',
         '$tanggal_keluar','$jam_keluar','$diagnosa_medik','$lama_dirawat','$diagnosa_utama',
         '$diagnosa_komplikasi','$penyebab_keracunan','$nama_operasi','$infeksi_nosokomial',
         '$penyebab_infeksi','$alergi','$radioterapi','$imunisasi','$transfusi',
         '$keadaan_keluar','$cara_keluar','$dokter_merawat'
      );
   ";
   $msg = "Data berhasil disimpan";
}

if (mysqli_query($koneksi, $sql)) {
   echo json_encode(["status" => "success", "message" => $msg]);
} else {
   echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
