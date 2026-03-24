
<?php
include '../../database/connect.php';

$query = "SELECT id_doctor, doctor_name FROM ms_doctor ORDER BY doctor_name ASC";
$result = mysqli_query($koneksi, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
   $data[] = $row;
}

echo json_encode($data);
