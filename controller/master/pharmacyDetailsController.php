<?php
include '../../database/connect.php';
header("Content-Type: application/json");

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

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

         if (!isset($_SESSION['id_customer'])) {
            echo json_encode([
               "success" => false,
               "message" => "Session tidak valid"
            ]);
            break;
         }

         $id_customer = (int) $_SESSION['id_customer'];

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

         $checkSql = "SELECT * FROM ms_pharmacy WHERE id_pharmacy = '$id' LIMIT 1";
         $checkRes = $koneksi->query($checkSql);

         if (!$checkRes || $checkRes->num_rows === 0) {
            echo json_encode([
               "success" => false,
               "message" => "Data tidak ditemukan"
            ]);
            break;
         }

         $row = $checkRes->fetch_assoc();

         $updates = [];
         $updateValues = [];

         foreach ($allowedFields as $field) {
            if (isset($_POST[$field])) {
               $value = $koneksi->real_escape_string($_POST[$field]);
               $updates[] = "$field = '$value'";
               $updateValues[$field] = $value;
            }
         }

         if (empty($updates)) {
            echo json_encode([
               "success" => false,
               "message" => "Tidak ada data diupdate"
            ]);
            break;
         }

         // Jika data global, clone ke customer yang update + log parent
         if ((int) $row['id_customer'] === 0) {
            $cek = $koneksi->prepare(
               "SELECT id FROM ms_pharmacy_parrent WHERE parent_id=? AND id_customer_real=?"
            );
            $cek->bind_param("ii", $id, $id_customer);
            $cek->execute();
            $exist = $cek->get_result()->fetch_assoc();
            $cek->close();

            if (!$exist) {
               $user = $_SESSION['fullname'] ?? null;
               $stmt = $koneksi->prepare(
                  "INSERT INTO ms_pharmacy_parrent (id_customer_real, parent_id, status_log, user)
                   VALUES (?, ?, 'UPDATE', ?)"
               );
               $stmt->bind_param("iis", $id_customer, $id, $user);
               $stmt->execute();
               $stmt->close();
            }

            $row['id_customer'] = $id_customer;
            foreach ($updateValues as $key => $value) {
               $row[$key] = $value;
            }

            unset($row['id_pharmacy'], $row['created_at']);

            $columns = array_keys($row);
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $types = str_repeat('s', count($columns));
            $values = array_values($row);

            $insert = $koneksi->prepare(
               "INSERT INTO ms_pharmacy (" . implode(',', $columns) . ") VALUES ($placeholders)"
            );
            $insert->bind_param($types, ...$values);

            if ($insert->execute()) {
               echo json_encode([
                  "success" => true,
                  "message" => "Data berhasil diupdate",
                  "new_id" => $koneksi->insert_id
               ]);
            } else {
               echo json_encode([
                  "success" => false,
                  "message" => $insert->error
               ]);
            }

            $insert->close();
         } else {
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
