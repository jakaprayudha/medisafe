<?php
include "../../database/connect.php";

$rm = $_GET['rm'];
$visit = $_GET['no'];

$q = mysqli_query($koneksi, "SELECT foto_path FROM pasien_dokumen WHERE nomor_rm='$rm' AND visit_ID='$visit' AND jenis_dokumen='FOTO_PASIEN'");
$r = mysqli_fetch_assoc($q);

echo json_encode([
   "foto" => $r ? $r['foto_path'] : null
]);
