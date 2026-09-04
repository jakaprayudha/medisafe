<?php

include '../../database/connect.php';

header('Content-Type: application/json');

$data = json_decode(
   file_get_contents("php://input"),
   true
);

$action = $data['action'] ?? '';


/*
|--------------------------------------------------------------------------
| INSERT
|--------------------------------------------------------------------------
*/

if ($action === 'insert') {

   $visit_ID =
      $data['visit_ID'] ?? '';

   $id_customer =
      $data['id_customer'] ?? '';

   $no_gigi =
      $data['no_gigi'] ?? '';

   $elemen =
      $data['elemen'] ?? '';

   $elemen_gigi =
      $data['elemen_gigi'] ?? '';

   $diagnosa =
      $data['diagnosa'] ?? '';

   $prosedur =
      $data['prosedur'] ?? '';

   $keterangan =
      $data['keterangan'] ?? '';


   $query = $koneksi->prepare("
        INSERT INTO odontogram
        (
            visit_ID,
            id_customer,
            no_gigi,
            elemen,
            elemen_gigi,
            diagnosa,
            prosedur,
            keterangan
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");


   $query->bind_param(
      "ssssssss",
      $visit_ID,
      $id_customer,
      $no_gigi,
      $elemen,
      $elemen_gigi,
      $diagnosa,
      $prosedur,
      $keterangan
   );


   if ($query->execute()) {

      echo json_encode([
         "status" => "success",
         "message" => "Data berhasil disimpan"
      ]);
   } else {

      echo json_encode([
         "status" => "error",
         "message" => $query->error
      ]);
   }

   exit;
}


/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

if ($action === 'update') {

   $visit_ID =
      $data['visit_ID'] ?? '';

   $old_no_gigi =
      $data['old_no_gigi'] ?? '';

   $no_gigi =
      $data['no_gigi'] ?? '';

   $elemen =
      $data['elemen'] ?? '';

   $elemen_gigi =
      $data['elemen_gigi'] ?? '';

   $diagnosa =
      $data['diagnosa'] ?? '';

   $prosedur =
      $data['prosedur'] ?? '';

   $keterangan =
      $data['keterangan'] ?? '';


   $query = $koneksi->prepare("
        UPDATE odontogram
        SET
            no_gigi = ?,
            elemen = ?,
            elemen_gigi = ?,
            diagnosa = ?,
            prosedur = ?,
            keterangan = ?
        WHERE visit_ID = ?
          AND no_gigi = ?
    ");


   $query->bind_param(
      "ssssssss",
      $no_gigi,
      $elemen,
      $elemen_gigi,
      $diagnosa,
      $prosedur,
      $keterangan,
      $visit_ID,
      $old_no_gigi
   );


   if ($query->execute()) {

      echo json_encode([
         "status" => "success",
         "message" => "Data berhasil diubah"
      ]);
   } else {

      echo json_encode([
         "status" => "error",
         "message" => $query->error
      ]);
   }

   exit;
}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

if ($action === 'delete') {

   $visit_ID =
      $data['visit_ID'] ?? '';

   $no_gigi =
      $data['no_gigi'] ?? '';


   $query = $koneksi->prepare("
        DELETE FROM odontogram
        WHERE visit_ID = ?
          AND no_gigi = ?
    ");


   $query->bind_param(
      "ss",
      $visit_ID,
      $no_gigi
   );


   if ($query->execute()) {

      echo json_encode([
         "status" => "success",
         "message" => "Data berhasil dihapus"
      ]);
   } else {

      echo json_encode([
         "status" => "error",
         "message" => $query->error
      ]);
   }

   exit;
}


echo json_encode([
   "status" => "error",
   "message" => "Action tidak ditemukan"
]);
