<?php
require '../../database/connect.php';
$visit = $_GET['visit'];
$clinic = mysqli_query($koneksi, "SELECT * FROM setting_clinic LIMIT 1");
$dataclinic = mysqli_fetch_array($clinic);
$visitCheck = mysqli_query($koneksi, "SELECT * FROM pasien_visit INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient INNER JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor INNER JOIN ms_poli ON ms_poli.id_poli = pasien_visit.id_poli INNER JOIN visit_pemeriksaan ON visit_pemeriksaan.nomor_visit = pasien_visit.visit_ID WHERE pasien_visit.visit_ID='$visit'");
$visitData = mysqli_fetch_array($visitCheck);
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Resume Medis Pasien</title>
   <style>
      @page {
         size: A4;
         margin: 15mm;
      }

      body {
         font-family: "Arial", sans-serif;
         font-size: 12pt;
         color: #000;
      }

      .header {
         text-align: center;
         margin-bottom: 15px;
      }

      .header h1 {
         font-size: 18pt;
         margin: 0;
         text-transform: uppercase;
         letter-spacing: 1px;
      }

      .header p {
         margin: 2px 0;
         font-size: 10pt;
         color: #444;
      }

      .line {
         border-top: 2px solid #000;
         margin: 10px 0 20px;
      }

      .section {
         margin-bottom: 20px;
      }

      .section h2 {
         font-size: 13pt;
         margin-bottom: 8px;
         border-bottom: 1px solid #555;
         padding-bottom: 3px;
         text-transform: uppercase;
      }

      .data-grid {
         display: grid;
         grid-template-columns: 30% 70%;
         row-gap: 6px;
      }

      .label {
         font-weight: bold;
         color: #222;
      }

      .value {
         border-bottom: 1px dotted #aaa;
         padding-left: 5px;
      }

      table.med-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 5px;
         font-size: 11pt;
      }

      table.med-table th,
      table.med-table td {
         border: 1px solid #888;
         padding: 6px;
         text-align: left;
      }

      table.med-table th {
         background: #f2f2f2;
      }

      ul {
         margin: 0;
         padding-left: 18px;
      }

      .signature {
         margin-top: 40px;
         display: flex;
         justify-content: flex-end;
      }

      .signature div {
         text-align: center;
         width: 250px;
      }

      .signature p {
         margin: 60px 0 0;
         border-top: 1px solid #000;
         display: inline-block;
         padding-top: 3px;
      }
   </style>
</head>

