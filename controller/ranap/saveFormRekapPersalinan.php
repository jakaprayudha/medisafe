<?php
/**
 * Simpan rekapitulasi pelayanan persalinan (tabel visit_persalinan, 1 baris per kunjungan).
 *   POST JSON { visit_ID, gravid, partus, abortus, jenis_persalinan, tarif_paket, ttd?:"data:image/png;base64,..", hapus_ttd? }
 * Perbaikan: semua isian disisipkan ke SQL (SQL injection) & tanpa cek login / batas faskes; nomor RM dari browser
 * (kolom varchar(20): RM lebih panjang -> gagal simpan); G/P/A tanpa validasi (kolom varchar(10)); tarif paket & tanda
 * tangan pasien tidak pernah bisa diisi; pesan error SQL dikirim ke browser.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
define('RP_LIB', true);
require_once __DIR__ . '/getFormRekapPersalinan.php';
header('Content-Type: application/json; charset=utf-8');

function rpBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') rpBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$in = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($in)) rpBalas(400, ['status' => 'error', 'message' => 'Data tidak valid.']);
$kunj = visitGuard($koneksi, 'visit_ID', (string) ($in['visit_ID'] ?? ''));
session_write_close();
if ((int) $kunj['visit_status'] === 99) rpBalas(409, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);

$tolak = fn($p, $f) => rpBalas(422, ['status' => 'error', 'message' => $p, 'field' => $f]);
$t = fn($k, $max) => mb_substr(trim((string) (is_scalar($in[$k] ?? null) ? $in[$k] : '')), 0, $max);
$d = [];
foreach (['gravid' => 'Gravid', 'partus' => 'Partus', 'abortus' => 'Abortus'] as $k => $lbl) {
   $x = $t($k, 10);
   if ($x !== '' && (!ctype_digit($x) || (int) $x > 30)) $tolak("$lbl harus angka 0–30.", $k);
   $d[$k] = $x === '' ? null : (string) (int) $x;
}
if ($d['gravid'] === null) $tolak('Gravid wajib diisi.', 'gravid');
if ((int) $d['gravid'] < 1) $tolak('Gravid minimal 1.', 'gravid');
if ((int) $d['partus'] + (int) $d['abortus'] > (int) $d['gravid']) $tolak('Partus + Abortus tidak boleh melebihi Gravid.', 'partus');
$d['jenis_persalinan'] = $t('jenis_persalinan', 100);
if ($d['jenis_persalinan'] === '') $tolak('Jenis persalinan wajib diisi.', 'jenis_persalinan');
$tarif = preg_replace('/\D/', '', $t('tarif_paket', 50));
if ($tarif !== '' && strlen($tarif) > 12) $tolak('Tarif paket tidak valid.', 'tarif_paket');
$d['tarif_paket'] = $tarif === '' ? null : (string) (int) $tarif;

$root = dirname(__DIR__, 2) . '/uploads/ttd/';
$ttdBaru = null;
try {
   if (is_string($in['ttd'] ?? null) && $in['ttd'] !== '') {
      if (!preg_match('#^data:image/png;base64,(.+)$#s', $in['ttd'], $m)) $tolak('Gambar tanda tangan tidak valid.', 'ttd');
      $bin = base64_decode(str_replace(' ', '+', $m[1]), true);
      $info = $bin ? @getimagesizefromstring($bin) : false;
      if (!$bin || strlen($bin) > 2 * 1024 * 1024 || !$info || $info[2] !== IMAGETYPE_PNG) $tolak('Gambar tanda tangan tidak valid.', 'ttd');
      if (!is_dir($root) && !@mkdir($root, 0755, true) && !is_dir($root)) rpBalas(500, ['status' => 'error', 'message' => 'Folder tanda tangan tidak tersedia.']);
      $ttdBaru = 'ttd_persalinan_' . (int) $kunj['id_visit'] . '_' . bin2hex(random_bytes(5)) . '.png';
      if (@file_put_contents($root . $ttdBaru, $bin, LOCK_EX) === false) rpBalas(500, ['status' => 'error', 'message' => 'Gagal menyimpan tanda tangan.']);
   }

   $src = rpAmbil($koneksi, (int) $kunj['id_visit']);
   $no = (string) $kunj['visit_ID'];
   $rm = mb_substr($src['pasien']['nomor_rm'], 0, 20);   // kolom varchar(20)

   $lama = null;
   $koneksi->begin_transaction();
   try {
      $st = $koneksi->prepare("SELECT id_persalinan, ttd_file FROM visit_persalinan WHERE visit_ID = ? ORDER BY id_persalinan DESC LIMIT 1 FOR UPDATE");
      $st->bind_param('s', $no);
      $st->execute();
      $row = $st->get_result()->fetch_assoc();
      $st->close();
      $ttd = $ttdBaru ?? (!empty($in['hapus_ttd']) ? null : ($row['ttd_file'] ?? null));
      if ($row) {
         $lama = $row['ttd_file'] !== $ttd ? $row['ttd_file'] : null;
         $id = (int) $row['id_persalinan'];
         $st = $koneksi->prepare("UPDATE visit_persalinan SET nomor_rm = ?, gravid = ?, partus = ?, abortus = ?, jenis_persalinan = ?, tarif_paket = ?, ttd_file = ? WHERE id_persalinan = ?");
         $st->bind_param('sssssssi', $rm, $d['gravid'], $d['partus'], $d['abortus'], $d['jenis_persalinan'], $d['tarif_paket'], $ttd, $id);
      } else {
         $st = $koneksi->prepare("INSERT INTO visit_persalinan (visit_ID, nomor_rm, gravid, partus, abortus, jenis_persalinan, tarif_paket, ttd_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
         $st->bind_param('ssssssss', $no, $rm, $d['gravid'], $d['partus'], $d['abortus'], $d['jenis_persalinan'], $d['tarif_paket'], $ttd);
      }
      $st->execute();
      $st->close();
      $koneksi->commit();
   } catch (Throwable $e) {
      $koneksi->rollback();
      throw $e;
   }
   // hanya file tanda tangan form ini yang dihapus (bukan tanda tangan registrasi)
   if ($lama && str_starts_with(basename($lama), 'ttd_persalinan_') && is_file($root . basename($lama))) @unlink($root . basename($lama));
   rpBalas(200, ['status' => 'success', 'message' => $row ? 'Rekap persalinan diperbarui.' : 'Rekap persalinan disimpan.']);
} catch (Throwable $e) {
   if ($ttdBaru) @unlink($root . $ttdBaru);
   error_log('[ranap/saveFormRekapPersalinan] ' . $e->getMessage());
   rpBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}
