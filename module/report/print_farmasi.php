<?php
// Cetak Laporan Stok Farmasi - kolom, urutan, filter & ringkasan sama dengan module/admin/rpt_farmasi
require_once '../../database/connect.php';
require_once __DIR__ . '/../../controller/report/farmasiStokService.php';
mysqli_report(MYSQLI_REPORT_OFF);

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}
$id_customer = $_SESSION['id_customer'] ?? null;
if (!$id_customer) {
   http_response_code(403);
   exit('Session tidak ditemukan');
}

$fromDate = (string) ($_GET['fromDate'] ?? date('Y-m-d'));
$toDate = (string) ($_GET['toDate'] ?? date('Y-m-d'));
if (($error = farmasiStokValidasiPeriode($fromDate, $toDate)) !== null) {
   exit(htmlspecialchars($error));
}
$filter = farmasiStokParamFilter($_GET);

try {
   $hasil = farmasiStokReport($koneksi, (string) $id_customer, $fromDate, $toDate);
} catch (Throwable $e) {
   error_log('[print farmasi] ' . $e->getMessage());
   exit('Gagal mengambil data laporan farmasi');
}
$rows = farmasiStokFilter($hasil['data'], $filter);
$ringkasan = farmasiStokRingkasan($rows);
try {
   $dash = farmasiStokDashboard($koneksi, (string) $id_customer, $fromDate, $toDate, $hasil['data']);
} catch (Throwable $e) {
   error_log('[print farmasi] analisis: ' . $e->getMessage());
   $dash = null;
}

// klinik yang sedang login
$stmt = $koneksi->prepare("SELECT * FROM setting_clinic WHERE id_customer = ? LIMIT 1");
$stmt->bind_param('s', $id_customer);
$stmt->execute();
$klinik = $stmt->get_result()->fetch_assoc() ?: [];
$stmt->close();

