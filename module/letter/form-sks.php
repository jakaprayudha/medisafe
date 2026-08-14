<?php
$title = 'Surat Keterangan Sehat';
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
                    <h5 class="card-title fw-semibold">Surat Keterangan Sehat</h5>
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
                          <th class="text-dark fw-normal">Keperluan</th>
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
<div class="modal fade" id="programModal" tabindex="-1">

  <div class="modal-dialog">

    <form id="programForm" class="modal-content">

      <div class="modal-header">

        <h5 class="modal-title">
          Tambah Surat Keterangan Sehat
        </h5>

        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="modal">
        </button>

      </div>


      <div class="modal-body">

        <!-- ID SURAT -->

        <input
          type="hidden"
          name="id"
          id="id">


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

        <!-- ======================================
     NOMOR SURAT
======================================= -->

        <div class="mb-3" id="nomorSuratWrapper">

          <label class="form-label">
            Nomor Surat
            <span class="text-danger">*</span>
          </label>

          <input
            type="text"
            name="nomor_surat"
            id="nomor_surat"
            class="form-control"
            placeholder="Masukkan nomor surat">

          <div
            class="form-text text-muted"
            id="nomorSuratHelp">
          </div>

        </div>


        <!-- INFO NOMOR OTOMATIS -->

        <div
          class="alert alert-primary d-none"
          id="nomorSuratAutoInfo">

          <div class="d-flex align-items-center">

            <iconify-icon
              icon="material-symbols:auto-awesome"
              width="22"
              class="me-2">
            </iconify-icon>

            <div>

              <strong>Nomor Surat Otomatis</strong>

              <div class="small mt-1">
                Nomor surat akan dibuat otomatis oleh sistem
                berdasarkan pengaturan penomoran surat.
              </div>

            </div>

          </div>

        </div>


        <!-- PASIEN -->

        <div class="mb-3">

          <label class="form-label">
            Nama Pasien
          </label>

          <select
            name="id_patient_select"
            id="id_patient_select"
            class="form-select"
            required>
          </select>

        </div>


        <!-- TANGGAL SURAT -->

        <div class="mb-3">

          <label class="form-label">
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


        <!-- ======================================
                     PEMERIKSAAN FISIK DARI PASIEN_VISIT
                ======================================= -->

        <div class="row">


          <!-- TEKANAN DARAH -->

          <div class="col-md-6 mb-3">

            <label class="form-label">
              Tekanan Darah
            </label>

            <input
              type="text"
              name="tekanan_darah"
              id="tekanan_darah"
              class="form-control"
              placeholder="120/80 mmHg">

          </div>


          <!-- NADI -->

          <div class="col-md-6 mb-3">

            <label class="form-label">
              Nadi
            </label>

            <input
              type="text"
              name="nadi"
              id="nadi"
              class="form-control"
              placeholder="80 x/menit">

          </div>


          <!-- SUHU -->

          <div class="col-md-6 mb-3">

            <label class="form-label">
              Suhu
            </label>

            <input
              type="text"
              name="suhu"
              id="suhu"
              class="form-control"
              placeholder="36.5 °C">

          </div>


          <!-- RESPIRASI -->

          <div class="col-md-6 mb-3">

            <label class="form-label">
              Respirasi
            </label>

            <input
              type="text"
              name="respirasi"
              id="respirasi"
              class="form-control"
              placeholder="20 x/menit">

          </div>


          <!-- BERAT BADAN -->

          <div class="col-md-6 mb-3">

            <label class="form-label">
              Berat Badan
            </label>

            <input
              type="text"
              name="berat_badan"
              id="berat_badan"
              class="form-control"
              placeholder="65 kg">

          </div>


          <!-- TINGGI BADAN -->

          <div class="col-md-6 mb-3">

            <label class="form-label">
              Tinggi Badan
            </label>

            <input
              type="text"
              name="tinggi_badan"
              id="tinggi_badan"
              class="form-control"
              placeholder="170 cm">

          </div>


          <!-- BMI -->

          <div class="col-md-6 mb-3">

            <label class="form-label">
              BMI
            </label>

            <input
              type="text"
              name="bmi"
              id="bmi"
              class="form-control"
              placeholder="22.5"
              readonly>

          </div>


          <!-- BMI KETERANGAN -->

          <div class="col-md-6 mb-3">

            <label class="form-label">
              Keterangan BMI
            </label>

            <input
              type="text"
              name="bmi_keterangan"
              id="bmi_keterangan"
              class="form-control"
              placeholder="Normal"
              readonly>

          </div>

        </div>


        <!-- ======================================
                     KEPERLUAN
                ======================================= -->

        <div class="mb-3">

          <label class="form-label">
            Keperluan
          </label>

          <select
            name="keperluan"
            id="keperluan"
            class="form-select"
            required>

            <option value="Administrasi">
              Administrasi
            </option>

            <option value="Pekerjaan">
              Pekerjaan
            </option>

            <option value="Pendidikan">
              Pendidikan
            </option>

            <option value="Lainnya">
              Lainnya
            </option>

          </select>

        </div>


        <!-- KETERANGAN -->

        <div class="mb-3">

          <label class="form-label">
            Keterangan
          </label>

          <textarea
            name="keterangan"
            id="keterangan"
            class="form-control"
            rows="3"
            placeholder="Keterangan tambahan..."></textarea>

        </div>

      </div>


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

        url: 'controller/admisi/patientVisitController',

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

                  id_poli: item.id_poli,


                  /*
                  |----------------------------------------------
                  | TANDA VITAL
                  |----------------------------------------------
                  */

                  tekanan_darah: item.tekanan_darah,

                  suhu: item.suhu,

                  nadi: item.nadi,

                  respirasi: item.respirasi,

                  tinggi_badan: item.tinggi_badan,

                  berat_badan: item.berat_badan,

                  bmi: item.bmi,

                  bmi_keterangan: item.bmi_keterangan,

                  saturasi: item.saturasi

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

        /* ----------------------------------------------
   VALIDASI NOMOR SURAT
---------------------------------------------- */

        const nomorSurat =
          $('#nomor_surat').val().trim();


        const modeNomor =
          String(
            settingNomorSurat?.mode_nomor || ''
          ).toUpperCase();


        /*
        |--------------------------------------------------------------------------
        | MANUAL
        |--------------------------------------------------------------------------
        */

        if (
          modeNomor === 'MANUAL' &&
          !nomorSurat
        ) {

          Swal.fire(
            'Perhatian!',
            'Nomor surat wajib diisi karena mode penomoran adalah manual.',
            'warning'
          );

          $('#nomor_surat').focus();

          return;

        }


        /*
        |--------------------------------------------------------------------------
        | TEKANAN DARAH
        |--------------------------------------------------------------------------
        */

        $('#tekanan_darah').val(
          data.tekanan_darah || ''
        );


        /*
        |--------------------------------------------------------------------------
        | NADI
        |--------------------------------------------------------------------------
        */

        $('#nadi').val(
          data.nadi || ''
        );


        /*
        |--------------------------------------------------------------------------
        | SUHU
        |--------------------------------------------------------------------------
        */

        $('#suhu').val(
          data.suhu || ''
        );


        /*
        |--------------------------------------------------------------------------
        | RESPIRASI
        |--------------------------------------------------------------------------
        */

        $('#respirasi').val(
          data.respirasi || ''
        );


        /*
        |--------------------------------------------------------------------------
        | BERAT BADAN
        |--------------------------------------------------------------------------
        */

        $('#berat_badan').val(
          data.berat_badan || ''
        );


        /*
        |--------------------------------------------------------------------------
        | TINGGI BADAN
        |--------------------------------------------------------------------------
        */

        $('#tinggi_badan').val(
          data.tinggi_badan || ''
        );


        /*
        |--------------------------------------------------------------------------
        | BMI
        |--------------------------------------------------------------------------
        */

        $('#bmi').val(
          data.bmi || ''
        );


        /*
        |--------------------------------------------------------------------------
        | BMI KETERANGAN
        |--------------------------------------------------------------------------
        */

        $('#bmi_keterangan').val(
          data.bmi_keterangan || ''
        );


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

        $('#tekanan_darah').val('');

        $('#nadi').val('');

        $('#suhu').val('');

        $('#respirasi').val('');

        $('#berat_badan').val('');

        $('#tinggi_badan').val('');

        $('#bmi').val('');

        $('#bmi_keterangan').val('');

      }
    );

  });
