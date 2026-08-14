<?php
$title = 'Surat Keterangan Berobat';
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
                    <h5 class="card-title fw-semibold">Surat Keterangan Berobat</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal col-1">Nomor Surat</th>
                          <th class="text-dark fw-normal">Tanggal</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th class="text-dark fw-normal">Dokter Pemeriksa</th>
                          <th class="text-dark fw-normal">Layanan</th>
                          <th class="text-dark fw-normal">Keterangan</th>
                          <th scope="col" class="text-dark fw-normal text-center col-1">Actions</th>
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
     MODAL SURAT KETERANGAN BEROBAT
========================================================== -->

<div
  class="modal fade"
  id="programModal"
  tabindex="-1"
  aria-hidden="true">

  <div
    class="modal-dialog modal-lg modal-dialog-centered">

    <form
      id="programForm"
      class="modal-content">


      <!-- =====================================================
           HEADER
      ====================================================== -->

      <div class="modal-header">

        <h5 class="modal-title">
          Tambah Surat Keterangan Berobat
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
             ID
        ==================================================== -->

        <input
          type="hidden"
          name="id"
          id="id">


        <input
          type="hidden"
          name="id_patient"
          id="id_patient">


        <input
          type="hidden"
          name="id_visit"
          id="id_visit">


        <input
          type="hidden" value="<?= $_SESSION['id_customer'] ?>"
          name="id_customer"
          id="id_customer">


        <!-- ===================================================
             PASIEN
        ==================================================== -->

        <div class="mb-3">

          <label
            for="id_patient_select"
            class="form-label fw-semibold">

            Nama Pasien

            <span class="text-danger">
              *
            </span>

          </label>


          <select
            name="id_patient_select"
            id="id_patient_select"
            class="form-select"
            style="width:100%;"
            required>

            <option value=""></option>

          </select>


          <small class="text-muted">

            Cari berdasarkan nama pasien,
            No. RM, NIK atau BPJS.

          </small>

        </div>


        <!-- ===================================================
             NOMOR SURAT
        ==================================================== -->

        <div
          class="mb-3"
          id="nomorSuratWrapper">

          <label
            for="nomor_surat"
            class="form-label fw-semibold">

            Nomor Surat

          </label>


          <input
            type="text"
            name="nomor_surat"
            id="nomor_surat"
            class="form-control"
            placeholder="Nomor surat">


          <div
            id="nomorSuratInfo"
            class="form-text">
          </div>

        </div>


        <!-- ===================================================
             TANGGAL SURAT
        ==================================================== -->

        <div class="mb-3">

          <label
            for="tanggal_surat"
            class="form-label fw-semibold">

            Tanggal Surat

            <span class="text-danger">
              *
            </span>

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
             DOKTER / POLI
        ==================================================== -->

        <div class="row">


          <!-- DOKTER -->

          <div class="col-md-6 mb-3">

            <label
              for="id_doctor"
              class="form-label fw-semibold">

              Dokter

            </label>


            <input
              type="text"
              id="id_doctor"
              class="form-control"
              readonly>

          </div>


          <!-- POLI -->

          <div class="col-md-6 mb-3">

            <label
              for="id_poli"
              class="form-label fw-semibold">

              Layanan / Poli

            </label>


            <input
              type="text"
              id="id_poli"
              class="form-control"
              readonly>

          </div>

        </div>


        <!-- ===================================================
             KETERANGAN
        ==================================================== -->

        <div class="mb-3">

          <label
            for="keterangan"
            class="form-label fw-semibold">

            Keterangan

          </label>


          <textarea
            name="keterangan"
            id="keterangan"
            class="form-control"
            rows="4"
            placeholder="Keterangan tambahan..."></textarea>

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
          class="btn btn-primary">

          <i class="fas fa-save me-1"></i>

          Simpan

        </button>

      </div>


    </form>

  </div>

</div>

