<?php
require '../../database/connect.php';
session_start();

header('Content-Type: application/json');

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak ditemukan'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

   // =========================
   // 🔹 GET DATA
   // =========================
   case 'GET':

      $stmt = $koneksi->prepare("
         SELECT sc.*, mf.*
         FROM setting_clinic sc
         LEFT JOIN ms_faskes mf ON mf.id_clinic = sc.id
         WHERE sc.id_customer = ?
         LIMIT 1
      ");

      $stmt->bind_param("i", $id_customer);
      $stmt->execute();

      $result = $stmt->get_result();
      $data = $result->fetch_assoc();

      echo json_encode([
         'status' => 'success',
         'data' => $data ?: null
      ]);

      $stmt->close();
      break;


   // =========================
   // 🔹 INSERT / UPDATE
   // =========================
   case 'POST':

      $margin_obat = $_POST['margin_obat'] ?? 0;
      $margin_bmhp = $_POST['margin_bmhp'] ?? 0;

      mysqli_begin_transaction($koneksi);

      try {

         // 🔍 CEK DATA EXIST
         $check = $koneksi->prepare("
            SELECT id FROM setting_clinic 
            WHERE id_customer = ?
            LIMIT 1
         ");
         $check->bind_param("i", $id_customer);
         $check->execute();

         $result = $check->get_result();
         $row = $result->fetch_assoc();
         $check->close();

         if ($row) {

            // 🔄 UPDATE
            $stmt = $koneksi->prepare("
               UPDATE setting_clinic 
               SET margin_obat = ?, margin_bmhp = ?
               WHERE id_customer = ?
            ");

            $stmt->bind_param("ddi", $margin_obat, $margin_bmhp, $id_customer);
         } else {

            // ➕ INSERT
            $stmt = $koneksi->prepare("
               INSERT INTO setting_clinic (id_customer, margin_obat, margin_bmhp)
               VALUES (?, ?, ?)
            ");

            $stmt->bind_param("idd", $id_customer, $margin_obat, $margin_bmhp);
         }

         if (!$stmt->execute()) {
            throw new Exception($stmt->error);
         }

         $stmt->close();

         mysqli_commit($koneksi);

         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil disimpan'
         ]);
      } catch (Exception $e) {

         mysqli_rollback($koneksi);

         echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
         ]);
      }

      break;
}
