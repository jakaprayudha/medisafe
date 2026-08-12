<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Surat Keterangan Sehat</title>

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

    .page {
      width: 210mm;
      min-height: 297mm;
      margin: 20px auto;
      padding: 18mm 20mm 20mm 20mm;
      background: #fff;
      position: relative;
    }

    /* =========================
           KOP SURAT
        ========================= */

    .kop {
      display: flex;
      align-items: center;
      border-bottom: 3px solid #000;
      padding-bottom: 8px;
    }

    .kop-logo {
      width: 25mm;
      text-align: center;
    }

    .kop-logo img {
      max-width: 22mm;
      max-height: 22mm;
      object-fit: contain;
    }

    .kop-content {
      flex: 1;
      text-align: center;
      line-height: 1.2;
    }

    .kop-content .nama-instansi {
      font-size: 16pt;
      font-weight: bold;
      text-transform: uppercase;
    }

    .kop-content .nama-klinik {
      font-size: 18pt;
      font-weight: bold;
      text-transform: uppercase;
    }

    .kop-content .alamat {
      font-size: 9.5pt;
      margin-top: 3px;
    }

    .kop-content .kontak {
      font-size: 9pt;
      margin-top: 2px;
    }

    .kop-empty {
      width: 25mm;
    }

    /* =========================
           JUDUL
        ========================= */

    .judul {
      text-align: center;
      margin-top: 30px;
    }

    .judul h1 {
      margin: 0;
      font-size: 15pt;
      text-decoration: underline;
      text-transform: uppercase;
    }

    .nomor {
      margin-top: 6px;
      font-size: 11pt;
    }

    /* =========================
           ISI SURAT
        ========================= */

    .isi {
      margin-top: 28px;
      font-size: 12pt;
      line-height: 1.6;
    }

    .pembuka {
      text-align: justify;
      margin-bottom: 15px;
    }

    .identitas {
      width: 100%;
      margin: 8px 0 18px 0;
    }

    .identitas td {
      vertical-align: top;
      padding: 3px 0;
      font-size: 12pt;
    }

    .identitas .label {
      width: 45mm;
    }

    .identitas .separator {
      width: 7mm;
      text-align: center;
    }

    .identitas .value {
      font-weight: bold;
    }

    .pernyataan {
      text-align: justify;
      margin-top: 10px;
    }

    .keperluan {
      margin-top: 18px;
      text-align: justify;
    }

    /* =========================
           TABEL HASIL PEMERIKSAAN
        ========================= */

    .pemeriksaan {
      margin-top: 20px;
      width: 100%;
      border-collapse: collapse;
    }

    .pemeriksaan th,
    .pemeriksaan td {
      border: 1px solid #000;
      padding: 7px 8px;
      font-size: 11pt;
    }

    .pemeriksaan th {
      text-align: center;
      font-weight: bold;
    }

    .pemeriksaan td:nth-child(1) {
      width: 45%;
    }

    .pemeriksaan td:nth-child(2) {
      width: 20%;
      text-align: center;
    }

    .pemeriksaan td:nth-child(3) {
      width: 35%;
    }

    /* =========================
           KESIMPULAN
        ========================= */

    .kesimpulan {
      margin-top: 20px;
      text-align: justify;
    }

    .kesimpulan strong {
      text-transform: uppercase;
    }

    /* =========================
           TANDA TANGAN
        ========================= */

    .ttd-wrapper {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-top: 35px;
    }

    .ttd {
      width: 70mm;
      text-align: center;
      font-size: 11pt;
    }

    .qr-verifikasi {
      width: 35mm;
      text-align: center;
      font-size: 7.5pt;
    }

    .qr-verifikasi img {
      width: 28mm;
      height: 28mm;
      display: block;
      margin: 0 auto 3px auto;
    }

    .ttd .tempat-tanggal {
      margin-bottom: 10px;
    }

    .ttd .jabatan {
      margin-bottom: 5px;
    }

    .ttd .space {
      height: 30mm;
    }

    .ttd .nama-dokter {
      font-weight: bold;
      text-decoration: underline;
    }

    .ttd .sip {
      margin-top: 2px;
    }

    /* =========================
           FOOTER
        ========================= */

    .footer {
      position: absolute;
      bottom: 12mm;
      left: 20mm;
      right: 20mm;
      border-top: 1px solid #999;
      padding-top: 5px;
      text-align: center;
      font-size: 8pt;
      color: #555;
    }

    /* =========================
           PRINT
        ========================= */

    @page {
      size: A4 portrait;
      margin: 0;
    }

    @media print {

      html,
      body {
        background: #fff;
        margin: 0;
        padding: 0;
      }

      .page {
        width: 210mm;
        min-height: 297mm;
        margin: 0;
        padding: 18mm 20mm 20mm 20mm;
        box-shadow: none;
      }

      .no-print {
        display: none !important;
      }
    }

    /* =========================
           BUTTON CETAK
        ========================= */

    .toolbar {
      width: 210mm;
      margin: 15px auto;
      text-align: right;
    }

    .btn-print {
      border: none;
      background: #198754;
      color: #fff;
      padding: 10px 18px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
    }

    .btn-print:hover {
      background: #157347;
    }
  </style>
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
      'https://medisafe.sehatdigital.id/module/letter/verifikasi-surat?id='
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
        pv.suhu
     FROM surat_sehat ss
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

      <!-- =========================
                 TANDA TANGAN
            ========================== -->

      <div class="ttd-wrapper">

        <div class="ttd">

          <div class="tempat-tanggal">
            <?= htmlspecialchars($dataClinic['faskes_city'] ?? 'Deli Serdang') ?>,
            <?= tanggalIndonesia($dataSurat['tanggal_surat']) ?>
          </div>

          <div class="jabatan">
            Dokter Pemeriksa,
          </div>

          <div class="space"></div>

          <div class="nama-dokter">
            <?= htmlspecialchars($dataSurat['id_doctor']) ?>
          </div>

          <div class="sip">
            SIP. <?= htmlspecialchars($dataSurat['sip_number']) ?>
          </div>

        </div>


        <!-- QR CODE -->

        <div class="qr-verifikasi">

          <img
            src="data:image/png;base64,<?= $qrBase64 ?>"
            alt="QR Verifikasi">

          <div>
            Scan untuk verifikasi
          </div>

        </div>

      </div>
    </div>

    <!-- FOOTER -->

    <div class="footer">
      Surat ini diterbitkan melalui Sistem Informasi Klinik dan merupakan
      dokumen resmi fasilitas pelayanan kesehatan.
    </div>
  </div>
</body>

</html>