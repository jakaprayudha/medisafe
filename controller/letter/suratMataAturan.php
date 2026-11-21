<?php
/**
 * Aturan field Surat Hasil Pemeriksaan Mata. Dipakai controller (validasi server) DAN module/letter/form-eye.php
 * (dikirim ke JS sebagai SM_ATURAN untuk validasi klien), sehingga aturan klien & server selalu sama.
 * Aturan: null = bebas (hanya panjang), 'dioptri' (+/-0.00 atau plano), 'axis' (0-180), 'angka:min:maks', 'td' (sistol/diastol)
 */
/** field data surat => [panjang maks, aturan format (regex) atau null, label untuk pesan] */
const MATA_FIELD = [
   'tekanan_darah' => [20, 'td', 'tekanan darah (mis. 120/80)'],
   'nadi' => [10, 'angka:20:250', 'nadi'],
   'suhu' => [10, 'angka:30:45', 'suhu'],
   'respirasi' => [10, 'angka:5:80', 'respirasi'],
   'gula_darah_sewaktu' => [20, 'angka:1:2000', 'gula darah sewaktu'],
   'gula_darah_keterangan' => [255, null, 'keterangan gula darah'],
   'kolesterol_total' => [20, 'angka:1:2000', 'kolesterol total'],
   'kolesterol_keterangan' => [255, null, 'keterangan kolesterol'],
   'asam_urat' => [20, 'angka:0.1:50', 'asam urat'],
   'asam_urat_keterangan' => [255, null, 'keterangan asam urat'],
   'hemoglobin' => [20, 'angka:1:30', 'hemoglobin'],
   'hemoglobin_keterangan' => [255, null, 'keterangan hemoglobin'],
   'visus_od_tanpa_koreksi_jauh' => [50, null, 'visus OD tanpa koreksi jauh'],
   'visus_od_tanpa_koreksi_dekat' => [50, null, 'visus OD tanpa koreksi dekat'],
   'visus_od_dengan_koreksi_jauh' => [50, null, 'visus OD dengan koreksi jauh'],
   'visus_od_dengan_koreksi_dekat' => [50, null, 'visus OD dengan koreksi dekat'],
   'visus_os_tanpa_koreksi_jauh' => [50, null, 'visus OS tanpa koreksi jauh'],
   'visus_os_tanpa_koreksi_dekat' => [50, null, 'visus OS tanpa koreksi dekat'],
   'visus_os_dengan_koreksi_jauh' => [50, null, 'visus OS dengan koreksi jauh'],
   'visus_os_dengan_koreksi_dekat' => [50, null, 'visus OS dengan koreksi dekat'],
   'refraksi_od_sph' => [10, 'dioptri', 'SPH OD'],
   'refraksi_od_cyl' => [10, 'dioptri', 'CYL OD'],
   'refraksi_od_axis' => [10, 'axis', 'AXIS OD (0–180)'],
   'refraksi_od_add' => [10, 'dioptri', 'ADD OD'],
   'refraksi_os_sph' => [10, 'dioptri', 'SPH OS'],
   'refraksi_os_cyl' => [10, 'dioptri', 'CYL OS'],
   'refraksi_os_axis' => [10, 'axis', 'AXIS OS (0–180)'],
   'refraksi_os_add' => [10, 'dioptri', 'ADD OS'],
   'pd' => [50, null, 'PD'],
   'tio_od' => [10, 'angka:0:90', 'TIO OD'],
   'tio_os' => [10, 'angka:0:90', 'TIO OS'],
   'segmen_anterior_od' => [2000, null, 'segmen anterior OD'],
   'segmen_anterior_os' => [2000, null, 'segmen anterior OS'],
   'segmen_posterior_od' => [2000, null, 'segmen posterior OD'],
   'segmen_posterior_os' => [2000, null, 'segmen posterior OS'],
   'kesimpulan' => [3000, null, 'kesimpulan'],
   'rekomendasi' => [3000, null, 'rekomendasi'],
];
/** minimal satu dari field ini terisi = surat berisi hasil pemeriksaan mata */
const MATA_INTI = ['visus_od_tanpa_koreksi_jauh', 'visus_od_tanpa_koreksi_dekat', 'visus_od_dengan_koreksi_jauh', 'visus_od_dengan_koreksi_dekat',
   'visus_os_tanpa_koreksi_jauh', 'visus_os_tanpa_koreksi_dekat', 'visus_os_dengan_koreksi_jauh', 'visus_os_dengan_koreksi_dekat',
   'refraksi_od_sph', 'refraksi_od_cyl', 'refraksi_os_sph', 'refraksi_os_cyl', 'tio_od', 'tio_os',
   'segmen_anterior_od', 'segmen_anterior_os', 'segmen_posterior_od', 'segmen_posterior_os', 'kesimpulan'];

/** Cek satu nilai terhadap aturan MATA_FIELD. */
function smCocok(string $v, ?string $aturan): bool
{
   if ($v === '' || $aturan === null) return true;
   if ($aturan === 'dioptri') return (bool) preg_match('/^([+-]?\d{1,2}([.,]\d{1,2})?|plano|pl)$/i', $v);
   if ($aturan === 'td') return (bool) preg_match('/^\d{2,3}\s*\/\s*\d{2,3}$/', $v);
   if ($aturan === 'axis') return preg_match('/^\d{1,3}$/', $v) && (int) $v <= 180;
   if (str_starts_with($aturan, 'angka:')) {
      [, $min, $max] = explode(':', $aturan);
      if (!preg_match('/^\d{1,4}([.,]\d{1,2})?$/', $v)) return false;
      $n = (float) str_replace(',', '.', $v);
      return $n >= (float) $min && $n <= (float) $max;
   }
   return (bool) preg_match($aturan, $v);
}
