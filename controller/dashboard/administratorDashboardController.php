<?php

/**
 * Dashboard Superadmin (module/dashboard/dashboard-administrator) — data GLOBAL seluruh faskes.
 *   GET ?action=dashboard&periode=today|week|month|custom&tanggal_mulai=..&tanggal_selesai=..[&refresh=1]
 *
 * Perbaikan performa & keamanan:
 *  - dulu TANPA cek login/role: siapa pun bisa membaca angka global; sekarang hanya superadmin (401/403).
 *  - session dilepas (session_write_close) agar halaman lain tidak menunggu request ini.
 *  - ~16 query -> 6-8: hitung faskes 3x -> 1x; COUNT(DISTINCT id_patient) di pasien_visit (0,5 dtk) diganti hitung
 *    ms_patient digabung dengan IDSH (1 query); RME lengkap/total 2 scan -> 1 scan; SHOW COLUMNS tiap pemuatan -> sekali.
 *  - angka global yang jarang berubah (faskes, pasien, IDSH, SatuSehat) di-cache di file sementara (5 menit),
 *    data periode (kunjungan, RME) 60 detik; ?refresh=1 memaksa hitung ulang. Hanya angka agregat yang di-cache.
 *  - rentang tanggal dibatasi maks. 366 hari (dulu bebas -> scan tabel penuh).
 *  - tidak ada lagi pesan "belum tersedia": import, SatuSehat, eKYC dan kontrak kini dibaca dari tabel yang ada.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
include '../../database/connect.php';
mysqli_report(MYSQLI_REPORT_OFF);

$roles = $_SESSION['roles'] ?? '';
$login = isset($_SESSION['uid_user']);
session_write_close();
if (!$login) {
   http_response_code(401);
   echo json_encode(['status' => false, 'message' => 'Sesi berakhir, silakan login ulang.']);
   exit;
}
if ($roles !== 'superadmin') {
   http_response_code(403);
   echo json_encode(['status' => false, 'message' => 'Akses hanya untuk superadmin.']);
   exit;
}

function dbJawab(array $d): void
{
   echo json_encode(['status' => true, 'message' => 'Dashboard berhasil diambil.'] + $d, JSON_UNESCAPED_UNICODE);
   exit;
}

/** Satu baris hasil query (atau []). */
function dbSatu(string $sql): array
{
   global $koneksi;
   $r = mysqli_query($koneksi, $sql);
   if (!$r) return [];
   $row = mysqli_fetch_assoc($r) ?: [];
   mysqli_free_result($r);
   return $row;
}

function dbSemua(string $sql): array
{
   global $koneksi;
   $r = mysqli_query($koneksi, $sql);
   if (!$r) return [];
   $rows = mysqli_fetch_all($r, MYSQLI_ASSOC);
   mysqli_free_result($r);
   return $rows;
}

/** Cache agregat di file sementara. $ttl detik; $segar=true -> hitung ulang. Mengembalikan [data, umurDetik]. */
function dbCache(string $kunci, int $ttl, bool $segar, callable $hitung): array
{
   $file = sys_get_temp_dir() . '/medisafe_dash_' . md5($kunci) . '.json';
   if (!$segar && is_file($file) && ($umur = time() - filemtime($file)) < $ttl) {
      $d = json_decode((string) @file_get_contents($file), true);
      if (is_array($d)) return [$d, $umur];
   }
   $d = $hitung();
   $tmp = $file . '.' . bin2hex(random_bytes(4));
   if (@file_put_contents($tmp, json_encode($d)) !== false) @rename($tmp, $file);   // tulis atomik
   return [$d, 0];
}

function dbPersen($a, $b): float
{
   return (float) $b > 0 ? round($a / $b * 100, 1) : 0.0;
}

