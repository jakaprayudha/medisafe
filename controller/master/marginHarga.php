<?php
/**
 * Aturan harga jual dari margin farmasi (setting_clinic.margin_obat / margin_bmhp).
 *
 * - Margin dianggap AKTIF bila bernilai > 0. Kosong / 0 = harga jual diisi manual.
 * - Harga jual = harga beli × (1 + margin%), dibulatkan KE ATAS:
 *   di bawah Rp 1.000 ke kelipatan Rp 10 (harga per tablet), selebihnya ke kelipatan Rp 100.
 * - BMHP / Alkes / Alat kesehatan memakai margin_bmhp, selain itu margin_obat.
 *
 * Dipakai oleh: marginFarmasiController (setting & terapkan massal), pharmacyDetailsController (edit obat),
 * pharmacyStockController (penerimaan stok memperbarui harga beli terakhir).
 */

/** Kelipatan pembulatan untuk sebuah harga. */
function kelipatanPembulatan(float $harga): int
{
   return $harga < 1000 ? 10 : 100;
}

/** @return array{obat: ?float, bmhp: ?float} */
function marginFaskes(mysqli $k, int $idc): array
{
   $st = $k->prepare("SELECT margin_obat, margin_bmhp FROM setting_clinic WHERE id_customer = ? LIMIT 1");
   $st->bind_param('i', $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc() ?: [];
   $st->close();
   $aktif = fn($v) => $v !== null && $v !== '' && (float) $v > 0 ? (float) $v : null;
   return ['obat' => $aktif($r['margin_obat'] ?? null), 'bmhp' => $aktif($r['margin_bmhp'] ?? null)];
}

function kategoriBmhp(?string $kategori): bool
{
   return (bool) preg_match('/^\s*(bmhp|alkes|alat)/i', (string) $kategori);
}

/** Margin (%) yang berlaku untuk kategori ini, null bila tidak aktif. */
function marginKategori(array $margin, ?string $kategori): ?float
{
   return kategoriBmhp($kategori) ? $margin['bmhp'] : $margin['obat'];
}

function hargaJualMargin(float $beli, float $persen): int
{
   if ($beli <= 0) {
      return 0;
   }
   // round(...,6) mencegah 7216*1.1 = 7937.600000001 dibulatkan ke atas secara keliru
   $mentah = round($beli * (1 + $persen / 100), 6);
   $k = kelipatanPembulatan($mentah);
   return (int) (ceil($mentah / $k) * $k);
}
