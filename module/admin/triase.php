<?php
$title = 'Triase Kegawatdaruratan';
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
                  <h4 class="mb-3">Triase Kegawatdaruratan</h4>

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

                  <!-- FORM TRIASE -->
                  <div class="card">
                    <div class="card-body">
                      <h4 class="mb-3">Form Triase Pasien</h4>

                      <div class="row">
                        <div class="col-3 mb-3">
                          <label class="form-label">Tanggal Masuk</label>
                          <input type="date" id="tanggal_masuk" class="form-control">
                        </div>

                        <div class="col-3 mb-3">
                          <label class="form-label">Jam Masuk</label>
                          <input type="time" id="jam_masuk" class="form-control">
                        </div>

                        <div class="col-6 mb-3">
                          <label class="form-label">Keluhan Utama</label>
                          <input type="text" id="keluhan_utama" class="form-control" placeholder="Contoh: Sesak napas, nyeri dada ...">
                        </div>
                      </div>

                      <h5 class="mt-3">Pemeriksaan Vital Sign</h5>
                      <div class="row">
                        <div class="col-2 mb-3">
                          <label class="form-label">Tekanan Darah</label>
                          <input type="text" id="tekanan_darah" class="form-control" placeholder="120/80">
                        </div>

                        <div class="col-2 mb-3">
                          <label class="form-label">Nadi (x/menit)</label>
                          <input type="number" id="nadi" class="form-control">
                        </div>

                        <div class="col-2 mb-3">
                          <label class="form-label">RR (x/menit)</label>
                          <input type="number" id="rr" class="form-control">
                        </div>

                        <div class="col-2 mb-3">
                          <label class="form-label">Suhu (°C)</label>
                          <input type="number" step="0.1" id="suhu" class="form-control">
                        </div>

                        <div class="col-2 mb-3">
                          <label class="form-label">SpO₂ (%)</label>
                          <input type="number" id="spo2" class="form-control">
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

                      <h5 class="mt-3">Kategori Triase</h5>
                      <select id="triase" class="form-select mb-3">
                        <option value="">-- Pilih --</option>
                        <option value="Merah">🔴 Merah – Gawat Darurat</option>
                        <option value="Kuning">🟡 Kuning – Darurat</option>
                        <option value="Hijau">🟢 Hijau – Tidak darurat</option>
                        <option value="Hitam">⚫ Hitam – Meninggal</option>
                      </select>

                      <!-- MERAH -->
                      <div id="box-merah" class="triase-box border rounded p-2 mb-3" style="display:none;">
                        <h6 class="text-danger fw-bold">🔴 Triase Merah</h6>
                        <div class="form-check"><input class="form-check-input merah" type="checkbox" value="Henti napas/jantung"> Henti napas/jantung</div>
                        <div class="form-check"><input class="form-check-input merah" type="checkbox" value="GCS ≤ 8"> GCS ≤ 8</div>
                        <div class="form-check"><input class="form-check-input merah" type="checkbox" value="Syok (TD < 90)"> Syok (TD < 90)</div>
                            <div class="form-check"><input class="form-check-input merah" type="checkbox" value="Perdarahan masif"> Perdarahan masif</div>
                        </div>

                        <!-- KUNING -->
                        <div id="box-kuning" class="triase-box border rounded p-2 mb-3" style="display:none;">
                          <h6 class="text-warning fw-bold">🟡 Triase Kuning</h6>
                          <div class="form-check"><input class="form-check-input kuning" type="checkbox" value="Sesak sedang"> Sesak sedang</div>
                          <div class="form-check"><input class="form-check-input kuning" type="checkbox" value="Suhu > 39°C"> Suhu > 39°C</div>
                          <div class="form-check"><input class="form-check-input kuning" type="checkbox" value="Nyeri sedang"> Nyeri sedang</div>
                        </div>

                        <!-- HIJAU -->
                        <div id="box-hijau" class="triase-box border rounded p-2 mb-3" style="display:none;">
                          <h6 class="text-success fw-bold">🟢 Triase Hijau</h6>
                          <div class="form-check"><input class="form-check-input hijau" type="checkbox" value="Keluhan ringan"> Keluhan ringan</div>
                          <div class="form-check"><input class="form-check-input hijau" type="checkbox" value="Nyeri ringan"> Nyeri ringan</div>
                        </div>

                        <!-- HITAM -->
                        <div id="box-hitam" class="triase-box border rounded p-2 mb-3" style="display:none;">
                          <h6 class="fw-bold">⚫ Triase Hitam</h6>
                          <div class="form-check"><input class="form-check-input hitam" type="checkbox" value="Meninggal di tempat"> Meninggal di tempat</div>
                          <div class="form-check"><input class="form-check-input hitam" type="checkbox" value="Tidak ada tanda kehidupan"> Tidak ada tanda kehidupan</div>
                        </div>

                        <input type="hidden" id="referensi_triase">

                        <div class="mb-3">
                          <label class="form-label">Catatan Tambahan</label>
                          <textarea id="catatan" rows="3" class="form-control"></textarea>
                        </div>
                      </div>
                    </div>

                    <div class="text-end mt-3">
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
  document.getElementById("triase").addEventListener("change", function() {

    // sembunyikan semua dulu
    document.querySelectorAll(".triase-box").forEach(box => box.style.display = "none");

    let kategori = this.value;

    if (kategori === "Merah") {
      document.getElementById("box-merah").style.display = "block";
    } else if (kategori === "Kuning") {
      document.getElementById("box-kuning").style.display = "block";
    } else if (kategori === "Hijau") {
      document.getElementById("box-hijau").style.display = "block";
    } else if (kategori === "Hitam") {
      document.getElementById("box-hitam").style.display = "block";
    }
  });
</script>
<script>
  function ambilChecklist() {
    let kategori = document.getElementById("triase").value;
    let list = [];

    document.querySelectorAll("." + kategori.toLowerCase() + ":checked").forEach(el => {
      list.push(el.value);
    });

    document.getElementById("referensi_triase").value = list.join(" | ");
  }

  function applyChecklist(ref, kategori) {
    if (!ref) return;

    // Pisahkan string referensi menjadi array
    let items = ref.split(" | ").map(i => i.trim());

    // Aktifkan box kategori triase
    document.getElementById("triase").value = kategori;
    document.getElementById("triase").dispatchEvent(new Event("change"));

    // Centang checkbox yang sesuai
    items.forEach(val => {
      document.querySelectorAll("." + kategori.toLowerCase()).forEach(cb => {
        if (cb.value.trim() === val) {
          cb.checked = true;
        }
      });
    });
  }

  document.addEventListener("DOMContentLoaded", () => {

    const url = new URLSearchParams(window.location.search);
    const no = url.get("no");
    const rm = url.get("rm");

    if (!no || !rm) return;

    // ===== GET DATA PASIEN + INAP =====
    fetch(`controller/ranap/getFormTriase.php?no=${no}&rm=${rm}`)
      .then(r => r.json())
      .then(res => {

        if (!res || res.status !== "success") return;

        const p = res.pasien ?? {};
        const i = res.triase ?? {};

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
        // ===== EDIT MODE (Jika i ada) =====
        for (let key in i) {
          if (document.getElementById(key)) {
            document.getElementById(key).value = i[key] ?? "";
          }
        }

        // Jalankan auto-centang checkbox jika ada triase
        if (i.triase && i.referensi_triase) {
          applyChecklist(i.referensi_triase, i.triase);
        }

      })
      .catch(err => console.error("ERR GET:", err));

  });

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
</script>

</html>