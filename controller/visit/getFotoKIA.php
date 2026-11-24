<?php
/**
 * Foto Buku KIA (module/admin/form_upload_buku_kia). Tabel buku_kia (tanpa id_customer -> dibatasi lewat kunjungan),
 * file di uploads/bukukia/.
 *   GET ?visit=<visit_ID>  (atau ?no=)
 *   -> { status, files:[url..] (kompatibel), data:[{id, url, created_at}] kunjungan ini,
 *        riwayat:[{id, url, created_at, visit_ID, visit_date}] kunjungan lain pasien yang sama di faskes ini, batal }
 * Perbaikan: rm & visit disisipkan ke SQL (SQL injection), tanpa cek login & batas faskes, difilter nomor RM dari URL,
 * header JSON tidak dikirim (halaman memakai JSON.parse manual).
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$kunj = visitGuard($koneksi, 'visit_ID', (string) ($_GET['visit'] ?? $_GET['no'] ?? ''));
session_write_close();
$no = (string) $kunj['visit_ID'];
$idc = (int) $kunj['id_customer'];
$pasien = (string) $kunj['id_patient'];
$url = fn($p) => preg_match('#^uploads/bukukia/([^/]+)$#', (string) $p, $m) ? 'uploads/bukukia/' . rawurlencode($m[1]) : null;

try {
   $st = $koneksi->prepare("SELECT k.id, k.file_path, k.created_at, k.nomor_visit, v.visit_date
      FROM buku_kia k JOIN pasien_visit v ON v.visit_ID = k.nomor_visit AND v.id_customer = ?
      WHERE v.id_patient = ? ORDER BY v.visit_date DESC, k.id ASC LIMIT 500");
   $st->bind_param('is', $idc, $pasien);
   $st->execute();
   $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   $data = $riwayat = [];
   foreach ($rows as $r) {
      $u = $url($r['file_path']);
      if (!$u) continue;
      $x = ['id' => (int) $r['id'], 'url' => $u, 'created_at' => $r['created_at']];
      if ($r['nomor_visit'] === $no) $data[] = $x;
      else $riwayat[] = $x + ['visit_ID' => $r['nomor_visit'], 'visit_date' => $r['visit_date']];
   }
   echo json_encode([
      'status' => $data ? 'success' : 'empty',
      'files' => array_column($data, 'url'),
      'data' => $data,
      'riwayat' => $riwayat,
      'batal' => (int) $kunj['visit_status'] === 99,
   ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
   error_log('[visit/getFotoKIA] ' . $e->getMessage());
   http_response_code(500);
   echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}
