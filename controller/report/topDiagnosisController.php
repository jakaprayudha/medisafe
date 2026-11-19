<?php
/**
 * Laporan diagnosis ICD-10.
 * GET format=json   -> semua diagnosis (urut terbanyak) + ringkasan dashboard + opsi filter
 * GET format=excel  -> XLSX (Ringkasan, Semua Diagnosis, Per Bab)
 * GET format=print  -> halaman cetak (format=pdf lama diarahkan ke sini)
 * GET action=patients&icd_code=... -> daftar kunjungan untuk satu kode
 * Filter: fromDate, toDate, jenis (utama|semua), doctor, poli, provider, gender
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/topDiagnosisService.php';

$format = $_GET['format'] ?? 'json';
if ($format === 'pdf') $format = 'print';
if (!in_array($format, ['json', 'print', 'excel'], true)) {
   http_response_code(400);
   exit('Format laporan tidak valid.');
}
$jsonErr = function (string $msg, int $code) use ($format) {
   http_response_code($code);
   if ($format === 'json') {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['status' => 'error', 'message' => $msg, 'data' => []]);
   } else {
      header('Content-Type: text/plain; charset=utf-8');
      echo $msg;
   }
   exit;
};
if (empty($_SESSION['id_customer']) || !in_array($_SESSION['roles'] ?? '', ['admin', 'superadmin', 'administrator'], true)) {
   $jsonErr('Akses laporan tidak diizinkan.', 403);
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
   $jsonErr('Metode tidak diizinkan.', 405);
}
$idc = (int) $_SESSION['id_customer'];

try {
   if (($_GET['action'] ?? '') === 'patients') {
      $res = topDiagnosisPatients($koneksi, (string) $idc, $_GET);
      header('Content-Type: application/json; charset=utf-8');
      header('Cache-Control: no-store');
      echo json_encode(['status' => 'success'] + $res, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
      exit;
   }

   $rep = topDiagnosisReport($koneksi, (string) $idc, $_GET);
   $label = dgLabelFilter($rep);
   $periode = apTglIndo($rep['fromDate']) . ($rep['fromDate'] !== $rep['toDate'] ? ' s/d ' . apTglIndo($rep['toDate']) : '');

   if ($format === 'json') {
      header('Content-Type: application/json; charset=utf-8');
      header('Cache-Control: no-store');
      $rep['label'] = $label;
      if (empty($_GET['tanpa_opsi'])) {
         $rep['opsi'] = apOpsi($koneksi, $idc, ['from' => $rep['fromDate'], 'to' => $rep['toDate']]);
      }
      echo json_encode(['status' => 'success'] + $rep, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
      exit;
   }

   $klinik = apKlinik($koneksi, $idc);
   $r = $rep['ringkasan'];

   if ($format === 'excel') {
      require_once __DIR__ . '/xlsxRingan.php';
      $x = new XlsxRingan();
      $x->sheet('Ringkasan', [38, 14, 10]);
      $x->row([['v' => 'LAPORAN DIAGNOSIS ICD-10 — ' . mb_strtoupper($klinik['nama']), 's' => 3]]);
      $x->row([['v' => 'Periode: ' . $periode, 's' => 4]]);
      $x->row([['v' => $label . ' · Kunjungan batal tidak dihitung · Dibuat ' . date('d-m-Y H:i'), 's' => 4]]);
      $x->kosong();
      $x->row(['Indikator', 'Nilai'], 1);
      foreach ([
         ['Kunjungan (tanpa batal)', $r['kunjungan']],
         ['Kunjungan dengan diagnosis', $r['terdiagnosis']],
         ['Kunjungan tanpa diagnosis', $r['tanpa_diagnosis']],
         ['Pasien terdiagnosis (unik)', $r['pasien']],
         ['Jumlah kode diagnosis berbeda', $r['kode_unik']],
         ['Total diagnosis dihitung', $r['total_diagnosis']],
         ['Total pada 10 diagnosis teratas', $r['top10']],
      ] as [$k, $v]) $x->row([$k, ['v' => $v, 's' => 6]]);
      $x->row(['Persentase 10 teratas', ['v' => $r['pct_top10'] / 100, 's' => 2]]);
      $x->row(['Persentase tanpa diagnosis', ['v' => $r['pct_tanpa'] / 100, 's' => 2]]);
      $x->kosong();
      $x->row(['10 Diagnosis Teratas', 'Jumlah', '%'], 1);
      foreach (array_slice($rep['data'], 0, 10) as $d) {
         $x->row([$d['rank'] . '. ' . $d['icd_code'] . ' — ' . $d['diagnosis_name'], ['v' => $d['total_visits'], 's' => 6], ['v' => $d['pct'] / 100, 's' => 2]]);
      }

      $x->sheet('Semua Diagnosis', [8, 11, 55, 12, 10, 10, 10, 10, 10, 10, 30]);
      $x->freezeHeader();
      $x->autoFilter(11);
      $x->row(['Peringkat', 'Kode ICD-10', 'Diagnosis', 'Jumlah', '%', 'Utama', 'Sekunder', 'Laki-laki', 'Perempuan', 'Pasien unik', 'Bab ICD-10'], 1);
      foreach ($rep['data'] as $d) {
         $x->row([$d['rank'], ['v' => $d['icd_code'], 't' => 'str', 's' => 5], $d['diagnosis_name'], $d['total_visits'], ['v' => $d['pct'] / 100, 's' => 2],
            $d['utama'], $d['sekunder'], $d['laki'], $d['perempuan'], $d['pasien'], $d['bab'] . ' · ' . $d['bab_nama']], 5);
      }

      $x->sheet('Per Bab ICD-10', [8, 40, 12, 12, 10]);
      $x->row(['Bab', 'Kelompok', 'Jumlah', 'Kode berbeda', '%'], 1);
      foreach ($r['bab'] as $b) {
         $x->row([$b['bab'], $b['nama'], $b['jumlah'], $b['kode'], ['v' => $r['total_diagnosis'] ? $b['jumlah'] / $r['total_diagnosis'] : 0, 's' => 2]], 5);
      }
      setcookie('dg_unduh', preg_replace('/[^a-z0-9]/', '', (string) ($_GET['token'] ?? '')), ['path' => '/', 'samesite' => 'Lax']);
      $x->kirim('diagnosis_icd10_' . $rep['fromDate'] . '_' . $rep['toDate'] . '.xlsx');
      exit;
   }

   /* ---------- print ---------- */
   $e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
   $n = fn($v) => number_format((float) $v, 0, ',', '.');
   $pc = fn($v) => str_replace('.', ',', (string) $v) . '%';
   $isi = ($_GET['isi'] ?? 'semua') === 'top10' ? 'top10' : 'semua';
   $daftar = $isi === 'top10' ? array_slice($rep['data'], 0, 10) : $rep['data'];
   $max = max([1, ...array_column(array_slice($rep['data'], 0, 10), 'total_visits')]);
   $q = $_GET;
   $q['isi'] = $isi === 'top10' ? 'semua' : 'top10';
   header('Content-Type: text/html; charset=utf-8');
   ?>
