<?php
/**
 * Pengaturan Satu Sehat untuk admin faskes (module/admin/setting_satusehat).
 * Kredensial hanya-baca (dikelola superadmin lewat module/administrator/master-satu-sehat).
 *
 * GET                  -> konfigurasi (secret disamarkan) + status token
 * GET  ?aksi=dokter    -> daftar dokter beserta NIK & ID IHS
 * POST aksi=tes        -> uji token + baca Organization
 * POST aksi=nik        -> simpan NIK dokter (ID IHS direset bila NIK berubah)
 * POST aksi=ihs        -> cari ID IHS dokter dari NIK ke Satu Sehat lalu simpan
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';

header('Content-Type: application/json');

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Session tidak ditemukan']);
   exit;
}
requireRole(['admin', 'superadmin', 'administrator']);

const SS_BASE = 'https://api-satusehat.kemkes.go.id';
const SS_FHIR = SS_BASE . '/fhir-r4/v1';

function ssJawab(array $d, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
   exit;
}

function ssGagal(string $msg, int $code = 422): void
{
   ssJawab(['status' => 'error', 'message' => $msg], $code);
}

function ssSetting(mysqli $k, int $idc): ?array
{
   $st = $k->prepare("SELECT * FROM setting_satusehat WHERE id_customer=? LIMIT 1");
   $st->bind_param('i', $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function ssDokter(mysqli $k, int $idc, int $id): ?array
{
   $st = $k->prepare("SELECT id_doctor, doctor_name, doctor_nik, idsh FROM ms_doctor WHERE id_doctor=? AND id_customer=?");
   $st->bind_param('ii', $id, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function ssHttp(string $method, string $url, array $headers, ?string $body = null): array
{
   $ch = curl_init($url);
   curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_CONNECTTIMEOUT => 8,
      CURLOPT_TIMEOUT => 20,
   ]);
   if ($body !== null) {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
   }
   $raw = curl_exec($ch);
   $err = curl_error($ch);
   $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
   curl_close($ch);
   return [$code, $raw === false ? null : json_decode($raw, true), $err];
}

/** Token akses; pakai cache di setting_satusehat bila belum kedaluwarsa (sama seperti generateToken). */
function ssToken(mysqli $k, int $idc, array $cfg, bool $paksaBaru = false): string
{
   if (!$paksaBaru && !empty($cfg['token']) && !empty($cfg['expire_at']) && strtotime($cfg['expire_at']) > time() + 60) {
      return $cfg['token'];
   }
   if (empty($cfg['client_id']) || empty($cfg['client_secret'])) {
      throw new RuntimeException('Client ID / Client Secret belum diisi. Hubungi tim IT.');
   }
   [$code, $res, $err] = ssHttp(
      'POST',
      SS_BASE . '/oauth2/v1/accesstoken?grant_type=client_credentials',
      ['Content-Type: application/x-www-form-urlencoded'],
      http_build_query(['client_id' => $cfg['client_id'], 'client_secret' => $cfg['client_secret']])
   );
   if ($err) {
      throw new RuntimeException('Tidak dapat terhubung ke Satu Sehat: ' . $err);
   }
   if (empty($res['access_token'])) {
      $pesan = $res['issue'][0]['details']['text'] ?? $res['fault']['faultstring'] ?? $res['error_description'] ?? ('HTTP ' . $code);
      throw new RuntimeException('Kredensial ditolak Satu Sehat (' . $pesan . ').');
   }
   $exp = date('Y-m-d H:i:s', time() + (int) ($res['expires_in'] ?? 3599));
   $st = $k->prepare("UPDATE setting_satusehat SET token=?, expire_at=? WHERE id_customer=?");
   $st->bind_param('ssi', $res['access_token'], $exp, $idc);
   $st->execute();
   $st->close();
   return $res['access_token'];
}

