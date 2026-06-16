<?php
// Dummy data pendapatan & beban
$pendapatan = [
  ["Jasa Medis", 15000000],
  ["Penjualan Obat", 10000000],
  ["Lain-lain", 2500000],
];

$beban = [
  ["Gaji Dokter & Staf", 8000000],
  ["Obat Habis Pakai", 3000000],
  ["Operasional & Utilitas", 2000000],
  ["Lain-lain", 1000000],
];

// Hitung total
$totalPendapatan = array_sum(array_column($pendapatan, 1));
$totalBeban      = array_sum(array_column($beban, 1));
$labaBersih      = $totalPendapatan - $totalBeban;
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Laporan Laba Rugi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @media print {
      .no-print {
        display: none;
      }
    }

    .table td,
    .table th {
      vertical-align: middle;
    }
  </style>
</head>

<body>
  <div class="container mt-4">
    <h3 class="text-center">Laporan Laba Rugi</h3>
    <p class="text-center">Periode: Januari 2025</p>

    <!-- Pendapatan -->
    <h5 class="mt-4">Pendapatan</h5>
    <table class="table table-bordered table-sm">
      <thead class="table-light">
        <tr>
          <th>Jenis Pendapatan</th>
          <th class="text-end">Jumlah (Rp)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pendapatan as $p): ?>
          <tr>
            <td><?= $p[0] ?></td>
            <td class="text-end"><?= number_format($p[1], 0, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th class="text-end">Total Pendapatan</th>
          <th class="text-end"><?= number_format($totalPendapatan, 0, ',', '.') ?></th>
        </tr>
      </tfoot>
    </table>

    <!-- Beban -->
    <h5 class="mt-4">Beban</h5>
    <table class="table table-bordered table-sm">
      <thead class="table-light">
        <tr>
          <th>Jenis Beban</th>
          <th class="text-end">Jumlah (Rp)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($beban as $b): ?>
          <tr>
            <td><?= $b[0] ?></td>
            <td class="text-end"><?= number_format($b[1], 0, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th class="text-end">Total Beban</th>
          <th class="text-end"><?= number_format($totalBeban, 0, ',', '.') ?></th>
        </tr>
      </tfoot>
    </table>

    <!-- Laba Bersih -->
    <div class="alert <?= $labaBersih >= 0 ? 'alert-success' : 'alert-danger' ?> text-end">
      <h5>Laba Bersih: Rp <?= number_format($labaBersih, 0, ',', '.') ?></h5>
    </div>

    <button class="btn btn-secondary no-print" onclick="window.print()">
      <i class="fas fa-print"></i> Cetak
    </button>
  </div>
</body>

</html>