<?php
$title = "Lembar Masuk dan Keluar Pasien Rawat Inap";
$subtitle = '';
require '../../../database/connect.php';

$visit = $_GET['no'] ?? '';
$rm    = $_GET['rm'] ?? '';
$checkdata = mysqli_query($koneksi, "SELECT * FROM pasien_visit INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient INNER JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor INNER JOIN permintaan_ranap ON permintaan_ranap.visit_ID_inpatient = pasien_visit.visit_ID LEFT JOIN ms_room ON ms_room.id_room = permintaan_ranap.id_room LEFT JOIN ms_room_bed ON ms_room_bed.id_bed = permintaan_ranap.id_bed LEFT JOIN ms_provider ON ms_provider.id_provider = pasien_visit.id_provider INNER JOIN icd_10 ON icd_10.code = pasien_visit.diagnosa WHERE visit_ID='$visit' AND nomor_rm='$rm'");
$dataio = mysqli_fetch_array($checkdata);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title) ?></title>

  <style>
    /* =========================
   PAGE SETUP
========================= */
    @page {
      size: A4;
      margin: 15mm 20mm;
    }

    /* =========================
   BODY KHUSUS RANAP
========================= */
    body.ranap-body {
      font-family: "Times New Roman", serif;
      font-size: 10pt;
      margin: 0;
      padding: 0;
      background: #fff;
      color: #000;
    }

    /* =========================
   CONTAINER
========================= */
    .ranap-container {
      width: 100%;
      max-width: 760px;
      margin: auto;
    }

    /* =========================
   JUDUL
========================= */
    .ranap-title {
      text-align: center;
      font-weight: bold;
      font-size: 14pt;
      margin: 10px 0 12px;
      text-transform: uppercase;
    }

    /* =========================
   SECTION
========================= */
    .ranap-section {
      border: 1px solid #000;
      margin-bottom: 10px;
    }

    .ranap-section-title {
      background: #f2f2f2;
      font-weight: bold;
      padding: 5px 8px;
      border-bottom: 1px solid #000;
      font-size: 11pt;
    }

    /* =========================
   META GRID (2 KOLOM)
========================= */
    .ranap-meta {
      display: grid;
      grid-template-columns: 1fr 1fr;
    }

    .ranap-meta div {
      padding: 6px 8px;
      border-right: 1px solid #000;
      border-bottom: 1px solid #000;
    }

    .ranap-meta div:nth-child(2n) {
      border-right: none;
    }

    /* =========================
   TABLE DETAIL
========================= */
    .ranap-table {
      width: 100%;
      border-collapse: collapse;
    }

    .ranap-table td {
      border: 1px solid #000;
      padding: 5px 6px;
      vertical-align: top;
    }

    /* =========================
   HIGHLIGHT
========================= */
    .ranap-highlight {
      font-weight: bold;
    }

    /* =========================
   PRINT CONTROL
========================= */
    @media print {
      .ranap-noprint {
        display: none !important;
      }
    }
  </style>
</head>

<body class="ranap-body">

  <div class="ranap-container">

    <?php include 'kop-surat.php'; ?>

    <div class="ranap-title"><?= $title ?></div>

    <!-- ================= IDENTITAS & ADMINISTRASI ================= -->
    <div class="ranap-section">
      <div class="ranap-section-title">IDENTITAS & ADMINISTRASI</div>
      <div class="ranap-meta">
        <div>Status Perkawinan: <span id="status_perkawinan"></span></div>
        <div>Asuransi: BPJS / Lainnya</div>
        <div>Penanggung Jawab: <span id="penanggung_jawab"></span></div>
        <div>Alamat PJ: <span id="alamat_pj"></span></div>
      </div>
    </div>

    <!-- ================= MASUK & KELUAR ================= -->
    <div class="ranap-section">
      <div class="ranap-section-title">MASUK & KELUAR RAWAT INAP</div>
      <div class="ranap-meta">
        <div>
          Tanggal Masuk:
          <span class="ranap-highlight" id="tanggal_masuk"></span><br>
          Jam: <span id="jam_masuk"></span>
        </div>
        <div>
          Tanggal Keluar:
          <span class="ranap-highlight" id="tanggal_keluar"></span><br>
          Jam: <span id="jam_keluar"></span>
        </div>
        <div>Ruang / Kelas: <span id="ruang_rawat"></span></div>
        <div>Lama Dirawat: <span class="ranap-highlight" id="lama_dirawat"></span> Hari</div>
      </div>
    </div>

    <!-- ================= CARA MASUK ================= -->
    <div class="ranap-section">
      <div class="ranap-section-title">CARA MASUK RAWAT INAP</div>
      <div style="padding:6px 8px">
        Klinik Tutun Sehati<br>
        1. Dokter / Paramedis<br>
        2. Pustu / Polindes<br>
        3. Instansi Kesehatan<br>
        4. Kasus Polisi<br>
        <strong>5. Datang Sendiri</strong> <input type="checkbox" checked>
      </div>
    </div>

    <!-- ================= DIAGNOSA & TINDAKAN ================= -->
    <div class="ranap-section">
      <div class="ranap-section-title">DIAGNOSA & TINDAKAN</div>
      <table class="ranap-table">
        <tr>
          <td width="30%">Diagnosa Medik</td>
          <td id="diagnosa_medik"><?= $dataio['anamnesa'] ?></td>
        </tr>
        <tr>
          <td>Diagnosa Akhir</td>
          <td>
            Utama: <span id="diagnosa_utama"><?= $dataio['diagnosa_utama'] ?></span><br>
            Komplikasi: <span id="diagnosa_sekunder"><?= $dataio['diagnosa_sekunder'] ?></span>
          </td>
        </tr>
        <tr>
          <td>Tindakan / Operasi</td>
          <td id="nama_operasi"></td>
        </tr>
      </table>
    </div>

    <!-- ================= CATATAN MEDIS LAIN ================= -->
    <div class="ranap-section">
      <div class="ranap-section-title">CATATAN MEDIS LAINNYA</div>
      <table class="ranap-table">
        <tr>
          <td width="30%">Alergi</td>
          <td id="alergi"></td>
        </tr>
        <tr>
          <td>Infeksi Nosokomial</td>
          <td id="infeksi_nosokomial"></td>
        </tr>
        <tr>
          <td>Penyebab Infeksi</td>
          <td id="penyebab_infeksi"></td>
        </tr>
        <tr>
          <td>Keadaan Keluar</td>
          <td class="ranap-highlight" id="keadaan_keluar"></td>
        </tr>
        <tr>
          <td>Cara Keluar</td>
          <td id="cara_keluar"></td>
        </tr>
        <tr>
          <td>Dokter Merawat</td>
          <td class="ranap-highlight" id="dokter_merawat"></td>
        </tr>
      </table>
    </div>

    <div class="ranap-noprint" style="text-align:center;margin-top:15px">
      <button onclick="window.print()">🖨 Cetak Halaman</button>
    </div>

  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const params = new URLSearchParams(window.location.search);
      const no = params.get("no");
      const rm = params.get("rm");
      if (!no || !rm) return;

      fetch(`getranap.php?no=${no}&rm=${rm}`)
        .then(res => res.json())
        .then(resp => {
          if (resp?.status !== "success") return;
          const d = resp.data || {};
          Object.keys(d).forEach(key => {
            const el = document.getElementById(key);
            if (el) el.textContent = d[key] ?? "-";
          });
        })
        .catch(console.error);
    });
  </script>

</body>

</html>