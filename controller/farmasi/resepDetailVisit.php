<?php
/**
 * Semua tiket resep + itemnya untuk satu kunjungan (module/admin/farmasi_order_detail).
 *
 * GET ?no=<visit_ID> -> { status, tiket:[{...tiket, items:[...], total}], ringkas:{menunggu, disiapkan, selesai, total} }
 *
 * Dulu halaman memanggil controller/visit/permintaanFarmasiAddOns sekali per tiket. Tabel
 * permintaan_pharmacy_details (±630 rb baris) tidak punya index id_permintaan_farmasi, jadi tiap
 * panggilan = 1 scan penuh (13 tiket ≈ 6,6 dtk). Di sini item semua tiket diambil dalam 1 query.
 * Tiket draf dokter (status 0) tidak ditampilkan, sama seperti sebelumnya.
 */
require '../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');
if (!ini_get('zlib.output_compression')) @ob_start('ob_gzhandler');

function balas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') balas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$idc = (string) ($_SESSION['id_customer'] ?? '');
session_write_close();
if ($idc === '') balas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);
$no = trim((string) ($_GET['no'] ?? ''));
if ($no === '' || strlen($no) > 100) balas(422, ['status' => 'error', 'message' => 'Nomor kunjungan tidak valid.']);

try {
   $st = $koneksi->prepare("SELECT id_permintaan_farmasi, permintaan_number, created_at, status_permintaan, catatan_permintaan,
         tipe_obat, status_obat_pulang, rck_nama, rck_jumlah, rck_satuan, rck_signa, rck_tipe
      FROM permintaan_pharmacy
      WHERE id_visit = ? AND id_customer = ? AND status_permintaan IN (1, 2, 3)
      ORDER BY status_permintaan ASC, created_at DESC, id_permintaan_farmasi DESC");
   $st->bind_param('ss', $no, $idc);
   $st->execute();
   $tiket = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();

   $items = [];
   if ($tiket) {
      $ids = array_map(fn($t) => (int) $t['id_permintaan_farmasi'], $tiket);
      $tanda = implode(',', array_fill(0, count($ids), '?'));
      $st = $koneksi->prepare("SELECT d.id_pharmacy_details, d.id_permintaan_farmasi, d.id_pharmacy, d.qty, d.qty_racik, d.signa, d.catatan, d.harga,
            m.pharmacy_name_generic, m.pharmacy_name_trade, m.pharmacy_unit
         FROM permintaan_pharmacy_details d
         LEFT JOIN ms_pharmacy m ON m.id_pharmacy = d.id_pharmacy
         WHERE d.id_permintaan_farmasi IN ($tanda)
         ORDER BY d.id_pharmacy_details ASC");
      $st->bind_param(str_repeat('i', count($ids)), ...$ids);
      $st->execute();
      foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $d) $items[(int) $d['id_permintaan_farmasi']][] = $d;
      $st->close();
   }
} catch (Throwable $e) {
   error_log('[farmasi/resepDetailVisit] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memuat resep.']);
}

$ringkas = ['menunggu' => 0, 'disiapkan' => 0, 'selesai' => 0, 'total' => 0];
foreach ($tiket as &$t) {
   $t['id_permintaan_farmasi'] = (int) $t['id_permintaan_farmasi'];
   $t['status_permintaan'] = (int) $t['status_permintaan'];
   $t['items'] = $items[$t['id_permintaan_farmasi']] ?? [];
   $t['total'] = 0;
   foreach ($t['items'] as &$d) {
      $d['qty'] = (int) $d['qty'];
      $d['harga'] = (int) $d['harga'];
      $d['subtotal'] = $d['qty'] * $d['harga'];
      $t['total'] += $d['subtotal'];
   }
   unset($d);
   $ringkas['total'] += $t['total'];
   $ringkas[[1 => 'menunggu', 2 => 'disiapkan', 3 => 'selesai'][$t['status_permintaan']]]++;
}
unset($t);

balas(200, ['status' => 'success', 'tiket' => $tiket, 'ringkas' => $ringkas]);
