<?php
/**
 * Foto / dokumentasi perawatan per kunjungan (module/admin/form_dok_perawatan). Tabel pasien_dokumen, jenis FOTO_PERAWATAN.
 *   GET    ?no=<visit_ID>             -> { status, data:[{id_dokumen, foto_path, rilis, tgl_upload}], kunjungan:{...} }
 *   POST   ?no=..  (multipart: tgl_upload, foto_path)   -> unggah 1 foto
 *   POST   ?no=..&id=<id>  (tgl_upload)                 -> ubah tanggal
 *   DELETE ?no=..&id=<id>                               -> hapus (+ file)
 * Path file tetap "uploads/perawatan/<nama>" karena dipakai cetakan (print/formulir_dokumen, bundle klaim).
 * Perbaikan: INSERT & daftar dirangkai langsung dari input (SQL injection); tanpa cek login & batas faskes (lihat/ubah/
 * hapus foto faskes lain lewat id); nomor RM dari form (bisa dipalsukan); hanya cek ekstensi (isi file tidak diperiksa)
 * & tanpa batas ukuran; tanggal tanpa validasi (ada data tahun 2027); hapus data tidak menghapus file foto (tetap
 * bisa diakses publik); folder unggahan tidak dibuat bila belum ada; gagal simpan DB meninggalkan file; pesan error
 * tanpa kode HTTP.
 */
include '../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();

function dpBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) dpBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

const DP_JENIS = 'FOTO_PERAWATAN';
const DP_DIR_WEB = 'uploads/perawatan/';
const DP_DIR = __DIR__ . '/../../' . DP_DIR_WEB;
const DP_MAKS = 10 * 1024 * 1024;

