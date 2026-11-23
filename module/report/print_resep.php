<?php
/**
 * Cetak laporan pelayanan resep (A4). Filter sama dengan module/admin/rpt_resep.
 * ?rincian=0 -> rekap saja (tanpa daftar resep).
 */
require_once '../../database/connect.php';
require_once '../../controller/report/resepService.php';

$idc = (string) ($_SESSION['id_customer'] ?? '');
$petugas = (string) ($_SESSION['fullname'] ?? '');
session_write_close();
if ($idc === '') {
   http_response_code(403);
   exit('Session tidak ditemukan. Silakan login ulang.');
}
try {
   $p = rsParams($_GET);
} catch (InvalidArgumentException $e) {
   http_response_code(422);
   exit($e->getMessage());
}
[$tiket, $obat] = rsData($koneksi, $idc, $p);
$a = rsRingkasan($tiket, $obat, $p);
$r = $a['ringkasan'];
$penjamin = rsPenjamin($koneksi);
$klinik = rsKlinik($koneksi, $idc);
$periode = rsTglIndo($p['from']) . ($p['from'] !== $p['to'] ? ' s/d ' . rsTglIndo($p['to']) : '');
$denganRincian = ($_GET['rincian'] ?? '0') === '1';   // default rekap: daftar resep bisa ribuan baris
$urlRincian = '?' . http_build_query(array_merge($_GET, ['rincian' => $denganRincian ? '0' : '1']));
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$rp = fn($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
$ang = fn($n) => number_format((float) $n, 0, ',', '.');
$pct = fn($x, $y) => $y ? number_format($x / $y * 100, 1, ',', '.') . '%' : '0%';
$logo = $klinik['logo'] !== '' ? '../../uploads/' . rawurlencode($klinik['logo']) : '';
$hariAktif = array_values(array_filter($a['harian'], fn($x) => $x['resep'] > 0));
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Laporan Pelayanan Resep <?= $h($periode) ?></title>
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
      .kepala .j b { font-size: 15px; display: block; }
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
      tr.jumlah td { font-weight: 700; border-top: 1px solid #111; border-bottom: 2px solid #111; }
      tr { break-inside: avoid; }
      .dua { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
      .ttd { display: flex; justify-content: flex-end; margin-top: 24px; break-inside: avoid; }
      .ttd div { width: 200px; text-align: center; }
      .ttd .garis { margin-top: 50px; border-top: 1px solid #111; padding-top: 3px; font-weight: 700; }
      .kaki { margin-top: 10px; font-size: 9px; color: #444; }
      @media print { body { background: #fff; } .toolbar { display: none; } .kertas { width: auto; margin: 0; padding: 0; box-shadow: none; } }
      @page { size: A4 portrait; margin: 10mm; }
   </style>
</head>

<body>
   <div class="toolbar">
      <button class="utama" onclick="window.print()">🖨️ Cetak</button>
      <button onclick="location.href='<?= $h($urlRincian) ?>'"><?= $denganRincian ? 'Rekap saja' : 'Sertakan daftar resep (' . $ang(count($tiket)) . ')' ?></button>
      <button onclick="window.close()">Tutup</button>
   </div>
   <div class="kertas">
      <div class="kepala">
         <?php if ($logo): ?><img src="<?= $h($logo) ?>" alt="Logo"><?php endif ?>
         <div class="k"><b><?= $h($klinik['nama']) ?></b><?= $klinik['alamat'] ? $h($klinik['alamat']) . '<br>' : '' ?><?= $klinik['telp'] ? 'Telp. ' . $h($klinik['telp']) : '' ?></div>
         <div class="j"><b>LAPORAN PELAYANAN RESEP</b><?= $h($periode) ?></div>
      </div>
      <div class="filter">Filter: <?= $h(rsLabelFilter($p, $penjamin)) ?></div>

      <div class="kpi">
         <div><span>Resep</span><b><?= $ang($r['resep']) ?></b> <span style="display:inline"><?= $ang($r['kunjungan']) ?> kunjungan</span></div>
         <div><span>Selesai diserahkan</span><b><?= $pct($r['selesai'], $r['resep']) ?></b> <span style="display:inline"><?= $ang($r['selesai']) ?> resep</span></div>
         <div><span>Obat keluar</span><b><?= $ang($r['qty']) ?></b> <span style="display:inline"><?= $ang($r['item']) ?> item</span></div>
         <div><span>Nilai obat</span><b><?= $rp($r['nilai']) ?></b> <span style="display:inline"><?= $ang($r['racikan']) ?> racikan</span></div>
      </div>

      <div class="dua">
         <div>
            <h3>Per dokter</h3>
            <table><thead><tr><th>Dokter</th><th class="num">Resep</th><th class="num">Nilai</th></tr></thead><tbody>
               <?php foreach (array_slice($a['grup']['dokter'], 0, 15) as $o): ?><tr><td><?= $h($o['nama']) ?></td><td class="num"><?= $ang($o['resep']) ?></td><td class="num"><?= $rp($o['nilai']) ?></td></tr><?php endforeach ?>
            </tbody></table>
         </div>
         <div>
            <h3>Per penjamin &amp; jenis</h3>
            <table><thead><tr><th>Penjamin</th><th class="num">Resep</th><th class="num">Nilai</th></tr></thead><tbody>
               <?php foreach ($a['grup']['penjamin'] as $o): ?><tr><td><?= $h($o['nama']) ?></td><td class="num"><?= $ang($o['resep']) ?></td><td class="num"><?= $rp($o['nilai']) ?></td></tr><?php endforeach ?>
               <?php foreach ($a['grup']['jenis'] as $o): ?><tr><td>Jenis: <?= $h($o['nama']) ?></td><td class="num"><?= $ang($o['resep']) ?></td><td class="num"><?= $rp($o['nilai']) ?></td></tr><?php endforeach ?>
            </tbody></table>
         </div>
      </div>

      <h3>Obat / BMHP keluar (30 teratas)</h3>
      <table>
         <thead><tr><th>No</th><th>Obat / BMHP</th><th class="num">Qty</th><th class="num">Resep</th><th class="num">Nilai</th></tr></thead>
         <tbody>
            <?php foreach (array_slice($a['obat_semua'], 0, 30) as $i => $o): ?>
               <tr><td><?= $i + 1 ?></td><td><?= $h($o['nama']) ?></td><td class="num"><?= $ang($o['qty']) ?><?= $o['satuan'] ? ' ' . $h($o['satuan']) : '' ?></td><td class="num"><?= $ang($o['resep']) ?></td><td class="num"><?= $rp($o['nilai']) ?></td></tr>
            <?php endforeach ?>
            <?php if (!$a['obat_semua']): ?><tr><td colspan="5" style="text-align:center;padding:8px">Tidak ada obat keluar.</td></tr><?php endif ?>
         </tbody>
      </table>

      <?php if (count($a['harian']) > 1): ?>
         <h3>Rekap harian</h3>
         <table>
            <thead><tr><th>Tanggal</th><th class="num">Resep</th><th class="num">Selesai</th><th class="num">Nilai</th></tr></thead>
            <tbody>
               <?php foreach ($hariAktif as $x): ?><tr><td><?= date('d/m/Y', strtotime($x['tanggal'])) ?></td><td class="num"><?= $ang($x['resep']) ?></td><td class="num"><?= $ang($x['selesai']) ?></td><td class="num"><?= $rp($x['nilai']) ?></td></tr><?php endforeach ?>
               <tr class="jumlah"><td>Total</td><td class="num"><?= $ang($r['resep']) ?></td><td class="num"><?= $ang($r['selesai']) ?></td><td class="num"><?= $rp($r['nilai']) ?></td></tr>
            </tbody>
         </table>
      <?php endif ?>

      <?php if ($denganRincian): ?>
         <h3>Daftar resep</h3>
         <table>
            <thead><tr><th>No</th><th>Tanggal</th><th>No. resep</th><th>Pasien</th><th>Dokter</th><th>Status</th><th class="num">Item</th><th class="num">Nilai</th></tr></thead>
            <tbody>
               <?php foreach ($tiket as $i => $d): ?>
                  <tr><td><?= $i + 1 ?></td><td><?= date('d/m/Y', strtotime($d['tanggal'])) ?> <?= $h($d['jam']) ?></td><td><?= $h($d['nomor']) ?></td><td><?= $h($d['pasien']) ?> <span style="color:#444">(<?= $h($d['nomor_rm']) ?>)</span></td><td><?= $h($d['dokter']) ?></td><td><?= $h($d['status_label']) ?></td><td class="num"><?= $ang($d['item']) ?></td><td class="num"><?= $rp($d['nilai']) ?></td></tr>
               <?php endforeach ?>
               <tr class="jumlah"><td colspan="7" class="num">Total nilai obat</td><td class="num"><?= $rp($r['nilai']) ?></td></tr>
            </tbody>
         </table>
      <?php endif ?>

      <div class="ttd"><div>Petugas farmasi<div class="garis"><?= $h($petugas ?: '..............................') ?></div></div></div>
      <div class="kaki">Dicetak <?= date('d/m/Y H:i') ?><?= $petugas ? ' oleh ' . $h($petugas) : '' ?> · Nilai obat = jumlah × harga pada resep.</div>
   </div>
   <script>window.addEventListener('load', function() { setTimeout(function() { window.print(); }, 300); });</script>
</body>

</html>
