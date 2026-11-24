<?php
/**
 * Unggah / hapus foto Buku KIA kunjungan (module/admin/form_upload_buku_kia). Tabel buku_kia, file di uploads/bukukia/.
 *   POST multipart visit=<visit_ID>, file_foto (satu) atau file_foto[] (maks. 10)  -> { status, message, jumlah }
 *   POST visit=<visit_ID>, aksi=hapus, id=<id>                                    -> { status, message }
 * Perbaikan: tanpa cek login & batas faskes; rm/visit disisipkan ke SQL (SQL injection) & ke nama file (path traversal);
 * hanya ekstensi yang dicek (isi tidak), tanpa batas ukuran; time() sebagai nama -> dua unggahan di detik yang sama
 * saling menimpa; file tertinggal bila insert gagal; tidak bisa menghapus foto; nomor RM dari browser (kolom varchar(20)).
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
header('Content-Type: application/json; charset=utf-8');

const KIA_DIR = 'uploads/bukukia/';
const KIA_MAKS = 5 * 1024 * 1024;

function kiaBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') kiaBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$kunj = visitGuard($koneksi, 'visit_ID', (string) ($_POST['visit'] ?? ''));
session_write_close();
$no = (string) $kunj['visit_ID'];
$dir = dirname(__DIR__, 2) . '/' . KIA_DIR;

try {
   if (($_POST['aksi'] ?? '') === 'hapus') {
      $id = (int) ($_POST['id'] ?? 0);
      $st = $koneksi->prepare("SELECT file_path FROM buku_kia WHERE id = ? AND nomor_visit = ? LIMIT 1");
      $st->bind_param('is', $id, $no);
      $st->execute();
      $path = $st->get_result()->fetch_row()[0] ?? null;
      $st->close();
      if ($path === null) kiaBalas(404, ['status' => 'error', 'message' => 'Foto tidak ditemukan atau sudah terhapus.']);
      $st = $koneksi->prepare("DELETE FROM buku_kia WHERE id = ? AND nomor_visit = ?");
      $st->bind_param('is', $id, $no);
      $st->execute();
      $st->close();
      if (strpos((string) $path, KIA_DIR) === 0 && is_file($dir . basename($path))) @unlink($dir . basename($path));
      kiaBalas(200, ['status' => 'success', 'message' => 'Foto dihapus.']);
   }

   if ((int) $kunj['visit_status'] === 99) kiaBalas(409, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
   // normalisasi file_foto (satu) / file_foto[] (banyak)
   $f = $_FILES['file_foto'] ?? null;
   $daftar = [];
   if ($f && is_array($f['name'])) {
      foreach ($f['name'] as $i => $n) $daftar[] = ['name' => $n, 'tmp_name' => $f['tmp_name'][$i], 'size' => $f['size'][$i], 'error' => $f['error'][$i]];
   } elseif ($f) {
      $daftar[] = $f;
   }
   $daftar = array_values(array_filter($daftar, fn($x) => ($x['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE));
   if (!$daftar) kiaBalas(422, ['status' => 'error', 'message' => 'Pilih foto Buku KIA terlebih dahulu.']);
   if (count($daftar) > 10) kiaBalas(422, ['status' => 'error', 'message' => 'Maksimal 10 foto sekali unggah.']);

   $siap = [];
   foreach ($daftar as $i => $x) {
      $ke = count($daftar) > 1 ? ' (file ke-' . ($i + 1) . ')' : '';
      if ($x['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($x['tmp_name'])) kiaBalas(422, ['status' => 'error', 'message' => "Foto gagal diunggah$ke, coba lagi."]);
      if ($x['size'] > KIA_MAKS) kiaBalas(422, ['status' => 'error', 'message' => "Ukuran foto maksimal 5 MB$ke."]);
      $info = @getimagesize($x['tmp_name']);
      $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? null;
      if (!$info || !$ext) kiaBalas(422, ['status' => 'error', 'message' => "File harus berupa foto JPG / PNG / WEBP$ke."]);
      $siap[] = [$x['tmp_name'], $ext];
   }

   if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) kiaBalas(500, ['status' => 'error', 'message' => 'Folder Buku KIA tidak tersedia.']);
   $st = $koneksi->prepare("SELECT p.nomor_rm FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient WHERE v.id_visit = ? LIMIT 1");
   $idv = (int) $kunj['id_visit'];
   $st->bind_param('i', $idv);
   $st->execute();
   $rm = mb_substr((string) ($st->get_result()->fetch_row()[0] ?? ''), 0, 20);   // kolom varchar(20)
   $st->close();

   $baru = [];
   $koneksi->begin_transaction();
   try {
      $st = $koneksi->prepare("INSERT INTO buku_kia (nomor_rm, nomor_visit, file_path, created_at) VALUES (?, ?, ?, NOW())");
      foreach ($siap as [$tmp, $ext]) {
         $nama = 'bukuKIA_' . preg_replace('/[^A-Za-z0-9_-]/', '', $no) . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
         if (!move_uploaded_file($tmp, $dir . $nama)) throw new RuntimeException('move gagal');
         $baru[] = $nama;
         $path = KIA_DIR . $nama;
         $st->bind_param('sss', $rm, $no, $path);
         $st->execute();
      }
      $st->close();
      $koneksi->commit();
   } catch (Throwable $e) {
      $koneksi->rollback();
      foreach ($baru as $n) @unlink($dir . $n);   // jangan tinggalkan file yatim
      throw $e;
   }
   kiaBalas(200, ['status' => 'success', 'message' => count($baru) . ' foto Buku KIA tersimpan.', 'jumlah' => count($baru), 'file_url' => KIA_DIR . rawurlencode(end($baru))]);
} catch (Throwable $e) {
   error_log('[visit/uploadKIA] ' . $e->getMessage());
   kiaBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}
