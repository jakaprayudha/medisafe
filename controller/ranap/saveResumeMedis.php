<?php
/**
 * Simpan Resume Medis (module/admin/resume_medis). POST ?no=<visit_ID>, JSON:
 *   { tanggal_pulang, jam_pulang, diagnosa_utama: "KODE", diagnosa_sekunder: ["KODE", ...], pemeriksaan_fisik,
 *     terapi_rawat, alergi_obat, terapi_pulang, rencana_tindak_lanjut, kondisi_pulang, cara_keluar }
 * Pemetaan kolom tetap seperti sebelumnya (dipakai print/formulir_resume_v2 & laporan):
 *   resume_medis: diagnosa (ringkasan teks), tindakan = pemeriksaan fisik, pemeriksaan_penunjang = terapi selama rawat,
 *                 obat = alergi (lama), instruksi = terapi pulang, tanggal_pulang, jam_pulang, dokter, petugas
 *   pasien_visit: diagnosa_utama, diagnosa_sekunder ("KODE - nama; ..."), pemeriksaan_fisik, alergi_obat,
 *                 kondisi_pulang, cara_keluar, rencana_tindak_lanjut
 * Perbaikan: semua nilai dirangkai ke SQL (SQL injection); tanpa cek login & batas faskes (UPDATE pasien_visit hanya
 * WHERE visit_ID); klik Simpan berulang membuat resume dobel (ada kunjungan dengan 17 baris) & resume dicari lewat
 * nomor RM dari URL (format beda -> baris baru); dokter/petugas tidak pernah terisi; tanpa validasi tanggal/diagnosa/
 * pilihan; pesan error SQL dikirim ke browser.
 */
require '../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$petugas = (string) ($_SESSION['fullname'] ?? ($_SESSION['username'] ?? ''));
session_write_close();

const RS_KONDISI = ['Sembuh', 'Membaik', 'Belum Sembuh', 'Mati < 48 Jam', 'Mati > 48 Jam', 'Lainnya'];
const RS_CARA = ['Diizinkan Pulang', 'Pulang Paksa', 'Lari', 'Pindah Ke FKTP Rawat Inap Lain', 'Dirujuk Ke RS', 'Lainnya'];

function rsBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) rsBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rsBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);

$no = trim((string) ($_GET['no'] ?? ''));
$in = json_decode((string) file_get_contents('php://input', false, null, 0, 512 * 1024), true);
if ($no === '' || !is_array($in)) rsBalas(422, ['status' => 'error', 'message' => 'Data tidak valid.']);
$tolak = fn($pesan, $f) => rsBalas(422, ['status' => 'error', 'message' => $pesan, 'field' => $f]);
$t = fn($k, $max) => mb_substr(trim((string) (is_scalar($in[$k] ?? null) ? $in[$k] : '')), 0, $max);

