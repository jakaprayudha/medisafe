<?php
/**
 * Parameter pemeriksaan laboratorium (module/administrator/master-lab-detail). Data global: khusus superadmin.
 *   GET kode=[&] | GET id=            -> daftar parameter sebuah pemeriksaan / detail satu parameter
 *   POST aksi=simpan|hapus            -> tambah/ubah, hapus
 * Perbaikan: sebelumnya TANPA cek login/role, SQL injection di semua query, pemanggilan fungsi yang tidak ada
 * (generateKodeLab) & UPDATE memakai kolom yang tidak ada, serta tombol Edit/Hapus di halaman memakai URL salah (`&id_item=`
 * tanpa `?`). Parameter yang sudah punya hasil pasien tidak boleh dihapus (hasil jadi yatim).
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
require_once __DIR__ . '/../../utility/activityLog.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
requireRole(['superadmin', 'administrator']);
$method = $_SERVER['REQUEST_METHOD'];
session_write_close();

function ldJson(string $status, string $message, $data = null, int $code = 200): void
{
   http_response_code($code);
   echo json_encode(['status' => $status, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}
function ldQ(mysqli $k, string $sql, string $t = '', array $a = []): array
{
   $st = $k->prepare($sql);
   if (!$st) {
      error_log('[labItem] ' . $k->error);
      ldJson('error', 'Terjadi kesalahan server.', null, 500);
   }
   if ($t !== '') $st->bind_param($t, ...$a);
   if (!$st->execute()) {
      error_log('[labItem] ' . $st->error);
      ldJson('error', 'Terjadi kesalahan server.', null, 500);
   }
   $r = $st->get_result();
   $out = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
   $st->close();
   return $out;
}
function ldExec(mysqli $k, string $sql, string $t, array $a): void
{
   $st = $k->prepare($sql);
   if (!$st || !$st->bind_param($t, ...$a) || !$st->execute()) {
      error_log('[labItem] ' . ($st ? $st->error : $k->error));
      ldJson('error', 'Gagal menyimpan data.', null, 500);
   }
   $st->close();
}

/* ------------------------------ GET ------------------------------ */
if ($method === 'GET') {
   if (isset($_GET['id'])) {
      $row = ldQ($koneksi, 'SELECT id, kode, urutan, assemen, satuan, minimum, maksimum, tipe_isian FROM laboratorium_item WHERE id = ? LIMIT 1', 'i', [(int) $_GET['id']])[0] ?? null;
      if (!$row) ldJson('error', 'Parameter tidak ditemukan.', null, 404);
      ldJson('success', 'OK', $row);
   }
   $kode = trim((string) ($_GET['kode'] ?? ''));
   if ($kode === '') ldJson('error', 'Kode pemeriksaan tidak ada.', null, 422);
   $rows = ldQ($koneksi, 'SELECT id, urutan, assemen, satuan, minimum, maksimum, tipe_isian FROM laboratorium_item WHERE kode = ? ORDER BY (urutan IS NULL OR urutan = \'\'), CAST(urutan AS UNSIGNED), id', 's', [$kode]);
   $pakai = [];
   if ($rows) {
      $ids = array_map(fn($r) => (int) $r['id'], $rows);
      $ph = implode(',', array_fill(0, count($ids), '?'));
      foreach (ldQ($koneksi, "SELECT id_item, COUNT(*) n FROM laboratorium_result WHERE id_item IN ($ph) GROUP BY id_item", str_repeat('i', count($ids)), $ids) as $r) $pakai[(int) $r['id_item']] = (int) $r['n'];
   }
   foreach ($rows as &$r) {
      $r['id'] = (int) $r['id'];
      $r['dipakai'] = $pakai[$r['id']] ?? 0;
   }
   unset($r);
   ldJson('success', 'OK', $rows);
}

if ($method !== 'POST') ldJson('error', 'Method tidak didukung.', null, 405);

$aksi = (string) ($_POST['aksi'] ?? 'simpan');
$idItem = (int) ($_POST['id_item'] ?? 0);
$ldLama = $idItem > 0 ? logAmbilBaris($koneksi, 'laboratorium_item', 'id', $idItem) : null;
function ldLog(mysqli $k, int $id, ?array $lama, string $ket): void
{
   $r = $lama ?? logAmbilBaris($k, 'laboratorium_item', 'id', $id) ?? [];
   $induk = logAmbilBaris($k, 'laboratorium_detail', 'kode', $r['kode'] ?? null);
   logBaris($k, 'master', 'laboratorium_item', 'id', $id, $lama, ['tipe' => 'lab_parameter', 'label' => trim(($induk['assemen'] ?? $r['kode'] ?? '') . ' · ' . ($r['assemen'] ?? ''), ' ·')], $ket);
}

