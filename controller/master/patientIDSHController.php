<?php
/**
 * Ringkasan ID Satu Sehat (IHS) pasien seluruh faskes (module/administrator/patient-idsh).
 *   GET [refresh=1] -> { faskes: [{id_customer, nama, total, lengkap, antre, tidak_ditemukan, nik, pct}], total: {...} }
 * Status per pasien sama dengan patientIDSHDetailControllerByClinic: lengkap (punya IHS) · antre (NIK valid, belum dicek)
 * · tidak_ditemukan (NIK valid, sudah dicek) · nik (NIK kosong / bukan 16 digit).
 * Perbaikan: sebelumnya TANPA cek login/role dan hanya menghitung "NIK terisi / IDSH terisi / kurang" tanpa membedakan
 * penyebabnya. Kini khusus superadmin/administrator, satu query teragregasi + cache file 5 menit.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
requireRole(['superadmin', 'administrator']);
session_write_close();

function piJawab(array $d, int $c = 200): void
{
   http_response_code($c);
   echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}
function piGagal(mysqli $k): void
{
   error_log('[patientIDSH] ' . $k->error);
   piJawab(['status' => 'error', 'message' => 'Gagal memuat data pasien.'], 500);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') piJawab(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);

const PI_TTL = 300;
$cacheFile = sys_get_temp_dir() . '/medisafe_patientidsh.json';
if (empty($_GET['refresh']) && is_file($cacheFile) && time() - filemtime($cacheFile) < PI_TTL) {
   $c = json_decode((string) file_get_contents($cacheFile), true);
   if (is_array($c)) {
      $c['cache'] = time() - filemtime($cacheFile);
      piJawab($c);
   }
}

$t0 = microtime(true);
$ada = "(p.idsh IS NOT NULL AND p.idsh <> '')";
$nikOk = "p.patient_nik REGEXP '^[0-9]{16}$'";
$rs = $koneksi->query("SELECT p.id_customer, COUNT(*) total,
      SUM($ada) lengkap,
      SUM(NOT $ada AND $nikOk AND COALESCE(p.is_checked_satusehat, 0) <> 1) antre,
      SUM(NOT $ada AND $nikOk AND COALESCE(p.is_checked_satusehat, 0) = 1) tidak_ditemukan,
      SUM(NOT $ada AND (p.patient_nik IS NULL OR NOT $nikOk)) nik
   FROM ms_patient p GROUP BY p.id_customer");
if (!$rs) piGagal($koneksi);
$agg = [];
foreach ($rs->fetch_all(MYSQLI_ASSOC) as $r) $agg[(int) $r['id_customer']] = $r;

$rs = $koneksi->query("SELECT id_customer, clinic_name FROM setting_clinic WHERE status != 99 ORDER BY clinic_name");
if (!$rs) piGagal($koneksi);
$kunci = ['total', 'lengkap', 'antre', 'tidak_ditemukan', 'nik'];
$faskes = [];
$jumlah = array_fill_keys($kunci, 0);
foreach ($rs->fetch_all(MYSQLI_ASSOC) as $c) {
   $id = (int) $c['id_customer'];
   $row = ['id_customer' => $id, 'nama' => (string) $c['clinic_name']];
   foreach ($kunci as $k) {
      $row[$k] = (int) ($agg[$id][$k] ?? 0);
      $jumlah[$k] += $row[$k];
   }
   $row['pct'] = $row['total'] ? round($row['lengkap'] / $row['total'] * 100, 1) : null;
   $faskes[] = $row;
}
$jumlah['pct'] = $jumlah['total'] ? round($jumlah['lengkap'] / $jumlah['total'] * 100, 1) : null;

$out = ['status' => 'success', 'faskes' => $faskes, 'total' => $jumlah, 'cache' => 0, 'ms' => (int) round((microtime(true) - $t0) * 1000)];
$tmp = $cacheFile . '.' . getmypid();
if (@file_put_contents($tmp, json_encode($out, JSON_UNESCAPED_UNICODE)) !== false) @rename($tmp, $cacheFile);
piJawab($out);
