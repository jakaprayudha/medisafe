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

function farmasiStokReport(mysqli $koneksi, string $idCustomer, string $fromDate, string $toDate): array
{
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
        AND p.pharmacy_status = 1
      ORDER BY p.pharmacy_name_generic ASC");
   if (!$stmt) {
      throw new RuntimeException('Prepare query obat gagal: ' . $koneksi->error);
   }
   $stmt->bind_param('ss', $idCustomer, $idCustomer);
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
      WHERE id_customer = ?
      GROUP BY id_pharmacy");
   if (!$stmt) {
      throw new RuntimeException('Prepare query stok masuk gagal: ' . $koneksi->error);
   }
   $stmt->bind_param('ssss', $fromDate, $fromDate, $toDate, $idCustomer);
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
         SUM(CASE WHEN d.created_at BETWEEN ? AND ? THEN d.qty ELSE 0 END) AS periode
      FROM permintaan_pharmacy_details d
      INNER JOIN permintaan_pharmacy pp ON pp.id_permintaan_farmasi = d.id_permintaan_farmasi
      WHERE pp.id_customer = ?
      GROUP BY d.id_pharmacy");
   if (!$stmt) {
      throw new RuntimeException('Prepare query stok keluar gagal: ' . $koneksi->error);
   }
   $stmt->bind_param('ssss', $awalPeriode, $awalPeriode, $akhirPeriode, $idCustomer);
   $stmt->execute();
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
      $keluar[(string) $r['id_pharmacy']] = $r;
   }
   $stmt->close();

   // ================== HITUNG ==================
   $rows = [];
   $ringkasan = [
      'total_item' => 0, 'habis' => 0, 'minimum' => 0, 'maximum' => 0, 'normal' => 0,
      'stok_masuk' => 0.0, 'stok_keluar' => 0.0, 'nilai_stok' => 0.0,
   ];

   foreach ($obat as $row) {
      $id = (string) $row['id_pharmacy'];
      $m = $masuk[$id] ?? ['semua' => 0, 'sebelum' => 0, 'periode' => 0];
      $k = $keluar[$id] ?? ['semua' => 0, 'sebelum' => 0, 'periode' => 0];

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
      $row['status_stock'] = $statusStock;
      $row['status_code'] = $statusCode;
      $rows[] = $row;

      $ringkasan['total_item']++;
      $ringkasan[$statusCode]++;
      $ringkasan['stok_masuk'] += $stokMasuk;
      $ringkasan['stok_keluar'] += $stokKeluar;
      $ringkasan['nilai_stok'] += $nilaiStok;
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
      'stok_masuk' => 0.0, 'stok_keluar' => 0.0, 'nilai_stok' => 0.0];
   foreach ($rows as $r) {
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
