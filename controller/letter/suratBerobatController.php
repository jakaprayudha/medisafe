<?php
/**
 * Surat Keterangan Berobat (module/letter/form-medical).
 *
 * GET  ?dari&sampai (tanggal_surat, bawaan 30 hari terakhir) -> daftar ringkas
 * GET  ?id=                                                    -> satu surat (+ detail kunjungan untuk form ubah)
 * GET  ?id_visit=                                              -> detail kunjungan yang akan tercetak + surat berobat
 *                                                                 lain untuk kunjungan itu (peringatan dobel)
 * POST id_patient, id_visit, tanggal_surat, keterangan, nomor_surat (mode manual)
 * PUT  ?id=  tanggal_surat, keterangan, nomor_surat (mode manual; mode otomatis nomor tetap)
 * DELETE ?id=
 * surat_berobat menyimpan id_customer sendiri; semua query dibatasi sb.id_customer = faskes session, dan
 * kunjungan juga harus milik faskes yang sama.
 *
 * Perbaikan dari versi lama: generator nomor otomatis membaca kunci setting yang tidak ada (format_nomor /
 * nomor_tertinggi) sehingga format di pengaturan diabaikan & nomor_berobat tidak pernah naik, dan nomor dihitung dari
 * angka di belakang 1.000 surat terakhir (bisa bentrok bila dua simpan bersamaan); sekarang lewat nomorSurat.php
 * (baris setting dikunci dalam transaksi). Daftar dulu memuat seluruh riwayat (SELECT sb.*), pesan error berisi
 * detail SQL, semua error dibalas HTTP 200, tanggal tidak divalidasi, nomor manual tidak bisa dikoreksi saat ubah.
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
function sbQ(string $sql, string $t, array $p): array
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
function sbExec(string $sql, string $t, array $p): int
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
function sbTgl($v): ?string
{
   $v = trim((string) $v);
   if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return null;
   [$y, $m, $d] = array_map('intval', explode('-', $v));
   return checkdate($m, $d, $y) ? $v : null;
}

if (!$koneksi) responseJson('error', 'Koneksi database gagal.', [], 500);
$id_customer = (string) ($_SESSION['id_customer'] ?? '');
// urutan sama dengan versi lama (created_by berisi uid_user)
$PENGGUNA = (string) ($_SESSION['uid_user'] ?? $_SESSION['id_user'] ?? $_SESSION['username'] ?? 'system');
session_write_close();
if ($id_customer === '') responseJson('error', 'Session faskes/customer tidak ditemukan.', [], 401);

const SB_KET_MAKS = 2000;
const SB_SELECT = "SELECT sb.id, sb.id_visit, sb.id_patient, sb.nomor_surat, sb.tanggal_surat, sb.keterangan, sb.created_at, sb.created_by,
      mp.patient_name, mp.nomor_rm, mp.patient_nik, mp.patient_bpjs, mp.patient_datebirth, mp.patient_gender,
      pv.id_doctor, pv.id_poli, pv.visit_ID, pv.visit_date, pv.visit_time
   FROM surat_berobat sb
   LEFT JOIN pasien_visit pv ON pv.id_visit = sb.id_visit AND pv.id_customer = sb.id_customer
   LEFT JOIN ms_patient mp ON mp.id_patient = sb.id_patient";
// kolom kunjungan tambahan (tanda vital, diagnosa yang tercetak) untuk form
const SB_KUNJUNGAN = "pv.tekanan_darah, pv.suhu, pv.nadi, pv.respirasi, pv.saturasi, pv.visit_notes, pv.kdDiag1, pv.nmDiag1, pv.diagnosa,
      (SELECT cd.icd10 FROM icd_10 cd WHERE cd.code = pv.diagnosa LIMIT 1) AS nama_diagnosa";

/** Nomor surat sudah dipakai surat berobat lain di faskes ini? */
function sbNomorDipakai(string $nomor, int $kecuali = 0): bool
{
   global $id_customer;
   return (bool) sbQ("SELECT id FROM surat_berobat WHERE id_customer = ? AND nomor_surat = ? AND id <> ? LIMIT 1", 'ssi', [$id_customer, $nomor, $kecuali]);
}

