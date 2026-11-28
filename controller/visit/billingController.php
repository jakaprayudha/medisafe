<?php
/**
 * Tindakan / biaya jasa per kunjungan (module/admin/billing — menu "Tindakan").
 *   GET    ?no=<visit_ID>        -> { status, data:[...], kunjungan:{lunas, batal}, total:{...} }
 *   GET    ?no=..&id=<id>        -> 1 item
 *   POST   (form)                -> tambah
 *   PUT    (form, id_billing)    -> ubah
 *   DELETE ?id=<id>              -> hapus
 * Item tidak bisa ditambah/diubah/dihapus bila tagihan kunjungan sudah lunas (pasien_visit.status_bayar = 1).
 * Perbaikan: GET ?id tanpa batas faskes (item faskes lain bisa dibaca); tambah ke kunjungan faskes lain / batal;
 * PUT bisa memindahkan item ke kunjungan lain (id_visit); harga/qty/diskon tidak divalidasi (diskon negatif atau
 * melebihi subtotal, qty 0); item bisa diubah setelah lunas sehingga tidak cocok dengan pembayaran; pesan error SQL
 * dikirim ke browser; gagal hapus/ubah tetap "success".
 */
include '../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();

function blBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) blBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

const BL_KATEGORI = ['Tindakan', 'Konsultasi', 'Obat/BMHP/Alkes', 'Lainnya'];

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         isset($_GET['id']) ? blGetId((int) $_GET['id'], $idc) : blList(trim((string) ($_GET['no'] ?? '')), $idc);
         break;
      case 'POST':
         blSimpan($_POST, $idc, false);
         break;
      case 'PUT':
         parse_str(file_get_contents('php://input'), $put);
         blSimpan($put, $idc, true);
         break;
      case 'DELETE':
         blHapus((int) ($_GET['id'] ?? 0), $idc);
         break;
      default:
         blBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[visit/billingController] ' . $e->getMessage());
   blBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

