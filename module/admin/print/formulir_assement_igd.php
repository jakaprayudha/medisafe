<?php
require '../../../database/connect.php';

$visit = $_GET['no'] ?? '';
$checkpasien = mysqli_query(
   $koneksi,
   "SELECT * FROM pasien_visit
   INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient
   INNER JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor
   WHERE pasien_visit.visit_ID='$visit'"
);

$datapasien = mysqli_fetch_array($checkpasien);
?>

<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Asesmen Awal IGD</title>

   <style>
      /* =========================
   CSS KHUSUS FILE INI
   ========================= */
      .igd-assessment {
         margin: 20px;
         font-family: Arial, Helvetica, sans-serif;
         font-size: 12px;
         color: #000;
      }

      .igd-assessment table {
         border-collapse: collapse;
         width: 100%;
      }

      .igd-assessment th,
      .igd-assessment td {
         border: 1px solid #000;
         padding: 6px;
         vertical-align: top;
      }

      .igd-assessment th {
         background-color: #f2f2f2;
         font-weight: bold;
      }

      .igd-assessment .no-border td {
         border: none;
      }

      .igd-assessment .text-right {
         text-align: right;
      }

      .igd-assessment .text-center {
         text-align: center;
      }

      .igd-assessment img {
         max-height: 100px;
      }

      .igd-assessment .checkbox {
         margin-bottom: 4px;
      }

      .igd-assessment .ttd {
         width: 300px;
         float: right;
         text-align: center;
         margin-top: 40px;
      }

      .igd-assessment h3 {
         margin: 0;
      }
   </style>
</head>

<body>

   <div class="igd-assessment">

      <div class="text-right">
         <u>IGD-<?= htmlspecialchars($visit) ?></u>
      </div>

      <table>
         <tr>
            <td style="width:10%">
               <img src="../../../assets/images/logos/logotutun.png" alt="Logo">
            </td>
            <td class="text-center">
               <h3>ASESMEN AWAL MEDIS PASIEN GAWAT DARURAT</h3>
            </td>
            <td>
               <table class="no-border">
                  <tr>
                     <td>Nama Pasien</td>
                     <td>: <?= $datapasien['patient_name'] ?></td>
                  </tr>
                  <tr>
                     <td>Nomor RM</td>
                     <td>: <?= $datapasien['nomor_rm'] ?></td>
                  </tr>
                  <tr>
                     <td>Tanggal Lahir</td>
                     <td>: <?= $datapasien['patient_datebirth'] ?></td>
                  </tr>
                  <tr>
                     <td>Jenis Kelamin</td>
                     <td>: <?= $datapasien['patient_gender'] ?></td>
                  </tr>
                  <tr>
                     <td>Datang ke IGD</td>
                     <td>: <?= $datapasien['visit_date'] ?> <?= $datapasien['visit_time'] ?></td>
                  </tr>
                  <tr>
                     <td>Layanan</td>
                     <td>: IGD</td>
                  </tr>
                  <tr>
                     <td>Jaminan</td>
                     <td>: BPJS</td>
                  </tr>
                  <tr>
                     <td>Dokter</td>
                     <td>: <?= $datapasien['doctor_name'] ?></td>
                  </tr>
               </table>
            </td>
         </tr>
      </table>

      <br>

      <table>
         <tr>
            <th>Airway</th>
            <th>Breathing</th>
            <th>Circulation</th>
            <th>Disability</th>
            <th>Vital Sign</th>
         </tr>
         <tr>
            <td>
               <div class="checkbox"><input type="checkbox" checked> Bebas</div>
               <div class="checkbox"><input type="checkbox"> Gargling</div>
               <div class="checkbox"><input type="checkbox"> Stridor</div>
               <div class="checkbox"><input type="checkbox"> Terintubasi</div>
            </td>
            <td>
               <div class="checkbox"><input type="checkbox" checked> Spontan</div>
               <div class="checkbox"><input type="checkbox"> Tachipneu</div>
               <div class="checkbox"><input type="checkbox"> Dispneu</div>
               <div class="checkbox"><input type="checkbox"> Apneu</div>
            </td>
            <td>
               Nadi: <b>Kuat</b><br>
               CRT: <b>&lt; 2 detik</b><br>
               Turgor: <b>Baik</b>
            </td>
            <td>
               GCS E: 4<br>
               GCS V: 5<br>
               GCS M: 6
            </td>
            <td>
               TD: 120/80 mmHg<br>
               Nadi: 82 x/menit<br>
               RR: 20 x/menit<br>
               Suhu: 36.7 °C
            </td>
         </tr>
      </table>

      <br>

      <table>
         <tr>
            <th style="width:20%">ANAMNESIS</th>
            <td>
               <input type="checkbox" checked> Auto Anamnesa
               <input type="checkbox"> Allo Anamnesa
            </td>
         </tr>
      </table>

      <table class="no-border">
         <tr>
            <td style="width:25%">Keluhan Utama</td>
            <td>: DEMAM, LEMAS, PUSING, MUAL, MUNTAH</td>
         </tr>
         <tr>
            <td>Riwayat Penyakit Sekarang</td>
            <td>: Pusing dan demam</td>
         </tr>
         <tr>
            <td>Riwayat Penyakit Dahulu</td>
            <td>: Hipertensi</td>
         </tr>
         <tr>
            <td>Riwayat Pengobatan</td>
            <td>: Paracetamol, Cetirizine</td>
         </tr>
         <tr>
            <td>Riwayat Alergi</td>
            <td>: Tidak ada</td>
         </tr>
      </table>

      <br>

      <table>
         <tr>
            <th colspan="3">DIAGNOSA</th>
         </tr>
         <tr>
            <td style="width:20%">Diagnosa Kerja</td>
            <td>:</td>
            <td>R50.9 Fever, unspecified</td>
         </tr>
         <tr>
            <td>Diagnosa Banding</td>
            <td>:</td>
            <td>O21 Excessive vomiting</td>
         </tr>
      </table>

      <br>

      <table>
         <tr>
            <th style="width:20%">TERAPI</th>
            <td>:</td>
            <td>
               IVFD RL, Injeksi Ranitidine, Paracetamol, Cetirizine
            </td>
         </tr>
      </table>

      <br>

      <table>
         <tr>
            <th style="width:20%">Perawatan Lanjutan</th>
            <td>
               <input type="checkbox" checked> Rawat Inap
               <input type="checkbox"> Rawat Intensive
            </td>
         </tr>
      </table>

      <div class="ttd">
         Tg Morawa, 29 Oktober 2025<br><br>
         Dokter<br><br><br>
         <img src="../../../uploads/ttd/drdevi.png" alt="TTD"><br>
         <b><?= $datapasien['doctor_name'] ?></b><br>
         Dokter
      </div>

   </div>

</body>

</html>