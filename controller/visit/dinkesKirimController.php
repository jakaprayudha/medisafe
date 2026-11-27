<?php
/**
 * Kirim manual / cek status Dinkes dari halaman registrasi (module/admin/registrasi).
 *   POST aksi=kirim visit=<visit_ID> -> dkKirimVisit (dewasa/balita ditentukan umur; balita butuh form Dinkes Balita)
 *   POST aksi=cek   visit=<visit_ID> -> dkCekRemote (GET ke Dinkes: sudah ada atau belum)
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../dinkes/dinkesService.php';
header('Content-Type: application/json; charset=utf-8');

function dkrBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if ($idc <= 0) dkrBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') dkrBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);

$visit = trim((string) ($_POST['visit'] ?? ''));
$aksi = (string) ($_POST['aksi'] ?? '');
if ($visit === '' || !in_array($aksi, ['kirim', 'cek'], true)) dkrBalas(422, ['status' => 'error', 'message' => 'Permintaan tidak valid.']);

$hasil = $aksi === 'kirim' ? dkKirimVisit($koneksi, $idc, $visit) : dkCekRemote($koneksi, $idc, $visit);
dkrBalas(200, ['status' => 'success', 'hasil' => $hasil]);
