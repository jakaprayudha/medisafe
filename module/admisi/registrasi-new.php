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
                          <th class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Lengkap</th>
                          <th scope="col" class="text-dark fw-normal">NIK</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">Agama</th>
                          <th scope="col" class="text-dark fw-normal">No.Handphone</th>
                          <th scope="col" class="text-dark fw-normal text-center">Foto</th>
                          <th scope="col" class="text-dark fw-normal">Face Status</th>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
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
          <video id="video" width="100%" autoplay playsinline></video>
          <canvas id="canvas" style="display:none;"></canvas>

          <div class="mt-3">
            <button id="captureBtn" class="btn btn-success">
              Ambil Gambar
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog">
      <form id="programForm" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_patient" id="id_patient">
          <div class="row">
            <div class="col-12">
              <div class="alert alert-warning" role="alert">
                Nomor rekam medis di generate otomatis, untuk melakukan perubahan silahkan klik tombol ubah pada data pasien
              </div>
            </div>
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label required" id="patient_name">Nama Pasien</label>
                <input type="text" id="patient_name" name="patient_name" class="form-control" required>
              </div>
            </div>
            <div class="col-6">
              <div class="mb-3">
                <label class="form-label required">Jenis Kelamin</label>
                <select name="patient_gender" class="form-select" id="patient_gender" required>
                  <option value="">PILIH</option>
                  <option value="Laki-laki">Laki-laki</option>
                  <option value="Perempuan">Perempuan</option>
                </select>
              </div>
            </div>
            <div class="col-6">
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
            <div class="col-6">
              <div class="mb-3">
                <label class="form-label required">Tempat Lahir</label>
                <input type="text" id="patient_place" name="patient_place" class="form-control" required>
              </div>
            </div>
            <div class="col-6">
              <div class="mb-3">
                <label class="form-label required">Tanggal Lahir</label>
                <input type="date" id="patient_datebirth" name="patient_datebirth" class="form-control" required>
              </div>
            </div>
            <div class="col-12">
              <div class="mb-3">
                <label class="form-label ">No.Handphone</label>
                <input type="text" id="patient_phone" name="patient_phone" class="form-control">
              </div>
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
</body>



<script>
  const getBaseUrl = () => {
    const path = window.location.pathname.split('/');
    return path[1] ? `${window.location.origin}/${path[1]}/` : `${window.location.origin}/`;
  };

  const baseUrl = getBaseUrl();
  const apiUrl = 'controller/master/patientContrroller';
  let table;
  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: true,
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "actions": `
                      <div class="text-center">
								<div class="btn-group btn-group-sm" role="group">
                  <!-- Tombol kamera -->
                  <a class="btn btn-primary camera-btn" href="javascript:;" data-id="${row.id_patient}">
                    <i class="fas fa-camera"></i>
                  </a>

                	<a class="btn btn-info" href="module/admin/patient_details?no=${row.patient_number}&pt=${row.id_patient}">
											<i class="fas fa-info-circle"></i>
									</a>
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_patient}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_patient}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "rm": row.nomor_rm ?? "-",
              "name": row.patient_name ?? "-",
              "nik": row.patient_nik ?? "-",
              "gender": row.patient_gender ?? "-",
              "agama": row.patient_religion ?? "-",
              "phone": row.patient_phone ?? "-",


              "face_image": row.face_image ? `
                <a href="${baseUrl}${row.face_image.replace('../../','')}" target="_blank">
                  <img src="${baseUrl}${row.face_image.replace('../../','')}" 
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
          data: "rm"
        }, {
          data: "name"
        },
        {
          data: "nik"
        },
        {
          data: "gender"
        },
        {
          data: "agama"
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
        {
          data: "actions",
          orderable: false,
          searchable: false
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

      let formData = $(this).serialize();
      let id = $('#id_patient').val();

      $.ajax({
        url: apiUrl,
        type: 'POST',
        data: formData,
        success: function(res) {
          let data = typeof res === 'string' ? JSON.parse(res) : res;

          if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success');
            $('#programModal').modal('hide');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', data.message, 'error');
          }
        },
        error: function(xhr) {
          Swal.fire('Error!', xhr.responseText, 'error');
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
              if (data.status === 'success') {
                Swal.fire('Berhasil!', 'Data dihapus.', 'success');
                table.ajax.reload(null, false);
              }
            });
        }
      });
    });
  });
</script>
<script>
  let currentPatientId = null;
  let stream = null;

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

    } catch (err) {
      alert("Kamera tidak bisa diakses");
      console.error(err);
    }

  });

  document.getElementById("captureBtn").addEventListener("click", function() {

    const video = document.getElementById("video");
    const canvas = document.getElementById("canvas");

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext("2d");
    ctx.drawImage(video, 0, 0);

    const imageData = canvas.toDataURL("image/png");

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
        alert("Wajah berhasil disimpan");

        $("#cameraModal").modal("hide");

        setTimeout(() => {
          table.ajax.reload(null, false);
        }, 500); // delay 0.5 detik
      });


  });

  /* =========================
     CLEANUP (INI YANG PENTING)
  ========================= */
  document.getElementById("cameraModal")
    .addEventListener("hidden.bs.modal", function() {

      console.log("🛑 Stop camera");

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

</html>