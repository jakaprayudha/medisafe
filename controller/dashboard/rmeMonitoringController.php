<?php
/**
 * Monitoring kelengkapan RME (module/admin/monitoring-visit).
 *   GET jenis=poli|ranap&from=&to=[&id_customer=]
 *     -> { checklist: [definisi item], kolom, data: [baris], ringkasan }
 *
 * Checklist dibedakan: kunjungan poli (rawat jalan) vs rawat inap. Item disusun dari isian yang memang ada di form
 * aplikasi (mis. pemeriksaan fisik & edukasi tidak ada di form poli, jadi bukan syarat poli).
 * Faskes dari SESSION (superadmin boleh memilih). Data terbatas periode (maks 92 hari) dan tabel pendukung
 * diambil sekali per tabel untuk kunjungan di periode (bukan subquery per baris).
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$roles = $_SESSION['roles'] ?? '';
requireRole(['admin', 'superadmin', 'administrator']);
$idc = in_array($roles, ['superadmin', 'administrator'], true) && !empty($_GET['id_customer'])
   ? (int) $_GET['id_customer'] : (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();

function jawab(array $d, int $c = 200): void
{
   http_response_code($c);
   echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}
function q(mysqli $k, string $sql, string $t = '', array $a = []): array
{
   $st = $k->prepare($sql);
   if (!$st) {
      error_log('[rmeMonitoring] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal memuat data RME.'], 500);
   }
   if ($t !== '') $st->bind_param($t, ...$a);
   $st->execute();
   $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   return $r;
}
/** Ambil baris tabel pendukung untuk sekumpulan visit_ID (per 1000). */
function perVisit(mysqli $k, string $sql, array $ids): array
{
   $out = [];
   foreach (array_chunk($ids, 1000) as $b) {
      $ph = implode(',', array_fill(0, count($b), '?'));
      foreach (q($k, str_replace('{IDS}', $ph, $sql), str_repeat('s', count($b)), $b) as $r) $out[] = $r;
   }
   return $out;
}

if ($idc <= 0) jawab(['status' => 'error', 'message' => 'Faskes tidak ditemukan.'], 422);
$jenis = ($_GET['jenis'] ?? 'poli') === 'ranap' ? 'ranap' : 'poli';
$tgl = function ($v, $d) {
   $v = (string) $v;
   $x = DateTime::createFromFormat('!Y-m-d', $v);
   return $x && $x->format('Y-m-d') === $v ? $v : $d;
};
$from = $tgl($_GET['from'] ?? '', date('Y-m-d', strtotime($jenis === 'ranap' ? '-29 days' : '-6 days')));
$to = $tgl($_GET['to'] ?? '', date('Y-m-d'));
if ($to < $from) [$from, $to] = [$to, $from];
if ((new DateTime($from))->diff(new DateTime($to))->days > 92) jawab(['status' => 'error', 'message' => 'Rentang maksimal 93 hari.'], 422);

const INTEGRASI = ['pcare', 'satusehat'];
const CL_POLI = [
   ['ttv', 'Tanda vital', 'TD, nadi, suhu'],
   ['bbtb', 'BB / TB', 'Berat & tinggi badan'],
   ['anamnesa', 'Anamnesa', 'Keluhan / anamnesa'],
   ['diagnosa', 'Diagnosis ICD-10', 'Diagnosis utama'],
   ['terapi', 'Terapi', 'Tindakan atau resep obat'],
   ['selesai', 'Pemeriksaan selesai', 'Status kunjungan selesai'],
   ['pcare', 'Kunjungan PCare', 'Khusus pasien BPJS'],
   ['satusehat', 'Satu Sehat', 'Encounter terkirim'],
];
const CL_RANAP = [
   ['triase', 'Triase', 'Form triase IGD/ranap'],
   ['pengkajian', 'Pengkajian awal', 'TTV, anamnesa & pemeriksaan fisik'],
   ['alergi', 'Riwayat alergi', 'Diisi (termasuk "tidak ada")'],
   ['diagnosa', 'Diagnosis ICD-10', 'Diagnosis utama'],
   ['persetujuan', 'Persetujuan rawat', 'Tanda tangan opname / dokumen'],
   ['cppt', 'CPPT harian', 'Minimal 1 CPPT per hari rawat'],
   ['cpo', 'Catatan pemberian obat', 'CPO terisi'],
   ['edukasi', 'Edukasi', 'Edukasi pasien/keluarga'],
   ['kondisi_pulang', 'Kondisi pulang', 'Wajib bila sudah pulang'],
   ['resume', 'Resume medis', 'Wajib bila sudah pulang'],
   ['satusehat', 'Satu Sehat', 'Encounter terkirim'],
];

