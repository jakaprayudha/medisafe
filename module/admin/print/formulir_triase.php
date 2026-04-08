<?php
$title = "FORMULIR TRIASE KEGAWATDARURATAN";
$subtitle = "Assesmen Medis Awal Pasien IGD";
require '../../../database/connect.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title><?= $title ?></title>

  <style>
    @page {
      size: A4;
      margin: 1.5cm;
    }

    @media print {
      * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
    }

    /* =================================================
   TRIAGE STYLE (AMAN)
================================================= */
    body.triase-body {
      font-family: "Times New Roman", serif;
      font-size: 11pt;
      margin: 0;
      background: #fff;
    }

    .triase-container {
      max-width: 760px;
      margin: auto;
    }

    .triase-table {
      width: 100%;
      border-collapse: collapse;
    }

    .triase-table th,
    .triase-table td {
      border: 1px solid #000;
      padding: 6px;
    }

    .triase-section {
      margin-top: 14px;
      font-weight: bold;
    }

    .triase-header td {
      padding: 6px;
    }

    /* ATS BADGE */
    .badge-triase {
      padding: 4px 14px;
      border-radius: 14px;
      font-weight: bold;
      color: #fff;
      font-size: 12px;
    }

    .ats1 {
      background: #d9534f;
    }

    .ats2 {
      background: #f0ad4e;
      color: #000;
    }

    .ats3 {
      background: #5cb85c;
    }

    .ats4 {
      background: #000;
    }

    .ats5 {
      background: #999;
    }

    /* PAIN SCALE */
    .pain-scale {
      display: flex;
      gap: 12px;
      margin-top: 8px;
    }

    .pain-item {
      text-align: center;
      font-size: 26px;
      width: 48px;
    }

    .pain-item span {
      display: block;
      font-size: 11px;
    }

    .pain-selected {
      border: 3px solid red;
      border-radius: 10px;
      padding: 4px;
      background: #ffe5e5;
    }

    /* =================================================
   IGD ABCDE STYLE (DISCOPE)
================================================= */
    .igd-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .igd-table th,
    .igd-table td {
      border: 1px solid #000;
      padding: 6px;
      vertical-align: top;
    }

    .igd-table th {
      background: #f2f2f2;
      text-align: center;
    }

    .igd-check {
      display: block;
      margin-bottom: 4px;
    }

    .igd-ttd {
      width: 300px;
      margin-left: auto;
      margin-top: 30px;
      text-align: center;
    }

    /* PRINT */
    @media print {
      .triase-noprint {
        display: none;
      }
    }
  </style>
</head>

<body class="triase-body">

  <div class="triase-container">

    <?php require 'kop-surat.php'; ?>

    <!-- ================= HEADER ================= -->
    <table class="triase-header">
      <tr>
        <td width="D"><img id="barcode_rm" height="40"></td>
        <td>
          Kategori ATS :
          <span id="tri_kategori_badge" class="badge-triase">-</span>
        </td>
      </tr>
    </table>

    <table class="igd-table">
      <tr>
        <th>Airway</th>
        <th>Breathing</th>
        <th>Circulation</th>
        <th>Disability</th>
        <th>Vital Sign</th>
      </tr>
      <tr>
        <td>
          <label class="igd-check"><input type="checkbox" checked> Bebas</label>
          <label class="igd-check"><input type="checkbox"> Gargling</label>
          <label class="igd-check"><input type="checkbox"> Stridor</label>
          <label class="igd-check"><input type="checkbox"> Terintubasi</label>
        </td>
        <td>
          <label class="igd-check"><input type="checkbox" checked> Spontan</label>
          <label class="igd-check"><input type="checkbox"> Tachipneu</label>
          <label class="igd-check"><input type="checkbox"> Dispneu</label>
          <label class="igd-check"><input type="checkbox"> Apneu</label>
        </td>
        <td>
          Nadi: <b>Kuat</b><br>
          CRT: <b>&lt; 2 detik</b><br>
          Turgor: <b>Baik</b>
        </td>
        <td>
          GCS E: <span id="gcs_e"></span><br>
          GCS V: <span id="gcs_v"></span><br>
          GCS M: <span id="gcs_m"></span>
        </td>
        <td>
          TD: <span id="td"></span> mmHg<br>
          Nadi: <span id="nadi"></span> x/menit<br>
          RR: <span id="rr"></span> x/menit<br>
          Suhu: <span id="suhu"></span> °C
        </td>
      </tr>
    </table>

    <table class="igd-table">
      <tr>
        <th style="width:20%">ANAMNESIS</th>
        <td>
          <label><input type="checkbox" checked> Auto Anamnesa</label>
          <label><input type="checkbox"> Allo Anamnesa</label>
        </td>
      </tr>
    </table>

    <table class="igd-table igd-table-noborder">
      <tr>
        <td style="width:25%">Keluhan Utama</td>
        <td id="tri_keluhan">: </td>
      </tr>
      <tr>
        <td>Riwayat Penyakit Sekarang</td>
        <td id="riwayat_penyakit_sekarang">: </td>
      </tr>
      <tr>
        <td>Riwayat Penyakit Dahulu</td>
        <td id="riwayat_penyakit_pribadi">: </td>
      </tr>
      <tr>
        <td>Riwayat Pengobatan</td>
        <td id="riwayat_pengobatan">: </td>
      </tr>
      <tr>
        <td>Riwayat Alergi</td>
        <td id="riwayat_alergi">: </td>
      </tr>
    </table>

    <!-- ================= TRIASE ================= -->
    <table class="igd-table">
      <tr>
        <th colspan="3">DIAGNOSA</th>
      </tr>
      <tr>
        <td style="width:20%">Diagnosa Kerja</td>
        <td id="diagnosa_utama"></td>
      </tr>
      <tr>
        <td>Diagnosa Banding</td>
        <td id="diagnosa_sekunder"></td>
      </tr>
    </table>

    <div class="triase-section">Skala Nyeri</div>
    <div id="painScale" class="pain-scale"></div>
    <table class="triase-table">
      <tr>
        <td>Nilai Nyeri : <b id="tri_nyeri"></b> / 10</td>
      </tr>
    </table>

    <!-- =====================================================
     IGD – ABCDE (DIGABUNG DARI FORM IGD)
