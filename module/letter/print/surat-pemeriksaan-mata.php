<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Surat Hasil Pemeriksaan Mata</title>
  <link rel="stylesheet" href="surat-mata.css" />


</head>

<body>
  <!-- =====================================
         TOOLBAR
    ====================================== -->

  <div class="toolbar no-print">
    <button type="button" class="btn-print" onclick="window.print()">
      🖨 Cetak Hasil Pemeriksaan
    </button>
  </div>

  <!-- =====================================
         DOCUMENT
    ====================================== -->

  <div class="page">
    <!-- =================================
             KOP
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
      'https://app.medisafe.id/module/letter/verifikasi-surat-mata?id='
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
        dc.sip_number
     FROM surat_pemeriksaan_mata ss
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

    <?php

    function kategoriTekananDarah($value)
    {
      if (empty($value)) {
        return '-';
      }

      $parts = preg_split('/[\/\-]/', $value);

      if (count($parts) < 2) {
        return '-';
      }

      $sistol = (int) trim($parts[0]);
      $diastol = (int) trim($parts[1]);

      if ($sistol < 90 || $diastol < 60) {
        return 'Rendah';
      }

      if ($sistol < 120 && $diastol < 80) {
        return 'Normal';
      }

      if ($sistol < 130 && $diastol < 80) {
        return 'Elevated';
      }

      if ($sistol < 140 || $diastol < 90) {
        return 'Hipertensi Derajat 1';
      }

      if ($sistol < 180 || $diastol < 120) {
        return 'Hipertensi Derajat 2';
      }

      return 'Krisis Hipertensi';
    }


    function kategoriNadi($value)
    {
      if ($value === '' || $value === null) {
        return '-';
      }

      $nadi = (float) $value;

      if ($nadi < 60) {
        return 'Bradikardia';
      }

      if ($nadi <= 100) {
        return 'Normal';
      }

      return 'Takikardia';
    }


    function kategoriSuhu($value)
    {
      if ($value === '' || $value === null) {
        return '-';
      }

      $suhu = (float) $value;

      if ($suhu < 36.0) {
        return 'Hipotermia';
      }

      if ($suhu <= 37.5) {
        return 'Normal';
      }

      if ($suhu <= 38.0) {
        return 'Subfebris';
      }

      if ($suhu <= 39.0) {
        return 'Demam';
      }

      return 'Demam Tinggi';
    }


    function kategoriRespirasi($value)
    {
      if ($value === '' || $value === null) {
        return '-';
      }

      $respirasi = (float) $value;

      if ($respirasi < 12) {
        return 'Bradipnea';
      }

      if ($respirasi <= 20) {
        return 'Normal';
      }

      return 'Takipnea';
    }


    $kategoriTekananDarah =
      kategoriTekananDarah(
        $dataSurat['tekanan_darah'] ?? ''
      );

    $kategoriNadi =
      kategoriNadi(
        $dataSurat['nadi'] ?? ''
      );

    $kategoriSuhu =
      kategoriSuhu(
        $dataSurat['suhu'] ?? ''
      );

    $kategoriRespirasi =
      kategoriRespirasi(
        $dataSurat['respirasi'] ?? ''
      );

    ?>

    <?php

    /*
|--------------------------------------------------------------------------
| KATEGORI GULA DARAH SEWAKTU
|--------------------------------------------------------------------------
*/

    function kategoriGulaDarah($value)
    {
      if ($value === '' || $value === null) {
        return '-';
      }

      $nilai = (float) $value;

      if ($nilai < 70) {
        return 'Rendah';
      }

      if ($nilai <= 200) {
        return 'Normal';
      }

      return 'Tinggi';
    }


    /*
|--------------------------------------------------------------------------
| KATEGORI KOLESTEROL TOTAL
|--------------------------------------------------------------------------
*/

    function kategoriKolesterol($value)
    {
      if ($value === '' || $value === null) {
        return '-';
      }

      $nilai = (float) $value;

      if ($nilai < 200) {
        return 'Normal';
      }

      if ($nilai < 240) {
        return 'Batas Tinggi';
      }

      return 'Tinggi';
    }


    /*
|--------------------------------------------------------------------------
| KATEGORI ASAM URAT
|--------------------------------------------------------------------------
|
| Rentang umum dewasa:
| Laki-laki : sekitar 3.4 - 7.0 mg/dL
| Perempuan : sekitar 2.4 - 6.0 mg/dL
|
| Karena data jenis kelamin tersedia pada pasien,
| kategori dapat disesuaikan dengan gender.
|--------------------------------------------------------------------------
*/

    function kategoriAsamUrat($value, $gender = '')
    {
      if ($value === '' || $value === null) {
        return '-';
      }

      $nilai = (float) $value;

      $gender = strtolower(trim($gender));

      if (
        $gender === 'l' ||
        $gender === 'male' ||
        $gender === 'laki-laki' ||
        $gender === 'laki laki' ||
        $gender === 'pria'
      ) {

        if ($nilai < 3.4) {
          return 'Rendah';
        }

        if ($nilai <= 7.0) {
          return 'Normal';
        }

        return 'Tinggi';
      }


      if (
        $gender === 'p' ||
        $gender === 'female' ||
        $gender === 'perempuan' ||
        $gender === 'wanita'
      ) {

        if ($nilai < 2.4) {
          return 'Rendah';
        }

        if ($nilai <= 6.0) {
          return 'Normal';
        }

        return 'Tinggi';
      }


      /*
    |--------------------------------------------------------------------------
    | Jika gender tidak diketahui
    |--------------------------------------------------------------------------
    */

      if ($nilai < 2.4) {
        return 'Rendah';
      }

      if ($nilai <= 7.0) {
        return 'Normal';
      }

      return 'Tinggi';
    }


    /*
|--------------------------------------------------------------------------
| KATEGORI HEMOGLOBIN
|--------------------------------------------------------------------------
|
| Disesuaikan berdasarkan jenis kelamin.
|--------------------------------------------------------------------------
*/

    function kategoriHemoglobin($value, $gender = '')
    {
      if ($value === '' || $value === null) {
        return '-';
      }

      $nilai = (float) $value;

      $gender = strtolower(trim($gender));


      /*
    |--------------------------------------------------------------------------
    | LAKI-LAKI
    |--------------------------------------------------------------------------
    */

      if (
        $gender === 'l' ||
        $gender === 'male' ||
        $gender === 'laki-laki' ||
        $gender === 'laki laki' ||
        $gender === 'pria'
      ) {

        if ($nilai < 13.0) {
          return 'Rendah';
        }

        if ($nilai <= 17.0) {
          return 'Normal';
        }

        return 'Tinggi';
      }


      /*
    |--------------------------------------------------------------------------
    | PEREMPUAN
    |--------------------------------------------------------------------------
    */

      if (
        $gender === 'p' ||
        $gender === 'female' ||
        $gender === 'perempuan' ||
        $gender === 'wanita'
      ) {

        if ($nilai < 12.0) {
          return 'Rendah';
        }

        if ($nilai <= 15.0) {
          return 'Normal';
        }

        return 'Tinggi';
      }


      /*
    |--------------------------------------------------------------------------
    | DEFAULT
    |--------------------------------------------------------------------------
    */

      if ($nilai < 12.0) {
        return 'Rendah';
      }

      if ($nilai <= 17.0) {
        return 'Normal';
      }

      return 'Tinggi';
    }


    /*
|--------------------------------------------------------------------------
| AMBIL KATEGORI
|--------------------------------------------------------------------------
*/

    $kategoriGulaDarah =
      kategoriGulaDarah(
        $dataSurat['gula_darah_sewaktu'] ?? ''
      );


    $kategoriKolesterol =
      kategoriKolesterol(
        $dataSurat['kolesterol_total'] ?? ''
      );


    $kategoriAsamUrat =
      kategoriAsamUrat(
        $dataSurat['asam_urat'] ?? '',
        $dataSurat['patient_gender'] ?? ''
      );


    $kategoriHemoglobin =
      kategoriHemoglobin(
        $dataSurat['hemoglobin'] ?? '',
        $dataSurat['patient_gender'] ?? ''
      );

    ?>


    <div class="judul">
      <h1>Surat Hasil Pemeriksaan Mata</h1>

      <div class="nomor">Nomor: <?= $dataSurat['nomor_surat'] ?></div>
    </div>

    <!-- =================================
             ISI
        ================================== -->

    <div class="isi">
      <div class="pembuka">
        Berdasarkan pemeriksaan kesehatan dan pemeriksaan mata yang telah
        dilakukan di
        <strong><?= $dataClinic['clinic_name'] ?></strong>, berikut adalah hasil
        pemeriksaan terhadap pasien:
      </div>

      <!-- =================================
                 IDENTITAS
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
                 A. TANDA VITAL
            ================================== -->

      <div class="section-title">A. Pemeriksaan Tanda Vital</div>

      <table class="data-table vital">
        <thead>
          <tr>
            <th>Jenis Pemeriksaan</th>
            <th>Hasil</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>

          <tr>
            <td>Tekanan Darah</td>

            <td>
              <?= htmlspecialchars($dataSurat['tekanan_darah'] ?? '-') ?>
              mmHg
            </td>

            <td>
              <?= htmlspecialchars($kategoriTekananDarah) ?>
            </td>
          </tr>


          <tr>
            <td>Nadi</td>

            <td>
              <?= htmlspecialchars($dataSurat['nadi'] ?? '-') ?>
              x/menit
            </td>

            <td>
              <?= htmlspecialchars($kategoriNadi) ?>
            </td>
          </tr>


          <tr>
            <td>Suhu</td>

            <td>
              <?= htmlspecialchars($dataSurat['suhu'] ?? '-') ?>
              °C
            </td>

            <td>
              <?= htmlspecialchars($kategoriSuhu) ?>
            </td>
          </tr>


          <tr>
            <td>Respirasi</td>

            <td>
              <?= htmlspecialchars($dataSurat['respirasi'] ?? '-') ?>
              x/menit
            </td>

            <td>
              <?= htmlspecialchars($kategoriRespirasi) ?>
            </td>
          </tr>

        </tbody>
      </table>

      <!-- =================================
                 B. PEMERIKSAAN PENUNJANG
            ================================== -->

      <div class="section-title">B. Pemeriksaan Laboratorium / Penunjang</div>

      <table class="data-table lab">
        <thead>
          <tr>
            <th>Jenis Pemeriksaan</th>
            <th>Hasil</th>
            <th>Nilai Rujukan / Keterangan</th>
          </tr>
        </thead>
        <tbody>

          <tr>
            <td>Gula Darah Sewaktu</td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['gula_darah_sewaktu'] ?? '-'
              ) ?>
              mg/dL
            </td>

            <td>
              <?= htmlspecialchars(
                $kategoriGulaDarah
              ) ?>
            </td>
          </tr>


          <tr>
            <td>Kolesterol Total</td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['kolesterol_total'] ?? '-'
              ) ?>
              mg/dL
            </td>

            <td>
              <?= htmlspecialchars(
                $kategoriKolesterol
              ) ?>
            </td>
          </tr>


          <tr>
            <td>Asam Urat</td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['asam_urat'] ?? '-'
              ) ?>
              mg/dL
            </td>

            <td>
              <?= htmlspecialchars(
                $kategoriAsamUrat
              ) ?>
            </td>
          </tr>


          <tr>
            <td>Hemoglobin</td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['hemoglobin'] ?? '-'
              ) ?>
              g/dL
            </td>

            <td>
              <?= htmlspecialchars(
                $kategoriHemoglobin
              ) ?>
            </td>
          </tr>

        </tbody>
      </table>

      <!-- =================================
                 C. PEMERIKSAAN VISUS
            ================================== -->

      <div class="section-title">C. Pemeriksaan Visus Mata</div>

      <table class="data-table visus">
        <thead>
          <tr>
            <th rowspan="2">Mata</th>

            <th colspan="2">Tanpa Koreksi</th>

            <th colspan="2">Dengan Koreksi</th>
          </tr>

          <tr>
            <th>Jauh</th>
            <th>Dekat</th>
            <th>Jauh</th>
            <th>Dekat</th>
          </tr>
        </thead>

        <tbody>

          <!-- MATA KANAN / OD -->
          <tr>

            <td class="mata">
              Mata Kanan (OD)
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['visus_od_tanpa_koreksi_jauh'] ?? '-'
              ) ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['visus_od_tanpa_koreksi_dekat'] ?? '-'
              ) ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['visus_od_dengan_koreksi_jauh'] ?? '-'
              ) ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['visus_od_dengan_koreksi_dekat'] ?? '-'
              ) ?>
            </td>

          </tr>


          <!-- MATA KIRI / OS -->
          <tr>

            <td class="mata">
              Mata Kiri (OS)
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['visus_os_tanpa_koreksi_jauh'] ?? '-'
              ) ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['visus_os_tanpa_koreksi_dekat'] ?? '-'
              ) ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['visus_os_dengan_koreksi_jauh'] ?? '-'
              ) ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['visus_os_dengan_koreksi_dekat'] ?? '-'
              ) ?>
            </td>

          </tr>

        </tbody>
      </table>

      <!-- =================================
                 D. REFRAKSI
            ================================== -->

      <!-- =================================
     D. REFRAKSI
