<body>
   <?php include 'kopsurat.php'; ?>

   <div class="form-triase">
      <style>
         @page {
            size: A4;
            margin: 1.5cm;
         }

         body {
            font-family: "Times New Roman", serif;
            font-size: 10pt;
         }

         table {
            width: 100%;
            border-collapse: collapse;
         }

         td,
         th {
            border: 1px solid #000;
            padding: 5px;
         }

         .title {
            text-align: center;
            font-weight: bold;
            font-size: 16pt;
            margin: 10px 0;
         }

         /* BADGE WARNA TRIASE */
         .badge-triase {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            color: #fff;
         }

         .merah {
            background: #d9534f;
         }

         .kuning {
            background: #f0ad4e;
         }

         .hijau {
            background: #5cb85c;
         }

         .hitam {
            background: #000;
         }

         .no-print {
            margin-top: 10px;
            text-align: center;
         }

         @media print {
            .no-print {
               display: none;
            }
         }

         /* QR & Barcode */
         .signature-box,
         .qr-box,
         .barcode-box {
            margin-top: 15px;
            text-align: center;
         }

         .signature-img {
            width: 140px;
            height: auto;
         }
      </style>

      <div class="title">FORMULIR TRIASE KEGAWATDARURATAN</div>

      <!-- ================= IDENTITAS PASIEN ================= -->
      <table>
         <tr>
            <td>Nama Pasien: <b><span id="tri_nama"></span></b></td>
            <td>No RM: <b><span id="tri_rm"></span></b></td>
            <td rowspan="2" class="barcode-box">
               <img id="barcode_rm" src="" alt="Barcode RM">
            </td>
         </tr>
         <tr>
            <td>Kelamin: <b><span id="tri_jk"></span></b></td>
            <td>Usia: <b><span id="tri_usia"></span></b></td>
         </tr>
      </table>

      <table>
         <tr>
            <td>Dokter: <b><span id="tri_dokter"></span></b></td>
            <td>Kategori Triase:
               <span id="tri_kategori_badge" class="badge-triase">-</span>
            </td>
         </tr>
      </table>

      <!-- ================= KELUHAN ================= -->
      <h4>Keluhan Utama</h4>
      <table>
         <tr>
            <td><span id="tri_keluhan"></span></td>
         </tr>
      </table>

      <!-- ================= VITAL SIGN ================= -->
      <h4>Pemeriksaan Vital Sign</h4>
      <table>
         <tr>
            <th>Tekanan Darah</th>
            <th>Nadi</th>
            <th>RR</th>
            <th>Suhu</th>
            <th>SpO₂</th>
         </tr>
         <tr>
            <td><span id="tri_td"></span></td>
            <td><span id="tri_nadi"></span></td>
            <td><span id="tri_rr"></span></td>
            <td><span id="tri_suhu"></span></td>
            <td><span id="tri_spo2"></span></td>
         </tr>
      </table>

      <!-- ================= GCS ================= -->
      <h4>GCS</h4>
      <table>
         <tr>
            <th>Mata (E)</th>
            <th>Verbal (V)</th>
            <th>Motorik (M)</th>
            <th>Total</th>
         </tr>
         <tr>
            <td><span id="tri_gcs_e"></span></td>
            <td><span id="tri_gcs_v"></span></td>
            <td><span id="tri_gcs_m"></span></td>
            <td><b><span id="tri_gcs_total"></span></b></td>
         </tr>
      </table>

      <!-- ================= SKALA NYERI ================= -->
      <h4>Skala Nyeri</h4>
      <table>
         <tr>
            <td><span id="tri_nyeri"></span> / 10</td>
         </tr>
      </table>

      <!-- ================= REFERENSI TRIASE ================= -->
      <h4>Referensi Penentuan Triase</h4>
      <table>
         <tr>
            <td><span id="tri_referensi"></span></td>
         </tr>
      </table>

      <!-- ================= CATATAN ================= -->
      <h4>Catatan Tambahan</h4>
      <table>
         <tr>
            <td><span id="tri_catatan"></span></td>
         </tr>
      </table>

      <!-- ================= QR PETUGAS + TTD ================= -->
      <div class="qr-box">
         <h4>QR Code Petugas</h4>
         <img id="qr_petugas" src="" width="130">
      </div>

      <div class="signature-box">
         <h4>Tanda Tangan Pemeriksa</h4>
         <img id="ttd_petugas" class="signature-img" src="" alt="Tanda Tangan">
         <div><b><span id="nama_petugas"></span></b></div>
      </div>

      <div class="no-print">
         <button onclick="window.print()">🖨 Cetak Halaman</button>
      </div>

   </div>
