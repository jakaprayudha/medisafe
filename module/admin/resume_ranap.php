<!doctype html>
<html lang="id">

<head>
   <meta charset="utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1" />
   <title>Resume Medis Pasien</title>
   <style>
      :root {
         --bg: #f7f9fb;
         --card: #ffffff;
         --muted: #6b7280;
         --accent: #0f766e;
         --danger: #b91c1c;
         --glass: rgba(15, 118, 110, 0.06);
         --shadow: 0 6px 18px rgba(12, 12, 12, 0.08);
         --radius: 12px;
         font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      }

      html,
      body {
         height: 100%;
         margin: 0;
         background: var(--bg);
         color: #0f172a
      }

      .container {
         max-width: 980px;
         margin: 28px auto;
         padding: 20px
      }

      .card {
         background: var(--card);
         border-radius: var(--radius);
         box-shadow: var(--shadow);
         padding: 18px
      }

      /* Header */
      .header {
         display: flex;
         align-items: center;
         gap: 16px
      }

      .logo {
         width: 72px;
         height: 72px;
         border-radius: 10px;
         background: linear-gradient(135deg, var(--accent), #063f3a);
         display: flex;
         align-items: center;
         justify-content: center;
         color: #fff;
         font-weight: 700;
         font-size: 20px
      }

      .hgroup {
         flex: 1
      }

      .hgroup h1 {
         margin: 0;
         font-size: 20px
      }

      .hgroup p {
         margin: 2px 0 0;
         color: var(--muted);
         font-size: 13px
      }

      .meta {
         display: flex;
         gap: 10px;
         align-items: center
      }

      .badge {
         background: var(--glass);
         padding: 8px 10px;
         border-radius: 8px;
         font-size: 13px;
         color: var(--accent);
      }

      /* Two column layout */
      .grid {
         display: grid;
         grid-template-columns: 1fr 2fr;
         gap: 16px;
         margin-top: 16px
      }

      .section-title {
         font-size: 13px;
         color: var(--muted);
         margin-bottom: 8px
      }

      /* Left column */
      .left .card {
         padding: 12px
      }

      .info-row {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 8px
      }

      .info-label {
         color: var(--muted);
         font-size: 13px
      }

      .info-value {
         font-weight: 600
      }

      .pill {
         display: inline-block;
         padding: 6px 10px;
         border-radius: 999px;
         background: var(--glass);
         font-size: 12px
      }

      /* Vitals */
      .vitals {
         display: flex;
         gap: 8px;
         flex-wrap: wrap
      }

      .vital {
         flex: 1 1 120px;
         background: #f8fafc;
         border-radius: 10px;
         padding: 8px;
         text-align: center
      }

      .vital .v {
         font-weight: 700;
         font-size: 18px
      }

      .vital .l {
         color: var(--muted);
         font-size: 12px
      }

      /* Right column */
      .right .card {
         padding: 14px
      }

      .section {
         margin-bottom: 14px
      }

      .med-list,
      .diag-list,
      .lab-list {
         list-style: none;
         padding: 0;
         margin: 0
      }

      .med-list li,
      .diag-list li,
      .lab-list li {
         padding: 8px;
         border-radius: 8px;
         margin-bottom: 8px;
         background: #fbfbfd;
         border: 1px solid #eef2f6
      }

      .med-top {
         display: flex;
         justify-content: space-between;
         align-items: center
      }

      .notes {
         white-space: pre-wrap;
         background: #fff;
         padding: 12px;
         border-radius: 8px;
         border: 1px dashed #eef2f6
      }

      /* Footer & print */
      .footer {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-top: 18px;
         color: var(--muted);
         font-size: 13px
      }

      @media print {
         body {
            background: #fff
         }

         .container {
            max-width: 100%;
            margin: 0;
            padding: 0
         }

         .card {
            box-shadow: none;
            border-radius: 0
         }

         .grid {
            grid-template-columns: 1fr 2fr
         }

         .logo {
            display: none
         }

         .badge {
            display: inline-block
         }
      }

      /* Responsive */
      @media (max-width:880px) {
         .grid {
            grid-template-columns: 1fr;
         }

         .header {
            flex-direction: row
         }
      }

      /* small helpers */
      .muted {
         color: var(--muted)
      }

      .right .small {
         font-size: 13px;
         color: var(--muted)
      }

      .text-danger {
         color: var(--danger)
      }
   </style>
</head>

<body>
   <div class="container">
      <div class="card header">
         <div class="logo">RS</div>
         <div class="hgroup">
            <h1>Resume Medis Pasien</h1>
            <p class="muted">Ringkasan data medis untuk rujukan cepat — cetak/ekspor sesuai kebutuhan</p>
         </div>
         <div class="meta">
            <div class="badge">No. Rekam: 123456</div>
            <div class="badge">Tanggal: 2025-11-05</div>
         </div>
      </div>

      <div class="grid">
         <!-- LEFT -->
         <div class="left">
            <div class="card">
               <div class="section-title">Data Pasien</div>
               <div class="info-row">
                  <div class="info-label">Nama</div>
                  <div class="info-value">Budi Santoso</div>
               </div>
               <div class="info-row">
                  <div class="info-label">Jenis Kelamin</div>
                  <div class="info-value">Laki-laki</div>
               </div>
               <div class="info-row">
                  <div class="info-label">Tanggal Lahir</div>
                  <div class="info-value">1980-03-15 (45 th)</div>
               </div>
               <div class="info-row">
                  <div class="info-label">Alamat</div>
                  <div class="info-value">Jl. Merdeka No. 12, Jakarta</div>
               </div>
               <div class="info-row">
                  <div class="info-label">No. Telp</div>
                  <div class="info-value">0812-3456-7890</div>
               </div>
               <hr style="margin:12px 0;border:none;border-top:1px solid #eef2f6" />

               <div class="section-title">Alergi</div>
               <div class="pill">Penicillin</div>

               <div style="height:12px"></div>
               <div class="section-title">Vita / Tanda Vital</div>
               <div class="vitals">
                  <div class="vital">
                     <div class="v">120/80</div>
                     <div class="l">Tekanan Darah</div>
                  </div>
                  <div class="vital">
                     <div class="v">72</div>
                     <div class="l">Nadi (bpm)</div>
                  </div>
                  <div class="vital">
                     <div class="v">18</div>
                     <div class="l">RR (x/menit)</div>
                  </div>
                  <div class="vital">
                     <div class="v">36.6°C</div>
                     <div class="l">Suhu</div>
                  </div>
                  <div class="vital">
                     <div class="v">68 kg</div>
                     <div class="l">Berat</div>
                  </div>
               </div>

               <div style="height:12px"></div>
               <div class="section-title">Kontak Darurat</div>
               <div class="info-row">
                  <div class="info-label">Nama</div>
                  <div class="info-value">Siti Aminah</div>
               </div>
               <div class="info-row">
                  <div class="info-label">Hubungan</div>
                  <div class="info-value">Istri</div>
               </div>
               <div class="info-row">
                  <div class="info-label">Telp</div>
                  <div class="info-value">0813-9999-0000</div>
               </div>
            </div>
         </div>

         <!-- RIGHT -->
         <div class="right">
            <div class="card">
               <div class="section">
                  <div class="section-title">Diagnosa Aktif</div>
                  <ul class="diag-list">
                     <li>
                        <div class="med-top">
                           <div><strong>Diabetes Mellitus Tipe 2</strong>
                              <div class="small muted">ICD-10: E11</div>
                           </div>
                           <div class="pill">Masih Dirawat</div>
                        </div>
                        <div class="small muted">Tanggal diagnosa: 2020-02-12</div>
                     </li>
                     <li>
                        <div class="med-top">
                           <div><strong>Hipertensi</strong>
                              <div class="small muted">ICD-10: I10</div>
                           </div>
                           <div class="pill">Kontrol</div>
                        </div>
                     </li>
                  </ul>
               </div>

               <div class="section">
                  <div class="section-title">Obat Saat Ini</div>
                  <ul class="med-list">
                     <li>
                        <div class="med-top">
                           <div><strong>Metformin 500 mg</strong>
                              <div class="small muted">1 tablet, 2x sehari</div>
                           </div>
                           <div class="muted">Oral</div>
                        </div>
                     </li>
                     <li>
                        <div class="med-top">
                           <div><strong>Amlodipine 5 mg</strong>
                              <div class="small muted">1 tablet, 1x sehari</div>
                           </div>
                           <div class="muted">Oral</div>
                        </div>
                     </li>
                  </ul>
               </div>

               <div class="section">
                  <div class="section-title">Hasil Laboratorium Terbaru</div>
                  <ul class="lab-list">
                     <li><strong>HbA1c</strong> — 7.2% (2025-10-20)</li>
                     <li><strong>Gula Darah Puasa</strong> — 145 mg/dL (2025-10-20)</li>
                     <li><strong>Creatinine</strong> — 0.9 mg/dL (2025-10-20)</li>
                  </ul>
               </div>

               <div class="section">
                  <div class="section-title">Ringkasan Perawatan / Plan</div>
                  <div class="notes">- Teruskan Metformin, pantau gula darah harian.
                     - Jadwalkan edukasi diet dan aktivitas.
                     - Kontrol kembali dalam 1 bulan atau lebih cepat jika ada keluhan.
                     - Rujuk ke spesialis nefrologi jika kreatinin naik 20% dari baseline.</div>
               </div>

               <div class="section">
                  <div class="section-title">Riwayat Medis Singkat</div>
                  <div class="small muted">Diabetes sejak 2019 · Hipertensi sejak 2018 · Tidak ada riwayat operasi besar.</div>
               </div>

               <div class="section">
                  <div class="section-title">Catatan Dokter</div>
                  <div class="notes">Pasien stabil. Observasi gula dan tekanan. Perlu modifikasi terapi jika HbA1c > 7.5% pada kontrol berikutnya.</div>
               </div>

               <div class="section" style="display:flex;gap:10px;align-items:center;justify-content:space-between">
                  <div>
                     <div class="small muted">Dokter Penanggung Jawab</div>
                     <div style="font-weight:700">dr. Agus Prasetyo, Sp.PD</div>
                  </div>
                  <div style="text-align:right">
                     <div class="small muted">Tanda Tangan</div>
                     <div style="height:48px;width:180px;border-bottom:1px dashed #e6eef2;margin-top:8px"></div>
                  </div>
               </div>

            </div>
         </div>
      </div>

      <div class="footer muted">
         <div>Generated by KlinikApp • Untuk keperluan medis saja</div>
         <div>Halaman 1 dari 1</div>
      </div>
   </div>
</body>

</html>