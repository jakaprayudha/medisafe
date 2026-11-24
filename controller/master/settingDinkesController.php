<?php
/**
 * Pengaturan integrasi Dinkes Deli Serdang untuk admin faskes (module/admin/setting_dinkes).
 *
 * GET                       -> konfigurasi (token disamarkan)
 * GET  ?aksi=puskesmas      -> daftar puskesmas dari Dinkes (refrensi-puskesmas)
 * GET  ?aksi=diagnosa&q=A01 -> pencarian ICD10 di Dinkes (referensi-diagnosa)
 * POST aksi=simpan          -> simpan token (kosong = tidak diubah), puskes_id, aktif
 * POST aksi=tes             -> uji token (baca refrensi-puskesmas)
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

/** Panggil API Dinkes dengan token tersimpan; keluar dengan pesan error bila gagal. */
function dsAmbil(array $cfg, string $path, array $q = []): array
{
   if (trim((string) ($cfg['token'] ?? '')) === '') dsGagal('Token Dinkes belum diisi.');
   [$code, $res, $err] = dkHttp((string) $cfg['token'], 'GET', $path, $q);
   if (!dkOk($code, $res)) dsGagal(dkPesan($code, $res, (string) $err), 502);
   return $res['data'] ?? [];
}

$method = $_SERVER['REQUEST_METHOD'];
$aksi = $method === 'POST' ? ($_POST['aksi'] ?? '') : ($_GET['aksi'] ?? '');
$cfg = dkSetting($koneksi, $idc);

if ($method === 'GET') {
   if ($aksi === '') {
      dsJawab(['status' => 'success', 'data' => $cfg ? [
         'token' => trim((string) $cfg['token']) === '' ? null : str_repeat('•', 8) . substr((string) $cfg['token'], -4),
         'puskes_id' => $cfg['puskes_id'],
         'aktif' => (int) $cfg['aktif'],
         'diubah_oleh' => $cfg['updated_by'],
         'diubah_pada' => $cfg['updated_at'],
      ] : null]);
   }
   if ($aksi === 'puskesmas') dsJawab(['status' => 'success', 'data' => dsAmbil($cfg ?? [], 'refrensi-puskesmas')]);
   if ($aksi === 'diagnosa') {
      $q = trim((string) ($_GET['q'] ?? ''));
      if (mb_strlen($q) < 2) dsJawab(['status' => 'success', 'data' => []]);
      dsJawab(['status' => 'success', 'data' => dsAmbil($cfg ?? [], 'referensi-diagnosa', ['q' => $q])]);
   }
   dsGagal('Aksi tidak dikenal.');
}

if ($method !== 'POST') dsGagal('Method tidak diizinkan', 405);

switch ($aksi) {
   case 'simpan':
      $token = trim((string) ($_POST['token'] ?? ''));
      $puskes = (int) ($_POST['puskes_id'] ?? 0);
      $aktif = !empty($_POST['aktif']) ? 1 : 0;
      if ($token !== '' && !preg_match('/^[\x21-\x7E ]{10,2000}$/', $token)) dsGagal('Format token tidak valid.');
      if ($token === '' && (!$cfg || trim((string) $cfg['token']) === '')) {
         if ($aktif) dsGagal('Isi token Dinkes sebelum mengaktifkan integrasi.');
      }
      if ($aktif && $puskes <= 0) dsGagal('Pilih puskesmas default sebelum mengaktifkan integrasi.');
      $user = (string) ($_SESSION['username'] ?? '');
      $st = $koneksi->prepare("INSERT INTO setting_dinkes (id_customer, token, puskes_id, aktif, updated_by) VALUES (?, NULLIF(?, ''), NULLIF(?, 0), ?, ?)
         ON DUPLICATE KEY UPDATE token=IF(VALUES(token) IS NULL, token, VALUES(token)), puskes_id=VALUES(puskes_id), aktif=VALUES(aktif), updated_by=VALUES(updated_by)");
      $st->bind_param('isiis', $idc, $token, $puskes, $aktif, $user);
      $st->execute();
      $st->close();
      dsJawab(['status' => 'success', 'message' => 'Pengaturan Dinkes disimpan.']);

   case 'tes':
      $data = dsAmbil($cfg ?? [], 'refrensi-puskesmas');
      dsJawab(['status' => 'success', 'message' => 'Terhubung ke Dinkes (' . count($data) . ' puskesmas terbaca).']);

   default:
      dsGagal('Aksi tidak dikenal.');
}
