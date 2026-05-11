<?php
include '../../database/connect.php';
$id_visit = $_GET['id_visit'] ?? null;

$query = "SELECT pv.id_visit, pv.id_patient, mp.face_image
FROM pasien_visit pv
JOIN ms_patient mp ON pv.id_patient = mp.id_patient
";

if ($id_visit) {
   $id_visit = mysqli_real_escape_string($koneksi, $id_visit);
   $query .= " WHERE pv.id_visit = '$id_visit'";
}
$result = mysqli_query($koneksi, $query);

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
   $data[] = [
      "id_visit" => $row['id_visit'],
      "name" => $row['id_patient'],
      "image" => "../uploads/faces/" . $row['face_image']
   ];
}

echo json_encode($data);
