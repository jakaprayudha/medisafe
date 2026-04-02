<?php
require '../../database/connect.php';

$search = $_GET['search'] ?? '';

$q = mysqli_query($koneksi, "SELECT code, icd10 
  FROM icd_10 
  WHERE code LIKE '%$search%' 
     OR icd10 LIKE '%$search%' 
  LIMIT 20
");

$data = [];

while ($row = mysqli_fetch_assoc($q)) {
   $data[] = [
      "id" => $row['code'],
      "text" => $row['code'] . ' - ' . $row['icd10']
   ];
}

echo json_encode($data);
