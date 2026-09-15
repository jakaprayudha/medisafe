<?php
$title = 'Master Faskes Details';
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
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-tab-pane" type="button" role="tab" aria-controls="user-tab-pane" aria-selected="false">User</button>
                </li>
                <!-- PREVIEW KONTRAK -->
                <li class="nav-item" role="presentation">
                  <button class="nav-link"
                    id="kontrak-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#kontrak-tab-pane"
                    type="button"
                    role="tab"
                    aria-controls="kontrak-tab-pane"
                    aria-selected="false">
                    <i class="fas fa-file-contract me-1"></i>
                    Preview Kontrak
                  </button>
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
                  <div class="tab-pane fade" id="user-tab-pane" role="tabpanel" aria-labelledby="user-tab" tabindex="0">
                    <div class="card-body p-4">
                      <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title fw-semibold">Data User Faskes</h5>
                        <!-- Grup tombol di sisi kanan -->
                        <div class="d-flex ms-auto gap-2">
                          <button class="btn btn-primary" id="btnTambahUser"><i class="fas fa-plus"></i> Tambah</button>
                        </div>
                      </div>
                      <div class="table-responsive" data-simplebar>
                        <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTableUser">
                          <thead>
                            <tr>
                              <th class="text-dark fw-normal col-1">Fullname</th>
                              <th class="text-dark fw-normal col-1">Username</th>
                              <th scope="col" class="text-dark fw-normal">Roles</th>
                              <th class="text-dark fw-normal">Registrasi</th>
                              <th scope="col" class="text-dark fw-normal text-center col-1">Status</th>
                              <th scope="col" class="text-dark fw-normal text-center col-1">Actions</th>
                            </tr>
                          </thead>
                          <tbody></tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  <!-- PREVIEW KONTRAK -->
                  <div class="tab-pane fade"
                    id="kontrak-tab-pane"
                    role="tabpanel"
                    aria-labelledby="kontrak-tab"
                    tabindex="0">

                    <div class="card-body p-4">

                      <div class="d-flex justify-content-between align-items-center mb-4">

                        <div>
                          <h5 class="card-title fw-semibold mb-1">
                            <i class="fas fa-file-contract me-2"></i>
                            Preview Kontrak
                          </h5>

                          <small class="text-muted">
                            Preview dokumen kontrak kerja sama faskes
                          </small>
                        </div>

                        <div class="d-flex gap-2">

                          <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            id="btnRefreshKontrak">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh
                          </button>

                          <button type="button"
                            class="btn btn-primary btn-sm"
                            id="btnPrintKontrak">
                            <i class="fas fa-print me-1"></i>
                            Cetak Kontrak
                          </button>

                        </div>

                      </div>

                      <div class="border rounded bg-light p-2">

                        <div id="kontrakPreview"
                          style="
      min-height: 700px;
      background: #fff;
      border-radius: 4px;
      overflow: hidden;
  ">

                          <iframe
                            id="kontrakFrame"
                            title="Preview Kontrak"
                            src="about:blank"
                            style="
      width: 100%;
      height: 1100px;
      border: 0;
      display: block;
      background: #fff;
  "></iframe>

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

<div class="modal fade" id="userModal">
  <div class="modal-dialog">
    <form id="userForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">User Faskes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id_user" id="user_id">

        <div class="mb-3">
          <label>Fullname</label>
          <input type="text" name="fullname" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
          <label>Role</label>
          <select name="roles" class="form-select">
            <option value="">PILIH</option>
            <option value="bidan">Bidan</option>
            <option value="perawat">Perawat</option>
            <option value="receptionis">Receptionis</option>
            <option value="kasir">Kasir</option>
            <option value="apoteker">Apoteker</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

