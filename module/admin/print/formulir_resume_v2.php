<?php
$title = "";
$subtitle = "";
require_once '../../../database/connect.php';

$visit = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';

$query = "SELECT * FROM pasien_visit 
          LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient 
          INNER JOIN permintaan_ranap ON permintaan_ranap.visit_ID_inpatient = pasien_visit.visit_ID 
          LEFT JOIN ms_room ON ms_room.id_room = permintaan_ranap.id_room 
          LEFT JOIN ms_room_bed ON ms_room_bed.id_bed = permintaan_ranap.id_bed 
          LEFT JOIN ms_provider ON ms_provider.id_provider = pasien_visit.id_provider 
          LEFT JOIN icd_10 ON icd_10.code = pasien_visit.diagnosa 
          LEFT JOIN resume_medis ON resume_medis.visit_ID = pasien_visit.visit_ID AND resume_medis.nomor_rm = ms_patient.nomor_rm 
          LEFT JOIN ms_users AS dokter ON REPLACE(REPLACE(REPLACE(REPLACE(LOWER(dokter.fullname), 'dr.', ''), 'dr ', ''), '.', ''), ' ', '') = 
       REPLACE(REPLACE(REPLACE(REPLACE(LOWER(pasien_visit.id_doctor), 'dr.', ''), 'dr ', ''), '.', ''), ' ', '')
          LEFT JOIN  pasien_triase ON pasien_triase.visit_ID = pasien_visit.visit_ID
          WHERE pasien_visit.visit_ID='$visit' AND ms_patient.nomor_rm='$rm'";

$checkdata = mysqli_query($koneksi, $query);
$dataresume = mysqli_fetch_array($checkdata) ?: [];
$diagnosamasuk = mysqli_query($koneksi, "SELECT pasien_visit.diagnosa, icd_10.code, icd_10.icd10 FROM pasien_visit LEFT JOIN icd_10 ON icd_10.code = pasien_visit.diagnosa  WHERE visit_ID='$visit'");
$datadiagnosamasuk = mysqli_fetch_array($diagnosamasuk);

