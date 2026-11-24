<?php
/**
 * Simpan / hapus foto pasien kunjungan (module/admin/form_capture_patient). pasien_dokumen jenis FOTO_PASIEN.
 *   POST JSON { visit, image:"data:image/(jpeg|png|webp);base64,..." } -> { status, message, foto }
 *   POST JSON { visit, hapus:1 }                                       -> { status, message }
 * Perbaikan: tanpa cek login & faskes; nama file dari rm/visit kiriman browser tanpa disaring (path traversal: file
 * bisa ditulis ke folder mana pun); rm/visit disisipkan ke SQL (SQL injection); isi gambar tidak divalidasi dan hanya
 * prefix JPEG yang dibuang (upload PNG tersimpan rusak); ON DUPLICATE KEY tanpa unique key -> tiap simpan menambah
 * baris baru; folder uploads/foto tidak dibuat; pesan error SQL dikirim ke browser.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
header('Content-Type: application/json; charset=utf-8');

function sfBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
   exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sfBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$in = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($in)) sfBalas(400, ['status' => 'error', 'message' => 'Data tidak valid.']);
$kunj = visitGuard($koneksi, 'visit_ID', (string) ($in['visit'] ?? ''));
session_write_close();
$no = (string) $kunj['visit_ID'];
$dir = dirname(__DIR__, 2) . '/uploads/foto/';

try {
   $st = $koneksi->prepare("SELECT p.nomor_rm FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient WHERE v.id_visit = ? LIMIT 1");
   $idv = (int) $kunj['id_visit'];
   $st->bind_param('i', $idv);
   $st->execute();
   $rm = mb_substr((string) ($st->get_result()->fetch_row()[0] ?? ''), 0, 255);
   $st->close();

   if (!empty($in['hapus'])) {
      $lama = sfLama($no);
      if (!$lama) sfBalas(404, ['status' => 'error', 'message' => 'Foto tidak ditemukan.']);
      $st = $koneksi->prepare("DELETE FROM pasien_dokumen WHERE visit_ID = ? AND jenis_dokumen = 'FOTO_PASIEN'");
      $st->bind_param('s', $no);
      $st->execute();
      $st->close();
      sfHapusFile($dir, $lama['foto_path']);
      sfBalas(200, ['status' => 'success', 'message' => 'Foto dihapus.']);
   }

   if ((int) $kunj['visit_status'] === 99) sfBalas(409, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
   if (!preg_match('#^data:image/(jpeg|png|webp);base64,(.+)$#s', (string) ($in['image'] ?? ''), $m)) {
      sfBalas(422, ['status' => 'error', 'message' => 'Foto belum diambil / format tidak didukung (JPG, PNG, WEBP).']);
   }
   $bin = base64_decode(str_replace(' ', '+', $m[2]), true);
   if ($bin === false || strlen($bin) > 3 * 1024 * 1024) sfBalas(422, ['status' => 'error', 'message' => 'Ukuran foto maksimal 3 MB.']);
   $info = @getimagesizefromstring($bin);
   $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? null;
   if (!$info || !$ext || $info[0] < 50 || $info[1] < 50) sfBalas(422, ['status' => 'error', 'message' => 'File bukan foto yang valid.']);

   if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) sfBalas(500, ['status' => 'error', 'message' => 'Folder foto tidak tersedia.']);
   $nama = 'capture_' . preg_replace('/[^A-Za-z0-9_-]/', '', $no) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
   if (@file_put_contents($dir . $nama, $bin, LOCK_EX) === false) sfBalas(500, ['status' => 'error', 'message' => 'Gagal menyimpan file foto.']);
   $path = 'uploads/foto/' . $nama;
   $tgl = date('Y-m-d');

   $koneksi->begin_transaction();
   try {
      $lama = sfLama($no, true);
      if ($lama) {
         $st = $koneksi->prepare("UPDATE pasien_dokumen SET foto_path = ?, nomor_rm = ?, rilis = ?, tgl_upload = NOW() WHERE id_dokumen = ?");
         $idd = (int) $lama['id_dokumen'];
         $st->bind_param('sssi', $path, $rm, $tgl, $idd);
      } else {
         $st = $koneksi->prepare("INSERT INTO pasien_dokumen (nomor_rm, visit_ID, foto_path, jenis_dokumen, rilis) VALUES (?, ?, ?, 'FOTO_PASIEN', ?)");
         $st->bind_param('ssss', $rm, $no, $path, $tgl);
      }
      $st->execute();
      $st->close();
      $koneksi->commit();
   } catch (Throwable $e) {
      $koneksi->rollback();
      @unlink($dir . $nama);
      throw $e;
   }
   if ($lama) sfHapusFile($dir, $lama['foto_path']);   // foto lama diganti
   sfBalas(200, ['status' => 'success', 'message' => 'Foto berhasil disimpan.', 'foto' => 'uploads/foto/' . rawurlencode($nama)]);
} catch (Throwable $e) {
   error_log('[visit/saveFoto] ' . $e->getMessage());
   sfBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

function sfLama(string $no, bool $kunci = false): ?array
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT id_dokumen, foto_path FROM pasien_dokumen WHERE visit_ID = ? AND jenis_dokumen = 'FOTO_PASIEN' ORDER BY id_dokumen DESC LIMIT 1" . ($kunci ? ' FOR UPDATE' : ''));
   $st->bind_param('s', $no);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

/** Hapus file hanya bila memang di uploads/foto/. */
function sfHapusFile(string $dir, ?string $path): void
{
   $path = (string) $path;
   if (strpos($path, 'uploads/foto/') !== 0) return;
   $f = $dir . basename($path);
   if (is_file($f)) @unlink($f);
}
