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

$diagnosa_utama = $data['diagnosa_utama'] ?? "";
$diagnosa_sekunder = $data['diagnosa_sekunder'] ?? "";
$diagnosa_utama_text = $data['diagnosa_utama_text'] ?? "";
$diagnosa_sekunder_text = $data['diagnosa_sekunder_text'] ?? "";
$pemeriksaan_fisik = $data['pemeriksaan_fisik'] ?? "";
$pemeriksaan_penunjang = $data['pemeriksaan_penunjang'] ?? "";
$alergi_obat = $data['alergi_obat'] ?? "";
$instruksi = $data['instruksi'] ?? "";
$kondisi_pulang = $data['kondisi_pulang'] ?? "";
$cara_keluar = $data['cara_keluar'] ?? "";
$rencana_tindak_lanjut = $data['rencana_tindak_lanjut'] ?? "";
$petugas = $data['petugas'] ?? "";
$dokter = $data['dokter'] ?? "";

function normalize_codes($value)
{
   if (!$value) {
      return [];
   }

   $parts = preg_split('/[;,]+/', $value);
   $out = [];

   foreach ($parts as $part) {
      $code = trim($part);
      if ($code !== '') {
         $out[] = $code;
      }
   }

   return $out;
}

function build_icd_text($koneksi, $codes)
{
   if (!$codes) {
      return "";
   }

   $escaped = array_map(function ($code) use ($koneksi) {
      return "'" . mysqli_real_escape_string($koneksi, $code) . "'";
   }, $codes);

   $sql = "SELECT code, icd10 FROM icd_10 WHERE code IN (" . implode(',', $escaped) . ")";
   $result = mysqli_query($koneksi, $sql);

   if (!$result) {
      return "";
   }

   $map = [];
   while ($row = mysqli_fetch_assoc($result)) {
      $map[$row['code']] = $row['code'] . ' - ' . $row['icd10'];
   }

   $texts = [];
   foreach ($codes as $code) {
      if (isset($map[$code])) {
         $texts[] = $map[$code];
      }
   }

   return implode('; ', $texts);
}

$tindakan_input = $data['tindakan'] ?? "";
$obat_input = $data['obat'] ?? "";

if ($diagnosa_utama_text === '' && $diagnosa_utama !== '') {
   $diagnosa_utama_text = build_icd_text($koneksi, normalize_codes($diagnosa_utama));
}

if ($diagnosa_sekunder_text === '' && $diagnosa_sekunder !== '') {
   $diagnosa_sekunder_text = build_icd_text($koneksi, normalize_codes($diagnosa_sekunder));
}

if ($pemeriksaan_fisik === '' && $tindakan_input !== '') {
   $pemeriksaan_fisik = $tindakan_input;
}

if ($alergi_obat === '' && $obat_input !== '') {
   $alergi_obat = $obat_input;
}

// Simpan ringkasan diagnosa ke resume_medis
$diagnosa = trim($diagnosa_utama_text . ($diagnosa_sekunder_text ? " | " . $diagnosa_sekunder_text : ""));
if ($diagnosa === '') {
   $diagnosa = $data['diagnosa'] ?? '';
}
$tindakan = $pemeriksaan_fisik;
$obat = $alergi_obat;

// CEK SUDAH ADA
$cek = mysqli_query($koneksi, "
   SELECT id_resume FROM resume_medis 
   WHERE visit_ID='$no' AND nomor_rm='$rm'
");

mysqli_begin_transaction($koneksi);

try {
   if (mysqli_num_rows($cek) > 0) {
      // UPDATE resume_medis
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
      // INSERT resume_medis
      $sql = "INSERT INTO resume_medis (
            visit_ID, nomor_rm, diagnosa, tindakan, pemeriksaan_penunjang, obat, instruksi, petugas, dokter
         )
         VALUES (
            '$no', '$rm', '$diagnosa', '$tindakan', '$pemeriksaan_penunjang', '$obat', '$instruksi', '$petugas', '$dokter'
         );
      ";
      $msg = "Data berhasil disimpan";
   }

   if (!mysqli_query($koneksi, $sql)) {
      throw new Exception(mysqli_error($koneksi));
   }

   // UPDATE pasien_visit untuk field UI lainnya
   $sqlVisit = "
      UPDATE pasien_visit SET
         diagnosa_utama='$diagnosa_utama_text',
         diagnosa_sekunder='$diagnosa_sekunder_text',
         pemeriksaan_fisik='$pemeriksaan_fisik',
         alergi_obat='$alergi_obat',
         kondisi_pulang='$kondisi_pulang',
         cara_keluar='$cara_keluar',
         rencana_tindak_lanjut='$rencana_tindak_lanjut'
      WHERE visit_ID='$no'
   ";

   if (!mysqli_query($koneksi, $sqlVisit)) {
      throw new Exception(mysqli_error($koneksi));
   }

   mysqli_commit($koneksi);
   echo json_encode(["status" => "success", "message" => $msg]);
} catch (Exception $e) {
   mysqli_rollback($koneksi);
   echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