/* ---------------------------- hapus ----------------------------- */
if ($aksi === 'hapus') {
   if ($idItem <= 0) ldJson('error', 'ID parameter tidak valid.', null, 422);
   if (!ldQ($koneksi, 'SELECT 1 FROM laboratorium_item WHERE id = ? LIMIT 1', 'i', [$idItem])) ldJson('error', 'Parameter tidak ditemukan.', null, 404);
   $n = (int) (ldQ($koneksi, 'SELECT COUNT(*) n FROM laboratorium_result WHERE id_item = ?', 'i', [$idItem])[0]['n'] ?? 0);
   if ($n > 0) ldJson('error', "Parameter ini sudah punya $n hasil pasien sehingga tidak bisa dihapus.", null, 409);
   ldExec($koneksi, 'DELETE FROM laboratorium_item WHERE id = ?', 'i', [$idItem]);
   ldLog($koneksi, $idItem, $ldLama, 'Parameter lab dihapus');
   ldJson('success', 'Parameter dihapus.');
}

/* ------------------------ tambah / ubah ------------------------- */
$kode = trim((string) ($_POST['kode_lab'] ?? ''));
$urutan = trim((string) ($_POST['urutan'] ?? ''));
$nama = trim((string) ($_POST['nama'] ?? ''));
$satuan = trim((string) ($_POST['satuan'] ?? ''));
$min = trim((string) ($_POST['minimum'] ?? ''));
$max = trim((string) ($_POST['maksimum'] ?? ''));
$tipe = (string) ($_POST['tipe_isian'] ?? 'keduanya');
if (!in_array($tipe, ['numerik', 'teks', 'keduanya'], true)) ldJson('error', 'Tipe isian tidak valid.', ['field' => 'tipe_isian'], 422);
if ($nama === '') ldJson('error', 'Nama parameter wajib diisi.', ['field' => 'nama'], 422);
if (mb_strlen($nama) > 100) ldJson('error', 'Nama parameter maksimal 100 karakter.', ['field' => 'nama'], 422);
if ($urutan !== '' && !preg_match('/^\d{1,5}$/', $urutan)) ldJson('error', 'Urutan harus berupa angka.', ['field' => 'urutan'], 422);
if (mb_strlen($satuan) > 100) ldJson('error', 'Satuan maksimal 100 karakter.', ['field' => 'satuan'], 422);
if (mb_strlen($min) > 250) ldJson('error', 'Nilai minimum maksimal 250 karakter.', ['field' => 'minimum'], 422);
if (mb_strlen($max) > 100) ldJson('error', 'Nilai maksimum maksimal 100 karakter.', ['field' => 'maksimum'], 422);
if ($tipe === 'numerik') {
   // parameter numerik: batas normal (bila diisi) harus angka agar bisa dibandingkan dengan hasil
   foreach (['minimum' => $min, 'maksimum' => $max] as $f => $v) {
      if ($v !== '' && !preg_match('/^-?\d+([.,]\d+)?$/', $v)) ldJson('error', 'Parameter numerik: nilai ' . $f . ' harus berupa angka (contoh: 12,5), atau kosongkan.', ['field' => $f], 422);
   }
}

if ($idItem > 0) {
   if (!ldQ($koneksi, 'SELECT 1 FROM laboratorium_item WHERE id = ? LIMIT 1', 'i', [$idItem])) ldJson('error', 'Parameter tidak ditemukan.', null, 404);
   ldExec($koneksi, 'UPDATE laboratorium_item SET urutan = ?, assemen = ?, satuan = ?, minimum = ?, maksimum = ?, tipe_isian = ? WHERE id = ?', 'ssssssi', [$urutan, $nama, $satuan, $min, $max, $tipe, $idItem]);
   ldLog($koneksi, $idItem, $ldLama, 'Parameter lab diubah');
   ldJson('success', 'Parameter diperbarui.');
}

if ($kode === '' || !ldQ($koneksi, 'SELECT 1 FROM laboratorium_detail WHERE kode = ? LIMIT 1', 's', [$kode])) ldJson('error', 'Pemeriksaan induk tidak ditemukan.', null, 404);
if ($urutan === '') {
   // urutan otomatis: setelah parameter terakhir
   $urutan = (string) (((int) (ldQ($koneksi, 'SELECT MAX(CAST(urutan AS UNSIGNED)) m FROM laboratorium_item WHERE kode = ?', 's', [$kode])[0]['m'] ?? 0)) + 1);
}
ldExec($koneksi, 'INSERT INTO laboratorium_item (kode, urutan, assemen, satuan, minimum, maksimum, tipe_isian) VALUES (?, ?, ?, ?, ?, ?, ?)', 'sssssss', [$kode, $urutan, $nama, $satuan, $min, $max, $tipe]);
ldLog($koneksi, (int) $koneksi->insert_id, null, 'Parameter lab ditambahkan');
ldJson('success', 'Parameter ditambahkan.');
