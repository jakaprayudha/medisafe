<?php
/**
 * Surat Keterangan Rawat Inap (module/letter/form-outpatient).
 *
 * GET  ?dari&sampai (tanggal_surat, bawaan 30 hari terakhir) -> {data:[ringkas]}
 * GET  ?id=                                                    -> satu surat (+ data kunjungan terkini untuk saran pembaruan)
 * GET  ?cari=1&search= (min. 2 huruf)                                -> kunjungan rawat inap faskes ini, maks. 30 (pemilih pasien)
 * GET  ?check_setting=1                                        -> setting nomor surat (kompatibilitas)
 * POST id_patient, id_visit, tanggal_surat, tanggal_masuk, tanggal_pulang, ruangan, keterangan, nomor_surat (mode manual)
 * PUT  ?id=  tanggal_surat, tanggal_masuk, tanggal_pulang, ruangan, keterangan, nomor_surat (mode manual)
 * DELETE ?id=
 * diagnosa & id_doctor diambil server dari kunjungan (resume medis / diagnosa kunjungan, DPJP kunjungan), lama dihitung
 * server dari tanggal masuk & pulang (selisih hari, minimal 1; kosong bila belum pulang) — sama seperti versi lama.
 *
 * Perbaikan: nomor otomatis lewat nomorSurat.php (nomor dilewati bila terpakai, nomor terakhir ikut disetel), mode
 * manual cek duplikat di dalam transaksi, daftar dibatasi rentang tanggal (dulu seluruh riwayat + SELECT sri.*),
 * pencarian pasien sendiri (dulu endpoint terpisah tanpa LIMIT, minimal 1 huruf, baris ganda bila resume medis /
 * permintaan ranap lebih dari satu), tanggal masuk/pulang bisa dikoreksi & divalidasi, ubah surat bisa mengoreksi nomor
 * pada mode manual, kode HTTP benar, pesan error tidak lagi berisi detail SQL.
 */
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../database/connect.php';
require_once __DIR__ . '/nomorSurat.php';
require_once __DIR__ . '/../../utility/activityLog.php';
header('Content-Type: application/json; charset=utf-8');

