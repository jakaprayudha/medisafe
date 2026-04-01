<?php
require '../../database/connect.php';

$pt = $_GET['pt'];

$q = mysqli_query($koneksi, "SELECT file_ttd 
  FROM pasien_ttd_pernyataan 
  WHERE id_patient='$pt' 
  ORDER BY id_patient DESC 
  LIMIT 1
");

$data = mysqli_fetch_assoc($q);

if ($data) {
   echo json_encode([
      'status' => 'success',
      'file' => $data['file_ttd']
   ]);
} else {
   echo json_encode(['status' => 'empty']);
}
