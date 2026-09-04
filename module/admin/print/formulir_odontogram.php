<?php

require '../../../database/connect.php';
require '../../admin/getdataclinic.php';

$visit_ID = $_GET['no'] ?? '';
$rm       = $_GET['rm'] ?? '';

if (!$visit_ID) {
   die('Visit ID tidak ditemukan.');
}


/*
|--------------------------------------------------------------------------
| DATA DOKTER
|--------------------------------------------------------------------------
*/

$doctorName = '';

$visit_ID_safe = mysqli_real_escape_string(
   $koneksi,
   $visit_ID
);

$qDoctor = mysqli_query(
   $koneksi,
   "
    SELECT id_doctor
    FROM pasien_visit
    WHERE visit_ID = '$visit_ID_safe'
    LIMIT 1
    "
);

if ($qDoctor && $doctor = mysqli_fetch_assoc($qDoctor)) {
   $doctorName = $doctor['id_doctor'] ?? '';
}


/*
|--------------------------------------------------------------------------
| TANDA TANGAN DOKTER
|--------------------------------------------------------------------------
*/

$signatureDokter = null;

if ($doctorName) {

   $doctorNameClean = trim(
      preg_replace(
         '/^dr\.?\s*/i',
         '',
         $doctorName
      )
   );

   $doctorNameClean = mysqli_real_escape_string(
      $koneksi,
      $doctorNameClean
   );

   $qSignature = mysqli_query(
      $koneksi,
      "
        SELECT signature_user
        FROM ms_users
        WHERE fullname LIKE '%$doctorNameClean%'
        LIMIT 1
        "
   );

   if (
      $qSignature &&
      $signature = mysqli_fetch_assoc($qSignature)
   ) {
      $signatureDokter =
         $signature['signature_user'] ?? null;
   }
}


/*
|--------------------------------------------------------------------------
| TANDA TANGAN SAKSI / USER
|--------------------------------------------------------------------------
*/

$signatureUser = null;
$fullnameUser = $_SESSION['fullname'] ?? '';

if (isset($_SESSION['uid_user'])) {

   $uidUser = mysqli_real_escape_string(
      $koneksi,
      $_SESSION['uid_user']
   );

   $qUserSignature = mysqli_query(
      $koneksi,
      "
        SELECT signature_user
        FROM ms_users
        WHERE uid_user = '$uidUser'
        LIMIT 1
        "
   );

   if (
      $qUserSignature &&
      $userSignature = mysqli_fetch_assoc($qUserSignature)
   ) {

      $signatureUser =
         $userSignature['signature_user'] ?? null;
   }
}

?>


