<?php
$title = 'Form SEP (Surat Eligibilitas Peserta)';
$no = $_GET['no'];
$rm = $_GET['rm'];
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
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <h4 class="mb-3">Form SEP (Surat Eligibilitas Peserta)</h4>
                  <!-- Data Pasien -->
                  <div class="row">
                    <div class="col-3">
                      <div class="mb-3">
                        <label for="patient_name" class="form-label">Nama Pasien</label>
                        <input type="text" id="patient_name" readonly name="patient_name" class="form-control bg-light">
                      </div>
                    </div>
                    <div class="col-3">
                      <div class="mb-3">
                        <label for="patient_gender" class="form-label">Gender</label>
                        <input type="text" id="patient_gender" name="patient_gender" class="form-control bg-light" readonly>
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
                        <input type="text" id="doctor_name" name="dokter" class="form-control bg-light" readonly>
                      </div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <div class="alert alert-info border-2 shadow-sm"
                      role="alert"
                      style="background-color:#e8f4ff; border-color:#0d6efd;">
                      <div class="d-flex align-items-start">

                        <div class="me-3">
                          <iconify-icon icon="mdi:information-outline"
                            style="font-size: 2rem; color:#0d6efd;"></iconify-icon>
                        </div>

                        <div>
                          <h6 class="fw-bold text-primary mb-2">PENJELASAN SEP & INTEGRASI BPJS</h6>

                          <p class="mb-0" style="font-size: 0.9rem; color:#003366; line-height:1.55;">
                            <strong>Surat Eligibilitas Peserta (SEP)</strong> adalah dokumen resmi yang
                            digunakan untuk memvalidasi kepesertaan BPJS Kesehatan dalam setiap layanan medis.
                            SEP memastikan bahwa peserta memiliki hak layanan sesuai ketentuan yang berlaku.
                            <br><br>
                            Saat ini, proses unggah dokumen SEP digunakan untuk kebutuhan klaim sebelum adanya proses adanya <strong>Integrasi dengan BPJS Kesehatan</strong>. Apabila nantinya Faskes telah terintegrasi dengan BPJS Kesehatan, maka proses pembuatan SEP akan dilakukan secara otomatis melalui sistem kami, sehingga menghilangkan kebutuhan untuk mengunggah dokumen SEP secara manual.
                          </p>
                        </div>

                      </div>
                    </div>
                  </div>
                  <div class="text-end mt-2">
                    <div id="sep_preview_box" class="mt-2"></div>

                    <a href="module/admin/print/formulir_sep.php?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>" target="_blank">
                      <button class="btn btn-outline-primary">
                        <iconify-icon icon="mdi:printer-outline"></iconify-icon>
                        Cetak
                      </button>
                    </a>
                    <button class="btn btn-primary" id="openModal">
                      <iconify-icon icon="mdi:upload-outline"></iconify-icon>
                      Upload File SEP
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

  <?php
  require 'library.php';
  ?>
</body>

<script>
  document.addEventListener("DOMContentLoaded", () => {

    const no = "<?= $_GET['no'] ?>";
    const rm = "<?= $_GET['rm'] ?>";

    // ==========================================
    //  👉 FUNGSI UTAMA: LOAD SEP TANPA REFRESH
    // ==========================================
    function loadSEP() {

      fetch(`controller/sep/getSEP.php?no=${no}&rm=${rm}`)
        .then(r => r.json())
        .then(res => {

          if (res.status !== "success") return;

          const p = res.pasien;
          const sep = res.sep;
          const box = document.getElementById("sep_preview_box");

          // SET DATA PASIEN
          if (p) {
            document.getElementById("patient_name").value = p.patient_name;
            document.getElementById("patient_gender").value = p.patient_gender;
            document.getElementById("doctor_name").value = p.doctor_name;
            document.getElementById("usia").value = res.usia;
          }

          // =======================
          //  TAMPILKAN SEP JIKA ADA
          // =======================
          if (sep && sep.sep_file) {

            let fileURL = `uploads/sep/${sep.sep_file}`;

            box.innerHTML = `
                <div class="alert alert-success d-flex justify-content-between align-items-center">
                    <div>
                       <strong>File SEP sudah ada:</strong><br>
                       <small>${sep.sep_file}</small>
                    </div>

                    <div>
                       <a href="${fileURL}" target="_blank" class="btn btn-sm btn-info">
                          <iconify-icon icon="mdi:eye-outline"></iconify-icon>
                          Lihat
                       </a>

                       <button class="btn btn-sm btn-danger" id="deleteSEP">
                          <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                          Hapus
                       </button>
                    </div>
                </div>
            `;

            // ACTION DELETE
            document.getElementById("deleteSEP").onclick = () => {

              Swal.fire({
                title: "Hapus SEP?",
                text: "File SEP akan dihapus dari server!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Hapus"
              }).then(x => {

                if (!x.isConfirmed) return;

                fetch("controller/sep/deleteSEP.php", {
                    method: "POST",
                    headers: {
                      "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `no_visit=${no}&rm=${rm}`
                  })
                  .then(r => r.json())
                  .then(res => {

                    Swal.fire(res.status, res.message, res.status);

                    if (res.status === "success") {
                      loadSEP(); // ← REFRESH DATA TANPA REFRESH HALAMAN
                    }
                  });
              });
            };

          } else {
            box.innerHTML = `
                <div class="alert alert-warning">
                  <strong>Belum ada file SEP.</strong> Silakan upload.
                </div>`;
          }
        });
    }

    // MUAT DATA PERTAMA KALI
    loadSEP();


    // ==========================================
    //  👉 MODAL UPLOAD SEP (LIVE REFRESH)
    // ==========================================
    document.getElementById("openModal").addEventListener("click", () => {
      Swal.fire({
        title: "Upload File SEP",
        html: `<input type="file" id="sep_input" class="form-control mb-3">`,
        showCancelButton: true,
        confirmButtonText: "Upload",
        preConfirm: () => {
          let file = document.getElementById('sep_input').files[0];
          if (!file) {
            Swal.showValidationMessage("Silakan pilih file!");
            return false;
          }
          return file;
        }
      }).then(result => {

        if (!result.isConfirmed) return;
        let file = result.value;

        let form = new FormData();
        form.append("sep_file", file);
        form.append("id_patient", rm);
        form.append("no_visit", no);

        fetch("controller/sep/uploadSEP.php", {
            method: "POST",
            body: form
          })
          .then(r => r.json())
          .then(res => {

            Swal.fire(res.status, res.message, res.status);

            if (res.status === "success") {
              loadSEP(); // ← UPDATE PREVIEW TANPA REFRESH
            }
          });
      });
    });

  });
</script>

</html>