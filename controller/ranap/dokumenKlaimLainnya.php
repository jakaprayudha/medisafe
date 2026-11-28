<?php
/**
 * Dokumen klaim lainnya per kunjungan (module/admin/form_upload_klaim, cetak print/formulir_dokumen_lainnya).
 * Tabel pasien_dokumen_klaim_lainnya, file PDF di uploads/lainnya/.
 *   GET    ?no=<visit_ID>              -> { status, data:[{id_dokumen, tanggal, judul_dokumen, dokumen_path, created_at}] }
 *   GET    ?no=..&id=<id_dokumen>      -> 1 data
 *   POST   ?no=..  multipart (tgl_upload, judul_dokumen, dokumen_path=PDF)  -> tambah
 *   POST   ?no=..&id=<id>  (tgl_upload, judul_dokumen)                        -> ubah tanggal & judul
 *   DELETE ?no=..&id=<id>              -> hapus data + file
 * Perbaikan: query tambah & daftar dirangkai dari input (SQL injection); tanpa cek login; ambil/ubah/hapus per id tanpa
 * batas faskes & kunjungan (dokumen faskes lain bisa dihapus beserta filenya); visit_ID dari form tidak dicek
 * kepemilikannya; hanya cek ekstensi (isi bukan PDF lolos) dan tanpa batas ukuran; pesan "Format harus JPG/PNG" untuk
 * file PDF; judul kosong tersimpan; file tertinggal bila insert gagal; respon tanpa header JSON.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

const DL_MAKS = 5 * 1024 * 1024;
const DL_DIR = 'uploads/lainnya/';

function dlBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

$no = trim((string) ($_GET['no'] ?? $_POST['visit_ID'] ?? ''));
$kunj = visitGuard($koneksi, 'visit_ID', $no);   // 401 / 404 bila bukan kunjungan faskes ini
session_write_close();
$no = (string) $kunj['visit_ID'];
$idc = (string) $kunj['id_customer'];            // kolom id_customer bertipe varchar
$root = dirname(__DIR__, 2);

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         isset($_GET['id']) ? dlGetId((int) $_GET['id'], $no, $idc) : dlList($no, $idc);
         break;
      case 'POST':
         isset($_GET['id']) ? dlUbah((int) $_GET['id'], $no, $idc) : dlTambah($no, $idc, $kunj, $root);
         break;
      case 'DELETE':
         dlHapus((int) ($_GET['id'] ?? 0), $no, $idc, $root);
         break;
      default:
         dlBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[ranap/dokumenKlaimLainnya] ' . $e->getMessage());
   dlBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

function dlCari(int $id, string $no, string $idc): ?array
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT id_dokumen, tanggal, judul_dokumen, dokumen_path, created_at FROM pasien_dokumen_klaim_lainnya
      WHERE id_dokumen = ? AND visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('iss', $id, $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function dlList(string $no, string $idc): void
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT id_dokumen, tanggal, judul_dokumen, dokumen_path, created_at FROM pasien_dokumen_klaim_lainnya
      WHERE visit_ID = ? AND id_customer = ? ORDER BY tanggal DESC, id_dokumen DESC");
   $st->bind_param('ss', $no, $idc);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   dlBalas(200, ['status' => 'success', 'data' => $data]);
}

function dlGetId(int $id, string $no, string $idc): void
{
   $r = dlCari($id, $no, $idc);
   if (!$r) dlBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   dlBalas(200, ['status' => 'success', 'data' => $r]);
}

/** Validasi tanggal & judul. */
function dlIsi(): array
{
   $tgl = trim((string) ($_POST['tgl_upload'] ?? ''));
   $judul = mb_substr(trim((string) ($_POST['judul_dokumen'] ?? '')), 0, 255);
   $d = DateTime::createFromFormat('!Y-m-d', $tgl);
   if (!$d || $d->format('Y-m-d') !== $tgl) dlBalas(422, ['status' => 'error', 'message' => 'Tanggal wajib diisi dengan tanggal yang valid.', 'field' => 'tgl_upload']);
   if ($tgl > date('Y-m-d')) dlBalas(422, ['status' => 'error', 'message' => 'Tanggal tidak boleh di masa depan.', 'field' => 'tgl_upload']);
   if ($judul === '') dlBalas(422, ['status' => 'error', 'message' => 'Judul dokumen wajib diisi.', 'field' => 'judul_dokumen']);
   return [$tgl, $judul];
}

