<?php
require '../../database/connect.php'; // Koneksi ke database

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

// Handle GET: Ambil 1 data setting bisnis
if ($method === 'GET') {
   $query = "SELECT * FROM setting_bisnis LIMIT 1";
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
   $namaBisnis = trim($_POST['nama_bisnis']);
   $telepon = trim($_POST['telepon']);
   $alamat = trim($_POST['alamat']);

   // Validasi input
   if (empty($namaBisnis) || empty($telepon) || empty($alamat)) {
      echo json_encode(["status" => "error", "message" => "Semua field wajib diisi!"]);
      exit;
   }

   // Cek apakah data sudah ada
   $checkQuery = "SELECT COUNT(*) as total FROM setting_bisnis";
   $result = $koneksi->query($checkQuery);
   $row = $result->fetch_assoc();

   if ($row['total'] == 0) {
      // Insert jika belum ada data
      $query = "INSERT INTO setting_bisnis (business_name, phone_number, address) VALUES (?, ?, ?)";
   } else {
      // Update jika data sudah ada
      $query = "UPDATE setting_bisnis SET business_name = ?, phone_number = ?, address = ?";
   }

   // Jalankan query dengan prepared statement
   $stmt = $koneksi->prepare($query);
   if (!$stmt) {
      echo json_encode(["status" => "error", "message" => "Gagal mempersiapkan query."]);
      exit;
   }

   $stmt->bind_param("sss", $namaBisnis, $telepon, $alamat);

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
