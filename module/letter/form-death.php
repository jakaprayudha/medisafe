<?php
$title = 'Surat Keterangan Kematian';
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
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <?php
    require '../admin/sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require '../admin/navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Surat Keterangan Kematian</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table
                      class="table text-nowrap align-middle table-custom mb-0"
                      id="periodeTable">

                      <thead>
                        <tr>

                          <th class="text-dark fw-normal col-1">
                            Nomor Surat
                          </th>

                          <th class="text-dark fw-normal">
                            Tanggal Surat
                          </th>

                          <th class="text-dark fw-normal">
                            Nama Pasien
                          </th>

                          <th class="text-dark fw-normal">
                            Tanggal Kematian
                          </th>

                          <th class="text-dark fw-normal">
                            Waktu Kematian
                          </th>

                          <th class="text-dark fw-normal">
                            Ruangan
                          </th>

                          <th class="text-dark fw-normal">
                            Dokter Menyatakan
                          </th>

                          <th
                            scope="col"
                            class="text-dark fw-normal text-center col-1">
                            Actions
                          </th>

                        </tr>
                      </thead>

                      <tbody></tbody>

                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



  <?php
  require '../admin/library.php';
  ?>
</body>
<!-- =========================================================
     MODAL SURAT KETERANGAN KEMATIAN
========================================================= -->

<div class="modal fade" id="programModal" tabindex="-1">

  <div class="modal-dialog">

    <form id="programForm" class="modal-content">

      <!-- =====================================================
           HEADER
      ====================================================== -->

      <div class="modal-header">

        <h5 class="modal-title">
          Tambah Surat Keterangan Kematian
        </h5>

        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal">
        </button>

      </div>


      <!-- =====================================================
           BODY
      ====================================================== -->

      <div class="modal-body">

        <!-- ===================================================
             ID SURAT
        ==================================================== -->

        <input
          type="hidden"
          name="id"
          id="id">


        <!-- ===================================================
             ID CUSTOMER
             Tidak digunakan sebagai sumber utama.
             Controller tetap mengambil dari SESSION.
        ==================================================== -->

        <input
          type="hidden"
          name="id_customer"
          id="id_customer"
          value="<?= htmlspecialchars($_SESSION['id_customer'] ?? '') ?>">


        <!-- ===================================================
             ID PATIENT
        ==================================================== -->

        <input
          type="hidden"
          name="id_patient"
          id="id_patient">


        <!-- ===================================================
             ID VISIT
        ==================================================== -->

        <input
          type="hidden"
          name="id_visit"
          id="id_visit">


        <!-- ===================================================
             NOMOR SURAT
        ==================================================== -->

        <div class="mb-3">

          <label
            for="nomor_surat"
            class="form-label">

            Nomor Surat

          </label>


          <input
            type="text"
            name="nomor_surat"
            id="nomor_surat"
            class="form-control"
            placeholder="Nomor surat">


          <!-- INFORMASI MODE -->

          <div
            id="nomor_surat_info"
            class="mt-2">

          </div>

        </div>


        <!-- ===================================================
             PASIEN
        ==================================================== -->

        <div class="mb-3">

          <label
            for="id_patient_select"
            class="form-label">

            Nama Pasien

          </label>


          <select
            name="id_patient_select"
            id="id_patient_select"
            class="form-select"
            required>

          </select>

        </div>


        <!-- ===================================================
             TANGGAL SURAT
        ==================================================== -->

        <div class="mb-3">

          <label
            for="tanggal_surat"
            class="form-label">

            Tanggal Surat

          </label>


          <input
            type="date"
            name="tanggal_surat"
            id="tanggal_surat"
            value="<?= date('Y-m-d') ?>"
            class="form-control"
            required>

        </div>


        <!-- ===================================================
             DATA KEMATIAN
        ==================================================== -->

        <div class="row">

          <!-- =================================================
               TANGGAL KEMATIAN
          ================================================== -->

          <div class="col-md-6 mb-3">

            <label
              for="tanggal_kematian"
              class="form-label">

              Tanggal Kematian

            </label>


            <input
              type="date"
              name="tanggal_kematian"
              id="tanggal_kematian"
              class="form-control"
              required>

          </div>


          <!-- =================================================
               WAKTU KEMATIAN
          ================================================== -->

          <div class="col-md-6 mb-3">

            <label
              for="waktu_kematian"
              class="form-label">

              Waktu Kematian

            </label>


            <input
              type="time"
              name="waktu_kematian"
              id="waktu_kematian"
              class="form-control"
              required>

          </div>


          <!-- =================================================
               RUANGAN
          ================================================== -->

          <div class="col-md-6 mb-3">

            <label
              for="ruangan"
              class="form-label">

              Ruangan

            </label>


            <input
              type="text"
              name="ruangan"
              id="ruangan"
              class="form-control"
              placeholder="Contoh: Ruang Rawat Inap"
              required>

          </div>


          <!-- =================================================
               DOKTER MENYATAKAN
          ================================================== -->

          <div class="col-md-6 mb-3">

            <label
              for="dokter_menyatakan"
              class="form-label">

              Dokter yang Menyatakan

            </label>


            <select
              class="form-select"
              name="dokter_menyatakan"
              id="dokter_menyatakan"
              required>

              <option value="">
                Pilih Dokter
              </option>


              <?php

              $idcust =
                $_SESSION['id_customer'] ?? '';

              $getdokter = tampildata("
                  SELECT
                      id_doctor,
                      doctor_name
                  FROM ms_doctor
                  WHERE doctor_status = 1
                    AND id_customer = '$idcust'
                  ORDER BY doctor_name ASC
              ");

              ?>


              <?php foreach ($getdokter as $dct): ?>

                <option
                  value="<?= htmlspecialchars(
                            $dct['id_doctor']
                          ) ?>">

                  <?= htmlspecialchars(
                    $dct['doctor_name']
                  ) ?>

                </option>

              <?php endforeach; ?>

            </select>

          </div>

        </div>

      </div>


      <!-- =====================================================
           FOOTER
      ====================================================== -->

      <div class="modal-footer">

        <button
          type="button"
          class="btn btn-light"
          data-bs-dismiss="modal">

          Batal

        </button>


        <button
          type="submit"
          class="btn btn-primary"
          id="btnSimpanSurat">

          <i class="fas fa-save me-1"></i>

          Simpan

        </button>

      </div>

    </form>

  </div>

