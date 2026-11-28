<?php
/**
 * Pengaturan penomoran surat (module/admin/setting_surat).
 *
 * GET                               -> setting faskes (atau bawaan bila belum ada). Dipakai juga oleh form surat.
 * POST action=update_mode           -> simpan mode AUTO / MANUAL saja
 * POST (save_setting)               -> simpan format + nomor terakhir tiap jenis surat
 *      nomor_<jenis>_awal (opsional) -> nilai nomor saat halaman dimuat. Bila nomor tidak diubah, nilai di database
 *                                       dipertahankan (bisa sudah naik karena ada surat baru); bila diubah padahal
 *                                       database sudah berubah sejak dimuat -> 409 agar tidak menimpa nomor terbaru.
 */
date_default_timezone_set('Asia/Jakarta');
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
require_once __DIR__ . '/../../utility/activityLog.php';

header('Content-Type: application/json; charset=utf-8');

function responseJson(string $status, string $message = '', array $data = [], int $code = 200): void
{
   http_response_code($code);
   echo json_encode(['status' => $status, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
   exit;
}

if (!$koneksi) {
   responseJson('error', 'Koneksi database gagal.', [], 500);
}
if (!isset($_SESSION['id_customer']) || $_SESSION['id_customer'] === '') {
   responseJson('error', 'Session faskes/customer tidak ditemukan.', [], 401);
}

$id_customer = (string) $_SESSION['id_customer'];
$user = (string) ($_SESSION['id_user'] ?? $_SESSION['username'] ?? 'system');

/**
 * Jenis surat => [nama, format bawaan, penanda yang dikenali generator nomor di controller surat tsb].
 * Harus sama dengan generator di masing-masing controller (suratSakitController, dst.).
 */
const JENIS_SURAT = [
   'sakit'      => ['Surat Keterangan Sakit', 'SKS/{NO}/{MM}/{YYYY}', ['NO', 'DD', 'MM', 'YY', 'YYYY']],
   'berobat'    => ['Surat Keterangan Berobat', 'SKB/{NO}/{MM}/{YYYY}', ['NO', 'NO:n', 'DD', 'MM', 'YY', 'YYYY']],
   'sehat'      => ['Surat Keterangan Sehat', 'SKH/{NO}/{MM}/{YYYY}', ['NO', 'DD', 'MM', 'YYYY']],
   'rawat_inap' => ['Surat Rawat Inap', 'SRI/{NO}/{MM}/{YYYY}', ['NO', 'DD', 'MM', 'YY', 'YYYY']],
   'kematian'   => ['Surat Keterangan Kematian', 'SKM/{NO}/{MM}/{YYYY}', ['NO', 'NO:n', 'DD', 'MM', 'YYYY']],
   'mata'       => ['Surat Pemeriksaan Mata', 'SPM/{NO}/{MM}/{YYYY}', ['NO', 'MM', 'YYYY', 'ROMAN']],
];

function ambilSetting(mysqli $k, string $idc): ?array
{
   $st = $k->prepare("SELECT * FROM setting_surat WHERE id_customer = ? LIMIT 1");
   $st->bind_param('s', $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc() ?: null;
   $st->close();
   return $r;
}

/** Pesan error bila format tidak valid untuk jenis surat ini, null bila valid. */
function cekFormat(string $format, array $penanda): ?string
{
   if ($format === '') {
      return 'tidak boleh kosong';
   }
   if (mb_strlen($format) > 100) {
      return 'maksimal 100 karakter';
   }
   preg_match_all('/\{([^{}]*)\}/', $format, $m);
   $adaNo = false;
   foreach ($m[1] as $t) {
      $kunci = preg_match('/^NO:\d{1,2}$/', $t) ? 'NO:n' : $t;
      if (!in_array($kunci, $penanda, true)) {
         return 'penanda {' . $t . '} tidak dikenali';
      }
      $adaNo = $adaNo || $kunci === 'NO' || $kunci === 'NO:n';
   }
   if (!$adaNo) {
      return 'wajib memuat {NO} agar setiap surat bernomor unik';
   }
   if (preg_match('/[{}]/', preg_replace('/\{[^{}]*\}/', '', $format))) {
      return 'kurung kurawal tidak berpasangan';
   }
   return null;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
   $data = ambilSetting($koneksi, $id_customer);
   if (!$data) {
      $data = ['id' => null, 'id_customer' => $id_customer, 'mode_nomor' => 'AUTO'];
      foreach (JENIS_SURAT as $j => $def) {
         $data["format_$j"] = $def[1];
         $data["nomor_$j"] = 0;
      }
   }
   responseJson('success', 'Setting nomor surat berhasil diambil.', $data);
}

if ($method !== 'POST') {
   responseJson('error', 'Method tidak diizinkan.', [], 405);
}

requireRole(['admin', 'superadmin', 'administrator']);

/** Log perubahan setting_surat (hanya kolom yang berubah). */
function ssLog(mysqli $k, ?array $lama, ?array $baru, string $ket): void
{
   $buang = array_flip(['updated_at', 'updated_by', 'created_at', 'created_by']);
   $l = $lama ? array_diff_key($lama, $buang) : null;
   $b = $baru ? array_diff_key($baru, $buang) : null;
   if ($l && $b) {
      [$l, $b] = logBeda($l, $b);
      if (!$b) return;
   }
   logAktivitas($k, 'pengaturan', $lama ? 'ubah' : 'tambah', ['tipe' => 'setting_surat', 'id' => $baru['id'] ?? ($lama['id'] ?? ''), 'label' => 'Penomoran surat'], $ket, $l, $b);
}

$action = $_POST['action'] ?? 'save_setting';
$mode = strtoupper(trim((string) ($_POST['mode_nomor'] ?? '')));
if (!in_array($mode, ['AUTO', 'MANUAL'], true)) {
   responseJson('error', 'Mode penomoran tidak valid.', [], 422);
}

$ada = ambilSetting($koneksi, $id_customer);

if ($action === 'update_mode') {
   if ($ada) {
      $st = $koneksi->prepare("UPDATE setting_surat SET mode_nomor = ?, updated_at = NOW(), updated_by = ? WHERE id_customer = ?");
      $st->bind_param('sss', $mode, $user, $id_customer);
   } else {
      $st = $koneksi->prepare("INSERT INTO setting_surat (id_customer, mode_nomor, created_at, created_by) VALUES (?, ?, NOW(), ?)");
      $st->bind_param('sss', $id_customer, $mode, $user);
   }
   if (!$st->execute()) {
      error_log('[settingSuratController] ' . $st->error);
      responseJson('error', 'Mode penomoran gagal disimpan.', [], 500);
   }
   $st->close();
   ssLog($koneksi, $ada, ambilSetting($koneksi, $id_customer), 'Mode penomoran surat disimpan');
   responseJson('success', 'Mode penomoran berhasil disimpan.', ambilSetting($koneksi, $id_customer) ?? []);
}

/* ---------- simpan format + nomor ---------- */
$nilai = [];
$salah = [];
$bentrok = [];
foreach (JENIS_SURAT as $j => [$nama, $bawaan, $penanda]) {
   $format = trim((string) ($_POST["format_$j"] ?? ($ada["format_$j"] ?? $bawaan)));
   if ($e = cekFormat($format, $penanda)) {
      $salah[] = "Format $nama: $e.";
   }

   $raw = trim((string) ($_POST["nomor_$j"] ?? ''));
   $nomor = $raw === '' ? (int) ($ada["nomor_$j"] ?? 0) : filter_var($raw, FILTER_VALIDATE_INT);
   if ($nomor === false || $nomor < 0 || $nomor > 99999999) {
      $salah[] = "Nomor terakhir $nama harus angka bulat 0 – 99.999.999.";
      $nomor = 0;
   }

   // jaga nomor yang sudah naik karena surat baru dibuat selama halaman setting terbuka
   $db = (int) ($ada["nomor_$j"] ?? 0);
   if ($ada && isset($_POST["nomor_{$j}_awal"]) && $_POST["nomor_{$j}_awal"] !== '') {
      $awal = (int) $_POST["nomor_{$j}_awal"];
      if ($nomor === $awal) {
         $nomor = $db;
      } elseif ($db !== $awal) {
         $bentrok[] = "$nama (sekarang $db)";
      }
   }
   $nilai[$j] = [$format, (int) $nomor];
}

if ($salah) {
   responseJson('error', implode(' ', $salah), ['errors' => $salah], 422);
}
if ($bentrok) {
   responseJson(
      'error',
      'Nomor terakhir sudah berubah sejak halaman dibuka karena ada surat baru: ' . implode(', ', $bentrok)
         . '. Muat ulang halaman lalu ubah lagi.',
      ['conflict' => true],
      409
   );
}

$kolom = [];
$tipe = 's';
$param = [$mode];
foreach ($nilai as $j => [$f, $n]) {
   $kolom[] = "format_$j";
   $kolom[] = "nomor_$j";
   $tipe .= 'si';
   array_push($param, $f, $n);
}

if ($ada) {
   $set = implode(', ', array_map(fn($c) => "$c = ?", $kolom));
   $sql = "UPDATE setting_surat SET mode_nomor = ?, $set, updated_at = NOW(), updated_by = ? WHERE id_customer = ?";
   $tipe .= 'ss';
   array_push($param, $user, $id_customer);
} else {
   $sql = "INSERT INTO setting_surat (mode_nomor, " . implode(', ', $kolom) . ", created_at, created_by, id_customer)
           VALUES (?, " . implode(', ', array_fill(0, count($kolom), '?')) . ", NOW(), ?, ?)";
   $tipe .= 'ss';
   array_push($param, $user, $id_customer);
}

$st = $koneksi->prepare($sql);
$st->bind_param($tipe, ...$param);
if (!$st->execute()) {
   error_log('[settingSuratController] ' . $st->error);
   responseJson('error', 'Pengaturan nomor surat gagal disimpan.', [], 500);
}
$st->close();
ssLog($koneksi, $ada, ambilSetting($koneksi, $id_customer), 'Format & nomor terakhir surat disimpan');

responseJson('success', 'Pengaturan nomor surat berhasil disimpan.', ambilSetting($koneksi, $id_customer) ?? []);
