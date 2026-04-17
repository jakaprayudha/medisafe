<?php
$title = "Formulir Resume Medis";
$subtitle = "";
require '../../../database/connect.php';

$visit = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';
$checkdata = mysqli_query($koneksi, "SELECT * FROM pasien_visit LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient INNER JOIN permintaan_ranap ON permintaan_ranap.visit_ID_inpatient = pasien_visit.visit_ID LEFT JOIN ms_room ON ms_room.id_room = permintaan_ranap.id_room LEFT JOIN ms_room_bed ON ms_room_bed.id_bed = permintaan_ranap.id_bed LEFT JOIN ms_provider ON ms_provider.id_provider = pasien_visit.id_provider LEFT JOIN icd_10 ON icd_10.code = pasien_visit.diagnosa LEFT JOIN resume_medis ON resume_medis.visit_ID = pasien_visit.visit_ID AND resume_medis.nomor_rm = ms_patient.nomor_rm WHERE pasien_visit.visit_ID='$visit' AND ms_patient.nomor_rm='$rm'");
$dataresume = mysqli_fetch_array($checkdata);
?>

<style>
   .resume-container {
      max-width: 760px;
      margin: auto;
   }

   .resume-header {
      text-align: center;
      margin-bottom: 12px;
   }

   .resume-title {
      font-weight: bold;
      text-transform: uppercase;
      font-size: 13pt;
      margin: 2px 0;
   }

   .resume-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 11pt;
   }

   .resume-table td,
   .resume-table th {
      border: 1px solid #000;
      padding: 6px;
      vertical-align: top;
   }

   .resume-label {
      font-weight: bold;
      width: 22%;
      white-space: nowrap;
   }

   .resume-sign-area {
      margin-top: 35px;
      text-align: right;
   }

   .resume-doc-sign {
      margin-top: 45px;
      display: inline-block;
      text-align: center;
   }

   .resume-doc-line {
      border-top: 1px solid #000;
      width: 220px;
      padding-top: 4px;
      font-weight: bold;
      font-size: 11pt;
   }
</style>

