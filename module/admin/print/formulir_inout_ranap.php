<?php
require '../../../database/connect.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Formulir Rawat Inap</title>
   <link rel="stylesheet" href="style.css">
   <style>
      table {
         width: 100%;
         border-collapse: collapse;
         border: 1px solid #000;
         font-size: 10pt;
      }

      td,
      th {
         border: 1px solid #000;
         padding: 3px 5px;
         vertical-align: top;
      }

      .no-border {
         border: none !important;
      }

      .center {
         text-align: center;
      }

      .no-print {
         margin-top: 10px;
         text-align: center;
      }

      @media print {
         .no-print {
            display: none;
         }
      }
   </style>
</head>

<body>

   <?php require 'kopsurat.php' ?>

   <table>
      <tr>
         <td>Nama Pasien :</td>
         <td colspan="2"></td>
         <td>Nomor Dokumentasi Medik</td>
      </tr>
      <tr>
         <td>Tanggal Lahir :</td>
         <td></td>
         <td>Agama :</td>
         <td>Prosedur masuk rawat inap Puskesmas</td>
      </tr>
      <tr>
         <td>Pendidikan :</td>
         <td></td>
         <td>Sex : LK/PR</td>
         <td>
            1. URJ &nbsp;
            2. UGD &nbsp;
            3. Langsung &nbsp;
            4. TP2RN
         </td>
      </tr>
      <tr>
         <td>Pekerjaan :</td>
         <td></td>
         <td>No. Kartu Peserta BPJS :</td>
         <td></td>
      </tr>
      <tr>
         <td>Alamat Lengkap :</td>
         <td colspan="3"></td>
      </tr>
      <tr>
         <td>Asuransi Lain</td>
         <td colspan="3">
            <strong>Cara Masuk Rawat Inap</strong><br>
            Klinik Tutun Sehati<br>
            1. Dokter/Para Medis<br>
            2. Pustu/Polindes<br>
            3. Instansi Kesehatan<br>
            4. Kasus Polisi<br>
            5. Datang Sendiri
         </td>
      </tr>
      <tr>
         <td>Status Perkawinan<br>1. Kawin &nbsp; 2. Belum Kawin &nbsp; 3. Duda &nbsp; 4. Janda</td>
         <td colspan="2">Tanggal Masuk<br>
            <table width="100%">
               <tr>
                  <td>Tanggal</td>
                  <td>Bulan</td>
                  <td>Tahun</td>
               </tr>
               <tr>
                  <td style="height:20px;"></td>
                  <td></td>
                  <td></td>
               </tr>
            </table>
         </td>
         <td>Jam :</td>
      </tr>
      <tr>
         <td>Nama Penanggung Jawab:</td>
         <td colspan="3"></td>
      </tr>
      <tr>
         <td>Alamat Lengkap:</td>
         <td colspan="2">Tanggal Dipindahkan:<br>
            <table width="100%">
               <tr>
                  <td>Tanggal</td>
                  <td>Bulan</td>
                  <td>Tahun</td>
               </tr>
               <tr>
                  <td style="height:20px;"></td>
                  <td></td>
                  <td></td>
               </tr>
            </table>
         </td>
         <td>Jam:</td>
      </tr>
      <tr>
         <td>Bagian / Ruang Rawat / Kelas</td>
         <td colspan="2"></td>
         <td></td>
      </tr>
      <tr>
         <td>Tanggal Keluar</td>
         <td colspan="2">
            <table width="100%">
               <tr>
                  <td>Tanggal</td>
                  <td>Bulan</td>
                  <td>Tahun</td>
               </tr>
               <tr>
                  <td style="height:20px;"></td>
                  <td></td>
                  <td></td>
               </tr>
            </table>
         </td>
         <td>Jam:</td>
      </tr>
      <tr>
         <td>Diagnosa Medik :</td>
         <td colspan="2"></td>
         <td>Lama Dirawat: ______ Hari</td>
      </tr>
      <tr>
         <td>Diagnosa Akhir</td>
         <td colspan="3">
            Utama : <br><br>
            Komplikasi :
         </td>
      </tr>
      <tr>
         <td>Penyebab Luar Cedera & Keracunan / Morfologi Neoplasma</td>
         <td colspan="3"></td>
      </tr>
      <tr>
         <td>Nama Operasi / Tindakan</td>
         <td colspan="3"></td>
      </tr>
      <tr>
         <td>Infeksi Nosokomial :</td>
         <td>Penyebab Infeksi :</td>
         <td colspan="2"></td>
      </tr>
      <tr>
         <td>Alergi Terhadap :</td>
         <td>Pengobatan Radio Therapy / Kedokteran Nuklir</td>
         <td>Imunisasi yang diperoleh selama dirawat:</td>
         <td>Transfusi Darah:</td>
      </tr>
      <tr>
         <td>Keadaan Keluar :</td>
         <td colspan="3">
            1. Sembuh &nbsp;
            2. Membaik &nbsp;
            3. Belum Sembuh &nbsp;
            4. Mati &lt;48 jam &nbsp;
            5. Mati &gt;48 jam
         </td>
      </tr>
      <tr>
         <td>Cara Keluar :</td>
         <td colspan="3">
            1. Diizinkan Pulang &nbsp;
            2. Pulang Paksa &nbsp;
            3. Lari &nbsp;
            4. Pindah ke FKTP lain &nbsp;
            5. Dirujuk ke .................................
         </td>
      </tr>
      <tr>
         <td>Dokter yang merawat</td>
         <td colspan="3">Tanda Tangan</td>
      </tr>
   </table>

   <div class="no-print">
      <button onclick="window.print()">🖨 Cetak Halaman</button>
   </div>

</body>

</html>