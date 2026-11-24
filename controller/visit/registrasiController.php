<?php
/**
 * Daftar kunjungan pendaftaran (registrasi poliklinik / UGD) + aksi lama (getID, PUT, DELETE) untuk registrasi_ugd.
 *
 * GET  ?v=2&tab=belum|selesai|batal|checkin|sehat|sehatbatal&fromDate&toDate&doctor&provider&poli&tipe_pasien
 *        -> {status, data:[...kolom ringkas], hitung:{tab: jumlah, ..., tanpa_vital}}   (module/admin/registrasi)
 * GET  tanpa v=2 -> format lama (pasien_visit.* + join) untuk pemeriksaan / report / registrasi_ugd.
 * GET  ?id=  PUT  DELETE ?id=  -> hanya kunjungan faskes di session.
 *
 * Perbaikan:
 * - DATE(visit_date) BETWEEN -> visit_date BETWEEN (index idx_date_cust terpakai; 2,3 dtk -> 0,1 dtk untuk ~7rb baris).
 * - v=2 hanya mengirim kolom yang ditampilkan (dulu pasien_visit.* termasuk anamnesa, catatan, error Satu Sehat, dst.).
 * - "created_user != 'JKNSehat'" menyingkirkan baris created_user NULL -> sekarang NULL-safe.
 * - Status 10 (booking Mobile JKN belum check-in) dulu tidak tampil di tab mana pun -> tab "Belum check-in".
 * - getID / PUT / DELETE dulu tanpa cek faskes: kunjungan faskes mana pun bisa dibaca / diubah / dihapus.
 */
include '../../database/connect.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();
$ID_CUSTOMER = (int) ($_SESSION['id_customer'] ?? 0);
$ROLE = (string) ($_SESSION['roles'] ?? '');
session_write_close();
if ($ID_CUSTOMER <= 0) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Session tidak valid / expired']);
   exit;
}

require_once __DIR__ . '/registrasiService.php';

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

function getDataV2()
{
   global $koneksi, $ID_CUSTOMER;
   try {
      $hasil = rgDaftar($koneksi, $ID_CUSTOMER, $_GET);
      if (!ini_get('zlib.output_compression') && extension_loaded('zlib')) ob_start('ob_gzhandler');
      echo json_encode($hasil, JSON_UNESCAPED_UNICODE);
   } catch (Throwable $e) {
      error_log('[registrasiController] ' . $e->getMessage());
      http_response_code(500);
      echo json_encode(['status' => 'error', 'message' => 'Gagal memuat data pendaftaran.']);
   }
}

/** Format lama (dipakai halaman lain). */
function getData()
{
   global $koneksi, $ID_CUSTOMER;
   [$w, $t, $p] = rgFilter($ID_CUSTOMER, $_GET);
   $tab = $_GET['tab'] ?? null;
   $where = implode(' AND ', $w);
   $tabCond = match ($tab) {
      'belum' => " AND pv.visit_status NOT IN (3,4,99,10)",
      'selesai' => " AND pv.visit_status IN (3,4)",
      'batal' => " AND pv.visit_status IN (99)",
      default => '',
   };
   if ($tab === 'sehat') $tabCond .= " AND pv.created_user = 'JKNSehat' AND pv.visit_status NOT IN (3,4,99,10)";
   elseif ($tab === 'sehatbatal') $tabCond .= " AND pv.created_user = 'JKNSehat' AND pv.visit_status IN (99)";
   else $tabCond .= " AND NOT (pv.created_user <=> 'JKNSehat')";

   $query = "SELECT pv.*, ms.patient_name, ms.nomor_rm, ms.patient_gender, ms.patient_datebirth, ms_poli.poli_name, pr.provider_name,
         ms.patient_bpjs, ms.face_image AS patient_face_image
      FROM pasien_visit pv
      LEFT JOIN ms_patient ms ON ms.id_patient = pv.id_patient
      LEFT JOIN ms_poli ON ms_poli.id_poli = pv.id_poli
      LEFT JOIN ms_provider pr ON pr.id_provider = pv.id_provider
      WHERE $where $tabCond
      ORDER BY pv.visit_date ASC, pv.id_poli ASC, pv.id_doctor ASC, LEFT(pv.visit_antrian, 1) ASC,
         CAST(REGEXP_SUBSTR(pv.visit_antrian, '[0-9]+$') AS UNSIGNED) ASC";
   $stmt = $koneksi->prepare($query);
   if (!$stmt) {
      http_response_code(500);
      echo json_encode(['status' => 'error', 'message' => 'Gagal memuat data.']);
      error_log('[registrasiController] ' . $koneksi->error);
      return;
   }
   $stmt->bind_param($t, ...$p);
   if (!$stmt->execute()) {
      http_response_code(500);
      echo json_encode(['status' => 'error', 'message' => 'Gagal memuat data.']);
      return;
   }
   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();
   echo json_encode(['status' => 'success', 'data' => $data]);
}

