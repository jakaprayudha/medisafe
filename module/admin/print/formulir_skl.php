<div class="form-skl">
   <style>
      @page {
         size: 216mm 356mm;
         margin: 15mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         color: #000;
         line-height: 1.5;
         margin: 0;
      }

      .title {
         text-align: center;
         font-weight: bold;
         font-size: 18pt;
         text-decoration: underline;
         margin-bottom: 5px;
      }

      .nomor {
         text-align: center;
         margin-bottom: 15px;
      }

      table.info {
         width: 100%;
         margin-bottom: 8px;
      }

      table.info td {
         padding: 3px 4px;
         vertical-align: top;
      }

      .indent {
         text-indent: 35px;
         text-align: justify;
         margin-bottom: 10px;
      }

      /* SIGNATURE AREA */
      .signature-wrapper {
         width: 100%;
         margin-top: 35px;
         display: flex;
         justify-content: space-between;
      }

      .signature-left {
         width: 40%;
         text-align: center;
      }

      .signature-right {
         width: 40%;
         text-align: center;
      }

      .signature-img {
         width: 160px;
         height: 80px;
         margin: 5px auto;
         background-size: contain;
         background-repeat: no-repeat;
         background-position: center;
      }

      .qr-box {
         width: 120px;
         height: 120px;
         margin: 5px auto;
         border: 1px solid #000;
         background-size: cover;
         background-repeat: no-repeat;
         background-position: center;
      }

      /* CAP KAKI */
      .footprint-container {
         display: flex;
         justify-content: space-between;
         margin-top: 20px;
      }

      .footprint-box {
         width: 48%;
         border: 1px solid #000;
         padding: 5px;
         text-align: center;
      }

      .footprint-img {
         width: 100%;
         height: 200px;
         border: 1px solid #000;
         background-size: contain;
         background-repeat: no-repeat;
         background-position: center;
      }
   </style>

   <?php require 'kopsurat.php'; ?>

   <div class="title">SURAT KETERANGAN LAHIR</div>
   <div class="nomor">No : ……/SKL/KTS/…/…/20…</div>

   <p class="indent">
      Yang bertanda tangan dibawah ini, Bidan <b>KLINIK TUTUN SEHATI</b> menyatakan bahwa pada tanggal
      <span id="tgl_lahir">..................................</span> jam
      <span id="jam_lahir">..................</span> WIB, Pasien kami :
   </p>

   <table class="info">
      <tr>
         <td>1 Nama</td>
         <td>: <span id="ibu_nama"></span></td>
      </tr>
      <tr>
         <td>2 Umur</td>
         <td>: <span id="ibu_umur"></span></td>
      </tr>
      <tr>
         <td>3 Nama Suami</td>
         <td>: <span id="suami_nama"></span></td>
      </tr>
      <tr>
         <td>4 Agama</td>
         <td>: <span id="ibu_agama"></span></td>
      </tr>
      <tr>
         <td>5 Pekerjaan</td>
         <td>: <span id="ibu_pekerjaan"></span></td>
      </tr>
      <tr>
         <td>6 Alamat</td>
         <td>: <span id="ibu_alamat"></span></td>
      </tr>
   </table>

   <p class="indent">
      Telah melahirkan anak <b><span id="anak_ke"></span></b> pada hari
      <b><span id="hari_lahir"></span></b>,
      <b><span id="jk"></span></b>,
      dengan berat badan <b><span id="bb"></span> Kg</b>,
      panjang badan <b><span id="pb"></span> cm</b>,
      diberi nama : <b><span id="nama_bayi"></span></b>.
   </p>

   <p class="indent">
      Demikianlah Surat Keterangan ini dibuat untuk digunakan sebagaimana mestinya.
   </p>

   <!-- SIGNATURE AREA -->
   <div class="signature-wrapper">

      <div class="signature-left">
         <b>QR Verifikasi</b><br>
         <div id="qr_code" class="qr-box"></div>
      </div>

      <div class="signature-right">
         Tj. Morawa, <span id="tgl_surat"></span><br>
         Yang menerangkan:<br><br>
         <div id="ttd_bidan" class="signature-img"></div>
         <b>Hj. SALMIAH, AM.Keb</b>
      </div>

   </div>

   <!-- FOOTPRINT -->
   <div class="footprint-container">
      <div class="footprint-box">
         <div class="box-label">Cap Kaki Kiri</div>
         <div id="cap_kiri" class="footprint-img"></div>
      </div>

      <div class="footprint-box">
         <div class="box-label">Cap Kaki Kanan</div>
         <div id="cap_kanan" class="footprint-img"></div>
      </div>
   </div>

</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const no = new URLSearchParams(window.location.search).get("no");
      const rm = new URLSearchParams(window.location.search).get("rm");
      if (!no || !rm) return;

      fetch("get_skl.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {

            if (res.status !== "success") return;

            const d = res.data;
            const set = (id, val) => {
               let el = document.getElementById(id);
               if (el) el.textContent = val ?? "";
            };

            // DATA IBU
            set("ibu_nama", d.nama_ibu);
            set("ibu_umur", d.umur_ibu);
            set("suami_nama", d.nama_suami);
            set("ibu_agama", d.agama);
            set("ibu_pekerjaan", d.pekerjaan);
            set("ibu_alamat", d.alamat);

            // DATA KELAHIRAN
            set("hari_lahir", d.hari_lahir);
            set("tgl_lahir", d.tanggal_lahir);
            set("jam_lahir", d.jam_lahir);
            set("jk", d.jenis_kelamin === "L" ? "Laki-laki" : "Perempuan");
            set("bb", d.berat_badan);
            set("pb", d.panjang_badan);
            set("nama_bayi", d.nama_bayi);
            set("anak_ke", "Pertama");
            set("tgl_surat", d.tanggal_lahir);

            // CAP KAKI
            if (d.cap_kiri)
               document.getElementById("cap_kiri").style.backgroundImage = `url('${d.cap_kiri}')`;

            if (d.cap_kanan)
               document.getElementById("cap_kanan").style.backgroundImage = `url('${d.cap_kanan}')`;

            // TTD BIDAN
            if (d.ttd_bidan)
               document.getElementById("ttd_bidan").style.backgroundImage = `url('${d.ttd_bidan}')`;

            // QR CODE
            if (d.qr_verifikasi)
               document.getElementById("qr_code").style.backgroundImage = `url('${d.qr_verifikasi}')`;
         });
   });
</script>