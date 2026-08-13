<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Surat Keterangan Berobat</title>
  <link rel="stylesheet" href="surat-berobat.css" />
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
      'https://app.medisafe.id/module/letter/verifikasi-surat-berobat?id='
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
        pv.visit_time,
        pv.id_poli,
        mp.nomor_rm,
        pv.visit_date,
        dc.sip_number
     FROM surat_berobat ss
     INNER JOIN pasien_visit pv 
        ON pv.id_visit = ss.id_visit
    INNER JOIN ms_patient mp
        ON mp.id_patient = pv.id_patient
    LEFT JOIN ms_doctor dc 
        ON dc.doctor_name = pv.id_doctor
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


    <div class="judul">
      <h1>Surat Keterangan Berobat</h1>

      <div class="nomor">Nomor: <?= $dataSurat['nomor_surat'] ?></div>
    </div>

    <!-- =================================
             ISI SURAT
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
      </table>

      <!-- =================================
                 KETERANGAN BEROBAT
            ================================== -->

      <div class="keterangan">
        Dengan ini menerangkan bahwa pasien tersebut di atas
        <strong>benar telah datang dan mendapatkan pelayanan kesehatan /
          pemeriksaan medis</strong>
        di <strong><?= $dataClinic['clinic_name'] ?></strong> pada tanggal
        <strong><?= tanggalIndonesia($dataSurat['visit_date']) ?></strong>.
      </div>

      <!-- =================================
                 DETAIL KUNJUNGAN
            ================================== -->

      <table class="detail-kunjungan">
        <thead>
          <tr>
            <th>Keterangan</th>

            <th>Detail</th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td>Tanggal Berobat</td>

            <td><?= tanggalIndonesia($dataSurat['visit_date']) ?></td>
          </tr>

          <tr>
            <td>Waktu Pelayanan</td>

            <td><?= $dataSurat['visit_time'] ?></td>
          </tr>

          <tr>
            <td>Poli / Unit Pelayanan</td>

            <td><?= tanggalIndonesia($dataSurat['id_poli']) ?></td>
          </tr>

          <tr>
            <td>Dokter Pemeriksa</td>

            <td><?= tanggalIndonesia($dataSurat['id_doctor']) ?></td>
          </tr>

          <tr>
            <td>Nomor Rekam Medis</td>

            <td><?= $dataSurat['nomor_rm'] ?></td>
          </tr>
        </tbody>
      </table>

      <!-- =================================
                 KETERANGAN TAMBAHAN
            ================================== -->

      <div class="tambahan">
        Surat keterangan ini diberikan kepada yang bersangkutan untuk dapat
        dipergunakan sebagai bukti bahwa yang bersangkutan telah menjalani
        pemeriksaan dan mendapatkan pelayanan kesehatan pada fasilitas
        pelayanan kesehatan kami.
      </div>

      <!-- =================================
                 KESIMPULAN
            ================================== -->

      <div class="kesimpulan">
        Demikian surat keterangan berobat ini dibuat dengan sebenar-benarnya
        untuk dapat dipergunakan sebagaimana mestinya.
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