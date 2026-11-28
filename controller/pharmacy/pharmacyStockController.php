<?php
// Stok per obat (module/admin/pharmacy_stock).
// Perhitungan stok memakai farmasiStokReport() yang sama dengan Laporan Farmasi (rpt_farmasi),
// jadi angka di halaman ini selalu sama dengan laporan, Excel, dan Print.
//
//   GET    ?action=detail&id_pharmacy=&fromDate=&toDate=   -> info obat + stok periode
//   GET    ?action=kartu&id_pharmacy=&fromDate=&toDate=    -> kartu stok (mutasi + saldo)
//   GET    ?action=penerimaan&id_pharmacy=[&fromDate=&toDate=]
//   POST   action=stock_awal | penerimaan
//   DELETE ?id_penerimaan=

include '../../database/connect.php';
require_once __DIR__ . '/../report/farmasiStokService.php';
require_once __DIR__ . '/../master/marginHarga.php';
require_once __DIR__ . '/../../utility/activityLog.php';
mysqli_report(MYSQLI_REPORT_OFF);

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$id_customer = (string) ($_SESSION['id_customer'] ?? '');
$username = $_SESSION['username'] ?? 'system';

function jawab(bool $ok, array $data = [], int $code = 200): void
{
   http_response_code($code);
   echo json_encode(['status' => $ok] + $data, JSON_UNESCAPED_UNICODE);
   exit;
}

if ($id_customer === '') {
   jawab(false, ['message' => 'Session customer tidak ditemukan.'], 401);
}

/** Periode dari request; default bulan berjalan. */
function periode(array $src): array
{
   $from = (string) ($src['fromDate'] ?? date('Y-m-01'));
   $to = (string) ($src['toDate'] ?? date('Y-m-d'));
   if (($err = farmasiStokValidasiPeriode($from, $to)) !== null) {
      jawab(false, ['message' => $err], 422);
   }
   return [$from, $to];
}

function idObat(array $src): int
{
   $id = (int) ($src['id_pharmacy'] ?? 0);
   if ($id <= 0) {
      jawab(false, ['message' => 'ID obat tidak ditemukan.'], 422);
   }
   return $id;
}

/** Obat milik klinik ini (boleh diubah). Obat master bersama hanya bisa dilihat. */
function obatMilikKlinik(mysqli $koneksi, int $idObat, string $idCustomer): bool
{
   $stmt = $koneksi->prepare("SELECT 1 FROM ms_pharmacy WHERE id_pharmacy = ? AND id_customer = ? LIMIT 1");
   $stmt->bind_param('is', $idObat, $idCustomer);
   $stmt->execute();
   $ada = (bool) $stmt->get_result()->fetch_row();
   $stmt->close();
   return $ada;
}

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         $action = $_GET['action'] ?? 'detail';
         if ($action === 'detail') getDetailStock($koneksi, $id_customer);
         if ($action === 'kartu') getKartuStock($koneksi, $id_customer);
         if ($action === 'penerimaan') getPenerimaan($koneksi, $id_customer);
         jawab(false, ['message' => 'Action GET tidak ditemukan.'], 400);
         // no break: jawab() selalu exit
      case 'POST':
         $action = $_POST['action'] ?? '';
         if ($action === 'stock_awal') setStockAwal($koneksi, $id_customer);
         if ($action === 'penerimaan') savePenerimaan($koneksi, $id_customer, $username);
         jawab(false, ['message' => 'Action POST tidak ditemukan.'], 400);
         // no break
      case 'DELETE':
         deletePenerimaan($koneksi, $id_customer);
         // no break
      default:
         jawab(false, ['message' => 'Method request tidak didukung.'], 405);
   }
} catch (Throwable $e) {
   error_log('[pharmacyStock] ' . $e->getMessage());
   jawab(false, ['message' => 'Terjadi kesalahan pada server.'], 500);
}

