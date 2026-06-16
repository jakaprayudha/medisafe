<?php
$title = 'Laporan Persediaan Farmasi';
require '../../utility/env.php';
$env = loadEnv();
$apiUrl = getenv('API_URL');
?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title><?= $title ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <style>
    body {
      font-size: 13px;
    }

    @media print {
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>

<body>
  <div class="container-fluid mt-4" id="printArea">
    <div class="text-center mb-4">
      <h4 class="fw-bold">📋 Laporan Persediaan Farmasi</h4>
      <p class="mb-0">Dicetak pada: <?= date('d-m-Y H:i') ?></p>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped" id="stockTable">
        <thead class="table-light">
          <tr>
            <th>Kategori</th>
            <th>Nama Generic/Trade</th>
            <th>Golongan</th>
            <th>Stock Min</th>
            <th>Stock Max</th>
            <th>Masuk</th>
            <th>Keluar</th>
            <th>Stock Akhir</th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr class="fw-bold">
            <td colspan="7" class="text-end">Total Stock Akhir :</td>
            <td id="totalStockAkhir">0</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="text-center mt-3 no-print">
    <button class="btn btn-primary" onclick="window.print()">
      <i class="fas fa-print"></i> Cetak
    </button>
  </div>

  <script>
    const apiUrl = '../../controller/master/pharmacyStock';

    $(document).ready(function() {
      var table = $('#stockTable').DataTable({
        paging: false,
        searching: false,
        info: false,
        ajax: {
          url: apiUrl,
          type: "GET",
          dataSrc: function(json) {
            return json.data.map(function(row) {
              return {
                "category": row.pharmacy_category ?? "-",
                "name": (row.pharmacy_name_generic ?? "-") + "/" + (row.pharmacy_name_trade ?? "-"),
                "golongan": row.pharmcy_golongan ?? "-",
                "stock_min": row.stok_min ?? 0,
                "stock_max": row.stok_max ?? 0,
                "masuk": row.total_penerimaan ?? 0,
                "keluar": row.total_pengeluaran ?? 0,
                "stock_akhir": row.stock_akhir ?? 0
              };
            });
          }
        },
        columns: [{
            data: "category"
          },
          {
            data: "name"
          },
          {
            data: "golongan"
          },
          {
            data: "stock_min"
          },
          {
            data: "stock_max"
          },
          {
            data: "masuk"
          },
          {
            data: "keluar"
          },
          {
            data: "stock_akhir"
          }
        ],
        footerCallback: function(row, data) {
          var api = this.api();
          let totalStock = api
            .column(7)
            .data()
            .reduce((a, b) => (parseFloat(a) || 0) + (parseFloat(b) || 0), 0);
          $(api.column(7).footer()).html(totalStock.toLocaleString());
        }
      });
    });
  </script>
</body>

</html>