<?php
/**
 * Surat Keterangan Sakit (module/letter/form-sick).
 *
 * GET  ?dari&sampai (tanggal_surat, bawaan 30 hari terakhir) -> {data:[ringkas], dari, sampai}
 * GET  ?id=                                                    -> satu surat
 * POST id_patient, id_visit, tanggal_surat, tanggal_mulai, tanggal_selesai, keterangan, nomor_surat (mode manual)
 * PUT  ?id=  tanggal_surat, tanggal_mulai, tanggal_selesai, keterangan, nomor_surat (mode manual)
 * DELETE ?id=
 * surat_sakit tidak menyimpan id_customer -> akses faskes selalu lewat pasien_visit.
 *
 * Perbaikan: nomor otomatis dialokasikan dalam transaksi dengan baris setting dikunci (dulu dua simpan bersamaan
 * bisa mendapat nomor sama, dan nomor yang dilewati karena duplikat tidak tercatat sehingga pencarian duplikat
 * diulang setiap surat), daftar dibatasi rentang tanggal (dulu seluruh riwayat + SELECT ss.*), validasi format
 * tanggal & rentang, kode HTTP benar, dan pesan error tidak lagi berisi detail SQL.
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
function ssQ(string $sql, string $t, array $p): array
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
function ssExec(string $sql, string $t, array $p): int
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
function ssTgl($v): ?string
{
   $v = trim((string) $v);
   if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return null;
   [$y, $m, $d] = array_map('intval', explode('-', $v));
   return checkdate($m, $d, $y) ? $v : null;
}

if (!$koneksi) responseJson('error', 'Koneksi database gagal.', [], 500);
$id_customer = (string) ($_SESSION['id_customer'] ?? '');
$PENGGUNA = (string) ($_SESSION['id_user'] ?? $_SESSION['uid_user'] ?? $_SESSION['username'] ?? 'system');
session_write_close();
if ($id_customer === '') responseJson('error', 'Session faskes/customer tidak ditemukan.', [], 401);

const SS_SELECT = "SELECT ss.id, ss.id_visit, ss.id_patient, ss.nomor_surat, ss.tanggal_surat, ss.tanggal_mulai, ss.tanggal_selesai, ss.lama,
      ss.keterangan, ss.created_at, ss.created_by, mp.patient_name, mp.nomor_rm, mp.patient_nik, mp.patient_datebirth, mp.patient_gender,
      pv.id_doctor, pv.visit_ID, pv.visit_date
   FROM surat_sakit ss
   JOIN pasien_visit pv ON pv.id_visit = ss.id_visit AND pv.id_customer = ?
   LEFT JOIN ms_patient mp ON mp.id_patient = ss.id_patient";

/** Nomor surat sudah dipakai surat sakit lain di faskes ini? */
function ssNomorDipakai(string $nomor, int $kecuali = 0): bool
{
   global $id_customer;
   return (bool) ssQ("SELECT ss.id FROM surat_sakit ss JOIN pasien_visit pv ON pv.id_visit = ss.id_visit
      WHERE pv.id_customer = ? AND ss.nomor_surat = ? AND ss.id <> ? LIMIT 1", 'ssi', [$id_customer, $nomor, $kecuali]);
}

/** Validasi tanggal input -> [tanggal_surat, mulai, selesai, lama] atau balas error. */
function ssValidasiTanggal(array $in): array
{
   $ts = ssTgl($in['tanggal_surat'] ?? '');
   $mulai = ssTgl($in['tanggal_mulai'] ?? '');
   $selesai = ssTgl($in['tanggal_selesai'] ?? '');
   if (!$ts) responseJson('error', 'Tanggal surat wajib diisi dengan benar.', [], 422);
   if (!$mulai || !$selesai) responseJson('error', 'Tanggal mulai dan selesai istirahat wajib diisi.', [], 422);
   if ($selesai < $mulai) responseJson('error', 'Tanggal selesai tidak boleh sebelum tanggal mulai.', [], 422);
   $lama = (int) (new DateTime($mulai))->diff(new DateTime($selesai))->days + 1;
   if ($lama > 366) responseJson('error', 'Lama istirahat maksimal 1 tahun.', [], 422);
   return [$ts, $mulai, $selesai, $lama];
}

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         if (isset($_GET['id']) && $_GET['id'] !== '') {
            $r = ssQ(SS_SELECT . " WHERE ss.id = ? LIMIT 1", 'si', [$id_customer, (int) $_GET['id']])[0] ?? null;
            if (!$r) responseJson('error', 'Data surat tidak ditemukan.', [], 404);
            $s = nsSetting($koneksi, $id_customer, 'sakit');
            $r['mode_nomor'] = $s['mode'];
            responseJson('success', 'Data surat berhasil diambil.', $r);
         }
         $sampai = ssTgl($_GET['sampai'] ?? '') ?? date('Y-m-d');
         $dari = ssTgl($_GET['dari'] ?? '') ?? date('Y-m-d', strtotime('-29 days'));
         if ($dari > $sampai) [$dari, $sampai] = [$sampai, $dari];
         $rows = ssQ(SS_SELECT . " WHERE ss.tanggal_surat BETWEEN ? AND ? ORDER BY ss.tanggal_surat DESC, ss.id DESC LIMIT 5000", 'sss', [$id_customer, $dari, $sampai]);
         foreach ($rows as &$r) { $r['id'] = (int) $r['id']; $r['lama'] = (int) $r['lama']; }
         unset($r);
         responseJson('success', 'Data surat berhasil diambil.', $rows);

      case 'POST':
         $idPatient = trim((string) ($_POST['id_patient'] ?? ''));
         $idVisit = trim((string) ($_POST['id_visit'] ?? ''));
         if ($idPatient === '' || $idVisit === '') responseJson('error', 'Pilih pasien / kunjungan terlebih dahulu.', [], 422);
         [$ts, $mulai, $selesai, $lama] = ssValidasiTanggal($_POST);
         $ket = trim((string) ($_POST['keterangan'] ?? ''));
         if (!ssQ("SELECT 1 FROM pasien_visit WHERE id_visit = ? AND id_patient = ? AND id_customer = ? LIMIT 1", 'sss', [$idVisit, $idPatient, $id_customer]))
            responseJson('error', 'Kunjungan pasien tidak valid atau bukan milik faskes ini.', [], 404);

         $koneksi->begin_transaction();
         $n = nsAlokasi($koneksi, $id_customer, 'sakit', (string) ($_POST['nomor_surat'] ?? ''), fn($no) => ssNomorDipakai($no));
         if ($n['error']) {
            $koneksi->rollback();
            responseJson($n['kode'] ?? 'error', $n['error'], $n['kode'] ? ['redirect' => 'module/admin/setting_surat'] : [], $n['kode'] ? 409 : 422);
         }
         ssExec("INSERT INTO surat_sakit (id_visit, id_patient, nomor_surat, tanggal_surat, tanggal_mulai, tanggal_selesai, lama, keterangan, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", 'ssssssiss', [$idVisit, $idPatient, $n['nomor'], $ts, $mulai, $selesai, $lama, $ket, $PENGGUNA]);
         $idBaru = (int) $koneksi->insert_id;
         nsSimpan($koneksi, $n);
         $koneksi->commit();
         responseJson('success', 'Surat keterangan sakit ' . $n['nomor'] . ' tersimpan.', ['id' => $idBaru, 'nomor_surat' => $n['nomor'], 'lama' => $lama, 'mode_nomor' => $n['mode']]);

      case 'PUT':
         parse_str((string) file_get_contents('php://input'), $put);
         $id = (int) ($put['id'] ?? $_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         [$ts, $mulai, $selesai, $lama] = ssValidasiTanggal($put);
         $ket = trim((string) ($put['keterangan'] ?? ''));
         $lama_ = ssQ(SS_SELECT . " WHERE ss.id = ? LIMIT 1", 'si', [$id_customer, $id])[0] ?? null;
         if (!$lama_) responseJson('error', 'Data surat tidak ditemukan.', [], 404);
         $nomor = $lama_['nomor_surat'];
         $s = nsSetting($koneksi, $id_customer, 'sakit');
         // mode manual: nomor boleh dikoreksi; mode otomatis: nomor tetap
         if ($s['mode'] === 'MANUAL' && isset($put['nomor_surat'])) {
            $nomorBaru = trim((string) $put['nomor_surat']);
            if ($nomorBaru === '') responseJson('error', 'Nomor surat wajib diisi (mode penomoran manual).', [], 422);
            if ($nomorBaru !== $nomor && ssNomorDipakai($nomorBaru, $id)) responseJson('error', "Nomor surat $nomorBaru sudah dipakai surat lain.", [], 409);
            $nomor = $nomorBaru;
         }
         ssExec("UPDATE surat_sakit ss JOIN pasien_visit pv ON pv.id_visit = ss.id_visit
            SET ss.nomor_surat = ?, ss.tanggal_surat = ?, ss.tanggal_mulai = ?, ss.tanggal_selesai = ?, ss.lama = ?, ss.keterangan = ?, ss.updated_at = NOW(), ss.updated_by = ?
            WHERE ss.id = ? AND pv.id_customer = ?", 'ssssissis', [$nomor, $ts, $mulai, $selesai, $lama, $ket, $PENGGUNA, $id, $id_customer]);
         responseJson('success', 'Surat keterangan sakit diperbarui.', ['id' => $id, 'nomor_surat' => $nomor, 'lama' => $lama]);

      case 'DELETE':
         $id = (int) ($_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         $n = ssExec("DELETE ss FROM surat_sakit ss JOIN pasien_visit pv ON pv.id_visit = ss.id_visit WHERE ss.id = ? AND pv.id_customer = ?", 'is', [$id, $id_customer]);
         $n > 0 ? responseJson('success', 'Surat keterangan sakit dihapus.') : responseJson('error', 'Data tidak ditemukan atau tidak memiliki akses.', [], 404);

      default:
         responseJson('error', 'Method tidak diizinkan.', [], 405);
   }
} catch (Throwable $e) {
   try { $koneksi->rollback(); } catch (Throwable $x) {}
   error_log('[suratSakitController] ' . $e->getMessage());
   responseJson('error', 'Terjadi kesalahan server. Coba lagi.', [], 500);
}
