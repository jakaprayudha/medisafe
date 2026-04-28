<?php
include '../../database/connect.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// ambil data
$no_gigi       = $data['no_gigi'] ?? null;
$elemen        = $data['elemen'] ?? null;
$elemen_gigi   = $data['elemen_gigi'] ?? null;
$diagnosa      = $data['diagnosa'] ?? null;
$prosedur      = $data['prosedur'] ?? null;
$keterangan    = $data['keterangan'] ?? null;
$visit_ID      = $data['visit_ID'] ?? null;
$id_customer   = $data['id_customer'] ?? null;

// validasi minimal
if (!$no_gigi || !$visit_ID) {
   echo json_encode([
      "status" => "error",
      "message" => "Data tidak lengkap"
   ]);
   exit;
}

// query insert + update
$stmt = $koneksi->prepare("
    INSERT INTO odontogram 
    (visit_ID, no_gigi, elemen, elemen_gigi, diagnosa, prosedur, keterangan, id_customer)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
      elemen = VALUES(elemen),
      elemen_gigi = VALUES(elemen_gigi),
      diagnosa = VALUES(diagnosa),
      prosedur = VALUES(prosedur),
      keterangan = VALUES(keterangan)
");

$stmt->bind_param(
   "sisssssi",
   $visit_ID,
   $no_gigi,
   $elemen,
   $elemen_gigi,
   $diagnosa,
   $prosedur,
   $keterangan,
   $id_customer
);

if ($stmt->execute()) {
   echo json_encode([
      "status" => "success",
      "message" => "Data berhasil disimpan"
   ]);
} else {
   echo json_encode([
      "status" => "error",
      "message" => $stmt->error
   ]);
}
