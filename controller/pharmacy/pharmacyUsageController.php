<?php
// Penelusuran pemakaian obat pasien (module/admin/pharmacy_usage, module/pharmacy/pemakaian-obat).
//   GET action=get_medicines                         -> master obat faskes (untuk pilihan obat)
//   GET action=ranking&from=&to=                     -> rekap pemakaian per obat + dashboard (tren, dokter, poli, layanan, golongan)
//   GET action=excel&from=&to=                       -> XLSX (Ringkasan, Rekap per Obat, Per Dokter, Per Poli)
//   GET action=list&from=&to=&id_pharmacy=&status=&layanan=&q=&start=&length=&draw=[&summary=1]
//                                                    -> daftar pemakaian (server-side, per halaman)
//   GET action=detail&id_pharmacy_details=           -> detail satu pemakaian + isi resep
//
// Catatan performa: tabel resep & detail belum ber-index (id_visit / id_permintaan_farmasi / id_pharmacy),
// jadi setiap query dibatasi periode (maks 366 hari) dan daftar diambil per halaman.
include '../../database/connect.php';
mysqli_report(MYSQLI_REPORT_OFF);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function jawab(array $data, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}

function semua(mysqli $k, string $sql, string $types = '', array $params = []): array
{
   $stmt = $k->prepare($sql);
   if (!$stmt) {
      error_log('[pharmacyUsageController] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal mengambil data pemakaian obat.'], 500);
   }
   if ($types !== '') $stmt->bind_param($types, ...$params);
   $stmt->execute();
   $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();
   return $rows;
}

