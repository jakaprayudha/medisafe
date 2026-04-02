<?php
$title = 'Form Masuk dan Keluar Pasien Rawat Inap';
$no = $_GET['no'];
$rm = $_GET['rm'];
require '../../database/connect.php';
require '../../controller/view.php';
$checkvisit = mysqli_query($koneksi, "SELECT * FROM pasien_visit INNER JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient INNER JOIN icd_10 ON icd_10.code = pasien_visit.diagnosa
INNER JOIN permintaan_ranap ON permintaan_ranap.visit_ID_inpatient = pasien_visit.visit_ID INNER JOIN ms_doctor ON ms_doctor.id_doctor = pasien_visit.id_doctor INNER JOIN ms_room ON ms_room.id_room = permintaan_ranap.id_room INNER JOIN ms_room_bed ON ms_room_bed.id_bed = permintaan_ranap.id_bed
 WHERE visit_ID='$no' AND nomor_rm='$rm'");
$dataio =  mysqli_fetch_array($checkvisit);
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
                  <h4 class="mb-3">Form Masuk dan Keluar Pasien Rawat Inap</h4>

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

                  <!-- FORM RAWAT INAP -->
                  <div class="row">

                    <div class="col-3 mb-3">
                      <label class="form-label">Tanggal Masuk</label>
                      <input type="date" id="tanggal_masuk" value="<?= $dataio['visit_date'] ?? '' ?>" class="form-control">
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Jam Masuk</label>
                      <input type="time" id="jam_masuk" value="<?= $dataio['visit_time'] ?? '' ?>" class="form-control">
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Penanggung Jawab</label>
                      <input type="text" id="penanggung_jawab" value="<?= $dataio['penanggung_jawab'] ?? '' ?>" class="form-control">
                    </div>


                    <div class="col-3 mb-3">
                      <label class="form-label">Tanggal Pindah</label>
                      <input type="date" id="tanggal_pindah" value="<?= $dataio['ranap_date'] ?? '' ?>" class="form-control">
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Jam Pindah</label>
                      <input type="time" id="jam_pindah" value="<?= $dataio['ranap_time'] ?? '' ?>" class="form-control">
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Ruang Rawat / Kelas</label>
                      <input type="text" id="ruang_rawat" value="<?= $dataio['room_name'] ?? '' ?> / <?= $dataio['bed_name'] ?? '' ?>" class="form-control bg-light" readonly>
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Tanggal Keluar</label>
                      <input type="date" id="tanggal_keluar" value="<?= $dataio['visit_date_out'] ?? '' ?>" class="form-control">
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Jam Keluar</label>
                      <input type="time" id="jam_keluar" value="<?= $dataio['visit_out'] ?? '' ?>" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Anamnesa Masuk</label>
                      <input type="text" id="diagnosa_medik" class="form-control" value="<?= $dataio['anamnesa'] ?? '' ?>">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Diagnosa Utama</label>
                      <input type="text" id="diagnosa_utama" class="form-control bg-light" readonly value="<?= $dataio['code']  ?? '' ?> - <?= $dataio['icd10'] ?>">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Diagnosa Sekunder</label>
                      <input type="text" id="diagnosa_komplikasi" class="form-control" value="<?= $dataio['diagnosa_sekunder'] ?? '' ?>">
                    </div>

                    <div class="col-12 mb-3">
                      <label class="form-label">Penyebab Cedera / Keracunan</label>
                      <textarea id="penyebab_keracunan" class="form-control" rows="2"><?= $dataio['penyebab_keracunan'] ?? '' ?></textarea>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Nama Operasi / Tindakan</label>
                      <input type="text" id="nama_operasi" class="form-control">
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Infeksi Nosokomial</label>
                      <select id="infeksi_nosokomial" class="form-control">
                        <option value="">-- Pilih --</option>
                        <option>Tidak</option>
                        <option>Ya</option>
                      </select>
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Penyebab Infeksi</label>
                      <input type="text" id="penyebab_infeksi" class="form-control">
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Alergi</label>
                      <input type="text" id="alergi" class="form-control">
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Radio Therapy</label>
                      <input type="text" id="radioterapi" class="form-control">
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Imunisasi</label>
                      <input type="text" id="imunisasi" class="form-control">
                    </div>

                    <div class="col-3 mb-3">
                      <label class="form-label">Transfusi Darah</label>
                      <input type="text" id="transfusi" class="form-control">
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Keadaan Pulang</label>
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
                      <label class="form-label">Dokter Merawat</label>
                      <input type="text" id="dokter_merawat" class="form-control bg-light" readonly value="<?= $dataio['doctor_name'] ?? '' ?>">
                    </div>
                    <div class="col-6 mb-3">
                      <label class="form-label">Lama Dirawat</label>
                      <input type="number" id="lama_dirawat" class="form-control">
                    </div>

                  </div>

                  <div class="text-end mt-3">
                    <a href="module/admin/print/formulir_inout_ranap?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
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

    // ===== GET DATA PASIEN + INAP =====
    fetch(`controller/ranap/getFormInap.php?no=${no}&rm=${rm}`)
      .then(r => r.json())
      .then(res => {

        if (!res || res.status !== "success") return;

        const p = res.pasien ?? {};
        const i = res.inap ?? {};

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
      "tanggal_masuk", "jam_masuk", "status_perkawinan", "penanggung_jawab",
      "alamat_pj", "tanggal_pindah", "jam_pindah", "ruang_rawat",
      "tanggal_keluar", "jam_keluar", "diagnosa_medik", "lama_dirawat",
      "diagnosa_utama", "diagnosa_komplikasi", "penyebab_keracunan",
      "nama_operasi", "infeksi_nosokomial", "penyebab_infeksi", "alergi",
      "radioterapi", "imunisasi", "transfusi", "keadaan_keluar",
      "cara_keluar", "dokter_merawat"
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

    fetch("controller/ranap/saveInap.php", {
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

  function hitungLamaDirawat() {
    const tglMasuk = document.getElementById("tanggal_masuk").value;
    const tglKeluar = document.getElementById("tanggal_keluar").value;

    if (!tglMasuk || !tglKeluar) return;

    const masuk = new Date(tglMasuk);
    const keluar = new Date(tglKeluar);

    // hitung selisih hari
    const selisih = Math.floor((keluar - masuk) / (1000 * 60 * 60 * 24));

    // minimal 1 hari kalau sudah masuk
    const lama = selisih >= 0 ? selisih + 1 : 0;

    document.getElementById("lama_dirawat").value = lama;
  }

  document.getElementById("tanggal_masuk").addEventListener("change", hitungLamaDirawat);
  document.getElementById("tanggal_keluar").addEventListener("change", hitungLamaDirawat);
</script>

</html>