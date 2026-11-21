<?php
/**
 * Surat Keterangan Kematian (module/letter/form-death).
 *
 * GET  ?dari&sampai (tanggal_surat, bawaan 30 hari terakhir) -> daftar ringkas
 * GET  ?id=                                                    -> satu surat
 * POST id_patient, id_visit, tanggal_surat, tanggal_kematian, waktu_kematian, ruangan, dokter_menyatakan,
 *      nomor_surat (mode manual)
 * PUT  ?id=  tanggal_surat, tanggal_kematian, waktu_kematian, ruangan, dokter_menyatakan, nomor_surat (mode manual)
 * DELETE ?id=
 * Semua akses dibatasi faskes sesi (surat_kematian.id_customer dan pasien_visit.id_customer).
 * Kolom yang ditulis sama dengan versi lama (dibaca print/surat-keterangan-kematian.php & verifikasi-surat-kematian.php).
 *
 * Perbaikan: PUT selalu gagal (bind_param("ssssss is") -> jumlah tipe tidak cocok), nomor otomatis dialokasikan
 * dalam transaksi dengan baris setting dikunci (dulu cek duplikat di luar transaksi -> dua simpan bersamaan bisa
 * bernomor sama, dan nomor yang sudah terpakai langsung ditolak alih-alih dilewati), dokter tidak dicek milik faskes,
 * tanggal/jam tidak divalidasi (kematian di masa depan, sebelum lahir/kunjungan, surat sebelum kematian), satu pasien
 * bisa dibuatkan beberapa surat kematian, daftar memuat seluruh riwayat, redirect setting ke halaman yang tidak ada,
 * pesan error berisi detail SQL, dan semua error dibalas HTTP 200.
 */
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../database/connect.php';
require_once __DIR__ . '/nomorSurat.php';
header('Content-Type: application/json; charset=utf-8');

