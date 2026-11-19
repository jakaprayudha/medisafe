<?php
/**
 * Laporan obat kedaluwarsa (module/admin/pharmacy_expired) — dipakai controller, Excel & cetak.
 *
 * Kenapa tidak cukup melihat ED batch:
 *   batch lama yang ED-nya lewat sering sudah habis terpakai. Jadi sisa stok per batch diperkirakan dengan FIFO:
 *   stok saat ini (rumus sama dengan Laporan Stok Farmasi) dialokasikan ke penerimaan TERBARU lebih dulu,
 *   karena penerimaan lama yang terpakai duluan. Batch dengan perkiraan sisa 0 = "habis terpakai".
 *
 * Perkiraan terbuang:
 *   pemakaian rata-rata 90 hari terakhir per obat; batch dihabiskan berurutan dari ED terdekat.
 *   Unit yang tidak sempat terpakai sebelum ED = perkiraan terbuang (× harga beli = potensi kerugian).
 */
require_once __DIR__ . '/../report/farmasiStokService.php';

const EX_HARI_PAKAI = 90;
const EX_STATUS = [
   'kadaluarsa' => ['Kedaluwarsa', 0],
   'kritis' => ['≤ 30 hari', 1],
   'waspada' => ['31–90 hari', 2],
   'perhatian' => ['91–180 hari', 3],
   'aman' => ['> 180 hari', 4],
   'tanpa_ed' => ['Tanpa ED', 5],
];

function exStatus(?int $sisaHari): string
{
   if ($sisaHari === null) return 'tanpa_ed';
   if ($sisaHari < 0) return 'kadaluarsa';
   if ($sisaHari <= 30) return 'kritis';
   if ($sisaHari <= 90) return 'waspada';
   if ($sisaHari <= 180) return 'perhatian';
   return 'aman';
}

function exTanggal($v): ?string
{
   $v = trim((string) $v);
   if ($v === '' || str_starts_with($v, '0000')) return null;
   $d = DateTime::createFromFormat('!Y-m-d', substr($v, 0, 10));
   return $d && (int) $d->format('Y') >= 2000 ? $d->format('Y-m-d') : null;
}

