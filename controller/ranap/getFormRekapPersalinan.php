<?php
/**
 * Data form "Rekapitulasi Pelayanan Persalinan" (module/admin/form_rekap_persalinan, cetak print/formulir_persalinan).
 *   GET ?no=<visit_ID> -> { status, pasien:{...}, persalinan:{tersimpan}|null, awal:{gravid, partus, abortus dari ANC}, batal }
 * Perbaikan: no/rm disisipkan ke SQL (SQL injection); tanpa cek login & batas faskes; LEFT JOIN ms_doctor ON id_doctor
 * (berisi nama dokter) -> dokter selalu kosong; difilter nomor RM dari URL; usia DATEDIFF/365; SELECT * (ikut ttd_file).
 * Fungsi rpAmbil() juga dipakai simpan & cetak.
 */
if (!defined('RP_LIB')) {
   include '../../database/connect.php';
   require_once __DIR__ . '/../../utility/visitGuard.php';
   header('Content-Type: application/json; charset=utf-8');
   header('Cache-Control: no-store');
   $kunj = visitGuard($koneksi, 'visit_ID', (string) ($_GET['no'] ?? ''));
   session_write_close();
   try {
      $r = rpAmbil($koneksi, (int) $kunj['id_visit']);
      if ($r['persalinan']) {
         $f = basename((string) $r['persalinan']['ttd_file']);
         $r['persalinan']['ttd'] = $f !== '' && is_file(dirname(__DIR__, 2) . '/uploads/ttd/' . $f) ? 'uploads/ttd/' . rawurlencode($f) : null;
         unset($r['persalinan']['ttd_file']);
      }
      echo json_encode(['status' => 'success'] + $r, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
   } catch (Throwable $e) {
      error_log('[ranap/getFormRekapPersalinan] ' . $e->getMessage());
      http_response_code(500);
      echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
   }
   exit;
}

function rpAmbil(mysqli $db, int $idVisit): array
{
   $st = $db->prepare("SELECT v.visit_ID, v.visit_status, v.visit_date, v.id_doctor, v.id_patient, v.id_customer,
         p.nomor_rm, p.patient_name, p.patient_gender, p.patient_datebirth, p.patient_place, p.patient_nik, p.patient_bpjs,
         p.patient_address, p.patient_phone
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

   $st = $db->prepare("SELECT id_persalinan, gravid, abortus, partus, jenis_persalinan, tarif_paket, ttd_file, created_at
      FROM visit_persalinan WHERE visit_ID = ? ORDER BY id_persalinan DESC LIMIT 1");
   $st->bind_param('s', $no);
   $st->execute();
   $simpan = $st->get_result()->fetch_assoc() ?: null;
   $st->close();

   // isian awal G/P/A dari catatan ANC terakhir pasien ini di faskes ini
   $awal = ['gravid' => '', 'partus' => '', 'abortus' => ''];
   $idc = (string) ($v['id_customer'] ?? '');
   $idp = (string) ($v['id_patient'] ?? '');
   $idcInt = (int) $idc;
   $st = $db->prepare("SELECT a.gravida, a.para, a.abortus FROM pasien_persalinan_anc a
      JOIN pasien_visit pv ON pv.visit_ID = a.id_visit COLLATE utf8mb4_general_ci AND pv.id_customer = ?
      WHERE pv.id_patient = ? AND a.id_customer = ? AND (a.gravida <> '' OR a.para <> '')
      ORDER BY a.tanggal_kontrol DESC, a.id_anc DESC LIMIT 1");
   $st->bind_param('iss', $idcInt, $idp, $idc);
   $st->execute();
   if ($a = $st->get_result()->fetch_assoc()) $awal = ['gravid' => (string) $a['gravida'], 'partus' => (string) $a['para'], 'abortus' => (string) $a['abortus']];
   $st->close();

   return [
      'pasien' => [
         'nama_pasien' => (string) ($v['patient_name'] ?? ''), 'nomor_rm' => (string) ($v['nomor_rm'] ?? ''),
         'jk' => (string) ($v['patient_gender'] ?? ''), 'usia' => $usia, 'doctor_name' => (string) ($v['id_doctor'] ?? ''),
         'tanggal_lahir' => $tgl($v['patient_datebirth'] ?? ''), 'tempat_lahir' => (string) ($v['patient_place'] ?? ''),
         'nik' => (string) ($v['patient_nik'] ?? ''), 'bpjs' => (string) ($v['patient_bpjs'] ?? ''),
         'alamat' => (string) ($v['patient_address'] ?? ''), 'telepon' => (string) ($v['patient_phone'] ?? ''),
         'tanggal_pelayanan' => $tgl($v['visit_date'] ?? ''),
      ],
      'persalinan' => $simpan,
      'awal' => $awal,
      'batal' => (int) ($v['visit_status'] ?? 0) === 99,
   ];
}
