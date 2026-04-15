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
                          <th class="col-3">Item</th>
                          <th class="col-2">Qty</th>
                          <th>Signa</th>
                          <th>Catatan</th>
                          <th class="col-1">Actions</th>
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
              "actions": (function() {

                // ✅ kalau status selesai → tampil badge saja
                if (row.status_permintaan == 3) {
                  return `
                  <div class="text-center">
                    <span class="badge bg-success">
                      <i class="fas fa-check"></i> Approved
                    </span>
                  </div>
                `;
                }
                // ✅ selain itu tampil tombol normal
                return `
                  <div class="text-center">
                    <div class="btn-group btn-group-sm" role="group">

                      <a class="btn btn-warning edit-btn" 
                        href="javascript:;" 
                        data-id="${row.id_pharmacy_details}">
                        <i class="fas fa-edit"></i>
                      </a>

                      <a class="btn btn-danger delete-btn" 
                        href="javascript:;" 
                        data-id="${row.id_pharmacy_details}">
                        <i class="fas fa-trash"></i>
                      </a>

                    </div>
                  </div>
                `;

              })(),
              "nama": row.pharmacy_name_generic + '/' + row.pharmacy_name_trade ?? "-",
              "qty": row.qty ?? "-",
              "signa": row.signa ?? "-",
              "catatan": row.catatan ?? "-",
              "id_pharmacy": row.id_pharmacy
            };
          });
        }
      },

      // 🔥 INI TEMPATNYA
      createdRow: function(row, data) {
        $(row).attr('data-id-pharmacy', data.id_pharmacy);
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
          data: "catatan"
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

    $('#btnTambah').on('click', async function() {
      let rows = $('#periodeTable tbody tr.new-row');
      // 🔥 save dulu semua row lama
      for (let i = 0; i < rows.length; i++) {
        await saveRow($(rows[i]));
      }

      // 🔥 TAMBAH ROW BARU (TANPA DATA-ID)
      let html = `
          <tr class="new-row bg-light">
            <td>
              <select class="form-select form-select-sm item-select select2-item" style="width:100%">
                <option value="">Pilih Item</option>
                <?php foreach ($getbarang as $barang): ?>
                  <option value="<?= $barang['id_pharmacy']; ?>">
                    <?= $barang['pharmacy_name_generic']; ?>/<?= $barang['pharmacy_name_trade']; ?>
                  </option>
                <?php endforeach ?>
              </select>
            </td>

            <td><input type="number" class="form-control form-control-sm qty" value="1"></td>
            <td><input type="text" class="form-control form-control-sm signa"></td>
            <td><input type="text" class="form-control form-control-sm catatan"></td>

            <td class="text-center">
              <button class="btn btn-danger btn-sm remove-row">
                <i class="fas fa-times"></i>
              </button>
            </td>

          </tr>
          `;

      $('#periodeTable tbody').prepend(html);

      // 🔥 INIT SELECT2
      let select = $('.select2-item').first();

      select.select2({
        width: '100%',
        dropdownParent: $('body')
      });

      select.select2('open');

    });

    function saveRow(row) {

      let id = row.data('id');
      let idPharmacy = row.data('id-pharmacy');

      // 🔥 kalau belum ada, ambil dari select
      if (!idPharmacy) {
        idPharmacy = row.find('.item-select').val();
      }

      // 🔥 VALIDASI WAJIB
      if (!idPharmacy) {
        console.warn('id_pharmacy kosong, skip save');
        return;
      }

      let data = {
        id_permintaan_farmasi: "<?= $_GET['id'] ?>",
        id_pharmacy: idPharmacy, // 🔥 FIX DI SINI
        qty: row.find('.qty').val(),
        signa: row.find('.signa').val(),
        catatan: row.find('.catatan').val(),
        created_user: "<?= $_SESSION['fullname'] ?>"
      };




      // 🔥 INSERT
      if (!id) {

        return fetch(apiUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data)
          })
          .then(res => res.json())
          .then(res => {
            if (res.status === 'success') {
              row.data('id', res.id); // 🔥 WAJIB ADA
              row.removeClass('bg-light').addClass('bg-success-subtle');
            }
          });
      }

      // 🔥 UPDATE
      else {
        data.id_pharmacy_details = id;
        return fetch(apiUrl, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams(data)
        });

      }
    }


    $(document).on('click', '.remove-row', function() {
      $(this).closest('tr').remove();
    });


    $(document).on('change', '.item-select', function() {
      let row = $(this).closest('tr');
      let val = $(this).val();

      // 🔥 SIMPAN KE ROW
      row.attr('data-id-pharmacy', val);

      saveRow(row);
    });
    $(document).on('input', '.qty, .signa, .catatan', function() {

      let row = $(this).closest('tr');

      if (!row.data('id')) return;

      clearTimeout(row.data('timer'));

      let timer = setTimeout(() => {
        saveRow(row);
      }, 500);

      row.data('timer', timer);
    });

    $(document).on('click', '.edit-btn', function() {

      let btn = $(this);
      let row = btn.closest('tr');
      let id = btn.data('id');

      // 🔥 penting: ambil dari dataset sebelumnya
      let idPharmacy = row.data('id-pharmacy');
      if (!idPharmacy) {
        console.error('id_pharmacy hilang!');
      }

      // 🔥 ambil value lama
      let qty = row.find('td:eq(1)').text();
      let signa = row.find('td:eq(2)').text();
      let catatan = row.find('td:eq(3)').text();
      // 🔥 set id ke row
      row.data('id', id);
      // 🔥 ubah jadi input
      row.find('td:eq(1)').html(`
    <input type="number" class="form-control form-control-sm qty" value="${qty}">
  `);

      row.find('td:eq(2)').html(`
    <input type="text" class="form-control form-control-sm signa" value="${signa}">
  `);

      row.find('td:eq(3)').html(`
    <input type="text" class="form-control form-control-sm catatan" value="${catatan}">
  `);

      // 🔥 ubah tombol jadi save
      row.find('td:eq(4)').html(`
    <button class="btn btn-success btn-sm save-edit">
      <i class="fas fa-check"></i>
    </button>
  `);

    });



    $(document).on('click', '.save-edit', function() {

      let row = $(this).closest('tr');

      saveRow(row); // 🔥 pakai function yang sama

      Swal.fire({
        toast: true,
        icon: 'success',
        title: 'Update tersimpan',
        position: 'top-end',
        timer: 1500,
        showConfirmButton: false
      });

      // 🔥 reload biar balik normal
      $('#periodeTable').DataTable().ajax.reload(null, false);

    });

    $(document).on('click', '.delete-btn', function() {

      let id = $(this).data('id');

      if (!id) return;

      Swal.fire({
        title: 'Hapus?',
        icon: 'warning',
        showCancelButton: true
      }).then((result) => {

        if (result.isConfirmed) {

          fetch(`controller/visit/permintaanFarmasiDetails?id=${id}`, {
              method: 'DELETE'
            })
            .then(res => res.json())
            .then(res => {

              if (res.status === 'success') {

                Swal.fire({
                  toast: true,
                  icon: 'success',
                  title: 'Terhapus',
                  position: 'top-end',
                  timer: 1500,
                  showConfirmButton: false
                });

                $('#periodeTable').DataTable().ajax.reload(null, false);
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