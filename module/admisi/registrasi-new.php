<?php
$title = 'Pasien';
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
    /* =========================================================
   FACE CAMERA
   ========================================================= */

    .face-camera-wrapper {
      position: relative;
      width: 100%;
      max-width: 640px;
      margin: 0 auto;

      background: #000;

      overflow: hidden;
      border-radius: 14px;

      /* supaya kamera tidak terlalu tinggi */
      aspect-ratio: 4 / 3;
    }


    /* =========================================================
   VIDEO
   ========================================================= */

    #video {
      width: 100%;
      height: 100%;

      display: block;

      object-fit: cover;

      /* Mirror seperti kamera depan */
      transform: scaleX(-1);
    }


    /* =========================================================
   OVERLAY
   ========================================================= */

    .face-overlay {

      position: absolute;

      inset: 0;

      display: flex;

      align-items: center;
      justify-content: center;

      pointer-events: none;

      /*
     * Background transparan.
     * Gelapnya dibuat oleh box-shadow
     * pada face-frame.
     */

    }


    /* =========================================================
   FACE FRAME
   Bentuk menyerupai kepala / wajah
   ========================================================= */

    .face-circle {

      position: relative;

      width: 250px;
      height: 320px;

      /*
     * Bentuk wajah:
     * - bagian kepala membulat
     * - sisi pipi rounded
     * - bagian bawah mengecil seperti dagu
     */

      border-radius:
        48% 48% 44% 44% / 38% 38% 62% 62%;

      border: 4px solid #00ff88;

      background: transparent;

      /*
     * Membuat area luar frame menjadi gelap
     * tetapi bagian wajah tetap terlihat jelas.
     */

      box-shadow:

        0 0 0 9999px rgba(0, 0, 0, 0.45),

        0 0 18px rgba(0, 255, 136, 0.65),

        inset 0 0 15px rgba(0, 255, 136, 0.05);

      transition:
        border-color 0.3s ease,
        box-shadow 0.3s ease;

    }


    /* =========================================================
   CORNER MARKER
   ========================================================= */

    .face-corner {

      position: absolute;

      width: 30px;
      height: 30px;

      border-color: #ffffff;

      pointer-events: none;

    }


    /* TOP LEFT */

    .corner-tl {

      top: -4px;
      left: -4px;

      border-top: 5px solid;
      border-left: 5px solid;

      border-radius: 5px 0 0 0;

    }


    /* TOP RIGHT */

    .corner-tr {

      top: -4px;
      right: -4px;

      border-top: 5px solid;
      border-right: 5px solid;

      border-radius: 0 5px 0 0;

    }


    /* BOTTOM LEFT */

    .corner-bl {

      bottom: -4px;
      left: -4px;

      border-bottom: 5px solid;
      border-left: 5px solid;

      border-radius: 0 0 0 5px;

    }


    /* BOTTOM RIGHT */

    .corner-br {

      bottom: -4px;
      right: -4px;

      border-bottom: 5px solid;
      border-right: 5px solid;

      border-radius: 0 0 5px 0;

    }


    /* =========================================================
   TEXT PANDUAN
   ========================================================= */

    .face-guide-text {

      font-size: 16px;

      font-weight: 600;

      color: #333;

      line-height: 1.5;

    }


    .face-guide-text i {

      color: #198754;

      margin-right: 5px;

    }


    .face-guide-small {

      font-size: 13px;

      color: #777;

      margin-top: 4px;

      line-height: 1.5;

    }


    /* =========================================================
   BUTTON
   ========================================================= */

    #captureBtn {

      min-width: 160px;

      font-weight: 600;

      border-radius: 8px;

    }


    /* =========================================================
   MODAL CAMERA
   ========================================================= */

    #cameraModal .modal-body {

      padding: 20px;

    }


    #cameraModal .modal-header {

      border-bottom: 1px solid #eee;

    }


    #cameraModal .modal-title {

      font-weight: 600;

    }


    /* =========================================================
   CAMERA STATUS
   ========================================================= */

    .face-status {

      display: inline-flex;

      align-items: center;

      gap: 6px;

      margin-top: 8px;

      font-size: 13px;

      font-weight: 500;

    }


    /* =========================================================
   STATUS WARNA
   ========================================================= */

    .face-frame-success {

      border-color: #00ff88 !important;

      box-shadow:

        0 0 0 9999px rgba(0, 0, 0, 0.45),

        0 0 25px rgba(0, 255, 136, 0.85),

        inset 0 0 20px rgba(0, 255, 136, 0.08);

    }


    .face-frame-warning {

      border-color: #ffc107 !important;

      box-shadow:

        0 0 0 9999px rgba(0, 0, 0, 0.45),

        0 0 25px rgba(255, 193, 7, 0.75);

    }


    .face-frame-danger {

      border-color: #dc3545 !important;

      box-shadow:

        0 0 0 9999px rgba(0, 0, 0, 0.45),

        0 0 25px rgba(220, 53, 69, 0.75);

    }


    /* =========================================================
   ANIMATION
   ========================================================= */

    @keyframes facePulse {

      0% {

        box-shadow:

          0 0 0 9999px rgba(0, 0, 0, 0.45),

          0 0 12px rgba(0, 255, 136, 0.5);

      }

      50% {

        box-shadow:

          0 0 0 9999px rgba(0, 0, 0, 0.45),

          0 0 25px rgba(0, 255, 136, 0.85);

      }

      100% {

        box-shadow:

          0 0 0 9999px rgba(0, 0, 0, 0.45),

          0 0 12px rgba(0, 255, 136, 0.5);

      }

    }


    .face-circle {

      animation: facePulse 2s infinite;

    }


    /* =========================================================
   DESKTOP
   ========================================================= */

    @media (min-width: 768px) {

      .face-camera-wrapper {

        max-width: 640px;

      }

      .face-circle {

        width: 250px;
        height: 320px;

      }

    }


    /* =========================================================
   TABLET
   ========================================================= */

    @media (max-width: 767px) {

      .face-camera-wrapper {

        max-width: 100%;

      }

      .face-circle {

        width: 220px;
        height: 285px;

      }

    }


    /* =========================================================
   MOBILE
   ========================================================= */

    @media (max-width: 576px) {

      .face-camera-wrapper {

        aspect-ratio: 3 / 4;

        border-radius: 12px;

      }

      .face-circle {

        width: 205px;
        height: 270px;

        border-width: 3px;

      }

      .face-corner {

        width: 25px;
        height: 25px;

      }

      .face-guide-text {

        font-size: 14px;

      }

      .face-guide-small {

        font-size: 12px;

      }

      #captureBtn {

        width: 100%;

        max-width: 240px;

      }

    }


    /* =========================================================
   VERY SMALL MOBILE
   ========================================================= */

    @media (max-width: 380px) {

      .face-circle {

        width: 185px;
        height: 245px;

      }

    }


    /* =========================================================
   REDUCE MOTION
   ========================================================= */

    @media (prefers-reduced-motion: reduce) {

      .face-circle {

        animation: none;

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
                    <h5 class="card-title fw-semibold">Data Pasien</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <!-- Tombol -->
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                          <th class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Lengkap</th>
                          <th scope="col" class="text-dark fw-normal">NIK</th>
                          <th scope="col" class="text-dark fw-normal">BPJS</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">No.Handphone</th>
                          <th scope="col" class="text-dark fw-normal text-center">Foto</th>
                          <th scope="col" class="text-dark fw-normal">Face Status</th>

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


  <div class="modal fade" id="cameraModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Ambil Wajah</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body text-center">

          <div class="face-camera-wrapper">

            <video id="video" autoplay playsinline></video>

            <!-- Overlay kamera -->
            <div class="face-overlay">

              <!-- Lingkaran wajah -->
              <div class="face-circle">
                <div class="face-corner corner-tl"></div>
                <div class="face-corner corner-tr"></div>
                <div class="face-corner corner-bl"></div>
                <div class="face-corner corner-br"></div>
              </div>

            </div>

          </div>

          <div class="face-guide-text mt-3">
            <i class="fas fa-user-circle"></i>
            Posisikan wajah tepat di dalam lingkaran
          </div>

          <div class="face-guide-small">
            Pastikan wajah terlihat jelas, menghadap kamera dan tidak terpotong.
          </div>

          <div id="faceStatus" class="face-status text-warning">
            <i class="fas fa-exclamation-circle"></i>
            <span>Memuat deteksi wajah...</span>
          </div>

          <canvas id="canvas" style="display:none;"></canvas>

          <div class="mt-3">
            <button id="captureBtn" class="btn btn-success px-4">
              <i class="fas fa-camera"></i>
              Ambil Wajah
            </button>
          </div>

        </div>

      </div>
    </div>
  </div>
</body>

<div class="modal fade" id="programModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="programForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_patient" id="id_patient">
        <input type="hidden" name="patient_provinsi" id="provinsi_text">
        <input type="hidden" name="patient_kabupaten" id="kabupaten_text">
        <input type="hidden" name="patient_kecamatan" id="kecamatan_text">
        <input type="hidden" name="patient_kelurahan" id="kelurahan_text">
        <div class="row">
          <div class="col-12">
            <div class="alert alert-warning" role="alert">
              Nomor rekam medis di generate otomatis, untuk melakukan perubahan silahkan klik tombol ubah pada data pasien
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Nama Pasien</label>
              <input type="text" id="patient_name" name="patient_name" class="form-control" required>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label">NIK</label>
              <div class="input-group">
                <input
                  type="text"
                  id="patient_nik"
                  name="patient_nik"
                  class="form-control">
                <button
                  class="btn btn-primary"
                  type="button" id="btncarinik">
                  <i class="bi bi-search"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label">No.BPJS</label>
              <div class="input-group">
                <input
                  type="text"
                  id="patient_bpjs"
                  name="patient_bpjs"
                  class="form-control">
                <button
                  class="btn btn-primary"
                  type="button" id="btncaribpjs">
                  <i class="bi bi-search"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="col-3">
            <div class="mb-3">
              <label class="form-label required">Jenis Kelamin</label>
              <select name="patient_gender" class="form-select" id="patient_gender" required>
                <option value="">PILIH</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
              </select>
            </div>
          </div>
          <div class="col-3">
            <div class="mb-3">
              <label class="form-label required">Agama</label>
              <select name="patient_religion" class="form-select" id="patient_religion" required>
                <option value="">PILIH</option>
                <option value="Islam">Islam</option>
                <option value="Kristen">Kristen</option>
                <option value="Katolik">Katolik</option>
                <option value="Hindu">Hindu</option>
                <option value="Budha">Budha</option>
              </select>
            </div>
          </div>
          <div class="col-3">
            <div class="mb-3">
              <label class="form-label required">Tempat Lahir</label>
              <input type="text" id="patient_place" name="patient_place" class="form-control" required>
            </div>
          </div>
          <div class="col-3">
            <div class="mb-3">
              <label class="form-label required">Tanggal Lahir</label>
              <input type="date" id="patient_datebirth" name="patient_datebirth" class="form-control" required>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label ">No.Handphone</label>
              <input type="text" id="patient_phone" name="patient_phone" class="form-control">
            </div>
          </div>
          <div class="col-3">
            <label class="form-label required">Provinsi</label>
            <select id="provinsi" class="form-select">
              <option value="">PILIH</option>
            </select>
          </div>

          <div class="col-3">
            <label class="form-label required">Kabupaten/Kota</label>
            <select id="kabupaten" class="form-select">
              <option value="">PILIH</option>
            </select>
          </div>

          <div class="col-3">
            <label class="form-label required">Kecamatan</label>
            <select id="kecamatan" class="form-select">
              <option value="">PILIH</option>
            </select>
          </div>

          <div class="col-3">
            <label class="form-label required">Kelurahan</label>
            <select id="kelurahan" class="form-select">
              <option value="">PILIH</option>
            </select>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label">Alamat</label>
              <textarea name="patient_address" id="patient_address" class="form-control" rows="5"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
<script>
  const getBaseUrl = () => {
    const path = window.location.pathname.split('/');
    return path[1] ? `${window.location.origin}/${path[1]}/` : `${window.location.origin}/`;
  };

  const baseUrl = getBaseUrl();
  const apiUrl = 'controller/master/patientContrroller';
  let table;
  $(document).ready(function() {
    table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {


            // 🔥 CEK DATA KOSONG
            if (!json.data || json.data.length === 0) {

              // hapus alert lama biar gak dobel
              $('#emptyAlert').remove();

              // tampilkan alert
              $('.card-body').prepend(`
                  <div id="emptyAlert" class="alert alert-warning">
                    ⚠️ Data pasien ini akan tersedia ketika faskes mendaftarkan pasien 
                    karena sudah terintegrasi dengan <b>PCare BPJS</b>
                  </div>
                `);

              return []; // tetap return array kosong ke datatable
            }

            // 🔥 HAPUS ALERT kalau data sudah ada
            $('#emptyAlert').remove();

            return {
              "actions": `
                <div class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-light camera-btn" 
                      data-id="${row.id_patient}" title="Ambil Foto">
                      <i class="fas fa-camera"></i>
                    </button>

                    <a class="btn btn-info" 
                      href="module/admin/patient_details?pt=${row.id_patient}" 
                      title="Detail">
                      <i class="fas fa-info-circle"></i>
                    </a>

                    <button class="btn btn-warning edit-btn" 
                      data-id="${row.id_patient}" title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>

                    <button class="btn btn-danger delete-btn" 
                      data-id="${row.id_patient}" title="Hapus">
                      <i class="fas fa-trash"></i>
                    </button>

                  </div>
                </div>
              `,
              "rm": row.nomor_rm ?? "-",
              "name": row.patient_name ?? "-",
              "nik": row.patient_nik ?? "-",
              "bpjs": row.patient_bpjs ?? "-",
              "gender": row.patient_gender ?? "-",
              "phone": row.patient_phone ?? "-",
              "face_image": row.face_image ? `
                <a href="${baseUrl}${row.face_image.replace(/^(\.\.\/)+/,'')}" target="_blank">
                  <img src="${baseUrl}${row.face_image.replace(/^(\.\.\/)+/,'')}" 
                      style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                </a>
              ` : '-',
              "face_status": row.face_image ?
                '<span class="badge bg-success">✔️ Sudah direkam</span>' : '<span class="badge bg-danger">❌ Belum direkam</span>',
            };
          });
        }
      },
      columns: [{
          data: "actions",
          orderable: false,
          searchable: false
        }, {
          data: "rm"
        }, {
          data: "name"
        },
        {
          data: "nik"
        },
        {
          data: "bpjs"
        },
        {
          data: "gender"
        },
        {
          data: "phone"
        },
        {
          data: "face_image"
        },
        {
          data: "face_status"
        },
      ],
      order: [
        [1, 'asc']
      ],
      footerCallback: function(row, data, start, end, display) {
        var api = this.api();

        // Hitung total bobot
        let total = api
          .column(3, {
            page: 'current'
          })
          .data()
          .reduce((a, b) => {
            return (parseFloat(a) || 0) + (parseFloat(b) || 0);
          }, 0);

        // Tampilkan di footer
        $(api.column(3).footer()).html(total.toFixed(2) + " %");
      }
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
      $('#id_patient').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();

      let formData = $(this).serializeArray();

      let id = $('#id_patient').val();

      if (id) {
        formData.push({
          name: '_method',
          value: 'PUT'
        });
      }

      $.ajax({
        url: apiUrl,
        type: 'POST', // 🔥 SELALU POST
        data: $.param(formData),
        success: function(res) {
          let data = typeof res === 'string' ? JSON.parse(res) : res;

          if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success');
            $('#programModal').modal('hide');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', data.message, 'error');
          }
        }
      });
    });
    // 🔹 Edit
    $(document).on('click', '.edit-btn', function() {
      let id = $(this).data('id');
      fetch(apiUrl + `?id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let d = resp.data;

            // isi otomatis berdasarkan name field
            for (let key in d) {
              $(`[name="${key}"]`).val(d[key]);
            }

            $('#programModal .modal-title').text('Edit Data');
            $('#programModal').modal('show');
          }
        });
    });

    // 🔹 Delete
    $(document).on('click', '.delete-btn', function() {
      let id = $(this).data('id');

      Swal.fire({
        title: 'Hapus Data?',
        text: 'Data akan dihapus',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {

          fetch(apiUrl + `?id=${id}`, {
              method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {

              // 🔴 KALAU ADA RELASI
              if (data.status === 'has_relation') {
                Swal.fire({
                  title: 'Data memiliki relasi!',
                  text: 'Hapus juga semua data terkait?',
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonText: 'Ya, hapus semua',
                  cancelButtonText: 'Tidak'
                }).then((res2) => {
                  if (res2.isConfirmed) {
                    fetch(apiUrl + `?id=${id}&force=true`, {
                        method: 'DELETE'
                      })
                      .then(res => res.json())
                      .then(del => {
                        if (del.status === 'success') {
                          Swal.fire('Berhasil!', 'Semua data dihapus.', 'success');
                          table.ajax.reload(null, false);
                        }
                      });
                  }
                });
              }

              // ✅ SUCCESS NORMAL
              else if (data.status === 'success') {
                Swal.fire('Berhasil!', data.message, 'success');
                table.ajax.reload(null, false);
              }

              // ❌ ERROR
              else {
                Swal.fire('Gagal!', data.message, 'error');
              }

            });
        }
      });
    });

    $(document).on('click', '#btncarinik', function() {
      cariDataPatient('nik', '#btncarinik');
    });

    $(document).on('click', '#btncaribpjs', function() {
      cariDataPatient('noka', '#btncaribpjs');
    });

    function cariDataPatient(type, id) {
      let btn = $(id);
      const nomor = type == "nik" ?
        $('#patient_nik').val() :
        $('#patient_bpjs').val();
      $.ajax({
        type: 'GET',
        url: 'controller/admisi/services/getInfoPatient.php',
        data: {
          type: type,
          nomor: nomor
        },
        dataType: 'json',
        beforeSend: function() {
          btn.prop('disabled', true);
          btn.find('.btn-text').addClass('d-none');
          btn.find('.btn-loading').removeClass('d-none');

        },
        complete: function() {
          btn.prop('disabled', false);
          btn.find('.btn-text').removeClass('d-none');
          btn.find('.btn-loading').addClass('d-none');

        },
        success: function(response) {
          if (type == 'nik') {
            $('#patient_bpjs').val(response.noKartu);
          } else {
            $('#patient_nik').val(response.nik);
          }
          $('#patient_name').val(response.nama);
          $('#patient_datebirth').val(response.tglLahir);
          $('#patient_phone').val(response.noHP);
          if (response.sex == 'L') {
            $('#patient_gender').val('Laki-laki').trigger('change');
          } else {
            $('#patient_gender').val('Perempuan').trigger('change');
          }
        },
        error: function(xhr, status, error) {
          console.log(error);
        }
      });
    }
  });
</script>
<script src="assets/js/face-api.min.js"></script>
<script>
  let currentPatientId = null;
  let stream = null;
  let faceTimer = null;
  let faceModelReady = null;
  let faceTooClose = false;

  function loadFaceModel() {
    if (!faceModelReady) {
      faceModelReady = faceapi.nets.tinyFaceDetector.loadFromUri('models');
    }
    return faceModelReady;
  }

  function setFaceState(ok, message, tooClose = false) {
    faceTooClose = tooClose;
    const circle = document.querySelector(".face-circle");
    const status = document.getElementById("faceStatus");
    const btn = document.getElementById("captureBtn");
    circle.classList.toggle("face-frame-success", ok);
    circle.classList.toggle("face-frame-warning", !ok);
    status.className = "face-status " + (ok ? "text-success" : "text-warning");
    status.innerHTML = `<i class="fas ${ok ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i><span>${message}</span>`;
  }

  // Cek apakah wajah (koordinat video asli) berada pas di dalam lingkaran
  function evaluateFace(box, video) {
    const vr = video.getBoundingClientRect();
    const cr = document.querySelector(".face-circle").getBoundingClientRect();
    const scale = Math.max(vr.width / video.videoWidth, vr.height / video.videoHeight);
    const offX = (vr.width - video.videoWidth * scale) / 2;
    const offY = (vr.height - video.videoHeight * scale) / 2;

    // video di-mirror (scaleX(-1))
    const fw = box.width * scale;
    const fh = box.height * scale;
    const fx = vr.width - (box.x * scale + offX + fw);
    const fy = box.y * scale + offY;

    const faceCx = fx + fw / 2;
    const faceCy = fy + fh / 2;
    const cCx = cr.left - vr.left + cr.width / 2;
    const cCy = cr.top - vr.top + cr.height / 2;

    const dx = Math.abs(faceCx - cCx) / (cr.width / 2);
    const dy = Math.abs(faceCy - cCy) / (cr.height / 2);
    const ratio = fw / cr.width;

    if (ratio < 0.5) return {ok: false, msg: "Dekatkan wajah ke kamera"};
    if (ratio > 0.95) return {ok: false, tooClose: true, msg: "Terlalu dekat, mundurkan sedikit"};
    if (dx > 0.2 || dy > 0.2) return {ok: false, msg: "Posisikan wajah di tengah lingkaran"};
    return {ok: true, msg: "Wajah pas, siap diambil"};
  }

  async function startFaceDetection() {
    const video = document.getElementById("video");
    stopFaceDetection();
    faceTooClose = false;
    setFaceState(false, "Memuat deteksi wajah...");
    try {
      await loadFaceModel();
    } catch (e) {
      console.error(e);
      setFaceState(false, "Gagal memuat model deteksi wajah");
      return;
    }
    const opts = new faceapi.TinyFaceDetectorOptions({inputSize: 320, scoreThreshold: 0.5});
    let busy = false;
    faceTimer = setInterval(async () => {
      if (busy || !video.videoWidth || video.paused) return;
      busy = true;
      try {
        const det = await faceapi.detectSingleFace(video, opts);
        if (!faceTimer) return;
        if (!det) setFaceState(false, "Wajah tidak terdeteksi");
        else {
          const r = evaluateFace(det.box, video);
          setFaceState(r.ok, r.msg, !!r.tooClose);
        }
      } catch (e) {
        console.error(e);
      } finally {
        busy = false;
      }
    }, 250);
  }

  function stopFaceDetection() {
    if (faceTimer) {
      clearInterval(faceTimer);
      faceTimer = null;
    }
  }

  $(document).on("click", ".camera-btn", async function() {
    // WAJIB: ambil id dari tombol
    currentPatientId = $(this).data("id");
    console.log("📌 ID PATIENT:", currentPatientId);
    const modalEl = document.getElementById("cameraModal");
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: true
      });

      const video = document.getElementById("video");
      video.srcObject = stream;

      await video.play();
      startFaceDetection();

    } catch (err) {
      alert("Kamera tidak bisa diakses");
      console.error(err);
    }

  });
  document.getElementById("captureBtn").addEventListener("click", function() {
    if (this.disabled) return;
    if (faceTooClose) {
      Swal.fire("Terlalu dekat", "Mundurkan wajah sedikit dari kamera.", "warning");
      return;
    }

    const video = document.getElementById("video");
    const canvas = document.getElementById("canvas");

    if (!video.videoWidth || !video.videoHeight) {
      Swal.fire(
        "Kamera belum siap",
        "Tunggu sampai kamera aktif.",
        "warning"
      );
      return;
    }

    /*
     * =====================================================
     * UKURAN AREA WAJAH
     * =====================================================
     */

    const circleElement = document.querySelector(".face-circle");

    const circleSize = circleElement.offsetWidth;

    const videoDisplayWidth = video.clientWidth;
    const videoDisplayHeight = video.clientHeight;

    /*
     * Rasio antara ukuran video asli
     * dengan ukuran video yang tampil di layar
     */
    const scaleX = video.videoWidth / videoDisplayWidth;
    const scaleY = video.videoHeight / videoDisplayHeight;

    /*
     * Posisi tengah video
     */
    const centerX = video.videoWidth / 2;
    const centerY = video.videoHeight / 2;

    /*
     * Ukuran crop mengikuti lingkaran
     */
    const cropWidth = circleSize * scaleX;
    const cropHeight = circleSize * scaleY;

    /*
     * Posisi crop dari video asli
     */
    const cropX = centerX - (cropWidth / 2);
    const cropY = centerY - (cropHeight / 2);

    /*
     * =====================================================
     * CANVAS HASIL CAPTURE
     * =====================================================
     */

    canvas.width = cropWidth;
    canvas.height = cropHeight;

    const ctx = canvas.getContext("2d");

    /*
     * Karena preview kamera dibuat mirror,
     * hasil capture juga dibuat mirror agar sesuai preview.
     */
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);

    ctx.drawImage(
      video,

      cropX,
      cropY,
      cropWidth,
      cropHeight,

      0,
      0,
      cropWidth,
      cropHeight
    );

    /*
     * Reset transform
     */
    ctx.setTransform(1, 0, 0, 1, 0, 0);

    /*
     * Convert ke base64
     */
    const imageData = canvas.toDataURL(
      "image/jpeg",
      0.90
    );

    /*
     * =====================================================
     * KIRIM KE SERVER
     * =====================================================
     */

    const captureBtn = document.getElementById("captureBtn");

    captureBtn.disabled = true;
    captureBtn.dataset.saving = "1";

    captureBtn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-1"></span>
        Menyimpan...
    `;

    fetch("controller/admisi/recordFace.php", {

        method: "POST",

        headers: {
          "Content-Type": "application/json"
        },

        body: JSON.stringify({
          id: currentPatientId,
          image: imageData
        })

      })
      .then(res => res.json())

      .then(res => {

        if (res.status === "success") {

          Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: "Foto wajah berhasil direkam.",
            timer: 1500,
            showConfirmButton: false
          });

          const modalEl = document.getElementById("cameraModal");

          const modal = bootstrap.Modal.getInstance(modalEl);

          if (modal) {
            modal.hide();
          }

          setTimeout(() => {
            table.ajax.reload(null, false);
          }, 500);

        } else {

          Swal.fire(
            "Gagal!",
            res.message || "Wajah gagal disimpan.",
            "error"
          );

        }

      })

      .catch(error => {

        console.error(error);

        Swal.fire(
          "Error!",
          "Terjadi kesalahan saat menyimpan wajah.",
          "error"
        );

      })

      .finally(() => {

        delete captureBtn.dataset.saving;
        captureBtn.disabled = false;

        captureBtn.innerHTML = `
            <i class="fas fa-camera"></i>
            Ambil Wajah
        `;

      });

  });

  /* =========================
     CLEANUP (INI YANG PENTING)
  ========================= */
  document.getElementById("cameraModal")
    .addEventListener("hidden.bs.modal", function() {

      console.log("🛑 Stop camera");
      stopFaceDetection();

      if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
      }

      const video = document.getElementById("video");
      if (video) {
        video.srcObject = null; // penting
      }

    });
</script>
<script>
  const apiWilayah = "controller/master/wilayah.php";

  // 🔥 LOAD PROVINSI
  function loadProvinsi() {
    fetch(`${apiWilayah}?type=provinsi`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        data.forEach(d => {
          html += `<option value="${d.id}">${d.nama}</option>`;
        });
        $('#provinsi').html(html);
      });
  }

  // 🔥 LOAD KABUPATEN
  $('#provinsi').on('change', function() {
    let id = $(this).val();

    $('#kabupaten').html('<option>Loading...</option>');
    $('#kecamatan').html('<option>PILIH</option>');
    $('#kelurahan').html('<option>PILIH</option>');

    fetch(`${apiWilayah}?type=kabupaten&id=${id}`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        data.forEach(d => {
          html += `<option value="${d.id}">${d.nama}</option>`;
        });
        $('#kabupaten').html(html);
      });
  });

  // 🔥 LOAD KECAMATAN
  $('#kabupaten').on('change', function() {
    let id = $(this).val();

    $('#kecamatan').html('<option>Loading...</option>');
    $('#kelurahan').html('<option>PILIH</option>');

    fetch(`${apiWilayah}?type=kecamatan&id=${id}`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        data.forEach(d => {
          html += `<option value="${d.id}">${d.nama}</option>`;
        });
        $('#kecamatan').html(html);
      });
  });

  // 🔥 LOAD KELURAHAN
  $('#kecamatan').on('change', function() {
    let id = $(this).val();

    $('#kelurahan').html('<option>Loading...</option>');

    fetch(`${apiWilayah}?type=kelurahan&id=${id}`)
      .then(res => res.json())
      .then(data => {
        let html = '<option value="">PILIH</option>';
        data.forEach(d => {
          html += `<option value="${d.id}">${d.nama}</option>`;
        });
        $('#kelurahan').html(html);
      });
  });

  // 🔥 INIT
  $(document).ready(function() {
    loadProvinsi();
  });
</script>

<script>
  $('#provinsi').on('change', function() {
    $('#provinsi_text').val($(this).find('option:selected').text());
  });

  $('#kabupaten').on('change', function() {
    $('#kabupaten_text').val($(this).find('option:selected').text());
  });

  $('#kecamatan').on('change', function() {
    $('#kecamatan_text').val($(this).find('option:selected').text());
  });

  $('#kelurahan').on('change', function() {
    $('#kelurahan_text').val($(this).find('option:selected').text());
  });
</script>

</html>