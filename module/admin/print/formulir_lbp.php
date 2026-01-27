<body class="lbp-body">

<style>
@page {
  size: A4;
  margin: 1.5cm;
}

/* =========================
   BODY & CONTAINER (SAMA)
========================= */
body.lbp-body {
  font-family: "Times New Roman", serif;
  font-size: 11pt;
  margin: 0;
  background: #fff;
  color: #000;
}

.lbp-container {
  max-width: 760px;
  margin: auto;
}

/* =========================
   HEADER
========================= */
.lbp-header {
  text-align: center;
  margin-bottom: 12px;
}

.lbp-header img {
  width: 110px;
  margin-bottom: 6px;
}

.lbp-title {
  font-weight: bold;
  text-transform: uppercase;
  font-size: 13pt;
  margin: 2px 0;
}

/* =========================
   TABLE
========================= */
.lbp-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

.lbp-table th,
.lbp-table td {
  border: 1px solid #000;
  padding: 6px;
  vertical-align: top;
  font-size: 11pt;
}

.lbp-table th {
  background: #f2f2f2;
  text-align: center;
  font-weight: bold;
}

.lbp-col-tgl { width: 12%; text-align: center; }
.lbp-col-uraian { width: 68%; }
.lbp-col-ttd { width: 20%; text-align: center; }

/* =========================
   SIGNATURE
========================= */
.lbp-sign-area {
  margin-top: 35px;
  text-align: right;
}

.lbp-doc-sign {
  margin-top: 45px;
  display: inline-block;
  text-align: center;
}

.lbp-doc-sign img {
  height: 60px;
  object-fit: contain;
  display: block;
  margin: 0 auto 4px;
}

.lbp-doc-line {
  border-top: 1px solid #000;
  width: 180px;
  padding-top: 4px;
  font-weight: bold;
  font-size: 11pt;
}

/* =========================
   PRINT
========================= */
.lbp-no-print {
  text-align: center;
  margin-top: 15px;
}

@media print {
  .lbp-no-print {
    display: none;
  }
}
</style>

<div class="lbp-container">

<?php include 'kopsurat.php'; ?>

<!-- ================= HEADER ================= -->
<div class="lbp-header">
  <img src="../../../assets/images/logos/logobpjs.png" alt="BPJS Logo">
  <div class="lbp-title">Lembar Bukti Pelayanan (LBP)</div>
  <div class="lbp-title">Klaim RITP</div>
  <div class="lbp-title">BPJS Kesehatan Cabang Lubuk Pakam</div>
</div>

<!-- ================= TABLE ================= -->
<table class="lbp-table">
  <thead>
    <tr>
      <th class="lbp-col-tgl">TGL</th>
      <th class="lbp-col-uraian">URAIAN PELAYANAN</th>
      <th class="lbp-col-ttd">TANDA TANGAN PASIEN</th>
    </tr>
  </thead>
  <tbody id="lbp_body"></tbody>
</table>

<!-- ================= SIGNATURE ================= -->
<div class="lbp-sign-area">
  Dokter yang Merawat
  <div class="lbp-doc-sign">
    <img src="../../../uploads/ttd/drdevi.png" alt="TTD Dokter">
    <div class="lbp-doc-line">dr. Devi Eka Pertiwi</div>
  </div>
</div>

<div class="lbp-no-print">
  <button onclick="window.print()">🖨 Cetak Halaman</button>
</div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const url = new URLSearchParams(window.location.search);
  const visit = url.get("no");
  const rm = url.get("rm");

  if (!visit || !rm) return;

  fetch(`getlbp.php?visit=${visit}&rm=${rm}`)
    .then(res => res.json())
    .then(resp => {
      if (!resp || resp.status !== "success") return;

      let html = "";
      resp.data.forEach(row => {
        html += `
          <tr>
            <td class="lbp-col-tgl">
              ${new Date(row.tgl_pelayanan).toLocaleDateString("id-ID",{day:"2-digit",month:"2-digit"})}
            </td>
            <td class="lbp-col-uraian">
              ${row.uraian.replace(/\\n/g,"<br>")}
            </td>
            <td class="lbp-col-ttd">
              <img src="../../../uploads/ttd/regina.png"
                   style="height:40px; object-fit:contain;">
            </td>
          </tr>
        `;
      });

      document.getElementById("lbp_body").innerHTML = html;
    });
});
</script>

</body>