<?php
$title = 'Farmasi Order';
require '../../controller/view.php';
require '../../database/connect.php';
require '../../utility/env.php';
// Memuat file .env
$env = loadEnv();
// Mengambil nilai API_URL dari environment
$apiUrl = getenv('API_URL');
$no = $_GET['no'];
$check = mysqli_query($koneksi, "SELECT * FROM pasien_visit LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient LEFT JOIN permintaan_pharmacy ON permintaan_pharmacy.id_visit = pasien_visit.visit_ID  WHERE pasien_visit.visit_ID='$no'");
$data = mysqli_fetch_array($check);

// Hitung usia jika data ditemukan
// if ($data) {
//   $patient_datebirth = new DateTime($data['patient_datebirth']);
//   $tanggal_visit = new DateTime($data['visit_date']);

//   $usia = $patient_datebirth->diff($tanggal_visit);
// }

?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    .info-item {
      display: flex;
      align-items: center;
      gap: 14px;
      background: #f0fdfa;
      padding: 14px 16px;
      border-radius: 12px;
    }

    .info-item i {
      font-size: 28px;
      color: #0f766e;
    }

    .info-item .label {
      font-size: 13px;
      color: #64748b;
    }

    .info-item .value {
      font-size: 16px;
      font-weight: 600;
      color: #0f172a;
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
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <!-- NAMA PASIEN -->
                  <h4 class="fw-bold mb-2">
                    <?= $data['patient_name'] ?>
                    <span class="badge bg-warning text-dark ms-2">
                      RM : <?= $data['nomor_rm'] ?>
                    </span>
                  </h4>

                  <!-- INFO PASIEN -->
                  <div class="text-muted mb-3">
                    <i class="ti ti-calendar"></i>
                    <!-- Usia:
                    <?= $usia->y ?> Th <?= $usia->m ?> Bl <?= $usia->d ?> Hr
                    &nbsp;•&nbsp; -->
                    <i class="ti ti-gender-bigender"></i>
                    <?= $data['patient_gender'] ?>
                  </div>

                  <!-- GARIS PEMISAH -->
                  <hr class="my-3">

                  <!-- INFO DOKTER & LAYANAN -->
                  <div class="row g-3">

                    <!-- DOKTER -->
                    <div class="col-md-6">
                      <div class="info-item">
                        <i class="ti ti-stethoscope"></i>
                        <div>
                          <div class="label">Dokter</div>
                          <div class="value"><?= $data['id_doctor'] ?></div>
                        </div>
                      </div>
                    </div>

                    <!-- LAYANAN -->
                    <div class="col-md-6">
                      <div class="info-item">
                        <i class="ti ti-building-hospital"></i>
                        <div>
                          <div class="label">Layanan</div>
                          <div class="value"><?= $data['id_poli'] ?></div>
                        </div>
                      </div>
                    </div>

                  </div>

                  <!-- TOMBOL PANGGIL -->
                  <div class="mt-4 text-end">
                    <button
                      class="btn btn-warning btn-call"
                      data-antrian="<?= $data['visit_antrian'] ?? '-' ?>"
                      data-nama="<?= $data['patient_name'] ?>"
                      data-poli="<?= $data['id_poli'] ?>"
                      data-visit="<?= $data['visit_ID'] ?>"
                      data-dokter="<?= $data['id_doctor'] ?>"
                      data-obat="<?= $data['obat'] ?? 'Silakan ambil obat di farmasi' ?>">
                      <i class="ti ti-volume"></i> Panggil Pasien
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4 " class="">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Permintaan Farmasi</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-light" onclick="window.history.back()">
                        <i class="fas fa-arrow-left"></i> Kembali
                      </button>
                      <a href="module/print/struk_obat?no=<?= $no ?>&rm=<?= $_GET['rm'] ?>&id=<?= $_GET['id'] ?>" target="_blank">
                        <button class="btn btn-outline-info"><i class="fas fa-print"></i> Struk</button>
                      </a>
                      <a href="module/print/resep?no=<?= $no ?>&rm=<?= $_GET['rm'] ?>&id=<?= $_GET['id'] ?>" target="_blank">
                        <button class="btn btn-outline-warning"><i class="fas fa-print"></i> Resep</button>
                      </a>
                      <button id="btnPersiapan" class="btn btn-danger">
                        <i class="fas fa-check-circle"></i> Persiapan Obat
                      </button>
                      <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Item</th>
                          <th scope="col" class="text-dark fw-normal">Qty</th>
                          <th scope="col" class="text-dark fw-normal">Signa</th>
                          <th scope="col" class="text-dark fw-normal">Harga</th>
                          <th scope="col" class="text-dark fw-normal">Total</th>
                          <th scope="col" class="text-dark fw-normal">Catatan</th>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
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

  <?php
  require 'library.php';
  ?>
</body>
<div class="modal fade" id="programModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="programForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_pharmacy_details" id="id_pharmacy_details">
        <input type="hidden" name="id_visit" id="id_visit" value="<?= $_GET['no'] ?>">
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label for="id_pharmacy" class="form-label">Nama Item <span class="text-danger">*</span> </label>
              <select name="id_pharmacy" id="id_pharmacy" class="form-select js-example-basic-item" required>
                <option value="">Select Option</option>
                <?php
                $getbarang = tampildata("SELECT * FROM ms_pharmacy WHERE pharmacy_status='1'");
                ?>
                <?php foreach ($getbarang as $barang): ?>
                  <option value="<?= $barang['id_pharmacy']; ?>" data-harga="<?= $barang['pharmacy_sale']; ?>"><?= $barang['pharmacy_name_generic']; ?>/<?= $barang['pharmacy_name_trade']; ?></option>
                <?php endforeach ?>
              </select>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Harga Dasar</label>
              <input type="number" id="harga" name="harga" class="form-control" required>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Qty</label>
              <input type="number" id="qty" name="qty" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label required">Signa</label>
              <input type="text" id="signa" name="signa" class="form-control" required>
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label">Catatan</label>
              <textarea name="catatan_permintaan" id="catatan_permintaan" class="form-control" rows="5"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

</html>

<script>
  const apiUrl = 'controller/visit/permintaanFarmasiDetails?no=<?= $_GET['id'] ?>';

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            let harga = parseFloat(row.harga) || 0;
            let qty = parseFloat(row.qty) || 0;
            let total = qty * harga;


            // Formatter IDR
            let formatter = new Intl.NumberFormat("id-ID", {
              style: "currency",
              currency: "IDR",
              minimumFractionDigits: 0
            });


            return {
              "actions": `
                      <div class="text-center">
								<div class="btn-group btn-group-sm" role="group">
                  <a class="btn btn-info approve-btn" href="javascript:;" data-id="${row.id_pharmacy_details}">
											<i class="fas fa-check-circle"></i>
									</a>
									<a class="btn btn-warning edit-btn" href="javascript:;" data-id="${row.id_pharmacy_details}">
											<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger delete-btn" href="javascript:;" data-id="${row.id_pharmacy_details}">
											<i class="fas fa-trash"></i>
									</a>
								</div>
							</div>
                    `,
              "nama": row.pharmacy_name_generic + '/' + row.pharmacy_name_trade ?? "-",
              "qty": row.qty ?? "-",
              "signa": row.signa ?? "-",
              "harga": formatter.format(harga), // ✅ harga format IDR
              "total": formatter.format(total), // ✅ total format IDR
              "catatan": row.catatan_permintaan ?? "-",
              "status": row.status_item === '1' ?
                '<span class="badge bg-success text-center d-block">Approve</span>' : '<span class="badge bg-danger text-center d-block">Belum proses</span>'
            };
          });
        }
      },
      columns: [{
          data: "nama"
        },
        {
          data: "qty"
        },
        {
          data: "signa"
        },
        {
          data: "harga"
        },
        {
          data: "total"
        },
        {
          data: "catatan"
        },
        {
          data: "status"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
        },
      ],
      footerCallback: function(row, data, start, end, display) {
        var api = this.api();

        // Hitung total bobot
        let total = api
          .column(3, {
            page: 'current'
          })
          .data()
          .reduce((a, b) => {
            return (parseFloat(a) || 0) + (parseFloat(b) || 0);
          }, 0);

        // Tampilkan di footer
        $(api.column(3).footer()).html(total.toFixed(2) + " %");
      }
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

    // 🔹 Tambah
    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset(); // ✅ pakai programForm, bukan addForm
      $('#id_pharmacy_details').val('');
      $('#programModal .modal-title').text('Tambah Data');
      $('#programModal').modal('show');
    });

    // 🔹 Submit (Tambah / Update)
    $('#programForm').on('submit', function(e) {
      e.preventDefault();
      let formData = new URLSearchParams(new FormData(this));
      let id = $('#id_pharmacy_details').val();

      fetch(apiUrl + (id ? `?id=${id}` : ''), {
          method: id ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success');
            $('#programModal').modal('hide');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', data.message, 'error');
          }
        });
    });
    // 🔹 Edit
    $(document).on('click', '.edit-btn', function() {
      let id = $(this).data('id');
      fetch(apiUrl + `&id=${id}`)
        .then(res => res.json())
        .then(resp => {
          if (resp.status === 'success') {
            let d = resp.data;

            // isi otomatis field biasa
            for (let key in d) {
              if (key !== "id_pharmacy" && key !== "harga") { // skip select & harga
                $(`[name="${key}"]`).val(d[key]);
              }
            }

            // isi dropdown select2
            $('#id_pharmacy').val(d.id_pharmacy).trigger("change");

            // isi harga langsung dari response DB
            $('#harga').val(d.harga);

            $('#programModal .modal-title').text('Edit Data');
            $('#programModal').modal('show');
          }
        });
    });
    // 🔹 Delete
    $(document).on('click', '.delete-btn', function() {
      let id = $(this).data('id');
      Swal.fire({
        title: 'Hapus Data?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(apiUrl + `&id=${id}`, {
              method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', 'Data dihapus.', 'success');
                table.ajax.reload(null, false);
              }
            });
        }
      });
    });

    // 🔹 Approve
    $(document).on('click', '.approve-btn', function() {
      let id = $(this).data('id');

      Swal.fire({
        title: 'Approve Permintaan?',
        text: 'Permintaan farmasi akan ditandai sebagai selesai.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Approve',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(apiUrl + `&approve=1`, {
              method: 'PUT',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: `id_pharmacy_details=${id}`
            })
            .then(res => res.json())
            .then(resp => {
              if (resp.status === 'success') {
                Swal.fire('Berhasil!', resp.message, 'success');
                table.ajax.reload(null, false);
              } else {
                Swal.fire('Gagal!', resp.message, 'error');
              }
            });
        }
      });
    });
  });
