<?php
// Gambar & dokumen obat (module/admin/pharmacy_details).  GET ?id_pharmacy=ID
include "../../database/connect.php";
require_once __DIR__ . '/pharmacyAkses.php';
header("Content-Type: application/json; charset=utf-8");

$idc = (int) ($_SESSION['id_customer'] ?? 0);
$row = $idc > 0 ? obatFaskes($koneksi, (int) ($_GET['id_pharmacy'] ?? ($_GET['pharmacy_number'] ?? 0)), $idc) : null;
if (!$row) {
   http_response_code(404);
   echo json_encode(["status" => "error", "message" => "Obat tidak ditemukan."]);
   exit;
}
echo json_encode([
   "status" => "success",
   "files" => ["pharmacy_image" => $row['pharmacy_image'], "pharmacy_docs" => $row['pharmacy_docs']],
]);
