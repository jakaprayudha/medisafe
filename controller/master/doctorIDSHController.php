<?php
/**
 * Ringkasan ID Satu Sehat (IHS) dokter seluruh faskes (module/administrator/doctor-idsh).
 *   GET -> { faskes: [{id_customer, nama, total, lengkap, cari, nik, dobel, terdampak, kunjungan}], total: {...} }
 * Hanya dokter aktif. Status sama dengan doctorIDSHDetailControllerByClinic: lengkap (punya IHS) · cari (NIK 16 digit,
 * IHS belum dicari) · nik (NIK kosong / bukan 16 digit). "dobel" = NIK sama dipakai >1 dokter aktif di faskes yang sama.
 * "terdampak" = kunjungan 30 hari terakhir yang dokternya belum punya IHS (tidak bisa dikirim ke Satu Sehat).
 * Perbaikan: sebelumnya TANPA cek login/role dan hanya menghitung "NIK terisi / IDSH terisi / kurang" tanpa dampak.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
requireRole(['superadmin', 'administrator']);
session_write_close();

function diJawab(array $d, int $c = 200): void
{
   http_response_code($c);
   echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}
function diGagal(mysqli $k): void
{
   error_log('[doctorIDSH] ' . $k->error);
   diJawab(['status' => 'error', 'message' => 'Gagal memuat data dokter.'], 500);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') diJawab(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);

$t0 = microtime(true);
$rs = $koneksi->query("SELECT id_customer, doctor_name, doctor_nik, idsh FROM ms_doctor WHERE doctor_status = 1");
if (!$rs) diGagal($koneksi);
$dok = [];
$nikHitung = [];
foreach ($rs->fetch_all(MYSQLI_ASSOC) as $d) {
   $c = (int) $d['id_customer'];
   $dok[$c][] = $d;
   $n = preg_replace('/\D/', '', (string) $d['doctor_nik']);
   if ($n !== '') $nikHitung[$c][$n] = ($nikHitung[$c][$n] ?? 0) + 1;
}

// kunjungan 30 hari terakhir per faskes & nama dokter (pasien_visit.id_doctor menyimpan NAMA dokter)
$awal = date('Y-m-d', strtotime('-29 days'));
$st = $koneksi->prepare("SELECT id_customer, LOWER(TRIM(id_doctor)) d, COUNT(*) n FROM pasien_visit
                         WHERE visit_date >= ? AND (visit_status IS NULL OR visit_status <> 99) GROUP BY id_customer, d");
if (!$st) diGagal($koneksi);
$st->bind_param('s', $awal);
$st->execute();
$kunj = [];
$kunjTotal = [];
foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
   $kunj[(int) $r['id_customer']][(string) $r['d']] = (int) $r['n'];
   $kunjTotal[(int) $r['id_customer']] = ($kunjTotal[(int) $r['id_customer']] ?? 0) + (int) $r['n'];
}
$st->close();

$rs = $koneksi->query("SELECT id_customer, clinic_name FROM setting_clinic WHERE status != 99 ORDER BY clinic_name");
if (!$rs) diGagal($koneksi);
$kunci = ['total', 'lengkap', 'cari', 'nik', 'dobel', 'terdampak', 'kunjungan'];
$faskes = [];
$jumlah = array_fill_keys($kunci, 0);
foreach ($rs->fetch_all(MYSQLI_ASSOC) as $c) {
   $id = (int) $c['id_customer'];
   $row = ['id_customer' => $id, 'nama' => (string) $c['clinic_name'], 'total' => 0, 'lengkap' => 0, 'cari' => 0, 'nik' => 0, 'dobel' => 0, 'terdampak' => 0, 'kunjungan' => $kunjTotal[$id] ?? 0];
   foreach ($dok[$id] ?? [] as $d) {
      $row['total']++;
      $nik = trim((string) $d['doctor_nik']);
      $digit = preg_replace('/\D/', '', $nik);
      $ada = trim((string) $d['idsh']) !== '';
      if ($ada) $row['lengkap']++;
      elseif (preg_match('/^\d{16}$/', $nik)) $row['cari']++;
      else $row['nik']++;
      if ($digit !== '' && ($nikHitung[$id][$digit] ?? 0) > 1) $row['dobel']++;
      if (!$ada) $row['terdampak'] += $kunj[$id][mb_strtolower(trim((string) $d['doctor_name']))] ?? 0;
   }
   foreach ($kunci as $k) $jumlah[$k] += $row[$k];
   $row['pct'] = $row['total'] ? round($row['lengkap'] / $row['total'] * 100, 1) : null;
   $faskes[] = $row;
}
$jumlah['pct'] = $jumlah['total'] ? round($jumlah['lengkap'] / $jumlah['total'] * 100, 1) : null;

diJawab(['status' => 'success', 'faskes' => $faskes, 'total' => $jumlah, 'ms' => (int) round((microtime(true) - $t0) * 1000)]);
