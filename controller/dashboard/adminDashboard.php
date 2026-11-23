<?php
/**
 * Data dashboard admin (module/dashboard/dashboard-admin.php).
 *
 * GET ?bagian=statistik&dari=Y-m-d&sampai=Y-m-d[&poli=..][&penjamin=id_provider]
 *     -> KPI (+ pembanding periode sebelumnya), tren harian, jam & hari sibuk, penjamin, poli, dokter,
 *        diagnosa teratas, demografi, metode bayar.  Maks 366 hari.
 * GET ?bagian=operasional
 *     -> kondisi hari ini: status kunjungan per poli, bed rawat inap, stok obat kritis.
 *
 * Menggantikan controller/dashboard/dashboardController.php?action=dashboard yang:
 *  - menghitung stok dengan 2 subquery per obat ke permintaan_pharmacy_details (±630 rb baris, tanpa index
 *    id_pharmacy) -> 15 dtk di faskes 1, >30 dtk (timeout) di faskes 10. Kini pakai farmasiStokReport
 *    (1 query agregat, ±1 dtk) dan dimuat terpisah agar statistik tampil duluan.
 *  - periode custom tidak pernah terpakai (JS kirim start_date/end_date, PHP baca start/end).
 *  - kunjungan batal (visit_status 99) ikut dihitung; "jenis pembayaran" mayoritas "Tidak Diketahui"
 *    karena menghitung metode_bayar semua kunjungan termasuk yang belum/tidak bayar.
 */
require '../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');
if (!ini_get('zlib.output_compression')) @ob_start('ob_gzhandler');

function balas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') balas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$idc = (string) ($_SESSION['id_customer'] ?? '');
session_write_close();
if ($idc === '') balas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

$tz = new DateTimeZone('Asia/Jakarta');
$hariIni = (new DateTime('now', $tz))->format('Y-m-d');
$bagian = $_GET['bagian'] ?? 'statistik';

try {
   if ($bagian === 'operasional') balas(200, operasional($koneksi, $idc, $hariIni));
   if ($bagian !== 'statistik') balas(422, ['status' => 'error', 'message' => 'Bagian tidak dikenal.']);

   $tgl = function ($v) {
      $v = (string) $v;
      $d = DateTime::createFromFormat('!Y-m-d', $v);
      return ($d && $d->format('Y-m-d') === $v) ? $v : null;
   };
   $dari = $tgl($_GET['dari'] ?? '') ?? substr($hariIni, 0, 8) . '01';
   $sampai = $tgl($_GET['sampai'] ?? '') ?? $hariIni;
   if ($sampai < $dari) [$dari, $sampai] = [$sampai, $dari];
   $jumlahHari = (new DateTime($dari))->diff(new DateTime($sampai))->days + 1;
   if ($jumlahHari > 366) balas(422, ['status' => 'error', 'message' => 'Rentang maksimal 366 hari.']);
   $poli = trim((string) ($_GET['poli'] ?? ''));
   $penjamin = trim((string) ($_GET['penjamin'] ?? ''));
   balas(200, statistik($koneksi, $idc, $dari, $sampai, $jumlahHari, $poli, $penjamin));
} catch (Throwable $e) {
   error_log('[dashboard/adminDashboard] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memuat dashboard.']);
}

/* ======================================================================= */

