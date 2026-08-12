<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Surat Keterangan Sakit</title>

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

      /* =====================================
           PAGE A4
        ===================================== */

      .page {
        width: 210mm;
        min-height: 297mm;
        margin: 20px auto;
        padding: 18mm 20mm 20mm 20mm;
        background: #fff;
        position: relative;
      }

      /* =====================================
           KOP SURAT
        ===================================== */

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

      .nama-instansi {
        font-size: 16pt;
        font-weight: bold;
        text-transform: uppercase;
      }

      .nama-klinik {
        font-size: 18pt;
        font-weight: bold;
        text-transform: uppercase;
      }

      .alamat {
        font-size: 9.5pt;
        margin-top: 3px;
      }

      .kontak {
        font-size: 9pt;
        margin-top: 2px;
      }

      .kop-empty {
        width: 25mm;
      }

      /* =====================================
           JUDUL
        ===================================== */

      .judul {
        text-align: center;
        margin-top: 30px;
      }

      .judul h1 {
        margin: 0;
        font-size: 15pt;
        text-transform: uppercase;
        text-decoration: underline;
      }

      .nomor {
        margin-top: 6px;
        font-size: 11pt;
      }

      /* =====================================
           ISI SURAT
        ===================================== */

      .isi {
        margin-top: 28px;
        font-size: 12pt;
        line-height: 1.6;
      }

      .pembuka {
        text-align: justify;
        margin-bottom: 15px;
      }

      /* =====================================
           IDENTITAS PASIEN
        ===================================== */

      .identitas {
        width: 100%;
        margin: 8px 0 20px 0;
        border-collapse: collapse;
      }

      .identitas td {
        vertical-align: top;
        padding: 3px 0;
        font-size: 12pt;
      }

      .identitas .label {
        width: 48mm;
      }

      .identitas .separator {
        width: 7mm;
        text-align: center;
      }

      .identitas .value {
        font-weight: bold;
      }

      /* =====================================
           KETERANGAN SAKIT
        ===================================== */

      .keterangan {
        text-align: justify;
        margin-top: 12px;
      }

      .periode {
        margin-top: 20px;
        padding: 12px 15px;
        border: 1px solid #000;
      }

      .periode-title {
        font-weight: bold;
        margin-bottom: 8px;
      }

      .periode table {
        width: 100%;
        border-collapse: collapse;
      }

      .periode td {
        padding: 3px 0;
      }

      .periode .label {
        width: 55mm;
      }

      .periode .separator {
        width: 7mm;
        text-align: center;
      }

      /* =====================================
           KESIMPULAN
        ===================================== */

      .kesimpulan {
        margin-top: 22px;
        text-align: justify;
      }

      .highlight {
        font-weight: bold;
      }

      /* =====================================
           TANDA TANGAN
        ===================================== */

      .ttd-wrapper {
        display: flex;
        justify-content: flex-end;
        margin-top: 40px;
      }

      .ttd {
        width: 70mm;
        text-align: center;
        font-size: 11pt;
      }

      .tempat-tanggal {
        margin-bottom: 10px;
      }

      .jabatan {
        margin-bottom: 5px;
      }

      .space {
        height: 32mm;
      }

      .nama-dokter {
        font-weight: bold;
        text-decoration: underline;
      }

      .sip {
        margin-top: 2px;
      }

      /* =====================================
           FOOTER
        ===================================== */

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

      /* =====================================
           TOOLBAR
        ===================================== */

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

      /* =====================================
           PRINT
        ===================================== */

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
    </style>
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

      <div class="kop">
        <div class="kop-logo">
          <img src="assets/img/logo-klinik.png" alt="Logo Klinik" />
        </div>

        <div class="kop-content">
          <div class="nama-instansi">PEMERINTAH KABUPATEN / KOTA</div>

          <div class="nama-klinik">KLINIK SEHAT DIGITAL</div>

          <div class="alamat">
            Jl. Contoh No. 123, Kecamatan Contoh, Kabupaten/Kota
          </div>

          <div class="kontak">
            Telp. (061) 123456 &nbsp; | &nbsp; Email: klinik@example.com
          </div>
        </div>

        <div class="kop-empty"></div>
      </div>

      <!-- =================================
             JUDUL
        ================================== -->

      <div class="judul">
        <h1>Surat Keterangan Sakit</h1>

        <div class="nomor">Nomor: 445/___/SKS/___/2026</div>
      </div>

      <!-- =================================
             ISI
        ================================== -->

      <div class="isi">
        <div class="pembuka">
          Yang bertanda tangan di bawah ini, Dokter pada
          <strong>Klinik Sehat Digital</strong>, menerangkan bahwa:
        </div>

        <!-- =================================
                 IDENTITAS PASIEN
            ================================== -->

        <table class="identitas">
          <tr>
            <td class="label">Nama Lengkap</td>

            <td class="separator">:</td>

            <td class="value">NAMA PASIEN</td>
          </tr>

          <tr>
            <td class="label">NIK</td>

            <td class="separator">:</td>

            <td>1234567890123456</td>
          </tr>

          <tr>
            <td class="label">Tempat, Tanggal Lahir</td>

            <td class="separator">:</td>

            <td>Medan, 01 Januari 1990</td>
          </tr>

          <tr>
            <td class="label">Jenis Kelamin</td>

            <td class="separator">:</td>

            <td>Laki-laki</td>
          </tr>

          <tr>
            <td class="label">Alamat</td>

            <td class="separator">:</td>

            <td>Jl. Contoh No. 123, Kecamatan Contoh, Kabupaten/Kota</td>
          </tr>
        </table>

        <!-- =================================
                 KETERANGAN
            ================================== -->

        <div class="keterangan">
          Berdasarkan hasil pemeriksaan kesehatan yang telah dilakukan pada
          tanggal
          <strong>10 Agustus 2026</strong>, terhadap yang bersangkutan
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

              <td>10 Agustus 2026</td>
            </tr>

            <tr>
              <td class="label">Sampai Dengan</td>

              <td class="separator">:</td>

              <td>12 Agustus 2026</td>
            </tr>

            <tr>
              <td class="label">Lama Istirahat</td>

              <td class="separator">:</td>

              <td>
                <strong>3 (tiga) hari</strong>
              </td>
            </tr>
          </table>
        </div>

        <!-- =================================
                 KESIMPULAN
            ================================== -->

        <div class="kesimpulan">
          Dengan demikian, yang bersangkutan diberikan keterangan untuk
          <span class="highlight"> beristirahat selama 3 (tiga) hari </span>,
          terhitung mulai tanggal
          <strong>10 Agustus 2026</strong>
          sampai dengan
          <strong>12 Agustus 2026</strong>.
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
            <div class="tempat-tanggal">Medan, 10 Agustus 2026</div>

            <div class="jabatan">Dokter Pemeriksa,</div>

            <div class="space"></div>

            <div class="nama-dokter">dr. Nama Dokter</div>

            <div class="sip">SIP. 123456789</div>
          </div>
        </div>
      </div>

      <!-- =================================
             FOOTER
        ================================== -->

      <div class="footer">
        Surat keterangan ini diterbitkan melalui Sistem Informasi Klinik dan
        merupakan dokumen resmi fasilitas pelayanan kesehatan.
      </div>
    </div>
  </body>
</html>
