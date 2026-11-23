<?php
/**
 * Daftar pembayaran pasien untuk module/admin/kasir.
 *
 * GET ?fromDate=Y-m-d&toDate=Y-m-d (tanggal kunjungan, maks 62 hari)
 *  -> { status, data:[...], rentang:{dari, sampai} }
 *
 * Hanya kunjungan yang pemeriksaannya sudah diselesaikan (visit_status = 4).
 * tagihan = obat (permintaan_pharmacy_details qty*harga) + jasa (pasien_billing qty*harga-diskon, min 0),
 * rumus sama dengan module/admin/kasir_detail.
 *
 * Versi lama (controller/visit/kasirController):
 *  - SELECT pasien_visit.* (±100 kolom) -> hari ini 535 KB, sebulan 16,9 MB
 *  - DATE(visit_date) BETWEEN .. -> index idx_date_cust tidak terpakai (scan 200 rb baris)
 *  - filter dokter membandingkan id_doctor (berisi NAMA dokter) dengan id angka ms_doctor -> selalu kosong
 *  - filter poli dikirim tapi tidak dipakai; toDate kosong (setelah Reset) -> 0 baris
 *  - session_start() ganda -> Notice PHP sebelum JSON
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

$hariIni = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
$tgl = function ($v) {
   $v = (string) $v;
   $d = DateTime::createFromFormat('!Y-m-d', $v);
   return ($d && $d->format('Y-m-d') === $v) ? $v : null;
};
$dari = $tgl($_GET['fromDate'] ?? '') ?? $hariIni;
$sampai = $tgl($_GET['toDate'] ?? '') ?? $dari;
if ($sampai < $dari) [$dari, $sampai] = [$sampai, $dari];
if ((new DateTime($dari))->diff(new DateTime($sampai))->days > 62) balas(422, ['status' => 'error', 'message' => 'Rentang tanggal maksimal 62 hari.']);
$akhir = (new DateTime($sampai))->modify('+1 day')->format('Y-m-d');

$sql = "SELECT pv.visit_ID, pv.visit_date, pv.visit_time, pv.id_doctor, pv.id_poli, pv.id_provider,
      pv.status_bayar, pv.metode_bayar, pv.nomor_transaksi, pv.amount_results, pv.status_rawatinap,
      COALESCE(NULLIF(TRIM(pv.patient_name_pcare), ''), mp.patient_name) AS patient_name, mp.nomor_rm, mp.patient_gender, mp.patient_datebirth,
      prv.provider_name
   FROM pasien_visit pv
   LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
   LEFT JOIN ms_provider prv ON prv.id_provider = pv.id_provider
   WHERE pv.id_customer = ? AND pv.visit_status = 4 AND pv.visit_date >= ? AND pv.visit_date < ?
   ORDER BY pv.visit_date DESC, pv.visit_time DESC, pv.id_visit DESC";

// Tagihan dihitung terpisah (2 langkah). permintaan_pharmacy_details (±630 rb baris) tidak punya index
// id_permintaan_farmasi; JOIN langsung membuat MySQL memindai tabel itu lalu lookup per baris (±0,85 dtk),
// sedangkan ambil id tiket dulu lalu IN (...) cukup 1 pemindaian (±0,3 dtk).
$obat = $jasa = [];
try {
   $st = $koneksi->prepare($sql);
   $st->bind_param('sss', $idc, $dari, $akhir);
   $st->execute();
   $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();

   $adaVisit = array_fill_keys(array_filter(array_column($rows, 'visit_ID'), fn($v) => $v !== null && $v !== ''), true);
   if ($adaVisit) {
      // tiket resep dibuat pada/sesudah tanggal kunjungan (mundur 1 hari untuk jaga-jaga zona waktu)
      $resepDari = (new DateTime($dari))->modify('-1 day')->format('Y-m-d');
      $st = $koneksi->prepare("SELECT id_permintaan_farmasi, id_visit FROM permintaan_pharmacy WHERE id_customer = ? AND created_at >= ?");
      $st->bind_param('ss', $idc, $resepDari);
      $st->execute();
      $tiket = [];
      foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as [$idt, $v]) if (isset($adaVisit[$v])) $tiket[(int) $idt] = $v;
      $st->close();
      foreach (array_chunk(array_keys($tiket), 20000) as $ids) {
         $st = $koneksi->prepare("SELECT id_permintaan_farmasi, SUM(qty * harga) FROM permintaan_pharmacy_details
            WHERE id_permintaan_farmasi IN (" . implode(',', array_fill(0, count($ids), '?')) . ") GROUP BY id_permintaan_farmasi");
         $st->bind_param(str_repeat('i', count($ids)), ...$ids);
         $st->execute();
         foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as [$idt, $sum]) $obat[$tiket[(int) $idt]] = ($obat[$tiket[(int) $idt]] ?? 0) + (int) $sum;
         $st->close();
      }
      $st = $koneksi->prepare("SELECT id_visit, SUM(GREATEST(0, billing_qty * billing_price - billing_discount)) FROM pasien_billing
         WHERE id_customer = ? GROUP BY id_visit");
      $st->bind_param('s', $idc);
      $st->execute();
      foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as [$v, $sum]) if (isset($adaVisit[$v])) $jasa[$v] = (int) $sum;
      $st->close();
   }
} catch (Throwable $e) {
   error_log('[visit/kasirList] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memuat data pembayaran.']);
}

$acuan = new DateTime($hariIni);
$data = [];
foreach ($rows as $r) {
   $umur = null;
   if (!empty($r['patient_datebirth']) && ($lahir = DateTime::createFromFormat('!Y-m-d', substr((string) $r['patient_datebirth'], 0, 10)))) $umur = $lahir->diff($acuan)->y;
   $o = $obat[$r['visit_ID']] ?? 0;
   $j = $jasa[$r['visit_ID']] ?? 0;
   $data[] = [
      'visit_ID' => $r['visit_ID'],
      'visit_date' => $r['visit_date'],
      'visit_time' => $r['visit_time'],
      'dokter' => $r['id_doctor'],   // kolom id_doctor berisi nama dokter
      'poli' => $r['id_poli'],       // kolom id_poli berisi nama poli
      'id_provider' => (int) $r['id_provider'],
      'provider_name' => $r['provider_name'],
      'patient_name' => $r['patient_name'],
      'nomor_rm' => $r['nomor_rm'],
      'patient_gender' => $r['patient_gender'],
      'umur' => $umur,
      'rawat_inap' => (int) $r['status_rawatinap'] === 1,
      'lunas' => (int) $r['status_bayar'] === 1,
      'obat' => $o,
      'jasa' => $j,
      'tagihan' => $o + $j,
      'metode_bayar' => $r['metode_bayar'],
      'nomor_transaksi' => $r['nomor_transaksi'],
      'dibayar' => (int) $r['amount_results'],
   ];
}

balas(200, ['status' => 'success', 'data' => $data, 'rentang' => ['dari' => $dari, 'sampai' => $sampai]]);
