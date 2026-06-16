<?php
include '../../database/connect.php';
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case "GET":
      // Ambil data nomor RM terakhir
      $sql = "SELECT nomor_rm_end FROM setting_clinic WHERE id = 1 LIMIT 1";
      $result = $koneksi->query($sql);

      if ($result && $result->num_rows > 0) {
         $row = $result->fetch_assoc();
         echo json_encode([
            "status" => 200,
            "data" => $row
         ]);
      } else {
         echo json_encode([
            "status" => 404,
            "message" => "Nomor RM belum diatur"
         ]);
      }
      break;

   case "POST":
      $input = json_decode(file_get_contents("php://input"), true);
      $nomor_rm = intval($input['nomor_rm']);

      if ($nomor_rm <= 0) {
         echo json_encode(["status" => 400, "message" => "Nomor RM tidak valid"]);
         exit;
      }

      // Cek apakah record sudah ada
      $check = $koneksi->query("SELECT id FROM setting_clinic WHERE id = 1 LIMIT 1");

      if ($check && $check->num_rows > 0) {
         // Update data jika sudah ada
         $query = "UPDATE setting_clinic SET nomor_rm_end = ? WHERE id = 1";
         $stmt = $koneksi->prepare($query);
         $stmt->bind_param("s", $nomor_rm);
         $success = $stmt->execute();
         $stmt->close();
      } else {
         // Insert data jika belum ada
         $query = "INSERT INTO setting_clinic (id, nomor_rm_end) VALUES (1, ?)";
         $stmt = $koneksi->prepare($query);
         $stmt->bind_param("s", $nomor_rm);
         $success = $stmt->execute();
         $stmt->close();
      }

      if ($success) {
         echo json_encode(["status" => 200, "message" => "Nomor RM berhasil disimpan"]);
      } else {
         echo json_encode(["status" => 500, "message" => "Gagal menyimpan nomor RM"]);
      }
      break;

   default:
      echo json_encode(["status" => 405, "message" => "Method not allowed"]);
}

$koneksi->close();
