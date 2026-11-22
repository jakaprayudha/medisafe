<?php
/**
 * Daftar permintaan resep per kunjungan untuk module/pharmacy/permintaan-resep.
 *
 * GET ?fromDate=Y-m-d&toDate=Y-m-d  (tanggal permintaan dibuat, maks 62 hari)
 *  -> { status, data:[...per kunjungan...], rentang:{dari, sampai} }
 *
 * Satu kunjungan bisa punya banyak tiket (non racikan / racikan / BMHP) dengan status berbeda.
 * Versi lama (controller/farmasi/farmasiOrder, masih dipakai halaman admin) memakai GROUP BY id_visit
 * dengan permintaan_pharmacy.* -> status yang tampil diambil dari tiket acak, sehingga kunjungan yang
 * masih punya tiket menunggu bisa masuk tab "Selesai" (dan sebaliknya). Di sini status dihitung dari
 * semua tiket: selesai = semua tiket selesai.
 *
 * Status tiket: 0 = draf dokter (belum dikirim), 1 = menunggu, 2 = diproses/persiapan, 3 = selesai.
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

$sql = "SELECT t.*, pv.visit_date, pv.visit_time, pv.source_hub, pv.id_provider, pv.id_doctor, pv.id_poli,
      COALESCE(mp.patient_name, pv.patient_name_pcare) AS patient_name, mp.nomor_rm, mp.patient_gender, mp.patient_datebirth,
      prv.provider_name
   FROM (
      SELECT pp.id_visit AS visit_ID,
         MIN(pp.id_permintaan_farmasi) AS id_permintaan_farmasi,
         MIN(pp.created_at) AS created_at,
         MAX(pp.created_at) AS terakhir,
         COUNT(*) AS jumlah,
         SUM(pp.status_permintaan = 0) AS s0,
         SUM(pp.status_permintaan = 1) AS s1,
         SUM(pp.status_permintaan = 2) AS s2,
         SUM(pp.status_permintaan = 3) AS s3,
         SUM(pp.tipe_obat = 'Racikan') AS racikan,
         SUM(pp.tipe_obat = 'BMHP') AS bmhp,
         MAX(pp.status_obat_pulang) AS obat_pulang,
         SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT pp.permintaan_number ORDER BY pp.id_permintaan_farmasi SEPARATOR ', '), ', ', 3) AS nomor
      FROM permintaan_pharmacy pp
      WHERE pp.id_customer = ? AND pp.created_at >= ? AND pp.created_at < ?
      GROUP BY pp.id_visit
   ) t
   JOIN pasien_visit pv ON pv.visit_ID = t.visit_ID AND pv.id_customer = ?
   LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
   LEFT JOIN ms_provider prv ON prv.id_provider = pv.id_provider
   ORDER BY t.created_at ASC";

try {
   $st = $koneksi->prepare($sql);
   $st->bind_param('ssss', $idc, $dari, $akhir, $idc);
   $st->execute();
   $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
} catch (Throwable $e) {
   error_log('[farmasi/permintaanResepList] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memuat permintaan resep.']);
}

$acuan = new DateTime($hariIni);
foreach ($rows as &$r) {
   foreach (['jumlah', 's0', 's1', 's2', 's3', 'racikan', 'bmhp', 'obat_pulang'] as $f) $r[$f] = (int) $r[$f];
   // status kunjungan dari semua tiket
   $r['status'] = $r['s3'] === $r['jumlah'] ? 'selesai' : ($r['s1'] > 0 ? 'menunggu' : ($r['s2'] > 0 ? 'diproses' : 'draf'));
   $r['umur'] = null;
   if (!empty($r['patient_datebirth']) && ($lahir = DateTime::createFromFormat('!Y-m-d', substr((string) $r['patient_datebirth'], 0, 10)))) {
      $r['umur'] = $lahir->diff($acuan)->y;
   }
   unset($r['patient_datebirth']);
}
unset($r);

balas(200, ['status' => 'success', 'data' => $rows, 'rentang' => ['dari' => $dari, 'sampai' => $sampai]]);
