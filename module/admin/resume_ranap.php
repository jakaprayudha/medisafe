<div class="resume-page">

   <style>
      .resume-page {
         width: 210mm;
         min-height: 297mm;
         padding: 12mm;
         margin: 0 auto;
         font-family: "Inter", sans-serif;
         background: white;
         box-sizing: border-box;
      }

      .resume-page .header {
         display: flex;
         align-items: center;
         gap: 16px;
         margin-bottom: 14px;
      }

      .resume-page .container {
         width: 100%;
      }

      .resume-page .card {
         background: #ffffff;
         padding: 12px;
         border: 1px solid #e5e7eb;
         border-radius: 8px;
      }

      .resume-page .grid {
         display: grid;
         grid-template-columns: 1.2fr 2fr;
         gap: 12px;
         margin-top: 12px;
      }

      .resume-page .section-title {
         font-size: 12px;
         color: #6b7280;
         margin-bottom: 6px;
      }

      .resume-page .info-row {
         display: flex;
         justify-content: space-between;
         margin-bottom: 6px;
      }

      .resume-page .info-label {
         color: #6b7280;
         font-size: 12px;
      }

      .resume-page .info-value {
         font-weight: 600;
         font-size: 12px;
      }

      .resume-page .vitals {
         display: flex;
         gap: 6px;
         flex-wrap: wrap;
      }

      .resume-page .vital {
         flex: 1 1 100px;
         background: #f0fdf4;
         border: 1px solid #d1fae5;
         border-radius: 6px;
         padding: 6px;
         text-align: center;
      }

      .resume-page .med-list li,
      .resume-page .diag-list li {
         border: 1px solid #e5e7eb;
         background: #fafafa;
         border-radius: 6px;
         padding: 6px;
         margin-bottom: 6px;
      }

      .resume-page .notes {
         white-space: pre-wrap;
         background: #fafafa;
         border: 1px dashed #d1d5db;
         padding: 8px;
         border-radius: 6px;
      }

      @media print {
         .resume-page {
            padding: 8mm;
            background: white;
            page-break-after: always;
         }
      }
   </style>

   <?php include 'kopsurat.php'; ?>

   <div class="container">

      <!-- grid kiri & kanan -->
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
            </div>
         </div>

         <!-- RIGHT -->
         <div class="right">
            <div class="card">
               <div class="section-title">Diagnosa Aktif</div>
               <ul class="diag-list">
                  <li>DM Tipe 2 — E11</li>
                  <li>Hipertensi — I10</li>
               </ul>
            </div>
         </div>

      </div>

   </div>

</div>