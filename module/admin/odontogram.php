<?php
$title = 'Odontogram';
require '../../controller/view.php';

?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    .odontogram {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .row-gigi {
      display: flex;
      justify-content: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .gigi {
      width: 50px;
      height: 60px;
      border: 1px solid #ccc;
      border-radius: 6px;
      text-align: center;
      cursor: pointer;
      transition: 0.2s;
      background: #fff;
      position: relative;
    }

    .gigi:hover {
      background: #e6f7ff;
      border-color: #1890ff;
    }

    .nomor {
      font-size: 12px;
      font-weight: bold;
      margin-top: 4px;
    }

    .gigi-box {
      width: 30px;
      height: 30px;
      border: 1px solid #999;
      margin: 5px auto;
    }
  </style>
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
            <div class="col-12">
              <?php
              require 'card-pasien.php';
              ?>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Odontogram</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/print/print_all_resep?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>" target="_blank">
                        <button class="btn btn-light"><i class="fas fa-print"></i> Print</button>
                      </a>
                    </div>
                  </div>



                  <div class="row">

                    <!-- 🦷 ODONTOGRAM FULL -->
                    <div class="col-12">
                      <div class="card mb-3">
                        <div class="card-body">

                          <div class="odontogram">

                            <!-- Atas -->
                            <div class="row-gigi">
                              <?php
                              foreach ([18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28] as $g) {
                                echo "<div class='gigi' onclick='pilihGigi($g, this)'>
                      <div class='nomor'>$g</div>
                      <div class='gigi-box'></div>
                    </div>";
                              }
                              ?>
                            </div>

                            <!-- Tengah -->
                            <div class="row-gigi">
                              <?php
                              foreach ([55, 54, 53, 52, 51, 61, 62, 63, 64, 65] as $g) {
                                echo "<div class='gigi' onclick='pilihGigi($g, this)'>
                      <div class='nomor'>$g</div>
                      <div class='gigi-box'></div>
                    </div>";
                              }
                              ?>
                            </div>

                            <div class="row-gigi">
                              <?php
                              foreach ([85, 84, 83, 82, 81, 71, 72, 73, 74, 75] as $g) {
                                echo "<div class='gigi' onclick='pilihGigi($g, this)'>
                      <div class='nomor'>$g</div>
                      <div class='gigi-box'></div>
                    </div>";
                              }
                              ?>
                            </div>

                            <!-- Bawah -->
                            <div class="row-gigi">
                              <?php
                              foreach ([48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38] as $g) {
                                echo "<div class='gigi' onclick='pilihGigi($g, this)'>
                      <div class='nomor'>$g</div>
                      <div class='gigi-box'></div>
                    </div>";
                              }
                              ?>
                            </div>

                          </div>

                        </div>
                      </div>
                    </div>


                    <!-- 🧾 FORM (KIRI) -->
                    <div class="col-md-4">
                      <div class="card">
                        <div class="card-body">

                          <h6>Input Data</h6>

                          <div class="mb-3">
                            <label>Elemen</label>
                            <select class="form-control" id="elemen">
                              <option value="">---Pilih---</option>
                              <option value="mahkota">Mahkota</option>
                              <option value="akar">Akar</option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label>Elemen Gigi</label>
                            <select class="form-control" id="elemen_gigi">
                              <option value="">---Pilih---</option>
                              <option value="mesial">Mesial</option>
                              <option value="distal">Distal</option>
                              <option value="oklusal">Oklusal</option>
                              <option value="lingual">Lingual</option>
                              <option value="bukal">Bukal</option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label>Diagnosa</label>
                            <input type="text" class="form-control" id="diagnosa">
                          </div>

                          <div class="mb-3">
                            <label>Prosedur</label>
                            <select class="form-control" id="prosedur">
                              <option value="">---</option>
                              <option value="tambal">Tambal</option>
                              <option value="cabut">Cabut</option>
                            </select>
                          </div>

                          <div class="mb-3">
                            <label>Keterangan</label>
                            <input type="text" class="form-control" id="keterangan">
                          </div>

                          <button class="btn btn-success w-100" onclick="simpanOdontogram()">
                            💾 Simpan
                          </button>

                        </div>
                      </div>
                    </div>


                    <!-- 📊 TABLE (KANAN) -->
                    <div class="col-md-8">
                      <div class="card">
                        <div class="card-body">

                          <h6>Data Odontogram</h6>

                          <table class="table table-bordered table-sm" id="tableOdontogram">
                            <thead class="table-light">
                              <tr>
                                <th>No</th>
                                <th>Gigi</th>
                                <th>Elemen</th>
                                <th>Diagnosa</th>
                                <th>Prosedur</th>
                              </tr>
                            </thead>
                            <tbody></tbody>
                          </table>

                        </div>
                      </div>
                    </div>

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
  function loadTable() {
    fetch('../controller/visit/getOdontogram.php?visit_ID=<?= $_GET['no'] ?>')
      .then(res => res.json())
      .then(res => {
        const tbody = document.querySelector('#tableOdontogram tbody');
        tbody.innerHTML = '';

        let no = 1;

        res.data.forEach(row => {
          tbody.innerHTML += `
          <tr>
            <td>${no++}</td>
            <td>${row.no_gigi}</td>
            <td>${row.elemen || '-'}</td>
            <td>${row.elemen_gigi || '-'}</td>
            <td>${row.diagnosa || '-'}</td>
            <td>${row.prosedur || '-'}</td>
            <td>${row.keterangan || '-'}</td>
          </tr>
        `;
        });
      });
  }
  let selectedGigi = null;
  let isLoading = false;

  // pilih gigi
  function pilihGigi(no, el) {
    selectedGigi = no;

    // reset semua warna
    document.querySelectorAll('.gigi').forEach(g => {
      g.style.background = '#fff';
    });

    // highlight yg dipilih
    el.style.background = "#ffccc7";

    console.log("Selected gigi:", no);
  }


  // simpan data
  function simpanOdontogram() {
    if (isLoading) return;

    if (!selectedGigi) {
      alert("⚠️ Pilih gigi dulu!");
      return;
    }

    const elemen = document.getElementById('elemen').value;
    const elemen_gigi = document.getElementById('elemen_gigi').value;
    const diagnosa = document.getElementById('diagnosa').value.trim();
    const prosedur = document.getElementById('prosedur').value;
    const keterangan = document.getElementById('keterangan').value;

    if (!diagnosa) {
      alert("⚠️ Diagnosa wajib diisi!");
      return;
    }

    isLoading = true;

    fetch('../controller/visit/odontogramController.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          no_gigi: selectedGigi,
          elemen,
          elemen_gigi,
          diagnosa,
          prosedur,
          keterangan,
          visit_ID: "<?= $_GET['no'] ?>",
          id_customer: "<?= $_GET['rm'] ?>"
        })
      })
      .then(res => res.json())
      .then(res => {
        isLoading = false;

        if (res.status === "success") {
          alert("✅ Data berhasil disimpan");
          loadTable();
          resetForm();

          // optional: kasih warna permanen
          tandaiGigi(selectedGigi);

        } else {
          alert("❌ " + res.message);
        }
      })
      .catch(err => {
        isLoading = false;
        console.error(err);
        alert("❌ Terjadi error");
      });
  }


  // reset form
  function resetForm() {
    document.getElementById('elemen').value = '';
    document.getElementById('elemen_gigi').value = '';
    document.getElementById('diagnosa').value = '';
    document.getElementById('prosedur').value = '';
    document.getElementById('keterangan').value = '';
  }


  // tandai gigi setelah simpan
  function tandaiGigi(no) {
    document.querySelectorAll('.gigi').forEach(g => {
      const nomor = g.querySelector('.nomor').innerText;

      if (parseInt(nomor) === no) {
        g.style.background = "#b7eb8f"; // hijau = tersimpan
      }
    });
  }
</script>

</html>