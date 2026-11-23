<?php
/**
 * Daftar petugas klinis faskes sesi (dokter/bidan/perawat/apoteker) untuk pilihan petugas (mis. CPO).
 * Perbaikan: dulu SELECT * -> hash password, username & data akun ikut terkirim ke browser; SQL dirangkai langsung.
 */
include '../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header("Content-Type: application/json; charset=utf-8");
$id_customer = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();
if ($id_customer <= 0) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);
   exit;
}

$st = $koneksi->prepare("SELECT id_user, fullname, roles FROM ms_users
   WHERE id_customer = ? AND roles IN ('dokter','bidan','perawat','apoteker') ORDER BY fullname");
$st->bind_param('i', $id_customer);
$st->execute();
$data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

echo json_encode(["status" => "success", "data" => $data], JSON_UNESCAPED_UNICODE);
