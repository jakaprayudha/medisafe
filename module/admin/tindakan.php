<?php
$title = 'Pemeriksaan';
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
            <nav>
              <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <a href="module/admin/pemeriksaan_a?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                  <button class="nav-link ">Pemeriksaan Medis</button>
                </a>
                <a href="module/admin/permintaan_farmasi?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                  <button class="nav-link ">Permintaan Farmasi</button>
                </a>
                <a href="module/admin/vaksin?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                  <button class="nav-link">Vaksin</button>
                </a>
                <a href="module/admin/tindakan?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                  <button class="nav-link active">Tindakan</button>
                </a>
                <a href="module/admin/riwayat?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                  <button class="nav-link">Riwayat Pengobatan</button>
                </a>
              </div>
            </nav>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4 " class="">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Tindakan</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
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
                          <th class="text-dark fw-normal">Status</th>
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
              <button class="btn btn-warning edit-btn" data-id="${row.id}">Ubah</button>
              <button class="btn btn-danger delete-btn" data-id="${row.id}">Hapus</button>
            </div>
          `,
              "item": row.item,
              "qty": formatter.format(row.qty),
              "harga": rupiahFormatter.format(row.harga),
              "diskon": rupiahFormatter.format(row.diskon),
              "total": rupiahFormatter.format(row.qty * row.harga - row.diskon),
              "status_billing": '<span class="badge ' + (row.status_billing == 1 ? 'bg-success' : 'bg-warning ') + ' d-block text-center">' + (row.status_billing == 1 ? 'Selesai' : 'On Check') + '</span>'
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
          "data": "status_billing"
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

  });
</script>