</body>

<script>
   document.addEventListener("DOMContentLoaded", function() {

      const params = new URLSearchParams(window.location.search);
      const no = params.get("no");
      const rm = params.get("rm");

      if (!no || !rm) return;

      /* ================= FETCH DATA TRIASE ================= */
      fetch(`../../../controller/ranap/getFormTriase.php?no=${no}&rm=${rm}`)
         .then(res => res.json())
         .then(resp => {
            if (!resp || resp.status !== "success") return;

            const p = resp.pasien || {};
            const t = resp.triase || {};

            /* ===== Identitas ===== */
            document.getElementById("tri_nama").innerText = p.nama_pasien || "";
            document.getElementById("tri_rm").innerText = rm;
            document.getElementById("tri_jk").innerText = p.jk || "";
            document.getElementById("tri_usia").innerText = p.usia || "";
            document.getElementById("tri_dokter").innerText = p.doctor_name || "";

            /* ===== Warna Triase ===== */
            let badge = document.getElementById("tri_kategori_badge");
            badge.innerText = t.triase || "-";

            if (t.triase === "Merah") badge.classList.add("merah");
            else if (t.triase === "Kuning") badge.classList.add("kuning");
            else if (t.triase === "Hijau") badge.classList.add("hijau");
            else if (t.triase === "Hitam") badge.classList.add("hitam");

            /* ===== Keluhan ===== */
            document.getElementById("tri_keluhan").innerText = t.keluhan_utama || "";

            /* ===== Vital sign ===== */
            document.getElementById("tri_td").innerText = t.tekanan_darah || "";
            document.getElementById("tri_nadi").innerText = t.nadi || "";
            document.getElementById("tri_rr").innerText = t.rr || "";
            document.getElementById("tri_suhu").innerText = t.suhu || "";
            document.getElementById("tri_spo2").innerText = t.spo2 || "";

            /* ===== GCS ===== */
            document.getElementById("tri_gcs_e").innerText = t.gcs_e || "";
            document.getElementById("tri_gcs_v").innerText = t.gcs_v || "";
            document.getElementById("tri_gcs_m").innerText = t.gcs_m || "";
            document.getElementById("tri_gcs_total").innerText = t.gcs_total || "";

            /* ===== Nyeri ===== */
            document.getElementById("tri_nyeri").innerText = t.skala_nyeri || "";

            /* ===== Referensi & Catatan ===== */
            document.getElementById("tri_referensi").innerText = t.referensi_triase || "";
            document.getElementById("tri_catatan").innerText = t.catatan || "";

            /* ================= BARCODE NO RM ================= */
            document.getElementById("barcode_rm").src =
               `https://barcode.tec-it.com/barcode.ashx?data=${rm}&code=Code128&dpi=96`;

            /* ================= QR CODE PETUGAS ================= */
            if (p.doctor_name)
               document.getElementById("qr_petugas").src =
               `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(p.doctor_name)}`;

            /* ================= TANDA TANGAN DIGITAL ================= */
            if (p.ttd_path) {
               document.getElementById("ttd_petugas").src = "../../../uploads/signature/" + p.ttd_path;
            } else {
               document.getElementById("ttd_petugas").style.display = "none";
            }

            document.getElementById("nama_petugas").innerText = p.doctor_name || "-";
         });
   });
</script>