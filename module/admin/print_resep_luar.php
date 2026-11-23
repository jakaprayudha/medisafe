<?php
/**
 * Cetak Resep Luar (A5) — dibuka dari module/admin/resep.
 * GET ?no=<visit_ID>&rm=<nomor_rm>
 * Perbaikan: query dirangkai langsung dari URL (SQL injection), resep tidak dibatasi faskes, umur & jenis kelamin
 * selalu kosong (diambil dari pasien_visit, padahal ada di ms_patient), nama klinik/dokter dicetak tanpa escape,
 * tanpa logo klinik, langsung print walau resep kosong.
 */
require '../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$petugas = (string) ($_SESSION['fullname'] ?? '');
session_write_close();
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
if ($idc <= 0) { http_response_code(401); exit('Sesi berakhir, silakan login ulang.'); }
if ($no === '') { http_response_code(422); exit('Parameter kunjungan tidak lengkap.'); }

$st = $koneksi->prepare("SELECT v.visit_ID, v.visit_date, v.id_doctor, v.patient_name_pcare, p.patient_name, p.nomor_rm, p.patient_gender,
      p.patient_datebirth, p.patient_address
   FROM pasien_visit v
   LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
   WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$d = $st->get_result()->fetch_assoc();
$st->close();
if (!$d) { http_response_code(404); exit('Kunjungan tidak ditemukan untuk faskes ini.'); }

$st = $koneksi->prepare("SELECT resep_number, prescriptio, signatura, subscriptio, pro, created_at FROM resep_luar WHERE id_visit = ? ORDER BY id_resep ASC");
$st->bind_param('s', $no);
$st->execute();
$resep = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

$st = $koneksi->prepare("SELECT sc.clinic_name, sc.image_clinic, f.faskes_address, f.faskes_city, f.faskes_phone
   FROM setting_clinic sc LEFT JOIN ms_faskes f ON f.id_clinic = sc.id WHERE sc.id_customer = ? LIMIT 1");
$st->bind_param('i', $idc);
$st->execute();
$klinik = $st->get_result()->fetch_assoc() ?: [];
$st->close();

$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$umur = '';
if (!empty($d['patient_datebirth']) && ($lahir = DateTime::createFromFormat('!Y-m-d', substr((string) $d['patient_datebirth'], 0, 10)))) {
   $selisih = $lahir->diff(new DateTime((string) ($d['visit_date'] ?: 'now')));
   $umur = $selisih->y > 0 ? $selisih->y . ' th' : ($selisih->m > 0 ? $selisih->m . ' bln' : $selisih->days . ' hari');
}
$nama = $d['patient_name'] ?: $d['patient_name_pcare'];
$logo = !empty($klinik['image_clinic']) ? '../../uploads/' . rawurlencode($klinik['image_clinic']) : '';
$alamat = trim(implode(', ', array_filter([$klinik['faskes_address'] ?? '', $klinik['faskes_city'] ?? ''])));
$dokter = trim((string) $d['id_doctor']);
$tglResep = $resep ? date('d/m/Y', strtotime($resep[0]['created_at'])) : date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Resep — <?= $h($nama) ?></title>
   <style>
      * { box-sizing: border-box; }
      body { margin: 0; background: #e5e7eb; color: #111; font: 12px Arial, Helvetica, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .toolbar { position: sticky; top: 0; display: flex; gap: 8px; justify-content: center; padding: 10px; background: #1e293b; z-index: 5; }
      .toolbar button { font: 600 13px Arial, sans-serif; padding: 7px 14px; border-radius: 6px; border: 1px solid #475569; background: #fff; cursor: pointer; }
      .toolbar .utama { background: #2563eb; border-color: #2563eb; color: #fff; }
      .kertas { width: 148mm; min-height: 210mm; margin: 16px auto; background: #fff; padding: 10mm; box-shadow: 0 2px 12px rgba(0, 0, 0, .15); display: flex; flex-direction: column; }
      .kop { display: flex; align-items: center; gap: 10px; border-bottom: 2px solid #111; padding-bottom: 6px; }
      .kop img { width: 48px; height: 48px; object-fit: contain; }
      .kop .k { flex: 1; }
      .kop .k b { font-size: 14px; text-transform: uppercase; display: block; }
      .kop .k div { font-size: 10px; color: #333; }
      .baris { display: flex; justify-content: space-between; font-size: 11px; margin: 8px 0 4px; }
      .dokter { font-size: 11px; margin-bottom: 6px; }
      .resep { flex: 1; padding: 6px 0; }
      .item { display: flex; gap: 8px; margin-bottom: 12px; break-inside: avoid; }
      .item .r { font: italic 700 20px Georgia, 'Times New Roman', serif; width: 30px; flex-shrink: 0; line-height: 1; }
      .item .pres { font-size: 13px; font-weight: 700; white-space: pre-line; }
      .item .sub { font-size: 12px; font-style: italic; white-space: pre-line; }
      .item .sig { font-size: 12.5px; margin-top: 2px; white-space: pre-line; }
      .item .sig b { font-family: Georgia, 'Times New Roman', serif; font-style: italic; }
      .item .garis { border-bottom: 1px dashed #999; margin-top: 6px; width: 70%; }
      .kosong { text-align: center; color: #666; padding: 30px 0; }
      .ttd { display: flex; justify-content: flex-end; margin-top: 10px; break-inside: avoid; }
      .ttd div { width: 200px; text-align: center; font-size: 11px; }
      .ttd .nm { margin-top: 48px; border-top: 1px solid #111; padding-top: 3px; font-weight: 700; }
      .pasien { border: 1px solid #111; border-radius: 4px; padding: 6px 8px; font-size: 11px; margin-top: 10px; display: grid; grid-template-columns: 70px 1fr 50px 1fr; gap: 2px 6px; }
      .pasien span { color: #333; }
      .kaki { margin-top: 6px; font-size: 9px; color: #555; text-align: center; }
      @media print { body { background: #fff; } .toolbar { display: none; } .kertas { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; } }
      @page { size: A5 portrait; margin: 8mm; }
   </style>
</head>

<body>
   <div class="toolbar"><button class="utama" onclick="window.print()">🖨️ Cetak</button><button onclick="window.close()">Tutup</button></div>
   <div class="kertas">
      <div class="kop">
         <?php if ($logo): ?><img src="<?= $h($logo) ?>" alt="Logo"><?php endif ?>
         <div class="k"><b><?= $h($klinik['clinic_name'] ?? 'Klinik') ?></b><?php if ($alamat): ?><div><?= $h($alamat) ?></div><?php endif ?><?php if (!empty($klinik['faskes_phone'])): ?><div>Telp. <?= $h($klinik['faskes_phone']) ?></div><?php endif ?></div>
      </div>
      <div class="baris">
         <span><?= $h(($klinik['faskes_city'] ?? '') ? $klinik['faskes_city'] . ', ' : '') ?><?= $h($tglResep) ?></span>
         <span>No. RM: <b><?= $h($d['nomor_rm'] ?: $rm) ?></b></span>
      </div>
      <div class="dokter">Dokter: <b><?= $h($dokter ?: '-') ?></b></div>

      <div class="resep">
         <?php if (!$resep): ?>
            <div class="kosong">Belum ada resep luar untuk kunjungan ini.</div>
         <?php endif ?>
         <?php foreach ($resep as $r): ?>
            <div class="item">
               <div class="r">R/</div>
               <div style="flex:1">
                  <div class="pres"><?= $h($r['prescriptio']) ?></div>
                  <?php if (trim((string) $r['subscriptio']) !== ''): ?><div class="sub"><?= $h($r['subscriptio']) ?></div><?php endif ?>
                  <div class="sig"><b>S.</b> <?= $h($r['signatura']) ?></div>
                  <?php if (trim((string) $r['pro']) !== ''): ?><div class="sub">pro: <?= $h($r['pro']) ?></div><?php endif ?>
                  <div class="garis"></div>
               </div>
            </div>
         <?php endforeach ?>
      </div>

      <div class="ttd">
         <div>Dokter penulis resep<div class="nm"><?= $h($dokter ?: '..............................') ?></div></div>
      </div>
      <div class="pasien">
         <span>Pro</span><b><?= $h($nama ?: '-') ?></b>
         <span>Umur</span><div><?= $h($umur ?: '-') ?><?= $d['patient_gender'] ? ' / ' . $h($d['patient_gender']) : '' ?></div>
         <span>Alamat</span><div style="grid-column: span 3"><?= $h($d['patient_address'] ?: '-') ?></div>
      </div>
      <div class="kaki">Dicetak <?= date('d/m/Y H:i') ?><?= $petugas ? ' oleh ' . $h($petugas) : '' ?> · Resep untuk ditebus di apotek luar.</div>
   </div>
   <?php if ($resep): ?><script>window.addEventListener('load', function() { setTimeout(function() { window.print(); }, 300); });</script><?php endif ?>
</body>

</html>
