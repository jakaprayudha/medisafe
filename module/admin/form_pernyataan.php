<?php
$title = 'Pemeriksaan';
require '../../controller/view.php';
require '../../database/connect.php';
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
                  <h4 class="mb-3">Surat Pernyataan Peserta</h4>
                  <!-- Data Pasien -->
                  <?php require 'card-pasien.php'; ?>
                  <div class="mb-3">
                    <div class="alert alert-danger border-2 shadow-sm" role="alert" style="background-color:#fff5f5; border-color:#dc3545;">
                      <div class="d-flex align-items-start">
                        <div class="me-3">
                          <iconify-icon icon="streamline-ultimate:cash-payment-bills-bold"
                            style="font-size: 2rem; color:#dc3545;"></iconify-icon>
                        </div>
                        <div>
                          <h6 class="fw-bold text-danger mb-2">Pernyataan Pemanfaatan Data Medis</h6>
                          <p class="mb-0" style="font-size: 0.9rem; color:#333; line-height:1.5;">
                            Dengan sadar, terkait pemanfaatan jaminan pelayanan kesehatan <strong>BPJS Kesehatan</strong>,
                            dengan ini saya menyatakan kesediaan atas data medis (<em>rekam medis</em>) diri saya
                            untuk dipergunakan oleh <strong>Dokter, Rumah Sakit,</strong> dan <strong>BPJS Kesehatan</strong>
                            sesuai dengan kepentingan pelayanan dan administrasi kesehatan yang berlaku.
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="text-end mt-2">
                    <a href="module/admin/print/formulir_pernyataan.php?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>" target="_blank">
                      <button class="btn btn-outline-primary">
                        <iconify-icon icon="mdi:printer-outline"></iconify-icon>
                        Cetak
                      </button>
                    </a>
                    <button class="btn btn-outline-danger" id="openModal">
                      <iconify-icon icon="mdi:check-decagram-outline"></iconify-icon>
                      Saya Mengerti dan Setuju
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


</html>