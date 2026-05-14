  <?php
  $title = 'Pemeriksaan';
  require '../../controller/view.php';
  require '../../database/connect.php';
  $no = $_GET['no'];
  $id_customer = $_SESSION['id_customer'];
  $check = mysqli_query($koneksi, "SELECT * FROM pasien_visit WHERE visit_ID='$no' AND id_customer='$id_customer'");
  $datacheck = mysqli_fetch_array($check);
  $idvisit = $datacheck['id_visit'];

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
            @$rme = $_GET['rme']; // default a
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
                    <h4 class="mb-3">Surat Persetujuan Opname</h4>
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

                            <h6 class="fw-bold text-danger mb-3">
                              Surat Persetujuan Rawat Inap (Opname)
                            </h6>

                            <!-- FORM INPUT -->
                            <div class="mb-3">
                              <label class="form-label">Nama Keluarga</label>
                              <input type="text" class="form-control" id="nama_pasien" placeholder="Masukkan nama pasien">
                            </div>

                            <div class="mb-3">
                              <label class="form-label">Umur</label>
                              <input type="text" class="form-control" id="umur_pasien" placeholder="Masukkan umur">
                            </div>

                            <div class="mb-3">
                              <label class="form-label">Jenis Kelamin</label>
                              <select class="form-control" id="jk_pasien">
                                <option value="">-- Pilih --</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                              </select>
                            </div>

                            <div class="mb-3">
                              <label class="form-label">Hubungan Keluarga</label>
                              <select class="form-control" id="keluarga">
                                <option value="">-- Pilih --</option>
                                <option value="Diri Saya">Diri Saya</option>
                                <option value="Suami">Suami</option>
                                <option value="Istri">Istri</option>
                                <option value="Anak">Anak</option>
                                <option value="Ayah">Ayah</option>
                                <option value="Ibu">Ibu</option>
                                <option value="Keluarga Lain">Keluarga Lain</option>
                              </select>
                            </div>

                            <!-- ISI SURAT -->
                            <p style="font-size: 0.9rem; color:#333; line-height:1.6;">
                              Saya yang bertanda tangan di bawah ini menyatakan bahwa:
                            </p>

                            <p style="font-size: 0.9rem; color:#333; line-height:1.6;">
                              Nama : <span id="preview_nama" class="fw-bold"></span><br>
                              Umur : <span id="preview_umur" class="fw-bold"></span><br>
                              Jenis Kelamin : <span id="preview_jk" class="fw-bold"></span> <br>
                              Hubungan Keluarga : <span id="preview_keluarga" class="fw-bold"></span>
                            </p>

                            <p style="font-size: 0.9rem; color:#333; line-height:1.6;">
                              Dengan ini menyatakan sesungguhnya telah memberikan persetujuan untuk dilakukan tindakan medis berupa <strong>opname dan perawatan</strong>, terhadap pasien tersebut.
                            </p>

                            <p style="font-size: 0.9rem; color:#333; line-height:1.6;">
                              Saya juga telah menyatakan dengan sesungguhnya bahwa saya telah diberikan informasi dan penjelasan terhadap tindakan medis yang akan dilakukan tersebut dan telah memahami sepenuhnya informasi dan penjelasan yang diberikan oleh dokter.
                            </p>

                          </div>
                        </div>
                      </div>
                    </div>
                    <div id="ttdSection" class="text-end mt-2"></div>
                    <div class="text-end mt-2">
                      <a href="module/admin/print/formulir_surat_persetujuan?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>" target="_blank">
                        <button class="btn btn-outline-primary">
                          <iconify-icon icon="mdi:printer-outline"></iconify-icon>
                          Cetak
                        </button>
                      </a>
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
  <div class="modal fade" id="ttdModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">✍️ Tanda Tangan Pasien</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body text-center">
          <input type="hidden" hidden id="ttd_id_visit" value="<?= $idvisit ?>">

          <canvas id="signaturePad"
            style="border:1px solid #ccc; width:100%; height:400;">
          </canvas>

          <div class="mt-3 d-flex justify-content-between">
            <button class="btn btn-warning" id="clearSignature">🧹 Clear</button>
            <button class="btn btn-primary" id="saveSignature">💾 Simpan</button>
          </div>

        </div>

      </div>
    </div>
  </div>

  <script>
    fetch(`controller/visit/getTTDKeluarga?no=<?= $no ?>`)
      .then(res => res.json())
      .then(resp => {

        let d = resp.data;
        let container = document.getElementById('ttdSection');
        if (d && d.ttd && d.ttd !== 'null' && d.ttd !== '') {

          container.innerHTML = `
          <div class="alert alert-success text-start shadow-sm">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

              <div>
                <strong>✔️ Berkas sudah ditandatangani</strong><br>
                <small class="text-muted">Pasien telah menyetujui pernyataan.</small>
              </div>

              <div class="d-flex align-items-center gap-2">
                <img src="${d.ttd}" 
                    style="height:60px; border:1px solid #ccc; border-radius:6px; background:#fff;"
                    onerror="this.style.display='none'">

                <a href="${d.ttd}" target="_blank" class="btn btn-sm btn-outline-success">
                  📄 Lihat
                </a>
              </div>

            </div>
          </div>
        `;

        } else {

          container.innerHTML = `
          <button class="btn btn-outline-danger" id="openModal">
            <iconify-icon icon="mdi:check-decagram-outline"></iconify-icon>
            Saya Mengerti dan Setuju
          </button>
        `;

          document.getElementById("openModal").onclick = function() {
            const modal = new bootstrap.Modal(document.getElementById("ttdModal"));
            modal.show();
          };
        }

      })
      .catch(err => {
        console.error('TTD error:', err);
      });
  </script>


  <script>
    document.addEventListener("DOMContentLoaded", function() {

      const canvas = document.getElementById('signaturePad');
      const ctx = canvas.getContext('2d');

      let drawing = false;

      function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);

        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = 400 * ratio;

        canvas.getContext("2d").scale(ratio, ratio);
      }

      $('#ttdModal').on('shown.bs.modal', function() {
        resizeCanvas();
      });

      // DRAW
      canvas.addEventListener('mousedown', () => drawing = true);
      canvas.addEventListener('mouseup', () => {
        drawing = false;
        ctx.beginPath();
      });
      canvas.addEventListener('mousemove', draw);

      canvas.addEventListener('touchstart', () => drawing = true);
      canvas.addEventListener('touchend', () => {
        drawing = false;
        ctx.beginPath();
      });
      canvas.addEventListener('touchmove', drawTouch);

      function draw(e) {
        if (!drawing) return;

        ctx.lineWidth = 2;
        ctx.lineCap = 'round';

        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(e.offsetX, e.offsetY);
      }

      function drawTouch(e) {
        e.preventDefault();
        if (!drawing) return;

        const rect = canvas.getBoundingClientRect();
        const touch = e.touches[0];

        const x = touch.clientX - rect.left;
        const y = touch.clientY - rect.top;

        ctx.lineWidth = 2;
        ctx.lineCap = 'round';

        ctx.lineTo(x, y);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x, y);
      }

      // CLEAR
      document.getElementById('clearSignature').onclick = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
      };

      // OPEN MODAL
      document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'openModal') {
          const modal = new bootstrap.Modal(document.getElementById("ttdModal"));
          modal.show();
        }
      });

      // SAVE 🔥
      document.getElementById('saveSignature').onclick = function() {

        const id_visit = document.getElementById('ttd_id_visit').value;
        const nama = document.getElementById('nama_pasien').value;
        const umur = document.getElementById('umur_pasien').value;
        const jk = document.getElementById('jk_pasien').value;
        const hubungan = document.getElementById('keluarga').value;

        if (!id_visit) {
          alert('ID visit tidak ditemukan');
          return;
        }

        if (!nama || !umur || !jk) {
          alert('Nama, umur, dan jenis kelamin wajib diisi');
          return;
        }

        const image = canvas.toDataURL('image/png');

        if (image === "data:,") {
          alert("Tanda tangan kosong");
          return;
        }

        fetch('controller/admisi/saveSignatureKeluarga.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              id_visit: id_visit,
              image: image,
              nama: nama,
              umur: umur,
              jk: jk,
              hubungan: hubungan
            })
          })
          .then(res => res.json())
          .then(resp => {
            console.log("RESPONSE:", resp);

            if (resp.status === 'success') {

              alert('Tanda tangan berhasil disimpan');

              const modalEl = document.getElementById('ttdModal');
              const modalInstance = bootstrap.Modal.getInstance(modalEl);
              modalInstance.hide();

              fetch(`controller/visit/getTTDKeluarga?no=<?= $no ?>`)
                .then(res => res.json())
                .then(r => {
                  let d = r.data;
                  if (d && d.ttd) {
                    renderTTD(d.ttd);
                  }
                });

            }
          })
          .catch(err => {
            console.error(err);
            alert('Fetch error');
          });
      };

      const modalEl = document.getElementById('ttdModal');

      modalEl.addEventListener('hidden.bs.modal', function() {
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
      });

    });
  </script>
  <script>
    function renderTTD(ttdUrl) {
      let container = document.getElementById('ttdSection');

      container.innerHTML = `
      <div class="alert alert-success text-start shadow-sm">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

          <div>
            <strong>✔️ Berkas sudah ditandatangani</strong><br>
            <small class="text-muted">Pasien telah menyetujui pernyataan.</small>
          </div>

          <div class="d-flex align-items-center gap-2">
            <img src="${ttdUrl}" 
                style="height:60px; border:1px solid #ccc; border-radius:6px; background:#fff;">

            <a href="${ttdUrl}" target="_blank" class="btn btn-sm btn-outline-success">
              📄 Lihat
            </a>
          </div>

        </div>
      </div>
    `;
    }
  </script>
  <script>
    document.getElementById('nama_pasien').addEventListener('input', function() {
      document.getElementById('preview_nama').innerText = this.value;
    });

    document.getElementById('umur_pasien').addEventListener('input', function() {
      document.getElementById('preview_umur').innerText = this.value;
    });

    document.getElementById('jk_pasien').addEventListener('change', function() {
      document.getElementById('preview_jk').innerText = this.value;
    });

    document.getElementById('keluarga').addEventListener('change', function() {
      document.getElementById('preview_keluarga').innerText = this.value;
    });
  </script>

  </html>