<?php
/**
 * Surat Hasil Pemeriksaan Mata (module/letter/form-eye).
 *
 * GET  ?dari&sampai (tanggal_surat, bawaan 30 hari terakhir) -> {data:[ringkas]}
 * GET  ?id=                                                    -> satu surat (semua kolom)
 * GET  ?check_setting=1                                        -> setting nomor (kompatibel versi lama)
 * POST id_patient, id_visit, tanggal_surat, nomor_surat (mode manual), + field MATA_FIELD
 * PUT  ?id=  tanggal_surat, nomor_surat (mode manual), + field MATA_FIELD (field yang tidak dikirim tidak diubah)
 * DELETE ?id=
 * Semua akses dibatasi faskes sesi: spm.id_customer DAN pasien_visit.id_customer.
 *
 * Perbaikan dari versi lama: PUT boleh mengganti id_visit/id_patient tanpa cek faskes (surat bisa dipindah ke
 * kunjungan faskes lain), nomor manual & otomatis tidak dicek duplikat, nomor otomatis lewat nomorSurat.php
 * (setting dikunci, nomor terpakai dilewati), mode manual kini bisa mengoreksi nomor saat ubah, daftar dibatasi
 * rentang tanggal (dulu seluruh riwayat + spm.*), JOIN dokter salah kolom (doctor_code vs nama dokter di
 * pasien_visit.id_doctor), validasi tanggal/panjang/format nilai, kode HTTP benar, pesan error tanpa detail SQL.
 */
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../database/connect.php';
require_once __DIR__ . '/nomorSurat.php';
require_once __DIR__ . '/suratMataAturan.php';
header('Content-Type: application/json; charset=utf-8');

function responseJson(string $status, string $message = '', array $data = [], int $kode = 200, array $ekstra = []): void
{
   http_response_code($kode);
   if ($kode === 200 && $_SERVER['REQUEST_METHOD'] === 'GET' && !ini_get('zlib.output_compression') && extension_loaded('zlib')) ob_start('ob_gzhandler');
   echo json_encode(['status' => $status, 'message' => $message, 'data' => $data] + $ekstra, JSON_UNESCAPED_UNICODE);
   exit;
}
function smQ(string $sql, string $t, array $p): array
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
function smExec(string $sql, string $t, array $p): int
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
function smTgl($v): ?string
{
   $v = trim((string) $v);
   if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return null;
   [$y, $m, $d] = array_map('intval', explode('-', $v));
   return checkdate($m, $d, $y) ? $v : null;
}
/**
 * Ambil & validasi field data dari input. $semua=true (POST): field tak dikirim = ''. PUT: hanya field yang dikirim.
 * @return array<string,string>
 */
function smField(array $in, bool $semua): array
{
   $out = [];
   $salah = [];
   foreach (MATA_FIELD as $f => [$maks, $aturan, $label]) {
      if (!array_key_exists($f, $in)) { if ($semua) $out[$f] = ''; continue; }
      if (!is_scalar($in[$f])) responseJson('error', "Isian $label tidak valid.", [], 422);
      $v = trim(str_replace("\r\n", "\n", (string) $in[$f]));
      if (mb_strlen($v) > $maks) responseJson('error', "Isian $label maksimal $maks karakter.", [], 422);
      if (!smCocok($v, $aturan)) $salah[] = $label;
      $out[$f] = $v;
   }
   if ($salah) responseJson('error', 'Format tidak valid: ' . implode(', ', $salah) . '.', [], 422);
   return $out;
}

if (!$koneksi) responseJson('error', 'Koneksi database gagal.', [], 500);
$id_customer = (string) ($_SESSION['id_customer'] ?? '');
$PENGGUNA = (string) ($_SESSION['uid_user'] ?? $_SESSION['username'] ?? 'system');
session_write_close();
if ($id_customer === '') responseJson('error', 'Session faskes tidak ditemukan.', [], 401);

const SM_DARI = " FROM surat_pemeriksaan_mata spm
   JOIN pasien_visit pv ON pv.id_visit = spm.id_visit AND pv.id_customer = ?
   LEFT JOIN ms_patient mp ON mp.id_patient = spm.id_patient
   WHERE spm.id_customer = ?";
const SM_PASIEN = "mp.patient_name, mp.nomor_rm, mp.patient_nik, mp.patient_datebirth, mp.patient_gender, pv.id_doctor, pv.visit_ID, pv.visit_date";

