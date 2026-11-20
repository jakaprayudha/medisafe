<?php
// Shared by recordFace.php / recordFaceVisit.php.
// $dir: uploads subfolder, $table/$col/$pk: target row.
function saveFaceImage($koneksi, $dir, $table, $col, $pk)
{
   header('Content-Type: application/json');
   $fail = function ($msg, $code = 400) {
      http_response_code($code);
      echo json_encode(["status" => "error", "message" => $msg]);
      exit;
   };

   $data  = json_decode(file_get_contents("php://input"), true);
   $id    = (int)($data['id'] ?? 0);
   $raw   = preg_replace('#^data:image/\w+;base64,#i', '', (string)($data['image'] ?? ''));
   $bin   = base64_decode($raw, true);

   if (!$id || !$bin || strlen($bin) > 10 * 1024 * 1024) $fail("Data gambar tidak valid");

   // hanya baris milik faskes di session (dulu: foto pasien/kunjungan faskes mana pun bisa ditimpa)
   if (session_status() === PHP_SESSION_NONE) @session_start();
   $idc = (int) ($_SESSION['id_customer'] ?? 0);
   session_write_close();
   if ($idc <= 0) $fail("Session tidak valid / expired", 401);
   $cek = mysqli_prepare($koneksi, "SELECT 1 FROM $table WHERE $pk = ? AND id_customer = ? LIMIT 1");
   mysqli_stmt_bind_param($cek, "ii", $id, $idc);
   mysqli_stmt_execute($cek);
   if (!mysqli_fetch_row(mysqli_stmt_get_result($cek))) $fail("Data tidak ditemukan di faskes ini", 404);
   mysqli_stmt_close($cek);
   if (!function_exists('imagecreatefromstring') || !($img = @imagecreatefromstring($bin))) $fail("File bukan gambar");

   // downscale (max 640px) + recompress as JPEG q75
   $w = imagesx($img);
   $h = imagesy($img);
   $max = 640;
   if (max($w, $h) > $max) {
      $s = $max / max($w, $h);
      $img = imagescale($img, (int)round($w * $s), (int)round($h * $s));
   }

   $abs = __DIR__ . "/../../uploads/$dir";
   if (!is_dir($abs) && !mkdir($abs, 0775, true)) $fail("Folder upload tidak tersedia", 500);

   $name = time() . "_$id.jpg";
   if (!imagejpeg($img, "$abs/$name", 75)) $fail("Gagal menyimpan file", 500);

   $path = "/../../uploads/$dir/$name";
   $stmt = mysqli_prepare($koneksi, "UPDATE $table SET $col = ? WHERE $pk = ? AND id_customer = ?");
   mysqli_stmt_bind_param($stmt, "sii", $path, $id, $idc);
   if (!mysqli_stmt_execute($stmt)) {
      @unlink("$abs/$name");
      $fail("Gagal update database", 500);
   }

   echo json_encode(["status" => "success", "path" => $path]);
}