<script>
  const apiUrl =
    'controller/letter/suratBerobatController';

  const settingApiUrl =
    'controller/letter/settingSuratController';


  $(document).ready(function() {


    /* ==========================================================
       DATATABLE
    ========================================================== */

    var table =
      $('#periodeTable').DataTable({

        processing: true,

        serverSide: false,

        ajax: {

          url: apiUrl,

          type: 'GET',

          dataSrc: function(json) {


            if (
              json.status !== 'success'
            ) {

              Swal.fire(
                'Gagal!',
                json.message ||
                'Gagal mengambil data.',
                'error'
              );

              return [];

            }


            return (json.data || []).map(
              function(row) {


                return {

                  actions: `

                  <div class="text-end">

                    <div
                      class="btn-group btn-group-sm"
                      role="group">


                      <!-- PRINT -->

                      <a
                        class="btn btn-primary"
                        href="module/letter/print/surat-keterangan-berobat?id=${row.id}"
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


                  nomor_surat: row.nomor_surat || '-',


                  tanggal_surat: row.tanggal_surat || '-',


                  patient_name: row.patient_name || '-',


                  nomor_rm: row.id_doctor || '-',


                  layanan: row.id_poli || '-',


                  keterangan: row.keterangan || '-'

                };

              }
            );

          }

        },


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
            data: 'nomor_rm'
          },

          {
            data: 'layanan'
          },

          {
            data: 'keterangan'
          },

          {
            data: 'actions',

            orderable: false,

            searchable: false

          }

        ]

      });


    /* ==========================================================
       CUSTOM SEARCH
    ========================================================== */

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


    /* ==========================================================
       SELECT2 PASIEN
       SATU SAJA
    ========================================================== */

    const $select =
      $('#id_patient_select');


    /*
    |--------------------------------------------------------------------------
    | DESTROY JIKA SUDAH PERNAH DI-INISIALISASI
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
    | SELECT2
    |--------------------------------------------------------------------------
    */

    $select.select2({

      dropdownParent: $('#programModal'),

      width: '100%',

      placeholder: 'Cari Pasien Kunjungan...',

      allowClear: true,

      minimumInputLength: 2,

      ajax: {

        url: 'controller/admisi/patientVisitController',

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

            search: params.term || ''

          };

        },


        /*
        |--------------------------------------------------------------------------
        | HASIL
        |--------------------------------------------------------------------------
        */

        processResults: function(response) {


          const items =
            response.data || [];


          return {

            results:

              items.map(
                function(item) {


                  return {

                    /*
                    |--------------------------------------------------------------------------
                    | VALUE SELECT2
                    |--------------------------------------------------------------------------
                    */

                    id: item.id_patient,


                    /*
                    |--------------------------------------------------------------------------
                    | TEXT
                    |--------------------------------------------------------------------------
                    */

                    text:

                      (
                        item.patient_name ||
                        'Pasien'
                      )

                      +

                      ' | RM: ' +

                      (
                        item.nomor_rm ||
                        '-'
                      )

                      +

                      ' | ' +

                      (
                        item.visit_date ||
                        '-'
                      ),


                    /*
                    |--------------------------------------------------------------------------
                    | DATA
                    |--------------------------------------------------------------------------
                    */

                    id_patient: item.id_patient,


                    id_visit: item.id_visit,


                    visit_ID: item.visit_ID,


                    patient_name: item.patient_name,


                    nomor_rm: item.nomor_rm,


                    patient_nik: item.patient_nik,


                    patient_bpjs: item.patient_bpjs,


                    id_doctor: item.id_doctor,


                    id_poli: item.id_poli,


                    visit_date: item.visit_date

                  };

                }
              )

          };

        },


        cache: true

      }

    });


    /* ==========================================================
       SELECT PASIEN
    ========================================================== */

    $select.off(
      'select2:select'
    );


    $select.on(
      'select2:select',
      function(e) {


        const data =
          e.params.data;


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
        | POLI
        |--------------------------------------------------------------------------
        */

        $('#id_poli')
          .val(
            data.id_poli ||
            ''
          );


        console.log(
          'Visit dipilih:',
          data
        );

      }
    );


    /* ==========================================================
       CLEAR PASIEN
    ========================================================== */

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


        $('#id_doctor')
          .val('');


        $('#id_poli')
          .val('');

      }
    );


    /* ==========================================================
       CHECK SETTING NOMOR SURAT
    ========================================================== */

    function checkSettingNomorSurat(
      callback
    ) {


      /*
      |--------------------------------------------------------------------------
      | TYPE SURAT
      |--------------------------------------------------------------------------
      */

      const type =
        'surat_berobat';


      fetch(

          settingApiUrl +
          '?type=' +
          encodeURIComponent(type),

          {

            method: 'GET',

            cache: 'no-store'

          }

        )

        .then(
          function(res) {

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
            | SETTING TIDAK ADA
            |--------------------------------------------------------------------------
            */

            if (
              resp.status !==
              'success'
            ) {


              Swal.fire({

                  icon: 'warning',

                  title: 'Nomor Surat Belum Diatur',

                  text: resp.message ||
                    'Silakan atur setting nomor surat terlebih dahulu.',

                  showCancelButton: true,

                  confirmButtonText: 'Setting Nomor Surat',

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


              callback(null);

              return;

            }


            /*
            |--------------------------------------------------------------------------
            | DATA SETTING
            |--------------------------------------------------------------------------
            */

            const setting =
              resp.data || {};


            /*
            |--------------------------------------------------------------------------
            | VALIDASI MODE
            |--------------------------------------------------------------------------
            */

            const mode =
              String(
                setting.mode_nomor ||
                ''
              ).toUpperCase();


            if (
              mode !== 'MANUAL' &&
              mode !== 'AUTO'
            ) {


              Swal.fire({

                  icon: 'warning',

                  title: 'Setting Nomor Surat Belum Lengkap',

                  text: 'Mode nomor Surat Keterangan Berobat belum ditentukan.',

                  confirmButtonText: 'Setting Sekarang'

                })

                .then(
                  function() {

                    window.location.href =
                      'module/letter/setting-surat';

                  }
                );


              callback(null);

              return;

            }


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            callback(
              setting
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
              'Gagal mengecek setting nomor surat.',
              'error'
            );


            callback(null);

          }
        );

    }


    /* ==========================================================
       APPLY SETTING NOMOR SURAT
    ========================================================== */

    function applySettingNomorSurat(
      setting,
      nomorExisting = ''
    ) {


      if (
        !setting
      ) {

        return;

      }


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


        /*
        |----------------------------------------------------------
        | DISABLE INPUT
        |----------------------------------------------------------
        */

        $('#nomor_surat')
          .val(
            nomorExisting || ''
          )
          .prop(
            'disabled',
            true
          );


        /*
        |----------------------------------------------------------
        | INFO
        |----------------------------------------------------------
        */

        $('#nomorSuratInfo')
          .removeClass(
            'text-warning text-danger'
          )
          .addClass(
            'text-success'
          )
          .html(

            '<i class="fas fa-magic me-1"></i>' +

            'Mode otomatis. Nomor surat akan dibuat oleh sistem.'

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


        /*
        |----------------------------------------------------------
        | ENABLE INPUT
        |----------------------------------------------------------
        */

        $('#nomor_surat')
          .val(
            nomorExisting || ''
          )
          .prop(
            'disabled',
            false
          );


        /*
        |----------------------------------------------------------
        | INFO
        |----------------------------------------------------------
        */

        $('#nomorSuratInfo')
          .removeClass(
            'text-success text-danger'
          )
          .addClass(
            'text-warning'
          )
          .html(

            '<i class="fas fa-keyboard me-1"></i>' +

            'Mode manual. Silakan masukkan nomor surat.'

          );

      }

    }


    /* ==========================================================
       TAMBAH SURAT
    ========================================================== */

    $('#btnTambah').on(
      'click',
      function() {


        /*
        |--------------------------------------------------------------------------
        | RESET FORM
        |--------------------------------------------------------------------------
        */

        $('#programForm')[0]
          .reset();


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
        | RESET DOKTER / POLI
        |--------------------------------------------------------------------------
        */

        $('#id_doctor')
          .val('');


        $('#id_poli')
          .val('');


        /*
        |--------------------------------------------------------------------------
        | RESET SELECT2
        |--------------------------------------------------------------------------
        */

        $select
          .val(null)
          .trigger('change');


        /*
        |--------------------------------------------------------------------------
        | TANGGAL
        |--------------------------------------------------------------------------
        */

        $('#tanggal_surat')
          .val(
            '<?= date('Y-m-d') ?>'
          );


        /*
        |--------------------------------------------------------------------------
        | NOMOR SURAT
        |--------------------------------------------------------------------------
        */

        $('#nomor_surat')
          .val('')
          .prop(
            'disabled',
            true
          );


        $('#nomorSuratInfo')
          .html('');


        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        $('#programModal .modal-title')
          .text(
            'Tambah Surat Keterangan Berobat'
          );


        /*
        |--------------------------------------------------------------------------
        | CHECK SETTING
        |--------------------------------------------------------------------------
        */

        checkSettingNomorSurat(
          function(setting) {


            /*
            |--------------------------------------------------------------------------
            | SETTING TIDAK ADA
            |--------------------------------------------------------------------------
            */

            if (
              !setting
            ) {

              return;

            }


            /*
            |--------------------------------------------------------------------------
            | APPLY SETTING
            |--------------------------------------------------------------------------
            */

            applySettingNomorSurat(
              setting
            );


            /*
            |--------------------------------------------------------------------------
            | TAMPILKAN MODAL
            |--------------------------------------------------------------------------
            */

            $('#programModal')
              .modal('show');

          }
        );

      }
    );


    /* ==========================================================
       SUBMIT FORM
    ========================================================== */

    $('#programForm').on(
      'submit',
      function(e) {


        e.preventDefault();


        const form =
          this;


        const id =
          $('#id')
          .val();


        const idPatient =
          $('#id_patient')
          .val();


        const idVisit =
          $('#id_visit')
          .val();


        /*
        |--------------------------------------------------------------------------
        | VALIDASI PASIEN
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
        | VALIDASI NOMOR MANUAL
        |--------------------------------------------------------------------------
        |
        | Kalau input nomor aktif berarti MANUAL.
        |
        |--------------------------------------------------------------------------
        */

        const nomorDisabled =
          $('#nomor_surat')
          .prop('disabled');


        const nomorSurat =
          $('#nomor_surat')
          .val()
          .trim();


        if (
          !nomorDisabled &&
          !nomorSurat
        ) {


          Swal.fire(
            'Perhatian!',
            'Nomor surat wajib diisi karena menggunakan mode manual.',
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
        | NOMOR DISABLED
        |--------------------------------------------------------------------------
        |
        | Jika AUTO, nomor_surat disabled dan tidak ikut FormData.
        | Controller akan generate otomatis.
        |
        |--------------------------------------------------------------------------
        */


        /*
        |--------------------------------------------------------------------------
        | BUTTON
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
              id

              ?

              '?id=' +
              encodeURIComponent(id)

              :

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

                    title: 'Nomor Surat Belum Diatur',

                    text: data.message ||
                      'Setting nomor surat belum tersedia.',

                    showCancelButton: true,

                    confirmButtonText: 'Setting Sekarang',

                    cancelButtonText: 'Batal'

                  })

                  .then(
                    function(result) {


                      if (
                        result.isConfirmed
                      ) {


                        window.location.href =
                          (
                            data.data &&
                            data.data.redirect
                          )

                          ?

                          data.data.redirect

                          :

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


                Swal.fire({

                  icon: 'success',

                  title: 'Berhasil!',

                  text: data.message ||
                    'Surat berhasil disimpan.',

                  timer: 1500,

                  showConfirmButton: false

                });


                /*
                |--------------------------------------------------------------------------
                | CLOSE MODAL
                |--------------------------------------------------------------------------
                */

                $('#programModal')
                  .modal('hide');


                /*
                |--------------------------------------------------------------------------
                | RELOAD TABLE
                |--------------------------------------------------------------------------
                */

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


              console.error(
                'Submit error:',
                error
              );


              Swal.fire(
                'Error!',
                'Terjadi kesalahan pada server.',
                'error'
              );

            }
          );

      }
    );


    /* ==========================================================
       EDIT
    ========================================================== */

    $(document).on(
      'click',
      '.edit-btn',
      function() {


        const id =
          $(this)
          .data('id');


        if (
          !id
        ) {

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
        | GET DATA
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
                resp.data || {};


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
              | TANGGAL
              |--------------------------------------------------------------------------
              */

              $('#tanggal_surat')
                .val(
                  d.tanggal_surat ||
                  ''
                );


              /*
              |--------------------------------------------------------------------------
              | NOMOR SURAT
              |--------------------------------------------------------------------------
              */

              $('#nomor_surat')
                .val(
                  d.nomor_surat ||
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
              | POLI
              |--------------------------------------------------------------------------
              */

              $('#id_poli')
                .val(
                  d.id_poli ||
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
              | SELECT2 PASIEN
              |--------------------------------------------------------------------------
              */

              const option =
                new Option(

                  (
                    d.patient_name ||
                    'Pasien'
                  )

                  +

                  ' | RM: ' +

                  (
                    d.nomor_rm ||
                    '-'
                  )

                  +

                  ' | ' +

                  (
                    d.visit_date ||
                    '-'
                  ),

                  d.id_patient,

                  true,

                  true

                );


              $select
                .empty()
                .append(option)
                .trigger('change');


              /*
              |--------------------------------------------------------------------------
              | SETTING NOMOR
              |--------------------------------------------------------------------------
              |
              | Ambil ulang setting agar mode MANUAL/AUTO
              | selalu sesuai setting terbaru.
              |
              |--------------------------------------------------------------------------
              */

              checkSettingNomorSurat(
                function(setting) {


                  if (
                    !setting
                  ) {

                    return;

                  }


                  applySettingNomorSurat(

                    setting,

                    d.nomor_surat ||
                    ''

                  );


                  /*
                  |--------------------------------------------------------------------------
                  | TITLE
                  |--------------------------------------------------------------------------
                  */

                  $('#programModal .modal-title')
                    .text(
                      'Edit Surat Keterangan Berobat'
                    );


                  /*
                  |--------------------------------------------------------------------------
                  | SHOW
                  |--------------------------------------------------------------------------
                  */

                  $('#programModal')
                    .modal('show');

                }
              );

            }
          )

          .catch(
            function(error) {


              Swal.close();


              console.error(
                'Edit error:',
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


    /* ==========================================================
       DELETE
    ========================================================== */

    $(document).on(
      'click',
      '.delete-btn',
      function() {


        const id =
          $(this)
          .data('id');


        if (
          !id
        ) {

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

            confirmButtonColor: '#d33',

            reverseButtons: true

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


                      Swal.fire({

                        icon: 'success',

                        title: 'Berhasil!',

                        text: data.message ||
                          'Surat berhasil dihapus.',

                        timer: 1500,

                        showConfirmButton: false

                      });


                      /*
                      |--------------------------------------------------------------------------
                      | RELOAD
                      |--------------------------------------------------------------------------
                      */

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
                      'Delete error:',
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


    /* ==========================================================
       RESET MODAL
    ========================================================== */

    $('#programModal').on(
      'hidden.bs.modal',
      function() {


        /*
        |--------------------------------------------------------------------------
        | RESET HIDDEN
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
        | RESET DOKTER / POLI
        |--------------------------------------------------------------------------
        */

        $('#id_doctor')
          .val('');


        $('#id_poli')
          .val('');


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
          );


        $('#nomorSuratInfo')
          .html('');


        /*
        |--------------------------------------------------------------------------
        | RESET KETERANGAN
        |--------------------------------------------------------------------------
        */

        $('#keterangan')
          .val('');


        /*
        |--------------------------------------------------------------------------
        | RESET SELECT2
        |--------------------------------------------------------------------------
        */

        $select
          .val(null)
          .trigger('change');

      }
    );


  });
</script>

</html>