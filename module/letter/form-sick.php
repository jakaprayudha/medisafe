<?php
$title = 'Surat Keterangan Sakit';
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
                    <h5 class="card-title fw-semibold">Surat Keterangan Sakit</h5>
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
<!-- =========================================================
     MODAL SURAT KETERANGAN SAKIT
========================================================= -->

<div
  class="modal fade"
  id="programModal"
  tabindex="-1"
  aria-hidden="true">

  <div class="modal-dialog modal-lg">

    <form
      id="programForm"
      class="modal-content">


      <!-- =====================================================
           HEADER
      ====================================================== -->

      <div class="modal-header">

        <h5 class="modal-title">
          Tambah Surat Keterangan Sakit
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
             NOMOR SURAT MANUAL
        ==================================================== -->

        <div
          class="mb-3"
          id="nomorSuratWrapper">

          <label
            for="nomor_surat"
            class="form-label">

            Nomor Surat

            <span class="text-danger">
              *
            </span>

          </label>


          <input
            type="text"
            name="nomor_surat"
            id="nomor_surat"
            class="form-control"
            placeholder="Masukkan nomor surat">


          <div
            id="nomorSuratHelp"
            class="form-text text-muted">
          </div>

        </div>


        <!-- ===================================================
             INFO AUTO
        ==================================================== -->

        <div
          class="alert alert-primary d-none"
          id="nomorSuratAutoInfo">

          <div class="d-flex align-items-start">

            <iconify-icon
              icon="material-symbols:auto-awesome"
              width="24"
              class="me-2">
            </iconify-icon>


            <div>

              <strong>
                Nomor Surat Otomatis
              </strong>


              <div class="small mt-1">

                Nomor surat akan dibuat otomatis
                oleh sistem berdasarkan pengaturan
                nomor surat fasilitas kesehatan.

              </div>

            </div>

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

            <span class="text-danger">
              *
            </span>

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
             PERIODE SAKIT
        ==================================================== -->

        <div class="row">


          <!-- TANGGAL MULAI -->

          <div class="col-md-6 mb-3">

            <label
              for="tanggal_mulai"
              class="form-label">

              Tanggal Mulai

              <span class="text-danger">
                *
              </span>

            </label>


            <input
              type="date"
              name="tanggal_mulai"
              id="tanggal_mulai"
              class="form-control"
              required>

          </div>


          <!-- TANGGAL SELESAI -->

          <div class="col-md-6 mb-3">

            <label
              for="tanggal_selesai"
              class="form-label">

              Tanggal Selesai

              <span class="text-danger">
                *
              </span>

            </label>


            <input
              type="date"
              name="tanggal_selesai"
              id="tanggal_selesai"
              class="form-control"
              required>

          </div>

        </div>


        <!-- ===================================================
             LAMA
        ==================================================== -->

        <div class="mb-3">

          <label
            for="lama"
            class="form-label">

            Lama Istirahat

          </label>


          <div class="input-group">

            <input
              type="text"
              id="lama"
              name="lama"
              class="form-control"
              readonly>


            <span class="input-group-text">
              Hari
            </span>

          </div>


          <div class="form-text">
            Lama sakit dihitung otomatis berdasarkan
            tanggal mulai dan tanggal selesai.
          </div>

        </div>


        <!-- ===================================================
             KETERANGAN
        ==================================================== -->

        <div class="mb-3">

          <label
            for="keterangan"
            class="form-label">

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
  function hitungLamaSakit() {

    const mulai =
      $('#tanggal_mulai').val();

    const selesai =
      $('#tanggal_selesai').val();


    if (!mulai || !selesai) {

      $('#lama').val('');

      return;
    }


    const start =
      new Date(mulai + 'T00:00:00');

    const end =
      new Date(selesai + 'T00:00:00');


    if (end < start) {

      $('#lama').val('');

      return;
    }


    const diff =
      Math.round(
        (end - start) /
        (1000 * 60 * 60 * 24)
      ) + 1;


    $('#lama').val(
      diff
    );
  }


  $('#tanggal_mulai, #tanggal_selesai')
    .on(
      'change',
      hitungLamaSakit
    );
