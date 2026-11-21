<?php
/**
 * Batalkan (hapus) permintaan rawat inap yang belum di-approve. POST id_ranap.
 * Dipakai module/admin/registrasi_booking_ranap dan module/admin/rawat_inap.
 *
 * Perbaikan: dulu SQL injection (id_ranap langsung masuk query), tanpa cek faskes, dan selalu "berhasil" walaupun
 * tidak ada baris yang terhapus (sudah di-approve / id salah).
 */
session_start();
require_once '../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');

$IDC = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();
function cbJawab(array $d, int $kode = 200): void
{
   http_response_code($kode);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($IDC <= 0) cbJawab(['status' => 'error', 'message' => 'Session tidak valid / expired'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') cbJawab(['status' => 'error', 'message' => 'Metode tidak diizinkan.'], 405);
$id = (int) ($_POST['id_ranap'] ?? 0);
if ($id <= 0) cbJawab(['status' => 'error', 'message' => 'ID rawat inap tidak ditemukan.']);

try {
   $st = $koneksi->prepare("DELETE rn FROM permintaan_ranap rn JOIN pasien_visit pv ON pv.visit_ID = rn.visit_ID_inpatient
      WHERE rn.id_ranap = ? AND rn.ranap_booking = 0 AND pv.id_customer = ?");
   $st->bind_param('ii', $id, $IDC);
   $st->execute();
   $n = $st->affected_rows;
   $st->close();
   $n > 0
      ? cbJawab(['status' => 'success', 'message' => 'Permintaan rawat inap dibatalkan.'])
      : cbJawab(['status' => 'error', 'message' => 'Permintaan tidak ditemukan atau sudah di-approve (tidak dapat dibatalkan).']);
} catch (Throwable $e) {
   error_log('[cancelRawatInap] ' . $e->getMessage());
   cbJawab(['status' => 'error', 'message' => 'Gagal membatalkan permintaan.'], 500);
}
