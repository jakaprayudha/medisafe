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
.cpo-center { text-align: center; }
.cpo-right  { text-align: right; }

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
    <td width="18%">Nama</td>
    <td width="32%">: <span id="lab_nama"></span></td>
    <td width="20%">Tgl Pemeriksaan</td>
    <td width="30%">: <span id="lab_tgl_periksa"></span></td>
  </tr>
  <tr>
    <td>Tgl Lahir</td>
    <td>: <span id="lab_tgllahir"></span> (<span id="lab_umur"></span> th)</td>
    <td>Jenis Kelamin</td>
    <td>: <span id="lab_jk"></span></td>
  </tr>
  <tr>
    <td>Alamat</td>
    <td colspan="3">: <span id="lab_alamat"></span></td>
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

  <tbody>
    <tr class="cpo-section">
      <td colspan="3">Hematologi</td>
    </tr>
    <tr><td>Hemoglobin (Hb)</td><td id="lab_hb"></td><td>11.0 – 17.5 g/dL</td></tr>
    <tr><td>Leukosit (WBC)</td><td id="lab_wbc"></td><td>4.0 – 10.1 ×10³/μL</td></tr>
    <tr><td>Eritrosit (RBC)</td><td id="lab_rbc"></td><td>3.5 – 5.5 ×10¹²/L</td></tr>
    <tr><td>Trombosit (PLT)</td><td id="lab_plt"></td><td>100 – 300 ×10³/μL</td></tr>
    <tr><td>Hematokrit (HCT)</td><td id="lab_hct"></td><td>37 – 50 %</td></tr>
    <tr><td>MCV</td><td id="lab_mcv"></td><td>82 – 95 fL</td></tr>
    <tr><td>MCH</td><td id="lab_mch"></td><td>27 – 31 pg</td></tr>
    <tr><td>MCHC</td><td id="lab_mchc"></td><td>32 – 36 g/dL</td></tr>
    <tr><td>LYM</td><td id="lab_lym"></td><td>23.4 – 40 %</td></tr>

    <tr class="cpo-section">
      <td colspan="3">Widal / Salmonella</td>
    </tr>
    <tr><td>Salmonella Typhi (O)</td><td id="lab_sto"></td><td>≤ 1/40</td></tr>
    <tr><td>Salmonella Paratyphi A – O</td><td id="lab_spa_o"></td><td>≤ 1/40</td></tr>
    <tr><td>Salmonella Paratyphi B – O</td><td id="lab_spb_o"></td><td>≤ 1/40</td></tr>
    <tr><td>Salmonella Paratyphi C – O</td><td id="lab_spc_o"></td><td>≤ 1/40</td></tr>
    <tr><td>Salmonella Typhi (H)</td><td id="lab_sth"></td><td>≤ 1/40</td></tr>
    <tr><td>Salmonella Paratyphi A – H</td><td id="lab_spa_h"></td><td>≤ 1/40</td></tr>
    <tr><td>Salmonella Paratyphi B – H</td><td id="lab_spb_h"></td><td>≤ 1/40</td></tr>
    <tr><td>Salmonella Paratyphi C – H</td><td id="lab_spc_h"></td><td>≤ 1/40</td></tr>
  </tbody>
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
      const set = (id, v) => document.getElementById(id).innerText = v ?? "";

      ["nama","tgl_periksa","tgllahir","umur","alamat","jk",
       "hb","wbc","rbc","plt","hct","mcv","mch","mchc","lym",
       "sto","spa_o","spb_o","spc_o","sth","spa_h","spb_h","spc_h"
      ].forEach(k => set("lab_"+k, d[k]));

      set("lab_petugas", d.petugas);

      const url = `${location.origin}/verify_lab.php?no=${no}&rm=${rm}`;
      lab_qr.innerHTML =
        `<img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=${encodeURIComponent(url)}">`;

      const abnormal = (id, lo, hi) => {
        const el = document.getElementById(id);
        const v = parseFloat((el.innerText||"").replace(",","."));
        if (!isNaN(v) && (v<lo || v>hi)) el.classList.add("cpo-abnormal");
      };

      abnormal("lab_hb",11,17.5);
      abnormal("lab_wbc",4,10.1);
      abnormal("lab_rbc",3.5,5.5);
      abnormal("lab_plt",100,300);
      abnormal("lab_hct",37,50);
      abnormal("lab_mcv",82,95);
      abnormal("lab_mch",27,31);
      abnormal("lab_mchc",32,36);
      abnormal("lab_lym",23.4,40);
    });
});
</script>

</body>