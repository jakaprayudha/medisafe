<?php
$title = 'Surat Keterangan Rawat Inap';
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
                    <h5 class="card-title fw-semibold">Surat Keterangan Rawat Inap</h5>
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
                            Tanggal
                          </th>

                          <th class="text-dark fw-normal">
                            Nama Pasien
                          </th>

                          <th class="text-dark fw-normal">
                            Dokter
                          </th>

                          <th class="text-dark fw-normal">
                            Tanggal Masuk
                          </th>

                          <th class="text-dark fw-normal">
                            Tanggal Pulang
                          </th>

                          <th class="text-dark fw-normal">
                            Diagnosa
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
<!-- ==========================================================
     MODAL SURAT KETERANGAN RAWAT INAP
=========================================================== -->
<!-- =====================================================
     MODAL SURAT KETERANGAN RAWAT INAP
===================================================== -->

<div
  class="modal fade"
  id="programModal"
  tabindex="-1"
  aria-hidden="true">

  <div
    class="modal-dialog modal-lg modal-dialog-scrollable">

    <form
      id="programForm"
      class="modal-content">

      <!-- =================================================
           HEADER
      ================================================== -->

      <div class="modal-header">

        <h5 class="modal-title">
          Tambah Surat Keterangan Rawat Inap
        </h5>

        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal">
        </button>

      </div>


      <!-- =================================================
           BODY
      ================================================== -->

      <div class="modal-body">

        <!-- ID SURAT -->

        <input
          type="hidden"
          name="id"
          id="id">


        <!-- ID CUSTOMER -->

        <input
          type="hidden"
          name="id_customer"
          id="id_customer"
          value="<?= htmlspecialchars(
                    $_SESSION['id_customer'] ?? ''
                  ) ?>">


        <!-- ID PATIENT -->

        <input
          type="hidden"
          name="id_patient"
          id="id_patient">


        <!-- ID VISIT -->

        <input
          type="hidden"
          name="id_visit"
          id="id_visit">


        <!-- =================================================
             IDENTITAS SURAT
        ================================================== -->

        <div class="card border mb-3">

          <div class="card-header bg-light">

            <strong>

              <i class="fas fa-file-medical me-1"></i>

              Identitas Surat

            </strong>

          </div>


          <div class="card-body">

            <div class="row">


              <!-- PASIEN -->

              <div class="col-md-8 mb-3">

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


              <!-- NOMOR SURAT -->

              <div class="col-md-4 mb-3">

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


              <!-- TANGGAL SURAT -->

              <div class="col-md-4">

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


            </div>

          </div>

        </div>


        <!-- =================================================
             INFORMASI RAWAT INAP
        ================================================== -->

        <div class="card border mb-3">

          <div class="card-header bg-light">

            <strong>

              <i class="fas fa-bed me-1"></i>

              Informasi Rawat Inap

            </strong>

          </div>


          <div class="card-body">

            <div class="row">


              <!-- DIAGNOSA -->

              <div class="col-md-12 mb-3">

                <label
                  for="diagnosa"
                  class="form-label">

                  Diagnosa

                </label>


                <input
                  type="text"
                  name="diagnosa"
                  id="diagnosa"
                  class="form-control"
                  readonly>

              </div>


              <!-- TANGGAL MASUK -->

              <div class="col-md-6 mb-3">

                <label
                  for="tanggal_masuk"
                  class="form-label">

                  Tanggal Masuk

                </label>


                <input
                  type="date"
                  name="tanggal_masuk"
                  id="tanggal_masuk"
                  class="form-control"
                  readonly>

              </div>


              <!-- TANGGAL PULANG -->

              <div class="col-md-6 mb-3">

                <label
                  for="tanggal_pulang"
                  class="form-label">

                  Tanggal Pulang

                </label>


                <input
                  type="date"
                  name="tanggal_pulang"
                  id="tanggal_pulang"
                  class="form-control"
                  readonly>

              </div>


              <!-- DPJP -->

              <div class="col-md-12 mb-3">

                <label
                  for="id_doctor"
                  class="form-label">

                  Dokter / DPJP

                </label>


                <input
                  type="text"
                  name="id_doctor"
                  id="id_doctor"
                  class="form-control"
                  readonly>

              </div>


            </div>

          </div>

        </div>


        <!-- =================================================
             KETERANGAN
        ================================================== -->

        <div class="card border mb-3">

          <div class="card-header bg-light">

            <strong>

              <i class="fas fa-comment-medical me-1"></i>

              Keterangan

            </strong>

          </div>


          <div class="card-body">

            <textarea
              name="keterangan"
              id="keterangan"
              class="form-control"
              rows="4"
              placeholder="Keterangan tambahan mengenai rawat inap..."></textarea>

          </div>

        </div>


      </div>


      <!-- =================================================
           FOOTER
      ================================================== -->

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
  | URL
  |--------------------------------------------------------------------------
  */

  const apiUrl =
    'controller/letter/suratRawatInapController';

  const patientVisitUrl =
    'controller/admisi/patientVisitRawatInapController';


  /*
  |--------------------------------------------------------------------------
  | HELPER SET VALUE
  |--------------------------------------------------------------------------
  */

  function setValue(id, value) {

    $('#' + id).val(
      value !== null &&
      value !== undefined ?
      value :
      ''
    );

  }


  /*
  |--------------------------------------------------------------------------
  | ESCAPE HTML
  |--------------------------------------------------------------------------
  */

  function escapeHtml(value) {

    if (
      value === null ||
      value === undefined
    ) {

      return '';

    }

    return $('<div>')
      .text(value)
      .html();

  }


  /*
  |--------------------------------------------------------------------------
  | RESET NOMOR SURAT
  |--------------------------------------------------------------------------
  */

  function resetNomorSurat() {

    $('#nomor_surat')
      .val('')
      .prop('readonly', true)
      .addClass('bg-light')
      .attr(
        'placeholder',
        'Nomor surat'
      );


    $('#nomor_surat_info')
      .html('');

  }


  /*
  |--------------------------------------------------------------------------
  | TAMPILKAN MODE NOMOR SURAT
  |--------------------------------------------------------------------------
  */

  function tampilkanModeNomor(
    mode,
    format = '',
    nomor = ''
  ) {

    mode =
      String(mode || '')
      .toUpperCase();


    /*
    |--------------------------------------------------------------------------
    | MANUAL
    |--------------------------------------------------------------------------
    */

    if (mode === 'MANUAL') {

      $('#nomor_surat')
        .val('')
        .prop('readonly', false)
        .removeClass('bg-light')
        .attr(
          'placeholder',
          'Masukkan nomor surat'
        );


      $('#nomor_surat_info')
        .html(`

          <div class="alert alert-warning py-2 mb-0">

            <div class="d-flex align-items-center">

              <i class="fas fa-keyboard me-2"></i>

              <div>

                <strong>Penomoran Manual</strong>

                <div class="small">
                  Nomor surat diisi secara manual.
                </div>

              </div>

            </div>

          </div>

        `);


      return;

    }


    /*
    |--------------------------------------------------------------------------
    | AUTO
    |--------------------------------------------------------------------------
    */

    if (mode === 'AUTO') {

      $('#nomor_surat')
        .val('')
        .prop('readonly', true)
        .addClass('bg-light')
        .attr(
          'placeholder',
          'Nomor surat dibuat otomatis'
        );


      $('#nomor_surat_info')
        .html(`

          <div class="alert alert-success py-2 mb-0">

            <div class="d-flex align-items-center">

              <i class="fas fa-robot me-2"></i>

              <div>

                <strong>Penomoran Otomatis</strong>

                <div class="small">
                  Format:
                  <strong>
                    ${escapeHtml(format || '-')}
                  </strong>
                </div>

                <div class="small">
                  Nomor terakhir:
                  <strong>
                    ${escapeHtml(nomor)}
                  </strong>
                </div>

              </div>

            </div>

          </div>

        `);


      return;

    }


    /*
    |--------------------------------------------------------------------------
    | MODE TIDAK VALID
    |--------------------------------------------------------------------------
    */

    $('#nomor_surat')
      .val('')
      .prop('readonly', true)
      .addClass('bg-light');


    $('#nomor_surat_info')
      .html(`

        <div class="alert alert-danger py-2 mb-0">

          <i class="fas fa-exclamation-triangle me-1"></i>

          Mode penomoran surat belum dikonfigurasi.

        </div>

      `);

  }


  /*
  |--------------------------------------------------------------------------
  | CHECK SETTING NOMOR SURAT
  |--------------------------------------------------------------------------
  |
  | Controller:
  |
  | ?check_setting=1
  |
  |--------------------------------------------------------------------------
  */

  function checkSettingNomorSurat(callback) {

    fetch(
        apiUrl +
        '?check_setting=1'
      )

      .then(
        function(res) {

          return res.json();

        }
      )

      .then(
        function(response) {


          console.log(
            'SETTING NOMOR RAWAT INAP:',
            response
          );


          /*
          |--------------------------------------------------------------------------
          | SETTING BELUM ADA
          |--------------------------------------------------------------------------
          */

          if (
            response.status ===
            'setting_required'
          ) {

            Swal.fire({

                icon: 'warning',

                title: 'Setting Nomor Surat Belum Ada',

                html: `

                <p class="mb-2">

                  Setting nomor surat untuk
                  <strong>
                    Surat Keterangan Rawat Inap
                  </strong>
                  belum dibuat.

                </p>

                <p class="text-muted small mb-0">

                  Silakan atur mode penomoran
                  Manual atau Otomatis terlebih dahulu.

                </p>

              `,

                showCancelButton: true,

                confirmButtonText: '<i class="fas fa-cog me-1"></i> Setting Nomor Surat',

                cancelButtonText: 'Batal',

                confirmButtonColor: '#0d6efd'

              })

              .then(
                function(result) {

                  if (
                    result.isConfirmed
                  ) {

                    window.location.href =
                      'module/letter/setting-surat';

                  }

                }
              );


            return;

          }


          /*
          |--------------------------------------------------------------------------
          | ERROR
          |--------------------------------------------------------------------------
          */

          if (
            response.status !==
            'success'
          ) {

            Swal.fire(
              'Gagal!',
              response.message ||
              'Gagal membaca setting nomor surat.',
              'error'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | DATA SETTING
          |--------------------------------------------------------------------------
          */

          const setting =
            response.data ||
            response.setting || {};


          const mode =
            setting.mode_nomor ||
            response.mode_nomor ||
            '';


          const format =
            setting.format_rawat_inap ||
            response.format_rawat_inap ||
            '';


          const nomor =
            setting.nomor_rawat_inap ??
            response.nomor_rawat_inap ??
            0;


          /*
          |--------------------------------------------------------------------------
          | VALIDASI MODE
          |--------------------------------------------------------------------------
          */

          if (
            mode !== 'AUTO' &&
            mode !== 'MANUAL'
          ) {

            Swal.fire({

                icon: 'warning',

                title: 'Mode Penomoran Belum Valid',

                text: 'Silakan periksa setting nomor surat rawat inap.',

                showCancelButton: true,

                confirmButtonText: 'Buka Setting',

                cancelButtonText: 'Batal'

              })

              .then(
                function(result) {

                  if (
                    result.isConfirmed
                  ) {

                    window.location.href =
                      'module/letter/setting-surat';

                  }

                }
              );


            return;

          }


          /*
          |--------------------------------------------------------------------------
          | CALLBACK
          |--------------------------------------------------------------------------
          */

          if (
            typeof callback ===
            'function'
          ) {

            callback({

              mode_nomor: mode,

              format_rawat_inap: format,

              nomor_rawat_inap: nomor

            });

          }

        }
      )

      .catch(
        function(error) {

          console.error(
            'checkSettingNomorSurat:',
            error
          );


          Swal.fire(
            'Error!',
            'Tidak dapat membaca setting nomor surat.',
            'error'
          );

        }
      );

  }


  /*
  |--------------------------------------------------------------------------
  | DOCUMENT READY
  |--------------------------------------------------------------------------
  */

  $(document).ready(
    function() {


      /*
      |--------------------------------------------------------------------------
      | DATATABLE
      |--------------------------------------------------------------------------
      */

      const table =
        $('#periodeTable').DataTable({

          processing: true,

          serverSide: false,

          responsive: true,

          scrollX: true,

          ajax: {

            url: apiUrl,

            type: 'GET',

            dataSrc: function(json) {


              console.log(
                'DATA SURAT RAWAT INAP:',
                json
              );


              /*
              |--------------------------------------------------------------------------
              | ERROR
              |--------------------------------------------------------------------------
              */

              if (
                json.status !==
                'success'
              ) {

                Swal.fire(
                  'Gagal!',
                  json.message ||
                  'Gagal mengambil data surat.',
                  'error'
                );


                return [];

              }


              /*
              |--------------------------------------------------------------------------
              | DATA
              |--------------------------------------------------------------------------
              */

              return (
                json.data || []
              ).map(
                function(row) {


                  /*
                  |--------------------------------------------------------------------------
                  | ACTION
                  |--------------------------------------------------------------------------
                  */

                  const actions = `

                      <div class="text-end">

                        <div
                          class="btn-group btn-group-sm"
                          role="group">

                          <!-- CETAK -->

                          <a
                            class="btn btn-primary"
                            href="module/letter/print/surat-keterangan-rawat-inap?id=${encodeURIComponent(row.id)}"
                            target="_blank"
                            title="Cetak Surat">

                            <i class="fas fa-print"></i>

                          </a>


                          <!-- EDIT -->

                          <a
                            class="btn btn-warning edit-btn"
                            href="javascript:void(0);"
                            data-id="${row.id}"
                            title="Edit">

                            <i class="fas fa-edit"></i>

                          </a>


                          <!-- DELETE -->

                          <a
                            class="btn btn-danger delete-btn"
                            href="javascript:void(0);"
                            data-id="${row.id}"
                            title="Hapus">

                            <i class="fas fa-trash"></i>

                          </a>

                        </div>

                      </div>

                    `;


                  /*
                  |--------------------------------------------------------------------------
                  | RETURN
                  |--------------------------------------------------------------------------
                  */

                  return {

                    id: row.id ||
                      '',

                    nomor_surat: row.nomor_surat ||
                      '-',

                    tanggal_surat: row.tanggal_surat ||
                      '-',

                    patient_name: row.patient_name ||
                      '-',

                    doctor_name: row.id_doctor ||
                      '-',

                    visit_date: row.visit_date ||
                      '-',

                    tanggal_masuk: row.tanggal_masuk ||
                      row.visit_date ||
                      '-',

                    tanggal_pulang: row.tanggal_pulang ||
                      '-',

                    diagnosa: row.diagnosa ||
                      '-',

                    keterangan: row.keterangan ||
                      '-',

                    actions: actions

                  };

                }
              );

            },


            error: function(xhr) {

              console.error(
                'DataTable Error:',
                xhr.responseText
              );


              Swal.fire(
                'Error!',
                'Gagal mengambil data surat rawat inap.',
                'error'
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
              data: 'doctor_name'
            },

            {
              data: 'tanggal_masuk'
            },

            {
              data: 'tanggal_pulang'
            },

            {
              data: 'diagnosa'
            },

            {
              data: 'actions',

              orderable: false,

              searchable: false,

              className: 'text-center'

            }

          ],


          /*
          |--------------------------------------------------------------------------
          | LANGUAGE
          |--------------------------------------------------------------------------
          */

          language: {

            emptyTable: 'Belum ada data surat rawat inap.',

            processing: 'Memuat data...',

            search: 'Cari:',

            lengthMenu: 'Tampilkan _MENU_ data',

            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',

            infoEmpty: 'Tidak ada data',

            zeroRecords: 'Data tidak ditemukan.'

          }

        });


      /*
      |--------------------------------------------------------------------------
      | CUSTOM SEARCH
      |--------------------------------------------------------------------------
      */

      $('#customSearch').on(
        'keyup',
        function() {

          table
            .search(
              this.value
            )
            .draw();

        }
      );


      /*
      |--------------------------------------------------------------------------
      | INIT SELECT2
      |--------------------------------------------------------------------------
      */

      function initPatientSelect() {


        const $select =
          $('#id_patient_select');


        /*
        |--------------------------------------------------------------------------
        | CEK ELEMENT
        |--------------------------------------------------------------------------
        */

        if (
          !$select.length
        ) {

          console.error(
            '#id_patient_select tidak ditemukan.'
          );

          return;

        }


        /*
        |--------------------------------------------------------------------------
        | DESTROY SELECT2 LAMA
        |--------------------------------------------------------------------------
        */

        if (
          $select.hasClass(
            'select2-hidden-accessible'
          )
        ) {

          $select.select2(
            'destroy'
          );

        }


        /*
        |--------------------------------------------------------------------------
        | INIT
        |--------------------------------------------------------------------------
        */

        $select.select2({

          dropdownParent: $('#programModal'),

          width: '100%',

          placeholder: 'Cari pasien rawat inap...',

          allowClear: true,

          minimumInputLength: 1,

          ajax: {

            url: patientVisitUrl,

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


              console.log(
                'RESPONSE PASIEN:',
                response
              );


              if (
                !response ||
                response.status !==
                'success'
              ) {

                console.error(
                  'Response pasien tidak valid:',
                  response
                );


                return {

                  results: []

                };

              }


              const items =
                response.data || [];


              return {

                results:

                  items.map(
                    function(item) {

                      return {

                        /*
                        |------------------------------------
                        | VALUE
                        |------------------------------------
                        */

                        id: item.id_patient,


                        /*
                        |------------------------------------
                        | TEXT
                        |------------------------------------
                        */

                        text:

                          (
                            item.patient_name ||
                            '-'
                          )

                          +

                          ' | RM: '

                          +

                          (
                            item.nomor_rm ||
                            '-'
                          )

                          +

                          ' | Masuk: '

                          +

                          (
                            item.visit_date ||
                            '-'
                          ),


                        /*
                        |------------------------------------
                        | DATA
                        |------------------------------------
                        */

                        id_patient: item.id_patient ||
                          '',

                        id_visit: item.id_visit ||
                          '',

                        visit_ID: item.visit_ID ||
                          '',

                        patient_name: item.patient_name ||
                          '',

                        nomor_rm: item.nomor_rm ||
                          '',

                        patient_nik: item.patient_nik ||
                          '',

                        patient_bpjs: item.patient_bpjs ||
                          '',

                        visit_date: item.visit_date ||
                          '',

                        tanggal_pulang: item.tanggal_pulang ||
                          '',

                        diagnosa: item.diagnosa ||
                          '',

                        id_doctor: item.id_doctor ||
                          '',

                        id_poli: item.id_poli ||
                          '',

                        saturasi: item.saturasi ||
                          ''

                      };

                    }
                  )

              };

            },


            /*
            |--------------------------------------------------------------------------
            | AJAX ERROR
            |--------------------------------------------------------------------------
            */

            error: function(xhr) {

              console.error(
                'Patient Search Error:',
                xhr.responseText
              );

            },


            cache: true

          }

        });


        /*
        |--------------------------------------------------------------------------
        | SELECT PASIEN
        |--------------------------------------------------------------------------
        */

        $select.off(
          'select2:select'
        );


        $select.on(
          'select2:select',
          function(e) {


            const data =
              e.params.data;


            console.log(
              'PASIEN DIPILIH:',
              data
            );


            /*
            |--------------------------------------------------------------------------
            | ID PATIENT
            |--------------------------------------------------------------------------
            */

            $('#id_patient')
              .val(
                data.id_patient ||
                ''
              );


            /*
            |--------------------------------------------------------------------------
            | ID VISIT
            |--------------------------------------------------------------------------
            */

            $('#id_visit')
              .val(
                data.id_visit ||
                ''
              );


            /*
            |--------------------------------------------------------------------------
            | DIAGNOSA
            |--------------------------------------------------------------------------
            */

            $('#diagnosa')
              .val(
                data.diagnosa ||
                ''
              );


            /*
            |--------------------------------------------------------------------------
            | TANGGAL MASUK
            |--------------------------------------------------------------------------
            */

            $('#tanggal_masuk')
              .val(
                data.visit_date ||
                ''
              );


            /*
            |--------------------------------------------------------------------------
            | TANGGAL PULANG
            |--------------------------------------------------------------------------
            */

            $('#tanggal_pulang')
              .val(
                data.tanggal_pulang ||
                ''
              );


            /*
            |--------------------------------------------------------------------------
            | DOKTER
            |--------------------------------------------------------------------------
            */

            $('#id_doctor')
              .val(
                data.id_doctor ||
                ''
              );


            /*
            |--------------------------------------------------------------------------
            | DEBUG
            |--------------------------------------------------------------------------
            */

            console.log(
              'ID PATIENT:',
              data.id_patient
            );

            console.log(
              'ID VISIT:',
              data.id_visit
            );

            console.log(
              'DIAGNOSA:',
              data.diagnosa
            );

            console.log(
              'TANGGAL MASUK:',
              data.visit_date
            );

            console.log(
              'TANGGAL PULANG:',
              data.tanggal_pulang
            );

            console.log(
              'DOKTER:',
              data.id_doctor
            );

          }
        );


        /*
        |--------------------------------------------------------------------------
        | CLEAR SELECT2
        |--------------------------------------------------------------------------
        */

        $select.off(
          'select2:clear'
        );


        $select.on(
          'select2:clear',
          function() {


            $('#id_patient')
              .val('');


            $('#id_visit')
              .val('');


            $('#diagnosa')
              .val('');


            $('#tanggal_masuk')
              .val('');


            $('#tanggal_pulang')
              .val('');


            $('#id_doctor')
              .val('');

          }
        );

      }


      /*
      |--------------------------------------------------------------------------
      | INIT SELECT2 SAAT MODAL DIBUKA
      |--------------------------------------------------------------------------
      */

      $('#programModal').on(
        'shown.bs.modal',
        function() {

          initPatientSelect();

        }
      );


      /*
      |--------------------------------------------------------------------------
      | TAMBAH SURAT
      |--------------------------------------------------------------------------
      */

      $('#btnTambah').on(
        'click',
        function() {


          /*
          |--------------------------------------------------------------------------
          | RESET FORM
          |--------------------------------------------------------------------------
          */

          const form =
            $('#programForm')[0];


          if (form) {

            form.reset();

          }


          /*
          |--------------------------------------------------------------------------
          | RESET ID
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
          | RESET SELECT2
          |--------------------------------------------------------------------------
          */

          $('#id_patient_select')
            .val(null)
            .trigger('change');


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
          | RESET RAWAT INAP
          |--------------------------------------------------------------------------
          */

          $('#diagnosa')
            .val('');


          $('#tanggal_masuk')
            .val('');


          $('#tanggal_pulang')
            .val('');


          $('#id_doctor')
            .val('');


          $('#keterangan')
            .val('');


          /*
          |--------------------------------------------------------------------------
          | RESET NOMOR
          |--------------------------------------------------------------------------
          */

          resetNomorSurat();


          /*
          |--------------------------------------------------------------------------
          | TITLE
          |--------------------------------------------------------------------------
          */

          $('#programModal .modal-title')
            .text(
              'Tambah Surat Keterangan Rawat Inap'
            );


          /*
          |--------------------------------------------------------------------------
          | CHECK SETTING TERLEBIH DAHULU
          |--------------------------------------------------------------------------
          */

          checkSettingNomorSurat(
            function(setting) {


              tampilkanModeNomor(

                setting.mode_nomor,

                setting.format_rawat_inap,

                setting.nomor_rawat_inap

              );


              /*
              |--------------------------------------------------------------------------
              | BUKA MODAL SETELAH SETTING VALID
              |--------------------------------------------------------------------------
              */

              $('#programModal')
                .modal('show');

            }
          );

        }
      );


      /*
      |--------------------------------------------------------------------------
      | SUBMIT FORM
      |--------------------------------------------------------------------------
      */

      $('#programForm').on(
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
          | VALIDASI PASIEN
          |--------------------------------------------------------------------------
          */

          if (!idPatient) {

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

          if (!idVisit) {

            Swal.fire(
              'Perhatian!',
              'Visit rawat inap pasien tidak ditemukan.',
              'warning'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | VALIDASI NOMOR MANUAL
          |--------------------------------------------------------------------------
          |
          | Jika input nomor tidak readonly berarti MANUAL.
          |
          |--------------------------------------------------------------------------
          */

          const nomorReadonly =
            $('#nomor_surat')
            .prop('readonly');


          const nomorSurat =
            $.trim(
              $('#nomor_surat').val()
            );


          if (
            !nomorReadonly &&
            !nomorSurat
          ) {

            Swal.fire(
              'Perhatian!',
              'Nomor surat wajib diisi karena penomoran menggunakan mode MANUAL.',
              'warning'
            );


            $('#nomor_surat')
              .focus();


            return;

          }


          /*
          |--------------------------------------------------------------------------
          | FORM DATA
          |--------------------------------------------------------------------------
          */

          const formData =
            new URLSearchParams(
              new FormData(form)
            );


          /*
          |--------------------------------------------------------------------------
          | BUTTON
          |--------------------------------------------------------------------------
          */

          const submitButton =
            $(form).find(
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
                '?id=' +
                encodeURIComponent(id) :
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

                return res.json();

              }
            )

            .then(
              function(data) {


                /*
                |--------------------------------------------------------------------------
                | RESTORE BUTTON
                |--------------------------------------------------------------------------
                */

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

                  Swal.fire({

                      icon: 'warning',

                      title: 'Setting Nomor Surat Belum Ada',

                      text: data.message ||
                        'Silakan setting nomor surat terlebih dahulu.',

                      showCancelButton: true,

                      confirmButtonText: 'Buka Setting',

                      cancelButtonText: 'Batal'

                    })

                    .then(
                      function(result) {

                        if (
                          result.isConfirmed
                        ) {

                          window.location.href =
                            'module/letter/setting-surat';

                        }

                      }
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
                    'Surat keterangan rawat inap berhasil disimpan.',
                    'success'
                  );


                  $('#programModal')
                    .modal('hide');


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
                  'Data gagal disimpan.',
                  'error'
                );

              }
            )

            .catch(
              function(error) {


                console.error(
                  'Submit Error:',
                  error
                );


                submitButton
                  .prop(
                    'disabled',
                    false
                  )
                  .html(
                    originalText
                  );


                Swal.fire(
                  'Error!',
                  'Response server bukan JSON atau terjadi kesalahan server.',
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

      $(document).on(
        'click',
        '.edit-btn',
        function() {


          const id =
            $(this).data('id');


          if (!id) {

            Swal.fire(
              'Error!',
              'ID surat tidak ditemukan.',
              'error'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | LOADING
          |--------------------------------------------------------------------------
          */

          Swal.fire({

            title: 'Memuat data...',

            allowOutsideClick: false,

            didOpen: function() {

              Swal.showLoading();

            }

          });


          /*
          |--------------------------------------------------------------------------
          | GET DETAIL
          |--------------------------------------------------------------------------
          */

          fetch(

              apiUrl +
              '?id=' +
              encodeURIComponent(id)

            )

            .then(
              function(res) {

                return res.json();

              }
            )

            .then(
              function(resp) {


                Swal.close();


                /*
                |--------------------------------------------------------------------------
                | ERROR
                |--------------------------------------------------------------------------
                */

                if (
                  resp.status !==
                  'success'
                ) {

                  Swal.fire(
                    'Gagal!',
                    resp.message ||
                    'Data surat tidak ditemukan.',
                    'error'
                  );

                  return;

                }


                const d =
                  resp.data;


                /*
                |--------------------------------------------------------------------------
                | ID
                |--------------------------------------------------------------------------
                */

                $('#id')
                  .val(
                    d.id ||
                    ''
                  );


                $('#id_patient')
                  .val(
                    d.id_patient ||
                    ''
                  );


                $('#id_visit')
                  .val(
                    d.id_visit ||
                    ''
                  );


                /*
                |--------------------------------------------------------------------------
                | NOMOR SURAT
                |--------------------------------------------------------------------------
                |
                | EDIT:
                | Nomor lama selalu dikunci.
                |
                |--------------------------------------------------------------------------
                */

                $('#nomor_surat')
                  .val(
                    d.nomor_surat ||
                    ''
                  )
                  .prop(
                    'readonly',
                    true
                  )
                  .addClass(
                    'bg-light'
                  );


                $('#nomor_surat_info')
                  .html(`

                    <div class="alert alert-secondary py-2 mb-0">

                      <i class="fas fa-lock me-1"></i>

                      Nomor surat dikunci karena
                      data sedang diedit.

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
                | DIAGNOSA
                |--------------------------------------------------------------------------
                */

                $('#diagnosa')
                  .val(
                    d.diagnosa ||
                    ''
                  );


                /*
                |--------------------------------------------------------------------------
                | TANGGAL MASUK
                |--------------------------------------------------------------------------
                */

                $('#tanggal_masuk')
                  .val(
                    d.tanggal_masuk ||
                    d.visit_date ||
                    ''
                  );


                /*
                |--------------------------------------------------------------------------
                | TANGGAL PULANG
                |--------------------------------------------------------------------------
                */

                $('#tanggal_pulang')
                  .val(
                    d.tanggal_pulang ||
                    ''
                  );


                /*
                |--------------------------------------------------------------------------
                | DOKTER
                |--------------------------------------------------------------------------
                */

                $('#id_doctor')
                  .val(
                    d.id_doctor ||
                    ''
                  );


                /*
                |--------------------------------------------------------------------------
                | KETERANGAN
                |--------------------------------------------------------------------------
                */

                $('#keterangan')
                  .val(
                    d.keterangan ||
                    ''
                  );


                /*
                |--------------------------------------------------------------------------
                | PATIENT SELECT2
                |--------------------------------------------------------------------------
                */

                const patientName =
                  d.patient_name ||
                  'Pasien';


                const nomorRM =
                  d.nomor_rm ||
                  '-';


                const tanggalMasuk =
                  d.tanggal_masuk ||
                  d.visit_date ||
                  '-';


                const option =
                  new Option(

                    patientName +

                    ' | RM: ' +

                    nomorRM +

                    ' | Masuk: ' +

                    tanggalMasuk,

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
                | TITLE
                |--------------------------------------------------------------------------
                */

                $('#programModal .modal-title')
                  .text(
                    'Edit Surat Keterangan Rawat Inap'
                  );


                /*
                |--------------------------------------------------------------------------
                | SHOW MODAL
                |--------------------------------------------------------------------------
                */

                $('#programModal')
                  .modal('show');

              }
            )

            .catch(
              function(error) {


                Swal.close();


                console.error(
                  'Edit Error:',
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

      $(document).on(
        'click',
        '.delete-btn',
        function() {


          const id =
            $(this).data('id');


          if (!id) {

            Swal.fire(
              'Error!',
              'ID surat tidak ditemukan.',
              'error'
            );

            return;

          }


          /*
          |--------------------------------------------------------------------------
          | CONFIRM
          |--------------------------------------------------------------------------
          */

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


                /*
                |--------------------------------------------------------------------------
                | DELETE
                |--------------------------------------------------------------------------
                */

                fetch(

                    apiUrl +
                    '?id=' +
                    encodeURIComponent(id),

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
                          data.message ||
                          'Surat berhasil dihapus.',
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
                          'Data gagal dihapus.',
                          'error'
                        );

                      }

                    }
                  )

                  .catch(
                    function(error) {


                      console.error(
                        'Delete Error:',
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

        });


      /*
      |--------------------------------------------------------------------------
      | RESET MODAL SETELAH DITUTUP
      |--------------------------------------------------------------------------
      */

      $('#programModal').on(
        'hidden.bs.modal',
        function() {


          /*
          |--------------------------------------------------------------------------
          | RESET FORM
          |--------------------------------------------------------------------------
          */

          const form =
            $('#programForm')[0];


          if (form) {

            form.reset();

          }


          /*
          |--------------------------------------------------------------------------
          | RESET ID
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
          | RESET SELECT2
          |--------------------------------------------------------------------------
          */

          $('#id_patient_select')
            .val(null)
            .trigger('change');


          /*
          |--------------------------------------------------------------------------
          | RESET RAWAT INAP
          |--------------------------------------------------------------------------
          */

          $('#diagnosa')
            .val('');


          $('#tanggal_masuk')
            .val('');


          $('#tanggal_pulang')
            .val('');


          $('#id_doctor')
            .val('');


          $('#keterangan')
            .val('');


          /*
          |--------------------------------------------------------------------------
          | RESET NOMOR
          |--------------------------------------------------------------------------
          */

          resetNomorSurat();


          /*
          |--------------------------------------------------------------------------
          | RESET TITLE
          |--------------------------------------------------------------------------
          */

          $('#programModal .modal-title')
            .text(
              'Tambah Surat Keterangan Rawat Inap'
            );

        }
      );

    }
  );
</script>

</html>