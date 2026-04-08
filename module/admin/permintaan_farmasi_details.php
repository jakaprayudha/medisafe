<?php
$title = 'Permintan Farmasi';
require "../../controller/view.php";
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
          <div class="col-12">
            <div class="card shadow-sm border-0 mb-4" style="border-radius:16px;">
              <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5 class="fw-bold mb-0">
                    <i class="ti ti-ticket me-2 text-primary"></i>
                    Tiket Order Farmasi
                  </h5>
                  <button id="btnKirim" class="btn btn-primary d-none">
                    <i class="ti ti-send"></i> Kirim Obat
                  </button>
                </div>

                <div class="row g-3">

                  <div class="col-md-4">
                    <div class="info-item">
                      <i class="ti ti-hash"></i>
                      <div>
                        <div class="label">No Permintaan</div>
                        <div class="value" id="permintaan_number">-</div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="info-item">
                      <i class="ti ti-calendar"></i>
                      <div>
                        <div class="label">Tanggal</div>
                        <div class="value" id="created_at">-</div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="info-item">
                      <i class="ti ti-pill"></i>
                      <div>
                        <div class="label">Tipe Obat</div>
                        <div class="value" id="tipe_obat">-</div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="info-item">
                      <i class="ti ti-flask"></i>
                      <div>
                        <div class="label">Racikan</div>
                        <div class="value" id="racikan">-</div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="info-item">
                      <i class="ti ti-notes"></i>
                      <div>
                        <div class="label">Catatan</div>
                        <div class="value" id="catatan">-</div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="info-item">
                      <i class="ti ti-check"></i>
                      <div>
                        <div class="label">Status Proses</div>
                        <div class="value" id="status_obat">-</div>
                      </div>
                    </div>
                  </div>

                </div>

              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Detail Item Permintaan Farmasi</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-light" onclick="history.back()"><i class="fas fa-arrow-left"></i> Kembali</button>
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
        <input type="hidden" name="id_permintaan_farmasi" id="id_permintaan_farmasi" value="<?= $_GET['id'] ?>">
        <input type="hidden" name="id_pharmacy_details" id="id_pharmacy_details">
        <input type="hidden" name="created_user" id="created_user" value="<?= $_SESSION['fullname'] ?>">
        <div class="row">
          <div class="col-12">
            <div class="mb-3">
              <label for="id_pharmacy" class="form-label">Nama Item <span class="text-danger">*</span> </label>
              <select name="id_pharmacy" id="id_pharmacy" class="form-select js-example-basic-item" required>
                <option value="">Select Option</option>
                <?php
                $id_cust = $_SESSION['id_customer'];
                $getbarang = tampildata("
                SELECT * FROM ms_pharmacy 
                WHERE pharmacy_status='1'
                AND (id_customer = '$id_cust' OR id_customer = '0')
              ");
                ?>
                <?php foreach ($getbarang as $barang): ?>
                  <option value="<?= $barang['id_pharmacy']; ?>" data-harga="<?= $barang['pharmacy_sale']; ?>"><?= $barang['pharmacy_name_generic']; ?>/<?= $barang['pharmacy_name_trade']; ?></option>
                <?php endforeach ?>
              </select>
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label">Harga Dasar</label>
              <input type="number" id="harga" name="harga" class="form-control">
            </div>
          </div>
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label required">Qty</label>
              <input type="number" id="qty" name="qty" class="form-control" required>
            </div>
          </div>
          <div class="col-12" id="group_signa">
            <div class="mb-3">
              <label class="form-label">Signa</label>
              <input type="text" id="signa" name="signa" class="form-control">
            </div>
          </div>
          <div class="col-12">
            <div class="mb-3">
              <label class="form-label">Catatan</label>
              <textarea name="catatan" id="catatan" class="form-control" rows="5"></textarea>
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
<script>
  $(document).ready(function() {
    $('#id_pharmacy').select2({
      dropdownParent: $('#programModal'),
      width: '100%'
    });

    $('#id_pharmacy').on('change', function() {
      let harga = $(this).find(':selected').data('harga') || 0;
      $('#harga').val(harga);
    });
  });
</script>
<script>
  let tipeObatGlobal = ''
  const apiUrl = 'controller/visit/permintaanFarmasiDetails?no=<?= $_GET['id'] ?>';

  $(document).ready(function() {
    const formatRupiah = (angka) => {
      const number = Number(angka);
      if (isNaN(number)) return "-";
      return new Intl.NumberFormat("id-ID").format(number);
    };
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false, // 🔹 ubah jadi false
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {
            return {
              "actions": `
                      <div class="text-center">
								<div class="btn-group btn-group-sm" role="group">
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
              "harga": formatRupiah(row.harga),
              "total": formatRupiah(row.total_item),
              "catatan": row.catatan ?? "-",
              "status": row.status_item === '1' ?
                '<span class="badge bg-success text-center d-block">Selesai</span>' : '<span class="badge bg-danger text-center d-block">Belum proses</span>'
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

    $('#btnTambah').on('click', function() {
      $('#programForm')[0].reset();
      $('#id_pharmacy_details').val('');
      $('#programModal .modal-title').text('Tambah Data');

      setTimeout(() => {
        handleSigna(); // 🔥 delay biar pasti dapet value
      }, 100);

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
            handleSigna();
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
  });
</script>

<script>
  const getParam = (name) => {
    const url = new URL(window.location.href);
    return url.searchParams.get(name);
  };

  const idPermintaan = getParam('id');

  fetch(`controller/farmasi/getPermintaanById.php?id=${idPermintaan}`)
    .then(res => res.json())
    .then(res => {

      if (res.status !== 'success') return;

      const d = res.data;

      // =========================
      // FORMAT TANGGAL
      // =========================
      let tgl = '-';
      if (d.created_at) {
        const date = new Date(d.created_at);
        tgl = date.toLocaleString('id-ID');
      }

      // =========================
      // RACIKAN (ANTI KOSONG)
      // =========================
      let racikan = '-';
      if (d.rck_jumlah || d.rck_satuan || d.rck_signa) {
        racikan = `${d.rck_jumlah || ''} ${d.rck_satuan || ''} ${d.rck_signa ? '(' + d.rck_signa + ')' : ''}`;
      }

      // =========================
      // ISI DATA
      // =========================
      $('#permintaan_number').text(d.permintaan_number || '-');
      $('#created_at').text(tgl);
      $('#tipe_obat').text(d.tipe_obat || '-');
      tipeObatGlobal = d.tipe_obat || '';
      handleSigna();
      $('#racikan').text(racikan);
      $('#catatan').text(d.catatan_permintaan || '-');

      // =========================
      // STATUS (SINGLE SOURCE 🔥)
      // =========================
      let statusText = '';
      let statusClass = '';

      $('#btnKirim').addClass('d-none'); // default hide

      if (d.status_permintaan == 0) {
        statusText = 'Menunggu Kirim';
        statusClass = 'bg-warning text-dark';

        // tampilkan tombol kirim
        $('#btnKirim').removeClass('d-none');

      } else if (d.status_permintaan == 1) {
        statusText = 'Terikirim Ke Farmasi';
        statusClass = 'bg-primaru';

      } else if (d.status_permintaan == 2) {
        statusText = 'Sedang Diproses';
        statusClass = 'bg-info';

      } else if (d.status_permintaan == 3) {
        statusText = 'Selesai';
        statusClass = 'bg-success';

      } else {
        statusText = 'Unknown';
        statusClass = 'bg-secondary';
      }

      // =========================
      // RENDER STATUS
      // =========================
      $('#status_obat').html(`
        <span class="badge ${statusClass}">
          ${statusText}
        </span>
      `);

      $('#statusBadge')
        .removeClass()
        .addClass(`badge ${statusClass}`)
        .text(statusText);

    });

  // =========================
  // ACTION TOMBOL KIRIM
  // =========================
  $('#btnKirim').on('click', function() {

    Swal.fire({
      title: 'Kirim obat?',
      text: 'Pastikan data sudah benar',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, kirim',
      cancelButtonText: 'Batal'
    }).then((result) => {

      if (result.isConfirmed) {

        fetch('controller/farmasi/kirimObat.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              id: idPermintaan
            })
          })
          .then(res => res.json())
          .then(res => {

            if (res.status === 'success') {
              Swal.fire('Berhasil!', 'Obat berhasil dikirim', 'success')
                .then(() => location.reload());
            } else {
              Swal.fire('Gagal!', res.message || 'Terjadi error', 'error');
            }

          });

      }

    });

  });

  function handleSigna() {

    const tipe = (tipeObatGlobal || '').toLowerCase();

    console.log('TIPE OBAT:', tipe); // debug penting

    if (tipe === 'racikan') {

      // 🔥 RACIKAN
      $('#group_signa').hide();
      $('#signa').val('-');

    } else {

      // 🔥 NON RACIKAN (INI YANG HARUS MASUK)
      $('#group_signa').show();

      if ($('#signa').val() === '-') {
        $('#signa').val('');
      }
    }
  }
</script>

</html>