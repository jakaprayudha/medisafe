<?php
require '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');
$search = mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100);
// prepared statement (dulu nilai pencarian ditempel langsung ke SQL)
$pola = '%' . addcslashes($search, '%_\\') . '%';
$st = $koneksi->prepare("SELECT code, icd10 FROM icd_10 WHERE code LIKE ? OR icd10 LIKE ?
   ORDER BY (code = ?) DESC, (code LIKE ?) DESC, code LIMIT 20");
$awal = addcslashes($search, '%_\\') . '%';
$st->bind_param('ssss', $pola, $pola, $search, $awal);
$st->execute();
$q = $st->get_result();

$data = [];

while ($row = mysqli_fetch_assoc($q)) {
   $data[] = [
      "id" => $row['code'],
      "text" => $row['code'] . ' - ' . $row['icd10']
   ];
}

echo json_encode($data);