function responseJson(string $status, string $message = '', array $data = [], int $kode = 200): void
{
   http_response_code($kode);
   if ($kode === 200 && $_SERVER['REQUEST_METHOD'] === 'GET' && !ini_get('zlib.output_compression') && extension_loaded('zlib')) ob_start('ob_gzhandler');
   echo json_encode(['status' => $status, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
   exit;
}
function riQ(string $sql, string $t, array $p): array
{
   global $koneksi;
   $st = $koneksi->prepare($sql);
   if (!$st) throw new RuntimeException($koneksi->error);
   if ($t !== '') $st->bind_param($t, ...$p);
   if (!$st->execute()) throw new RuntimeException($st->error);
   $r = $st->get_result();
   $out = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
   $st->close();
   return $out;
}
function riExec(string $sql, string $t, array $p): int
{
   global $koneksi;
   $st = $koneksi->prepare($sql);
   if (!$st) throw new RuntimeException($koneksi->error);
   $st->bind_param($t, ...$p);
   if (!$st->execute()) throw new RuntimeException($st->error);
   $n = $st->affected_rows;
   $st->close();
   return $n;
}
function riTgl($v): ?string
{
   $v = substr(trim((string) $v), 0, 10);
   if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return null;
   [$y, $m, $d] = array_map('intval', explode('-', $v));
   return checkdate($m, $d, $y) && $y >= 1900 ? $v : null;
}
/** Lama rawat: selisih hari masuk -> pulang, minimal 1; '' bila belum pulang (perilaku lama). */
function riLama(?string $masuk, ?string $pulang): string
{
   if (!$masuk || !$pulang) return '';
   return (string) max(1, (int) (new DateTime($masuk))->diff(new DateTime($pulang))->days);
}

if (!$koneksi) responseJson('error', 'Koneksi database gagal.', [], 500);
$id_customer = (string) ($_SESSION['id_customer'] ?? '');
$PENGGUNA = (string) ($_SESSION['uid_user'] ?? $_SESSION['id_user'] ?? $_SESSION['username'] ?? 'system');
session_write_close();
if ($id_customer === '') responseJson('error', 'Session faskes/customer tidak ditemukan.', [], 401);

const RI_SELECT = "SELECT sri.id, sri.id_visit, sri.id_patient, sri.nomor_surat, sri.tanggal_surat, sri.tanggal_masuk, sri.tanggal_pulang,
      sri.lama, sri.ruangan, sri.diagnosa, sri.id_doctor, sri.keterangan, sri.created_at, sri.created_by, sri.updated_at,
      mp.patient_name, mp.nomor_rm, mp.patient_nik, mp.patient_datebirth, mp.patient_gender,
      pv.visit_ID, pv.visit_date, pv.kdDiag1, pv.nmDiag1, pv.diagnosa AS diagnosa_kode
   FROM surat_rawat_inap sri
   JOIN pasien_visit pv ON pv.id_visit = sri.id_visit AND pv.id_customer = ?
   LEFT JOIN ms_patient mp ON mp.id_patient = sri.id_patient";

/**
 * Kunjungan rawat inap faskes ini (+ kamar terakhir dari permintaan_ranap, resume medis terakhir, nama ICD, jumlah surat).
 * permintaan_ranap & resume_medis bisa >1 baris per kunjungan -> diambil yang terbaru.
 */
function riKunjungan(string $where, string $t, array $p, int $limit): array
{
   global $id_customer;
   $rows = riQ("SELECT pv.id_visit, pv.id_patient, pv.visit_ID, pv.visit_date, pv.visit_time, pv.id_doctor, pv.id_poli AS poli_asal,
         pv.kdDiag1, pv.nmDiag1, pv.diagnosa AS diagnosa_kode,
         mp.patient_name, mp.nomor_rm, mp.patient_nik, mp.patient_bpjs, mp.patient_datebirth, mp.patient_gender,
         pr.status AS status_ranap, pr.ranap_date, r.room_name, r.service_class, b.bed_name
      FROM (SELECT visit_ID_inpatient AS vid, MAX(id_ranap) AS idr FROM permintaan_ranap
            WHERE visit_ID_inpatient IS NOT NULL AND visit_ID_inpatient <> '' GROUP BY visit_ID_inpatient) x
      JOIN permintaan_ranap pr ON pr.id_ranap = x.idr
      JOIN pasien_visit pv ON pv.visit_ID = x.vid AND pv.id_customer = ? AND pv.status_rawatinap = 1
      JOIN ms_patient mp ON mp.id_patient = pv.id_patient
      LEFT JOIN ms_room r ON r.id_room = pr.id_room
      LEFT JOIN ms_room_bed b ON b.id_bed = pr.id_bed
      WHERE $where
      ORDER BY (pr.status = 'aktif') DESC, pv.visit_date DESC, pv.id_visit DESC
      LIMIT $limit", 's' . $t, array_merge([$id_customer], $p));
   if (!$rows) return [];

   $vid = array_values(array_unique(array_column($rows, 'visit_ID')));
   $ph = implode(',', array_fill(0, count($vid), '?'));
   $resume = [];
   foreach (riQ("SELECT rm.visit_ID, rm.tanggal_pulang, rm.diagnosa FROM resume_medis rm
         JOIN (SELECT MAX(id_resume) AS id FROM resume_medis WHERE visit_ID IN ($ph) GROUP BY visit_ID) t ON t.id = rm.id_resume",
         str_repeat('s', count($vid)), $vid) as $r) $resume[$r['visit_ID']] = $r;

   $kode = array_values(array_unique(array_filter(array_column($rows, 'diagnosa_kode'), fn($k) => trim((string) $k) !== '')));
   $icd = [];
   if ($kode) {
      $ph2 = implode(',', array_fill(0, count($kode), '?'));
      foreach (riQ("SELECT code, icd10 FROM icd_10 WHERE code IN ($ph2)", str_repeat('s', count($kode)), $kode) as $r) $icd[$r['code']] = $r['icd10'];
   }

   $idv = array_values(array_unique(array_map('strval', array_column($rows, 'id_visit'))));
   $ph3 = implode(',', array_fill(0, count($idv), '?'));
   $jumlah = [];
   foreach (riQ("SELECT id_visit, COUNT(*) AS n FROM surat_rawat_inap WHERE id_customer = ? AND id_visit IN ($ph3) GROUP BY id_visit",
         's' . str_repeat('s', count($idv)), array_merge([$id_customer], $idv)) as $r) $jumlah[$r['id_visit']] = (int) $r['n'];

   foreach ($rows as &$r) {
      $rs = $resume[$r['visit_ID']] ?? null;
      $r['tanggal_pulang'] = $rs ? riTgl($rs['tanggal_pulang']) : null;
      $r['diagnosa_resume'] = $rs ? trim((string) $rs['diagnosa']) : '';
      $r['diagnosa'] = riDiagnosa($r['diagnosa_resume'], $r, $icd[$r['diagnosa_kode']] ?? '');
      $kamar = trim((string) $r['bed_name']);
      $ruang = trim((string) $r['room_name']);
      // bed_name biasanya sudah memuat nama ruang ("MELATI-01")
      $r['ruangan'] = $kamar !== '' && $ruang !== '' && stripos($kamar, $ruang) !== 0 ? "$ruang / $kamar" : ($kamar !== '' ? $kamar : $ruang);
      $r['jumlah_surat'] = $jumlah[(string) $r['id_visit']] ?? 0;
      // pemilih bersama (surat-form.js) menampilkan id_poli di hasil -> isi dengan kamar & status rawat
      $r['id_poli'] = trim(($r['ruangan'] !== '' ? $r['ruangan'] : 'Kamar -') . ' · ' . ($r['status_ranap'] === 'aktif' ? 'masih dirawat' : 'pulang'));
   }
   unset($r);
   return $rows;
}
/** Diagnosa untuk surat: resume medis, lalu diagnosa PCare (kdDiag1), lalu kode ICD kunjungan. */
function riDiagnosa(string $resume, array $pv, string $namaIcd): string
{
   if ($resume !== '') return $resume;
   if (trim((string) ($pv['kdDiag1'] ?? '')) !== '') return trim($pv['kdDiag1'] . ' - ' . ($pv['nmDiag1'] ?? ''));
   $k = trim((string) ($pv['diagnosa_kode'] ?? ''));
   return $k === '' ? '' : ($namaIcd !== '' ? "$k - $namaIcd" : $k);
}

/** Nomor surat sudah dipakai surat rawat inap lain di faskes ini? */
function riNomorDipakai(string $nomor, int $kecuali = 0): bool
{
   global $id_customer;
   return (bool) riQ("SELECT id FROM surat_rawat_inap WHERE id_customer = ? AND nomor_surat = ? AND id <> ? LIMIT 1", 'ssi', [$id_customer, $nomor, $kecuali]);
}

/** Validasi input bersama POST/PUT -> [tanggal_surat, masuk, pulang|null, lama, ruangan, keterangan]. $bawaan: nilai bila field tidak dikirim. */
function riValidasi(array $in, array $bawaan): array
{
   $ts = riTgl($in['tanggal_surat'] ?? '');
   if (!$ts) responseJson('error', 'Tanggal surat wajib diisi dengan benar.', [], 422);
   $masuk = array_key_exists('tanggal_masuk', $in) ? riTgl($in['tanggal_masuk']) : riTgl($bawaan['masuk'] ?? '');
   if (!$masuk) responseJson('error', 'Tanggal masuk wajib diisi dengan benar.', [], 422);
   if (array_key_exists('tanggal_pulang', $in)) {
      $raw = trim((string) $in['tanggal_pulang']);
      $pulang = $raw === '' ? null : riTgl($raw);
      if ($raw !== '' && !$pulang) responseJson('error', 'Format tanggal pulang tidak valid.', [], 422);
   } else {
      $pulang = riTgl($bawaan['pulang'] ?? '');
   }
   if ($pulang && $pulang < $masuk) responseJson('error', 'Tanggal pulang tidak boleh sebelum tanggal masuk.', [], 422);
   if ($pulang && (new DateTime($masuk))->diff(new DateTime($pulang))->days > 366) responseJson('error', 'Lama rawat maksimal 1 tahun. Periksa tanggal masuk & pulang.', [], 422);
   $ruangan = trim((string) ($in['ruangan'] ?? $bawaan['ruangan'] ?? ''));
   if (mb_strlen($ruangan) > 255) responseJson('error', 'Ruangan maksimal 255 karakter.', [], 422);
   $ket = trim((string) ($in['keterangan'] ?? ''));
   if (mb_strlen($ket) > 2000) responseJson('error', 'Keterangan maksimal 2000 karakter.', [], 422);
   return [$ts, $masuk, $pulang, riLama($masuk, $pulang), $ruangan, $ket];
}

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         if (isset($_GET['check_setting'])) {
            $s = nsSetting($koneksi, $id_customer, 'rawat_inap');
            if (!$s['ada']) responseJson('setting_required', 'Setting nomor surat rawat inap belum dibuat.');
            responseJson('success', '', ['mode_nomor' => $s['mode'], 'format_rawat_inap' => $s['format'], 'nomor_rawat_inap' => $s['terakhir']]);
         }
         if (isset($_GET['cari'])) {
            // select2 bersama mengirim ?search=term (URL pemilih: ?cari=1&search=...)
            $term = trim((string) ($_GET['search'] ?? $_GET['cari']));
            if (mb_strlen($term) < 2) responseJson('success', 'Ketik minimal 2 huruf.', []);
            $like = '%' . addcslashes(mb_substr($term, 0, 60), '%_\\') . '%';
            $rows = riKunjungan("(mp.patient_name LIKE ? OR mp.nomor_rm LIKE ? OR mp.patient_nik LIKE ? OR mp.patient_bpjs LIKE ?)", 'ssss', [$like, $like, $like, $like], 30);
            responseJson('success', '', $rows);
         }
         if (isset($_GET['id']) && $_GET['id'] !== '') {
            $r = riQ(RI_SELECT . " WHERE sri.id = ? AND sri.id_customer = ? LIMIT 1", 'sis', [$id_customer, (int) $_GET['id'], $id_customer])[0] ?? null;
            if (!$r) responseJson('error', 'Data surat tidak ditemukan.', [], 404);
            $r['id'] = (int) $r['id'];
            $r['mode_nomor'] = nsSetting($koneksi, $id_customer, 'rawat_inap')['mode'];
            // data kunjungan terkini (mis. pasien sudah pulang setelah surat dibuat)
            $k = riKunjungan('pv.id_visit = ?', 's', [(string) $r['id_visit']], 1)[0] ?? null;
            $r['kunjungan'] = $k ? ['tanggal_pulang' => $k['tanggal_pulang'], 'ruangan' => $k['ruangan'], 'status_ranap' => $k['status_ranap'], 'diagnosa' => $k['diagnosa']] : null;
            responseJson('success', 'Data surat berhasil diambil.', $r);
         }
         $sampai = riTgl($_GET['sampai'] ?? '') ?? date('Y-m-d');
         $dari = riTgl($_GET['dari'] ?? '') ?? date('Y-m-d', strtotime('-29 days'));
         if ($dari > $sampai) [$dari, $sampai] = [$sampai, $dari];
         $rows = riQ(RI_SELECT . " WHERE sri.id_customer = ? AND sri.tanggal_surat BETWEEN ? AND ? ORDER BY sri.tanggal_surat DESC, sri.id DESC LIMIT 5000",
            'ssss', [$id_customer, $id_customer, $dari, $sampai]);
         foreach ($rows as &$r) $r['id'] = (int) $r['id'];
         unset($r);
         responseJson('success', 'Data surat berhasil diambil.', $rows);

      case 'POST':
         $idPatient = trim((string) ($_POST['id_patient'] ?? ''));
         $idVisit = trim((string) ($_POST['id_visit'] ?? ''));
         if ($idPatient === '' || $idVisit === '') responseJson('error', 'Pilih pasien / kunjungan rawat inap terlebih dahulu.', [], 422);
         $k = riKunjungan('pv.id_visit = ? AND pv.id_patient = ?', 'ss', [$idVisit, $idPatient], 1)[0] ?? null;
         if (!$k) responseJson('error', 'Kunjungan rawat inap tidak valid atau bukan milik faskes ini.', [], 404);
         [$ts, $masuk, $pulang, $lama, $ruangan, $ket] = riValidasi($_POST, ['masuk' => $k['visit_date'], 'pulang' => $k['tanggal_pulang'], 'ruangan' => $k['ruangan']]);

         $koneksi->begin_transaction();
         $n = nsAlokasi($koneksi, $id_customer, 'rawat_inap', (string) ($_POST['nomor_surat'] ?? ''), fn($no) => riNomorDipakai($no), $ts);
         if ($n['error']) {
            $koneksi->rollback();
            responseJson($n['kode'] ?? 'error', $n['error'], $n['kode'] ? ['redirect' => 'module/admin/setting_surat'] : [], $n['kode'] ? 409 : 422);
         }
         riExec("INSERT INTO surat_rawat_inap (id_visit, id_patient, id_customer, nomor_surat, tanggal_surat, tanggal_masuk, tanggal_pulang,
               diagnosa, id_doctor, keterangan, created_by, ruangan, lama)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 'sssssssssssss',
            [$idVisit, $idPatient, $id_customer, $n['nomor'], $ts, $masuk, $pulang, $k['diagnosa'], (string) $k['id_doctor'], $ket, $PENGGUNA, $ruangan, $lama]);
         $idBaru = (int) $koneksi->insert_id;
         nsSimpan($koneksi, $n);
         $koneksi->commit();
         logBaris($koneksi, 'surat', 'surat_rawat_inap', 'id', $idBaru, $__lg ?? null, ['label' => 'Surat keterangan rawat inap' . ' ' . (logAmbilBaris($koneksi, 'surat_rawat_inap', 'id', $idBaru)['nomor_surat'] ?? $__lg['nomor_surat'] ?? '')], 'Surat keterangan rawat inap');
         responseJson('success', 'Surat keterangan rawat inap ' . $n['nomor'] . ' tersimpan.', ['id' => $idBaru, 'nomor_surat' => $n['nomor'], 'lama' => $lama, 'mode_nomor' => $n['mode']]);

      case 'PUT':
         parse_str((string) file_get_contents('php://input'), $put);
         $id = (int) ($put['id'] ?? $_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         $lamaSurat = riQ(RI_SELECT . " WHERE sri.id = ? AND sri.id_customer = ? LIMIT 1", 'sis', [$id_customer, $id, $id_customer])[0] ?? null;
         if (!$lamaSurat) responseJson('error', 'Data surat tidak ditemukan atau bukan milik faskes ini.', [], 404);
         [$ts, $masuk, $pulang, $lama, $ruangan, $ket] = riValidasi($put, ['masuk' => $lamaSurat['tanggal_masuk'] ?: $lamaSurat['visit_date'], 'pulang' => $lamaSurat['tanggal_pulang'], 'ruangan' => $lamaSurat['ruangan']]);
         $nomor = (string) $lamaSurat['nomor_surat'];
         $s = nsSetting($koneksi, $id_customer, 'rawat_inap');
         // mode manual: nomor boleh dikoreksi; mode otomatis: nomor tetap
         if ($s['mode'] === 'MANUAL' && isset($put['nomor_surat'])) {
            $nomorBaru = trim((string) $put['nomor_surat']);
            if ($nomorBaru === '') responseJson('error', 'Nomor surat wajib diisi (mode penomoran manual).', [], 422);
            if (mb_strlen($nomorBaru) > 100) responseJson('error', 'Nomor surat maksimal 100 karakter.', [], 422);
            if ($nomorBaru !== $nomor && riNomorDipakai($nomorBaru, $id)) responseJson('error', "Nomor surat $nomorBaru sudah dipakai surat lain.", [], 409);
            $nomor = $nomorBaru;
         }
         // diagnosa/DPJP tetap seperti saat dibuat; bila dulu kosong, isi dari data kunjungan terkini
         $diag = (string) $lamaSurat['diagnosa'];
         $dok = (string) $lamaSurat['id_doctor'];
         if (trim($diag) === '' || trim($dok) === '') {
            $k = riKunjungan('pv.id_visit = ?', 's', [(string) $lamaSurat['id_visit']], 1)[0] ?? null;
            if ($k && trim($diag) === '') $diag = $k['diagnosa'];
            if ($k && trim($dok) === '') $dok = (string) $k['id_doctor'];
         }
         $__lg ??= logAmbilBaris($koneksi, 'surat_rawat_inap', 'id', $id); // log: kondisi sebelum diubah/dihapus
         riExec("UPDATE surat_rawat_inap SET nomor_surat = ?, tanggal_surat = ?, tanggal_masuk = ?, tanggal_pulang = ?, lama = ?, ruangan = ?,
               keterangan = ?, diagnosa = ?, id_doctor = ?, updated_at = NOW(), updated_by = ?
            WHERE id = ? AND id_customer = ?", 'ssssssssssis', [$nomor, $ts, $masuk, $pulang, $lama, $ruangan, $ket, $diag, $dok, $PENGGUNA, $id, $id_customer]);
         logBaris($koneksi, 'surat', 'surat_rawat_inap', 'id', $id, $__lg ?? null, ['label' => 'Surat keterangan rawat inap' . ' ' . (logAmbilBaris($koneksi, 'surat_rawat_inap', 'id', $id)['nomor_surat'] ?? $__lg['nomor_surat'] ?? '')], 'Surat keterangan rawat inap');
         responseJson('success', 'Surat keterangan rawat inap diperbarui.', ['id' => $id, 'nomor_surat' => $nomor, 'ruangan' => $ruangan, 'lama' => $lama]);

      case 'DELETE':
         $id = (int) ($_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         $__lg ??= logAmbilBaris($koneksi, 'surat_rawat_inap', 'id', $id); // log: kondisi sebelum diubah/dihapus
         $n = riExec("DELETE FROM surat_rawat_inap WHERE id = ? AND id_customer = ?", 'is', [$id, $id_customer]);
         if ($n > 0) logBaris($koneksi, 'surat', 'surat_rawat_inap', 'id', $id, $__lg ?? null, ['label' => 'Surat keterangan rawat inap' . ' ' . (logAmbilBaris($koneksi, 'surat_rawat_inap', 'id', $id)['nomor_surat'] ?? $__lg['nomor_surat'] ?? '')], 'Surat keterangan rawat inap');
         $n > 0 ? responseJson('success', 'Surat keterangan rawat inap dihapus.') : responseJson('error', 'Data tidak ditemukan atau tidak memiliki akses.', [], 404);

      default:
         responseJson('error', 'Method tidak diizinkan.', [], 405);
   }
} catch (Throwable $e) {
   try { $koneksi->rollback(); } catch (Throwable $x) {}
   error_log('[suratRawatInapController] ' . $e->getMessage());
   responseJson('error', 'Terjadi kesalahan server. Coba lagi.', [], 500);
}