===================================================== -->
    <table class="igd-table">
      <tr>
        <th style="width:20%">TERAPI</th>
        <td>:</td>
        <td></td>
      </tr>
    </table>


    <table class="igd-table">
      <tr>
        <th style="width:20%">Perawatan Lanjutan</th>
        <td>
          <label><input type="checkbox" checked> Rawat Inap</label>
          <label><input type="checkbox"> Rawat Intensive</label>
        </td>
      </tr>
    </table>
    <div class="triase-section">Catatan Tambahan</div>
    <table class="triase-table">
      <tr>
        <td id="tri_catatan"></td>
      </tr>
    </table>



    <div class="igd-ttd">
      Dokter<br><br>
      <img src="../../../uploads/ttd/drdevi.png" height="90"><br>
      <b id="nama_petugas"></b>
    </div>

    <div class="triase-noprint" style="text-align:center;margin-top:10px">
      <button onclick="window.print()">🖨 Cetak</button>
    </div>

  </div>

  <script>
    function renderPainScale(level) {
      const faces = ["😀", "🙂", "🙂", "😐", "😐", "😩", "😫", "😣", "😭", "😭", "😭"];
      let html = "";
      for (let i = 0; i <= 10; i++) {
        html += `<div class="pain-item ${i==level?'pain-selected':''}">
      ${faces[i]}<span>${i}</span></div>`;
      }
      painScale.innerHTML = html;
    }
    document.addEventListener("DOMContentLoaded", () => {

      const p = new URLSearchParams(location.search);

      fetch(`../../../controller/ranap/getFormTriase.php?no=${p.get("no")}&rm=${p.get("rm")}`)
        .then(r => r.json())
        .then(res => {

          const d = res.data || {};

          // =============================
          // DATA UTAMA
          // =============================
          tri_keluhan.innerText = d.keluhan_utama || "-";
          riwayat_penyakit_sekarang.innerText = d.riwayat_penyakit_sekarang || "-";
          riwayat_pengobatan.innerText = d.riwayat_pengobatan || "-";
          riwayat_penyakit_pribadi.innerText = d.riwayat_penyakit_pribadi || "-";
          riwayat_alergi.innerText = d.riwayat_alergi || "-";
          diagnosa_utama.innerText =
            `${d.code || '-'} - ${d.icd10 || ''}`;
          diagnosa_sekunder.innerText = d.diagnosa_sekunder || "-";
          tri_nyeri.innerText = d.skala_nyeri || "0";
          tri_catatan.innerText = d.catatan || "-";
          gcs_e.innerText = d.gcs_e || "-";
          gcs_m.innerText = d.gcs_m || "-";
          gcs_v.innerText = d.gcs_v || "-";
          td.innerText = d.tekanan_darah || "-";
          nadi.innerText = d.nadi || "-";
          rr.innerText = d.rr || "-";
          suhu.innerText = d.suhu || "-";
          spo2.innerText = d.spo2 || "-";
          tgl_periksa.innerText = d.tgl_periksa || "-";
          nama_petugas.innerText = d.id_doctor || "-";

          // =============================
          // PAIN SCALE
          // =============================
          renderPainScale(parseInt(d.skala_nyeri || 0));

          // =============================
          // DOKTER
          // =============================
          nama_petugas.innerText = d.id_doctor || "-";

          // =============================
          // BARCODE
          // =============================
          barcode_rm.src =
            `https://barcode.tec-it.com/barcode.ashx?data=${encodeURIComponent(d.visit_ID || "")}&code=Code128`;

          // =============================
          // ATS BADGE (kalau ada)
          // =============================
          const badge = document.getElementById("tri_kategori_badge");
          const ats = parseInt(d.triase || 0); // dari field triase

          badge.className = "badge-triase";

          if (ats >= 1 && ats <= 5) {
            badge.innerText = "ATS " + ats;
            badge.classList.add("ats" + ats);
          } else {
            badge.innerText = "-";
          }

          // =============================
          // VITAL SIGN (DINAMIS)
          // =============================
          document.querySelectorAll(".igd-table")[0].rows[1].cells[4].innerHTML = `
        TD: ${d.tekanan_darah || '-'} mmHg<br>
        Nadi: ${d.nadi || '-'} x/menit<br>
        RR: ${d.rr || '-'} x/menit<br>
        Suhu: ${d.suhu || '-'} °C<br>
        SpO2: ${d.spo2 || '-'} %
      `;

          // =============================
          // GCS
          // =============================
          document.querySelectorAll(".igd-table")[0].rows[1].cells[3].innerHTML = `
        GCS E: ${d.gcs_e || '-'}<br>
        GCS V: ${d.gcs_v || '-'}<br>
        GCS M: ${d.gcs_m || '-'}
      `;

          // =============================
          // TTD DOKTER
          // =============================
          const ttdImg = document.querySelector(".igd-ttd img");

          if (d.signature_path) {
            ttdImg.src = `../../../uploads/ttd/${d.signature_path}`;
          } else {
            ttdImg.src = `../../../assets/img/no-sign.png`;
          }

        });
    });
  </script>

</body>

</html>