<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Surat Keterangan Sakit</title>
  <link rel="stylesheet" href="surat-sakit.css" />


</head>

<body>
  <!-- =====================================
         TOOLBAR
    ====================================== -->

  <div class="toolbar no-print">
    <button type="button" class="btn-print" onclick="window.print()">
      🖨 Cetak Surat
    </button>
  </div>

  <!-- =====================================
         A4 DOCUMENT
    ====================================== -->

  <div class="page">
    <!-- =================================
             KOP SURAT
        ================================== -->

    <?php include 'kop-surat.php' ?>

    <!-- =================================
             JUDUL
        ================================== -->

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
      'https://app.medisafe.id/module/letter/verifikasi-surat-sakit?id='
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
        pv.nmDiag1,
        pv.kdDiag1,
        pv.diagnosa,
        cd.icd10
     FROM surat_sakit ss
     INNER JOIN pasien_visit pv 
        ON pv.id_visit = ss.id_visit
    INNER JOIN ms_patient mp
        ON mp.id_patient = pv.id_patient
    LEFT JOIN ms_doctor dc 
        ON dc.doctor_name = pv.id_doctor
    LEFT JOIN icd_10 cd ON cd.code = pv.diagnosa
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



    <div class="judul">
      <h1>Surat Keterangan Sakit</h1>

      <div class="nomor">Nomor: <?= $dataSurat['nomor_surat'] ?></div>
    </div>

    <!-- =================================
             ISI
        ================================== -->

    <div class="isi">
      <div class="pembuka">
        Yang bertanda tangan di bawah ini, Dokter pada
        <strong><?= $dataClinic['clinic_name'] ?></strong>, menerangkan bahwa:
      </div>

      <!-- =================================
                 IDENTITAS PASIEN
            ================================== -->


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

        <tr>
          <td class="label">Diagnosa</td>
          <td class="separator">:</td>
          <td>
            <?php
            if (!empty($dataSurat['kdDiag1'])) {
              echo $dataSurat['kdDiag1'] . ' - ' . ($dataSurat['nmDiag1'] ?? '-');
            } elseif (!empty($dataSurat['diagnosa'])) {
              echo $dataSurat['diagnosa'] . ' - ' . ($dataSurat['icd10'] ?? '-');
            } else {
              echo '-';
            }
            ?>
          </td>
        </tr>
      </table>

      <!-- =================================
                 KETERANGAN
            ================================== -->

      <div class="keterangan">
        Berdasarkan hasil pemeriksaan kesehatan yang telah dilakukan pada
        tanggal
        <strong><?= tanggalIndonesia($dataSurat['visit_date']) ?></strong></strong>, terhadap yang bersangkutan
        dinyatakan dalam kondisi yang memerlukan
        <strong>istirahat</strong> dan tidak dapat melaksanakan aktivitas
        seperti biasa untuk sementara waktu.
      </div>

      <!-- =================================
                 PERIODE ISTIRAHAT
            ================================== -->

      <div class="periode">
        <div class="periode-title">Keterangan Istirahat</div>

        <table>
          <tr>
            <td class="label">Mulai Istirahat</td>

            <td class="separator">:</td>

            <td><?= tanggalIndonesia($dataSurat['tanggal_mulai']) ?></td>
          </tr>

          <tr>
            <td class="label">Sampai Dengan</td>

            <td class="separator">:</td>

            <td><?= tanggalIndonesia($dataSurat['tanggal_selesai']) ?></td>
          </tr>

          <tr>
            <td class="label">Lama Istirahat</td>

            <td class="separator">:</td>

            <td>
              <strong>
                <?= htmlspecialchars($dataSurat['lama']) ?>
                (<?= terbilang($dataSurat['lama']) ?>) hari
              </strong>
            </td>
          </tr>

          <tr>
            <td class="label">Keterangan</td>

            <td class="separator">:</td>

            <td><?= $dataSurat['keterangan'] ?></td>
          </tr>
        </table>
      </div>

      <!-- =================================
                 KESIMPULAN
            ================================== -->

      <div class="kesimpulan">
        Dengan demikian, yang bersangkutan diberikan keterangan untuk
        <span class="highlight"> beristirahat selama <?= htmlspecialchars($dataSurat['lama']) ?>
          (<?= terbilang($dataSurat['lama']) ?>) hari </span>,
        terhitung mulai tanggal
        <strong><?= tanggalIndonesia($dataSurat['tanggal_mulai']) ?></strong>
        sampai dengan
        <strong><?= tanggalIndonesia($dataSurat['tanggal_selesai']) ?></strong>.
      </div>

      <div class="kesimpulan">
        Demikian surat keterangan sakit ini dibuat dengan sebenar-benarnya
        untuk dapat dipergunakan sebagaimana mestinya.
      </div>

      <!-- =================================
                 TANDA TANGAN DOKTER
            ================================== -->

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

    <!-- =================================
             FOOTER
        ================================== -->

    <div class="footer">
      Surat ini diterbitkan melalui Sistem Informasi Klinik <strong>Medisafe</strong> dan merupakan
      dokumen resmi fasilitas pelayanan kesehatan. <br> <i>www.medisafe.id</i>
    </div>
  </div>
</body>

</html>