function responseJson(string $status, string $message = '', array $data = [], int $kode = 200): void
{
   http_response_code($kode);
   if ($kode === 200 && $_SERVER['REQUEST_METHOD'] === 'GET' && !ini_get('zlib.output_compression') && extension_loaded('zlib')) ob_start('ob_gzhandler');
   echo json_encode(['status' => $status, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
   exit;
}
function skQ(string $sql, string $t, array $p): array
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
function skExec(string $sql, string $t, array $p): int
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
function skTgl($v): ?string
{
   $v = substr(trim((string) $v), 0, 10);
   if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return null;
   [$y, $m, $d] = array_map('intval', explode('-', $v));
   return checkdate($m, $d, $y) && $y >= 1900 ? $v : null;
}
function skJam($v): ?string
{
   return preg_match('/^([01]\d|2[0-3]):([0-5]\d)(:[0-5]\d)?$/', trim((string) $v), $m) ? $m[1] . ':' . $m[2] : null;
}

if (!$koneksi) responseJson('error', 'Koneksi database gagal.', [], 500);
$id_customer = (string) ($_SESSION['id_customer'] ?? '');
$PENGGUNA = (string) ($_SESSION['uid_user'] ?? $_SESSION['id_user'] ?? $_SESSION['username'] ?? 'system');
session_write_close();
if ($id_customer === '') responseJson('error', 'Session faskes tidak ditemukan.', [], 401);

const SK_SELECT = "SELECT sk.id, sk.id_visit, sk.id_patient, sk.nomor_surat, sk.tanggal_surat, sk.tanggal_kematian, sk.waktu_kematian,
      sk.ruangan, sk.dokter_menyatakan, sk.created_at, sk.updated_at, sk.created_by, sk.updated_by,
      mp.patient_name, mp.nomor_rm, mp.patient_nik, mp.patient_datebirth, mp.patient_gender,
      pv.id_doctor, pv.id_poli, pv.visit_ID, pv.visit_date, md.doctor_name
   FROM surat_kematian sk
   JOIN pasien_visit pv ON pv.id_visit = sk.id_visit AND pv.id_customer = sk.id_customer
   LEFT JOIN ms_patient mp ON mp.id_patient = sk.id_patient
   LEFT JOIN ms_doctor md ON md.id_doctor = sk.dokter_menyatakan AND md.id_customer = sk.id_customer
   WHERE sk.id_customer = ?";

/** Nomor surat sudah dipakai surat kematian lain di faskes ini? */
function skNomorDipakai(string $nomor, int $kecuali = 0): bool
{
   global $id_customer;
   return (bool) skQ("SELECT id FROM surat_kematian WHERE id_customer = ? AND nomor_surat = ? AND id <> ? LIMIT 1", 'ssi', [$id_customer, $nomor, $kecuali]);
}

/**
 * Validasi isian -> [tanggal_surat, tanggal_kematian, waktu_kematian, ruangan, dokter] atau balas 422.
 * $tglKunjungan / $tglLahir: batas bawah tanggal kematian.
 */
function skValidasi(array $in, ?string $tglKunjungan, ?string $tglLahir): array
{
   global $id_customer;
   $ts = skTgl($in['tanggal_surat'] ?? '');
   $tk = skTgl($in['tanggal_kematian'] ?? '');
   $jam = skJam($in['waktu_kematian'] ?? '');
   $ruangan = trim((string) ($in['ruangan'] ?? ''));
   $dokter = trim((string) ($in['dokter_menyatakan'] ?? ''));
   $kini = date('Y-m-d H:i');
   if (!$ts) responseJson('error', 'Tanggal surat wajib diisi dengan benar.', [], 422);
   if (!$tk) responseJson('error', 'Tanggal kematian wajib diisi dengan benar.', [], 422);
   if (!$jam) responseJson('error', 'Waktu kematian wajib diisi (format JJ:MM).', [], 422);
   if ("$tk $jam" > $kini) responseJson('error', 'Tanggal / waktu kematian tidak boleh di masa depan.', [], 422);
   $tglLahir = skTgl($tglLahir);
   if ($tglLahir && $tk < $tglLahir) responseJson('error', 'Tanggal kematian tidak boleh sebelum tanggal lahir pasien.', [], 422);
   $tglKunjungan = skTgl($tglKunjungan);
   // toleransi 1 hari: pasien meninggal sebelum tengah malam lalu didaftarkan setelahnya (death on arrival)
   if ($tglKunjungan && $tk < date('Y-m-d', strtotime($tglKunjungan . ' -1 day'))) responseJson('error', 'Tanggal kematian terlalu jauh sebelum tanggal kunjungan (' . $tglKunjungan . ').', [], 422);
   if ($ts < $tk) responseJson('error', 'Tanggal surat tidak boleh sebelum tanggal kematian.', [], 422);
   if ($ruangan === '') responseJson('error', 'Ruangan / tempat kematian wajib diisi.', [], 422);
   if (mb_strlen($ruangan) > 255) responseJson('error', 'Ruangan maksimal 255 karakter.', [], 422);
   if ($dokter === '' || !ctype_digit($dokter)) responseJson('error', 'Dokter yang menyatakan wajib dipilih.', [], 422);
   if (!skQ("SELECT 1 FROM ms_doctor WHERE id_doctor = ? AND id_customer = ? LIMIT 1", 'is', [(int) $dokter, $id_customer]))
      responseJson('error', 'Dokter tidak ditemukan di faskes ini.', [], 422);
   return [$ts, $tk, $jam, $ruangan, $dokter];
}

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         if (isset($_GET['id']) && $_GET['id'] !== '') {
            $r = skQ(SK_SELECT . " AND sk.id = ? LIMIT 1", 'si', [$id_customer, (int) $_GET['id']])[0] ?? null;
            if (!$r) responseJson('error', 'Data surat tidak ditemukan.', [], 404);
            $s = nsSetting($koneksi, $id_customer, 'kematian');
            $r['id'] = (int) $r['id'];
            $r['mode_nomor'] = $s['mode'];
            responseJson('success', 'Data surat berhasil diambil.', $r);
         }
         $sampai = skTgl($_GET['sampai'] ?? '') ?? date('Y-m-d');
         $dari = skTgl($_GET['dari'] ?? '') ?? date('Y-m-d', strtotime('-29 days'));
         if ($dari > $sampai) [$dari, $sampai] = [$sampai, $dari];
         $rows = skQ(SK_SELECT . " AND sk.tanggal_surat BETWEEN ? AND ? ORDER BY sk.tanggal_surat DESC, sk.id DESC LIMIT 5000", 'sss', [$id_customer, $dari, $sampai]);
         foreach ($rows as &$r) $r['id'] = (int) $r['id'];
         unset($r);
         responseJson('success', 'Data surat berhasil diambil.', $rows);

      case 'POST':
         $idPatient = trim((string) ($_POST['id_patient'] ?? ''));
         $idVisit = trim((string) ($_POST['id_visit'] ?? ''));
         if ($idPatient === '' || $idVisit === '') responseJson('error', 'Pilih pasien / kunjungan terlebih dahulu.', [], 422);
         $v = skQ("SELECT pv.visit_date, mp.patient_datebirth FROM pasien_visit pv LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
            WHERE pv.id_visit = ? AND pv.id_patient = ? AND pv.id_customer = ? LIMIT 1", 'sss', [$idVisit, $idPatient, $id_customer])[0] ?? null;
         if (!$v) responseJson('error', 'Kunjungan pasien tidak valid atau bukan milik faskes ini.', [], 404);
         [$ts, $tk, $jam, $ruangan, $dokter] = skValidasi($_POST, $v['visit_date'], $v['patient_datebirth']);
         // seorang pasien hanya meninggal sekali: tolak surat kedua (ubah surat yang ada bila perlu koreksi)
         $ada = skQ("SELECT nomor_surat FROM surat_kematian WHERE id_customer = ? AND id_patient = ? LIMIT 1", 'ss', [$id_customer, $idPatient])[0] ?? null;
         if ($ada) responseJson('error', 'Pasien ini sudah memiliki surat keterangan kematian (nomor ' . ($ada['nomor_surat'] ?: '-') . '). Ubah surat tersebut bila perlu koreksi.', [], 409);

         $koneksi->begin_transaction();
         $n = nsAlokasi($koneksi, $id_customer, 'kematian', (string) ($_POST['nomor_surat'] ?? ''), fn($no) => skNomorDipakai($no), $ts);
         if ($n['error']) {
            $koneksi->rollback();
            responseJson($n['kode'] ?? 'error', $n['error'], $n['kode'] ? ['redirect' => 'module/admin/setting_surat'] : [], $n['kode'] ? 409 : 422);
         }
         skExec("INSERT INTO surat_kematian (id_visit, id_patient, id_customer, nomor_surat, tanggal_surat, tanggal_kematian, waktu_kematian, ruangan, dokter_menyatakan, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 'ssssssssss', [$idVisit, $idPatient, $id_customer, $n['nomor'], $ts, $tk, $jam, $ruangan, $dokter, $PENGGUNA]);
         $idBaru = (int) $koneksi->insert_id;
         nsSimpan($koneksi, $n);
         $koneksi->commit();
         responseJson('success', 'Surat keterangan kematian ' . $n['nomor'] . ' tersimpan.', ['id' => $idBaru, 'id_customer' => $id_customer, 'nomor_surat' => $n['nomor'], 'mode_nomor' => $n['mode']]);

      case 'PUT':
         parse_str((string) file_get_contents('php://input'), $put);
         $id = (int) ($put['id'] ?? $_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         $lama = skQ(SK_SELECT . " AND sk.id = ? LIMIT 1", 'si', [$id_customer, $id])[0] ?? null;
         if (!$lama) responseJson('error', 'Data surat tidak ditemukan.', [], 404);
         [$ts, $tk, $jam, $ruangan, $dokter] = skValidasi($put, $lama['visit_date'], $lama['patient_datebirth']);
         $nomor = (string) $lama['nomor_surat'];
         $s = nsSetting($koneksi, $id_customer, 'kematian');
         // mode manual: nomor boleh dikoreksi; mode otomatis: nomor tetap
         if ($s['mode'] === 'MANUAL' && isset($put['nomor_surat'])) {
            $nomorBaru = trim((string) $put['nomor_surat']);
            if ($nomorBaru === '') responseJson('error', 'Nomor surat wajib diisi (mode penomoran manual).', [], 422);
            if (mb_strlen($nomorBaru) > 100) responseJson('error', 'Nomor surat maksimal 100 karakter.', [], 422);
            if ($nomorBaru !== $nomor && skNomorDipakai($nomorBaru, $id)) responseJson('error', "Nomor surat $nomorBaru sudah dipakai surat lain.", [], 409);
            $nomor = $nomorBaru;
         }
         skExec("UPDATE surat_kematian SET nomor_surat = ?, tanggal_surat = ?, tanggal_kematian = ?, waktu_kematian = ?, ruangan = ?, dokter_menyatakan = ?,
               updated_at = NOW(), updated_by = ?
            WHERE id = ? AND id_customer = ?", 'sssssssis', [$nomor, $ts, $tk, $jam, $ruangan, $dokter, $PENGGUNA, $id, $id_customer]);
         responseJson('success', 'Surat keterangan kematian diperbarui.', ['id' => $id, 'nomor_surat' => $nomor]);

      case 'DELETE':
         $id = (int) ($_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         $n = skExec("DELETE FROM surat_kematian WHERE id = ? AND id_customer = ?", 'is', [$id, $id_customer]);
         $n > 0 ? responseJson('success', 'Surat keterangan kematian dihapus.') : responseJson('error', 'Data tidak ditemukan atau bukan milik faskes ini.', [], 404);

      default:
         responseJson('error', 'Method tidak diizinkan.', [], 405);
   }
} catch (Throwable $e) {
   try { $koneksi->rollback(); } catch (Throwable $x) {}
   error_log('[suratKematianController] ' . $e->getMessage());
   responseJson('error', 'Terjadi kesalahan server. Coba lagi.', [], 500);
}