</div>
<script>
  /*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
*/

  const apiUrl =
    'controller/letter/suratKematianController';


  /*
  |--------------------------------------------------------------------------
  | API SETTING NOMOR SURAT
  |--------------------------------------------------------------------------
  |
  | Tabel:
  | setting_surat
  |
  | Untuk kematian:
  | format_kematian
  | nomor_kematian
  |
  */

  const settingApiUrl =
    'controller/letter/settingSuratController';


  /*
  |--------------------------------------------------------------------------
  | DOCUMENT READY
  |--------------------------------------------------------------------------
  */

  $(document).ready(function() {


    /*
    |--------------------------------------------------------------------------
    | DATATABLE
    |--------------------------------------------------------------------------
    */

    var table =
      $('#periodeTable').DataTable({

        processing: true,

        serverSide: false,

        scrollX: true,

        ajax: {

          url: apiUrl,

          type: 'GET',

          dataSrc: function(json) {

            if (
              json.status !==
              'success'
            ) {

              Swal.fire(
                'Gagal!',
                json.message ||
                'Gagal mengambil data.',
                'error'
              );

              return [];

            }


            return json.data.map(
              function(row) {

                return {

                  /*
                  |--------------------------------------------------------------------------
                  | ACTIONS
                  |--------------------------------------------------------------------------
                  */

                  actions: `

                  <div class="text-end">

                    <div
                      class="btn-group btn-group-sm"
                      role="group">

                      <!-- PRINT -->

                      <a
                        class="btn btn-primary"
                        href="module/letter/print/surat-keterangan-kematian?id=${row.id}"
                        target="_blank"
                        title="Cetak Surat">

                        <i class="fas fa-print"></i>

                      </a>


                      <!-- EDIT -->

                      <a
                        class="btn btn-warning edit-btn"
                        href="javascript:;"
                        data-id="${row.id}"
                        title="Edit">

                        <i class="fas fa-edit"></i>

                      </a>


                      <!-- DELETE -->

                      <a
                        class="btn btn-danger delete-btn"
                        href="javascript:;"
                        data-id="${row.id}"
                        title="Hapus">

                        <i class="fas fa-trash"></i>

                      </a>

                    </div>

                  </div>

                `,


                  /*
                  |--------------------------------------------------------------------------
                  | DATA
                  |--------------------------------------------------------------------------
                  */

                  nomor_surat: row.nomor_surat ||
                    '-',


                  tanggal_surat: row.tanggal_surat ||
                    '-',


                  patient_name: row.patient_name ||
                    '-',


                  tanggal_kematian: row.tanggal_kematian ||
                    '-',


                  waktu_kematian: row.waktu_kematian ||
                    '-',


                  ruangan: row.ruangan ||
                    '-',


                  dokter_name: row.doctor_name ||
                    '-'

                };

              }
            );

          }

        },


        /*
        |--------------------------------------------------------------------------
        | COLUMNS
        |--------------------------------------------------------------------------
        */

        columns: [

          {
            data: 'nomor_surat'
          },


          {
            data: 'tanggal_surat'
          },


          {
            data: 'patient_name'
          },


          {
            data: 'tanggal_kematian'
          },


          {
            data: 'waktu_kematian'
          },


          {
            data: 'ruangan'
          },


          {
            data: 'dokter_name'
          },


          {
            data: 'actions',

            orderable: false,

            searchable: false

          }

        ]

      });


    /*
    |--------------------------------------------------------------------------
    | CUSTOM SEARCH
    |--------------------------------------------------------------------------
    */

    $('#customSearch')
      .on(
        'keyup',
        function() {

          table
            .search(this.value)
            .draw();

        }
      );


    /*
    |--------------------------------------------------------------------------
    | SELECT2 PASIEN
    |--------------------------------------------------------------------------
    */

    const $patient =
      $('#id_patient_select');


    /*
    |--------------------------------------------------------------------------
    | INIT PATIENT SELECT
    |--------------------------------------------------------------------------
    */

    function initPatientSelect() {


      /*
      |--------------------------------------------------------------------------
      | DESTROY SELECT2 JIKA SUDAH ADA
      |--------------------------------------------------------------------------
      */

      if (
        $patient.hasClass(
          'select2-hidden-accessible'
        )
      ) {

        $patient.select2(
          'destroy'
        );

      }


      /*
      |--------------------------------------------------------------------------
      | INIT SELECT2
      |--------------------------------------------------------------------------
      */

      $patient.select2({

        dropdownParent: $('#programModal'),

        width: '100%',

        placeholder: 'Cari Pasien Kunjungan...',

        allowClear: true,

        minimumInputLength: 2,

        ajax: {

          url: 'controller/admisi/patientVisitControllerInOut',

          type: 'GET',

          dataType: 'json',

          delay: 300,


          /*
          |--------------------------------------------------------------------------
          | SEARCH
          |--------------------------------------------------------------------------
          */

          data: function(params) {

            return {

              search: params.term ||
                ''

            };

          },


          /*
          |--------------------------------------------------------------------------
          | RESULT
          |--------------------------------------------------------------------------
          */

          processResults: function(response) {

            let items =
              response.data || [];


            return {

              results:

                items.map(
                  function(item) {

                    return {

                      /*
                      |--------------------------------------
                      | VALUE
                      |--------------------------------------
                      */

                      id: item.id_patient,


                      /*
                      |--------------------------------------
                      | TEXT
                      |--------------------------------------
                      */

                      text:

                        item.patient_name +

                        ' | RM: ' +

                        (
                          item.nomor_rm ||
                          '-'
                        ) +

                        ' | ' +

                        (
                          item.visit_date ||
                          '-'
                        ),


                      /*
                      |--------------------------------------
                      | DATA
                      |--------------------------------------
                      */

                      id_patient: item.id_patient,

                      id_visit: item.id_visit,

                      visit_ID: item.visit_ID,

                      patient_name: item.patient_name,

                      nomor_rm: item.nomor_rm,

                      patient_nik: item.patient_nik,

                      visit_date: item.visit_date,

                      id_doctor: item.id_doctor

                    };

                  }
                )

            };

          },


          cache: true

        }

      });


      /*
      |--------------------------------------------------------------------------
      | SELECT PATIENT
      |--------------------------------------------------------------------------
      */

      $patient.off(
        'select2:select'
      );


      $patient.on(
        'select2:select',
        function(e) {

          const data =
            e.params.data;


          /*
          |--------------------------------------------------------------------------
          | ID PATIENT
          |--------------------------------------------------------------------------
          */

          $('#id_patient').val(
            data.id_patient
          );


          /*
          |--------------------------------------------------------------------------
          | ID VISIT
          |--------------------------------------------------------------------------
          */

          $('#id_visit').val(
            data.id_visit
          );


          /*
          |--------------------------------------------------------------------------
          | DEFAULT TANGGAL KEMATIAN
          |--------------------------------------------------------------------------
          */

          if (
            data.visit_date &&
            !$('#tanggal_kematian').val()
          ) {

            $('#tanggal_kematian')
              .val(
                data.visit_date
              );

          }


          /*
          |--------------------------------------------------------------------------
          | DEBUG
          |--------------------------------------------------------------------------
          */

          console.log(
            'Visit dipilih:',
            data
          );

        }
      );


      /*
      |--------------------------------------------------------------------------
      | CLEAR
      |--------------------------------------------------------------------------
      */

      $patient.off(
        'select2:clear'
      );


      $patient.on(
        'select2:clear',
        function() {

          $('#id_patient')
            .val('');

          $('#id_visit')
            .val('');

        }
      );

    }


    /*
    |--------------------------------------------------------------------------
    | INIT SELECT2
    |--------------------------------------------------------------------------
    */

    initPatientSelect();


    /*
    |--------------------------------------------------------------------------
    | RESET NOMOR SURAT
    |--------------------------------------------------------------------------
    */

    function resetNomorSurat() {

      $('#nomor_surat')
        .val('')
        .prop(
          'disabled',
          false
        )
        .prop(
          'readonly',
          false
        )
        .prop(
          'required',
          true
        );

      $('#nomor_surat_info')
        .html('');

    }


    /*
    |--------------------------------------------------------------------------
    | SET MODE NOMOR MANUAL
    |--------------------------------------------------------------------------
    */

    function setNomorManual() {

      $('#nomor_surat')
        .prop(
          'disabled',
          false
        )
        .prop(
          'readonly',
          false
        )
        .prop(
          'required',
          true
        )
        .attr(
          'placeholder',
          'Masukkan nomor surat'
        );


      $('#nomor_surat_info')
        .html(`

        <div class="alert alert-warning py-2 px-3 mb-0">

          <i class="fas fa-keyboard me-1"></i>

          <strong>Mode Manual</strong><br>

          Nomor surat harus diisi secara manual.

        </div>

      `);

    }


    /*
    |--------------------------------------------------------------------------
    | SET MODE AUTO
    |--------------------------------------------------------------------------
    */

    function setNomorAuto(
      format,
      nomor
    ) {

      $('#nomor_surat')
        .val('')
        .prop(
          'disabled',
          true
        )
        .prop(
          'readonly',
          true
        )
        .prop(
          'required',
          false
        )
        .attr(
          'placeholder',
          'Nomor dibuat otomatis'
        );


      $('#nomor_surat_info')
        .html(`

        <div class="alert alert-info py-2 px-3 mb-0">

          <i class="fas fa-robot me-1"></i>

          <strong>Mode Otomatis</strong><br>

          Nomor surat akan dibuat otomatis oleh sistem.

          ${
            format
              ? `<br>
                 <small>
                   Format:
                   <strong>${escapeHtml(format)}</strong>
                 </small>`
              : ''
          }

          ${
            nomor !== undefined &&
            nomor !== null
              ? `<br>
                 <small>
                   Nomor terakhir:
                   <strong>${escapeHtml(String(nomor))}</strong>
                 </small>`
              : ''
          }

        </div>

      `);

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(
      value
    ) {

      return String(value)
        .replace(
          /&/g,
          '&amp;'
        )
        .replace(
          /</g,
          '&lt;'
        )
        .replace(
          />/g,
          '&gt;'
        )
        .replace(
          /"/g,
          '&quot;'
        )
        .replace(
          /'/g,
          '&#039;'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SETTING NOMOR SURAT
    |--------------------------------------------------------------------------
    |
    | Khusus surat kematian:
    |
    | mode_nomor
    | format_kematian
    | nomor_kematian
    |
    |--------------------------------------------------------------------------
    */

    function checkSettingNomorSurat() {

      return fetch(
          settingApiUrl +
          '?check=1'
        )

        .then(
          function(res) {

            if (
              !res.ok
            ) {

              throw new Error(
                'HTTP ' +
                res.status
              );

            }

            return res.json();

          }
        )

        .then(
          function(resp) {

            console.log(
              'Setting nomor surat:',
              resp
            );


            /*
            |--------------------------------------------------------------------------
            | SETTING BELUM ADA
            |--------------------------------------------------------------------------
            */

            if (
              resp.status ===
              'setting_required' ||
              resp.status ===
              'not_found' ||
              resp.setting_not_found ===
              true
            ) {

              return {

                success: false,

                settingRequired: true,

                message: resp.message ||
                  'Setting nomor surat belum dibuat.'

              };

            }


            /*
            |--------------------------------------------------------------------------
            | ERROR
            |--------------------------------------------------------------------------
            */

            if (
              resp.status !==
              'success'
            ) {

              return {

                success: false,

                settingRequired: false,

                message: resp.message ||
                  'Gagal membaca setting nomor surat.'

              };

            }


            /*
            |--------------------------------------------------------------------------
            | AMBIL DATA SETTING
            |--------------------------------------------------------------------------
            */

            let setting =
              resp.data ||
              resp.setting || {};


            /*
            |--------------------------------------------------------------------------
            | MODE
            |--------------------------------------------------------------------------
            */

            let mode =
              String(
                setting.mode_nomor ||
                ''
              )
              .toUpperCase();


            /*
            |--------------------------------------------------------------------------
            | VALIDASI MODE
            |--------------------------------------------------------------------------
            */

            if (
              mode !== 'AUTO' &&
              mode !== 'MANUAL'
            ) {

              return {

                success: false,

                settingRequired: true,

                message: 'Mode penomoran surat belum ditentukan.'

              };

            }


            /*
            |--------------------------------------------------------------------------
            | KHUSUS KEMATIAN
            |--------------------------------------------------------------------------
            */

            return {

              success: true,

              settingRequired: false,

              mode: mode,

              format: setting.format_kematian ||
                'SKM/{NO}/{MM}/{YYYY}',

              nomor: setting.nomor_kematian ||
                0

            };

          }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SHOW SETTING REQUIRED ALERT
    |--------------------------------------------------------------------------
    */

    function showSettingRequired(
      message
    ) {

      Swal.fire({

        title: 'Setting Nomor Surat Belum Ada',

        text: message ||
          'Silakan lakukan setting nomor surat terlebih dahulu.',

        icon: 'warning',

        showCancelButton: true,

        confirmButtonText: 'Buka Setting Surat',

        cancelButtonText: 'Batal',

        confirmButtonColor: '#0d6efd'

      }).then(
        function(result) {

          if (
            result.isConfirmed
          ) {

            window.location.href =
              'module/letter/setting-surat';

          }

        }
      );

    }


    /*
    |--------------------------------------------------------------------------
    | APPLY SETTING TO MODAL
    |--------------------------------------------------------------------------
    */

    function applyNomorSetting(
      setting
    ) {

      if (
        !setting ||
        !setting.success
      ) {

        return false;

      }


      /*
      |--------------------------------------------------------------------------
      | MANUAL
      |--------------------------------------------------------------------------
      */

      if (
        setting.mode ===
        'MANUAL'
      ) {

        setNomorManual();

        return true;

      }


      /*
      |--------------------------------------------------------------------------
      | AUTO
      |--------------------------------------------------------------------------
      */

      if (
        setting.mode ===
        'AUTO'
      ) {

        setNomorAuto(

          setting.format,

          setting.nomor

        );

        return true;

      }


      return false;

    }


    /*
    |--------------------------------------------------------------------------
    | TAMBAH SURAT
    |--------------------------------------------------------------------------
    */

    $('#btnTambah')
      .on(
        'click',
        function() {


          /*
          |--------------------------------------------------------------------------
          | CEK SETTING DULU
          |--------------------------------------------------------------------------
          */

          checkSettingNomorSurat()

            .then(
              function(setting) {


                /*
                |--------------------------------------------------------------------------
                | SETTING BELUM ADA
                |--------------------------------------------------------------------------
                */

                if (
                  setting.settingRequired
                ) {

                  showSettingRequired(
                    setting.message
                  );

                  return;

                }


                /*
                |--------------------------------------------------------------------------
                | ERROR CEK SETTING
                |--------------------------------------------------------------------------
                */

                if (
                  !setting.success
                ) {

                  Swal.fire(
                    'Gagal!',
                    setting.message ||
                    'Gagal membaca setting nomor surat.',
                    'error'
                  );

                  return;

                }


                /*
                |--------------------------------------------------------------------------
                | RESET FORM
                |--------------------------------------------------------------------------
                */

                $('#programForm')[0]
                  .reset();


                /*
                |--------------------------------------------------------------------------
                | ID
                |--------------------------------------------------------------------------
                */

                $('#id')
                  .val('');


                $('#id_patient')
                  .val('');


                $('#id_visit')
                  .val('');


                /*
                |--------------------------------------------------------------------------
                | RESET PATIENT
                |--------------------------------------------------------------------------
                */

                $('#id_patient_select')
                  .val(null)
                  .trigger(
                    'change'
                  );


                /*
                |--------------------------------------------------------------------------
                | TANGGAL SURAT
                |--------------------------------------------------------------------------
                */

                $('#tanggal_surat')
                  .val(
                    '<?= date('Y-m-d') ?>'
                  );


                /*
                |--------------------------------------------------------------------------
                | TANGGAL KEMATIAN
                |--------------------------------------------------------------------------
                */

                $('#tanggal_kematian')
                  .val('');


                /*
                |--------------------------------------------------------------------------
                | WAKTU
                |--------------------------------------------------------------------------
                */

                $('#waktu_kematian')
                  .val('');


                /*
                |--------------------------------------------------------------------------
                | RUANGAN
                |--------------------------------------------------------------------------
                */

                $('#ruangan')
                  .val('');


                /*
                |--------------------------------------------------------------------------
                | DOKTER
                |--------------------------------------------------------------------------
                */

                $('#dokter_menyatakan')
                  .val('')
                  .trigger(
                    'change'
                  );


                /*
                |--------------------------------------------------------------------------
                | NOMOR SURAT
                |--------------------------------------------------------------------------
                */

                $('#nomor_surat')
                  .val('');


                /*
                |--------------------------------------------------------------------------
                | APPLY MODE
                |--------------------------------------------------------------------------
                */

                applyNomorSetting(
                  setting
                );


                /*
                |--------------------------------------------------------------------------
                | TITLE
                |--------------------------------------------------------------------------
                */

                $('#programModal .modal-title')
                  .text(
                    'Tambah Surat Keterangan Kematian'
                  );


                /*
                |--------------------------------------------------------------------------
                | SHOW MODAL
                |--------------------------------------------------------------------------
                */

                $('#programModal')
                  .modal(
                    'show'
                  );

              }
            )

            .catch(
              function(error) {

                console.error(
                  'Setting error:',
                  error
                );


                Swal.fire(
                  'Error!',
                  'Gagal memeriksa setting nomor surat.',
                  'error'
                );

              }
            );

        }
      );


    /*
    |--------------------------------------------------------------------------
    | SUBMIT
    |--------------------------------------------------------------------------
    */

    $('#programForm')
      .on(
        'submit',
        function(e) {

          e.preventDefault();


          const form =
            this;


          const id =
            $('#id').val();


          const idPatient =
            $('#id_patient').val();


          const idVisit =
            $('#id_visit').val();


          /*
          |--------------------------------------------------------------------------
          | VALIDASI PATIENT
          |--------------------------------------------------------------------------
          */

          if (
            !idPatient
          ) {

            Swal.fire(
              'Perhatian!',
              'Silakan pilih pasien terlebih dahulu.',
              'warning'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | VALIDASI VISIT
          |--------------------------------------------------------------------------
          */

          if (
            !idVisit
          ) {

            Swal.fire(
              'Perhatian!',
              'Visit pasien tidak ditemukan.',
              'warning'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | VALIDASI NOMOR SURAT
          |--------------------------------------------------------------------------
          |
          | Hanya validasi jika input aktif.
          |
          |--------------------------------------------------------------------------
          */

          const nomorDisabled =
            $('#nomor_surat')
            .prop(
              'disabled'
            );


          if (
            !id &&
            !nomorDisabled
          ) {

            if (
              !$('#nomor_surat')
              .val()
              .trim()
            ) {

              Swal.fire(
                'Perhatian!',
                'Nomor surat wajib diisi karena menggunakan mode manual.',
                'warning'
              );

              return;

            }

          }


          /*
          |--------------------------------------------------------------------------
          | VALIDASI TANGGAL KEMATIAN
          |--------------------------------------------------------------------------
          */

          if (
            !$('#tanggal_kematian')
            .val()
          ) {

            Swal.fire(
              'Perhatian!',
              'Tanggal kematian wajib diisi.',
              'warning'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | VALIDASI WAKTU
          |--------------------------------------------------------------------------
          */

          if (
            !$('#waktu_kematian')
            .val()
          ) {

            Swal.fire(
              'Perhatian!',
              'Waktu kematian wajib diisi.',
              'warning'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | VALIDASI RUANGAN
          |--------------------------------------------------------------------------
          */

          if (
            !$('#ruangan')
            .val()
            .trim()
          ) {

            Swal.fire(
              'Perhatian!',
              'Ruangan wajib diisi.',
              'warning'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | VALIDASI DOKTER
          |--------------------------------------------------------------------------
          */

          if (
            !$('#dokter_menyatakan')
            .val()
          ) {

            Swal.fire(
              'Perhatian!',
              'Dokter yang menyatakan wajib dipilih.',
              'warning'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | FORM DATA
          |--------------------------------------------------------------------------
          */

          let formData =
            new URLSearchParams(
              new FormData(form)
            );


          /*
          |--------------------------------------------------------------------------
          | BUTTON LOADING
          |--------------------------------------------------------------------------
          */

          const submitButton =
            $(form)
            .find(
              'button[type="submit"]'
            );


          const originalText =
            submitButton.html();


          submitButton
            .prop(
              'disabled',
              true
            )
            .html(
              '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...'
            );


          /*
          |--------------------------------------------------------------------------
          | REQUEST
          |--------------------------------------------------------------------------
          */

          fetch(

              apiUrl +

              (
                id ?
                '?id=' + id :
                ''
              ),

              {

                method: id ?
                  'PUT' : 'POST',

                headers: {

                  'Content-Type': 'application/x-www-form-urlencoded'

                },

                body: formData

              }

            )

            .then(
              function(res) {

                return res.text();

              }
            )

            .then(
              function(text) {

                /*
                |--------------------------------------------------------------------------
                | DEBUG RESPONSE
                |--------------------------------------------------------------------------
                */

                console.log(
                  'Response server:',
                  text
                );


                let data;


                try {

                  data =
                    JSON.parse(
                      text
                    );

                } catch (error) {

                  throw new Error(
                    text ||
                    'Response server bukan JSON.'
                  );

                }


                return data;

              }
            )

            .then(
              function(data) {


                submitButton
                  .prop(
                    'disabled',
                    false
                  )
                  .html(
                    originalText
                  );


                /*
                |--------------------------------------------------------------------------
                | SETTING REQUIRED
                |--------------------------------------------------------------------------
                */

                if (
                  data.status ===
                  'setting_required'
                ) {

                  showSettingRequired(
                    data.message
                  );

                  return;

                }


                /*
                |--------------------------------------------------------------------------
                | SUCCESS
                |--------------------------------------------------------------------------
                */

                if (
                  data.status ===
                  'success'
                ) {


                  Swal.fire(
                    'Berhasil!',
                    data.message ||
                    'Data berhasil disimpan.',
                    'success'
                  );


                  $('#programModal')
                    .modal(
                      'hide'
                    );


                  table.ajax.reload(
                    null,
                    false
                  );


                  return;

                }


                /*
                |--------------------------------------------------------------------------
                | ERROR
                |--------------------------------------------------------------------------
                */

                Swal.fire(
                  'Gagal!',
                  data.message ||
                  'Terjadi kesalahan.',
                  'error'
                );

              }
            )

            .catch(
              function(error) {

                submitButton
                  .prop(
                    'disabled',
                    false
                  )
                  .html(
                    originalText
                  );


                console.error(
                  'Submit error:',
                  error
                );


                Swal.fire(
                  'Error!',
                  error.message ||
                  'Terjadi kesalahan pada server.',
                  'error'
                );

              }
            );

        }
      );


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    $(document)
      .on(
        'click',
        '.edit-btn',
        function() {


          let id =
            $(this).data('id');


          fetch(
              apiUrl +
              '?id=' +
              id
            )

            .then(
              function(res) {

                return res.json();

              }
            )

            .then(
              function(resp) {


                if (
                  resp.status !==
                  'success'
                ) {

                  Swal.fire(
                    'Gagal!',
                    resp.message ||
                    'Data tidak ditemukan.',
                    'error'
                  );

                  return;

                }


                let d =
                  resp.data;


                /*
                |--------------------------------------------------------------------------
                | ID
                |--------------------------------------------------------------------------
                */

                $('#id')
                  .val(
                    d.id
                  );


                $('#id_patient')
                  .val(
                    d.id_patient
                  );


                $('#id_visit')
                  .val(
                    d.id_visit
                  );


                /*
                |--------------------------------------------------------------------------
                | NOMOR SURAT
                |--------------------------------------------------------------------------
                |
                | NOMOR LAMA TIDAK BOLEH DIUBAH
                |
                |--------------------------------------------------------------------------
                */

                $('#nomor_surat')
                  .val(
                    d.nomor_surat ||
                    ''
                  )
                  .prop(
                    'disabled',
                    false
                  )
                  .prop(
                    'readonly',
                    true
                  )
                  .prop(
                    'required',
                    false
                  );


                $('#nomor_surat_info')
                  .html(`

                  <div class="alert alert-secondary py-2 px-3 mb-0">

                    <i class="fas fa-lock me-1"></i>

                    Nomor surat tersimpan dan tidak dapat diubah saat edit.

                  </div>

                `);


                /*
                |--------------------------------------------------------------------------
                | TANGGAL SURAT
                |--------------------------------------------------------------------------
                */

                $('#tanggal_surat')
                  .val(
                    d.tanggal_surat ||
                    ''
                  );


                /*
                |--------------------------------------------------------------------------
                | TANGGAL KEMATIAN
                |--------------------------------------------------------------------------
                */

                $('#tanggal_kematian')
                  .val(
                    d.tanggal_kematian ||
                    ''
                  );


                /*
                |--------------------------------------------------------------------------
                | WAKTU KEMATIAN
                |--------------------------------------------------------------------------
                */

                $('#waktu_kematian')
                  .val(
                    d.waktu_kematian ||
                    ''
                  );


                /*
                |--------------------------------------------------------------------------
                | RUANGAN
                |--------------------------------------------------------------------------
                */

                $('#ruangan')
                  .val(
                    d.ruangan ||
                    ''
                  );


                /*
                |--------------------------------------------------------------------------
                | DOKTER
                |--------------------------------------------------------------------------
                */

                $('#dokter_menyatakan')
                  .val(
                    d.dokter_menyatakan ||
                    ''
                  )
                  .trigger(
                    'change'
                  );


                /*
                |--------------------------------------------------------------------------
                | SELECT2 PATIENT
                |--------------------------------------------------------------------------
                */

                const option =
                  new Option(

                    d.patient_name +

                    ' - RM ' +

                    (
                      d.nomor_rm ||
                      '-'
                    ),

                    d.id_patient,

                    true,

                    true

                  );


                $('#id_patient_select')
                  .empty()
                  .append(
                    option
                  )
                  .trigger(
                    'change'
                  );


                /*
                |--------------------------------------------------------------------------
                | MODAL TITLE
                |--------------------------------------------------------------------------
                */

                $('#programModal .modal-title')
                  .text(
                    'Edit Surat Keterangan Kematian'
                  );


                /*
                |--------------------------------------------------------------------------
                | SHOW MODAL
                |--------------------------------------------------------------------------
                */

                $('#programModal')
                  .modal(
                    'show'
                  );

              }
            )

            .catch(
              function(error) {

                console.error(
                  error
                );


                Swal.fire(
                  'Error!',
                  'Gagal mengambil data surat.',
                  'error'
                );

              }
            );

        }
      );


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    $(document)
      .on(
        'click',
        '.delete-btn',
        function() {


          let id =
            $(this).data('id');


          Swal.fire({

              title: 'Hapus Surat?',

              text: 'Data surat yang dihapus tidak dapat dikembalikan.',

              icon: 'warning',

              showCancelButton: true,

              confirmButtonText: 'Ya, Hapus',

              cancelButtonText: 'Batal',

              confirmButtonColor: '#d33'

            })

            .then(
              function(result) {


                if (
                  !result.isConfirmed
                ) {

                  return;

                }


                fetch(

                    apiUrl +
                    '?id=' +
                    id,

                    {

                      method: 'DELETE'

                    }

                  )

                  .then(
                    function(res) {

                      return res.json();

                    }
                  )

                  .then(
                    function(data) {


                      if (
                        data.status ===
                        'success'
                      ) {


                        Swal.fire(
                          'Berhasil!',
                          data.message,
                          'success'
                        );


                        table.ajax.reload(
                          null,
                          false
                        );


                      } else {


                        Swal.fire(
                          'Gagal!',
                          data.message ||
                          'Gagal menghapus data.',
                          'error'
                        );

                      }

                    }
                  )

                  .catch(
                    function(error) {

                      console.error(
                        error
                      );


                      Swal.fire(
                        'Error!',
                        'Gagal menghapus data.',
                        'error'
                      );

                    }
                  );

              }
            );

        }
      );


    /*
    |--------------------------------------------------------------------------
    | RESET MODAL SAAT DITUTUP
    |--------------------------------------------------------------------------
    */

    $('#programModal')
      .on(
        'hidden.bs.modal',
        function() {

          /*
          |--------------------------------------------------------------------------
          | RESET NOMOR
          |--------------------------------------------------------------------------
          */

          $('#nomor_surat')
            .val('')
            .prop(
              'disabled',
              false
            )
            .prop(
              'readonly',
              false
            )
            .prop(
              'required',
              true
            );


          $('#nomor_surat_info')
            .html('');


          /*
          |--------------------------------------------------------------------------
          | RESET SELECT2
          |--------------------------------------------------------------------------
          */

          $('#id_patient_select')
            .val(null)
            .trigger(
              'change'
            );

        }
      );

  });
</script>

</html>