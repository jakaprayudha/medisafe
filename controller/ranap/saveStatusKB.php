
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
   "faskes_kode",
   "kode_keluarga",
   "nama_suami",
   "pendidikan_suami",
   "pekerjaan_suami",
   "anak_lk",
   "anak_pr",
   "umur_anak_terakhir",
   "kb_terakhir",
   "haid_terakhir",
   "hamil",
   "gpa_g",
   "gpa_p",
   "gpa_a",
   "menyusui",
   "riwayat_sakit",
   "keadaan_umum",
   "berat_badan",
   "tekanan_darah",
   "pemeriksaan_tambahan",
   "metode_pilihan",
   "tgl_dilayani",
   "tgl_dicabut",
   "penanggung_jawab"
];

foreach ($fields as $f)
   $$f = $data[$f] ?? "";

// CEK SUDAH ADA
$cek = mysqli_query($koneksi, "
   SELECT id_kb_status FROM visit_kb_status 
   WHERE visit_ID='$no' AND nomor_rm='$rm'
");

if (mysqli_num_rows($cek) > 0) {
   // UPDATE
   $sql = "UPDATE visit_kb_status SET 
         faskes_kode='$faskes_kode',
         kode_keluarga='$kode_keluarga',
         nama_suami='$nama_suami',
         pendidikan_suami='$pendidikan_suami',
         pekerjaan_suami='$pekerjaan_suami',
         anak_lk='$anak_lk',
         anak_pr='$anak_pr',
         umur_anak_terakhir='$umur_anak_terakhir',
         kb_terakhir='$kb_terakhir',
         haid_terakhir='$haid_terakhir',
         hamil='$hamil',
         gpa_g='$gpa_g',
         gpa_p='$gpa_p',
         gpa_a='$gpa_a',
         menyusui='$menyusui',
         riwayat_sakit='$riwayat_sakit',
         keadaan_umum='$keadaan_umum',
         berat_badan='$berat_badan',
         tekanan_darah='$tekanan_darah',
         pemeriksaan_tambahan='$pemeriksaan_tambahan',
         metode_pilihan='$metode_pilihan',
         tgl_dilayani='$tgl_dilayani',
         tgl_dicabut='$tgl_dicabut',
         penanggung_jawab='$penanggung_jawab'
      WHERE visit_ID='$no' AND nomor_rm='$rm'
   ";
   $msg = "Data berhasil diperbarui";
} else {
   // INSERT
   $sql = "INSERT INTO visit_kb_status (
        faskes_kode,
        kode_keluarga,
        nama_suami,
        pendidikan_suami,
        pekerjaan_suami,
        anak_lk,
        anak_pr,
        umur_anak_terakhir,
        kb_terakhir,
        haid_terakhir,  
        hamil,
        gpa_g,
        gpa_p,
        gpa_a,
        menyusui,
        riwayat_sakit,  
        keadaan_umum,
        berat_badan,
        tekanan_darah,
        pemeriksaan_tambahan,
        metode_pilihan,
        tgl_dilayani,
        tgl_dicabut,
        penanggung_jawab
      )
      VALUES (
         '$faskes_kode',
         '$kode_keluarga',
         '$nama_suami',
         '$pendidikan_suami',
         '$pekerjaan_suami',
         '$anak_lk',
         '$anak_pr',
         '$umur_anak_terakhir',
         '$kb_terakhir',
         '$haid_terakhir',
         '$hamil',
         '$gpa_g',
         '$gpa_p',
         '$gpa_a',
         '$menyusui',
         '$riwayat_sakit',
         '$keadaan_umum',
         '$berat_badan',
         '$tekanan_darah',
         '$pemeriksaan_tambahan',
         '$metode_pilihan',
         '$tgl_dilayani',
         '$tgl_dicabut',
         '$penanggung_jawab'
      );
   ";
   $msg = "Data berhasil disimpan";
}

if (mysqli_query($koneksi, $sql)) {
   echo json_encode(["status" => "success", "message" => $msg]);
} else {
   echo json_encode(["status" => "error", "message" => mysqli_error($koneksi)]);
}
