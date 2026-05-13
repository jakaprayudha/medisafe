<?php
require '../../../database/connect.php';
require '../../admin/getdataclinic.php';

$saksi = mysqli_query($koneksi, "SELECT signature_user FROM ms_users WHERE uid_user = '" . $_SESSION['uid_user'] . "'");
$saksi = mysqli_fetch_assoc($saksi);
$signatureSaksi = $saksi['signature_user'] ?? null;
?>
<div class="form-surat-persetujuan">

   <style>
      .form-surat-persetujuan {
         width: 210mm;
         min-height: 297mm;
         margin: 0 auto;
         font-family: "Times New Roman", serif;
         padding: 0 10mm;
      }

      .form-surat-persetujuan header {
         text-align: center;
      }

      .ttd-kanan {
         text-align: left;
         /* 🔥 ini kunci */
      }

      .form-surat-persetujuan .judul {
         margin-top: 20px;
         text-align: center;
         font-weight: bold;
         font-size: 18px;
         text-decoration: underline;
      }

      .form-surat-persetujuan .section {
         margin-top: 20px;
         font-size: 16px;
         line-height: 1.45;
      }

      .form-surat-persetujuan .data {
         margin-left: 20px;
         margin-bottom: 15px;
         width: 100%;
      }

      .form-surat-persetujuan .data td {
         padding: 3px 5px;
         vertical-align: top;
         font-size: 16px;
      }

      .form-surat-persetujuan ol li {
         margin-bottom: 6px;
      }

      .form-surat-persetujuan .ttd-wrapper {
         display: flex;
         justify-content: space-between;
         margin-top: 5px;
      }

      .form-surat-persetujuan .kolom-ttd {
         width: 30%;
         text-align: center;
         font-size: 16px;
      }

      .form-surat-persetujuan .ttd-box {
         margin-top: 55px;
         border-top: 1px solid #000;
         padding-top: 5px;
         font-size: 16px;
      }

      .form-surat-persetujuan .tanggal {
         text-align: right;
         margin-top: 30px;
         font-size: 16px;
      }
   </style>

   <?php include 'kopsurat.php'; ?>

   <h3 class="judul">SURAT PERSETUJUAN TINDAKAN MEDIS</h3>

   <div class="section">

      <p>Saya yang bertandatangan di bawah ini:</p>

      <!-- DATA PENYETUJU (family / wali / diri sendiri) -->
      <table class="data">
         <tr>
            <td>Nama</td>
            <td>: <span id="nama_keluarga"></span></td>
         </tr>
         <tr>
            <td>Umur</td>
            <td>: <span id="umur_keluarga"></span> Tahun</td>
         </tr>
         <tr>
            <td>Jenis Kelamin</td>
            <td>: <span id="gender_keluarga"></span></td>
         </tr>
      </table>

      <p>
         Dengan ini menyatakan sesungguhnya telah memberikan persetujuan/penolakan untuk dilakukan tindakan medis berupa <strong>Opname dan Pengobatan</strong>, terhadap <span id="hubungan"></span>, dengan
         <b><span id="sp_tindakan"></span></b>
      </p>

      <!-- DATA PASIEN -->
      <table class="data">
         <tr>
            <td>Nama</td>
            <td>: <span id="sp_nama"></span></td>
         </tr>
         <tr>
            <td>Umur</td>
            <td>: <span id="sp_usia"></span></td>
         </tr>
         <tr>
            <td>Jenis Kelamin</td>
            <td>: <span id="sp_jk"></span></td>
         </tr>
         <tr>
            <td>No. Kartu</td>
            <td>: <span id="sp_bpjs"></span></td>
         </tr>
      </table>

      <p>Saya Juga telah menyatakan dengan sesungguhnya bahwa saya :</p>

      <ol>
         <li>Telah diberikan informasi dan penjelasan terhadap tindakan medis yang akan dilakukan tersebut.</li>
         <li>Telah memahami sepenuhnya seluruh informasi dan penjelasan yang diberikan oleh dokter.</li>
      </ol>

      <p>
         Demikian pernyataan persetujuan tindakan medis ini saya buat dengan penuh kesadaran dan tanpa paksaan
      </p>

   </div>

   <p class="tanggal"><span id="sp_tanggal"></span></p>
   <div class="ttd-wrapper">
      <div class="kolom-ttd">
         <p>Saksi</p>
         <img src="../../../uploads/ttd_faskes/<?= $signatureSaksi ?>" style="height:100px;" alt="">
         <div class="ttd-box"><?= $_SESSION['fullname'] ?></div>

      </div>


      <?php
      // Default signatureDokter
      $signatureDokter = null;
      $doctorName = null;
      // Try to get doctor name from GET param or fallback to JS fill
      if (isset($_GET['no']) && isset($_GET['rm'])) {
         // Try to get doctor name from pasien_visit
         $no = $_GET['no'];
         $rm = $_GET['rm'];
         $q = mysqli_query($koneksi, "SELECT id_doctor FROM pasien_visit WHERE visit_ID='" . mysqli_real_escape_string($koneksi, $no) . "' LIMIT 1");
         if ($row = mysqli_fetch_assoc($q)) {
            $doctorName = $row['id_doctor'];
         }
      }
      if ($doctorName) {
         // Remove 'dr. ', 'dr ', or 'dr' prefix if present
         $doctorNameClean = preg_replace('/^dr\.?\s*/i', '', $doctorName);
         // Find signature by fullname LIKE
         $dokter = mysqli_query($koneksi, "SELECT signature_user FROM ms_users WHERE fullname LIKE '%" . mysqli_real_escape_string($koneksi, $doctorNameClean) . "%'");
         $dokter = mysqli_fetch_assoc($dokter);
         $signatureDokter = $dokter['signature_user'] ?? null;
      }
      ?>
      <div class="kolom-ttd">
         <p>Dokter yang Merawat</p>
         <?php if ($signatureDokter): ?>
            <img src="../../../uploads/ttd_faskes/<?= $signatureDokter ?>" style="height:100px;" alt="">
         <?php else: ?>
            <span class="text-danger">Belum ada tanda tangan dokter</span>
         <?php endif; ?>
         <div class="ttd-box"><span id="sp_dokter"></span></div>
      </div>


      <div class="kolom-ttd ttd-kanan">
         <p>Yang Membuat Pernyataan</p>
         <div id="ttdPersetujuan">
            <!-- TTD image will be loaded here -->
         </div>
         <div class="ttd-box">
            <span id="sp_nama_penyetuju_ttd"></span>
         </div>
      </div>

   </div>

