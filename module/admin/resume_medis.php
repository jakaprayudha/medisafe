<?php
$title = 'Resume Medis';
$no = $_GET['no'];
$rm = $_GET['rm'];
require '../../database/connect.php';
require '../../controller/view.php';
$checkvisit = mysqli_query($koneksi, "SELECT * FROM pasien_visit INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient INNER JOIN icd_10 ON icd_10.code = pasien_visit.diagnosa WHERE visit_ID='$no' AND nomor_rm='$rm'");
$dataresume =  mysqli_fetch_array($checkvisit);
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
                      <label class="form-label">Diagnosa Masuk</label>
                      <input type="text" class="form-control   bg-light" readonly value="<?= $dataresume['diagnosa'] ?? '' ?> - <?= $dataresume['icd10'] ?? '' ?>">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Indikasi Rawat Inap</label>
                      <input type="text" class="form-control bg-light" readonly value="<?= $dataresume['anamnesa'] ?? '' ?> ">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Diagnosa Utama (ICD-10)</label>
                      <select id="diagnosa_utama" name="diagnosa_utama" class="form-select"></select>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Diagnosa Sekunder (ICD-10)</label>
                      <select id="diagnosa_sekunder" name="diagnosa_sekunder[]" class="form-select" multiple></select>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Pemeriksaan Fisik</label>
                      <textarea id="pemeriksaan_fisik" name="pemeriksaan_fisik" class="form-control" rows="6"></textarea>
                    </div>
                    <?php
                    $terapi = '';
                    $gettiket = mysqli_query($koneksi, "SELECT * FROM permintaan_pharmacy WHERE id_visit='$no' AND status_obat_pulang=0");
                    while ($tiket = mysqli_fetch_assoc($gettiket)) {
                      $idvisit = $tiket['id_permintaan_farmasi'];
                      $getobat = mysqli_query($koneksi, "SELECT * FROM permintaan_pharmacy_details INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy_details.id_pharmacy WHERE id_permintaan_farmasi='$idvisit'");
                      while ($obat = mysqli_fetch_assoc($getobat)) {
                        $terapi .= "- {$obat['pharmacy_name_generic']} {$obat['qty']} {$obat['signa']}\n";
                      }
                    }
                    ?>
                    <div class="col-6 mb-3">
                      <label class="form-label">Terapi Selama Rawat Inap</label>
                      <textarea id="pemeriksaan_penunjang" class="form-control" rows="6"><?= $terapi ?></textarea>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Alergi Obat</label>
                      <textarea id="alergi_obat" class="form-control" rows="6" value="<?= $dataresume['alergi_obat'] ?? '' ?> "></textarea>
                    </div>
                    <?php
                    $no = $_GET['no'];
                    $terapipulang = '';
                    $gettiketpulang = mysqli_query($koneksi, "SELECT * FROM permintaan_pharmacy WHERE id_visit='$no' AND status_obat_pulang=1");
                    while ($tiket = mysqli_fetch_assoc($gettiketpulang)) {
                      $idvisit = $tiket['id_permintaan_farmasi'];
                      $getobat = mysqli_query($koneksi, "SELECT * FROM permintaan_pharmacy_details INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy_details.id_pharmacy WHERE id_permintaan_farmasi='$idvisit'");
                      while ($obat = mysqli_fetch_assoc($getobat)) {
                        $terapipulang .= "- {$obat['pharmacy_name_generic']} {$obat['qty']} {$obat['signa']}\n";
                      }
                    }
                    ?>
                    <div class="col-6 mb-3">
                      <label class="form-label">Terapi Pulang</label>
                      <textarea id="instruksi" class="form-control" rows="6"><?= $terapipulang ?></textarea>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Kondisi Pasien Saat Pulang</label>
                      <select name="kondisi_pulang" id="kondisi_pulang" class="form-select">
                        <option value="">PILIH</option>
                        <option value="Membaik">Membaik</option>
                        <option value="Rujuk">Rujuk</option>
                        <option value="Lemah">Lemah</option>
                        <option value="Lainnya">Lainnya</option>
                      </select>
                    </div>


                    <div class="col-6 mb-3">
                      <label class="form-label">Cara Keluar</label>
                      <select name="cara_keluar" id="cara_keluar" class="form-select">
                        <option value="">PILIH</option>
                        <option value="Lari">Lari</option>
                        <option value="Pulang">Pulang</option>
                        <option value="Paksa">Paksa</option>
                        <option value="Diizinkan Pulang">Diizinkan Pulang</option>
                        <option value="Lainnya">Lainnya</option>
                      </select>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Rencana Tindak Lanjut</label>
                      <textarea id="rencana_tindak_lanjut" name="rencana_tindak_lanjut" class="form-control" rows="6" value="<?= $dataresume['rencana_tindak_lanjut'] ?? '' ?> "></textarea>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">DPJP</label>
                      <input type="text" id="dokter" class="form-control bg-light" readonly>
                    </div>

                  </div>

                  <div class="text-end mt-3">
                    <a href="module/admin/print/formulir_resume_v2?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
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

<script>
  function initICD(selector, multiple = false) {
    $(selector).select2({
      width: '100%',
      placeholder: 'Cari diagnosa ICD-10...',
      minimumInputLength: 2,
      multiple: multiple,
      ajax: {
        url: 'controller/visit/getICD10.php',
        dataType: 'json',
        delay: 300,
        data: params => ({
          search: params.term
        }),
        processResults: data => ({
          results: data
        })
      }
    });
  }

  // utama = single
  initICD('#diagnosa_utama');

  // sekunder = multi 🔥
  initICD('#diagnosa_sekunder', true);
</script>

</html>