// ============================================================
// DETAIL STOK (sama persis dengan baris obat di Laporan Farmasi)
// ============================================================
function getDetailStock(mysqli $koneksi, string $idCustomer): void
{
   $idObat = idObat($_GET);
   [$from, $to] = periode($_GET);

   $hasil = farmasiStokReport($koneksi, $idCustomer, $from, $to, $idObat);
   $obat = $hasil['data'][0] ?? null;
   if (!$obat) {
      jawab(false, ['message' => 'Data obat tidak ditemukan atau tidak aktif.'], 404);
   }

   $obat['stok_dasar'] = (float) $obat['stok_dasar'];
   $obat['bisa_ubah'] = obatMilikKlinik($koneksi, $idObat, $idCustomer);
   $obat['nama_dagang_kode'] = farmasiStokSubNama($obat);
   $obat['kategori'] = farmasiStokIsi($obat['pharmacy_category']) ?: '-';
   $obat['satuan'] = farmasiStokIsi($obat['pharmacy_unit']) ?: '';

   jawab(true, ['periode' => ['fromDate' => $from, 'toDate' => $to], 'data' => $obat]);
}

// ============================================================
// KARTU STOK: mutasi masuk (penerimaan) & keluar (resep) + saldo berjalan
// ============================================================
function getKartuStock(mysqli $koneksi, string $idCustomer): void
{
   $idObat = idObat($_GET);
   [$from, $to] = periode($_GET);

   $hasil = farmasiStokReport($koneksi, $idCustomer, $from, $to, $idObat);
   $obat = $hasil['data'][0] ?? null;
   if (!$obat) {
      jawab(false, ['message' => 'Data obat tidak ditemukan atau tidak aktif.'], 404);
   }

   $mutasi = [];
   $idObatStr = (string) $idObat;
   $stmt = $koneksi->prepare("SELECT id_penerimaan, tanggal, nomor_faktur, supplier, batch, jumlah, created_at
      FROM pharmacy_stock_penerimaan
      WHERE id_pharmacy = ? AND id_customer = ? AND tanggal BETWEEN ? AND ?");
   $stmt->bind_param('ssss', $idObatStr, $idCustomer, $from, $to);
   $stmt->execute();
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $m) {
      $ket = trim(implode(' • ', array_filter([
         $m['nomor_faktur'] ? 'Faktur ' . $m['nomor_faktur'] : '',
         $m['supplier'],
         $m['batch'] ? 'Batch ' . $m['batch'] : '',
      ])));
      $mutasi[] = [
         'waktu' => $m['tanggal'] . ' 00:00:00',
         'jenis' => 'masuk',
         'referensi' => 'Penerimaan',
         'keterangan' => $m['supplier'] ?: ($m['nomor_faktur'] ? 'Faktur ' . $m['nomor_faktur'] : '-'),
         'sub' => $ket,
         'masuk' => (float) $m['jumlah'],
         'keluar' => 0.0,
         'urut' => 0,
      ];
   }
   $stmt->close();

   $awal = $from . ' 00:00:00';
   $akhir = $to . ' 23:59:59';
   $stmt = $koneksi->prepare("SELECT d.created_at, d.qty, pp.id_permintaan_farmasi, pp.id_visit, pp.status_permintaan,
         pv.patient_name_pcare
      FROM permintaan_pharmacy_details d
      INNER JOIN permintaan_pharmacy pp ON pp.id_permintaan_farmasi = d.id_permintaan_farmasi
      LEFT JOIN pasien_visit pv ON pv.visit_ID = pp.id_visit AND pv.id_customer = pp.id_customer
      WHERE d.id_pharmacy = ? AND pp.id_customer = ? AND d.created_at BETWEEN ? AND ?");
   $stmt->bind_param('isss', $idObat, $idCustomer, $awal, $akhir);
   $stmt->execute();
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $k) {
      $mutasi[] = [
         'waktu' => $k['created_at'],
         'jenis' => 'keluar',
         'referensi' => 'Resep #' . $k['id_permintaan_farmasi'],
         'keterangan' => $k['patient_name_pcare'] ?: '-',
         'sub' => (string) $k['id_visit'],
         'masuk' => 0.0,
         'keluar' => (float) $k['qty'],
         'urut' => 1,
      ];
   }
   $stmt->close();

   usort($mutasi, fn($a, $b) => [$a['waktu'], $a['urut']] <=> [$b['waktu'], $b['urut']]);
   $saldo = (float) $obat['stok_awal'];
   foreach ($mutasi as &$m) {
      $saldo += $m['masuk'] - $m['keluar'];
      $m['saldo'] = $saldo;
      unset($m['urut']);
   }
   unset($m);

   jawab(true, [
      'stok_awal' => (float) $obat['stok_awal'],
      'stok_akhir' => (float) $obat['stok_akhir'],
      'data' => $mutasi,
   ]);
}