<div class="form-odontogram">

   <style>
      /* =====================================================
           PAGE
        ===================================================== */

      .form-odontogram {

         width: 210mm;
         min-height: 297mm;

         margin: 0 auto;

         padding: 0 10mm;

         font-family:
            "Times New Roman",
            serif;

         color: #000;

         font-size: 13px;

         line-height: 1.4;

      }


      .form-odontogram header {

         text-align: center;

      }


      /* =====================================================
           JUDUL
        ===================================================== */

      .form-odontogram .judul {

         margin-top: 18px;

         margin-bottom: 5px;

         text-align: center;

         font-weight: bold;

         font-size: 18px;

         text-decoration: underline;

      }


      .form-odontogram .subjudul {

         text-align: center;

         font-size: 13px;

         margin-bottom: 20px;

      }


      /* =====================================================
           IDENTITAS PASIEN
        ===================================================== */

      .form-odontogram .section {

         margin-top: 15px;

         font-size: 13px;

      }


      .form-odontogram .data {

         width: 100%;

         margin-bottom: 15px;

         border-collapse: collapse;

      }


      .form-odontogram .data td {

         padding: 3px 5px;

         vertical-align: top;

         font-size: 13px;

      }


      .form-odontogram .data .label {

         width: 110px;

      }


      /* =====================================================
           ODONTOGRAM
        ===================================================== */

      .odontogram-wrapper {

         width: 100%;

         margin-top: 15px;

         border: 1px solid #000;

         padding: 12px 8px;

      }


      .odontogram-title {

         text-align: center;

         font-weight: bold;

         font-size: 14px;

         margin-bottom: 10px;

      }


      .odontogram {

         display: flex;

         flex-direction: column;

         gap: 10px;

      }


      .row-gigi {

         display: flex;

         justify-content: center;

         gap: 4px;

         flex-wrap: nowrap;

      }


      .gigi {

         width: 35px;

         min-width: 35px;

         height: 48px;

         border: 1px solid #000;

         text-align: center;

         position: relative;

         box-sizing: border-box;

      }


      .gigi.nomor-gigi {

         background: #fff;

      }


      .nomor {

         font-size: 9px;

         font-weight: bold;

         line-height: 12px;

         height: 13px;

      }


      .gigi-box {

         width: 24px;

         height: 24px;

         border: 1px solid #000;

         margin: 4px auto 0;

         box-sizing: border-box;

      }


      /*
        |--------------------------------------------------------------------------
        | STATUS GIGI
        |--------------------------------------------------------------------------
        */

      .gigi.has-data {

         background: #d9f7be;

      }


      .gigi.has-data .gigi-box {

         background: #b7eb8f;

      }


      .gigi.abnormal {

         background: #fff1f0;

      }


      .gigi.abnormal .gigi-box {

         background: #ffccc7;

      }


      .separator-gigi {

         width: 10px;

         min-width: 10px;

      }


      .rahang-label {

         text-align: center;

         font-weight: bold;

         font-size: 11px;

         margin: 2px 0;

      }


      /* =====================================================
           KETERANGAN
        ===================================================== */

      .legend {

         display: flex;

         justify-content: center;

         gap: 20px;

         margin-top: 12px;

         font-size: 10px;

      }


      .legend-item {

         display: flex;

         align-items: center;

         gap: 5px;

      }


      .legend-box {

         width: 14px;

         height: 14px;

         border: 1px solid #000;

      }


      .legend-normal {

         background: #fff;

      }


      .legend-data {

         background: #b7eb8f;

      }


      .legend-abnormal {

         background: #ffccc7;

      }


      /* =====================================================
           TABLE DETAIL
        ===================================================== */

      .detail-title {

         font-weight: bold;

         margin-top: 20px;

         margin-bottom: 7px;

         font-size: 14px;

      }


      .odontogram-table {

         width: 100%;

         border-collapse: collapse;

         margin-top: 5px;

      }


      .odontogram-table th,
      .odontogram-table td {

         border: 1px solid #000;

         padding: 5px 6px;

         font-size: 10px;

         vertical-align: top;

      }


      .odontogram-table th {

         text-align: center;

         font-weight: bold;

         background: #f2f2f2;

      }


      .odontogram-table td.center {

         text-align: center;

      }


      /* =====================================================
           CATATAN
        ===================================================== */

      .catatan {

         margin-top: 15px;

         font-size: 12px;

      }


      .catatan-title {

         font-weight: bold;

         margin-bottom: 4px;

      }


      /* =====================================================
           TANGGAL
        ===================================================== */

      .tanggal {

         text-align: right;

         margin-top: 25px;

         font-size: 13px;

      }


      /* =====================================================
           TANDA TANGAN
        ===================================================== */

      .ttd-wrapper {

         display: flex;

         justify-content: space-between;

         margin-top: 10px;

      }


      .kolom-ttd {

         width: 30%;

         text-align: center;

         font-size: 13px;

      }


      .ttd-space {

         height: 75px;

         display: flex;

         align-items: center;

         justify-content: center;

      }


      .ttd-space img {

         max-height: 70px;

         max-width: 130px;

      }


      .ttd-box {

         border-top: 1px solid #000;

         padding-top: 5px;

         min-height: 20px;

         font-size: 13px;

      }


      /* =====================================================
           PRINT
        ===================================================== */

      @media print {

         @page {

            size: A4 portrait;

            margin: 10mm;

         }


         .form-odontogram {

            width: 100%;

            min-height: auto;

            margin: 0;

            padding: 0;

         }


         .no-print {

            display: none !important;

         }


         .odontogram-wrapper {

            break-inside: avoid;

         }


         .odontogram-table {

            break-inside: auto;

         }


         .odontogram-table tr {

            break-inside: avoid;

            break-after: auto;

         }


         .ttd-wrapper {

            break-inside: avoid;

         }

      }


      /* =====================================================
           SCREEN
        ===================================================== */

      @media screen {

         body {

            background: #eee;

         }


         .form-odontogram {

            background: #fff;

            min-height: 297mm;

            padding-top: 10mm;

            padding-bottom: 10mm;

            box-shadow:
               0 0 10px rgba(0, 0, 0, .12);

         }

      }
   </style>


   <?php include 'kopsurat.php'; ?>


   <!-- =====================================================
         JUDUL
    ====================================================== -->

   <h3 class="judul">
      ODONTOGRAM
   </h3>


   <div class="subjudul">
      Rekam Medis Gigi dan Mulut
   </div>


   <!-- =====================================================
         IDENTITAS PASIEN
    ====================================================== -->

   <div class="section">

      <table class="data">

         <tr>

            <td class="label">
               Nama Pasien
            </td>

            <td>
               : <span id="od_nama">-</span>
            </td>

            <td class="label">
               No. RM
            </td>

            <td>
               : <span id="od_rm">
                  <?= htmlspecialchars($rm) ?>
               </span>
            </td>

         </tr>


         <tr>

            <td class="label">
               Tanggal Lahir
            </td>

            <td>
               : <span id="od_tanggal_lahir">-</span>
            </td>

            <td class="label">
               Jenis Kelamin
            </td>

            <td>
               : <span id="od_jk">-</span>
            </td>

         </tr>


         <tr>

            <td class="label">
               No. Kartu
            </td>

            <td>
               : <span id="od_bpjs">-</span>
            </td>

            <td class="label">
               Dokter
            </td>

            <td>
               :
               <span id="od_dokter">
                  <?= htmlspecialchars($doctorName ?: '-') ?>
               </span>
            </td>

         </tr>


         <tr>

            <td class="label">
               Tanggal Kunjungan
            </td>

            <td colspan="3">
               : <span id="od_tanggal_visit">-</span>
            </td>

         </tr>

      </table>

   </div>


   <!-- =====================================================
         ODONTOGRAM VISUAL
    ====================================================== -->

   <div class="odontogram-wrapper">

      <div class="odontogram-title">
         GAMBAR ODONTOGRAM
      </div>


      <div class="odontogram">


         <!-- =========================
                 RAHANG ATAS
            ========================== -->

         <div class="rahang-label">
            RAHANG ATAS
         </div>


         <div class="row-gigi">

            <?php

            $gigiAtasKanan = [
               18,
               17,
               16,
               15,
               14,
               13,
               12,
               11
            ];

            foreach ($gigiAtasKanan as $g) {

               echo '
                    <div
                        class="gigi nomor-gigi"
                        id="gigi-' . $g . '">

                        <div class="nomor">
                            ' . $g . '
                        </div>

                        <div class="gigi-box"></div>

                    </div>
                    ';
            }


            echo '<div class="separator-gigi"></div>';


            $gigiAtasKiri = [
               21,
               22,
               23,
               24,
               25,
               26,
               27,
               28
            ];

            foreach ($gigiAtasKiri as $g) {

               echo '
                    <div
                        class="gigi nomor-gigi"
                        id="gigi-' . $g . '">

                        <div class="nomor">
                            ' . $g . '
                        </div>

                        <div class="gigi-box"></div>

                    </div>
                    ';
            }

            ?>

         </div>


         <!-- =========================
                 RAHANG ATAS SULUNG
            ========================== -->

         <div class="row-gigi">

            <?php

            $gigiSulungAtasKanan = [
               55,
               54,
               53,
               52,
               51
            ];

            foreach ($gigiSulungAtasKanan as $g) {

               echo '
                    <div
                        class="gigi nomor-gigi"
                        id="gigi-' . $g . '">

                        <div class="nomor">
                            ' . $g . '
                        </div>

                        <div class="gigi-box"></div>

                    </div>
                    ';
            }


            echo '<div class="separator-gigi"></div>';


            $gigiSulungAtasKiri = [
               61,
               62,
               63,
               64,
               65
            ];

            foreach ($gigiSulungAtasKiri as $g) {

               echo '
                    <div
                        class="gigi nomor-gigi"
                        id="gigi-' . $g . '">

                        <div class="nomor">
                            ' . $g . '
                        </div>

                        <div class="gigi-box"></div>

                    </div>
                    ';
            }

            ?>

         </div>


         <div
            style="
                    border-top:1px dashed #000;
                    margin:2px 20px;
                ">
         </div>


         <!-- =========================
                 RAHANG BAWAH SULUNG
            ========================== -->

         <div class="row-gigi">

            <?php

            $gigiSulungBawahKanan = [
               85,
               84,
               83,
               82,
               81
            ];

            foreach ($gigiSulungBawahKanan as $g) {

               echo '
                    <div
                        class="gigi nomor-gigi"
                        id="gigi-' . $g . '">

                        <div class="nomor">
                            ' . $g . '
                        </div>

                        <div class="gigi-box"></div>

                    </div>
                    ';
            }


            echo '<div class="separator-gigi"></div>';


            $gigiSulungBawahKiri = [
               71,
               72,
               73,
               74,
               75
            ];

            foreach ($gigiSulungBawahKiri as $g) {

               echo '
                    <div
                        class="gigi nomor-gigi"
                        id="gigi-' . $g . '">

                        <div class="nomor">
                            ' . $g . '
                        </div>

                        <div class="gigi-box"></div>

                    </div>
                    ';
            }

            ?>

         </div>


         <!-- =========================
                 RAHANG BAWAH
            ========================== -->

         <div class="row-gigi">

            <?php

            $gigiBawahKanan = [
               48,
               47,
               46,
               45,
               44,
               43,
               42,
               41
            ];

            foreach ($gigiBawahKanan as $g) {

               echo '
                    <div
                        class="gigi nomor-gigi"
                        id="gigi-' . $g . '">

                        <div class="nomor">
                            ' . $g . '
                        </div>

                        <div class="gigi-box"></div>

                    </div>
                    ';
            }


            echo '<div class="separator-gigi"></div>';


            $gigiBawahKiri = [
               31,
               32,
               33,
               34,
               35,
               36,
               37,
               38
            ];

            foreach ($gigiBawahKiri as $g) {

               echo '
                    <div
                        class="gigi nomor-gigi"
                        id="gigi-' . $g . '">

                        <div class="nomor">
                            ' . $g . '
                        </div>

                        <div class="gigi-box"></div>

                    </div>
                    ';
            }

            ?>

         </div>


         <div class="rahang-label">
            RAHANG BAWAH
         </div>


      </div>


      <!-- =================================================
             LEGEND
        ================================================== -->

      <div class="legend">

         <div class="legend-item">

            <span class="legend-box legend-normal"></span>

            <span>
               Tidak ada data
            </span>

         </div>


         <div class="legend-item">

            <span class="legend-box legend-data"></span>

            <span>
               Ada data odontogram
            </span>

         </div>


         <div class="legend-item">

            <span class="legend-box legend-abnormal"></span>

            <span>
               Kondisi abnormal
            </span>

         </div>

      </div>

   </div>


   <!-- =====================================================
         DETAIL ODONTOGRAM
    ====================================================== -->

   <div class="detail-title">
      DETAIL PEMERIKSAAN ODONTOGRAM
   </div>


   <table
      class="odontogram-table"
      id="tableOdontogram">

      <thead>

         <tr>

            <th width="30">
               No
            </th>

            <th width="45">
               Gigi
            </th>

            <th>
               Elemen
            </th>

            <th>
               Elemen Gigi
            </th>

            <th>
               Diagnosa
            </th>

            <th>
               Prosedur
            </th>

            <th>
               Keterangan
            </th>

         </tr>

      </thead>


      <tbody>

         <tr>

            <td
               colspan="7"
               style="text-align:center;">

               Memuat data...

            </td>

         </tr>

      </tbody>

   </table>


   <!-- =====================================================
         CATATAN
    ====================================================== -->

   <div class="catatan">

      <div class="catatan-title">
         Catatan:
      </div>

      <div>
         Data odontogram di atas merupakan bagian dari
         rekam medis pasien dan digunakan sebagai dokumentasi
         kondisi gigi dan mulut pasien pada saat pemeriksaan.
      </div>

   </div>


   <!-- =====================================================
         TANGGAL
    ====================================================== -->

   <p class="tanggal">

      <span id="od_tempat">
         <?= $datafaskes['faskes_district'] ?? '' ?>
      </span>,

      <span id="od_tanggal_print">
         -
      </span>

   </p>


   <!-- =====================================================
         TANDA TANGAN
    ====================================================== -->

   <div class="ttd-wrapper">


      <!-- SAKSI / PETUGAS -->

      <div class="kolom-ttd">

         <p>
            Petugas
         </p>


         <div class="ttd-space">

            <?php

            if ($signatureUser) {

               echo '
                    <img
                        src="../../../uploads/ttd_faskes/'
                  . htmlspecialchars($signatureUser) .
                  '"
                        alt="Tanda Tangan"
                    >
                    ';
            }

            ?>

         </div>


         <div class="ttd-box">

            <?= htmlspecialchars(
               $fullnameUser ?: '-'
            ) ?>

         </div>

      </div>



      <!-- DOKTER -->

      <div class="kolom-ttd">

         <p>
            Dokter Pemeriksa
         </p>


         <div class="ttd-space">

            <?php

            if ($signatureDokter) {

               echo '
                    <img
                        src="../../../uploads/ttd_faskes/'
                  . htmlspecialchars($signatureDokter) .
                  '"
                        alt="Tanda Tangan Dokter"
                    >
                    ';
            }

            ?>

         </div>


         <div class="ttd-box">

            <span id="ttd_dokter">
               <?= htmlspecialchars(
                  $doctorName ?: '-'
               ) ?>
            </span>

         </div>

      </div>



      <!-- PASIEN -->

      <div class="kolom-ttd">

         <p>
            Pasien / Keluarga
         </p>


         <div class="ttd-space">
            &nbsp;
         </div>


         <div class="ttd-box">

            <span id="od_nama_ttd">
               -
            </span>

         </div>

      </div>


   </div>


