<?php
/**
 * Daftar permintaan laboratorium per kunjungan untuk module/lab/permintaan-lab.
 *
 * GET ?fromDate=Y-m-d&toDate=Y-m-d (tanggal permintaan dibuat, maks 62 hari)
 *  -> { status, data:[...per kunjungan...], rentang:{dari, sampai} }
 *
 * Status dihitung dari hasil yang sudah disimpan (laboratorium_result):
 *   per pemeriksaan: lengkap = ada hasil, belum = belum ada hasil (isi/harus = parameter terisi/jumlah parameter, info saja);
 *   per kunjungan : selesai = semua pemeriksaan ada hasil, proses = sebagian pemeriksaan, belum = belum ada sama sekali.
 * Versi lama (controller/lab/pemeriksaanOrder) membaca kolom status_permintaan yang tidak ada di
 * visit_inspection -> semua permintaan tampil "Belum Pemeriksaan" dan tab Selesai selalu kosong;
 * juga memuat seluruh riwayat faskes tanpa batas tanggal.
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

$sql = "SELECT vi.id_inspection, vi.id_visit, vi.inspection_name, vi.inspection_date, vi.inspection_source, vi.inspection_note, vi.created_at,
      COALESCE(e.n, 0) AS harus, COALESCE(r.isi, 0) AS isi,
      pv.visit_date, pv.visit_time, pv.visit_status, pv.source_hub, pv.id_doctor, pv.id_poli, pv.id_provider,
      COALESCE(NULLIF(TRIM(pv.patient_name_pcare), ''), mp.patient_name) AS patient_name, mp.nomor_rm, mp.patient_gender, mp.patient_datebirth,
      prv.provider_name
   FROM visit_inspection vi
   JOIN pasien_visit pv ON pv.visit_ID = vi.id_visit AND pv.id_customer = ?
   LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
   LEFT JOIN ms_provider prv ON prv.id_provider = pv.id_provider
   LEFT JOIN (
      SELECT ld.assemen, COUNT(DISTINCT li.id) n
      FROM laboratorium_detail ld JOIN laboratorium_item li ON li.kode = ld.kode
      GROUP BY ld.assemen
   ) e ON e.assemen = vi.inspection_name
   LEFT JOIN (
      SELECT lr.id_inspection, COUNT(DISTINCT CASE WHEN TRIM(COALESCE(lr.hasil, '')) <> '' THEN lr.id_item END) isi
      FROM laboratorium_result lr
      WHERE lr.id_inspection IN (SELECT x.id_inspection FROM visit_inspection x WHERE x.id_customer = ? AND x.created_at >= ? AND x.created_at < ?)
      GROUP BY lr.id_inspection
   ) r ON r.id_inspection = vi.id_inspection
   WHERE vi.id_customer = ? AND vi.created_at >= ? AND vi.created_at < ?
   ORDER BY vi.created_at ASC, vi.id_inspection ASC";

try {
   $st = $koneksi->prepare($sql);
   $st->bind_param('sssssss', $idc, $idc, $dari, $akhir, $idc, $dari, $akhir);
   $st->execute();
   $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
} catch (Throwable $e) {
   error_log('[lab/permintaanLabList] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memuat permintaan lab.']);
}

// gabung per kunjungan
$acuan = new DateTime($hariIni);
$per = [];
foreach ($rows as $r) {
   $v = $r['id_visit'];
   if (!isset($per[$v])) {
      $umur = null;
      if (!empty($r['patient_datebirth']) && ($lahir = DateTime::createFromFormat('!Y-m-d', substr((string) $r['patient_datebirth'], 0, 10)))) $umur = $lahir->diff($acuan)->y;
      $per[$v] = [
         'visit_ID' => $v,
         'id_inspection' => (int) $r['id_inspection'],
         'created_at' => $r['created_at'],
         'visit_date' => $r['visit_date'],
         'visit_time' => $r['visit_time'],
         'source_hub' => $r['source_hub'],
         'id_doctor' => $r['id_doctor'],
         'id_poli' => $r['id_poli'],
         'id_provider' => $r['id_provider'],
         'provider_name' => $r['provider_name'],
         'patient_name' => $r['patient_name'],
         'nomor_rm' => $r['nomor_rm'],
         'patient_gender' => $r['patient_gender'],
         'umur' => $umur,
         'pemeriksaan' => [],
         'jumlah' => 0, 'lengkap' => 0, 'belum' => 0,
      ];
   }
   $harus = (int) $r['harus'];
   $isi = (int) $r['isi'];
   // ada hasil tersimpan = pemeriksaan selesai. Tidak dibanding jumlah parameter: master item berisi baris judul
   // (mis. "Kimia Urine", "Makroskopis urine") yang memang tidak diisi -> Urine Rutin selalu 12/15.
   $st = $isi === 0 ? 'belum' : 'lengkap';
   $per[$v]['pemeriksaan'][] = ['nama' => $r['inspection_name'], 'isi' => $isi, 'harus' => $harus, 'status' => $st, 'catatan' => $r['inspection_note']];
   $per[$v]['jumlah']++;
   $per[$v][$st]++;
}
$data = [];
foreach ($per as $p) {
   $p['status'] = $p['lengkap'] === $p['jumlah'] ? 'selesai' : ($p['lengkap'] > 0 ? 'proses' : 'belum');
   $data[] = $p;
}

balas(200, ['status' => 'success', 'data' => $data, 'rentang' => ['dari' => $dari, 'sampai' => $sampai]]);