================================== -->

      <div class="section-title">
        D. Pemeriksaan Refraksi
      </div>

      <table class="data-table refraksi">

        <thead>
          <tr>
            <th>Mata</th>
            <th>SPH</th>
            <th>CYL</th>
            <th>AXIS</th>
            <th>ADD</th>
            <th>PD</th>
          </tr>
        </thead>

        <tbody>

          <!-- =========================
         OD / MATA KANAN
    ========================== -->

          <tr>

            <td>
              OD / Kanan
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['refraksi_od_sph'] ?? '-'
              ) ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['refraksi_od_cyl'] ?? '-'
              ) ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['refraksi_od_axis'] ?? '-'
              ) ?>

              <?php if (!empty($dataSurat['refraksi_od_axis'])): ?>
                °
              <?php endif; ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['refraksi_od_add'] ?? '-'
              ) ?>
            </td>

            <td rowspan="2">

              <?= htmlspecialchars(
                $dataSurat['pd'] ?? '-'
              ) ?>

              <?php if (!empty($dataSurat['pd'])): ?>
                mm
              <?php endif; ?>

            </td>

          </tr>


          <!-- =========================
         OS / MATA KIRI
    ========================== -->

          <tr>

            <td>
              OS / Kiri
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['refraksi_os_sph'] ?? '-'
              ) ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['refraksi_os_cyl'] ?? '-'
              ) ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['refraksi_os_axis'] ?? '-'
              ) ?>

              <?php if (!empty($dataSurat['refraksi_os_axis'])): ?>
                °
              <?php endif; ?>
            </td>

            <td>
              <?= htmlspecialchars(
                $dataSurat['refraksi_os_add'] ?? '-'
              ) ?>
            </td>

          </tr>

        </tbody>

      </table>


      <!-- =================================
     E. PEMERIKSAAN MATA LAINNYA