// ============================================================
// RIWAYAT PENERIMAAN (opsional per periode)
// ============================================================
function getPenerimaan(mysqli $koneksi, string $idCustomer): void
{
   $idObat = (string) idObat($_GET);
   $pakaiPeriode = isset($_GET['fromDate']) || isset($_GET['toDate']);
   $sql = "SELECT id_penerimaan, id_pharmacy, tanggal, nomor_faktur, supplier, batch, expired,
         jumlah, harga_beli, keterangan, created_at, created_by
      FROM pharmacy_stock_penerimaan
      WHERE id_pharmacy = ? AND id_customer = ?" . ($pakaiPeriode ? " AND tanggal BETWEEN ? AND ?" : "") . "
      ORDER BY tanggal DESC, id_penerimaan DESC";
   $stmt = $koneksi->prepare($sql);
   if ($pakaiPeriode) {
      [$from, $to] = periode($_GET);
      $stmt->bind_param('ssss', $idObat, $idCustomer, $from, $to);
   } else {
      $stmt->bind_param('ss', $idObat, $idCustomer);
   }
   $stmt->execute();
   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();
   jawab(true, ['data' => $data]);
}

// ============================================================
// SIMPAN STOK DASAR (ms_pharmacy.pharmacy_stock)
// ============================================================
function setStockAwal(mysqli $koneksi, string $idCustomer): void
{
   $idObat = idObat($_POST);
   $stok = $_POST['stock_awal'] ?? '';
   if ($stok === '' || !is_numeric($stok) || (float) $stok < 0) {
      jawab(false, ['message' => 'Stok awal harus angka 0 atau lebih.'], 422);
   }
   if (!obatMilikKlinik($koneksi, $idObat, $idCustomer)) {
      jawab(false, ['message' => 'Obat master bersama tidak bisa diubah stok awalnya dari klinik.'], 403);
   }
   $stok = (int) $stok;
   $obLama = obatSnapshot($koneksi, $idObat);
   $stmt = $koneksi->prepare("UPDATE ms_pharmacy SET pharmacy_stock = ? WHERE id_pharmacy = ? AND id_customer = ?");
   $stmt->bind_param('iis', $stok, $idObat, $idCustomer);
   if (!$stmt->execute()) {
      error_log('[pharmacyStock] ' . $stmt->error);
      jawab(false, ['message' => 'Stok awal gagal disimpan.'], 500);
   }
   $stmt->close();
   if ($obLama && (int) $obLama['pharmacy_stock'] !== $stok) {
      logAktivitas($koneksi, 'stok', 'penyesuaian', ['tipe' => 'obat', 'id' => $idObat, 'label' => (string) $obLama['nama_obat']],
         'Stok awal ' . (int) $obLama['pharmacy_stock'] . ' → ' . $stok . ' (' . ($stok - (int) $obLama['pharmacy_stock'] >= 0 ? '+' : '') . ($stok - (int) $obLama['pharmacy_stock']) . ')',
         ['pharmacy_stock' => (int) $obLama['pharmacy_stock']], ['pharmacy_stock' => $stok]);
   }
   jawab(true, ['message' => 'Stok awal berhasil disimpan.', 'data' => ['id_pharmacy' => $idObat, 'stock_awal' => $stok]]);
}