function statistik(mysqli $k, string $idc, string $dari, string $sampai, int $jumlahHari, string $poli, string $penjamin): array
{
   $akhir = (new DateTime($sampai))->modify('+1 day')->format('Y-m-d');
   // periode pembanding: sama panjang, tepat sebelum periode ini
   $prevSampai = (new DateTime($dari))->modify('-1 day')->format('Y-m-d');
   $prevDari = (new DateTime($dari))->modify('-' . $jumlahHari . ' day')->format('Y-m-d');
   $prevAkhir = $dari;

   // Agregasi dilakukan di database (GROUP BY) — mengirim puluhan ribu baris mentah ke PHP jauh lebih lambat
   // (faskes 1, 1 tahun: 61 rb baris ≈ 8 dtk transfer). Kolom poli & penjamin ikut di-GROUP agar filter
   // dan daftar pilihan filter bisa dihitung dari hasil yang sama.
   $q = function (string $sql, string $tipe, array $nilai) use ($k): array {
      $st = $k->prepare($sql);
      $st->bind_param($tipe, ...$nilai);
      $st->execute();
      $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
      $st->close();
      return $r;
   };
   $dariVisit = "FROM pasien_visit pv FORCE INDEX (idx_date_cust) WHERE pv.id_customer = ? AND pv.visit_date >= ? AND pv.visit_date < ?";
   $p3 = [$idc, $dari, $akhir];
   $lolos = fn($r) => ($poli === '' || (string) $r['id_poli'] === $poli) && ($penjamin === '' || (string) $r['id_provider'] === $penjamin);

   $rowsDim = $q("SELECT pv.id_poli, pv.id_provider, pv.visit_status = 99 AS batal, pv.visit_status = 4 AS selesai, pv.visit_status = 10 AS checkin,
         pv.status_rawatinap = 1 AS ranap, pv.status_bayar = 1 AS lunas, pv.metode_bayar, COUNT(*) n, SUM(pv.amount_results) rp
      $dariVisit GROUP BY 1, 2, 3, 4, 5, 6, 7, 8", 'sss', $p3);
   $rowsTgl = $q("SELECT pv.visit_date, pv.id_poli, pv.id_provider, pv.visit_status = 99 AS batal, COUNT(*) n,
         SUM(CASE WHEN pv.status_bayar = 1 THEN pv.amount_results ELSE 0 END) rp
      $dariVisit GROUP BY 1, 2, 3, 4", 'sss', $p3);
   $rowsJam = $q("SELECT LEFT(pv.visit_time, 2) jam, pv.id_poli, pv.id_provider, COUNT(*) n
      $dariVisit AND pv.visit_status <> 99 GROUP BY 1, 2, 3", 'sss', $p3);
   $rowsDok = $q("SELECT pv.id_doctor, pv.id_poli, pv.id_provider, COUNT(*) n, SUM(pv.visit_status = 4) selesai
      $dariVisit AND pv.visit_status <> 99 AND pv.id_doctor <> '' GROUP BY 1, 2, 3", 'sss', $p3);
   $rowsDiag = $q("SELECT UPPER(TRIM(pv.kdDiag1)) kode, MAX(pv.nmDiag1) nama, pv.id_poli, pv.id_provider, COUNT(*) n
      $dariVisit AND pv.visit_status <> 99 AND pv.kdDiag1 <> '' GROUP BY 1, 3, 4", 'sss', $p3);

   $saring = '';
   $tipe = '';
   $nilai = [];
   if ($poli !== '') { $saring .= ' AND pv.id_poli = ?'; $tipe .= 's'; $nilai[] = $poli; }
   if ($penjamin !== '') { $saring .= ' AND pv.id_provider = ?'; $tipe .= 's'; $nilai[] = $penjamin; }
   $rowsPasien = $q("SELECT mp.patient_gender g, TIMESTAMPDIFF(YEAR, mp.patient_datebirth, ?) u, COUNT(*) n
      FROM (SELECT DISTINCT pv.id_patient $dariVisit AND pv.visit_status <> 99 AND pv.id_patient <> ''$saring) x
      LEFT JOIN ms_patient mp ON mp.id_patient = x.id_patient GROUP BY 1, 2", 'ssss' . $tipe, array_merge([$sampai], $p3, $nilai));
   // pasien baru = kunjungan pertama di faskes ini jatuh di periode
   $baru = $q("SELECT COUNT(*) n FROM (SELECT pv.id_patient FROM pasien_visit pv
         WHERE pv.id_customer = ? AND pv.id_patient <> '' AND pv.visit_status <> 99$saring
         GROUP BY pv.id_patient HAVING MIN(pv.visit_date) >= ? AND MIN(pv.visit_date) < ?) x", 's' . $tipe . 'ss', array_merge([$idc], $nilai, [$dari, $akhir]));
   // pembanding periode sebelumnya
   $pPrev = [$idc, $prevDari, $prevAkhir];
   $rowsPrev = $q("SELECT pv.visit_date, pv.visit_status = 99 AS batal, COUNT(*) n, SUM(CASE WHEN pv.status_bayar = 1 THEN pv.amount_results ELSE 0 END) rp
      $dariVisit$saring GROUP BY 1, 2", 'sss' . $tipe, array_merge($pPrev, $nilai));
   $prevPasien = $q("SELECT COUNT(DISTINCT pv.id_patient) n $dariVisit AND pv.visit_status <> 99 AND pv.id_patient <> ''$saring", 'sss' . $tipe, array_merge($pPrev, $nilai));

   // ---------- olah ----------
   $opsiPoli = $opsiPenjamin = [];
   $namaPenjamin = [];
   $st = $k->query("SELECT id_provider, provider_name FROM ms_provider");
   foreach ($st->fetch_all(MYSQLI_NUM) as [$id, $nm]) $namaPenjamin[(string) $id] = trim((string) $nm);
   $nmPenjamin = fn($id) => ($namaPenjamin[(string) $id] ?? '') ?: 'Lainnya';
   $nmPoli = fn($p) => trim((string) $p) ?: '(tanpa poli)';

   $kpi = ['kunjungan' => 0, 'batal' => 0, 'selesai' => 0, 'dilayani' => 0, 'belum_checkin' => 0, 'rawat_inap' => 0,
      'transaksi' => 0, 'pendapatan' => 0, 'pasien_unik' => 0, 'pasien_baru' => (int) $baru[0]['n']];
   $perPenjamin = $perPoli = $perMetode = [];
   foreach ($rowsDim as $r) {
      $opsiPoli[$nmPoli($r['id_poli'])] = (string) $r['id_poli'];
      if ($r['id_provider'] !== null) $opsiPenjamin[(string) $r['id_provider']] = $nmPenjamin($r['id_provider']);
      if (!$lolos($r)) continue;
      $n = (int) $r['n'];
      if ($r['batal']) { $kpi['batal'] += $n; continue; }
      $kpi['kunjungan'] += $n;
      if ($r['selesai']) $kpi['selesai'] += $n; elseif ($r['checkin']) $kpi['belum_checkin'] += $n; else $kpi['dilayani'] += $n;
      if ($r['ranap']) $kpi['rawat_inap'] += $n;
      $np = $nmPenjamin($r['id_provider']);
      $perPenjamin[$np] = $perPenjamin[$np] ?? ['nama' => $np, 'n' => 0, 'pendapatan' => 0];
      $perPenjamin[$np]['n'] += $n;
      $pl = $nmPoli($r['id_poli']);
      $perPoli[$pl] = ($perPoli[$pl] ?? 0) + $n;
      if ($r['lunas']) {
         $rp = (int) $r['rp'];
         $kpi['transaksi'] += $n;
         $kpi['pendapatan'] += $rp;
         $perPenjamin[$np]['pendapatan'] += $rp;
         $m = trim((string) $r['metode_bayar']);
         $m = $m === 'AUTO' ? 'Tanpa tagihan' : ($m ?: 'Lainnya');
         $perMetode[$m] = $perMetode[$m] ?? ['nama' => $m, 'n' => 0, 'total' => 0];
         $perMetode[$m]['n'] += $n;
         $perMetode[$m]['total'] += $rp;
      }
   }

   $tren = [];
   for ($d = new DateTime($dari); $d->format('Y-m-d') <= $sampai; $d->modify('+1 day')) {
      $tren[$d->format('Y-m-d')] = ['tgl' => $d->format('Y-m-d'), 'kunjungan' => 0, 'batal' => 0, 'pendapatan' => 0];
   }
   $hariMinggu = array_fill(1, 7, 0);   // 1 = Senin
   foreach ($rowsTgl as $r) {
      if (!$lolos($r) || !isset($tren[$r['visit_date']])) continue;
      if ($r['batal']) { $tren[$r['visit_date']]['batal'] += (int) $r['n']; continue; }
      $tren[$r['visit_date']]['kunjungan'] += (int) $r['n'];
      $tren[$r['visit_date']]['pendapatan'] += (int) $r['rp'];
      $hariMinggu[(int) date('N', strtotime($r['visit_date']))] += (int) $r['n'];
   }
   $jam = array_fill(0, 24, 0);
   foreach ($rowsJam as $r) {
      if ($lolos($r) && ctype_digit((string) $r['jam']) && (int) $r['jam'] < 24) $jam[(int) $r['jam']] += (int) $r['n'];
   }
   $perDokter = [];
   foreach ($rowsDok as $r) {
      if (!$lolos($r)) continue;
      $dok = trim((string) $r['id_doctor']);
      // nama dokter diketik bebas: "Victor Eka Harianto", "dr VICTOR EKA HARIANTO", "dr.HENDRY SYAHPUTRA" -> 1 dokter
      $kunci = trim(preg_replace('/^(dr|drg)\s+/', '', preg_replace('/[\s.,]+/', ' ', mb_strtolower($dok))));
      $perDokter[$kunci] = $perDokter[$kunci] ?? ['nama' => $dok, 'n' => 0, 'selesai' => 0, '_varian' => []];
      $perDokter[$kunci]['n'] += (int) $r['n'];
      $perDokter[$kunci]['selesai'] += (int) $r['selesai'];
      $perDokter[$kunci]['_varian'][$dok] = ($perDokter[$kunci]['_varian'][$dok] ?? 0) + (int) $r['n'];
   }
   foreach ($perDokter as &$dk) {   // tampilkan penulisan nama yang paling sering dipakai
      arsort($dk['_varian']);
      $dk['nama'] = array_key_first($dk['_varian']);
      unset($dk['_varian']);
   }
   unset($dk);
   $perDiag = [];
   $adaDiag = 0;
   foreach ($rowsDiag as $r) {
      if (!$lolos($r)) continue;
      $perDiag[$r['kode']] = $perDiag[$r['kode']] ?? ['kode' => $r['kode'], 'nama' => $r['nama'], 'n' => 0];
      $perDiag[$r['kode']]['n'] += (int) $r['n'];
      $adaDiag += (int) $r['n'];
   }
   $gender = ['L' => 0, 'P' => 0, '?' => 0];
   $umur = ['0–5' => 0, '6–17' => 0, '18–44' => 0, '45–59' => 0, '60+' => 0, '?' => 0];
   foreach ($rowsPasien as $r) {
      $n = (int) $r['n'];
      $kpi['pasien_unik'] += $n;
      $g = mb_strtolower((string) $r['g']);
      $gender[str_starts_with($g, 'p') ? 'P' : (str_starts_with($g, 'l') ? 'L' : '?')] += $n;
      $u = $r['u'];
      $umur[$u === null || $u < 0 || $u > 120 ? '?' : ($u <= 5 ? '0–5' : ($u <= 17 ? '6–17' : ($u <= 44 ? '18–44' : ($u <= 59 ? '45–59' : '60+'))))] += $n;
   }

   $sebelum = ['dari' => $prevDari, 'sampai' => $prevSampai, 'kunjungan' => 0, 'pasien_unik' => (int) $prevPasien[0]['n'], 'pendapatan' => 0, 'batal' => 0];
   $trenLalu = array_fill(0, $jumlahHari, 0);
   $awalLalu = new DateTime($prevDari);
   foreach ($rowsPrev as $r) {
      if ($r['batal']) { $sebelum['batal'] += (int) $r['n']; continue; }
      $sebelum['kunjungan'] += (int) $r['n'];
      $sebelum['pendapatan'] += (int) $r['rp'];
      $i = $awalLalu->diff(new DateTime($r['visit_date']))->days;
      if ($i < $jumlahHari) $trenLalu[$i] += (int) $r['n'];
   }

   $urut = function (array $a, string $kunci = 'n', ?int $batas = null) {
      usort($a, fn($x, $y) => $y[$kunci] <=> $x[$kunci]);
      return $batas ? array_slice($a, 0, $batas) : $a;
   };
   arsort($perPoli);
   ksort($opsiPoli);
   asort($opsiPenjamin);

   return [
      'status' => 'success',
      'rentang' => ['dari' => $dari, 'sampai' => $sampai, 'hari' => $jumlahHari],
      'kpi' => $kpi,
      'sebelum' => $sebelum,
      'tren' => array_values($tren),
      'tren_lalu' => $trenLalu,
      'jam' => $jam,
      'hari_minggu' => array_values($hariMinggu),
      'penjamin' => $urut(array_values($perPenjamin)),
      'poli' => array_map(fn($n, $v) => ['nama' => $n, 'n' => $v], array_keys($perPoli), $perPoli),
      'dokter' => $urut(array_values($perDokter), 'n', 10),
      'dokter_total' => count($perDokter),
      'diagnosa' => $urut(array_values($perDiag), 'n', 10),
      'diagnosa_terisi' => $adaDiag,
      'gender' => $gender,
      'umur' => $umur,
      'metode' => $urut(array_values($perMetode), 'total'),
      'opsi' => [
         'poli' => array_map(fn($n, $v) => ['id' => $v, 'nama' => $n], array_keys($opsiPoli), $opsiPoli),
         'penjamin' => array_map(fn($id, $n) => ['id' => (string) $id, 'nama' => $n], array_keys($opsiPenjamin), $opsiPenjamin),
      ],
   ];
}

function operasional(mysqli $k, string $idc, string $hariIni): array
{
   // status kunjungan hari ini per poli: 10 belum check-in, 0–3 dalam layanan, 4 selesai, 99 batal
   $st = $k->prepare("SELECT COALESCE(NULLIF(TRIM(id_poli), ''), '(tanpa poli)') poli,
         SUM(visit_status = 10) checkin, SUM(visit_status IN (0, 1, 2, 3)) layanan, SUM(visit_status = 4) selesai, SUM(visit_status = 99) batal,
         SUM(visit_status = 4 AND COALESCE(status_bayar, 0) <> 1) belum_bayar
      FROM pasien_visit WHERE id_customer = ? AND visit_date = ? GROUP BY 1 ORDER BY COUNT(*) DESC");
   $st->bind_param('ss', $idc, $hariIni);
   $st->execute();
   $perPoli = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   $hari = ['checkin' => 0, 'layanan' => 0, 'selesai' => 0, 'batal' => 0, 'belum_bayar' => 0];
   foreach ($perPoli as &$p) {
      foreach ($hari as $c => $_) { $p[$c] = (int) $p[$c]; $hari[$c] += $p[$c]; }
   }
   unset($p);

   $st = $k->prepare("SELECT COALESCE(NULLIF(TRIM(r.service_class), ''), 'Tanpa kelas') kelas, COUNT(b.id_bed) total, SUM(b.bed_status <> 0) terisi
      FROM ms_room r LEFT JOIN ms_room_bed b ON b.id_room = r.id_room AND b.id_customer = r.id_customer
      WHERE r.id_customer = ? AND r.room_status = 1 GROUP BY 1 ORDER BY 1");
   $st->bind_param('s', $idc);
   $st->execute();
   $bed = array_map(fn($r) => ['kelas' => $r['kelas'], 'total' => (int) $r['total'], 'terisi' => (int) $r['terisi']], $st->get_result()->fetch_all(MYSQLI_ASSOC));
   $st->close();

   // stok: rumus sama dengan Laporan Stok Farmasi (1 query agregat masuk & keluar)
   require_once __DIR__ . '/../report/farmasiStokService.php';
   $lap = farmasiStokReport($k, $idc, $hariIni, $hariIni);
   $kritis = [];
   $habis = $minimum = 0;
   foreach ($lap['data'] as $o) {
      $stok = (float) $o['stok_saat_ini'];
      $min = (float) $o['stok_min'];
      if ($stok <= 0) $habis++;
      elseif ($min > 0 && $stok < $min) $minimum++;
      else continue;
      $dagang = trim((string) $o['pharmacy_name_trade']);
      $nama = $o['pharmacy_name_generic'] . (preg_match('/[\p{L}\d]{2,}/u', $dagang) && strcasecmp($dagang, (string) $o['pharmacy_name_generic']) !== 0 ? ' (' . $dagang . ')' : '');
      $satuan = trim((string) $o['pharmacy_unit']);
      $kritis[] = ['nama' => $nama, 'stok' => $stok, 'min' => $min, 'satuan' => preg_match('/\p{L}/u', $satuan) ? $satuan : '', 'keluar_hari_ini' => (float) $o['stok_keluar']];
   }
   // prioritas: yang dipakai hari ini dulu, lalu stok terkecil
   usort($kritis, fn($a, $b) => [$b['keluar_hari_ini'] > 0, $a['stok']] <=> [$a['keluar_hari_ini'] > 0, $b['stok']]);

   return [
      'status' => 'success',
      'tanggal' => $hariIni,
      'hari_ini' => $hari,
      'per_poli' => $perPoli,
      'bed' => $bed,
      'stok' => ['total_item' => count($lap['data']), 'habis' => $habis, 'minimum' => $minimum, 'items' => array_slice($kritis, 0, 8)],
   ];
}
