<?php
include '../../database/connect.php';

// ambil id_visit dari request (opsional filter)
$id_visit = $_GET['id_visit'] ?? null;

$query = "SELECT 
    pv.id_visit,
    pv.id_patient,
    mp.face_image
  FROM pasien_visit pv
  JOIN ms_patient mp 
    ON pv.id_patient = mp.id_patient
";

// kalau mau filter 1 visit
if ($id_visit) {
   $id_visit = mysqli_real_escape_string($koneksi, $id_visit);
   $query .= " WHERE pv.id_visit = '$id_visit'";
}

$result = mysqli_query($koneksi, $query);

$data = [];

while ($row = mysqli_fetch_assoc($result)) {
   $data[] = [
      "id_visit" => $row['id_visit'],     // 🔥 ini penting
      "name" => $row['id_patient'],       // label tetap patient
      "image" => $row['face_image']
   ];
}

echo json_encode($data);
