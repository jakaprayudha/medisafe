<?php
$title = 'Triase Kegawatdaruratan';
$no = $_GET['no'];
$rm = $_GET['rm'];
require '../../controller/view.php';
$checkrawatinap = mysqli_query($koneksi, "SELECT permintaan_ranap.*, pasien_visit.*, ms_patient.nomor_rm, ms_patient.id_patient FROM permintaan_ranap INNER JOIN ms_patient ON ms_patient.id_patient = permintaan_ranap.id_patient INNER JOIN pasien_visit ON pasien_visit.visit_ID = permintaan_ranap.visit_ID_inpatient WHERE permintaan_ranap.visit_ID_inpatient = '$no' AND ms_patient.nomor_rm = '$rm' ");
$datarawapinap = mysqli_fetch_array($checkrawatinap);
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
                  <h4 class="mb-3">Triase Kegawatdaruratan</h4>
                  <hr>
                  <?php
                  require 'card-pasien.php';
                  ?>

                  <!-- FORM TRIASE -->
                  <div class="card">
                    <div class="card-body">
                      <h4 class="mb-3">Form Triase Pasien</h4>

                      <div class="row">
                        <div class="col-3 mb-3">
                          <label class="form-label">Tanggal Masuk</label>
                          <input type="date" value="<?= $datarawapinap['ranap_date'] ?>" id="tanggal_masuk" class="form-control">
                        </div>

                        <div class="col-3 mb-3">
                          <label class="form-label">Jam Masuk</label>
                          <input type="time" value="<?= $datarawapinap['ranap_time'] ?>" id="jam_masuk" class="form-control">
                        </div>

                        <div class="col-6 mb-3">
                          <label class="form-label">Keluhan Utama</label>
                          <input type="text" id="keluhan_utama" class="form-control" value="<?= $datarawapinap['anamnesa'] ?>">
                        </div>

                        <div class="col-3 mb-3">
                          <label class="form-label">Anamnesis</label>
                          <select name="anamnesa_choice" class="form-select" id="anamnesa_choice">
                            <option value="Auto Anamnesa">Auto Anamnesa</option>
                            <option value="Allo Anamnesa">Allo Anamnesa</option>
                          </select>
                        </div>
                      </div>

                      <h5 class="mt-3">Pemeriksaan Vital Sign</h5>
                      <div class="row">
                        <div class="col-md-4">
                          <label class="form-label">
                            Tekanan Darah (mmHg) <span class="text-danger">*</span>
                          </label>

                          <div class="d-flex gap-2">
                            <input
                              type="number"
                              id="sistolik"
                              class="form-control bg-light" readonly
                              placeholder="Sistolik"
                              required>

                            <span class="align-self-center">/</span>

                            <input
                              type="number"
                              id="diastolik"
                              class="form-control bg-light" readonly
                              placeholder="Diastolik"
                              required>
                          </div>

                          <!-- hidden tetap dipakai -->
                          <input
                            type="hidden"
                            id="tekanan_darah"
                            name="tekanan_darah"
                            value="<?= @$datarawapinap['tekanan_darah'] ?>">
                        </div>
                        <div class="col-2 mb-3">
                          <label class="form-label">Nadi (x/menit)</label>
                          <input type="number" id="nadi" value="<?= $datarawapinap['nadi'] ?>" class="form-control bg-light" readonly>
                        </div>

                        <div class="col-2 mb-3">
                          <label class="form-label">RR (x/menit)</label>
                          <input type="number" id="rr" value="<?= $datarawapinap['respirasi'] ?>" class="form-control bg-light" readonly">
                        </div>

                        <div class="col-2 mb-3">
                          <label class="form-label">Suhu (°C)</label>
                          <input type="number" step="0.1" id="suhu" value="<?= $datarawapinap['suhu'] ?>" class="form-control bg-light" readonly">
                        </div>

                        <div class="col-2 mb-3">
                          <label class="form-label">Saturasi SpO₂ (%)</label>
                          <input type="number" id="spo2" value="<?= $datarawapinap['saturasi'] ?>" class="form-control bg-light" readonly>
                        </div>
                      </div>

                      <h5 class="mt-3">GCS</h5>
                      <div class="row">
                        <div class="col-2 mb-3">
                          <label>Mata (E)</label>
                          <select id="gcs_e" class="form-select">
                            <option value="4">4 - Spontan</option>
                            <option value="3">3 - Suara</option>
                            <option value="2">2 - Nyeri</option>
                            <option value="1">1 - Tidak ada</option>
                          </select>
                        </div>

                        <div class="col-2 mb-3">
                          <label>Verbal (V)</label>
                          <select id="gcs_v" class="form-select">
                            <option value="5">5 - Orientasi baik</option>
                            <option value="4">4 - Bingung</option>
                            <option value="3">3 - Kata tidak tepat</option>
                            <option value="2">2 - Suara tidak jelas</option>
                            <option value="1">1 - Tidak ada</option>
                          </select>
                        </div>

                        <div class="col-2 mb-3">
                          <label>Motorik (M)</label>
                          <select id="gcs_m" class="form-select">
                            <option value="6">6 - Perintah dipatuhi</option>
                            <option value="5">5 - Lokalisir nyeri</option>
                            <option value="4">4 - Tarikan dari nyeri</option>
                            <option value="3">3 - Fleksi abnormal</option>
                            <option value="2">2 - Ekstensi abnormal</option>
                            <option value="1">1 - Tidak ada</option>
                          </select>
                        </div>

                        <div class="col-2 mb-3">
                          <label>Total GCS</label>
                          <input type="text" id="total_gcs" class="form-control" readonly>
                        </div>
                      </div>

                      <script>
                        function hitungGCS() {
                          let e = parseInt(document.getElementById("gcs_e").value);
                          let v = parseInt(document.getElementById("gcs_v").value);
                          let m = parseInt(document.getElementById("gcs_m").value);
                          document.getElementById("total_gcs").value = e + v + m;
                        }

                        document.querySelectorAll("#gcs_e, #gcs_v, #gcs_m").forEach(el => {
                          el.addEventListener("change", hitungGCS);
                        });

                        hitungGCS();
                      </script>

                      <h5 class="mt-3">Skala Nyeri</h5>
                      <div class="row align-items-center mb-3">
                        <div class="col-10">
                          <input type="range" min="0" max="10" id="nyeri" class="form-range">
                        </div>
                        <div class="col-2">
                          <input type="text" id="nyeri_value" class="form-control" readonly>
                        </div>
                      </div>

                      <script>
                        let nyeri = document.getElementById("nyeri");
                        let nyeri_value = document.getElementById("nyeri_value");

                        nyeri.addEventListener("input", () => {
                          nyeri_value.value = nyeri.value;
                        });

                        nyeri_value.value = nyeri.value;
                      </script>

                      <h5 class="mt-3">Kategori Triase (SAAN Non Psikiatri)</h5>
                      <select id="triase" class="form-select mb-3">
                        <option value="">-- Pilih ATS --</option>
                        <option value="ATS 1">🔴 ATS 1 - Resusitasi</option>
                        <option value="ATS 2">🟡 ATS 2 - Emergensi</option>
                        <option value="ATS 3">🟢 ATS 3 - Urgensi</option>
                        <option value="ATS 4">⚪ ATS 4 - Non-Urgen</option>
                      </select>

                      <!-- ============================= -->
                      <!-- ATS 1 -->
                      <!-- ============================= -->
                      <div id="box-ats1" class="triase-box border rounded p-2 mb-3" style="display:none;">
                        <h6 class="fw-bold text-danger">🔴 ATS 1 (Resusitasi)</h6>

                        <label class="fw-bold mt-2">A. Airway</label>
                        <div class="form-check"><input class="form-check-input ats1" type="checkbox" value="Sumbatan Jalan Nafas"> Sumbatan Jalan Nafas</div>

                        <label class="fw-bold mt-2">B. Breathing</label>
                        <div class="form-check"><input class="form-check-input ats1" type="checkbox" value="Henti Nafas"> Henti Nafas</div>

                        <label class="fw-bold mt-2">C. Circulation</label>
                        <div class="form-check"><input class="form-check-input ats1" type="checkbox" value="Henti Jantung"> Henti Jantung</div>

                        <label class="fw-bold mt-2">D. Disability</label>
                        <div class="form-check"><input class="form-check-input ats1" type="checkbox" value="Nyeri berat tidak respon obat"> Nyeri berat tidak respon obat</div>

                        <label class="fw-bold mt-2">E. Exposure</label>
                        <div class="form-check"><input class="form-check-input ats1" type="checkbox" value="Kejang berkelanjutan"> Kejang berkelanjutan</div>

                        <label class="fw-bold mt-2">F. Psikiatri</label>
                        <div class="form-check"><input class="form-check-input ats1" type="checkbox" value="Gangguan perilaku berat mengancam diri & orang lain"> Gangguan perilaku berat mengancam diri & orang lain</div>
                      </div>



                      <!-- ============================= -->
                      <!-- ATS 2 -->
                      <!-- ============================= -->
                      <div id="box-ats2" class="triase-box border rounded p-2 mb-3" style="display:none;">
                        <h6 class="fw-bold text-warning">🟡 ATS 2 (Emergensi)</h6>

                        <label class="fw-bold mt-2">A. Airway</label>
                        <div class="form-check"><input class="form-check-input ats2" type="checkbox" value="Tidak ada sumbatan"> Tidak ada sumbatan</div>

                        <label class="fw-bold mt-2">B. Breathing</label>
                        <div class="form-check"><input class="form-check-input ats2" type="checkbox" value="RR < 10 x/menit, distress napas berat"> RR < 10 x/menit, distress napas berat</div>

                            <label class="fw-bold mt-2">C. Circulation</label>
                            <div class="form-check"><input class="form-check-input ats2" type="checkbox" value=" Sistolik < 80 mmHg"> Sistolik < 80 mmHg</div>

                                <label class="fw-bold mt-2">D. Disability</label>
                                <div class="form-check"><input class="form-check-input ats2" type="checkbox" value="Nyeri sedang"> Nyeri sedang</div>

                                <label class="fw-bold mt-2">E. Exposure</label>
                                <div class="form-check"><input class="form-check-input ats2" type="checkbox" value="Nyeri dada tipikal"> Nyeri dada tipikal</div>
                                <div class="form-check"><input class="form-check-input ats2" type="checkbox" value="Defisit neurologis"> Defisit Neurologis</div>

                                <label class="fw-bold mt-2">F. Psikiatri</label>
                                <div class="form-check"><input class="form-check-input ats2" type="checkbox" value="Datang dengan restrain"> Datang dengan restrain</div>
                            </div>



                            <!-- ============================= -->
                            <!-- ATS 3 -->
                            <!-- ============================= -->
                            <div id="box-ats3" class="triase-box border rounded p-2 mb-3" style="display:none;">
                              <h6 class="fw-bold text-success">🟢 ATS 3 (Urgensi)</h6>

                              <label class="fw-bold mt-2">A. Airway</label>
                              <div class="form-check"><input class="form-check-input ats3" type="checkbox" value="Tidak ada sumbatan"> Tidak ada sumbatan</div>

                              <label class="fw-bold mt-2">B. Breathing</label>
                              <div class="form-check"><input class="form-check-input ats3" type="checkbox" value="Takipnea / distress sedang"> Takipnea / distress sedang</div>

                              <label class="fw-bold mt-2">C. Circulation</label>
                              <div class="form-check"><input class="form-check-input ats3" type="checkbox" value="Gangguan sirkulasi (akral dingin, nadi <50 atau>150)"> Gangguan sirkulasi (akral dingin, nadi <50 atau>150)</div>

                              <label class="fw-bold mt-2">D. Disability</label>
                              <div class="form-check"><input class="form-check-input ats3" type="checkbox" value="Cedera kepala ringan"> Cedera kepala ringan</div>

                              <label class="fw-bold mt-2">E. Exposure</label>
                              <div class="form-check"><input class="form-check-input ats3" type="checkbox" value="Nyeri hebat"> Nyeri hebat</div>
                              <div class="form-check"><input class="form-check-input ats3" type="checkbox" value="Multiple trauma"> Multiple trauma</div>

                              <label class="fw-bold mt-2">F. Psikiatri</label>
                              <div class="form-check"><input class="form-check-input ats3" type="checkbox" value="Agresif fisik"> Agresif secara fisik</div>
                              <div class="form-check"><input class="form-check-input ats3" type="checkbox" value="Ancaman bunuh diri"> Ancaman bunuh diri</div>
                            </div>



                            <!-- ============================= -->
                            <!-- ATS 4 -->
                            <!-- ============================= -->
                            <div id="box-ats4" class="triase-box border rounded p-2 mb-3" style="display:none;">
                              <h6 class="fw-bold text-dark">⚪ ATS 4 (Non Urgen)</h6>

                              <label class="fw-bold mt-2">A. Airway</label>
                              <div class="form-check"><input class="form-check-input ats4" type="checkbox" value="Tidak ada sumbatan"> Tidak ada sumbatan</div>

                              <label class="fw-bold mt-2">B. Breathing</label>
                              <div class="form-check"><input class="form-check-input ats4" type="checkbox" value="Dipsnea ringan"> Dipsnea ringan</div>

                              <label class="fw-bold mt-2">C. Circulation</label>
                              <div class="form-check"><input class="form-check-input ats4" type="checkbox" value="Muntah / diare tanda dehidrasi ringan"> Muntah / diare tanda dehidrasi</div>

                              <label class="fw-bold mt-2">E. Exposure</label>
                              <div class="form-check"><input class="form-check-input ats4" type="checkbox" value="Luka kecil"> Luka kecil</div>

                              <label class="fw-bold mt-2">F. Psikiatri</label>
                              <div class="form-check"><input class="form-check-input ats4" type="checkbox" value="Keluhan minor"> Keluhan minor</div>
                            </div>

                            <input type="hidden" id="referensi_triase">

                            <div class="mb-3">
                              <label class="form-label">Catatan Tambahan</label>
                              <textarea id="catatan" rows="3" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                          <button class="btn btn-light" onclick="window.history.back()">
                            <iconify-icon icon="mdi:arrow-left"></iconify-icon>
                            Kembail
                          </button>
                          <a href="module/admin/print/formulir_triase?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
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
  document.addEventListener("DOMContentLoaded", function() {
    loadTriase();
    const td = document.getElementById("tekanan_darah").value;

    if (td && td.includes("/")) {
      const [s, d] = td.split("/");

      document.getElementById("sistolik").value = s;
      document.getElementById("diastolik").value = d;
    }

    const $diagnosa = $('#diagnosa');

    // destroy kalau sudah ada
    if ($diagnosa.hasClass("select2-hidden-accessible")) {
      $diagnosa.select2('destroy');
    }

    // init select2 ICD
    $diagnosa.select2({
      width: '100%',
      placeholder: 'Cari diagnosa ICD-10...',
      minimumInputLength: 2,
      ajax: {
        url: 'controller/visit/getICD10.php',
        type: 'GET',
        dataType: 'json',
        delay: 300,
        data: function(params) {
          return {
            search: params.term
          };
        },
        processResults: function(data) {
          return {
            results: data
          };
        }
      }
    });

    const existingDiagnosa = `<?= @$data['diagnosa'] ?>`; // misal A00

    if (existingDiagnosa) {

      fetch(`controller/visit/getICD10.php?search=${existingDiagnosa}`)
        .then(res => res.json())
        .then(data => {

          const item = data.find(d => d.id === existingDiagnosa);

          if (item) {
            const option = new Option(item.text, item.id, true, true);
            $('#diagnosa').append(option).trigger('change');
          }

        });

    }
  });

  document.getElementById("formPemeriksaan").addEventListener("submit", function(e) {

    const s = document.getElementById("sistolik").value;
    const d = document.getElementById("diastolik").value;

    if (!s || !d) {
      alert("Tekanan darah wajib diisi!");
      e.preventDefault();
      return;
    }

    const td = `${s}/${d}`;

    // 🔥 INI KUNCI
    document.getElementById("tekanan_darah").value = td;

    console.log("TD DIKIRIM:", td); // debug
  });
