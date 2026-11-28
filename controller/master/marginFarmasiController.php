<?php
/**
 * Setting margin harga jual farmasi (module/admin/setting_margin).
 *
 * GET                               -> margin tersimpan + ringkasan & daftar obat yang harga jualnya akan berubah
 * GET  ?obat=&bmhp=                 -> sama, tetapi memakai margin usulan (pratinjau sebelum disimpan)
 * POST margin_obat, margin_bmhp[, terapkan=1]
 *                                   -> simpan margin; terapkan=1 sekaligus hitung ulang harga jual semua obat faskes
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
require_once __DIR__ . '/marginHarga.php';
require_once __DIR__ . '/../../utility/activityLog.php';

header('Content-Type: application/json');

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Session tidak ditemukan']);
   exit;
}
requireRole(['admin', 'superadmin', 'administrator']);

function mfJawab(array $d, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}

/** '' / null -> null (margin tidak aktif); angka 0–500 -> float; selain itu false. */
function mfBacaMargin($v)
{
   $v = trim(str_replace(',', '.', (string) ($v ?? '')));
   if ($v === '') {
      return null;
   }
   if (!is_numeric($v) || (float) $v < 0 || (float) $v > 500) {
      return false;
   }
   return round((float) $v, 2);
}

/**
 * Hitung rencana harga jual semua obat milik faskes.
 * Harga beli acuan = harga beli di master obat; bila kosong, harga beli penerimaan stok terakhir.
 */
function mfRencana(mysqli $k, int $idc, array $margin): array
{
   $st = $k->prepare(
      "SELECT id_pharmacy, pharmacy_name_generic, pharmacy_name_trade, pharmacy_category, pharmacy_unit,
              COALESCE(pharmacy_buy, 0) AS beli, COALESCE(pharmacy_sale, 0) AS jual, pharmacy_status
       FROM ms_pharmacy WHERE id_customer = ?"
   );
   $st->bind_param('i', $idc);
   $st->execute();
   $obat = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();

   $idcStr = (string) $idc;
   $st = $k->prepare(
      "SELECT id_pharmacy, harga_beli FROM pharmacy_stock_penerimaan
       WHERE id_customer = ? AND harga_beli > 0
       ORDER BY tanggal DESC, id_penerimaan DESC"
   );
   $st->bind_param('s', $idcStr);
   $st->execute();
   $terima = [];
   foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
      $terima[(int) $r['id_pharmacy']] ??= (float) $r['harga_beli'];
   }
   $st->close();

   $ring = [
      'obat' => ['total' => 0, 'ada_beli' => 0, 'berubah' => 0, 'tanpa_jual' => 0],
      'bmhp' => ['total' => 0, 'ada_beli' => 0, 'berubah' => 0, 'tanpa_jual' => 0],
   ];
   $ubah = [];
   $tanpaBeli = 0;
   foreach ($obat as $o) {
      $grup = kategoriBmhp($o['pharmacy_category']) ? 'bmhp' : 'obat';
      $ring[$grup]['total']++;
      if ((float) $o['jual'] <= 0) {
         $ring[$grup]['tanpa_jual']++;
      }
      $beli = (float) $o['beli'];
      $sumber = 'master';
      if ($beli <= 0 && isset($terima[(int) $o['id_pharmacy']])) {
         $beli = $terima[(int) $o['id_pharmacy']];
         $sumber = 'penerimaan';
      }
      if ($beli <= 0) {
         $tanpaBeli++;
         continue;
      }
      $ring[$grup]['ada_beli']++;
      $pct = $margin[$grup];
      if ($pct === null) {
         continue;
      }
      $baru = hargaJualMargin($beli, $pct);
      if ($baru === (int) round((float) $o['jual']) && $sumber === 'master') {
         continue;
      }
      $ring[$grup]['berubah']++;
      $ubah[] = [
         'id' => (int) $o['id_pharmacy'],
         'nama' => trim($o['pharmacy_name_generic'] . ($o['pharmacy_name_trade'] ? ' (' . $o['pharmacy_name_trade'] . ')' : '')),
         'kategori' => $o['pharmacy_category'],
         'grup' => $grup,
         'satuan' => $o['pharmacy_unit'],
         'beli' => (int) round($beli),
         'sumber_beli' => $sumber,
         'jual_lama' => (int) round((float) $o['jual']),
         'jual_baru' => $baru,
         'aktif' => (string) $o['pharmacy_status'] !== '0',
      ];
   }
   usort($ubah, fn($a, $b) => strcasecmp($a['nama'], $b['nama']));

   // obat master bawaan yang dipakai faskes (tidak bisa dihitung massal, harus disalin lewat detail obat)
   $st = $k->prepare(
      "SELECT COUNT(*) FROM ms_pharmacy mp
       LEFT JOIN ms_pharmacy_parrent log ON log.parent_id = mp.id_pharmacy AND log.id_customer_real = ?
       WHERE mp.id_customer = 0 AND log.id IS NULL"
   );
   $st->bind_param('i', $idc);
   $st->execute();
   $global = (int) $st->get_result()->fetch_row()[0];
   $st->close();

   return ['ringkasan' => $ring, 'tanpa_beli' => $tanpaBeli, 'master_bawaan' => $global, 'perubahan' => $ubah];
}