</div>

<script>
   document.addEventListener("DOMContentLoaded", function() {
      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      // GET DATA PASIEN
      fetch(`getpasien.php?no=${no}&rm=${rm}`)
         .then(res => res.json())
         .then(data => {
            if (!data) return;

            // Hitung umur
            let age = "";
            if (data.patient_datebirth) {
               const b = new Date(data.patient_datebirth);
               const t = new Date();
               age = (t.getFullYear() - b.getFullYear()) + " tahun";
            }
            document.getElementById("nama_keluarga").innerText = data.opname_keluarga_name;
            document.getElementById("umur_keluarga").innerText = data.opname_umur;
            document.getElementById("gender_keluarga").innerText = data.opname_gender;
            document.getElementById("hubungan").innerText = data.opname_hubungan_pasien;
            // Isi bagian PASIEN
            document.getElementById("sp_nama").innerText = data.patient_name;
            document.getElementById("sp_usia").innerText = age;
            document.getElementById("sp_jk").innerText = data.patient_gender;
            document.getElementById("sp_bpjs").innerText = data.patient_bpjs ?? "-";

            document.getElementById("sp_nama_penyetuju_ttd").innerText = data.opname_keluarga_name;


            // Dokter
            document.getElementById("sp_dokter").innerText = data.id_doctor;

            // Tindakan default
            document.getElementById("sp_tindakan").innerText = "Pemeriksaan / Pengobatan Medis";
            // 🔥 SET TANGGAL TTD
            if (data.visit_date) {
               let tanggal = formatTanggal(data.visit_date);

               document.getElementById("sp_tanggal").innerText =
                  "<?= $datafaskes['faskes_district'] ?>, " + tanggal;
            }
         });

      // ttd pasien
      fetch(`../../../controller/visit/getTTD.php?no=${no}`)
         .then(res => res.json())
         .then(resp => {
            let d = resp.data;
            let ttdContainer = document.getElementById('ttdPersetujuan');
            if (d && d.ttd && d.ttd !== 'null' && d.ttd !== '') {
               ttdContainer.innerHTML = `<img src="${d.ttd}" style="height:100px; border:1px solid #ccc; border-radius:6px; background:#fff;" alt="TTD">`;
            } else {
               ttdContainer.innerHTML = `<span class="text-danger">Belum ada tanda tangan</span>`;
            }
         })
         .catch(err => {
            let ttdContainer = document.getElementById('ttdPersetujuan');
            ttdContainer.innerHTML = `<span class="text-danger">Error mengambil tanda tangan</span>`;
         });
   });

   // 🔥 FORMAT TANGGAL INDONESIA
   function formatTanggal(tgl) {
      const bulan = [
         "Januari", "Februari", "Maret", "April", "Mei", "Juni",
         "Juli", "Agustus", "September", "Oktober", "November", "Desember"
      ];

      let d = new Date(tgl);
      let hari = d.getDate();
      let bln = bulan[d.getMonth()];
      let thn = d.getFullYear();

      return `${hari} ${bln} ${thn}`;
   }
</script>