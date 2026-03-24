
<?php
include '../../database/connect.php';

$query = "SELECT id_provider, provider_name FROM ms_provider ORDER BY provider_name ASC";
$result = mysqli_query($koneksi, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
   $data[] = $row;
}

echo json_encode($data);
