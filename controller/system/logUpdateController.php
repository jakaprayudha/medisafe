<?php
/**
 * CRUD Log Update sistem (khusus superadmin).
 * Perbaikan: sebelumnya TANPA cek login/role (siapa pun bisa tambah/ubah/hapus lewat URL), PUT tidak memvalidasi tipe,
 * PDF tidak bisa diganti saat edit, PDF lama yatim saat jenis panduan diubah, pesan error SQL bocor ke klien,
 * folder upload dibuat saat setiap request. Ubah & hapus kini lewat POST (multipart) agar PDF bisa diganti.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
require '../../database/connect.php';
require_once '../../utility/security.php';
require_once __DIR__ . '/../../utility/activityLog.php';

requireRole(['superadmin']);
$method = $_SERVER['REQUEST_METHOD'];
session_write_close();

const LU_DIR = __DIR__ . '/../../uploads/system-update/';
const LU_URL = 'uploads/system-update/';
const LU_TIPE = ['update', 'feature', 'improvement', 'bug', 'fix', 'security', 'maintenance'];
const LU_PANDUAN = ['none', 'url', 'video', 'pdf'];
const LU_KOLOM = 'id_update, title, description, type, version, guide_type, guide_url, guide_file, created_at, is_read';

function luJson(string $status, string $message, $data = null, int $code = 200): void
{
   http_response_code($code);
   echo json_encode(['status' => $status, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
   exit;
}

function luGagal(string $pesan, string $detail, int $code = 500): void
{
   error_log('[logUpdate] ' . $pesan . ': ' . $detail);
   luJson('error', $pesan, null, $code);
}

/** Hapus file PDF panduan hanya bila benar-benar berada di folder upload. */
function luHapusFile(?string $rel): void
{
   if (!$rel || strpos($rel, LU_URL) !== 0) return;
   $path = realpath(__DIR__ . '/../../' . $rel);
   $base = realpath(LU_DIR);
   if ($path && $base && strpos($path, $base . DIRECTORY_SEPARATOR) === 0 && is_file($path)) @unlink($path);
}

/** Validasi field teks. Mengembalikan [data, pesan galat|null, nama field]. */
function luBaca(array $in): array
{
   $d = [
      'title' => trim((string) ($in['title'] ?? '')),
      'description' => trim((string) ($in['description'] ?? '')),
      'type' => trim((string) ($in['type'] ?? 'update')),
      'version' => trim((string) ($in['version'] ?? '')),
      'guide_type' => trim((string) ($in['guide_type'] ?? 'none')),
      'guide_url' => trim((string) ($in['guide_url'] ?? '')),
   ];
   if ($d['title'] === '') return [$d, 'Judul update wajib diisi.', 'title'];
   if (mb_strlen($d['title']) > 255) return [$d, 'Judul maksimal 255 karakter.', 'title'];
   if (mb_strlen($d['version']) > 50) return [$d, 'Versi maksimal 50 karakter.', 'version'];
   if (!in_array($d['type'], LU_TIPE, true)) $d['type'] = 'update';
   if (!in_array($d['guide_type'], LU_PANDUAN, true)) $d['guide_type'] = 'none';
   if (in_array($d['guide_type'], ['url', 'video'], true)) {
      if ($d['guide_url'] === '') return [$d, 'URL panduan wajib diisi.', 'guide_url'];
      if (mb_strlen($d['guide_url']) > 1000 || !filter_var($d['guide_url'], FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $d['guide_url'])) {
         return [$d, 'URL panduan tidak valid (harus diawali http:// atau https://).', 'guide_url'];
      }
   } else {
      $d['guide_url'] = null;
   }
   return [$d, null, ''];
}

/** Proses unggahan PDF. Mengembalikan path relatif, null bila tidak ada file, atau keluar dengan 422. */
function luSimpanPdf(): ?string
{
   $f = $_FILES['guide_file'] ?? null;
   if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return null;
   if ($f['error'] !== UPLOAD_ERR_OK) {
      luJson('error', $f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE ? 'Ukuran PDF melebihi batas server.' : 'Gagal mengunggah file PDF.', ['field' => 'guide_file'], 422);
   }
   if ($f['size'] > 20 * 1024 * 1024) luJson('error', 'Ukuran PDF maksimal 20 MB.', ['field' => 'guide_file'], 422);
   $fh = fopen($f['tmp_name'], 'rb');
   $magic = $fh ? fread($fh, 5) : '';
   if ($fh) fclose($fh);
   $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
   if (strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) !== 'pdf' || $mime !== 'application/pdf' || $magic !== '%PDF-') {
      luJson('error', 'File panduan harus berformat PDF.', ['field' => 'guide_file'], 422);
   }
   if (!is_dir(LU_DIR) && !@mkdir(LU_DIR, 0755, true)) luGagal('Gagal menyiapkan folder upload.', LU_DIR);
   $nama = 'guide_' . date('YmdHis') . '_' . bin2hex(random_bytes(5)) . '.pdf';
   if (!move_uploaded_file($f['tmp_name'], LU_DIR . $nama)) luGagal('Gagal menyimpan file PDF.', $nama);
   return LU_URL . $nama;
}

