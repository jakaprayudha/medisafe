<?php
include '../../database/connect.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

// Gunakan _method untuk spoof PUT/DELETE
if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}

switch ($method) {
   case 'GET': // get data by pharmacy_number
      if (isset($_GET['no'])) {
         $no = $koneksi->real_escape_string($_GET['no']);
         $sql = "SELECT * FROM ms_pharmacy  WHERE id_pharmacy = '$no' LIMIT 1";
         $result = $koneksi->query($sql);

         if ($result && $result->num_rows > 0) {
            echo json_encode([
               "success" => true,
               "data" => $result->fetch_assoc()
            ]);
         } else {
            echo json_encode([
               "success" => false,
               "message" => "Pharmacy tidak ditemukan"
            ]);
         }
      } else {
         echo json_encode([
            "success" => false,
            "message" => "Parameter no tidak ditemukan"
         ]);
      }
      break;

   case 'PUT':

      if (isset($_POST['id_pharmacy'])) {

         $id = $koneksi->real_escape_string($_POST['id_pharmacy']);

         $allowedFields = [
            'pharmacy_code',
            'pharmacy_name_generic',
            'pharmacy_name_trade',
            'pharmacy_category',
            'pharmacy_sub_category',
            'pharmcy_golongan',
            'pharmcy_jenis_drugs',
            'pharmacy_bentuk_sediaan',
            'pharmacy_dosis',
            'pharmacy_unit',
            'pharmacy_kemasan',
            'pharmacy_buy',
            'pharmacy_sale',
            'stok_min',
            'stok_max',
            'pharmacy_supplier',
            'pharmacy_factory',
            'pharmacy_code_catalog',
            'fornas',
            'formularium_rs',
            'pharmacy_description',
            'pharmacy_status'
         ];

         $updates = [];

         foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
               $value = $koneksi->real_escape_string($_POST[$field]);
               $updates[] = "$field = '$value'";
            }
         }

         if (!empty($updates)) {

            $sql = "UPDATE ms_pharmacy 
                 SET " . implode(", ", $updates) . " 
                 WHERE id_pharmacy = '$id'";

            if ($koneksi->query($sql)) {
               echo json_encode([
                  "success" => true,
                  "message" => "Data berhasil diupdate"
               ]);
            } else {
               echo json_encode([
                  "success" => false,
                  "message" => $koneksi->error
               ]);
            }
         } else {
            echo json_encode([
               "success" => false,
               "message" => "Tidak ada data diupdate"
            ]);
         }
      } else {
         echo json_encode([
            "success" => false,
            "message" => "id_pharmacy tidak ditemukan"
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
