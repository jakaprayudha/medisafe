<?php
include '../../database/connect.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

// Gunakan _method untuk spoof PUT/DELETE
if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}

switch ($method) {
   case 'GET': // get data by nurse_number
      if (isset($_GET['no'])) {
         $no = $koneksi->real_escape_string($_GET['no']);
         $sql = "SELECT * FROM ms_nurse WHERE nurse_number = '$no' LIMIT 1";
         $result = $koneksi->query($sql);

         if ($result && $result->num_rows > 0) {
            echo json_encode([
               "success" => true,
               "data" => $result->fetch_assoc()
            ]);
         } else {
            echo json_encode([
               "success" => false,
               "message" => "Dokter tidak ditemukan"
            ]);
         }
      } else {
         echo json_encode([
            "success" => false,
            "message" => "Parameter no tidak ditemukan"
         ]);
      }
      break;

   case 'PUT': // update data
      if (isset($_POST['nurse_number'])) {
         $doctorNo = $koneksi->real_escape_string($_POST['nurse_number']);

         // daftar field yang boleh diupdate
         $allowedFields = [
            'nurse_name',
            'nurse_profesi',
            'nurse_category',
            'nurse_status',
            'nurse_phone',
            'nurse_mail',
            'nurse_birthdate',
            'nurse_gender',
            'nurse_address',
            'nurse_sip',
            'nurse_str',
            'nurse_expaired'
         ];

         $updates = [];
         foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
               $value = $koneksi->real_escape_string($_POST[$field]);
               $updates[] = "$field = '$value'";
            }
         }

         if (!empty($updates)) {
            $updates[] = "updated_at = NOW()";
            $sql = "UPDATE ms_nurse SET " . implode(", ", $updates) . " WHERE nurse_number = '$doctorNo'";

            if ($koneksi->query($sql)) {
               echo json_encode([
                  "success" => true,
                  "message" => "Data dokter berhasil diperbarui"
               ]);
            } else {
               echo json_encode([
                  "success" => false,
                  "message" => "Gagal update: " . $koneksi->error
               ]);
            }
         } else {
            echo json_encode([
               "success" => false,
               "message" => "Tidak ada field yang dikirim"
            ]);
         }
      } else {
         echo json_encode([
            "success" => false,
            "message" => "nurse_number tidak ditemukan"
         ]);
      }
      break;

   default:
      echo json_encode([
         "success" => false,
         "message" => "Method $method tidak diizinkan"
      ]);
      break;
}
