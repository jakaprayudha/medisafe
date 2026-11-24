<?php
/**
 * Data form "Masuk & Keluar Pasien Rawat Inap" (module/admin/form_io_ranap, cetak print/formulir_inout_ranap).
 *   GET ?no=<visit_ID> -> { status, pasien:{...}, awal:{isian bawaan dari data kunjungan}, inap:{tersimpan}|null, batal }
 * Perbaikan: no/rm disisipkan ke SQL (SQL injection); tanpa cek login & batas faskes; LEFT JOIN ms_doctor ON id_doctor
 * (kolom berisi nama dokter) -> nama dokter selalu kosong; difilter nomor RM dari URL; usia dihitung DATEDIFF/365.
 * Fungsi ioAmbil() juga dipakai halaman cetak.
 */
if (!defined('IO_RANAP_LIB')) {
   include '../../database/connect.php';
   require_once __DIR__ . '/../../utility/visitGuard.php';
   header('Content-Type: application/json; charset=utf-8');
   header('Cache-Control: no-store');
   $kunj = visitGuard($koneksi, 'visit_ID', (string) ($_GET['no'] ?? ''));
   session_write_close();
   try {
      echo json_encode(['status' => 'success'] + ioAmbil($koneksi, (int) $kunj['id_visit']), JSON_UNESCAPED_UNICODE);
   } catch (Throwable $e) {
      error_log('[ranap/getFormInap] ' . $e->getMessage());
      http_response_code(500);
      echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
   }
   exit;
}

/** Pasien, isian bawaan (dari kunjungan / permintaan ranap) dan data visit_ranap tersimpan untuk 1 kunjungan. */
function ioAmbil(mysqli $db, int $idVisit): array
{
   $st = $db->prepare("SELECT v.visit_ID, v.visit_status, v.visit_date, v.visit_time, v.visit_date_out, v.id_doctor, v.anamnesa, v.diagnosa,
         v.diagnosa_sekunder, v.penanggung_jawab, v.penyebab_keracunan, v.kondisi_pulang, v.cara_keluar,
         p.nomor_rm, p.patient_name, p.patient_gender, p.patient_datebirth, p.patient_marital_status, p.patient_allergy,
         p.patient_religion, p.patient_education, p.patient_occupation, p.patient_bpjs, p.patient_address,
         p.patient_emergency_contact_name, i.icd10, pr.ranap_date, pr.ranap_time, r.room_name, b.bed_name
      FROM pasien_visit v
      LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
      LEFT JOIN icd_10 i ON i.code = v.diagnosa
      LEFT JOIN permintaan_ranap pr ON pr.visit_ID_inpatient = v.visit_ID
      LEFT JOIN ms_room r ON r.id_room = pr.id_room
      LEFT JOIN ms_room_bed b ON b.id_bed = pr.id_bed
      WHERE v.id_visit = ? ORDER BY pr.id_ranap DESC LIMIT 1");
   $st->bind_param('i', $idVisit);
   $st->execute();
   $v = $st->get_result()->fetch_assoc() ?: [];
   $st->close();

   $tgl = fn($s) => preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $s, $m) ? $m[0] : '';
   $jam = fn($s) => preg_match('/^([01]\d|2[0-3]):[0-5]\d/', trim((string) $s), $m) ? $m[0] : '';
   $usia = '';
   if ($tgl($v['patient_datebirth'] ?? '')) {
      $d = (new DateTime($tgl($v['patient_datebirth'])))->diff(new DateTime($tgl($v['visit_date'] ?? '') ?: 'today'));
      $usia = $d->y ? "{$d->y} th {$d->m} bln" : ($d->m ? "{$d->m} bln {$d->d} hr" : "{$d->d} hr");
   }
   $ruang = trim(implode(' / ', array_filter([(string) ($v['room_name'] ?? ''), (string) ($v['bed_name'] ?? '')])));
   $dx = trim(implode(' - ', array_filter([(string) ($v['diagnosa'] ?? ''), (string) ($v['icd10'] ?? '')])));

   $awal = [
      'tanggal_masuk' => $tgl($v['visit_date'] ?? ''),
      'jam_masuk' => $jam($v['visit_time'] ?? ''),
      'status_perkawinan' => (string) ($v['patient_marital_status'] ?? ''),
      'penanggung_jawab' => (string) (($v['penanggung_jawab'] ?? '') ?: ($v['patient_emergency_contact_name'] ?? '')),
      'alamat_pj' => '',
      'tanggal_pindah' => $tgl($v['ranap_date'] ?? ''),
      'jam_pindah' => $jam($v['ranap_time'] ?? ''),
      'ruang_rawat' => $ruang,
      'tanggal_keluar' => $tgl($v['visit_date_out'] ?? ''),   // varchar, bisa berisi teks lain
      'jam_keluar' => '',                                        // visit_out berisi status ("Rawat Inap"), bukan jam
      'diagnosa_medik' => (string) ($v['anamnesa'] ?? ''),
      'diagnosa_utama' => $dx,
      'diagnosa_komplikasi' => (string) ($v['diagnosa_sekunder'] ?? ''),
      'penyebab_keracunan' => (string) ($v['penyebab_keracunan'] ?? ''),
      'alergi' => (string) ($v['patient_allergy'] ?? ''),
      'keadaan_keluar' => (string) ($v['kondisi_pulang'] ?? ''),
      'cara_keluar' => (string) ($v['cara_keluar'] ?? ''),
      'dokter_merawat' => (string) ($v['id_doctor'] ?? ''),   // kolom id_doctor berisi nama dokter
   ];

   $visitId = (string) ($v['visit_ID'] ?? '');
   $st = $db->prepare("SELECT * FROM visit_ranap WHERE visit_ID = ? ORDER BY id_inap DESC LIMIT 1");
   $st->bind_param('s', $visitId);
   $st->execute();
   $inap = $st->get_result()->fetch_assoc() ?: null;
   $st->close();
   if ($inap) {
      foreach (['jam_masuk', 'jam_pindah', 'jam_keluar'] as $k) $inap[$k] = $jam($inap[$k] ?? '');
   }

   return [
      'pasien' => [
         'nama_pasien' => (string) ($v['patient_name'] ?? ''), 'nomor_rm' => (string) ($v['nomor_rm'] ?? ''),
         'jk' => (string) ($v['patient_gender'] ?? ''), 'usia' => $usia, 'tanggal_lahir' => $tgl($v['patient_datebirth'] ?? ''),
         'agama' => (string) ($v['patient_religion'] ?? ''), 'pendidikan' => (string) ($v['patient_education'] ?? ''),
         'pekerjaan' => (string) ($v['patient_occupation'] ?? ''), 'nomor_bpjs' => (string) ($v['patient_bpjs'] ?? ''),
         'alamat' => (string) ($v['patient_address'] ?? ''), 'doctor_name' => (string) ($v['id_doctor'] ?? ''),
      ],
      'awal' => $awal,
      'inap' => $inap,
      'batal' => (int) ($v['visit_status'] ?? 0) === 99,
   ];
}
