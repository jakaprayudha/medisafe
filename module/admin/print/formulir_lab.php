<body class="cpo-body">

  <div class="cpo-container">

    <style>
      @page {
        size: A4;
        margin: 15mm 20mm;
      }

      /* =========================
   BODY KHUSUS LAB (CPO STYLE)
========================= */
      body.cpo-body {
        font-family: "Times New Roman", serif;
        font-size: 11pt;
        margin: 0;
        padding: 0;
        background: #fff;
        color: #000;
      }

      /* =========================
   CONTAINER
========================= */
      .cpo-container {
        width: 100%;
        max-width: 760px;
        margin: auto;
      }

      .cpo-info .label {
        width: 15%;
        font-weight: bold;
        white-space: nowrap;
        vertical-align: top;
      }

      .cpo-info .value {
        width: 35%;
      }

      .cpo-info .wrap {
        word-break: break-word;
        white-space: normal;
      }

      /* =========================
   TITLE
========================= */
      .cpo-title {
        text-align: center;
        font-size: 14pt;
        font-weight: bold;
        text-transform: uppercase;
        margin: 10px 0 15px;
      }

      /* =========================
   INFO PASIEN
========================= */
      .cpo-info td {
        border: none !important;
        padding: 3px 6px;
        font-size: 11pt;
      }

      /* =========================
   TABLE GENERAL
========================= */
      .cpo-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
      }

      .cpo-table th,
      .cpo-table td {
        border: 1px solid #000;
        padding: 6px;
        vertical-align: top;
        font-size: 11pt;
      }

      .cpo-table th {
        background: #f2f2f2;
        font-weight: bold;
        text-align: center;
      }

      /* =========================
   SECTION HEADER
========================= */
      .cpo-section {
        background: #f4f4f4;
        font-weight: bold;
      }

      /* =========================
   ABNORMAL
========================= */
      .cpo-abnormal {
        color: #c00;
        font-weight: bold;
      }

      /* =========================
   HELPER
========================= */
      .cpo-center {
        text-align: center;
      }

      .cpo-right {
        text-align: right;
      }

      /* =========================
   FOOTER
========================= */
      .cpo-footer {
        display: flex;
        justify-content: space-between;
        margin-top: 35px;
        align-items: flex-end;
      }

      .cpo-qr {
        text-align: center;
        width: 140px;
        font-size: 9pt;
      }

      .cpo-qr img {
        width: 120px;
        height: 120px;
      }

      .cpo-ttd {
        width: 220px;
        text-align: center;
        font-size: 10pt;
      }

      .cpo-ttd img {
        width: 80px;
        margin: 5px 0;
      }

      .cpo-ttd-line {
        border-top: 1px solid #000;
        margin-top: 5px;
        padding-top: 3px;
        font-weight: bold;
      }

      /* =========================
   PRINT
========================= */
      @media print {
        .cpo-noprint {
          display: none !important;
        }
      }
    </style>

    <?php require 'kopsurat.php'; ?>

    <div class="cpo-title">HASIL LABORATORIUM</div>

    <!-- ================= INFO PASIEN ================= -->
    <table class="cpo-table cpo-info">
      <tr>
        <td class="label">Nama</td>
        <td class="value">: <span id="lab_nama"></span></td>

        <td class="label">Tgl Periksa</td>
        <td class="value">: <span id="lab_tgl_periksa"></span></td>
      </tr>

      <tr>
        <td class="label">Jenis Kelamin</td>
        <td class="value">: <span id="lab_jk"></span></td>

        <td class="label align-top">Alamat</td>
        <td class="value wrap">: <span id="lab_alamat"></span></td>
      </tr>
    </table>
    <!-- ================= TABEL LAB ================= -->
    <table class="cpo-table">
      <thead>
        <tr>
          <th>PEMERIKSAAN</th>
          <th width="120">HASIL</th>
          <th width="180">NILAI NORMAL</th>
        </tr>
      </thead>

      <tbody></tbody>
    </table>

    <!-- ================= FOOTER ================= -->
    <div class="cpo-footer">
      <div class="cpo-qr">
        <div id="lab_qr"></div>
        Scan untuk verifikasi hasil
      </div>

      <div class="cpo-ttd">
        Pengisi Data<br>
        <img src="../../../uploads/ttd/lab.png" alt="TTD">
        <div class="cpo-ttd-line" id="lab_petugas"></div>
        Petugas Laboratorium
      </div>
    </div>

  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const p = new URLSearchParams(window.location.search);
      const no = p.get("no");
      const rm = p.get("rm");
      if (!no || !rm) return;

      fetch(`getlab.php?no=${no}&rm=${rm}`)
        .then(r => r.json())
        .then(res => {
          if (res.status !== "success") return;

          const d = res.data || {};
          const pasien = d.pasien || {};
          const hasil = d.hasil || {};

          const set = (id, v) => {
            const el = document.getElementById(id);
            if (el) el.innerText = v ?? "";
          };

          // ================= PASIEN =================
          set("lab_nama", pasien.nama);
          set("lab_tgl_periksa", pasien.tgl_periksa);
          set("lab_tgllahir", pasien.tgllahir);
          // set("lab_umur", pasien.umur);
          set("lab_jk", pasien.jk);
          set("lab_alamat", pasien.alamat);
          set("lab_petugas", d.petugas);

          // ================= TABLE DINAMIS =================
          const tbody = document.querySelectorAll(".cpo-table")[1].querySelector("tbody");
          tbody.innerHTML = "";

          for (let group in hasil) {

            // HEADER GROUP
            tbody.innerHTML += `
          <tr class="cpo-section">
            <td colspan="3">${group}</td>
          </tr>
        `;

            hasil[group].forEach(item => {

              // cek abnormal
              let val = parseFloat(item.hasil);
              let min = parseFloat((item.normal.split('-')[0] || '').trim());
              let max = parseFloat((item.normal.split('-')[1] || '').trim());

              let cls = (!isNaN(val) && (!isNaN(min) && !isNaN(max)) && (val < min || val > max)) ?
                'cpo-abnormal' :
                '';

              tbody.innerHTML += `
            <tr>
              <td>${item.nama}</td>
              <td class="cpo-center ${cls}">
                ${item.hasil ?? '-'} ${item.satuan ?? ''}
              </td>
              <td class="cpo-center">${item.normal}</td>
            </tr>
          `;
            });
          }

          // ================= QR =================
          const url = `${location.origin}/verify_lab.php?no=${no}&rm=${rm}`;
          document.getElementById("lab_qr").innerHTML =
            `<img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(url)}">`;

        });
    });
  </script>

</body>