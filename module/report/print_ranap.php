<?php
/** Cetak laporan rawat inap: ringkasan + tabel pasien. Angka sama dengan halaman & Excel. */
require_once '../../database/connect.php';
require_once '../../controller/report/ranapService.php';

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) exit('Session tidak ditemukan');
try {
   $p = rnParams($_GET);
} catch (InvalidArgumentException $e) {
   exit(htmlspecialchars($e->getMessage()));
}
@set_time_limit(120);

$rows = rnRows($koneksi, $idc, $p);
$km = rnKamar($koneksi, $idc);
$r = rnRingkasan($rows, $p, $km['bed'], $koneksi);
$klinik = apKlinik($koneksi, $idc);
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$n = fn($v) => number_format((float) $v, 0, ',', '.');
$d1 = fn($v) => str_replace('.', ',', (string) $v);
$periode = apTglIndo($p['from']) . ($p['from'] !== $p['to'] ? ' s/d ' . apTglIndo($p['to']) : '');
$denganData = ($_GET['isi'] ?? 'lengkap') !== 'ringkasan';
$tot = max(1, $r['total']);

function rnBar(array $data, int $tot, string $warna): string
{
   $max = max([1, ...array_column($data, 'jumlah')]);
   $h = '';
   foreach ($data as $d) {
      $h .= '<tr><td class="bl">' . htmlspecialchars($d['nama']) . '</td><td class="bb"><div style="width:' . round($d['jumlah'] / $max * 100) . '%;background:' . $warna . '"></div></td>'
         . '<td class="bn">' . number_format($d['jumlah'], 0, ',', '.') . '</td><td class="bp">' . round($d['jumlah'] / $tot * 100) . '%</td></tr>';
   }
   return $h ?: '<tr><td colspan="4" class="muted">Tidak ada data</td></tr>';
}
$q = $_GET;
$q['isi'] = $denganData ? 'ringkasan' : 'lengkap';
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="utf-8">
   <title>Laporan Rawat Inap <?= $e($periode) ?></title>
   <style>
      @page { size: A4 landscape; margin: 10mm; }
      * { box-sizing: border-box; }
      body { font-family: Arial, Helvetica, sans-serif; font-size: 10.5px; color: #1f2937; margin: 0; }
      .kop { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #2563eb; padding-bottom: 6px; margin-bottom: 10px; }
      .kop h1 { font-size: 16px; margin: 0; } .sub { color: #64748b; }
      .kpi { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin-bottom: 10px; }
      .kpi div { border: 1px solid #dbe3ee; border-radius: 6px; padding: 6px 8px; } .kpi b { display: block; font-size: 15px; } .kpi span { color: #64748b; font-size: 9.5px; }
      .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 10px; }
      .box { border: 1px solid #dbe3ee; border-radius: 6px; padding: 6px 8px; break-inside: avoid; }
      .box h3 { font-size: 10px; margin: 0 0 4px; text-transform: uppercase; color: #475569; }
      .bars { width: 100%; border-collapse: collapse; } .bars td { padding: 1.5px 3px; }
      .bl { width: 45%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 0; }
      .bb div { height: 8px; border-radius: 2px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .bn { width: 12%; text-align: right; } .bp { width: 12%; text-align: right; color: #64748b; }
      table.data { width: 100%; border-collapse: collapse; } table.data th, table.data td { border: 1px solid #cbd5e1; padding: 3px 4px; text-align: left; }
      table.data th { background: #eef2ff; -webkit-print-color-adjust: exact; print-color-adjust: exact; } table.data thead { display: table-header-group; }
      table.data tr { break-inside: avoid; } .nw { white-space: nowrap; } .r { text-align: right; }
      .st-pulang { color: #15803d; font-weight: bold; } .st-dirawat { color: #1d4ed8; font-weight: bold; } .st-belum { color: #b45309; font-weight: bold; }
      .muted { color: #94a3b8; }
      .tools { position: fixed; top: 10px; right: 10px; display: flex; gap: 6px; }
      .tools a, .tools button { font: inherit; padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; text-decoration: none; color: #1f2937; }
      .tools .pri { background: #2563eb; border-color: #2563eb; color: #fff; }
      @media screen { body { padding: 60px 16px 16px; max-width: 1250px; margin: 0 auto; } }
      @media print { .tools { display: none; } }
   </style>
</head>

<body>
   <div class="tools">
      <a href="?<?= $e(http_build_query($q)) ?>"><?= $denganData ? 'Cetak ringkasan saja' : 'Sertakan tabel pasien' ?></a>
      <button class="pri" onclick="window.print()">Cetak</button>
   </div>
   <div class="kop">
      <div><h1>Laporan Rawat Inap</h1><div class="sub"><?= $e($klinik['nama']) ?></div></div>
      <div class="sub" style="text-align:right">Periode masuk: <b><?= $e($periode) ?></b><br><?= $e(rnLabelFilter($p, $km['rooms'])) ?> · Dicetak <?= date('d-m-Y H:i') ?></div>
   </div>
   <div class="kpi">
      <div><b><?= $n($r['total']) ?></b><span>Pasien masuk</span></div>
      <div><b><?= $n($r['status']['pulang']) ?></b><span>Pulang</span></div>
      <div><b><?= $n($r['status']['dirawat']) ?></b><span>Masih dirawat</span></div>
      <div><b><?= $n($r['status']['belum']) ?></b><span>Belum ada CPPT</span></div>
      <div><b><?= $d1($r['alos']) ?> hari</b><span>Rata-rata lama rawat</span></div>
      <div><b><?= $r['bor'] === null ? '-' : $d1($r['bor']) . '%' ?></b><span>BOR (<?= $n($r['bed']) ?> bed)</span></div>
      <div><b><?= $d1($r['pct_sembuh']) ?>%</b><span>Pulang sembuh/membaik</span></div>
   </div>
   <div class="grid">
      <div class="box"><h3>Kondisi pulang</h3><table class="bars"><?= rnBar($r['kondisi'], max(1, $r['status']['pulang']), '#16a34a') ?></table></div>
      <div class="box"><h3>Lama rawat</h3><table class="bars"><?= rnBar($r['los_bin'], max(1, array_sum(array_column($r['los_bin'], 'jumlah'))), '#2563eb') ?></table></div>
      <div class="box"><h3>Kamar</h3><table class="bars"><?= rnBar($r['kamar'], $tot, '#f59e0b') ?></table></div>
      <div class="box"><h3>Diagnosis teratas</h3><table class="bars"><?= rnBar(array_map(fn($d) => ['nama' => $d['kode'] . ' ' . $d['nama'], 'jumlah' => $d['jumlah']], array_slice($r['diagnosa'], 0, 8)), $tot, '#a855f7') ?></table></div>
   </div>

   <?php if ($denganData): ?>
      <table class="data">
         <thead><tr><th>No</th><th>Masuk</th><th>Pulang</th><th class="r">Lama</th><th>Status</th><th>Nama Pasien</th><th>No. RM</th><th>L/P</th><th class="r">Umur</th><th>Dokter</th><th>Diagnosis</th><th>Kamar</th><th>Jenis Bayar</th><th>Kondisi Pulang</th></tr></thead>
         <tbody>
            <?php if (!$rows): ?><tr><td colspan="14" class="muted" style="text-align:center;padding:14px">Tidak ada pasien rawat inap pada periode ini.</td></tr><?php endif; ?>
            <?php foreach ($rows as $i => $d): ?>
               <tr>
                  <td><?= $i + 1 ?></td>
                  <td class="nw"><?= $e(apTglIndo($d['masuk']) . ' ' . $d['jam']) ?></td>
                  <td class="nw"><?= $d['pulang'] ? $e(apTglIndo($d['pulang']) . ' ' . $d['jam_pulang']) : '-' ?></td>
                  <td class="r"><?= $d['los'] ?? ($d['los_valid'] ? '-' : '?') ?></td>
                  <td class="st-<?= $e($d['status']) ?>"><?= $e(RN_STATUS_LABEL[$d['status']]) ?></td>
                  <td><?= $e($d['nama']) ?></td><td><?= $e($d['rm'] ?: '-') ?></td><td><?= $e($d['gender']) ?></td><td class="r"><?= $d['umur'] ?? '-' ?></td>
                  <td><?= $e($d['dokter']) ?></td><td><?= $e($d['diagnosa'] ?: '-') ?></td>
                  <td><?= $e(trim($d['kamar'] . ($d['bed'] ? ' / ' . $d['bed'] : '')) ?: '-') ?></td>
                  <td><?= $e($d['bayar']) ?></td><td><?= $e($d['kondisi'] ?: '-') ?></td>
               </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   <?php endif; ?>
   <p class="muted">Lama rawat dihitung dari tanggal masuk rawat inap sampai tanggal pulang (minimal 1 hari). BOR = hari rawat dalam periode ÷ (jumlah bed × hari periode).</p>
   <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>

</html>