<!DOCTYPE html>
<html lang="id">
<head>
   <meta charset="utf-8">
   <title>Laporan Diagnosis <?= $e($periode) ?></title>
   <style>
      @page { size: A4 portrait; margin: 12mm; }
      * { box-sizing: border-box; }
      body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #1f2937; margin: 0; }
      .kop { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #2563eb; padding-bottom: 6px; margin-bottom: 10px; }
      .kop h1 { font-size: 16px; margin: 0; } .sub { color: #64748b; }
      .kpi { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; margin-bottom: 10px; }
      .kpi div { border: 1px solid #dbe3ee; border-radius: 6px; padding: 6px 8px; } .kpi b { display: block; font-size: 15px; } .kpi span { color: #64748b; font-size: 10px; }
      h3 { font-size: 11px; text-transform: uppercase; color: #475569; letter-spacing: .3px; margin: 10px 0 4px; }
      .bars { width: 100%; border-collapse: collapse; } .bars td { padding: 2px 3px; }
      .bar { height: 10px; border-radius: 2px; background: #2563eb; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      table.data { width: 100%; border-collapse: collapse; } table.data th, table.data td { border: 1px solid #cbd5e1; padding: 3px 5px; text-align: left; }
      table.data th { background: #eef2ff; -webkit-print-color-adjust: exact; print-color-adjust: exact; } table.data thead { display: table-header-group; }
      table.data tr { break-inside: avoid; } .r { text-align: right; } .top { background: #fefce8; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .tools { position: fixed; top: 10px; right: 10px; display: flex; gap: 6px; }
      .tools a, .tools button { font: inherit; padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; text-decoration: none; color: #1f2937; }
      .tools .pri { background: #2563eb; border-color: #2563eb; color: #fff; }
      @media screen { body { padding: 60px 16px 16px; max-width: 900px; margin: 0 auto; } }
      @media print { .tools { display: none; } }
   </style>
</head>
<body>
   <div class="tools">
      <a href="?<?= $e(http_build_query($q)) ?>"><?= $isi === 'top10' ? 'Cetak semua diagnosis' : 'Cetak 10 teratas saja' ?></a>
      <button class="pri" onclick="window.print()">Cetak</button>
   </div>
   <div class="kop">
      <div><h1>Laporan Diagnosis ICD-10</h1><div class="sub"><?= $e($klinik['nama']) ?></div></div>
      <div class="sub" style="text-align:right">Periode: <b><?= $e($periode) ?></b><br><?= $e($label) ?> · Dicetak <?= date('d-m-Y H:i') ?></div>
   </div>
   <div class="kpi">
      <div><b><?= $n($r['kunjungan']) ?></b><span>Kunjungan (tanpa batal)</span></div>
      <div><b><?= $n($r['terdiagnosis']) ?></b><span>Dengan diagnosis</span></div>
      <div><b><?= $n($r['tanpa_diagnosis']) ?></b><span>Tanpa diagnosis (<?= $pc($r['pct_tanpa']) ?>)</span></div>
      <div><b><?= $n($r['kode_unik']) ?></b><span>Kode diagnosis berbeda</span></div>
      <div><b><?= $pc($r['pct_top10']) ?></b><span>Porsi 10 teratas</span></div>
   </div>
   <h3>10 diagnosis teratas</h3>
   <table class="bars">
      <?php foreach (array_slice($rep['data'], 0, 10) as $d): ?>
         <tr>
            <td style="width:4%"><?= $d['rank'] ?></td>
            <td style="width:38%"><b><?= $e($d['icd_code']) ?></b> <?= $e(mb_strimwidth($d['diagnosis_name'], 0, 52, '…')) ?></td>
            <td><div class="bar" style="width:<?= round($d['total_visits'] / $max * 100) ?>%"></div></td>
            <td class="r" style="width:9%"><?= $n($d['total_visits']) ?></td>
            <td class="r sub" style="width:8%"><?= $pc($d['pct']) ?></td>
         </tr>
      <?php endforeach; ?>
      <?php if (!$rep['data']): ?><tr><td class="sub">Tidak ada data diagnosis pada periode ini.</td></tr><?php endif; ?>
   </table>
   <h3><?= $isi === 'top10' ? '10 diagnosis teratas' : 'Semua diagnosis (' . $n(count($rep['data'])) . ' kode)' ?></h3>
   <table class="data">
      <thead><tr><th>#</th><th>Kode</th><th>Diagnosis</th><th>Bab</th><th class="r">Utama</th><th class="r">Sekunder</th><th class="r">L</th><th class="r">P</th><th class="r">Jumlah</th><th class="r">%</th></tr></thead>
      <tbody>
         <?php foreach ($daftar as $d): ?>
            <tr class="<?= $d['rank'] <= 10 ? 'top' : '' ?>">
               <td><?= $d['rank'] ?></td><td><b><?= $e($d['icd_code']) ?></b></td><td><?= $e($d['diagnosis_name']) ?></td><td><?= $e($d['bab']) ?></td>
               <td class="r"><?= $n($d['utama']) ?></td><td class="r"><?= $n($d['sekunder']) ?></td><td class="r"><?= $n($d['laki']) ?></td><td class="r"><?= $n($d['perempuan']) ?></td>
               <td class="r"><b><?= $n($d['total_visits']) ?></b></td><td class="r"><?= $pc($d['pct']) ?></td>
            </tr>
         <?php endforeach; ?>
      </tbody>
   </table>
   <p class="sub">Kunjungan batal tidak dihitung. L/P dihitung dari diagnosis utama. Baris kuning = 10 diagnosis teratas.</p>
   <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
<?php
} catch (Throwable $error) {
   $invalid = $error instanceof InvalidArgumentException;
   if (!$invalid) error_log('Top diagnosis report failed: ' . get_class($error) . ': ' . $error->getMessage());
   $jsonErr($invalid ? $error->getMessage() : 'Gagal membuat laporan diagnosis. Silakan hubungi administrator.', $invalid ? 400 : 500);
}
