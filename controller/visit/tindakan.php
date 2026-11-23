<?php
/**
 * Tindakan / jasa (pasien_billing) satu kunjungan — module/admin/kasir_detail.
 *
 * GET    ?no=<visit_ID>        -> { status, data:[...] }
 * GET    ?id=<id_billing>      -> { status, user:{...} }   (nama "user" dipertahankan untuk kompatibilitas)
 * POST   nomor_visit, item, qty, diskon, kategori, catatan   -> tambah (harga dari ms_tarif faskes)
 * PUT    iduser|id, jumlah|harga, diskon, qty?, catatan?      -> ubah
 * DELETE ?id=<id_billing>
 *
 * Perbaikan dari versi lama: SQL injection (no, search, harga), diskon 0 ditolak saat ubah ("wajib diisi"),
 * kunjungan tidak dicek milik faskes, item tetap bisa diubah/dihapus setelah kunjungan lunas,
 * qty/diskon negatif atau diskon melebihi subtotal, session_start() ganda (Notice sebelum JSON).
 */
require '../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');

function balas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

$idc = (string) ($_SESSION['id_customer'] ?? '');
session_write_close();
if ($idc === '') balas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

function angka($v, string $nama, int $min = 0): int
{
   $v = trim((string) ($v ?? ''));
   if ($v === '') return $min;
   if (!preg_match('/^\d{1,10}$/', str_replace('.', '', $v))) balas(422, ['status' => 'error', 'message' => "$nama harus berupa angka bulat."]);
   $n = (int) str_replace('.', '', $v);
   if ($n < $min) balas(422, ['status' => 'error', 'message' => "$nama minimal $min."]);
   return $n;
}

/** Kunjungan milik faskes & belum lunas, atau berhenti dengan error. */
function cekKunjungan(mysqli $k, string $no, string $idc): void
{
   $st = $k->prepare("SELECT status_bayar FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('ss', $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$r) balas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   if ((int) $r['status_bayar'] === 1) balas(409, ['status' => 'error', 'message' => 'Kunjungan sudah lunas, tagihan tidak bisa diubah lagi.']);
}

function ambilItem(mysqli $k, int $id, string $idc): array
{
   $st = $k->prepare("SELECT * FROM pasien_billing WHERE id_billing = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('is', $id, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$r) balas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   return $r;
}

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         if (isset($_GET['id'])) balas(200, ['status' => 'success', 'user' => ambilItem($koneksi, (int) $_GET['id'], $idc)]);
         $no = trim((string) ($_GET['no'] ?? ''));
         $st = $koneksi->prepare("SELECT * FROM pasien_billing WHERE id_visit = ? AND id_customer = ? ORDER BY id_billing");
         $st->bind_param('ss', $no, $idc);
         $st->execute();
         $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
         $st->close();
         balas(200, ['status' => 'success', 'data' => $data, 'recordsTotal' => count($data), 'recordsFiltered' => count($data)]);

      case 'POST':
         $no = trim((string) ($_POST['nomor_visit'] ?? ''));
         $item = trim((string) ($_POST['item'] ?? ''));
         if ($item === '') balas(422, ['status' => 'error', 'message' => 'Nama tindakan harus dipilih.']);
         cekKunjungan($koneksi, $no, $idc);
         $qty = angka($_POST['qty'] ?? '1', 'Jumlah', 1);
         $diskon = angka($_POST['diskon'] ?? '0', 'Diskon');
         $kategori = mb_substr(trim((string) ($_POST['kategori'] ?? '')), 0, 255);
         $catatan = mb_substr(trim((string) ($_POST['catatan'] ?? '')), 0, 2000);
         $st = $koneksi->prepare("SELECT tarif_amount FROM ms_tarif WHERE tarif_name = ? AND id_customer = ? AND tarif_status = 1 ORDER BY id_tarif DESC LIMIT 1");
         $st->bind_param('ss', $item, $idc);
         $st->execute();
         $t = $st->get_result()->fetch_assoc();
         $st->close();
         if (!$t) balas(422, ['status' => 'error', 'message' => 'Tindakan tidak ada di master tarif faskes ini.']);
         $harga = (int) $t['tarif_amount'];
         if ($diskon > $harga * $qty) balas(422, ['status' => 'error', 'message' => 'Diskon tidak boleh melebihi subtotal (Rp' . number_format($harga * $qty, 0, ',', '.') . ').']);
         $st = $koneksi->prepare("INSERT INTO pasien_billing (id_customer, id_visit, billing_item, billing_price, billing_qty, billing_discount, billing_category, billing_notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
         $st->bind_param('sssiiiss', $idc, $no, $item, $harga, $qty, $diskon, $kategori, $catatan);
         $st->execute();
         $st->close();
         balas(200, ['status' => 'success', 'message' => 'Tindakan ditambahkan.']);

      case 'PUT':
         parse_str(file_get_contents('php://input'), $in);
         $id = (int) ($in['id'] ?? $in['iduser'] ?? 0);
         $lama = ambilItem($koneksi, $id, $idc);
         cekKunjungan($koneksi, (string) $lama['id_visit'], $idc);
         $harga = angka($in['harga'] ?? $in['jumlah'] ?? $lama['billing_price'], 'Harga');
         $qty = angka($in['qty'] ?? $lama['billing_qty'], 'Jumlah', 1);
         $diskon = angka($in['diskon'] ?? '0', 'Diskon');
         $catatan = array_key_exists('catatan', $in) ? mb_substr(trim((string) $in['catatan']), 0, 2000) : (string) $lama['billing_notes'];
         if ($diskon > $harga * $qty) balas(422, ['status' => 'error', 'message' => 'Diskon tidak boleh melebihi subtotal (Rp' . number_format($harga * $qty, 0, ',', '.') . ').']);
         $st = $koneksi->prepare("UPDATE pasien_billing SET billing_price = ?, billing_qty = ?, billing_discount = ?, billing_notes = ? WHERE id_billing = ? AND id_customer = ?");
         $st->bind_param('iiisis', $harga, $qty, $diskon, $catatan, $id, $idc);
         $st->execute();
         $st->close();
         balas(200, ['status' => 'success', 'message' => 'Tindakan diperbarui.']);

      case 'DELETE':
         $id = (int) ($_GET['id'] ?? 0);
         $lama = ambilItem($koneksi, $id, $idc);
         cekKunjungan($koneksi, (string) $lama['id_visit'], $idc);
         $st = $koneksi->prepare("DELETE FROM pasien_billing WHERE id_billing = ? AND id_customer = ?");
         $st->bind_param('is', $id, $idc);
         $st->execute();
         $st->close();
         balas(200, ['status' => 'success', 'message' => 'Tindakan dihapus.']);

      default:
         balas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[visit/tindakan] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memproses tindakan.']);
}
