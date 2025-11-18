<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Partograf</title>

   <style>
      @page {
         size: A4;
         margin: 10mm;
      }

      body {
         font-family: "Times New Roman", serif;
         font-size: 11pt;
         color: #000;
      }

      .title {
         font-size: 18pt;
         font-weight: bold;
         text-align: center;
         margin-bottom: 10px;
      }

      .top-info {
         font-size: 11pt;
         margin-bottom: 8px;
         line-height: 1.2;
      }

      .line {
         display: inline-block;
         border-bottom: 1px dotted #000;
         width: 120px;
      }

      .bigline {
         display: inline-block;
         border-bottom: 1px dotted #000;
         width: 200px;
      }

      /* ====== GRID PARTOGRAF ====== */
      table.grid {
         border-collapse: collapse;
         width: 100%;
         margin-top: 10px;
      }

      .grid td,
      .grid th {
         border: 1px solid #000;
         width: 20px;
         height: 18px;
         text-align: center;
         font-size: 9pt;
         padding: 0;
      }

      /* Section titles */
      .section-title {
         font-weight: bold;
         margin-top: 10px;
         margin-bottom: 3px;
      }

      .label-left {
         font-size: 10pt;
         font-weight: bold;
         text-align: left;
         padding: 4px 0;
      }

      /* Rotated text for left indicators */
      .rotate {
         writing-mode: vertical-rl;
         transform: rotate(180deg);
         font-size: 9pt;
         text-align: center;
      }
   </style>

</head>

<body>

   <div class="title">PARTOGRAF</div>

   <!-- ======================= HEADER INFO ======================= -->
   <div class="top-info">
      No. Register: <span class="line"></span> &nbsp;&nbsp;
      No. Puskesmas: <span class="line"></span> &nbsp;&nbsp;
      Ketuban pecah sejak jam: <span class="line"></span>
      <br>

      Nama Ibu: <span class="bigline"></span> &nbsp;&nbsp;
      Umur: <span class="line"></span> &nbsp;&nbsp;
      G: <span class="line"></span> P: <span class="line"></span> A: <span class="line"></span>
      <br>

      Tanggal: <span class="line"></span> &nbsp;&nbsp;
      Jam: <span class="line"></span> &nbsp;&nbsp;
      Mules sejak jam: <span class="line"></span>
      <br>

      Alamat: <span class="bigline" style="width:300px;"></span>
   </div>

   <!-- ======================= DENYUT JANTUNG JANIN ======================= -->
   <div class="section-title">Denyut Jantung Janin (per menit)</div>
   <table class="grid">
      <tr>
         <td class="rotate" rowspan="10">Denyut Janin</td>
         <!-- 16 jam waktu -->
         <td colspan="16"></td>
      </tr>

      <!-- 10 baris grid -->
      <?php for ($i = 0; $i < 10; $i++): ?>
         <tr>
            <?php for ($j = 0; $j < 16; $j++): ?>
               <td></td>
            <?php endfor; ?>
         </tr>
      <?php endfor; ?>
   </table>

   <!-- ======================= PEMBUKAAN SERVIKS ======================= -->
   <div class="section-title">Pembukaan Serviks (cm)</div>
   <table class="grid">
      <tr>
         <td class="rotate" rowspan="8">Serviks</td>
         <td colspan="16"></td>
      </tr>
      <?php for ($i = 0; $i < 8; $i++): ?>
         <tr>
            <?php for ($j = 0; $j < 16; $j++): ?>
               <td></td>
            <?php endfor; ?>
         </tr>
      <?php endfor; ?>
   </table>

   <!-- ======================= KONTRAKSI ======================= -->
   <div class="section-title">Kontraksi</div>
   <div class="label-left">
      <span style="display:inline-block;width:14px;height:14px;border:1px solid #000;"></span> &lt; 20 detik &nbsp;
      <span style="display:inline-block;width:14px;height:14px;border:1px solid #000;background:#ccc;"></span> 20–40 detik &nbsp;
      <span style="display:inline-block;width:14px;height:14px;border:1px solid #000;background:#000;"></span> &gt; 40 detik
   </div>

   <table class="grid">
      <tr>
         <td class="rotate" rowspan="6">Kontraksi</td>
         <td colspan="16"></td>
      </tr>
      <?php for ($i = 0; $i < 6; $i++): ?>
         <tr>
            <?php for ($j = 0; $j < 16; $j++): ?>
               <td></td>
            <?php endfor; ?>
         </tr>
      <?php endfor; ?>
   </table>

   <!-- ======================= OKSITOSIN ======================= -->
   <div class="section-title">Oksitosin U/min</div>
   <table class="grid">
      <tr>
         <td class="rotate" rowspan="4">Oksitosin</td>
         <td colspan="16"></td>
      </tr>
      <?php for ($i = 0; $i < 4; $i++): ?>
         <tr>
            <?php for ($j = 0; $j < 16; $j++): ?>
               <td></td>
            <?php endfor; ?>
         </tr>
      <?php endfor; ?>
   </table>

   <!-- ======================= NADI / TEKANAN DARAH ======================= -->
   <div class="section-title">Nadi / Tekanan Darah</div>
   <table class="grid">
      <tr>
         <td class="rotate" rowspan="8">Nadi</td>
         <td colspan="16"></td>
      </tr>
      <?php for ($i = 0; $i < 8; $i++): ?>
         <tr>
            <?php for ($j = 0; $j < 16; $j++): ?>
               <td></td>
            <?php endfor; ?>
         </tr>
      <?php endfor; ?>
   </table>

   <!-- ======================= SUHU ======================= -->
   <div class="section-title">Suhu (°C)</div>
   <table class="grid">
      <tr>
         <td class="rotate" rowspan="4">Suhu</td>
         <td colspan="16"></td>
      </tr>
      <?php for ($i = 0; $i < 4; $i++): ?>
         <tr>
            <?php for ($j = 0; $j < 16; $j++): ?>
               <td></td>
            <?php endfor; ?>
         </tr>
      <?php endfor; ?>
   </table>

   <!-- ======================= URIN ======================= -->
   <div class="section-title">Urin (Protein, Aseton, Volume)</div>
   <table class="grid">
      <tr>
         <td class="rotate" rowspan="4">Urin</td>
         <td colspan="16"></td>
      </tr>
      <?php for ($i = 0; $i < 4; $i++): ?>
         <tr>
            <?php for ($j = 0; $j < 16; $j++): ?>
               <td></td>
            <?php endfor; ?>
         </tr>
      <?php endfor; ?>
   </table>

</body>

</html>