function mfTerapkan(mysqli $k, int $idc, array $ubah): int
{
   $st = $k->prepare(
      "UPDATE ms_pharmacy SET pharmacy_sale = ?, pharmacy_buy = ?,
              pharmacy_price_buy = IF(COALESCE(pharmacy_price_buy, 0) > 0, pharmacy_price_buy, ?)
       WHERE id_pharmacy = ? AND id_customer = ?"
   );
   $n = 0;
   foreach ($ubah as $u) {
      $st->bind_param('iiiii', $u['jual_baru'], $u['beli'], $u['beli'], $u['id'], $idc);
      $st->execute();
      $n += $st->affected_rows > 0 ? 1 : 0;
   }
   $st->close();
   return $n;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
   $simpan = marginFaskes($koneksi, $idc);
   $st = $koneksi->prepare("SELECT margin_obat, margin_bmhp FROM setting_clinic WHERE id_customer = ? LIMIT 1");
   $st->bind_param('i', $idc);
   $st->execute();
   $raw = $st->get_result()->fetch_assoc() ?: ['margin_obat' => null, 'margin_bmhp' => null];
   $st->close();

   $pakai = $simpan;
   if (isset($_GET['obat']) || isset($_GET['bmhp'])) {
      $o = mfBacaMargin($_GET['obat'] ?? '');
      $b = mfBacaMargin($_GET['bmhp'] ?? '');
      $pakai = ['obat' => $o ?: null, 'bmhp' => $b ?: null];
   }
   $r = mfRencana($koneksi, $idc, $pakai);
   mfJawab([
      'status' => 'success',
      'data' => [
         'margin_obat' => $raw['margin_obat'] !== null ? (float) $raw['margin_obat'] : null,
         'margin_bmhp' => $raw['margin_bmhp'] !== null ? (float) $raw['margin_bmhp'] : null,
         'aktif' => $simpan,
      ] + $r,
   ]);
}

if ($method !== 'POST') {
   mfJawab(['status' => 'error', 'message' => 'Method tidak diizinkan'], 405);
}

$mo = mfBacaMargin($_POST['margin_obat'] ?? '');
$mb = mfBacaMargin($_POST['margin_bmhp'] ?? '');
if ($mo === false || $mb === false) {
   mfJawab(['status' => 'error', 'message' => 'Margin harus angka 0–500 (%), atau dikosongkan untuk menonaktifkan.'], 422);
}

$koneksi->begin_transaction();
try {
   $st = $koneksi->prepare("SELECT id, margin_obat, margin_bmhp FROM setting_clinic WHERE id_customer = ? LIMIT 1");
   $st->bind_param('i', $idc);
   $st->execute();
   $mfLama = $st->get_result()->fetch_assoc();
   $ada = (bool) $mfLama;
   $st->close();

   $st = $ada
      ? $koneksi->prepare("UPDATE setting_clinic SET margin_obat = ?, margin_bmhp = ? WHERE id_customer = ?")
      : $koneksi->prepare("INSERT INTO setting_clinic (margin_obat, margin_bmhp, id_customer) VALUES (?, ?, ?)");
   $st->bind_param('ddi', $mo, $mb, $idc);
   if (!$st->execute()) {
      throw new RuntimeException($st->error);
   }
   $st->close();

   $diterapkan = 0;
   $mfUbah = [];
   if (!empty($_POST['terapkan'])) {
      $r = mfRencana($koneksi, $idc, marginFaskes($koneksi, $idc));
      $diterapkan = mfTerapkan($koneksi, $idc, $r['perubahan']);
      $mfUbah = $r['perubahan'];
   }
   $koneksi->commit();
   // log setelah commit: setting margin + daftar harga jual yang diubah massal (maks. 500 baris di log)
   logAktivitas($koneksi, 'stok', 'ubah_harga', ['tipe' => 'margin', 'label' => 'Margin farmasi'],
      'Margin obat ' . ($mo === null ? 'nonaktif' : $mo . '%') . ', BMHP ' . ($mb === null ? 'nonaktif' : $mb . '%')
         . (!empty($_POST['terapkan']) ? " · harga jual $diterapkan obat diperbarui" : ' · harga belum diterapkan'),
      ['margin_obat' => $mfLama['margin_obat'] ?? null, 'margin_bmhp' => $mfLama['margin_bmhp'] ?? null],
      ['margin_obat' => $mo, 'margin_bmhp' => $mb, 'harga_diubah' => array_slice($mfUbah, 0, 500)]);
} catch (Throwable $e) {
   $koneksi->rollback();
   error_log('[marginFarmasiController] ' . $e->getMessage());
   mfJawab(['status' => 'error', 'message' => 'Gagal menyimpan margin.'], 500);
}

mfJawab([
   'status' => 'success',
   'message' => 'Margin disimpan.' . (!empty($_POST['terapkan']) ? " Harga jual $diterapkan obat diperbarui." : ''),
   'diterapkan' => $diterapkan,
]);
