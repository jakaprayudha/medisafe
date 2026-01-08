<div class="form-cpo">

   <style>
      @page {
         size: A4;
         margin: 15mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 12pt;
         color: #000;
      }

      .title {
         margin-top: 10px;
         text-align: center;
         font-weight: bold;
         font-size: 18pt;
         text-decoration: underline;
      }

      /* ================= INFO PASIEN ================= */
      table.info {
         width: 100%;
         margin-top: 15px;
         font-size: 12pt;
      }

      table.info td {
         padding: 3px 0;
      }

      /* ================= TABEL OBAT ================= */
      table.data {
         width: 100%;
         border-collapse: collapse;
         margin-top: 15px;
         font-size: 11pt;
      }

      table.data th,
      table.data td {
         border: 1px solid #000;
         padding: 4px 5px;
         vertical-align: top;
      }

      .center {
         text-align: center;
      }

      .small-header {
         font-size: 10pt;
         text-align: center;
      }

      /* Jadwal kosong */
      .empty {
         color: red;
         font-weight: bold;
      }

      /* ================= FOOTER ================= */
      .footer-cpo {
         width: 100%;
         display: flex;
         justify-content: space-between;
         margin-top: 40px;
         padding-top: 10px;
      }

      .qr-sec {
         text-align: center;
         width: 140px;
      }

      .qr-sec img {
         width: 120px;
         height: 120px;
         margin-bottom: 5px;
      }

      .qr-text {
         font-size: 10pt;
      }

      .ttd-sec {
         text-align: center;
         width: 250px;
         margin-right: 30px;
      }

      .ttd-line {
         margin: 60px auto 5px auto;
         border-bottom: 1px solid #000;
         width: 180px;
      }

      .ttd-name {
         font-weight: bold;
         font-size: 11pt;
      }

      .ttd-role {
         font-size: 10pt;
         margin-top: 2px;
      }

      @media print {
         button {
            display: none;
         }
      }
   </style>

   <?php require 'kopsurat.php'; ?>

   <div class="title">CATATAN PEMBERIAN OBAT</div>

   <!-- ================= INFO PASIEN ================= -->
   <table class="info">
      <tr>
         <td width="20%">NAMA PASIEN</td>
         <td>: <span id="cpo_nama"></span></td>
         <td width="20%">NOMOR RM</td>
         <td>: <span id="cpo_rm"></span></td>
      </tr>
      <tr>
         <td>TANGGAL LAHIR</td>
         <td>: <span id="cpo_tgllahir"></span></td>
         <td>RUANGAN</td>
         <td>: <span id="cpo_ruangan"></span></td>
      </tr>
      <tr>
         <td>DIAGNOSA</td>
         <td colspan="3">: <span id="cpo_diagnosa"></span></td>
      </tr>
   </table>

   <!-- ================= TABEL OBAT ================= -->
   <table class="data">
      <thead>
         <tr>
            <th width="70">Tanggal</th>
            <th width="200">Nama Obat Oral<br>dan Injeksi</th>
            <th width="70">Dosis</th>
            <th width="90">Signature</th>
            <th colspan="4" class="center">Jadwal & Jam Pemberian</th>
            <th width="80">Paraf<br>Keluarga</th>
            <th width="80">Paraf<br>Petugas</th>
         </tr>
         <tr class="small-header">
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th>Pagi</th>
            <th>Siang</th>
            <th>Sore</th>
            <th>Malam</th>
            <th></th>
            <th></th>
         </tr>
      </thead>
      <tbody id="cpo_body"></tbody>
   </table>

   <!-- ================= FOOTER ================= -->
   <div class="footer-cpo">
      <div class="qr-sec">
         <div id="cpo_qr"></div>
         <div class="qr-text">Scan untuk verifikasi</div>
      </div>

      <div class="ttd-sec">
         <div style="height:60px;">Pengisi Data</div>
         <img src="../../../uploads/ttd/farmasi.png" alt="">
         <div class="ttd-line"></div>
         <p>Darma</p>
         <!-- <div class="ttd-name" id="cpo_petugas"></div> -->
         <div class="ttd-role">Ka. Petugas Ruangan</div>
      </div>
   </div>

   <button onclick="window.print()">🖨 Cetak</button>
</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const params = new URLSearchParams(window.location.search);
      const no = params.get("no");
      const rm = params.get("rm");
      if (!no || !rm) return;

      fetch(`getcpo.php?no=${no}&rm=${rm}`)
         .then(r => r.json())
         .then(res => {
            if (res.status !== "success") return;

            const data = res.data;

            /* ===== HEADER ===== */
            if (data.length > 0) {
               const d = data[0];
               cpo_nama.innerText = d.nama_pasien;
               cpo_rm.innerText = d.nomor_rm;
               cpo_tgllahir.innerText = d.tgl_lahir;
               cpo_ruangan.innerText = d.ruangan;
               cpo_diagnosa.innerText = d.diagnosa;
            }

            /* ===== GROUP BY TANGGAL ===== */
            const grouped = {};
            data.forEach(item => {
               if (!grouped[item.tanggal]) grouped[item.tanggal] = [];
               grouped[item.tanggal].push(item);
            });

            const tbody = document.getElementById("cpo_body");
            tbody.innerHTML = "";

            const makeTd = (text, center = false, empty = false) => {
               const td = document.createElement("td");
               td.innerText = text || "";
               if (center) td.classList.add("center");
               if (empty && (!text || text.trim() === "")) td.classList.add("empty");
               return td;
            };

            Object.keys(grouped).forEach(tanggal => {
               const items = grouped[tanggal];

               items.forEach((obat, i) => {
                  const tr = document.createElement("tr");

                  if (i === 0) {
                     const tdTanggal = makeTd(tanggal, true);
                     tdTanggal.rowSpan = items.length;
                     tr.appendChild(tdTanggal);
                  }

                  tr.appendChild(makeTd(obat.nama_obat));
                  tr.appendChild(makeTd(obat.dosis));
                  tr.appendChild(makeTd(obat.signature));

                  tr.appendChild(makeTd(obat.jam_pagi, true, true));
                  tr.appendChild(makeTd(obat.jam_siang, true, true));
                  tr.appendChild(makeTd(obat.jam_sore, true, true));
                  tr.appendChild(makeTd(obat.jam_malam, true, true));

                  tr.appendChild(makeTd(obat.paraf_keluarga));
                  tr.appendChild(makeTd(obat.paraf_petugas));

                  tbody.appendChild(tr);
               });
            });

            /* ===== QR CODE ===== */
            const verifyUrl =
               `${location.origin}/verify_cpo.php?no=${encodeURIComponent(no)}&rm=${encodeURIComponent(rm)}`;

            cpo_qr.innerHTML =
               `<img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(verifyUrl)}">`;
         });
   });
</script>