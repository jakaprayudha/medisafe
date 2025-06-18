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
$check = mysqli_query($koneksi, "SELECT * FROM ms_pasien INNER JOIN pasien_visit ON pasien_visit.nomor_rm = ms_pasien.nomor_rm WHERE pasien_visit.nomor_rm='$rm'");
$data = mysqli_fetch_array($check);

// Hitung usia jika data ditemukan
if ($data) {
  $tanggal_lahir = new DateTime($data['tanggal_lahir']);
  $tanggal_visit = new DateTime($data['tanggal']);

  $usia = $tanggal_lahir->diff($tanggal_visit);
}


// Total dari permintaan_farmasi
$queryObat = mysqli_query($koneksi, "SELECT qty, harga FROM permintaan_farmasi WHERE nomor_visit='$no' AND nomor_rm='$rm'");
$totalObat = 0;
while ($row = mysqli_fetch_assoc($queryObat)) {
  $totalObat += $row['qty'] * $row['harga'];
}

// Total dari pasien_billing
$queryBilling = mysqli_query($koneksi, "SELECT qty, harga, diskon FROM pasien_billing WHERE nomor_visit='$no' AND nomor_rm='$rm'");
$totalBilling = 0;
while ($row = mysqli_fetch_assoc($queryBilling)) {
  $subtotal = ($row['qty'] * $row['harga']) - $row['diskon'];
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
                <div class="card-body d-flex justify-content-between align-items-start">
                  <div>
                    <h5 class="card-title">
                      <?= $data['nama_pasien'] ?>
                      <span class="badge bg-warning">RM : <?= $data['nomor_rm'] ?></span>
                    </h5>
                    <p class="card-text">Tanggal Lahir : <?= $data['tanggal_lahir'] ?> <?= $data['gender'] ?></p>
                  </div>
                  <div class="text-end">
                    <h1 class="text-danger" style="font-size: 24px;">
                      Rp <?= number_format($totalKeseluruhan, 0, ',', '.') ?>
                    </h1>
                    <button class="btn btn-success mt-2" data-bs-toggle="modal" data-bs-target="#bayar"><i class="fas fa-coins"></i> Bayar</button>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4 " class="">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Tindakan & Administrasi</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/print/struk_billing?no=<?= $no ?>&rm=<?= $rm ?>" target="_blank">
                        <button class="btn btn-info"><i class="fas fa-print"></i> Cetak</button>
                      </a>
                      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Tambah</button>
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
                <div class="card-body p-4 " class="">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Rincian Farmasi</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config2">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Item</th>
                          <th class="text-dark fw-normal">QTY</th>
                          <th class="text-dark fw-normal">Harga</th>
                          <th class="text-dark fw-normal">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $getobat = tampildata("SELECT * FROM permintaan_farmasi WHERE nomor_visit='$no' AND nomor_rm='$rm'");
                        ?>
                        <?php foreach ($getobat as $obat): ?>
                          <tr>
                            <td><?= $obat['item'] ?></td>
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
              $gettarif = tampildata("SELECT * FROM ms_tarif WHERE status_tarif='1'");
              ?>
              <?php foreach ($gettarif as $tarif): ?>
                <option value="<?= $tarif['nama_tarif']; ?>"><?= $tarif['nama_tarif']; ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="qty" class="form-label">Jumlah <span class="text-danger">*</span> </label>
            <input type="number" value="1" name="qty" id="qty" required class="form-control">
          </div>
          <div class="mb-3">
            <label for="diskon" class="form-label">Diskon </label>
            <input type="number" name="diskon" id="diskon" class="form-control">
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
      <form id="bayarForm">
        <input type="hidden" name="nomor_rm" id="nomor_rm" value="<?= $rm ?>">
        <input type="hidden" name="total" id="total" value="<?= $totalKeseluruhan ?>">
        <input type="hidden" name="nomor_visit" id="nomor_visit" value="<?= $no ?>">
        <div class="modal-body">
          <div class="alert alert-primary" role="alert">
            <h1> Rp <?= number_format($totalKeseluruhan, 0, ',', '.') ?></h1>
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
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
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
  // Mengambil nilai API_URL dari PHP
  const apiUrl = '<?php echo $apiUrl . 'visit/' . 'tindakan' ?>';
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
        "data": function(d) {
          d.rm = nomor_rm;
          d.no = nomor_visit;
        },
        "dataSrc": function(json) {
          // Format data yang akan ditampilkan dalam tabel
          return json.data.map(function(row, index) {
            return {
              "actions": `
            <div class="text-center">
              <button class="btn btn-warning edit-btn" data-id="${row.id}">Diskon</button>
            </div>
          `,
              "item": row.item,
              "qty": formatter.format(row.qty),
              "harga": rupiahFormatter.format(row.harga),
              "diskon": rupiahFormatter.format(row.diskon),
              "total": rupiahFormatter.format(row.qty * row.harga - row.diskon),
              "catatan": row.catatan_billing,
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

      const formData = new URLSearchParams({
        nomor_rm: nomor_rm,
        nomor_visit: nomor_visit,
        item: item,
        diskon: diskon,
        qty: qty,
        catatan: catatan
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

      fetch(apiUrl + `?id=${userId}`)
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            const user = data.user;
            $('#editdiskon').val(user.diskon);
            $('#edit').modal('show');
            $('#editForm').data('id', user.id); // Simpan ID di form
          } else {
            Swal.fire('Gagal!', 'Data user tidak ditemukan.', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          Swal.fire('Terjadi Kesalahan!', 'Gagal memuat data. Coba lagi nanti.', 'error');
        });
    });

    // Handle submit form update
    $('#editForm').on('submit', function(e) {
      e.preventDefault();

      const userId = $(this).data('id');
      const diskon = $('#editdiskon').val();

      const data = {
        iduser: userId,
        diskon: diskon
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



  });
</script>