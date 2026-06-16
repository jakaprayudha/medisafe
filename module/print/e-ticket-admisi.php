<?php
require '../../database/connect.php';
$no_antrian = $_GET['no_antrian'] ?? '-';
$nama       = $_GET['nama'] ?? '-';
$poli       = $_GET['poli'] ?? '-';
$checkpoli = mysqli_query($koneksi, "SELECT poli_name FROM ms_poli WHERE id_poli = '$poli'");
if ($row = mysqli_fetch_assoc($checkpoli)) {
    $poli = $row['poli_name'];
}

date_default_timezone_set('Asia/Jakarta');
$tanggal = date('d-m-Y');
$jam     = date('H:i');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Cetak Tiket Antrean</title>

<style>
  @media print {
    body {
      margin: 0;
    }
  }
  body {
    width: 58mm;
    font-family: Arial, sans-serif;
    font-size: 12px;
    text-align: center;
  }
  .title {
    font-size: 14px;
    font-weight: bold;
  }
  .queue {
    font-size: 40px;
    font-weight: bold;
    margin: 10px 0;
  }
  .line {
    border-top: 1px dashed #000;
    margin: 8px 0;
  }
  .footer {
    font-size: 10px;
    margin-top: 8px;
  }
</style>
</head>

<body onload="window.print()">

<div class="title">KLINIK</div>
<div>Layanan Admisi</div>

<div class="line"></div>

<div>Nomor Antrean</div>
<div class="queue"><?= htmlspecialchars($no_antrian) ?></div>

<div class="line"></div>

<div>Nama</div>
<strong><?= htmlspecialchars($nama) ?></strong>

<div class="line"></div>

<div>Poli</div>
<strong><?= htmlspecialchars($poli) ?></strong>

<div class="line"></div>

<div><?= $tanggal ?> | <?= $jam ?></div>

<div class="footer">
  Harap menunggu panggilan<br>
  Terima kasih 🙏
</div>

</body>
</html>