<?php
$title = 'Permintaan Rawat Inap';
require '../../database/connect.php';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    .icon-circle {
      width: 45px;
      height: 45px;
      border-radius: 50%;
    }

    .animate__animated {
      animation-duration: 0.8s;
      animation-fill-mode: both;
    }

    .animate__fadeInUp {
      animation-name: fadeInUp;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
      }

      to {
        opacity: 1;
        transform: none;
      }
    }

    @media (max-width: 768px) {
      .d-flex.flex-wrap.justify-content-end.gap-2 {
        justify-content: center !important;
      }

      #btnBatalRanap,
      #formRawatInap button[type="submit"] {
        width: 100%;
        justify-content: center;
        font-size: 0.95rem;
        padding: 10px 0;
      }
    }
  </style>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <?php
    require 'sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require 'navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <?php
          $rme = $_GET['rme']; // default a
          if ($rme == 'a') {
            include 'menu_rme.php';
          } else if ($rme == 'b') {
            include 'menu_rmeb.php';
          }
          ?>
          <?php
          $visitId = $_GET['no'] ?? '';
          $rm = $_GET['rm'] ?? '';
          $datapasien = null;
          $datavisit = null;

          if ($visitId !== '') {
            $checkvisit = mysqli_query($koneksi, "SELECT 
              pv.id_patient,
              mp.nomor_rm,
              md.doctor_name, 
              pv.diagnosa,
              pv.id_doctor,
              icd_10.code,
              icd_10.icd10 as icd_name
            FROM pasien_visit pv
            LEFT JOIN ms_patient mp 
              ON mp.id_patient = pv.id_patient
            LEFT JOIN ms_doctor md 
              ON pv.id_doctor = md.id_doctor 
            LEFT JOIN icd_10 
              ON icd_10.code = pv.diagnosa  
            WHERE pv.visit_ID = '$visitId'
            LIMIT 1
            ");
            $datavisit = mysqli_fetch_array($checkvisit);
            if ($datavisit) {
              $datapasien = $datavisit;
            }
          }

          if (!$datapasien && $rm !== '') {
            $checkpasien = mysqli_query($koneksi, "SELECT * FROM ms_patient WHERE nomor_rm = '$rm'");
            $datapasien = mysqli_fetch_array($checkpasien);
          }
          ?>

          <div class="row">
            <div class="col-12">
              <?php
              require 'card-pasien.php';
              ?>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100 shadow-sm border-0">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold mb-0">
                      <iconify-icon icon="mdi:hospital-bed" class="text-primary fs-5 me-1"></iconify-icon>
                      Permintaan Rawat Inap
                    </h5>
                  </div>

                  <form id="formRawatInap">
                    <div id="alertSuccess" class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 d-flex align-items-center d-none" role="alert" style="animation: fadeIn 0.6s;">
                      <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                      <div><strong>Berhasil!</strong> Permintaan Rawat Inap Berhasil Dibuat.</div>
                      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    <style>
                      @keyframes fadeIn {
                        from {
                          opacity: 0;
                          transform: translateY(-10px);
                        }

                        to {
                          opacity: 1;
                          transform: translateY(0);
                        }
                      }
                    </style>

                    <div class="row g-3">
                      <input type="hidden" name="id_patient" id="id_patient" value="<?= $datapasien['id_patient'] ?? '' ?>">
                      <input type="hidden" name="visit_ID_inpatient" id="visit_ID_inpatient" value="<?= $visitId ?>">


                      <!-- Dokter Penanggung Jawab -->
                      <div class="col-md-6">
                        <label for="dokter" class="form-label fw-semibold">Dokter Penanggung Jawab</label>
                        <!-- <select class="form-select" id="dokter" name="id_doctor" required>
                          <option value="" selected disabled>Pilih dokter</option> -->
                        <?php
                        // $getdokter = mysqli_query($koneksi, "SELECT * FROM ms_doctor ORDER BY doctor_name ASC");
                        // while ($dok = mysqli_fetch_array($getdokter)) {
                        //   echo '<option value="' . $dok['id_doctor'] . '">' . $dok['doctor_name'] . '</option>';
                        // }
                        ?>
                        <!-- </select> -->
                        <input type="text" class="form-control bg-light" id="" name="" value="<?= $datavisit['id_doctor'] ?? '' ?>" readonly>
                        <input type="hidden" name="id_doctor" id="id_doctor" value="<?= $datavisit['id_doctor'] ?? '' ?>">
                      </div>

                      <!-- Tanggal & Waktu Masuk -->
                      <div class="col-md-3">
                        <label for="tanggalMasuk" class="form-label fw-semibold">Tanggal Masuk</label>
                        <input type="date" value="<?= date('Y-m-d') ?>" class="form-control" id="tanggalMasuk" name="ranap_date" required>
                      </div>
                      <div class="col-md-3">
                        <label for="waktuMasuk" class="form-label fw-semibold">Waktu</label>
                        <input type="time" value="<?= date('H:i') ?>" class="form-control" id="waktuMasuk" name="ranap_time" required>
                      </div>

                      <!-- Diagnosa -->
                      <div class="col-md-12">
                        <label for="diagnosa" class="form-label fw-semibold">Diagnosa Awal</label>
                        <input type="text" class="form-control bg-light" id="diagnosa" name="diagnosa_awal" value="<?= isset($datavisit['code']) ? ($datavisit['code'] . "-" . $datavisit['icd_name']) : '' ?>" readonly>
                      </div>

                      <!-- Catatan -->
                      <div class="col-12">
                        <label for="catatan" class="form-label fw-semibold">Catatan Tambahan</label>
                        <textarea class="form-control" id="catatan" name="ranap_notes" rows="5" placeholder="Tulis catatan dokter atau kebutuhan khusus pasien"></textarea>
                      </div>

                      <!-- Tombol Aksi -->
                      <div class="col-12 mt-4">
                        <div class="d-flex flex-wrap justify-content-end gap-2">
                          <button type="button" id="btnBatalRanap" class="btn btn-danger d-flex align-items-center">
                            <iconify-icon icon="mdi:cancel" class="me-1"></iconify-icon>
                            <span>Batalkan Permintaan</span>
                          </button>
                          <button type="submit" id="btnSimpanRanap" class="btn btn-primary d-flex align-items-center">
                            <iconify-icon icon="mdi:check-circle-outline" class="me-1"></iconify-icon>
                            <span>Simpan Permintaan</span>
                          </button>
                        </div>
                      </div>
                    </div>
                  </form>
                  <!-- ✅ Data hasil simpan (Desain baru yang keren) -->
                  <div id="dataRanapBaru" class="mt-4 d-none">
                    <div class="card border-0 shadow-sm rounded-4 animate__animated animate__fadeInUp">
                      <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                          <div class="icon-circle bg-success-subtle text-success me-3 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="mdi:clipboard-check-outline" width="26" height="26"></iconify-icon>
                          </div>
                          <div>
                            <h6 class="fw-bold mb-0 text-success">Permintaan Rawat Inap Berhasil</h6>
                            <small class="text-muted">Data permintaan terbaru berhasil disimpan</small>
                          </div>
                        </div>
                        <hr>

                        <div class="row g-3">
                          <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border-start border-success border-3 shadow-sm">
                              <p class="text-muted mb-1"><i class="bi bi-person-vcard me-1"></i>Dokter Penanggung Jawab</p>
                              <h6 id="showDokter" class="fw-semibold mb-0 text-dark"></h6>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 border-start border-primary border-3 shadow-sm">
                              <p class="text-muted mb-1"><i class="bi bi-calendar-event me-1"></i>Tanggal Masuk</p>
                              <h6 id="showTanggal" class="fw-semibold mb-0 text-dark"></h6>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 border-start border-info border-3 shadow-sm">
                              <p class="text-muted mb-1"><i class="bi bi-clock-history me-1"></i>Waktu</p>
                              <h6 id="showWaktu" class="fw-semibold mb-0 text-dark"></h6>
                            </div>
                          </div>
                          <div class="col-md-12">
                            <div class="p-3 bg-light rounded-3 border-start border-warning border-3 shadow-sm">
                              <p class="text-muted mb-1"><i class="bi bi-activity me-1"></i>Diagnosa Awal</p>
                              <h6 id="showDiagnosa" class="fw-semibold mb-0 text-dark"></h6>
                            </div>
                          </div>
                          <div class="col-md-12">
                            <div class="p-3 bg-light rounded-3 border-start border-secondary border-3 shadow-sm">
                              <p class="text-muted mb-1"><i class="bi bi-journal-text me-1"></i>Catatan Tambahan</p>
                              <p id="showCatatan" class="fw-normal mb-0 text-dark"></p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
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
</body>

</html>

<!-- ✅ Script AJAX Insert + Get Data -->
<script>
  $(document).ready(function() {

    // 🔹 Fungsi untuk ambil data ranap terbaru dari server
    function loadDataRanap() {
      $.ajax({
        url: "controller/visit/getLastRawatInap",
        type: "GET",
        data: {
          id_patient: $("#id_patient").val(),
          visit_ID_inpatient: $("#visit_ID_inpatient").val()
        },
        dataType: "json",
        success: function(response) {
          if (response.status === "success" && response.data) {

            $("#showDokter").text(response.data.id_doctor);
            $("#showTanggal").text(response.data.ranap_date);
            $("#showWaktu").text(response.data.ranap_time);
            $("#showDiagnosa").text(response.data.diagnosa_awal);
            $("#showCatatan").text(response.data.ranap_notes || "-");

            $("#dataRanapBaru")
              .data("id_ranap", response.data.id_ranap)
              .removeClass("d-none");

            // ✅ Disable tombol simpan jika data sudah ada
            $("#btnSimpanRanap")
              .prop("disabled", true)
              .removeClass("btn-primary")
              .addClass("btn-light")
              .html(`
            <iconify-icon icon="mdi:check-all" class="me-1"></iconify-icon>
            Permintaan Sudah Ada
          `);

          } else {

            $("#dataRanapBaru").addClass("d-none");

            // ✅ Enable kembali jika belum ada data
            $("#btnSimpanRanap")
              .prop("disabled", false)
              .removeClass("btn-secondary")
              .addClass("btn-primary")
              .html(`
            <iconify-icon icon="mdi:check-circle-outline" class="me-1"></iconify-icon>
            Simpan Permintaan
          `);
          }
        },
        error: function() {
          console.warn("Gagal memuat data rawat inap terakhir.");
        }
      });
    }

    // 🔹 Jalankan saat halaman pertama kali dibuka
    loadDataRanap();

    // 🔹 Saat form disubmit (simpan data baru)
    $("#formRawatInap").on("submit", function(e) {
      e.preventDefault();
      $.ajax({
        url: "controller/visit/insertRawatInap",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        beforeSend: function() {
          $('#btnSimpanRanap').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');
        },
        success: function(response) {
          $('#btnSimpanRanap').prop('disabled', false).html('<iconify-icon icon="mdi:check-circle-outline" class="me-1"></iconify-icon>Simpan Permintaan');

          if (response.status === "success") {
            $("#alertSuccess").removeClass("d-none");

            // ✅ Setelah simpan, muat ulang data agar selalu update
            loadDataRanap();

            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: 'Permintaan rawat inap telah disimpan.',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              text: response.message || 'Terjadi kesalahan saat menyimpan data.'
            });
          }
        },
        error: function() {
          $('#btnSimpanRanap').prop('disabled', false);
          Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Tidak dapat terhubung ke server.'
          });
        }
      });
    });

    // 🔹 Fungsi hapus data rawat inap terakhir jika belum booking
    $("#btnBatalRanap").on("click", function() {
      const id_ranap = $("#dataRanapBaru").data("id_ranap"); // ambil dari elemen hasil load

      if (!id_ranap) {
        Swal.fire({
          icon: 'warning',
          title: 'Tidak ada data!',
          text: 'Tidak ada permintaan rawat inap yang bisa dibatalkan.'
        });
        return;
      }

      Swal.fire({
        title: 'Batalkan Permintaan?',
        text: 'Data permintaan rawat inap terakhir akan dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "controller/visit/cancelRawatInap",
            type: "POST",
            dataType: "json",
            data: {
              id_ranap: id_ranap
            },
            success: function(response) {
              if (response.status === "success") {
                Swal.fire({
                  icon: 'success',
                  title: 'Dibatalkan!',
                  text: response.message,
                  timer: 1800,
                  showConfirmButton: false
                });
                $("#dataRanapBaru").addClass("d-none");
                $("#alertSuccess").addClass("d-none");

                // reset form
                $("#formRawatInap")[0].reset();

                // hapus id ranap lama
                $("#dataRanapBaru").removeData("id_ranap");

                // aktifkan kembali tombol simpan
                $("#btnSimpanRanap")
                  .prop("disabled", false)
                  .removeClass("btn-light btn-secondary")
                  .addClass("btn-primary")
                  .html(`
                      <iconify-icon icon="mdi:check-circle-outline" class="me-1"></iconify-icon>
                      Simpan Permintaan
                    `);

                // load ulang data terbaru
                loadDataRanap();
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Gagal!',
                  text: response.message || 'Tidak dapat menghapus data.'
                });
              }
            },
            error: function() {
              Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: 'Tidak dapat terhubung ke server.'
              });
            }
          });
        }
      });
    });
  });
</script>