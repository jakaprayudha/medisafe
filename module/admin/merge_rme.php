<?php
$title = 'Gabung RME';

require '../../controller/view.php';
require '../../database/connect.php';
?>

<!doctype html>
<html lang="id">

<head>

  <base href="../../">

  <?php
  require '../../assets/template/head.php';
  ?>

  <style>
    .patient-search-result {
      cursor: pointer;
      transition: all .2s ease;
    }

    .patient-search-result:hover {
      background: #f8fafc;
    }

    .patient-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 18px;
    }

    .patient-info-item {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }

    .patient-info-item .label {
      font-size: 11px;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: .4px;
    }

    .patient-info-item .value {
      font-size: 15px;
      font-weight: 600;
      color: #0f172a;
    }

    .source-badge {
      background: #0d6efd;
      color: #fff;
      font-size: 11px;
      padding: 4px 8px;
      border-radius: 20px;
    }

    .match-badge {
      background: #198754;
      color: #fff;
      font-size: 11px;
      padding: 4px 8px;
      border-radius: 20px;
    }

    .empty-state {
      padding: 50px 20px;
      text-align: center;
      color: #94a3b8;
    }

    .empty-state i {
      font-size: 48px;
      margin-bottom: 10px;
    }

    .loading-state {
      padding: 40px;
      text-align: center;
    }

    .search-patient-name {
      font-weight: 600;
      color: #0f172a;
    }

    .search-patient-meta {
      font-size: 12px;
      color: #64748b;
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

    <?php
    require 'sidebar.php';
    ?>

    <div class="body-wrapper">

      <?php
      require 'navbar.php';
      ?>

      <div class="body-wrapper-inner">

        <div class="container-fluid">

          <!-- =====================================================
               HEADER
          ====================================================== -->

          <div class="row">

            <div class="col-12">

              <div class="card border-0 shadow-sm">

                <div class="card-body">

                  <h4 class="fw-bold mb-1">
                    <i class="ti ti-file-symlink text-primary me-2"></i>
                    Gabung RME
                  </h4>

                  <p class="text-muted mb-0">
                    Cari pasien berdasarkan Nomor RM, Nama, NIK atau Nomor Kartu
                    untuk menentukan rekam medis sumber.
                  </p>

                </div>

              </div>

            </div>

          </div>


          <!-- =====================================================
               SEARCH PASIEN
          ====================================================== -->

          <div class="row mt-3">

            <div class="col-12">

              <div class="card">

                <div class="card-body">

                  <h5 class="fw-semibold mb-3">

                    <i class="ti ti-search text-primary me-2"></i>

                    Cari Pasien Sumber

                  </h5>


                  <div class="row g-2">

                    <div class="col-md-9">

                      <label class="form-label fw-semibold">
                        Nomor RM / Nama / NIK / Nomor Kartu
                      </label>

                      <input
                        type="text"
                        id="keywordPatient"
                        class="form-control"
                        placeholder="Ketik nomor RM, nama pasien, NIK atau nomor kartu...">

                    </div>


                    <div class="col-md-3 d-flex align-items-end">

                      <button
                        type="button"
                        id="btnCariPasien"
                        class="btn btn-primary w-100">

                        <i class="ti ti-search me-1"></i>

                        Cari Pasien

                      </button>

                    </div>

                  </div>


                  <!-- =================================================
                       HASIL PENCARIAN PASIEN
                  ================================================== -->

                  <div
                    id="searchResultWrapper"
                    class="mt-4"
                    style="display:none;">

                    <div class="d-flex justify-content-between align-items-center mb-2">

                      <h6 class="fw-semibold mb-0">
                        Hasil Pencarian
                      </h6>

                      <span
                        id="totalPatientResult"
                        class="badge bg-secondary">
                        0 Data
                      </span>

                    </div>


                    <div class="table-responsive">

                      <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">

                          <tr>

                            <th width="50">
                              #
                            </th>

                            <th>
                              Nomor RM
                            </th>

                            <th>
                              Nama Pasien
                            </th>

                            <th>
                              NIK
                            </th>

                            <th>
                              No. Kartu
                            </th>

                            <th width="100">
                              Aksi
                            </th>

                          </tr>

                        </thead>

                        <tbody id="patientSearchBody">

                          <tr>

                            <td
                              colspan="6"
                              class="empty-state">

                              <i class="ti ti-user-search d-block"></i>

                              Silakan lakukan pencarian pasien.

                            </td>

                          </tr>

                        </tbody>

                      </table>

                    </div>

                  </div>


                  <!-- =================================================
                       PASIEN SUMBER
                  ================================================== -->

                  <div
                    id="sourcePatientWrapper"
                    class="mt-4"
                    style="display:none;">

                    <div class="patient-card">

                      <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                          <h6 class="fw-bold mb-1">

                            <i class="ti ti-user-check text-primary me-2"></i>

                            Pasien RME Sumber

                          </h6>

                          <small class="text-muted">
                            Pasien ini digunakan sebagai acuan penggabungan RME.
                          </small>

                        </div>

                        <span class="source-badge">
                          RM SUMBER
                        </span>

                      </div>


                      <div class="row g-3">

                        <div class="col-md-2">

                          <div class="patient-info-item">

                            <span class="label">
                              ID Patient
                            </span>

                            <span
                              class="value"
                              id="source_id_patient">
                              -
                            </span>

                          </div>

                        </div>


                        <div class="col-md-2">

                          <div class="patient-info-item">

                            <span class="label">
                              Nomor RM
                            </span>

                            <span
                              class="value"
                              id="source_rm">
                              -
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3">

                          <div class="patient-info-item">

                            <span class="label">
                              Nama Pasien
                            </span>

                            <span
                              class="value"
                              id="source_name">
                              -
                            </span>

                          </div>

                        </div>


                        <div class="col-md-2">

                          <div class="patient-info-item">

                            <span class="label">
                              NIK
                            </span>

                            <span
                              class="value"
                              id="source_nik">
                              -
                            </span>

                          </div>

                        </div>


                        <div class="col-md-3">

                          <div class="patient-info-item">

                            <span class="label">
                              No. Kartu
                            </span>

                            <span
                              class="value"
                              id="source_kartu">
                              -
                            </span>

                          </div>

                        </div>

                      </div>

                    </div>

                  </div>

                </div>

              </div>

            </div>

          </div>


          <!-- =====================================================
               RIWAYAT VISIT
          ====================================================== -->

          <div
            class="row mt-3"
            id="visitWrapper"
            style="display:none;">

            <div class="col-12">

              <div class="card">

                <div class="card-body">

                  <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                      <h5 class="fw-semibold mb-1">

                        <i class="ti ti-history text-success me-2"></i>

                        Riwayat Visit Terkait

                      </h5>

                      <small class="text-muted">

                        Menampilkan visit dari pasien yang memiliki NIK atau
                        Nomor Kartu yang sama.

                      </small>

                    </div>


                    <span
                      id="totalVisit"
                      class="badge bg-secondary">

                      0 Visit

                    </span>

                  </div>


                  <div class="table-responsive">

                    <table
                      class="table table-bordered table-hover align-middle">

                      <thead class="table-light">

                        <tr>

                          <th width="50">
                            #
                          </th>

                          <th>
                            Status
                          </th>

                          <th>
                            No. RM
                          </th>

                          <th>
                            ID Patient
                          </th>

                          <th>
                            Nama Pasien
                          </th>

                          <th>
                            NIK
                          </th>

                          <th>
                            No. Kartu
                          </th>

                          <th>
                            ID Visit
                          </th>

                          <th>
                            Tanggal Visit
                          </th>

                          <th>
                            Dokter
                          </th>

                          <th>
                            Poli
                          </th>

                        </tr>

                      </thead>


                      <tbody id="visitTableBody">

                        <tr>

                          <td
                            colspan="11"
                            class="empty-state">

                            <i class="ti ti-file-search d-block"></i>

                            Pilih pasien untuk melihat visit terkait.

                          </td>

                        </tr>

                      </tbody>

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
  require 'library.php';
  ?>


  <script>
    $(document).ready(function() {

      // ============================================================
      // ENTER = SEARCH
      // ============================================================

      $('#keywordPatient').on('keypress', function(e) {

        if (e.which === 13) {

          e.preventDefault();

          $('#btnCariPasien').click();

        }

      });


      // ============================================================
      // CARI PASIEN
      // ============================================================

      $('#btnCariPasien').on('click', function() {

        const keyword =
          $.trim($('#keywordPatient').val());

        if (!keyword) {

          Swal.fire({
            icon: 'warning',
            title: 'Pencarian Kosong',
            text: 'Silakan ketik Nomor RM, Nama, NIK atau Nomor Kartu.'
          });

          return;

        }


        const btn =
          $(this);

        const originalText =
          btn.html();


        btn
          .prop('disabled', true)
          .html(`
            <span
              class="spinner-border spinner-border-sm me-1">
            </span>
            Mencari...
          `);


        $('#searchResultWrapper')
          .show();


        $('#patientSearchBody')
          .html(`
            <tr>

              <td
                colspan="6"
                class="loading-state">

                <div
                  class="spinner-border text-primary mb-2">
                </div>

                <div>
                  Mencari data pasien...
                </div>

              </td>

            </tr>
          `);


        fetch(
            'controller/admin/gabungRme.php?action=patients&keyword=' +
            encodeURIComponent(keyword)
          )

          .then(response => {

            if (!response.ok) {

              throw new Error(
                'HTTP Error ' + response.status
              );

            }

            return response.json();

          })


          .then(response => {

            console.log(
              'Search Patient:',
              response
            );


            if (response.status !== 'success') {

              throw new Error(
                response.message ||
                'Gagal mencari pasien.'
              );

            }


            const patients =
              response.data || [];


            $('#totalPatientResult')
              .text(
                patients.length + ' Data'
              );


            if (patients.length === 0) {

              $('#patientSearchBody')
                .html(`
                  <tr>

                    <td
                      colspan="6"
                      class="empty-state">

                      <i class="ti ti-user-x d-block"></i>

                      Tidak ditemukan pasien
                      dengan kata kunci
                      "<strong>${escapeHtml(keyword)}</strong>".

                    </td>

                  </tr>
                `);

              return;

            }


            let html = '';


            patients.forEach(function(patient, index) {

              html += `

                <tr class="patient-search-result">

                  <td>
                    ${index + 1}
                  </td>

                  <td>

                    <strong>
                      ${escapeHtml(patient.nomor_rm || '-')}
                    </strong>

                  </td>

                  <td>

                    <div class="search-patient-name">

                      ${escapeHtml(patient.patient_name || '-')}

                    </div>

                  </td>

                  <td>

                    ${escapeHtml(patient.patient_nik || '-')}

                  </td>

                  <td>

                    ${escapeHtml(patient.patient_number || '-')}

                  </td>

                  <td>

                    <button
                      type="button"
                      class="btn btn-sm btn-primary btnPilihPasien"
                      data-id="${patient.id_patient}">

                      <i class="ti ti-check me-1"></i>

                      Pilih

                    </button>

                  </td>

                </tr>

              `;

            });


            $('#patientSearchBody')
              .html(html);

          })


          .catch(function(error) {

            console.error(
              'Error Search Patient:',
              error
            );


            $('#patientSearchBody')
              .html(`
                <tr>

                  <td
                    colspan="6"
                    class="text-center text-danger py-5">

                    <i class="ti ti-alert-circle fs-1 d-block mb-2"></i>

                    ${escapeHtml(
                      error.message ||
                      'Gagal mengambil data pasien.'
                    )}

                  </td>

                </tr>
              `);


            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: error.message ||
                'Gagal mengambil data pasien.'
            });

          })


          .finally(function() {

            btn
              .prop('disabled', false)
              .html(originalText);

          });

      });


      // ============================================================
      // PILIH PASIEN
      // ============================================================

      $(document).on(
        'click',
        '.btnPilihPasien',
        function() {

          const idPatient =
            $(this).data('id');


          if (!idPatient) {

            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'ID pasien tidak ditemukan.'
            });

            return;

          }


          loadPatientRme(idPatient);

        }
      );


      // ============================================================
      // LOAD RME PASIEN
      // ============================================================

      function loadPatientRme(idPatient) {

        $('#visitWrapper')
          .show();


        $('#visitTableBody')
          .html(`
            <tr>

              <td
                colspan="11"
                class="loading-state">

                <div
                  class="spinner-border text-primary mb-2">
                </div>

                <div>
                  Mengambil data RME pasien...
                </div>

              </td>

            </tr>
          `);


        fetch(
            'controller/admin/gabungRme.php?action=search&id_patient=' +
            encodeURIComponent(idPatient)
          )

          .then(response => {

            if (!response.ok) {

              throw new Error(
                'HTTP Error ' + response.status
              );

            }

            return response.json();

          })


          .then(response => {

            console.log(
              'RME Response:',
              response
            );


            if (response.status !== 'success') {

              throw new Error(
                response.message ||
                'Data RME tidak ditemukan.'
              );

            }


            const source =
              response.source_patient;


            // ======================================================
            // SOURCE PATIENT CARD
            // ======================================================

            if (source) {

              $('#source_id_patient')
                .text(source.id_patient || '-');

              $('#source_rm')
                .text(source.nomor_rm || '-');

              $('#source_name')
                .text(source.patient_name || '-');

              $('#source_nik')
                .text(source.patient_nik || '-');

              $('#source_kartu')
                .text(source.patient_number || '-');


              $('#sourcePatientWrapper')
                .show();

            }


            // ======================================================
            // VISIT
            // ======================================================

            const visits =
              response.visits || [];


            $('#totalVisit')
              .text(
                visits.length + ' Visit'
              );


            if (visits.length === 0) {

              $('#visitTableBody')
                .html(`
                  <tr>

                    <td
                      colspan="11"
                      class="empty-state">

                      <i class="ti ti-database-off d-block"></i>

                      Tidak ditemukan visit terkait.

                    </td>

                  </tr>
                `);

              return;

            }


            let html = '';


            visits.forEach(function(visit, index) {

              const isSource =
                String(visit.id_patient) ===
                String(source.id_patient);


              const statusHtml =
                isSource

                ?

                `
                  <span class="source-badge">
                    RM SUMBER
                  </span>
                `

                :

                `
                  <span class="match-badge">
                    MATCH
                  </span>
                `;


              html += `

                <tr>

                  <td>
                    ${index + 1}
                  </td>

                  <td>
                    ${statusHtml}
                  </td>

                  <td>

                    <strong>
                      ${escapeHtml(
                        visit.nomor_rm || '-'
                      )}
                    </strong>

                  </td>

                  <td>
                    ${escapeHtml(
                      visit.id_patient || '-'
                    )}
                  </td>

                  <td>
                    ${escapeHtml(
                      visit.patient_name || '-'
                    )}
                  </td>

                  <td>
                    ${escapeHtml(
                      visit.patient_nik || '-'
                    )}
                  </td>

                  <td>
                    ${escapeHtml(
                      visit.patient_number || '-'
                    )}
                  </td>

                  <td>
                    ${escapeHtml(
                      visit.visit_ID || '-'
                    )}
                  </td>

                  <td>
                    ${escapeHtml(
                      visit.visit_date || '-'
                    )}
                  </td>

                  <td>
                    ${escapeHtml(
                      visit.nama_dokter || '-'
                    )}
                  </td>

                  <td>
                    ${escapeHtml(
                      visit.nama_poli || '-'
                    )}
                  </td>

                </tr>

              `;

            });


            $('#visitTableBody')
              .html(html);

          })


          .catch(function(error) {

            console.error(
              'Error Load RME:',
              error
            );


            $('#visitTableBody')
              .html(`
                <tr>

                  <td
                    colspan="11"
                    class="text-center text-danger py-5">

                    <i class="ti ti-alert-circle fs-1 d-block mb-2"></i>

                    ${escapeHtml(
                      error.message ||
                      'Gagal mengambil data RME.'
                    )}

                  </td>

                </tr>
              `);


            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: error.message ||
                'Gagal mengambil data RME.'
            });

          });

      }


      // ============================================================
      // ESCAPE HTML
      // ============================================================

      function escapeHtml(value) {

        return String(value ?? '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');

      }

    });
  </script>

</body>

</html>