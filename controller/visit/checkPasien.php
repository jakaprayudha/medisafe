<?php
require '../../database/connect.php';
header('Content-Type: application/json');

$q = @$_GET['q'];
$checkdata = mysqli_query($koneksi, "SELECT * FROM ms_pasien WHERE nomor_rm like '%$q%' OR nama_pasien like '%$q%'");

$searchResults = [];
while ($row = mysqli_fetch_assoc($checkdata)) {
   $searchResults[] = $row;
}

echo json_encode(['items' => $searchResults]);

exit;
