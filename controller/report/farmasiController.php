<?php
// Data tabel Laporan Farmasi (module/admin/rpt_farmasi). Perhitungan ada di farmasiStokService.php
// supaya tabel, Excel, dan Print selalu sama.

include '../../database/connect.php';
require_once __DIR__ . '/farmasiStokService.php';
mysqli_report(MYSQLI_REPORT_OFF);

header("Content-Type: application/json; charset=UTF-8");

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$id_customer = $_SESSION['id_customer'] ?? null;
if (!$id_customer) {
   echo json_encode(["status" => "error", "message" => "Session tidak ditemukan"]);
   exit;
}

$fromDate = (string) ($_GET['fromDate'] ?? date('Y-m-d'));
$toDate = (string) ($_GET['toDate'] ?? date('Y-m-d'));

$error = farmasiStokValidasiPeriode($fromDate, $toDate);
if ($error !== null) {
   echo json_encode(["status" => "error", "message" => $error]);
   exit;
}

try {
   $hasil = farmasiStokReport($koneksi, (string) $id_customer, $fromDate, $toDate);
} catch (Throwable $e) {
   error_log('[rpt_farmasi] ' . $e->getMessage());
   echo json_encode(["status" => "error", "message" => "Gagal mengambil data laporan farmasi"]);
   exit;
}

echo json_encode([
   "status" => "success",
   "filter" => ["fromDate" => $fromDate, "toDate" => $toDate],
   "total" => count($hasil['data']),
   "ringkasan" => $hasil['ringkasan'],
   "data" => $hasil['data'],
], JSON_UNESCAPED_UNICODE);
