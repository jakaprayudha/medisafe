<div class="form-skrining-hipotiroid">
   <style>
      @page {
         size: A4;
         margin: 20mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         line-height: 1.5;
         color: #000;
      }

      .title {
         text-align: center;
         font-weight: bold;
         font-size: 16pt;
         margin-bottom: 20px;
         text-decoration: underline;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 10px;
      }

      td {
         padding: 3px 5px;
         vertical-align: top;
         font-size: 12pt;
      }

      .label {
         width: 150px;
      }

      .indent {
         margin-left: 20px;
      }

      .signature-box {
         margin-top: 60px;
         width: 100%;
         display: flex;
         justify-content: space-between;
      }

      .sig-col {
         width: 40%;
         text-align: center;
      }

      .isi {
         font-weight: bold;
      }

      .signature-line {
         margin-top: 60px;
         border-top: 1px solid #000;
         padding-top: 5px;
         font-size: 11pt;
      }
   </style>
   <?php
   require 'kopsurat.php';
   ?>
   <h3 style="text-align: center;"> Pernyataan Tidak Dilakukan Skrinning Hipotiroid Kongenital</h3>

   <p>Saya yang bertandatangan di bawah ini :</p>

   <table>
      <tr>
         <td class="label">Nama</td>
         <td>: <span id="p_nama" class="isi"></span></td>
      </tr>
      <tr>
         <td>NIP</td>
         <td>: <span id="p_nip" class="isi"></span></td>
      </tr>
      <tr>
         <td>Pangkat / Golongan</td>
         <td>: <span id="p_pangkat" class="isi"></span></td>
      </tr>
      <tr>
         <td>Jabatan</td>
         <td>: <span id="p_jabatan" class="isi"></span></td>
      </tr>
   </table>
   <p>Menyatakan bahwa :</p>

   <table>
      <tr>
         <td class="label">Nama Bayi</td>
         <td>: <span id="b_nama" class="isi"></span></td>
      </tr>

      <tr>
         <td>Jenis Kelamin</td>
         <td>: <span id="b_jk" class="isi"></span></td>
      </tr>

      <tr>
         <td>Tanggal Lahir</td>
         <td>: <span id="b_lahir" class="isi"></span></td>
      </tr>

      <tr>
         <td>No. Rekam Medis</td>
         <td>: <span id="b_rm" class="isi"></span></td>
      </tr>

      <tr>
         <td>Nama Ibu</td>
         <td>: <span id="ibu_nama" class="isi"></span></td>
      </tr>
   </table>

   <p style="text-align: justify;">
      Tidak dapat dilakukan Skrinning Hipotiroid Kongenital dengan alasan: bayi tidak memungkinkan diambil sampel SHK dibuktikan dengan surat keterangan tenaga kesehatan yang merawat.
   </p>
   <div class="signature-box">
      <div class="sig-col">
         Mengetahui,<br>
         Petugas Fasyankes
         <div class="signature-line">( <span id="p_nama2"></span> )</div>
      </div>

      <div class="sig-col">
         <span id="ttd_lokasi"></span>, <span id="ttd_tanggal"></span><br>
         Orang Tua / Wali
         <div class="signature-line">( __________________________ )</div>
      </div>
   </div>
</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no"); // visit
      const rm = url.get("rm"); // rekam medis

      if (!no || !rm) return;

      fetch("get_skrining.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {

            if (res.status !== "success") return;

            const d = res.data;

            const set = (id, val) => {
               const el = document.getElementById(id);
               if (el) el.textContent = val ?? "";
            };

            // PETUGAS
            set("p_nama", d.petugas_nama);
            set("p_nip", d.petugas_nip);
            set("p_pangkat", d.petugas_pangkat);
            set("p_jabatan", d.petugas_jabatan);
            set("p_nama2", d.petugas_nama); // tanda tangan

            // BAYI
            set("b_nama", d.bayi_nama);
            set("b_jk", d.bayi_jk === "L" ? "Laki-laki" : "Perempuan");
            set("b_lahir", d.bayi_tgllahir);
            set("b_rm", d.nomor_rm);

            // IBU
            set("ibu_nama", d.ibu_nama);

            // TTD
            set("ttd_lokasi", d.lokasi_ttd);
            set("ttd_tanggal", d.tanggal_ttd);
         });
   });
</script>