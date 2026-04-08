<?php
require '../../database/connect.php';

header("Content-Type: application/json");
session_start();

// 🔥 AMBIL SESSION
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      "status" => "error",
      "message" => "Session tidak ditemukan"
   ]);
   exit;
}

// 🔥 PARAMETER
$no = $_GET['no'] ?? null; // visit_ID

if (!$no) {
   echo json_encode([
      "status" => "error",
      "message" => "Parameter visit_ID tidak ada"
   ]);
   exit;
}

/* ================================================
   GET DATA TRIASE SAJA
================================================= */
$stmt = $koneksi->prepare("
   SELECT 
      id_triase,
      visit_ID,
      tanggal_masuk,
      jam_masuk,
      keluhan_utama,

      tekanan_darah,
      nadi,
      rr,
      suhu,
      spo2,

      gcs_e,
      gcs_v,
      gcs_m,
      gcs_total,

      skala_nyeri,
      triase,
      referensi_triase,
      catatan,

      created_at,
      updated_at
   FROM pasien_triase
   WHERE visit_ID = ?
   ORDER BY id_triase DESC
   LIMIT 1
");

$stmt->bind_param("s", $no);
$stmt->execute();

$result = $stmt->get_result();
$triase = $result->fetch_assoc();

$stmt->close();

/* ================================================
   RESPONSE
================================================= */
echo json_encode([
   "status" => "success",
   "data" => $triase ?? null
]);
