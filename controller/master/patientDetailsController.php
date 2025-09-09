<?php
include '../../database/connect.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

// Gunakan _method untuk spoof PUT/DELETE
if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}

switch ($method) {
   case 'GET': // get data by patient_number
      if (isset($_GET['no'])) {
         $no = $koneksi->real_escape_string($_GET['no']);
         $sql = "SELECT * FROM ms_patient WHERE patient_number = '$no' LIMIT 1";
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
      if (isset($_POST['patient_number'])) {
         $patientNo = $koneksi->real_escape_string($_POST['patient_number']);

         // daftar field yang boleh diupdate
         $allowedFields = [
            'patient_nik',
            'nomor_rm',
            'patient_datebirth',
            'patient_religion',
            'patient_gender',
            'patient_address',
            'patient_place',
            'patient_notes',
            'patient_provinsi',
            'patient_kabupaten',
            'patient_kecamatan',
            'patient_kelurahan',
            'patient_desa',
            'patient_phone',
            'patient_name',
            'patient_blood',
            'patient_mail',
            'patient_marital_status',
            'patient_nationality',
            'patient_education',
            'patient_occupation',
            'patient_emergency_contact_name',
            'patient_emergency_contact_relation',
            'patient_emergency_contact_phone',
            'patient_allergy',
            'patient_disability',
            'nomor_rm_any',
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
            $sql = "UPDATE ms_patient SET " . implode(", ", $updates) . " WHERE patient_number = '$patientNo'";

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
            "message" => "patient_number tidak ditemukan"
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
