<?php
/**
 * Kelengkapan data per faskes untuk halaman Data Import (module/administrator/data-import). Khusus superadmin/administrator.
 *   GET [refresh=1] -> { data: [{id_customer, clinic_name, pasien, dokter, nakes, farmasi, visit, poli, tarif, provider}] }
 * Perbaikan: sebelumnya TANPA cek login/role; 8 subquery COUNT(*) berkorelasi per faskes (~1,4 dtk, memindai ulang tabel
 * kunjungan & pasien untuk tiap faskes); SELECT f.* mengirim seluruh kolom setting_clinic; GET ?id= memakai tabel/kolom
 * yang tidak ada (pasien, dokter, visit...); pesan error SQL bocor. Kini satu GROUP BY per tabel (~0,35 dtk total) dan
 * dicache 5 menit.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
requireRole(['superadmin', 'administrator']);
session_write_close();

function fiJawab(array $d, int $c = 200): void
{
   http_response_code($c);
   echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') fiJawab(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);

$cache = sys_get_temp_dir() . '/medisafe_faskesimport.json';
if (empty($_GET['refresh']) && is_file($cache) && time() - filemtime($cache) < 300) {
   $c = json_decode((string) file_get_contents($cache), true);
   if (is_array($c)) {
      $c['cache'] = time() - filemtime($cache);
      fiJawab($c);
   }
}

$t0 = microtime(true);
$tabel = [
   'pasien' => 'ms_patient', 'dokter' => 'ms_doctor', 'nakes' => 'ms_nurse', 'farmasi' => 'ms_pharmacy',
   'visit' => 'pasien_visit', 'poli' => 'ms_poli', 'tarif' => 'ms_tarif', 'provider' => 'ms_provider',
];
$hitung = [];
foreach ($tabel as $kunci => $t) {
   $rs = $koneksi->query("SELECT id_customer, COUNT(*) n FROM $t GROUP BY id_customer");
   if (!$rs) {
      error_log('[faskesImport] ' . $t . ': ' . $koneksi->error);
      fiJawab(['status' => 'error', 'message' => 'Gagal memuat data.'], 500);
   }
   foreach ($rs->fetch_all(MYSQLI_ASSOC) as $r) $hitung[$kunci][(int) $r['id_customer']] = (int) $r['n'];
}
$rs = $koneksi->query("SELECT id_customer, clinic_name FROM setting_clinic WHERE status != 99 ORDER BY clinic_name");
if (!$rs) {
   error_log('[faskesImport] ' . $koneksi->error);
   fiJawab(['status' => 'error', 'message' => 'Gagal memuat data.'], 500);
}
$data = [];
foreach ($rs->fetch_all(MYSQLI_ASSOC) as $c) {
   $id = (int) $c['id_customer'];
   $row = ['id_customer' => $id, 'clinic_name' => (string) $c['clinic_name']];
   foreach ($tabel as $kunci => $_) $row[$kunci] = $hitung[$kunci][$id] ?? 0;
   $data[] = $row;
}
$out = ['status' => 'success', 'data' => $data, 'cache' => 0, 'ms' => (int) round((microtime(true) - $t0) * 1000)];
$tmp = $cache . '.' . getmypid();
if (@file_put_contents($tmp, json_encode($out, JSON_UNESCAPED_UNICODE)) !== false) @rename($tmp, $cache);
fiJawab($out);
