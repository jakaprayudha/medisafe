<?php
// Monitoring Antrean Online BPJS (FKTP) - module/admin/monitoring-antrean
// Read-only. Tidak ada log respons BPJS, jadi status disimpulkan dari data sistem:
//   - Add antrean : JKNOnsite hanya tersimpan jika /antrean/add sukses (prosesPendaftaranTunggal),
//                   MobileJKN berasal dari BPJS.
//   - Panggil     : antrian_poli.status (0 belum, 1 hadir/dipanggil, 99 batal) + pasien_visit.visit_status
//   - Waktu       : hadir = visit_date + visit_time, selesai periksa = pcare_kunjungan.created_at,
//                   resep = permintaan_pharmacy.created_at pertama.

ob_start();
require_once __DIR__ . '/../../database/connect.php';
ob_end_clean();
mysqli_report(MYSQLI_REPORT_OFF);
date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out($data, $code = 200)
{
   http_response_code($code);
   echo json_encode($data, JSON_UNESCAPED_UNICODE);
   exit;
}

function rows($koneksi, $sql, $types = '', $params = [])
{
   $st = mysqli_prepare($koneksi, $sql);
   if (!$st) {
      error_log('[antreanMonitoring] ' . mysqli_error($koneksi));
      out(['status' => 'error', 'message' => 'Gagal memuat data.'], 500);
   }
   if ($params) mysqli_stmt_bind_param($st, $types, ...$params);
   if (!mysqli_stmt_execute($st)) {
      error_log('[antreanMonitoring] ' . mysqli_stmt_error($st));
      out(['status' => 'error', 'message' => 'Gagal memuat data.'], 500);
   }
   $res = mysqli_stmt_get_result($st);
   return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}

$roles = $_SESSION['roles'] ?? '';
if (empty($_SESSION['id_customer']) || !in_array($roles, ['superadmin', 'admin'], true)) {
   out(['status' => 'error', 'message' => 'Akses tidak diizinkan.'], 403);
}
$isSuper = $roles === 'superadmin';
// Admin selalu faskes sendiri; superadmin boleh memilih faskes.
$idc = (string) ($isSuper && !empty($_GET['id_customer']) ? (int) $_GET['id_customer'] : (int) $_SESSION['id_customer']);

$action = $_GET['action'] ?? 'data';

if ($action === 'faskes') {
   if (!$isSuper) out(['status' => 'error', 'message' => 'Akses tidak diizinkan.'], 403);
   $data = rows(
      $koneksi,
      "SELECT c.id_customer, c.clinic_name,
              EXISTS(SELECT 1 FROM setting_antrol s WHERE s.id_customer = c.id_customer AND TRIM(COALESCE(s.base_url, '')) <> '') antrol_aktif
       FROM setting_clinic c ORDER BY antrol_aktif DESC, c.clinic_name"
   );
   out(['status' => 'success', 'data' => $data]);
}

if ($action !== 'data') {
   out(['status' => 'error', 'message' => 'Action tidak dikenal.'], 400);
}

