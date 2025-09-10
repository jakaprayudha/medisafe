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
                  <button class="nav-link active">Vaksin</button>
                </a>
                <a href="module/admin/tindakan?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>">
                  <button class="nav-link">Tindakan</button>
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
                    <h5 class="card-title fw-semibold">Vaksin</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">

                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Tanggal Diberikan</th>
                          <th scope="col" class="text-dark fw-normal">Vaksin</th>
                          <th class="text-dark fw-normal">Oleh</th>
                          <th class="text-dark fw-normal">Catatan</th>
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
<div class="modal fade" id="edit" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Catatan Vaksin</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editForm">
        <input type="hidden" name="nomor_rm" id="nomor_rm" value="<?= $rm ?>">
        <input type="hidden" name="nomor_visit" id="nomor_visit" value="<?= $no ?>">
        <div class="modal-body">
          <div class="mb-3">
            <label for="catatan" class="form-label">Catatan Vaksin </label>
            <textarea name="catatan" id="catatan" class="form-control" rows="10"></textarea>
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
  // Variabel table dibuat global
  let table;

  // Mengambil nilai API_URL dari PHP
  const apiUrl = '<?php echo $apiUrl . 'visit/' . 'vaksin' ?>';

  $(document).ready(function() {
    // Ambil nilai nomor_rm dan nomor_visit dari input tersembunyi
    const nomor_rm = document.getElementById('nomor_rm').value;
    const nomor_visit = document.getElementById('nomor_visit').value;

    // Inisialisasi DataTable
    table = $('#zero_config').DataTable({
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url": apiUrl,
        "type": "GET",
        "data": function(d) {
          d.rm = nomor_rm;
          d.no = nomor_visit;
          d._ts = Date.now(); // Tambahkan timestamp untuk hindari cache
        },
        "dataSrc": function(json) {
          return json.data.map(function(row, index) {
            return {
              "tanggal": row.created_at,
              "item": row.item,
              "oleh": row.dokter,
              "catatan_vaksin": row.catatan_vaksin,
              "actions": `
                <div class="text-center">
                  <button class="btn btn-primary edit-btn" data-id="${row.id}">Catatan</button>
                </div>
              `
            };
          });
        }
      },
      "columns": [{
          "data": "tanggal"
        },
        {
          "data": "item"
        },
        {
          "data": "oleh"
        },
        {
          "data": "catatan_vaksin"
        },
        {
          "data": "actions"
        }
      ]
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
          $('#catatan').val(user.catatan_vaksin);
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
    const catatan = $('#catatan').val();

    const data = {
      iduser: userId,
      catatan: catatan
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
</script>