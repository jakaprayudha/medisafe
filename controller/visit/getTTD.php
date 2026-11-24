<?php
/**
 * Tanda tangan pasien kunjungan (pasien_visit.signature_path, uploads/ttd/) — dipakai module/admin/form_pernyataan.
 *   GET ?no=<visit_ID> -> { status, data: null | { ttd:<url absolut>, url:<url relatif>, file, ada, waktu } }
 * Perbaikan: tanpa login id_customer null -> selalu "belum tanda tangan"; URL dasar dihitung dengan membuang 4 segmen
 * PHP_SELF (salah bila aplikasi di sub-folder / URL tanpa .php); SELECT * seluruh kolom kunjungan; nama file tanpa encode.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$kunj = visitGuard($koneksi, 'visit_ID', (string) ($_GET['no'] ?? ''));
session_write_close();

$st = $koneksi->prepare("SELECT signature_path FROM pasien_visit WHERE id_visit = ? AND id_customer = ? LIMIT 1");
$idv = (int) $kunj['id_visit'];
$idc = (int) $kunj['id_customer'];
$st->bind_param('ii', $idv, $idc);
$st->execute();
$file = basename((string) ($st->get_result()->fetch_row()[0] ?? ''));
$st->close();

if ($file === '') {
   echo json_encode(['status' => 'success', 'data' => null]);
   exit;
}
$path = dirname(__DIR__, 2) . '/uploads/ttd/' . $file;
$url = 'uploads/ttd/' . rawurlencode($file);
// akar aplikasi = 3 tingkat di atas /controller/visit/getTTD.php
$akar = rtrim(str_replace('\\', '/', dirname((string) $_SERVER['SCRIPT_NAME'], 3)), '/');
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
echo json_encode([
   'status' => 'success',
   'data' => [
      'ttd' => ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $akar . '/' . $url,
      'url' => $url,
      'file' => $file,
      'ada' => is_file($path),
      'waktu' => is_file($path) ? date('Y-m-d H:i', filemtime($path)) : null,
   ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
