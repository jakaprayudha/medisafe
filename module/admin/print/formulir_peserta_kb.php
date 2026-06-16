<div class="form-card-kb">
   <style>
      @page {
         size: A4 potrait;
         margin: 10mm;
      }

      body {
         font-family: Arial;
         margin: 0;
         padding: 0;
      }

      .page {
         width: 297mm;
         padding: 10mm;
         box-sizing: border-box;
      }

      .two-col {
         display: flex;
         gap: 15px;
      }

      .col {
         width: 50%;
         border: 1px solid #333;
         padding: 15px;
      }

      .title {
         font-size: 20px;
         font-weight: bold;
         text-align: center;
      }

      .row {
         margin-bottom: 6px;
         font-size: 13px;
      }

      .label {
         display: inline-block;
         width: 140px;
      }

      .underline {
         display: inline-block;
         border-bottom: 1px solid #000;
         width: 200px;
         height: 14px;
      }

      .small-box {
         width: 12px;
         height: 12px;
         border: 1px solid #000;
         display: inline-block;
         text-align: center;
         line-height: 12px;
      }

      .section-title {
         font-weight: bold;
         border-bottom: 1px solid #000;
         margin-top: 10px;
         font-size: 14px;
      }

      .boxgroup {
         letter-spacing: 6px;
         font-size: 14px;
      }

      table.jadwal {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px;
         font-size: 13px;
      }

      table.jadwal td,
      table.jadwal th {
         border: 1px solid #000;
         padding: 5px;
         height: 22px;
      }
   </style> <?php require 'kopsurat.php' ?>

   <div class="two-col">

      <!-- LEFT -->
      <div class="col">
         <div class="title">KARTU PESERTA KB</div>

         <div class="row"><span class="label">Nama Peserta KB</span><span class="underline"><span id="card_nama_peserta"></span></span></div>
         <div class="row"><span class="label">Nama Suami/Istri</span><span class="underline"><span id="card_nama_pasangan"></span></span></div>
         <div class="row"><span class="label">Tgl Lahir / Umur</span><span class="underline"><span id="card_tgllahir_umur"></span></span></div>
         <div class="row"><span class="label">Alamat Peserta KB</span><span class="underline"><span id="card_alamat"></span></span></div>

         <div class="section-title">Tahapan KB</div>
         <div class="row">
            <span class="small-box" id="box_baru"></span> Peserta Baru<br>
            <span class="small-box" id="box_lama"></span> Peserta Lama<br>
            <span class="small-box" id="box_pindahan"></span> Pindahan
         </div>

         <div class="section-title">Status Peserta JKN</div>
         <div class="row">
            <span class="small-box" id="box_jkn"></span> Peserta JKN<br>
            <span class="small-box" id="box_nonjkn"></span> Bukan Peserta JKN
         </div>

         <div class="row"><span class="label">Nomor Sakit</span><span class="underline"><span id="card_no_sakit"></span></span></div>
         <div class="row"><span class="label">Nama Faskes KB</span><span class="underline"><span id="card_nama_faskes"></span></span></div>
         <div class="row"><span class="label">Nomor Kode Faskes</span><span class="boxgroup" id="card_kode_faskes"></span></div>

         <br>
         <div class="row">Petugas Pelayanan KB:</div>
         <div class="underline"><span id="card_petugas"></span></div>
      </div>

      <!-- RIGHT -->
      <div class="col">

         <div class="section-title">Metode Kontrasepsi</div>
         <div class="row"><span class="underline" style="width: 150px;"><span id="card_metode"></span></span></div>

         <div class="row">Tgl Mulai : <span class="boxgroup" id="card_mulai"></span></div>
         <div class="row">Tgl Dilepas : <span class="boxgroup" id="card_selesai"></span></div>

         <div class="row">(Bila IUD / Implan / Kondom)</div>

         <div class="section-title">Catatan Pelayanan</div>
         <table class="jadwal">
            <tr>
               <th width="40%">DIPESAN KEMBALI</th>
               <th>KETERANGAN</th>
            </tr>

            <?php for ($i = 1; $i <= 12; $i++): ?>
               <tr>
                  <td id="cat<?= $i ?>_dipesan"></td>
                  <td id="cat<?= $i ?>_ket"></td>
               </tr>
            <?php endfor; ?>
         </table>

      </div>

   </div>
</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {
      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      fetch("getcard.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {
            if (res.status !== "success") return;
            const d = res.data;

            const set = (id, val) => {
               const el = document.getElementById(id);
               if (el) el.innerText = val ?? "";
            };

            set("card_nama_peserta", d.nama_peserta);
            set("card_nama_pasangan", d.nama_pasangan);
            set("card_tgllahir_umur", `${d.tgl_lahir} / ${d.umur} Tahun`);
            set("card_alamat", d.alamat);

            // Tahap KB
            document.getElementById("box_baru").innerText = d.tahap_kb === "BARU" ? "√" : "";
            document.getElementById("box_lama").innerText = d.tahap_kb === "LAMA" ? "√" : "";
            document.getElementById("box_pindahan").innerText = d.tahap_kb === "PINDAHAN" ? "√" : "";

            // JKN
            document.getElementById("box_jkn").innerText = d.status_jkn === "JKN" ? "√" : "";
            document.getElementById("box_nonjkn").innerText = d.status_jkn === "NONJKN" ? "√" : "";

            set("card_no_sakit", d.nomor_sakit);
            set("card_nama_faskes", d.nama_faskes);
            set("card_kode_faskes", d.kode_faskes);

            set("card_metode", d.metode);
            set("card_mulai", d.tgl_mulai);
            set("card_selesai", d.tgl_selesai);

            set("card_petugas", d.petugas ?? "");

            // ================= CATATAN PELAYANAN =====================
            for (let i = 1; i <= 12; i++) {
               const ket = d[`catatan_${i}`];

               document.getElementById(`cat${i}_dipesan`).innerText =
                  ket && ket.toLowerCase().includes('bulan') ? '1 Bulan Lagi' : '';

               document.getElementById(`cat${i}_ket`).innerText = ket ?? '';
            }
         });

   });
</script>