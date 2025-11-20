<body class="lbp-body">

   <style>
      @page {
         size: A4;
         margin: 1.5cm;
      }

      .lbp-body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
      }

      .lbp-header {
         text-align: center;
         margin-bottom: 10px;
      }

      .lbp-header img {
         width: 120px;
         margin-bottom: 5px;
      }

      .lbp-title {
         font-weight: bold;
         text-transform: uppercase;
         margin-top: 5px;
      }

      .lbp-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 15px;
      }

      .lbp-table th,
      .lbp-table td {
         border: 1px solid #000;
         vertical-align: top;
         padding: 6px;
      }

      .lbp-table th {
         text-align: center;
         font-weight: bold;
      }

      .lbp-col-tgl {
         width: 12%;
      }

      .lbp-col-uraian {
         width: 68%;
      }

      .lbp-col-ttdpasien {
         width: 20%;
         text-align: center;
      }

      .lbp-sign-area {
         margin-top: 40px;
         width: 100%;
         text-align: right;
      }

      .lbp-doc-sign {
         margin-top: 60px;
         display: inline-block;
         text-align: center;
      }

      .lbp-doc-line {
         border-top: 1px solid #000;
         width: 180px;
         margin: 3px auto 0 auto;
         padding-top: 2px;
      }

      .lbp-no-print {
         text-align: center;
         margin-top: 20px;
      }

      @media print {
         .lbp-no-print {
            display: none;
         }
      }
   </style>
   <?php include 'kopsurat.php'; ?>
   <div class="lbp-header">
      <img src="../../../assets/images/logos/logobpjs.png" alt="BPJS Logo">
      <div class="lbp-title">LEMbar Bukti Pelayanan (LBP)</div>
      <div class="lbp-title">KLAIM RITP</div>
      <div class="lbp-title">BPJS Kesehatan Cabang Lubuk Pakam</div>
   </div>

   <table class="lbp-table">
      <tr>
         <th class="lbp-col-tgl">TGL</th>
         <th class="lbp-col-uraian">URAIAN PELAYANAN</th>
         <th class="lbp-col-ttdpasien">TANDA TANGAN PASIEN</th>
      </tr>
      <tbody id="lbp_body"></tbody>
   </table>

   <div class="lbp-sign-area">
      <div>Dokter yang merawat</div>
      <div class="lbp-doc-sign">
         <div style="height:60px;">(ttd)</div>
         <div class="lbp-doc-line">dr. .......................</div>
      </div>
   </div>

   <div class="lbp-no-print">
      <button onclick="window.print()">🖨 Cetak Halaman</button>
   </div>

</body>

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
                  <td>${new Date(row.tgl_pelayanan).toLocaleDateString("id-ID",{day:"2-digit",month:"2-digit"})}</td>
                  <td>${row.uraian.replace(/\n/g,"<br>")}</td>
                  <td>✒️</td>
               </tr>
            `;
            });

            document.getElementById("lbp_body").innerHTML = html;
         });
   });
</script>