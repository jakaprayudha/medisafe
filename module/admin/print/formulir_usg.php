<?php
/**
 * Cetak hasil pemeriksaan USG kunjungan (semua pemeriksaan, satu halaman per pemeriksaan).
 * Dirender di server (dulu lewat get_usg.php yang rentan SQL injection, tanpa batas faskes, hanya pemeriksaan pertama,
 * alamat gambar ditulis tetap "/medisafe/" sehingga gambar hilang di server lain, kolom ttd_dokter / qr_code tidak ada,
 * dan halaman kosong bila URL tanpa rm).
 */
require_once '../../../database/connect.php';
require_once __DIR__ . '/helper_ttd_dokter.php';
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$no = trim((string) ($_GET['no'] ?? ''));
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
if ($idc <= 0) {
   http_response_code(401);
   exit('Sesi berakhir, silakan login ulang.');
}

$st = $koneksi->prepare("SELECT v.visit_ID, p.patient_name, p.nomor_rm, p.patient_datebirth FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
   WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$pasien = $st->get_result()->fetch_assoc();
$st->close();
if (!$pasien) {
   http_response_code(404);
   exit('Kunjungan tidak ditemukan.');
}
$st = $koneksi->prepare("SELECT tanggal_pemeriksaan, usia_kandungan, usg1, usg2, usg3, dokter, interpretasi FROM usg_results WHERE visit_ID = ? ORDER BY tanggal_pemeriksaan ASC, id_usg ASC");
$st->bind_param('s', $no);
$st->execute();
$data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

$BLN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl = function ($s) use ($BLN) {
   return preg_match('/^(\d{4})-(\d{2})-(\d{2})/', (string) $s, $m) ? (int) $m[3] . ' ' . $BLN[(int) $m[2]] . ' ' . $m[1] : '-';
};
$foto = fn($p) => preg_match('#^uploads/usg/([^/]+)$#', (string) $p, $m) && is_file(__DIR__ . '/../../../uploads/usg/' . $m[1]) ? '../../../uploads/usg/' . rawurlencode($m[1]) : null;
$ttdCache = [];
$ttd = function ($dokter) use (&$ttdCache, $koneksi, $idc) {
   if (!array_key_exists($dokter, $ttdCache)) {
      $f = cariTtdDokter($koneksi, $dokter, $idc);
      $ttdCache[$dokter] = $f && is_file(__DIR__ . '/../../../uploads/ttd_faskes/' . basename($f)) ? '../../../uploads/ttd_faskes/' . rawurlencode(basename($f)) : null;
   }
   return $ttdCache[$dokter];
};
?>
<!doctype html>
<html lang="id">

<head>
   <meta charset="utf-8">
   <title>Hasil USG <?= $h($pasien['patient_name']) ?></title>
   <style>
      @page { size: A4; margin: 15mm; }
      body { font-family: "Times New Roman", serif; font-size: 12pt; color: #000; margin: 0; }
      .halaman { width: 180mm; margin: 0 auto; page-break-after: always; }
      .halaman:last-child { page-break-after: auto; }
      .judul { text-align: center; font-size: 15pt; font-weight: bold; margin: 8px 0 12px; text-decoration: underline; }
      table.id { border-collapse: collapse; margin-bottom: 10px; }
      table.id td { padding: 2px 6px 2px 0; vertical-align: top; }
      .foto { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; margin: 8px 0; }
      .foto.satu { grid-template-columns: 1fr; }
      .foto div { border: 1px solid #000; height: 75mm; display: flex; align-items: center; justify-content: center; background: #000; position: relative; }
      .foto.satu div { height: 110mm; }
      .foto img { max-width: 100%; max-height: 100%; object-fit: contain; }
      .foto span { position: absolute; left: 4px; top: 4px; background: #fff; font-size: 9pt; padding: 0 4px; }
      .interp { border: 1px solid #000; padding: 8px 10px; min-height: 30mm; white-space: pre-line; margin-top: 4px; }
      .ttd { margin-top: 16px; margin-left: auto; width: 70mm; text-align: center; }
      .ttd img { height: 22mm; max-width: 60mm; object-fit: contain; display: block; margin: 4px auto; }
      .ttd .ruang { height: 22mm; }
      .ttd b { display: block; border-top: 1px solid #000; padding-top: 2px; }
      .kosong { text-align: center; margin-top: 40px; font-style: italic; }
      @media screen { body { background: #e5e7eb; padding: 16px 0; } .halaman { background: #fff; padding: 12mm; box-shadow: 0 1px 4px rgba(0, 0, 0, .2); margin-bottom: 16px; width: 186mm; } }
   </style>
</head>

<body>
   <?php if (!$data): ?>
      <div class="halaman">
         <?php include 'kopsurat.php'; ?>
         <div class="judul">HASIL PEMERIKSAAN USG</div>
         <div class="kosong">Belum ada pemeriksaan USG untuk kunjungan ini.</div>
      </div>
   <?php endif; ?>
   <?php foreach ($data as $r):
      $f = array_values(array_filter([1 => $foto($r['usg1']), 2 => $foto($r['usg2']), 3 => $foto($r['usg3'])]));
      $t = $ttd((string) $r['dokter']);
   ?>
      <div class="halaman">
         <?php include 'kopsurat.php'; ?>
         <div class="judul">HASIL PEMERIKSAAN USG</div>
         <table class="id">
            <tr><td style="width:45mm">Nama Pasien</td><td>: <b><?= $h($pasien['patient_name']) ?></b></td></tr>
            <tr><td>No. Rekam Medis</td><td>: <?= $h($pasien['nomor_rm']) ?></td></tr>
            <tr><td>Tanggal Lahir</td><td>: <?= $h($tgl($pasien['patient_datebirth'])) ?></td></tr>
            <tr><td>Tanggal Pemeriksaan</td><td>: <?= $h($tgl($r['tanggal_pemeriksaan'])) ?></td></tr>
            <tr><td>Usia Kandungan</td><td>: <?= $h($r['usia_kandungan']) ?></td></tr>
         </table>
         <?php if ($f): ?>
            <div class="foto <?= count($f) === 1 ? 'satu' : '' ?>">
               <?php foreach ($f as $i => $src): ?>
                  <div><span>USG <?= $i + 1 ?></span><img src="<?= $h($src) ?>" alt="USG <?= $i + 1 ?>"></div>
               <?php endforeach; ?>
            </div>
         <?php endif; ?>
         <b>Interpretasi / Hasil Bacaan Dokter:</b>
         <div class="interp"><?= $h($r['interpretasi']) ?></div>
         <div class="ttd">
            Dokter Pemeriksa
            <?php if ($t): ?><img src="<?= $h($t) ?>" alt="TTD dokter"><?php else: ?><div class="ruang"></div><?php endif; ?>
            <b><?= $h($r['dokter']) ?></b>
         </div>
      </div>
   <?php endforeach; ?>
</body>

</html>