================================== -->

      <div class="section-title">
        E. Pemeriksaan Mata Lainnya
      </div>

      <table class="data-table mata-lain">

        <thead>

          <tr>

            <th>
              Pemeriksaan
            </th>

            <th>
              OD / Kanan
            </th>

            <th>
              OS / Kiri
            </th>

          </tr>

        </thead>


        <tbody>

          <!-- =========================
         TIO
    ========================== -->

          <tr>

            <td>
              Tekanan Intraokular (TIO)
            </td>

            <td>

              <?= htmlspecialchars(
                $dataSurat['tio_od'] ?? '-'
              ) ?>

              <?php if (!empty($dataSurat['tio_od'])): ?>
                mmHg
              <?php endif; ?>

            </td>

            <td>

              <?= htmlspecialchars(
                $dataSurat['tio_os'] ?? '-'
              ) ?>

              <?php if (!empty($dataSurat['tio_os'])): ?>
                mmHg
              <?php endif; ?>

            </td>

          </tr>


          <!-- =========================
         SEGMEN ANTERIOR
    ========================== -->

          <tr>

            <td>
              Segmen Anterior
            </td>

            <td>

              <?= nl2br(
                htmlspecialchars(
                  $dataSurat['segmen_anterior_od'] ?? '-'
                )
              ) ?>

            </td>

            <td>

              <?= nl2br(
                htmlspecialchars(
                  $dataSurat['segmen_anterior_os'] ?? '-'
                )
              ) ?>

            </td>

          </tr>


          <!-- =========================
         SEGMEN POSTERIOR
    ========================== -->

          <tr>

            <td>
              Segmen Posterior
            </td>

            <td>

              <?= nl2br(
                htmlspecialchars(
                  $dataSurat['segmen_posterior_od'] ?? '-'
                )
              ) ?>

            </td>

            <td>

              <?= nl2br(
                htmlspecialchars(
                  $dataSurat['segmen_posterior_os'] ?? '-'
                )
              ) ?>

            </td>

          </tr>

        </tbody>

      </table>
      <!-- =================================
                 F. KESIMPULAN
            ================================== -->

      <div class="section-title">F. Kesimpulan Pemeriksaan</div>
      <?php

      /*
|--------------------------------------------------------------------------
| DATA VISUS
|--------------------------------------------------------------------------
*/

      $odVisusTanpaJauh =
        $dataSurat['visus_od_tanpa_koreksi_jauh'] ?? '-';

      $odVisusKoreksiJauh =
        $dataSurat['visus_od_dengan_koreksi_jauh'] ?? '-';

      $osVisusTanpaJauh =
        $dataSurat['visus_os_tanpa_koreksi_jauh'] ?? '-';

      $osVisusKoreksiJauh =
        $dataSurat['visus_os_dengan_koreksi_jauh'] ?? '-';


      /*
|--------------------------------------------------------------------------
| DATA REFRAKSI
|--------------------------------------------------------------------------
*/

      $odSph = $dataSurat['od_sph'] ?? '';
      $odCyl = $dataSurat['od_cyl'] ?? '';
      $odAxis = $dataSurat['od_axis'] ?? '';

      $osSph = $dataSurat['os_sph'] ?? '';
      $osCyl = $dataSurat['os_cyl'] ?? '';
      $osAxis = $dataSurat['os_axis'] ?? '';


      /*
|--------------------------------------------------------------------------
| ANALISIS REFRAKSI
|--------------------------------------------------------------------------
*/

      $kelainanRefraksi = [];


      // OD SPH
      if ($odSph !== '' && is_numeric($odSph)) {

        $nilai = (float) $odSph;

        if ($nilai < 0) {
          $kelainanRefraksi[] = 'miopia mata kanan';
        } elseif ($nilai > 0) {
          $kelainanRefraksi[] = 'hipermetropia mata kanan';
        }
      }


      // OS SPH
      if ($osSph !== '' && is_numeric($osSph)) {

        $nilai = (float) $osSph;

        if ($nilai < 0) {
          $kelainanRefraksi[] = 'miopia mata kiri';
        } elseif ($nilai > 0) {
          $kelainanRefraksi[] = 'hipermetropia mata kiri';
        }
      }


      // OD CYL
      if ($odCyl !== '' && is_numeric($odCyl)) {

        if ((float) $odCyl != 0) {
          $kelainanRefraksi[] = 'astigmatisme mata kanan';
        }
      }


      // OS CYL
      if ($osCyl !== '' && is_numeric($osCyl)) {

        if ((float) $osCyl != 0) {
          $kelainanRefraksi[] = 'astigmatisme mata kiri';
        }
      }


      /*
|--------------------------------------------------------------------------
| KESIMPULAN REFRAKSI
|--------------------------------------------------------------------------
*/

      if (!empty($kelainanRefraksi)) {

        $kesimpulanRefraksi =
          'Ditemukan kelainan refraksi berupa ' .
          implode(' dan ', $kelainanRefraksi) .
          '.';
      } else {

        $kesimpulanRefraksi =
          'Tidak ditemukan kelainan refraksi yang tercatat.';
      }


      /*
|--------------------------------------------------------------------------
| PEMERIKSAAN PENUNJANG
|--------------------------------------------------------------------------
*/

      $hasilPenunjang = 'Pemeriksaan penunjang dalam batas normal.';


      // GDS
      if (
        isset($dataSurat['gula_darah_sewaktu']) &&
        $dataSurat['gula_darah_sewaktu'] !== ''
      ) {

        $kategoriGDS =
          kategoriGulaDarah(
            $dataSurat['gula_darah_sewaktu']
          );

        if ($kategoriGDS !== 'Normal') {
          $hasilPenunjang =
            'Ditemukan hasil pemeriksaan penunjang yang perlu diperhatikan.';
        }
      }


      // Kolesterol
      if (
        isset($dataSurat['kolesterol_total']) &&
        $dataSurat['kolesterol_total'] !== ''
      ) {

        $kategoriKolesterol =
          kategoriKolesterol(
            $dataSurat['kolesterol_total']
          );

        if ($kategoriKolesterol !== 'Normal') {
          $hasilPenunjang =
            'Ditemukan hasil pemeriksaan penunjang yang perlu diperhatikan.';
        }
      }


      // Asam urat
      if (
        isset($dataSurat['asam_urat']) &&
        $dataSurat['asam_urat'] !== ''
      ) {

        $kategoriAsamUrat =
          kategoriAsamUrat(
            $dataSurat['asam_urat'],
            $dataSurat['patient_gender'] ?? ''
          );

        if ($kategoriAsamUrat !== 'Normal') {
          $hasilPenunjang =
            'Ditemukan hasil pemeriksaan penunjang yang perlu diperhatikan.';
        }
      }


      // Hemoglobin
      if (
        isset($dataSurat['hemoglobin']) &&
        $dataSurat['hemoglobin'] !== ''
      ) {

        $kategoriHemoglobin =
          kategoriHemoglobin(
            $dataSurat['hemoglobin'],
            $dataSurat['patient_gender'] ?? ''
          );

        if ($kategoriHemoglobin !== 'Normal') {
          $hasilPenunjang =
            'Ditemukan hasil pemeriksaan penunjang yang perlu diperhatikan.';
        }
      }

      ?>
      <div class="kesimpulan-box">

        <p>
          Berdasarkan hasil pemeriksaan kesehatan, laboratorium/penunjang dan
          pemeriksaan mata, diperoleh hasil sebagai berikut:
        </p>


        <!-- VISUS OD -->

        <p>
          <strong>
            1. Visus mata kanan (OD):
            <?= htmlspecialchars($odVisusTanpaJauh) ?>
            tanpa koreksi dan
            <?= htmlspecialchars($odVisusKoreksiJauh) ?>
            dengan koreksi.
          </strong>
        </p>


        <!-- VISUS OS -->

        <p>
          <strong>
            2. Visus mata kiri (OS):
            <?= htmlspecialchars($osVisusTanpaJauh) ?>
            tanpa koreksi dan
            <?= htmlspecialchars($osVisusKoreksiJauh) ?>
            dengan koreksi.
          </strong>
        </p>


        <!-- REFRAKSI -->

        <p>
          <strong>
            3. <?= htmlspecialchars($kesimpulanRefraksi) ?>
          </strong>
        </p>


        <!-- PENUNJANG -->

        <p>
          <strong>
            4. <?= htmlspecialchars($hasilPenunjang) ?>
          </strong>
        </p>

      </div>

      <!-- =================================
                 G. REKOMENDASI
            ================================== -->
      <div class="section-title">
        G. Rekomendasi
      </div>

      <div class="rekomendasi-box">

        <?php
        $rekomendasi = trim(
          $dataSurat['rekomendasi'] ?? ''
        );

        if ($rekomendasi !== ''):

          echo nl2br(
            htmlspecialchars($rekomendasi)
          );

        else:
        ?>

          Pasien disarankan melakukan pemeriksaan dan tindak lanjut
          sesuai hasil pemeriksaan serta anjuran dokter.

        <?php endif; ?>

      </div>

      <!-- =================================
                 CATATAN
            ================================== -->

      <div class="catatan">
        Catatan: Hasil pemeriksaan merupakan kondisi pasien pada saat
        pemeriksaan dilakukan dan dapat berubah sesuai kondisi kesehatan
        pasien.
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
      Dokumen hasil pemeriksaan ini diterbitkan melalui Sistem Informasi
      Klinik dan merupakan dokumen pelayanan kesehatan.
    </div>
  </div>
</body>

</html>