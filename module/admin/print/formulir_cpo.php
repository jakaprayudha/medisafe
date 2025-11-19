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

      /* Jadwal kosong = warna merah */
      .empty {
         color: red;
         font-weight: bold;
      }

      /* ================= FOOTER: QR + TTD ================= */
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
   </style>

   <?php require 'kopsurat.php' ?>

   <div class="title">CATATAN PEMBERIAN OBAT</div>

   <!-- ================== INFO PASIEN ================== -->
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
         <td>: <span id="cpo_diagnosa"></span></td>
         <td></td>
         <td></td>
      </tr>
   </table>

   <!-- ================== TABEL OBAT ================== -->
   <table class="data" id="cpo_table">
      <thead>
         <tr>
            <th width="70px">Tanggal</th>
            <th width="200px">Nama Obat Oral<br>dan Injeksi</th>
            <th width="70px">Dosis</th>
            <th width="90px">Signature</th>
            <th colspan="4" class="center">Jadwal dan Jam Pemberian Obat</th>
            <th width="80px">Paraf<br>Keluarga</th>
            <th width="80px">Paraf<br>Petugas</th>
         </tr>

         <tr class="small-header">
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th width="60px">Pagi</th>
            <th width="60px">Siang</th>
            <th width="60px">Sore</th>
            <th width="60px">Malam</th>
            <th></th>
            <th></th>
         </tr>
      </thead>

      <tbody id="cpo_body"></tbody>
   </table>

   <!-- ================== FOOTER ================== -->
   <div class="footer-cpo">

      <!-- QR AREA -->
      <div class="qr-sec">
         <div id="cpo_qr"></div>
         <div class="qr-text">Scan untuk verifikasi</div>
      </div>

      <!-- TTD PETUGAS -->
      <div class="ttd-sec">
         <div style="height:60px;">Pengisi Data</div>

         <div class="ttd-line"></div>

         <div class="ttd-name" id="cpo_petugas"></div>
         <div class="ttd-role">Petugas Ruangan</div>
      </div>

   </div>

</div>

<script>
   document.addEventListener("DOMContentLoaded", () => {

      const url = new URLSearchParams(window.location.search);
      const no = url.get("no");
      const rm = url.get("rm");

      if (!no || !rm) return;

      fetch("getcpo.php?no=" + no + "&rm=" + rm)
         .then(r => r.json())
         .then(res => {

            if (res.status !== "success") return;

            const data = res.data;

            // ================= HEADER PASIEN =================
            if (data.length > 0) {
               const d = data[0];
               document.getElementById("cpo_nama").innerText = d.nama_pasien;
               document.getElementById("cpo_rm").innerText = d.nomor_rm;
               document.getElementById("cpo_tgllahir").innerText = d.tgl_lahir;
               document.getElementById("cpo_ruangan").innerText = d.ruangan;
               document.getElementById("cpo_diagnosa").innerText = d.diagnosa;
               document.getElementById("cpo_petugas").innerText = d.petugas;
            }

            // ================= GENERATE ROWS =================
            const tbody = document.getElementById("cpo_body");
            tbody.innerHTML = "";

            data.forEach(obat => {
               const tr = document.createElement("tr");

               const makeTd = (content, center = false, highlight = false) => {
                  const td = document.createElement("td");
                  td.innerText = content || "";
                  if (center) td.classList.add("center");
                  if (highlight && (!content || content.trim() === ""))
                     td.classList.add("empty");
                  return td;
               };

               tr.appendChild(makeTd(obat.tanggal));
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

            // ================= QR CODE =================
            const verifyUrl = window.location.origin +
               "/verify_cpo.php?no=" + encodeURIComponent(no) +
               "&rm=" + encodeURIComponent(rm);

            const qrContainer = document.getElementById("cpo_qr");

            const qrImg = document.createElement("img");
            qrImg.src =
               "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" +
               encodeURIComponent(verifyUrl);

            qrImg.alt = "QR Verifikasi CPO";
            qrContainer.innerHTML = "";
            qrContainer.appendChild(qrImg);

         });

   });
</script>