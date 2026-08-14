<?php

$title = 'Data Surat Surat FKTP';

require '../../controller/view.php';

?>

<!doctype html>

<html lang="en">

<head>

  <base href="../../">

  <?php
  require '../../assets/template/head.php';
  ?>

</head>


<body>


  <!-- =========================================================
       BODY WRAPPER
  ========================================================== -->

  <div
    class="page-wrapper"
    id="main-wrapper"
    data-layout="vertical"
    data-navbarbg="skin6"
    data-sidebartype="full"
    data-sidebar-position="fixed"
    data-header-position="fixed">


    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

    <?php
    require 'sidebar.php';
    ?>


    <!-- =========================================================
         MAIN WRAPPER
    ========================================================== -->

    <div class="body-wrapper">


      <!-- =======================================================
           HEADER
      ======================================================== -->

      <?php
      require 'navbar.php';
      ?>


      <!-- =======================================================
           CONTENT
      ======================================================== -->

      <div class="body-wrapper-inner">

        <div class="container-fluid">


          <!-- =====================================================
               HEADER
          ====================================================== -->

          <div class="row mb-4">

            <div class="col-12">

              <div
                class="d-flex align-items-center justify-content-between">

                <div>

                  <h4 class="fw-semibold mb-1">

                    Surat &amp; Dokumen

                  </h4>


                  <p class="text-muted mb-0">

                    Kelola dan cetak surat keterangan pasien
                    berdasarkan pelayanan medis.

                  </p>

                </div>

              </div>

            </div>

          </div>


          <!-- =====================================================
               STATUS NOMOR SURAT
          ====================================================== -->

          <div
            class="alert d-none mb-4"
            id="nomorSuratStatus">

            <div class="d-flex align-items-center">

              <iconify-icon
                id="nomorSuratStatusIcon"
                icon="material-symbols:settings-outline"
                width="24"
                class="me-2">
              </iconify-icon>


              <div>

                <strong id="nomorSuratStatusTitle">
                  Status Nomor Surat
                </strong>


                <div
                  id="nomorSuratStatusText"
                  class="small">
                </div>

              </div>

            </div>

          </div>


          <!-- =====================================================
               CARD SURAT
          ====================================================== -->

          <div class="row g-4">


            <!-- =================================================
                 1. SURAT KETERANGAN SEHAT
            ================================================== -->

            <div class="col-xl-4 col-md-6">

              <div class="card border-0 shadow-sm h-100">

                <div class="card-body">


                  <!-- HEADER -->

                  <div class="d-flex align-items-start mb-4">

                    <div
                      class="d-flex align-items-center gap-3">


                      <!-- ICON -->

                      <div
                        class="
                          rounded-circle
                          bg-success-subtle
                          d-flex
                          align-items-center
                          justify-content-center
                        "
                        style="
                          width:52px;
                          height:52px;
                          flex:0 0 52px;
                        ">

                        <iconify-icon
                          icon="material-symbols:health-and-safety-outline"
                          width="28"
                          class="text-success">
                        </iconify-icon>

                      </div>


                      <!-- TITLE -->

                      <div>

                        <h5 class="fw-semibold mb-1">

                          Surat Keterangan Sehat

                        </h5>


                        <span
                          class="
                            badge
                            bg-success-subtle
                            text-success
                          ">

                          Kesehatan

                        </span>

                      </div>

                    </div>

                  </div>


                  <!-- DESCRIPTION -->

                  <p class="text-muted mb-4">

                    Surat yang menerangkan kondisi kesehatan
                    pasien berdasarkan hasil pemeriksaan dokter.

                  </p>


                  <!-- FOOTER -->

                  <div
                    class="
                      d-flex
                      align-items-center
                      justify-content-between
                    ">

                    <small class="text-muted">

                      <iconify-icon
                        icon="material-symbols:description-outline"
                        style="vertical-align:middle;">
                      </iconify-icon>

                      Surat Kesehatan

                    </small>


                    <!-- BUTTON -->

                    <a
                      href="module/letter/form-sks"
                      class="btn btn-success btn-sm btn-buat-surat"
                      data-jenis="sehat"
                      data-nama="Surat Keterangan Sehat"
                      data-url="module/letter/form-sks"
                      style="
                        width:145px;
                        height:46px;
                        padding:0 15px;
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        gap:5px;
                        white-space:nowrap;
                      ">

                      <iconify-icon
                        icon="material-symbols:arrow-forward">
                      </iconify-icon>

                      Buat Surat

                    </a>

                  </div>

                </div>

              </div>

            </div>


            <!-- =================================================
                 2. SURAT KETERANGAN SAKIT
            ================================================== -->

            <div class="col-xl-4 col-md-6">

              <div class="card border-0 shadow-sm h-100">

                <div class="card-body">


                  <!-- HEADER -->

                  <div class="d-flex align-items-start mb-4">

                    <div
                      class="
                        d-flex
                        align-items-center
                        gap-3
                      ">


                      <!-- ICON -->

                      <div
                        class="
                          rounded-circle
                          bg-danger-subtle
                          d-flex
                          align-items-center
                          justify-content-center
                        "
                        style="
                          width:52px;
                          height:52px;
                          flex:0 0 52px;
                        ">

                        <iconify-icon
                          icon="material-symbols:sick-outline"
                          width="28"
                          class="text-danger">
                        </iconify-icon>

                      </div>


                      <!-- TITLE -->

                      <div>

                        <h5 class="fw-semibold mb-1">

                          Surat Keterangan Sakit

                        </h5>


                        <span
                          class="
                            badge
                            bg-danger-subtle
                            text-danger
                          ">

                          Istirahat

                        </span>

                      </div>

                    </div>

                  </div>


                  <!-- DESCRIPTION -->

                  <p class="text-muted mb-4">

                    Surat keterangan yang menyatakan pasien
                    memerlukan waktu istirahat berdasarkan
                    pemeriksaan dokter.

                  </p>


                  <!-- FOOTER -->

                  <div
                    class="
                      d-flex
                      align-items-center
                      justify-content-between
                    ">

                    <small class="text-muted">

                      <iconify-icon
                        icon="material-symbols:description-outline"
                        style="vertical-align:middle;">
                      </iconify-icon>

                      Surat Medis

                    </small>


                    <!-- BUTTON -->

                    <a
                      href="module/letter/form-sick"
                      class="btn btn-danger btn-sm btn-buat-surat"
                      data-jenis="sakit"
                      data-nama="Surat Keterangan Sakit"
                      data-url="module/letter/form-sick"
                      style="
                        width:145px;
                        height:46px;
                        padding:0 15px;
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        gap:5px;
                        white-space:nowrap;
                      ">

                      <iconify-icon
                        icon="material-symbols:arrow-forward">
                      </iconify-icon>

                      Buat Surat

                    </a>

                  </div>

                </div>

              </div>

            </div>


            <!-- =================================================
                 3. SURAT BEROBAT
            ================================================== -->

            <div class="col-xl-4 col-md-6">

              <div class="card border-0 shadow-sm h-100">

                <div class="card-body">


                  <!-- HEADER -->

                  <div class="d-flex align-items-start mb-4">

                    <div
                      class="
                        d-flex
                        align-items-center
                        gap-3
                      ">


                      <!-- ICON -->

                      <div
                        class="
                          rounded-circle
                          bg-primary-subtle
                          d-flex
                          align-items-center
                          justify-content-center
                        "
                        style="
                          width:52px;
                          height:52px;
                          flex:0 0 52px;
                        ">

                        <iconify-icon
                          icon="material-symbols:medical-information-outline"
                          width="28"
                          class="text-primary">
                        </iconify-icon>

                      </div>


                      <!-- TITLE -->

                      <div>

                        <h5 class="fw-semibold mb-1">

                          Surat Berobat

                        </h5>


                        <span
                          class="
                            badge
                            bg-primary-subtle
                            text-primary
                          ">

                          Kunjungan

                        </span>

                      </div>

                    </div>

                  </div>


                  <!-- DESCRIPTION -->

                  <p class="text-muted mb-4">

                    Surat yang menerangkan bahwa pasien telah
                    datang dan mendapatkan pelayanan kesehatan
                    pada faskes.

                  </p>


                  <!-- FOOTER -->

                  <div
                    class="
                      d-flex
                      align-items-center
                      justify-content-between
                    ">

                    <small class="text-muted">

                      <iconify-icon
                        icon="material-symbols:description-outline"
                        style="vertical-align:middle;">
                      </iconify-icon>

                      Bukti Kunjungan

                    </small>


                    <!-- BUTTON -->

                    <a
                      href="module/letter/form-medical"
                      class="btn btn-primary btn-sm btn-buat-surat"
                      data-jenis="berobat"
                      data-nama="Surat Berobat"
                      data-url="module/letter/form-medical"
                      style="
                        width:145px;
                        height:46px;
                        padding:0 15px;
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        gap:5px;
                        white-space:nowrap;
                      ">

                      <iconify-icon
                        icon="material-symbols:arrow-forward">
                      </iconify-icon>

                      Buat Surat

                    </a>

                  </div>

                </div>

              </div>

            </div>


            <!-- =================================================
                 4. CATATAN KEMATIAN
            ================================================== -->

            <div class="col-xl-4 col-md-6">

              <div class="card border-0 shadow-sm h-100">

                <div class="card-body">


                  <!-- HEADER -->

                  <div class="d-flex align-items-start mb-4">

                    <div
                      class="
                        d-flex
                        align-items-center
                        gap-3
                      ">


                      <!-- ICON -->

                      <div
                        class="
                          rounded-circle
                          bg-dark-subtle
                          d-flex
                          align-items-center
                          justify-content-center
                        "
                        style="
                          width:52px;
                          height:52px;
                          flex:0 0 52px;
                        ">

                        <iconify-icon
                          icon="material-symbols:deceased-outline"
                          width="28"
                          class="text-dark">
                        </iconify-icon>

                      </div>


                      <!-- TITLE -->

                      <div>

                        <h5 class="fw-semibold mb-1">

                          Catatan Kematian

                        </h5>


                        <span
                          class="
                            badge
                            bg-dark-subtle
                            text-dark
                          ">

                          Kematian

                        </span>

                      </div>

                    </div>

                  </div>


                  <!-- DESCRIPTION -->

                  <p class="text-muted mb-4">

                    Dokumen catatan kematian yang memuat
                    identitas dan informasi waktu serta tempat
                    kematian pasien.

                  </p>


                  <!-- FOOTER -->

                  <div
                    class="
                      d-flex
                      align-items-center
                      justify-content-between
                    ">

                    <small class="text-muted">

                      <iconify-icon
                        icon="material-symbols:description-outline"
                        style="vertical-align:middle;">
                      </iconify-icon>

                      Catatan Medis

                    </small>


                    <!-- BUTTON -->

                    <a
                      href="module/letter/form-death"
                      class="btn btn-dark btn-sm btn-buat-surat"
                      data-jenis="kematian"
                      data-nama="Catatan Kematian"
                      data-url="module/letter/form-death"
                      style="
                        width:145px;
                        height:46px;
                        padding:0 15px;
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        gap:5px;
                        white-space:nowrap;
                      ">

                      <iconify-icon
                        icon="material-symbols:arrow-forward">
                      </iconify-icon>

                      Buat Surat

                    </a>

                  </div>

                </div>

              </div>

            </div>


            <!-- =================================================
                 5. PEMERIKSAAN MATA
            ================================================== -->

            <div class="col-xl-4 col-md-6">

              <div class="card border-0 shadow-sm h-100">

                <div class="card-body">


                  <!-- HEADER -->

                  <div class="d-flex align-items-start mb-4">

                    <div
                      class="
                        d-flex
                        align-items-center
                        gap-3
                      ">


                      <!-- ICON -->

                      <div
                        class="
                          rounded-circle
                          bg-warning-subtle
                          d-flex
                          align-items-center
                          justify-content-center
                        "
                        style="
                          width:52px;
                          height:52px;
                          flex:0 0 52px;
                        ">

                        <iconify-icon
                          icon="material-symbols:visibility-outline"
                          width="28"
                          class="text-warning">
                        </iconify-icon>

                      </div>


                      <!-- TITLE -->

                      <div>

                        <h5 class="fw-semibold mb-1">

                          Pemeriksaan Mata

                        </h5>


                        <span
                          class="
                            badge
                            bg-warning-subtle
                            text-warning
                          ">

                          Pemeriksaan

                        </span>

                      </div>

                    </div>

                  </div>


                  <!-- DESCRIPTION -->

                  <p class="text-muted mb-4">

                    Hasil pemeriksaan mata meliputi visus,
                    refraksi, pemeriksaan mata, tanda vital
                    dan penunjang medis.

                  </p>


                  <!-- FOOTER -->

                  <div
                    class="
                      d-flex
                      align-items-center
                      justify-content-between
                    ">

                    <small class="text-muted">

                      <iconify-icon
                        icon="material-symbols:visibility-outline"
                        style="vertical-align:middle;">
                      </iconify-icon>

                      Hasil Pemeriksaan

                    </small>


                    <!-- BUTTON -->

                    <a
                      href="module/letter/form-eye"
                      class="btn btn-warning btn-sm btn-buat-surat"
                      data-jenis="mata"
                      data-nama="Pemeriksaan Mata"
                      data-url="module/letter/form-eye"
                      style="
                        width:145px;
                        height:46px;
                        padding:0 15px;
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        gap:5px;
                        white-space:nowrap;
                      ">

                      <iconify-icon
                        icon="material-symbols:arrow-forward">
                      </iconify-icon>

                      Buat Surat

                    </a>

                  </div>

                </div>

              </div>

            </div>


            <!-- =================================================
                 6. SURAT RAWAT INAP
            ================================================== -->

            <div class="col-xl-4 col-md-6">

              <div class="card border-0 shadow-sm h-100">

                <div class="card-body">


                  <!-- HEADER -->

                  <div class="d-flex align-items-start mb-4">

                    <div
                      class="
                        d-flex
                        align-items-center
                        gap-3
                      ">


                      <!-- ICON -->

                      <div
                        class="
                          rounded-circle
                          bg-info-subtle
                          d-flex
                          align-items-center
                          justify-content-center
                        "
                        style="
                          width:52px;
                          height:52px;
                          flex:0 0 52px;
                        ">

                        <iconify-icon
                          icon="material-symbols:bed-outline"
                          width="28"
                          class="text-info">
                        </iconify-icon>

                      </div>


                      <!-- TITLE -->

                      <div>

                        <h5 class="fw-semibold mb-1">

                          Surat Rawat Inap

                        </h5>


                        <span
                          class="
                            badge
                            bg-info-subtle
                            text-info
                          ">

                          Rawat Inap

                        </span>

                      </div>

                    </div>

                  </div>


                  <!-- DESCRIPTION -->

                  <p class="text-muted mb-4">

                    Surat keterangan yang menerangkan bahwa
                    pasien menjalani perawatan rawat inap,
                    termasuk periode perawatan.

                  </p>


                  <!-- FOOTER -->

                  <div
                    class="
                      d-flex
                      align-items-center
                      justify-content-between
                    ">

                    <small class="text-muted">

                      <iconify-icon
                        icon="material-symbols:local-hotel-outline"
                        style="vertical-align:middle;">
                      </iconify-icon>

                      Rawat Inap

                    </small>


                    <!-- BUTTON -->

                    <a
                      href="module/letter/form-outpatient"
                      class="
                        btn
                        btn-info
                        btn-sm
                        text-white
                        btn-buat-surat
                      "
                      data-jenis="rawat_inap"
                      data-nama="Surat Rawat Inap"
                      data-url="module/letter/form-outpatient"
                      style="
                        width:145px;
                        height:46px;
                        padding:0 15px;
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        gap:5px;
                        white-space:nowrap;
                      ">

                      <iconify-icon
                        icon="material-symbols:arrow-forward">
                      </iconify-icon>

                      Buat Surat

                    </a>

                  </div>

                </div>

              </div>

            </div>


          </div>


        </div>

      </div>

    </div>

  </div>


  <!-- =========================================================
       LIBRARY
  ========================================================== -->

  <?php
  require 'library.php';
  ?>


  <!-- =========================================================
       SCRIPT
  ========================================================== -->

  <script>
    /*
    |--------------------------------------------------------------------------
    | API SETTING NOMOR SURAT
    |--------------------------------------------------------------------------
    */

    const settingSuratApi =
      'controller/letter/settingSuratController.php';


    /*
    |--------------------------------------------------------------------------
    | HALAMAN SETTING
    |--------------------------------------------------------------------------
    |
    | Sesuaikan jika nama route halaman setting Anda berbeda.
    |
    */

    const settingSuratPage =
      'module/letter/setting-surat';


    /*
    |--------------------------------------------------------------------------
    | CACHE SETTING
    |--------------------------------------------------------------------------
    */

    let settingNomorSurat =
      null;


    let checkingSetting =
      false;


    /*
    |--------------------------------------------------------------------------
    | CHECK SETTING NOMOR SURAT
    |--------------------------------------------------------------------------
    */

    function checkSettingNomorSurat() {

      return fetch(
          settingSuratApi, {
            method: 'GET',
            cache: 'no-store'
          }
        )

        .then(function(response) {

          if (!response.ok) {

            throw new Error(
              'HTTP Error ' +
              response.status
            );

          }

          return response.json();

        })

        .then(function(response) {


          console.log(
            'SETTING NOMOR SURAT:',
            response
          );


          /*
          |--------------------------------------------------------------------------
          | ERROR
          |--------------------------------------------------------------------------
          */

          if (
            response.status !==
            'success'
          ) {

            throw new Error(
              response.message ||
              'Gagal mengambil setting nomor surat.'
            );

          }


          settingNomorSurat =
            response.data || null;


          return settingNomorSurat;

        });

    }


    /*
    |--------------------------------------------------------------------------
    | TAMPILKAN STATUS GLOBAL
    |--------------------------------------------------------------------------
    */

    function tampilkanStatusSetting() {

      const statusBox =
        document.getElementById(
          'nomorSuratStatus'
        );


      const statusTitle =
        document.getElementById(
          'nomorSuratStatusTitle'
        );


      const statusText =
        document.getElementById(
          'nomorSuratStatusText'
        );


      const statusIcon =
        document.getElementById(
          'nomorSuratStatusIcon'
        );


      if (
        !statusBox ||
        !settingNomorSurat
      ) {

        return;

      }


      const mode =
        String(
          settingNomorSurat.mode_nomor ||
          ''
        ).toUpperCase();


      statusBox.classList.remove(
        'd-none',
        'alert-primary',
        'alert-success',
        'alert-warning'
      );


      /*
      |--------------------------------------------------------------------------
      | AUTO
      |--------------------------------------------------------------------------
      */

      if (
        mode === 'AUTO'
      ) {

        statusBox.classList.add(
          'alert-primary'
        );


        statusIcon.setAttribute(
          'icon',
          'material-symbols:auto-awesome'
        );


        statusTitle.innerText =
          'Nomor Surat Otomatis';


        statusText.innerHTML =
          'Penomoran surat saat ini menggunakan <strong>mode otomatis</strong>. Nomor akan dibuat oleh sistem berdasarkan format dan nomor terakhir yang telah diatur.';

      }


      /*
      |--------------------------------------------------------------------------
      | MANUAL
      |--------------------------------------------------------------------------
      */
      else if (
        mode === 'MANUAL'
      ) {

        statusBox.classList.add(
          'alert-success'
        );


        statusIcon.setAttribute(
          'icon',
          'material-symbols:edit-document-outline'
        );


        statusTitle.innerText =
          'Nomor Surat Manual';


        statusText.innerHTML =
          'Penomoran surat saat ini menggunakan <strong>mode manual</strong>. Nomor surat akan diisi langsung pada form masing-masing surat.';

      }


      /*
      |--------------------------------------------------------------------------
      | MODE TIDAK DIKENAL
      |--------------------------------------------------------------------------
      */
      else {

        statusBox.classList.add(
          'alert-warning'
        );


        statusIcon.setAttribute(
          'icon',
          'material-symbols:warning-outline'
        );


        statusTitle.innerText =
          'Mode Nomor Surat Belum Valid';


        statusText.innerText =
          'Silakan periksa kembali pengaturan nomor surat.';

      }

    }


    /*
    |--------------------------------------------------------------------------
    | ALERT BELUM SETTING
    |--------------------------------------------------------------------------
    */

    function tampilkanBelumSetting(
      namaSurat
    ) {

      Swal.fire({

          icon: 'warning',

          title: 'Nomor Surat Belum Diatur',

          html: `
          <div class="text-muted">

            Pengaturan nomor surat untuk
            <strong>${namaSurat}</strong>
            belum tersedia.

            <br><br>

            Silakan atur terlebih dahulu
            apakah nomor surat akan dibuat
            <strong>otomatis</strong>
            atau
            <strong>manual</strong>.

          </div>
          `,

          showCancelButton: true,

          confirmButtonText: `
          <iconify-icon
            icon="material-symbols:settings-outline"
            style="vertical-align:middle;">
          </iconify-icon>

          Setting Nomor Surat
          `,

          cancelButtonText: 'Batal',

          reverseButtons: true

        })

        .then(function(result) {

          if (
            result.isConfirmed
          ) {

            window.location.href =
              settingSuratPage;

          }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | ALERT MODE AUTO
    |--------------------------------------------------------------------------
    */

    function tampilkanModeAuto(
      namaSurat,
      url
    ) {

      Swal.fire({

          icon: 'info',

          title: 'Nomor Surat Otomatis',

          html: `
          <div class="text-muted">

            <div class="mb-2">

              <strong>
                ${namaSurat}
              </strong>

            </div>

            <div
              class="
                p-3
                rounded
                bg-primary-subtle
                text-primary
                mb-3
              "
            >

              <iconify-icon
                icon="material-symbols:auto-awesome"
                width="22"
                style="vertical-align:middle;">
              </iconify-icon>

              Nomor surat akan
              <strong>dibuat otomatis</strong>
              oleh sistem.

            </div>

            <small>

              Format dan nomor terakhir
              mengikuti pengaturan nomor surat.

            </small>

          </div>
          `,

          showCancelButton: true,

          confirmButtonText: 'Lanjut Buat Surat',

          cancelButtonText: 'Batal',

          reverseButtons: true

        })

        .then(function(result) {

          if (
            result.isConfirmed
          ) {

            window.location.href =
              url;

          }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | ALERT MODE MANUAL
    |--------------------------------------------------------------------------
    */

    function tampilkanModeManual(
      namaSurat,
      url
    ) {

      Swal.fire({

          icon: 'success',

          title: 'Nomor Surat Manual',

          html: `
          <div class="text-muted">

            <div class="mb-2">

              <strong>
                ${namaSurat}
              </strong>

            </div>

            <div
              class="
                p-3
                rounded
                bg-success-subtle
                text-success
                mb-3
              "
            >

              <iconify-icon
                icon="material-symbols:edit-document-outline"
                width="22"
                style="vertical-align:middle;">
              </iconify-icon>

              Nomor surat akan
              <strong>diisi secara manual</strong>
              pada form surat.

            </div>

            <small>

              Silakan masukkan nomor surat
              sesuai penomoran yang berlaku.

            </small>

          </div>
          `,

          showCancelButton: true,

          confirmButtonText: 'Lanjut Buat Surat',

          cancelButtonText: 'Batal',

          reverseButtons: true

        })

        .then(function(result) {

          if (
            result.isConfirmed
          ) {

            window.location.href =
              url;

          }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | BUTTON BUAT SURAT
    |--------------------------------------------------------------------------
    */

    document
      .querySelectorAll(
        '.btn-buat-surat'
      )
      .forEach(function(button) {


        button.addEventListener(
          'click',
          function(event) {

            /*
            |--------------------------------------------------------------------------
            | STOP LINK DEFAULT
            |--------------------------------------------------------------------------
            */

            event.preventDefault();


            /*
            |--------------------------------------------------------------------------
            | CEGAH DOUBLE CLICK
            |--------------------------------------------------------------------------
            */

            if (
              checkingSetting
            ) {

              return;

            }


            checkingSetting =
              true;


            /*
            |--------------------------------------------------------------------------
            | DATA
            |--------------------------------------------------------------------------
            */

            const namaSurat =
              this.dataset.nama ||
              'Surat';


            const url =
              this.dataset.url ||
              this.getAttribute(
                'href'
              );


            /*
            |--------------------------------------------------------------------------
            | LOADING
            |--------------------------------------------------------------------------
            */

            Swal.fire({

              title: 'Memeriksa Pengaturan...',

              text: 'Memeriksa mode nomor surat.',

              allowOutsideClick: false,

              allowEscapeKey: false,

              showConfirmButton: false,

              didOpen: function() {

                Swal.showLoading();

              }

            });


            /*
            |--------------------------------------------------------------------------
            | CHECK API
            |--------------------------------------------------------------------------
            */

            checkSettingNomorSurat()

              .then(function(setting) {


                /*
                |--------------------------------------------------------------------------
                | TUTUP LOADING
                |--------------------------------------------------------------------------
                */

                Swal.close();


                /*
                |--------------------------------------------------------------------------
                | BELUM ADA SETTING
                |--------------------------------------------------------------------------
                */

                if (
                  !setting ||
                  !setting.id
                ) {

                  tampilkanBelumSetting(
                    namaSurat
                  );

                  return;

                }


                /*
                |--------------------------------------------------------------------------
                | MODE
                |--------------------------------------------------------------------------
                */

                const mode =
                  String(
                    setting.mode_nomor ||
                    ''
                  ).toUpperCase();


                /*
                |--------------------------------------------------------------------------
                | AUTO
                |--------------------------------------------------------------------------
                */

                if (
                  mode === 'AUTO'
                ) {

                  tampilkanModeAuto(
                    namaSurat,
                    url
                  );

                  return;

                }


                /*
                |--------------------------------------------------------------------------
                | MANUAL
                |--------------------------------------------------------------------------
                */

                if (
                  mode === 'MANUAL'
                ) {

                  tampilkanModeManual(
                    namaSurat,
                    url
                  );

                  return;

                }


                /*
                |--------------------------------------------------------------------------
                | INVALID
                |--------------------------------------------------------------------------
                */

                Swal.fire({

                    icon: 'warning',

                    title: 'Mode Belum Valid',

                    text: 'Pengaturan nomor surat belum memiliki mode AUTO atau MANUAL.',

                    confirmButtonText: 'Buka Setting'

                  })

                  .then(function() {

                    window.location.href =
                      settingSuratPage;

                  });

              })

              .catch(function(error) {


                console.error(
                  'CHECK SETTING ERROR:',
                  error
                );


                Swal.close();


                Swal.fire({

                  icon: 'error',

                  title: 'Gagal Memeriksa Setting',

                  text: error.message ||
                    'Pengaturan nomor surat gagal diperiksa.'

                });

              })

              .finally(function() {

                checkingSetting =
                  false;

              });

          });

      });


    /*
    |--------------------------------------------------------------------------
    | CHECK SAAT HALAMAN DIBUKA
    |--------------------------------------------------------------------------
    |
    | Ini hanya untuk menampilkan status.
    | Tidak mengganggu user.
    |
    */

    document.addEventListener(
      'DOMContentLoaded',
      function() {

        checkSettingNomorSurat()

          .then(function(setting) {

            /*
            |--------------------------------------------------------------------------
            | BELUM ADA
            |--------------------------------------------------------------------------
            */

            if (
              !setting ||
              !setting.id
            ) {

              const statusBox =
                document.getElementById(
                  'nomorSuratStatus'
                );


              const statusTitle =
                document.getElementById(
                  'nomorSuratStatusTitle'
                );


              const statusText =
                document.getElementById(
                  'nomorSuratStatusText'
                );


              const statusIcon =
                document.getElementById(
                  'nomorSuratStatusIcon'
                );


              statusBox.classList.remove(
                'd-none'
              );


              statusBox.classList.add(
                'alert-warning'
              );


              statusIcon.setAttribute(
                'icon',
                'material-symbols:warning-outline'
              );


              statusTitle.innerText =
                'Nomor Surat Belum Diatur';


              statusText.innerHTML =
                `
                Silakan lakukan pengaturan
                <strong>Nomor Surat</strong>
                sebelum membuat surat.
                `;


              return;

            }


            /*
            |--------------------------------------------------------------------------
            | ADA SETTING
            |--------------------------------------------------------------------------
            */

            tampilkanStatusSetting();

          })

          .catch(function(error) {

            console.error(
              'INITIAL SETTING ERROR:',
              error
            );

          });

      }
    );
  </script>


</body>

</html>