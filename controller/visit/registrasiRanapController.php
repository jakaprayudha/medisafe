<?php
/**
 * Data registrasi rawat inap.
 *
 * GET ?v=2&tab=dirawat|pulang&fromDate&toDate&doctor&provider   (module/admin/registrasi_ranap)
 *   -> {status, data:[ringkas], hitung:{dirawat, pulang, masuk_hari_ini, pulang_hari_ini}, bed:{total, terisi_menurut_bed}}
 *   Status rawat diambil dari permintaan_ranap.status (aktif / pulang) yang diubah halaman Status Pulang.
 *   "Sedang dirawat" TIDAK difilter tanggal (pasien yang masuk beberapa hari lalu tetap tampil).
 * GET tanpa v=2 -> format lama (dipakai module/admin/pemeriksaan_inap).
 * GET ?id= / PUT / DELETE -> hanya kunjungan faskes session.
 *
 * Perbaikan: dulu status dari status_perawatan_inap yang tidak pernah diisi (semua "Perawatan"), tab memakai
 * visit_status (semua kunjungan ranap = 4 sehingga tab "Belum dilayani" selalu kosong), DATE(visit_date) tanpa index,
 * SELECT pasien_visit.* + ms_patient.* + ms_room.* + ms_room_bed.* (ratusan kolom, saling menimpa), dan
 * getID / PUT / DELETE tanpa cek faskes (DELETE menghapus kunjungan mana pun).
 */
include '../../database/connect.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();
$IDC = (int) ($_SESSION['id_customer'] ?? 0);
$ROLE = (string) ($_SESSION['roles'] ?? '');
session_write_close();
if ($IDC <= 0) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Session tidak valid / expired']);
   exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
   case 'GET':
      if (isset($_GET['id'])) getID($_GET['id']);
      elseif (($_GET['v'] ?? '') === '2') getDataV2();
      else getData();
      break;
   case 'PUT':
      updateData();
      break;
   case 'DELETE':
      deleteData();
      break;
   default:
      http_response_code(405);
      echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan.']);
}