/** Kunjungan milik faskes sesi -> ['status_bayar','visit_status'] atau null */
function blKunjungan(string $visit, int $idc): ?array
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT status_bayar, visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('si', $visit, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function blCari(int $id, int $idc): ?array
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT id_billing, id_visit, billing_number, billing_item, billing_category, billing_price, billing_qty, billing_discount, billing_notes, created_at
      FROM pasien_billing WHERE id_billing = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('ii', $id, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function blList(string $no, int $idc): void
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT id_billing, id_visit, billing_number, billing_item, billing_category, billing_price, billing_qty, billing_discount, billing_notes, created_at
      FROM pasien_billing WHERE id_visit = ? AND id_customer = ? ORDER BY id_billing ASC");
   $st->bind_param('si', $no, $idc);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   $k = blKunjungan($no, $idc);
   $sub = $disk = 0;
   foreach ($data as $r) {
      $sub += (int) $r['billing_price'] * (int) $r['billing_qty'];
      $disk += (int) $r['billing_discount'];
   }
   blBalas(200, [
      'status' => 'success',
      'data' => $data,
      'kunjungan' => $k ? ['lunas' => (int) $k['status_bayar'] === 1, 'batal' => (int) $k['visit_status'] === 99] : null,
      'total' => ['subtotal' => $sub, 'diskon' => $disk, 'total' => max(0, $sub - $disk)],
   ]);
}

function blGetId(int $id, int $idc): void
{
   $r = blCari($id, $idc);
   if (!$r) blBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   blBalas(200, ['status' => 'success', 'data' => $r]);
}

/** Validasi isi form. */
function blIsi(array $in): array
{
   $t = fn($k, $max) => mb_substr(trim((string) (is_scalar($in[$k] ?? null) ? $in[$k] : '')), 0, $max);
   $angka = fn($k) => preg_replace('/[^\d-]/', '', $t($k, 20));   // terima "50.000" / "Rp 50.000"
   $d = [
      'billing_item' => $t('billing_item', 255),
      'billing_category' => $t('billing_category', 50),
      'billing_price' => $angka('billing_price'),
      'billing_qty' => $angka('billing_qty'),
      'billing_discount' => $angka('billing_discount'),
      'billing_notes' => $t('billing_notes', 2000),
   ];
   $tolak = fn($pesan, $f) => blBalas(422, ['status' => 'error', 'message' => $pesan, 'field' => $f]);
   if ($d['billing_item'] === '') $tolak('Nama item wajib diisi.', 'billing_item');
   if (!in_array($d['billing_category'], BL_KATEGORI, true)) $tolak('Kategori tidak valid.', 'billing_category');
   if ($d['billing_price'] === '' || (int) $d['billing_price'] < 0 || (int) $d['billing_price'] > 1000000000) $tolak('Harga harus angka 0 atau lebih.', 'billing_price');
   if ($d['billing_qty'] === '' || (int) $d['billing_qty'] < 1 || (int) $d['billing_qty'] > 10000) $tolak('Qty minimal 1.', 'billing_qty');
   $d['billing_discount'] = $d['billing_discount'] === '' ? 0 : (int) $d['billing_discount'];
   $d['billing_price'] = (int) $d['billing_price'];
   $d['billing_qty'] = (int) $d['billing_qty'];
   if ($d['billing_discount'] < 0) $tolak('Diskon tidak boleh negatif.', 'billing_discount');
   if ($d['billing_discount'] > $d['billing_price'] * $d['billing_qty']) $tolak('Diskon melebihi subtotal (harga × qty).', 'billing_discount');
   return $d;
}

function blSimpan(array $in, int $idc, bool $ubah): void
{
   global $koneksi;
   if ($ubah) {
      $id = (int) ($in['id_billing'] ?? ($_GET['id'] ?? 0));
      $lama = $id > 0 ? blCari($id, $idc) : null;
      if (!$lama) blBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
      $visit = (string) $lama['id_visit'];
   } else {
      $visit = trim((string) ($in['id_visit'] ?? ($_GET['no'] ?? '')));
   }
   $k = blKunjungan($visit, $idc);
   if (!$k) blBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   if ((int) $k['status_bayar'] === 1) blBalas(423, ['status' => 'error', 'message' => 'Tagihan kunjungan ini sudah lunas, item tidak bisa diubah. Batalkan pembayaran di kasir terlebih dahulu.']);
   if (!$ubah && (int) $k['visit_status'] === 99) blBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
   $d = blIsi($in);

   if ($ubah) {
      // id_visit sengaja tidak ikut diubah (dulu bisa memindahkan item ke kunjungan lain)
      $st = $koneksi->prepare("UPDATE pasien_billing SET billing_item = ?, billing_category = ?, billing_price = ?, billing_qty = ?, billing_discount = ?, billing_notes = ?
         WHERE id_billing = ? AND id_customer = ?");
      $st->bind_param('ssiiisii', $d['billing_item'], $d['billing_category'], $d['billing_price'], $d['billing_qty'], $d['billing_discount'], $d['billing_notes'], $id, $idc);
      $st->execute();
      $kena = $st->affected_rows;
      $st->close();
      if ($kena > 0) blLog('ubah', $id, $visit, $lama, $d);
      blBalas(200, ['status' => 'success', 'message' => 'Item diperbarui.', 'id' => $id]);
   }
   $nomor = blNomor();
   $st = $koneksi->prepare("INSERT INTO pasien_billing (billing_number, id_customer, id_visit, billing_item, billing_category, billing_price, billing_qty, billing_discount, billing_notes)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
   $st->bind_param('sisssiiis', $nomor, $idc, $visit, $d['billing_item'], $d['billing_category'], $d['billing_price'], $d['billing_qty'], $d['billing_discount'], $d['billing_notes']);
   $st->execute();
   $id = $st->insert_id;
   $st->close();
   if ($id) blLog('tambah', (int) $id, $visit, null, $d + ['billing_number' => $nomor]);
   blBalas(200, ['status' => 'success', 'message' => 'Item ditambahkan.', 'id' => $id]);
}

function blHapus(int $id, int $idc): void
{
   global $koneksi;
   $lama = $id > 0 ? blCari($id, $idc) : null;
   if (!$lama) blBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah terhapus.']);
   $k = blKunjungan((string) $lama['id_visit'], $idc);
   if ($k && (int) $k['status_bayar'] === 1) blBalas(423, ['status' => 'error', 'message' => 'Tagihan kunjungan ini sudah lunas, item tidak bisa dihapus. Batalkan pembayaran di kasir terlebih dahulu.']);
   $st = $koneksi->prepare("DELETE FROM pasien_billing WHERE id_billing = ? AND id_customer = ?");
   $st->bind_param('ii', $id, $idc);
   $st->execute();
   $kena = $st->affected_rows;
   $st->close();
   if ($kena > 0) blLog('hapus', $id, (string) $lama['id_visit'], $lama, null);
   blBalas(200, ['status' => 'success', 'message' => 'Item dihapus.']);
}

/** Log perubahan item tagihan (modul billing). */
function blLog(string $aksi, int $id, string $visit, ?array $lama, ?array $baru): void
{
   global $koneksi;
   require_once __DIR__ . '/../../utility/activityLog.php';
   logTagihan($koneksi, $aksi, $id, $visit, $lama, $baru, 'Menu Tindakan');
}

function blNomor(): string
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT 1 FROM pasien_billing WHERE billing_number = ? LIMIT 1");
   for ($i = 0; $i < 20; $i++) {
      $n = 'BIL-' . random_int(100000, 999999);
      $st->bind_param('s', $n);
      $st->execute();
      if (!$st->get_result()->fetch_row()) {
         $st->close();
         return $n;
      }
   }
   $st->close();
   throw new RuntimeException('Gagal membuat nomor billing unik.');
}
