<?php
$title = "";
$subtitle = "";
require_once '../../../database/connect.php';
require_once __DIR__ . '/helper_ttd_dokter.php';

$visit = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';

$query = "SELECT * FROM pasien_visit 
          LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient 
          INNER JOIN permintaan_ranap ON permintaan_ranap.visit_ID_inpatient = pasien_visit.visit_ID 
          LEFT JOIN ms_room ON ms_room.id_room = permintaan_ranap.id_room 
          LEFT JOIN ms_room_bed ON ms_room_bed.id_bed = permintaan_ranap.id_bed 
          LEFT JOIN ms_provider ON ms_provider.id_provider = pasien_visit.id_provider 
          LEFT JOIN icd_10 ON icd_10.code = pasien_visit.diagnosa 
          LEFT JOIN resume_medis ON resume_medis.id_resume = (SELECT MAX(r2.id_resume) FROM resume_medis r2 WHERE r2.visit_ID = pasien_visit.visit_ID) 
          LEFT JOIN ms_users AS dokter ON REPLACE(REPLACE(REPLACE(REPLACE(LOWER(dokter.fullname), 'dr.', ''), 'dr ', ''), '.', ''), ' ', '') = 
       REPLACE(REPLACE(REPLACE(REPLACE(LOWER(pasien_visit.id_doctor), 'dr.', ''), 'dr ', ''), '.', ''), ' ', '')
          LEFT JOIN  pasien_triase ON pasien_triase.visit_ID = pasien_visit.visit_ID
          WHERE pasien_visit.visit_ID = ? AND pasien_visit.id_customer = ?
          LIMIT 1";
