<?php
/**
 * Cetak laporan kasir (A4). Filter sama dengan module/admin/rpt_kasir.
 */
require_once '../../database/connect.php';
require_once '../../controller/report/kasirService.php';

$idc = (string) ($_SESSION['id_customer'] ?? '');
$petugas = (string) ($_SESSION['fullname'] ?? '');
session_write_close();
if ($idc === '') {
   http_response_code(403);
   exit('Session tidak ditemukan. Silakan login ulang.');
}
try {
   $p = ksParams($_GET);
} catch (InvalidArgumentException $e) {
   http_response_code(422);
   exit($e->getMessage());
}
$rows = ksRows($koneksi, $idc, $p);
usort($rows, fn($a, $b) => [$a['tgl_bayar'], $a['no_transaksi']] <=> [$b['tgl_bayar'], $b['no_transaksi']]);
$a = ksRingkasan($rows, $p);
$r = $a['ringkasan'];
$belum = ksBelumBayar($koneksi, $idc, $p);
$opsi = ksOpsi($koneksi, $idc);
$klinik = ksKlinik($koneksi, $idc);
$periode = ksTglIndo($p['from']) . ($p['from'] !== $p['to'] ? ' s/d ' . ksTglIndo($p['to']) : '');
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$rp = fn($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
$pct = fn($a, $b) => $b ? number_format($a / $b * 100, 1, ',', '.') . '%' : '0%';
$tgl = fn($d) => date('d/m/Y', strtotime($d));
$logo = $klinik['logo'] !== '' ? '../../uploads/' . rawurlencode($klinik['logo']) : '';
$hariAktif = array_values(array_filter($a['harian'], fn($x) => $x['transaksi'] > 0));
$denganRincian = ($_GET['rincian'] ?? '1') !== '0';
$urlRincian = '?' . http_build_query(array_merge($_GET, ['rincian' => $denganRincian ? '0' : '1']));
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Laporan Kasir <?= $h($periode) ?></title>
   <style>
      * { box-sizing: border-box; }
      body { margin: 0; background: #e5e7eb; color: #111; font: 11px Arial, Helvetica, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .toolbar { position: sticky; top: 0; display: flex; gap: 8px; justify-content: center; padding: 10px; background: #1e293b; z-index: 5; }
      .toolbar button { font: 600 13px Arial, sans-serif; padding: 7px 14px; border-radius: 6px; border: 1px solid #475569; background: #fff; cursor: pointer; }
      .toolbar .utama { background: #2563eb; border-color: #2563eb; color: #fff; }
      .kertas { width: 210mm; margin: 16px auto; background: #fff; padding: 12mm; box-shadow: 0 2px 12px rgba(0, 0, 0, .15); }
      .kepala { display: flex; gap: 12px; align-items: center; border-bottom: 2px solid #111; padding-bottom: 8px; }
      .kepala img { width: 54px; height: 54px; object-fit: contain; }
      .kepala .k { flex: 1; }
      .kepala .k b { font-size: 15px; text-transform: uppercase; display: block; }
      .kepala .j { text-align: right; }
      .kepala .j b { font-size: 16px; display: block; }
      .filter { margin: 6px 0 10px; color: #333; }
      .kpi { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-bottom: 10px; }
      .kpi div { border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 8px; }
      .kpi span { display: block; color: #333; font-size: 10px; }
      .kpi b { font-size: 13px; }
      h3 { font-size: 12px; margin: 12px 0 4px; text-transform: uppercase; }
      table { width: 100%; border-collapse: collapse; }
      th { background: #f1f5f9; text-align: left; font-size: 10px; text-transform: uppercase; padding: 4px 5px; border-top: 1px solid #111; border-bottom: 1px solid #111; }
      td { padding: 3px 5px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
      .num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
      tfoot td, tr.jumlah td { font-weight: 700; border-top: 1px solid #111; border-bottom: 2px solid #111; }
      tr { break-inside: avoid; }
      .dua { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
      .ttd { display: flex; justify-content: flex-end; margin-top: 24px; break-inside: avoid; }
      .ttd div { width: 200px; text-align: center; }
      .ttd .garis { margin-top: 50px; border-top: 1px solid #111; padding-top: 3px; font-weight: 700; }
      .kaki { margin-top: 10px; font-size: 9px; color: #444; }
      @media print {
         body { background: #fff; }
         .toolbar { display: none; }
         .kertas { width: auto; margin: 0; padding: 0; box-shadow: none; }
      }
      @page { size: A4 portrait; margin: 10mm; }
   </style>
</head>

<body>
   <div class="toolbar">
      <button class="utama" onclick="window.print()">🖨️ Cetak</button>
      <button onclick="location.href='<?= $h($urlRincian) ?>'"><?= $denganRincian ? 'Rekap saja (tanpa rincian)' : 'Dengan rincian transaksi' ?></button>
      <button onclick="window.close()">Tutup</button>
   </div>
   <div class="kertas">
      <div class="kepala">
         <?php if ($logo): ?><img src="<?= $h($logo) ?>" alt="Logo"><?php endif ?>
         <div class="k"><b><?= $h($klinik['nama']) ?></b><?= $klinik['alamat'] ? $h($klinik['alamat']) . '<br>' : '' ?><?= $klinik['telp'] ? 'Telp. ' . $h($klinik['telp']) : '' ?></div>
         <div class="j"><b>LAPORAN KASIR</b><?= $h($periode) ?></div>
      </div>
      <div class="filter">Filter: <?= $h(ksLabelFilter($p, $opsi)) ?></div>

      <div class="kpi">
         <div><span>Total diterima</span><b><?= $rp($r['total']) ?></b></div>
         <div><span>Transaksi</span><b><?= number_format($r['transaksi'], 0, ',', '.') ?></b> <span style="display:inline"><?= number_format($r['berbayar'], 0, ',', '.') ?> berbayar</span></div>
         <div><span>Tunai / non-tunai</span><b><?= $rp($r['tunai']) ?></b> <span style="display:inline">/ <?= $rp($r['non_tunai']) ?></span></div>
         <div><span>Belum dibayar</span><b><?= $rp($belum['total']) ?></b> <span style="display:inline"><?= number_format($belum['kunjungan'], 0, ',', '.') ?> kunjungan</span></div>
      </div>

      <div class="dua">
         <div>
            <h3>Per metode bayar</h3>
            <table><thead><tr><th>Metode</th><th class="num">Trx</th><th class="num">Total</th><th class="num">%</th></tr></thead><tbody>
               <?php foreach ($a['metode'] as $g): ?><tr><td><?= $h($g['nama']) ?></td><td class="num"><?= number_format($g['transaksi'], 0, ',', '.') ?></td><td class="num"><?= $rp($g['total']) ?></td><td class="num"><?= $pct($g['total'], $r['total']) ?></td></tr><?php endforeach ?>
            </tbody></table>
         </div>
         <div>
            <h3>Per penjamin</h3>
            <table><thead><tr><th>Penjamin</th><th class="num">Trx</th><th class="num">Total</th><th class="num">%</th></tr></thead><tbody>
               <?php foreach ($a['penjamin'] as $g): ?><tr><td><?= $h($g['nama']) ?></td><td class="num"><?= number_format($g['transaksi'], 0, ',', '.') ?></td><td class="num"><?= $rp($g['total']) ?></td><td class="num"><?= $pct($g['total'], $r['total']) ?></td></tr><?php endforeach ?>
            </tbody></table>
         </div>
      </div>

      <?php if (count($a['harian']) > 1): ?>
         <h3>Rekap harian</h3>
         <table>
            <thead><tr><th>Tanggal</th><th class="num">Transaksi</th><th class="num">Tunai</th><th class="num">Non-tunai</th><th class="num">Total</th></tr></thead>
            <tbody><?php foreach ($hariAktif as $x): ?><tr><td><?= $tgl($x['tanggal']) ?></td><td class="num"><?= number_format($x['transaksi'], 0, ',', '.') ?></td><td class="num"><?= $rp($x['tunai']) ?></td><td class="num"><?= $rp($x['non_tunai']) ?></td><td class="num"><?= $rp($x['total']) ?></td></tr><?php endforeach ?></tbody>
            <tfoot><tr><td>Total</td><td class="num"><?= number_format($r['transaksi'], 0, ',', '.') ?></td><td class="num"><?= $rp($r['tunai']) ?></td><td class="num"><?= $rp($r['non_tunai']) ?></td><td class="num"><?= $rp($r['total']) ?></td></tr></tfoot>
         </table>
      <?php endif ?>

      <?php if ($denganRincian): ?>
      <h3>Rincian transaksi</h3>
      <table>
         <thead><tr><th>No</th><th>Tgl bayar</th><th>No. transaksi</th><th>Pasien</th><th>Penjamin</th><th>Metode</th><th class="num">Total</th></tr></thead>
         <tbody>
            <?php foreach ($rows as $i => $d): ?>
               <tr><td><?= $i + 1 ?></td><td><?= $tgl($d['tgl_bayar']) ?></td><td><?= $h($d['no_transaksi']) ?></td><td><?= $h($d['pasien']) ?> <span style="color:#444">(<?= $h($d['nomor_rm']) ?>)</span></td><td><?= $h($d['penjamin']) ?></td><td><?= $h($d['metode']) ?></td><td class="num"><?= $rp($d['total']) ?></td></tr>
            <?php endforeach ?>
            <?php if (!$rows): ?><tr><td colspan="7" style="text-align:center;padding:10px">Belum ada pembayaran pada periode ini.</td></tr><?php endif ?>
            <tr class="jumlah"><td colspan="6" class="num">Total diterima</td><td class="num"><?= $rp($r['total']) ?></td></tr>
         </tbody>
      </table>
      <?php endif ?>

      <div class="ttd"><div>Kasir<div class="garis"><?= $h($petugas ?: '..............................') ?></div></div></div>
      <div class="kaki">Dicetak <?= date('d/m/Y H:i') ?><?= $petugas ? ' oleh ' . $h($petugas) : '' ?> · Tanggal bayar diambil dari nomor transaksi.</div>
   </div>
   <script>window.addEventListener('load', function() { setTimeout(function() { window.print(); }, 300); });</script>
</body>

</html>
