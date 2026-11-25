<?php
/**
 * Kredensial Satu Sehat per faskes (module/administrator/master-satu-sehat). Khusus superadmin/administrator.
 *   GET                        -> daftar semua faskes + status kredensial (client secret TIDAK dikirim, hanya disamarkan)
 *   GET id_customer=           -> detail satu faskes untuk form ubah
 *   POST aksi=simpan           -> simpan kredensial & lokasi; client_secret kosong = tidak diubah
 * Perbaikan: sebelumnya
 *  - TANPA cek login/role, dan client secret seluruh faskes dikirim apa adanya ke siapa pun yang membuka URL;
 *  - detail memakai join sc.id = sh.id_customer (salah; daftar memakai id_customer), sehingga form ubah menampilkan/menyimpan
 *    kredensial faskes LAIN;
 *  - simpan menulis kolom `address` yang tidak ada di setting_satusehat -> selalu gagal (alamat ada di location_display);
 *  - faskes nonaktif (status 99) ikut tampil; pesan error SQL bocor ke klien.
 * Mengubah kredensial menghapus token cache agar permintaan berikutnya memakai kredensial baru.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
requireRole(['superadmin', 'administrator']);
$method = $_SERVER['REQUEST_METHOD'];
$operator = (string) ($_SESSION['username'] ?? '');
session_write_close();

function shJson(string $status, string $message, $data = null, int $code = 200): void
{
   http_response_code($code);
   echo json_encode(['status' => $status, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}
function shQ(mysqli $k, string $sql, string $t = '', array $a = []): array
{
   $st = $k->prepare($sql);
   if (!$st) {
      error_log('[satuSehat] ' . $k->error);
      shJson('error', 'Terjadi kesalahan server.', null, 500);
   }
   if ($t !== '') $st->bind_param($t, ...$a);
   if (!$st->execute()) {
      error_log('[satuSehat] ' . $st->error);
      shJson('error', 'Terjadi kesalahan server.', null, 500);
   }
   $r = $st->get_result();
   $out = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
   $st->close();
   return $out;
}
function shMask(?string $v): ?string
{
   $v = (string) $v;
   return $v === '' ? null : str_repeat('•', 8) . (strlen($v) > 8 ? substr($v, -4) : '');
}
function shRow(array $r): array
{
   $exp = $r['expire_at'] ? strtotime($r['expire_at']) : 0;
   return [
      'id_customer' => (int) $r['id_customer'],
      'clinic_name' => (string) $r['clinic_name'],
      'organization_id' => (string) $r['organization_id'],
      'client_id' => (string) $r['client_id'],
      'client_secret' => shMask($r['client_secret']),
      'ada' => $r['id_ss'] !== null,
      'lengkap' => trim((string) $r['organization_id']) !== '' && trim((string) $r['client_id']) !== '' && trim((string) $r['client_secret']) !== '',
      'location_id' => (string) $r['location_id'],
      'address' => (string) $r['location_display'],
      'city' => (string) $r['city'],
      'postal_code' => (string) $r['postal_code'],
      'latitude' => (string) $r['latitude'],
      'longitude' => (string) $r['longitude'],
      'token_aktif' => $exp > time(),
      'token_sampai' => $r['expire_at'],
      'diubah_oleh' => (string) $r['user'],
   ];
}
const SH_SELECT = "SELECT sc.id_customer, sc.clinic_name, sh.id_ss, sh.organization_id, sh.client_id, sh.client_secret, sh.location_id, sh.location_display,
                          sh.city, sh.postal_code, sh.latitude, sh.longitude, sh.expire_at, sh.user
                   FROM setting_clinic sc LEFT JOIN setting_satusehat sh ON sh.id_customer = sc.id_customer";

/* ------------------------------ GET ------------------------------ */
if ($method === 'GET') {
   if (isset($_GET['id_customer'])) {
      $r = shQ($koneksi, SH_SELECT . ' WHERE sc.id_customer = ? AND sc.status != 99 LIMIT 1', 'i', [(int) $_GET['id_customer']])[0] ?? null;
      if (!$r) shJson('error', 'Faskes tidak ditemukan.', null, 404);
      shJson('success', 'OK', shRow($r));
   }
   $rows = shQ($koneksi, SH_SELECT . ' WHERE sc.status != 99 ORDER BY sc.clinic_name');
   shJson('success', 'OK', array_map('shRow', $rows));
}