// Perbaikan: dulu dirangkai dari URL (SQL injection), tanpa batas faskes, difilter nomor RM dari URL (format beda ->
// kosong) dan resume_medis di-join lewat nomor RM sehingga bila dobel yang tercetak baris lama -> kini resume terbaru.
$rsIdc = (int) ($_SESSION['id_customer'] ?? 0);
$rsSt = $koneksi->prepare($query);
$rsSt->bind_param('si', $visit, $rsIdc);
$rsSt->execute();
$dataresume = $rsSt->get_result()->fetch_array() ?: [];
$rsSt->close();
$dataresume['signature_user'] = cariTtdDokter($koneksi, $dataresume['id_doctor'] ?? '', $_SESSION['id_customer'] ?? null) ?? '';
$rsSt = $koneksi->prepare("SELECT pasien_visit.diagnosa, icd_10.code, icd_10.icd10 FROM pasien_visit LEFT JOIN icd_10 ON icd_10.code = pasien_visit.diagnosa WHERE visit_ID = ? AND pasien_visit.id_customer = ?");
$rsSt->bind_param('si', $visit, $rsIdc);
$rsSt->execute();
$datadiagnosamasuk = $rsSt->get_result()->fetch_array();
$rsSt->close();

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
            <td id="rm_name" style="width: 28%;"><?= htmlspecialchars((string) ($dataresume['patient_name'] ?? '')) ?></td>
            <td class="resume-label">Tanggal Masuk</td>
            <td id="rm_tgl_masuk" style="width: 28%;">
               <?= htmlspecialchars(($dataresume['visit_date'] ?? '') . ' ' . substr($dataresume['jam_masuk'] ?? '', 0, 5)) ?>
            </td>
         </tr>
         <tr>
            <td class="resume-label">Jenis Kelamin</td>
            <td id="rm_jk"><?= htmlspecialchars((string) ($dataresume['patient_gender'] ?? '')) ?></td>
            <td class="resume-label">Tanggal Keluar</td>
            <td id="rm_tgl_keluar"><?= htmlspecialchars(($dataresume['tanggal_pulang'] ?? '') . ' ' . ($dataresume['jam_pulang'] ?? '')) ?></td>
         </tr>
         <tr>
            <td class="resume-label">No. Rekam Medis</td>
            <td id="rm_no_rm"><?= htmlspecialchars((string) ($dataresume['nomor_rm'] ?? '')) ?></td>
            <td class="resume-label">Ruang Rawat</td>
            <td id="rm_ruang"><?= htmlspecialchars((string) ($dataresume['room_name'] ?? '')) ?></td>
         </tr>
         <tr>
            <td class="resume-label">Tanggal Lahir</td>
            <td id="rm_tgl_lahir"><?= htmlspecialchars((string) ($dataresume['patient_datebirth'] ?? '')) ?></td>
            <td class="resume-label">Kamar / Kelas</td>
            <td id="rm_kelas"><?= htmlspecialchars((string) ($dataresume['bed_name'] ?? '')) ?></td>
         </tr>
         <tr>
            <td class="resume-label">Cara Bayar</td>
            <td id="rm_cara_bayar"><?= htmlspecialchars((string) ($dataresume['provider_name'] ?? '')) ?></td>
            <td class="resume-label">Dokter</td>
            <td id="rm_dpjp"><?= htmlspecialchars((string) ($dataresume['id_doctor'] ?? '')) ?></td>
         </tr>

         <tr>
            <td class="resume-label">Diagnosa Masuk</td>
            <td colspan="3" id="rm_dpjp_diagnosa"><?php echo ($datadiagnosamasuk['diagnosa'] ?? '') . ' - ' . ($datadiagnosamasuk['icd10'] ?? ''); ?></td>
         </tr>
         <tr>
            <td class="resume-label">Indikasi Rawat Inap</td>
            <td colspan="3" id="rs_indikasi"><?= htmlspecialchars((string) ($dataresume['anamnesa'] ?? '')) ?></td>
         </tr>
         <tr>
            <td class="resume-label">Pemeriksaan Fisik</td>
            <td colspan="3" id="rs_fisik" style="white-space:pre-line"><?= htmlspecialchars((string) ($dataresume['pemeriksaan_fisik'] ?? '')) ?></td>
         </tr>
         <tr>
            <td class="resume-label">Diagnosa Utama</td>
            <td colspan="3" id="rm_dpjp_utama"><?= htmlspecialchars((string) ($dataresume['diagnosa_utama'] ?? '')) ?></td>
         </tr>
         <?php
         $sekunder = trim((string) ($dataresume['diagnosa_sekunder'] ?? ''));
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
         // dihitung hanya bila resume belum berisi & kunjungan milik faskes (tabel farmasi tanpa index, ±0,8 dtk)
         if ($dataresume && ($dataresume['pemeriksaan_penunjang'] ?? null) === null) {
            $obSt = $koneksi->prepare("SELECT COALESCE(NULLIF(m.pharmacy_name_generic, ''), d.item_name) AS nama, d.qty, d.signa
               FROM permintaan_pharmacy t
               JOIN permintaan_pharmacy_details d ON d.id_permintaan_farmasi = t.id_permintaan_farmasi
               LEFT JOIN ms_pharmacy m ON m.id_pharmacy = d.id_pharmacy
               WHERE t.id_visit = ? AND t.id_customer = ? AND t.status_obat_pulang = 0");
            $obSt->bind_param('si', $visit, $rsIdc);
            $obSt->execute();
            foreach ($obSt->get_result()->fetch_all(MYSQLI_ASSOC) as $obat) $terapi .= "- {$obat['nama']} {$obat['qty']} {$obat['signa']}\n";
            $obSt->close();
         }
         ?>
         <tr>
            <td class="resume-label">Terapi Selama di Klinik</td>
            <td colspan="3" style="white-space:pre-line" id="rs_terapi_rs"><?= htmlspecialchars((string) ($dataresume['pemeriksaan_penunjang'] ?? $terapi)) ?></td>
         </tr>

         <tr>
            <td class="resume-label">Alergi Obat</td>
            <td colspan="3" id="rs_alergi"><?= htmlspecialchars((string) ($dataresume['alergi_obat'] ?? '')) ?></td>
         </tr>

         <?php
         $terapipulang = '';
         // dihitung hanya bila resume belum berisi & kunjungan milik faskes (tabel farmasi tanpa index, ±0,8 dtk)
         if ($dataresume && ($dataresume['instruksi'] ?? null) === null) {
            $obSt = $koneksi->prepare("SELECT COALESCE(NULLIF(m.pharmacy_name_generic, ''), d.item_name) AS nama, d.qty, d.signa
               FROM permintaan_pharmacy t
               JOIN permintaan_pharmacy_details d ON d.id_permintaan_farmasi = t.id_permintaan_farmasi
               LEFT JOIN ms_pharmacy m ON m.id_pharmacy = d.id_pharmacy
               WHERE t.id_visit = ? AND t.id_customer = ? AND t.status_obat_pulang = 1");
            $obSt->bind_param('si', $visit, $rsIdc);
            $obSt->execute();
            foreach ($obSt->get_result()->fetch_all(MYSQLI_ASSOC) as $obat) $terapipulang .= "- {$obat['nama']} {$obat['qty']} {$obat['signa']}\n";
            $obSt->close();
         }
         ?>
         <tr>
            <td class="resume-label">Terapi Pulang</td>
            <td colspan="3" style="white-space:pre-line" id="rs_terapi_pulang"><?= htmlspecialchars((string) ($dataresume['instruksi'] ?? $terapipulang)) ?></td>
         </tr>

         <tr>
            <td class="resume-label">Kondisi Pasien Saat Pulang</td>
            <td colspan="3" id="rs_kondisi_pulang"><?= htmlspecialchars((string) ($dataresume['kondisi_pulang'] ?? '')) ?></td>
         </tr>
         <tr>
            <td class="resume-label">Cara Keluar</td>
            <td colspan="3" id="rs_cara_keluar"><?= htmlspecialchars((string) ($dataresume['cara_keluar'] ?? '')) ?></td>
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
               <td colspan="3" id="rs_rencana"><?= htmlspecialchars((string) ($dataresume['rencana_tindak_lanjut'] ?? '')) ?></td>
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
               <div class="resume-doc-line" id="doctor_name"><?= htmlspecialchars((string) ($dataresume['id_doctor'] ?? '')) ?></div>
            </div>
         </div>
      </div>

   </div>

</body>

</html>