// isian teks panjang (anamnesa, tindakan, dll.) tidak ditarik: cukup tanda terisi/kosong dari database
$f = fn($c) => "(TRIM(COALESCE(pv.$c, '')) NOT IN ('', '-'))";
$visit = q($koneksi, "SELECT pv.visit_ID, pv.visit_date, pv.visit_time, pv.visit_status, pv.id_doctor, pv.id_poli, pv.id_provider,
                              {$f('tekanan_darah')} AND {$f('nadi')} AND {$f('suhu')} f_ttv,
                              {$f('berat_badan')} AND {$f('tinggi_badan')} f_bbtb,
                              {$f('anamnesa')} OR {$f('keluhan_penyerta')} f_anam,
                              {$f('pemeriksaan_fisik')} f_fisik, {$f('diagnosa')} f_dx, {$f('tindakan')} f_tind, {$f('edukasi')} f_edu,
                              {$f('riwayat_alergi')} f_alergi, {$f('kondisi_pulang')} f_kp, {$f('signature_opname')} f_ttd,
                              {$f('noKunjung')} f_pcare, {$f('id_encounter')} f_enc,
                              pv.patient_name_pcare, ms.patient_name, ms.nomor_rm
                       FROM pasien_visit pv LEFT JOIN ms_patient ms ON ms.id_patient = pv.id_patient
                       WHERE pv.visit_date BETWEEN ? AND ? AND pv.id_customer = ?
                         AND (pv.visit_status IS NULL OR pv.visit_status NOT IN (99, 10))
                         AND COALESCE(pv.status_rawatinap, 0) = ?
                       ORDER BY pv.visit_date DESC, pv.visit_time DESC", 'ssii', [$from, $to, $idc, $jenis === 'ranap' ? 1 : 0]);
$ids = array_column($visit, 'visit_ID');
$cl = $jenis === 'ranap' ? CL_RANAP : CL_POLI;
$kode = array_column($cl, 0);

// ---------- data pendukung ----------
$resep = [];
if ($ids) {
   // resep: disaring lewat faskes & tanggal (tabel tanpa index id_visit)
   foreach (q($koneksi, "SELECT DISTINCT id_visit FROM permintaan_pharmacy WHERE id_customer = ? AND created_at >= ? AND created_at < ? + INTERVAL " . ($jenis === 'ranap' ? 60 : 2) . " DAY",
      'sss', [(string) $idc, $from, $to]) as $r) $resep[$r['id_visit']] = true;
}
$triase = $cppt = $cpo = $resume = $dok = $ranap = [];
if ($jenis === 'ranap' && $ids) {
   foreach (perVisit($koneksi, "SELECT DISTINCT visit_ID v FROM pasien_triase WHERE visit_ID IN ({IDS})", $ids) as $r) $triase[$r['v']] = true;
   foreach (perVisit($koneksi, "SELECT visit_ID v, COUNT(*) n, COUNT(DISTINCT DATE(COALESCE(cppt_date, created_at))) hari FROM visit_cppt WHERE visit_ID IN ({IDS}) GROUP BY visit_ID", $ids) as $r) $cppt[$r['v']] = $r;
   foreach (perVisit($koneksi, "SELECT visit_ID v, COUNT(*) n FROM pasien_cpo WHERE visit_ID IN ({IDS}) GROUP BY visit_ID", $ids) as $r) $cpo[$r['v']] = (int) $r['n'];
   foreach (perVisit($koneksi, "SELECT visit_ID v, tanggal_pulang FROM resume_medis WHERE visit_ID IN ({IDS})", $ids) as $r) $resume[$r['v']] = $r['tanggal_pulang'];
   foreach (perVisit($koneksi, "SELECT visit_ID v, COUNT(*) n FROM pasien_dokumen WHERE visit_ID IN ({IDS}) GROUP BY visit_ID", $ids) as $r) $dok[$r['v']] = (int) $r['n'];
   foreach (perVisit($koneksi, "SELECT visit_ID_inpatient v, status FROM permintaan_ranap WHERE visit_ID_inpatient IN ({IDS})", $ids) as $r) $ranap[$r['v']] = $r['status'];
}

// ---------- checklist per kunjungan ----------
// nilai item: 1 lengkap, 0 belum, null tidak berlaku
$hariIni = new DateTime(date('Y-m-d'));
$rows = [];
foreach ($visit as $v) {
   $id = $v['visit_ID'];
   $c = [];
   $ttv = (bool) $v['f_ttv'];
   $anam = (bool) $v['f_anam'];
   if ($jenis === 'poli') {
      $c['ttv'] = $ttv;
      $c['bbtb'] = (bool) $v['f_bbtb'];
      $c['anamnesa'] = $anam;
      $c['diagnosa'] = (bool) $v['f_dx'];
      $c['terapi'] = $v['f_tind'] || isset($resep[$id]);
      $c['selesai'] = (int) $v['visit_status'] === 4;
      $c['pcare'] = (int) $v['id_provider'] === 1 ? (bool) $v['f_pcare'] : null;
      $c['satusehat'] = (bool) $v['f_enc'];
      $info = '';
   } else {
      $tp = $resume[$id] ?? null;
      $pulang = ($tp !== null && trim((string) $tp) !== '') || ($ranap[$id] ?? '') === 'pulang';
      $akhir = $pulang && $tp && strtotime($tp) ? new DateTime(substr($tp, 0, 10)) : $hariIni;
      $lama = max(1, (int) (new DateTime($v['visit_date']))->diff($akhir)->format('%r%a'));
      if ($lama > 90) $lama = 1;
      $hariCppt = (int) ($cppt[$id]['hari'] ?? 0);
      $c['triase'] = isset($triase[$id]);
      $c['pengkajian'] = $ttv && $anam && $v['f_fisik'];
      $c['alergi'] = (bool) $v['f_alergi'];
      $c['diagnosa'] = (bool) $v['f_dx'];
      $c['persetujuan'] = $v['f_ttd'] || !empty($dok[$id]);
      $c['cppt'] = $hariCppt >= $lama;
      $c['cpo'] = !empty($cpo[$id]) || !isset($resep[$id]) ? (!empty($cpo[$id]) ? true : null) : false;
      $c['edukasi'] = (bool) $v['f_edu'];
      $c['kondisi_pulang'] = $pulang ? (bool) $v['f_kp'] : null;
      $c['resume'] = $pulang ? ($tp !== null && trim((string) $tp) !== '') : null;
      $c['satusehat'] = (bool) $v['f_enc'];
      $info = ($pulang ? 'Pulang' : 'Dirawat') . ' · ' . $lama . ' hr · CPPT ' . ((int) ($cppt[$id]['n'] ?? 0)) . 'x/' . $hariCppt . ' hr';
   }
   // item integrasi (PCare, Satu Sehat) ditampilkan tapi tidak dihitung dalam kelengkapan dokumentasi RME
   $berlaku = array_filter($c, fn($x, $k) => $x !== null && !in_array($k, INTEGRASI, true), ARRAY_FILTER_USE_BOTH);
   $ok = count(array_filter($berlaku));
   $rows[] = [
      'visit' => $id,
      'tanggal' => $v['visit_date'],
      'jam' => substr((string) $v['visit_time'], 0, 5),
      'pasien' => (string) ($v['patient_name'] ?: $v['patient_name_pcare']),
      'rm' => (string) $v['nomor_rm'],
      'dokter' => trim((string) $v['id_doctor']) ?: '-',
      'poli' => trim((string) $v['id_poli']) ?: '-',
      'cek' => array_map(fn($k) => $c[$k] === null ? null : ($c[$k] ? 1 : 0), $kode),
      'pct' => $berlaku ? (int) round($ok / count($berlaku) * 100) : 100,
      'info' => $info,
   ];
}

// ---------- ringkasan ----------
$item = [];
foreach ($kode as $i => $k) {
   $n = $okN = 0;
   foreach ($rows as $r) {
      if ($r['cek'][$i] === null) continue;
      $n++;
      $okN += $r['cek'][$i];
   }
   $item[] = ['kode' => $k, 'berlaku' => $n, 'lengkap' => $okN, 'pct' => $n ? round($okN / $n * 100, 1) : null];
}
$dokter = [];
foreach ($rows as $r) {
   $d = &$dokter[$r['dokter']];
   $d ??= ['nama' => $r['dokter'], 'kunjungan' => 0, 'lengkap' => 0, 'pct_sum' => 0];
   $d['kunjungan']++;
   $d['lengkap'] += $r['pct'] === 100 ? 1 : 0;
   $d['pct_sum'] += $r['pct'];
   unset($d);
}
$dokter = array_values(array_map(fn($d) => ['nama' => $d['nama'], 'kunjungan' => $d['kunjungan'], 'lengkap' => $d['lengkap'], 'rata' => round($d['pct_sum'] / $d['kunjungan'], 1)], $dokter));
usort($dokter, fn($x, $y) => $y['kunjungan'] <=> $x['kunjungan']);
$total = count($rows);
$lengkap = count(array_filter($rows, fn($r) => $r['pct'] === 100));

if (!ini_get('zlib.output_compression') && extension_loaded('zlib')) ob_start('ob_gzhandler');
jawab([
   'status' => 'success',
   'jenis' => $jenis,
   'periode' => ['from' => $from, 'to' => $to],
   'checklist' => array_map(fn($x) => ['kode' => $x[0], 'label' => $x[1], 'ket' => $x[2], 'integrasi' => in_array($x[0], INTEGRASI, true)], $cl),
   'kolom' => ['visit', 'tanggal', 'jam', 'pasien', 'rm', 'dokter', 'poli', 'cek', 'pct', 'info'],
   'data' => array_map('array_values', $rows),
   'ringkasan' => [
      'total' => $total,
      'lengkap' => $lengkap,
      'pct_lengkap' => $total ? round($lengkap / $total * 100, 1) : null,
      'rata' => $total ? round(array_sum(array_column($rows, 'pct')) / $total, 1) : null,
      'item' => $item,
      'dokter' => $dokter,
   ],
]);
