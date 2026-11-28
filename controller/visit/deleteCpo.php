<?php
/**
 * Hapus 1 baris CPO. POST id.
 * Perbaikan: id ditempel langsung ke SQL (injection) & tanpa cek faskes -> baris faskes lain bisa terhapus;
 * pesan error SQL dikirim ke browser; id yang tidak ada tetap "berhasil".
 */
include '../../database/connect.php';
require_once __DIR__ . '/cpoService.php';
require_once __DIR__ . '/../../utility/activityLog.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') cpoBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$idc = cpoCustomer();
$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) cpoBalas(422, ['status' => 'error', 'message' => 'ID tidak valid.']);

try {
   $cpoLama = logAmbilBaris($koneksi, 'pasien_cpo', 'id', $id);
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
logBaris($koneksi, 'klinis', 'pasien_cpo', 'id', $id, $cpoLama, ['tipe' => 'cpo', 'label' => 'CPO ' . ($cpoLama['nama_item'] ?? ''), 'visit_ID' => $cpoLama['visit_ID'] ?? null], 'Catatan pemberian obat dihapus');
cpoBalas(200, ['status' => 'success', 'message' => 'Data CPO dihapus.']);
