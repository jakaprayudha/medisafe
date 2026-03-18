<?php
include '../../database/connect.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

// Gunakan _method untuk spoof PUT/DELETE
if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}

switch ($method) {
   case 'GET': // get data by doctor_number
      if (isset($_GET['no'])) {
         $no = $koneksi->real_escape_string($_GET['no']);
         $sql = "SELECT * FROM ms_doctor WHERE doctor_number = '$no' LIMIT 1";
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
      if (isset($_POST['doctor_number'])) {
         $doctorNo = $koneksi->real_escape_string($_POST['doctor_number']);

         // daftar field yang boleh diupdate
         $allowedFields = [
            'doctor_name',
            'id_poli',
            'doctor_category',
            'doctor_status',
            'doctor_phone',
            'doctor_mail',
            'doctor_birthdate',
            'doctor_gender',
            'doctor_address',
            'doctor_sip',
            'doctor_str',
            'doctor_expaired',
            'doctor_his',
            'str_lembaga',
            'str_number',
            'str_date_expaired',
            'sip_lembaga',
            'sip_number',
            'sip_date_expaired',
            'doctor_title_front',
            'doctor_title_back',
            'doctor_antrean',
            'estimated_services',
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
            $sql = "UPDATE ms_doctor SET " . implode(", ", $updates) . " WHERE doctor_number = '$doctorNo'";

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
            "message" => "doctor_number tidak ditemukan"
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
