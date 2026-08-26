<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Surat Keterangan Sehat</title>
  <link rel="stylesheet" href="surat.css" />
</head>

<body>

  <!-- TOOLBAR -->
  <div class="toolbar no-print">
    <button class="btn-print" onclick="window.print()">🖨 Cetak Surat</button>
  </div>

  <!-- A4 -->
  <div class="page">
    <?php include 'kop-surat.php' ?>
    <!-- =========================
             JUDUL
        ========================== -->
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
      'https://app.medisafe.id/module/letter/verifikasi-surat?id='
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
        dc.sip_number,
        pv.suhu,
        pv.nmDiag1,
        pv.kdDiag1,
        pv.diagnosa,
        cd.icd10
     FROM surat_sehat ss
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

    function keteranganTekananDarah($value)
    {
      if (empty($value)) {
        return '-';
      }

      // Contoh: 120/80
      $parts = preg_split('/[\/\-]/', trim($value));

      if (count($parts) < 2) {
        return '-';
      }

      $sistolik = (int) $parts[0];
      $diastolik = (int) $parts[1];

      if ($sistolik < 90 || $diastolik < 60) {
        return 'Di bawah batas rujukan';
      }

      if ($sistolik >= 140 || $diastolik >= 90) {
        return 'Di atas batas rujukan';
      }

      return 'Dalam batas rujukan';
    }


    function keteranganNadi($value)
    {
      if ($value === '' || $value === null) {
        return '-';
      }

      $nadi = (int) preg_replace('/[^0-9]/', '', $value);

      if ($nadi < 60) {
        return 'Di bawah batas rujukan';
      }

      if ($nadi > 100) {
        return 'Di atas batas rujukan';
      }

      return 'Dalam batas rujukan';
    }


    function keteranganSuhu($value)
    {
      if ($value === '' || $value === null) {
        return '-';
      }

      $suhu = (float) str_replace(',', '.', $value);

      if ($suhu < 36.0) {
        return 'Di bawah batas rujukan';
      }

      if ($suhu > 37.5) {
        return 'Di atas batas rujukan';
      }

      return 'Dalam batas rujukan';
    }


    function keteranganBMI($value)
    {
      if ($value === '' || $value === null) {
        return '-';
      }

      $bmi = (float) str_replace(',', '.', $value);

      if ($bmi < 18.5) {
        return 'Berat badan kurang';
      }

      if ($bmi < 25) {
        return 'Normal';
      }

      if ($bmi < 30) {
        return 'Berat badan berlebih';
      }

      return 'Obesitas';
    } ?>

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
      <h1>Surat Keterangan Sehat</h1>

      <div class="nomor">Nomor: <?= $dataSurat['nomor_surat'] ?></div>
    </div>

    <!-- =========================
             ISI
        ========================== -->

    <div class="isi">
      <div class="pembuka">
        Yang bertanda tangan di bawah ini, Dokter pada
        <strong><?= $dataClinic['clinic_name'] ?></strong>, menerangkan bahwa:
      </div>

      <!-- IDENTITAS PASIEN -->

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

      <div class="pernyataan">
        Berdasarkan hasil pemeriksaan kesehatan yang telah dilakukan pada
        tanggal
        <strong><?= tanggalIndonesia($dataSurat['tanggal_surat']) ?></strong>, terhadap yang bersangkutan diperoleh
        hasil bahwa kondisi kesehatan yang bersangkutan dalam keadaan
        <strong>SEHAT</strong> dan dinyatakan layak untuk melakukan aktivitas
        sesuai dengan keperluannya.
      </div>

      <!-- HASIL PEMERIKSAAN -->

      <table class="pemeriksaan">

        <thead>

          <tr>

            <th>Jenis Pemeriksaan</th>

            <th>Hasil</th>

            <th>Keterangan</th>

          </tr>

        </thead>


        <tbody>


          <!-- TEKANAN DARAH -->

          <tr>

            <td>
              Tekanan Darah
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['tekanan_darah'] ?? '-'
              ) ?>

              mmHg
            </td>

            <td>
              <?= keteranganTekananDarah(
                $dataSurat['tekanan_darah'] ?? ''
              ) ?>
            </td>

          </tr>


          <!-- NADI -->

          <tr>

            <td>
              Nadi
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['nadi'] ?? '-'
              ) ?>

              x/menit
            </td>

            <td>
              <?= keteranganNadi(
                $dataSurat['nadi'] ?? ''
              ) ?>
            </td>

          </tr>


          <!-- SUHU -->

          <tr>

            <td>
              Suhu
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['suhu'] ?? '-'
              ) ?>

              <sup>o</sup>C
            </td>

            <td>
              <?= keteranganSuhu(
                $dataSurat['suhu'] ?? ''
              ) ?>
            </td>

          </tr>


          <!-- BERAT BADAN -->

          <tr>

            <td>
              Berat Badan
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['berat_badan'] ?? '-'
              ) ?>

              Kg
            </td>

            <td>
              -
            </td>

          </tr>


          <!-- TINGGI BADAN -->

          <tr>

            <td>
              Tinggi Badan
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['tinggi_badan'] ?? '-'
              ) ?>

              cm
            </td>

            <td>
              -
            </td>

          </tr>


          <!-- BMI -->

          <?php if (
            isset($dataSurat['bmi']) &&
            $dataSurat['bmi'] !== ''
          ): ?>

            <tr>

              <td>
                Indeks Massa Tubuh (BMI)
              </td>

              <td>
                <?= htmlspecialchars(
                  $dataSurat['bmi']
                ) ?>
              </td>

              <td>
                <?= keteranganBMI(
                  $dataSurat['bmi']
                ) ?>
              </td>

            </tr>

          <?php endif; ?>


        </tbody>

      </table>

      <div class="keperluan">
        Surat keterangan sehat ini dibuat untuk keperluan:

        <strong> <?= strtoupper($dataSurat['keperluan']) ?> </strong>

        dan dapat dipergunakan sebagaimana mestinya.
      </div>

      <!-- KESIMPULAN -->

      <div class="kesimpulan">
        Demikian surat keterangan ini dibuat dengan sebenar- benarnya untuk
        dapat dipergunakan sebagaimana mestinya.
      </div>

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

    <!-- FOOTER -->

    <div class="footer">
      Surat ini diterbitkan melalui Sistem Informasi Klinik <strong>Medisafe</strong> dan merupakan
      dokumen resmi fasilitas pelayanan kesehatan. <br> <i>www.medisafe.id</i>
    </div>
  </div>
</body>

</html>