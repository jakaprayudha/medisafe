<?php

/**
 * kontrak.php
 * Preview / cetak Perjanjian Kerjasama Sistem Informasi Medisafe
 *
 * URL:
 * kontrak.php?no=3
 */

require '../../database/connect.php';

$no = isset($_GET['no']) ? (int) $_GET['no'] : 0;

if ($no <= 0) {
   die('ID Faskes tidak valid.');
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA FASKES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        f.id_faskes,
        f.faskes_code,
        f.pic_name,
        f.pic_phone,
        f.pic_email,
        f.faskes_address,
        f.faskes_prov,
        f.faskes_city,
        f.faskes_district,
        f.faskes_village,
        f.faskes_status,
        f.contract_date,
        f.faskes_payment,
        f.contract_amount,
        f.contract_start,
        f.contract_end,
        f.contract_number,
        f.order_number,
        f.id_clinic,
        f.faskes_phone,

        sc.clinic_name

    FROM ms_faskes f

    LEFT JOIN setting_clinic sc
        ON sc.id = f.id_clinic

    WHERE sc.id = ?

    LIMIT 1
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
   die('Query database gagal.');
}

mysqli_stmt_bind_param($stmt, "i", $no);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$faskes = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$faskes) {
   die('Data Faskes tidak ditemukan.');
}


/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
*/

function e($value)
{
   return htmlspecialchars(
      (string) ($value ?? ''),
      ENT_QUOTES,
      'UTF-8'
   );
}


