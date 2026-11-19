<?php
/** Cetak laporan appointment: ringkasan (dashboard) + tabel data. Angka sama persis dengan halaman & Excel. */
require_once '../../database/connect.php';
require_once '../../controller/report/appointmentService.php';

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) {
   exit('Session tidak ditemukan');
}
try {
   $p = apParams($_GET);
} catch (InvalidArgumentException $e) {
   exit(htmlspecialchars($e->getMessage()));
}
@set_time_limit(120);

$rows = apRows($koneksi, $idc, $p);
$r = apRingkasan($rows, $p);
$klinik = apKlinik($koneksi, $idc);
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$n = fn($v) => number_format((float) $v, 0, ',', '.');
$periode = apTglIndo($p['from']) . ($p['from'] !== $p['to'] ? ' s/d ' . apTglIndo($p['to']) : '');
$denganData = ($_GET['isi'] ?? 'lengkap') !== 'ringkasan';
$batasCetak = 5000;
$terpotong = $denganData && count($rows) > $batasCetak;

function apBar(array $data, int $aktif, string $warna): string
{
   $max = max([1, ...array_column($data, 'jumlah')]);
   $h = '';
   foreach ($data as $d) {
      $w = round($d['jumlah'] / $max * 100);
      $pct = $aktif ? round($d['jumlah'] / $aktif * 100, 1) : 0;
      $h .= '<tr><td class="bl">' . htmlspecialchars($d['nama']) . '</td><td class="bb"><div style="width:' . $w . '%;background:' . $warna . '"></div></td>'
         . '<td class="bn">' . number_format($d['jumlah'], 0, ',', '.') . '</td><td class="bp">' . str_replace('.', ',', (string) $pct) . '%</td></tr>';
   }
   return $h ?: '<tr><td colspan="4" class="muted">Tidak ada data</td></tr>';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="utf-8">
   <title>Laporan Appointment <?= $e($periode) ?></title>
   <style>
      @page { size: A4 landscape; margin: 10mm; }
      * { box-sizing: border-box; }
      body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #1f2937; margin: 0; }
      .kop { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #2563eb; padding-bottom: 6px; margin-bottom: 10px; }
      .kop h1 { font-size: 16px; margin: 0; }
      .kop .sub { color: #64748b; font-size: 11px; }
      .kpi { display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px; margin-bottom: 10px; }
      .kpi div { border: 1px solid #dbe3ee; border-radius: 6px; padding: 6px 8px; }
      .kpi b { display: block; font-size: 16px; }
      .kpi span { color: #64748b; font-size: 10px; }
      .grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 10px; }
      .box { border: 1px solid #dbe3ee; border-radius: 6px; padding: 6px 8px; break-inside: avoid; }
      .box h3 { font-size: 11px; margin: 0 0 4px; text-transform: uppercase; color: #475569; letter-spacing: .3px; }
      .bars { width: 100%; border-collapse: collapse; }
      .bars td { padding: 2px 3px; border: 0; }
      .bl { width: 42%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 0; }
      .bb div { height: 9px; border-radius: 2px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .bn { width: 12%; text-align: right; }
      .bp { width: 12%; text-align: right; color: #64748b; }
      table.data { width: 100%; border-collapse: collapse; }
      table.data th, table.data td { border: 1px solid #cbd5e1; padding: 3px 5px; text-align: left; }
      table.data th { background: #eef2ff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      table.data thead { display: table-header-group; }
      table.data tr { break-inside: avoid; }
      .st { font-weight: bold; }
      .st-selesai { color: #15803d; } .st-batal { color: #b91c1c; } .st-booking { color: #b45309; } .st-proses { color: #1d4ed8; }
      .muted { color: #94a3b8; }
      .warn { background: #fff7ed; border: 1px solid #fdba74; padding: 6px 8px; border-radius: 6px; margin-bottom: 8px; }
      .tools { position: fixed; top: 10px; right: 10px; display: flex; gap: 6px; }
      .tools a, .tools button { font: inherit; padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; text-decoration: none; color: #1f2937; }
      .tools .pri { background: #2563eb; border-color: #2563eb; color: #fff; }
      @media screen { body { padding: 60px 16px 16px; max-width: 1200px; margin: 0 auto; } }
      table.data td:nth-child(2) { white-space: nowrap; }
      @media print { .tools { display: none; } }
   </style>
</head>

<body>
   <div class="tools">
      <?php $q = $_GET; $q['isi'] = $denganData ? 'ringkasan' : 'lengkap'; ?>
      <a href="?<?= $e(http_build_query($q)) ?>"><?= $denganData ? 'Cetak ringkasan saja' : 'Sertakan tabel data' ?></a>
      <button class="pri" onclick="window.print()">Cetak</button>
   </div>

   <div class="kop">
      <div>
         <h1>Laporan Appointment</h1>
         <div class="sub"><?= $e($klinik['nama']) ?></div>
      </div>
      <div class="sub" style="text-align:right">
         Periode: <b><?= $e($periode) ?></b><br>
         <?= $e(apLabelFilter($p)) ?> · Dicetak <?= date('d-m-Y H:i') ?>
      </div>
   </div>

   <div class="kpi">
      <div><b><?= $n($r['total']) ?></b><span>Total pendaftaran</span></div>
      <div><b><?= $n($r['status']['selesai']) ?></b><span>Selesai (<?= str_replace('.', ',', (string) $r['rate_selesai']) ?>%)</span></div>
      <div><b><?= $n($r['status']['proses']) ?></b><span>Dalam proses</span></div>
      <div><b><?= $n($r['status']['booking']) ?></b><span>Belum check-in</span></div>
      <div><b><?= $n($r['status']['batal']) ?></b><span>Batal (<?= str_replace('.', ',', (string) $r['rate_batal']) ?>%)</span></div>
      <div><b><?= $n($r['pasien_unik']) ?></b><span>Pasien unik</span></div>
   </div>

   <div class="grid">
      <div class="box">
         <h3>Dokter</h3>
         <table class="bars"><?= apBar($r['dokter'], $r['aktif'], '#2563eb') ?></table>
      </div>
      <div class="box">
         <h3>Poli</h3>
         <table class="bars"><?= apBar($r['poli'], $r['aktif'], '#13b5a8') ?></table>
      </div>
      <div class="box">
         <h3>Jenis bayar &amp; gender</h3>
         <table class="bars">
            <?= apBar($r['bayar'], $r['aktif'], '#f59e0b') ?>
            <?= apBar([['nama' => 'Laki-laki', 'jumlah' => $r['gender']['L']], ['nama' => 'Perempuan', 'jumlah' => $r['gender']['P']]], $r['aktif'], '#a855f7') ?>
         </table>
         <div class="muted" style="margin-top:4px">Rata-rata <?= str_replace('.', ',', (string) $r['rata_hari']) ?> kunjungan/hari<?= $r['jam_sibuk'] !== null ? sprintf(' · tersibuk %02d:00–%02d:00', $r['jam_sibuk'], $r['jam_sibuk'] + 1) : '' ?></div>
      </div>
   </div>

   <?php if ($denganData): ?>
      <?php if ($terpotong): ?>
         <div class="warn">Data berisi <?= $n(count($rows)) ?> baris; yang dicetak <?= $n($batasCetak) ?> baris terbaru. Gunakan <b>Export Excel</b> untuk data lengkap.</div>
      <?php endif; ?>
      <table class="data">
         <thead>
            <tr>
               <th style="width:34px">No</th><th>Tanggal</th><th>Status</th><th>No. BPJS</th><th>NIK</th><th>Nama Pasien</th><th>L/P</th><th>Dokter</th><th>Poli</th><th>Jenis Bayar</th>
            </tr>
         </thead>
         <tbody>
            <?php if (!$rows): ?>
               <tr><td colspan="10" class="muted" style="text-align:center;padding:14px">Tidak ada data pada periode ini.</td></tr>
            <?php endif; ?>
            <?php foreach (array_slice($rows, 0, $batasCetak) as $i => $x): ?>
               <tr>
                  <td><?= $i + 1 ?></td>
                  <td><?= $e(apTglIndo($x['tanggal']) . ' ' . $x['jam']) ?></td>
                  <td class="st st-<?= $e($x['grup']) ?>"><?= $e($x['status_label']) ?></td>
                  <td><?= $e($x['noKartu'] ?: '-') ?></td>
                  <td><?= $e($x['nik'] ?: '-') ?></td>
                  <td><?= $e($x['nama']) ?></td>
                  <td><?= $e($x['gender']) ?></td>
                  <td><?= $e($x['dokter']) ?></td>
                  <td><?= $e($x['poli']) ?></td>
                  <td><?= $e($x['bayar']) ?></td>
               </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   <?php endif; ?>
   <script>
      window.addEventListener('load', () => setTimeout(() => window.print(), 300));
   </script>
</body>

</html>
