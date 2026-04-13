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
              <label class="form-label required" id="patient_name">Nama Pasien</label>
              <input type="text" id="patient_name" name="patient_name" class="form-control" required>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label " id="patient_name">NIK</label>
              <input type="text" id="patient_nik" name="patient_nik" class="form-control">
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label " id="patient_name">No.BPJS</label>
              <input type="text" id="patient_bpjs" name="patient_bpjs" class="form-control">
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
    var table = $('#periodeTable').DataTable({
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
                      href="module/admin/patient_details?no=${row.patient_number}&pt=${row.id_patient}" 
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