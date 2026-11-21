<?php
/**
 * Surat Keterangan Sehat (module/letter/form-sks).
 *
 * GET  ?dari&sampai (tanggal_surat, bawaan 30 hari terakhir) -> {data:[...]}
 * GET  ?id=                                                    -> satu surat
 * POST id_patient, id_visit, tanggal_surat, tekanan_darah, nadi, berat_badan, tinggi_badan, keperluan, keterangan,
 *      nomor_surat (mode manual)
 * PUT  ?id=  field yang sama (pasien/kunjungan tidak bisa diganti), nomor_surat hanya dipakai di mode manual
 * DELETE ?id=
 * surat_sehat menyimpan id_customer sendiri -> semua akses dibatasi ss.id_customer = faskes sesi.
 * Suhu & BMI tidak punya kolom di surat_sehat: halaman cetak mengambil suhu dari pasien_visit.
 *
 * Perbaikan: nomor otomatis lewat nomorSurat.php (baris setting dikunci + cek duplikat; dulu nomor otomatis tidak
 * pernah dicek duplikat), daftar dibatasi rentang tanggal & kolom eksplisit (dulu seluruh riwayat + ss.*), validasi
 * tanggal / tanda vital / keperluan di server, PUT tidak lagi bisa memindahkan surat ke pasien/kunjungan lain,
 * kode HTTP benar, dan pesan error tidak lagi berisi detail SQL.
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
function shQ(string $sql, string $t, array $p): array
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
function shExec(string $sql, string $t, array $p): int
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
function shTgl($v): ?string
{
   $v = trim((string) $v);
   if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return null;
   [$y, $m, $d] = array_map('intval', explode('-', $v));
   return checkdate($m, $d, $y) ? $v : null;
}

if (!$koneksi) responseJson('error', 'Koneksi database gagal.', [], 500);
$id_customer = (string) ($_SESSION['id_customer'] ?? '');
// sama dengan versi lama: created_by/updated_by berisi nama pengguna
$PENGGUNA = (string) ($_SESSION['id_user'] ?? $_SESSION['fullname'] ?? $_SESSION['username'] ?? 'system');
session_write_close();
if ($id_customer === '') responseJson('error', 'Session faskes tidak ditemukan.', [], 401);

const SH_SELECT = "SELECT ss.id, ss.id_visit, ss.id_patient, ss.nomor_surat, ss.tanggal_surat, ss.tekanan_darah, ss.nadi,
      ss.berat_badan, ss.tinggi_badan, ss.keperluan, ss.keterangan, ss.created_at, ss.created_by, ss.updated_at, ss.updated_by,
      mp.patient_name, mp.nomor_rm, mp.patient_nik, mp.patient_datebirth, mp.patient_gender,
      pv.id_doctor, pv.visit_ID, pv.visit_date, pv.id_poli, pv.suhu, pv.respirasi, pv.saturasi
   FROM surat_sehat ss
   LEFT JOIN pasien_visit pv ON pv.id_visit = ss.id_visit AND pv.id_customer = ss.id_customer
   LEFT JOIN ms_patient mp ON mp.id_patient = ss.id_patient
   WHERE ss.id_customer = ?";

/** Nomor surat sudah dipakai surat sehat lain di faskes ini? */
function shNomorDipakai(string $nomor, int $kecuali = 0): bool
{
   global $id_customer;
   return (bool) shQ("SELECT id FROM surat_sehat WHERE id_customer = ? AND nomor_surat = ? AND id <> ? LIMIT 1", 'ssi', [$id_customer, $nomor, $kecuali]);
}

/** Angka dari input ("65", "65,5", "65 kg") dalam rentang; '' bila kosong; balas 422 bila tidak valid. */
function shAngka(array $in, string $kol, string $label, float $min, float $max, string $satuan): string
{
   $v = trim((string) ($in[$kol] ?? ''));
   if ($v === '') return '';
   if (!preg_match('/^(\d{1,3}(?:[.,]\d{1,2})?)\s*(?:' . preg_quote($satuan, '/') . ')?$/i', $v, $m)) responseJson('error', "$label harus berupa angka.", [], 422);
   $n = (float) str_replace(',', '.', $m[1]);
   if ($n < $min || $n > $max) responseJson('error', "$label harus di antara " . $min . '–' . $max . " $satuan.", [], 422);
   return rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
}

