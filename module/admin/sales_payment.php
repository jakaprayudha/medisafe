<?php
$title = 'Payment';
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
            <div class="col-12">
              <div class="alert alert-primary" role="alert">
                Total Bayar : <span id="total-bayar">Rp0</span><br>
                Total Item : <span id="total-item">0</span> <br>
                Total Diskon : <span id="total-diskon">Rp0</span><br>
              </div>
            </div>
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Produk</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/sales_order">
                        <button class="btn btn-light"><i class="fas fa-arrow-left"></i> Kembali</button>
                      </a>
                      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#add"><i class="fas fa-plus"></i> Pembayaran</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="zero_config">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal">Nama Produk</th>
                          <th class="text-dark fw-normal">Harga</th>
                          <th class="text-dark fw-normal">Satuan</th>
                          <th class="text-dark fw-normal">QTY</th>
                          <th class="text-dark fw-normal">Total</th>
                          <th class="text-dark fw-normal">Diskon 1</th>
                          <th class="text-dark fw-normal">Diskon 2</th>
                          <th class="text-dark fw-normal">Diskon 3</th>
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
        <div class="modal-body">
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="kode" class="form-label">Jenis Bayar </label>
                <select name="" class="form-select" id="">
                  <option value="">Tunai</option>
                  <option value="">Transfer</option>
                </select>
              </div>
            </div>
            <div class="col">
              <div class="mb-3">
                <label for="produk" class="form-label">Total Bayar Akhir <span class="text-danger">*</span> </label>
                <input type="text" name="produk" id="produk" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="mb-3">
                <label for="produk" class="form-label"> Jatuh Tempo <span class="text-danger">*</span> </label>
                <input type="number" name="produk" id="produk" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="alert alert-warning" role="alert">
            Apabila ada harga satuan lainnya, maka bisa gunakan fitur detail products untuk menambahkan harga lainnya.
          </div>
          <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi </label>
            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5"></textarea>
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
<script>
  const apiUrl = '<?php echo $apiUrl . 'sales/' . 'salesPayment' ?>';
  $(document).ready(function() {
    var table = $('#zero_config').DataTable({
      "processing": true,
      "serverSide": true,
      "scrollX": true,
      "ajax": {
        "url": apiUrl,
        "type": "GET",
        "dataSrc": function(json) {
          let totalItem = 0;
          let totalBayar = 0;

          json.data.forEach(function(row) {
            totalItem += parseInt(row.qty) || 0;
            totalBayar += parseFloat(row.total) || 0;
          });

          document.getElementById("total-item").textContent = totalItem;
          document.getElementById("total-bayar").textContent = totalBayar.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
          });

          return json.data.map(function(row) {
            return {
              "produk": `${row.product_code} - ${row.product_name}`,
              "harga_satuan": row.harga_satuan ? parseFloat(row.harga_satuan).toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
              }) : '-',
              "unit": row.unit,
              "qty": row.qty,
              "total": row.total ? parseFloat(row.total).toLocaleString('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
              }) : '-',
              "diskon_1": `<input type="number" class="form-control form-control-sm diskon-input" data-id="${row.id_order}" data-type="diskon_1" value="${row.diskon_1 || 0}" style="width:70px;">`,
              "diskon_2": `<input type="number" class="form-control form-control-sm diskon-input" data-id="${row.id_order}" data-type="diskon_2" value="${row.diskon_2 || 0}" style="width:70px;">`,
              "diskon_3": `<input type="number" class="form-control form-control-sm diskon-input" data-id="${row.id_order}" data-type="diskon_3" value="${row.diskon_3 || 0}" style="width:70px;">`,
              "actions": `
                <div class="text-center">
                  <button class="btn btn-success btn-sm btn-save-diskon" data-id="${row.id_order}" title="Simpan">
                    <i class="fas fa-save"></i>
                  </button>
                  <button class="btn btn-danger btn-sm delete-btn" data-id="${row.id_order}" title="Batal">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              `
            };
          });
        }
      },
      "columns": [{
          "data": "produk"
        },
        {
          "data": "harga_satuan"
        },
        {
          "data": "unit"
        },
        {
          "data": "qty"
        },
        {
          "data": "total"
        },
        {
          "data": "diskon_1"
        },
        {
          "data": "diskon_2"
        },
        {
          "data": "diskon_3"
        },
        {
          "data": "actions"
        }
      ]
    });

    function updateTotal() {
      let totalDiskon = 0;
      let totalBayar = 0;
      let totalItem = 0;

      $('#zero_config tbody tr').each(function() {
        const row = $(this);
        const hargaText = row.find('td:eq(1)').text().replace(/[Rp.,\s]/g, '') || '0';
        const qty = parseFloat(row.find('td:eq(3)').text()) || 0;
        const harga = parseFloat(hargaText) || 0;

        let diskon1 = parseFloat(row.find('input[data-type="diskon_1"]').val()) || 0;
        let diskon2 = parseFloat(row.find('input[data-type="diskon_2"]').val()) || 0;
        let diskon3 = parseFloat(row.find('input[data-type="diskon_3"]').val()) || 0;

        let subtotal = harga * qty;
        let diskonTotal = subtotal * (diskon1 + diskon2 + diskon3) / 100;

        totalDiskon += diskonTotal;
        totalBayar += subtotal - diskonTotal;
        totalItem += qty;
      });

      document.getElementById("total-item").textContent = totalItem;
      document.getElementById("total-bayar").textContent = totalBayar.toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
      });
      document.getElementById("total-diskon").textContent = totalDiskon.toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
      });
    }

    $(document).on('input', '.diskon-input', function() {
      updateTotal();
    });

    $(document).on('click', '.btn-save-diskon', function() {
      const id = $(this).data('id');
      const row = $(this).closest('tr');

      const diskon_1 = row.find('input[data-type="diskon_1"]').val();
      const diskon_2 = row.find('input[data-type="diskon_2"]').val();
      const diskon_3 = row.find('input[data-type="diskon_3"]').val();
      console.log(apiUrl + `?id=${id}`);
      fetch(apiUrl + `?id=${id}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            diskon_1,
            diskon_2,
            diskon_3
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire('Berhasil!', 'Diskon diperbarui.', 'success');
            table.ajax.reload(null, false);
          } else {
            Swal.fire('Gagal!', data.message, 'error');
          }
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Terjadi Kesalahan!', 'Tidak dapat menyimpan perubahan.', 'error');
        });
    });

    document.getElementById("addForm").addEventListener("submit", function(event) {
      event.preventDefault();
      const kode = document.getElementById("kode").value;
      const produk = document.getElementById("produk").value;
      const kategori = document.getElementById("kategori").value;
      const satuan = document.getElementById("satuan").value;
      const harga_beli = document.getElementById("harga_beli").value;
      const harga_jual = document.getElementById("harga_jual").value;
      const description = document.getElementById("deskripsi").value;

      fetch(apiUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            'kode': kode,
            'produk': produk,
            'kategori': kategori,
            'satuan': satuan,
            'harga_beli': harga_beli,
            'harga_jual': harga_jual,
            'description': description
          })
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

    $(document).on('click', '.delete-btn', function() {
      var id = $(this).data('id');
      Swal.fire({
        title: 'Hapus Data?',
        text: "Apakah Anda yakin ingin menghapus data ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(apiUrl + `?id=${id}`, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.status === 'success') {
                Swal.fire('Berhasil!', 'Data berhasil dihapus.', 'success').then(() => {
                  table.ajax.reload(null, false);
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