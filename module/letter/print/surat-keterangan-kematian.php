<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Surat Keterangan Catatan Kematian</title>
  <link rel="stylesheet" href="surat-kematian.css" />
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
         DOKUMEN A4
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
     FROM surat_kematian ss
     INNER JOIN pasien_visit pv 
        ON pv.id_visit = ss.id_visit
    INNER JOIN ms_patient mp
        ON mp.id_patient = pv.id_patient
    LEFT JOIN ms_doctor dc 
        ON dc.id_doctor = ss.dokter_menyatakan
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
      <h1>Surat Keterangan Catatan Kematian</h1>

      <div class="nomor">Nomor: <?= $dataSurat['nomor_surat'] ?></div>
    </div>

    <!-- =================================
             ISI SURAT
        ================================== -->

    <div class="isi">
      <div class="pembuka">
        Yang bertanda tangan di bawah ini, Dokter pada
        <strong><?= $dataClinic['clinic_name'] ?></strong>, berdasarkan catatan pelayanan
        kesehatan dan/atau hasil pemeriksaan yang dilakukan, menerangkan
        bahwa:
      </div>

      <!-- =================================
                 IDENTITAS ALMARHUM/ALMARHUMAH
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
      </table>

      <!-- =================================
                 CATATAN KEMATIAN
            ================================== -->

      <div class="section-title">Catatan Kematian</div>

      <table class="kematian">
        <thead>
          <tr>
            <th>Keterangan</th>

            <th>Data</th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td class="label">Tanggal Kematian</td>

            <td><?= tanggalIndonesia($dataSurat['tanggal_kematian']) ?></td>
          </tr>

          <tr>
            <td class="label">Waktu Kematian</td>

            <td><?= $dataSurat['waktu_kematian'] ?> WIB</td>
          </tr>

          <tr>
            <td class="label">Tempat Kematian</td>

            <td><?= $dataClinic['clinic_name'] ?></td>
          </tr>

          <tr>
            <td class="label">Ruangan / Unit</td>

            <td><?= $dataSurat['ruangan'] ?></td>
          </tr>

          <tr>
            <td class="label">Dokter yang Menyatakan</td>

            <td><?= $dataSurat['doctor_name'] ?></td>
          </tr>
        </tbody>
      </table>

      <!-- =================================
                 PERNYATAAN
            ================================== -->

      <div class="section-title">Pernyataan</div>

      <div class="pernyataan-box">
        <strong>
          Menerangkan bahwa yang bersangkutan telah dinyatakan meninggal dunia
          pada tanggal
          <u><?= tanggalIndonesia($dataSurat['tanggal_kematian']) ?></u>, pukul <u><?= $dataSurat['waktu_kematian'] ?> WIB</u>, di
          <u><?= $dataClinic['clinic_name'] ?></u>.
        </strong>

        <br /><br />

        Keterangan ini dibuat berdasarkan catatan pelayanan kesehatan dan
        pemeriksaan yang dilakukan oleh tenaga medis yang berwenang pada
        fasilitas pelayanan kesehatan tersebut.
      </div>

      <!-- =================================
                 CATATAN
            ================================== -->

      <div class="catatan">
        Catatan: Surat ini merupakan catatan/keterangan pelayanan fasilitas
        kesehatan dan bukan merupakan dokumen kependudukan atau akta kematian
        yang diterbitkan oleh instansi yang berwenang.
      </div>

      <!-- =================================
                 PENUTUP
            ================================== -->

      <div class="pernyataan">
        Demikian surat keterangan catatan kematian ini dibuat dengan
        sebenar-benarnya untuk dapat dipergunakan sebagaimana mestinya.
      </div>

      <!-- =================================
                 TANDA TANGAN
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