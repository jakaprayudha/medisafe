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
                  <?php
                  require 'card-pasien.php';
                  ?>

                  <hr>

                  <!-- FORM RESUM MEDIS -->
                  <div class="row">

                    <div class="col-6 mb-3">
                      <label class="form-label">Tanggal Pulang</label>
                      <input type="date" class="form-control" required value="">
                    </div>

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

                    // 🔥 langsung join semua (tidak perlu looping 2x)
                    $query = mysqli_query($koneksi, "
   SELECT 
      mp.pharmacy_name_generic,
      pd.qty,
      pd.signa
   FROM permintaan_pharmacy pp
   JOIN permintaan_pharmacy_details pd 
      ON pd.id_permintaan_farmasi = pp.id_permintaan_farmasi
   JOIN ms_pharmacy mp 
      ON mp.id_pharmacy = pd.id_pharmacy
   WHERE pp.id_visit = '$no'
   AND pp.status_obat_pulang = 0
");

                    while ($obat = mysqli_fetch_assoc($query)) {
                      $terapi .= "- {$obat['pharmacy_name_generic']} {$obat['qty']} {$obat['signa']}\n";
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
                      <label class="form-label">Rencana Tindak Lanjut</label>
                      <textarea id="rencana_tindak_lanjut" name="rencana_tindak_lanjut" class="form-control" rows="6" value="<?= $dataresume['rencana_tindak_lanjut'] ?? '' ?> "></textarea>
                    </div>

                    <div class="col-6 mb-3">
                      <label class="form-label">Kondisi Pasien Saat Pulang</label>
                      <select name="kondisi_pulang" id="kondisi_pulang" class="form-select">
                        <option value="">PILIH</option>
                        <option value="Sembuh">Sembuh</option>
                        <option value="Rujuk">Rujuk</option>
                        <option value="Lemah">Lemah</option>
                        <option value="Meninggal">Meninggal</option>
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


                    <!-- 
                    <div class="col-6 mb-3">
                      <label class="form-label">DPJP</label>
                      <input type="text" id="dokter" class="form-control bg-light" readonly>
                    </div> -->

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
  function extractCode(text) {
    if (!text) return "";
    const parts = text.split(" - ");
    return parts[0].trim();
  }

  function normalizeList(value) {
    if (!value) return [];
    if (Array.isArray(value)) return value;
    return value
      .split(";")
      .map(v => v.trim())
      .filter(Boolean);
  }

  function setSelect2Single(selector, storedText) {
    if (!storedText) return;
    const code = extractCode(storedText) || storedText;
    const option = new Option(storedText, code, true, true);
    $(selector).append(option).trigger('change');
  }

  function setSelect2Multiple(selector, storedText) {
    const items = normalizeList(storedText);
    if (!items.length) return;
    items.forEach(text => {
      const code = extractCode(text) || text;
      const option = new Option(text, code, true, true);
      $(selector).append(option);
    });
    $(selector).trigger('change');
  }

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

        // ===== SET DATA DARI resume_medis =====
        for (let key in i) {
          if (document.getElementById(key)) {
            document.getElementById(key).value = i[key] ?? "";
          }
        }

        // ===== SET DATA DARI pasien_visit =====
        if (document.getElementById("pemeriksaan_fisik"))
          document.getElementById("pemeriksaan_fisik").value = p.pemeriksaan_fisik ?? "";

        if (document.getElementById("alergi_obat"))
          document.getElementById("alergi_obat").value = p.alergi_obat ?? "";

        if (document.getElementById("kondisi_pulang"))
          document.getElementById("kondisi_pulang").value = p.kondisi_pulang ?? "";

        if (document.getElementById("cara_keluar"))
          document.getElementById("cara_keluar").value = p.cara_keluar ?? "";

        if (document.getElementById("rencana_tindak_lanjut"))
          document.getElementById("rencana_tindak_lanjut").value = p.rencana_tindak_lanjut ?? "";

        setSelect2Single('#diagnosa_utama', p.diagnosa_utama ?? "");
        setSelect2Multiple('#diagnosa_sekunder', p.diagnosa_sekunder ?? "");

      })
      .catch(err => console.error("ERR GET:", err));

  });

  // =============== SAVE DATA RANAP ===============
  document.getElementById("openModal").addEventListener("click", () => {

    const diagUtamaData = $('#diagnosa_utama').select2('data') || [];
    const diagUtamaText = diagUtamaData.length ? (diagUtamaData[0].text || "") : "";
    const diagUtamaVal = diagUtamaData.length ? (diagUtamaData[0].id || "") : "";

    const diagSekunderData = $('#diagnosa_sekunder').select2('data') || [];
    const diagSekunderText = diagSekunderData
      .map(item => item.text || "")
      .filter(Boolean)
      .join('; ');
    const diagSekunderVal = diagSekunderData
      .map(item => item.id || "")
      .filter(Boolean)
      .join(',');

    const diagnosaMasuk = document.getElementById("diagnosa_masuk")?.value ?? "";
    const diagnosaText = diagUtamaText || diagSekunderText ?
      `${diagUtamaText}${diagSekunderText ? ' | ' + diagSekunderText : ''}` :
      diagnosaMasuk;

    let data = {
      visit_ID: "<?= $_GET['no'] ?>",
      nomor_rm: "<?= $_GET['rm'] ?>",
    };

    data.diagnosa_utama = diagUtamaVal;
    data.diagnosa_utama_text = diagUtamaText;
    data.diagnosa_sekunder = diagSekunderVal;
    data.diagnosa_sekunder_text = diagSekunderText;
    data.diagnosa = diagnosaText;
    data.pemeriksaan_fisik = document.getElementById("pemeriksaan_fisik")?.value ?? "";
    data.pemeriksaan_penunjang = document.getElementById("pemeriksaan_penunjang")?.value ?? "";
    data.alergi_obat = document.getElementById("alergi_obat")?.value ?? "";
    data.instruksi = document.getElementById("instruksi")?.value ?? "";
    data.kondisi_pulang = document.getElementById("kondisi_pulang")?.value ?? "";
    data.cara_keluar = document.getElementById("cara_keluar")?.value ?? "";
    data.rencana_tindak_lanjut = document.getElementById("rencana_tindak_lanjut")?.value ?? "";
    data.id_doctor = document.getElementById("dokter")?.value ?? "";
    data.tindakan = data.pemeriksaan_fisik;
    data.obat = data.alergi_obat;

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