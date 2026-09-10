<?php

$title = 'Kontrak';

require '../../controller/view.php';


// ==========================================================
// SESSION CUSTOMER
// ==========================================================

$id_customer = $_SESSION['id_customer'] ?? '';


// ==========================================================
// VALIDASI SESSION
// ==========================================================

if (empty($id_customer)) {
  $errorMessage = 'Session ID Customer tidak ditemukan.';
  $contractId = '';
} else {

  // ======================================================
  // AMBIL ID CLINIC DARI setting_clinic
  // berdasarkan id_customer
  // ======================================================

  $contractId = '';

  $sqlClinic = "
        SELECT id
        FROM setting_clinic
        WHERE id_customer = ?
        LIMIT 1
    ";

  $stmtClinic = mysqli_prepare($koneksi, $sqlClinic);

  if ($stmtClinic) {

    mysqli_stmt_bind_param(
      $stmtClinic,
      "s",
      $id_customer
    );

    mysqli_stmt_execute($stmtClinic);

    $resultClinic = mysqli_stmt_get_result($stmtClinic);

    if ($rowClinic = mysqli_fetch_assoc($resultClinic)) {

      // ID setting_clinic menjadi parameter no
      $contractId = $rowClinic['id'];
    }

    mysqli_stmt_close($stmtClinic);
  }

  if (empty($contractId)) {
    $errorMessage = 'Data clinic untuk customer ini tidak ditemukan.';
  } else {
    $errorMessage = '';
  }
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
    /* =====================================================
           KONTRAK PAGE
        ===================================================== */

    .contract-wrapper {
      width: 100%;
      background: #f5f6f8;
      border-radius: 10px;
      overflow: hidden;
    }

    .contract-toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding: 15px 20px;
      background: #ffffff;
      border-bottom: 1px solid #e9ecef;
    }

    .contract-title {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
      color: #2b2b2b;
    }

    .contract-subtitle {
      margin: 3px 0 0;
      font-size: 13px;
      color: #6c757d;
    }

    .contract-actions {
      display: flex;
      gap: 8px;
    }

    .contract-frame-wrapper {
      width: 100%;
      background: #e9ecef;
      padding: 15px;
    }

    #contractFrame {
      display: block;
      width: 100%;
      height: 1200px;
      border: 0;
      border-radius: 6px;
      background: #ffffff;
    }

    .contract-loading {
      min-height: 500px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #ffffff;
      border-radius: 6px;
    }

    .contract-error {
      min-height: 400px;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      background: #ffffff;
      border-radius: 6px;
      padding: 30px;
    }

    .contract-error-icon {
      width: 70px;
      height: 70px;
      margin: 0 auto 15px;
      border-radius: 50%;
      background: #fff3cd;
      color: #856404;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 30px;
    }

    @media (max-width: 768px) {

      .contract-toolbar {
        flex-direction: column;
        align-items: flex-start;
      }

      .contract-actions {
        width: 100%;
      }

      .contract-actions button {
        flex: 1;
      }

      #contractFrame {
        height: 1000px;
      }

    }
  </style>

</head>