$idc = (string) (int) ($_SESSION['id_customer'] ?? 0);
if ($idc === '0') jawab(['status' => 'error', 'message' => 'Session customer tidak ditemukan atau telah kedaluwarsa.'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'GET') jawab(['status' => 'error', 'message' => 'Metode HTTP tidak didukung.'], 405);

/** periode [dari, sampai] — default bulan berjalan, maksimal 366 hari */
function periode(): array
{
   $from = DateTime::createFromFormat('!Y-m-d', (string) ($_GET['from'] ?? ($_GET['fromDate'] ?? ''))) ?: new DateTime('first day of this month 00:00');
   $to = DateTime::createFromFormat('!Y-m-d', (string) ($_GET['to'] ?? ($_GET['toDate'] ?? ''))) ?: new DateTime('today');
   if ($to < $from) [$from, $to] = [$to, $from];
   if ($from->diff($to)->days > 366) jawab(['status' => 'error', 'message' => 'Periode maksimal 1 tahun. Persempit rentang tanggal.'], 422);
   return [$from->format('Y-m-d 00:00:00'), (clone $to)->modify('+1 day')->format('Y-m-d 00:00:00'), $from->format('Y-m-d'), $to->format('Y-m-d')];
}

// filter visibilitas master obat faskes (sama dengan pharmacyController)
const FILTER_MASTER = "
   (mp.id_customer = ? OR (mp.id_customer = 0 AND log.id IS NULL))
   AND (log.status_log IS NULL OR log.status_log != 'DELETE')
   AND mp.pharmacy_name_generic IS NOT NULL AND TRIM(mp.pharmacy_name_generic) != ''";

$action = $_GET['action'] ?? 'list';

// ================= MASTER OBAT =================
if ($action === 'get_medicines') {
   $rows = semua(
      $koneksi,
      "SELECT mp.id_pharmacy, mp.pharmacy_code, mp.pharmacy_name_generic, mp.pharmacy_name_trade,
              mp.pharmacy_category, mp.pharmcy_golongan, mp.pharmacy_unit
       FROM ms_pharmacy mp
       LEFT JOIN ms_pharmacy_parrent log ON log.parent_id = mp.id_pharmacy AND log.id_customer_real = ?
       WHERE " . FILTER_MASTER . "
       ORDER BY mp.pharmacy_name_generic ASC",
      'ii',
      [(int) $idc, (int) $idc]
   );
   jawab(['status' => 'success', 'data' => $rows]);
}

// ================= REKAP PER OBAT + DASHBOARD =================
/**
 * Rekap pemakaian pada periode: per obat (ranking) + tren, per dokter, per poli, layanan, golongan.
 * 1 query baris resep (tanpa join), info kunjungan diambil untuk ID yang muncul saja (index visit_ID),
 * lalu semua agregasi di PHP. Qty tanpa harga di resep diestimasi dengan harga master obat.
 */
function hitungRekap(mysqli $koneksi, string $idc, string $dari, string $sampai, string $dariTgl, string $sampaiTgl): array
{
   $hari = (new DateTime($dariTgl))->diff(new DateTime($sampaiTgl))->days + 1;
   $perHari = $hari <= 92;
   $idcInt = (int) $idc;

   // harga master dimuat lebih dulu: qty tanpa harga di resep langsung diestimasi per baris (hemat memori)
   $hargaM = [];
   $st = $koneksi->prepare("SELECT id_pharmacy, pharmacy_price_item FROM ms_pharmacy WHERE id_customer IN (?, 0)");
   $st->bind_param('i', $idcInt);
   $st->execute();
   $res = $st->get_result();
   while ($m = $res->fetch_row()) $hargaM[(int) $m[0]] = (int) $m[1];
   $st->close();

   $stmt = $koneksi->prepare(
      "SELECT ppd.id_pharmacy, pp.id_permintaan_farmasi, pp.id_visit, ppd.qty, ppd.harga, pp.created_at
       FROM permintaan_pharmacy pp
       JOIN permintaan_pharmacy_details ppd ON ppd.id_permintaan_farmasi = pp.id_permintaan_farmasi
       WHERE pp.id_customer = ? AND pp.created_at >= ? AND pp.created_at < ?"
   );
   if (!$stmt) jawab(['status' => 'error', 'message' => 'Gagal mengambil rekap pemakaian.'], 500);
   $stmt->bind_param('sss', $idc, $dari, $sampai);
   $stmt->execute();
   $hasil = $stmt->get_result();

   $per = [];
   $resepVisit = [];   // id resep -> id kunjungan
   $resepWaktu = [];   // id resep -> kunci tren
   $tren = [];         // kunci -> [qty, nilai]
   $perVisit = [];     // id kunjungan -> [qty, nilai]
   $totalQty = 0;
   $baris = 0;
   while ($r = $hasil->fetch_row()) {
      [$idPh, $idResep, $idVisit, $qty, $harga, $waktu] = $r;
      $idPh = (int) $idPh;
      $qty = (int) $qty;
      $harga = (int) $harga;
      $nilai = $qty * ($harga > 0 ? $harga : ($hargaM[$idPh] ?? 0));
      $p = &$per[$idPh];
      $p ??= ['n' => 0, 'qty' => 0, 'visit' => [], 'nominal' => 0];
      $p['n']++;
      $p['qty'] += $qty;
      $p['visit'][$idVisit] = true;
      $p['nominal'] += $nilai;
      unset($p);
      $kunci = $perHari ? substr($waktu, 0, 10) : substr($waktu, 0, 7);
      $resepVisit[$idResep] = (string) $idVisit;
      $resepWaktu[$idResep] ??= $kunci;
      $t = &$tren[$kunci];
      $t ??= [0, 0];
      $t[0] += $qty;
      $t[1] += $nilai;
      unset($t);
      $v = &$perVisit[$idVisit];
      $v ??= [0, 0];
      $v[0] += $qty;
      $v[1] += $nilai;
      unset($v);
      $totalQty += $qty;
      $baris++;
   }
   $stmt->close();

   // info kunjungan (dokter, poli, rawat inap) hanya untuk ID yang muncul — index visit_ID, per 1000
   $visit = [];
   foreach (array_chunk(array_map('strval', array_keys($perVisit)), 1000) as $blok) {
      $ph = implode(',', array_fill(0, count($blok), '?'));
      $st = $koneksi->prepare("SELECT visit_ID, id_doctor, id_poli, status_rawatinap FROM pasien_visit WHERE visit_ID IN ($ph)");
      $st->bind_param(str_repeat('s', count($blok)), ...$blok);
      $st->execute();
      $res = $st->get_result();
      while ($v = $res->fetch_row()) {
         $visit[$v[0]] = [trim((string) $v[1]) ?: '-', trim((string) $v[2]) ?: '-', (int) $v[3] === 1 ? 'Rawat inap' : 'Rawat jalan'];
      }
      $st->close();
   }
   $resepPerVisit = array_count_values($resepVisit);
   $ember = ['dokter' => [], 'poli' => [], 'layanan' => []];
   foreach ($perVisit as $idVisit => [$q, $n]) {
      $v = $visit[$idVisit] ?? ['-', '-', 'Tanpa kunjungan'];
      $jr = $resepPerVisit[(string) $idVisit] ?? 0;
      foreach (['dokter' => $v[0], 'poli' => $v[1], 'layanan' => $v[2]] as $jenis => $k) {
         $t = &$ember[$jenis][$k];
         $t ??= ['qty' => 0, 'nominal' => 0, 'resep' => 0];
         $t['qty'] += $q;
         $t['nominal'] += $n;
         $t['resep'] += $jr;
         unset($t);
      }
   }
   $resepPerTgl = array_count_values($resepWaktu);
   $jmlResep = count($resepVisit);
   $jmlVisit = count($perVisit);
   unset($resepVisit, $resepWaktu, $perVisit, $visit);

   $info = [];
   if ($per) {
      $ids = array_keys($per);
      $ph = implode(',', array_fill(0, count($ids), '?'));
      foreach (semua(
         $koneksi,
         "SELECT id_pharmacy, pharmacy_code, pharmacy_name_generic, pharmacy_name_trade, pharmacy_category,
                 pharmcy_golongan, pharmacy_unit
          FROM ms_pharmacy WHERE id_pharmacy IN ($ph)",
         str_repeat('i', count($ids)),
         $ids
      ) as $m) {
         $info[(int) $m['id_pharmacy']] = $m;
      }
   }

   $data = [];
   $totalNominal = 0;
   $golongan = [];
   foreach ($per as $idPh => $p) {
      $m = $info[$idPh] ?? [];
      $totalNominal += $p['nominal'];
      $g = trim((string) ($m['pharmcy_golongan'] ?? ''));
      $g = preg_match('/[\p{L}\p{N}]/u', $g) ? ucwords(mb_strtolower($g)) : 'Tidak diisi';
      $golongan[$g] ??= ['qty' => 0, 'nominal' => 0, 'obat' => 0];
      $golongan[$g]['qty'] += $p['qty'];
      $golongan[$g]['nominal'] += $p['nominal'];
      $golongan[$g]['obat']++;
      $data[] = [
         'id_pharmacy' => $idPh,
         'pharmacy_code' => $m['pharmacy_code'] ?? null,
         'pharmacy_name_generic' => $m['pharmacy_name_generic'] ?? '(obat tidak ada di master)',
         'pharmacy_name_trade' => $m['pharmacy_name_trade'] ?? null,
         'pharmacy_category' => $m['pharmacy_category'] ?? null,
         'pharmcy_golongan' => $m['pharmcy_golongan'] ?? null,
         'pharmacy_unit' => $m['pharmacy_unit'] ?? null,
         'jml_item' => $p['n'],
         'jml_kunjungan' => count($p['visit']),
         'total_qty' => $p['qty'],
         'nominal' => $p['nominal'],
      ];
   }
   usort($data, fn($a, $b) => $b['total_qty'] <=> $a['total_qty'] ?: $b['jml_kunjungan'] <=> $a['jml_kunjungan']);
   foreach ($data as $i => &$d) $d['rank'] = $i + 1;
   unset($d);

   $trenOut = [];
   if ($perHari) {
      for ($d = new DateTime($dariTgl); $d->format('Y-m-d') <= $sampaiTgl; $d->modify('+1 day')) $trenOut[$d->format('Y-m-d')] = ['nama' => $d->format('Y-m-d'), 'qty' => 0, 'nominal' => 0, 'resep' => 0];
   }
   foreach ($tren as $k => [$q, $n]) $trenOut[$k] = ['nama' => (string) $k, 'qty' => $q, 'nominal' => $n, 'resep' => $resepPerTgl[$k] ?? 0];
   ksort($trenOut);
   $daftar = function (array $e) {
      $o = array_map(fn($k, $v) => ['nama' => (string) $k] + $v, array_keys($e), $e);
      usort($o, fn($x, $y) => $y['resep'] <=> $x['resep']);
      return $o;
   };
   uasort($golongan, fn($x, $y) => $y['qty'] <=> $x['qty']);

   return [
      'status' => 'success',
      'periode' => [$dariTgl, $sampaiTgl],
      'summary' => [
         'jml_obat' => count($data),
         'jml_resep' => $jmlResep,
         'jml_kunjungan' => $jmlVisit,
         'total_qty' => $totalQty,
         'nominal' => $totalNominal,
         'item_per_resep' => $jmlResep ? round($baris / $jmlResep, 1) : 0,
         'nilai_per_resep' => $jmlResep ? round($totalNominal / $jmlResep) : 0,
         'resep_per_hari' => round($jmlResep / max(1, $hari), 1),
      ],
      'dashboard' => [
         'periode' => $perHari ? 'hari' : 'bulan',
         'tren' => array_values($trenOut),
         'dokter' => $daftar($ember['dokter']),
         'poli' => $daftar($ember['poli']),
         'layanan' => $daftar($ember['layanan']),
         'golongan' => array_map(fn($k, $v) => ['nama' => (string) $k] + $v, array_keys($golongan), $golongan),
      ],
      'data' => $data,
   ];
}

if ($action === 'ranking' || $action === 'excel') {
   [$dari, $sampai, $dariTgl, $sampaiTgl] = periode();
   $kunciCache = "usage_rank2_{$idc}_{$dariTgl}_{$sampaiTgl}";
   $cache = $_SESSION[$kunciCache] ?? null;
   if (is_array($cache) && $cache['t'] > time() - 300 && empty($_GET['refresh'])) {
      $out = $cache['data'] + ['cached' => true];
   } else {
      $out = hitungRekap($koneksi, $idc, $dari, $sampai, $dariTgl, $sampaiTgl);
      $_SESSION[$kunciCache] = ['t' => time(), 'data' => $out];
   }
   if ($action === 'ranking') jawab($out);

   // ---------- Excel ----------
   require_once __DIR__ . '/../report/xlsxRingan.php';
   require_once __DIR__ . '/../report/appointmentService.php';
   $klinik = apKlinik($koneksi, (int) $idc);
   $s = $out['summary'];
   $db = $out['dashboard'];
   $rp = fn($v) => ['v' => (float) $v, 's' => 6];
   $x = new XlsxRingan();
   $x->sheet('Ringkasan', [34, 16, 12, 16]);
   $x->row([['v' => 'LAPORAN PEMAKAIAN OBAT PASIEN — ' . mb_strtoupper($klinik['nama']), 's' => 3]]);
   $x->row([['v' => 'Periode ' . apTglIndo($dariTgl) . ' s/d ' . apTglIndo($sampaiTgl) . ' · nilai = qty × harga resep (harga master bila kosong) · dibuat ' . date('d-m-Y H:i'), 's' => 4]]);
   $x->kosong();
   $x->row(['Indikator', 'Nilai'], 1);
   foreach ([
      ['Jenis obat dipakai', $s['jml_obat']], ['Resep', $s['jml_resep']], ['Kunjungan pasien', $s['jml_kunjungan']],
      ['Jumlah obat keluar', $s['total_qty']], ['Estimasi nilai obat', $s['nominal']], ['Rata-rata nilai per resep', $s['nilai_per_resep']],
   ] as [$k, $v]) $x->row([$k, $rp($v)]);
   $x->row(['Rata-rata item per resep', (float) $s['item_per_resep']]);
   $x->row(['Rata-rata resep per hari', (float) $s['resep_per_hari']]);
   foreach (['Layanan' => $db['layanan'], 'Golongan obat' => $db['golongan']] as $judul => $isi) {
      $x->kosong();
      $x->row([$judul, 'Jumlah keluar', $judul === 'Golongan obat' ? 'Jenis obat' : 'Resep', 'Nilai'], 1);
      foreach ($isi as $v) $x->row([$v['nama'], $rp($v['qty']), $v['resep'] ?? $v['obat'], $rp($v['nominal'])]);
   }
   $x->kosong();
   $x->row([$db['periode'] === 'hari' ? 'Tanggal' : 'Bulan', 'Jumlah keluar', 'Resep', 'Nilai'], 1);
   foreach ($db['tren'] as $t) $x->row([$t['nama'], $rp($t['qty']), $t['resep'], $rp($t['nominal'])]);

   $x->sheet('Rekap per Obat', [8, 36, 22, 16, 16, 10, 12, 12, 14, 16]);
   $x->freezeHeader();
   $x->autoFilter(10);
   $x->row(['Peringkat', 'Obat', 'Nama dagang', 'Kategori', 'Golongan', 'Satuan', 'Kunjungan', 'Baris resep', 'Jumlah keluar', 'Estimasi nilai'], 1);
   foreach ($out['data'] as $d) {
      $x->row([$d['rank'], $d['pharmacy_name_generic'], (string) $d['pharmacy_name_trade'], (string) $d['pharmacy_category'], (string) $d['pharmcy_golongan'], (string) $d['pharmacy_unit'],
         $d['jml_kunjungan'], $d['jml_item'], $rp($d['total_qty']), $rp($d['nominal'])], 5);
   }
   foreach (['Per Dokter' => $db['dokter'], 'Per Poli' => $db['poli']] as $judul => $isi) {
      $x->sheet($judul, [34, 10, 14, 16, 16]);
      $x->freezeHeader();
      $x->row([$judul === 'Per Dokter' ? 'Dokter' : 'Poli', 'Resep', 'Jumlah keluar', 'Estimasi nilai', 'Nilai per resep'], 1);
      foreach ($isi as $v) $x->row([$v['nama'], $v['resep'], $rp($v['qty']), $rp($v['nominal']), $rp($v['resep'] ? $v['nominal'] / $v['resep'] : 0)], 5);
   }
   setcookie('pu_unduh', preg_replace('/[^a-z0-9]/', '', (string) ($_GET['token'] ?? '')), ['path' => '/', 'samesite' => 'Lax']);
   header_remove('Content-Type');
   $x->kirim('pemakaian_obat_' . $dariTgl . '_' . $sampaiTgl . '.xlsx');
   exit;
}

// ================= DAFTAR PEMAKAIAN =================
if ($action === 'list') {
   [$dari, $sampai, $dariTgl, $sampaiTgl] = periode();
   $where = ['pp.id_customer = ?', 'pp.created_at >= ?', 'pp.created_at < ?'];
   $types = 'sss';
   $params = [$idc, $dari, $sampai];

   $idPh = (int) ($_GET['id_pharmacy'] ?? 0);
   if ($idPh > 0) {
      $where[] = 'ppd.id_pharmacy = ?';
      $types .= 'i';
      $params[] = $idPh;
   }
   $status = (string) ($_GET['status'] ?? ($_GET['status_permintaan'] ?? ''));
   if ($status !== '' && $status !== 'all' && ctype_digit($status)) {
      $where[] = 'pp.status_permintaan = ?';
      $types .= 'i';
      $params[] = (int) $status;
   }
   $layanan = (string) ($_GET['layanan'] ?? '');
   if ($layanan === 'Rawat Inap') {
      $where[] = "(pv.status_rawatinap = 1 OR pv.source_hub = 'Rawat Inap')";
   } elseif ($layanan === 'UGD') {
      $where[] = "pv.source_hub IN ('UGD', 'IGD')";
   } elseif ($layanan === 'Poliklinik') {
      $where[] = "(pv.source_hub = 'Poliklinik' AND (pv.status_rawatinap IS NULL OR pv.status_rawatinap <> 1))";
   }
   $q = trim(mb_substr((string) ($_GET['q'] ?? ($_GET['search'] ?? '')), 0, 60));
   if ($q !== '') {
      $like = '%' . addcslashes($q, '%_\\') . '%';
      $where[] = '(p.patient_name LIKE ? OR pv.patient_name_pcare LIKE ? OR p.nomor_rm LIKE ? OR pp.permintaan_number LIKE ? OR pv.id_doctor LIKE ?'
         . ($idPh > 0 ? '' : ' OR mp.pharmacy_name_generic LIKE ? OR mp.pharmacy_name_trade LIKE ?') . ')';
      $n = $idPh > 0 ? 5 : 7;
      $types .= str_repeat('s', $n);
      array_push($params, ...array_fill(0, $n, $like));
   }
   $whereSql = implode(' AND ', $where);
   // join pasien hanya bila filter membutuhkannya (pencarian / layanan)
   $butuhPasien = $q !== '' || in_array($layanan, ['Rawat Inap', 'UGD', 'Poliklinik'], true);
   $fromFilter = "FROM permintaan_pharmacy pp
      JOIN permintaan_pharmacy_details ppd ON ppd.id_permintaan_farmasi = pp.id_permintaan_farmasi"
      . ($q !== '' && $idPh <= 0 ? " LEFT JOIN ms_pharmacy mp ON mp.id_pharmacy = ppd.id_pharmacy" : '')
      . ($butuhPasien ? " LEFT JOIN pasien_visit pv ON pv.visit_ID = pp.id_visit LEFT JOIN ms_patient p ON p.id_patient = pv.id_patient" : '');

   $start = max(0, (int) ($_GET['start'] ?? 0));
   $length = min(max((int) ($_GET['length'] ?? 25), 1), 100);

   // 1) urut & potong di id saja
   $ids = array_map('intval', array_column(semua(
      $koneksi,
      "SELECT ppd.id_pharmacy_details $fromFilter WHERE $whereSql
       ORDER BY pp.created_at DESC, ppd.id_pharmacy_details DESC LIMIT ?, ?",
      $types . 'ii',
      array_merge($params, [$start, $length])
   ), 'id_pharmacy_details'));

   // 2) lengkapi data untuk baris di halaman ini
   $rows = [];
   if ($ids) {
      $ph = implode(',', array_fill(0, count($ids), '?'));
      $rows = semua(
         $koneksi,
         "SELECT ppd.id_pharmacy_details, ppd.id_permintaan_farmasi, ppd.id_pharmacy, ppd.item_name, ppd.signa,
                 ppd.qty, ppd.catatan, ppd.harga,
                 pp.permintaan_number, pp.status_permintaan, pp.tipe_obat, pp.created_at AS tgl_resep,
                 pv.visit_ID, pv.visit_date, pv.source_hub, pv.status_rawatinap, pv.id_doctor AS doctor_name,
                 pv.id_poli AS poli_name, pv.id_provider,
                 p.id_patient, p.nomor_rm, COALESCE(p.patient_name, pv.patient_name_pcare) AS patient_name,
                 p.patient_gender, p.patient_datebirth,
                 mp.pharmacy_code, mp.pharmacy_name_generic, mp.pharmacy_name_trade, mp.pharmacy_unit, mp.pharmcy_golongan
          FROM permintaan_pharmacy_details ppd
          JOIN permintaan_pharmacy pp ON pp.id_permintaan_farmasi = ppd.id_permintaan_farmasi
          LEFT JOIN ms_pharmacy mp ON mp.id_pharmacy = ppd.id_pharmacy
          LEFT JOIN pasien_visit pv ON pv.visit_ID = pp.id_visit
          LEFT JOIN ms_patient p ON p.id_patient = pv.id_patient
          WHERE ppd.id_pharmacy_details IN ($ph)
          ORDER BY pp.created_at DESC, ppd.id_pharmacy_details DESC",
         str_repeat('i', count($ids)),
         $ids
      );
   }
   $out = ['status' => 'success', 'draw' => (int) ($_GET['draw'] ?? 1), 'periode' => [$dariTgl, $sampaiTgl], 'data' => $rows];

   // ringkasan & total baris hanya saat filter berubah (bukan setiap pindah halaman).
   // Tanpa filter tambahan, ringkasan diambil dari rekap periode yang sudah di-cache (hemat 1 scan tabel).
   $rekap = $_SESSION["usage_rank_{$idc}_{$dariTgl}_{$sampaiTgl}"] ?? null;
   $tanpaFilter = $q === '' && $layanan === '' && ($status === '' || $status === 'all');
   $perluRingkasan = !empty($_GET['summary']) || !isset($_GET['total']);
   if ($perluRingkasan && $tanpaFilter && is_array($rekap) && $rekap['t'] > time() - 300) {
      $r = $rekap['data'];
      if ($idPh > 0) {
         $o = current(array_filter($r['data'], fn($x) => $x['id_pharmacy'] === $idPh)) ?: null;
         $sum = ['n' => $o['jml_item'] ?? 0, 'resep' => $o['jml_item'] ?? 0, 'kunjungan' => $o['jml_kunjungan'] ?? 0, 'qty' => $o['total_qty'] ?? 0, 'nominal' => $o['nominal'] ?? 0];
      } else {
         $sum = ['n' => array_sum(array_column($r['data'], 'jml_item')), 'resep' => $r['summary']['jml_resep'], 'kunjungan' => $r['summary']['jml_kunjungan'], 'qty' => $r['summary']['total_qty'], 'nominal' => $r['summary']['nominal']];
      }
      $out['summary'] = array_map('intval', $sum);
      $total = (int) $sum['n'];
   } elseif ($perluRingkasan) {
      $s = semua(
         $koneksi,
         "SELECT COUNT(*) n, COUNT(DISTINCT pp.id_permintaan_farmasi) resep,
                 COUNT(DISTINCT pp.id_visit) kunjungan,
                 COALESCE(SUM(ppd.qty), 0) qty,
                 COALESCE(SUM(ppd.qty * COALESCE(NULLIF(ppd.harga, 0), mp.pharmacy_price_item, 0)), 0) nominal
          FROM permintaan_pharmacy pp
          JOIN permintaan_pharmacy_details ppd ON ppd.id_permintaan_farmasi = pp.id_permintaan_farmasi
          LEFT JOIN ms_pharmacy mp ON mp.id_pharmacy = ppd.id_pharmacy"
         . ($butuhPasien ? " LEFT JOIN pasien_visit pv ON pv.visit_ID = pp.id_visit LEFT JOIN ms_patient p ON p.id_patient = pv.id_patient" : '') . "
          WHERE $whereSql",
         $types,
         $params
      )[0];
      $out['summary'] = array_map('intval', $s);
      $total = (int) $s['n'];
   } else {
      $total = max(0, (int) $_GET['total']);
   }
   $out['recordsTotal'] = $out['recordsFiltered'] = $total;

   if ($idPh > 0) {
      $out['obat'] = semua(
         $koneksi,
         "SELECT id_pharmacy, pharmacy_code, pharmacy_name_generic, pharmacy_name_trade, pharmacy_category,
                 pharmacy_sub_category, pharmcy_golongan, pharmacy_unit, kfa_code
          FROM ms_pharmacy WHERE id_pharmacy = ? AND (id_customer = ? OR id_customer = 0) LIMIT 1",
         'ii',
         [$idPh, (int) $idc]
      )[0] ?? null;
   }
   jawab($out);
}

// ================= DETAIL =================
if ($action === 'detail') {
   $idDetail = (int) ($_GET['id_pharmacy_details'] ?? 0);
   $d = $idDetail > 0 ? (semua(
      $koneksi,
      "SELECT ppd.id_pharmacy_details, ppd.id_permintaan_farmasi, ppd.qty, ppd.signa, ppd.catatan, ppd.item_name,
              pp.permintaan_number, pp.status_permintaan, pp.tipe_obat, pp.catatan_permintaan, pp.created_at AS tgl_resep,
              pp.rck_jumlah, pp.rck_satuan, pp.rck_signa,
              pv.visit_ID, pv.visit_date, pv.source_hub, pv.id_doctor AS doctor_name, pv.id_poli AS poli_name,
              p.id_patient, p.nomor_rm, COALESCE(p.patient_name, pv.patient_name_pcare) AS patient_name,
              p.patient_gender, p.patient_datebirth, p.patient_address, p.patient_allergy,
              mprov.provider_name,
              mp.pharmacy_code, mp.pharmacy_name_generic, mp.pharmacy_name_trade, mp.pharmacy_unit
       FROM permintaan_pharmacy_details ppd
       JOIN permintaan_pharmacy pp ON pp.id_permintaan_farmasi = ppd.id_permintaan_farmasi
       LEFT JOIN ms_pharmacy mp ON mp.id_pharmacy = ppd.id_pharmacy
       LEFT JOIN pasien_visit pv ON pv.visit_ID = pp.id_visit
       LEFT JOIN ms_patient p ON p.id_patient = pv.id_patient
       LEFT JOIN ms_provider mprov ON mprov.id_provider = pv.id_provider
       WHERE ppd.id_pharmacy_details = ? AND pp.id_customer = ?
       LIMIT 1",
      'is',
      [$idDetail, $idc]
   )[0] ?? null) : null;
   if (!$d) jawab(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);

   $d['other_medicines'] = semua(
      $koneksi,
      "SELECT d.id_pharmacy_details, d.qty, d.signa, d.catatan,
              COALESCE(NULLIF(TRIM(mp.pharmacy_name_generic), ''), d.item_name) AS nama_obat,
              mp.pharmacy_name_trade, mp.pharmacy_unit
       FROM permintaan_pharmacy_details d
       LEFT JOIN ms_pharmacy mp ON mp.id_pharmacy = d.id_pharmacy
       WHERE d.id_permintaan_farmasi = ?
       ORDER BY d.id_pharmacy_details ASC",
      'i',
      [(int) $d['id_permintaan_farmasi']]
   );
   jawab(['status' => 'success', 'data' => $d]);
}

jawab(['status' => 'error', 'message' => 'Aksi tidak dikenal.'], 400);