// ================== PARAMETER ==================
$today = date('Y-m-d');
$from = $_GET['from'] ?? $today;
$to = $_GET['to'] ?? $from;
$validDate = fn($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
if (!$validDate($from) || !$validDate($to)) {
   out(['status' => 'error', 'message' => 'Format tanggal tidak valid.'], 422);
}
if ($from > $to) {
   [$from, $to] = [$to, $from];
}
if ((strtotime($to) - strtotime($from)) / 86400 > 30) {
   out(['status' => 'error', 'message' => 'Rentang tanggal maksimal 31 hari.'], 422);
}

// ================== KONFIGURASI ANTROL ==================
$cfg = rows($koneksi, "SELECT kodePPK, base_url, service FROM setting_antrol WHERE id_customer = ? LIMIT 1", 's', [$idc]);
$antrolAktif = $cfg && trim((string) $cfg[0]['base_url']) !== '';
$config = [
   'antrol_aktif' => $antrolAktif,
   'kode_ppk' => $cfg[0]['kodePPK'] ?? null,
   'service' => $cfg[0]['service'] ?? null,
];

// ================== DATA KUNJUNGAN BPJS RAWAT JALAN ==================
$visits = rows(
   $koneksi,
   "SELECT pv.visit_ID, pv.visit_date, pv.visit_time, pv.created_at, pv.created_user, pv.visit_status,
           pv.visit_antrian, pv.id_poli, pv.id_doctor, pv.code_doctor, pv.patient_name_pcare, pv.noKartu,
           pk.created_at AS periksa_at
    FROM pasien_visit pv
    LEFT JOIN pcare_kunjungan pk ON pk.noKunjungan = pv.noKunjung AND pv.noKunjung <> ''
    WHERE pv.visit_date BETWEEN ? AND ?
      AND pv.id_customer = ?
      AND pv.id_provider = 1
      AND (pv.source_hub IS NULL OR pv.source_hub <> 'Rawat Inap')
    ORDER BY pv.visit_date DESC, pv.visit_time DESC",
   'sss',
   [$from, $to, $idc]
);

// antrean & resep diambil sekali per rentang (tabel tanpa index nomor_visit/id_visit)
$antrean = [];
foreach (rows(
   $koneksi,
   "SELECT nomor_visit, status, created_at FROM antrian_poli WHERE id_customer = ? AND tanggal BETWEEN ? AND ?",
   'sss',
   [$idc, $from, $to]
) as $a) {
   $antrean[$a['nomor_visit']] = $a;
}
$resep = [];
foreach (rows(
   $koneksi,
   "SELECT id_visit, MIN(created_at) resep_at FROM permintaan_pharmacy
    WHERE id_customer = ? AND created_at >= ? AND created_at < ? + INTERVAL 2 DAY
    GROUP BY id_visit",
   'sss',
   [$idc, $from, $to]
) as $r) {
   $resep[$r['id_visit']] = $r['resep_at'];
}

// ================== KLASIFIKASI ==================
function sumber($createdUser)
{
   return match ($createdUser) {
      'MobileJKN' => 'mobile',
      'JKNOnsite' => 'onsite',
      'JKNSehat' => 'sehat',
      default => 'lain',
   };
}

// selisih menit, null kalau tidak wajar (negatif / > 12 jam)
function menit($dari, $sampai)
{
   if (!$dari || !$sampai) return null;
   $a = strtotime($dari);
   $b = strtotime($sampai);
   if ($a === false || $b === false) return null;
   $m = ($b - $a) / 60;
   return ($m < 0 || $m > 720) ? null : round($m, 1);
}

$detail = [];
foreach ($visits as $v) {
   $src = sumber($v['created_user']);
   $online = $src === 'mobile' || ($src === 'onsite' && $antrolAktif);
   $ap = $antrean[$v['visit_ID']] ?? null;
   $vs = (int) $v['visit_status'];
   $lewat = $v['visit_date'] < $today;

   // status add antrean ke BPJS
   if ($src === 'mobile') {
      $add = 'mobile';
   } elseif ($src === 'onsite') {
      $add = $antrolAktif ? 'terkirim' : 'nonaktif';
   } elseif ($src === 'sehat') {
      $add = 'tidak_wajib';
   } else {
      $add = 'tidak_dikirim';
   }

   // status panggil (hadir / tidak hadir)
   $panggil = null;
   if ($src === 'mobile' || $src === 'onsite') {
      if ($vs === 99 || ($ap && (int) $ap['status'] === 99)) {
         $panggil = 'batal';
      } elseif ($vs === 10) {
         $panggil = $lewat ? 'tidak_checkin' : 'belum_checkin';
      } elseif ($ap && (int) $ap['status'] === 1) {
         $panggil = 'hadir';
      } elseif (!$ap) {
         $panggil = 'tanpa_antrean';
      } else {
         $panggil = $lewat ? 'belum_lapor' : 'menunggu';
      }
   }

   $hadirAt = $v['visit_time'] ? $v['visit_date'] . ' ' . $v['visit_time'] : null;
   $ambilAt = $ap['created_at'] ?? $v['created_at'];
   $resepAt = $resep[$v['visit_ID']] ?? null;
   $dilayani = !in_array($vs, [99, 10], true);

   $detail[] = [
      'visit_ID' => $v['visit_ID'],
      'tanggal' => $v['visit_date'],
      'antrean' => $v['visit_antrian'],
      'pasien' => $v['patient_name_pcare'],
      'noKartu' => $v['noKartu'],
      'poli' => $v['id_poli'],
      'dokter' => $v['id_doctor'] ?: $v['code_doctor'],
      'sumber' => $src,
      'online' => $online,
      'dilayani' => $dilayani,
      'status_add' => $add,
      'status_panggil' => $panggil,
      'ambil_at' => $ambilAt,
      'hadir_at' => $hadirAt,
      'periksa_at' => $v['periksa_at'],
      'resep_at' => $resepAt,
      // ambil antrean -> hadir hanya dihitung di hari yang sama (booking Mobile JKN bisa H-1)
      'm_ambil_hadir' => $ambilAt && substr($ambilAt, 0, 10) === $v['visit_date'] ? menit($ambilAt, $hadirAt) : null,
      'm_hadir_periksa' => menit($hadirAt, $v['periksa_at']),
      'm_periksa_resep' => menit($v['periksa_at'], $resepAt),
   ];
}

// ================== AGREGASI ==================
function agregat(array $rows)
{
   $a = [
      'total' => count($rows),
      'sakit' => 0, 'sehat' => 0,
      'dilayani' => 0, 'online_dilayani' => 0, 'mobile_dilayani' => 0,
      'antrean_online' => 0, 'mobile' => 0, 'onsite' => 0, 'tanpa_antrol' => 0,
      'hadir' => 0, 'batal' => 0, 'tidak_checkin' => 0, 'belum_lapor' => 0, 'menunggu' => 0, 'tanpa_antrean' => 0,
   ];
   $waktu = ['m_ambil_hadir' => [], 'm_hadir_periksa' => [], 'm_periksa_resep' => []];
   foreach ($rows as $r) {
      if ($r['sumber'] === 'sehat') {
         $a['sehat']++;
         continue;
      }
      $a['sakit']++;
      if ($r['sumber'] === 'mobile') $a['mobile']++;
      if ($r['sumber'] === 'onsite') $a['onsite']++;
      if (!$r['online']) $a['tanpa_antrol']++;
      if ($r['online']) $a['antrean_online']++;
      if ($r['dilayani']) {
         $a['dilayani']++;
         if ($r['online']) $a['online_dilayani']++;
         if ($r['sumber'] === 'mobile') $a['mobile_dilayani']++;
      }
      switch ($r['status_panggil']) {
         case 'hadir': $a['hadir']++; break;
         case 'batal': $a['batal']++; break;
         case 'tidak_checkin': $a['tidak_checkin']++; break;
         case 'belum_lapor': $a['belum_lapor']++; break;
         case 'menunggu':
         case 'belum_checkin': $a['menunggu']++; break;
         case 'tanpa_antrean': $a['tanpa_antrean']++; break;
      }
      foreach ($waktu as $k => $_) {
         if ($r[$k] !== null) $waktu[$k][] = $r[$k];
      }
   }
   $pct = fn($x, $y) => $y > 0 ? round($x / $y * 100, 1) : null;
   $a['pct_online'] = $pct($a['online_dilayani'], $a['dilayani']);
   $a['pct_mobile'] = $pct($a['mobile_dilayani'], $a['dilayani']);
   // status sudah final = hadir / batal; yang belum final = belum dilaporkan / booking tidak check-in
   $final = $a['hadir'] + $a['batal'];
   $a['pct_terlapor'] = $pct($final, $final + $a['belum_lapor'] + $a['tidak_checkin']);
   foreach ($waktu as $k => $list) {
      sort($list);
      $n = count($list);
      $a[$k . '_avg'] = $n ? round(array_sum($list) / $n, 1) : null;
      $a[$k . '_med'] = $n ? ($n % 2 ? $list[intdiv($n, 2)] : round(($list[$n / 2 - 1] + $list[$n / 2]) / 2, 1)) : null;
      $a[$k . '_n'] = $n;
   }
   return $a;
}

function kelompok(array $rows, string $kunci)
{
   $grup = [];
   foreach ($rows as $r) {
      $grup[(string) ($r[$kunci] ?? '-') ?: '-'][] = $r;
   }
   $hasil = [];
   foreach ($grup as $nama => $isi) {
      $hasil[] = ['nama' => $nama] + agregat($isi);
   }
   return $hasil;
}

out([
   'status' => 'success',
   'periode' => ['from' => $from, 'to' => $to],
   'config' => $config,
   'kpi' => agregat($detail),
   'harian' => kelompok($detail, 'tanggal'),
   'poli' => kelompok($detail, 'poli'),
   'dokter' => kelompok($detail, 'dokter'),
   'detail' => $detail,
]);
