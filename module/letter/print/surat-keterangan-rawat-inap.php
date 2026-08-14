<!DOCTYPE html>
<html lang="id">

<head>

   <meta charset="UTF-8">

   <title>Surat Keterangan Rawat Inap</title>
   <link rel="stylesheet" href="surat-rawatinap.css" />

</head>


<body>


   <!-- =====================================================
       A4
  ====================================================== -->

   <div class="page">


      <!-- =====================================================
         KOP SURAT
    ====================================================== -->

      <?php include 'kop-surat.php' ?>


      <?php

      $id = $_GET['id'] ?? '';

      if (empty($id)) {
         die('ID surat tidak ditemukan.');
      }


      /*
    |--------------------------------------------------------------------------
    | AUTOLOAD
    |--------------------------------------------------------------------------
    */

      require_once '../../../vendor/autoload.php';


      /*
|--------------------------------------------------------------------------
| QR CODE
|--------------------------------------------------------------------------
*/

      use Endroid\QrCode\Builder\Builder;
      use Endroid\QrCode\Writer\PngWriter;


      /*
|--------------------------------------------------------------------------
| URL VERIFIKASI
|--------------------------------------------------------------------------
*/

      $urlVerifikasi =
         'https://app.medisafe.id/module/letter/verifikasi-surat-kematian?id='
         . urlencode(md5($id));


      /*
|--------------------------------------------------------------------------
| BUILD QR
|--------------------------------------------------------------------------
*/

      $builder = new Builder(
         writer: new PngWriter(),
         data: $urlVerifikasi,
         size: 120,
         margin: 5
      );

      $resultQr = $builder->build();


      /*
|--------------------------------------------------------------------------
| BASE64
|--------------------------------------------------------------------------
*/

      $qrBase64 = base64_encode(
         $resultQr->getString()
      );

      ?>

      <?php


      $checkSurat = mysqli_query(
         $koneksi,
         "SELECT 
        ss.*,
        pv.id_doctor,
        pv.patient_name_pcare,
        mp.patient_nik,
        mp.patient_datebirth,
        mp.patient_place,
        mp.patient_gender,
        mp.patient_address,
        pv.id_doctor,
        pv.visit_date,
        dc.sip_number,
        dc.doctor_name
     FROM surat_rawat_inap ss
     INNER JOIN pasien_visit pv 
        ON pv.id_visit = ss.id_visit
    INNER JOIN ms_patient mp
        ON mp.id_patient = pv.id_patient
    LEFT JOIN ms_doctor dc 
        ON dc.doctor_name = ss.id_doctor
     WHERE ss.id = '$id'
     LIMIT 1"
      );
      $dataSurat = mysqli_fetch_array($checkSurat);
      ?>

      <?php

      function tanggalIndonesia($tanggal)
      {
         if (empty($tanggal)) {
            return '-';
         }

         $bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
         ];

         $time = strtotime($tanggal);

         return date('d', $time) . ' ' .
            $bulan[(int)date('m', $time)] . ' ' .
            date('Y', $time);
      }
      ?>

      <?php

      function terbilang($angka)
      {
         $angka = (int) $angka;

         $bilangan = [
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas'
         ];

         if ($angka < 12) {
            return $bilangan[$angka];
         }

         if ($angka < 20) {
            return terbilang($angka - 10) . ' belas';
         }

         if ($angka < 100) {
            return terbilang((int) ($angka / 10))
               . ' puluh '
               . terbilang($angka % 10);
         }

         if ($angka < 200) {
            return 'seratus ' . terbilang($angka - 100);
         }

         if ($angka < 1000) {
            return terbilang((int) ($angka / 100))
               . ' ratus '
               . terbilang($angka % 100);
         }

         if ($angka < 2000) {
            return 'seribu ' . terbilang($angka - 1000);
         }

         if ($angka < 1000000) {
            return terbilang((int) ($angka / 1000))
               . ' ribu '
               . terbilang($angka % 1000);
         }

         return (string) $angka;
      }
      ?>

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
               <td class="label">Nama Lengkap</td>
               <td class="separator">:</td>
               <td class="value"><?= $dataSurat['patient_name_pcare'] ?></td>
            </tr>

            <tr>
               <td class="label">NIK</td>
               <td class="separator">:</td>
               <td><?= $dataSurat['patient_nik'] ?></td>
            </tr>

            <tr>
               <td class="label">Tempat, Tanggal Lahir</td>
               <td class="separator">:</td>
               <td><?= $dataSurat['patient_place'] ?>, <?= $dataSurat['patient_datebirth'] ?></td>
            </tr>

            <tr>
               <td class="label">Jenis Kelamin</td>
               <td class="separator">:</td>
               <td><?= $dataSurat['patient_gender'] ?></td>
            </tr>

            <tr>
               <td class="label">Alamat</td>
               <td class="separator">:</td>
               <td><?= $dataSurat['patient_address'] ?></td>
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
                        $dataSurat['tanggal_pulang'] ?? '-'
                     ) ?>

                  </td>

               </tr>


               <tr>

                  <td class="label">
                     Lama Rawat
                  </td>

                  <td class="value">

                     <?= htmlspecialchars(
                        $dataSurat['lama'] ?? '-'
                     ) ?>

                     <?php if (!empty($dataSurat['lama'])): ?>

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
                        $dataSurat['diagnosa'] ?? '-'
                     ) ?>

                  </td>

               </tr>


               <tr>

                  <td class="label">
                     Dokter Penanggung Jawab
                  </td>

                  <td class="value">

                     <?= htmlspecialchars(
                        $dataSurat['id_doctor'] ?? '-'
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

                  <?= htmlspecialchars(
                     !empty($dataClinic['faskes_city'])
                        ? $dataClinic['faskes_city']
                        : 'Deli Serdang'
                  ) ?>,

                  <?= tanggalIndonesia(
                     $dataSurat['tanggal_surat']
                  ) ?>

               </div>


               <div class="jabatan">

                  Dokter Pemeriksa,

               </div>


               <!-- QR VERIFIKASI -->

               <div class="qr-verifikasi">

                  <img
                     src="data:image/png;base64,<?= $qrBase64 ?>"
                     alt="QR Verifikasi">

                  <div>
                     Scan untuk verifikasi
                  </div>

               </div>


               <!-- NAMA DOKTER -->

               <div class="nama-dokter">

                  <?= htmlspecialchars(
                     $dataSurat['id_doctor'] ?? '-'
                  ) ?>

               </div>


               <!-- SIP -->

               <div class="sip">

                  SIP.
                  <?= htmlspecialchars(
                     $dataSurat['sip_number'] ?? '-'
                  ) ?>

               </div>

            </div>

         </div>


      </div>



      <!-- =====================================================
         FOOTER
    ====================================================== -->

      <div class="footer">
         Surat ini diterbitkan melalui Sistem Informasi Klinik <strong>Medisafe</strong> dan merupakan
         dokumen resmi fasilitas pelayanan kesehatan. <br> <i>www.medisafe.id</i>
      </div>

   </div>


</body>

</html>