</script>

<script>
  /* ==========================================
   SHOW / HIDE BOX TRIASE (ATS)
========================================== */
  document.getElementById("triase").addEventListener("change", function() {

    document.querySelectorAll(".triase-box").forEach(box => box.style.display = "none");

    let kategori = this.value; // "ATS 1"
    let idBox = "box-" + kategori.replace(" ", "").toLowerCase(); // box-ats1

    let box = document.getElementById(idBox);
    if (box) box.style.display = "block";
  });


  /* ==========================================
     AMBIL CHECKLIST (SAAT SIMPAN)
  ========================================== */
  function ambilChecklist() {
    let kategori = document.getElementById("triase").value;
    if (!kategori) return "";

    let className = kategori.replace(" ", "").toLowerCase(); // ats1 / ats2 / ats3 / ats4
    let list = [];

    document.querySelectorAll("." + className + ":checked").forEach(el => {
      list.push(el.value);
    });
    document.getElementById("referensi_triase").value = JSON.stringify(list);
  }


  /* ==========================================
     APPLY CHECKLIST (SAAT EDIT)
  ========================================== */
  function applyChecklist(refString, kategori) {

    if (!refString || !kategori) return;

    let items = [];

    try {
      items = JSON.parse(refString);
    } catch (e) {
      items = [];
    }

    // set ATS
    document.getElementById("triase").value = kategori;
    document.getElementById("triase").dispatchEvent(new Event("change"));

    let className = kategori.replace(" ", "").toLowerCase();

    document.querySelectorAll("." + className).forEach(cb => {
      cb.checked = items.includes(cb.value.trim());
    });
  }


  // =============== SAVE DATA RANAP ===============
  document.getElementById("openModal").addEventListener("click", () => {

    // AMBIL DULU CHECKBOX YANG DICENTANG
    ambilChecklist();

    const fields = [
      "tanggal_masuk",
      "jam_masuk",
      "keluhan_utama",

      // VITAL SIGN
      "tekanan_darah",
      "nadi",
      "rr",
      "suhu",
      "spo2",

      // GCS
      "gcs_e",
      "gcs_v",
      "gcs_m",
      "total_gcs",

      // NYERI
      "nyeri",
      "nyeri_value",

      // TRIASE
      "triase",
      "referensi_triase", // hasil checklist
      "anamnesa_choice",

      // CATATAN
      "catatan"
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

    fetch("controller/ranap/saveTriase.php", {
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

  function loadTriase() {

    const no = "<?= $_GET['no'] ?>";

    fetch(`controller/ranap/getTriase.php?no=${no}`)
      .then(res => res.json())
      .then(res => {

        if (res.status !== 'success' || !res.data) return;

        let d = res.data;

        // =========================
        // INPUT BIASA
        // =========================
        document.getElementById("keluhan_utama").value = d.keluhan_utama ?? '';
        document.getElementById("catatan").value = d.catatan ?? '';

        // =========================
        // DROPDOWN
        // =========================
        if (d.anamnesa_choice) {
          document.getElementById("anamnesa_choice").value = d.anamnesa_choice;
        }

        if (d.triase) {
          document.getElementById("triase").value = d.triase;
          document.getElementById("triase").dispatchEvent(new Event("change"));
        }

        // =========================
        // GCS
        // =========================
        document.getElementById("gcs_e").value = d.gcs_e ?? 4;
        document.getElementById("gcs_v").value = d.gcs_v ?? 5;
        document.getElementById("gcs_m").value = d.gcs_m ?? 6;
        // ✅ BENAR
        document.getElementById("total_gcs").value = d.gcs_total ?? '';

        // =========================
        // NYERI (SLIDER)
        // =========================
        // ✅ FIX
        if (d.skala_nyeri !== null) {
          document.getElementById("nyeri").value = d.skala_nyeri;
          document.getElementById("nyeri_value").value = d.skala_nyeri;
        }

        // =========================
        // CHECKBOX ATS
        // =========================
        // 🔥 HARUS SEBELUM applyChecklist
        document.getElementById("triase").value = d.triase;
        document.getElementById("triase").dispatchEvent(new Event("change"));
        // baru
        applyChecklist(d.referensi_triase, d.triase);

      });
  }
</script>

</html>