// ============================================================
// SIMPAN PENERIMAAN
// ============================================================
function savePenerimaan(mysqli $koneksi, string $idCustomer, string $username): void
{
   $idObat = idObat($_POST);
   $tanggal = trim((string) ($_POST['tanggal'] ?? ''));
   $expired = trim((string) ($_POST['expired'] ?? ''));
   $jumlah = $_POST['jumlah'] ?? 0;
   $hargaBeli = $_POST['harga_beli'] ?? 0;
   $teks = fn($k, $max = 255) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);

   $tglValid = fn($d) => ($x = DateTime::createFromFormat('Y-m-d', $d)) && $x->format('Y-m-d') === $d;
   if (!$tglValid($tanggal)) {
      jawab(false, ['message' => 'Tanggal penerimaan wajib diisi dengan benar.'], 422);
   }
   if ($expired !== '' && !$tglValid($expired)) {
      jawab(false, ['message' => 'Tanggal expired tidak valid.'], 422);
   }
   if (!is_numeric($jumlah) || (int) $jumlah <= 0) {
      jawab(false, ['message' => 'Jumlah penerimaan harus lebih dari 0.'], 422);
   }
   if (!obatMilikKlinik($koneksi, $idObat, $idCustomer)) {
      jawab(false, ['message' => 'Obat tidak ditemukan pada master klinik ini.'], 404);
   }

   $idObatStr = (string) $idObat;
   $jumlah = (int) $jumlah;
   $hargaBeli = is_numeric($hargaBeli) && (float) $hargaBeli >= 0 ? (float) $hargaBeli : 0.0;
   $expired = $expired !== '' ? $expired : null;
   $faktur = $teks('nomor_faktur');
   $supplier = $teks('supplier');
   $batch = $teks('batch');
   $keterangan = $teks('keterangan', 2000);

   $stmt = $koneksi->prepare("INSERT INTO pharmacy_stock_penerimaan
      (id_pharmacy, id_customer, tanggal, nomor_faktur, supplier, batch, expired, jumlah, harga_beli, keterangan, created_by)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
   $stmt->bind_param('sssssssidss', $idObatStr, $idCustomer, $tanggal, $faktur, $supplier, $batch, $expired, $jumlah, $hargaBeli, $keterangan, $username);
   if (!$stmt->execute()) {
      error_log('[pharmacyStock] ' . $stmt->error);
      jawab(false, ['message' => 'Penerimaan gagal disimpan.'], 500);
   }
   $id = $stmt->insert_id;
   $stmt->close();
   $obLama = obatSnapshot($koneksi, $idObat);

   // Margin aktif (Setting Margin): harga beli penerimaan ini menjadi harga beli terbaru
   // dan harga jual obat dihitung ulang dari margin.
   $hargaJual = null;
   if ($hargaBeli > 0) {
      $idcInt = (int) $idCustomer;
      $st = $koneksi->prepare("SELECT pharmacy_category FROM ms_pharmacy WHERE id_pharmacy = ? AND id_customer = ?");
      $st->bind_param('ii', $idObat, $idcInt);
      $st->execute();
      $kat = ($st->get_result()->fetch_assoc() ?: [])['pharmacy_category'] ?? null;
      $st->close();
      $pct = marginKategori(marginFaskes($koneksi, $idcInt), $kat);
      if ($pct !== null) {
         $hargaJual = hargaJualMargin($hargaBeli, $pct);
         $beliBulat = (int) round($hargaBeli);
         $st = $koneksi->prepare("UPDATE ms_pharmacy SET pharmacy_buy = ?, pharmacy_price_buy = ?, pharmacy_sale = ? WHERE id_pharmacy = ? AND id_customer = ?");
         $st->bind_param('iiiii', $beliBulat, $beliBulat, $hargaJual, $idObat, $idcInt);
         if (!$st->execute()) {
            error_log('[pharmacyStock] harga margin: ' . $st->error);
            $hargaJual = null;
         }
         $st->close();
      }
   }

   $pnBaru = ['tanggal' => $tanggal, 'nomor_faktur' => $faktur, 'supplier' => $supplier, 'batch' => $batch, 'expired' => $expired, 'jumlah' => $jumlah, 'harga_beli' => $hargaBeli, 'keterangan' => $keterangan];
   logAktivitas($koneksi, 'stok', 'stok_masuk', ['tipe' => 'obat', 'id' => $idObat, 'label' => (string) ($obLama['nama_obat'] ?? 'Obat #' . $idObat)],
      'Penerimaan +' . $jumlah . ($faktur !== '' ? ' · faktur ' . $faktur : '') . ($supplier !== '' ? ' · ' . $supplier : '') . ' (penerimaan #' . $id . ')', null, $pnBaru);
   if ($hargaJual !== null && $obLama && (int) $obLama['pharmacy_sale'] !== (int) $hargaJual) {
      logAktivitas($koneksi, 'stok', 'ubah_harga', ['tipe' => 'obat', 'id' => $idObat, 'label' => (string) $obLama['nama_obat']],
         'Harga otomatis dari margin setelah penerimaan #' . $id,
         ['pharmacy_buy' => $obLama['pharmacy_buy'], 'pharmacy_sale' => $obLama['pharmacy_sale']], ['pharmacy_buy' => (int) round($hargaBeli), 'pharmacy_sale' => $hargaJual]);
   }
   jawab(true, [
      'message' => 'Penerimaan stok berhasil disimpan.' . ($hargaJual !== null ? ' Harga jual diperbarui otomatis menjadi Rp ' . number_format($hargaJual, 0, ',', '.') . ' (margin).' : ''),
      'id' => $id,
      'harga_jual' => $hargaJual,
   ]);
}

