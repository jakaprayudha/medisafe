<?php
$title = 'Produk';
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
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <style>
    .select2-container .select2-selection--single {
      height: 38px !important;
      padding: 0.375rem 0.75rem;
      line-height: 1.5;
      border: 1px solid #ced4da;
      border-radius: 0.375rem;
    }

    .select2-selection__rendered {
      line-height: 38px !important;
    }

    .select2-selection__arrow {
      height: 38px !important;
    }


    .select2-container {
      width: 100% !important;
    }

    .select2-selection--single {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .select2-container--default .select2-selection--single {
      width: 100% !important;
    }
  </style>
  <style>
    .table-responsive-scroll {
      overflow-x: auto;
    }

    .table td input,
    .table td select {
      min-width: 150px;
    }

    .table td .form-control,
    .table td .form-select {
      width: 100%;
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
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Pembelian Produk</h5>
                    <!-- Grup tombol di sisi kanan -->
                  </div>
                  <div class="table-responsive-scroll" data-simplebar>
                    <form id="formProduk">
                      <table class="table text-nowrap align-middle table-custom mb-0" id="product_table">
                        <thead>
                          <tr>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Satuan</th>
                            <th>Harga Jual</th>
                            <th>QTY</th>
                            <th>Disc 1</th>
                            <th>Disc 2</th>
                            <th>Disc 3</th>
                            <th>Total</th>
                            <th class="text-center">Actions</th>
                          </tr>
                        </thead>
                        <tbody id="product_body">
                          <!-- Baris dinamis akan ditambahkan di sini -->
                        </tbody>
                      </table>
                      <button type="button" id="addRowBtn" class="btn btn-success mt-2">+ Tambah Produk</button>
                      <button type="submit" class="btn btn-primary mt-2">Simpan</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="alert alert-primary d-flex justify-content-between" role="alert">
                <span><strong>Total Bayar:</strong> Rp <span id="totalBayar">0</span></span>
                <span><strong>Total Item:</strong> <span id="totalItem">0</span> produk</span>
                <span><strong>Total Diskon:</strong> Rp <span id="totalDiskon">0</span></span>
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
  let rowCount = 0;

  function updateGrandTotal() {
    let grandTotal = 0;
    let totalItem = 0;
    let totalDiskon = 0;

    $('#product_body tr').each(function() {
      const qty = parseFloat($(this).find('.qty-input').val()) || 0;
      const price = parseFloat($(this).find('.price-input').val()) || 0;
      const disc1 = parseFloat($(this).find('.disc1-input').val()) || 0;
      const disc2 = parseFloat($(this).find('.disc2-input').val()) || 0;
      const disc3 = parseFloat($(this).find('.disc3-input').val()) || 0;

      const totalHarga = qty * price;

      // Diskon berjenjang
      const afterDisc1 = totalHarga * (1 - disc1 / 100);
      const afterDisc2 = afterDisc1 * (1 - disc2 / 100);
      const afterDisc3 = afterDisc2 * (1 - disc3 / 100);

      const totalDiskonItem = totalHarga - afterDisc3;

      $(this).find('.total-output').val(afterDisc3.toLocaleString('id-ID'));

      grandTotal += afterDisc3;
      totalDiskon += totalDiskonItem;

      if (qty > 0) totalItem += 1;
    });

    $('#totalBayar').text(grandTotal.toLocaleString('id-ID'));
    $('#totalItem').text(totalItem);
    $('#totalDiskon').text(totalDiskon.toLocaleString('id-ID'));
  }

  function addRow() {
    rowCount++;

    const newRow = $(`
      <tr>
        <td style="width: 100px">
          <select name="product[${rowCount}][id]" class="form-control product-select" style="width: 100%" required></select>
        </td>
        <td><input type="text" name="product[${rowCount}][name]" class="form-control bg-light" readonly></td>
        <td>
          <select name="product[${rowCount}][unit]" class="form-select unit-select" required style="min-width: 100px">
            <option value="">-</option>
          </select>
        </td>
        <td><input type="number" name="product[${rowCount}][price]" class="form-control bg-light price-input" readonly></td>
        <td><input type="number" name="product[${rowCount}][qty]" class="form-control qty-input" required></td>
      <td><input type="number" name="product[${rowCount}][disc1]" class="form-control disc1-input" value="0"></td>
      <td><input type="number" name="product[${rowCount}][disc2]" class="form-control disc2-input" value="0"></td>
      <td><input type="number" name="product[${rowCount}][disc3]" class="form-control disc3-input" value="0"></td>
      <td><input type="text" name="product[${rowCount}][total]" class="form-control total-output" readonly></td>
        <td class="text-center">
          <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
          <input type="hidden" name="product[${rowCount}][idquotation]" value="<?= $_GET['no'] ?>">
        </td>
      </tr>
    `);

    $('#product_body').append(newRow);

    const select = newRow.find('.product-select');

    select.select2({
      placeholder: 'Cari kode atau nama produk',
      width: 'style',
      ajax: {
        url: 'controller/sales/productList.php',
        dataType: 'json',
        delay: 250,
        data: function(params) {
          return {
            q: params.term
          };
        },
        processResults: function(data) {
          return {
            results: data.map(item => ({
              id: item.id_product,
              text: item.product_code,
              full_text: `${item.product_code} - ${item.product_name}`,
              data: item
            }))
          };
        },
        cache: true
      },
      templateResult: function(data) {
        if (data.loading) return data.text;
        return data.full_text || data.text;
      },
      templateSelection: function(data) {
        return data.text;
      }
    });
  }

  $('#addRowBtn').on('click', function() {
    addRow();
  });

  $(document).on('click', '.remove-row', function() {
    $(this).closest('tr').remove();
    updateGrandTotal();
  });

  $(document).on('select2:select', '.product-select', function(e) {
    const data = e.params.data.data;
    const row = $(this).closest('tr');
    const productId = data.id_product || data.id;

    row.find("input[name$='[name]']").val(data.product_name);
    row.find("select[name$='[unit]']").html('<option value="">Loading...</option>');
    row.find("input[name$='[price]']").val('');
    row.find("input[name$='[qty]']").val('');
    row.find("input[name$='[total]']").val('');

    $.ajax({
      url: 'controller/sales/getUnitsByProduct.php',
      type: 'GET',
      data: {
        id_product: productId
      },
      dataType: 'json',
      success: function(res) {
        const unitSelect = row.find("select[name$='[unit]']");
        unitSelect.empty();

        if (res.length > 0) {
          res.forEach(unit => {
            const option = $('<option>')
              .val(unit.unit_name)
              .text(unit.unit_name)
              .attr('data-price', unit.price);

            unitSelect.append(option);
          });

          unitSelect.val(res[0].unit_name).trigger('change');
        } else {
          const fallbackPrice = data.product_price || 0;
          unitSelect.append(
            $('<option>').val('pcs').text('pcs').attr('data-price', fallbackPrice)
          );
          unitSelect.val('pcs').trigger('change');
        }
      },
      error: function() {
        const unitSelect = row.find("select[name$='[unit]']");
        unitSelect.empty().append(
          $('<option>').val('pcs').text('pcs').attr('data-price', data.product_price || 0)
        );
        unitSelect.val('pcs').trigger('change');
      }
    });

    row.find("input[name$='[qty]']").focus();
  });

  $(document).on('change', '.unit-select', function() {
    const row = $(this).closest('tr');
    const selected = $(this).find('option:selected');
    const price = selected.data('price') || 0;
    row.find('.price-input').val(price).trigger('input');
    updateGrandTotal();
  });

  $(document).on('input', '.price-input, .qty-input, .disc1-input, .disc2-input, .disc3-input', function() {
    updateGrandTotal();
  });

  $('#formProduk').on('submit', function(e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);

    $.ajax({
      url: 'controller/sales/recordItem.php',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(res) {
        try {
          const response = JSON.parse(res);
          if (response.status === 'success') {
            alert('Produk berhasil disimpan');
            if (response.redirect) {
              window.location.href = response.redirect;
            } else {
              $('#product_body').empty();
              updateGrandTotal();
            }
          } else {
            alert('Gagal menyimpan: ' + response.message);
          }
        } catch (e) {
          console.error('Invalid JSON response', res);
          alert('Terjadi kesalahan saat memproses jawaban server.');
        }
      },
      error: function(xhr) {
        console.error('AJAX Error:', xhr.responseText);
        alert('Gagal menyimpan: ' + xhr.statusText);
      }
    });
  });
</script>

</html>