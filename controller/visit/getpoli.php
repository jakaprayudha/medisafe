
<?php
include '../../database/connect.php';

$query = "SELECT kdPoli, nmPoli FROM master_poli ORDER BY nmPoli ASC";
$result = mysqli_query($koneksi, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
   $data[] = $row;
}

echo json_encode($data);
