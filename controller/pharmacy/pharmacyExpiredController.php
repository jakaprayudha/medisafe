<?php
/**
 * Obat kedaluwarsa (module/admin/pharmacy_expired).
 * GET                 -> { batches, items, ringkasan } (JSON)
 * GET format=excel    -> XLSX (Ringkasan, Per Batch, Per Obat)
 * GET format=print    -> halaman cetak (isi=risiko|semua)
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/expiredService.php';

$idc = (string) ($_SESSION['id_customer'] ?? '');
$format = in_array($_GET['format'] ?? '', ['excel', 'print'], true) ? $_GET['format'] : 'json';
$gagal = function (string $m, int $c) use ($format) {
   http_response_code($c);
   if ($format === 'json') {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['status' => 'error', 'message' => $m]);
   } else {
      header('Content-Type: text/plain; charset=utf-8');
      echo $m;
   }
   exit;
};
if ($idc === '') $gagal('Session customer tidak ditemukan atau telah kedaluwarsa.', 401);

try {
   $rep = exReport($koneksi, $idc);
} catch (Throwable $e) {
   error_log('[pharmacyExpired] ' . $e->getMessage());
   $gagal('Gagal memuat data obat kedaluwarsa.', 500);
}

if ($format === 'json') {
   header('Content-Type: application/json; charset=utf-8');
   header('Cache-Control: no-store');
   echo json_encode(['status' => 'success', 'hari_ini' => date('Y-m-d'), 'hari_pakai' => EX_HARI_PAKAI] + $rep, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}

require_once __DIR__ . '/../report/appointmentService.php'; // apKlinik, apTglIndo
$klinik = apKlinik($koneksi, (int) $idc);
$r = $rep['ringkasan'];
$label = fn($s) => EX_STATUS[$s][0];
$tgl = fn($d) => $d ? apTglIndo($d) : '-';

if ($format === 'excel') {
   require_once __DIR__ . '/../report/xlsxRingan.php';
   $rpC = fn($v) => ['v' => (float) $v, 's' => 6];
   $x = new XlsxRingan();
   $x->sheet('Ringkasan', [34, 12, 12, 16]);
   $x->row([['v' => 'LAPORAN OBAT KEDALUWARSA — ' . mb_strtoupper($klinik['nama']), 's' => 3]]);
   $x->row([['v' => 'Posisi per ' . $tgl(date('Y-m-d')) . ' · sisa per batch diperkirakan FIFO dari stok saat ini · laju pakai rata-rata ' . EX_HARI_PAKAI . ' hari', 's' => 4]]);
   $x->kosong();
   $x->row(['Status ED (batch masih berstok)', 'Batch', 'Sisa', 'Nilai (harga beli)'], 1);
   foreach (EX_STATUS as $k => [$nm]) $x->row([$nm, $r['status'][$k]['batch'], $rpC($r['status'][$k]['qty']), $rpC($r['status'][$k]['nilai'])]);
   $x->kosong();
   $x->row(['Indikator', 'Nilai'], 1);
   foreach ([
      ['Obat aktif', $r['total_obat']], ['Obat berstok', $r['obat_berstok']], ['Obat berstok tanpa data batch/ED', $r['obat_tanpa_batch']],
      ['Batch masih berstok', $r['batch_berstok']], ['Batch sudah habis terpakai', $r['batch_habis']],
      ['Perkiraan terbuang (unit)', $r['terbuang']['qty']], ['Perkiraan kerugian (harga beli)', $r['terbuang']['nilai']],
   ] as [$k, $v]) $x->row([$k, $rpC($v)]);
   $x->kosong();
   $x->row(['ED per bulan (12 bulan)', 'Batch', 'Sisa', 'Nilai'], 1);
   foreach ($r['timeline'] as $t) $x->row([$t['bulan'] === 'lewat' ? 'Sudah lewat' : $t['bulan'], $t['batch'], $rpC($t['qty']), $rpC($t['nilai'])]);
   $x->kosong();
   $x->row(['Perkiraan terbuang terbesar', 'Unit', 'Nilai', 'ED terdekat'], 1);
   foreach ($r['top_terbuang'] as $t) $x->row([$t['obat'], $rpC($t['terbuang']), $rpC($t['nilai']), $t['ed'] ?? '-']);

   $x->sheet('Per Batch', [30, 14, 22, 16, 11, 11, 9, 9, 9, 13, 14, 10, 14, 16]);
   $x->freezeHeader();
   $x->autoFilter(14);
   $x->row(['Obat', 'Batch', 'Supplier', 'No. Faktur', 'Diterima', 'ED', 'Sisa hari', 'Diterima (qty)', 'Perk. sisa', 'Harga beli', 'Nilai sisa', 'Perk. terbuang', 'Nilai terbuang', 'Status'], 1);
   foreach ($rep['batches'] as $b) {
      $x->row([$b['obat'], ['v' => $b['batch'], 't' => 'str', 's' => 5], $b['supplier'], ['v' => $b['faktur'], 't' => 'str', 's' => 5], $b['diterima_tgl'], $b['expired'] ?? '-',
         $b['sisa_hari'] ?? '', $b['diterima'], $b['sisa'], $rpC($b['harga']), $rpC($b['nilai_sisa']), $b['terbuang'], $rpC($b['nilai_terbuang']),
         $b['sisa'] > 0 ? $label($b['status']) : 'Habis terpakai'], 5);
   }
   $x->sheet('Per Obat', [32, 14, 8, 9, 10, 10, 11, 10, 10, 12, 14, 14, 14]);
   $x->freezeHeader();
   $x->autoFilter(13);
   $x->row(['Obat', 'Kategori', 'Satuan', 'Stok', 'Pakai/hari', 'Habis dlm (hari)', 'ED terdekat', 'Sisa hari', 'Batch berstok', 'Stok tanpa batch', 'Perk. terbuang', 'Nilai terbuang', 'Status'], 1);
   foreach ($rep['items'] as $i) {
      $x->row([$i['obat'], $i['kategori'], $i['satuan'], $i['stok'], $i['pakai_hari'], $i['hari_habis'] ?? '-', $i['ed_terdekat'] ?? '-', $i['sisa_hari'] ?? '',
         $i['batch_berstok'], $i['tanpa_batch'], $i['terbuang'], $rpC($i['nilai_terbuang']), $label($i['status'])], 5);
   }
   setcookie('ex_unduh', preg_replace('/[^a-z0-9]/', '', (string) ($_GET['token'] ?? '')), ['path' => '/', 'samesite' => 'Lax']);
   $x->kirim('obat_kedaluwarsa_' . date('Y-m-d') . '.xlsx');
   exit;
}

/* ---------- cetak ---------- */
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$n = fn($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
$rp = fn($v) => 'Rp ' . number_format(round((float) $v), 0, ',', '.');
$isi = ($_GET['isi'] ?? 'risiko') === 'semua' ? 'semua' : 'risiko';
$daftar = array_values(array_filter($rep['batches'], fn($b) => $b['sisa'] > 0 && ($isi === 'semua' || in_array($b['status'], ['kadaluarsa', 'kritis', 'waspada', 'perhatian'], true))));
$q = $_GET;
$q['isi'] = $isi === 'semua' ? 'risiko' : 'semua';
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="utf-8">
   <title>Obat Kedaluwarsa <?= $e(date('d-m-Y')) ?></title>
   <style>
      @page { size: A4 landscape; margin: 10mm; }
      * { box-sizing: border-box; }
      body { font-family: Arial, Helvetica, sans-serif; font-size: 10.5px; color: #1f2937; margin: 0; }
      .kop { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #2563eb; padding-bottom: 6px; margin-bottom: 10px; }
      .kop h1 { font-size: 16px; margin: 0; } .sub { color: #64748b; }
      .kpi { display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px; margin-bottom: 10px; }
      .kpi div { border: 1px solid #dbe3ee; border-radius: 6px; padding: 6px 8px; } .kpi b { display: block; font-size: 14px; } .kpi span { color: #64748b; font-size: 9.5px; }
      table.data { width: 100%; border-collapse: collapse; } table.data th, table.data td { border: 1px solid #cbd5e1; padding: 3px 5px; text-align: left; }
      table.data th { background: #eef2ff; -webkit-print-color-adjust: exact; print-color-adjust: exact; } table.data thead { display: table-header-group; }
      table.data tr { break-inside: avoid; } .r { text-align: right; } .nw { white-space: nowrap; }
      .s-kadaluarsa { color: #b91c1c; font-weight: bold; } .s-kritis { color: #c2410c; font-weight: bold; } .s-waspada { color: #b45309; font-weight: bold; } .s-perhatian { color: #1d4ed8; } .s-aman { color: #15803d; } .s-tanpa_ed { color: #64748b; }
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
      <a href="?<?= $e(http_build_query($q)) ?>"><?= $isi === 'semua' ? 'Hanya batch berisiko (≤ 180 hari)' : 'Semua batch berstok' ?></a>
      <button class="pri" onclick="window.print()">Cetak</button>
   </div>
   <div class="kop">
      <div><h1>Laporan Obat Kedaluwarsa</h1><div class="sub"><?= $e($klinik['nama']) ?></div></div>
      <div class="sub" style="text-align:right">Posisi per <b><?= $e($tgl(date('Y-m-d'))) ?></b><br><?= $isi === 'semua' ? 'Semua batch berstok' : 'Batch berstok dengan ED ≤ 180 hari / lewat' ?> · Dicetak <?= date('d-m-Y H:i') ?></div>
   </div>
   <div class="kpi">
      <?php foreach (['kadaluarsa', 'kritis', 'waspada', 'perhatian'] as $k): ?>
         <div><b class="s-<?= $k ?>"><?= $n($r['status'][$k]['batch']) ?> batch</b><span><?= $e($label($k)) ?> · <?= $rp($r['status'][$k]['nilai']) ?></span></div>
      <?php endforeach; ?>
      <div><b><?= $rp($r['terbuang']['nilai']) ?></b><span>Perkiraan kerugian (<?= $n($r['terbuang']['qty']) ?> unit)</span></div>
      <div><b><?= $n($r['obat_tanpa_batch']) ?> obat</b><span>Berstok tanpa data batch/ED</span></div>
   </div>
   <table class="data">
      <thead><tr><th>No</th><th>Obat</th><th>Batch</th><th>Supplier</th><th>ED</th><th class="r">Sisa hari</th><th class="r">Perk. sisa</th><th class="r">Nilai sisa</th><th class="r">Perk. terbuang</th><th>Status</th></tr></thead>
      <tbody>
         <?php if (!$daftar): ?><tr><td colspan="10" class="muted" style="text-align:center;padding:12px">Tidak ada batch berstok yang berisiko.</td></tr><?php endif; ?>
         <?php foreach ($daftar as $i => $b): ?>
            <tr>
               <td><?= $i + 1 ?></td><td><?= $e($b['obat']) ?></td><td><?= $e($b['batch']) ?></td><td><?= $e($b['supplier']) ?></td>
               <td class="nw"><?= $e($tgl($b['expired'])) ?></td><td class="r"><?= $b['sisa_hari'] === null ? '-' : ($b['sisa_hari'] < 0 ? 'lewat ' . -$b['sisa_hari'] : $b['sisa_hari']) ?></td>
               <td class="r"><?= $n($b['sisa']) ?> <?= $e($b['satuan']) ?></td><td class="r nw"><?= $rp($b['nilai_sisa']) ?></td>
               <td class="r"><?= $b['terbuang'] > 0 ? $n($b['terbuang']) . ' · ' . $rp($b['nilai_terbuang']) : '-' ?></td>
               <td class="s-<?= $e($b['status']) ?>"><?= $e($label($b['status'])) ?></td>
            </tr>
         <?php endforeach; ?>
      </tbody>
   </table>
   <p class="muted">Sisa per batch diperkirakan FIFO dari stok saat ini (penerimaan terbaru dianggap masih tersisa). Perkiraan terbuang memakai rata-rata pemakaian <?= EX_HARI_PAKAI ?> hari terakhir.</p>
   <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
