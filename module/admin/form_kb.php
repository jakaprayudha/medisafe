<?php
$title = 'Kartu Status Peserta KB';
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
                  <h4 class="mb-3">Kartu Status Peserta KB</h4>

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

                  <!-- FORM KARTU STATUS KB -->
                  <div class="row">

                    <div class="col-6 mb-3">
                      <label class="form-label">Nomor kode Faskes KB</label>
                      <input type="text" id="faskes_kode" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Kode Keluarga Indonesia</label>
                      <input type="text" id="kode_keluarga" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Nama Suami</label>
                      <input type="text" id="nama_suami" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Pendidikan Suami</label>
                      <input type="text" id="pendidikan_suami" class="form-control">
                    </div>


                    <div class="col-6 mb-3">
                      <label class="form-label">Pekerjaan Suami</label>
                      <input type="text" id="pekerjaan_suami" class="form-control">
                    </div>


                    <div class="col-3 mb-3">
                      <label class="form-label">Anak Hidup (Laki-laki)</label>
                      <input type="number" id="anak_lk" class="form-control" min="0">
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Anak Hidup (Perempuan)</label>
                      <input type="number" id="anak_pr" class="form-control" min="0">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Umur Anak Terakhir</label>
                      <input type="text" id="umur_anak_terakhir" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">KB Terakhir</label>
                      <input type="text" id="kb_terakhir" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Haid Terakhir</label>
                      <input type="date" required id="haid_terakhir" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Hamil</label>
                      <select name="hamil" id="hamil" class="form-select">
                        <option value="Tidak">Tidak</option>
                        <option value="Ya">Ya</option>
                      </select>
                    </div>

                    <div class="col-2 mb-3">
                      <label class="form-label">G (Gravida)</label>
                      <input type="number" id="gpa_g" class="form-control" min="0">
                    </div>

                    <div class="col-2 mb-3">
                      <label class="form-label">P (Partus)</label>
                      <input type="number" id="gpa_p" class="form-control" min="0">
                    </div>

                    <div class="col-2 mb-3">
                      <label class="form-label">A (Abortus)</label>
                      <input type="number" id="gpa_a" class="form-control" min="0">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Menyusui</label>
                      <select name="menyusui" id="menyusui" class="form-select">
                        <option value="Tidak">Tidak</option>
                        <option value="Ya">Ya</option>
                      </select>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Riwayat Penyakit</label>
                      <input type="text" id="riwayat_sakit" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Keaadaan Umum</label>
                      <input type="text" id="keadaan_umum" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Berat Badan</label>
                      <input type="number" id="berat_badan" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Tekanan Darah (mmHg)</label>
                      <input type="text" id="tekanan_darah" class="form-control" maxlength="7" placeholder="120/80">
                    </div>

                    <script>
                      document.getElementById('tekanan_darah').addEventListener('input', function(e) {
                        let v = e.target.value.replace(/[^0-9]/g, ''); // hanya angka
                        if (v.length > 3) {
                          e.target.value = v.slice(0, 3) + "/" + v.slice(3, 5);
                        } else {
                          e.target.value = v;
                        }
                      });
                    </script>

                    <div class="col-6 mb-3">
                      <label class="form-label">Pemeriksaan Tambahan</label>
                      <input type="text" id="pemeriksaan_tambahan" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Metode KB Yang Dipilih</label>
                      <input type="text" id="metode_pilihan" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Tanggal Dilayani</label>
                      <input type="date" id="tgl_dilayani" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Tanggal Dicabut</label>
                      <input type="date" id="tgl_dicabut" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Penanggung Jawab</label>
                      <input type="text" id="penanggung_jawab" class="form-control">
                    </div>
                  </div>

                  <div class="text-end mt-3">
                    <a href="module/admin/print/formulir_status_kb?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
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
  function autoSelect(id, value) {
    const el = document.getElementById(id);
    if (!el) return;

    const val = (value ?? "").toString().toLowerCase();

    for (const opt of el.options) {
      if (opt.value.toLowerCase() === val) {
        el.value = opt.value;
        return;
      }
    }
  }

  document.addEventListener("DOMContentLoaded", () => {

    const url = new URLSearchParams(window.location.search);
    const no = url.get("no");
    const rm = url.get("rm");

    if (!no || !rm) return;

    // ===== GET DATA PASIEN + INAP =====
    fetch(`controller/ranap/getFormStatusKB.php?no=${no}&rm=${rm}`)
      .then(r => r.json())
      .then(res => {

        if (!res || res.status !== "success") return;

        const p = res.pasien ?? {};
        const i = res.statuskb ?? {};

        // Isi identitas pasien (aman walaupun null)
        if (document.getElementById("patient_name"))
          document.getElementById("patient_name").value = p.nama_pasien ?? "";

        if (document.getElementById("patient_gender"))
          document.getElementById("patient_gender").value = p.jk ?? "";

        if (document.getElementById("doctor_name"))
          document.getElementById("doctor_name").value = p.doctor_name ?? "";

        if (document.getElementById("usia"))
          document.getElementById("usia").value = p.usia ?? "";

        // ===== EDIT MODE (Jika i ada) =====
        for (let key in i) {
          const el = document.getElementById(key);
          if (!el) continue;

          if (el.tagName === "SELECT") {
            autoSelect(key, i[key]);
          } else {
            el.value = i[key] ?? "";
          }
        }

      })
      .catch(err => console.error("ERR GET:", err));

  });

  // =============== SAVE DATA RANAP ===============
  document.getElementById("openModal").addEventListener("click", () => {

    const fields = [
      "faskes_kode", "kode_keluarga", "nama_suami", "pendidikan_suami",
      "pekerjaan_suami", "anak_lk", "anak_pr", "umur_anak_terakhir", "kb_terakhir",
      "haid_terakhir", "hamil", "gpa_g", "gpa_p", "gpa_a", "menyusui", "riwayat_sakit", "keadaan_umum",
      "berat_badan", "tekanan_darah", "pemeriksaan_tambahan", "metode_pilihan",
      "tgl_dilayani", "tgl_dicabut", "penanggung_jawab"
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

    fetch("controller/ranap/saveStatusKB.php", {
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