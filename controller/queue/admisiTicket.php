<?php
header('Content-Type: application/json');
include '../../database/connect.php';

date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');

$input = json_decode(file_get_contents('php://input'), true);

$jenis = strtoupper(trim($input['jenis_layanan'] ?? '')); // BPJS / UMUM
$nama  = trim($input['nama_pasien'] ?? '');
$idPoli = (int)($input['id_poli'] ?? 0);

if (!$jenis || !$nama || !$idPoli) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data tidak lengkap'
   ]);
   exit;
}

/* =========================
   PREFIX NOMOR ANTRIAN
========================= */
if ($jenis === 'BPJS') {
   $prefix = 'B';
} elseif ($jenis === 'UMUM') {
   $prefix = 'U';
} else {
   echo json_encode([
      'status' => 'error',
      'message' => 'Jenis layanan tidak valid'
   ]);
   exit;
}

/* =========================
   GENERATE NOMOR ANTRIAN
   (PER HARI + PER JENIS)
========================= */
$q = mysqli_query($koneksi, "
   SELECT COUNT(*) AS total
   FROM transaction_queue
   WHERE queue_date = '$today'
     AND no_antrian LIKE '$prefix-%'
");

$row = mysqli_fetch_assoc($q);
$urut = (int)$row['total'] + 1;

$noAntrian = $prefix . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

/* =========================
   INSERT DATA ANTRIAN
========================= */
$insert = mysqli_query($koneksi, "
   INSERT INTO transaction_queue
   (
      no_antrian,
      nama_pasien,
      id_poli,
      status,
      queue_date
   )
   VALUES
   (
      '$noAntrian',
      '$nama',
      $idPoli,
      'menunggu',
      '$today'
   )
");

if (!$insert) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Gagal menyimpan antrean'
   ]);
   exit;
}

echo json_encode([
   'status' => 'success',
   'no_antrian' => $noAntrian,
   'nama_pasien' => $nama,
   'poli' => $idPoli
]);