/* ------------------------------ GET ------------------------------ */
if ($method === 'GET') {
   if (isset($_GET['id'])) {
      $id = (int) $_GET['id'];
      $st = $koneksi->prepare('SELECT ' . LU_KOLOM . ' FROM system_update_log WHERE id_update = ? LIMIT 1');
      $st->bind_param('i', $id);
      $st->execute();
      $row = $st->get_result()->fetch_assoc();
      $st->close();
      if (!$row) luJson('error', 'Data log update tidak ditemukan.', null, 404);
      luJson('success', 'Data berhasil diambil.', $row);
   }
   $rs = $koneksi->query('SELECT ' . LU_KOLOM . ' FROM system_update_log ORDER BY created_at DESC, id_update DESC');
   if (!$rs) luGagal('Gagal mengambil data.', $koneksi->error);
   luJson('success', 'Data berhasil diambil.', $rs->fetch_all(MYSQLI_ASSOC));
}

if ($method !== 'POST') luJson('error', 'Method tidak didukung.', null, 405);

/* ------------------------- POST: hapus --------------------------- */
$aksi = $_POST['aksi'] ?? 'simpan';
if ($aksi === 'hapus') {
   $id = (int) ($_POST['id'] ?? 0);
   if ($id <= 0) luJson('error', 'ID update tidak valid.', null, 422);
   $st = $koneksi->prepare('SELECT guide_file FROM system_update_log WHERE id_update = ? LIMIT 1');
   $st->bind_param('i', $id);
   $st->execute();
   $row = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$row) luJson('error', 'Data log update tidak ditemukan.', null, 404);
   $luLama = logAmbilBaris($koneksi, 'system_update_log', 'id_update', $id);
   $st = $koneksi->prepare('DELETE FROM system_update_log WHERE id_update = ?');
   $st->bind_param('i', $id);
   if (!$st->execute()) luGagal('Gagal menghapus data.', $st->error);
   $st->close();
   luHapusFile($row['guide_file']);
   logBaris($koneksi, 'sistem', 'system_update_log', 'id_update', $id, $luLama, ['tipe' => 'log_update', 'label' => trim(($luLama['version'] ?? '') . ' · ' . ($luLama['title'] ?? ''), ' ·')], 'Log update sistem dihapus');
   luJson('success', 'Log update berhasil dihapus.');
}

/* ---------------------- POST: tambah / ubah ---------------------- */
$id = (int) ($_POST['id'] ?? 0);
[$d, $galat, $field] = luBaca($_POST);
if ($galat) luJson('error', $galat, ['field' => $field], 422);

$lama = null;
if ($id > 0) {
   $st = $koneksi->prepare('SELECT guide_file FROM system_update_log WHERE id_update = ? LIMIT 1');
   $st->bind_param('i', $id);
   $st->execute();
   $lama = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$lama) luJson('error', 'Data log update tidak ditemukan.', null, 404);
}

$luLama = $id > 0 ? logAmbilBaris($koneksi, 'system_update_log', 'id_update', $id) : null;
$fileBaru = $d['guide_type'] === 'pdf' ? luSimpanPdf() : null;
$fileLama = $lama['guide_file'] ?? null;
if ($d['guide_type'] === 'pdf') {
   if (!$fileBaru && !$fileLama) luJson('error', 'Pilih file PDF panduan terlebih dahulu.', ['field' => 'guide_file'], 422);
   $guideFile = $fileBaru ?: $fileLama;
} else {
   $guideFile = null;
}

if ($id > 0) {
   $st = $koneksi->prepare('UPDATE system_update_log SET title = ?, description = ?, type = ?, version = ?, guide_type = ?, guide_url = ?, guide_file = ? WHERE id_update = ?');
   $st->bind_param('sssssssi', $d['title'], $d['description'], $d['type'], $d['version'], $d['guide_type'], $d['guide_url'], $guideFile, $id);
} else {
   $st = $koneksi->prepare('INSERT INTO system_update_log (title, description, type, version, guide_type, guide_url, guide_file, is_read) VALUES (?, ?, ?, ?, ?, ?, ?, 0)');
   $st->bind_param('sssssss', $d['title'], $d['description'], $d['type'], $d['version'], $d['guide_type'], $d['guide_url'], $guideFile);
}
if (!$st->execute()) {
   luHapusFile($fileBaru);
   luGagal('Gagal menyimpan data.', $st->error);
}
$luId = $id > 0 ? $id : (int) $st->insert_id;
$st->close();
logBaris($koneksi, 'sistem', 'system_update_log', 'id_update', $luId, $luLama, ['tipe' => 'log_update', 'label' => trim($d['version'] . ' · ' . $d['title'], ' ·')], $id > 0 ? 'Log update sistem diubah' : 'Log update sistem ditambahkan');
// PDF lama dibuang bila diganti atau jenis panduan tidak lagi PDF
if ($fileLama && $fileLama !== $guideFile) luHapusFile($fileLama);
luJson('success', $id > 0 ? 'Log update berhasil diperbarui.' : 'Log update berhasil ditambahkan.');
