<?php

$title = 'Log Update';

require '../../controller/view.php';


/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
*/

function e($value)
{
  return htmlspecialchars(
    $value ?? '',
    ENT_QUOTES,
    'UTF-8'
  );
}

?>

<!doctype html>

<html lang="id">

<head>

  <base href="../../">

  <?php
  require '../../assets/template/head.php';
  ?>

  <style>
    /* =========================================================
           PAGE HEADER
        ========================================================= */

    .update-page-title {
      font-size: 24px;
      font-weight: 700;
      color: #111827;
    }

    .update-page-subtitle {
      color: #64748b;
      font-size: 14px;
    }


    /* =========================================================
           CARD
        ========================================================= */

    .update-card {
      border: 0;
      border-radius: 16px;
      box-shadow:
        0 5px 20px rgba(15, 23, 42, .06);
    }


    /* =========================================================
           TYPE BADGE
        ========================================================= */

    .type-badge {
      display: inline-flex;
      align-items: center;

      padding: 5px 10px;

      border-radius: 7px;

      font-size: 10px;
      font-weight: 800;

      text-transform: uppercase;
    }

    .type-feature {
      background: #dcfce7;
      color: #166534;
    }

    .type-improvement {
      background: #fef3c7;
      color: #92400e;
    }

    .type-bug,
    .type-fix {
      background: #fee2e2;
      color: #991b1b;
    }

    .type-security {
      background: #ede9fe;
      color: #5b21b6;
    }

    .type-maintenance {
      background: #e0f2fe;
      color: #075985;
    }

    .type-update {
      background: #e0e7ff;
      color: #3730a3;
    }


    /* =========================================================
           GUIDE BADGE
        ========================================================= */

    .guide-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;

      padding: 5px 9px;

      border-radius: 7px;

      background: #f1f5f9;
      color: #475569;

      font-size: 10px;
      font-weight: 700;
    }


    /* =========================================================
           MODAL
        ========================================================= */

    .update-modal .modal-content {
      border: 0;
      border-radius: 18px;
      box-shadow:
        0 25px 70px rgba(15, 23, 42, .20);
    }

    .update-modal .modal-header {
      padding: 22px 24px;
    }

    .update-modal .modal-body {
      padding: 24px;
    }


    /* =========================================================
           FORM
        ========================================================= */

    .form-label {
      font-weight: 600;
      color: #334155;
    }

    .form-control,
    .form-select {
      border-radius: 9px;
      border-color: #e2e8f0;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #6366f1;
      box-shadow:
        0 0 0 3px rgba(99, 102, 241, .10);
    }


    /* =========================================================
           DESCRIPTION
        ========================================================= */

    .description-cell {
      max-width: 350px;

      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;

      color: #475569;
    }


    /* =========================================================
           PREVIEW GUIDE
        ========================================================= */

    .guide-preview {
      display: none;

      padding: 14px;

      margin-top: 10px;

      border-radius: 10px;

      background: #f8fafc;
      border: 1px solid #e2e8f0;
    }

    .guide-preview.show {
      display: block;
    }


    /* =========================================================
           RESPONSIVE
        ========================================================= */

    @media (max-width: 768px) {

      .update-page-title {
        font-size: 21px;
      }

    }
  </style>

</head>


<body>


  <div
    class="page-wrapper"
    id="main-wrapper"
    data-layout="vertical"
    data-navbarbg="skin6"
    data-sidebartype="full"
    data-sidebar-position="fixed"
    data-header-position="fixed">


    <!-- SIDEBAR -->

    <?php
    require '../admin/sidebar.php';
    ?>


    <!-- MAIN -->

    <div class="body-wrapper">


      <!-- NAVBAR -->

      <?php
      require '../admin/navbar-master.php';
      ?>


      <div class="body-wrapper-inner">

        <div class="container-fluid">


          <!-- =====================================================
                     HEADER
                ====================================================== -->

          <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

              <div class="update-page-title">

                <i class="ti ti-history text-primary me-2"></i>

                Log Update

              </div>

              <div class="update-page-subtitle">

                Kelola riwayat pembaruan sistem Medisafe.

              </div>

            </div>


            <button
              type="button"
              class="btn btn-primary"
              id="btnTambah">

              <i class="ti ti-plus me-1"></i>

              Tambah Update

            </button>

          </div>



          <!-- =====================================================
                     TABLE
                ====================================================== -->

          <div class="card update-card">

            <div class="card-body p-4">

              <div class="table-responsive">

                <table
                  class="table align-middle"
                  id="updateTable"
                  width="100%">

                  <thead>

                    <tr>

                      <th width="50">
                        ID
                      </th>

                      <th>
                        Update
                      </th>

                      <th>
                        Tipe
                      </th>

                      <th>
                        Versi
                      </th>

                      <th>
                        Panduan
                      </th>

                      <th>
                        Tanggal
                      </th>

                      <th
                        class="text-center"
                        width="120">
                        Action
                      </th>

                    </tr>

                  </thead>

                  <tbody>

                  </tbody>

                </table>

              </div>

            </div>

          </div>


        </div>

      </div>

    </div>

  </div>



  <!-- =============================================================
     MODAL FORM
