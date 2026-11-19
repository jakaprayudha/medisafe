<?php
/**
 * Laporan tindakan / prosedur.
 * GET fromDate, toDate[, doctor, poli, provider, kategori]
 *   format=json (default) -> { kolom, data: [baris tindakan], ringkasan (per tindakan, per dokter, …), opsi, filter }
 *   format=print          -> halaman cetak
 * Export Excel: module/report/export_prosedur.php
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/prosedurService.php';

$format = ($_GET['format'] ?? 'json') === 'print' ? 'print' : 'json';
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$gagal = function (string $m, int $c) use ($format) {
   http_response_code($c);
   if ($format === 'json') {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['status' => 'error', 'message' => $m, 'data' => []]);
   } else {
      header('Content-Type: text/plain; charset=utf-8');
      echo $m;
   }
   exit;
};
if (!$idc) $gagal('Session tidak ditemukan', 401);
// fee per dokter bersifat sensitif: hanya admin
if (!in_array($_SESSION['roles'] ?? '', ['admin', 'superadmin', 'administrator'], true)) $gagal('Akses laporan tidak diizinkan.', 403);

try {
   $p = prParams($_GET);
   $rows = prRows($koneksi, $idc, $p);
   $r = prRingkasan($rows, $p, prKunjungan($koneksi, $idc, $p));
} catch (InvalidArgumentException $e) {
   $gagal($e->getMessage(), 422);
} catch (Throwable $e) {
   error_log('[prosedurController] ' . $e->getMessage());
   $gagal('Gagal memuat laporan tindakan.', 500);
}

if ($format === 'json') {
   header('Content-Type: application/json; charset=utf-8');
   header('Cache-Control: no-store');
   $kolom = ['tanggal', 'jam', 'nama', 'rm', 'dokter', 'poli', 'bayar', 'item', 'kategori', 'qty', 'harga', 'diskon', 'total', 'fee_dokter', 'fee_perawat', 'fee_admin', 'tarif'];
   $opsi = null;
   if (empty($_GET['tanpa_opsi'])) {
      $o = apOpsi($koneksi, $idc, ['from' => $p['from'], 'to' => $p['to']]);
      $opsi = $o + ['kategori' => PR_KATEGORI];
   }
   echo json_encode([
      'status' => 'success',
      'kolom' => $kolom,
      'data' => array_map(fn($x) => array_map(fn($c) => $x[$c], $kolom), $rows),
      'ringkasan' => $r,
      'opsi' => $opsi,
      'filter' => $p + ['label' => prLabelFilter($p)],
   ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}

/* ---------------- cetak ---------------- */
$klinik = apKlinik($koneksi, $idc);
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$n = fn($v) => number_format((float) $v, 0, ',', '.');
$periode = apTglIndo($p['from']) . ($p['from'] !== $p['to'] ? ' s/d ' . apTglIndo($p['to']) : '');
$isi = in_array($_GET['isi'] ?? '', ['ringkasan', 'detail'], true) ? $_GET['isi'] : 'rekap';
$maxItem = max([1, ...array_column(array_slice($r['items'], 0, 10), 'qty')]);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="utf-8">
   <title>Laporan Tindakan <?= $e($periode) ?></title>
   <style>
      @page { size: A4 landscape; margin: 10mm; }
      * { box-sizing: border-box; }
      body { font-family: Arial, Helvetica, sans-serif; font-size: 10.5px; color: #1f2937; margin: 0; }
      .kop { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #2563eb; padding-bottom: 6px; margin-bottom: 10px; }
      .kop h1 { font-size: 16px; margin: 0; } .sub { color: #64748b; }
      .kpi { display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px; margin-bottom: 10px; }
      .kpi div { border: 1px solid #dbe3ee; border-radius: 6px; padding: 6px 8px; } .kpi b { display: block; font-size: 14px; } .kpi span { color: #64748b; font-size: 9.5px; }
      h3 { font-size: 10.5px; text-transform: uppercase; color: #475569; margin: 12px 0 4px; letter-spacing: .3px; }
      table.data { width: 100%; border-collapse: collapse; } table.data th, table.data td { border: 1px solid #cbd5e1; padding: 3px 5px; text-align: left; }
      table.data th { background: #eef2ff; -webkit-print-color-adjust: exact; print-color-adjust: exact; } table.data thead { display: table-header-group; }
      table.data tr { break-inside: avoid; } .r { text-align: right; } .nw { white-space: nowrap; } tfoot td { font-weight: bold; background: #f8fafc; }
      .bar { height: 8px; border-radius: 2px; background: #2563eb; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .muted { color: #94a3b8; }
      .tools { position: fixed; top: 10px; right: 10px; display: flex; gap: 6px; }
      .tools a, .tools button { font: inherit; padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; text-decoration: none; color: #1f2937; }
      .tools a.on { background: #eef2ff; border-color: #2563eb; } .tools .pri { background: #2563eb; border-color: #2563eb; color: #fff; }
      @media screen { body { padding: 60px 16px 16px; max-width: 1250px; margin: 0 auto; } }
      @media print { .tools { display: none; } }
   </style>
</head>
<body>
   <div class="tools">
      <?php foreach (['ringkasan' => 'Ringkasan', 'rekap' => 'Rekap per tindakan & dokter', 'detail' => '+ Detail transaksi'] as $k => $lbl): $q = $_GET; $q['isi'] = $k; ?>
         <a href="?<?= $e(http_build_query($q)) ?>" class="<?= $isi === $k ? 'on' : '' ?>"><?= $lbl ?></a>
      <?php endforeach; ?>
      <button class="pri" onclick="window.print()">Cetak</button>
   </div>
   <div class="kop">
      <div><h1>Laporan Tindakan &amp; Prosedur</h1><div class="sub"><?= $e($klinik['nama']) ?></div></div>
      <div class="sub" style="text-align:right">Periode: <b><?= $e($periode) ?></b><br><?= $e(prLabelFilter($p)) ?> · Dicetak <?= date('d-m-Y H:i') ?></div>
   </div>
   <div class="kpi">
      <div><b><?= prRp($r['total']) ?></b><span>Pendapatan (setelah diskon)</span></div>
      <div><b><?= $n($r['qty']) ?></b><span><?= $n($r['jenis']) ?> jenis tindakan</span></div>
      <div><b><?= $n($r['kunjungan_tindakan']) ?></b><span>Kunjungan dengan tindakan (<?= str_replace('.', ',', (string) $r['pct_kunjungan']) ?>%)</span></div>
      <div><b><?= prRp($r['fee_dokter']) ?></b><span>Fee dokter</span></div>
      <div><b><?= prRp($r['fee_perawat'] + $r['fee_admin']) ?></b><span>Fee perawat + admin</span></div>
      <div><b><?= prRp($r['diskon']) ?></b><span>Total diskon</span></div>
   </div>

   <h3>10 tindakan terbanyak</h3>
   <table class="data">
      <thead><tr><th>#</th><th>Tindakan</th><th>Kategori</th><th style="width:28%"></th><th class="r">Jumlah</th><th class="r">Kunjungan</th><th class="r">Pendapatan</th><th class="r">Fee dokter</th></tr></thead>
      <tbody>
         <?php foreach (array_slice($r['items'], 0, 10) as $it): ?>
            <tr><td><?= $it['rank'] ?></td><td><?= $e($it['item']) ?></td><td><?= $e($it['kategori']) ?></td><td><div class="bar" style="width:<?= round($it['qty'] / $maxItem * 100) ?>%"></div></td>
               <td class="r"><?= $n($it['qty']) ?></td><td class="r"><?= $n($it['kunjungan']) ?></td><td class="r nw"><?= prRp($it['total']) ?></td><td class="r nw"><?= prRp($it['fee_dokter']) ?></td></tr>
         <?php endforeach; ?>
         <?php if (!$r['items']): ?><tr><td colspan="8" class="muted" style="text-align:center">Tidak ada tindakan pada periode ini.</td></tr><?php endif; ?>
      </tbody>
   </table>

   <?php if ($isi !== 'ringkasan'): ?>
      <h3>Rekap per dokter</h3>
      <table class="data">
         <thead><tr><th>Dokter</th><th class="r">Kunjungan</th><th class="r">Jumlah tindakan</th><th class="r">Jenis</th><th class="r">Pendapatan</th><th class="r">Fee dokter</th><th class="r">Fee perawat</th><th class="r">Fee admin</th></tr></thead>
         <tbody>
            <?php foreach ($r['dokter'] as $d): ?>
               <tr><td><?= $e($d['dokter']) ?></td><td class="r"><?= $n($d['kunjungan']) ?></td><td class="r"><?= $n($d['qty']) ?></td><td class="r"><?= $n($d['jenis']) ?></td>
                  <td class="r nw"><?= prRp($d['total']) ?></td><td class="r nw"><?= prRp($d['fee_dokter']) ?></td><td class="r nw"><?= prRp($d['fee_perawat']) ?></td><td class="r nw"><?= prRp($d['fee_admin']) ?></td></tr>
            <?php endforeach; ?>
         </tbody>
         <tfoot><tr><td>Total</td><td class="r"><?= $n($r['kunjungan_tindakan']) ?></td><td class="r"><?= $n($r['qty']) ?></td><td class="r"><?= $n($r['jenis']) ?></td><td class="r nw"><?= prRp($r['total']) ?></td>
            <td class="r nw"><?= prRp($r['fee_dokter']) ?></td><td class="r nw"><?= prRp($r['fee_perawat']) ?></td><td class="r nw"><?= prRp($r['fee_admin']) ?></td></tr></tfoot>
      </table>

      <h3>Semua tindakan (<?= $n(count($r['items'])) ?> jenis)</h3>
      <table class="data">
         <thead><tr><th>#</th><th>Tindakan</th><th>Kategori</th><th class="r">Jumlah</th><th class="r">Kunjungan</th><th class="r">Rata-rata harga</th><th class="r">Pendapatan</th><th class="r">Fee dokter</th><th class="r">Fee perawat</th><th class="r">Fee admin</th></tr></thead>
         <tbody>
            <?php foreach ($r['items'] as $it): ?>
               <tr><td><?= $it['rank'] ?></td><td><?= $e($it['item']) ?><?= $it['tarif'] ? '' : ' <span class="muted">(tanpa master tarif)</span>' ?></td><td><?= $e($it['kategori']) ?></td>
                  <td class="r"><?= $n($it['qty']) ?></td><td class="r"><?= $n($it['kunjungan']) ?></td><td class="r nw"><?= prRp($it['rata']) ?></td><td class="r nw"><?= prRp($it['total']) ?></td>
                  <td class="r nw"><?= prRp($it['fee_dokter']) ?></td><td class="r nw"><?= prRp($it['fee_perawat']) ?></td><td class="r nw"><?= prRp($it['fee_admin']) ?></td></tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   <?php endif; ?>

   <?php if ($isi === 'detail'): ?>
      <h3>Detail transaksi (<?= $n(count($rows)) ?> baris)</h3>
      <table class="data">
         <thead><tr><th>Tanggal</th><th>Pasien</th><th>Dokter</th><th>Tindakan</th><th class="r">Qty</th><th class="r">Harga</th><th class="r">Diskon</th><th class="r">Total</th><th class="r">Fee dokter</th><th>Jenis bayar</th></tr></thead>
         <tbody>
            <?php foreach ($rows as $x): ?>
               <tr><td class="nw"><?= $e(apTglIndo($x['tanggal']) . ' ' . $x['jam']) ?></td><td><?= $e($x['nama']) ?> <span class="muted"><?= $e($x['rm']) ?></span></td><td><?= $e($x['dokter']) ?></td><td><?= $e($x['item']) ?></td>
                  <td class="r"><?= $n($x['qty']) ?></td><td class="r nw"><?= prRp($x['harga']) ?></td><td class="r nw"><?= $x['diskon'] ? prRp($x['diskon']) : '-' ?></td><td class="r nw"><?= prRp($x['total']) ?></td><td class="r nw"><?= prRp($x['fee_dokter']) ?></td><td><?= $e($x['bayar']) ?></td></tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   <?php endif; ?>
   <p class="muted">Pendapatan = harga × jumlah − diskon. Fee dihitung dari master tarif faskes ini × jumlah; tindakan yang namanya tidak ada di master tarif tidak memiliki fee.</p>
   <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
