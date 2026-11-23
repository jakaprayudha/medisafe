<?php
/**
 * Cetak invoice / rincian pembayaran kunjungan.
 * GET ?no=<visit_ID>[&format=a4|80mm][&cetak=0]
 *
 * Rincian & total memakai controller/visit/_tagihan.php (sama dengan module/admin/kasir_detail & daftar kasir).
 * Perbaikan dari versi lama: SQL injection (no ditempel ke query), data pasien tidak dibatasi faskes,
 * nomor baris kosong ($i tidak didefinisikan), obat yang tidak ada di master hilang dari rincian (INNER JOIN),
 * subtotal jasa bisa negatif, info pembayaran (metode, no. transaksi, dibayar, kembalian) tidak tercetak,
 * dan lebar halaman 210mm + margin 20mm membuat isi terpotong / mengecil saat dicetak.
 */
require '../../database/connect.php';
require __DIR__ . '/../../controller/visit/_tagihan.php';

$idc = (string) ($_SESSION['id_customer'] ?? '');
$petugas = (string) ($_SESSION['fullname'] ?? '');
session_write_close();
$no = trim((string) ($_GET['no'] ?? ''));
$format = ($_GET['format'] ?? '') === '80mm' ? '80mm' : 'a4';
$autoCetak = ($_GET['cetak'] ?? '1') !== '0';

$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$rp = fn($n) => 'Rp' . number_format((float) $n, 0, ',', '.');

if ($idc === '' || $no === '') {
   http_response_code($idc === '' ? 401 : 422);
   exit($idc === '' ? 'Sesi berakhir, silakan login ulang.' : 'Nomor kunjungan tidak valid.');
}
$kv = ambilKunjungan($koneksi, $no, $idc);
if (!$kv) {
   http_response_code(404);
   exit('Kunjungan tidak ditemukan untuk faskes ini.');
}
$tagihan = ambilTagihan($koneksi, $no, $idc);

