<div class="form-trisemester3">

   <style>
      @page {
         size: A4 portrait;
         margin: 12mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
      }

      .title {
         font-weight: bold;
         font-size: 14pt;
         text-align: center;
         margin-bottom: 8px;
      }

      .subtitle {
         text-align: center;
         font-size: 12pt;
         margin-bottom: 15px;
      }

      table {
         width: 100%;
         border-collapse: collapse;
         margin-bottom: 10px;
      }

      td,
      th {
         border: 1px solid #000;
         padding: 4px 6px;
      }

      th {
         background: #f2f2f2;
         font-weight: bold;
      }

      .section-title {
         margin-top: 10px;
         font-weight: bold;
         text-transform: uppercase;
      }

      .signature {
         margin-top: 40px;
         text-align: right;
         padding-right: 30px;
      }
   </style>
   <?php
   require 'kopsurat.php';
   ?>
   <div class="title">PELAYANAN DOKTER</div>
   <div class="subtitle">Lembar Pemeriksaan Dokter Trimester III<br>(Usia Kehamilan 25–42 Minggu)</div>

   <!-- Pemeriksaan Fisik -->
   <div class="section-title">Pemeriksaan Fisik</div>
   <table>
      <tr>
         <td width="20%">Nama</td>
         <td colspan="3" id="nama_pasien"></td>
      </tr>
      <tr>
         <td>Keadaan Umum</td>
         <td colspan="3" id="keadaan_umum"></td>
      </tr>

      <tr>
         <td rowspan="7">Pemeriksaan</td>
         <td>Kepala</td>
         <td>Normal / Tidak Normal</td>
         <td id="kepala"></td>
      </tr>

      <tr>
         <td>Leher</td>
         <td>Normal / Tidak Normal</td>
         <td id="leher"></td>
      </tr>
      <tr>
         <td>Dada</td>
         <td>Normal / Tidak Normal</td>
         <td id="dada"></td>
      </tr>
      <tr>
         <td>Jantung</td>
         <td>Normal / Tidak Normal</td>
         <td id="jantung"></td>
      </tr>
      <tr>
         <td>Paru</td>
         <td>Normal / Tidak Normal</td>
         <td id="paru"></td>
      </tr>
      <tr>
         <td>Perut</td>
         <td>Normal / Tidak Normal</td>
         <td id="perut"></td>
      </tr>
      <tr>
         <td>Tungkai</td>
         <td>Normal / Tidak Normal</td>
         <td id="tungkai"></td>
      </tr>
   </table>

   <!-- USG -->
   <div class="section-title">USG Trimester III</div>
   <table>
      <tr>
         <td width="20%">EFW</td>
         <td id="efw"></td>
         <td width="20%">Tgl USG</td>
         <td id="tgl_usg"></td>
         <td width="20%">Kehamilan (minggu)</td>
         <td id="usia_kehamilan"></td>
      </tr>

      <tr>
         <td>BPD</td>
         <td id="bpd"></td>
         <td>HC</td>
         <td id="hc"></td>
         <td>AC</td>
         <td id="ac"></td>
      </tr>
      <tr>
         <td>TBJ</td>
         <td id="tbj"></td>
         <td>FL</td>
         <td id="fl"></td>
         <td>Usia Kehamilan</td>
         <td id="usia_kehamilan_usg"></td>
      </tr>
      <tr>
         <td>Jumlah Janin</td>
         <td id="jumlah_janin"></td>
         <td>His</td>
         <td id="his"></td>
         <td>DJJ</td>
         <td id="djj"></td>
      </tr>
      <tr>
         <td>Letak Janin</td>
         <td colspan="5" id="letak_janin"></td>
      </tr>
      <tr>
         <td>Plasenta</td>
         <td id="plasenta"></td>
         <td>Ketuban</td>
         <td colspan="3" id="ketuban"></td>
      </tr>
   </table>

   <!-- Pemeriksaan Lab -->
   <div class="section-title">Lingkari Pilihan Yang Sesuai</div>
   <table>
      <tr>
         <td width="25%">Hemoglobin</td>
         <td id="hb"></td>
         <td width="25%">Hasil</td>
         <td colspan="3"></td>
      </tr>

      <tr>
         <td>Gula Darah Puasa</td>
         <td id="gula_puasa"></td>
         <td>Hasil Pemeriksaan</td>
         <td colspan="3"></td>
      </tr>

      <tr>
         <td>Gula 2 jam post prandial</td>
         <td id="gula_2_jam"></td>
         <td>Hasil</td>
         <td id=""></td>
         <td colspan="2"></td>
      </tr>
   </table>

   <!-- Konseling -->
   <div class="section-title">Konseling</div>
   <table>
      <tr>
         <td width="35%">Pilihan Rencana Kontrasepsi</td>
         <td colspan="3" id="kontrasepsi"></td>
      </tr>
      <tr>
         <td>Metode</td>
         <td colspan="3" id="metode"></td>
      </tr>
      <tr>
         <td>Kesimpulan</td>
         <td colspan="3" id="kesimpulan" style="height:40px;"></td>
      </tr>
   </table>

   <div class="signature">
      Dokter yang memeriksa<br><br><br><br>
      ( <span id="dokter">_____________________</span> )
   </div>

</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      fetch("get_trimester3.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {

            if (res.status !== "success") return;

            const d = res.data;

            const set = (id, val) => {
               const el = document.getElementById(id);
               if (el) el.innerText = val ?? "";
            };

            // === Pemeriksaan Fisik ===
            set("nama_pasien", d.nama_pasien);
            set("keadaan_umum", d.keadaan_umum);
            set("kepala", d.kepala);
            set("leher", d.leher);
            set("dada", d.dada);
            set("jantung", d.jantung);
            set("paru", d.paru);
            set("perut", d.perut);
            set("tungkai", d.tungkai);

            // === USG ===
            set("efw", d.efw);
            set("tgl_usg", d.tgl_usg);
            set("usia_kehamilan", d.usia_kehamilan);
            set("bpd", d.bpd);
            set("hc", d.hc);
            set("ac", d.ac);
            set("tbj", d.tbj);
            set("fl", d.fl);
            set("usia_kehamilan_usg", d.usia_kehamilan_usg);
            set("jumlah_janin", d.jumlah_janin);
            set("his", d.his);
            set("djj", d.djj);
            set("letak_janin", d.letak_janin);
            set("plasenta", d.plasenta);
            set("ketuban", d.ketuban);

            // === LAB ===
            set("hb", d.hb);
            set("gula_puasa", d.gula_puasa);
            set("gula_2_jam", d.gula_2_jam);

            // === KONSELING ===
            set("kontrasepsi", d.kontrasepsi);
            set("metode", d.metode);
            set("kesimpulan", d.kesimpulan);

         });
   });
</script>