function formatTanggalIndonesia($date)
{
   if (!$date) {
      return '-';
   }

   $timestamp = strtotime($date);

   if (!$timestamp) {
      return $date;
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

   return date('d', $timestamp)
      . ' '
      . $bulan[(int) date('m', $timestamp)]
      . ' '
      . date('Y', $timestamp);
}


function terbilang($angka)
{
   $angka = (int) $angka;

   $huruf = [
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
      return $huruf[$angka];
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

   if ($angka < 1000000000) {
      return terbilang((int) ($angka / 1000000))
         . ' juta '
         . terbilang($angka % 1000000);
   }

   return (string) $angka;
}


/*
|--------------------------------------------------------------------------
| DATA DINAMIS KONTRAK
|--------------------------------------------------------------------------
*/

$namaFaskes = $faskes['clinic_name'] ?: 'Fasilitas Kesehatan';

$picName = $faskes['pic_name'] ?: '-';

$picPhone = $faskes['pic_phone'] ?: '-';

$picEmail = $faskes['pic_email'] ?: '-';

$alamatFaskes = implode(', ', array_filter([
   $faskes['faskes_address'],
   $faskes['faskes_village'],
   $faskes['faskes_district'],
   $faskes['faskes_city'],
   $faskes['faskes_prov']
]));

$contractNumber = $faskes['contract_number'] ?: '-';

$contractDate = formatTanggalIndonesia(
   $faskes['contract_date']
);

$contractStart = formatTanggalIndonesia(
   $faskes['contract_start']
);

$contractEnd = formatTanggalIndonesia(
   $faskes['contract_end']
);

$contractAmount = (int) ($faskes['contract_amount'] ?? 0);

$contractAmountFormatted = 'Rp. '
   . number_format(
      $contractAmount,
      0,
      ',',
      '.'
   );

$contractAmountText = ucfirst(
   trim(
      terbilang($contractAmount)
   )
) . ' rupiah';


/*
|--------------------------------------------------------------------------
| PIHAK
|--------------------------------------------------------------------------
*/

$katesNama = 'Khairul Fadhli Margolang, M.Kom';
$katesJabatan = 'Direktur';
$katesPerusahaan = 'PT. KREATIF TECHNO SOLUSINDO';

$mitraNama = $picName;
$mitraJabatan = 'Penanggung Jawab';
$mitraPerusahaan = $namaFaskes;


/*
|--------------------------------------------------------------------------
| QR VALIDASI
|--------------------------------------------------------------------------
*/

$secretKey = 'MEDISAFE-KONTRAK-2026-SECRET';

function generateContractToken(
   $no,
   $party,
   $secretKey
) {
   return hash_hmac(
      'sha256',
      $no . '|' . $party,
      $secretKey
   );
}

$tokenKates = generateContractToken(
   $no,
   'KATES',
   $secretKey
);

$tokenMitra = generateContractToken(
   $no,
   'MITRA',
   $secretKey
);


/*
|--------------------------------------------------------------------------
| URL VALIDASI
|--------------------------------------------------------------------------
*/

$validationBaseUrl =
   (isset($_SERVER['HTTPS'])
      && $_SERVER['HTTPS'] !== 'off'
      ? 'https'
      : 'http')
   . '://'
   . $_SERVER['HTTP_HOST']
   . dirname($_SERVER['SCRIPT_NAME'])
   . '/validasi-kontrak.php';


$validationUrlKates =
   $validationBaseUrl
   . '?no=' . urlencode($no)
   . '&pihak=KATES'
   . '&token=' . urlencode($tokenKates);


$validationUrlMitra =
   $validationBaseUrl
   . '?no=' . urlencode($no)
   . '&pihak=MITRA'
   . '&token=' . urlencode($tokenMitra);


/*
|--------------------------------------------------------------------------
| QR IMAGE
|--------------------------------------------------------------------------
*/

$qrKates =
   'https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=10&data='
   . urlencode($validationUrlKates);


$qrMitra =
   'https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=10&data='
   . urlencode($validationUrlMitra);

?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Perjanjian Kerjasama - Medisafe</title>
   <style>
      @page {
         size: A4;
         margin: 20mm 22mm
      }

      * {
         box-sizing: border-box
      }

      html,
      body {
         margin: 0;
         padding: 0;
         background: #e9ecef;
         color: #111;
         font-family: "Times New Roman", Times, serif;
         font-size: 12pt;
         line-height: 1.45
      }

      .toolbar {
         position: sticky;
         top: 0;
         z-index: 1000;
         padding: 12px;
         background: #fff;
         border-bottom: 1px solid #ddd;
         text-align: center;
         font-family: Arial, sans-serif
      }

      .toolbar button {
         border: 0;
         border-radius: 6px;
         padding: 9px 18px;
         margin: 0 4px;
         cursor: pointer;
         font-size: 13px
      }

      .btn-print {
         background: #0d6efd;
         color: #fff
      }

      .btn-close {
         background: #6c757d;
         color: #fff
      }

      .document {
         width: 210mm;
         min-height: 297mm;
         margin: 20px auto;
         padding: 20mm 22mm;
         background: #fff;
         box-shadow: 0 0 8px rgba(0, 0, 0, .18)
      }

      .title,
      .subtitle {
         text-align: center;
         font-weight: bold
      }

      .number {
         text-align: center;
         margin-bottom: 20px
      }

      .text,
      .party {
         text-align: justify;
         margin: 0 0 9px
      }

      .party-label {
         font-weight: bold
      }

      .section-title {
         text-align: center;
         font-weight: bold;
         margin: 16px 0 10px
      }

      ol {
         margin-top: 5px;
         padding-left: 25px
      }

      ol li {
         padding-left: 5px;
         margin-bottom: 5px;
         text-align: justify
      }

      .signature-page,
      .attachment {
         page-break-before: always
      }

      .signature-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 35px
      }

      .signature-table td {
         width: 50%;
         text-align: center;
         vertical-align: top;
         padding: 8px
      }

      .signature-space {
         height: 85px
      }

      .attachment-title {
         text-align: center;
         font-weight: bold;
         margin-bottom: 20px
      }

      .sla-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 10px
      }

      .sla-table th,
      .sla-table td {
         border: 1px solid #222;
         padding: 7px;
         vertical-align: top
      }

      .sla-table th {
         text-align: center
      }

      .bank-table {
         width: 100%;
         margin-top: 10px
      }

      .bank-table td {
         padding: 3px 5px;
         vertical-align: top
      }

      @media print {

         html,
         body {
            background: #fff
         }

         .toolbar {
            display: none !important
         }

         .document {
            width: auto;
            min-height: auto;
            margin: 0;
            padding: 0;
            box-shadow: none
         }

         .signature-area {
            height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 10px 0 8px;
         }

         .signature-qr {
            width: 90px;
            height: 90px;
            object-fit: contain;
         }

         .qr-label {
            font-family: Arial, sans-serif;
            font-size: 7px;
            letter-spacing: .5px;
            margin-top: 2px;
            color: #555;
         }
      }
   </style>
</head>

