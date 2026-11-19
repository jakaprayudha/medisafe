<?php
/**
 * Data laporan stok farmasi, dipakai bersama oleh:
 *   - controller/report/farmasiController.php (tabel module/admin/rpt_farmasi)
 *   - module/report/export_farmasi.php        (Excel)
 *   - module/report/print_farmasi.php         (Print)
 *
 * Rumus (per klinik):
 *   stok dasar   = ms_pharmacy.pharmacy_stock
 *   masuk        = pharmacy_stock_penerimaan.jumlah (per tanggal penerimaan, klinik ini)
 *   keluar       = permintaan_pharmacy_details.qty  (per tanggal dibuat, resep klinik ini)
 *   stok awal    = dasar + masuk sebelum periode - keluar sebelum periode
 *   stok akhir   = stok awal + masuk periode - keluar periode   (posisi di tanggal akhir)
 *   stok saat ini= dasar + semua masuk - semua keluar
 *
 * Masuk & keluar diambil dengan 1 query agregat masing-masing (bukan subquery per obat),
 * karena permintaan_pharmacy_details (600rb+ baris) tidak ber-index id_pharmacy.
 */

function farmasiStokReport(mysqli $koneksi, string $idCustomer, string $fromDate, string $toDate, ?int $idPharmacy = null): array
{
   // $idPharmacy diisi = hitung satu obat saja (halaman admin/pharmacy_stock), rumus tetap sama
   $satuObat = $idPharmacy !== null;
   // ================== MASTER OBAT KLINIK ==================
   $stmt = $koneksi->prepare("SELECT
         p.id_pharmacy, p.pharmacy_code, p.pharmacy_name_generic, p.pharmacy_name_trade,
         p.pharmacy_category, p.pharmacy_sub_category, p.pharmcy_golongan, p.pharmcy_jenis_drugs,
         p.pharmacy_bentuk_sediaan, p.pharmacy_dosis, p.pharmacy_unit, p.pharmacy_kemasan,
         p.pharmacy_supplier, p.pharmacy_factory,
         COALESCE(p.pharmacy_price_buy, 0) AS pharmacy_price_buy,
         COALESCE(p.pharmacy_buy, 0) AS pharmacy_buy,
         COALESCE(p.pharmacy_sale, 0) AS pharmacy_sale,
         COALESCE(p.pharmacy_stock, 0) AS stok_dasar,
         COALESCE(p.stok_min, 0) AS stok_min,
         COALESCE(p.stok_max, 0) AS stok_max
      FROM ms_pharmacy p
      LEFT JOIN ms_pharmacy_parrent log
         ON log.parent_id = p.id_pharmacy AND log.id_customer_real = ?
      WHERE (p.id_customer = ? OR (p.id_customer = 0 AND log.id IS NULL))
        AND (log.status_log IS NULL OR log.status_log != 'DELETE')
        AND p.pharmacy_name_generic IS NOT NULL
        AND TRIM(p.pharmacy_name_generic) != ''
        AND p.pharmacy_status = 1" . ($satuObat ? " AND p.id_pharmacy = ?" : "") . "
      ORDER BY p.pharmacy_name_generic ASC");
   if (!$stmt) {
      throw new RuntimeException('Prepare query obat gagal: ' . $koneksi->error);
   }
   $satuObat ? $stmt->bind_param('ssi', $idCustomer, $idCustomer, $idPharmacy) : $stmt->bind_param('ss', $idCustomer, $idCustomer);
   $stmt->execute();
   $obat = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();

   $awalPeriode = $fromDate . ' 00:00:00';
   $akhirPeriode = $toDate . ' 23:59:59';

   // ================== MASUK (penerimaan) ==================
   $masuk = [];
   $stmt = $koneksi->prepare("SELECT id_pharmacy,
         SUM(jumlah) AS semua,
         SUM(CASE WHEN tanggal < ? THEN jumlah ELSE 0 END) AS sebelum,
         SUM(CASE WHEN tanggal BETWEEN ? AND ? THEN jumlah ELSE 0 END) AS periode
      FROM pharmacy_stock_penerimaan
      WHERE id_customer = ?" . ($satuObat ? " AND id_pharmacy = ?" : "") . "
      GROUP BY id_pharmacy");
   if (!$stmt) {
      throw new RuntimeException('Prepare query stok masuk gagal: ' . $koneksi->error);
   }
   $idObatStr = (string) $idPharmacy;
   $satuObat ? $stmt->bind_param('sssss', $fromDate, $fromDate, $toDate, $idCustomer, $idObatStr)
      : $stmt->bind_param('ssss', $fromDate, $fromDate, $toDate, $idCustomer);
   $stmt->execute();
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
      $masuk[(string) $r['id_pharmacy']] = $r;
   }
   $stmt->close();

   // ================== KELUAR (resep klinik ini) ==================
   $keluar = [];
   $stmt = $koneksi->prepare("SELECT d.id_pharmacy,
         SUM(d.qty) AS semua,
         SUM(CASE WHEN d.created_at < ? THEN d.qty ELSE 0 END) AS sebelum,
         SUM(CASE WHEN d.created_at BETWEEN ? AND ? THEN d.qty ELSE 0 END) AS periode,
         SUM(CASE WHEN d.created_at BETWEEN ? AND ? THEN d.qty * COALESCE(d.harga, 0) ELSE 0 END) AS nilai_periode
      FROM permintaan_pharmacy_details d
      INNER JOIN permintaan_pharmacy pp ON pp.id_permintaan_farmasi = d.id_permintaan_farmasi
      WHERE pp.id_customer = ?" . ($satuObat ? " AND d.id_pharmacy = ?" : "") . "
      GROUP BY d.id_pharmacy");
   if (!$stmt) {
      throw new RuntimeException('Prepare query stok keluar gagal: ' . $koneksi->error);
   }
   $satuObat ? $stmt->bind_param('ssssssi', $awalPeriode, $awalPeriode, $akhirPeriode, $awalPeriode, $akhirPeriode, $idCustomer, $idPharmacy)
      : $stmt->bind_param('ssssss', $awalPeriode, $awalPeriode, $akhirPeriode, $awalPeriode, $akhirPeriode, $idCustomer);
   $stmt->execute();
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
      $keluar[(string) $r['id_pharmacy']] = $r;
   }
   $stmt->close();

   // ================== HITUNG ==================
   $rows = [];
   $ringkasan = [
      'total_item' => 0, 'habis' => 0, 'minimum' => 0, 'maximum' => 0, 'normal' => 0,
      'stok_masuk' => 0.0, 'stok_keluar' => 0.0, 'nilai_stok' => 0.0, 'nilai_keluar' => 0.0,
   ];

   foreach ($obat as $row) {
      $id = (string) $row['id_pharmacy'];
      $m = $masuk[$id] ?? ['semua' => 0, 'sebelum' => 0, 'periode' => 0];
      $k = $keluar[$id] ?? ['semua' => 0, 'sebelum' => 0, 'periode' => 0, 'nilai_periode' => 0];

      $dasar = (float) $row['stok_dasar'];
      $stokAwal = $dasar + (float) $m['sebelum'] - (float) $k['sebelum'];
      $stokMasuk = (float) $m['periode'];
      $stokKeluar = (float) $k['periode'];
      $stokAkhir = $stokAwal + $stokMasuk - $stokKeluar;
      $stokSaatIni = $dasar + (float) $m['semua'] - (float) $k['semua'];
      $stokMin = (float) $row['stok_min'];
      $stokMax = (float) $row['stok_max'];

      // harga beli: pharmacy_price_buy, fallback pharmacy_buy
      $hargaBeli = (float) $row['pharmacy_price_buy'];
      if ($hargaBeli <= 0) {
         $hargaBeli = (float) $row['pharmacy_buy'];
      }
      $nilaiStok = max(0, $stokAkhir) * $hargaBeli;

      // status berdasarkan stok akhir periode
      if ($stokAkhir <= 0) {
         [$statusCode, $statusStock] = ['habis', 'Habis'];
      } elseif ($stokMin > 0 && $stokAkhir < $stokMin) {
         [$statusCode, $statusStock] = ['minimum', 'Di Bawah Minimum'];
      } elseif ($stokMax > 0 && $stokAkhir > $stokMax) {
         [$statusCode, $statusStock] = ['maximum', 'Di Atas Maksimum'];
      } else {
         [$statusCode, $statusStock] = ['normal', 'Normal'];
      }

      $row['pharmacy_stock'] = $stokAwal; // kompatibilitas nama lama
      $row['stok_awal'] = $stokAwal;
      $row['stok_masuk'] = $stokMasuk;
      $row['stok_keluar'] = $stokKeluar;
      $row['stok_akhir'] = $stokAkhir;
      $row['stok_saat_ini'] = $stokSaatIni;
      $row['stok_min'] = $stokMin;
      $row['stok_max'] = $stokMax;
      $row['pharmacy_price_buy'] = $hargaBeli;
      $row['nilai_stok'] = $nilaiStok;
      // nilai obat keluar (harga jual saat diserahkan) & perkiraan stok cukup untuk berapa hari
      $row['nilai_keluar'] = (float) ($k['nilai_periode'] ?? 0);
      $hariPeriode = (new DateTime($fromDate))->diff(new DateTime($toDate))->days + 1;
      $row['hari_stok'] = $stokKeluar > 0 && $stokAkhir > 0 ? round($stokAkhir / ($stokKeluar / $hariPeriode), 1) : null;
      $row['status_stock'] = $statusStock;
      $row['status_code'] = $statusCode;
      $rows[] = $row;

      $ringkasan['total_item']++;
      $ringkasan[$statusCode]++;
      $ringkasan['stok_masuk'] += $stokMasuk;
      $ringkasan['stok_keluar'] += $stokKeluar;
      $ringkasan['nilai_stok'] += $nilaiStok;
      $ringkasan['nilai_keluar'] += $row['nilai_keluar'];
   }

   return ['data' => $rows, 'ringkasan' => $ringkasan];
}

/** Validasi tanggal Y-m-d; kembalikan pesan error atau null. */
function farmasiStokValidasiPeriode(string $fromDate, string $toDate): ?string
{
   foreach ([$fromDate, $toDate] as $tgl) {
      $d = DateTime::createFromFormat('Y-m-d', $tgl);
      if (!$d || $d->format('Y-m-d') !== $tgl) {
         return 'Format tanggal tidak valid';
      }
   }
   if ($fromDate > $toDate) {
      return 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir';
   }
   if ((new DateTime($fromDate))->diff(new DateTime($toDate))->days + 1 > 366) {
      return 'Rentang tanggal maksimal 366 hari';
   }
   return null;
}

/** Pembungkus array agar loop lama `while ($row = $result->fetch_assoc())` tetap jalan. */
class FarmasiStokResult
{
   private int $i = 0;

   public function __construct(private array $rows)
   {
   }

   public function fetch_assoc(): ?array
   {
      return $this->rows[$this->i++] ?? null;
   }
}

// ============================================================
// FILTER, URUTAN & TAMPILAN BERSAMA (layar, Excel, Print)
// Harus sama dengan logika di module/admin/rpt_farmasi.php
// ============================================================

/** Isian yang hanya tanda baca (".", "'", "-") dianggap kosong. */
function farmasiStokIsi($v): string
{
   $v = trim((string) ($v ?? ''));
   return preg_match('/[\p{L}\p{N}]/u', $v) ? $v : '';
}

/** Baris kedua kolom Nama Obat: nama dagang • kode. */
function farmasiStokSubNama(array $r): string
{
   return implode(' • ', array_filter([farmasiStokIsi($r['pharmacy_name_trade'] ?? ''), farmasiStokIsi($r['pharmacy_code'] ?? '')]));
}

const FARMASI_STOK_STATUS = ['Habis', 'Di Bawah Minimum', 'Di Atas Maksimum', 'Normal'];
const FARMASI_STOK_SORT = [
   'nama' => 'pharmacy_name_generic', 'kategori' => 'pharmacy_category', 'satuan' => 'pharmacy_unit',
   'stok_awal' => 'stok_awal', 'masuk' => 'stok_masuk', 'keluar' => 'stok_keluar',
   'stok_akhir' => 'stok_akhir', 'nilai' => 'nilai_stok', 'status' => 'status_stock',
   'nilai_keluar' => 'nilai_keluar', 'hari_stok' => 'hari_stok',
];

/** Ambil filter tampilan dari query string (sama dengan pilihan di layar). */
function farmasiStokParamFilter(array $get): array
{
   $status = (string) ($get['status'] ?? '');
   $sort = (string) ($get['sort'] ?? 'nama');
   return [
      'status' => in_array($status, FARMASI_STOK_STATUS, true) ? $status : '',
      'kategori' => mb_substr(trim((string) ($get['kategori'] ?? '')), 0, 100),
      'aktif' => !empty($get['aktif']) && $get['aktif'] !== '0',
      'q' => mb_substr(trim((string) ($get['q'] ?? '')), 0, 100),
      'sort' => isset(FARMASI_STOK_SORT[$sort]) ? $sort : 'nama',
      'dir' => strtolower((string) ($get['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc',
   ];
}

function farmasiStokFilter(array $rows, array $f): array
{
   $q = mb_strtolower($f['q']);
   $rows = array_values(array_filter($rows, function ($r) use ($f, $q) {
      if ($f['status'] !== '' && $r['status_stock'] !== $f['status']) return false;
      if ($f['kategori'] !== '' && (farmasiStokIsi($r['pharmacy_category']) ?: '-') !== $f['kategori']) return false;
      if ($f['aktif'] && !((float) $r['stok_masuk'] || (float) $r['stok_keluar'])) return false;
      if ($q !== '') {
         $teks = mb_strtolower(implode(' ', [
            $r['pharmacy_name_generic'], farmasiStokSubNama($r), farmasiStokIsi($r['pharmacy_category']),
            farmasiStokIsi($r['pharmacy_unit']), $r['status_stock'],
         ]));
         foreach (preg_split('/\s+/', $q) as $kata) {
            if ($kata !== '' && mb_strpos($teks, $kata) === false) return false;
         }
      }
      return true;
   }));

   $kunci = FARMASI_STOK_SORT[$f['sort']];
   $arah = $f['dir'] === 'desc' ? -1 : 1;
   usort($rows, function ($a, $b) use ($kunci, $arah) {
      $x = $a[$kunci] ?? '';
      $y = $b[$kunci] ?? '';
      $c = is_numeric($x) && is_numeric($y) ? ($x <=> $y) : strcasecmp((string) $x, (string) $y);
      return $c * $arah ?: strcasecmp((string) $a['pharmacy_name_generic'], (string) $b['pharmacy_name_generic']);
   });
   return $rows;
}

/** Ringkasan untuk baris hasil filter (dipakai kartu ringkasan & baris total). */
function farmasiStokRingkasan(array $rows): array
{
   $s = ['total_item' => count($rows), 'habis' => 0, 'minimum' => 0, 'maximum' => 0, 'normal' => 0,
      'stok_masuk' => 0.0, 'stok_keluar' => 0.0, 'nilai_stok' => 0.0, 'nilai_keluar' => 0.0];
   foreach ($rows as $r) {
      $s['nilai_keluar'] += (float) ($r['nilai_keluar'] ?? 0);
      $s[$r['status_code']]++;
      $s['stok_masuk'] += (float) $r['stok_masuk'];
      $s['stok_keluar'] += (float) $r['stok_keluar'];
      $s['nilai_stok'] += (float) $r['nilai_stok'];
   }
   return $s;
}

/** Teks keterangan filter aktif, untuk kop Excel/Print. */
function farmasiStokLabelFilter(array $f): string
{
   $l = [];
   if ($f['status'] !== '') $l[] = 'Status: ' . $f['status'];
   if ($f['kategori'] !== '') $l[] = 'Kategori: ' . $f['kategori'];
   if ($f['aktif']) $l[] = 'Hanya yang ada transaksi';
   if ($f['q'] !== '') $l[] = 'Pencarian: "' . $f['q'] . '"';
   return $l ? implode(' • ', $l) : 'Semua obat';
}

/** Angka format Indonesia, desimal hanya bila perlu (sama dengan layar). */
function farmasiStokAngka($v): string
{
   // maksimal 2 desimal tanpa nol di belakang (1,5 bukan 1,50) - sama dengan toLocaleString di layar
   $s = number_format(round((float) $v, 2), 2, ',', '.');
   return rtrim(rtrim($s, '0'), ',');
}

function farmasiStokRupiah($v): string
{
   return 'Rp ' . number_format(round((float) $v), 0, ',', '.');
}


/**
 * Analisis untuk dashboard laporan farmasi (rpt_farmasi), Excel (sheet Analisis) & Print.
 * $rows = hasil farmasiStokReport()['data'] (sebelum filter tampilan).
 */
function farmasiStokDashboard(mysqli $koneksi, string $idCustomer, string $fromDate, string $toDate, array $rows): array
{
   $hari = (new DateTime($fromDate))->diff(new DateTime($toDate))->days + 1;
   $perHari = $hari <= 92;
   $grp = $perHari ? 'DATE(d.created_at)' : "DATE_FORMAT(d.created_at, '%Y-%m')";

   // tren keluar (1 query agregat; tabel detail besar tanpa index id_pharmacy, jadi disaring lewat permintaan klinik)
   $tren = [];
   if ($perHari) {
      for ($d = new DateTime($fromDate); $d->format('Y-m-d') <= $toDate; $d->modify('+1 day')) $tren[$d->format('Y-m-d')] = ['keluar' => 0, 'nilai' => 0, 'resep' => 0, 'masuk' => 0];
   }
   $stmt = $koneksi->prepare("SELECT $grp t, SUM(d.qty) q, SUM(d.qty * COALESCE(d.harga, 0)) n, COUNT(DISTINCT d.id_permintaan_farmasi) r
      FROM permintaan_pharmacy_details d
      INNER JOIN permintaan_pharmacy pp ON pp.id_permintaan_farmasi = d.id_permintaan_farmasi
      WHERE pp.id_customer = ? AND d.created_at BETWEEN ? AND ?
      GROUP BY t");
   $a = $fromDate . ' 00:00:00';
   $z = $toDate . ' 23:59:59';
   $stmt->bind_param('sss', $idCustomer, $a, $z);
   $stmt->execute();
   $resep = 0;
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
      $tren[$r['t']] ??= ['keluar' => 0, 'nilai' => 0, 'resep' => 0, 'masuk' => 0];
      $tren[$r['t']]['keluar'] = (float) $r['q'];
      $tren[$r['t']]['nilai'] = (float) $r['n'];
      $tren[$r['t']]['resep'] = (int) $r['r'];
      $resep += (int) $r['r'];
   }
   $stmt->close();
   $grpM = $perHari ? 'tanggal' : "DATE_FORMAT(tanggal, '%Y-%m')";
   $stmt = $koneksi->prepare("SELECT $grpM t, SUM(jumlah) q FROM pharmacy_stock_penerimaan WHERE id_customer = ? AND tanggal BETWEEN ? AND ? GROUP BY t");
   $stmt->bind_param('sss', $idCustomer, $fromDate, $toDate);
   $stmt->execute();
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
      $tren[$r['t']] ??= ['keluar' => 0, 'nilai' => 0, 'resep' => 0, 'masuk' => 0];
      $tren[$r['t']]['masuk'] = (float) $r['q'];
   }
   $stmt->close();
   ksort($tren);

   // kedaluwarsa: batch penerimaan dengan ED lewat / ≤ 90 hari, hanya obat yang masih ada stoknya
   $stokIni = [];
   $namaObat = [];
   foreach ($rows as $r) {
      $stokIni[(string) $r['id_pharmacy']] = (float) $r['stok_saat_ini'];
      $namaObat[(string) $r['id_pharmacy']] = $r['pharmacy_name_generic'];
   }
   $ed = [];
   $stmt = $koneksi->prepare("SELECT id_pharmacy, batch, expired, SUM(jumlah) jumlah FROM pharmacy_stock_penerimaan
      WHERE id_customer = ? AND expired IS NOT NULL AND expired <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
      GROUP BY id_pharmacy, batch, expired ORDER BY expired ASC");
   $stmt->bind_param('s', $idCustomer);
   $stmt->execute();
   $hariIni = new DateTime(date('Y-m-d'));
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
      $id = (string) $r['id_pharmacy'];
      if (($stokIni[$id] ?? 0) <= 0) continue;
      $sisa = (int) $hariIni->diff(new DateTime($r['expired']))->format('%r%a');
      $ed[] = ['obat' => $namaObat[$id] ?? ('#' . $id), 'batch' => (string) $r['batch'], 'expired' => $r['expired'], 'sisa_hari' => $sisa,
         'diterima' => (float) $r['jumlah'], 'stok_saat_ini' => $stokIni[$id]];
   }
   $stmt->close();

   // analisis per obat
   $keluar = array_values(array_filter($rows, fn($r) => (float) $r['stok_keluar'] > 0));
   usort($keluar, fn($x, $y) => (float) $y['stok_keluar'] <=> (float) $x['stok_keluar']);
   $nilai = $keluar;
   usort($nilai, fn($x, $y) => (float) $y['nilai_keluar'] <=> (float) $x['nilai_keluar']);
   $ringkas = fn($r) => ['id' => (int) $r['id_pharmacy'], 'nama' => $r['pharmacy_name_generic'], 'satuan' => farmasiStokIsi($r['pharmacy_unit']) ?: '-',
      'keluar' => (float) $r['stok_keluar'], 'nilai' => (float) $r['nilai_keluar'], 'stok_akhir' => (float) $r['stok_akhir'], 'hari_stok' => $r['hari_stok']];
   $mati = array_values(array_filter($rows, fn($r) => (float) $r['stok_akhir'] > 0 && (float) $r['stok_keluar'] <= 0));
   usort($mati, fn($x, $y) => (float) $y['nilai_stok'] <=> (float) $x['nilai_stok']);
   $kritis = array_values(array_filter($rows, fn($r) => $r['hari_stok'] !== null && $r['hari_stok'] < 7));
   usort($kritis, fn($x, $y) => $x['hari_stok'] <=> $y['hari_stok']);
   $kat = [];
   foreach ($rows as $r) {
      if ((float) $r['nilai_keluar'] <= 0) continue;
      $k = farmasiStokIsi($r['pharmacy_category']) ?: 'Tanpa kategori';
      $kat[$k] = ($kat[$k] ?? 0) + (float) $r['nilai_keluar'];
   }
   arsort($kat);
   $negatif = count(array_filter($rows, fn($r) => (float) $r['stok_akhir'] < 0));

   return [
      'hari' => $hari,
      'periode' => $perHari ? 'hari' : 'bulan',
      'resep' => $resep,
      'obat_keluar' => count($keluar),
      'tren' => array_map(fn($k, $v) => ['t' => $k] + $v, array_keys($tren), $tren),
      'top_keluar' => array_map($ringkas, array_slice($keluar, 0, 10)),
      'top_nilai' => array_map($ringkas, array_slice($nilai, 0, 10)),
      'kategori' => array_map(fn($k, $v) => ['nama' => (string) $k, 'jumlah' => $v], array_keys($kat), $kat),
      'mati' => count($mati),
      'mati_nilai' => array_sum(array_column($mati, 'nilai_stok')),
      'mati_top' => array_map(fn($r) => ['nama' => $r['pharmacy_name_generic'], 'stok_akhir' => (float) $r['stok_akhir'], 'nilai_stok' => (float) $r['nilai_stok']], array_slice($mati, 0, 10)),
      'kritis' => array_map($ringkas, array_slice($kritis, 0, 15)),
      'kritis_jumlah' => count($kritis),
      'negatif' => $negatif,
      'ed' => array_slice($ed, 0, 50),
      'ed_lewat' => count(array_filter($ed, fn($x) => $x['sisa_hari'] < 0)),
      'ed_dekat' => count(array_filter($ed, fn($x) => $x['sisa_hari'] >= 0)),
   ];
}
