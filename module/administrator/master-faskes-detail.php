<?php
$title = 'Master Faskes';
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
    require '../admin/sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require '../admin/navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Data Faskes</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Pembayaran</button>
                </li>
              </ul>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">

                <div class="tab-content" id="myTabContent">
                  <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                    <div class="card-body p-4">
                      <form id="programForm">
                        <div class="row">
                          <input type="faskesForm" hidden name="id_faskes" id="faskes_id">
                          <!-- 🏥 INFORMASI FASKES -->
                          <div class="col-12 mb-4">
                            <h5>🏥 Informasi Faskes</h5>
                            <hr />
                            <div class="row">
                              <div class="col-6 mb-3">
                                <label>Faskes Code</label>
                                <input type="text" name="faskes_code" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Faskes Name</label>
                                <input type="text" name="faskes_name" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Status</label>
                                <select name="faskes_status" class="form-select">
                                  <option value="">-- Pilih Status --</option>
                                  <option value="1">Aktif</option>
                                  <option value="0">Nonaktif</option>
                                </select>
                              </div>

                              <div class="col-6 mb-3">
                                <label>Metode Pembayaran</label>
                                <select name="faskes_payment" class="form-select">
                                  <option value="">-- Pilih Metode --</option>
                                  <option value="Tunai">Tunai</option>
                                  <option value="Transfer">Transfer</option>
                                </select>
                              </div>
                            </div>
                          </div>

                          <!-- 👤 PIC -->
                          <div class="col-12 mb-4">
                            <h5>👤 PIC (Penanggung Jawab)</h5>
                            <hr />
                            <div class="row">
                              <div class="col-6 mb-3">
                                <label>Nama PIC</label>
                                <input type="text" name="pic_name" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>No HP PIC</label>
                                <input type="text" name="pic_phone" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Email PIC</label>
                                <input type="email" name="pic_email" class="form-control" />
                              </div>
                            </div>
                          </div>

                          <!-- 📍 ALAMAT -->
                          <div class="col-12 mb-4">
                            <h5>📍 Alamat</h5>
                            <hr />
                            <div class="row">
                              <div class="col-6 mb-3">
                                <label>Alamat</label>
                                <input type="text" name="faskes_address" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Provinsi</label>
                                <input type="text" name="faskes_prov" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Kota</label>
                                <input type="text" name="faskes_city" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Kecamatan</label>
                                <input type="text" name="faskes_district" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Kelurahan</label>
                                <input type="text" name="faskes_village" class="form-control" />
                              </div>
                            </div>
                          </div>

                          <!-- 📄 KONTRAK -->
                          <div class="col-12 mb-4">
                            <h5>📄 Informasi Kontrak</h5>
                            <hr />
                            <div class="row">
                              <div class="col-6 mb-3">
                                <label>Contract Number</label>
                                <input type="text" name="contract_number" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Order Number</label>
                                <input type="text" readonly name="order_number" class="form-control bg-light" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Contract Date</label>
                                <input type="date" name="contract_date" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Contract Start</label>
                                <input type="date" name="contract_start" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Contract End</label>
                                <input type="date" name="contract_end" class="form-control" />
                              </div>

                              <div class="col-6 mb-3">
                                <label>Contract Amount</label>
                                <input type="number" name="contract_amount" class="form-control" />
                              </div>
                            </div>
                          </div>

                          <!-- BUTTON -->
                          <div class="col-12 text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                              💾 Simpan Data
                            </button>
                          </div>

                        </div>
                      </form>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                    <div class="card-body p-4">
                      <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title fw-semibold">Data Pembayaran Faskes</h5>
                        <!-- Grup tombol di sisi kanan -->
                        <div class="d-flex ms-auto gap-2">
                          <button class="btn btn-primary" id="btnTambah"><i class="fas fa-plus"></i> Tambah</button>
                        </div>
                      </div>
                      <div class="table-responsive" data-simplebar>
                        <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                          <thead>
                            <tr>
                              <th class="text-dark fw-normal col-1">Invoice ID</th>
                              <th class="text-dark fw-normal col-1">Tanggal</th>
                              <th scope="col" class="text-dark fw-normal">Metode Bayar</th>
                              <th class="text-dark fw-normal">Nominal</th>
                              <th class="text-dark fw-normal">Keterangan</th>
                              <th class="text-dark fw-normal">File</th>
                              <th scope="col" class="text-dark fw-normal text-center col-1">Status</th>
                              <th scope="col" class="text-dark fw-normal text-center col-1">Actions</th>
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



  <?php
  require '../admin/library.php';
  ?>
</body>

