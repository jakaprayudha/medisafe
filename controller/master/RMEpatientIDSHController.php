<?php
/**
 * Ringkasan monitoring kelengkapan RME seluruh faskes (module/administrator/monitoring-visit).
 *   GET from=&to=[&refresh=1]  -> { faskes: [...], total: {...}, cache }
 * Perbaikan: sebelumnya TANPA cek login/role, memindai seluruh 214 rb kunjungan (+ join 180 rb resep tanpa indeks, ~2,4 dtk)
 * tanpa batas periode, dan hanya menghitung diagnosa/obat/lab. Kini: khusus superadmin/administrator, dibatasi periode
 * (maks 366 hari), satu query teragregasi + cache file 60 detik, dan menghitung semua item checklist rawat jalan
 * (sama dengan controller/dashboard/rmeMonitoringController.php). Rawat inap dihitung terpisah; detailnya lewat
 * rmeMonitoringController?jenis=ranap.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
requireRole(['superadmin', 'administrator']);
session_write_close();

function rmJawab(array $d, int $c = 200): void
{
   http_response_code($c);
   echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') rmJawab(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);

$tgl = function ($v, $d) {
   $v = (string) $v;
   $x = DateTime::createFromFormat('!Y-m-d', $v);
   return $x && $x->format('Y-m-d') === $v ? $v : $d;
};
$to = min($tgl($_GET['to'] ?? '', date('Y-m-d')), date('Y-m-d'));
$from = $tgl($_GET['from'] ?? '', date('Y-m-01'));
if ($to < $from) [$from, $to] = [$to, $from];
if ((new DateTime($from))->diff(new DateTime($to))->days > 366) rmJawab(['status' => 'error', 'message' => 'Rentang maksimal 366 hari.'], 422);

$cacheFile = sys_get_temp_dir() . '/medisafe_rmesum_' . md5($from . $to) . '.json';
if (empty($_GET['refresh']) && is_file($cacheFile) && time() - filemtime($cacheFile) < 60) {
   $c = json_decode((string) file_get_contents($cacheFile), true);
   if (is_array($c)) {
      $c['cache'] = time() - filemtime($cacheFile);
      rmJawab($c);
   }
}

$t0 = microtime(true);
$f = fn($c) => "(TRIM(COALESCE(p.$c, '')) NOT IN ('', '-'))";
$sql = "SELECT v.id_customer,
           COUNT(*) total,
           SUM(v.ranap) ranap,
           SUM(1 - v.ranap) jalan,
           SUM((1 - v.ranap) * v.f_ttv) ttv,
           SUM((1 - v.ranap) * v.f_bbtb) bbtb,
           SUM((1 - v.ranap) * v.f_anam) anam,
           SUM((1 - v.ranap) * v.f_dx) dx,
           SUM((1 - v.ranap) * v.f_terapi) terapi,
           SUM((1 - v.ranap) * v.f_resep) resep,
           SUM((1 - v.ranap) * v.f_selesai) selesai,
           SUM((1 - v.ranap) * v.bpjs) bpjs,
           SUM((1 - v.ranap) * v.bpjs * v.f_pcare) pcare,
           SUM((1 - v.ranap) * v.f_enc) satusehat,
           SUM((1 - v.ranap) * v.f_ttv * v.f_bbtb * v.f_anam * v.f_dx * v.f_terapi * v.f_selesai) lengkap
        FROM (SELECT p.id_customer, COALESCE(p.status_rawatinap, 0) = 1 ranap,
                 (p.id_provider = 1) bpjs, (p.visit_status = 4) f_selesai,
                 ({$f('tekanan_darah')} AND {$f('nadi')} AND {$f('suhu')}) f_ttv,
                 ({$f('berat_badan')} AND {$f('tinggi_badan')}) f_bbtb,
                 ({$f('anamnesa')} OR {$f('keluhan_penyerta')}) f_anam,
                 {$f('diagnosa')} f_dx,
                 ({$f('tindakan')} OR r.id_visit IS NOT NULL) f_terapi,
                 (r.id_visit IS NOT NULL) f_resep,
                 {$f('noKunjung')} f_pcare, {$f('id_encounter')} f_enc
              FROM pasien_visit p
              LEFT JOIN (SELECT DISTINCT id_visit FROM permintaan_pharmacy
                         WHERE created_at >= ? AND created_at < ? + INTERVAL 2 DAY) r ON r.id_visit = p.visit_ID
              WHERE p.visit_date BETWEEN ? AND ? AND (p.visit_status IS NULL OR p.visit_status NOT IN (99, 10))) v
        GROUP BY v.id_customer";
$st = $koneksi->prepare($sql);
if (!$st) {
   error_log('[rmeSummary] ' . $koneksi->error);
   rmJawab(['status' => 'error', 'message' => 'Gagal memuat data.'], 500);
}
$st->bind_param('ssss', $from, $to, $from, $to);
$st->execute();
$agg = [];
foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $agg[(int) $r['id_customer']] = $r;
$st->close();

$rs = $koneksi->query("SELECT id_customer, clinic_name FROM setting_clinic WHERE status != 99 ORDER BY clinic_name");
if (!$rs) {
   error_log('[rmeSummary] ' . $koneksi->error);
   rmJawab(['status' => 'error', 'message' => 'Gagal memuat data.'], 500);
}
$kunci = ['total', 'ranap', 'jalan', 'ttv', 'bbtb', 'anam', 'dx', 'terapi', 'resep', 'selesai', 'bpjs', 'pcare', 'satusehat', 'lengkap'];
$faskes = [];
$jumlah = array_fill_keys($kunci, 0);
foreach ($rs->fetch_all(MYSQLI_ASSOC) as $c) {
   $id = (int) $c['id_customer'];
   $a = $agg[$id] ?? [];
   $row = ['id_customer' => $id, 'nama' => (string) $c['clinic_name']];
   foreach ($kunci as $k) {
      $row[$k] = (int) ($a[$k] ?? 0);
      $jumlah[$k] += $row[$k];
   }
   $row['pct'] = $row['jalan'] ? round($row['lengkap'] / $row['jalan'] * 100, 1) : null;
   $faskes[] = $row;
}
$jumlah['pct'] = $jumlah['jalan'] ? round($jumlah['lengkap'] / $jumlah['jalan'] * 100, 1) : null;

$out = ['status' => 'success', 'periode' => ['from' => $from, 'to' => $to], 'faskes' => $faskes, 'total' => $jumlah, 'cache' => 0, 'ms' => (int) round((microtime(true) - $t0) * 1000)];
$tmp = $cacheFile . '.' . getmypid();
if (@file_put_contents($tmp, json_encode($out, JSON_UNESCAPED_UNICODE)) !== false) @rename($tmp, $cacheFile);
rmJawab($out);