?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Formulir Resume Medis</title>

   <style>
      * {
         box-sizing: border-box;
      }

      @page {
         size: A4;
         margin: 15mm 20mm;
      }

      body {
         font-family: "Times New Roman", serif;
         margin: 0;
         padding: 0;
         color: #000;
      }

      .resume-container {
         width: 100%;
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

      .resume-table tr {
         page-break-inside: avoid;
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
      }

      .resume-sign-area {
         margin-top: 35px;
         text-align: right;
         page-break-inside: avoid;
      }

      .resume-doc-sign {
         margin-top: 15px;
         display: inline-block;
         text-align: center;
         width: 220px;
      }

      .resume-sign-img {
         height: 70px;
         object-fit: contain;
         margin-bottom: 5px;
      }

      .resume-doc-line {
         border-top: 1px solid #000;
         width: 100%;
         padding-top: 4px;
         font-weight: bold;
         font-size: 11pt;
      }
   </style>
</head>

<body class="resume-body">

   <div class="resume-container">

      <?php include 'kopsurat.php'; ?>

      <div class="resume-header">
         <div class="resume-title">Resume Medis</div>
      </div>

      <table class="resume-table">

         <tr>
            <td class="resume-label">Nama Pasien</td>
            <td id="rm_name" style="width: 28%;"><?php echo $dataresume['patient_name'] ?? ''; ?></td>
            <td class="resume-label">Tanggal Masuk</td>
            <td id="rm_tgl_masuk" style="width: 28%;">
               <?= ($dataresume['visit_date'] ?? '') . ' ' . substr($dataresume['jam_masuk'] ?? '', 0, 5); ?>
            </td>
         </tr>
         <tr>
            <td class="resume-label">Jenis Kelamin</td>
            <td id="rm_jk"><?php echo $dataresume['patient_gender'] ?? ''; ?></td>
            <td class="resume-label">Tanggal Keluar</td>
            <td id="rm_tgl_keluar"><?php echo ($dataresume['tanggal_pulang'] ?? '') . ' ' . ($dataresume['jam_pulang'] ?? ''); ?></td>
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

         <tr>
            <td class="resume-label">Diagnosa Masuk</td>
            <td colspan="3" id="rm_dpjp_diagnosa"><?php echo ($datadiagnosamasuk['diagnosa'] ?? '') . ' - ' . ($datadiagnosamasuk['icd10'] ?? ''); ?></td>
         </tr>
         <tr>
            <td class="resume-label">Indikasi Rawat Inap</td>
            <td colspan="3" id="rs_indikasi"><?php echo $dataresume['anamnesa'] ?? ''; ?></td>
         </tr>
         <tr>
            <td class="resume-label">Pemeriksaan Fisik</td>
            <td colspan="3" id="rs_fisik" style="white-space:pre-line"><?php echo $dataresume['pemeriksaan_fisik'] ?? ''; ?></td>
         </tr>
         <tr>
            <td class="resume-label">Diagnosa Utama</td>
            <td colspan="3" id="rm_dpjp_utama"><?php echo $dataresume['diagnosa_utama'] ?? ''; ?></td>
         </tr>
         <?php
         $sekunder = trim($dataresume['diagnosa_sekunder']);
         $hasilDiagnosa = $sekunder;

         if (!empty($sekunder)) {

            // Pecah berdasarkan koma atau spasi
            $listKode = preg_split('/[\s,]+/', $sekunder);

            // Hapus data kosong
            $listKode = array_filter(array_map('trim', $listKode));

            $hasilArray = [];
            $stmtIcd = mysqli_prepare($koneksi, "SELECT code, icd10 as nama FROM icd_10 WHERE code = ?");

            foreach ($listKode as $kode) {

               // Jika ada format "K30 - Nama"
               if (strpos($kode, ' - ') !== false) {

                  $explodeDiagnosa = explode(' - ', $kode, 2);

                  $kodeOnly = trim($explodeDiagnosa[0]);
                  $namaOnly = trim($explodeDiagnosa[1] ?? '');

                  // Jika nama sudah ada
                  if (!empty($namaOnly)) {
                     $hasilArray[] = $kode;
                     continue;
                  }

                  $kode = $kodeOnly;
               }

               // Cari ke database ICD
               $dataIcd = null;
               if ($stmtIcd) {
                  mysqli_stmt_bind_param($stmtIcd, "s", $kode);
                  mysqli_stmt_execute($stmtIcd);

                  $result = mysqli_stmt_get_result($stmtIcd);
                  $dataIcd = $result ? mysqli_fetch_assoc($result) : null;
               }

               if ($dataIcd) {
                  $hasilArray[] = $dataIcd['code'] . ' - ' . $dataIcd['nama'];
               } else {
                  $hasilArray[] = $kode;
               }
            }

            if ($stmtIcd) {
               mysqli_stmt_close($stmtIcd);
            }

            // Hindari duplicate
            $hasilArray = array_unique($hasilArray);

            $hasilDiagnosa = implode(', ', $hasilArray);
         }
         ?>

         <tr>
            <td class="resume-label">Diagnosa Sekunder</td>
            <td colspan="3" id="rm_dpjp_sekunder">
               <?= htmlspecialchars($hasilDiagnosa) ?>
            </td>
         </tr>

         <?php
         $terapi = '';
         $getobat = mysqli_query($koneksi, "SELECT ms_pharmacy.pharmacy_name_generic, permintaan_pharmacy_details.qty, permintaan_pharmacy_details.signa
            FROM permintaan_pharmacy
            INNER JOIN permintaan_pharmacy_details ON permintaan_pharmacy_details.id_permintaan_farmasi = permintaan_pharmacy.id_permintaan_farmasi
            INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy_details.id_pharmacy
            WHERE permintaan_pharmacy.id_visit='$visit' AND permintaan_pharmacy.status_obat_pulang=0");
         while ($obat = mysqli_fetch_assoc($getobat)) {
            $terapi .= "- {$obat['pharmacy_name_generic']} {$obat['qty']} {$obat['signa']}\n";
         }
         ?>
         <tr>
            <td class="resume-label">Terapi Selama di Klinik</td>
            <td colspan="3" style="white-space:pre-line" id="rs_terapi_rs"><?= $dataresume['pemeriksaan_penunjang'] ?? $terapi ?></td>
         </tr>

         <tr>
            <td class="resume-label">Alergi Obat</td>
            <td colspan="3" id="rs_alergi"><?php echo $dataresume['alergi_obat'] ?? ''; ?></td>
         </tr>

         <?php
         $terapipulang = '';
         $getobatpulang = mysqli_query($koneksi, "SELECT ms_pharmacy.pharmacy_name_generic, permintaan_pharmacy_details.qty, permintaan_pharmacy_details.signa
            FROM permintaan_pharmacy
            INNER JOIN permintaan_pharmacy_details ON permintaan_pharmacy_details.id_permintaan_farmasi = permintaan_pharmacy.id_permintaan_farmasi
            INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy_details.id_pharmacy
            WHERE permintaan_pharmacy.id_visit='$visit' AND permintaan_pharmacy.status_obat_pulang=1");
         while ($obat = mysqli_fetch_assoc($getobatpulang)) {
            $terapipulang .= "- {$obat['pharmacy_name_generic']} {$obat['qty']} {$obat['signa']}\n";
         }
         ?>
         <tr>
            <td class="resume-label">Terapi Pulang</td>
            <td colspan="3" style="white-space:pre-line" id="rs_terapi_pulang"><?= $dataresume['instruksi'] ?? $terapipulang ?></td>
         </tr>

         <tr>
            <td class="resume-label">Kondisi Pasien Saat Pulang</td>
            <td colspan="3" id="rs_kondisi_pulang"><?= $dataresume['kondisi_pulang'] ?? '' ?></td>
         </tr>
         <tr>
            <td class="resume-label">Cara Keluar</td>
            <td colspan="3" id="rs_cara_keluar"><?= $dataresume['cara_keluar'] ?? '' ?></td>
         </tr>

         <!-- 
          Trik Table Split: Memotong tabel utama di sini.
          Tujuannya agar DOMPDF dapat mengelompokkan baris terakhir dan kolom tanda tangan
          menjadi satu kesatuan blok yang tidak dapat dipisahkan (page-break-inside: avoid).
        -->
      </table>

      <div style="page-break-inside: avoid;">
         <table class="resume-table" style="margin-top: -1px;">
            <tr>
               <td class="resume-label">Rencana Tindak Lanjut</td>
               <td colspan="3" id="rs_rencana"><?= $dataresume['rencana_tindak_lanjut'] ?? '' ?></td>
            </tr>
         </table>

         <div class="resume-sign-area">
            <?php
            $tanggalPulang = !empty($dataresume['tanggal_pulang'])
               ? $dataresume['tanggal_pulang']
               : ($dataresume['visit_date_out'] ?? date('Y-m-d'));
            ?>

            <div class="resume-sign-city">
               Deli Serdang, <?= date('d F Y', strtotime($tanggalPulang)) ?>
            </div>
            <div class="resume-sign-title">
               Dokter yang Merawat
            </div>
            <div class="resume-doc-sign">
               <?php
               $ttdFile = $dataresume['signature_user'] ?? '';
               $ttdPath = '../../../uploads/ttd_faskes/' . $ttdFile;
               $imgSrc = (!empty($ttdFile) && file_exists($ttdPath)) ? $ttdPath : '../../../uploads/ttd/default.png';
               ?>
               <img src="<?= $imgSrc ?>" class="resume-sign-img" alt="TTD Dokter">
               <div class="resume-doc-line" id="doctor_name"><?= $dataresume['id_doctor'] ?? '' ?></div>
            </div>
         </div>
      </div>

   </div>

</body>

</html>