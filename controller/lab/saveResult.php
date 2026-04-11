<?php
include '../../database/connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$id_inspection = $data['id_inspection'] ?? null;
$items = $data['data'] ?? [];

if (!$id_inspection || empty($items)) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data tidak lengkap'
   ]);
   exit;
}

foreach ($items as $item) {

   $id_item = $item['id_item'];
   $hasil   = $item['hasil'];

   // 🔹 cek apakah sudah ada
   $check = $koneksi->prepare("
      SELECT id FROM laboratorium_result 
      WHERE id_inspection=? AND id_item=?
   ");
   $check->bind_param("ii", $id_inspection, $id_item);
   $check->execute();
   $res = $check->get_result();

   if ($res->num_rows > 0) {

      // 🔁 UPDATE
      $stmt = $koneksi->prepare("
         UPDATE laboratorium_result 
         SET hasil=? 
         WHERE id_inspection=? AND id_item=?
      ");
      $stmt->bind_param("sii", $hasil, $id_inspection, $id_item);
   } else {

      // ➕ INSERT
      $stmt = $koneksi->prepare("
         INSERT INTO laboratorium_result (id_inspection, id_item, hasil)
         VALUES (?, ?, ?)
      ");
      $stmt->bind_param("iis", $id_inspection, $id_item, $hasil);
   }

   $stmt->execute();
}

echo json_encode([
   'status' => 'success',
   'message' => 'Hasil berhasil disimpan'
]);
