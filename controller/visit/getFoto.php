<?php
include "../../database/connect.php";

$rm = $_GET['rm'];
$visit = $_GET['visit'];

$q = mysqli_query($koneksi, "SELECT foto_path FROM capture_patient WHERE rm='$rm' AND visit='$visit'");
$r = mysqli_fetch_assoc($q);

echo json_encode([
   "foto" => $r ? $r['foto_path'] : null
]);
