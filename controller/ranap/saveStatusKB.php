<?php
/**
 * Simpan Kartu Status Peserta KB (tabel visit_kb_status, 1 baris per kunjungan).
 *   POST JSON { visit_ID, faskes_kode, ..., penanggung_jawab } -> { status, message }
 * Perbaikan: baris kosong sebelum <?php (output sebelum header); semua isian disisipkan ke SQL (SQL injection) & tanpa
 * cek login / batas faskes; INSERT tidak menyertakan visit_ID & nomor_rm -> data tidak pernah bisa dibaca lagi dan
 * setiap simpan membuat baris baru; isian kosong '' ke kolom INT/DATE/FLOAT & teks bebas ke kolom ENUM
 * (pemeriksaan_tambahan) -> simpan gagal; umur anak teks bebas ke kolom INT; tanpa validasi panjang kolom
 * (faskes_kode 10, kode_keluarga 20, keadaan_umum 20); pesan error SQL dikirim ke browser.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
define('KB_LIB', true);
require_once __DIR__ . '/getFormStatusKB.php';
header('Content-Type: application/json; charset=utf-8');

function kbBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') kbBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$in = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($in)) kbBalas(400, ['status' => 'error', 'message' => 'Data tidak valid.']);
$kunj = visitGuard($koneksi, 'visit_ID', (string) ($in['visit_ID'] ?? ''));
session_write_close();
if ((int) $kunj['visit_status'] === 99) kbBalas(409, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);

$tolak = fn($p, $f) => kbBalas(422, ['status' => 'error', 'message' => $p, 'field' => $f]);
$t = fn($k) => trim((string) (is_scalar($in[$k] ?? null) ? $in[$k] : ''));
$d = [];
// teks (panjang = ukuran kolom)
foreach (['faskes_kode' => [10, 'Kode faskes KB'], 'kode_keluarga' => [20, 'Kode keluarga'], 'nama_suami' => [100, 'Nama suami'],
   'pendidikan_suami' => [50, 'Pendidikan suami'], 'pekerjaan_suami' => [100, 'Pekerjaan suami'], 'kb_terakhir' => [100, 'KB terakhir'],
   'keadaan_umum' => [20, 'Keadaan umum'], 'metode_pilihan' => [50, 'Metode KB'], 'penanggung_jawab' => [100, 'Penanggung jawab']] as $k => [$max, $lbl]) {
   $x = $t($k);
   if (mb_strlen($x) > $max) $tolak("$lbl maksimal $max karakter.", $k);
   $d[$k] = $x === '' ? null : $x;
}
$d['riwayat_sakit'] = mb_substr($t('riwayat_sakit'), 0, 5000) ?: null;
// angka bulat
foreach (['anak_lk' => [30, 'Anak laki-laki'], 'anak_pr' => [30, 'Anak perempuan'], 'umur_anak_terakhir' => [60, 'Umur anak terakhir'],
   'gpa_g' => [30, 'Gravida'], 'gpa_p' => [30, 'Partus'], 'gpa_a' => [30, 'Abortus']] as $k => [$max, $lbl]) {
   $x = $t($k);
   if ($x !== '' && (!ctype_digit($x) || (int) $x > $max)) $tolak("$lbl harus angka 0–$max.", $k);
   $d[$k] = $x === '' ? null : (int) $x;
}
if ($d['gpa_g'] !== null && (int) $d['gpa_p'] + (int) $d['gpa_a'] > $d['gpa_g']) $tolak('Partus + Abortus tidak boleh melebihi Gravida.', 'gpa_p');
// pilihan ya / tidak (ENUM)
foreach (['hamil' => 'Hamil', 'menyusui' => 'Menyusui', 'pemeriksaan_tambahan' => 'Pemeriksaan tambahan'] as $k => $lbl) {
   $x = strtolower($t($k));
   if (!in_array($x, ['', 'ya', 'tidak'], true)) $tolak("Pilihan $lbl tidak valid.", $k);
   $d[$k] = $x === '' ? null : $x;
}
if ($d['hamil'] === 'ya' && $d['metode_pilihan']) $tolak('Pasien hamil — metode KB tidak dapat diberikan. Kosongkan metode KB atau ubah status hamil.', 'metode_pilihan');
// berat badan & tekanan darah
$bb = str_replace(',', '.', $t('berat_badan'));
if ($bb !== '' && (!is_numeric($bb) || (float) $bb < 1 || (float) $bb > 300)) $tolak('Berat badan tidak valid (1–300 kg).', 'berat_badan');
$d['berat_badan'] = $bb === '' ? null : (string) round((float) $bb, 1);
$td = $t('tekanan_darah');
if ($td !== '' && !preg_match('#^(\d{2,3})/(\d{2,3})$#', $td, $m)) $tolak('Tekanan darah ditulis sistole/diastole, mis. 120/80.', 'tekanan_darah');
if ($td !== '' && ((int) $m[1] <= (int) $m[2] || (int) $m[1] > 300)) $tolak('Tekanan darah tidak masuk akal (sistole harus lebih besar dari diastole).', 'tekanan_darah');
$d['tekanan_darah'] = $td === '' ? null : $td;
// tanggal
foreach (['haid_terakhir' => 'Haid terakhir', 'tgl_dilayani' => 'Tanggal dilayani', 'tgl_dicabut' => 'Tanggal dicabut'] as $k => $lbl) {
   $x = $t($k);
   $o = $x === '' ? null : DateTime::createFromFormat('!Y-m-d', $x);
   if ($x !== '' && (!$o || $o->format('Y-m-d') !== $x)) $tolak("$lbl tidak valid.", $k);
   if ($x !== '' && $k !== 'tgl_dicabut' && $x > date('Y-m-d')) $tolak("$lbl tidak boleh di masa depan.", $k);
   $d[$k] = $x === '' ? null : $x;
}
if ($d['tgl_dicabut'] && $d['tgl_dilayani'] && $d['tgl_dicabut'] < $d['tgl_dilayani']) $tolak('Tanggal dicabut tidak boleh sebelum tanggal dilayani.', 'tgl_dicabut');

try {
   $src = kbAmbil($koneksi, (int) $kunj['id_visit']);
   $no = (string) $kunj['visit_ID'];
   $d['nomor_rm'] = mb_substr($src['pasien']['nomor_rm'], 0, 50);
   $koneksi->begin_transaction();
   try {
      $st = $koneksi->prepare("SELECT id_kb_status FROM visit_kb_status WHERE visit_ID = ? ORDER BY id_kb_status DESC LIMIT 1 FOR UPDATE");
      $st->bind_param('s', $no);
      $st->execute();
      $id = (int) ($st->get_result()->fetch_row()[0] ?? 0);
      $st->close();
      $nama = array_keys($d);
      $nilai = array_map(fn($x) => $x === null ? null : (string) $x, array_values($d));
      if ($id) {
         $sql = 'UPDATE visit_kb_status SET ' . implode(', ', array_map(fn($k) => "`$k` = ?", $nama)) . ' WHERE id_kb_status = ?';
         $nilai[] = $id;
         $tipe = str_repeat('s', count($d)) . 'i';
      } else {
         $nama[] = 'visit_ID';
         $nilai[] = $no;
         $sql = 'INSERT INTO visit_kb_status (`' . implode('`, `', $nama) . '`) VALUES (' . implode(', ', array_fill(0, count($nama), '?')) . ')';
         $tipe = str_repeat('s', count($nama));
      }
      $st = $koneksi->prepare($sql);
      $st->bind_param($tipe, ...$nilai);
      $st->execute();
      $st->close();
      $koneksi->commit();
   } catch (Throwable $e) {
      $koneksi->rollback();
      throw $e;
   }
   kbBalas(200, ['status' => 'success', 'message' => $id ? 'Kartu status KB diperbarui.' : 'Kartu status KB disimpan.']);
} catch (Throwable $e) {
   error_log('[ranap/saveStatusKB] ' . $e->getMessage());
   kbBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}
