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
         margin-top: 50px;
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
            <td>: <span id="">Regina Pardede</span></td>
         </tr>
         <tr>
            <td>Umur</td>
            <td>: <span id="sp_usia_penyetuju">56 Tahun</span></td>
         </tr>
         <tr>
            <td>Jenis Kelamin</td>
            <td>: <span id="sp_jk_penyetuju">Perempuan</span></td>
         </tr>
      </table>

      <p>
         Dengan ini menyatakan sesungguhnya telah memberikan persetujuan/penolakan untuk dilakukan tindakan medis berupa <strong>Opname dan Pengobatan</strong>, terhadap diri saya (Anak), dengan
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
            <td>No. BPJS</td>
            <td>: <span id=""></span>0001836618682</td>
         </tr>
      </table>

      <p>Saya menyatakan bahwa saya:</p>

      <ol>
         <li>Telah diberikan informasi dan penjelasan terhadap tindakan medis tersebut.</li>
         <li>Telah memahami sepenuhnya seluruh informasi yang diberikan oleh dokter.</li>
      </ol>

      <p>
         Demikian pernyataan persetujuan tindakan medis ini saya buat dengan penuh kesadaran dan tanpa paksaan
      </p>

   </div>

   <p class="tanggal">Tanjung Morawa, <span id="sp_tanggal">29 Oktober 2025</span></p>

   <div class="ttd-wrapper">

      <div class="kolom-ttd">
         <p>Saksi</p>
         <img src="../../../uploads/ttd/fitri.png" style="height:100px;" alt="">
         <div class="ttd-box">Fitri</div>

      </div>

      <div class="kolom-ttd">
         <p>Dokter yang Merawat</p>
         <img src="../../../uploads/ttd/drdevi.png" style="height:100px;" alt="">
         <div class="ttd-box"><span id="">dr. Devi Eka Pratiwi</span></div>
      </div>

      <div class="kolom-ttd">
         <p>Yang Membuat Pernyataan</p>
         <img src="../../../uploads/ttd/regina.png" style="height:100px;" alt="">
         <div class="ttd-box"><span id="sp_nama_penyetuju_ttd">Regina Pardede</span></div>
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

            // Isi bagian PASIEN
            document.getElementById("sp_nama").innerText = data.patient_name;
            document.getElementById("sp_usia").innerText = age;
            document.getElementById("sp_jk").innerText = data.patient_gender;
            document.getElementById("sp_bpjs").innerText = data.patient_bpjs ?? "-";

            // Isi bagian PENYETUJU (pakai data pasien dulu)
            document.getElementById("sp_nama_penyetuju").innerText = data.patient_name;
            document.getElementById("sp_usia_penyetuju").innerText = age;
            document.getElementById("sp_jk_penyetuju").innerText = data.patient_gender;
            document.getElementById("sp_nama_penyetuju_ttd").innerText = data.patient_name;

            // Dokter
            document.getElementById("sp_dokter").innerText = data.doctor_name ?? "........................";

            // Tindakan default
            document.getElementById("sp_tindakan").innerText = "Pemeriksaan / Pengobatan Medis";

            // Tanggal now
            document.getElementById("sp_tanggal").innerText = new Date().toISOString().substring(0, 10);
         });
   });
</script>