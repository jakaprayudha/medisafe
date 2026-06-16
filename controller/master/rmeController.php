<?php
require '../../database/connect.php'; // Koneksi ke database

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET: Ambil 1 data setting bisnis
if ($method === 'GET') {
   $query = "SELECT * FROM setting_clinic LIMIT 1";
   $result = $koneksi->query($query);
   $data = $result->fetch_assoc();

   if ($data) {
      echo json_encode(["status" => "success", "user" => $data]);
   } else {
      echo json_encode(["status" => "error", "message" => "Data tidak ditemukan."]);
   }
   exit;
}

// Handle POST: Insert atau Update data setting bisnis
if ($method === 'POST') {
   $rme_type = trim($_POST['rme_type']);
   $billing_tarif = trim($_POST['billing_tarif']);

   // Validasi input
   if (empty($rme_type) || empty($billing_tarif)) {
      echo json_encode(["status" => "error", "message" => "Semua field wajib diisi!"]);
      exit;
   }

   // Cek apakah data sudah ada
   $checkQuery = "SELECT COUNT(*) as total FROM setting_clinic";
   $result = $koneksi->query($checkQuery);
   $row = $result->fetch_assoc();

   if ($row['total'] == 0) {
      // Insert jika belum ada data
      $query = "INSERT INTO setting_clinic (rme_type, billing_tarif) VALUES (?, ?)";
   } else {
      // Update jika data sudah ada
      $query = "UPDATE setting_clinic SET rme_type = ?, billing_tarif = ?";
   }

   // Jalankan query dengan prepared statement
   $stmt = $koneksi->prepare($query);
   if (!$stmt) {
      echo json_encode(["status" => "error", "message" => "Gagal mempersiapkan query."]);
      exit;
   }

   $stmt->bind_param("ss", $rme_type, $billing_tarif);

   if ($stmt->execute()) {
      echo json_encode(["status" => "success", "message" => "Data berhasil disimpan."]);
   } else {
      echo json_encode(["status" => "error", "message" => "Gagal menyimpan data."]);
   }

   $stmt->close();
   exit;
}

// Jika metode tidak dikenali
echo json_encode(["status" => "error", "message" => "Invalid request."]);