</div>


<script>
   document.addEventListener(
      "DOMContentLoaded",
      function() {

         const url =
            new URLSearchParams(
               window.location.search
            );

         const no =
            url.get("no");

         const rm =
            url.get("rm");


         if (!no) {

            return;

         }


         /*
         |--------------------------------------------------------------------------
         | TANGGAL PRINT
         |--------------------------------------------------------------------------
         */

         const sekarang =
            new Date();

         document.getElementById(
               "od_tanggal_print"
            ).innerText =
            formatTanggal(
               sekarang
            );


         /*
         |--------------------------------------------------------------------------
         | DATA PASIEN
         |--------------------------------------------------------------------------
         */

         fetch(
               `getpasien.php?no=${encodeURIComponent(no)}&rm=${encodeURIComponent(rm || '')}`
            )

            .then(function(res) {

               if (!res.ok) {

                  throw new Error(
                     "Gagal mengambil data pasien"
                  );

               }

               return res.json();

            })

            .then(function(data) {

               if (!data) {
                  return;
               }


               /*
               |--------------------------------------------------------------------------
               | NAMA
               |--------------------------------------------------------------------------
               */

               document.getElementById(
                     "od_nama"
                  ).innerText =
                  data.patient_name || "-";


               document.getElementById(
                     "od_nama_ttd"
                  ).innerText =
                  data.patient_name || "-";


               /*
               |--------------------------------------------------------------------------
               | RM
               |--------------------------------------------------------------------------
               */

               if (data.patient_rm) {

                  document.getElementById(
                        "od_rm"
                     ).innerText =
                     data.patient_rm;

               }


               /*
               |--------------------------------------------------------------------------
               | TANGGAL LAHIR
               |--------------------------------------------------------------------------
               */

               if (data.patient_datebirth) {

                  document.getElementById(
                        "od_tanggal_lahir"
                     ).innerText =
                     formatTanggal(
                        data.patient_datebirth
                     );

               }


               /*
               |--------------------------------------------------------------------------
               | JENIS KELAMIN
               |--------------------------------------------------------------------------
               */

               document.getElementById(
                     "od_jk"
                  ).innerText =
                  data.patient_gender || "-";


               /*
               |--------------------------------------------------------------------------
               | BPJS
               |--------------------------------------------------------------------------
               */

               document.getElementById(
                     "od_bpjs"
                  ).innerText =
                  data.patient_bpjs || "-";


               /*
               |--------------------------------------------------------------------------
               | DOKTER
               |--------------------------------------------------------------------------
               */

               if (data.id_doctor) {

                  document.getElementById(
                        "od_dokter"
                     ).innerText =
                     data.id_doctor;

                  document.getElementById(
                        "ttd_dokter"
                     ).innerText =
                     data.id_doctor;

               }


               /*
               |--------------------------------------------------------------------------
               | TANGGAL VISIT
               |--------------------------------------------------------------------------
               */

               if (data.visit_date) {

                  document.getElementById(
                        "od_tanggal_visit"
                     ).innerText =
                     formatTanggal(
                        data.visit_date
                     );

               }

            })

            .catch(function(error) {

               console.error(
                  "Error pasien:",
                  error
               );

            });


         /*
         |--------------------------------------------------------------------------
         | DATA ODONTOGRAM
         |--------------------------------------------------------------------------
         */

         fetch(
               `../../../controller/visit/getOdontogram?visit_ID=${encodeURIComponent(no)}`
            )

            .then(function(res) {

               if (!res.ok) {

                  throw new Error(
                     "Gagal mengambil data odontogram"
                  );

               }

               return res.json();

            })

            .then(function(res) {

               console.log(
                  "Data odontogram:",
                  res
               );


               const tbody =
                  document.querySelector(
                     "#tableOdontogram tbody"
                  );


               tbody.innerHTML = "";


               if (
                  !res.data ||
                  res.data.length === 0
               ) {

                  tbody.innerHTML = `

                    <tr>

                        <td
                            colspan="7"
                            style="
                                text-align:center;
                                padding:10px;
                            ">

                            Tidak ada data odontogram.

                        </td>

                    </tr>

                `;

                  return;

               }


               /*
               |--------------------------------------------------------------------------
               | TABLE
               |--------------------------------------------------------------------------
               */

               let nomor = 1;


               res.data.forEach(
                  function(row) {

                     const tr =
                        document.createElement(
                           "tr"
                        );


                     tr.innerHTML = `

                        <td class="center">
                            ${nomor++}
                        </td>

                        <td class="center">
                            <strong>
                                ${escapeHtml(
                                    row.no_gigi ?? '-'
                                )}
                            </strong>
                        </td>

                        <td>
                            ${escapeHtml(
                                row.elemen ?? '-'
                            )}
                        </td>

                        <td>
                            ${escapeHtml(
                                row.elemen_gigi ?? '-'
                            )}
                        </td>

                        <td>
                            ${escapeHtml(
                                row.diagnosa ?? '-'
                            )}
                        </td>

                        <td>
                            ${escapeHtml(
                                row.prosedur ?? '-'
                            )}
                        </td>

                        <td>
                            ${escapeHtml(
                                row.keterangan ?? '-'
                            )}
                        </td>

                    `;


                     tbody.appendChild(tr);


                     /*
                     |--------------------------------------------------------------------------
                     | WARNA GIGI
                     |--------------------------------------------------------------------------
                     */

                     tandaiGigi(
                        row
                     );

                  }
               );

            })

            .catch(function(error) {

               console.error(
                  "Error odontogram:",
                  error
               );


               const tbody =
                  document.querySelector(
                     "#tableOdontogram tbody"
                  );


               tbody.innerHTML = `

                <tr>

                    <td
                        colspan="7"
                        style="
                            text-align:center;
                            color:#b42318;
                            padding:10px;
                        ">

                        Gagal mengambil data odontogram.

                    </td>

                </tr>

            `;

            });

      }
   );


   /*
   |--------------------------------------------------------------------------
   | TANDAI GIGI
   |--------------------------------------------------------------------------
   */

   function tandaiGigi(row) {

      const noGigi =
         parseInt(
            row.no_gigi
         );


      if (!noGigi) {
         return;
      }


      const element =
         document.getElementById(
            "gigi-" + noGigi
         );


      if (!element) {
         return;
      }


      /*
      |--------------------------------------------------------------------------
      | SEMUA DATA = HIJAU
      |--------------------------------------------------------------------------
      */

      element.classList.add(
         "has-data"
      );


      /*
      |--------------------------------------------------------------------------
      | DIAGNOSA ABNORMAL
      |--------------------------------------------------------------------------
      */

      const diagnosa =
         String(
            row.diagnosa || ""
         ).toLowerCase();


      const abnormalKeywords = [

         "karies",
         "caries",
         "abses",
         "nekrosis",
         "gangren",
         "impaksi",
         "periodontitis",
         "gingivitis",
         "pulpitis"

      ];


      const abnormal =
         abnormalKeywords.some(
            function(keyword) {

               return diagnosa.includes(
                  keyword
               );

            }
         );


      if (abnormal) {

         element.classList.remove(
            "has-data"
         );

         element.classList.add(
            "abnormal"
         );

      }

   }


   /*
   |--------------------------------------------------------------------------
   | FORMAT TANGGAL
   |--------------------------------------------------------------------------
   */

   function formatTanggal(tgl) {

      if (!tgl) {
         return "-";
      }


      const bulan = [

         "Januari",
         "Februari",
         "Maret",
         "April",
         "Mei",
         "Juni",
         "Juli",
         "Agustus",
         "September",
         "Oktober",
         "November",
         "Desember"

      ];


      const d =
         new Date(tgl);


      if (isNaN(d.getTime())) {

         return tgl;

      }


      return (

         d.getDate() +
         " " +
         bulan[d.getMonth()] +
         " " +
         d.getFullYear()

      );

   }


   /*
   |--------------------------------------------------------------------------
   | ESCAPE HTML
   |--------------------------------------------------------------------------
   */

   function escapeHtml(value) {

      return String(value)

         .replace(
            /&/g,
            "&amp;"
         )

         .replace(
            /</g,
            "&lt;"
         )

         .replace(
            />/g,
            "&gt;"
         )

         .replace(
            /"/g,
            "&quot;"
         )

         .replace(
            /'/g,
            "&#039;"
         );

   }
</script>