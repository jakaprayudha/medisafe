<?php
$title = 'Pemeriksaan';
require '../../controller/view.php';
require '../../database/connect.php';
require '../../controller/visit/assesmen.php';
$no = $_GET['no'];
$rm = $_GET['rm'];
$check = mysqli_query($koneksi, "SELECT * FROM pasien_visit INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient INNER JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor WHERE visit_ID='$no' AND nomor_rm='$rm'");
$data = mysqli_fetch_array($check);

// Hitung usia jika data ditemukan
if ($data) {
  $tanggal_lahir = new DateTime($data['patient_datebirth']);
  $tanggal_visit = new DateTime($data['visit_date']);

  $usia = $tanggal_lahir->diff($tanggal_visit);
}

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
          } else if ($rme == 'c') {
            include 'menu_rme_inap.php';
          }
          ?>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <input type="hidden" name="nomor_rm" value="<?= $rm ?>">
                  <input type="hidden" name="nomor_visit" value="<?= $no ?>">
                  <h4 class="mb-3">Form Foto Pasien</h4>
                  <!-- Data Pasien -->
                  <div class="row">
                    <div class="col-3">
                      <div class="mb-3">
                        <label for="patient_name" class="form-label">Nama Pasien</label>
                        <input type="text" value="<?= $data['patient_name'] ?>" id="patient_name" readonly name="patient_name" class="form-control bg-light">
                      </div>
                    </div>
                    <div class="col-3">
                      <div class="mb-3">
                        <label for="patient_gender" class="form-label">Gender</label>
                        <input type="text" value="<?= $data['patient_gender'] ?>" id="patient_gender" name="patient_gender" class="form-control bg-light" readonly>
                      </div>
                    </div>
                    <div class="col-3">
                      <div class="mb-3">
                        <label for="usia" class="form-label">Usia</label>
                        <input type="text" value="<?php echo  $usia->y . " tahun " . $usia->m . " bulan " . $usia->d . " hari"; ?>" id="usia" name="usia" class="form-control bg-light" readonly>
                      </div>
                    </div>
                    <div class="col-3">
                      <div class="mb-3">
                        <label for="doctor_name" class="form-label">Dokter</label>
                        <input type="text" value="<?= $data['doctor_name'] ?>" id="doctor_name" name="dokter" class="form-control bg-light" readonly>
                      </div>
                    </div>
                  </div>
                  <div class="mt-3 text-center">
                    <img id="fotoPasien" src="" class="img-thumbnail" style="max-width:250px; display:none;">
                  </div>
                  <div class="text-end mt-2">
                    <button class="btn btn-outline-success" id="btnFoto">
                      <iconify-icon icon="mdi:camera-outline"></iconify-icon> Ambil / Upload Foto
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal Tanda Tangan -->
  <div class="modal fade" id="modalTtd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-danger text-white">
          <h6 class="modal-title">Tanda Tangan Pasien</h6>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <p class="text-muted mb-2">Silakan tanda tangan di area berikut:</p>
          <canvas id="signature-pad" style="border: 1px dashed #ccc; width: 100%; height: 200px; border-radius: 8px;"></canvas>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-light" id="clear-signature">Hapus</button>
          <button type="button" class="btn btn-primary" id="save-signature">Simpan Tanda Tangan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Webcam -->
  <div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Ambil / Upload Foto Pasien</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="row">
            <div class="col-md-6 text-center">
              <video id="webcam" autoplay playsinline width="100%" class="border rounded"></video>
              <button class="btn btn-primary mt-2" id="captureBtn">
                <iconify-icon icon="mdi:camera"></iconify-icon>
                Ambil Foto
              </button>
            </div>

            <div class="col-md-6 text-center">
              <canvas id="canvas" class="border rounded w-100"></canvas>
              <div class="mt-3">ATAU</div>
              <input type="file" id="uploadFile" class="form-control mt-2" accept="image/*">
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button id="saveFoto" class="btn btn-success">Simpan Foto</button>
        </div>
      </div>
    </div>
  </div>
  <?php
  require 'library.php';
  ?>