$e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$tgl = fn($d) => date('d/m/Y', strtotime($d));
$badge = ['habis' => 'b-danger', 'minimum' => 'b-warning', 'maximum' => 'b-info', 'normal' => 'b-success'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <title>Laporan Stok Farmasi <?= $e($tgl($fromDate)) ?> - <?= $e($tgl($toDate)) ?></title>
   <style>
      @page {
         size: A4 landscape;
         margin: 10mm;
      }

      * {
         box-sizing: border-box;
      }

      body {
         font-family: Arial, Helvetica, sans-serif;
         font-size: 9.5px;
         color: #222;
         margin: 0;
      }

      .kop {
         text-align: center;
         border-bottom: 2px solid #222;
         padding-bottom: 6px;
         margin-bottom: 8px;
      }

      .kop .klinik {
         font-size: 14px;
         font-weight: bold;
         text-transform: uppercase;
      }

      .kop .alamat {
         font-size: 9px;
         color: #444;
         margin-top: 2px;
      }

      .judul {
         text-align: center;
         margin-bottom: 8px;
      }

      .judul h1 {
         font-size: 13px;
         margin: 0 0 3px;
         text-transform: uppercase;
      }

      .ringkasan {
         width: 100%;
         border-collapse: separate;
         border-spacing: 4px;
         margin-bottom: 6px;
      }

      .ringkasan td {
         border: 1px solid #ccc;
         border-radius: 4px;
         padding: 4px 6px;
         width: 16.66%;
      }

      .ringkasan .v {
         font-size: 12px;
         font-weight: bold;
      }

      .ringkasan .l {
         font-size: 8.5px;
         color: #555;
      }

      table.data {
         width: 100%;
         border-collapse: collapse;
      }

      table.data th {
         background: #2563eb;
         color: #fff;
         padding: 5px 4px;
         font-size: 9px;
         border: 1px solid #2563eb;
         -webkit-print-color-adjust: exact;
         print-color-adjust: exact;
      }

      table.data td {
         border: 1px solid #ccc;
         padding: 4px;
         vertical-align: top;
      }

      table.data tfoot td {
         background: #eef2f7;
         font-weight: bold;
      }

      thead {
         display: table-header-group;
      }

      tr {
         page-break-inside: avoid;
      }

      .r {
         text-align: right;
         white-space: nowrap;
      }

      .c {
         text-align: center;
      }

      .sub {
         color: #666;
         font-size: 8.5px;
      }

      .plus {
         color: #13a58b;
         font-weight: bold;
      }

      .minus,
      .neg {
         color: #d9534f;
         font-weight: bold;
      }

      .muted {
         color: #999;
      }

      .badge {
         display: inline-block;
         padding: 1px 5px;
         border-radius: 3px;
         font-size: 8.5px;
         font-weight: bold;
         white-space: nowrap;
         -webkit-print-color-adjust: exact;
         print-color-adjust: exact;
      }

      .b-danger {
         background: #fde2dc;
         color: #c0392b;
      }

      .b-warning {
         background: #fff1d6;
         color: #b7791f;
      }

      .b-info {
         background: #dff3ff;
         color: #1f78b4;
      }

      .b-success {
         background: #dcf7f0;
         color: #13a58b;
      }

      .catatan {
         margin-top: 8px;
         font-size: 8.5px;
         color: #444;
      }

      .dicetak {
         margin-top: 4px;
         font-size: 8.5px;
         color: #666;
         text-align: right;
      }

      .no-print {
         text-align: right;
         margin-bottom: 10px;
      }

      .no-print button {
         background: #2563eb;
         color: #fff;
         border: 0;
         border-radius: 6px;
         padding: 8px 16px;
         font-size: 12px;
         cursor: pointer;
      }

      @media screen {
         body {
            padding: 16px;
            background: #eef1f5;
         }

         .halaman {
            background: #fff;
            padding: 20px;
            max-width: 1200px;
            margin: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .08);
         }
      }

      @media print {
         .no-print {
            display: none;
         }
      }
         .analisis { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 10px; }
      .analisis > tbody > tr > td, .analisis > tr > td { width: 50%; vertical-align: top; border: 1px solid #d0d7e2; border-radius: 6px; padding: 6px 8px; }
      .analisis table { width: 100%; border-collapse: collapse; font-size: 9.5px; }
      .analisis table td { padding: 1.5px 2px; }
      .analisis .aj { font-weight: bold; font-size: 10px; text-transform: uppercase; color: #475569; margin-bottom: 3px; }
   </style>
</head>

<body>
   <div class="halaman">
      <div class="no-print"><button onclick="window.print()">🖨 Cetak Laporan</button></div>

      <div class="kop">
         <div class="klinik"><?= $e($klinik['clinic_name'] ?? 'Klinik') ?></div>
         <?php if (!empty($klinik['address']) || !empty($klinik['phone_number'])): ?>
            <div class="alamat">
               <?= $e($klinik['address'] ?? '') ?>
               <?= !empty($klinik['address']) && !empty($klinik['phone_number']) ? ' | ' : '' ?>
               <?= !empty($klinik['phone_number']) ? 'Telp. ' . $e($klinik['phone_number']) : '' ?>
            </div>
         <?php endif; ?>
      </div>

      <div class="judul">
         <h1>Laporan Stok Farmasi</h1>
         <div>Periode <?= $e($tgl($fromDate)) ?> s/d <?= $e($tgl($toDate)) ?> • <?= $e(farmasiStokLabelFilter($filter)) ?></div>
      </div>

      <table class="ringkasan">
         <tr>
            <td>
               <div class="v"><?= farmasiStokAngka($ringkasan['total_item']) ?></div>
               <div class="l">Obat ditampilkan</div>
            </td>
            <td>
               <div class="v neg"><?= farmasiStokAngka($ringkasan['habis']) ?></div>
               <div class="l">Stok habis</div>
            </td>
            <td>
               <div class="v" style="color:#b7791f"><?= farmasiStokAngka($ringkasan['minimum']) ?></div>
               <div class="l">Di bawah minimum</div>
            </td>
            <td>
               <div class="v" style="color:#1f78b4"><?= farmasiStokAngka($ringkasan['maximum']) ?></div>
               <div class="l">Di atas maksimum</div>
            </td>
            <td>
               <div class="v"><span class="plus"><?= farmasiStokAngka($ringkasan['stok_masuk']) ?></span> / <span class="minus"><?= farmasiStokAngka($ringkasan['stok_keluar']) ?></span></div>
               <div class="l">Masuk / keluar periode</div>
            </td>
            <td>
               <div class="v"><?= farmasiStokRupiah($ringkasan['nilai_stok']) ?></div>
               <div class="l">Nilai stok akhir (harga beli)</div>
            </td>
         </tr>
      </table>

      <?php if ($dash): ?>
         <table class="analisis">
            <tr>
               <td>
                  <div class="aj">10 obat paling banyak keluar</div>
                  <table>
                     <?php foreach ($dash['top_keluar'] as $i => $x): ?>
                        <tr><td><?= $i + 1 ?>. <?= $e($x['nama']) ?></td><td class="r"><?= farmasiStokAngka($x['keluar']) ?> <?= $e($x['satuan']) ?></td><td class="r"><?= farmasiStokRupiah($x['nilai']) ?></td></tr>
                     <?php endforeach; ?>
                     <?php if (!$dash['top_keluar']): ?><tr><td class="muted">Tidak ada obat keluar.</td></tr><?php endif; ?>
                  </table>
                  <div class="muted" style="margin-top:3px">Nilai obat keluar: <b><?= farmasiStokRupiah($hasil['ringkasan']['nilai_keluar']) ?></b> · <?= farmasiStokAngka($dash['resep']) ?> resep</div>
               </td>
               <td>
                  <div class="aj">Kedaluwarsa (ED lewat / ≤ 90 hari)</div>
                  <table>
                     <?php foreach (array_slice($dash['ed'], 0, 10) as $x): ?>
                        <tr><td><?= $e($x['obat']) ?> <span class="muted"><?= $e($x['batch']) ?></span></td><td class="r"><?= $e($tgl($x['expired'])) ?></td><td class="r <?= $x['sisa_hari'] < 0 ? 'neg' : '' ?>"><?= $x['sisa_hari'] < 0 ? 'lewat' : $x['sisa_hari'] . ' hr' ?></td></tr>
                     <?php endforeach; ?>
                     <?php if (!$dash['ed']): ?><tr><td class="muted">Tidak ada.</td></tr><?php endif; ?>
                  </table>
                  <div class="aj" style="margin-top:6px">Stok tidak bergerak: <?= farmasiStokAngka($dash['mati']) ?> obat · <?= farmasiStokRupiah($dash['mati_nilai']) ?></div>
               </td>
            </tr>
         </table>
      <?php endif; ?>

      <table class="data">
         <thead>
            <tr>
               <th style="width:28px">No</th>
               <th>Nama Obat</th>
               <th>Kategori</th>
               <th class="c">Satuan</th>
               <th class="r">Stok Awal</th>
               <th class="r">Masuk</th>
               <th class="r">Keluar</th>
               <th class="r">Stok Akhir</th>
               <th class="r">Min / Max</th>
               <th class="r">Nilai Stok</th>
               <th class="c">Status</th>
            </tr>
         </thead>
         <tbody>
            <?php if (!$rows): ?>
               <tr>
                  <td colspan="11" class="c muted">Tidak ada data.</td>
               </tr>
            <?php endif; ?>
            <?php foreach ($rows as $i => $r):
               $sub = farmasiStokSubNama($r);
               $masuk = (float) $r['stok_masuk'];
               $keluar = (float) $r['stok_keluar'];
               $akhir = (float) $r['stok_akhir'];
            ?>
               <tr>
                  <td class="c"><?= $i + 1 ?></td>
                  <td>
                     <b><?= $e($r['pharmacy_name_generic'] ?: '-') ?></b>
                     <?php if ($sub !== ''): ?><div class="sub"><?= $e($sub) ?></div><?php endif; ?>
                  </td>
                  <td><?= $e(farmasiStokIsi($r['pharmacy_category']) ?: '-') ?></td>
                  <td class="c"><?= $e(farmasiStokIsi($r['pharmacy_unit']) ?: '-') ?></td>
                  <td class="r"><?= farmasiStokAngka($r['stok_awal']) ?></td>
                  <td class="r"><?= $masuk > 0 ? '<span class="plus">+' . farmasiStokAngka($masuk) . '</span>' : '<span class="muted">0</span>' ?></td>
                  <td class="r"><?= $keluar > 0 ? '<span class="minus">−' . farmasiStokAngka($keluar) . '</span>' : '<span class="muted">0</span>' ?></td>
                  <td class="r"><b class="<?= $akhir <= 0 ? 'neg' : '' ?>"><?= farmasiStokAngka($akhir) ?></b></td>
                  <td class="r muted"><?= ((float) $r['stok_min'] || (float) $r['stok_max']) ? farmasiStokAngka($r['stok_min']) . ' / ' . farmasiStokAngka($r['stok_max']) : '-' ?></td>
                  <td class="r"><?= farmasiStokRupiah($r['nilai_stok']) ?></td>
                  <td class="c"><span class="badge <?= $badge[$r['status_code']] ?? 'b-success' ?>"><?= $e($r['status_stock']) ?></span></td>
               </tr>
            <?php endforeach; ?>
         </tbody>
         <tfoot>
            <tr>
               <td colspan="5">Total (sesuai filter)</td>
               <td class="r plus">+<?= farmasiStokAngka($ringkasan['stok_masuk']) ?></td>
               <td class="r minus">−<?= farmasiStokAngka($ringkasan['stok_keluar']) ?></td>
               <td></td>
               <td></td>
               <td class="r"><?= farmasiStokRupiah($ringkasan['nilai_stok']) ?></td>
               <td></td>
            </tr>
         </tfoot>
      </table>

      <div class="catatan">
         <b>Keterangan:</b> Stok Awal = stok pada awal periode. Stok Akhir = Stok Awal + Masuk − Keluar, posisi di tanggal akhir periode.
         Masuk dari penerimaan obat, keluar dari resep klinik ini. Status dihitung dari stok akhir.
      </div>
      <div class="dicetak">Dicetak <?= date('d/m/Y H:i') ?><?= !empty($_SESSION['fullname']) ? ' oleh ' . $e($_SESSION['fullname']) : '' ?></div>
   </div>
</body>

</html>