/** Validasi field isi surat -> [tanggal_surat, td, nadi, bb, tb, keperluan, keterangan] atau balas 422. */
function shValidasi(array $in): array
{
   $ts = shTgl($in['tanggal_surat'] ?? '');
   if (!$ts) responseJson('error', 'Tanggal surat wajib diisi dengan benar.', [], 422);
   $td = trim((string) ($in['tekanan_darah'] ?? ''));
   if ($td !== '') {
      if (!preg_match('#^(\d{2,3})\s*/\s*(\d{2,3})\s*(?:mmhg)?$#i', $td, $m)) responseJson('error', 'Tekanan darah harus berformat sistolik/diastolik, mis. 120/80.', [], 422);
      [$sis, $dia] = [(int) $m[1], (int) $m[2]];
      if ($sis < 50 || $sis > 300 || $dia < 20 || $dia > 200 || $dia >= $sis) responseJson('error', 'Nilai tekanan darah tidak wajar.', [], 422);
      $td = "$sis/$dia";
   }
   $nadi = shAngka($in, 'nadi', 'Nadi', 20, 250, 'x/menit');
   $bb = shAngka($in, 'berat_badan', 'Berat badan', 1, 400, 'kg');
   $tb = shAngka($in, 'tinggi_badan', 'Tinggi badan', 30, 250, 'cm');
   $kep = trim((string) ($in['keperluan'] ?? ''));
   if ($kep === '') responseJson('error', 'Keperluan surat wajib diisi.', [], 422);
   if (mb_strlen($kep) > 100) responseJson('error', 'Keperluan maksimal 100 karakter.', [], 422);
   $ket = trim((string) ($in['keterangan'] ?? ''));
   if (mb_strlen($ket) > 1000) responseJson('error', 'Keterangan maksimal 1000 karakter.', [], 422);
   return [$ts, $td, $nadi, $bb, $tb, $kep, $ket];
}

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         if (isset($_GET['id']) && $_GET['id'] !== '') {
            $r = shQ(SH_SELECT . " AND ss.id = ? LIMIT 1", 'si', [$id_customer, (int) $_GET['id']])[0] ?? null;
            if (!$r) responseJson('error', 'Data surat tidak ditemukan.', [], 404);
            $r['id'] = (int) $r['id'];
            $r['mode_nomor'] = nsSetting($koneksi, $id_customer, 'sehat')['mode'];
            responseJson('success', 'Data surat berhasil diambil.', $r);
         }
         $sampai = shTgl($_GET['sampai'] ?? '') ?? date('Y-m-d');
         $dari = shTgl($_GET['dari'] ?? '') ?? date('Y-m-d', strtotime('-29 days'));
         if ($dari > $sampai) [$dari, $sampai] = [$sampai, $dari];
         $rows = shQ(SH_SELECT . " AND ss.tanggal_surat BETWEEN ? AND ? ORDER BY ss.tanggal_surat DESC, ss.id DESC LIMIT 5000", 'sss', [$id_customer, $dari, $sampai]);
         foreach ($rows as &$r) $r['id'] = (int) $r['id'];
         unset($r);
         responseJson('success', 'Data surat berhasil diambil.', $rows);

      case 'POST':
         $idPatient = trim((string) ($_POST['id_patient'] ?? ''));
         $idVisit = trim((string) ($_POST['id_visit'] ?? ''));
         if ($idPatient === '' || $idVisit === '') responseJson('error', 'Pilih pasien / kunjungan terlebih dahulu.', [], 422);
         [$ts, $td, $nadi, $bb, $tb, $kep, $ket] = shValidasi($_POST);
         if (!shQ("SELECT 1 FROM pasien_visit WHERE id_visit = ? AND id_patient = ? AND id_customer = ? LIMIT 1", 'sss', [$idVisit, $idPatient, $id_customer]))
            responseJson('error', 'Kunjungan pasien tidak valid atau bukan milik faskes ini.', [], 404);

         $koneksi->begin_transaction();
         $n = nsAlokasi($koneksi, $id_customer, 'sehat', (string) ($_POST['nomor_surat'] ?? ''), fn($no) => shNomorDipakai($no));
         if ($n['error']) {
            $koneksi->rollback();
            responseJson($n['kode'] ?? 'error', $n['error'], $n['kode'] ? ['redirect' => 'module/admin/setting_surat'] : [], $n['kode'] ? 409 : 422);
         }
         shExec("INSERT INTO surat_sehat (id_customer, id_visit, id_patient, nomor_surat, tanggal_surat, tekanan_darah, nadi, berat_badan, tinggi_badan, keperluan, keterangan, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 'ssssssssssss', [$id_customer, $idVisit, $idPatient, $n['nomor'], $ts, $td, $nadi, $bb, $tb, $kep, $ket, $PENGGUNA]);
         $idBaru = (int) $koneksi->insert_id;
         nsSimpan($koneksi, $n);
         $koneksi->commit();
         responseJson('success', 'Surat keterangan sehat ' . $n['nomor'] . ' tersimpan.', ['id' => $idBaru, 'id_customer' => $id_customer, 'nomor_surat' => $n['nomor'], 'mode_nomor' => $n['mode']]);

      case 'PUT':
         parse_str((string) file_get_contents('php://input'), $put);
         $id = (int) ($put['id'] ?? $_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         [$ts, $td, $nadi, $bb, $tb, $kep, $ket] = shValidasi($put);
         $lama = shQ("SELECT id, nomor_surat FROM surat_sehat WHERE id = ? AND id_customer = ? LIMIT 1", 'is', [$id, $id_customer])[0] ?? null;
         if (!$lama) responseJson('error', 'Data surat tidak ditemukan.', [], 404);
         $nomor = (string) $lama['nomor_surat'];
         $s = nsSetting($koneksi, $id_customer, 'sehat');
         // mode manual: nomor boleh dikoreksi; mode otomatis / tanpa setting: nomor tetap
         if ($s['mode'] === 'MANUAL' && isset($put['nomor_surat'])) {
            $nomorBaru = trim((string) $put['nomor_surat']);
            if ($nomorBaru === '') responseJson('error', 'Nomor surat wajib diisi (mode penomoran manual).', [], 422);
            if (mb_strlen($nomorBaru) > 100) responseJson('error', 'Nomor surat maksimal 100 karakter.', [], 422);
            if ($nomorBaru !== $nomor && shNomorDipakai($nomorBaru, $id)) responseJson('error', "Nomor surat $nomorBaru sudah dipakai surat lain.", [], 409);
            $nomor = $nomorBaru;
         }
         shExec("UPDATE surat_sehat SET nomor_surat = ?, tanggal_surat = ?, tekanan_darah = ?, nadi = ?, berat_badan = ?, tinggi_badan = ?,
               keperluan = ?, keterangan = ?, updated_at = NOW(), updated_by = ?
            WHERE id = ? AND id_customer = ?", 'sssssssssis', [$nomor, $ts, $td, $nadi, $bb, $tb, $kep, $ket, $PENGGUNA, $id, $id_customer]);
         responseJson('success', 'Surat keterangan sehat diperbarui.', ['id' => $id, 'nomor_surat' => $nomor, 'mode_nomor' => $s['mode']]);

      case 'DELETE':
         $id = (int) ($_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         $n = shExec("DELETE FROM surat_sehat WHERE id = ? AND id_customer = ?", 'is', [$id, $id_customer]);
         $n > 0 ? responseJson('success', 'Surat keterangan sehat dihapus.') : responseJson('error', 'Data tidak ditemukan atau tidak memiliki akses.', [], 404);

      default:
         responseJson('error', 'Method tidak diizinkan.', [], 405);
   }
} catch (Throwable $e) {
   try { $koneksi->rollback(); } catch (Throwable $x) {}
   error_log('[suratSehatController] ' . $e->getMessage());
   responseJson('error', 'Terjadi kesalahan server. Coba lagi.', [], 500);
}