/** Validasi field isi -> [tanggal_surat, keterangan] atau balas error. */
function sbValidasi(array $in, ?string $tglKunjungan): array
{
   $ts = sbTgl($in['tanggal_surat'] ?? '');
   if (!$ts) responseJson('error', 'Tanggal surat wajib diisi dengan benar.', [], 422);
   if ($tglKunjungan && $ts < substr($tglKunjungan, 0, 10)) responseJson('error', 'Tanggal surat tidak boleh sebelum tanggal kunjungan (' . substr($tglKunjungan, 0, 10) . ').', [], 422);
   $ket = trim(str_replace("\r\n", "\n", (string) ($in['keterangan'] ?? '')));
   if (mb_strlen($ket) > SB_KET_MAKS) responseJson('error', 'Keterangan maksimal ' . SB_KET_MAKS . ' karakter.', [], 422);
   return [$ts, $ket];
}

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         if (isset($_GET['id']) && $_GET['id'] !== '') {
            $r = sbQ(str_replace('pv.visit_time', 'pv.visit_time, ' . SB_KUNJUNGAN, SB_SELECT) . " WHERE sb.id = ? AND sb.id_customer = ? LIMIT 1", 'is', [(int) $_GET['id'], $id_customer])[0] ?? null;
            if (!$r) responseJson('error', 'Data surat tidak ditemukan.', [], 404);
            $r['mode_nomor'] = nsSetting($koneksi, $id_customer, 'berobat')['mode'];
            responseJson('success', 'Data surat berhasil diambil.', $r);
         }
         if (isset($_GET['id_visit']) && $_GET['id_visit'] !== '') {
            $v = sbQ("SELECT pv.id_visit, pv.id_patient, pv.visit_date, pv.visit_time, pv.id_doctor, pv.id_poli, " . SB_KUNJUNGAN . "
               FROM pasien_visit pv WHERE pv.id_visit = ? AND pv.id_customer = ? LIMIT 1", 'is', [(int) $_GET['id_visit'], $id_customer])[0] ?? null;
            if (!$v) responseJson('error', 'Kunjungan tidak ditemukan.', [], 404);
            $v['surat'] = sbQ("SELECT id, nomor_surat, tanggal_surat FROM surat_berobat WHERE id_customer = ? AND id_visit = ? ORDER BY id DESC LIMIT 5", 'ss', [$id_customer, (string) $v['id_visit']]);
            responseJson('success', 'Data kunjungan berhasil diambil.', $v);
         }
         $sampai = sbTgl($_GET['sampai'] ?? '') ?? date('Y-m-d');
         $dari = sbTgl($_GET['dari'] ?? '') ?? date('Y-m-d', strtotime('-29 days'));
         if ($dari > $sampai) [$dari, $sampai] = [$sampai, $dari];
         $rows = sbQ(SB_SELECT . " WHERE sb.id_customer = ? AND sb.tanggal_surat BETWEEN ? AND ? ORDER BY sb.tanggal_surat DESC, sb.id DESC LIMIT 5000", 'sss', [$id_customer, $dari, $sampai]);
         foreach ($rows as &$r) $r['id'] = (int) $r['id'];
         unset($r);
         responseJson('success', 'Data surat berhasil diambil.', $rows);

      case 'POST':
         $idPatient = trim((string) ($_POST['id_patient'] ?? ''));
         $idVisit = trim((string) ($_POST['id_visit'] ?? ''));
         if ($idPatient === '' || $idVisit === '') responseJson('error', 'Pilih pasien / kunjungan terlebih dahulu.', [], 422);
         $v = sbQ("SELECT visit_date FROM pasien_visit WHERE id_visit = ? AND id_patient = ? AND id_customer = ? LIMIT 1", 'sss', [$idVisit, $idPatient, $id_customer])[0] ?? null;
         if (!$v) responseJson('error', 'Kunjungan pasien tidak valid atau bukan milik faskes ini.', [], 404);
         [$ts, $ket] = sbValidasi($_POST, $v['visit_date'] ?? null);

         $koneksi->begin_transaction();
         $n = nsAlokasi($koneksi, $id_customer, 'berobat', (string) ($_POST['nomor_surat'] ?? ''), fn($no) => sbNomorDipakai($no));
         if ($n['error']) {
            $koneksi->rollback();
            responseJson($n['kode'] ?? 'error', $n['error'], $n['kode'] ? ['redirect' => 'module/admin/setting_surat'] : [], $n['kode'] ? 409 : 422);
         }
         sbExec("INSERT INTO surat_berobat (id_visit, id_patient, id_customer, nomor_surat, tanggal_surat, keterangan, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)", 'sssssss', [$idVisit, $idPatient, $id_customer, $n['nomor'], $ts, $ket, $PENGGUNA]);
         $idBaru = (int) $koneksi->insert_id;
         nsSimpan($koneksi, $n);
         $koneksi->commit();
         responseJson('success', 'Surat keterangan berobat ' . $n['nomor'] . ' tersimpan.', ['id' => $idBaru, 'id_customer' => $id_customer, 'nomor_surat' => $n['nomor'], 'mode_nomor' => $n['mode']]);

      case 'PUT':
         parse_str((string) file_get_contents('php://input'), $put);
         $id = (int) ($put['id'] ?? $_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         $lama = sbQ(SB_SELECT . " WHERE sb.id = ? AND sb.id_customer = ? LIMIT 1", 'is', [$id, $id_customer])[0] ?? null;
         if (!$lama) responseJson('error', 'Data surat tidak ditemukan.', [], 404);
         [$ts, $ket] = sbValidasi($put, $lama['visit_date'] ?? null);
         $nomor = (string) $lama['nomor_surat'];
         // mode manual: nomor boleh dikoreksi; mode otomatis: nomor tetap
         if (nsSetting($koneksi, $id_customer, 'berobat')['mode'] === 'MANUAL' && isset($put['nomor_surat'])) {
            $nomorBaru = trim((string) $put['nomor_surat']);
            if ($nomorBaru === '') responseJson('error', 'Nomor surat wajib diisi (mode penomoran manual).', [], 422);
            if (mb_strlen($nomorBaru) > 100) responseJson('error', 'Nomor surat maksimal 100 karakter.', [], 422);
            if ($nomorBaru !== $nomor && sbNomorDipakai($nomorBaru, $id)) responseJson('error', "Nomor surat $nomorBaru sudah dipakai surat lain.", [], 409);
            $nomor = $nomorBaru;
         }
         sbExec("UPDATE surat_berobat SET nomor_surat = ?, tanggal_surat = ?, keterangan = ?, updated_at = NOW(), updated_by = ?
            WHERE id = ? AND id_customer = ?", 'ssssis', [$nomor, $ts, $ket, $PENGGUNA, $id, $id_customer]);
         responseJson('success', 'Surat keterangan berobat diperbarui.', ['id' => $id, 'nomor_surat' => $nomor]);

      case 'DELETE':
         $id = (int) ($_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         $n = sbExec("DELETE FROM surat_berobat WHERE id = ? AND id_customer = ?", 'is', [$id, $id_customer]);
         $n > 0 ? responseJson('success', 'Surat keterangan berobat dihapus.') : responseJson('error', 'Data tidak ditemukan atau tidak memiliki akses.', [], 404);

      default:
         responseJson('error', 'Method tidak diizinkan.', [], 405);
   }
} catch (Throwable $e) {
   try { $koneksi->rollback(); } catch (Throwable $x) {}
   error_log('[suratBerobatController] ' . $e->getMessage());
   responseJson('error', 'Terjadi kesalahan server. Coba lagi.', [], 500);
}