function getID($id)
{
   global $koneksi, $ID_CUSTOMER;
   $id = (int) $id;
   $stmt = $koneksi->prepare("SELECT * FROM pasien_visit WHERE id_visit = ? AND id_customer = ?");
   $stmt->bind_param('ii', $id, $ID_CUSTOMER);
   $stmt->execute();
   $data = $stmt->get_result()->fetch_assoc();
   $stmt->close();
   echo json_encode($data ? ['status' => 'success', 'data' => $data] : ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
}

function updateData()
{
   global $koneksi, $ID_CUSTOMER;
   parse_str(file_get_contents('php://input'), $put);
   $id = (int) ($put['id_visit'] ?? 0);
   if ($id <= 0) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }
   $fields = [];
   $values = [];
   foreach (['id_doctor', 'id_poli', 'visit_notes'] as $f) {
      if (isset($put[$f])) {
         $fields[] = "$f = ?";
         $values[] = (string) $put[$f];
      }
   }
   if (!$fields) {
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada data diupdate.']);
      return;
   }
   $values[] = $id;
   $values[] = $ID_CUSTOMER;
   $stmt = $koneksi->prepare("UPDATE pasien_visit SET " . implode(', ', $fields) . " WHERE id_visit = ? AND id_customer = ?");
   $stmt->bind_param(str_repeat('s', count($fields)) . 'ii', ...$values);
   $ok = $stmt->execute();
   $n = $stmt->affected_rows;
   $stmt->close();
   echo json_encode($ok && $n >= 0 ? ['status' => 'success', 'message' => 'Data berhasil diperbarui.'] : ['status' => 'error', 'message' => 'Update gagal.']);
}

function deleteData()
{
   global $koneksi, $ID_CUSTOMER, $ROLE;
   $id = (int) ($_GET['id'] ?? 0);
   if ($id <= 0) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }
   if (!in_array($ROLE, ['admin', 'receptionis', 'superadmin', 'administrator'], true)) {
      http_response_code(403);
      echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
      return;
   }
   // kunjungan yang sudah selesai dilayani berisi rekam medis -> tidak boleh dihapus
   require_once __DIR__ . '/../dinkes/dinkesService.php';
   $dkVid = dkVisitIdDikirim($koneksi, (int) $ID_CUSTOMER, $id); // Dinkes
   $stmt = $koneksi->prepare("DELETE FROM pasien_visit WHERE id_visit = ? AND id_customer = ? AND visit_status NOT IN (3,4)");
   $stmt->bind_param('ii', $id, $ID_CUSTOMER);
   $stmt->execute();
   $n = $stmt->affected_rows;
   $stmt->close();
   if ($n > 0 && $dkVid) dkHapusVisit($koneksi, (int) $ID_CUSTOMER, $dkVid); // Dinkes
   if ($n > 0) error_log("[registrasiController] kunjungan #$id dihapus oleh " . ($_SESSION['username'] ?? $ROLE));
   echo json_encode($n > 0 ? ['status' => 'success', 'message' => 'Data berhasil dihapus.']
      : ['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah dilayani (tidak dapat dihapus).']);
}
