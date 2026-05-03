<?php
$title = 'Kasir';
require '../../controller/view.php';
require '../../database/connect.php';
require '../../utility/env.php';
// Memuat file .env
$env = loadEnv();
// Mengambil nilai API_URL dari environment
$apiUrl = getenv('API_URL');
$no = $_GET['no'];
$rm = $_GET['rm'];
$check = mysqli_query($koneksi, "SELECT * FROM ms_patient LEFT JOIN pasien_visit ON pasien_visit.id_patient = ms_patient.id_patient WHERE pasien_visit.visit_ID='$no'");
$data = mysqli_fetch_array($check);

$queryObat = mysqli_query($koneksi, "
  SELECT pd.qty, pd.harga
  FROM permintaan_pharmacy_details pd
  INNER JOIN permintaan_pharmacy p 
    ON p.id_permintaan_farmasi = pd.id_permintaan_farmasi
  WHERE p.id_visit = '$no'
");

$totalObat = 0;
while ($row = mysqli_fetch_assoc($queryObat)) {
  $totalObat += $row['qty'] * $row['harga'];
}

// Total dari pasien_billing
$queryBilling = mysqli_query($koneksi, "SELECT * FROM pasien_billing WHERE id_visit='$no'");
$totalBilling = 0;
while ($row = mysqli_fetch_assoc($queryBilling)) {
  $subtotal = ($row['billing_qty'] * $row['billing_price']) - $row['billing_discount'];
  $totalBilling += max(0, $subtotal); // Hindari nilai negatif
}

// Total keseluruhan
$totalKeseluruhan = $totalObat + $totalBilling;

?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
  <style>
    .avatar-circle {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      font-weight: bold;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
    }

    .table-custom tbody tr {
      transition: all 0.2s ease;
    }

    .table-custom tbody tr:hover {
      background: #f8f9fa;
      transform: scale(1.002);
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
              <div class="card shadow-sm border-0 mb-3">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">

                  <!-- LEFT -->
                  <div class="d-flex align-items-center gap-3">
                    <div class="avatar-circle bg-primary text-white">
                      <?= strtoupper(substr($data['patient_name'], 0, 1)) ?>
                    </div>

                    <div>
                      <h5 class="mb-0 fw-semibold">
                        <?= $data['patient_name'] ?>
                      </h5>
                      <small class="text-muted">
                        No RM: <?= $data['nomor_rm'] ?>
                      </small>
                    </div>
                  </div>

                  <!-- RIGHT -->
                  <!-- RIGHT -->
                  <div class="d-flex flex-column align-items-end text-end">

                    <div class="fs-6 text-muted">Total Tagihan</div>

                    <div class="fs-4 fw-bold text-danger">
                      Rp <?= number_format($totalKeseluruhan, 0, ',', '.') ?>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-2">

                      <a href="module/print/struk_billing?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
                        <button class="btn btn-info shadow-sm">
                          <i class="fas fa-print"></i>
                        </button>
                      </a>

                      <?php if ($data['status_bayar'] == 1): ?>

                        <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-success-subtle border border-success text-success shadow-sm">

                          <i class="fas fa-check-circle"></i>
                          <span class="fw-semibold">Lunas</span>

                        </div>

                      <?php else: ?>

                        <button class="btn btn-primary px-3"
                          data-bs-toggle="modal" data-bs-target="#bayar">
                          <i class="fas fa-coins me-1"></i> Bayar
                        </button>

                      <?php endif; ?>

                    </div>

                  </div>

                </div>
              </div>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4 " class="">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-semibold d-flex align-items-center gap-2">
                      💼 Tindakan & Administrasi
                    </h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <button class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Item</th>
                          <th class="text-dark fw-normal">QTY</th>
                          <th class="text-dark fw-normal">Harga</th>
                          <th class="text-dark fw-normal">Diskon</th>
                          <th class="text-dark fw-normal">Total</th>
                          <th class="text-dark fw-normal text-wrap">Catatan</th>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4 ">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">💊 Rincian Farmasi</h5>
                    <div class="d-flex ms-auto gap-2"></div>
                  </div>

                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config2">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Item</th>
                          <th class="text-dark fw-normal">QTY</th>
                          <th class="text-dark fw-normal">Harga</th>
                          <th class="text-dark fw-normal">Total</th>
                        </tr>
                      </thead>
                      <tbody>

                        <?php
                        $getobat = tampildata("SELECT pd.*, mp.*
              FROM permintaan_pharmacy_details pd
              INNER JOIN permintaan_pharmacy p 
                ON p.id_permintaan_farmasi = pd.id_permintaan_farmasi
              INNER JOIN ms_pharmacy mp 
                ON mp.id_pharmacy = pd.id_pharmacy
              WHERE p.id_visit = '$no'
              AND p.id_customer = '$id_customer'
            ");
                        ?>

                        <?php foreach ($getobat as $obat): ?>
                          <tr>
                            <td><?= $obat['pharmacy_name_generic'] ?></td>
                            <td><?= number_format($obat['qty']) ?></td>
                            <td><?= number_format($obat['harga']) ?></td>
                            <td><?= number_format($obat['harga'] * $obat['qty']) ?></td>
                          </tr>
                        <?php endforeach ?>

                      </tbody>
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
<div class="modal fade" id="add" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Data</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addForm">
        <input type="hidden" name="nomor_rm" id="nomor_rm" value="<?= $rm ?>">
        <input type="hidden" name="nomor_visit" id="nomor_visit" value="<?= $no ?>">
        <div class="modal-body">
          <div class="mb-3">
            <label for="item" class="form-label">Nama Tindakan <span class="text-danger">*</span> </label>
            <select name="item" id="item" class="js-example-basic-item" required>
              <option value="">Select Option</option>
              <?php
              $gettarif = tampildata("SELECT * FROM ms_tarif WHERE tarif_status='1'");
              ?>
              <?php foreach ($gettarif as $tarif): ?>
                <option value="<?= $tarif['tarif_name']; ?>"><?= $tarif['tarif_name']; ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label required">Kategori</label>
            <select name="kategori" id="kategori" class="form-select" require>
              <option value="Tindakan">Tindakan</option>
              <option value="Konsultasi">Konsultasi</option>
              <option value="Obat/BMHP/Alkes">Obat/BMHP/Alkes</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>
          <div class="row">
            <div class="col-6">
              <div class="mb-3">
                <label for="qty" class="form-label">Jumlah <span class="text-danger">*</span> </label>
                <input type="number" value="1" name="qty" id="qty" required class="form-control">
              </div>
            </div>
            <div class="col-6">
              <div class="mb-3">
                <label for="diskon" class="form-label">Diskon </label>
                <input type="number" name="diskon" id="diskon" class="form-control">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label for="catatan" class="form-label">Catatan </label>
            <textarea name="catatan" id="catatan" class="form-control" rows="5"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="edit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Perubahan Data</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editForm">
        <input type="hidden" name="nomor_rm" id="nomor_rm" value="<?= $rm ?>">
        <input type="hidden" name="nomor_visit" id="nomor_visit" value="<?= $no ?>">
        <div class="modal-body">
          <div class="mb-3">
            <label for="editjumlah" class="form-label">Biaya Nominal </label>
            <input type="number" name="editjumlah" id="editjumlah" class="form-control">
          </div>
          <div class="mb-3">
            <label for="editdiskon" class="form-label">Diskon </label>
            <input type="number" name="editdiskon" id="editdiskon" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>



<div class="modal fade" id="bayar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Pembayaran</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="bayarForm" onsubmit="return false;">
        <input type="hidden" name="nomor_rm" id="nomor_rm" value="<?= $rm ?>">
        <input type="hidden" name="total" id="total" value="<?= $totalKeseluruhan ?>">
        <input type="hidden" name="nomor_visit" id="nomor_visit" value="<?= $no ?>">
        <div class="modal-body">
          <div class="alert alert-success text-center shadow-sm">
            <div class="fs-3">Total Pembayaran</div>
            <div class="fs-8 fw-bold">
              <strong> Rp <?= number_format($totalKeseluruhan, 0, ',', '.') ?></strong>
            </div>
          </div>
          <div class="mb-3">
            <label for="metode_bayar" class="form-label">Metode Bayar <span class="text-danger">*</span> </label>
            <select name="metode_bayar" id="metode_bayar" required class="form-select">
              <option value="Tunai">Tunai</option>
              <option value="Transfer">Transfer</option>
              <option value="QRIS">QRIS</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="nomor_transaksi" class="form-label">(Nomor Kartu/Nomor ID Transaksi/Nomor Referensi) </label>
            <input type="text" name="nomor_transaksi" id="nomor_transaksi" class="form-control">
          </div>
          <div class="row">
            <div class="col-6">
              <div class="mb-3">
                <label class="form-label">Uang Diterima</label>
                <input type="text" id="uang_diterima" name="uang_diterima" class="form-control w-bold fs-5">
              </div>
            </div>
            <div class="col-6">
              <div class="mb-3">
                <label class="form-label">Kembalian</label>
                <input type="text" id="kembalian" name="kembalian" class="form-control w-bold fs-5 bg-light" readonly>
              </div>
            </div>
            <div class="col-12">
              <small id="warningBayar" class="text-danger d-none">
                ⚠️ Uang kurang dari total pembayaran
              </small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="button" id="btnBayar" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>


</html>

<script>
  $('.js-example-basic-item').select2({
    placeholder: 'Cari Data',
    dropdownParent: '#add'
  });
</script>
<script>
  document.getElementById("btnBayar").addEventListener("click", function() {
    document.getElementById("bayarForm").dispatchEvent(new Event('submit'));
  });
  // Mengambil nilai API_URL dari PHP
  const apiUrl = 'controller/visit/tindakan?no=<?= $_GET['no'] ?>';
  $(document).ready(function() {
    // Formatter untuk angka biasa (qty)
    const formatter = new Intl.NumberFormat('id-ID', {
      style: 'decimal',
      maximumFractionDigits: 0
    });

    // Formatter untuk harga dan total (Rp)
    const rupiahFormatter = new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0
    });
    // Ambil nilai nomor_rm dan nomor_visit (bisa dari input tersembunyi atau field form)
    const nomor_rm = document.getElementById('nomor_rm').value;
    const nomor_visit = document.getElementById('nomor_visit').value;
    // Initialize DataTable
    var table = $('#zero_config').DataTable({
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url": apiUrl, // Ganti dengan URL API yang sesuai
        "type": "GET",
        "dataSrc": function(json) {
          // Format data yang akan ditampilkan dalam tabel
          return json.data.map(function(row, index) {
            return {
              "actions": `
            <div class="text-center">
              <button class="btn btn-warning edit-btn" data-id="${row.id_billing}">Ubah</button>
            </div>
          `,
              "item": row.billing_item,
              "qty": formatter.format(row.billing_qty),
              "harga": rupiahFormatter.format(row.billing_price),
              "diskon": rupiahFormatter.format(row.billing_discount),
              "total": rupiahFormatter.format(row.billing_qty * row.billing_price - row.billing_discount),
              "catatan": row.billing_notes,
            };
          });
        }
      },
      "columns": [{
          "data": "item"
        },
        {
          "data": "qty"
        },
        {
          "data": "harga"
        },
        {
          "data": "diskon"
        },
        {
          "data": "total"
        },
        {
          "data": "catatan"
        },
        {
          "data": "actions"
        }
      ]
    });

    // Handle form submission for adding 
    document.getElementById("addForm").addEventListener("submit", function(event) {
      event.preventDefault();
      const nomor_rm = document.getElementById("nomor_rm").value;
      const nomor_visit = document.getElementById("nomor_visit").value;
      const item = document.getElementById("item").value;
      const qty = document.getElementById("qty").value;
      const diskon = document.getElementById("diskon").value;
      const catatan = document.getElementById("catatan").value;
      const kategori = document.getElementById("kategori").value;

      const formData = new URLSearchParams({
        nomor_rm: nomor_rm,
        nomor_visit: nomor_visit,
        item: item,
        diskon: diskon,
        qty: qty,
        catatan: catatan,
        kategori: kategori
      });

      // ✅ Tampilkan data ke console
      console.log("Data yang dikirim:", formData.toString());

      fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire({
              title: 'Berhasil!',
              text: data.message,
              icon: 'success',
              confirmButtonText: 'OK'
            }).then(() => {
              document.getElementById("addForm").reset();
              $('#add').modal('hide');
              table.ajax.reload(null, false);
            });
          } else {
            Swal.fire({
              title: 'Gagal!',
              text: data.message,
              icon: 'error',
              confirmButtonText: 'Coba Lagi'
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire({
            title: 'Terjadi Kesalahan!',
            text: 'Gagal mengirim data. Coba lagi nanti.',
            icon: 'error',
            confirmButtonText: 'OK'
          });
        });
    });
    // Handle delete action
    $(document).on('click', '.delete-btn', function() {
      var id = $(this).data('id'); // Ambil iduser dari data-id
      Swal.fire({
        title: 'Hapus Data?',
        text: "Apakah Anda yakin ingin menghapus data ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          // Perform the deletion action using GET method
          fetch(apiUrl + `?id=${id}`, {
              method: 'DELETE', // Gunakan GET, bukan DELETE
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', 'Data berhasil dihapus.', 'success').then(() => {
                  table.ajax.reload(null, false); // Reload table without changing page
                });
              } else {
                Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              Swal.fire('Terjadi Kesalahan!', 'Gagal menghapus data. Coba lagi nanti.', 'error');
            });
        }
      });
    });

    // Handle klik tombol edit
    $(document).on('click', '.edit-btn', function() {
      const userId = $(this).data('id');
      console.log("ID DIKLIK:", userId);
      fetch(apiUrl + `&id=${userId}`)
        .then(res => res.json())
        .then(data => {

          console.log("DEBUG:", data); // 🔥 WAJIB

          // ❌ HANDLE ERROR RESPONSE
          if (data.status !== 'success') {
            Swal.fire('Gagal!', data.message || 'Data tidak ditemukan', 'error');
            return;
          }

          // ✅ AMBIL DATA DENGAN AMAN
          const user = data.user;

          if (!user) {
            throw new Error("User undefined dari API");
          }

          // ✅ SAFE ACCESS
          const diskon = user.billing_discount ?? 0;
          const jumlah = user.billing_price ?? 0;

          $('#editdiskon').val(diskon);
          $('#editjumlah').val(jumlah);

          // ✅ SHOW MODAL (BOOTSTRAP 5)
          const modal = new bootstrap.Modal(document.getElementById('edit'));
          modal.show();

          // ✅ SET ID
          $('#editForm').data('id', user.id_billing);

        })
        .catch(err => {
          console.error("ERROR:", err);
          Swal.fire('Error!', err.message, 'error');
        });
    });

    // Handle submit form update
    $('#editForm').on('submit', function(e) {
      e.preventDefault();

      const userId = $(this).data('id');
      const diskon = $('#editdiskon').val();
      const jumlah = $('#editjumlah').val();

      const data = {
        iduser: userId,
        diskon: diskon,
        jumlah: jumlah
      };

      console.log('Data dikirim:', data);

      fetch(apiUrl, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(data).toString(),
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', 'Data berhasil diperbarui.', 'success').then(() => {
              $('#edit').modal('hide');
              table.ajax.reload(null, false); // Reload tanpa reset halaman
            });
          } else {
            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat memperbarui data.', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire('Terjadi Kesalahan!', 'Gagal memperbarui data. Coba lagi nanti.', 'error');
        });
    });

    document.getElementById("btnBayar").addEventListener("click", function() {

      const rawInput = document.getElementById("uang_diterima").value.trim();

      if (rawInput === '') {
        Swal.fire({
          icon: 'warning',
          title: 'Input Kosong',
          text: 'Masukkan jumlah uang terlebih dahulu'
        });
        return;
      }

      const uang = parseInt(rawInput.replace(/\D/g, '')) || 0;
      const total = <?= $totalKeseluruhan ?>;

      if (uang < total) {
        Swal.fire({
          icon: 'warning',
          title: 'Pembayaran Kurang',
          text: 'Uang diterima belum mencukupi total pembayaran!',
        });
        return;
      }

      const id_visit = document.getElementById("nomor_visit").value;
      const metode = document.getElementById("metode_bayar").value;
      const totalClean = document.getElementById("total").value.replace(/\D/g, '');
      const bayar = document.getElementById("uang_diterima").value.replace(/\D/g, '');
      const kembalian = document.getElementById("kembalian").value.replace(/\D/g, '');

      Swal.fire({
        title: "Konfirmasi Pembayaran?",
        text: "Data akan disimpan sebagai Lunas",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Bayar",
        cancelButtonText: "Batal"
      }).then((result) => {

        if (result.isConfirmed) {

          fetch("controller/visit/bayar.php", {
              method: "POST",
              headers: {
                "Content-Type": "application/json"
              },
              body: JSON.stringify({
                id_visit: id_visit,
                metode_bayar: metode,
                total: totalClean,
                bayar: bayar,
                kembalian: kembalian
              })
            })
            .then(res => res.json())
            .then(res => {
              if (res.status === "success") {
                Swal.fire("Berhasil!", res.message, "success")
                  .then(() => location.reload());
              } else {
                Swal.fire("Gagal!", res.message, "error");
              }
            });

        }

      });

    });



  });
