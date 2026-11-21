<?php
/**
 * Daftar pasien poliklinik untuk halaman module/doctor/pemeriksaan.
 *
 * GET ?fromDate=Y-m-d&toDate=Y-m-d&tab=belum|sudah&provider=<id>
 *  -> { status, data:[...], hitung:{menunggu, diperiksa, selesai, konfirmasi}, rentang:{dari, sampai} }
 *
 * Perbaikan dibanding versi lama:
 *  - hanya kolom yang dipakai halaman (dulu pasien_visit.* + ms_patient.* -> ~3,4 KB per baris, 24 MB untuk sebulan);
 *  - join antrian_poli (tanpa index) diganti semi-join yang dibatasi kunjungan di rentang tanggal -> ~4 dtk jadi < 0,5 dtk;
 *  - status pcare lewat EXISTS (pcare_pendaftaran bisa punya 2 baris per kunjungan -> pasien tampil ganda);
 *  - filter tab dilakukan di server; rentang tanggal wajib & maksimal 31 hari (dulu Reset mengosongkan "Sampai"
 *    sehingga seluruh riwayat faskes, puluhan ribu kunjungan, ikut dimuat);
 *  - filter dokter memakai session, bukan nama/kode yang dikirim browser;
 *  - getID / PUT / DELETE dihapus: tidak dipakai dan tidak dibatasi faskes (siapa pun bisa menghapus kunjungan).
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
$role = (string) ($_SESSION['roles'] ?? '');
$namaDokter = trim((string) ($_SESSION['fullname'] ?? ''));
$kodeDokter = trim((string) ($_SESSION['kode_dokter'] ?? ''));
session_write_close();
if ($idc === '') balas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

$tz = new DateTimeZone('Asia/Jakarta');
$hariIni = (new DateTime('now', $tz))->format('Y-m-d');
$tgl = function ($v) use ($hariIni) {
   $v = (string) $v;
   $d = DateTime::createFromFormat('!Y-m-d', $v);
   return ($d && $d->format('Y-m-d') === $v) ? $v : null;
};
$dari = $tgl($_GET['fromDate'] ?? '') ?? $hariIni;
$sampai = $tgl($_GET['toDate'] ?? '') ?? $dari;
if ($sampai < $dari) [$dari, $sampai] = [$sampai, $dari];
if ((new DateTime($dari))->diff(new DateTime($sampai))->days > 31) {
   balas(422, ['status' => 'error', 'message' => 'Rentang tanggal maksimal 31 hari.']);
}
$tab = ($_GET['tab'] ?? 'belum') === 'sudah' ? 'sudah' : 'belum';
$provider = trim((string) ($_GET['provider'] ?? ''));

// kondisi dasar (dipakai daftar & hitungan)
$where = "pv.id_customer = ? AND pv.visit_date BETWEEN ? AND ? AND pv.source_hub <> 'Rawat Inap' AND NOT (pv.created_user <=> 'JKNSehat')";
$tipe = 'sss';
$par = [$idc, $dari, $sampai];
if ($provider !== '') {
   $where .= ' AND pv.id_provider = ?';
   $tipe .= 's';
   $par[] = $provider;
}
// selain admin hanya melihat pasiennya sendiri (nama dokter atau kode dokter BPJS)
if (!in_array($role, ['admin', 'superadmin'], true) && ($namaDokter !== '' || $kodeDokter !== '')) {
   $f = [];
   if ($namaDokter !== '') {
      $f[] = "REPLACE(LOWER(pv.id_doctor), 'dr. ', '') LIKE ?";
      $tipe .= 's';
      $par[] = '%' . mb_strtolower(preg_replace('/^dr\.?\s*/i', '', $namaDokter)) . '%';
   }
   if ($kodeDokter !== '') {
      $f[] = 'pv.code_doctor = ?';
      $tipe .= 's';
      $par[] = $kodeDokter;
   }
   $where .= ' AND (' . implode(' OR ', $f) . ')';
}

