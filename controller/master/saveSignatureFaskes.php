<?php
/**
 * Tanda tangan digital user faskes (module/admin/signature). Dipakai di dokumen cetak (resume, CPPT, triase, dll).
 *
 * POST JSON {aksi:"simpan", id_user, image:"data:image/png;base64,..."}  -> simpan dari kanvas
 * POST multipart aksi=unggah, id_user, file (PNG/JPG)                     -> unggah hasil scan
 * POST JSON {aksi:"hapus", id_user}                                       -> hapus tanda tangan
 *
 * Dulu: tanpa cek role/faskes (siapa pun bisa menimpa tanda tangan user faskes lain), isi file tidak divalidasi,
 * dan file lama dihapus sebelum file baru berhasil disimpan.
 * Sekarang: hanya admin faskes yang sama; gambar divalidasi & dinormalkan (PNG, latar transparan dirapikan),
 * file baru disimpan & DB diperbarui dulu, baru file lama dihapus.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function jawab(array $d, int $c = 200): void
{
   http_response_code($c);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}

requireRole(['admin', 'superadmin', 'administrator']);
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$oleh = (string) ($_SESSION['username'] ?? 'system');
session_write_close();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jawab(['status' => 'error', 'message' => 'Metode tidak diizinkan.'], 405);
if ($idc <= 0) jawab(['status' => 'error', 'message' => 'Session faskes tidak ditemukan.'], 401);

const TTD_MAKS_BYTE = 2 * 1024 * 1024;
$folder = dirname(__DIR__, 2) . '/uploads/ttd_faskes';

$json = json_decode((string) file_get_contents('php://input'), true);
$in = is_array($json) ? $json : $_POST;
$aksi = (string) ($in['aksi'] ?? (isset($in['image']) ? 'simpan' : ''));
$idUser = (int) ($in['id_user'] ?? 0);

// user harus milik faskes yang sama
$st = $koneksi->prepare("SELECT id_user, fullname, signature_user FROM ms_users WHERE id_user = ? AND id_customer = ? LIMIT 1");
$st->bind_param('ii', $idUser, $idc);
$st->execute();
$user = $st->get_result()->fetch_assoc();
$st->close();
if (!$user) jawab(['status' => 'error', 'message' => 'User tidak ditemukan di faskes ini.'], 404);
$fileLama = basename((string) $user['signature_user']);

function hapusFile(string $folder, string $nama): void
{
   if ($nama !== '' && preg_match('/^[\w.-]+\.(png|jpe?g)$/i', $nama) && is_file("$folder/$nama")) @unlink("$folder/$nama");
}

if ($aksi === 'hapus') {
   $st = $koneksi->prepare("UPDATE ms_users SET signature_user = NULL, signature_timestamp = NULL WHERE id_user = ? AND id_customer = ?");
   $st->bind_param('ii', $idUser, $idc);
   $st->execute();
   $st->close();
   hapusFile($folder, $fileLama);
   jawab(['status' => 'success', 'message' => 'Tanda tangan dihapus.']);
}

// ---------- ambil gambar ----------
if ($aksi === 'simpan') {
   $b64 = (string) ($in['image'] ?? '');
   if (!preg_match('#^data:image/(png|jpeg);base64,#', $b64)) jawab(['status' => 'error', 'message' => 'Format gambar tidak valid.'], 422);
   $data = base64_decode(substr($b64, strpos($b64, ',') + 1), true);
} elseif ($aksi === 'unggah') {
   $f = $_FILES['file'] ?? null;
   if (!$f || ($f['error'] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) jawab(['status' => 'error', 'message' => 'File gagal diunggah.'], 422);
   $data = file_get_contents($f['tmp_name']);
} else {
   jawab(['status' => 'error', 'message' => 'Aksi tidak dikenal.'], 400);
}
if ($data === false || $data === '' || strlen($data) > TTD_MAKS_BYTE) jawab(['status' => 'error', 'message' => 'Gambar kosong atau lebih dari 2 MB.'], 422);
$info = @getimagesizefromstring($data);
if (!$info || !in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) jawab(['status' => 'error', 'message' => 'File harus gambar PNG atau JPG.'], 422);
if ($info[0] > 4000 || $info[1] > 4000) jawab(['status' => 'error', 'message' => 'Ukuran gambar terlalu besar (maks 4000 px).'], 422);

// ---------- normalkan: PNG, latar putih jadi transparan (hasil scan), dirapikan ke area tanda tangan ----------
$png = $data;
if (function_exists('imagecreatefromstring')) {
   $src = @imagecreatefromstring($data);
   if (!$src) jawab(['status' => 'error', 'message' => 'Gambar tidak dapat dibaca.'], 422);
   $w = imagesx($src);
   $h = imagesy($src);
   $jpeg = $info[2] === IMAGETYPE_JPEG;
   // cari batas goresan (piksel cukup gelap & tidak transparan)
   $x0 = $w; $y0 = $h; $x1 = -1; $y1 = -1;
   $langkah = max(1, (int) floor(max($w, $h) / 800));
   for ($y = 0; $y < $h; $y += $langkah) {
      for ($x = 0; $x < $w; $x += $langkah) {
         $c = imagecolorat($src, $x, $y);
         $a = ($c >> 24) & 0x7F;
         $r = ($c >> 16) & 0xFF; $g = ($c >> 8) & 0xFF; $b = $c & 0xFF;
         if ($a < 100 && ($r + $g + $b) / 3 < 200) {
            if ($x < $x0) $x0 = $x;
            if ($y < $y0) $y0 = $y;
            if ($x > $x1) $x1 = $x;
            if ($y > $y1) $y1 = $y;
         }
      }
   }
   if ($x1 < 0) jawab(['status' => 'error', 'message' => 'Tanda tangan masih kosong.'], 422);
   $pad = max(6, (int) round(max($x1 - $x0, $y1 - $y0) * 0.04));
   $x0 = max(0, $x0 - $pad); $y0 = max(0, $y0 - $pad);
   $x1 = min($w - 1, $x1 + $pad + $langkah); $y1 = min($h - 1, $y1 + $pad + $langkah);
   $cw = $x1 - $x0 + 1;
   $ch = $y1 - $y0 + 1;
   // maksimal lebar 900 px (cukup tajam untuk cetak, file kecil)
   $skala = min(1, 900 / $cw);
   $nw = max(1, (int) round($cw * $skala));
   $nh = max(1, (int) round($ch * $skala));
   $dst = imagecreatetruecolor($nw, $nh);
   imagealphablending($dst, false);
   imagesavealpha($dst, true);
   imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 255, 255, 255, 127));
   imagecopyresampled($dst, $src, 0, 0, $x0, $y0, $nw, $nh, $cw, $ch);
   if ($jpeg || $info[2] === IMAGETYPE_PNG) {
      // latar terang (kertas scan) -> transparan, goresan dipertahankan
      for ($y = 0; $y < $nh; $y++) {
         for ($x = 0; $x < $nw; $x++) {
            $c = imagecolorat($dst, $x, $y);
            $a = ($c >> 24) & 0x7F;
            if ($a >= 120) continue;
            $r = ($c >> 16) & 0xFF; $g = ($c >> 8) & 0xFF; $b = $c & 0xFF;
            $terang = ($r + $g + $b) / 3;
            if ($terang > 215) imagesetpixel($dst, $x, $y, imagecolorallocatealpha($dst, 255, 255, 255, 127));
         }
      }
   }
   ob_start();
   imagepng($dst, null, 6);
   $png = (string) ob_get_clean();
   imagedestroy($src);
   imagedestroy($dst);
} elseif ($info[2] !== IMAGETYPE_PNG) {
   jawab(['status' => 'error', 'message' => 'Server belum mendukung JPG (ekstensi GD). Gunakan PNG.'], 422);
}

// ---------- simpan: file baru -> DB -> hapus file lama ----------
if (!is_dir($folder) && !@mkdir($folder, 0755, true) && !is_dir($folder)) jawab(['status' => 'error', 'message' => 'Folder tanda tangan tidak dapat dibuat.'], 500);
$nama = 'ttd_' . $idUser . '_' . bin2hex(random_bytes(6)) . '.png';
if (@file_put_contents("$folder/$nama", $png, LOCK_EX) === false) {
   error_log('[saveSignatureFaskes] gagal menulis ' . $nama);
   jawab(['status' => 'error', 'message' => 'Gagal menyimpan file tanda tangan. Periksa izin folder uploads.'], 500);
}
$now = date('Y-m-d H:i:s');
$st = $koneksi->prepare("UPDATE ms_users SET signature_user = ?, signature_timestamp = ? WHERE id_user = ? AND id_customer = ?");
$st->bind_param('ssii', $nama, $now, $idUser, $idc);
if (!$st->execute()) {
   @unlink("$folder/$nama");
   error_log('[saveSignatureFaskes] ' . $st->error);
   jawab(['status' => 'error', 'message' => 'Gagal menyimpan tanda tangan.'], 500);
}
$st->close();
if ($fileLama !== $nama) hapusFile($folder, $fileLama);
error_log("[saveSignatureFaskes] tanda tangan user #$idUser diperbarui oleh $oleh");

jawab(['status' => 'success', 'message' => 'Tanda tangan disimpan.', 'file' => $nama, 'waktu' => $now]);
