<?php
require '../../database/connect.php';
session_start();

header('Content-Type: application/json');

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode(['status' => 'error', 'message' => 'Session tidak ditemukan']);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

   // =========================
   // 🔹 GET DATA (FULL)
   // =========================
   case 'GET':

      $query = mysqli_query($koneksi, "SELECT sc.*, mf.*
         FROM setting_clinic sc
         LEFT JOIN ms_faskes mf ON mf.id_clinic = sc.id
         WHERE sc.id_customer = '$id_customer'
         LIMIT 1
      ");

      if ($data = mysqli_fetch_assoc($query)) {
         echo json_encode([
            'status' => 'success',
            'data' => $data
         ]);
      } else {
         echo json_encode([
            'status' => 'success',
            'data' => null
         ]);
      }

      break;


   // =========================
   // 🔹 INSERT / UPDATE FULL
   // =========================
   case 'POST':

      // 🔹 mapping semua field
      $clinic_name     = $_POST['clinic_name'] ?? '';
      $pic_name        = $_POST['pic_name'] ?? '';
      $pic_phone       = $_POST['pic_phone'] ?? '';
      $pic_email       = $_POST['pic_email'] ?? '';
      $faskes_address  = $_POST['faskes_address'] ?? '';
      $faskes_prov     = $_POST['faskes_prov'] ?? '';
      $faskes_city     = $_POST['faskes_city'] ?? '';
      $faskes_district = $_POST['faskes_district'] ?? '';
      $faskes_village  = $_POST['faskes_village'] ?? '';
      $contract_start  = date('Y-m-d', strtotime($_POST['contract_start']));
      $contract_end    = date('Y-m-d', strtotime($_POST['contract_start']));

      mysqli_begin_transaction($koneksi);

      try {

         // 🔹 CEK CLINIC
         $checkClinic = mysqli_query($koneksi, "
            SELECT id FROM setting_clinic 
            WHERE id_customer = '$id_customer'
            LIMIT 1
         ");

         if ($clinic = mysqli_fetch_assoc($checkClinic)) {

            $id_clinic = $clinic['id'];

            // 🔸 UPDATE CLINIC
            mysqli_query($koneksi, "
               UPDATE setting_clinic SET
                  clinic_name = '$clinic_name'
               WHERE id = '$id_clinic'
            ");

            // 🔸 CEK FASKES
            $checkFaskes = mysqli_query($koneksi, "
               SELECT id_faskes FROM ms_faskes 
               WHERE id_clinic = '$id_clinic'
            ");

            if (mysqli_num_rows($checkFaskes) > 0) {

               mysqli_query($koneksi, "
                  UPDATE ms_faskes SET
                     pic_name = '$pic_name',
                     pic_phone = '$pic_phone',
                     pic_email = '$pic_email',
                     faskes_address = '$faskes_address',
                     faskes_prov = '$faskes_prov',
                     faskes_city = '$faskes_city',
                     faskes_district = '$faskes_district',
                     faskes_village = '$faskes_village',
                     contract_start = '$contract_start',
                     contract_end = '$contract_end',
                     udpated_at = NOW()
                  WHERE id_clinic = '$id_clinic'
               ");
            } else {

               mysqli_query($koneksi, "
                  INSERT INTO ms_faskes (
                     pic_name, pic_phone, pic_email,
                     faskes_address, faskes_prov, faskes_city,
                     faskes_district, faskes_village,
                     contract_start, contract_end,
                     id_clinic, faskes_status
                  ) VALUES (
                     '$pic_name', '$pic_phone', '$pic_email',
                     '$faskes_address', '$faskes_prov', '$faskes_city',
                     '$faskes_district', '$faskes_village',
                     '$contract_start', '$contract_end',
                     '$id_clinic', 1
                  )
               ");
            }
         } else {

            // 🔸 INSERT CLINIC
            mysqli_query($koneksi, "
               INSERT INTO setting_clinic (clinic_name, id_customer, status)
               VALUES ('$clinic_name', '$id_customer', 1)
            ");

            $id_clinic = mysqli_insert_id($koneksi);

            // 🔸 INSERT FASKES
            mysqli_query($koneksi, "
               INSERT INTO ms_faskes (
                  pic_name, pic_phone, pic_email,
                  faskes_address, faskes_prov, faskes_city,
                  faskes_district, faskes_village,
                  contract_start, contract_end,
                  id_clinic, faskes_status
               ) VALUES (
                  '$pic_name', '$pic_phone', '$pic_email',
                  '$faskes_address', '$faskes_prov', '$faskes_city',
                  '$faskes_district', '$faskes_village',
                  '$contract_start', '$contract_end',
                  '$id_clinic', 1
               )
            ");
         }

         mysqli_commit($koneksi);

         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan'
         ]);
      } catch (Exception $e) {

         mysqli_rollback($koneksi);

         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menyimpan data'
         ]);
      }

      break;
}