<div class="modal fade" id="paymentModal">
  <div class="modal-dialog">
    <form id="paymentForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Pembayaran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="payment_id" name="id_payment">
        <input type="hidden" id="payment_order_number" name="order_number">

        <div class="mb-3">
          <label>Tanggal</label>
          <input type="date" name="tanggal" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Metode</label>
          <select name="metode" class="form-select">
            <option value="Transfer">Transfer</option>
            <option value="Tunai">Tunai</option>
          </select>
        </div>

        <div class="mb-3">
          <label>Nominal</label>
          <input type="number" name="nominal" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Keterangan</label>
          <textarea name="keterangan" class="form-control"></textarea>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
  const paymentApi = 'controller/master/faskesPaymentController';
  const urlParams = new URLSearchParams(window.location.search);
  const no = urlParams.get('no');

  let table;

  // 🔹 Load table pembayaran
  function loadPaymentTable() {
    if (!no) return;

    table = $('#periodeTable').DataTable({
      destroy: true,
      ajax: {
        url: paymentApi + '?no=' + no,
        dataSrc: 'data'
      },
      columns: [{
          data: 'invoice_number'
        },
        {
          data: 'payment_date'
        },
        {
          data: 'payment_method'
        },
        {
          data: 'payment_amount',
          render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ')
        },
        {
          data: 'payment_note'
        },
        {
          data: 'payment_file',
          render: function(data) {
            return data ? `<a href="uploads/${data}" target="_blank">File</a>` : '-';
          }
        },
        {
          data: 'payment_status',
          render: function(d) {
            return d == 1 ? 'Paid' : 'Pending';
          }
        },
        {
          data: null,
          render: function(row) {
            return `
            <button class="btn btn-warning btn-sm edit-btn" data-id="${row.id_payment}">Edit</button>
            <button class="btn btn-danger btn-sm delete-btn" data-id="${row.id_payment}">Hapus</button>
          `;
          }
        }
      ]
    });
  }

  // 🔹 tambah
  $('#btnTambah').on('click', function() {
    $('#paymentForm')[0].reset();
    $('#payment_id').val('');
    $('#payment_order_number').val(no);
    $('#paymentModal').modal('show');
  });

  // 🔹 submit (insert / update)
  $('#paymentForm').on('submit', function(e) {
    e.preventDefault();

    let id = $('#payment_id').val();
    let formData = new URLSearchParams(new FormData(this));

    fetch(paymentApi + (id ? '?id=' + id : ''), {
        method: id ? 'PUT' : 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        if (res.status === 'success') {
          Swal.fire('Berhasil!', res.message, 'success');
          $('#paymentModal').modal('hide');
          table.ajax.reload(null, false);
        } else {
          Swal.fire('Gagal!', res.message, 'error');
        }
      });
  });

  // 🔹 edit
  $(document).on('click', '.edit-btn', function() {
    let id = $(this).data('id');

    fetch(paymentApi + '?id=' + id)
      .then(res => res.json())
      .then(res => {
        let d = res.data;

        $('#payment_id').val(d.id_payment);
        $('#payment_order_number').val(d.order_number);
        $('[name="tanggal"]').val(d.payment_date);
        $('[name="metode"]').val(d.payment_method);
        $('[name="nominal"]').val(d.payment_amount);
        $('[name="keterangan"]').val(d.payment_note);

        $('#paymentModal').modal('show');
      });
  });

  // 🔹 delete
  $(document).on('click', '.delete-btn', function() {
    let id = $(this).data('id');

    Swal.fire({
      title: 'Hapus data?',
      icon: 'warning',
      showCancelButton: true
    }).then(result => {
      if (result.isConfirmed) {
        fetch(paymentApi + '?id=' + id, {
            method: 'DELETE'
          })
          .then(res => res.json())
          .then(res => {
            if (res.status === 'success') {
              Swal.fire('Berhasil', 'Data dihapus', 'success');
              table.ajax.reload(null, false);
            }
          });
      }
    });
  });

  $(document).ready(function() {
    loadPaymentTable();
  });
</script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const no = urlParams.get('no');

    if (no) {
      loadFaskesToForm(no);
    }
  });

  const faskesViewApi = 'controller/master/faskesDetailController';

  function loadFaskesToForm(no) {
    fetch(faskesViewApi + '?no=' + encodeURIComponent(no))
      .then(res => res.json())
      .then(res => {
        console.log('API RESPONSE:', res); // 🔥 debug

        if (res.status === 'success') {
          const data = res.data;

          Object.keys(data).forEach(key => {
            let el = document.querySelector(`[name="${key}"]`);
            if (el) {
              el.value = data[key] ?? '';
            } else {
              console.warn('Field tidak ditemukan:', key);
            }
          });

          let idField = document.getElementById('faskes_id');
          if (idField) idField.value = data.id_faskes ?? '';

        } else {
          Swal.fire('Gagal!', res.message, 'error');
        }
      })
      .catch(err => {
        console.error(err);
        Swal.fire('Error!', 'Gagal load data', 'error');
      });
  }
</script>
<script>
  const faskesUpdateApi = 'controller/master/faskesController';

  document.addEventListener('DOMContentLoaded', function() {

    const form = document.getElementById('faskesForm') || document.getElementById('programForm');

    form.addEventListener('submit', function(e) {
      e.preventDefault();

      const id = document.getElementById('faskes_id').value;

      if (!id) {
        Swal.fire('Warning!', 'ID tidak ditemukan', 'warning');
        return;
      }

      let formData = new URLSearchParams(new FormData(form));
      formData.append('id_faskes', id); // 🔥 WAJIB

      fetch(faskesUpdateApi, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(res => res.json())
        .then(res => {
          console.log('UPDATE RESPONSE:', res);

          if (res.status === 'success') {
            Swal.fire('Berhasil!', res.message, 'success');
          } else {
            Swal.fire('Gagal!', res.message, 'error');
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Error!', 'Gagal update data', 'error');
        });

    });

  });
</script>


</html>