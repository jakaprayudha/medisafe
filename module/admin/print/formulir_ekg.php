<?php

require_once '../../../database/connect.php';

$no = $_GET['no'] ?? null;
$rm = $_GET['rm'] ?? null;

$ekg = null;
if ($no && $rm) {
   $stmtEkg = mysqli_prepare(
      $koneksi,
      "SELECT er.*, mp.patient_name, mp.patient_datebirth
       FROM ekg_results er
       INNER JOIN ms_patient mp ON er.nomor_rm = mp.nomor_rm
       WHERE er.visit_ID = ? AND mp.nomor_rm = ?
       LIMIT 1"
   );
   if ($stmtEkg) {
      mysqli_stmt_bind_param($stmtEkg, 'ss', $no, $rm);
      mysqli_stmt_execute($stmtEkg);
      $resEkg = mysqli_stmt_get_result($stmtEkg);
      $ekg    = $resEkg ? mysqli_fetch_assoc($resEkg) : null;
      if ($stmtEkg) mysqli_stmt_close($stmtEkg);
   }
}

$ekgUsia = '';
if ($ekg && !empty($ekg['patient_datebirth'])) {
   $diffEkg = (new DateTime($ekg['patient_datebirth']))->diff(new DateTime());
   $ekgUsia = $diffEkg->y . ' tahun ' . $diffEkg->m . ' bulan ' . $diffEkg->d . ' hari';
}
?>
<div class="ekgprint-wrapper">
   <style>
      @page {
         size: A4;
         margin: 15mm;
      }

      body.ekgprint-body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         margin: 0;
         padding: 0;
         background: #fff;
      }

      /* ============================
         QR CODE (Clean No Conflict)
      ============================ */
      /* Reset total biar ga ketarik style lain */
      .ekgprint-qr-wrap,
      .ekgprint-qr-wrap * {
         all: unset;
      }

      .ekgprint-qr-wrap {
         position: absolute;
         top: 20mm;
         right: 15mm;
         width: 120px;
         height: 120px;
         display: none;
      }

      .ekgprint-qr-img {
         width: 120px;
         height: 120px;
         object-fit: cover;
         display: none;
      }

      /* kalau hide → hilang total */
      .ekgprint-q-hide {
         display: none !important;
         visibility: hidden !important;
         opacity: 0 !important;
         width: 0 !important;
         height: 0 !important;
         overflow: hidden !important;
         margin: 0 !important;
         padding: 0 !important;
         border: none !important;
      }

      /* ============================
         KONTEN EKG
      ============================ */
      .ekgprint-kop {
         text-align: center;
         margin-bottom: 5px;
      }

      .ekgprint-kop h1 {
         font-size: 26pt;
         font-weight: bold;
         margin: 0;
      }

      .ekgprint-kop h2 {
         font-size: 18pt;
         margin-top: -3px;
         font-weight: bold;
      }

      .ekgprint-alamat {
         font-size: 11pt;
         margin-top: 3px;
      }

      .ekgprint-hr {
         border: none;
         border-top: 2px solid #000;
         margin: 10px 0 20px 0;
      }

      .ekgprint-title {
         text-align: center;
         font-size: 16pt;
         font-weight: bold;
         text-transform: uppercase;
         margin-bottom: 15px;
      }

      table.ekgprint-identitas {
         width: 100%;
         margin-bottom: 15px;
         font-size: 12pt;
      }

      table.ekgprint-identitas td {
         padding: 4px 3px;
      }

      .ekgprint-line {
         border-bottom: 1px dotted #666;
         display: inline-block;
         width: 260px;
         height: 16px;
      }

      .ekgprint-box {
         width: 100%;
         height: 350px;
         border: 2px solid #000;
         background: #f7f7f7;
         margin-bottom: 15px;
         display: flex;
         justify-content: center;
         align-items: center;
         overflow: hidden;
      }

      .ekgprint-img {
         width: 100%;
         height: 100%;
         object-fit: contain;
      }

      .ekgprint-note-title {
         font-weight: bold;
         margin-bottom: 5px;
      }

      .ekgprint-note {
         width: 100%;
         height: 120px;
         border: 1px solid #000;
         padding: 8px;
         font-size: 12pt;
         white-space: pre-line;
      }

      .ekgprint-footer {
         text-align: right;
         margin-top: 30px;
      }

      .ekgprint-ttd {
         width: 180px;
         height: 80px;
         object-fit: contain;
         margin-bottom: -10px;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>

   <!-- QR Code (tampil hanya jika ada) -->
   <?php if (!empty($ekg['qr_code'])): ?>
      <div class="ekgprint-qr-wrap" style="display: block;">
         <img class="ekgprint-qr-img" style="display: block;" src="../../../<?= htmlspecialchars($ekg['qr_code']) ?>" alt="QR Code">
      </div>
   <?php endif; ?>

   <!-- KOP -->
   <?php require 'kopsurat.php'; ?>

   <hr class="ekgprint-hr">

   <div class="ekgprint-title">HASIL PEMERIKSAAN EKG</div>

   <!-- IDENTITAS PASIEN -->
   <table class="ekgprint-identitas">
      <tr>
         <td width="28%">Nama Pasien</td>
         <td>: <span class="ekgprint-line"><?= htmlspecialchars($ekg['patient_name'] ?? '') ?></span></td>
      </tr>
      <tr>
         <td>No. Rekam Medis</td>
         <td>: <span class="ekgprint-line"><?= htmlspecialchars($ekg['nomor_rm'] ?? '') ?></span></td>
      </tr>
      <tr>
         <td>Usia</td>
         <td>: <span class="ekgprint-line"><?= htmlspecialchars($ekgUsia) ?></span></td>
      </tr>
      <tr>
         <td>Tanggal Pemeriksaan</td>
         <td>: <span class="ekgprint-line"><?= htmlspecialchars($ekg['tanggal_pemeriksaan'] ?? '') ?></span></td>
      </tr>
   </table>

   <!-- GAMBAR EKG -->
   <?php if (!empty($ekg['ekg1'])): ?>
      <div class="ekgprint-box">
         <img class="ekgprint-img" src="../../../<?= htmlspecialchars($ekg['ekg1']) ?>" alt="EKG 1">
      </div>
   <?php endif; ?>

   <?php if (!empty($ekg['ekg2'])): ?>
      <div class="ekgprint-box">
         <img class="ekgprint-img" src="../../../<?= htmlspecialchars($ekg['ekg2']) ?>" alt="EKG 2">
      </div>
   <?php endif; ?>

   <!-- CATATAN -->
   <div class="ekgprint-note-title">Interpretasi / Catatan Dokter:</div>
   <div class="ekgprint-note"><?= nl2br(htmlspecialchars($ekg['interpretasi'] ?? '')) ?></div>

   <!-- FOOTER -->
   <div class="ekgprint-footer">
      Dokter Pemeriksa:<br><br>
      <?php if (!empty($ekg['ttd_dokter'])): ?>
         <img class="ekgprint-ttd" src="../../../<?= htmlspecialchars($ekg['ttd_dokter']) ?>" alt="TTD Dokter">
      <?php endif; ?>
      <br>
      <b><?= htmlspecialchars($ekg['dokter'] ?? '') ?></b>
   </div>
</div>