try {
   $st = $koneksi->prepare("SELECT v.visit_date, v.visit_status, v.id_doctor, p.nomor_rm,
         (SELECT pr.ranap_date FROM permintaan_ranap pr WHERE pr.visit_ID_inpatient = v.visit_ID ORDER BY pr.id_ranap DESC LIMIT 1) AS ranap_date
      FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
      WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1");
   $st->bind_param('si', $no, $idc);
   $st->execute();
   $k = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$k) rsBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   if ((int) $k['visit_status'] === 99) rsBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);

   // ---- validasi ----
   $tgl = $t('tanggal_pulang', 10);
   $d = DateTime::createFromFormat('!Y-m-d', $tgl);
   if (!$d || $d->format('Y-m-d') !== $tgl) $tolak('Tanggal pulang wajib diisi.', 'tanggal_pulang');
   $masuk = substr((string) ($k['ranap_date'] ?: $k['visit_date']), 0, 10);
   if ($masuk !== '' && $tgl < $masuk) $tolak('Tanggal pulang tidak boleh sebelum tanggal masuk (' . $masuk . ').', 'tanggal_pulang');
   if ($tgl > date('Y-m-d')) $tolak('Tanggal pulang tidak boleh di masa depan.', 'tanggal_pulang');
   $jam = $t('jam_pulang', 5);
   if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $jam)) $tolak('Jam pulang wajib diisi (JJ:MM).', 'jam_pulang');
   if ($tgl === date('Y-m-d') && $jam > date('H:i', time() + 300)) $tolak('Jam pulang tidak boleh di masa depan.', 'jam_pulang');

   $utama = strtoupper($t('diagnosa_utama', 10));
   if ($utama === '') $tolak('Diagnosa utama wajib diisi.', 'diagnosa_utama');
   $sek = is_array($in['diagnosa_sekunder'] ?? null) ? $in['diagnosa_sekunder'] : [];
   $sek = array_values(array_unique(array_filter(array_map(fn($c) => strtoupper(mb_substr(trim((string) $c), 0, 10)), $sek), fn($c) => $c !== '' && $c !== $utama)));
   if (count($sek) > 15) $tolak('Diagnosa sekunder maksimal 15.', 'diagnosa_sekunder');
   $kode = array_merge([$utama], $sek);
   $st = $koneksi->prepare("SELECT code, icd10 FROM icd_10 WHERE code IN (" . implode(',', array_fill(0, count($kode), '?')) . ")");
   $st->bind_param(str_repeat('s', count($kode)), ...$kode);
   $st->execute();
   $icd = [];
   foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) $icd[strtoupper($r['code'])] = $r['code'] . ' - ' . $r['icd10'];
   $st->close();
   if (!isset($icd[$utama])) $tolak("Kode ICD-10 $utama tidak ditemukan.", 'diagnosa_utama');
   foreach ($sek as $c) if (!isset($icd[$c])) $tolak("Kode ICD-10 $c tidak ditemukan.", 'diagnosa_sekunder');

   $kondisi = $t('kondisi_pulang', 50);
   if (!in_array($kondisi, RS_KONDISI, true)) $tolak('Pilih kondisi pasien saat pulang.', 'kondisi_pulang');
   $cara = $t('cara_keluar', 50);
   if (!in_array($cara, RS_CARA, true)) $tolak('Pilih cara keluar.', 'cara_keluar');
   $fisik = $t('pemeriksaan_fisik', 10000);
   $terapiRawat = $t('terapi_rawat', 10000);
   $alergi = $t('alergi_obat', 2000);
   $terapiPulang = $t('terapi_pulang', 10000);
   $rtl = $t('rencana_tindak_lanjut', 5000);

   $teksUtama = $icd[$utama];
   $teksSek = implode('; ', array_map(fn($c) => $icd[$c], $sek));
   $ringkas = $teksUtama . ($teksSek !== '' ? ' | ' . $teksSek : '');
   $dokter = mb_substr((string) $k['id_doctor'], 0, 100);
   $rm = (string) $k['nomor_rm'];
   $ptg = mb_substr($petugas, 0, 100);

   // ---- simpan (kunci baris kunjungan agar klik ganda tidak membuat resume dobel) ----
   $koneksi->begin_transaction();
   try {
      $st = $koneksi->prepare("SELECT id_visit FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? FOR UPDATE");
      $st->bind_param('si', $no, $idc);
      $st->execute();
      $st->get_result()->fetch_all();
      $st->close();
      $st = $koneksi->prepare("SELECT id_resume FROM resume_medis WHERE visit_ID = ? ORDER BY id_resume DESC LIMIT 1");
      $st->bind_param('s', $no);
      $st->execute();
      $idResume = (int) ($st->get_result()->fetch_row()[0] ?? 0);
      $st->close();
      if ($idResume > 0) {
         $st = $koneksi->prepare("UPDATE resume_medis SET nomor_rm = ?, diagnosa = ?, tindakan = ?, pemeriksaan_penunjang = ?, obat = ?, instruksi = ?,
            petugas = ?, dokter = ?, tanggal_pulang = ?, jam_pulang = ? WHERE id_resume = ?");
         $st->bind_param('ssssssssssi', $rm, $ringkas, $fisik, $terapiRawat, $alergi, $terapiPulang, $ptg, $dokter, $tgl, $jam, $idResume);
         $pesan = 'Resume medis diperbarui.';
      } else {
         $st = $koneksi->prepare("INSERT INTO resume_medis (visit_ID, nomor_rm, diagnosa, tindakan, pemeriksaan_penunjang, obat, instruksi, petugas, dokter, tanggal_pulang, jam_pulang)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
         $st->bind_param('sssssssssss', $no, $rm, $ringkas, $fisik, $terapiRawat, $alergi, $terapiPulang, $ptg, $dokter, $tgl, $jam);
         $pesan = 'Resume medis disimpan.';
      }
      $st->execute();
      $st->close();
      $st = $koneksi->prepare("UPDATE pasien_visit SET diagnosa_utama = ?, diagnosa_sekunder = ?, pemeriksaan_fisik = ?, alergi_obat = ?,
            kondisi_pulang = ?, cara_keluar = ?, rencana_tindak_lanjut = ?
         WHERE visit_ID = ? AND id_customer = ?");
      $st->bind_param('ssssssssi', $teksUtama, $teksSek, $fisik, $alergi, $kondisi, $cara, $rtl, $no, $idc);
      $st->execute();
      $st->close();
      $koneksi->commit();
   } catch (Throwable $e) {
      $koneksi->rollback();
      throw $e;
   }
   rsBalas(200, ['status' => 'success', 'message' => $pesan]);
} catch (Throwable $e) {
   error_log('[ranap/saveResumeMedis] ' . $e->getMessage());
   rsBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}