html
<script>
  /* =========================================================
   MASTER FASKES DETAIL
   =========================================================
   Fungsi:
   1. Payment
   2. Profile Faskes
   3. User
   4. Preview Kontrak

   CATATAN:
   Profile Faskes dikirim menggunakan POST.
   Controller faskesController yang menentukan:
   - INSERT jika ms_faskes belum ada
   - UPDATE jika ms_faskes sudah ada
   ========================================================= */


  /* =========================================================
     PAYMENT
     ========================================================= */

  const paymentApi = 'controller/master/faskesPaymentController';

  const urlParams = new URLSearchParams(window.location.search);
  const no = urlParams.get('no');

  let table;


  /* ---------------------------------------------------------
     LOAD TABLE PEMBAYARAN
     --------------------------------------------------------- */

  function loadPaymentTable() {

    if (!no) {
      console.warn('Parameter ?no= tidak ditemukan untuk Payment');
      return;
    }

    table = $('#periodeTable').DataTable({
      destroy: true,

      ajax: {
        url: paymentApi + '?no=' + encodeURIComponent(no),
        dataSrc: 'data'
      },

      columns: [

        {
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

          render: $.fn.dataTable.render.number(
            '.',
            ',',
            0,
            'Rp '
          )
        },

        {
          data: 'payment_note'
        },

        {
          data: 'payment_file',

          render: function(data) {

            if (!data) {
              return '-';
            }

            return `
                        <a
                            href="uploads/${encodeURIComponent(data)}"
                            target="_blank"
                            rel="noopener"
                        >
                            File
                        </a>
                    `;
          }
        },

        {
          data: 'payment_status',

          render: function(data) {

            return data == 1 ?
              'Paid' :
              'Pending';
          }
        },

        {
          data: null,

          render: function(row) {

            return `
                        <button
                            type="button"
                            class="btn btn-warning btn-sm edit-btn"
                            data-id="${row.id_payment}"
                        >
                            Edit
                        </button>

                        <button
                            type="button"
                            class="btn btn-danger btn-sm delete-btn"
                            data-id="${row.id_payment}"
                        >
                            Hapus
                        </button>
                    `;
          }
        }
      ]
    });
  }


  /* ---------------------------------------------------------
     TAMBAH PAYMENT
     --------------------------------------------------------- */

  $(document).on('click', '#btnTambah', function() {

    const form = document.getElementById('paymentForm');

    if (!form) {
      console.error('Form #paymentForm tidak ditemukan');
      return;
    }

    form.reset();

    $('#payment_id').val('');

    $('#payment_order_number').val(no || '');

    $('#paymentModal').modal('show');
  });


  /* ---------------------------------------------------------
     SUBMIT PAYMENT
     INSERT / UPDATE
     --------------------------------------------------------- */

  $(document).on('submit', '#paymentForm', function(e) {

    e.preventDefault();

    const form = this;

    const id = $('#payment_id').val();

    const formData = new URLSearchParams(
      new FormData(form)
    );

    /*
     * Pastikan order number selalu berasal dari URL.
     */
    if (no) {
      formData.set('order_number', no);
    }

    const url = paymentApi + (
      id ?
      '?id=' + encodeURIComponent(id) :
      ''
    );

    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method: method,

        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },

        body: formData
      })

      .then(function(res) {

        return res.json();
      })

      .then(function(res) {

        console.log('PAYMENT RESPONSE:', res);

        if (res.status === 'success') {

          Swal.fire(
            'Berhasil!',
            res.message || 'Data pembayaran berhasil disimpan.',
            'success'
          );

          $('#paymentModal').modal('hide');

          if (table) {
            table.ajax.reload(null, false);
          }

        } else {

          Swal.fire(
            'Gagal!',
            res.message || 'Data pembayaran gagal disimpan.',
            'error'
          );
        }
      })

      .catch(function(err) {

        console.error('PAYMENT ERROR:', err);

        Swal.fire(
          'Error!',
          'Terjadi kesalahan saat menyimpan pembayaran.',
          'error'
        );
      });
  });


  /* ---------------------------------------------------------
     EDIT PAYMENT
     --------------------------------------------------------- */

  $(document).on('click', '.edit-btn', function() {

    const id = $(this).data('id');

    if (!id) {
      Swal.fire(
        'Warning!',
        'ID pembayaran tidak ditemukan.',
        'warning'
      );

      return;
    }

    fetch(
        paymentApi +
        '?id=' +
        encodeURIComponent(id)
      )

      .then(function(res) {

        return res.json();
      })

      .then(function(res) {

        console.log('PAYMENT DETAIL:', res);

        if (res.status !== 'success') {

          Swal.fire(
            'Gagal!',
            res.message || 'Data pembayaran tidak ditemukan.',
            'error'
          );

          return;
        }

        const d = res.data || {};

        $('#payment_id').val(
          d.id_payment || ''
        );

        $('#payment_order_number').val(
          d.order_number || no || ''
        );

        $('[name="tanggal"]').val(
          d.payment_date || ''
        );

        $('[name="metode"]').val(
          d.payment_method || ''
        );

        $('[name="nominal"]').val(
          d.payment_amount || ''
        );

        $('[name="keterangan"]').val(
          d.payment_note || ''
        );

        $('#paymentModal').modal('show');
      })

      .catch(function(err) {

        console.error('PAYMENT DETAIL ERROR:', err);

        Swal.fire(
          'Error!',
          'Gagal mengambil data pembayaran.',
          'error'
        );
      });
  });


  /* ---------------------------------------------------------
     DELETE PAYMENT
     --------------------------------------------------------- */

  $(document).on('click', '.delete-btn', function() {

    const id = $(this).data('id');

    if (!id) {
      Swal.fire(
        'Warning!',
        'ID pembayaran tidak ditemukan.',
        'warning'
      );

      return;
    }

    Swal.fire({

        title: 'Hapus data?',

        text: 'Data pembayaran akan dihapus.',

        icon: 'warning',

        showCancelButton: true,

        confirmButtonText: 'Ya, hapus',

        cancelButtonText: 'Batal'

      })

      .then(function(result) {

        if (!result.isConfirmed) {
          return;
        }

        fetch(
            paymentApi +
            '?id=' +
            encodeURIComponent(id), {
              method: 'DELETE'
            }
          )

          .then(function(res) {

            return res.json();
          })

          .then(function(res) {

            console.log('DELETE PAYMENT RESPONSE:', res);

            if (res.status === 'success') {

              Swal.fire(
                'Berhasil',
                res.message || 'Data pembayaran dihapus.',
                'success'
              );

              if (table) {
                table.ajax.reload(null, false);
              }

            } else {

              Swal.fire(
                'Gagal!',
                res.message || 'Data gagal dihapus.',
                'error'
              );
            }
          })

          .catch(function(err) {

            console.error('DELETE PAYMENT ERROR:', err);

            Swal.fire(
              'Error!',
              'Gagal menghapus data pembayaran.',
              'error'
            );
          });
      });
  });


  /* =========================================================
     PROFILE FASKES
     ========================================================= */

  const faskesViewApi =
    'controller/master/faskesDetailController';

  const faskesSaveApi =
    'controller/master/faskesController';


  /* ---------------------------------------------------------
     LOAD PROFILE FASKES
     --------------------------------------------------------- */

  function loadFaskesToForm(noFaskes) {

    if (!noFaskes) {

      console.warn(
        'Parameter nomor/order Faskes tidak ditemukan.'
      );

      return;
    }

    fetch(
        faskesViewApi +
        '?no=' +
        encodeURIComponent(noFaskes)
      )

      .then(function(res) {

        return res.json();
      })

      .then(function(res) {

        console.log('FASKES DETAIL RESPONSE:', res);

        /*
         * Jika controller berhasil menemukan
         * setting_clinic tetapi ms_faskes belum ada,
         * controller tetap seharusnya mengembalikan
         * status success dengan data kosong/null.
         */

        if (res.status !== 'success') {

          Swal.fire(
            'Gagal!',
            res.message || 'Gagal mengambil data Faskes.',
            'error'
          );

          return;
        }

        const data = res.data || {};

        /*
         * Isi semua field berdasarkan NAME.
         */
        Object.keys(data).forEach(function(key) {

          const elements =
            document.querySelectorAll(
              `[name="${key}"]`
            );

          if (!elements.length) {

            console.warn(
              'Field tidak ditemukan:',
              key
            );

            return;
          }

          elements.forEach(function(el) {

            /*
             * Jangan mengubah input file.
             */
            if (
              el.type === 'file'
            ) {
              return;
            }

            /*
             * Checkbox.
             */
            if (
              el.type === 'checkbox'
            ) {

              el.checked =
                data[key] == 1 ||
                data[key] === true ||
                data[key] === '1';

              return;
            }

            /*
             * Radio.
             */
            if (
              el.type === 'radio'
            ) {

              el.checked =
                String(el.value) ===
                String(data[key] ?? '');

              return;
            }

            el.value =
              data[key] ?? '';
          });
        });


        /*
         * =====================================================
         * ID FASKES
         * =====================================================
         *
         * Jangan menjadikan ketiadaan id_faskes sebagai error.
         *
         * Jika ms_faskes belum ada:
         * id_faskes boleh kosong.
         *
         * order_number / no tetap dipakai sebagai
         * identitas clinic pada saat POST.
         */

        const idField =
          document.getElementById('faskes_id');

        if (idField) {

          idField.value =
            data.id_faskes ||
            '';
        }


        /*
         * Pastikan order_number tetap menggunakan
         * parameter URL.
         */
        const orderFields =
          document.querySelectorAll(
            '[name="order_number"]'
          );

        orderFields.forEach(function(el) {

          /*
           * order_number adalah readonly.
           * Tetap isi dengan no URL.
           */
          el.value =
            noFaskes;
        });


        /*
         * Simpan id clinic apabila controller
         * mengembalikannya.
         */
        const clinicIdField =
          document.getElementById('id_clinic');

        if (clinicIdField) {

          clinicIdField.value =
            data.id_clinic ||
            noFaskes ||
            '';
        }

      })

      .catch(function(err) {

        console.error(
          'LOAD FASKES ERROR:',
          err
        );

        Swal.fire(
          'Error!',
          'Gagal load data Profile Faskes.',
          'error'
        );
      });
  }


  /* ---------------------------------------------------------
     SUBMIT PROFILE FASKES
     =========================================================
     PENTING:
     Tidak lagi menggunakan PUT.

     Selalu POST.

     Controller akan menentukan:
     - INSERT jika ms_faskes belum ada
     - UPDATE jika ms_faskes sudah ada
     --------------------------------------------------------- */

  function initFaskesForm() {

    /*
     * Pada halaman Anda form utamanya bernama
     * #programForm.
     *
     * Tetap support #faskesForm jika suatu saat
     * ID form tersebut digunakan.
     */
    const form =
      document.getElementById('faskesForm') ||
      document.getElementById('programForm');

    if (!form) {

      console.error(
        'Form Profile Faskes tidak ditemukan.'
      );

      return;
    }


    /*
     * Hindari event submit terpasang dua kali.
     */
    if (
      form.dataset.faskesSubmitInitialized === '1'
    ) {
      return;
    }

    form.dataset.faskesSubmitInitialized = '1';


    form.addEventListener(
      'submit',
      function(e) {

        e.preventDefault();


        /*
         * Ambil FormData.
         */
        const rawFormData =
          new FormData(form);


        /*
         * Convert ke URLSearchParams.
         */
        const formData =
          new URLSearchParams();


        /*
         * Masukkan semua field form.
         */
        rawFormData.forEach(
          function(value, key) {

            /*
             * Jangan kirim file melalui endpoint
             * profile ini jika bukan bagian dari
             * controller faskes.
             */
            if (
              value instanceof File
            ) {
              return;
            }

            formData.append(
              key,
              value
            );
          }
        );


        /*
         * =================================================
         * WAJIB:
         * order_number berasal dari URL ?no=
         * =================================================
         */
        if (no) {

          formData.set(
            'order_number',
            no
          );
        }


        /*
         * =================================================
         * id_faskes
         * =================================================
         *
         * Jika sudah ada, kirim.
         *
         * Jika belum ada, JANGAN menggagalkan proses.
         *
         * Controller akan melakukan INSERT berdasarkan
         * id_clinic/order_number.
         */
        const idField =
          document.getElementById('faskes_id');

        const idFaskes =
          idField ?
          String(
            idField.value || ''
          ).trim() :
          '';


        if (idFaskes) {

          formData.set(
            'id_faskes',
            idFaskes
          );
        }


        /*
         * Jika ada hidden id_clinic, kirim juga.
         */
        const clinicField =
          document.getElementById('id_clinic');

        const idClinic =
          clinicField ?
          String(
            clinicField.value || ''
          ).trim() :
          '';


        if (idClinic) {

          formData.set(
            'id_clinic',
            idClinic
          );
        }


        /*
         * Jika id_clinic belum ada tetapi ?no=
         * tersedia, gunakan no sebagai fallback.
         */
        if (
          !formData.get('id_clinic') &&
          no
        ) {

          formData.set(
            'id_clinic',
            no
          );
        }


        /*
         * DEBUG
         */
        console.group(
          'SAVE PROFILE FASKES'
        );

        for (
          const [
            key,
            value
          ] of formData.entries()
        ) {

          console.log(
            key,
            ':',
            value
          );
        }

        console.groupEnd();


        /*
         * =================================================
         * VALIDASI MINIMAL
         * =================================================
         *
         * Jangan validasi id_faskes.
         *
         * Karena record ms_faskes mungkin memang belum ada.
         */

        if (!no && !formData.get('id_clinic')) {

          Swal.fire(
            'Warning!',
            'ID Faskes / Clinic tidak ditemukan.',
            'warning'
          );

          return;
        }


        /*
         * =================================================
         * SUBMIT
         * =================================================
         *
         * SELALU POST.
         *
         * Controller:
         * POST + ms_faskes belum ada = INSERT
         * POST + ms_faskes sudah ada    = UPDATE
         */
        fetch(
            faskesSaveApi, {
              method: 'POST',

              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              },

              body: formData
            }
          )

          .then(function(res) {

            /*
             * Ambil response text terlebih dahulu
             * supaya jika PHP mengeluarkan warning/error,
             * mudah dilihat di console.
             */
            return res.text();
          })

          .then(function(rawResponse) {

            console.log(
              'FASKES RAW RESPONSE:',
              rawResponse
            );


            let res;

            try {

              res =
                JSON.parse(
                  rawResponse
                );

            } catch (error) {

              console.error(
                'INVALID JSON RESPONSE:',
                error
              );

              Swal.fire(
                'Error!',
                'Response server bukan JSON. Cek error PHP di server.',
                'error'
              );

              return;
            }


            console.log(
              'FASKES SAVE RESPONSE:',
              res
            );


            if (
              res.status === 'success'
            ) {

              /*
               * Jika INSERT, controller biasanya
               * mengembalikan id_faskes.
               *
               * Simpan ke hidden field supaya
               * setelah berhasil, data berikutnya
               * mengetahui ID tersebut.
               */
              if (
                res.id_faskes &&
                idField
              ) {

                idField.value =
                  res.id_faskes;
              } else if (
                res.data &&
                res.data.id_faskes &&
                idField
              ) {

                idField.value =
                  res.data.id_faskes;
              }


              Swal.fire(
                'Berhasil!',
                res.message ||
                'Profile Faskes berhasil disimpan.',
                'success'
              );

            } else {

              Swal.fire(
                'Gagal!',
                res.message ||
                'Profile Faskes gagal disimpan.',
                'error'
              );
            }
          })

          .catch(function(err) {

            console.error(
              'SAVE FASKES ERROR:',
              err
            );

            Swal.fire(
              'Error!',
              'Gagal menyimpan Profile Faskes.',
              'error'
            );
          });

      }
    );
  }


  /* ---------------------------------------------------------
     INITIALIZE FASKES
     --------------------------------------------------------- */

  document.addEventListener(
    'DOMContentLoaded',
    function() {

      /*
       * Ambil ?no=
       */
      const params =
        new URLSearchParams(
          window.location.search
        );

      const noFaskes =
        params.get('no');


      /*
       * Load Profile.
       */
      if (noFaskes) {

        loadFaskesToForm(
          noFaskes
        );
      }


      /*
       * Init submit form.
       */
      initFaskesForm();

    }
  );


  /* =========================================================
     USER
     ========================================================= */

  const userApi =
    'controller/master/userControllerAdmin';

  const urlParamsUser =
    new URLSearchParams(
      window.location.search
    );

  const noUser =
    urlParamsUser.get('no');

  let tableUser;


  /* ---------------------------------------------------------
     LOAD USER TABLE
     --------------------------------------------------------- */

  function loadUserTable() {

    if (!noUser) {

      console.warn(
        'Parameter ?no= tidak ditemukan untuk User'
      );

      return;
    }


    tableUser =
      $('#periodeTableUser').DataTable({

        destroy: true,

        ajax: {

          url: userApi +
            '?no=' +
            encodeURIComponent(noUser),

          dataSrc: 'data'
        },

        columns: [

          {
            data: 'fullname'
          },

          {
            data: 'username'
          },

          {
            data: 'roles'
          },

          {
            data: 'created_at'
          },

          {
            data: 'status',

            className: 'text-center',

            render: function(
              data,
              type,
              row
            ) {

              const checked =
                data == 1 ?
                'checked' :
                '';

              return `
                                <div class="form-check form-switch d-flex justify-content-center">

                                    <input
                                        class="form-check-input toggleStatus"
                                        type="checkbox"
                                        data-id="${row.id_user}"
                                        ${checked}
                                    >

                                </div>
                            `;
            }
          },

          {
            data: null,

            className: 'text-center',

            render: function(row) {

              return `
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm editUser"
                                    data-id="${row.id_user}"
                                >
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm deleteUser"
                                    data-id="${row.id_user}"
                                >
                                    Hapus
                                </button>
                            `;
            }
          }
        ]
      });
  }


  /* ---------------------------------------------------------
     INIT USER TABLE
     --------------------------------------------------------- */

  $(document).ready(
    function() {

      loadPaymentTable();

      loadUserTable();

    }
  );


  /* ---------------------------------------------------------
     TAMBAH USER
     --------------------------------------------------------- */

  $(document).on(
    'click',
    '#btnTambahUser',
    function() {

      const form =
        document.getElementById(
          'userForm'
        );

      if (!form) {

        console.error(
          'Form #userForm tidak ditemukan'
        );

        return;
      }

      form.reset();

      $('#user_id').val('');

      $('#userModal').modal('show');
    }
  );


  /* ---------------------------------------------------------
     SUBMIT USER
     INSERT / UPDATE
     --------------------------------------------------------- */

  $(document).on(
    'submit',
    '#userForm',
    function(e) {

      e.preventDefault();

      const form = this;

      const id =
        $('#user_id').val();

      const formData =
        new URLSearchParams(
          new FormData(form)
        );


      const url =
        userApi +
        '?no=' +
        encodeURIComponent(noUser) +
        (
          id ?
          '&id=' +
          encodeURIComponent(id) :
          ''
        );


      fetch(
          url, {
            method: id ?
              'PUT' : 'POST',

            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },

            body: formData
          }
        )

        .then(
          function(res) {

            return res.json();
          }
        )

        .then(
          function(res) {

            console.log(
              'USER RESPONSE:',
              res
            );

            if (
              res.status ===
              'success'
            ) {

              Swal.fire(
                'Berhasil!',
                res.message ||
                'Data user tersimpan.',
                'success'
              );

              $('#userModal')
                .modal('hide');

              if (tableUser) {

                tableUser.ajax.reload(
                  null,
                  false
                );
              }

            } else {

              Swal.fire(
                'Error!',
                res.message ||
                'Data user gagal disimpan.',
                'error'
              );
            }
          }
        )

        .catch(
          function(err) {

            console.error(
              'USER ERROR:',
              err
            );

            Swal.fire(
              'Error!',
              'Gagal menyimpan data user.',
              'error'
            );
          }
        );
    }
  );


  /* ---------------------------------------------------------
     EDIT USER
     --------------------------------------------------------- */

  $(document).on(
    'click',
    '.editUser',
    function() {

      const id =
        $(this).data('id');

      if (!id) {

        Swal.fire(
          'Warning!',
          'ID user tidak ditemukan.',
          'warning'
        );

        return;
      }


      fetch(
          userApi +
          '?no=' +
          encodeURIComponent(noUser) +
          '&id=' +
          encodeURIComponent(id)
        )

        .then(
          function(res) {

            return res.json();
          }
        )

        .then(
          function(res) {

            console.log(
              'USER DETAIL:',
              res
            );


            if (
              res.status !==
              'success'
            ) {

              Swal.fire(
                'Gagal!',
                res.message ||
                'Data user tidak ditemukan.',
                'error'
              );

              return;
            }


            const d =
              res.data || {};


            $('#user_id').val(
              d.id_user || ''
            );

            $('[name="fullname"]').val(
              d.fullname || ''
            );

            $('[name="username"]').val(
              d.username || ''
            );

            $('[name="roles"]').val(
              d.roles || ''
            );


            $('#userModal')
              .modal('show');
          }
        )

        .catch(
          function(err) {

            console.error(
              'USER DETAIL ERROR:',
              err
            );

            Swal.fire(
              'Error!',
              'Gagal mengambil data user.',
              'error'
            );
          }
        );
    }
  );


  /* ---------------------------------------------------------
     DELETE USER
     --------------------------------------------------------- */

  $(document).on(
    'click',
    '.deleteUser',
    function() {

      const id =
        $(this).data('id');


      if (!id) {

        Swal.fire(
          'Warning!',
          'ID user tidak ditemukan.',
          'warning'
        );

        return;
      }


      Swal.fire({

          title: 'Hapus user?',

          text: 'Data user akan dihapus.',

          icon: 'warning',

          showCancelButton: true,

          confirmButtonText: 'Ya, hapus',

          cancelButtonText: 'Batal'

        })

        .then(
          function(result) {

            if (
              !result.isConfirmed
            ) {
              return;
            }


            fetch(
                userApi +
                '?no=' +
                encodeURIComponent(noUser) +
                '&id=' +
                encodeURIComponent(id), {
                  method: 'DELETE'
                }
              )

              .then(
                function(res) {

                  return res.json();
                }
              )

              .then(
                function(res) {

                  console.log(
                    'DELETE USER RESPONSE:',
                    res
                  );


                  if (
                    res.status ===
                    'success'
                  ) {

                    Swal.fire(
                      'Deleted!',
                      res.message ||
                      'Data user dihapus.',
                      'success'
                    );


                    if (
                      tableUser
                    ) {

                      tableUser.ajax.reload(
                        null,
                        false
                      );
                    }

                  } else {

                    Swal.fire(
                      'Gagal!',
                      res.message ||
                      'Data user gagal dihapus.',
                      'error'
                    );
                  }
                }
              )

              .catch(
                function(err) {

                  console.error(
                    'DELETE USER ERROR:',
                    err
                  );

                  Swal.fire(
                    'Error!',
                    'Gagal menghapus user.',
                    'error'
                  );
                }
              );
          }
        );
    }
  );


  /* ---------------------------------------------------------
     TOGGLE STATUS USER
     --------------------------------------------------------- */

  $(document).on(
    'change',
    '.toggleStatus',
    function() {

      const id =
        $(this).data('id');

      const status =
        $(this).is(':checked') ?
        1 :
        0;


      if (!id) {

        Swal.fire(
          'Warning!',
          'ID user tidak ditemukan.',
          'warning'
        );

        return;
      }


      fetch(
          userApi +
          '?no=' +
          encodeURIComponent(noUser) +
          '&id=' +
          encodeURIComponent(id) +
          '&toggle_status=1', {
            method: 'PUT',

            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },

            body: new URLSearchParams({
              id_user: id,

              status: status
            })
          }
        )

        .then(
          function(res) {

            return res.json();
          }
        )

        .then(
          function(res) {

            console.log(
              'TOGGLE USER RESPONSE:',
              res
            );


            if (
              res.status ===
              'success'
            ) {

              Swal.fire({

                icon: 'success',

                title: 'Updated',

                text: 'Status user berhasil diubah.',

                timer: 1200,

                showConfirmButton: false
              });

            } else {

              Swal.fire(
                'Error!',
                res.message ||
                'Status user gagal diubah.',
                'error'
              );
            }
          }
        )

        .catch(
          function(err) {

            console.error(
              'TOGGLE USER ERROR:',
              err
            );

            Swal.fire(
              'Error!',
              'Gagal update status user.',
              'error'
            );
          }
        );
    }
  );


  /* =========================================================
     PREVIEW KONTRAK
     ========================================================= */

  document.addEventListener(
    'DOMContentLoaded',
    function() {

      const params =
        new URLSearchParams(
          window.location.search
        );

      const noKontrak =
        params.get('no');


      const frame =
        document.getElementById(
          'kontrakFrame'
        );

      const btnRefresh =
        document.getElementById(
          'btnRefreshKontrak'
        );

      const btnPrint =
        document.getElementById(
          'btnPrintKontrak'
        );


      /* -------------------------------------------------
         LOAD KONTRAK
         ------------------------------------------------- */

      function loadKontrak() {

        if (!frame) {

          console.error(
            'Element #kontrakFrame tidak ditemukan'
          );

          return;
        }


        if (!noKontrak) {

          console.error(
            'Parameter ?no= tidak ditemukan'
          );

          return;
        }


        frame.src =
          'module/administrator/kontrak.php?no=' +
          encodeURIComponent(noKontrak) +
          '&t=' +
          Date.now();


        console.log(
          'Preview kontrak:',
          frame.src
        );
      }


      /* -------------------------------------------------
         LOAD OTOMATIS
         ------------------------------------------------- */

      loadKontrak();


      /* -------------------------------------------------
         REFRESH
         ------------------------------------------------- */

      if (btnRefresh) {

        btnRefresh.addEventListener(
          'click',
          function() {

            if (!noKontrak) {

              Swal.fire(
                'Warning!',
                'ID Faskes tidak ditemukan.',
                'warning'
              );

              return;
            }


            loadKontrak();
          }
        );
      }


      /* -------------------------------------------------
         PRINT
         ------------------------------------------------- */

      if (btnPrint) {

        btnPrint.addEventListener(
          'click',
          function() {

            if (
              !frame ||
              !frame.contentWindow
            ) {

              Swal.fire(
                'Warning!',
                'Preview kontrak belum dimuat.',
                'warning'
              );

              return;
            }


            frame.contentWindow
              .focus();

            frame.contentWindow
              .print();
          }
        );
      }

    }
  );
</script>


</html>