</script>
<script>
  $('#programModal').on('shown.bs.modal', function() {


    const $select =
      $('#id_patient_select');


    /*
    |--------------------------------------------------------------------------
    | DESTROY SELECT2 JIKA SUDAH ADA
    |--------------------------------------------------------------------------
    */

    if (
      $select.hasClass(
        'select2-hidden-accessible'
      )
    ) {

      $select.select2('destroy');

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

        url: 'controller/admisi/patientVisitControllerInOut',

        type: 'GET',

        dataType: 'json',

        delay: 300,


        /*
        |--------------------------------------------------------------------------
        | PARAMETER SEARCH
        |--------------------------------------------------------------------------
        */

        data: function(params) {

          return {

            search: params.term || ''

          };

        },


        /*
        |--------------------------------------------------------------------------
        | HASIL SEARCH
        |--------------------------------------------------------------------------
        */

        processResults: function(response) {


          let items =
            response.data ?
            response.data : [];


          return {

            results:

              items.map(function(item) {

                return {

                  /*
                  |----------------------------------------------
                  | SELECT2 VALUE
                  |----------------------------------------------
                  */

                  id: item.id_patient,


                  /*
                  |----------------------------------------------
                  | TAMPILAN
                  |----------------------------------------------
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
                  |----------------------------------------------
                  | DATA TAMBAHAN
                  |----------------------------------------------
                  */

                  id_patient: item.id_patient,

                  id_visit: item.id_visit,

                  visit_ID: item.visit_ID,

                  patient_name: item.patient_name,

                  nomor_rm: item.nomor_rm,

                  patient_nik: item.patient_nik,

                  visit_date: item.visit_date,

                  id_doctor: item.id_doctor,

                  tanggal_pulang: item.tanggal_pulang,



                };

              })

          };

        },


        cache: true

      }

    });


    /*
    |--------------------------------------------------------------------------
    | SAAT PASIEN / VISIT DIPILIH
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
               | TANGGAL MULAI
               |--------------------------------------------------------------------------
               */

        if (data.visit_date) {

          $('#tanggal_mulai').val(
            data.visit_date
          );

        }


        /*
        |--------------------------------------------------------------------------
        | DEFAULT SELESAI = TANGGAL MULAI
        |--------------------------------------------------------------------------
        */

        if (data.visit_date) {

          $('#tanggal_selesai').val(
            data.visit_date
          );

        }


        hitungLamaSakit();


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
    | CLEAR SELECT
    |--------------------------------------------------------------------------
    */

    $select.off(
      'select2:clear'
    );


    $select.on(
      'select2:clear',
      function() {


        $('#id_patient').val('');

        $('#id_visit').val('');

        $('#tanggal_mulai').val('');

        $('#tanggal_pulang').val('');

      }
    );

  });
</script>

