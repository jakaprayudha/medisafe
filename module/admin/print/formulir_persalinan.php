<?php
/**
 * Cetak rekapitulasi pelayanan persalinan (data dari rpAmbil() di controller/ranap/getFormRekapPersalinan.php).
 * Tetap berupa potongan HTML (tanpa <html>/<body>) agar aman di-bundle.
 * Perbaikan: data diambil lewat curl ke getpersalinan.php (tanpa cookie sesi; getpersalinan rentan SQL injection & tanpa
 * batas faskes) — gagal bila server memakai HTTPS self-signed / tidak bisa memanggil dirinya sendiri; tanggal pelayanan =
 * tanggal cetak (bukan tanggal kunjungan); nama kota "Tanjung Morawa" tertulis tetap; nilai dicetak tanpa escape;
 * tanda tangan default.png ditampilkan bila pasien belum tanda tangan; tarif tanpa format rupiah.
 */
require_once '../../../database/connect.php';
require_once '../../admin/getdataclinic.php';
if (!defined('RP_LIB')) define('RP_LIB', true);
require_once __DIR__ . '/../../../controller/ranap/getFormRekapPersalinan.php';

$rpNo = trim((string) ($_GET['no'] ?? ''));
$rpIdc = (int) ($_SESSION['id_customer'] ?? 0);
$rpH = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$st = $koneksi->prepare("SELECT id_visit FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
$st->bind_param('si', $rpNo, $rpIdc);
$st->execute();
$rpIdVisit = (int) ($st->get_result()->fetch_row()[0] ?? 0);
$st->close();
if (!$rpIdVisit) {
   echo '<p style="font-family:sans-serif;color:#b91c1c">Kunjungan tidak ditemukan untuk faskes ini.</p>';
   return;
}
$rp = rpAmbil($koneksi, $rpIdVisit);
$rpP = $rp['pasien'];
$rpS = $rp['persalinan'] ?: [];

if (!function_exists('formatTanggalIndonesia')) {
   function formatTanggalIndonesia($tanggal)
   {
      $b = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
      return preg_match('/^(\d{4})-(\d{2})-(\d{2})/', (string) $tanggal, $m) ? (int) $m[3] . ' ' . $b[(int) $m[2]] . ' ' . $m[1] : '-';
   }
}
$rpTglPelayanan = formatTanggalIndonesia($rpP['tanggal_pelayanan']);
$rpTempat = ucwords(strtolower(trim((string) ($datafaskes['faskes_district'] ?? '')))) ?: '..................';
$rpTtd = '';
if (!empty($rpS['ttd_file']) && is_file(__DIR__ . '/../../../uploads/ttd/' . basename($rpS['ttd_file']))) $rpTtd = '../../../uploads/ttd/' . rawurlencode(basename($rpS['ttd_file']));
$rpV = fn($k) => ($x = trim((string) ($rpS[$k] ?? ''))) !== '' ? $rpH($x) : '-';
$rpTarif = ($rpS['tarif_paket'] ?? '') !== '' && $rpS['tarif_paket'] !== null && ctype_digit((string) $rpS['tarif_paket']) ? 'Rp ' . number_format((int) $rpS['tarif_paket'], 0, ',', '.') : $rpV('tarif_paket');
?>
<style>
   .form-persalinan { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 0 10mm; box-sizing: border-box; font-family: "Times New Roman", serif; }
   .form-persalinan .form-title { text-align: center; font-size: 15pt; font-weight: bold; margin: 10px 0 18px; line-height: 1.4; text-transform: uppercase; }
   .form-persalinan .content { font-size: 13pt; line-height: 1.5; }
   .form-persalinan table { border-collapse: collapse; width: 100%; }
   .form-persalinan td { padding: 3px 4px; vertical-align: top; }
   .form-persalinan td.l { width: 230px; font-weight: bold; }
   .form-persalinan td.t { width: 12px; }
   .form-persalinan .signature { margin-top: 36px; display: flex; justify-content: flex-end; font-size: 13pt; }
   .form-persalinan .signature-block { text-align: center; min-width: 240px; }
   .form-persalinan .signature-image { height: 90px; display: flex; align-items: center; justify-content: center; }
   .form-persalinan .signature-image img { max-height: 90px; max-width: 220px; }
</style>

<div class="form-persalinan">
   <?php include 'kopsurat.php'; ?>

   <div class="form-title">
      Rekapitulasi Pelayanan Persalinan di Fasilitas Kesehatan Tingkat Pertama (FKTP)<br>
      BPJS Kesehatan Cabang Lubuk Pakam
   </div>

   <div class="content">
      <p>Saya yang bertanda tangan di bawah ini :</p>
      <table>
         <tr><td class="l">Nama Penderita</td><td class="t">:</td><td><?= $rpH($rpP['nama_pasien'] ?: '-') ?></td></tr>
         <tr><td class="l">Nomor Identitas</td><td class="t">:</td><td><?= $rpH($rpP['nik'] ?: '-') ?><?= $rpP['bpjs'] ? ' / BPJS ' . $rpH($rpP['bpjs']) : '' ?></td></tr>
         <tr><td class="l">Tempat / Tanggal Lahir</td><td class="t">:</td><td><?= $rpH($rpP['tempat_lahir'] ?: '-') ?> / <?= $rpH(formatTanggalIndonesia($rpP['tanggal_lahir'])) ?></td></tr>
         <tr><td class="l">Alamat dan Nomor Telepon</td><td class="t">:</td><td><?= $rpH($rpP['alamat'] ?: '-') ?>, <?= $rpH($rpP['telepon'] ?: '-') ?></td></tr>
         <tr><td class="l">Tanggal Pelayanan</td><td class="t">:</td><td><?= $rpH($rpTglPelayanan) ?></td></tr>
         <tr><td class="l">Gravid</td><td class="t">:</td><td><?= $rpV('gravid') ?></td></tr>
         <tr><td class="l">Abortus</td><td class="t">:</td><td><?= $rpV('abortus') ?></td></tr>
         <tr><td class="l">Partus</td><td class="t">:</td><td><?= $rpV('partus') ?></td></tr>
         <tr><td class="l">Jenis Persalinan</td><td class="t">:</td><td><?= $rpV('jenis_persalinan') ?></td></tr>
         <tr><td class="l">Besaran Tarif Paket</td><td class="t">:</td><td><?= $rpTarif ?></td></tr>
      </table>
   </div>

   <div class="signature">
      <div class="signature-block">
         <p style="margin:0 0 6px"><?= $rpH($rpTempat) ?>, <?= $rpH($rpTglPelayanan) ?></p>
         Yang Membuat Pernyataan
         <div class="signature-image"><?php if ($rpTtd): ?><img src="<?= $rpH($rpTtd) ?>" alt="Tanda tangan pasien"><?php endif; ?></div>
         <strong><u><?= $rpH($rpP['nama_pasien'] ?: '................................................') ?></u></strong>
      </div>
   </div>
</div>