$st = $koneksi->prepare("SELECT sc.clinic_name, sc.image_clinic, f.faskes_address, f.faskes_city, f.faskes_phone
   FROM setting_clinic sc LEFT JOIN ms_faskes f ON f.id_clinic = sc.id WHERE sc.id_customer = ? LIMIT 1");
$st->bind_param('s', $idc);
$st->execute();
$klinik = $st->get_result()->fetch_assoc() ?: [];
$st->close();

// obat dirapikan: 1 baris per obat + harga (dari beberapa tiket resep)
$obat = [];
foreach ($tagihan['obat'] as $o) {
   $k = $o['nama'] . '|' . $o['harga'];
   $obat[$k] = $obat[$k] ?? ['nama' => $o['nama'], 'satuan' => $o['satuan'], 'qty' => 0, 'harga' => $o['harga'], 'subtotal' => 0];
   $obat[$k]['qty'] += $o['qty'];
   $obat[$k]['subtotal'] += $o['subtotal'];
}
$obat = array_values($obat);
$jasa = $tagihan['jasa'];

$lunas = (int) $kv['status_bayar'] === 1;
$total = $tagihan['total'];
$dibayar = $lunas ? (int) $kv['amount_results'] : null;
$metode = $lunas ? ($kv['metode_bayar'] === 'AUTO' ? 'Tanpa tagihan' : ($kv['metode_bayar'] ?: '-')) : null;

function terbilang(int $n): string
{
   $s = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
   if ($n < 12) return $s[$n];
   if ($n < 20) return terbilang($n - 10) . ' belas';
   if ($n < 100) return terbilang(intdiv($n, 10)) . ' puluh ' . terbilang($n % 10);
   if ($n < 200) return 'seratus ' . terbilang($n - 100);
   if ($n < 1000) return terbilang(intdiv($n, 100)) . ' ratus ' . terbilang($n % 100);
   if ($n < 2000) return 'seribu ' . terbilang($n - 1000);
   if ($n < 1000000) return terbilang(intdiv($n, 1000)) . ' ribu ' . terbilang($n % 1000);
   if ($n < 1000000000) return terbilang(intdiv($n, 1000000)) . ' juta ' . terbilang($n % 1000000);
   return terbilang(intdiv($n, 1000000000)) . ' miliar ' . terbilang($n % 1000000000);
}
$terbilang = $total > 0 ? ucfirst(trim(preg_replace('/\s+/', ' ', terbilang($total)))) . ' rupiah' : 'Nol rupiah';

$tz = new DateTimeZone('Asia/Jakarta');
$dicetak = (new DateTime('now', $tz))->format('d/m/Y H:i');
$tglKunjungan = $kv['visit_date'] ? date('d/m/Y', strtotime($kv['visit_date'])) . ($kv['visit_time'] ? ' ' . substr($kv['visit_time'], 0, 5) : '') : '-';
$umur = '';
if (!empty($kv['patient_datebirth']) && ($lahir = DateTime::createFromFormat('!Y-m-d', substr((string) $kv['patient_datebirth'], 0, 10)))) {
   $umur = $lahir->diff(new DateTime('now', $tz))->y . ' th';
}
$alamat = trim(implode(', ', array_filter([$klinik['faskes_address'] ?? '', $klinik['faskes_city'] ?? ''])));
$logo = !empty($klinik['image_clinic']) ? '../../uploads/' . rawurlencode($klinik['image_clinic']) : '';
$urlFormat = fn($f) => '?' . http_build_query(['no' => $no, 'rm' => $_GET['rm'] ?? '', 'format' => $f, 'cetak' => 0]);
?>
<!DOCTYPE html>
<html lang="id">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>Invoice <?= $h($kv['patient_name'] ?? '') ?> — <?= $h($no) ?></title>
   <style>
      * { box-sizing: border-box; }
      body { margin: 0; background: #e5e7eb; color: #111; font-family: Arial, Helvetica, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .toolbar { position: sticky; top: 0; z-index: 5; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: center; padding: 10px; background: #1e293b; }
      .toolbar a, .toolbar button { font: 600 13px Arial, sans-serif; padding: 7px 14px; border-radius: 6px; border: 1px solid #475569; background: #fff; color: #111; cursor: pointer; text-decoration: none; }
      .toolbar .utama { background: #2563eb; border-color: #2563eb; color: #fff; }
      .toolbar .aktif { background: #0f172a; border-color: #94a3b8; color: #fff; }
      .toolbar span { color: #cbd5e1; font-size: 12px; margin: 0 4px; }
      .kertas { background: #fff; margin: 16px auto; box-shadow: 0 2px 12px rgba(0, 0, 0, .15); }
      .num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }

      /* ============ A4 ============ */
      .a4 .kertas { width: 210mm; min-height: 297mm; padding: 14mm 14mm 12mm; font-size: 12px; }
      .a4 .kepala { display: flex; align-items: center; gap: 14px; border-bottom: 2px solid #111; padding-bottom: 10px; }
      .a4 .kepala img { width: 64px; height: 64px; object-fit: contain; }
      .a4 .klinik { flex: 1; }
      .a4 .klinik b { font-size: 17px; text-transform: uppercase; display: block; }
      .a4 .klinik div { font-size: 11px; color: #333; }
      .a4 .judul { text-align: right; }
      .a4 .judul b { font-size: 18px; letter-spacing: .04em; display: block; }
      .a4 .judul div { font-size: 11px; color: #333; }
      .cap { display: inline-block; margin-top: 4px; padding: 2px 10px; border: 2px solid; border-radius: 4px; font-weight: 800; font-size: 12px; letter-spacing: .08em; }
      .cap.lunas { color: #15803d; border-color: #15803d; }
      .cap.belum { color: #b91c1c; border-color: #b91c1c; }
      .a4 .info { display: grid; grid-template-columns: 1fr 1fr; gap: 0 24px; margin: 12px 0; font-size: 12px; }
      .a4 .info div { display: flex; padding: 2px 0; }
      .a4 .info span { width: 105px; color: #333; flex-shrink: 0; }
      .a4 table { width: 100%; border-collapse: collapse; }
      .a4 .rinci th { background: #f1f5f9; border-top: 1px solid #111; border-bottom: 1px solid #111; padding: 6px; font-size: 11px; text-transform: uppercase; text-align: left; }
      .a4 .rinci th.num { text-align: right; }
      .a4 .rinci td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
      .a4 .rinci .grup td { background: #fafafa; font-weight: 700; border-bottom: 1px solid #cbd5e1; padding-top: 8px; }
      .a4 .rinci .sub td { font-weight: 700; border-bottom: 1px solid #94a3b8; }
      .a4 .rinci tr { break-inside: avoid; }
      .a4 .bawah { display: flex; gap: 24px; margin-top: 12px; break-inside: avoid; }
      .a4 .terbilang { flex: 1; font-size: 11px; border: 1px dashed #94a3b8; padding: 8px 10px; align-self: flex-start; }
      .a4 .ringkas { width: 300px; }
      .a4 .ringkas td { padding: 4px 6px; }
      .a4 .ringkas .total td { font-size: 15px; font-weight: 800; border-top: 2px solid #111; border-bottom: 2px solid #111; }
      .a4 .ttd { display: flex; justify-content: space-between; margin-top: 30px; break-inside: avoid; }
      .a4 .ttd div { width: 200px; text-align: center; }
      .a4 .ttd .garis { margin-top: 56px; border-top: 1px solid #111; padding-top: 4px; font-weight: 700; }
      .a4 .catatan { margin-top: 18px; font-size: 10px; color: #444; border-top: 1px solid #e5e7eb; padding-top: 6px; display: flex; justify-content: space-between; }

      /* ============ STRUK 80mm ============ */
      .s80 .kertas { width: 80mm; padding: 4mm 4mm 6mm; font-family: "Courier New", monospace; font-size: 11px; line-height: 1.35; }
      .s80 .tengah { text-align: center; }
      .s80 .klinik b { font-size: 13px; text-transform: uppercase; display: block; }
      .s80 .logo80 { display: block; width: 22mm; max-height: 22mm; object-fit: contain; margin: 0 auto 3px; filter: grayscale(1) contrast(1.2); }
      .s80 hr { border: 0; border-top: 1px dashed #111; margin: 5px 0; }
      .s80 table { width: 100%; border-collapse: collapse; }
      .s80 td { padding: 1px 0; vertical-align: top; }
      .s80 .item td { padding-top: 3px; }
      .s80 .total td { font-weight: 700; font-size: 13px; }
      .s80 .grup { font-weight: 700; margin-top: 4px; }

      @media print {
         body { background: #fff; }
         .toolbar { display: none !important; }
         .kertas { margin: 0; box-shadow: none; }
         .a4 .kertas { width: auto; min-height: 0; padding: 0; }
         .s80 .kertas { width: 100%; padding: 0 1mm; }
      }
      @page { margin: <?= $format === 'a4' ? '12mm' : '2mm 0' ?>; size: <?= $format === 'a4' ? 'A4 portrait' : '80mm auto' ?>; }
   </style>
</head>

<body class="<?= $format === 'a4' ? 'a4' : 's80' ?>">
   <div class="toolbar">
      <button type="button" class="utama" onclick="window.print()">🖨️ Cetak</button>
      <span>Format:</span>
      <a href="<?= $h($urlFormat('a4')) ?>" class="<?= $format === 'a4' ? 'aktif' : '' ?>" data-format="a4">A4</a>
      <a href="<?= $h($urlFormat('80mm')) ?>" class="<?= $format === '80mm' ? 'aktif' : '' ?>" data-format="80mm">Struk 80mm</a>
      <button type="button" onclick="window.close()">Tutup</button>
   </div>

<?php if ($format === 'a4'): ?>
   <div class="kertas">
      <div class="kepala">
         <?php if ($logo): ?><img src="<?= $h($logo) ?>" alt="Logo <?= $h($klinik['clinic_name'] ?? 'klinik') ?>"><?php endif ?>
         <div class="klinik">
            <b><?= $h($klinik['clinic_name'] ?? 'Klinik') ?></b>
            <?php if ($alamat): ?><div><?= $h($alamat) ?></div><?php endif ?>
            <?php if (!empty($klinik['faskes_phone'])): ?><div>Telp. <?= $h($klinik['faskes_phone']) ?></div><?php endif ?>
         </div>
         <div class="judul">
            <b>INVOICE</b>
            <div>Rincian Pembayaran Pasien</div>
            <div>No. <?= $h($lunas && $kv['nomor_transaksi'] ? $kv['nomor_transaksi'] : $no) ?></div>
            <span class="cap <?= $lunas ? 'lunas' : 'belum' ?>"><?= $lunas ? 'LUNAS' : 'BELUM LUNAS' ?></span>
         </div>
      </div>

      <div class="info">
         <div><span>Nama pasien</span>: <b>&nbsp;<?= $h($kv['patient_name'] ?? '-') ?></b></div>
         <div><span>No. kunjungan</span>: &nbsp;<?= $h($no) ?></div>
         <div><span>No. RM</span>: &nbsp;<?= $h($kv['nomor_rm'] ?? '-') ?><?= $umur ? ' · ' . $h($umur) : '' ?><?= !empty($kv['patient_gender']) ? ' · ' . $h($kv['patient_gender']) : '' ?></div>
         <div><span>Tgl. kunjungan</span>: &nbsp;<?= $h($tglKunjungan) ?></div>
         <div><span>Penjamin</span>: &nbsp;<?= $h($kv['provider_name'] ?? '-') ?><?= !empty($kv['noKartu']) ? ' · ' . $h($kv['noKartu']) : '' ?></div>
         <div><span>Poli / dokter</span>: &nbsp;<?= $h(trim(($kv['id_poli'] ?? '') . ' / ' . ($kv['id_doctor'] ?? ''), ' /')) ?: '-' ?></div>
      </div>

      <table class="rinci">
         <thead>
            <tr>
               <th style="width:32px">No</th>
               <th>Deskripsi</th>
               <th class="num" style="width:60px">Qty</th>
               <th class="num" style="width:100px">Harga</th>
               <th class="num" style="width:90px">Diskon</th>
               <th class="num" style="width:110px">Jumlah</th>
            </tr>
         </thead>
         <tbody>
            <?php $i = 1; ?>
            <?php if ($jasa): ?>
               <tr class="grup"><td colspan="6">Tindakan &amp; administrasi</td></tr>
               <?php foreach ($jasa as $b): ?>
                  <tr>
                     <td><?= $i++ ?></td>
                     <td><?= $h($b['billing_item']) ?><?= $b['billing_notes'] ? '<div style="font-size:10px;color:#444">' . $h($b['billing_notes']) . '</div>' : '' ?></td>
                     <td class="num"><?= number_format($b['billing_qty'], 0, ',', '.') ?></td>
                     <td class="num"><?= $rp($b['billing_price']) ?></td>
                     <td class="num"><?= $b['billing_discount'] ? '−' . $rp($b['billing_discount']) : '-' ?></td>
                     <td class="num"><?= $rp($b['subtotal']) ?></td>
                  </tr>
               <?php endforeach ?>
               <tr class="sub"><td colspan="5" class="num">Subtotal tindakan</td><td class="num"><?= $rp($tagihan['total_jasa']) ?></td></tr>
            <?php endif ?>
            <?php if ($obat): ?>
               <tr class="grup"><td colspan="6">Obat &amp; BMHP</td></tr>
               <?php foreach ($obat as $o): ?>
                  <tr>
                     <td><?= $i++ ?></td>
                     <td><?= $h($o['nama']) ?></td>
                     <td class="num"><?= number_format($o['qty'], 0, ',', '.') ?><?= $o['satuan'] && preg_match('/\p{L}/u', (string) $o['satuan']) ? ' ' . $h($o['satuan']) : '' ?></td>
                     <td class="num"><?= $rp($o['harga']) ?></td>
                     <td class="num">-</td>
                     <td class="num"><?= $rp($o['subtotal']) ?></td>
                  </tr>
               <?php endforeach ?>
               <tr class="sub"><td colspan="5" class="num">Subtotal obat</td><td class="num"><?= $rp($tagihan['total_obat']) ?></td></tr>
            <?php endif ?>
            <?php if (!$jasa && !$obat): ?>
               <tr><td colspan="6" style="text-align:center;padding:14px;color:#444">Tidak ada tagihan untuk kunjungan ini.</td></tr>
            <?php endif ?>
         </tbody>
      </table>

      <div class="bawah">
         <div class="terbilang"><b>Terbilang:</b><br><i><?= $h($terbilang) ?></i></div>
         <table class="ringkas">
            <tr class="total"><td>Total tagihan</td><td class="num"><?= $rp($total) ?></td></tr>
            <?php if ($lunas): ?>
               <tr><td>Metode bayar</td><td class="num"><?= $h($metode) ?></td></tr>
               <tr><td>Dibayar</td><td class="num"><?= $rp($kv['amount_payment']) ?></td></tr>
               <tr><td>Kembalian</td><td class="num"><?= $rp($kv['amount_changes']) ?></td></tr>
               <?php if ($dibayar !== $total): ?>
                  <tr><td colspan="2" style="font-size:10px;color:#b91c1c">Catatan: total saat pembayaran <?= $rp($dibayar) ?>; rincian berubah sesudahnya.</td></tr>
               <?php endif ?>
            <?php endif ?>
         </table>
      </div>

      <div class="ttd">
         <div>Pasien / keluarga<div class="garis"><?= $h($kv['patient_name'] ?? '') ?></div></div>
         <div>Kasir<div class="garis"><?= $h($petugas ?: '..............................') ?></div></div>
      </div>

      <div class="catatan">
         <span>Simpan invoice ini sebagai bukti pembayaran yang sah.</span>
         <span>Dicetak <?= $h($dicetak) ?><?= $petugas ? ' oleh ' . $h($petugas) : '' ?></span>
      </div>
   </div>

<?php else: ?>
   <div class="kertas">
      <div class="tengah klinik">
         <?php if ($logo): ?><img class="logo80" src="<?= $h($logo) ?>" alt="Logo <?= $h($klinik['clinic_name'] ?? 'klinik') ?>"><?php endif ?>
         <b><?= $h($klinik['clinic_name'] ?? 'Klinik') ?></b>
         <?php if ($alamat): ?><div><?= $h($alamat) ?></div><?php endif ?>
         <?php if (!empty($klinik['faskes_phone'])): ?><div>Telp. <?= $h($klinik['faskes_phone']) ?></div><?php endif ?>
      </div>
      <hr>
      <table>
         <tr><td>No</td><td class="num"><?= $h($lunas && $kv['nomor_transaksi'] ? $kv['nomor_transaksi'] : $no) ?></td></tr>
         <tr><td>Tanggal</td><td class="num"><?= $h($dicetak) ?></td></tr>
         <tr><td>Pasien</td><td class="num"><?= $h($kv['patient_name'] ?? '-') ?></td></tr>
         <tr><td>No. RM</td><td class="num"><?= $h($kv['nomor_rm'] ?? '-') ?></td></tr>
         <tr><td>Penjamin</td><td class="num"><?= $h($kv['provider_name'] ?? '-') ?></td></tr>
      </table>
      <hr>
      <?php if ($jasa): ?>
         <div class="grup">TINDAKAN</div>
         <table>
            <?php foreach ($jasa as $b): ?>
               <tr class="item"><td colspan="2"><?= $h($b['billing_item']) ?></td></tr>
               <tr><td>&nbsp;<?= number_format($b['billing_qty'], 0, ',', '.') ?> x <?= number_format($b['billing_price'], 0, ',', '.') ?><?= $b['billing_discount'] ? ' -' . number_format($b['billing_discount'], 0, ',', '.') : '' ?></td>
                  <td class="num"><?= number_format($b['subtotal'], 0, ',', '.') ?></td></tr>
            <?php endforeach ?>
         </table>
      <?php endif ?>
      <?php if ($obat): ?>
         <div class="grup">OBAT &amp; BMHP</div>
         <table>
            <?php foreach ($obat as $o): ?>
               <tr class="item"><td colspan="2"><?= $h($o['nama']) ?></td></tr>
               <tr><td>&nbsp;<?= number_format($o['qty'], 0, ',', '.') ?> x <?= number_format($o['harga'], 0, ',', '.') ?></td>
                  <td class="num"><?= number_format($o['subtotal'], 0, ',', '.') ?></td></tr>
            <?php endforeach ?>
         </table>
      <?php endif ?>
      <?php if (!$jasa && !$obat): ?><div class="tengah">Tidak ada tagihan</div><?php endif ?>
      <hr>
      <table>
         <tr class="total"><td>TOTAL</td><td class="num"><?= $rp($total) ?></td></tr>
         <?php if ($lunas): ?>
            <tr><td><?= $h($metode) ?></td><td class="num"><?= $rp($kv['amount_payment']) ?></td></tr>
            <tr><td>Kembali</td><td class="num"><?= $rp($kv['amount_changes']) ?></td></tr>
         <?php endif ?>
      </table>
      <hr>
      <div class="tengah"><b><?= $lunas ? '*** LUNAS ***' : '*** BELUM LUNAS ***' ?></b></div>
      <div class="tengah" style="margin-top:4px">Terima kasih, semoga lekas sembuh</div>
      <?php if ($petugas): ?><div class="tengah" style="font-size:10px">Kasir: <?= $h($petugas) ?></div><?php endif ?>
   </div>
<?php endif ?>

   <script>
      // format terakhir diingat per peramban; buka pertama kali langsung ke format itu
      (function() {
         var kunci = 'strukBilling.format', fmt = <?= json_encode($format) ?>, url = new URL(location.href);
         try {
            var simpan = localStorage.getItem(kunci);
            if (!url.searchParams.has('format') && simpan && simpan !== fmt) { url.searchParams.set('format', simpan); location.replace(url); return; }
            localStorage.setItem(kunci, fmt);
         } catch (e) {}
         <?php if ($autoCetak): ?>
         // cetak otomatis setelah logo termuat (dulu langsung saat onload, kadang logo belum tampil)
         window.addEventListener('load', function() { setTimeout(function() { window.print(); }, 250); });
         <?php endif ?>
      })();
   </script>
</body>

</html>