<body>

  <!-- =====================================================
         BODY WRAPPER
    ====================================================== -->

  <div
    class="page-wrapper"
    id="main-wrapper"
    data-layout="vertical"
    data-navbarbg="skin6"
    data-sidebartype="full"
    data-sidebar-position="fixed"
    data-header-position="fixed">


    <!-- =================================================
             SIDEBAR
        ================================================== -->

    <?php
    require 'sidebar.php';
    ?>


    <!-- =================================================
             MAIN WRAPPER
        ================================================== -->

    <div class="body-wrapper">


      <!-- =============================================
                 HEADER
            ============================================== -->

      <?php
      require 'navbar.php';
      ?>


      <!-- =============================================
                 CONTENT
            ============================================== -->

      <div class="body-wrapper-inner">

        <div class="container-fluid">

          <div class="row">

            <div class="col-lg-12 d-flex align-items-stretch">

              <div class="card w-100">

                <div class="card-body p-0">


                  <?php if (!empty($errorMessage)): ?>

                    <!-- =================================
                                             ERROR
                                        ================================== -->

                    <div class="contract-error">

                      <div>

                        <div class="contract-error-icon">

                          <i class="ti ti-file-off"></i>

                        </div>

                        <h5 class="fw-semibold mb-2">
                          Kontrak Tidak Ditemukan
                        </h5>

                        <p class="text-muted mb-0">
                          <?= htmlspecialchars($errorMessage) ?>
                        </p>

                      </div>

                    </div>


                  <?php else: ?>


                    <!-- =================================
                                             CONTRACT WRAPPER
                                        ================================== -->

                    <div class="contract-wrapper">


                      <!-- =============================
                                                 TOOLBAR
                                            ============================== -->

                      <div class="contract-toolbar">

                        <div>

                          <h5 class="contract-title">

                            <i class="ti ti-file-certificate me-2"></i>

                            Kontrak Kerjasama

                          </h5>

                          <p class="contract-subtitle">

                            Preview Perjanjian Kerjasama
                            Sistem Informasi Medisafe

                          </p>

                        </div>


                        <div class="contract-actions">

                          <button
                            type="button"
                            class="btn btn-outline-secondary btn-sm"
                            id="btnRefreshContract">

                            <i class="ti ti-refresh me-1"></i>

                            Refresh

                          </button>

                        </div>

                      </div>


                      <!-- =============================
                                                 CONTRACT PREVIEW
                                            ============================== -->

                      <div class="contract-frame-wrapper">

                        <div
                          id="contractLoading"
                          class="contract-loading">

                          <div class="text-center">

                            <div
                              class="spinner-border text-primary mb-3"
                              role="status"></div>

                            <div class="text-muted">

                              Memuat dokumen kontrak...

                            </div>

                          </div>

                        </div>


                        <iframe
                          id="contractFrame"
                          title="Preview Kontrak Kerjasama"
                          src="module/administrator/kontrak.php?no=<?= urlencode($contractId) ?>"
                          style="display:none;"></iframe>

                      </div>

                    </div>


                  <?php endif; ?>


                </div>

              </div>

            </div>

          </div>

        </div>

      </div>

    </div>

  </div>


  <!-- =====================================================
         LIBRARY
    ====================================================== -->

  <?php
  require 'library.php';
  ?>


  <!-- =====================================================
         JAVASCRIPT
    ====================================================== -->

  <?php if (empty($errorMessage)): ?>

    <script>
      document.addEventListener('DOMContentLoaded', function() {

        const frame = document.getElementById('contractFrame');

        const loading = document.getElementById('contractLoading');

        const btnRefresh = document.getElementById('btnRefreshContract');

        const btnPrint = document.getElementById('btnPrintContract');


        // ==================================================
        // LOAD CONTRACT
        // ==================================================

        function loadContract() {

          if (!frame) {
            console.error('contractFrame tidak ditemukan');
            return;
          }


          if (loading) {
            loading.style.display = 'flex';
          }


          frame.style.display = 'none';


          const baseUrl =
            'module/administrator/kontrak.php?no=<?= urlencode($contractId) ?>';


          frame.src =
            baseUrl +
            '&t=' +
            new Date().getTime();

        }


        // ==================================================
        // CONTRACT LOADED
        // ==================================================

        if (frame) {

          frame.addEventListener('load', function() {

            if (loading) {
              loading.style.display = 'none';
            }

            frame.style.display = 'block';

          });

        }


        // ==================================================
        // REFRESH
        // ==================================================

        if (btnRefresh) {

          btnRefresh.addEventListener('click', function() {

            loadContract();

          });

        }


        // ==================================================
        // PRINT
        // ==================================================

        if (btnPrint) {

          btnPrint.addEventListener('click', function() {

            if (!frame || !frame.contentWindow) {

              if (typeof Swal !== 'undefined') {

                Swal.fire(
                  'Warning!',
                  'Dokumen kontrak belum dimuat.',
                  'warning'
                );

              }

              return;

            }


            try {

              frame.contentWindow.focus();

              frame.contentWindow.print();

            } catch (error) {

              console.error(
                'Gagal mencetak kontrak:',
                error
              );

              if (typeof Swal !== 'undefined') {

                Swal.fire(
                  'Error!',
                  'Gagal membuka dialog cetak kontrak.',
                  'error'
                );

              }

            }

          });

        }

      });
    </script>

  <?php endif; ?>


</body>

</html>