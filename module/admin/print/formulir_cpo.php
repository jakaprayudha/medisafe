<?php
$title = "Formulir Catatan Pemberian Obat (CPO)";
$subtitle = "";
require '../../../database/connect.php';
$no = $_GET['no'];
$id_customer = $_SESSION['id_customer'];
$checkruangan = mysqli_query($koneksi, "SELECT ms_room.room_name, ms_room_bed.bed_name, pasien_visit.diagnosa, icd_10.icd10 FROM pasien_visit
LEFT JOIN permintaan_ranap ON pasien_visit.visit_ID = permintaan_ranap.visit_ID_inpatient 
LEFT JOIN ms_room ON permintaan_ranap.id_room = ms_room.id_room
LEFT JOIN ms_room_bed ON permintaan_ranap.id_bed = ms_room_bed.id_bed
LEFT JOIN icd_10 ON icd_10.code = pasien_visit.diagnosa
WHERE pasien_visit.visit_ID='$no' AND pasien_visit.id_customer='$id_customer'");
$dataruangan = mysqli_fetch_array($checkruangan);
$checkpetugas = mysqli_query($koneksi, "SELECT fullname,signature_user FROM ms_users WHERE id_customer='$id_customer' AND roles='apoteker' LIMIT 1");
$datapetugas = mysqli_fetch_array($checkpetugas);
?>

<body class="cpo-body">

  <div class="cpo-container">

    <style>
      @page {
        size: A4;
        margin: 15mm 20mm;
      }

      /* =========================
   BODY KHUSUS CPO
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

      /* =========================
   TITLE
========================= */
      .cpo-title {
        text-align: center;
        font-size: 16pt;
        font-weight: bold;
        text-decoration: underline;
        margin: 10px 0 15px;
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
        padding: 5px 6px;
        vertical-align: top;
      }

      .cpo-table th {
        background: #f2f2f2;
        font-weight: bold;
        text-align: center;
        font-size: 10pt;
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
   HELPER
========================= */
      .cpo-center {
        text-align: center;
      }

      .cpo-right {
        text-align: right;
      }

      .cpo-empty {
        color: red;
        font-weight: bold;
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

    <?php require 'kop-surat.php'; ?>

    <!-- ================= INFO PASIEN ================= -->
    <table class="cpo-table cpo-info">
      <tr>
        <td width="15%">Ruangan</td>
        <td width="35%">: <?= $dataruangan['room_name'] ?> Bed <?= $dataruangan['bed_name'] ?></td>
        <td width="15%">Diagnosa</td>
        <td width="35%">: <?= $dataruangan['diagnosa'] ?> - <?= $dataruangan['icd10'] ?></td>
      </tr>
    </table>

    <!-- ================= TABLE OBAT ================= -->
    <table class="cpo-table">
      <thead>
        <tr>
          <th width="70">Tanggal</th>
          <th>Nama Obat / Injeksi</th>
          <th width="70">Dosis</th>
          <th width="80">Sign</th>
          <th colspan="4">Jam Pemberian</th>
          <th width="70">Paraf<br>Keluarga</th>
          <th width="70">Paraf<br>Petugas</th>
        </tr>
        <tr>
          <th colspan="4"></th>
          <th>Pagi</th>
          <th>Siang</th>
          <th>Sore</th>
          <th>Malam</th>
          <th colspan="2"></th>
        </tr>
      </thead>
      <tbody id="cpo_body"></tbody>
    </table>

    <!-- ================= FOOTER ================= -->
    <div class="cpo-footer">
      <div class="cpo-qr">
        <div id="cpo_qr"></div>
        Scan untuk verifikasi
      </div>

      <div class="cpo-ttd">
        Pengisi Data<br>
        <img src="../../../uploads/ttd_faskes/<?= $datapetugas['signature_user'] ?? '' ?>" alt="TTD">
        <div class="cpo-ttd-line"><?= $datapetugas['fullname'] ?></div>
      </div>
    </div>

    <div class="cpo-noprint" style="text-align:center;margin-top:15px">
      <button onclick="window.print()">🖨 Cetak</button>
    </div>

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
          if (data.length > 0) {}

          const tbody = document.getElementById("cpo_body");
          tbody.innerHTML = "";

          const grouped = {};
          data.forEach(i => {
            if (!grouped[i.tanggal]) grouped[i.tanggal] = [];
            grouped[i.tanggal].push(i);
          });

          Object.keys(grouped).forEach(tgl => {
            grouped[tgl].forEach((o, idx) => {
              const tr = document.createElement("tr");

              if (idx === 0) {
                const td = document.createElement("td");
                td.rowSpan = grouped[tgl].length;
                td.textContent = tgl;
                td.className = "cpo-center";
                tr.appendChild(td);
              }

              tr.innerHTML += `
            <td>${o.nama_item || ""}</td>
            <td class="cpo-center">${o.dosis || ""}</td>
            <td class="cpo-center">${o.signature || ""}</td>
            <td class="cpo-center">${o.jam_pagi || ""}</td>
            <td class="cpo-center">${o.jam_siang || ""}</td>
            <td class="cpo-center">${o.jam_sore || ""}</td>
            <td class="cpo-center">${o.jam_malam || ""}</td>
            <td class="cpo-center">${o.paraf_keluarga ? `<img src="../../../uploads/ttd/${o.paraf_keluarga}" height="35">` : ""}</td>
            <td class="cpo-center">${o.paraf_petugas ? `<img src="../../../uploads/ttd/${o.paraf_petugas}" height="35">` : ""}</td>
          `;
              tbody.appendChild(tr);
            });
          });

          const verifyUrl = `${location.origin}/verify_cpo.php?no=${no}&rm=${rm}`;
          cpo_qr.innerHTML =
            `<img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(verifyUrl)}">`;
        });
    });
  </script>

</body>