<?php
/**
 * Cetak lembar masuk & keluar pasien rawat inap. Dirender di server memakai ioAmbil() (controller/ranap/getFormInap.php):
 * data tersimpan di visit_ranap, atau isian awal dari kunjungan bila belum disimpan.
 * Perbaikan: query dirangkai dari URL (SQL injection) tanpa batas faskes; INNER JOIN ms_doctor ON id_doctor (berisi nama)
 * & INNER JOIN icd_10 -> data kosong; getranap.php (SQL injection) hanya terisi bila data sudah disimpan, yang selalu
 * gagal; nama klinik "Klinik Tutun Sehati" tertulis tetap & "Datang Sendiri" selalu dicentang; diagnosa medik diisi kode
 * ICD; lama dirawat, transfusi, imunisasi, radioterapi, alergi pasien tidak tercetak.
 */
require_once '../../../database/connect.php';
define('IO_RANAP_LIB', true);
require_once __DIR__ . '/../../../controller/ranap/getFormInap.php';
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$no = trim((string) ($_GET['no'] ?? ''));
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
if ($idc <= 0) {
   http_response_code(401);
   exit('Sesi berakhir, silakan login ulang.');
}
$st = $koneksi->prepare("SELECT id_visit FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$idVisit = (int) ($st->get_result()->fetch_row()[0] ?? 0);
$st->close();
if (!$idVisit) {
   http_response_code(404);
   exit('Kunjungan tidak ditemukan.');
}
$io = ioAmbil($koneksi, $idVisit);
$d = $io['inap'] ?: $io['awal'];
foreach (['ruang_rawat', 'diagnosa_utama', 'dokter_merawat'] as $k) $d[$k] = $io['awal'][$k];
$p = $io['pasien'];
if (!$io['inap'] && $d['tanggal_masuk'] && $d['tanggal_keluar'] && $d['tanggal_keluar'] >= $d['tanggal_masuk']) {
   $d['lama_dirawat'] = max(1, (new DateTime($d['tanggal_masuk']))->diff(new DateTime($d['tanggal_keluar']))->days);
}
$BLN = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl = fn($s) => preg_match('/^(\d{4})-(\d{2})-(\d{2})/', (string) $s, $m) ? (int) $m[3] . ' ' . $BLN[(int) $m[2]] . ' ' . $m[1] : '-';
$v = fn($k) => ($x = trim((string) ($d[$k] ?? ''))) !== '' ? $h($x) : '-';
$title = 'Lembar Masuk dan Keluar Pasien Rawat Inap';
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title><?= $h($title) ?> - <?= $h($p['nama_pasien']) ?></title>
   <style>
      @page { size: A4; margin: 15mm 18mm; }
      body { font-family: "Times New Roman", serif; font-size: 10.5pt; margin: 0; color: #000; background: #fff; }
      .wadah { max-width: 760px; margin: auto; }
      .judul { text-align: center; font-weight: bold; font-size: 13pt; margin: 8px 0 10px; text-transform: uppercase; }
      .bag { border: 1px solid #000; margin-bottom: 8px; page-break-inside: avoid; }
      .bag-h { background: #f2f2f2; font-weight: bold; padding: 4px 8px; border-bottom: 1px solid #000; }
      table { width: 100%; border-collapse: collapse; }
      td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
      td.l { width: 28%; }
      .bag table tr:first-child td { border-top: 0; }
      .bag table td:first-child { border-left: 0; }
      .bag table td:last-child { border-right: 0; }
      .bag table tr:last-child td { border-bottom: 0; }
      .tebal { font-weight: bold; }
      .pre { white-space: pre-line; }
      .kotak { display: inline-block; width: 11px; height: 11px; border: 1px solid #000; vertical-align: -1px; margin-right: 4px; }
      .ttd { width: 70mm; margin: 14px 0 0 auto; text-align: center; }
      .ttd .ruang { height: 20mm; }
      .ttd b { display: block; border-top: 1px solid #000; padding-top: 2px; }
      .noprint { text-align: center; margin: 14px 0; }
      @media print { .noprint { display: none !important; } }
   </style>
</head>

<body>
   <div class="wadah">
      <?php $_GET['no'] = $no;
      include 'kop-surat.php'; ?>
      <div class="judul"><?= $h($title) ?></div>

      <div class="bag">
         <div class="bag-h">IDENTITAS & ADMINISTRASI</div>
         <table>
            <tr><td class="l">Nama Pasien / No. RM</td><td class="tebal"><?= $h($p['nama_pasien']) ?> / <?= $h($p['nomor_rm']) ?></td></tr>
            <tr><td class="l">Jenis Kelamin / Usia</td><td><?= $h($p['jk'] ?: '-') ?> / <?= $h($p['usia'] ?: '-') ?></td></tr>
            <tr><td class="l">Status Perkawinan</td><td><?= $v('status_perkawinan') ?></td></tr>
            <tr><td class="l">Penanggung Jawab</td><td><?= $v('penanggung_jawab') ?></td></tr>
            <tr><td class="l">Alamat Penanggung Jawab</td><td class="pre"><?= $v('alamat_pj') ?></td></tr>
            <tr><td class="l">No. BPJS</td><td><?= $h($p['nomor_bpjs'] ?: '-') ?></td></tr>
         </table>
      </div>

      <div class="bag">
         <div class="bag-h">MASUK & KELUAR RAWAT INAP</div>
         <table>
            <tr><td class="l">Tanggal / Jam Masuk</td><td class="tebal"><?= $h($tgl($d['tanggal_masuk'] ?? '')) ?> <?= $h($d['jam_masuk'] ?? '') ?></td></tr>
            <tr><td class="l">Tanggal / Jam Pindah</td><td><?= $h($tgl($d['tanggal_pindah'] ?? '')) ?> <?= $h($d['jam_pindah'] ?? '') ?></td></tr>
            <tr><td class="l">Ruang / Kelas</td><td><?= $v('ruang_rawat') ?></td></tr>
            <tr><td class="l">Tanggal / Jam Keluar</td><td class="tebal"><?= $h($tgl($d['tanggal_keluar'] ?? '')) ?> <?= $h($d['jam_keluar'] ?? '') ?></td></tr>
            <tr><td class="l">Lama Dirawat</td><td class="tebal"><?= !empty($d['lama_dirawat']) ? (int) $d['lama_dirawat'] . ' hari' : '-' ?></td></tr>
            <tr><td class="l">Cara Masuk</td><td>
               <?php foreach (['Dokter / Paramedis', 'Pustu / Polindes', 'Instansi Kesehatan', 'Kasus Polisi', 'Datang Sendiri'] as $c): ?>
                  <span style="margin-right:14px;white-space:nowrap"><span class="kotak"></span><?= $h($c) ?></span>
               <?php endforeach; ?>
            </td></tr>
         </table>
      </div>

      <div class="bag">
         <div class="bag-h">DIAGNOSA & TINDAKAN</div>
         <table>
            <tr><td class="l">Anamnesa Masuk</td><td class="pre"><?= $v('diagnosa_medik') ?></td></tr>
            <tr><td class="l">Diagnosa Utama</td><td class="tebal"><?= $v('diagnosa_utama') ?></td></tr>
            <tr><td class="l">Diagnosa Sekunder / Komplikasi</td><td class="pre"><?= $v('diagnosa_komplikasi') ?></td></tr>
            <tr><td class="l">Penyebab Cedera / Keracunan</td><td class="pre"><?= $v('penyebab_keracunan') ?></td></tr>
            <tr><td class="l">Operasi / Tindakan</td><td class="pre"><?= $v('nama_operasi') ?></td></tr>
         </table>
      </div>

      <div class="bag">
         <div class="bag-h">CATATAN MEDIS LAINNYA</div>
         <table>
            <tr><td class="l">Infeksi Nosokomial</td><td><?= $v('infeksi_nosokomial') ?><?= ($d['infeksi_nosokomial'] ?? '') === 'Ya' ? ' — ' . $v('penyebab_infeksi') : '' ?></td></tr>
            <tr><td class="l">Alergi</td><td><?= $v('alergi') ?></td></tr>
            <tr><td class="l">Radio Therapy</td><td><?= $v('radioterapi') ?></td></tr>
            <tr><td class="l">Imunisasi</td><td><?= $v('imunisasi') ?></td></tr>
            <tr><td class="l">Transfusi Darah</td><td><?= $v('transfusi') ?></td></tr>
            <tr><td class="l">Keadaan Keluar</td><td class="tebal"><?= $v('keadaan_keluar') ?></td></tr>
            <tr><td class="l">Cara Keluar</td><td><?= $v('cara_keluar') ?></td></tr>
         </table>
      </div>

      <div class="ttd">
         Dokter yang Merawat
         <div class="ruang"></div>
         <b><?= $v('dokter_merawat') ?></b>
      </div>
      <?php if (!$io['inap']): ?>
         <p class="noprint" style="color:#92400e">Catatan: data belum disimpan di form — yang tercetak adalah isian awal dari data kunjungan.</p>
      <?php endif; ?>
      <div class="noprint"><button onclick="window.print()">Cetak Halaman</button></div>
   </div>
</body>

</html>
