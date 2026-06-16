<?php
include "../../database/connect.php";

$rm    = $_GET['rm'] ?? '';
$visit = $_GET['visit'] ?? '';

$q = $koneksi->query("
    SELECT file_path 
    FROM buku_kia 
    WHERE nomor_rm = '$rm' AND nomor_visit = '$visit'
    ORDER BY id DESC
");

$files = [];
while ($row = $q->fetch_assoc()) {
   $files[] = $row['file_path'];
}

if (count($files) > 0) {
   echo json_encode([
      "status" => "success",
      "files" => $files
   ]);
} else {
   echo json_encode([
      "status" => "empty",
      "files" => []
   ]);
}