</script>

<script>
  const total = <?= $totalKeseluruhan ?>;

  const uangInput = document.getElementById('uang_diterima');
  const kembalianInput = document.getElementById('kembalian');

  // format rupiah
  function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
  }

  uangInput.addEventListener('input', function() {

    // ambil angka saja (hapus titik/koma)
    let uang = this.value.replace(/\D/g, '');
    uang = parseInt(uang) || 0;

    // hitung kembalian
    let kembali = uang - total;

    // tampilkan
    kembalianInput.value = kembali >= 0 ? formatRupiah(kembali) : 0;
  });
</script>
<script>
  const totalBayar = <?= $totalKeseluruhan ?>;
  const btnBayar = document.getElementById('btnBayar');
  const warning = document.getElementById('warningBayar');

  function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
  }

  uangInput.addEventListener('input', function() {

    let uang = this.value.replace(/\D/g, '');
    uang = parseInt(uang) || 0;

    let kembali = uang - totalBayar;

    // tampil kembalian
    kembalianInput.value = kembali >= 0 ? formatRupiah(kembali) : 0;
    if (total == 0) {
      Swal.fire({
        icon: 'info',
        title: 'Tidak Ada Tagihan',
        text: 'Pasien tidak memiliki tagihan, langsung diset lunas',
        confirmButtonText: 'OK'
      }).then(() => {

        fetch("controller/visit/bayar.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
              id_visit: document.getElementById("nomor_visit").value,
              metode_bayar: "AUTO",
              total: 0,
              bayar: 0,
              kembalian: 0
            })
          })
          .then(res => res.json())
          .then(res => {
            if (res.status === "success") {
              Swal.fire("Berhasil!", res.message, "success")
                .then(() => location.reload());
            }
          });

      });

      return;
    }
    // 🔥 VALIDASI + WARNING
    if (uang < totalBayar) {

      btnBayar.disabled = true;
      btnBayar.classList.add('btn-light');
      btnBayar.classList.remove('btn-primary');

      warning.classList.remove('d-none'); // 🔥 INI YANG KAMU MAU

    } else {
      btnBayar.disabled = false;
      btnBayar.classList.remove('btn-light');
      btnBayar.classList.add('btn-primary');

      warning.classList.add('d-none');
    }

  });
</script>