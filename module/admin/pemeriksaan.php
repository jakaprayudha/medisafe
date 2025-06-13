<?php
$title = 'Pemeriksaan';
require '../../controller/view.php';
require '../../utility/env.php';
// Memuat file .env
$env = loadEnv();
// Mengambil nilai API_URL dari environment
$apiUrl = getenv('API_URL');
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
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Pemeriksaan Pasien Poliklinik</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">

                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                          <th class="text-dark fw-normal">Registrasi</th>
                          <th scope="col" class="text-dark fw-normal">Nomor RM</th>
                          <th scope="col" class="text-dark fw-normal">Nama Pasien</th>
                          <th scope="col" class="text-dark fw-normal">P/L</th>
                          <th scope="col" class="text-dark fw-normal">TTL</th>
                          <th class="text-dark fw-normal">Dokter</th>
                          <th class="text-dark fw-normal">Poliklinik</th>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>

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

<script>
  // Mengambil nilai API_URL dari PHP
  const apiUrl = '<?php echo $apiUrl . 'visit/' . 'registrasiController' ?>';
  $(document).ready(function() {
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
                    <a href="module/admin/pemeriksaan_details?no=${row.nomor_visit}&rm=${row.nomor_rm}">
                      <button class="btn btn-primary">Pemeriksaan</button>
                    </a>
                  </div>
              `,
              "tanggal": row.tanggal + ' ' + row.waktu,
              "nomor_rm": row.nomor_rm,
              "nama_pasien": row.nama_pasien,
              "gender": row.gender,
              "ttl": row.tempat_lahir + ' ' + row.tanggal_lahir,
              "dokter": row.dokter + ' ' + row.dokter,
              "layanan": row.layanan + ' ' + row.layanan,
              "status_visit": '<span class="badge ' + (row.status_visit == 1 ? 'bg-success' : 'bg-danger') + ' d-block text-center">' + (row.status_visit == 1 ? 'Selesai' : 'Belum') + '</span>'
            };
          });
        }
      },
      "columns": [{
          "data": "actions"
        }, {
          "data": "tanggal"
        },
        {
          "data": "nomor_rm"
        },
        {
          "data": "nama_pasien"
        },
        {
          "data": "gender"
        },
        {
          "data": "ttl"
        },
        {
          "data": "dokter"
        },
        {
          "data": "layanan"
        },
        {
          "data": "status_visit"
        }

      ]
    });

    // Handle form submission for adding 
    document.getElementById("addForm").addEventListener("submit", function(event) {
      event.preventDefault();

      const data = document.getElementById("data").value;
      const layanan = document.getElementById("layanan").value;
      const dokter = document.getElementById("dokter").value;
      const catatan = document.getElementById("catatan").value;

      const formData = new URLSearchParams({
        data: data,
        layanan: layanan,
        dokter: dokter,
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

</html>