<?php
require '../../database/connect.php';

$no = $_GET['no']; // id_visit
$rm = $_GET['rm']; // no rekam medis
require 'getdataclinic.php';
// 🔹 Ambil data pasien
$qPasien = $koneksi->query("SELECT * FROM pasien_visit WHERE visit_ID='$no' AND id_customer='$id_customer' LIMIT 1");
$pasien = $qPasien->fetch_assoc();

// 🔹 Ambil data resep luar
$qResep = $koneksi->query("SELECT * FROM resep_luar WHERE id_visit='$no' ORDER BY id_resep ASC");
$resep = [];
while ($row = $qResep->fetch_assoc()) {
   $resep[] = $row;
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Cetak Resep Dokter</title>
   <style>
      body {
         font-family: "Times New Roman", serif;
         font-size: 13pt;
         margin: 20px;
      }

      .header {
         text-align: center;
         border-bottom: 2px solid #000;
         margin-bottom: 10px;
         padding-bottom: 5px;
      }

      .title {
         font-size: 16pt;
         font-weight: bold;
      }

      .info {
         margin-bottom: 10px;
      }

      .info td {
         padding: 3px 8px;
         vertical-align: top;
      }

      .resep {
         margin-top: 15px;
      }

      .resep td {
         padding: 4px;
         vertical-align: top;
      }

      .sign {
         margin-top: 50px;
         text-align: right;
      }

      .line {
         border-top: 1px dashed #000;
         margin-top: 5px;
      }
   </style>
</head>

<body onload="window.print()">

   <div class="header">
      <div class="title">Resep Dokter</div>
      <div><?= $datafaskes['clinic_name'] ?> <br>Alamat : <?= $datafaskes['faskes_address'] ?>, No.Telp : <?= $datafaskes['faskes_phone'] ?></div>
      <div class="line"></div>
   </div>

   <table class="info">
      <tr>
         <td><strong>No. RM</strong></td>
         <td>: <?= htmlspecialchars($rm) ?></td>
         <td><strong>Dokter</strong></td>
         <td>: <?= $pasien['id_doctor'] ?? '-' ?></td>
      </tr>
      <tr>
         <td><strong>Nama Pasien</strong></td>
         <td>: <?= $pasien['patient_name_pcare'] ?? '-' ?></td>
         <td><strong>Tgl Cetak</strong></td>
         <td>: <?= date('d-m-Y') ?></td>
      </tr>
      <tr>
         <td><strong>Usia / JK</strong></td>
         <td>:
            <?php
            if (!empty($pasien['patient_datebirth'])) {
               $tgl = new DateTime($pasien['patient_datebirth']);
               $umur = $tgl->diff(new DateTime())->y;
               echo $umur . " thn";
            }
            echo " / " . ($pasien['patient_gender'] ?? '-');
            ?>
         </td>
         <td></td>
         <td></td>
      </tr>
   </table>

   <div class="resep">
      <table width="100%" border="0">
         <?php foreach ($resep as $i => $r): ?>
            <tr>
               <td width="5%"><strong>R/</strong></td>
               <td>
                  <strong>Prescriptio:</strong> <?= nl2br(htmlspecialchars($r['prescriptio'])) ?><br>
                  <strong>Signatura:</strong> <?= nl2br(htmlspecialchars($r['signatura'])) ?><br>
                  <strong>Subscriptio:</strong> <?= nl2br(htmlspecialchars($r['subscriptio'])) ?><br>
                  <strong>Pro:</strong> <?= nl2br(htmlspecialchars($r['pro'])) ?>
               </td>
            </tr>
         <?php endforeach; ?>
      </table>
   </div>

   <div class="sign">
      <div>__________________________</div>
      <div><?= $pasien['id_doctor'] ?? 'Dokter' ?></div>
   </div>

</body>

</html>