/* ===== periode ===== */
function dbPeriode(): array
{
   $tipe = $_GET['periode'] ?? 'today';
   $hari = date('Y-m-d');
   $valid = fn($s) => ($d = DateTime::createFromFormat('!Y-m-d', (string) $s)) && $d->format('Y-m-d') === $s;
   if ($tipe === 'week') [$a, $b] = [date('Y-m-d', strtotime('monday this week')), $hari];
   elseif ($tipe === 'month') [$a, $b] = [date('Y-m-01'), $hari];
   elseif ($tipe === 'custom' && $valid($_GET['tanggal_mulai'] ?? '') && $valid($_GET['tanggal_selesai'] ?? '')) [$a, $b] = [$_GET['tanggal_mulai'], $_GET['tanggal_selesai']];
   else [$tipe, $a, $b] = ['today', $hari, $hari];
   if ($a > $b) [$a, $b] = [$b, $a];
   if ($b > $hari) $b = $hari;                                   // masa depan tidak ada datanya
   if ($a > $b) $a = $b;
   if ((strtotime($b) - strtotime($a)) / 86400 > 366) $a = date('Y-m-d', strtotime($b . ' -366 days'));
   return ['type' => $tipe, 'start' => $a, 'end' => $b];
}

/* ===== blok data ===== */
function dbGlobal(): array
{
   global $koneksi;
   $f = dbSatu("SELECT COUNT(*) total, COALESCE(SUM(faskes_status = 1), 0) aktif, COALESCE(SUM(faskes_status = 0 OR faskes_status IS NULL), 0) pending FROM ms_faskes");
   $p = dbSatu("SELECT COUNT(*) total, COALESCE(SUM(idsh IS NOT NULL AND TRIM(idsh) <> ''), 0) idsh FROM ms_patient");
   // kolom IDSH dokter: dicek sekali (SHOW COLUMNS), bukan satu query per kandidat
   $kol = array_column(dbSemua("SHOW COLUMNS FROM ms_doctor"), 'Field');
   $kolIdsh = null;
   foreach (['idsh', 'doctor_idsh', 'id_sh', 'doctor_id_sh'] as $c) if (in_array($c, $kol, true)) { $kolIdsh = $c; break; }
   $d = $kolIdsh
      ? dbSatu("SELECT COUNT(*) total, COALESCE(SUM(`$kolIdsh` IS NOT NULL AND TRIM(`$kolIdsh`) <> ''), 0) idsh FROM ms_doctor")
      : dbSatu("SELECT COUNT(*) total, 0 idsh FROM ms_doctor");
   $e = dbSemua("SELECT status_verifikasi s, COUNT(*) n FROM tte_ekyc GROUP BY status_verifikasi");
   $ekyc = array_column($e, 'n', 's');
   $im = dbSemua("SELECT status s, COUNT(*) n FROM import_jobs GROUP BY status");
   $imp = array_column($im, 'n', 's');
   $terbaru = dbSemua("SELECT mf.id_faskes, sc.clinic_name, mf.faskes_status, mf.faskes_city, mf.faskes_district, mf.created_at
      FROM ms_faskes mf LEFT JOIN setting_clinic sc ON sc.id = mf.id_clinic ORDER BY mf.id_faskes DESC LIMIT 6");
   $jobs = dbSemua("SELECT type, id_faskes, status, total_rows, processed_rows, success_count, error_count, created_at FROM import_jobs ORDER BY id DESC LIMIT 5");
   return [
      'faskes' => ['total' => (int) ($f['total'] ?? 0), 'aktif' => (int) ($f['aktif'] ?? 0), 'pending' => (int) ($f['pending'] ?? 0)],
      'pasien' => ['total' => (int) ($p['total'] ?? 0), 'idsh' => (int) ($p['idsh'] ?? 0)],
      'dokter' => ['total' => (int) ($d['total'] ?? 0), 'idsh' => (int) ($d['idsh'] ?? 0)],
      'ekyc' => ['menunggu' => (int) ($ekyc['ktp_menunggu'] ?? 0), 'total' => array_sum($ekyc)],
      'import' => ['status' => $imp, 'jobs' => $jobs],
      'faskes_terbaru' => $terbaru,
   ];
}

function dbPeriodeData(string $a, string $b): array
{
   $ea = mysqli_real_escape_string($GLOBALS['koneksi'], $a);
   $eb = mysqli_real_escape_string($GLOBALS['koneksi'], $b);
   // 1 scan: total kunjungan + RME lengkap + faskes aktif (semua memakai indeks idx_date_cust)
   $k = dbSatu("SELECT COUNT(*) total, COUNT(DISTINCT id_customer) faskes,
         COALESCE(SUM(TRIM(COALESCE(anamnesa, '')) <> '' AND TRIM(COALESCE(diagnosa, '')) <> '' AND TRIM(COALESCE(tindakan, '')) <> ''), 0) lengkap
      FROM pasien_visit WHERE visit_date BETWEEN '$ea' AND '$eb'");
   $hari = dbSemua("SELECT visit_date d, COUNT(*) n FROM pasien_visit WHERE visit_date BETWEEN '$ea' AND '$eb' GROUP BY visit_date ORDER BY visit_date");
   $top = dbSemua("SELECT v.id_customer, COUNT(*) n FROM pasien_visit v WHERE v.visit_date BETWEEN '$ea' AND '$eb' GROUP BY v.id_customer ORDER BY n DESC LIMIT 6");
   if ($top) {
      $ids = implode(',', array_map(fn($r) => (int) $r['id_customer'], $top));
      $nama = array_column(dbSemua("SELECT id_customer, clinic_name FROM setting_clinic WHERE id_customer IN ($ids)"), 'clinic_name', 'id_customer');
      foreach ($top as &$r) $r['nama'] = $nama[$r['id_customer']] ?? ('Faskes #' . $r['id_customer']);
      unset($r);
   }
   return [
      'kunjungan' => ['total' => (int) ($k['total'] ?? 0), 'faskes_aktif' => (int) ($k['faskes'] ?? 0), 'lengkap' => (int) ($k['lengkap'] ?? 0), 'per_hari' => $hari, 'top' => $top],
   ];
}

/** Rekap pengiriman SatuSehat. satusehat_log (530 rb+ baris) tidak punya indeks created_at -> scan penuh (~0,4 dtk), jadi di-cache lebih lama. */
function dbSatuSehat(string $a, string $b): array
{
   $ea = mysqli_real_escape_string($GLOBALS['koneksi'], $a);
   $eb = mysqli_real_escape_string($GLOBALS['koneksi'], $b);
   $ss = dbSemua("SELECT status s, COUNT(*) n FROM satusehat_log WHERE created_at >= '$ea 00:00:00' AND created_at < DATE_ADD('$eb', INTERVAL 1 DAY) GROUP BY status");
   $n = array_column($ss, 'n', 's');
   // log terakhir lewat PK (MAX(created_at) tanpa indeks = scan penuh)
   $terakhir = dbSatu("SELECT created_at t FROM satusehat_log ORDER BY id DESC LIMIT 1")['t'] ?? null;
   return ['sukses' => (int) ($n['success'] ?? 0), 'gagal' => (int) ($n['error'] ?? 0), 'terakhir' => $terakhir];
}

/* ===== susun respons ===== */
$periode = dbPeriode();
$segar = !empty($_GET['refresh']);
$t0 = microtime(true);
[$g, $umurG] = dbCache('global', 300, $segar, 'dbGlobal');
[$p, $umurP] = dbCache('periode:' . $periode['start'] . ':' . $periode['end'], 60, $segar, fn() => dbPeriodeData($periode['start'], $periode['end']));
[$ssRaw, $umurS] = dbCache('satusehat:' . $periode['start'] . ':' . $periode['end'], 300, $segar, fn() => dbSatuSehat($periode['start'], $periode['end']));

$ping = (bool) @mysqli_query($koneksi, 'SELECT 1');
$kun = $p['kunjungan'];
$ss = $ssRaw;
$ssTotal = $ss['sukses'] + $ss['gagal'];
$ssPersen = dbPersen($ss['sukses'], $ssTotal);
$rmeTotal = $kun['total'];
$rme = ['total' => $rmeTotal, 'lengkap' => $kun['lengkap'], 'tidak_lengkap' => max(0, $rmeTotal - $kun['lengkap']), 'persentase' => dbPersen($kun['lengkap'], $rmeTotal)];
$imp = $g['import']['status'];
$impGagal = (int) ($imp['failed'] ?? 0);
$impAntre = (int) (($imp['pending'] ?? 0) + ($imp['processing'] ?? 0));

$ssStatus = !$ssTotal ? ['unknown', 'TIDAK ADA AKTIVITAS', 'adm-warning'] : ($ssPersen >= 90 ? ['online', 'NORMAL', 'adm-online'] : ($ssPersen >= 70 ? ['warning', 'PERLU DICEK', 'adm-warning'] : ['error', 'BANYAK GAGAL', 'adm-error']));

$alerts = [];
if ($g['faskes']['pending'] > 0) $alerts[] = ['type' => 'danger', 'icon' => 'solar:danger-triangle-bold', 'title' => $g['faskes']['pending'] . ' faskes belum aktif', 'text' => 'Faskes berstatus pending menunggu aktivasi / verifikasi.', 'href' => 'module/administrator/master-faskes', 'aksi' => 'Kelola faskes'];
if ($g['ekyc']['menunggu'] > 0) $alerts[] = ['type' => 'warning', 'icon' => 'solar:shield-user-bold', 'title' => $g['ekyc']['menunggu'] . ' eKYC menunggu verifikasi', 'text' => 'KTP sudah diunggah dan belum diverifikasi.', 'href' => 'module/administrator/ekyc_verify', 'aksi' => 'Verifikasi'];
if ($impGagal > 0) $alerts[] = ['type' => 'danger', 'icon' => 'solar:cloud-cross-bold', 'title' => $impGagal . ' proses import gagal', 'text' => 'Periksa file dan ulangi proses import.', 'href' => 'module/administrator/data-import', 'aksi' => 'Lihat import'];
if ($impAntre > 0) $alerts[] = ['type' => 'info', 'icon' => 'solar:clock-circle-bold', 'title' => $impAntre . ' proses import belum selesai', 'text' => 'Status pending / sedang diproses.', 'href' => 'module/administrator/data-import', 'aksi' => 'Lihat import'];
if ($ssTotal >= 20 && $ssPersen < 90) $alerts[] = ['type' => $ssPersen < 70 ? 'danger' : 'warning', 'icon' => 'solar:link-broken-bold', 'title' => 'Pengiriman SatuSehat ' . $ssPersen . '% berhasil', 'text' => number_format($ss['gagal'], 0, ',', '.') . ' dari ' . number_format($ssTotal, 0, ',', '.') . ' pengiriman gagal pada periode ini.', 'href' => 'module/administrator/master-satu-sehat', 'aksi' => 'Cek SatuSehat'];
if ($rme['total'] >= 20 && $rme['persentase'] < 70) $alerts[] = ['type' => 'info', 'icon' => 'solar:document-text-bold', 'title' => 'Kelengkapan RME ' . $rme['persentase'] . '%', 'text' => number_format($rme['tidak_lengkap'], 0, ',', '.') . ' kunjungan belum lengkap (anamnesa / diagnosa / tindakan).', 'href' => 'module/administrator/monitoring-visit', 'aksi' => 'Monitoring RME'];
if (!$alerts) $alerts[] = ['type' => 'ok', 'icon' => 'solar:check-circle-bold', 'title' => 'Tidak ada yang perlu tindak lanjut', 'text' => 'Semua indikator utama dalam kondisi baik.', 'href' => null, 'aksi' => null];

dbJawab([
   'server_time' => date('Y-m-d H:i:s'),
   'scope' => 'global',
   'period' => $periode,
   'cache' => ['global_detik' => $umurG, 'periode_detik' => $umurP, 'satusehat_detik' => $umurS, 'waktu_ms' => (int) round((microtime(true) - $t0) * 1000)],
   'kpi' => [
      'faskes' => $g['faskes'],
      'pasien' => ['total' => $g['pasien']['total']],
      'dokter' => ['total' => $g['dokter']['total'], 'terverifikasi' => $g['dokter']['idsh'], 'persentase' => dbPersen($g['dokter']['idsh'], $g['dokter']['total'])],
      'kunjungan' => ['total' => $kun['total'], 'faskes_aktif' => $kun['faskes_aktif']],
      'satusehat' => ['sukses' => $ss['sukses'], 'gagal' => $ss['gagal'], 'persentase' => $ssPersen, 'total' => $ssTotal],
   ],
   'idsh' => [
      'dokter' => ['total' => $g['dokter']['total'], 'terverifikasi' => $g['dokter']['idsh'], 'persentase' => dbPersen($g['dokter']['idsh'], $g['dokter']['total'])],
      'pasien' => ['total' => $g['pasien']['total'], 'terverifikasi' => $g['pasien']['idsh'], 'persentase' => dbPersen($g['pasien']['idsh'], $g['pasien']['total'])],
   ],
   'kunjungan' => $kun,
   'faskes' => $g['faskes_terbaru'],
   'import' => ['status' => $imp, 'jobs' => $g['import']['jobs']],
   'ekyc' => $g['ekyc'],
   'rme' => $rme,
   'integration' => [
      ['name' => 'SatuSehat', 'description' => $ssTotal ? number_format($ssTotal, 0, ',', '.') . ' pengiriman, ' . $ssPersen . '% berhasil' : 'Tidak ada pengiriman pada periode ini', 'icon' => 'solar:link-bold', 'status' => $ssStatus[0], 'status_label' => $ssStatus[1], 'status_class' => $ssStatus[2], 'info' => $ss['terakhir'] ? 'Log terakhir ' . $ss['terakhir'] : null, 'href' => 'module/administrator/master-satu-sehat'],
      ['name' => 'Database', 'description' => 'Koneksi database aplikasi', 'icon' => 'solar:database-bold', 'status' => $ping ? 'online' : 'error', 'status_label' => $ping ? 'NORMAL' : 'ERROR', 'status_class' => $ping ? 'adm-online' : 'adm-error', 'info' => null, 'href' => null],
      ['name' => 'Import Data', 'description' => ($imp ? array_sum($imp) . ' job: ' . implode(', ', array_map(fn($k, $v) => "$v $k", array_keys($imp), $imp)) : 'Belum ada job import'), 'icon' => 'solar:cloud-upload-bold', 'status' => $impGagal ? 'error' : ($impAntre ? 'warning' : 'online'), 'status_label' => $impGagal ? 'ADA GAGAL' : ($impAntre ? 'ANTRE' : 'NORMAL'), 'status_class' => $impGagal ? 'adm-error' : ($impAntre ? 'adm-warning' : 'adm-online'), 'info' => null, 'href' => 'module/administrator/data-import'],
      ['name' => 'eKYC', 'description' => $g['ekyc']['total'] . ' pengajuan, ' . $g['ekyc']['menunggu'] . ' menunggu verifikasi', 'icon' => 'solar:shield-user-bold', 'status' => $g['ekyc']['menunggu'] ? 'warning' : 'online', 'status_label' => $g['ekyc']['menunggu'] ? 'MENUNGGU' : 'NORMAL', 'status_class' => $g['ekyc']['menunggu'] ? 'adm-warning' : 'adm-online', 'info' => null, 'href' => 'module/administrator/ekyc_verify'],
   ],
   'alerts' => $alerts,
]);
