<!DOCTYPE html>
<html lang="id">

<head>

   <meta charset="UTF-8">

   <title>Surat Keterangan Rawat Inap</title>

   <style>
      * {
         box-sizing: border-box;
      }

      html,
      body {
         margin: 0;
         padding: 0;
         background: #f2f2f2;
         font-family: "Times New Roman", Times, serif;
         color: #000;
      }

      /* =====================================================
       A4
    ===================================================== */

      .page {
         width: 210mm;
         min-height: 297mm;

         margin: 20px auto;

         padding: 12mm 16mm 14mm 16mm;

         background: #fff;

         position: relative;
      }

      /* =====================================================
       KOP SURAT
    ===================================================== */

      .kop {
         display: flex;

         align-items: center;

         border-bottom: 2.5px solid #000;

         padding-bottom: 5px;
      }

      .kop-logo {
         width: 23mm;

         text-align: center;

         flex-shrink: 0;
      }

      .kop-logo img {
         max-width: 20mm;

         max-height: 20mm;

         object-fit: contain;
      }

      .kop-content {
         flex: 1;

         text-align: center;

         line-height: 1.15;
      }

      .nama-instansi {
         font-size: 14pt;

         font-weight: bold;

         text-transform: uppercase;
      }

      .nama-klinik {
         font-size: 17pt;

         font-weight: bold;

         text-transform: uppercase;

         margin-top: 1px;
      }

      .alamat {
         font-size: 8.5pt;

         margin-top: 2px;
      }

      .kontak {
         font-size: 8.5pt;

         margin-top: 1px;
      }

      .kop-empty {
         width: 23mm;

         flex-shrink: 0;
      }

      /* =====================================================
       JUDUL
    ===================================================== */

      .judul {
         text-align: center;

         margin-top: 16px;
      }

      .judul h1 {
         margin: 0;

         font-size: 14pt;

         text-decoration: underline;

         text-transform: uppercase;
      }

      .nomor {
         margin-top: 3px;

         font-size: 10pt;
      }

      /* =====================================================
       ISI
    ===================================================== */

      .isi {
         margin-top: 16px;

         font-size: 11pt;

         line-height: 1.4;
      }

      .pembuka {
         text-align: justify;

         margin-bottom: 10px;
      }

      /* =====================================================
       IDENTITAS
    ===================================================== */

      .identitas {
         width: 100%;

         margin: 5px 0 12px 0;

         border-collapse: collapse;
      }

      .identitas td {
         vertical-align: top;

         padding: 3px 0;

         font-size: 11pt;
      }

      .identitas .label {
         width: 43mm;
      }

      .identitas .separator {
         width: 6mm;

         text-align: center;
      }

      .identitas .value {
         font-weight: bold;
      }

      /* =====================================================
       PERNYATAAN
    ===================================================== */

      .pernyataan {
         text-align: justify;

         margin-top: 8px;

         line-height: 1.45;
      }

      /* =====================================================
       INFORMASI RAWAT INAP
    ===================================================== */

      .rawat-inap {
         width: 100%;

         border-collapse: collapse;

         margin-top: 12px;

         page-break-inside: avoid;
      }

      .rawat-inap th,
      .rawat-inap td {
         border: 1px solid #000;

         padding: 6px 8px;

         font-size: 10.5pt;

         vertical-align: top;
      }

      .rawat-inap th {
         text-align: center;

         font-weight: bold;
      }

      .rawat-inap .label {
         width: 40%;
      }

      .rawat-inap .value {
         width: 60%;
      }

      /* =====================================================
       KETERANGAN
    ===================================================== */

      .keterangan {
         margin-top: 14px;

         text-align: justify;

         line-height: 1.45;
      }

      .keterangan-box {
         border: 1px solid #000;

         padding: 9px 11px;

         margin-top: 7px;

         min-height: 25mm;

         text-align: justify;

         line-height: 1.45;
      }

      /* =====================================================
       CATATAN
    ===================================================== */

      .catatan {
         margin-top: 12px;

         font-size: 8pt;

         font-style: italic;

         text-align: justify;

         line-height: 1.3;
      }

      /* =====================================================
       TANDA TANGAN
    ===================================================== */

      .ttd-wrapper {
         display: flex;

         justify-content: flex-end;

         margin-top: 15mm;

         page-break-inside: avoid;
      }

      .ttd {
         width: 75mm;

         text-align: center;

         font-size: 10pt;
      }

      .tempat-tanggal {
         margin-bottom: 4px;
      }

      .jabatan {
         margin-bottom: 2px;
      }

      .space-ttd {
         height: 22mm;
      }

      .nama-dokter {
         font-weight: bold;

         text-decoration: underline;
      }

      .sip {
         margin-top: 2px;
      }

      /* =====================================================
       QR
    ===================================================== */

      .qr-verifikasi {
         text-align: center;

         margin-top: 5px;
      }

      .qr-verifikasi img {
         width: 22mm;

         height: 22mm;

         display: block;

         margin: 0 auto 2px auto;
      }

      .qr-text {
         font-size: 6.5pt;
      }

      /* =====================================================
       FOOTER
    ===================================================== */

      .footer {
         position: absolute;

         bottom: 7mm;

         left: 16mm;

         right: 16mm;

         border-top: 1px solid #999;

         padding-top: 3px;

         text-align: center;

         font-size: 7pt;

         color: #555;
      }

      /* =====================================================
       PRINT
    ===================================================== */

      @page {
         size: A4 portrait;

         margin: 0;
      }

      @media print {

         html,
         body {
            width: 210mm;

            min-height: 297mm;

            margin: 0;

            padding: 0;

            background: #fff;
         }

         .page {
            width: 210mm;

            min-height: 297mm;

            margin: 0;

            padding: 12mm 16mm 14mm 16mm;

            box-shadow: none;

            page-break-after: avoid;
         }

         .no-print {
            display: none !important;
         }

         table,
         tr,
         td,
         th {
            page-break-inside: avoid;
         }

         .kop,
         .judul,
         .isi,
         .rawat-inap,
         .ttd-wrapper {
            page-break-inside: avoid;
         }
      }
   </style>