try {
   // hitungan per status (untuk KPI & badge tab)
   $st = $koneksi->prepare("SELECT SUM(pv.visit_status = 0) menunggu, SUM(pv.visit_status = 1) diperiksa, SUM(pv.visit_status = 4) selesai
      FROM pasien_visit pv WHERE $where");
   $st->bind_param($tipe, ...$par);
   $st->execute();
   $h = $st->get_result()->fetch_assoc();
   $st->close();

   $statusTab = $tab === 'belum' ? 'pv.visit_status IN (0, 1)' : 'pv.visit_status = 4';
   $sql = "SELECT pv.id_visit, pv.visit_ID, pv.visit_date, pv.visit_time, pv.visit_antrian, pv.visit_status, pv.source_hub,
         pv.id_poli, pv.id_doctor, pv.id_provider, pv.noKartu,
         COALESCE(NULLIF(TRIM(pv.patient_name_pcare), ''), mp.patient_name) AS nama,
         mp.nomor_rm, mp.patient_gender, mp.patient_datebirth, prv.provider_name,
         ap.status_panggil,
         CASE WHEN pv.id_provider = '1' THEN EXISTS (SELECT 1 FROM pcare_pendaftaran pp WHERE pp.nomor_visit = pv.visit_ID) END AS status_pcare
      FROM pasien_visit pv
      LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
      LEFT JOIN ms_provider prv ON prv.id_provider = pv.id_provider
      LEFT JOIN (
         SELECT a.nomor_visit, MIN(a.status) AS status_panggil
         FROM antrian_poli a
         WHERE a.id_customer = ? AND a.nomor_visit IN (SELECT v.visit_ID FROM pasien_visit v WHERE v.id_customer = ? AND v.visit_date BETWEEN ? AND ?)
         GROUP BY a.nomor_visit
      ) ap ON ap.nomor_visit = pv.visit_ID
      WHERE $where AND $statusTab
      ORDER BY LEFT(pv.visit_antrian, 1), CAST(REGEXP_SUBSTR(pv.visit_antrian, '[0-9]+$') AS UNSIGNED), pv.visit_time";
   $st = $koneksi->prepare($sql);
   $st->bind_param('ssss' . $tipe, $idc, $idc, $dari, $sampai, ...$par);
   $st->execute();
   $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
} catch (Throwable $e) {
   error_log('[doctor/registrasiController] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memuat daftar pasien.']);
}

$acuan = new DateTime($hariIni);
$konfirmasi = 0;
foreach ($rows as &$r) {
   // umur dihitung di server (tanggal lahir tidak dikirim)
   $r['umur'] = null;
   if (!empty($r['patient_datebirth']) && ($lahir = DateTime::createFromFormat('!Y-m-d', substr((string) $r['patient_datebirth'], 0, 10)))) {
      $r['umur'] = $lahir->diff($acuan)->y;
   }
   unset($r['patient_datebirth']);
   $r['status_panggil'] = $r['status_panggil'] === null ? null : (int) $r['status_panggil'];
   $r['status_pcare'] = $r['status_pcare'] === null ? null : (int) $r['status_pcare'];
   $r['visit_status'] = (int) $r['visit_status'];
   // BPJS yang belum dikonfirmasi hadir / belum terdaftar PCare: tombol Hadir / Tidak hadir (aturan lama)
   $r['perlu_hadir'] = (string) $r['id_provider'] === '1' && ($r['status_panggil'] === 0 || $r['status_pcare'] === 0);
   if ($r['perlu_hadir']) $konfirmasi++;
}
unset($r);

balas(200, [
   'status' => 'success',
   'data' => $rows,
   'hitung' => [
      'menunggu' => (int) ($h['menunggu'] ?? 0),
      'diperiksa' => (int) ($h['diperiksa'] ?? 0),
      'selesai' => (int) ($h['selesai'] ?? 0),
      'konfirmasi' => $tab === 'belum' ? $konfirmasi : null,
   ],
   'rentang' => ['dari' => $dari, 'sampai' => $sampai],
]);