<body>

   <div class="toolbar">
      <button class="btn-print" onclick="window.print()">🖨 Cetak Kontrak</button>
      <button class="btn-close" onclick="window.close()">Tutup</button>
   </div>

   <div class="document">

      <div class="title">PERJANJIAN KERJASAMA TENTANG</div>
      <div class="subtitle">PENYEDIAAN DAN PENGGUNAAN SISTEM INFORMASI MEDISAFE</div>
      <div class="number">
         ANTARA<br>

         <strong>
            <?= e($katesPerusahaan) ?>
         </strong>

         DENGAN<br>

         <strong>
            <?= e($namaFaskes) ?>
         </strong>

         <br>

         No:
         <?= e($contractNumber) ?>
      </div>

      <p class="text">
         Perjanjian Kerjasama Tentang Penyediaan dan Penggunaan
         Sistem Informasi Medisafe ini (untuk selanjutnya disebut
         sebagai ”Perjanjian”) ditandatangani pada hari ini,
         tanggal <?= e($contractDate) ?> oleh dan antara:
      </p>

      <p class="party"><span class="party-label">I. PT. Kreatif Techno Solusindo</span>, suatu perseroan terbatas
         yang didirikan menurut dan berdasarkan hukum Negara Republik Indonesia dan beralamat di
         Jl. Karya Wisata No. 3 Medan Johor – Sumatera Utara (20144), dalam hal ini diwakili secara
         sah oleh Khairul Fadhli Margolang, M.Kom selaku Direktur (untuk selanjutnya disebut “KATES”).</p>

      <p class="party">

         <span class="party-label">
            II. <?= e($namaFaskes) ?>
         </span>,

         yang beralamat di
         <?= e($alamatFaskes) ?>,

         dalam hal ini diwakili secara sah oleh

         <strong>
            <?= e($picName) ?>
         </strong>

         selaku
         <?= e($mitraJabatan) ?>

         (untuk selanjutnya disebut
         “MITRA”).

      </p>

      <p class="text">KATES dan MITRA selanjutnya secara bersama-sama disebut “PARA PIHAK”, dan masing-masing disebut “PIHAK”.</p>
      <p class="text"><strong>PARA PIHAK terlebih dahulu menerangkan hal-hal sebagai berikut:</strong></p>
      <p class="text">1. Bahwa, KATES adalah perusahaan yang bergerak di bidang pembuatan dan pengembangan sistem aplikasi serta, sebagai pemilik dan pengelola sistem informasi bernama: Medisafe (selanjutnya disebut “Sistem Informasi Medisafe”).</p>
      <p class="text">2. Bahwa, MITRA adalah perusahaan yang bergerak di bidang Fasilitas Kesehatan serta sebagai pemilik dan pengelola fasilitas kesehatan yang dikenal dengan nama Klinik Pratama Rawat Inap Tutun Sehati beralamat di Jl. Pasar Baru KM, 16,5, Tanjung Morawa.</p>
      <p class="text">3. Bahwa, PARA PIHAK sepakat dan setuju untuk melakukan penyediaan dan penggunaan Sistem Informasi Medisafe berdasarkan syarat-syarat dan ketentuan-ketentuan sebagaimana diatur dalam Perjanjian ini.</p>
      <p class="text">Berdasarkan hal-hal yang telah diuraikan di atas, PARA PIHAK setuju untuk saling mengikatkan diri dalam Perjanjian ini dengan syarat-syarat dan ketentuan-ketentuan sebagai berikut:</p>

      <div class="section-title">PASAL 1<br>RUANG LINGKUP PERJANJIAN</div>
      <p class="text">PARA PIHAK dengan ini setuju bahwa KATES akan menyediakan Sistem Informasi Medisafe untuk MITRA, dengan fitur-fitur sebagaimana tercantum dalam Lampiran 1 Perjanjian ini yang akan digunakan oleh MITRA guna menunjang kegiatan operasionalnya.</p>

      <div class="section-title">PASAL 2<br>SISTEM INFORMASI MEDISAFE</div>
      <p class="text">1. Sistem informasi yang disediakan oleh KATES untuk MITRA berdasarkan Perjanjian ini adalah sebagai berikut:</p>
      <ol type="a">
         <li>Sistem Informasi Medisafe dengan varian produk: Face ID, Admisi, Poli Klinik, RTGD, Farmasi, Kasir, Rawat Inap, Persalinan, Pelayanan Gigi, USG, Lab, dan KB;</li>
         <li>Premium support (whatsapp, live chat, email, ticketing);</li>
         <li>Perawatan dan perbaikan Sistem Informasi Medisafe berupa layanan support, maintenance dan bug fixing;</li>
         <li>Lisensi penggunaan Sistem Informasi Medisafe untuk 1 (satu) lokasi untuk periode 12 (dua belas) bulan penggunaan;</li>
         <li>Training dilakukan secara masa l. Jika MITRA membutuhkan lebih maka KATES berhak untuk menyampaikan penawaran baru untuk disetujui terlebih dahulu oleh MITRA serta menyesuaikan kebutuhan MITRA.</li>
      </ol>
      <p class="text">2. Sistem Informasi Medisafe hanya dapat diakses oleh MITRA melalui koneksi internet.</p>
      <p class="text">3. Fitur-fitur dalam Sistem Informasi Medisafe sebagaimana yang tercantum pada Lampiran 1 Perjanjian ini dapat ditambahkan atau dikurangi berdasarkan kesepakatan sebelumnya oleh PARA PIHAK dimana penambahan atau pengurangan fitur-fitur tersebut akan diatur dalam adendum yang merupakan satu kesatuan dan tidak terpisahkan dari Perjanjian ini.</p>
      <p class="text">4. KATES dari waktu ke waktu dapat meluncurkan pemutakhiran atau pembaharuan (update) pada Sistem Informasi Medisafe. Sebagai akibat dari peluncuran tersebut, MITRA wajib untuk mengizinkan terjadinya pemutakhiran atau pembaharuan (update) pada Sistem Informasi Medisafe untuk mendapatkan manfaat yang maksimal dari Sistem Informasi Medisafe.</p>

      <div class="section-title">
         PASAL 3<br>
         JANGKA WAKTU
      </div>

      <p class="text">

         Perjanjian ini mulai berlaku untuk 1 (satu) tahun
         terhitung sejak tanggal
         <strong><?= e($contractStart) ?></strong>

         sampai dengan tanggal

         <strong><?= e($contractEnd) ?></strong>,

         dan diperpanjang secara otomatis untuk periode
         1 (satu) tahunan, kecuali diakhiri oleh salah satu
         PIHAK dengan menyampaikan pemberitahuan tertulis
         kepada PIHAK lainnya selambat-lambatnya 30 (tiga puluh)
         hari kalender sebelum masing-masing jangka waktu
         Perjanjian berakhir.

      </p>

      <div class="section-title">PASAL 4<br>HAK DAN KEWAJIBAN KATES</div>
      <p class="text"><strong>1. Hak KATES:</strong> Tanpa mengurangi hak-hak KATES sebagaimana diatur dalam ketentuan lainnya di Perjanjian ini, KATES berhak untuk:</p>
      <ol type="a">
         <li>Menerima data dan informasi yang benar dan akurat dari MITRA termasuk namun tidak terbatas pada data dan informasi mengenai data migrasi yang dibutuhkan oleh KATES agar Sistem Informasi KATES dapat dipergunakan oleh MITRA secara optimal.</li>
         <li>Menerima pembayaran biaya berlangganan atas penggunaan Sistem Informasi KATES dari MITRA secara tepat waktu dan dalam jumlah sesuai dengan syarat-syarat dan ketentuan-ketentuan dalam Pasal 7 Perjanjian ini.</li>
         <li>Melimpahkan atau men-subkontrakkan pelaksanaan sebagian atau seluruh kewajiban berdasarkan Perjanjian ini kepada penyedia jasa lainnya dengan pemberitahuan tertulis kepada MITRA.</li>
         <li>Mengakhiri akses MITRA ke Sistem Informasi Medisafe apabila MITRA terbukti menggunakan Sistem Informasi Medisafe tidak sesuai dengan fungsi atau peruntukannya atau melanggar hak kekayaan intelektual KATES maupun pihak lainnya serta melanggar peraturan perundang-undangan yang berlaku.</li>
         <li>PARA PIHAK berhak untuk saling menghubungi selama masa berlakunya Perjanjian ini, melalui sarana komunikasi yang dianggap tepat, dalam rangka pelaksanaan kerja sama, koordinasi, klarifikasi, pemantauan layanan, atau kepentingan lain yang berkaitan dengan Perjanjian ini.</li>
      </ol>
      <p class="text"><strong>2. Kewajiban KATES:</strong></p>
      <ol type="a">
         <li>Menyiapkan personil atau tenaga kerja yang kompeten, profesional dan mempunyai keahlian yang baik untuk menyediakan Sistem Informasi Medisafe sesuai dengan syarat-syarat dan ketentuan-ketentuan dalam Perjanjian ini.</li>
         <li>Memastikan bahwa Sistem Informasi Medisafe termasuk fitur-fitur di dalamnya berfungsi dengan baik dan dapat digunakan oleh MITRA sesuai dengan tujuan penggunaannya.</li>
         <li>Memberikan training mengenai penggunaan Sistem Informasi Medisafe dengan jumlah sebagaimana diatur dalam Pasal 2 ayat 1 Perjanjian ini, dimana jadwal pelaksanaan training tersebut akan disepakati oleh PARA PIHAK secara tertulis.</li>
         <li>Memberikan bantuan penggunaan Sistem Informasi Medisafe baik dari media online chatting yang ada di website Medisafe, whatsapp, telepon, atau e-mail.</li>
         <li>Memberikan Service Level Agreement (SLA) atas penyediaan Sistem Informasi Medisafe sebagaimana diatur dalam Lampiran 1 Perjanjian ini.</li>
         <li>PARA PIHAK berkewajiban untuk bersikap kooperatif serta memberikan tanggapan agar komunikasi dan pelaksanaan lancar.</li>
      </ol>

      <div class="section-title">PASAL 5<br>HAK DAN KEWAJIBAN MITRA</div>
      <p class="text"><strong>1. Hak MITRA:</strong> Tanpa mengurangi hak-hak MITRA sebagaimana diatur dalam ketentuan lainnya di Perjanjian ini, MITRA berhak untuk menggunakan dan mendapatkan akses ke Sistem Informasi Medisafe dan fitur-fitur sesuai dengan syarat-syarat dan ketentuan-ketentuan dalam Perjanjian ini.</p>
      <p class="text"><strong>2. Kewajiban MITRA:</strong></p>
      <ol type="a">
         <li>Melakukan pembayaran biaya berlangganan atas penggunaan Sistem Informasi Medisafe sebagaimana diatur dalam Pasal 7 Perjanjian ini.</li>
         <li>Menggunakan Sistem Informasi Medisafe sesuai dengan peruntukkan, arahan, atau instruksi dari KATES yang disampaikan dari waktu ke waktu termasuk namun tidak terbatas kepada term of use yang tercantum dalam Sistem Informasi Medisafe.</li>
         <li>Tidak mengubah, menduplikasi, menggabungkan dengan sistem lainnya, menjual, menyewakan, dan/atau mengalihkan Sistem informasi Medisafe dalam bentuk apapun, baik sebagian atau seluruhnya, kepada pihak ketiga manapun, tanpa persetujuan tertulis sebelumnya dari KATES.</li>
         <li>Bertanggung jawab sepenuhnya atas kerugian atau klaim dalam bentuk apapun yang diderita oleh MITRA yang timbul sebagai akibat dari penggunaan dan/atau penyalahgunaan Sistem Informasi Medisafe.</li>
         <li>Menjamin bahwa MITRA tidak menyalahgunakan Sistem Informasi Medisafe termasuk namun tidak terbatas menggunakan Sistem Informasi Medisafe secara melawan hukum dan peraturan perundang-undangan yang berlaku.</li>
      </ol>

      <div class="section-title">PASAL 6<br>PENGECUALIAN LAYANAN</div>
      <ol>
         <li>Pemulihan data dan/atau informasi yang tidak termasuk dalam layanan penggunaan Sistem Informasi Medisafe;</li>
         <li>Perbaikan atau penggantian kerusakan atas Sistem Informasi Medisafe yang ditimbulkan oleh kesalahan atau kelalaian MITRA termasuk atas syarat dan ketentuan dalam Perjanjian ini maupun term of use yang tercantum dalam Sistem Informasi Medisafe;</li>
         <li>Perbaikan atas kerusakan Sistem Informasi Medisafe yang ditimbulkan oleh situasi apapun yang merupakan Force Majeure sebagaimana diatur dalam Pasal 10 Perjanjian ini;</li>
         <li>Pengembangan fitur di luar fitur-fitur yang tercantum pada Lampiran 1 Perjanjian ini.</li>
      </ol>

      <div class="section-title">PASAL 7<br>BIAYA DAN CARA PEMBAYARAN</div>
      <ol>
         <li>Atas penyediaan dan penggunaan Sistem Informasi Medisafe oleh KATES kepada MITRA berdasarkan Perjanjian ini, maka MITRA wajib membayar biaya berlangganan atas penggunaan Sistem Informasi Medisafe kepada KATES sebagaimana tercantum dalam Lampiran 2 Perjanjian ini.</li>
         <li>Pajak-pajak yang timbul sehubungan dengan pelaksanaan Perjanjian ini mengikuti ketentuan perpajakan yang berlaku di Indonesia.</li>
      </ol>

      <div class="section-title">PASAL 8<br>DUKUNGAN TEKNIS</div>
      <ol>
         <li>KATES wajib memberikan penjelasan dan/atau bantuan apabila terjadi masalah atau gangguan terhadap Sistem Informasi Medisafe sesuai dengan Service Level Agreement (SLA) sebagaimana tercantum dalam Lampiran 1 Perjanjian ini.</li>
         <li>Apabila terjadi gangguan jaringan pada Sistem Informasi Medisafe, maka MITRA wajib segera memberitahukan kepada KATES dan KATES akan melakukan perbaikan seperlunya agar Sistem Informasi Medisafe dapat segera dipergunakan kembali.</li>
         <li>Apabila terjadi maintenance pada Sistem Informasi Medisafe, maka KATES wajib segera memberitahukan kepada MITRA dan melakukan perbaikan seperlunya agar Sistem Informasi Medisafe dapat segera dipergunakan kembali, sesuai dengan Service Level Agreement (SLA) sebagaimana tercantum dalam Lampiran 1 Perjanjian ini.</li>
      </ol>

      <div class="section-title">PASAL 9<br>KERAHASIAAN</div>
      <ol>
         <li>Selain diatur berbeda dalam Perjanjian ini, masing-masing PIHAK setuju bahwa setiap data dan/atau informasi yang diungkapkan oleh salah satu PIHAK kepada PIHAK yang lain sehubungan dengan pelaksanaan Perjanjian ini akan dijaga kerahasiaannya dan hanya akan digunakan untuk tujuan pelaksanaan Perjanjian ini.</li>
         <li>Kewajiban untuk menjaga kerahasiaan atas Informasi Rahasia tersebut tidak berlaku untuk informasi yang dapat dibuktikan dengan dokumentasi bahwa:
            <ol type="a">
               <li>Informasi tersebut merupakan atau telah menjadi informasi umum/publik;</li>
               <li>Telah menjadi milik PIHAK penerima sebelum diserahkan kepadanya;</li>
               <li>Informasi telah diungkapkan atau digunakan oleh PIHAK penerima dengan persetujuan tertulis sebelumnya;</li>
               <li>Informasi telah dikembangkan secara independen oleh PIHAK penerima; atau</li>
               <li>Informasi yang diperoleh oleh PIHAK penerima secara sah dari pihak ketiga lainnya.</li>
            </ol>
         </li>
         <li>Dalam hal PIHAK penerima diwajibkan oleh statuta, peraturan, undang-undang, atau perintah pengadilan yang berwenang untuk mengungkapkan Informasi Rahasia, PIHAK penerima akan segera memberikan kepada PIHAK pengungkap pemberitahuan tertulis tentang kewajiban tersebut.</li>
         <li>Untuk pelaksanaan Perjanjian ini, “Informasi Rahasia” adalah setiap informasi dalam bentuk apa pun, terkait dengan Perjanjian ini atau Sistem Informasi Medisafe, baik bersifat komersial, keuangan, teknis, operasional, manajerial, bisnis, atau lainnya.</li>
      </ol>

      <div class="section-title">PASAL 10<br>FORCE MAJEURE</div>
      <ol>
         <li>Yang dimaksud dengan Force Majeure (Keadaan Memaksa) dalam Perjanjian ini adalah suatu keadaan atau peristiwa yang terjadi di luar kemampuan manusia termasuk tetapi tidak terbatas pada gempa bumi, topan, banjir besar, tsunami, kebakaran, tanah longsor, wabah penyakit, pemogokan umum, huru-hara, sabotase, perang, pemberontakan atau kebijakan Pemerintah yang berpengaruh signifikan terhadap pelaksanaan Perjanjian ini.</li>
         <li>Dalam hal terjadi Force Majeure, maka PIHAK yang mengalami keadaan Force Majeure berkewajiban untuk memberitahukan kepada PIHAK lainnya dalam waktu selambat-lambatnya 3 (tiga) hari kalender terhitung sejak terjadinya peristiwa tersebut dengan disertai bukti-bukti yang sah.</li>
         <li>PIHAK yang mengalami Force Majeure wajib mengupayakan dengan sebaik-baiknya untuk tetap melaksanakan kewajibannya sebagaimana diatur dalam Perjanjian ini segera setelah peristiwa Force Majeure berakhir.</li>
         <li>Apabila peristiwa Force Majeure berlangsung terus hingga melebihi atau diduga akan melebihi jangka waktu 30 (tiga puluh) hari kalender, maka PARA PIHAK sepakat untuk meninjau dan menyesuaikan kembali isi Perjanjian ini.</li>
      </ol>

      <div class="section-title">PASAL 11<br>PENGAKHIRAN PERJANJIAN</div>
      <ol>
         <li>Perjanjian ini dapat diakhiri oleh salah satu PIHAK berdasarkan hal-hal sebagai berikut:
            <ol type="a">
               <li>Kesepakatan antara PARA PIHAK secara tertulis untuk mengakhiri Perjanjian;</li>
               <li>Salah satu PIHAK tidak memenuhi atau melanggar salah satu atau lebih ketentuan yang diatur dalam Perjanjian ini (wanprestasi) dan tetap tidak memperbaikinya dalam waktu 30 (tiga puluh) hari kalender sejak surat pemberitahuan;</li>
               <li>Izin usaha atau izin operasional salah satu PIHAK dicabut oleh Pemerintah;</li>
               <li>Salah satu PIHAK dinyatakan pailit, likuidasi dan/atau dibubarkan.</li>
            </ol>
         </li>
         <li>Dalam hal Perjanjian ini berakhir atau diakhiri:
            <ol type="a">
               <li>Kewajiban-kewajiban PARA PIHAK yang timbul sebelum pengakhiran Perjanjian harus tetap dilaksanakan;</li>
               <li>PIHAK yang menerima Informasi Rahasia wajib mengembalikan atau memusnahkan Informasi Rahasia sesuai ketentuan Perjanjian;</li>
               <li>MITRA tidak berhak untuk menggunakan Sistem Informasi Medisafe dalam bentuk dan cara apapun;</li>
               <li>Mitra dapat melakukan pengambilan data secara mandiri melalui sistem Medisafe dengan memanfaatkan fitur penarikan (ekspor) data selambat-lambatnya sebelum tanggal berakhirnya masa langganan;</li>
               <li>Setelah berakhirnya masa langganan, Jaga tidak bertanggung jawab atas ketersediaan, penyimpanan, maupun akses terhadap data milik Mitra yang terdapat di dalam sistem Medisafe.</li>
            </ol>
         </li>
         <li>Sehubungan dengan pengakhiran Perjanjian ini, PARA PIHAK sepakat untuk mengesampingkan berlakunya ketentuan dalam Pasal 1266 Kitab Undang-Undang Hukum Perdata sejauh yang mensyaratkan diperlukannya putusan atau penetapan Hakim/Pengadilan terlebih dahulu.</li>
      </ol>

      <div class="section-title">PASAL 12<br>HAK KEKAYAAN INTELEKTUAL</div>
      <ol>
         <li>MITRA mengakui bahwa Sistem Informasi Medisafe dan fitur-fiturnya termasuk hak cipta, paten, rahasia dagang, merek dagang, dan hak kekayaan intelektual lainnya merupakan hak kekayaan intelektual yang dimiliki KATES.</li>
         <li>MITRA dilarang melakukan perbuatan apapun yang dapat merusak atau merugikan hak atas kekayaan intelektual KATES terkait dengan Sistem Informasi Medisafe.</li>
         <li>MITRA tidak berhak untuk mengajukan klaim atas hak kepemilikan, kepentingan, atau hak apapun terhadap hak kekayaan intelektual KATES.</li>
      </ol>

      <div class="section-title">PASAL 13<br>PERNYATAAN DAN JAMINAN</div>
      <p class="text">Masing-masing PIHAK dengan ini menyatakan dan menjamin kepada PIHAK lainnya bahwa:</p>
      <ol>
         <li>PARA PIHAK dalam membuat dan menandatangani Perjanjian ini telah mendapatkan persetujuan-persetujuan yang disyaratkan dalam Anggaran Dasar, peraturan perusahaan dari masing-masing PIHAK dan sesuai dengan peraturan yang berlaku.</li>
         <li>PIHAK yang bertindak untuk dan atas nama masing-masing PIHAK adalah PIHAK yang berwenang, berhak dan karenanya sah untuk mewakili masing-masing PIHAK.</li>
         <li>PARA PIHAK mempunyai setiap wewenang dan kemampuan untuk membuat dan melaksanakan Perjanjian ini, termasuk memiliki izin-izin dan persetujuan-persetujuan untuk melakukan usahanya sesuai peraturan berlaku.</li>
         <li>Penandatanganan dan pelaksanaan Perjanjian ini tidak menyebabkan salah satu PIHAK menjadi lalai, menyalahi atau melanggar kesepakatan kepada pihak ketiga.</li>
         <li>Dalam pelaksanaan Perjanjian ini, masing-masing PIHAK wajib menjaga citra/image dan menjaga nama baik PIHAK lainnya.</li>
      </ol>

      <div class="section-title">PASAL 14<br>HUKUM YANG BERLAKU DAN PENYELESAIAN PERSELISIHAN</div>
      <ol>
         <li>Perjanjian ini dibuat dan diatur dengan tunduk kepada hukum yang berlaku di Republik Indonesia.</li>
         <li>PARA PIHAK sepakat menyelesaikan setiap perselisihan yang timbul dari Perjanjian ini dengan jalan musyawarah. Apabila tidak tercapai kata mufakat dalam jangka waktu 30 (tiga puluh) hari kalender, maka PARA PIHAK sepakat sengketa tersebut akan diselesaikan dan diputus oleh Badan Arbitrase Nasional Indonesia (BANI) di Wahana Graha Lt. 1 &amp; 2, Jl. Mampang Prapatan No. 2, Jakarta 12760, menurut peraturan-peraturan administrasi dan prosedur arbitrase BANI.</li>
      </ol>

      <div class="section-title">PASAL 15<br>PEMBERITAHUAN</div>
      <p class="text"><strong>KATES:</strong><br>Nama: Khairul Fadhli Margolang, M.Kom<br>Jabatan: Direktur<br>Whatsapp: 085360154004<br>Email: khairulfadhli@gmail.com</p>
      <p class="text">

         <strong>MITRA:</strong><br>

         Nama:
         <?= e($picName) ?><br>

         Jabatan:
         <?= e($mitraJabatan) ?><br>

         Whatsapp:
         <?= e($picPhone) ?><br>

         Email:
         <?= e($picEmail) ?>

      </p>
      <div class="section-title">PASAL 16<br>PENUTUP</div>
      <ol>
         <li>Ketentuan yang belum diatur dalam Perjanjian ini akan disepakati bersama oleh PARA PIHAK dan dituangkan secara tertulis dalam suatu adendum atau amendemen.</li>
         <li>MITRA tidak dapat melimpahkan atau mengalihkan hak dan kewajibannya baik sebagian maupun seluruhnya tanpa persetujuan tertulis terlebih dahulu dari KATES.</li>
         <li>Apabila terdapat salah satu ketentuan dalam Perjanjian ini yang tidak dapat dilaksanakan karena bertentangan dengan hukum ataupun peraturan perundang-undangan yang berlaku maka hal tersebut tidak menjadikan batalnya keseluruhan isi Perjanjian.</li>
         <li>Dengan ditandatanganinya Perjanjian ini beserta semua lampirannya, maka semua kesepakatan lisan yang pernah ada dianggap telah termasuk dalam Perjanjian ini.</li>
         <li>Para Pihak sepakat untuk mematuhi Undang-undang No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP) serta peraturan pelaksanaannya yang berlaku di Indonesia.</li>
         <li>Perjanjian ini dan semua dokumen yang dimaksud oleh atau disampaikan sehubungan dengan Perjanjian ini dapat ditandatangani secara elektronik dan/atau tanda tangan basah dalam beberapa salinan, yang mana dianggap asli.</li>
      </ol>

      <p class="text">Demikian Perjanjian ini dibuat dan ditandatangani oleh wakil PARA PIHAK yang berwenang, pada hari dan tanggal sebagaimana tersebut pada awal Perjanjian ini.</p>

      <table class="signature-table">

         <tr>

            <!-- ========================= -->
            <!-- KATES -->
            <!-- ========================= -->

            <td>

               <strong>
                  <?= e($katesPerusahaan) ?>
               </strong>

               <div class="signature-area">

                  <img
                     src="<?= e($qrKates) ?>"
                     class="signature-qr"
                     alt="QR Validasi KATES">

                  <div class="qr-label">
                     SCAN UNTUK VALIDASI
                  </div>

               </div>

               <u>
                  <strong>
                     <?= e($katesNama) ?>
                  </strong>
               </u>

               <br>

               <?= e($katesJabatan) ?>

            </td>


            <!-- ========================= -->
            <!-- MITRA -->
            <!-- ========================= -->

            <td>

               <strong>
                  <?= e($namaFaskes) ?>
               </strong>

               <div class="signature-area">

                  <img
                     src="<?= e($qrMitra) ?>"
                     class="signature-qr"
                     alt="QR Validasi MITRA">

                  <div class="qr-label">
                     SCAN UNTUK VALIDASI
                  </div>

               </div>

               <u>
                  <strong>
                     <?= e($picName) ?>
                  </strong>
               </u>

               <br>

               <?= e($mitraJabatan) ?>

            </td>

         </tr>

      </table>
      <div class="attachment">
         <div class="attachment-title">LAMPIRAN 1</div>
         <p class="text"><strong>a. Benefit yang Terdapat Dalam Product Sistem Informasi Medisafe</strong></p>
         <p class="text"><strong>b. Service Level Agreement</strong></p>
         <ol>
            <li>Jaminan atas tersedianya layanan untuk menggunakan sistem Medisafe dalam waktu 1 tahun adalah sebesar 98% uptime server.</li>
            <li>Waktu support: Senin - Jumat dengan jam kerja 08.30 - 17.30 WIB (kecuali hari Libur).</li>
            <li>Waktu verifikasi keluhan: dalam 4 (empat) jam setelah keluhan diterima (kecuali hari libur).</li>
            <li>Waktu perbaikan:</li>
         </ol>
         <table class="sla-table">
            <tr>
               <th>Kategori</th>
               <th>Waktu Perbaikan</th>
            </tr>
            <tr>
               <td>High</td>
               <td>72 jam pada hari kerja</td>
            </tr>
            <tr>
               <td>Medium</td>
               <td>120 jam pada hari kerja</td>
            </tr>
            <tr>
               <td>Low</td>
               <td>168 jam pada hari kerja</td>
            </tr>
         </table>
         <p class="text">5. Layanan maintenance selama jangka waktu Perjanjian.</p>
      </div>

      <div class="attachment">
         <div class="attachment-title">LAMPIRAN 2</div>
         <p class="text"><strong>1.</strong> Biaya berlangganan Sistem Informasi Medisafe adalah sebesar Rp. 4.800.000 (empat juta delapan ratus ribu rupiah) untuk jangka waktu berlangganan 12 (dua belas) bulan PARA PIHAK sepakat bahwa jumlah biaya berlangganan sebagaimana tercantum pada bagian awal Perjanjian ini.</p>
         <p class="text"><strong>2. Untuk pembayaran dapat ditransfer melalui rekening berikut:</strong></p>
         <table class="bank-table">
            <tr>
               <td width="180">Nama Rekening</td>
               <td>: PT Kreatif Techno Solusindo</td>
            </tr>
            <tr>
               <td>Nomor Rekening</td>
               <td>: 16-4010-4000-0203</td>
            </tr>
            <tr>
               <td>Nama Bank</td>
               <td>: Bank Sumut</td>
            </tr>
         </table>
         <p class="text"><strong>3.</strong> Biaya sebagaimana dimaksud dalam ayat 1 lampiran ini akan dibayarkan oleh MITRA kepada KATES dalam jangka setelah tagihan diterima secara lengkap dan benar diterima oleh MITRA dengan tata cara pembayaran sebagaimana tercantum pada halaman billing pada Sistem Informasi Medisafe.</p>
      </div>

   </div>
</body>

</html>