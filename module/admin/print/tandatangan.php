<!-- footer.php -->
<?php
function formatTanggalIndonesia($tanggal)
{
   $bulanIndo = [
      1 => 'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember'
   ];

   $pecah = explode('-', $tanggal);
   return intval($pecah[2]) . ' ' . $bulanIndo[(int)$pecah[1]] . ' ' . $pecah[0];
}

$tanggalSekarang = formatTanggalIndonesia(date('Y-m-d'));
?>

<div class="signature">
   <p>Tanjung Morawa, <?= $tanggalSekarang ?></p>
   <div style="margin-top: 60px;">
      <strong><u><?= htmlspecialchars($data['patient_name'] ?? '....................................') ?></u></strong>
   </div>
   <div class="signature-name">Yang Membuat Pernyataan</div>
</div>