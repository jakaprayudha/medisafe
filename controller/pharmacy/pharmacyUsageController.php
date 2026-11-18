<?php
// Penelusuran pemakaian obat pasien (module/admin/pharmacy_usage, module/pharmacy/pemakaian-obat).
//   GET action=get_medicines                         -> master obat faskes (untuk pilihan obat)
//   GET action=ranking&from=&to=                     -> rekap pemakaian per obat pada periode
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

// ================= REKAP PER OBAT =================
if ($action === 'ranking') {
   [$dari, $sampai, $dariTgl, $sampaiTgl] = periode();
   $kunciCache = "usage_rank_{$idc}_{$dariTgl}_{$sampaiTgl}";
   $cache = $_SESSION[$kunciCache] ?? null;
   if (is_array($cache) && $cache['t'] > time() - 300 && empty($_GET['refresh'])) {
      jawab($cache['data'] + ['cached' => true]);
   }

   // 1 query baris mentah, agregasi di PHP (lebih cepat dari COUNT DISTINCT per grup di tabel tanpa index)
   $stmt = $koneksi->prepare(
      "SELECT ppd.id_pharmacy, pp.id_permintaan_farmasi, pp.id_visit, ppd.qty, ppd.harga
       FROM permintaan_pharmacy pp
       JOIN permintaan_pharmacy_details ppd ON ppd.id_permintaan_farmasi = pp.id_permintaan_farmasi
       WHERE pp.id_customer = ? AND pp.created_at >= ? AND pp.created_at < ?"
   );
   if (!$stmt) jawab(['status' => 'error', 'message' => 'Gagal mengambil rekap pemakaian.'], 500);
   $stmt->bind_param('sss', $idc, $dari, $sampai);
   $stmt->execute();
   $hasil = $stmt->get_result();

   $per = [];
   $resepAll = $visitAll = [];
   $totalQty = 0;
   while ($r = $hasil->fetch_row()) {
      [$idPh, $idResep, $idVisit, $qty, $harga] = $r;
      $idPh = (int) $idPh;
      $p = &$per[$idPh];
      $p ??= ['n' => 0, 'qty' => 0, 'visit' => [], 'nominal' => 0, 'tanpa_harga' => 0];
      $p['n']++;
      $p['qty'] += (int) $qty;
      $p['visit'][$idVisit] = true;
      if ((int) $harga > 0) $p['nominal'] += (int) $qty * (int) $harga;
      else $p['tanpa_harga'] += (int) $qty;
      unset($p);
      $resepAll[$idResep] = true;
      $visitAll[$idVisit] = true;
      $totalQty += (int) $qty;
   }
   $stmt->close();

   // nama & harga master untuk obat yang muncul (termasuk yang sudah dihapus dari daftar)
   $info = [];
   if ($per) {
      $ids = array_keys($per);
      $ph = implode(',', array_fill(0, count($ids), '?'));
      foreach (semua(
         $koneksi,
         "SELECT id_pharmacy, pharmacy_code, pharmacy_name_generic, pharmacy_name_trade, pharmacy_category,
                 pharmcy_golongan, pharmacy_unit, pharmacy_price_item
          FROM ms_pharmacy WHERE id_pharmacy IN ($ph)",
         str_repeat('i', count($ids)),
         $ids
      ) as $m) {
         $info[(int) $m['id_pharmacy']] = $m;
      }
   }

   $data = [];
   $totalNominal = 0;
   foreach ($per as $idPh => $p) {
      $m = $info[$idPh] ?? [];
      // qty tanpa harga di resep diestimasi dengan harga master
      $nominal = $p['nominal'] + $p['tanpa_harga'] * (int) ($m['pharmacy_price_item'] ?? 0);
      $totalNominal += $nominal;
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
         'nominal' => $nominal,
      ];
   }
   usort($data, fn($a, $b) => $b['total_qty'] <=> $a['total_qty'] ?: $b['jml_kunjungan'] <=> $a['jml_kunjungan']);

   $out = [
      'status' => 'success',
      'periode' => [$dariTgl, $sampaiTgl],
      'summary' => [
         'jml_obat' => count($data),
         'jml_resep' => count($resepAll),
         'jml_kunjungan' => count($visitAll),
         'total_qty' => $totalQty,
         'nominal' => $totalNominal,
      ],
      'data' => $data,
   ];
   $_SESSION[$kunciCache] = ['t' => time(), 'data' => $out];
   jawab($out);
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