function exReport(mysqli $k, string $idc): array
{
   $hariIni = date('Y-m-d');
   $awalPakai = date('Y-m-d', strtotime('-' . (EX_HARI_PAKAI - 1) . ' days'));
   // stok saat ini + keluar 90 hari terakhir per obat (sumber yang sama dengan Laporan Stok Farmasi)
   $stok = farmasiStokReport($k, $idc, $awalPakai, $hariIni)['data'];
   $obat = [];
   foreach ($stok as $r) $obat[(string) $r['id_pharmacy']] = $r;

   $st = $k->prepare("SELECT id_penerimaan, id_pharmacy, tanggal, nomor_faktur, supplier, batch, expired,
                             COALESCE(jumlah, 0) jumlah, COALESCE(harga_beli, 0) harga_beli
                      FROM pharmacy_stock_penerimaan WHERE id_customer = ?
                      ORDER BY tanggal DESC, id_penerimaan DESC");
   $st->bind_param('s', $idc);
   $st->execute();
   $perObat = [];
   foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $b) {
      if (isset($obat[(string) $b['id_pharmacy']])) $perObat[(string) $b['id_pharmacy']][] = $b;
   }
   $st->close();

   $today = new DateTime($hariIni);
   $batches = [];
   $items = [];
   foreach ($obat as $id => $o) {
      $stokIni = max(0.0, (float) $o['stok_saat_ini']);
      $pakaiHari = (float) $o['stok_keluar'] / EX_HARI_PAKAI;
      $hargaMaster = (float) $o['pharmacy_price_buy'];
      $sisaAlok = $stokIni;
      $daftar = [];
      // FIFO: stok yang tersisa ada di penerimaan terbaru
      foreach ($perObat[$id] ?? [] as $b) {
         $sisa = min((float) $b['jumlah'], $sisaAlok);
         $sisaAlok -= $sisa;
         $ed = exTanggal($b['expired']);
         $sisaHari = $ed ? (int) $today->diff(new DateTime($ed))->format('%r%a') : null;
         $harga = (float) $b['harga_beli'] > 0 ? (float) $b['harga_beli'] : $hargaMaster;
         $daftar[] = [
            'id_penerimaan' => (int) $b['id_penerimaan'],
            'id_pharmacy' => (int) $id,
            'obat' => $o['pharmacy_name_generic'],
            'dagang' => farmasiStokIsi($o['pharmacy_name_trade']),
            'kategori' => farmasiStokIsi($o['pharmacy_category']) ?: '-',
            'satuan' => farmasiStokIsi($o['pharmacy_unit']) ?: '-',
            'batch' => trim((string) $b['batch']) ?: '-',
            'supplier' => trim((string) $b['supplier']) ?: '-',
            'faktur' => trim((string) $b['nomor_faktur']) ?: '-',
            'diterima_tgl' => $b['tanggal'],
            'expired' => $ed,
            'sisa_hari' => $sisaHari,
            'diterima' => (float) $b['jumlah'],
            'sisa' => $sisa,
            'harga' => $harga,
            'nilai_sisa' => $sisa * $harga,
            'status' => exStatus($sisaHari),
            'terbuang' => 0.0,
            'nilai_terbuang' => 0.0,
         ];
      }
      // perkiraan terbuang: habiskan batch bersisa dari ED terdekat dengan laju pakai rata-rata
      $aktif = array_filter(array_keys($daftar), fn($i) => $daftar[$i]['sisa'] > 0 && $daftar[$i]['sisa_hari'] !== null);
      usort($aktif, fn($x, $y) => $daftar[$x]['sisa_hari'] <=> $daftar[$y]['sisa_hari']);
      $hariTerpakai = 0.0;
      foreach ($aktif as $i) {
         $b = &$daftar[$i];
         $hariBisa = max(0, $b['sisa_hari']) - $hariTerpakai;
         $terpakai = $pakaiHari > 0 ? min($b['sisa'], max(0, $hariBisa) * $pakaiHari) : 0;
         $b['terbuang'] = round($b['sisa'] - $terpakai, 2);
         $b['nilai_terbuang'] = $b['terbuang'] * $b['harga'];
         if ($pakaiHari > 0) $hariTerpakai += $terpakai / $pakaiHari;
         unset($b);
      }
      $berstok = array_values(array_filter($daftar, fn($b) => $b['sisa'] > 0));
      usort($berstok, fn($x, $y) => [EX_STATUS[$x['status']][1], $x['sisa_hari'] ?? PHP_INT_MAX] <=> [EX_STATUS[$y['status']][1], $y['sisa_hari'] ?? PHP_INT_MAX]);
      $tanpaBatch = max(0, $sisaAlok); // stok yang tidak tercakup data penerimaan (stok awal master)
      $terdekat = $berstok[0] ?? null;
      $status = $terdekat ? $terdekat['status'] : ($stokIni > 0 ? 'tanpa_ed' : null);
      if ($status !== null) {
         $items[] = [
            'id_pharmacy' => (int) $id,
            'obat' => $o['pharmacy_name_generic'],
            'dagang' => farmasiStokIsi($o['pharmacy_name_trade']),
            'kategori' => farmasiStokIsi($o['pharmacy_category']) ?: '-',
            'satuan' => farmasiStokIsi($o['pharmacy_unit']) ?: '-',
            'stok' => $stokIni,
            'pakai_hari' => round($pakaiHari, 2),
            'hari_habis' => $pakaiHari > 0 ? (int) floor($stokIni / $pakaiHari) : null,
            'ed_terdekat' => $terdekat['expired'] ?? null,
            'sisa_hari' => $terdekat['sisa_hari'] ?? null,
            'batch_berstok' => count($berstok),
            'tanpa_batch' => $tanpaBatch,
            'terbuang' => array_sum(array_column($daftar, 'terbuang')),
            'nilai_terbuang' => array_sum(array_column($daftar, 'nilai_terbuang')),
            'nilai_sisa' => array_sum(array_column($berstok, 'nilai_sisa')) + $tanpaBatch * $hargaMaster,
            'status' => $status,
         ];
      }
      foreach ($daftar as $b) $batches[] = $b;
   }
   usort($batches, fn($x, $y) => [$x['sisa'] > 0 ? 0 : 1, EX_STATUS[$x['status']][1], $x['sisa_hari'] ?? PHP_INT_MAX, $x['obat']]
      <=> [$y['sisa'] > 0 ? 0 : 1, EX_STATUS[$y['status']][1], $y['sisa_hari'] ?? PHP_INT_MAX, $y['obat']]);
   usort($items, fn($x, $y) => [EX_STATUS[$x['status']][1], $x['sisa_hari'] ?? PHP_INT_MAX, $x['obat']] <=> [EX_STATUS[$y['status']][1], $y['sisa_hari'] ?? PHP_INT_MAX, $y['obat']]);

   return ['batches' => $batches, 'items' => $items, 'ringkasan' => exRingkasan($batches, $items, count($obat))];
}