<script>
  const apiUrl = 'controller/letter/suratSakitController';


  $(document).ready(function() {


    /* ==========================================================
       DATATABLE
    ========================================================== */

    var table = $('#periodeTable').DataTable({

      processing: true,

      serverSide: false,

      ajax: {

        url: apiUrl,

        type: "GET",

        dataSrc: function(json) {

          if (json.status !== 'success') {

            Swal.fire(
              'Gagal!',
              json.message || 'Gagal mengambil data.',
              'error'
            );

            return [];
          }

          return json.data.map(function(row) {

            return {

              "actions": `

                            <div class="text-end">

                                <div
                                    class="btn-group btn-group-sm"
                                    role="group">

                                      <!-- PRINT -->
                                      <a
                                          class="btn btn-primary"
                                          href="module/letter/print/surat-keterangan-sakit?id=${row.id}"
                                          target="_blank"
                                          title="Cetak Surat">

                                          <i class="fas fa-print"></i>

                                      </a>

                                    <a
                                        class="btn btn-warning edit-btn"
                                        href="javascript:;"
                                        data-id="${row.id}">

                                        <i class="fas fa-edit"></i>

                                    </a>


                                    <a
                                        class="btn btn-danger delete-btn"
                                        href="javascript:;"
                                        data-id="${row.id}">

                                        <i class="fas fa-trash"></i>

                                    </a>

                                </div>

                            </div>

                        `,


              "nomor_surat": row.nomor_surat || '-',


              "tanggal_surat": row.tanggal_surat || '-',


              "patient_name": row.patient_name || '-',


              "nomor_rm": row.id_doctor || '-',


              "keterangan": row.keterangan || '-'

            };

          });

        }

      },


      columns: [

        {
          data: "nomor_surat"
        },

        {
          data: "tanggal_surat"
        },

        {
          data: "patient_name"
        },

        {
          data: "nomor_rm"
        },

        {
          data: "keterangan"
        },

        {
          data: "actions",
          orderable: false,
          searchable: false
        }

      ]

    });


    /* ==========================================================
       CUSTOM SEARCH
    ========================================================== */

    $('#customSearch').on('keyup', function() {

      table
        .search(this.value)
        .draw();

    });


    /* ==========================================================
       SELECT2 PASIEN
    ========================================================== */

    $('#id_patient_select').select2({

      dropdownParent: $('#programModal'),

      width: '100%',

      placeholder: 'Cari nama / NIK / BPJS pasien...',

      allowClear: true,

      minimumInputLength: 1,

      ajax: {

        url: 'controller/letter/searchPatient',

        dataType: 'json',

        delay: 300,

        data: function(params) {

          return {

            search: params.term

          };

        },

        processResults: function(data) {

          return {

            results: data.data.map(function(row) {

              return {

                id: row.id_patient,

                text: row.patient_name +
                  ' - RM ' +
                  (row.nomor_rm || '-') +
                  ' - ' +
                  (row.patient_nik || '-'),

                id_patient: row.id_patient,

                id_visit: row.id_visit,

                visit_ID: row.visit_ID,

                visit_date: row.visit_date

              };

            })

          };

        },

        cache: true

      }

    });


    /* ==========================================================
       SELECT PASIEN
    ========================================================== */

    $('#id_patient_select').on(
      'select2:select',
      function(e) {

        const data = e.params.data;


        $('#id_patient').val(
          data.id_patient
        );


        $('#id_visit').val(
          data.id_visit
        );

      }
    );


    /* ==========================================================
       CLEAR PASIEN
    ========================================================== */

    $('#id_patient_select').on(
      'select2:clear',
      function() {

        $('#id_patient').val('');

        $('#id_visit').val('');

      }
    );


    /* ==========================================================
       TAMBAH SURAT
    ========================================================== */

    $('#btnTambah').on('click', function() {


      $('#programForm')[0].reset();


      $('#id').val('');

      $('#id_patient').val('');

      $('#id_visit').val('');


      $('#id_patient_select')
        .val(null)
        .trigger('change');


      $('#tanggal_surat').val(
        '<?= date('Y-m-d') ?>'
      );


      $('#programModal .modal-title').text(
        'Tambah Surat Keterangan Sakit'
      );


      $('#programModal').modal('show');

    });


    /* ==========================================================
       SUBMIT FORM
    ========================================================== */

    $('#programForm').on(
      'submit',
      function(e) {

        e.preventDefault();


        const form = this;

        const id =
          $('#id').val();


        const idPatient =
          $('#id_patient').val();


        const idVisit =
          $('#id_visit').val();


        /* ----------------------------------------------
           VALIDASI PASIEN
        ---------------------------------------------- */

        if (!idPatient) {

          Swal.fire(
            'Perhatian!',
            'Silakan pilih pasien terlebih dahulu.',
            'warning'
          );

          return;
        }


        /* ----------------------------------------------
           VALIDASI VISIT
        ---------------------------------------------- */

        if (!idVisit) {

          Swal.fire(
            'Perhatian!',
            'Visit pasien tidak ditemukan.',
            'warning'
          );

          return;
        }


        /* ----------------------------------------------
           FORM DATA
        ---------------------------------------------- */

        let formData =
          new URLSearchParams(
            new FormData(form)
          );


        /* ----------------------------------------------
           BUTTON LOADING
        ---------------------------------------------- */

        const submitButton =
          $(form).find(
            'button[type="submit"]'
          );


        const originalText =
          submitButton.html();


        submitButton
          .prop('disabled', true)
          .html(
            '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...'
          );


        /* ----------------------------------------------
           REQUEST
        ---------------------------------------------- */

        fetch(
            apiUrl + (
              id ?
              '?id=' + id :
              ''
            ), {

              method: id ?
                'PUT' : 'POST',

              headers: {

                'Content-Type': 'application/x-www-form-urlencoded'

              },

              body: formData

            }
          )

          .then(function(res) {

            return res.json();

          })

          .then(function(data) {


            submitButton
              .prop('disabled', false)
              .html(originalText);


            if (
              data.status ===
              'success'
            ) {


              Swal.fire(
                'Berhasil!',
                data.message,
                'success'
              );


              $('#programModal')
                .modal('hide');


              table.ajax.reload(
                null,
                false
              );


            } else {


              Swal.fire(
                'Gagal!',
                data.message ||
                'Terjadi kesalahan.',
                'error'
              );

            }

          })

          .catch(function(error) {


            submitButton
              .prop('disabled', false)
              .html(originalText);


            console.error(error);


            Swal.fire(
              'Error!',
              'Terjadi kesalahan pada server.',
              'error'
            );

          });

      }
    );


    /* ==========================================================
       EDIT
    ========================================================== */

    $(document).on(
      'click',
      '.edit-btn',
      function() {

        let id = $(this).data('id');


        fetch(
            apiUrl + '?id=' + id
          )

          .then(function(res) {

            return res.json();

          })

          .then(function(resp) {

            if (
              resp.status === 'success'
            ) {

              let d = resp.data;


              /*
              |--------------------------------------------------------------------------
              | ID
              |--------------------------------------------------------------------------
              */

              $('#id').val(
                d.id
              );


              $('#id_patient').val(
                d.id_patient
              );


              $('#id_visit').val(
                d.id_visit
              );


              /*
              |--------------------------------------------------------------------------
              | TANGGAL SURAT
              |--------------------------------------------------------------------------
              */

              $('#tanggal_surat').val(
                d.tanggal_surat || ''
              );


              /*
              |--------------------------------------------------------------------------
              | TANGGAL MULAI
              |--------------------------------------------------------------------------
              */

              $('#tanggal_mulai').val(
                d.tanggal_mulai || ''
              );


              /*
              |--------------------------------------------------------------------------
              | TANGGAL SELESAI
              |--------------------------------------------------------------------------
              */

              $('#tanggal_selesai').val(
                d.tanggal_selesai || ''
              );


              /*
              |--------------------------------------------------------------------------
              | LAMA
              |--------------------------------------------------------------------------
              */

              $('#lama').val(
                d.lama || ''
              );


              /*
              |--------------------------------------------------------------------------
              | KETERANGAN
              |--------------------------------------------------------------------------
              */

              $('#keterangan').val(
                d.keterangan || ''
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
                  (d.nomor_rm || '-'),

                  d.id_patient,

                  true,

                  true

                );


              $('#id_patient_select')
                .empty()
                .append(option)
                .trigger('change');


              /*
              |--------------------------------------------------------------------------
              | MODAL TITLE
              |--------------------------------------------------------------------------
              */

              $('#programModal .modal-title')
                .text(
                  'Edit Surat Keterangan Sakit'
                );


              /*
              |--------------------------------------------------------------------------
              | TAMPILKAN MODAL
              |--------------------------------------------------------------------------
              */

              $('#programModal')
                .modal('show');


            } else {

              Swal.fire(
                'Gagal!',
                resp.message ||
                'Data surat tidak ditemukan.',
                'error'
              );

            }

          })

          .catch(function(error) {

            console.error(error);

            Swal.fire(
              'Error!',
              'Gagal mengambil data surat.',
              'error'
            );

          });

      }
    );


    /* ==========================================================
       DELETE
    ========================================================== */

    $(document).on(
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

          .then(function(result) {


            if (
              !result.isConfirmed
            ) {

              return;

            }


            fetch(
                apiUrl +
                '?id=' +
                id, {

                  method: 'DELETE'

                }
              )

              .then(function(res) {

                return res.json();

              })

              .then(function(data) {


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
                    data.message,
                    'error'
                  );

                }

              })

              .catch(function(error) {


                console.error(error);


                Swal.fire(
                  'Error!',
                  'Gagal menghapus data.',
                  'error'
                );

              });

          });

      }

    );


  });
</script>

</html>