try {
   $no = trim((string) ($_GET['no'] ?? ''));
   $k = dpKunjungan($no, $idc);
   if (!$k) dpBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         dpList($no, $k);
         break;
      case 'POST':
         isset($_GET['id']) ? dpUbahTanggal((int) $_GET['id'], $no, $k) : dpUnggah($no, $k);
         break;
      case 'DELETE':
         dpHapus((int) ($_GET['id'] ?? 0), $no);
         break;
      default:
         dpBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[ranap/dokumentasiPerawatan] ' . $e->getMessage());
   dpBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

/** Kunjungan milik faskes sesi + nomor RM pasien -> array | null */
function dpKunjungan(string $no, int $idc): ?array
{
   global $koneksi;
   if ($no === '') return null;
   $st = $koneksi->prepare("SELECT v.visit_status, v.visit_date, p.nomor_rm
      FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
      WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1");
   $st->bind_param('si', $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

/** Foto milik kunjungan ini (kunjungan sudah dicek milik faskes). */
function dpCari(int $id, string $no): ?array
{
   global $koneksi;
   $j = DP_JENIS;
   $st = $koneksi->prepare("SELECT id_dokumen, foto_path, rilis FROM pasien_dokumen WHERE id_dokumen = ? AND visit_ID = ? AND jenis_dokumen = ? LIMIT 1");
   $st->bind_param('iss', $id, $no, $j);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function dpList(string $no, array $k): void
{
   global $koneksi;
   $j = DP_JENIS;
   $st = $koneksi->prepare("SELECT id_dokumen, foto_path, rilis, tgl_upload FROM pasien_dokumen
      WHERE visit_ID = ? AND jenis_dokumen = ? ORDER BY rilis DESC, id_dokumen DESC");
   $st->bind_param('ss', $no, $j);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   dpBalas(200, [
      'status' => 'success',
      'data' => $data,
      'kunjungan' => ['batal' => (int) $k['visit_status'] === 99, 'tanggal' => $k['visit_date'], 'hari_ini' => date('Y-m-d')],
   ]);
}

/** Tanggal foto: wajib, valid, tidak di masa depan, tidak sebelum tanggal kunjungan. */
function dpTanggal(string $v, array $k): string
{
   $tolak = fn($p) => dpBalas(422, ['status' => 'error', 'message' => $p, 'field' => 'tgl_upload']);
   $d = DateTime::createFromFormat('!Y-m-d', $v);
   if (!$d || $d->format('Y-m-d') !== $v) $tolak('Tanggal wajib diisi dengan tanggal yang valid.');
   if ($v > date('Y-m-d')) $tolak('Tanggal tidak boleh di masa depan.');
   $awal = substr((string) ($k['visit_date'] ?? ''), 0, 10);
   if ($awal !== '' && $v < $awal) $tolak('Tanggal tidak boleh sebelum tanggal kunjungan (' . $awal . ').');
   return $v;
}

function dpUnggah(string $no, array $k): void
{
   global $koneksi;
   if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
      dpBalas(413, ['status' => 'error', 'message' => 'Ukuran foto melebihi batas unggah server.', 'field' => 'foto_path']);
   }
   if ((int) $k['visit_status'] === 99) dpBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
   $tgl = dpTanggal(trim((string) ($_POST['tgl_upload'] ?? '')), $k);

   $f = $_FILES['foto_path'] ?? null;
   $tolak = fn($p) => dpBalas(422, ['status' => 'error', 'message' => $p, 'field' => 'foto_path']);
   if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) $tolak('Pilih foto terlebih dahulu.');
   if (in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) $tolak('Ukuran foto melebihi batas unggah server.');
   if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) $tolak('Foto gagal diunggah, coba lagi.');
   if ($f['size'] > DP_MAKS) $tolak('Ukuran foto maksimal 10 MB.');
   $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
   $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
   if (!$ext || !@getimagesize($f['tmp_name'])) $tolak('File harus foto JPG, PNG, atau WEBP.');

   if (!is_dir(DP_DIR) && !mkdir(DP_DIR, 0755, true) && !is_dir(DP_DIR)) throw new RuntimeException('Folder unggahan tidak bisa dibuat.');
   $nama = 'perawatan_' . bin2hex(random_bytes(12)) . '.' . $ext;
   if (!move_uploaded_file($f['tmp_name'], DP_DIR . $nama)) throw new RuntimeException('Gagal memindahkan file foto.');

   try {
      $rm = (string) ($k['nomor_rm'] ?? '');
      $j = DP_JENIS;
      $path = DP_DIR_WEB . $nama;
      $st = $koneksi->prepare("INSERT INTO pasien_dokumen (nomor_rm, visit_ID, jenis_dokumen, foto_path, rilis) VALUES (?, ?, ?, ?, ?)");
      $st->bind_param('sssss', $rm, $no, $j, $path, $tgl);
      $st->execute();
      $id = $st->insert_id;
      $st->close();
   } catch (Throwable $e) {
      @unlink(DP_DIR . $nama);   // jangan tinggalkan file tanpa data
      throw $e;
   }
   dpBalas(200, ['status' => 'success', 'message' => 'Foto disimpan.', 'id' => $id, 'foto_path' => $path]);
}

function dpUbahTanggal(int $id, string $no, array $k): void
{
   global $koneksi;
   if (!dpCari($id, $no)) dpBalas(404, ['status' => 'error', 'message' => 'Foto tidak ditemukan.']);
   $tgl = dpTanggal(trim((string) ($_POST['tgl_upload'] ?? '')), $k);
   $j = DP_JENIS;
   $st = $koneksi->prepare("UPDATE pasien_dokumen SET rilis = ? WHERE id_dokumen = ? AND visit_ID = ? AND jenis_dokumen = ?");
   $st->bind_param('siss', $tgl, $id, $no, $j);
   $st->execute();
   $st->close();
   dpBalas(200, ['status' => 'success', 'message' => 'Tanggal foto diperbarui.']);
}

function dpHapus(int $id, string $no): void
{
   global $koneksi;
   $lama = $id > 0 ? dpCari($id, $no) : null;
   if (!$lama) dpBalas(404, ['status' => 'error', 'message' => 'Foto tidak ditemukan atau sudah terhapus.']);
   $j = DP_JENIS;
   $st = $koneksi->prepare("DELETE FROM pasien_dokumen WHERE id_dokumen = ? AND visit_ID = ? AND jenis_dokumen = ?");
   $st->bind_param('iss', $id, $no, $j);
   $st->execute();
   $st->close();
   // hapus file hanya bila berada di folder foto perawatan
   $path = (string) $lama['foto_path'];
   if (str_starts_with($path, DP_DIR_WEB) && is_file(DP_DIR . basename($path))) @unlink(DP_DIR . basename($path));
   dpBalas(200, ['status' => 'success', 'message' => 'Foto dihapus.']);
}