// ============================================================
// HAPUS PENERIMAAN
// ============================================================
function deletePenerimaan(mysqli $koneksi, string $idCustomer): void
{
   $id = (int) ($_GET['id_penerimaan'] ?? 0);
   if ($id <= 0) {
      jawab(false, ['message' => 'ID penerimaan tidak valid.'], 422);
   }
   $st = $koneksi->prepare("SELECT id_pharmacy, tanggal, nomor_faktur, supplier, batch, expired, jumlah, harga_beli, keterangan, created_by
      FROM pharmacy_stock_penerimaan WHERE id_penerimaan = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('is', $id, $idCustomer);
   $st->execute();
   $pnLama = $st->get_result()->fetch_assoc();
   $st->close();
   $stmt = $koneksi->prepare("DELETE FROM pharmacy_stock_penerimaan WHERE id_penerimaan = ? AND id_customer = ?");
   $stmt->bind_param('is', $id, $idCustomer);
   if (!$stmt->execute()) {
      error_log('[pharmacyStock] ' . $stmt->error);
      jawab(false, ['message' => 'Data penerimaan gagal dihapus.'], 500);
   }
   $hapus = $stmt->affected_rows;
   $stmt->close();
   if ($hapus > 0 && $pnLama) {
      $ob = obatSnapshot($koneksi, (int) $pnLama['id_pharmacy']);
      logAktivitas($koneksi, 'stok', 'hapus', ['tipe' => 'penerimaan', 'id' => $id, 'label' => (string) ($ob['nama_obat'] ?? 'Obat #' . $pnLama['id_pharmacy'])],
         'Penerimaan #' . $id . ' dihapus (stok −' . (int) $pnLama['jumlah'] . ')', $pnLama, null);
   }
   $hapus > 0
      ? jawab(true, ['message' => 'Data penerimaan berhasil dihapus.'])
      : jawab(false, ['message' => 'Data penerimaan tidak ditemukan atau bukan milik klinik ini.'], 404);
}