function exRingkasan(array $batches, array $items, int $totalObat): array
{
   $st = array_fill_keys(array_keys(EX_STATUS), ['batch' => 0, 'qty' => 0.0, 'nilai' => 0.0]);
   $bulan = [];
   $supplier = [];
   $namaSupplier = [];
   $terbuang = ['qty' => 0.0, 'nilai' => 0.0, 'batch' => 0];
   $habisTerpakai = 0;
   $tanpaEdBatch = 0;
   foreach ($batches as $b) {
      if ($b['sisa'] <= 0) {
         $habisTerpakai++;
         continue;
      }
      $st[$b['status']]['batch']++;
      $st[$b['status']]['qty'] += $b['sisa'];
      $st[$b['status']]['nilai'] += $b['nilai_sisa'];
      if ($b['terbuang'] > 0) {
         $terbuang['qty'] += $b['terbuang'];
         $terbuang['nilai'] += $b['nilai_terbuang'];
         $terbuang['batch']++;
      }
      if ($b['expired'] === null) $tanpaEdBatch++;
      if ($b['sisa_hari'] !== null && $b['sisa_hari'] <= 365) {
         $kunci = $b['sisa_hari'] < 0 ? 'lewat' : substr($b['expired'], 0, 7);
         $bulan[$kunci] ??= ['qty' => 0.0, 'nilai' => 0.0, 'batch' => 0];
         $bulan[$kunci]['qty'] += $b['sisa'];
         $bulan[$kunci]['nilai'] += $b['nilai_sisa'];
         $bulan[$kunci]['batch']++;
      }
      if (in_array($b['status'], ['kadaluarsa', 'kritis', 'waspada'], true)) {
         // nama supplier diketik bebas ("mitra sehat" / "Mitra sehat") -> digabung tanpa beda huruf
         $ks = mb_strtolower(preg_replace('/\s+/', ' ', trim($b['supplier'])));
         $namaSupplier[$ks] ??= $b['supplier'];
         $supplier[$ks] = ($supplier[$ks] ?? 0) + $b['nilai_sisa'];
      }
   }
   // 12 bulan ke depan selalu tampil
   $tl = ['lewat' => $bulan['lewat'] ?? ['qty' => 0, 'nilai' => 0, 'batch' => 0]];
   for ($i = 0; $i < 12; $i++) {
      $k = date('Y-m', strtotime(date('Y-m-01') . " +$i month"));
      $tl[$k] = $bulan[$k] ?? ['qty' => 0, 'nilai' => 0, 'batch' => 0];
   }
   arsort($supplier);
   $obatSt = array_fill_keys(array_keys(EX_STATUS), 0);
   foreach ($items as $it) $obatSt[$it['status']]++;
   $risiko = array_values(array_filter($items, fn($x) => $x['nilai_terbuang'] > 0));
   usort($risiko, fn($x, $y) => $y['nilai_terbuang'] <=> $x['nilai_terbuang']);
   return [
      'total_obat' => $totalObat,
      'obat_berstok' => count($items),
      'obat_status' => $obatSt,
      'status' => $st,
      'terbuang' => $terbuang,
      'batch_berstok' => array_sum(array_column($st, 'batch')),
      'batch_habis' => $habisTerpakai,
      'tanpa_ed_batch' => $tanpaEdBatch,
      'obat_tanpa_batch' => count(array_filter($items, fn($x) => $x['batch_berstok'] === 0)),
      'timeline' => array_map(fn($k, $v) => ['bulan' => $k] + $v, array_keys($tl), $tl),
      'supplier' => array_map(fn($k, $v) => ['nama' => (string) $namaSupplier[$k], 'jumlah' => $v], array_keys(array_slice($supplier, 0, 8, true)), array_slice($supplier, 0, 8, true)),
      'top_terbuang' => array_slice(array_map(fn($x) => ['obat' => $x['obat'], 'satuan' => $x['satuan'], 'terbuang' => $x['terbuang'], 'nilai' => $x['nilai_terbuang'], 'ed' => $x['ed_terdekat']], $risiko), 0, 10),
   ];
}
