<?php
$title = "Catatan Perkembangan Pasien Terintegrasi (CPPT)";
$subtitle = "";
require '../../../database/connect.php';
?>

<body class="cpo-body">

  <div class="cpo-container">

    <style>
      @page {
        size: A4;
        margin: 15mm 20mm;
      }

      /* =========================
   BODY KHUSUS (PAKAI STYLE CPO)
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
   CONTAINER (LEBAR SAMA CPO)
========================= */
      .cpo-container {
        width: 100%;
        max-width: 760px;
        margin: auto;
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
   TABLE GENERAL (CPO STYLE)
========================= */
      .cpo-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
      }

      .cpo-table th,
      .cpo-table td {
        border: 1px solid #000;
        padding: 6px;
        vertical-align: top;
        font-size: 10pt;
      }

      .cpo-table th {
        background: #f2f2f2;
        font-weight: bold;
        text-align: center;
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
   TTD
========================= */
      .cpo-ttd img {
        height: 35px;
        object-fit: contain;
        display: block;
        margin: 0 auto 4px;
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

    <?php include 'kop-surat.php'; ?>

    <div class="cpo-title">
      <?= strtoupper($title) ?>
    </div>

    <!-- ================= TABEL CPPT ================= -->
    <table class="cpo-table">
      <thead>
        <tr>
          <th width="15%">Tanggal / Jam</th>
          <th width="45%">Perkembangan (SOAP)</th>
          <th width="25%">Diagnosa / Instruksi</th>
          <th width="15%">Paraf / Nama</th>
        </tr>
      </thead>
      <tbody id="cppt_body">
        <!-- DATA CPPT -->
      </tbody>
    </table>

    <div class="cpo-noprint" style="text-align:center;margin-top:12px">
      <button onclick="window.print()">🖨 Cetak Halaman</button>
    </div>

  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const urlParams = new URLSearchParams(window.location.search);
      const no = urlParams.get("no");
      const rm = urlParams.get("rm");
      if (!no || !rm) return;

      fetch(`getcppt.php?visit=${no}&rm=${rm}`)
        .then(res => res.json())
        .then(resp => {
          if (!resp || resp.status !== "success") return;

          let html = "";

          resp.data.forEach(cppt => {
            let ttd = "";

            if (cppt.cppt_profesi) {
              const profesi = cppt.cppt_profesi.toLowerCase();
              if (profesi.includes("perawat")) {
                ttd = `<img src="../../../uploads/ttd/farmasi.png" alt="TTD Perawat">`;
              } else if (profesi.includes("dokter")) {
                ttd = `<img src="../../../uploads/ttd/drdevi.png" alt="TTD Dokter">`;
              }
            }

            html += `
          <tr>
            <td class="cpo-center">
              ${cppt.cppt_date}<br>${cppt.cppt_time}
            </td>
            <td>
              <b>S:</b> ${cppt.subjective || "-"}<br>
              <b>O:</b> ${cppt.objective || "-"}<br>
              <b>A:</b> ${cppt.analysis || "-"}<br>
              <b>P:</b> ${cppt.planning || "-"}
            </td>
            <td>${cppt.instruction || "-"}</td>
            <td class="cpo-center cpo-ttd">
              ${ttd}
              <b>${cppt.users_entry || "-"}</b><br>
              <small>(${cppt.cppt_profesi || "-"})</small>
            </td>
          </tr>
        `;
          });

          document.getElementById("cppt_body").innerHTML = html;
        });
    });
  </script>

</body>