</script>

<script>
  const apiUrl =
    'controller/letter/suratSehatController';


  /*
  |--------------------------------------------------------------------------
  | SETTING NOMOR SURAT
  |--------------------------------------------------------------------------
  */

  const settingSuratApi =
    'controller/letter/settingSuratController';


  const settingSuratPage =
    'module/letter/setting-surat';


  const jenisSurat =
    'sehat';


  let settingNomorSurat =
    null;


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
        | RESPONSE ERROR
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


        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */

        settingNomorSurat =
          response.data || null;


        return settingNomorSurat;

      });

  }

  /*
|--------------------------------------------------------------------------
| APPLY SETTING NOMOR SURAT
|--------------------------------------------------------------------------
*/

  function applyNomorSuratSetting(
    setting,
    nomorSurat
  ) {

    const wrapper =
      $('#nomorSuratWrapper');


    const input =
      $('#nomor_surat');


    const help =
      $('#nomorSuratHelp');


    const autoInfo =
      $('#nomorSuratAutoInfo');


    /*
    |--------------------------------------------------------------------------
    | RESET
    |--------------------------------------------------------------------------
    */

    wrapper.addClass(
      'd-none'
    );


    autoInfo.addClass(
      'd-none'
    );


    input
      .prop(
        'required',
        false
      )
      .prop(
        'readonly',
        false
      )
      .val(
        nomorSurat || ''
      );


    help.html('');


    /*
    |--------------------------------------------------------------------------
    | SETTING TIDAK ADA
    |--------------------------------------------------------------------------
    */

    if (
      !setting ||
      !setting.id
    ) {

      return;

    }


    /*
    |--------------------------------------------------------------------------
    | MODE
    |--------------------------------------------------------------------------
    */

    const mode =
      String(
        setting.mode_nomor || ''
      ).toUpperCase();


    /*
    |--------------------------------------------------------------------------
    | MANUAL
    |--------------------------------------------------------------------------
    */

    if (
      mode === 'MANUAL'
    ) {

      wrapper.removeClass(
        'd-none'
      );


      input
        .prop(
          'required',
          true
        )
        .prop(
          'readonly',
          false
        )
        .val(
          nomorSurat || ''
        );


      help.html(
        `
      <span class="text-primary">
        <i class="fas fa-edit me-1"></i>
        Nomor surat diisi secara manual.
      </span>
      `
      );


      return;

    }


    /*
    |--------------------------------------------------------------------------
    | AUTO
    |--------------------------------------------------------------------------
    */

    if (
      mode === 'AUTO'
    ) {

      wrapper.addClass(
        'd-none'
      );


      input
        .prop(
          'required',
          false
        )
        .prop(
          'readonly',
          true
        )
        .val('');


      autoInfo.removeClass(
        'd-none'
      );


      return;

    }


    /*
    |--------------------------------------------------------------------------
    | MODE INVALID
    |--------------------------------------------------------------------------
    */

    throw new Error(
      'Mode nomor surat tidak valid.'
    );

  }


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
                                          href="module/letter/print/surat-keterangan-sehat?id=${row.id}"
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


              "keperluan": row.keperluan || '-',


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
          data: "keperluan"
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
    /* ==========================================================
       TAMBAH SURAT
    ========================================================== */

    $('#btnTambah').on(
      'click',
      function() {


        /*
        |--------------------------------------------------------------------------
        | CEK SETTING TERLEBIH DAHULU
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

              alertSettingBelumAda();

              return;

            }


            /*
            |--------------------------------------------------------------------------
            | RESET FORM
            |--------------------------------------------------------------------------
            */

            $('#programForm')[0].reset();


            $('#id').val('');

            $('#id_patient').val('');

            $('#id_visit').val('');

            $('#nomor_surat').val('');


            $('#id_patient_select')
              .val(null)
              .trigger('change');


            $('#tanggal_surat').val(
              '<?= date('Y-m-d') ?>'
            );


            /*
            |--------------------------------------------------------------------------
            | APPLY SETTING NOMOR SURAT
            |--------------------------------------------------------------------------
            */

            applyNomorSuratSetting(
              setting
            );


            /*
            |--------------------------------------------------------------------------
            | TITLE
            |--------------------------------------------------------------------------
            */

            $('#programModal .modal-title')
              .text(
                'Tambah Surat Keterangan Sehat'
              );


            /*
            |--------------------------------------------------------------------------
            | SHOW MODAL
            |--------------------------------------------------------------------------
            */

            $('#programModal')
              .modal('show');

          })

          .catch(function(error) {


            Swal.close();


            console.error(
              'CHECK SETTING ERROR:',
              error
            );


            Swal.fire({

              icon: 'error',

              title: 'Gagal Memeriksa Setting',

              text: error.message ||
                'Pengaturan nomor surat gagal diperiksa.'

            });

          });

      }
    );


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

    /* ==========================================================
   EDIT
========================================================== */

    $(document).on(
      'click',
      '.edit-btn',
      function() {


        let id =
          $(this).data('id');


        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA SURAT
        |--------------------------------------------------------------------------
        */

        fetch(
            apiUrl +
            '?id=' +
            id
          )

          .then(function(res) {

            return res.json();

          })

          .then(function(resp) {


            if (
              resp.status !==
              'success'
            ) {

              Swal.fire(
                'Gagal!',
                resp.message,
                'error'
              );

              return;

            }


            let d =
              resp.data;


            /*
            |--------------------------------------------------------------------------
            | CHECK SETTING NOMOR SURAT
            |--------------------------------------------------------------------------
            */

            return checkSettingNomorSurat()

              .then(function(setting) {


                /*
                |--------------------------------------------------------------------------
                | BELUM ADA SETTING
                |--------------------------------------------------------------------------
                */

                if (
                  !setting ||
                  !setting.id
                ) {

                  alertSettingBelumAda();

                  return;

                }


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
                | NOMOR SURAT
                |--------------------------------------------------------------------------
                */

                applyNomorSuratSetting(
                  setting,
                  d.nomor_surat || ''
                );


                /*
                |--------------------------------------------------------------------------
                | TANGGAL
                |--------------------------------------------------------------------------
                */

                $('#tanggal_surat').val(
                  d.tanggal_surat
                );


                /*
                |--------------------------------------------------------------------------
                | PEMERIKSAAN FISIK
                |--------------------------------------------------------------------------
                */

                $('#tekanan_darah').val(
                  d.tekanan_darah || ''
                );


                $('#nadi').val(
                  d.nadi || ''
                );


                $('#suhu').val(
                  d.suhu || ''
                );


                $('#respirasi').val(
                  d.respirasi || ''
                );


                $('#berat_badan').val(
                  d.berat_badan || ''
                );


                $('#tinggi_badan').val(
                  d.tinggi_badan || ''
                );


                $('#bmi').val(
                  d.bmi || ''
                );


                $('#bmi_keterangan').val(
                  d.bmi_keterangan || ''
                );


                /*
                |--------------------------------------------------------------------------
                | KEPERLUAN
                |--------------------------------------------------------------------------
                */

                $('#keperluan').val(
                  d.keperluan || ''
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
                  .append(option)
                  .trigger('change');


                /*
                |--------------------------------------------------------------------------
                | MODAL TITLE
                |--------------------------------------------------------------------------
                */

                $('#programModal .modal-title')
                  .text(
                    'Edit Surat Keterangan Sehat'
                  );


                /*
                |--------------------------------------------------------------------------
                | SHOW MODAL
                |--------------------------------------------------------------------------
                */

                $('#programModal')
                  .modal('show');

              });

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

    /*
|--------------------------------------------------------------------------
| SET UI NOMOR SURAT
|--------------------------------------------------------------------------
*/

    function applyNomorSuratSetting(
      setting,
      nomorSurat = ''
    ) {

      const wrapper =
        $('#nomorSuratWrapper');

      const input =
        $('#nomor_surat');

      const help =
        $('#nomorSuratHelp');

      const autoInfo =
        $('#nomorSuratAutoInfo');


      /*
      |--------------------------------------------------------------------------
      | RESET
      |--------------------------------------------------------------------------
      */

      wrapper.addClass('d-none');

      autoInfo.addClass('d-none');

      input
        .prop('required', false)
        .prop('readonly', false)
        .val(nomorSurat || '');

      help.html('');


      /*
      |--------------------------------------------------------------------------
      | BELUM ADA SETTING
      |--------------------------------------------------------------------------
      */

      if (
        !setting ||
        !setting.id
      ) {

        return;

      }


      const mode =
        String(
          setting.mode_nomor || ''
        ).toUpperCase();


      /*
      |--------------------------------------------------------------------------
      | MANUAL
      |--------------------------------------------------------------------------
      */

      if (
        mode === 'MANUAL'
      ) {

        wrapper.removeClass('d-none');

        input
          .prop('required', true)
          .prop('readonly', false)
          .val(nomorSurat || '');


        help.html(
          `
      <span class="text-primary">
        <i class="fas fa-edit me-1"></i>
        Nomor surat diisi secara manual.
      </span>
      `
        );


        return;

      }


      /*
      |--------------------------------------------------------------------------
      | AUTO
      |--------------------------------------------------------------------------
      */

      if (
        mode === 'AUTO'
      ) {

        wrapper.addClass('d-none');

        input
          .prop('required', false)
          .prop('readonly', true)
          .val('');


        autoInfo.removeClass('d-none');


        return;

      }


      /*
      |--------------------------------------------------------------------------
      | MODE INVALID
      |--------------------------------------------------------------------------
      */

      wrapper.addClass('d-none');

    }

    /*
|--------------------------------------------------------------------------
| ALERT SETTING BELUM ADA
|--------------------------------------------------------------------------
*/

    function alertSettingBelumAda() {

      return Swal.fire({

          icon: 'warning',

          title: 'Nomor Surat Belum Diatur',

          html: `
      <div class="text-muted">

        Pengaturan nomor surat untuk
        <strong>Surat Keterangan Sehat</strong>
        belum tersedia.

        <br><br>

        Silakan atur terlebih dahulu
        mode penomoran surat
        <strong>Manual</strong> atau
        <strong>Otomatis</strong>.

      </div>
      `,

          showCancelButton: true,

          confirmButtonText: `
      <i class="fas fa-cog me-1"></i>
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


  });
</script>

</html>