</script>

<script>
  let currentStatus = <?= $data['status_permintaan'] ?? 1 ?>;
  console.log(currentStatus)
  const getUrlParam = (param) => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
  };

  const id_permintaan = getUrlParam("id");
  document.getElementById("btnPersiapan").addEventListener("click", function() {

    let nextStatus = $(this).data('next');

    Swal.fire({
      title: "Update status?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya"
    }).then((result) => {

      if (result.isConfirmed) {

        fetch("controller/farmasi/approveTiketOrder.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
              id: id_permintaan,
              status: nextStatus
            })
          })
          .then(res => res.json())
          .then(res => {
            if (res.status === "success") {
              Swal.fire("Berhasil!", "Status diupdate", "success")
                .then(() => location.reload());
            }
          });

      }

    });

  });
  $(document).ready(function() {
    renderButton(currentStatus);
  });
</script>

<script>
  function renderButton(status) {
    let btn = $('#btnPersiapan');

    if (status == 1) {
      btn
        .removeClass()
        .addClass('btn btn-danger')
        .html('<i class="fas fa-check-circle"></i> Persiapan Obat')
        .data('next', 2);
    } else if (status == 2) {
      btn
        .removeClass()
        .addClass('btn btn-success')
        .html('<i class="fas fa-check-circle"></i> Selesai')
        .data('next', 3);
    } else {
      btn
        .removeClass()
        .addClass('btn btn-secondary')
        .html('<i class="fas fa-check-circle"></i> Selesai')
        .prop('disabled', true);
    }
  }