/** Nomor surat sudah dipakai surat mata lain di faskes ini? */
function smNomorDipakai(string $nomor, int $kecuali = 0): bool
{
   global $id_customer;
   return (bool) smQ("SELECT id FROM surat_pemeriksaan_mata WHERE id_customer = ? AND nomor_surat = ? AND id <> ? LIMIT 1", 'ssi', [$id_customer, $nomor, $kecuali]);
}
function smAdaIsi(array $f): bool
{
   foreach (MATA_INTI as $k) if (($f[$k] ?? '') !== '') return true;
   return false;
}
function smSatu(int $id): ?array
{
   global $id_customer;
   $kol = 'spm.id, spm.id_visit, spm.id_patient, spm.nomor_surat, spm.tanggal_surat, spm.' . implode(', spm.', array_keys(MATA_FIELD))
      . ', spm.created_at, spm.created_by, spm.updated_at, spm.updated_by, ' . SM_PASIEN . ', mp.patient_place, mp.patient_address';
   return smQ("SELECT $kol" . SM_DARI . " AND spm.id = ? LIMIT 1", 'ssi', [$id_customer, $id_customer, $id])[0] ?? null;
}

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         if (($_GET['check_setting'] ?? '') === '1') {
            $s = nsSetting($koneksi, $id_customer, 'mata');
            if (!$s['ada']) responseJson('setting_required', 'Setting nomor surat belum dibuat.', ['redirect' => 'module/admin/setting_surat']);
            responseJson('success', '', ['mode_nomor' => $s['mode'], 'format_mata' => $s['format'], 'nomor_mata' => $s['terakhir']]);
         }
         if (isset($_GET['id']) && $_GET['id'] !== '') {
            $r = smSatu((int) $_GET['id']);
            if (!$r) responseJson('error', 'Data surat tidak ditemukan.', [], 404);
            $r['mode_nomor'] = nsSetting($koneksi, $id_customer, 'mata')['mode'];
            responseJson('success', 'Data surat berhasil diambil.', $r);
         }
         $sampai = smTgl($_GET['sampai'] ?? '') ?? date('Y-m-d');
         $dari = smTgl($_GET['dari'] ?? '') ?? date('Y-m-d', strtotime('-29 days'));
         if ($dari > $sampai) [$dari, $sampai] = [$sampai, $dari];
         $rows = smQ("SELECT spm.id, spm.id_visit, spm.id_patient, spm.nomor_surat, spm.tanggal_surat,
               spm.visus_od_tanpa_koreksi_jauh, spm.visus_od_dengan_koreksi_jauh, spm.visus_os_tanpa_koreksi_jauh, spm.visus_os_dengan_koreksi_jauh,
               LEFT(spm.kesimpulan, 300) AS kesimpulan, " . SM_PASIEN
            . SM_DARI . " AND spm.tanggal_surat BETWEEN ? AND ? ORDER BY spm.tanggal_surat DESC, spm.id DESC LIMIT 5000",
            'ssss', [$id_customer, $id_customer, $dari, $sampai]);
         foreach ($rows as &$r) $r['id'] = (int) $r['id'];
         unset($r);
         responseJson('success', 'Data surat berhasil diambil.', $rows);

      case 'POST':
         $idPatient = trim((string) ($_POST['id_patient'] ?? ''));
         $idVisit = trim((string) ($_POST['id_visit'] ?? ''));
         if ($idPatient === '' || $idVisit === '') responseJson('error', 'Pasien dan kunjungan wajib dipilih.', [], 422);
         $ts = smTgl($_POST['tanggal_surat'] ?? '');
         if (!$ts) responseJson('error', 'Tanggal surat wajib diisi dengan benar.', [], 422);
         $f = smField($_POST, true);
         if (!smAdaIsi($f)) responseJson('error', 'Isi minimal satu hasil pemeriksaan mata (visus, refraksi, TIO, segmen, atau kesimpulan).', [], 422);
         if (!smQ("SELECT 1 FROM pasien_visit WHERE id_visit = ? AND id_patient = ? AND id_customer = ? LIMIT 1", 'sss', [$idVisit, $idPatient, $id_customer]))
            responseJson('error', 'Kunjungan pasien tidak valid atau bukan milik faskes ini.', [], 404);

         $koneksi->begin_transaction();
         $n = nsAlokasi($koneksi, $id_customer, 'mata', (string) ($_POST['nomor_surat'] ?? ''), fn($no) => smNomorDipakai($no));
         if ($n['error']) {
            $koneksi->rollback();
            responseJson($n['kode'] ?? 'error', $n['error'], $n['kode'] ? ['redirect' => 'module/admin/setting_surat'] : [], $n['kode'] ? 409 : 422);
         }
         $kolom = array_merge(['id_visit', 'id_patient', 'nomor_surat', 'tanggal_surat'], array_keys($f), ['id_customer', 'created_by']);
         $nilai = array_merge([$idVisit, $idPatient, $n['nomor'], $ts], array_values($f), [$id_customer, $PENGGUNA]);
         smExec('INSERT INTO surat_pemeriksaan_mata (' . implode(', ', $kolom) . ') VALUES (' . implode(', ', array_fill(0, count($kolom), '?')) . ')',
            str_repeat('s', count($kolom)), $nilai);
         $idBaru = (int) $koneksi->insert_id;
         nsSimpan($koneksi, $n);
         $koneksi->commit();
         responseJson('success', 'Surat hasil pemeriksaan mata ' . $n['nomor'] . ' tersimpan.',
            ['id' => $idBaru, 'nomor_surat' => $n['nomor'], 'mode_nomor' => $n['mode']], 200, ['id' => $idBaru, 'nomor_surat' => $n['nomor']]);

      case 'PUT':
         parse_str((string) file_get_contents('php://input'), $put);
         $id = (int) ($put['id'] ?? $_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         $lama = smSatu($id);
         if (!$lama) responseJson('error', 'Data surat tidak ditemukan.', [], 404);
         $ts = array_key_exists('tanggal_surat', $put) ? smTgl($put['tanggal_surat']) : $lama['tanggal_surat'];
         if (!$ts) responseJson('error', 'Tanggal surat wajib diisi dengan benar.', [], 422);
         $f = smField($put, false);
         if (!smAdaIsi($f + $lama)) responseJson('error', 'Isi minimal satu hasil pemeriksaan mata (visus, refraksi, TIO, segmen, atau kesimpulan).', [], 422);
         $nomor = (string) $lama['nomor_surat'];
         // mode manual: nomor boleh dikoreksi; mode otomatis: nomor tetap. Pasien/kunjungan tidak bisa diganti.
         if (nsSetting($koneksi, $id_customer, 'mata')['mode'] === 'MANUAL' && isset($put['nomor_surat'])) {
            $nomorBaru = trim((string) $put['nomor_surat']);
            if ($nomorBaru === '') responseJson('error', 'Nomor surat wajib diisi (mode penomoran manual).', [], 422);
            if (mb_strlen($nomorBaru) > 100) responseJson('error', 'Nomor surat maksimal 100 karakter.', [], 422);
            if ($nomorBaru !== $nomor && smNomorDipakai($nomorBaru, $id)) responseJson('error', "Nomor surat $nomorBaru sudah dipakai surat lain.", [], 409);
            $nomor = $nomorBaru;
         }
         $set = ['spm.nomor_surat = ?', 'spm.tanggal_surat = ?'];
         $nilai = [$nomor, $ts];
         foreach ($f as $k => $v) { $set[] = "spm.`$k` = ?"; $nilai[] = $v; }
         $set[] = 'spm.updated_at = ?'; $nilai[] = date('Y-m-d H:i:s');
         $set[] = 'spm.updated_by = ?'; $nilai[] = $PENGGUNA;
         array_push($nilai, $id, $id_customer, $id_customer);
         smExec('UPDATE surat_pemeriksaan_mata spm JOIN pasien_visit pv ON pv.id_visit = spm.id_visit SET ' . implode(', ', $set)
            . ' WHERE spm.id = ? AND spm.id_customer = ? AND pv.id_customer = ?', str_repeat('s', count($nilai) - 3) . 'iss', $nilai);
         responseJson('success', 'Surat hasil pemeriksaan mata diperbarui.', ['id' => $id, 'nomor_surat' => $nomor]);

      case 'DELETE':
         $id = (int) ($_GET['id'] ?? 0);
         if ($id <= 0) responseJson('error', 'ID surat tidak ditemukan.', [], 422);
         $n = smExec("DELETE spm FROM surat_pemeriksaan_mata spm JOIN pasien_visit pv ON pv.id_visit = spm.id_visit
            WHERE spm.id = ? AND spm.id_customer = ? AND pv.id_customer = ?", 'iss', [$id, $id_customer, $id_customer]);
         $n > 0 ? responseJson('success', 'Surat berhasil dihapus.') : responseJson('error', 'Data tidak ditemukan atau tidak memiliki akses.', [], 404);

      default:
         responseJson('error', 'Method tidak diizinkan.', [], 405);
   }
} catch (Throwable $e) {
   try { $koneksi->rollback(); } catch (Throwable $x) {}
   error_log('[suratPemeriksaanMataController] ' . $e->getMessage());
   responseJson('error', 'Terjadi kesalahan server. Coba lagi.', [], 500);
}