<div class="resume-container">

   <?php include 'kopsurat.php'; ?>

   <div class="resume-header">
      <div class="resume-title">Resume Medis</div>
   </div>

   <table class="resume-table">
      <tr>
         <td class="resume-label">Nama Pasien</td>
         <td id="rm_name"><?php echo $dataresume['patient_name'] ?? ''; ?></td>
         <td class="resume-label">Tanggal Masuk</td>
         <td id="rm_tgl_masuk">
            <?= $dataresume['visit_date'] . ' ' . substr($dataresume['visit_time'], 0, 5); ?>
         </td>
      </tr>

      <tr>
         <td class="resume-label">Jenis Kelamin</td>
         <td id="rm_jk"><?php echo $dataresume['patient_gender'] ?? ''; ?></td>
         <td class="resume-label">Tanggal Keluar</td>
         <td id="rm_tgl_keluar"><?php echo $dataresume['tanggal_pulang'] . ' ' . $dataresume['jam_pulang'] ?? ''; ?></td>
      </tr>
      </tr>

      <tr>
         <td class="resume-label">No. Rekam Medis</td>
         <td id="rm_no_rm"><?php echo $dataresume['nomor_rm'] ?? ''; ?></td>
         <td class="resume-label">Ruang Rawat</td>
         <td id="rm_ruang"><?php echo $dataresume['room_name'] ?? ''; ?></td>
      </tr>

      <tr>
         <td class="resume-label">Tanggal Lahir</td>
         <td id="rm_tgl_lahir"><?php echo $dataresume['patient_datebirth'] ?? ''; ?></td>
         <td class="resume-label">Kamar / Kelas</td>
         <td id="rm_kelas"><?php echo $dataresume['bed_name'] ?? ''; ?></td>
      </tr>

      <tr>
         <td class="resume-label">Cara Bayar</td>
         <td id="rm_cara_bayar"><?php echo $dataresume['provider_name'] ?? ''; ?></td>
         <td class="resume-label">Dokter</td>
         <td id="rm_dpjp"><?php echo $dataresume['id_doctor'] ?? ''; ?></td>
      </tr>
   </table>

   <table class="resume-table">

      <tr>
         <td class="resume-label">Diagnosa Masuk</td>
         <td id="rm_dpjp"><?php echo $dataresume['diagnosa'] . ' - ' . $dataresume['icd10'] ?? ''; ?></td>
      </tr>

      <tr>
         <td class="resume-label">Indikasi Rawat Inap</td>
         <td colspan="3" id="rs_indikasi"><?php echo $dataresume['anamnesa'] ?? ''; ?></td>
      </tr>

      <tr>
         <td class="resume-label">Pemeriksaan Fisik</td>
         <td colspan="3" id="rs_fisik"><?php echo $dataresume['pemeriksaan_fisik'] ?? ''; ?>
         </td>
      </tr>

      <tr>
         <td class="resume-label">Diagnosa Utama</td>
         <td id="rm_dpjp"><?php echo $dataresume['diagnosa_utama'] ?? ''; ?></td>
      </tr>

      <tr>
         <td class="resume-label">Diagnosa Sekunder</td>
         <td id="rm_dpjp"><?php echo $dataresume['diagnosa_sekunder'] ?? ''; ?></td>
      </tr>
      <?php
      $no = $_GET['no'];
      $terapi = '';
      $gettiket = mysqli_query($koneksi, "SELECT * FROM permintaan_pharmacy WHERE id_visit='$no' AND status_obat_pulang=0");
      while ($tiket = mysqli_fetch_assoc($gettiket)) {
         $idvisit = $tiket['id_permintaan_farmasi'];
         $getobat = mysqli_query($koneksi, "SELECT * FROM permintaan_pharmacy_details INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy_details.id_pharmacy WHERE id_permintaan_farmasi='$idvisit'");
         while ($obat = mysqli_fetch_assoc($getobat)) {
            $terapi .= "- {$obat['pharmacy_name_generic']} {$obat['qty']} {$obat['signa']}\n";
         }
      }
      ?>
      <tr>
         <td class="resume-label">Terapi Selama di Klinik</td>
         <td colspan="3" style="white-space:pre-line" id="rs_terapi_rs"> <?= $dataresume['pemeriksaan_penunjang'] ?? $terapi ?>
         </td>
      </tr>

      <tr>
         <td class="resume-label">Alergi Obat</td>
         <td colspan="3" id="rs_alergi"><?php echo $dataresume['alergi_obat'] ?? ''; ?></td>
      </tr>
      <?php
      $no = $_GET['no'];
      $terapipulang = '';
      $gettiketpulang = mysqli_query($koneksi, "SELECT * FROM permintaan_pharmacy WHERE id_visit='$no' AND status_obat_pulang=1");
      while ($tiket = mysqli_fetch_assoc($gettiketpulang)) {
         $idvisit = $tiket['id_permintaan_farmasi'];
         $getobat = mysqli_query($koneksi, "SELECT * FROM permintaan_pharmacy_details INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy_details.id_pharmacy WHERE id_permintaan_farmasi='$idvisit'");
         while ($obat = mysqli_fetch_assoc($getobat)) {
            $terapipulang .= "- {$obat['pharmacy_name_generic']} {$obat['qty']} {$obat['signa']}\n";
         }
      }
      ?>
      <tr>
         <td class="resume-label">Terapi Pulang</td>
         <td colspan="3" style="white-space:pre-line" id="rs_terapi_pulang"> <?= $dataresume['instruksi'] ?? $terapipulang ?>

         </td>
      </tr>

      <tr>
         <td class="resume-label">Kondisi Pasien Saat Pulang</td>
         <td colspan="3" id="rs_kondisi_pulang"> <?= $dataresume['kondisi_pulang'] ?></td>
      </tr>

      <tr>
         <td class="resume-label">Cara Keluar</td>
         <td colspan="3" id="rs_cara_keluar"><?= $dataresume['cara_keluar'] ?></td>
      </tr>

      <tr>
         <td class="resume-label">Rencana Tindak Lanjut</td>
         <td colspan="3" id="rs_rencana"><?= $dataresume['rencana_tindak_lanjut'] ?>
         </td>
      </tr>

   </table>

   <div class="resume-sign-area">

      <div class="resume-sign-city">
         Deli Serdang, <?= date('d F Y', strtotime($dataresume['visit_date_out'] ?? date('Y-m-d'))) ?>
      </div>

      <div class="resume-sign-title">
         Dokter yang Merawat
      </div>

      <div class="resume-doc-sign">
         <img
            src="../../../uploads/ttd/drdevi.png"
            class="resume-sign-img" width="50px"
            alt="TTD Dokter">

         <div class="resume-doc-line" id="doctor_name"><?= $dataresume['doctor_name'] ?? '' ?></div>
      </div>

   </div>

</div>