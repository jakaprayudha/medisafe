<?php
/**
 * Status ID Satu Sehat (IHS) pasien (module/admin/patient-idsh).
 *   GET action=ringkasan                         -> jumlah per status + kunjungan 30 hari pasien tanpa IHS
 *   GET action=list&start=&length=&q=&status=&draw= -> daftar per halaman (DataTables server-side), NIK disamarkan
 *
 * Status: lengkap (punya IHS) · antre (NIK valid, belum dicek cron fill-id-pasien-satusehat)
 *         tidak_ditemukan (NIK valid, sudah dicek tapi tidak terdaftar) · nik (NIK kosong / bukan 16 digit)
 *
 * Dulu: faskes dari ?id_customer= tanpa cek role & semua pasien (puluhan ribu, NIK lengkap) dikirim sekaligus.
 * Sekarang faskes dari SESSION (superadmin boleh memilih), hanya admin, dan data diambil per halaman.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$roles = $_SESSION['roles'] ?? '';
requireRole(['admin', 'superadmin', 'administrator']);
$idc = in_array($roles, ['superadmin', 'administrator'], true) && !empty($_GET['id_customer'])
   ? (int) $_GET['id_customer']
   : (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();

function jawab(array $d, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}
if ($idc <= 0) jawab(['status' => 'error', 'message' => 'Faskes tidak ditemukan.'], 422);

const PI_STATUS_SQL = "CASE
   WHEN p.idsh IS NOT NULL AND p.idsh <> '' THEN 'lengkap'
   WHEN p.patient_nik NOT REGEXP '^[0-9]{16}$' OR p.patient_nik IS NULL THEN 'nik'
   WHEN p.is_checked_satusehat = '1' THEN 'tidak_ditemukan'
   ELSE 'antre' END";

function q(mysqli $k, string $sql, string $t = '', array $a = []): array
{
   $st = $k->prepare($sql);
   if (!$st) {
      error_log('[patientIDSH] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal memuat data pasien.'], 500);
   }
   if ($t !== '') $st->bind_param($t, ...$a);
   $st->execute();
   $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   return $r;
}

$action = $_GET['action'] ?? 'list';

if ($action === 'ringkasan') {
   $st = array_fill_keys(['lengkap', 'antre', 'tidak_ditemukan', 'nik'], 0);
   foreach (q($koneksi, "SELECT " . PI_STATUS_SQL . " s, COUNT(*) n FROM ms_patient p WHERE p.id_customer = ? GROUP BY s", 'i', [$idc]) as $r) {
      $st[$r['s']] = (int) $r['n'];
   }
   // kunjungan 30 hari terakhir yang pasiennya belum punya IHS (tidak bisa dikirim ke Satu Sehat)
   $awal = date('Y-m-d', strtotime('-29 days'));
   $kv = q($koneksi, "SELECT COUNT(*) total, SUM(p.idsh IS NULL OR p.idsh = '') tanpa, COUNT(DISTINCT IF(p.idsh IS NULL OR p.idsh = '', p.id_patient, NULL)) pasien
                      FROM pasien_visit v JOIN ms_patient p ON p.id_patient = v.id_patient
                      WHERE v.visit_date >= ? AND v.id_customer = ? AND (v.visit_status IS NULL OR v.visit_status <> 99)", 'si', [$awal, $idc])[0] ?? [];
   $nm = (string) (q($koneksi, "SELECT clinic_name FROM setting_clinic WHERE id_customer = ? LIMIT 1", 'i', [$idc])[0]['clinic_name'] ?? '');
   jawab(['status' => 'success', 'faskes' => $nm, 'status_pasien' => $st, 'total' => array_sum($st),
      'kunjungan_30' => ['total' => (int) ($kv['total'] ?? 0), 'tanpa_ihs' => (int) ($kv['tanpa'] ?? 0), 'pasien_tanpa_ihs' => (int) ($kv['pasien'] ?? 0)]]);
}

if ($action === 'list') {
   $start = max(0, (int) ($_GET['start'] ?? 0));
   $length = min(100, max(1, (int) ($_GET['length'] ?? 25)));
   $where = 'p.id_customer = ?';
   $t = 'i';
   $a = [$idc];
   $status = (string) ($_GET['status'] ?? '');
   if (in_array($status, ['lengkap', 'antre', 'tidak_ditemukan', 'nik'], true)) {
      $where .= ' AND (' . PI_STATUS_SQL . ') = ?';
      $t .= 's';
      $a[] = $status;
   }
   $cari = trim((string) ($_GET['q'] ?? ''));
   if ($cari !== '') {
      $cari = mb_substr($cari, 0, 60);
      if (preg_match('/^\d{6,16}$/', $cari)) {
         // angka: NIK / No. BPJS / RM diawali angka ini (memakai index NIK & BPJS)
         $where .= ' AND (p.patient_nik LIKE ? OR p.patient_bpjs LIKE ? OR p.nomor_rm LIKE ?)';
         $t .= 'sss';
         array_push($a, $cari . '%', $cari . '%', '%' . $cari . '%');
      } else {
         $where .= ' AND (p.patient_name LIKE ? OR p.nomor_rm LIKE ?)';
         $t .= 'ss';
         array_push($a, '%' . $cari . '%', '%' . $cari . '%');
      }
   }
   $urut = ($_GET['urut'] ?? '') === 'nama' ? 'p.patient_name ASC' : 'p.id_patient DESC';
   $total = (int) (q($koneksi, "SELECT COUNT(*) n FROM ms_patient p WHERE $where", $t, $a)[0]['n'] ?? 0);
   $rows = q($koneksi, "SELECT p.id_patient, p.patient_name, p.nomor_rm, p.patient_nik, p.patient_bpjs, p.patient_gender, p.patient_datebirth,
                               p.idsh, p.registration_date, " . PI_STATUS_SQL . " status
                        FROM ms_patient p WHERE $where ORDER BY $urut LIMIT ? OFFSET ?", $t . 'ii', [...$a, $length, $start]);
   $masker = fn($v) => strlen((string) $v) >= 8 ? substr($v, 0, 4) . str_repeat('•', max(0, strlen($v) - 8)) . substr($v, -4) : (string) $v;
   $data = array_map(fn($r) => [
      'id_patient' => (int) $r['id_patient'],
      'nama' => (string) $r['patient_name'],
      'rm' => (string) $r['nomor_rm'],
      'nik' => $masker(trim((string) $r['patient_nik'])),
      'nik_ada' => trim((string) $r['patient_nik']) !== '',
      'bpjs' => (string) $r['patient_bpjs'],
      'gender' => strtoupper(substr(trim((string) $r['patient_gender']), 0, 1)),
      'lahir' => (string) $r['patient_datebirth'],
      'idsh' => trim((string) $r['idsh']),
      'status' => $r['status'],
   ], $rows);
   jawab(['status' => 'success', 'draw' => (int) ($_GET['draw'] ?? 0), 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data]);
}

jawab(['status' => 'error', 'message' => 'Action tidak dikenal.'], 400);
