
<?php
include '../../database/connect.php';

$query = "SELECT id_poli, poli_name FROM ms_poli ORDER BY poli_name ASC";
$result = mysqli_query($koneksi, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
   $data[] = $row;
}

echo json_encode($data);