<body>

   <div class="header">
      <h1>Resume Medis Pasien</h1>
      <p><?= $dataclinic['clinic_name'] ?> <br>Alamat : <?= $dataclinic['address'] ?>, Telp. <?= $dataclinic['phone_number'] ?></p>
      <p>Tanggal Cetak: <?= date('d/m/Y') ?></p>
   </div>
   <div class="line"></div>

   <div class="section">
      <h2>Identitas Pasien</h2>
      <div class="data-grid">
         <div class="label">Nomor RM</div>
         <div class="value"><?= $visitData['nomor_rm'] ?></div>
         <div class="label">Nama Pasien</div>
         <div class="value"><?= $visitData['patient_name'] ?></div>
         <div class="label">TTL</div>
         <div class="value"><?= $visitData['patient_place'] ?>/<?= $visitData['patient_datebirth'] ?></div>
         <div class="label">Jenis Kelamin</div>
         <div class="value"><?= $visitData['patient_gender'] ?></div>
         <div class="label">Nomor Visit</div>
         <div class="value"><?= $visit ?></div>
         <div class="label">Tanggal Kunjungan</div>
         <div class="value"><?= $visitData['visit_date'] ?> <?= $visitData['visit_time'] ?></div>
         <div class="label">Dokter</div>
         <div class="value"><?= $visitData['doctor_name'] ?></div>
         <div class="label">Poli / Layanan</div>
         <div class="value"><?= $visitData['poli_name'] ?> (<?= $visitData['source_hub'] ?>)</div>
      </div>
   </div>

   <div class="section">
      <h2>Pemeriksaan Awal</h2>
      <div class="data-grid">
         <div class="label">Kondisi Masuk</div>
         <div class="value"><?= $visitData['kondisi_masuk'] ?></div>
         <div class="label">Tekanan Darah</div>
         <div class="value"><?= $visitData['tekanan_darah'] ?> mmHg</div>
         <div class="label">Suhu</div>
         <div class="value"><?= $visitData['suhu'] ?> °C</div>
         <div class="label">Nadi</div>
         <div class="value"><?= $visitData['nadi'] ?> x/menit</div>
         <div class="label">Respirasi</div>
         <div class="value"><?= $visitData['respirasi'] ?> x/menit</div>
         <div class="label">Tinggi</div>
         <div class="value"><?= $visitData['tinggi'] ?> cm</div>
         <div class="label">Berat</div>
         <div class="value"><?= $visitData['berat'] ?> kg</div>
         <div class="label">BMI</div>
         <div class="value"><?= $visitData['bmi'] ?> (<?= $visitData['bmi_ket'] ?>)</div>
      </div>
      <p><b>Pemeriksaan Fisik:</b><br><?= nl2br($visitData['pemeriksaan_fisik']) ?></p>
   </div>

   <?php
   $stmt = $koneksi->prepare("
    SELECT *
    FROM visit_anamnesa
    INNER JOIN ms_anamnesa_detail
        ON ms_anamnesa_detail.id_ass = visit_anamnesa.id_anamnesa_detail
    WHERE nomor_visit=?
");
   $stmt->bind_param("s", $visit);
   $stmt->execute();
   $result = $stmt->get_result();

   $anamnesa = [];
   while ($row = $result->fetch_assoc()) {
      $anamnesa[] = $row;
   }
   $stmt->close();
   ?>

   <div class="section">
      <h2>Anamnesa</h2>

      <?php if (count($anamnesa) > 0): ?>

         <ul>
            <?php foreach ($anamnesa as $a): ?>
               <li>
                  <strong><?= $a['ass_name'] ?></strong><br>
                  Catatan : <?= $a['detail'] ?>
               </li>
            <?php endforeach; ?>
         </ul>

      <?php else: ?>

         <p><?= nl2br(htmlspecialchars($visitData['anamnesa'])) ?></p>

      <?php endif; ?>
   </div>

   <div class="section">
      <h2>Analisis</h2>
      <p><?= nl2br($visitData['analyst']) ?></p>
   </div>
   <div class="section">
      <h2>Riwayat Konsumsi obat</h2>
      <p><?= nl2br($visitData['riwayat_konsumsi']) ?></p>
   </div>
   <div class="section">
      <h2>Diagnosa</h2>
      <p><?= nl2br($visitData['diagnosa']) ?></p>
   </div>

   <div class="section">
      <h2>Terapi / Tindakan</h2>
      <?php
      $getterapi = mysqli_query($koneksi, "SELECT visit_terapi.*, ms_therapi.terapi_name FROM visit_terapi  INNER JOIN ms_therapi ON ms_therapi.id_terapi = visit_terapi.id_terapi WHERE visit_terapi.nomor_visit='$visit'");
      $terapi = mysqli_fetch_all($getterapi, MYSQLI_ASSOC);
      ?>
      <ul>
         <?php foreach ($terapi as $t): ?>
            <li><strong><?= $t['terapi_name'] ?> : </strong> <br> Catatan <?= $t['detail'] ?></li>
         <?php endforeach; ?>
      </ul>
   </div>

   <div class="section">
      <h2>Resep</h2>
      <h3>Resep Luar</h3>
      <?php
      $getresepluar = mysqli_query($koneksi, "SELECT * FROM resep_luar WHERE id_visit='$visit' ");
      $resep_luar = mysqli_fetch_array($getresepluar);
      ?>
      <?php if (!empty($resep_luar)): ?>
         <div class="data-grid">
            <div class="label">Nomor Resep</div>
            <div class="value"><?= $resep_luar['resep_number'] ?></div>
            <div class="label">Prescription</div>
            <div class="value"><?= nl2br($resep_luar['prescriptio']) ?></div>
            <div class="label">Signatura</div>
            <div class="value"><?= nl2br($resep_luar['signatura']) ?></div>
            <div class="label">Subscriptio</div>
            <div class="value"><?= nl2br($resep_luar['subscriptio']) ?></div>
            <div class="label">Pro</div>
            <div class="value"><?= nl2br($resep_luar['pro']) ?></div>
         </div>
      <?php else: ?>
         <p><i>Tidak ada</i></p>
      <?php endif; ?>

      <h3>Resep Internal</h3>
      <?php
      $getresepinternal = mysqli_query($koneksi, "SELECT * FROM permintaan_pharmacy INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy.id_pharmacy WHERE id_visit='$visit'");
      $resep_internal = mysqli_fetch_all($getresepinternal, MYSQLI_ASSOC);
      ?>

      <?php if (!empty($resep_internal)): ?>
         <table class="med-table">
            <tr>
               <th>Obat</th>
               <th>Qty</th>
               <th>Signa</th>
               <th>Catatan</th>
            </tr>
            <?php foreach ($resep_internal as $r): ?>
               <tr>
                  <td>Generic Name : <?= $r['pharmacy_name_generic'] ?> <br> Trade Name : <?= $r['pharmacy_name_trade'] ?></td>
                  <td><?= $r['qty'] ?></td>
                  <td><?= $r['signa'] ?></td>
                  <td><?= $r['catatan_permintaan'] ?></td>
               </tr>
            <?php endforeach; ?>
         </table>
      <?php else: ?>
         <p><i>Tidak ada</i></p>
      <?php endif; ?>
   </div>

   <div class="signature">
      <div>
         <p><?= $visitData['doctor_name'] ?></p>
      </div>
   </div>

</body>

</html>