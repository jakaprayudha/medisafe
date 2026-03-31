<?php
$title = 'Resume Medis';
$no = $_GET['no'];
$rm = $_GET['rm'];
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
                  <h4 class="mb-3">Resume Medis</h4>

                  <!-- IDENTITAS PASIEN -->
                  <div class="row">
                    <div class="col-3 mb-3">
                      <label class="form-label">Nama Pasien</label>
                      <input type="text" id="patient_name" class="form-control bg-light" readonly>
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Gender</label>
                      <input type="text" id="patient_gender" class="form-control bg-light" readonly>
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Usia</label>
                      <input type="text" id="usia" class="form-control bg-light" readonly>
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Dokter</label>
                      <input type="text" id="doctor_name" class="form-control bg-light" readonly>
                    </div>
                  </div>

                  <hr>

                  <!-- FORM RESUM MEDIS -->
                  <div class="row">

                    <div class="col-6 mb-3">
                      <label class="form-label">Diagnosa</label>
                      <textarea id="diagnosa" class="form-control" rows="6"></textarea>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Tindakan</label>
                      <textarea id="tindakan" class="form-control" rows="6"></textarea>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Pemeriksaan Penunjang</label>
                      <textarea id="pemeriksaan_penunjang" class="form-control" rows="6"></textarea>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Obat</label>
                      <textarea id="obat" class="form-control" rows="6"></textarea>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Instruksi</label>
                      <textarea id="instruksi" class="form-control" rows="6"></textarea>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Petugas</label>
                      <input type="text" id="petugas" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">DPJP</label>
                      <input type="text" id="dokter" class="form-control">
                    </div>

                  </div>

                  <div class="text-end mt-3">
                    <a href="module/admin/print/formulir_resume?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
                      <button class="btn btn-outline-primary">
                        <iconify-icon icon="mdi:printer-outline"></iconify-icon> Cetak
                      </button>
                    </a>
                    <button id="openModal" class="btn btn-primary">
                      <iconify-icon icon="mdi:content-save-outline"></iconify-icon> Simpan
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

    const url = new URLSearchParams(window.location.search);
    const no = url.get("no");
    const rm = url.get("rm");

    if (!no || !rm) return;

    // ===== GET DATA PASIEN + RESUME =====
    fetch(`controller/ranap/getResume?no=${no}&rm=${rm}`)
      .then(r => r.json())
      .then(res => {

        if (!res || res.status !== "success") return;

        const p = res.pasien ?? {};
        const i = res.resume ?? {};

        // Isi identitas pasien (aman walaupun null)
        if (document.getElementById("patient_name"))
          document.getElementById("patient_name").value = p.nama_pasien ?? "";

        if (document.getElementById("patient_gender"))
          document.getElementById("patient_gender").value = p.jk ?? "";

        if (document.getElementById("doctor_name"))
          document.getElementById("doctor_name").value = p.doctor_name ?? "";

        if (document.getElementById("dokter"))
          document.getElementById("dokter").value = p.doctor_name ?? "";

        if (document.getElementById("usia"))
          document.getElementById("usia").value = p.usia ?? "";

        // ===== EDIT MODE (Jika i ada) =====
        for (let key in i) {
          if (document.getElementById(key)) {
            document.getElementById(key).value = i[key] ?? "";
          }
        }

      })
      .catch(err => console.error("ERR GET:", err));

  });

  // =============== SAVE DATA RANAP ===============
  document.getElementById("openModal").addEventListener("click", () => {

    const fields = [
      "diagnosa", "tindakan", "pemeriksaan_penunjang", "obat", "instruksi", "petugas", "dokter"
    ];

    let data = {
      visit_ID: "<?= $_GET['no'] ?>",
      nomor_rm: "<?= $_GET['rm'] ?>",
    };

    // Auto ambil semua fields (aman walau ada yang tidak ditemukan)
    fields.forEach(f => {
      let el = document.getElementById(f);
      data[f] = el ? (el.value ?? "") : "";
    });

    fetch("controller/ranap/saveResumeMedis.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
      })
      .then(r => r.json())
      .then(res => {
        Swal.fire({
          icon: res.status,
          title: res.status === "success" ? "Berhasil" : "Gagal",
          text: res.message
        });
      })
      .catch(err => {
        alert("Terjadi error saat menyimpan!");
        console.error(err);
      });

  });
</script>

</html>