function dlTambah(string $no, string $idc, array $kunj, string $root): void
{
   global $koneksi;
   if ((int) $kunj['visit_status'] === 99) dlBalas(409, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
   [$tgl, $judul] = dlIsi();

   $f = $_FILES['dokumen_path'] ?? null;
   $err = [
      UPLOAD_ERR_INI_SIZE => 'File melebihi batas upload server.', UPLOAD_ERR_FORM_SIZE => 'File terlalu besar.',
      UPLOAD_ERR_PARTIAL => 'Upload terputus, coba lagi.', UPLOAD_ERR_NO_FILE => 'Pilih file PDF terlebih dahulu.',
   ];
   if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
      dlBalas(422, ['status' => 'error', 'message' => $err[$f['error'] ?? UPLOAD_ERR_NO_FILE] ?? 'File gagal diunggah.', 'field' => 'dokumen_path']);
   }
   if ($f['size'] > DL_MAKS) dlBalas(422, ['status' => 'error', 'message' => 'Ukuran file maksimal 5 MB.', 'field' => 'dokumen_path']);
   $fi = new finfo(FILEINFO_MIME_TYPE);
   $head = (string) @file_get_contents($f['tmp_name'], false, null, 0, 5);
   if ($fi->file($f['tmp_name']) !== 'application/pdf' || $head !== '%PDF-') {
      dlBalas(422, ['status' => 'error', 'message' => 'File harus berformat PDF.', 'field' => 'dokumen_path']);
   }

   $dir = $root . '/' . DL_DIR;
   if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) dlBalas(500, ['status' => 'error', 'message' => 'Folder dokumen tidak tersedia.']);
   $nama = uniqid('docsklaim_', true) . '.pdf';
   if (!move_uploaded_file($f['tmp_name'], $dir . $nama)) dlBalas(500, ['status' => 'error', 'message' => 'Gagal menyimpan file.']);

   $path = DL_DIR . $nama;
   try {
      $st = $koneksi->prepare("INSERT INTO pasien_dokumen_klaim_lainnya (visit_ID, id_customer, tanggal, judul_dokumen, dokumen_path) VALUES (?, ?, ?, ?, ?)");
      $st->bind_param('sssss', $no, $idc, $tgl, $judul, $path);
      $st->execute();
      $id = $st->insert_id;
      $st->close();
   } catch (Throwable $e) {
      @unlink($dir . $nama);   // jangan tinggalkan file yatim
      throw $e;
   }
   logBaris($koneksi, 'dokumen', 'pasien_dokumen_klaim_lainnya', 'id_dokumen', $id, $__lg ?? null, ['label' => 'Dokumen klaim lainnya', 'visit_ID' => $no], 'Dokumen klaim lainnya');
   dlBalas(200, ['status' => 'success', 'message' => 'Dokumen berhasil disimpan.', 'id' => $id]);
}

function dlUbah(int $id, string $no, string $idc): void
{
   global $koneksi;
   if (!$id || !dlCari($id, $no, $idc)) dlBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   [$tgl, $judul] = dlIsi();
   $__lg ??= logAmbilBaris($koneksi, 'pasien_dokumen_klaim_lainnya', 'id_dokumen', $id); // log: kondisi sebelum diubah/dihapus
   $st = $koneksi->prepare("UPDATE pasien_dokumen_klaim_lainnya SET tanggal = ?, judul_dokumen = ? WHERE id_dokumen = ? AND visit_ID = ? AND id_customer = ?");
   $st->bind_param('ssiss', $tgl, $judul, $id, $no, $idc);
   $st->execute();
   $st->close();
   logBaris($koneksi, 'dokumen', 'pasien_dokumen_klaim_lainnya', 'id_dokumen', $id, $__lg ?? null, ['label' => 'Dokumen klaim lainnya', 'visit_ID' => $no], 'Dokumen klaim lainnya');
   dlBalas(200, ['status' => 'success', 'message' => 'Dokumen diperbarui.']);
}

function dlHapus(int $id, string $no, string $idc, string $root): void
{
   global $koneksi;
   $r = $id ? dlCari($id, $no, $idc) : null;
   if (!$r) dlBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah terhapus.']);
   $__lg ??= logAmbilBaris($koneksi, 'pasien_dokumen_klaim_lainnya', 'id_dokumen', $id); // log: kondisi sebelum diubah/dihapus
   $st = $koneksi->prepare("DELETE FROM pasien_dokumen_klaim_lainnya WHERE id_dokumen = ? AND visit_ID = ? AND id_customer = ?");
   $st->bind_param('iss', $id, $no, $idc);
   $st->execute();
   $st->close();
   // hapus file hanya bila benar-benar di folder uploads/lainnya
   $p = (string) $r['dokumen_path'];
   if (strpos($p, DL_DIR) === 0) {
      $file = $root . '/' . DL_DIR . basename($p);
      if (is_file($file)) @unlink($file);
   }
   logBaris($koneksi, 'dokumen', 'pasien_dokumen_klaim_lainnya', 'id_dokumen', $id, $__lg ?? null, ['label' => 'Dokumen klaim lainnya', 'visit_ID' => $no], 'Dokumen klaim lainnya');
   dlBalas(200, ['status' => 'success', 'message' => 'Dokumen dihapus.']);
}