function rrTgl($v, string $d): string
{
   return (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : $d;
}

function rrQ(string $sql, string $t, array $p): array
{
   global $koneksi;
   $st = $koneksi->prepare($sql);
   if (!$st) throw new RuntimeException($koneksi->error);
   if ($t !== '') $st->bind_param($t, ...$p);
   $st->execute();
   $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   return $r;
}

function getDataV2()
{
   global $IDC;
   $hariIni = date('Y-m-d');
   $from = rrTgl($_GET['fromDate'] ?? null, date('Y-m-d', strtotime('-6 days')));
   $to = rrTgl($_GET['toDate'] ?? null, $hariIni);
   if ($from > $to) [$from, $to] = [$to, $from];
   $tab = ($_GET['tab'] ?? '') === 'pulang' ? 'pulang' : 'dirawat';

   $w = "pv.id_customer = ? AND pv.status_rawatinap = 1";
   $t = 'i';
   $p = [$IDC];
   foreach (['doctor' => 'pv.id_doctor', 'provider' => 'pv.id_provider'] as $k => $kol) {
      $v = trim((string) ($_GET[$k] ?? ''));
      if ($v !== '') { $w .= " AND $kol = ?"; $t .= 's'; $p[] = $v; }
   }
   try {
      // jumlah: dirawat (tanpa filter tanggal), pulang (rentang tanggal masuk), masuk & pulang hari ini
      $h = rrQ("SELECT
            SUM(COALESCE(pr.status, '') <> 'pulang') AS dirawat,
            SUM(pr.status = 'pulang' AND pv.visit_date BETWEEN ? AND ?) AS pulang,
            SUM(pv.visit_date = ?) AS masuk_hari_ini
         FROM pasien_visit pv LEFT JOIN permintaan_ranap pr ON pr.visit_ID_inpatient = pv.visit_ID
         WHERE $w AND (COALESCE(pr.status, '') <> 'pulang' OR pv.visit_date >= ?)", 'sss' . $t . 's',
         array_merge([$from, $to, $hariIni], $p, [min($from, $hariIni)]))[0] ?? [];
      $bed = rrQ("SELECT COUNT(*) total, SUM(b.bed_status = 0) terisi FROM ms_room_bed b JOIN ms_room r ON r.id_room = b.id_room WHERE r.id_customer = ?", 'i', [$IDC])[0] ?? [];

      $cond = $tab === 'dirawat' ? " AND COALESCE(pr.status, '') <> 'pulang'" : " AND pr.status = 'pulang' AND pv.visit_date BETWEEN ? AND ?";
      $tt = $t . ($tab === 'pulang' ? 'ss' : '');
      $pp = $tab === 'pulang' ? array_merge($p, [$from, $to]) : $p;
      $rows = rrQ("SELECT pv.id_visit, pv.visit_ID, pv.id_patient, pv.visit_date, pv.visit_time, pv.visit_status, pv.id_doctor, pv.id_poli,
            pv.patient_name_pcare, pv.noKartu, pv.cara_keluar, pv.kondisi_pulang,
            mp.patient_name, mp.nomor_rm, mp.patient_gender, mp.patient_datebirth, mp.patient_bpjs,
            pr.id_ranap, pr.status AS status_ranap, pr.diagnosa_awal, pr.ranap_date,
            r.room_name, r.service_class, b.bed_name, prov.provider_name
         FROM pasien_visit pv
         LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
         LEFT JOIN permintaan_ranap pr ON pr.visit_ID_inpatient = pv.visit_ID
         LEFT JOIN ms_room r ON r.id_room = pr.id_room
         LEFT JOIN ms_room_bed b ON b.id_bed = pr.id_bed
         LEFT JOIN ms_provider prov ON prov.id_provider = pv.id_provider
         WHERE $w $cond
         ORDER BY pv.visit_date DESC, pv.visit_time DESC
         LIMIT 2000", $tt, $pp);
      foreach ($rows as &$r) {
         $r['id_visit'] = (int) $r['id_visit'];
         $r['lama_hari'] = $r['visit_date'] ? (int) (new DateTime(substr($r['visit_date'], 0, 10)))->diff(new DateTime($hariIni))->days + 1 : null;
      }
      unset($r);
      if (!ini_get('zlib.output_compression') && extension_loaded('zlib')) ob_start('ob_gzhandler');
      echo json_encode(['status' => 'success', 'tab' => $tab, 'data' => $rows, 'hari_ini' => $hariIni,
         'hitung' => array_map('intval', $h), 'bed' => array_map('intval', $bed)], JSON_UNESCAPED_UNICODE);
   } catch (Throwable $e) {
      error_log('[registrasiRanapController] ' . $e->getMessage());
      http_response_code(500);
      echo json_encode(['status' => 'error', 'message' => 'Gagal memuat data rawat inap.']);
   }
}

/** Format lama (module/admin/pemeriksaan_inap). */
function getData()
{
   global $koneksi, $IDC;
   $fromDate = $_GET['fromDate'] ?? null;
   $toDate = $_GET['toDate'] ?? null;
   $query = "SELECT pasien_visit.*, ms_patient.*, ms_room.*, ms_room_bed.*, ms_provider.provider_name
      FROM pasien_visit
      LEFT JOIN ms_patient ON ms_patient.id_patient = pasien_visit.id_patient
      LEFT JOIN permintaan_ranap ON permintaan_ranap.visit_ID_inpatient = pasien_visit.visit_ID
      LEFT JOIN ms_room ON ms_room.id_room = permintaan_ranap.id_room
      LEFT JOIN ms_room_bed ON ms_room_bed.id_bed = permintaan_ranap.id_bed
      LEFT JOIN ms_provider ON ms_provider.id_provider = pasien_visit.id_provider
      WHERE pasien_visit.status_rawatinap = 1 AND pasien_visit.id_customer = ?";
   $t = 'i';
   $p = [$IDC];
   if ($fromDate && $toDate) {
      $query .= " AND pasien_visit.visit_date BETWEEN ? AND ?";
      $t .= 'ss';
      array_push($p, rrTgl($fromDate, date('Y-m-d')), rrTgl($toDate, date('Y-m-d')));
   }
   $tab = $_GET['tab'] ?? null;
   if ($tab === 'belum') $query .= " AND pasien_visit.visit_status NOT IN (3,4)";
   if ($tab === 'selesai') $query .= " AND pasien_visit.visit_status IN (3,4)";
   $query .= " ORDER BY pasien_visit.visit_date ASC";
   try {
      echo json_encode(['status' => 'success', 'data' => rrQ($query, $t, $p)]);
   } catch (Throwable $e) {
      http_response_code(500);
      echo json_encode(['status' => 'error', 'message' => 'Gagal memuat data.']);
   }
}

function getID($id)
{
   global $IDC;
   $r = rrQ("SELECT * FROM pasien_visit WHERE id_visit = ? AND id_customer = ? LIMIT 1", 'ii', [(int) $id, $IDC])[0] ?? null;
   echo json_encode($r ? ['status' => 'success', 'data' => $r] : ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
}

function updateData()
{
   global $koneksi, $IDC;
   parse_str(file_get_contents('php://input'), $put);
   $id = (int) ($put['id_visit'] ?? 0);
   if ($id <= 0) { echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']); return; }
   $fields = [];
   $values = [];
   foreach (['id_doctor', 'id_poli', 'visit_notes'] as $f) {
      if (isset($put[$f])) { $fields[] = "$f = ?"; $values[] = (string) $put[$f]; }
   }
   if (!$fields) { echo json_encode(['status' => 'error', 'message' => 'Tidak ada data diupdate.']); return; }
   $values[] = $id;
   $values[] = $IDC;
   $st = $koneksi->prepare("UPDATE pasien_visit SET " . implode(', ', $fields) . " WHERE id_visit = ? AND id_customer = ?");
   $st->bind_param(str_repeat('s', count($fields)) . 'ii', ...$values);
   $ok = $st->execute();
   $st->close();
   echo json_encode($ok ? ['status' => 'success', 'message' => 'Data berhasil diperbarui.'] : ['status' => 'error', 'message' => 'Update gagal.']);
}

function deleteData()
{
   global $koneksi, $IDC, $ROLE;
   $id = (int) ($_GET['id'] ?? 0);
   if ($id <= 0) { echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']); return; }
   if (!in_array($ROLE, ['admin', 'superadmin', 'administrator'], true)) {
      http_response_code(403);
      echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
      return;
   }
   // kunjungan rawat inap berisi rekam medis -> hanya yang belum dilayani yang boleh dihapus (kecuali admin)
   require_once __DIR__ . '/../dinkes/dinkesService.php';
   require_once __DIR__ . '/../../utility/visitGuard.php';
   $kunciSelesai = bolehBukaKunciSelesai() ? '' : ' AND visit_status NOT IN (1,2,3,4)';
   $dkVid = dkVisitIdDikirim($koneksi, (int) $IDC, $id); // Dinkes
   require_once __DIR__ . '/../../utility/visitLog.php';
   $snapHapus = logKunjunganSnapshot($koneksi, 'id_visit', $id); // data kunjungan sebelum dihapus
   $st = $koneksi->prepare("DELETE FROM pasien_visit WHERE id_visit = ? AND id_customer = ?" . $kunciSelesai);
   $st->bind_param('ii', $id, $IDC);
   $st->execute();
   $n = $st->affected_rows;
   $st->close();
   if ($n > 0 && $dkVid) dkHapusVisit($koneksi, (int) $IDC, $dkVid); // Dinkes
   if ($n > 0 && $snapHapus) logKunjungan($koneksi, 'hapus', $snapHapus, 'Kunjungan dihapus permanen dari menu Registrasi rawat inap', $snapHapus);
   echo json_encode($n > 0 ? ['status' => 'success', 'message' => 'Data berhasil dihapus.'] : ['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah dilayani (tidak dapat dihapus).']);
}