function ssGet(mysqli $k, int $idc, array $cfg, string $path): array
{
   $token = ssToken($k, $idc, $cfg);
   [$code, $res, $err] = ssHttp('GET', SS_FHIR . $path, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
   if ($code === 401) { // token cache tidak berlaku lagi -> minta baru sekali
      $token = ssToken($k, $idc, $cfg, true);
      [$code, $res, $err] = ssHttp('GET', SS_FHIR . $path, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
   }
   if ($err) {
      throw new RuntimeException('Tidak dapat terhubung ke Satu Sehat: ' . $err);
   }
   return [$code, $res];
}

function ssMasker(?string $v): ?string
{
   $v = (string) $v;
   return $v === '' ? null : str_repeat('•', 8) . (strlen($v) > 8 ? substr($v, -4) : '');
}

$method = $_SERVER['REQUEST_METHOD'];
$aksi = $method === 'POST' ? ($_POST['aksi'] ?? '') : ($_GET['aksi'] ?? '');

// ================= GET =================
if ($method === 'GET' && $aksi === '') {
   $c = ssSetting($koneksi, $idc);
   if (!$c) {
      ssJawab(['status' => 'success', 'data' => null]);
   }
   $exp = $c['expire_at'] ? strtotime($c['expire_at']) : 0;
   ssJawab(['status' => 'success', 'data' => [
      'organization_id' => $c['organization_id'],
      'client_id' => $c['client_id'],
      'client_secret' => ssMasker($c['client_secret']),
      'lengkap' => !empty($c['organization_id']) && !empty($c['client_id']) && !empty($c['client_secret']),
      'location_id' => $c['location_id'],
      'address' => $c['location_display'],
      'city' => $c['city'],
      'postal_code' => $c['postal_code'],
      'latitude' => $c['latitude'],
      'longitude' => $c['longitude'],
      'token_aktif' => $exp > time(),
      'token_sampai' => $c['expire_at'],
      'diubah_oleh' => $c['user'],
   ]]);
}

if ($method === 'GET' && $aksi === 'dokter') {
   $st = $koneksi->prepare(
      "SELECT id_doctor, doctor_name, doctor_nik, idsh, doctor_phone, doctor_status
       FROM ms_doctor WHERE id_customer=? ORDER BY doctor_status DESC, doctor_name ASC"
   );
   $st->bind_param('i', $idc);
   $st->execute();
   $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();

   $nikCount = [];
   foreach ($rows as $r) {
      $n = preg_replace('/\D/', '', (string) $r['doctor_nik']);
      if ($n !== '') {
         $nikCount[$n] = ($nikCount[$n] ?? 0) + 1;
      }
   }
   foreach ($rows as &$r) {
      $n = preg_replace('/\D/', '', (string) $r['doctor_nik']);
      $r['nik_valid'] = (bool) preg_match('/^\d{16}$/', (string) $r['doctor_nik']);
      $r['nik_dobel'] = $n !== '' && ($nikCount[$n] ?? 0) > 1;
   }
   unset($r);
   ssJawab(['status' => 'success', 'data' => $rows]);
}

if ($method !== 'POST') {
   ssGagal('Method tidak diizinkan', 405);
}

// ================= POST =================
switch ($aksi) {
   case 'nik':
      $id = (int) ($_POST['id_doctor'] ?? 0);
      $nik = preg_replace('/\s+/', '', (string) ($_POST['doctor_nik'] ?? ''));
      if (!preg_match('/^\d{16}$/', $nik)) {
         ssGagal('NIK harus 16 digit angka.');
      }
      $d = ssDokter($koneksi, $idc, $id);
      if (!$d) {
         ssGagal('Dokter tidak ditemukan.', 404);
      }
      if ($nik === (string) $d['doctor_nik']) {
         ssJawab(['status' => 'success', 'message' => 'NIK tidak berubah.', 'berubah' => false]);
      }
      // ID IHS milik NIK lama tidak berlaku lagi
      $st = $koneksi->prepare("UPDATE ms_doctor SET doctor_nik=?, idsh=NULL, updated_at=NOW() WHERE id_doctor=? AND id_customer=?");
      $st->bind_param('sii', $nik, $id, $idc);
      $st->execute();
      $st->close();
      ssJawab(['status' => 'success', 'message' => 'NIK disimpan.', 'berubah' => true]);

   case 'ihs':
      $id = (int) ($_POST['id_doctor'] ?? 0);
      $d = ssDokter($koneksi, $idc, $id);
      if (!$d) {
         ssGagal('Dokter tidak ditemukan.', 404);
      }
      $nik = preg_replace('/\s+/', '', (string) $d['doctor_nik']);
      if (!preg_match('/^\d{16}$/', $nik)) {
         ssGagal('Isi NIK 16 digit dokter ini terlebih dahulu.');
      }
      $cfg = ssSetting($koneksi, $idc);
      if (!$cfg) {
         ssGagal('Kredensial Satu Sehat faskes belum dibuat. Hubungi tim IT.', 409);
      }
      try {
         [$code, $res] = ssGet($koneksi, $idc, $cfg, '/Practitioner?identifier=' . rawurlencode('https://fhir.kemkes.go.id/id/nik|' . $nik));
      } catch (RuntimeException $e) {
         ssGagal($e->getMessage(), 502);
      }
      if ($code !== 200) {
         ssGagal('Satu Sehat merespons HTTP ' . $code . '.', 502);
      }
      $res0 = $res['entry'][0]['resource'] ?? null;
      $ihs = $res0['id'] ?? null;
      if (!$ihs) {
         ssJawab(['status' => 'error', 'message' => 'NIK ini belum terdaftar sebagai tenaga kesehatan di Satu Sehat (SISDMK). Pastikan NIK benar atau daftarkan lewat SISDMK.'], 404);
      }
      $namaSS = $res0['name'][0]['text'] ?? null;
      $st = $koneksi->prepare("UPDATE ms_doctor SET idsh=?, doctor_nik=?, updated_at=NOW() WHERE id_doctor=? AND id_customer=?");
      $st->bind_param('ssii', $ihs, $nik, $id, $idc);
      $st->execute();
      $st->close();
      ssJawab(['status' => 'success', 'message' => 'ID Satu Sehat ditemukan.', 'idsh' => $ihs, 'nama_satusehat' => $namaSS]);

   case 'tes':
      $cfg = ssSetting($koneksi, $idc);
      if (!$cfg || empty($cfg['organization_id'])) {
         ssGagal('Kredensial Satu Sehat faskes belum lengkap. Hubungi tim IT.', 409);
      }
      $t0 = microtime(true);
      try {
         ssToken($koneksi, $idc, $cfg, true);
         $cfg = ssSetting($koneksi, $idc);
         [$code, $res] = ssGet($koneksi, $idc, $cfg, '/Organization/' . rawurlencode($cfg['organization_id']));
      } catch (RuntimeException $e) {
         ssGagal($e->getMessage(), 502);
      }
      $ms = (int) round((microtime(true) - $t0) * 1000);
      if ($code !== 200 || empty($res['id'])) {
         ssJawab(['status' => 'error', 'message' => 'Token berhasil dibuat, tetapi Organization ID ' . $cfg['organization_id'] . ' tidak ditemukan / tidak sesuai (HTTP ' . $code . ').', 'ms' => $ms], 502);
      }
      ssJawab(['status' => 'success', 'message' => 'Terhubung ke Satu Sehat.', 'organisasi' => $res['name'] ?? null, 'ms' => $ms]);

   default:
      ssGagal('Aksi tidak dikenal.', 400);
}