if ($method !== 'POST') shJson('error', 'Method tidak didukung.', null, 405);
if (($_POST['aksi'] ?? 'simpan') !== 'simpan') shJson('error', 'Aksi tidak dikenal.', null, 400);

/* ----------------------------- simpan ---------------------------- */
$idc = (int) ($_POST['id_customer'] ?? 0);
if ($idc <= 0 || !shQ($koneksi, 'SELECT 1 FROM setting_clinic WHERE id_customer = ? AND status != 99 LIMIT 1', 'i', [$idc])) {
   shJson('error', 'Faskes tidak ditemukan.', null, 404);
}
$f = fn($k) => trim((string) ($_POST[$k] ?? ''));
$org = $f('organization_id');
$cid = $f('client_id');
$sec = $f('client_secret');
$lat = str_replace(',', '.', $f('latitude'));
$lon = str_replace(',', '.', $f('longitude'));
$alamat = $f('address');
$kota = $f('city');
$pos = $f('postal_code');
$loc = $f('location_id');

$lama = shQ($koneksi, 'SELECT organization_id, client_id, client_secret FROM setting_satusehat WHERE id_customer = ? LIMIT 1', 'i', [$idc])[0] ?? null;

if ($org === '') shJson('error', 'Organization ID wajib diisi.', ['field' => 'organization_id'], 422);
if (!preg_match('/^[A-Za-z0-9._-]{1,100}$/', $org)) shJson('error', 'Organization ID hanya boleh huruf, angka, titik, minus, garis bawah.', ['field' => 'organization_id'], 422);
if ($cid === '') shJson('error', 'Client ID wajib diisi.', ['field' => 'client_id'], 422);
if (!preg_match('/^\S{1,200}$/', $cid)) shJson('error', 'Client ID tidak boleh mengandung spasi.', ['field' => 'client_id'], 422);
if ($sec === '' && !$lama) shJson('error', 'Client Secret wajib diisi untuk faskes yang belum punya kredensial.', ['field' => 'client_secret'], 422);
if ($sec !== '' && !preg_match('/^\S{1,300}$/', $sec)) shJson('error', 'Client Secret tidak boleh mengandung spasi.', ['field' => 'client_secret'], 422);
if ($lat !== '' && (!is_numeric($lat) || abs((float) $lat) > 90)) shJson('error', 'Latitude harus angka antara -90 dan 90.', ['field' => 'latitude'], 422);
if ($lon !== '' && (!is_numeric($lon) || abs((float) $lon) > 180)) shJson('error', 'Longitude harus angka antara -180 dan 180.', ['field' => 'longitude'], 422);
if (mb_strlen($alamat) > 1000 || mb_strlen($kota) > 255 || mb_strlen($pos) > 20 || mb_strlen($loc) > 255) shJson('error', 'Salah satu isian terlalu panjang.', null, 422);

$secBaru = $sec !== '' ? $sec : (string) ($lama['client_secret'] ?? '');
$berubah = !$lama || $lama['organization_id'] !== $org || $lama['client_id'] !== $cid || $lama['client_secret'] !== $secBaru;

if ($lama) {
   // kredensial berubah -> token lama tidak berlaku lagi
   $sql = 'UPDATE setting_satusehat SET organization_id = ?, client_id = ?, client_secret = ?, latitude = ?, longitude = ?, location_display = ?, city = ?, postal_code = ?, location_id = ?, user = ?'
      . ($berubah ? ', token = NULL, expire_at = NULL' : '') . ' WHERE id_customer = ?';
   $st = $koneksi->prepare($sql);
   $st && $st->bind_param('ssssssssssi', $org, $cid, $secBaru, $lat, $lon, $alamat, $kota, $pos, $loc, $operator, $idc);
} else {
   $st = $koneksi->prepare('INSERT INTO setting_satusehat (id_customer, organization_id, client_id, client_secret, latitude, longitude, location_display, city, postal_code, location_id, user) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
   $st && $st->bind_param('issssssssss', $idc, $org, $cid, $secBaru, $lat, $lon, $alamat, $kota, $pos, $loc, $operator);
}
if (!$st || !$st->execute()) {
   error_log('[satuSehat] ' . ($st ? $st->error : $koneksi->error));
   shJson('error', 'Gagal menyimpan data.', null, 500);
}
$st->close();
shJson('success', $berubah ? 'Kredensial disimpan. Token lama dihapus dan akan dibuat ulang otomatis.' : 'Data lokasi disimpan.');
