<?php
/**
 * Video display antrean (dipakai module/admin/setting_display, module/display/display-admisi,
 * controller/display/videoPlaylist).
 *
 * - Video tiap faskes disimpan di  video/faskes-<id_customer>/*.mp4
 * - Urutan & status aktif disimpan di video/faskes-<id_customer>/playlist.json
 * - Video lama di folder video/ (bawaan, sebelum per-faskes) tetap diputar
 *   untuk faskes yang belum punya video aktif sendiri.
 */

const VD_EXT = 'mp4';
const VD_MAX_BYTES = 100 * 1024 * 1024;

function vdRoot(): string
{
   return dirname(__DIR__, 2) . '/video';
}

function vdDirFaskes(int $idc): string
{
   return vdRoot() . '/faskes-' . $idc;
}

/** URL relatif dari root aplikasi. */
function vdUrlFaskes(int $idc): string
{
   return 'video/faskes-' . $idc . '/';
}

function vdNamaValid(string $name): bool
{
   return $name !== '' && $name[0] !== '.' && strpbrk($name, "/\\\0") === false
      && strtolower(pathinfo($name, PATHINFO_EXTENSION)) === VD_EXT;
}

/** @return string[] nama file mp4 di folder (urut natural). */
function vdScan(string $dir): array
{
   if (!is_dir($dir)) {
      return [];
   }
   $out = [];
   foreach (@scandir($dir) ?: [] as $f) {
      if (vdNamaValid($f) && !is_link("$dir/$f") && is_file("$dir/$f")) {
         $out[] = $f;
      }
   }
   natcasesort($out);
   return array_values($out);
}

/**
 * Daftar video faskes sesuai urutan playlist.json; file baru (belum tercatat) ditaruh di akhir & aktif.
 * @return array<int, array{file: string, aktif: bool, ukuran: int, diubah: int}>
 */
function vdDaftar(int $idc): array
{
   $dir = vdDirFaskes($idc);
   $files = vdScan($dir);
   $meta = [];
   $raw = is_file("$dir/playlist.json") ? json_decode((string) @file_get_contents("$dir/playlist.json"), true) : null;
   foreach (($raw['items'] ?? []) as $it) {
      if (is_array($it) && isset($it['file']) && in_array($it['file'], $files, true)) {
         $meta[$it['file']] = !empty($it['aktif']);
      }
   }
   $urut = array_keys($meta);
   foreach ($files as $f) {
      if (!isset($meta[$f])) {
         $urut[] = $f;
         $meta[$f] = true;
      }
   }
   return array_map(fn($f) => [
      'file' => $f,
      'aktif' => $meta[$f],
      'ukuran' => (int) @filesize("$dir/$f"),
      'diubah' => (int) @filemtime("$dir/$f"),
   ], $urut);
}

/** @param array<int, array{file: string, aktif: bool}> $items */
function vdSimpanUrutan(int $idc, array $items): void
{
   $dir = vdDirFaskes($idc);
   $json = json_encode(['items' => array_map(fn($i) => ['file' => $i['file'], 'aktif' => (bool) $i['aktif']], $items)], JSON_PRETTY_PRINT);
   $tmp = "$dir/.playlist-" . bin2hex(random_bytes(6)) . '.tmp';
   if (@file_put_contents($tmp, $json) === false || !@rename($tmp, "$dir/playlist.json")) {
      @unlink($tmp);
      throw new RuntimeException('Urutan video gagal disimpan. Periksa izin tulis folder video.');
   }
}

/** Video bawaan (folder video/ lama). */
function vdBawaan(): array
{
   return array_map(fn($f) => ['file' => $f, 'ukuran' => (int) @filesize(vdRoot() . "/$f")], vdScan(vdRoot()));
}

/**
 * Playlist yang diputar display: video aktif faskes, atau video bawaan bila faskes belum punya video aktif.
 * @return array{sumber: string, urls: string[]}
 */
function vdPlaylist(int $idc, string $prefix = ''): array
{
   $aktif = array_values(array_filter(vdDaftar($idc), fn($v) => $v['aktif']));
   if ($aktif) {
      return [
         'sumber' => 'faskes',
         'urls' => array_map(fn($v) => $prefix . vdUrlFaskes($idc) . rawurlencode($v['file']), $aktif),
      ];
   }
   return [
      'sumber' => 'bawaan',
      'urls' => array_map(fn($v) => $prefix . 'video/' . rawurlencode($v['file']), vdBawaan()),
   ];
}

/** Batas upload efektif (byte): minimum dari batas aplikasi, upload_max_filesize dan post_max_size. */
function vdBatasUpload(): int
{
   $batas = VD_MAX_BYTES;
   foreach (['upload_max_filesize', 'post_max_size'] as $k) {
      $v = ini_parse_quantity((string) ini_get($k));
      if ($v > 0) {
         $batas = min($batas, $k === 'post_max_size' ? $v - 64 * 1024 : $v);
      }
   }
   return max(0, $batas);
}