</head>


<body>


   <!-- =====================================================
       A4
  ====================================================== -->

   <div class="page">


      <!-- =====================================================
         KOP SURAT
    ====================================================== -->

      <div class="kop">


         <div class="kop-logo">

            <img
               src="../../../assets/images/logos/logodeliserdang.png"
               alt="Logo">

         </div>


         <div class="kop-content">

            <div class="nama-instansi">
               PEMERINTAH KABUPATEN DELI SERDANG
            </div>


            <div class="nama-klinik">
               <?= htmlspecialchars(
                  $dataClinic['clinic_name'] ?? '-'
               ) ?>
            </div>


            <div class="alamat">

               <?= htmlspecialchars(
                  $dataClinic['faskes_address'] ?? '-'
               ) ?>,

               <?= htmlspecialchars(
                  $dataClinic['faskes_city'] ?? 'Deli Serdang'
               ) ?>

               -

               <?= htmlspecialchars(
                  $dataClinic['faskes_prov'] ?? '-'
               ) ?>

            </div>


            <div class="kontak">

               Telp.

               <?= htmlspecialchars(
                  $dataClinic['pic_phone'] ?? '-'
               ) ?>

               &nbsp; | &nbsp;

               Email:

               <?= htmlspecialchars(
                  $dataClinic['pic_email'] ?? '-'
               ) ?>

            </div>

         </div>


         <div class="kop-empty"></div>


      </div>



      <!-- =====================================================
         JUDUL
    ====================================================== -->

      <div class="judul">

         <h1>
            SURAT KETERANGAN RAWAT INAP
         </h1>


         <div class="nomor">

            Nomor:
            <strong>
               <?= htmlspecialchars(
                  $dataSurat['nomor_surat'] ?? '-'
               ) ?>
            </strong>

         </div>

      </div>



      <!-- =====================================================
         ISI
    ====================================================== -->

      <div class="isi">


         <div class="pembuka">

            Yang bertanda tangan di bawah ini, menerangkan bahwa:

         </div>



         <!-- =================================================
           IDENTITAS PASIEN
      ================================================== -->

         <table class="identitas">

            <tr>

               <td class="label">
                  Nama
               </td>

               <td class="separator">
                  :
               </td>

               <td class="value">

                  <?= htmlspecialchars(
                     $dataSurat['patient_name'] ?? '-'
                  ) ?>

               </td>

            </tr>


            <tr>

               <td class="label">
                  Nomor Rekam Medis
               </td>

               <td class="separator">
                  :
               </td>

               <td class="value">

                  <?= htmlspecialchars(
                     $dataSurat['nomor_rm'] ?? '-'
                  ) ?>

               </td>

            </tr>


            <tr>

               <td class="label">
                  NIK
               </td>

               <td class="separator">
                  :
               </td>

               <td>

                  <?= htmlspecialchars(
                     $dataSurat['patient_nik'] ?? '-'
                  ) ?>

               </td>

            </tr>


            <tr>

               <td class="label">
                  Tempat / Tanggal Lahir
               </td>

               <td class="separator">
                  :
               </td>

               <td>

                  <?= htmlspecialchars(
                     $dataSurat['patient_place'] ?? '-'
                  ) ?>

                  /

                  <?= htmlspecialchars(
                     $dataSurat['patient_datebirth'] ?? '-'
                  ) ?>

               </td>

            </tr>


            <tr>

               <td class="label">
                  Jenis Kelamin
               </td>

               <td class="separator">
                  :
               </td>

               <td>

                  <?= htmlspecialchars(
                     $dataSurat['patient_gender'] ?? '-'
                  ) ?>

               </td>

            </tr>


            <tr>

               <td class="label">
                  Alamat
               </td>

               <td class="separator">
                  :
               </td>

               <td>

                  <?= htmlspecialchars(
                     $dataSurat['patient_address'] ?? '-'
                  ) ?>

               </td>

            </tr>

         </table>



         <!-- =================================================
           PERNYATAAN
      ================================================== -->

         <div class="pernyataan">

            Berdasarkan hasil pemeriksaan dan pelayanan medis yang
            telah diberikan, pasien tersebut telah mendapatkan
            pelayanan kesehatan dan menjalani <strong>rawat inap</strong>
            di fasilitas pelayanan kesehatan kami dengan rincian
            sebagai berikut:

         </div>



         <!-- =================================================
           INFORMASI RAWAT INAP
      ================================================== -->

         <table class="rawat-inap">

            <thead>

               <tr>

                  <th>
                     Informasi
                  </th>

                  <th>
                     Keterangan
                  </th>

               </tr>

            </thead>


            <tbody>

               <tr>

                  <td class="label">
                     Tanggal Masuk
                  </td>

                  <td class="value">

                     <?= htmlspecialchars(
                        $dataSurat['tanggal_masuk'] ?? '-'
                     ) ?>

                  </td>

               </tr>


               <tr>

                  <td class="label">
                     Tanggal Keluar
                  </td>

                  <td class="value">

                     <?= htmlspecialchars(
                        $dataSurat['tanggal_keluar'] ?? '-'
                     ) ?>

                  </td>

               </tr>


               <tr>

                  <td class="label">
                     Lama Rawat
                  </td>

                  <td class="value">

                     <?= htmlspecialchars(
                        $dataSurat['lama_rawat'] ?? '-'
                     ) ?>

                     <?php if (!empty($dataSurat['lama_rawat'])): ?>

                        hari

                     <?php endif; ?>

                  </td>

               </tr>


               <tr>

                  <td class="label">
                     Ruangan
                  </td>

                  <td class="value">

                     <?= htmlspecialchars(
                        $dataSurat['ruangan'] ?? '-'
                     ) ?>

                  </td>

               </tr>


               <tr>

                  <td class="label">
                     Diagnosis
                  </td>

                  <td class="value">

                     <?= htmlspecialchars(
                        $dataSurat['diagnosis'] ?? '-'
                     ) ?>

                  </td>

               </tr>


               <tr>

                  <td class="label">
                     Dokter Penanggung Jawab
                  </td>

                  <td class="value">

                     <?= htmlspecialchars(
                        $dataSurat['dokter'] ?? '-'
                     ) ?>

                  </td>

               </tr>

            </tbody>

         </table>



         <!-- =================================================
           KETERANGAN
      ================================================== -->

         <div class="keterangan">

            <strong>
               Keterangan:
            </strong>


            <div class="keterangan-box">

               <?php

               $keterangan =
                  trim(
                     $dataSurat['keterangan'] ?? ''
                  );

               if ($keterangan !== ''):

               ?>

                  <?= nl2br(
                     htmlspecialchars(
                        $keterangan
                     )
                  ) ?>

               <?php else: ?>

                  Pasien telah menjalani perawatan
                  rawat inap sesuai dengan pelayanan
                  medis yang diberikan.

               <?php endif; ?>

            </div>

         </div>



         <!-- =================================================
           PENUTUP
      ================================================== -->

         <div class="pernyataan">

            Demikian surat keterangan ini dibuat dengan sebenarnya
            untuk dapat dipergunakan sebagaimana mestinya.

         </div>



         <!-- =================================================
           TANDA TANGAN
      ================================================== -->

         <div class="ttd-wrapper">


            <div class="ttd">


               <div class="tempat-tanggal">

                  Deli Serdang,

                  <?= htmlspecialchars(
                     $dataSurat['tanggal_surat'] ?? '-'
                  ) ?>

               </div>


               <div class="jabatan">

                  Dokter Penanggung Jawab

               </div>


               <!-- QR -->

               <div class="qr-verifikasi">

                  <?php if (!empty($qrBase64)): ?>

                     <img
                        src="data:image/png;base64,<?= $qrBase64 ?>"
                        alt="QR Verifikasi">

                     <div class="qr-text">

                        Scan untuk verifikasi dokumen

                     </div>

                  <?php endif; ?>

               </div>


               <div class="space-ttd"></div>


               <div class="nama-dokter">

                  <?= htmlspecialchars(
                     $dataSurat['dokter'] ?? '-'
                  ) ?>

               </div>


               <div class="sip">

                  SIP:
                  <?= htmlspecialchars(
                     $dataSurat['sip_dokter'] ?? '-'
                  ) ?>

               </div>


            </div>


         </div>


      </div>



      <!-- =====================================================
         FOOTER
    ====================================================== -->

      <div class="footer">

         Dokumen ini diterbitkan secara elektronik melalui
         Sistem Informasi Klinik dan dapat diverifikasi
         menggunakan QR Code yang tercantum pada dokumen.

      </div>


   </div>


</body>

</html>