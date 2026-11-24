<?php
/**
 * Pengaturan integrasi Dinkes Deli Serdang untuk admin faskes (module/admin/setting_dinkes).
 *
 * GET                       -> konfigurasi (token disamarkan)
 * GET  ?aksi=puskesmas      -> daftar puskesmas dari Dinkes (refrensi-puskesmas)
 * GET  ?aksi=diagnosa&q=A01 -> pencarian ICD10 di Dinkes (referensi-diagnosa)
 * POST aksi=simpan          -> simpan puskes_id & aktif untuk faskes ini
 * POST aksi=tes             -> uji token (baca refrensi-puskesmas)
 * Token Dinkes satu untuk semua faskes: DINKES_TOKEN di .env.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
require_once __DIR__ . '/../dinkes/dinkesService.php';

header('Content-Type: application/json');

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Session tidak ditemukan']);
   exit;
}
requireRole(['admin', 'superadmin', 'administrator']);

function dsJawab(array $d, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
   exit;
}

function dsGagal(string $msg, int $code = 422): void
{
   dsJawab(['status' => 'error', 'message' => $msg], $code);
}

/** Panggil API Dinkes dengan token server; keluar dengan pesan error bila gagal. */
function dsAmbil(string $path, array $q = []): array
{
   if (dkToken() === '') dsGagal('Token Dinkes belum dipasang di server (DINKES_TOKEN di .env).');
   [$code, $res, $err] = dkHttp(dkToken(), 'GET', $path, $q);
   if (!dkOk($code, $res)) dsGagal(dkPesan($code, $res, (string) $err), 502);
   return $res['data'] ?? [];
}

$method = $_SERVER['REQUEST_METHOD'];
$aksi = $method === 'POST' ? ($_POST['aksi'] ?? '') : ($_GET['aksi'] ?? '');
$cfg = dkSetting($koneksi, $idc);

if ($method === 'GET') {
   if ($aksi === '') {
      dsJawab(['status' => 'success', 'token_server' => dkToken() !== '', 'data' => $cfg ? [
         'puskes_id' => $cfg['puskes_id'],
         'aktif' => (int) $cfg['aktif'],
         'diubah_oleh' => $cfg['updated_by'],
         'diubah_pada' => $cfg['updated_at'],
      ] : null]);
   }
   if ($aksi === 'puskesmas') dsJawab(['status' => 'success', 'data' => dsAmbil('refrensi-puskesmas')]);
   if ($aksi === 'diagnosa') {
      $q = trim((string) ($_GET['q'] ?? ''));
      if (mb_strlen($q) < 2) dsJawab(['status' => 'success', 'data' => []]);
      dsJawab(['status' => 'success', 'data' => dsAmbil('referensi-diagnosa', ['q' => $q])]);
   }
   dsGagal('Aksi tidak dikenal.');
}

if ($method !== 'POST') dsGagal('Method tidak diizinkan', 405);

switch ($aksi) {
   case 'simpan':
      $puskes = (int) ($_POST['puskes_id'] ?? 0);
      $aktif = !empty($_POST['aktif']) ? 1 : 0;
      if ($aktif && dkToken() === '') dsGagal('Token Dinkes belum dipasang di server (DINKES_TOKEN di .env).');
      if ($aktif && $puskes <= 0) dsGagal('Pilih puskesmas default sebelum mengaktifkan integrasi.');
      $user = (string) ($_SESSION['username'] ?? '');
      $st = $koneksi->prepare("INSERT INTO setting_dinkes (id_customer, puskes_id, aktif, updated_by) VALUES (?, NULLIF(?, 0), ?, ?)
         ON DUPLICATE KEY UPDATE puskes_id=VALUES(puskes_id), aktif=VALUES(aktif), updated_by=VALUES(updated_by)");
      $st->bind_param('iiis', $idc, $puskes, $aktif, $user);
      $st->execute();
      $st->close();
      dsJawab(['status' => 'success', 'message' => 'Pengaturan Dinkes disimpan.']);

   case 'tes':
      $data = dsAmbil('refrensi-puskesmas');
      dsJawab(['status' => 'success', 'message' => 'Terhubung ke Dinkes (' . count($data) . ' puskesmas terbaca).']);

   default:
      dsGagal('Aksi tidak dikenal.');
}