</script>
<script>
  $(document).on('click', '.btn-call', function() {
    const noAntrian = $(this).data('antrian');
    const nama = $(this).data('nama');
    const poli = $(this).data('poli');
    const visit = $(this).data('visit');
    const dokter = $(this).data('dokter');
    const obat = $(this).data('obat');

    callPatient(noAntrian, nama, poli, visit, dokter, obat);

    // 🔥 disable biar gak double klik
    $(this).prop('disabled', true);
  });

  function callPatient(noAntrian, namaPasien, poli, visitID, id_doctor, obat) {

    /* =========================
       1. SUARA
    ========================= */
    if ('speechSynthesis' in window) {

      speechSynthesis.cancel();

      const text = `
  
      pasien ${namaPasien}, dipersilahkan untuk ambil obat 
    `;

      const utterance = new SpeechSynthesisUtterance(text);

      utterance.lang = 'id-ID';
      utterance.rate = 0.9;
      utterance.pitch = 1;
      utterance.volume = 1;

      const voices = speechSynthesis.getVoices();
      const indo = voices.find(v => v.lang === 'id-ID');
      if (indo) utterance.voice = indo;

      speechSynthesis.speak(utterance);
    }

    /* =========================
       2. UPDATE STATUS
    ========================= */
    fetch('controller/queue/poliCall.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          visit_ID: visitID
        })
      })
      .then(res => res.json())
      .then(res => {
        if (res.status !== 'success') {
          console.warn('Update status gagal');
        }
      });
  }
</script>