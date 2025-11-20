<body>
   <?php include 'kopsurat.php'; ?>

   <div class="form-triase">
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

         body {
            font-family: "Times New Roman", serif;
            font-size: 11pt;
         }

         table {
            width: 100%;
            border-collapse: collapse;
         }

         td,
         th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
         }

         .title {
            text-align: center;
            font-weight: bold;
            font-size: 16pt;
            margin-bottom: 15px;
         }

         .badge-triase {
            font-weight: bold;
            padding: 3px 7px;
            color: #fff;
            border-radius: 4px;
         }

         .ats1 {
            background: #d9534f;
         }

         .ats2 {
            background: #f0ad4e;
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

         .head-ats1 {
            background: #d9534f;
            color: #fff;
            text-align: center;
         }

         .head-ats2 {
            background: #f0ad4e;
            color: #000;
            text-align: center;
         }

         .head-ats3 {
            background: #5cb85c;
            color: #fff;
            text-align: center;
         }

         .head-ats4 {
            background: #000;
            color: #fff;
            text-align: center;
         }

         .section-title {
            margin-top: 14px;
            margin-bottom: 5px;
            font-weight: bold;
         }

         .signature-box,
         .qr-box {
            margin-top: 20px;
            text-align: center;
         }

         .signature-img {
            width: 140px;
         }

         @media print {
            .no-print {
               display: none;
            }
         }

         .ats-label {
            font-weight: bold;
            background: #f5f5f5;
            width: 18%;
         }

         .cb-cell label {
            display: block;
            margin-bottom: 2px;
         }

         .pain-scale {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
         }

         .pain-item {
            text-align: center;
            font-size: 28px;
            /* <<< ukuran emoji besar */
            width: 55px;
         }

         .pain-item span {
            display: block;
            font-size: 12px;
            margin-top: 3px;
         }

         .pain-selected {
            border: 3px solid red;
            border-radius: 10px;
            padding: 5px;
            background: #ffe5e5;
         }
      </style>

      <div class="title">FORMULIR TRIASE KEGAWATDARURATAN</div>

      <!-- HEADER PASIEN -->
      <table>
         <tr>
            <td>Nama Pasien: <b><span id="tri_nama"></span></b></td>
            <td>No RM: <b><span id="tri_rm"></span></b></td>
            <td rowspan="2" style="text-align:center;">
               <img id="barcode_rm" src="" height="40">
            </td>
         </tr>
         <tr>
            <td>Jenis Kelamin: <b><span id="tri_jk"></span></b></td>
            <td>Usia: <b><span id="tri_usia"></span></b></td>
         </tr>
      </table>

      <table>
         <tr>
            <td>Dokter Pemeriksa: <b><span id="tri_dokter"></span></b></td>
            <td>Kategori ATS:
               <span id="tri_kategori_badge" class="badge-triase">-</span>
            </td>
         </tr>
      </table>

      <!-- DATA KELUHAN -->
      <div class="section-title">Keluhan Utama</div>
      <table>
         <tr>
            <td id="tri_keluhan"></td>
         </tr>
      </table>

      <!-- VITAL SIGN -->
      <div class="section-title">Pemeriksaan Vital Sign</div>
      <table>
         <tr>
            <th>Tekanan Darah</th>
            <th>Nadi</th>
            <th>RR</th>
            <th>Suhu</th>
            <th>SpO₂</th>
         </tr>
         <tr>
            <td id="tri_td"></td>
            <td id="tri_nadi"></td>
            <td id="tri_rr"></td>
            <td id="tri_suhu"></td>
            <td id="tri_spo2"></td>
         </tr>
      </table>

      <!-- GCS -->
      <div class="section-title">GCS</div>
      <table>
         <tr>
            <th>Mata (E)</th>
            <th>Verbal (V)</th>
            <th>Motorik (M)</th>
            <th>Total</th>
         </tr>
         <tr>
            <td id="tri_gcs_e"></td>
            <td id="tri_gcs_v"></td>
            <td id="tri_gcs_m"></td>
            <td><b id="tri_gcs_total"></b></td>
         </tr>
      </table>

      <!-- SKALA NYERI -->
      <div class="section-title">Skala Nyeri</div>

      <div id="painScale" class="pain-scale">
         <!-- Akan diisi otomatis lewat JS -->
      </div>

      <table style="margin-top: 10px;">
         <tr>
            <td><b>Nilai Nyeri:</b> <span id="tri_nyeri"></span> / 10</td>
         </tr>
      </table>

      <!-- BLOK ATS NON PSIKIATRI -->
      <div class="section-title">Australasian Triage Scale (ATS) – Pemeriksaan Non Psikiatri</div>

      <table>
         <tr>
            <th style="background:#e9e9e9; text-align:center;">Pemeriksaan</th>
            <th class="head-ats1">ATS 1</th>
            <th class="head-ats2">ATS 2</th>
            <th class="head-ats3">ATS 3</th>
            <th class="head-ats4">ATS 4</th>
         </tr>

         <!-- A. Airway -->
         <tr>
            <td class="ats-label">A. Airway</td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Sumbatan jalan nafas"> Sumbatan Jalan Nafas</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Tidak ada sumbatan ATS2"> Tidak ada sumbatan</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Tidak ada sumbatan ATS3"> Tidak ada sumbatan</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Doa (Tanda kehidupan, tanda ada denyut nadi, RC, EKG Flat)"> Doa (Tanda kehidupan, tanda ada denyut nadi, RC, EKG Flat)</label>
            </td>
         </tr>

         <!-- B. Breathing -->
         <tr>
            <td class="ats-label">B. Breathing</td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Henti Nafas"> Henti Nafas</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="RR < 10 / Distress pernafasan berat"> RR &lt; 10 x/menit, distress pernafasan berat</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Takipnea Distress pernafasan sedang"> Takipnea, distress pernafasan sedang</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Dipsnea"> Dispnea</label>
            </td>
         </tr>

         <!-- C. Circulation -->
         <tr>
            <td class="ats-label">C. Circulation</td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Henti Jantung"> Henti Jantung</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Sistolik < 80 MmHg"> Sistolik &lt; 80 mmHg</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Gangguan sirkulasi (Akral dingin, Nadi < 50 atau> 150, Banyak kehilangan darah, denggan dengan latargi)"> Gangguan sirkulasi (akral dingin, Nadi &lt;50 atau &gt;150, perdarahan, letargi)</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Muntah atau diare tanda dehidrasi"> Muntah/diare tanda dehidrasi</label>
            </td>
         </tr>

         <!-- D. Disability -->
         <tr>
            <td class="ats-label">D. Disability</td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Nyeri berat yang tidak respon dengan obat"> Nyeri berat yang tidak respon dengan obat</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Nyeri Sedang"> Nyeri sedang</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Cedera kepala ringan"> Cedera kepala ringan</label>
            </td>
            <td class="cb-cell">-</td>
         </tr>

         <!-- E. Exposure -->
         <tr>
            <td class="ats-label">E. Exposure</td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Kejang Berkelanjutan"> Kejang berkelanjutan</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Nyeri dada tipikal"> Nyeri dada tipikal</label>
               <label><input type="checkbox" class="cb-ats" value="Nyeri hebat"> Nyeri hebat</label>
               <label><input type="checkbox" class="cb-ats" value="Deficit Neurologis (hemiparesa, dispasia)"> Defisit neurologis (hemiparesa, dispasia)</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Riwayat kejang"> Riwayat kejang</label>
               <label><input type="checkbox" class="cb-ats" value="Riwayat pingsan"> Riwayat pingsan</label>
               <label><input type="checkbox" class="cb-ats" value="Deformitas laserasi"> Deformitas/laserasi</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Luka keci (luka lecet, luka robek kecil)"> Luka kecil (lecet/robek kecil)</label>
               <label><input type="checkbox" class="cb-ats" value="Kunjungan ulang untuk ganti verban evaluasi jahitan"> Kunjungan ulang ganti verban/evaluasi jahitan</label>
            </td>
         </tr>

         <!-- F. Psikiatri/Psikologi -->
         <tr>
            <td class="ats-label">F. Psikiatri / Psikologi</td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Gangguan perilaku berat yang mengancam diri pasien dan orang lain"> Gangguan perilaku berat mengancam diri & orang lain</label>
               <label><input type="checkbox" class="cb-ats" value="Membawa Senjata Tajam"> Membawa senjata tajam</label>
               <label><input type="checkbox" class="cb-ats" value="Merusak diri sendiri"> Merusak diri sendiri</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Datang dengan Renstrain"> Datang dengan restrain</label>
               <label><input type="checkbox" class="cb-ats" value="Perilaku kejam"> Perilaku kejam</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Agresif secara fisik dan ringan"> Agresif fisik ringan</label>
               <label><input type="checkbox" class="cb-ats" value="Agresif secara fisik dan lisan"> Agresif fisik dan lisan</label>
               <label><input type="checkbox" class="cb-ats" value="Mengancam / membahayakan keselamatan diri sendiri maupun orang lain"> Mengancam/membahayakan diri/orang lain</label>
            </td>
            <td class="cb-cell">
               <label><input type="checkbox" class="cb-ats" value="Keluhan minor yang saat berkunjung masih dirasakan"> Keluhan minor</label>
            </td>
         </tr>
      </table>

      <!-- OPSIONAL: teks referensi asli -->
      <table style="margin-top:5px;">
         <tr>
            <td style="font-size:9pt;">
               <b>Ringkasan Referensi Terpilih:</b>
               <span id="tri_referensi_text"></span>
            </td>
         </tr>
      </table>

      <!-- CATATAN -->
      <div class="section-title">Catatan Tambahan</div>
      <table>
         <tr>
            <td id="tri_catatan"></td>
         </tr>
      </table>

      <!-- QR PETUGAS -->
      <div class="qr-box">
         <h4>QR Petugas Pemeriksa</h4>
         <img id="qr_petugas" width="150">
      </div>

      <!-- TTD -->
      <div class="signature-box">
         <h4>Tanda Tangan Pemeriksa</h4>
         <img id="ttd_petugas" class="signature-img">
         <div><b id="nama_petugas"></b></div>
      </div>

      <div class="no-print">
         <button onclick="window.print()">🖨 Cetak Halaman</button>
      </div>

   </div>
</body>

<script>
   function renderPainScale(level) {

      const painFaces = [
         "😀", "🙂", "🙂", "😐", "😐", "😩", "😫", "😣", "😭", "😭", "😭"
      ];

      const painText = [
         "Tidak Nyeri",
         "Nyeri Ringan",
         "Nyeri Ringan",
         "Nyeri Sedang",
         "Nyeri Sedang",
         "Nyeri Berat",
         "Nyeri Sangat Berat",
         "Nyeri Sangat Berat",
         "Nyeri Tak Tertahankan",
         "Nyeri Tak Tertahankan",
         "Nyeri Tak Tertahankan"
      ];

      let html = "";

      for (let i = 0; i <= 10; i++) {
         html += `
         <div class="pain-item ${i == level ? "pain-selected" : ""}">
            ${painFaces[i]}
            <span>${i}</span>
         </div>
      `;
      }

      document.getElementById("painScale").innerHTML = html;
   }
   document.addEventListener("DOMContentLoaded", () => {

      const params = new URLSearchParams(window.location.search);
      const no = params.get("no");
      const rm = params.get("rm");

      if (!no || !rm) return;

      fetch(`../../../controller/ranap/getFormTriase.php?no=${no}&rm=${rm}`)
         .then(r => r.json())
         .then(res => {

            const p = res.pasien || {};
            const t = res.triase || {};

            // IDENTITAS
            document.getElementById("tri_nama").innerText = p.nama_pasien || "";
            document.getElementById("tri_rm").innerText = p.nomor_rm || rm;
            document.getElementById("tri_jk").innerText = p.jk || "";
            document.getElementById("tri_usia").innerText = p.usia || "";
            document.getElementById("tri_dokter").innerText = p.doctor_name || "";

            // KATEGORI ATS → Badge warna
            const badge = document.getElementById("tri_kategori_badge");
            badge.innerText = t.triase || "-";
            const kelas = (t.triase || "").replace(" ", "").toLowerCase(); // "ATS 1" -> "ats1"
            if (kelas) badge.classList.add(kelas);

            // KELUHAN
            document.getElementById("tri_keluhan").innerText = t.keluhan_utama || "";

            // VITAL SIGN
            document.getElementById("tri_td").innerText = t.tekanan_darah || "";
            document.getElementById("tri_nadi").innerText = t.nadi || "";
            document.getElementById("tri_rr").innerText = t.rr || "";
            document.getElementById("tri_suhu").innerText = t.suhu || "";
            document.getElementById("tri_spo2").innerText = t.spo2 || "";

            // GCS
            document.getElementById("tri_gcs_e").innerText = t.gcs_e || "";
            document.getElementById("tri_gcs_v").innerText = t.gcs_v || "";
            document.getElementById("tri_gcs_m").innerText = t.gcs_m || "";
            document.getElementById("tri_gcs_total").innerText = t.gcs_total || "";

            // NYERI
            document.getElementById("tri_nyeri").innerText = t.skala_nyeri || "";
            renderPainScale(parseInt(t.skala_nyeri || 0));

            // REFERENSI (teks asli)
            document.getElementById("tri_referensi_text").innerText = t.referensi_triase || "";

            // CATATAN
            document.getElementById("tri_catatan").innerText = t.catatan || "";

            // BARCODE
            document.getElementById("barcode_rm").src =
               `https://barcode.tec-it.com/barcode.ashx?data=${encodeURIComponent(p.nomor_rm || rm)}&code=Code128`;

            // QR PETUGAS
            if (p.doctor_name) {
               document.getElementById("qr_petugas").src =
                  `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(p.doctor_name)}`;
            }

            // TTD DIGITAL (jika tersedia)
            if (p.ttd_path) {
               document.getElementById("ttd_petugas").src = "../../../uploads/signature/" + p.ttd_path;
            } else {
               document.getElementById("ttd_petugas").style.display = "none";
            }

            document.getElementById("nama_petugas").innerText = p.doctor_name || "-";

            // ============================
            //  AUTO CENTANG ATS CHECKBOX
            // ============================
            const ref = (t.referensi_triase || "")
               .split("|")
               .map(v => v.trim())
               .filter(v => v.length > 0);

            document.querySelectorAll(".cb-ats").forEach(cb => {
               if (ref.includes(cb.value)) {
                  cb.checked = true;
               }
            });
         });
   });
</script>