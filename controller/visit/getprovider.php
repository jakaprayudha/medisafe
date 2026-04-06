
<?php
include '../../database/connect.php';

$id_customer = $_SESSION['id_customer'];
$query = "SELECT id_provider, provider_name FROM ms_provider WHERE id_customer='$id_customer' ORDER BY provider_name ASC";
$result = mysqli_query($koneksi, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
   $data[] = $row;
}

echo json_encode($data);
