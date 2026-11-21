<?php
/**
 * Daftar pasien rawat inap (module/doctor/pemeriksaan-rawat-inap & module/nurse/pemeriksaan-rawat-inap).
 *
 * GET ?tab=belum|selesai|pulang&fromDate=Y-m-d&toDate=Y-m-d   (tanggal = tanggal masuk, opsional)
 *  -> { status, data:[...], hitung:{belum, selesai, pulang}, rentang:{dari, sampai}|null }
 *
 * Tab (aturan lama, tidak berubah):
 *  belum   = belum ada CPPT & belum ada resume pulang
 *  selesai = sudah ada CPPT & belum ada resume pulang
 *  pulang  = resume medis dengan tanggal pulang terisi
 *
 * Perbaikan dibanding versi lama:
 *  - visit_cppt / resume_medis / permintaan_ranap tidak ber-index; dulu dicek per baris (subquery berulang),
 *    sekarang masing-masing dibaca sekali untuk kunjungan rawat inap faskes ini -> tab pulang 9 dtk jadi < 1 dtk;
 *  - hanya kolom yang dipakai (dulu pasien_visit.* + ms_patient.* + ms_poli.* -> 4,6 MB untuk tab pulang);
 *  - join ms_poli dihapus: pasien_visit.id_poli berisi nama poli, bukan id -> join tidak pernah cocok,
 *    malah menimpa kolom id_poli/id_customer/created_at dengan NULL;
 *  - tab pulang tanpa tanggal dibatasi 31 hari terakhir (dulu seluruh riwayat);
 *  - getID / PUT / DELETE dihapus: tidak dipakai & tidak dibatasi faskes (siapa pun bisa menghapus kunjungan).
 * Field lama yang dipakai halaman perawat tetap dikirim.
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

$tz = new DateTimeZone('Asia/Jakarta');
$hariIni = (new DateTime('now', $tz))->format('Y-m-d');
$tgl = function ($v) {
   $v = (string) $v;
   $d = DateTime::createFromFormat('!Y-m-d', $v);
   return ($d && $d->format('Y-m-d') === $v) ? $v : null;
};
$tab = in_array($_GET['tab'] ?? '', ['belum', 'selesai', 'pulang'], true) ? $_GET['tab'] : 'belum';
$dari = $tgl($_GET['fromDate'] ?? '');
$sampai = $tgl($_GET['toDate'] ?? '');
if ($dari && $sampai && $sampai < $dari) [$dari, $sampai] = [$sampai, $dari];
// tab pulang tanpa tanggal: 31 hari terakhir (pasien yang masih dirawat tetap tampil semua di tab lain)
$rentangPulang = ($dari || $sampai) ? null : [(new DateTime($hariIni))->modify('-31 days')->format('Y-m-d'), $hariIni];

$kunjungan = "SELECT v.visit_ID FROM pasien_visit v WHERE v.id_customer = ? AND v.status_rawatinap = 1";
$sql = "SELECT pv.id_visit, pv.visit_ID, pv.id_patient, pv.visit_date, pv.id_doctor, pv.id_provider, pv.noKartu,
      mp.patient_name, mp.nomor_rm, mp.patient_gender, mp.patient_datebirth, mp.patient_bpjs,
      prv.provider_name,
      pr.ranap_time, pr.status AS status_ranap, pr.id_doctor AS dpjp,
      COALESCE(pr.ranap_date, pv.visit_date) AS tanggal_masuk,
      rb.bed_name, rr.room_name, rr.service_class,
      COALESCE(cp.n, 0) AS jumlah_cppt, cp.terakhir AS cppt_terakhir,
      rs.tanggal_pulang, rs.jam_pulang
   FROM pasien_visit pv
   JOIN ms_patient mp ON mp.id_patient = pv.id_patient
   LEFT JOIN ms_provider prv ON prv.id_provider = pv.id_provider
   LEFT JOIN (
      SELECT x.* FROM (
         SELECT p.visit_ID_inpatient, p.ranap_date, p.ranap_time, p.status, p.id_doctor, p.id_bed,
            ROW_NUMBER() OVER (PARTITION BY p.visit_ID_inpatient ORDER BY (p.status = 'aktif') DESC, p.id_ranap DESC) urut
         FROM permintaan_ranap p WHERE p.visit_ID_inpatient IN ($kunjungan)
      ) x WHERE x.urut = 1
   ) pr ON pr.visit_ID_inpatient = pv.visit_ID
   LEFT JOIN ms_room_bed rb ON rb.id_bed = pr.id_bed
   LEFT JOIN ms_room rr ON rr.id_room = rb.id_room AND rr.id_customer = pv.id_customer
   LEFT JOIN (
      SELECT c.visit_ID, COUNT(*) n, MAX(CONCAT(c.cppt_date, ' ', COALESCE(c.cppt_time, ''))) terakhir
      FROM visit_cppt c WHERE c.visit_ID IN ($kunjungan) GROUP BY c.visit_ID
   ) cp ON cp.visit_ID = pv.visit_ID
   LEFT JOIN (
      SELECT r.visit_ID, MAX(r.tanggal_pulang) tanggal_pulang, MAX(r.jam_pulang) jam_pulang
      FROM resume_medis r WHERE r.tanggal_pulang IS NOT NULL AND r.tanggal_pulang <> '' AND r.visit_ID IN ($kunjungan)
      GROUP BY r.visit_ID
   ) rs ON rs.visit_ID = pv.visit_ID
   WHERE pv.id_customer = ? AND pv.status_rawatinap = 1
   ORDER BY tanggal_masuk ASC, pr.ranap_time ASC";

try {
   $st = $koneksi->prepare($sql);
   $st->bind_param('ssss', $idc, $idc, $idc, $idc);
   $st->execute();
   $semua = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
} catch (Throwable $e) {
   error_log('[doctor/registrasiInpatientController] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memuat daftar pasien rawat inap.']);
}

$acuan = new DateTime($hariIni);
$hitung = ['belum' => 0, 'selesai' => 0, 'pulang' => 0, 'resume_belum' => 0, 'resume_selesai' => 0];
$data = [];
foreach ($semua as $r) {
   $masuk = substr((string) $r['tanggal_masuk'], 0, 10);
   if ($dari && $masuk < $dari) continue;
   if ($sampai && $masuk > $sampai) continue;
   $grup = $r['tanggal_pulang'] !== null ? 'pulang' : ((int) $r['jumlah_cppt'] > 0 ? 'selesai' : 'belum');
   if ($grup === 'pulang' && $rentangPulang && ($masuk < $rentangPulang[0] || $masuk > $rentangPulang[1])) continue;
   $hitung[$grup]++;
   // status rawat inap sudah pulang, tetapi resume medis (tanggal pulang) belum diisi
   if ($grup !== 'pulang' && $r['status_ranap'] === 'pulang') $hitung['resume_' . $grup]++;
   if ($grup !== $tab) continue;

   $r['status_cppt'] = (int) $r['jumlah_cppt'] > 0 ? 1 : 0; // field lama (halaman perawat)
   $r['jumlah_cppt'] = (int) $r['jumlah_cppt'];
   $r['umur'] = null;
   if (!empty($r['patient_datebirth']) && ($lahir = DateTime::createFromFormat('!Y-m-d', substr((string) $r['patient_datebirth'], 0, 10)))) {
      $r['umur'] = $lahir->diff($acuan)->y;
   }
   unset($r['patient_datebirth']);
   // lama rawat (hari ke-n), sampai tanggal pulang bila sudah pulang
   $r['hari_rawat'] = null;
   if ($dm = DateTime::createFromFormat('!Y-m-d', $masuk)) {
      $akhir = ($r['tanggal_pulang'] && ($dp = DateTime::createFromFormat('!Y-m-d', substr((string) $r['tanggal_pulang'], 0, 10)))) ? $dp : $acuan;
      $r['hari_rawat'] = $dm <= $akhir ? $dm->diff($akhir)->days + 1 : 1;
   }
   $data[] = $r;
}
if ($tab === 'pulang') {
   // terbaru pulang di atas
   usort($data, fn($a, $b) => strcmp((string) $b['tanggal_pulang'] . $b['jam_pulang'], (string) $a['tanggal_pulang'] . $a['jam_pulang']));
}

balas(200, [
   'status' => 'success',
   'data' => $data,
   'hitung' => $hitung,
   'rentang' => $rentangPulang && $tab === 'pulang' ? ['dari' => $rentangPulang[0], 'sampai' => $rentangPulang[1]] : null,
]);
