<?php
/**
 * Hapus 1 baris CPO. POST id.
 * Perbaikan: id ditempel langsung ke SQL (injection) & tanpa cek faskes -> baris faskes lain bisa terhapus;
 * pesan error SQL dikirim ke browser; id yang tidak ada tetap "berhasil".
 */
include '../../database/connect.php';
require_once __DIR__ . '/cpoService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') cpoBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$idc = cpoCustomer();
$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) cpoBalas(422, ['status' => 'error', 'message' => 'ID tidak valid.']);

try {
   $st = $koneksi->prepare("DELETE FROM pasien_cpo WHERE id = ? AND id_customer = ?");
   $st->bind_param('ii', $id, $idc);
   $st->execute();
   $n = $st->affected_rows;
   $st->close();
} catch (Throwable $e) {
   error_log('[visit/deleteCpo] ' . $e->getMessage());
   cpoBalas(500, ['status' => 'error', 'message' => 'Gagal menghapus CPO.']);
}
if ($n < 1) cpoBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah terhapus.']);
cpoBalas(200, ['status' => 'success', 'message' => 'Data CPO dihapus.']);
