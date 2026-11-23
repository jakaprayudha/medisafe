<?php
/**
 * Cetak Pemeriksaan Generalis Tubuh (A4) — dibuka dari module/admin/pemeriksaan_generalis_tubuh.
 * GET ?no=<visit_ID>&rm=<nomor_rm>. Berdiri sendiri (tidak masuk cetak gabungan / bundling).
 */
require '../../database/connect.php';
require_once '../../controller/ranap/generalisTubuhService.php';

$idc = (int) ($_SESSION['id_customer'] ?? 0);
$petugas = (string) ($_SESSION['fullname'] ?? '');
session_write_close();
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
if ($idc <= 0) { http_response_code(401); exit('Sesi berakhir, silakan login ulang.'); }
if ($no === '' || $rm === '') { http_response_code(422); exit('Parameter kunjungan tidak lengkap.'); }

$st = $koneksi->prepare("SELECT p.patient_name, p.nomor_rm, p.patient_gender, p.patient_datebirth, v.visit_ID, v.visit_date, v.visit_time,
      v.id_doctor, v.id_poli, v.pemeriksaan_fisik, prv.provider_name
   FROM pasien_visit v
   INNER JOIN ms_patient p ON p.id_patient = v.id_patient
   LEFT JOIN ms_provider prv ON prv.id_provider = v.id_provider
   WHERE v.visit_ID = ? AND p.nomor_rm = ? AND v.id_customer = ? LIMIT 1");
$st->bind_param('ssi', $no, $rm, $idc);
$st->execute();
$d = $st->get_result()->fetch_assoc();
$st->close();
if (!$d) { http_response_code(404); exit('Kunjungan tidak ditemukan untuk faskes ini.'); }

$st = $koneksi->prepare("SELECT sc.clinic_name, sc.image_clinic, f.faskes_address, f.faskes_city, f.faskes_phone
   FROM setting_clinic sc LEFT JOIN ms_faskes f ON f.id_clinic = sc.id WHERE sc.id_customer = ? LIMIT 1");
$st->bind_param('i', $idc);
$st->execute();
$klinik = $st->get_result()->fetch_assoc() ?: [];
$st->close();

$fields = generalisTubuhFields();
$values = generalisTubuhValues($d['pemeriksaan_fisik'] ?? '');
$terisi = count(array_filter($values, fn($v) => $v !== ''));
$abnormal = [];
foreach ($fields as $group => $gf) foreach ($gf as $k => $f) if ($values[$k] !== '' && $values[$k] !== $f['normal']) $abnormal[] = $group . ' – ' . $f['label'] . ': ' . $values[$k];

$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$umur = '';
if (!empty($d['patient_datebirth']) && ($lahir = DateTime::createFromFormat('!Y-m-d', substr((string) $d['patient_datebirth'], 0, 10)))) {
   $umur = $lahir->diff(new DateTime((string) ($d['visit_date'] ?: 'now')))->y . ' th';
}
$tgl = fn($x) => $x ? date('d/m/Y', strtotime($x)) : '-';
$logo = !empty($klinik['image_clinic']) ? '../../uploads/' . rawurlencode($klinik['image_clinic']) : '';
$alamat = trim(implode(', ', array_filter([$klinik['faskes_address'] ?? '', $klinik['faskes_city'] ?? ''])));
$dokter = trim((string) $d['id_doctor']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Pemeriksaan Generalis — <?= $h($d['patient_name']) ?></title>
   <style>
      * { box-sizing: border-box; }
      body { margin: 0; background: #e5e7eb; color: #111; font: 12px Arial, Helvetica, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .toolbar { position: sticky; top: 0; display: flex; gap: 8px; justify-content: center; padding: 10px; background: #1e293b; z-index: 5; }
      .toolbar button { font: 600 13px Arial, sans-serif; padding: 7px 14px; border-radius: 6px; border: 1px solid #475569; background: #fff; cursor: pointer; }
      .toolbar .utama { background: #2563eb; border-color: #2563eb; color: #fff; }
      .kertas { width: 210mm; margin: 16px auto; background: #fff; padding: 12mm; box-shadow: 0 2px 12px rgba(0, 0, 0, .15); }
      .kop { display: flex; align-items: center; gap: 12px; border-bottom: 2px solid #111; padding-bottom: 8px; }
      .kop img { width: 58px; height: 58px; object-fit: contain; }
      .kop .k { flex: 1; }
      .kop .k b { font-size: 15px; text-transform: uppercase; display: block; }
      .kop .k div { font-size: 11px; color: #333; }
      h1 { font-size: 15px; text-align: center; margin: 12px 0 8px; letter-spacing: .04em; }
      .identitas { display: grid; grid-template-columns: 1fr 1fr; gap: 2px 24px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; margin-bottom: 10px; }
      .identitas div { display: flex; }
      .identitas span { width: 110px; color: #333; flex-shrink: 0; }
      .grid { columns: 2; column-gap: 14px; }
      .grup { break-inside: avoid; border: 1px solid #cbd5e1; border-radius: 6px; margin-bottom: 10px; overflow: hidden; }
      .grup b.j { display: block; background: #f1f5f9; padding: 4px 8px; font-size: 12px; border-bottom: 1px solid #cbd5e1; }
      .grup table { width: 100%; border-collapse: collapse; }
      .grup td { padding: 3px 8px; border-bottom: 1px dashed #e5e7eb; vertical-align: top; }
      .grup tr:last-child td { border-bottom: 0; }
      .grup td:first-child { width: 48%; color: #333; }
      .ab { font-weight: 700; text-decoration: underline; }
      .kosong { color: #777; }
      .ringkas { border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 10px; margin-bottom: 10px; }
      .ttd { display: flex; justify-content: flex-end; margin-top: 22px; break-inside: avoid; }
      .ttd div { width: 220px; text-align: center; }
      .ttd .garis { margin-top: 56px; border-top: 1px solid #111; padding-top: 3px; font-weight: 700; }
      .kaki { margin-top: 8px; font-size: 9px; color: #444; }
      @media print { body { background: #fff; } .toolbar { display: none; } .kertas { width: auto; margin: 0; padding: 0; box-shadow: none; } }
      @page { size: A4 portrait; margin: 12mm; }
   </style>
</head>

<body>
   <div class="toolbar"><button class="utama" onclick="window.print()">🖨️ Cetak</button><button onclick="window.close()">Tutup</button></div>
   <div class="kertas">
      <div class="kop">
         <?php if ($logo): ?><img src="<?= $h($logo) ?>" alt="Logo"><?php endif ?>
         <div class="k"><b><?= $h($klinik['clinic_name'] ?? 'Klinik') ?></b><?php if ($alamat): ?><div><?= $h($alamat) ?></div><?php endif ?><?php if (!empty($klinik['faskes_phone'])): ?><div>Telp. <?= $h($klinik['faskes_phone']) ?></div><?php endif ?></div>
      </div>
      <h1>PEMERIKSAAN GENERALIS TUBUH</h1>

      <div class="identitas">
         <div><span>Nama pasien</span>: <b>&nbsp;<?= $h($d['patient_name']) ?></b></div>
         <div><span>No. kunjungan</span>: &nbsp;<?= $h($d['visit_ID']) ?></div>
         <div><span>No. RM</span>: &nbsp;<?= $h($d['nomor_rm']) ?></div>
         <div><span>Tgl. kunjungan</span>: &nbsp;<?= $h($tgl($d['visit_date'])) ?></div>
         <div><span>JK / umur</span>: &nbsp;<?= $h(trim(($d['patient_gender'] ?? '') . ($umur ? ' / ' . $umur : ''), ' /')) ?: '-' ?></div>
         <div><span>Penjamin</span>: &nbsp;<?= $h($d['provider_name'] ?: '-') ?></div>
      </div>

      <div class="ringkas">
         <b>Ringkasan:</b> <?= $terisi ?> dari <?= count($values) ?> pemeriksaan terisi.
         <?= $abnormal ? 'Temuan abnormal: <b>' . $h(implode('; ', $abnormal)) . '</b>.' : ($terisi ? 'Tidak ada temuan abnormal.' : 'Pemeriksaan belum diisi.') ?>
      </div>

      <div class="grid">
         <?php foreach ($fields as $group => $gf): ?>
            <div class="grup">
               <b class="j"><?= $h($group) ?></b>
               <table>
                  <?php foreach ($gf as $k => $f): $v = $values[$k]; ?>
                     <tr><td><?= $h($f['label']) ?></td><td class="<?= $v === '' ? 'kosong' : ($v !== $f['normal'] ? 'ab' : '') ?>"><?= $v === '' ? '-' : $h($v) ?></td></tr>
                  <?php endforeach ?>
               </table>
            </div>
         <?php endforeach ?>
      </div>

      <div class="ttd">
         <div><?= $h($klinik['faskes_city'] ?: '') ?><?= !empty($klinik['faskes_city']) ? ', ' : '' ?><?= date('d/m/Y') ?><br>Dokter pemeriksa<div class="garis"><?= $h($dokter ?: '..............................') ?></div></div>
      </div>
      <div class="kaki">Dicetak <?= date('d/m/Y H:i') ?><?= $petugas ? ' oleh ' . $h($petugas) : '' ?> · Hasil bergaris bawah = di luar nilai normal.</div>
   </div>
   <script>window.addEventListener('load', function() { setTimeout(function() { window.print(); }, 300); });</script>
</body>

</html>
