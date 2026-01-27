<body class="resume-body">

<div class="resume-container">

<style>
@page {
  size: A4;
  margin: 15mm 20mm;
}

/* =========================
   CONTAINER SAJA (AMAN)
========================= */
.resume-container {
  width: 100%;
  max-width: 760px;
  margin: auto;

  font-family: "Times New Roman", serif;
  font-size: 11pt;
  color: #000;
}

/* =========================
   TITLE
========================= */
.resume-title {
  text-align: center;
  font-size: 14pt;
  font-weight: bold;
  text-transform: uppercase;
  margin: 10px 0 15px;
}

/* =========================
   TABLE IDENTITAS
========================= */
.resume-info {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 8px;
}

.resume-info td {
  padding: 4px 6px;
  font-size: 11pt;
  vertical-align: top;
}

.resume-line {
  border-bottom: 1px dotted #555;
  min-height: 16px;
}

/* =========================
   SECTION
========================= */
.resume-section {
  margin-top: 12px;
}

.resume-section-title {
  font-weight: bold;
  text-transform: uppercase;
  font-size: 11pt;
  border-bottom: 1px solid #000;
  margin-bottom: 4px;
  padding-bottom: 2px;
}

.resume-box {
  border: 1px solid #000;
  padding: 6px;
  min-height: 60px;
  white-space: pre-line;
  font-size: 11pt;
}

/* =========================
   SIGNATURE
========================= */
.resume-signature {
  display: flex;
  justify-content: space-between;
  margin-top: 35px;
}

.resume-sign-col {
  width: 48%;
  text-align: center;
  font-size: 11pt;
}

.resume-sign-col img {
  height: 80px;
  object-fit: contain;
  margin-bottom: 4px;
}

.resume-sign-line {
  border-top: 1px solid #000;
  margin-top: 6px;
  padding-top: 4px;
  font-weight: bold;
}

/* =========================
   PRINT
========================= */
@media print {
  .resume-noprint {
    display: none !important;
  }
}
</style>

<?php include 'kop-surat.php'; ?>

<div class="resume-title">RESUME MEDIS</div>

<!-- ================= IDENTITAS PASIEN ================= -->
<table class="resume-info">
  <tr>
    <td width="18%">Nama Pasien</td>
    <td width="32%" class="resume-line" id="r_nama"></td>
    <td width="18%">No. Rekam Medis</td>
    <td width="32%" class="resume-line" id="r_rm"></td>
  </tr>
  <tr>
    <td>Umur</td>
    <td class="resume-line" id="r_umur"></td>
    <td>Jenis Kelamin</td>
    <td class="resume-line" id="r_jk"></td>
  </tr>
  <tr>
    <td>Alamat</td>
    <td colspan="3" class="resume-line" id="r_alamat"></td>
  </tr>
  <tr>
    <td>Ruang / Kelas</td>
    <td class="resume-line" id="r_ruang"></td>
    <td>Tanggal Masuk</td>
    <td class="resume-line" id="r_masuk"></td>
  </tr>
  <tr>
    <td>Tanggal Keluar</td>
    <td class="resume-line" id="r_keluar"></td>
    <td>DPJP</td>
    <td class="resume-line" id="r_dpjp"></td>
  </tr>
</table>

<!-- ================= ISI RESUME ================= -->
<div class="resume-section">
  <div class="resume-section-title">Diagnosa</div>
  <div class="resume-box" id="r_diagnosa"></div>
</div>

<div class="resume-section">
  <div class="resume-section-title">Tindakan / Terapi</div>
  <div class="resume-box" id="r_tindakan"></div>
</div>

<div class="resume-section">
  <div class="resume-section-title">Hasil Pemeriksaan Penunjang</div>
  <div class="resume-box" id="r_penunjang"></div>
</div>

<div class="resume-section">
  <div class="resume-section-title">Obat yang Diberikan</div>
  <div class="resume-box" id="r_obat"></div>
</div>

<div class="resume-section">
  <div class="resume-section-title">Instruksi / Anjuran Lanjutan</div>
  <div class="resume-box" id="r_instruksi"></div>
</div>

<!-- ================= TTD ================= -->
<div class="resume-signature">
  <div class="resume-sign-col">
    <img src="../../../uploads/ttd/farmasi.png" alt="TTD Petugas">
    <div class="resume-sign-line" id="r_perawat">Petugas / Perawat</div>
  </div>

  <div class="resume-sign-col">
    <img src="../../../uploads/ttd/drdevi.png" alt="TTD Dokter">
    <div class="resume-sign-line" id="r_dokter">Dokter Penanggung Jawab</div>
  </div>
</div>

<div class="resume-noprint" style="text-align:center;margin-top:15px">
  <button onclick="window.print()">🖨 Cetak Resume</button>
</div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {

  const p = new URLSearchParams(window.location.search);
  const no = p.get("no");
  const rm = p.get("rm");
  if (!no || !rm) return;

  // === DATA PASIEN ===
  fetch(`getpasien.php?no=${no}&rm=${rm}`)
    .then(r => r.json())
    .then(d => {
      if (!d) return;

      let umur = "-";
      if (d.patient_datebirth) {
        const b = new Date(d.patient_datebirth);
        umur = (new Date().getFullYear() - b.getFullYear()) + " Tahun";
      }

      r_nama.innerText   = d.patient_name || "-";
      r_rm.innerText     = d.nomor_rm || "-";
      r_jk.innerText     = d.patient_gender || "-";
      r_umur.innerText   = umur;
      r_alamat.innerText = d.patient_address || "-";
      r_ruang.innerText  = d.source_hub || "-";
      r_masuk.innerText  = d.visit_date || "-";
      r_keluar.innerText = d.visit_out || "-";
      r_dpjp.innerText   = d.doctor_name || "-";
    });

  // === DATA RESUME ===
  fetch(`getresume.php?visit=${no}&rm=${rm}`)
    .then(r => r.json())
    .then(res => {
      if (!res || res.status !== "success") return;
      const x = res.data || {};

      r_diagnosa.innerText   = x.diagnosa || "";
      r_tindakan.innerText   = x.tindakan || "";
      r_penunjang.innerText  = x.pemeriksaan_penunjang || "";
      r_obat.innerText       = x.obat || "";
      r_instruksi.innerText  = x.instruksi || "";

      r_perawat.innerText = x.petugas || "Perawat";
      r_dokter.innerText  = x.dokter || "Dokter DPJP";
    });

});
</script>

</body>