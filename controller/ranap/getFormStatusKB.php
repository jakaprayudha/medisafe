<?php
/**
 * Data form "Kartu Status Peserta KB" (module/admin/form_kb, cetak print/formulir_status_kb). Tabel visit_kb_status.
 *   GET ?no=<visit_ID> -> { status, pasien:{...}, statuskb:{tersimpan}|null, awal:{isian bawaan}, batal }
 * Perbaikan: no/rm disisipkan ke SQL (SQL injection); tanpa cek login & batas faskes; LEFT JOIN ms_doctor ON id_doctor
 * (berisi nama dokter) -> dokter kosong; difilter nomor RM dari URL; usia DATEDIFF/365.
 * Fungsi kbAmbil() juga dipakai simpan & cetak.
 */
if (!defined('KB_LIB')) {
   include '../../database/connect.php';
   require_once __DIR__ . '/../../utility/visitGuard.php';
   header('Content-Type: application/json; charset=utf-8');
   header('Cache-Control: no-store');
   $kunj = visitGuard($koneksi, 'visit_ID', (string) ($_GET['no'] ?? ''));
   session_write_close();
   try {
      echo json_encode(['status' => 'success'] + kbAmbil($koneksi, (int) $kunj['id_visit']), JSON_UNESCAPED_UNICODE);
   } catch (Throwable $e) {
      error_log('[ranap/getFormStatusKB] ' . $e->getMessage());
      http_response_code(500);
      echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
   }
   exit;
}

function kbAmbil(mysqli $db, int $idVisit): array
{
   $st = $db->prepare("SELECT v.visit_ID, v.visit_status, v.visit_date, v.id_doctor, v.id_patient, v.id_customer, v.berat_badan, v.sistole, v.diastole,
         p.nomor_rm, p.patient_name, p.patient_gender, p.patient_datebirth, p.patient_address, p.patient_education, p.patient_occupation, p.patient_worker
      FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient WHERE v.id_visit = ? LIMIT 1");
   $st->bind_param('i', $idVisit);
   $st->execute();
   $v = $st->get_result()->fetch_assoc() ?: [];
   $st->close();

   $tgl = fn($s) => preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $s, $m) ? $m[0] : '';
   $usia = '';
   if ($tgl($v['patient_datebirth'] ?? '')) {
      $d = (new DateTime($tgl($v['patient_datebirth'])))->diff(new DateTime($tgl($v['visit_date'] ?? '') ?: 'today'));
      $usia = "{$d->y} th {$d->m} bln";
   }
   $no = (string) ($v['visit_ID'] ?? '');
   $st = $db->prepare("SELECT * FROM visit_kb_status WHERE visit_ID = ? ORDER BY id_kb_status DESC LIMIT 1");
   $st->bind_param('s', $no);
   $st->execute();
   $simpan = $st->get_result()->fetch_assoc() ?: null;
   $st->close();

   // G/P/A dari catatan ANC terakhir pasien ini di faskes ini
   $gpa = ['gpa_g' => '', 'gpa_p' => '', 'gpa_a' => ''];
   $idc = (string) ($v['id_customer'] ?? '');
   $idcInt = (int) $idc;
   $idp = (string) ($v['id_patient'] ?? '');
   $st = $db->prepare("SELECT a.gravida, a.para, a.abortus FROM pasien_persalinan_anc a
      JOIN pasien_visit pv ON pv.visit_ID = a.id_visit COLLATE utf8mb4_general_ci AND pv.id_customer = ?
      WHERE pv.id_patient = ? AND a.id_customer = ? AND (a.gravida <> '' OR a.para <> '')
      ORDER BY a.tanggal_kontrol DESC, a.id_anc DESC LIMIT 1");
   $st->bind_param('iss', $idcInt, $idp, $idc);
   $st->execute();
   if ($a = $st->get_result()->fetch_assoc()) $gpa = ['gpa_g' => (string) $a['gravida'], 'gpa_p' => (string) $a['para'], 'gpa_a' => (string) $a['abortus']];
   $st->close();

   $sis = (int) ($v['sistole'] ?? 0);
   $dia = (int) ($v['diastole'] ?? 0);
   $bb = is_numeric($v['berat_badan'] ?? null) && (float) $v['berat_badan'] > 0 ? (string) (float) $v['berat_badan'] : '';
   return [
      'pasien' => [
         'nama_pasien' => (string) ($v['patient_name'] ?? ''), 'nomor_rm' => (string) ($v['nomor_rm'] ?? ''),
         'jk' => (string) ($v['patient_gender'] ?? ''), 'usia' => $usia, 'doctor_name' => (string) ($v['id_doctor'] ?? ''),
         'tanggal_lahir' => $tgl($v['patient_datebirth'] ?? ''), 'alamat' => (string) ($v['patient_address'] ?? ''),
         'pendidikan_istri' => (string) ($v['patient_education'] ?? ''),
         'pekerjaan_istri' => (string) (($v['patient_occupation'] ?? '') ?: ($v['patient_worker'] ?? '')),
      ],
      'statuskb' => $simpan,
      'awal' => $gpa + [
         'berat_badan' => $bb,
         'tekanan_darah' => $sis && $dia ? "$sis/$dia" : '',
         'tgl_dilayani' => $tgl($v['visit_date'] ?? ''),
         'penanggung_jawab' => (string) ($v['id_doctor'] ?? ''),   // id_doctor berisi nama dokter
      ],
      'batal' => (int) ($v['visit_status'] ?? 0) === 99,
   ];
}