</body>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const modal = new bootstrap.Modal(document.getElementById("modalTtd"));
    const openModalBtn = document.getElementById("openModal");
    const canvas = document.getElementById("signature-pad");
    const clearBtn = document.getElementById("clear-signature");
    const saveBtn = document.getElementById("save-signature");

    // Setup SignaturePad
    const signaturePad = new SignaturePad(canvas, {
      backgroundColor: "rgba(255, 255, 255, 0)",
      penColor: "rgb(220, 53, 69)" // warna merah BPJS
    });

    // Resize canvas sesuai modal
    function resizeCanvas() {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      canvas.getContext("2d").scale(ratio, ratio);
      signaturePad.clear();
    }

    window.addEventListener("resize", resizeCanvas);
    modal._element.addEventListener("shown.bs.modal", resizeCanvas);

    // Buka modal
    openModalBtn.addEventListener("click", function() {
      modal.show();
    });

    // Hapus tanda tangan
    clearBtn.addEventListener("click", function() {
      signaturePad.clear();
    });

    // Simpan tanda tangan
    saveBtn.addEventListener("click", function() {
      if (signaturePad.isEmpty()) {
        Swal.fire({
          icon: "warning",
          title: "Belum ada tanda tangan!",
          text: "Silakan isi tanda tangan terlebih dahulu.",
          confirmButtonColor: "#dc3545"
        });
        return;
      }

      const dataUrl = signaturePad.toDataURL("image/png");

      // Kirim ke server
      fetch("controller/visit/saveSignature.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            nomor_visit: "<?= $no ?>",
            nomor_rm: "<?= $rm ?>",
            id_patient: "<?= $data['id_patient'] ?>",
            ttd: dataUrl
          })
        })
        .then(res => res.json())
        .then(result => {
          if (result.status === "success") {
            Swal.fire({
              icon: "success",
              title: "Tanda Tangan Tersimpan!",
              text: "Terima kasih, pernyataan Anda telah disetujui.",
              confirmButtonColor: "#198754",
              timer: 2000,
              showConfirmButton: false
            });
            setTimeout(() => modal.hide(), 2000);
          } else {
            Swal.fire({
              icon: "error",
              title: "Gagal Menyimpan!",
              text: "Terjadi kesalahan saat menyimpan tanda tangan.",
              confirmButtonColor: "#dc3545"
            });
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire({
            icon: "error",
            title: "Kesalahan Server",
            text: "Tidak dapat terhubung ke server.",
            confirmButtonColor: "#dc3545"
          });
        });
    });



  });
</script>

<script>
  // Buka modal
  document.getElementById("btnFoto").addEventListener("click", () => {
    new bootstrap.Modal(document.getElementById('modalFoto')).show();
    startWebcam();
  });

  // Start Webcam
  function startWebcam() {
    navigator.mediaDevices.getUserMedia({
        video: true
      })
      .then(stream => {
        document.getElementById("webcam").srcObject = stream;
      });
  }

  // Capture webcam → canvas
  document.getElementById("captureBtn").addEventListener("click", function() {
    let video = document.getElementById("webcam");
    let canvas = document.getElementById("canvas");

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext("2d").drawImage(video, 0, 0);
  });

  // Save foto
  document.getElementById("saveFoto").addEventListener("click", function() {
    const rm = document.querySelector("input[name='nomor_rm']").value;
    const visit = document.querySelector("input[name='nomor_visit']").value;

    let fileUpload = document.getElementById("uploadFile").files[0];

    // Jika upload file (bukan webcam)
    if (fileUpload) {
      let reader = new FileReader();
      reader.onload = () => {
        sendToServer(reader.result, rm, visit);
      };
      reader.readAsDataURL(fileUpload);
      return;
    }

    // Foto dari webcam
    let base64img = document.getElementById("canvas").toDataURL("image/jpeg");
    sendToServer(base64img, rm, visit);
  });

  // Kirim ke server
  function sendToServer(base64img, rm, visit) {
    fetch("controller/visit/saveFoto.php", {
        method: "POST",
        body: JSON.stringify({
          image: base64img,
          rm: rm,
          visit: visit
        })
      })
      .then(r => r.json())
      .then(res => {
        if (res.status === "success") {
          Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: res.message,
            timer: 1700,
            showConfirmButton: false
          });

          // Tampilkan foto tanpa reload
          if (res.foto) {
            document.getElementById("fotoPasien").src = res.foto;
            document.getElementById("fotoPasien").style.display = "block";
          }

          // Tutup modal
          setTimeout(() => {
            bootstrap.Modal.getInstance(document.getElementById('modalFoto')).hide();
          }, 600);

        } else {
          Swal.fire({
            icon: "error",
            title: "Gagal!",
            text: res.message
          });
        }
      });
  }

  window.addEventListener("DOMContentLoaded", () => {
    const rm = document.querySelector("input[name='nomor_rm']").value;
    const visit = document.querySelector("input[name='nomor_visit']").value;

    fetch(`controller/visit/getFoto.php?rm=${rm}&no=${visit}`)
      .then(r => r.json())
      .then(res => {
        if (res.foto) {
          document.getElementById("fotoPasien").src = res.foto;
          document.getElementById("fotoPasien").style.display = "block";
        }
      });
  });
</script>


</html>