============================================================= -->

  <div
    class="modal fade update-modal"
    id="updateModal"
    tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

      <form
        id="updateForm"
        class="modal-content"
        enctype="multipart/form-data">

        <div class="modal-header">

          <div>

            <h5
              class="modal-title fw-bold"
              id="updateModalTitle">
              Tambah Log Update
            </h5>

            <small class="text-muted">

              Tambahkan informasi pembaruan sistem.

            </small>

          </div>


          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"></button>

        </div>


        <div class="modal-body">

          <input
            type="hidden"
            name="id"
            id="id">


          <!-- TITLE -->

          <div class="mb-3">

            <label class="form-label">

              Judul Update
              <span class="text-danger">*</span>

            </label>

            <input
              type="text"
              name="title"
              id="title"
              class="form-control"
              placeholder="Contoh: Pembaruan Modul Farmasi"
              required>

          </div>


          <div class="row">


            <!-- TYPE -->

            <div class="col-md-6 mb-3">

              <label class="form-label">

                Tipe Update

              </label>

              <select
                name="type"
                id="type"
                class="form-select">

                <option value="update">
                  Update
                </option>

                <option value="feature">
                  Feature
                </option>

                <option value="improvement">
                  Improvement
                </option>

                <option value="bug">
                  Bug
                </option>

                <option value="fix">
                  Fix
                </option>

                <option value="security">
                  Security
                </option>

                <option value="maintenance">
                  Maintenance
                </option>

              </select>

            </div>


            <!-- VERSION -->

            <div class="col-md-6 mb-3">

              <label class="form-label">

                Versi

              </label>

              <input
                type="text"
                name="version"
                id="version"
                class="form-control"
                placeholder="Contoh: 1.2.0">

            </div>

          </div>


          <!-- DESCRIPTION -->

          <div class="mb-3">

            <label class="form-label">

              Deskripsi

            </label>

            <textarea
              name="description"
              id="description"
              class="form-control"
              rows="5"
              placeholder="Jelaskan perubahan atau fitur yang ditambahkan..."></textarea>

          </div>


          <!-- GUIDE -->

          <div class="border rounded-3 p-3">

            <div class="fw-semibold mb-3">

              <i class="ti ti-book-2 text-primary me-1"></i>

              Panduan Update

            </div>


            <!-- GUIDE TYPE -->

            <div class="mb-3">

              <label class="form-label">

                Jenis Panduan

              </label>

              <select
                name="guide_type"
                id="guide_type"
                class="form-select">

                <option value="none">
                  Tidak Ada
                </option>

                <option value="url">
                  Link URL
                </option>

                <option value="video">
                  Video
                </option>

                <option value="pdf">
                  PDF
                </option>

              </select>

            </div>


            <!-- URL -->

            <div
              id="guideUrlWrapper"
              style="display:none;">

              <label class="form-label">

                URL Panduan

              </label>

              <input
                type="url"
                name="guide_url"
                id="guide_url"
                class="form-control"
                placeholder="https://...">

              <small class="text-muted">

                Bisa menggunakan URL dokumentasi,
                website, YouTube, Vimeo, dan sebagainya.

              </small>

            </div>


            <!-- PDF -->

            <div
              id="guidePdfWrapper"
              style="display:none;">

              <label class="form-label">

                Upload PDF

              </label>

              <input
                type="file"
                name="guide_file"
                id="guide_file"
                class="form-control"
                accept="application/pdf,.pdf">

              <small class="text-muted">

                Maksimal 20 MB. Format PDF.

              </small>


              <div
                id="currentPdf"
                class="mt-2"
                style="display:none;">

              </div>

            </div>


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
            class="btn btn-primary"
            id="btnSimpan">

            <i class="ti ti-device-floppy me-1"></i>

            Simpan

          </button>

        </div>

      </form>

    </div>

  </div>



  <?php
  require '../admin/library.php';
  ?>



  <script>
    $(document).ready(function() {


      /* =========================================================
         API
      ========================================================= */

      const apiUrl =
        'controller/system/logUpdateController.php';



      /* =========================================================
         ESCAPE
      ========================================================= */

      function escapeHtml(text) {

        return $('<div>')
          .text(text ?? '')
          .html();

      }



      /* =========================================================
         TYPE CLASS
      ========================================================= */

      function typeClass(type) {

        type =
          (type || 'update')
          .toLowerCase();

        return 'type-' + type;

      }



      /* =========================================================
         GUIDE HTML
      ========================================================= */

      function guideHtml(row) {

        const type =
          row.guide_type || 'none';


        if (type === 'pdf') {

          if (!row.guide_file) {

            return `
                    <span class="text-muted">
                        -
                    </span>
                `;

          }


          return `

                <a
                    href="${escapeHtml(row.guide_file)}"
                    target="_blank"
                    class="guide-badge text-decoration-none"
                >

                    <i class="ti ti-file-type-pdf"></i>

                    PDF

                </a>

            `;

        }


        if (
          type === 'url' ||
          type === 'video'
        ) {

          if (!row.guide_url) {

            return `
                    <span class="text-muted">
                        -
                    </span>
                `;

          }


          const icon =
            type === 'video' ?
            'ti ti-player-play' :
            'ti ti-link';


          const label =
            type === 'video' ?
            'Video' :
            'URL';


          return `

                <a
                    href="${escapeHtml(row.guide_url)}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="guide-badge text-decoration-none"
                >

                    <i class="${icon}"></i>

                    ${label}

                </a>

            `;

        }


        return `

            <span class="text-muted">

                -

            </span>

        `;

      }



      /* =========================================================
         DATATABLE
      ========================================================= */

      const table =
        $('#updateTable').DataTable({

          processing: true,

          serverSide: false,

          responsive: true,

          ajax: {

            url: apiUrl,

            type: 'GET',

            dataSrc: function(json) {

              if (
                !json ||
                json.status !== 'success'
              ) {

                Swal.fire(
                  'Gagal!',
                  json?.message ||
                  'Gagal mengambil data.',
                  'error'
                );

                return [];

              }

              return json.data || [];

            }

          },

          columns: [

            {
              data: 'id_update'
            },


            {
              data: null,

              render: function(data, type, row) {

                const title =
                  escapeHtml(
                    row.title ||
                    'Update Sistem'
                  );


                const description =
                  escapeHtml(
                    row.description ||
                    ''
                  );


                return `

                            <div>

                                <div class="fw-semibold text-dark">

                                    ${title}

                                </div>

                                <div class="description-cell mt-1">

                                    ${description}

                                </div>

                            </div>

                        `;

              }

            },


            {
              data: 'type',

              render: function(data) {

                return `

                            <span class="type-badge ${typeClass(data)}">

                                ${escapeHtml(
                                    data || 'update'
                                )}

                            </span>

                        `;

              }

            },


            {
              data: 'version',

              render: function(data) {

                if (!data) {

                  return '-';

                }

                return `

                            <span class="badge bg-light text-dark">

                                v${escapeHtml(data)}

                            </span>

                        `;

              }

            },


            {
              data: null,

              render: function(data, type, row) {

                return guideHtml(row);

              }

            },


            {
              data: 'created_at',

              render: function(data) {

                return data || '-';

              }

            },


            {
              data: null,

              orderable: false,

              searchable: false,

              className: 'text-center',

              render: function(data, type, row) {

                return `

                            <div class="btn-group btn-group-sm">

                                <button
                                    type="button"
                                    class="btn btn-primary edit-btn"
                                    data-id="${row.id_update}"
                                    title="Edit"
                                >

                                    <i class="ti ti-edit"></i>

                                </button>


                                <button
                                    type="button"
                                    class="btn btn-danger delete-btn"
                                    data-id="${row.id_update}"
                                    title="Hapus"
                                >

                                    <i class="ti ti-trash"></i>

                                </button>

                            </div>

                        `;

              }

            }

          ],

          order: [
            [5, 'desc']
          ],

          language: {

            search: 'Cari:',

            lengthMenu: '_MENU_ data',

            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',

            emptyTable: 'Belum ada log update.',

            zeroRecords: 'Data tidak ditemukan.'

          }

        });



      /* =========================================================
         GUIDE TYPE CHANGE
      ========================================================= */

      function updateGuideFields() {

        const type =
          $('#guide_type').val();


        $('#guideUrlWrapper')
          .hide();


        $('#guidePdfWrapper')
          .hide();


        if (
          type === 'url' ||
          type === 'video'
        ) {

          $('#guideUrlWrapper')
            .show();

        }


        if (type === 'pdf') {

          $('#guidePdfWrapper')
            .show();

        }

      }


      $('#guide_type').on(
        'change',
        updateGuideFields
      );



      /* =========================================================
         TAMBAH
      ========================================================= */

      $('#btnTambah').on(
        'click',
        function() {

          $('#updateForm')[0].reset();

          $('#id').val('');

          $('#currentPdf')
            .hide()
            .html('');

          $('#updateModalTitle')
            .text('Tambah Log Update');

          updateGuideFields();

          $('#updateModal')
            .modal('show');

        }
      );



      /* =========================================================
         EDIT
      ========================================================= */

      $(document).on(
        'click',
        '.edit-btn',
        function() {

          const id =
            $(this).data('id');


          $.ajax({

            url: apiUrl +
              '?id=' +
              encodeURIComponent(id),

            type: 'GET',

            dataType: 'json',

            success: function(res) {

              if (
                !res ||
                res.status !== 'success'
              ) {

                Swal.fire(
                  'Gagal!',
                  res?.message ||
                  'Data tidak ditemukan.',
                  'error'
                );

                return;

              }


              const d =
                res.data;


              $('#id')
                .val(d.id_update);


              $('#title')
                .val(d.title);


              $('#description')
                .val(d.description);


              $('#type')
                .val(d.type || 'update');


              $('#version')
                .val(d.version || '');


              $('#guide_type')
                .val(
                  d.guide_type ||
                  'none'
                );


              $('#guide_url')
                .val(
                  d.guide_url ||
                  ''
                );


              /*
              |--------------------------------------------------------------------------
              | PDF EXISTING
              |--------------------------------------------------------------------------
              */

              if (
                d.guide_file
              ) {

                $('#currentPdf')
                  .html(`

                                <div class="alert alert-light border mb-0">

                                    <i class="ti ti-file-type-pdf text-danger me-1"></i>

                                    PDF saat ini:

                                    <a
                                        href="${escapeHtml(d.guide_file)}"
                                        target="_blank"
                                        class="fw-semibold"
                                    >
                                        Lihat PDF
                                    </a>

                                </div>

                            `)
                  .show();

              } else {

                $('#currentPdf')
                  .hide()
                  .html('');

              }


              updateGuideFields();


              $('#updateModalTitle')
                .text(
                  'Edit Log Update'
                );


              $('#updateModal')
                .modal('show');

            },

            error: function(xhr) {

              console.error(
                xhr.responseText
              );

              Swal.fire(
                'Error!',
                'Gagal mengambil data.',
                'error'
              );

            }

          });

        }
      );



      /* =========================================================
         SUBMIT
      ========================================================= */

      $('#updateForm').on(
        'submit',
        function(e) {

          e.preventDefault();


          const id =
            $('#id').val();


          const form =
            this;


          /*
          |--------------------------------------------------------------------------
          | CREATE
          |--------------------------------------------------------------------------
          */

          if (!id) {

            const formData =
              new FormData(form);


            $('#btnSimpan')
              .prop('disabled', true)
              .html(`
                        <span
                            class="spinner-border spinner-border-sm me-1"
                        ></span>
                        Menyimpan...
                    `);


            $.ajax({

              url: apiUrl,

              type: 'POST',

              data: formData,

              processData: false,

              contentType: false,

              dataType: 'json',

              success: function(res) {

                if (
                  res.status ===
                  'success'
                ) {

                  Swal.fire(
                    'Berhasil!',
                    res.message,
                    'success'
                  );


                  $('#updateModal')
                    .modal('hide');


                  table.ajax.reload(
                    null,
                    false
                  );

                } else {

                  Swal.fire(
                    'Gagal!',
                    res.message,
                    'error'
                  );

                }

              },

              error: function(xhr) {

                console.error(
                  xhr.responseText
                );

                Swal.fire(
                  'Error!',
                  'Terjadi kesalahan server.',
                  'error'
                );

              },

              complete: function() {

                $('#btnSimpan')
                  .prop(
                    'disabled',
                    false
                  )
                  .html(`
                                <i class="ti ti-device-floppy me-1"></i>
                                Simpan
                            `);

              }

            });


            return;

          }


          /*
          |--------------------------------------------------------------------------
          | UPDATE
          |--------------------------------------------------------------------------
          |
          | PUT tidak bisa menerima multipart/form-data
          | dengan cara yang sama seperti POST.
          |
          | Karena itu update data text menggunakan
          | URLSearchParams.
          |
          */

          const params =
            new URLSearchParams();


          params.append(
            'id',
            id
          );

          params.append(
            'title',
            $('#title').val()
          );

          params.append(
            'description',
            $('#description').val()
          );

          params.append(
            'type',
            $('#type').val()
          );

          params.append(
            'version',
            $('#version').val()
          );

          params.append(
            'guide_type',
            $('#guide_type').val()
          );

          params.append(
            'guide_url',
            $('#guide_url').val()
          );


          $('#btnSimpan')
            .prop('disabled', true)
            .html(`
                    <span
                        class="spinner-border spinner-border-sm me-1"
                    ></span>
                    Menyimpan...
                `);


          $.ajax({

            url: apiUrl,

            type: 'PUT',

            data: params.toString(),

            contentType: 'application/x-www-form-urlencoded',

            dataType: 'json',

            success: function(res) {

              if (
                res.status ===
                'success'
              ) {

                Swal.fire(
                  'Berhasil!',
                  res.message,
                  'success'
                );


                $('#updateModal')
                  .modal('hide');


                table.ajax.reload(
                  null,
                  false
                );

              } else {

                Swal.fire(
                  'Gagal!',
                  res.message,
                  'error'
                );

              }

            },

            error: function(xhr) {

              console.error(
                xhr.responseText
              );

              Swal.fire(
                'Error!',
                'Terjadi kesalahan server.',
                'error'
              );

            },

            complete: function() {

              $('#btnSimpan')
                .prop(
                  'disabled',
                  false
                )
                .html(`
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan
                        `);

            }

          });

        }
      );



      /* =========================================================
         DELETE
      ========================================================= */

      $(document).on(
        'click',
        '.delete-btn',
        function() {

          const id =
            $(this).data('id');


          Swal.fire({

            title: 'Hapus Log Update?',

            text: 'Data dan file panduan PDF jika ada akan dihapus.',

            icon: 'warning',

            showCancelButton: true,

            confirmButtonText: 'Ya, Hapus',

            cancelButtonText: 'Batal',

            confirmButtonColor: '#dc3545'

          }).then(function(result) {

            if (!result.isConfirmed) {

              return;

            }


            $.ajax({

              url: apiUrl,

              type: 'DELETE',

              data: {
                id: id
              },

              dataType: 'json',

              success: function(res) {

                if (
                  res.status ===
                  'success'
                ) {

                  Swal.fire(
                    'Berhasil!',
                    res.message,
                    'success'
                  );


                  table.ajax.reload(
                    null,
                    false
                  );

                } else {

                  Swal.fire(
                    'Gagal!',
                    res.message,
                    'error'
                  );

                }

              },

              error: function(xhr) {

                console.error(
                  xhr.responseText
                );

                Swal.fire(
                  'Error!',
                  'Gagal menghapus data.',
                  'error'
                );

              }

            });

          });

        }
      );


    });
  </script>


</body>

</html>