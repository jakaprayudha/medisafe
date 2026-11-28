<?php
/**
 * Dokumen SEP & FKPP per kunjungan (modal "Dokumen SEP / FKPP" di registrasi poliklinik).
 *
 * GET  ?no={visit_ID}                       -> {status, sep:{file,url,waktu,ada}|null, fkpp:{...}|null}
 * GET juga mengembalikan informed (Informed Consent -> pasien_visit.file_informed), dipakai module/admin/form_upload_klaim.
 * POST multipart no={visit_ID}, jenis=sep|fkpp|informed, file=(PDF, maks 5 MB) -> simpan & kembalikan info file
 *
 * Kolom & folder SAMA dengan form_upload_klaim / cetak bundle klaim:
 *   SEP  -> pasien_visit.file_spp,  FKPP -> pasien_visit.file_fkpp,  file di uploads/dokumen/
 * Dulu menu "Upload SEP / FKPP" mengarah ke module/admin/form_sep & form_fkpp yang tidak ada (404), sedangkan
 * controller lama (controller/sep, controller/fkpp) menulis ke kolom lain (sep_file / fkpp_file) yang tidak dibaca
 * bundle klaim, sehingga 2.600+ dokumen yang sudah ada tidak pernah tampil.
 * Kolom lama tetap dibaca sebagai cadangan (uploads/sep, uploads/fkpp).
 * Hanya PDF: bundle klaim menggabungkan file PDF.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
require_once __DIR__ . '/../../utility/activityLog.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function dkJawab(array $d, int $kode = 200): void
{
   http_response_code($kode);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}

const DK_JENIS = [
   'sep' => ['kolom' => 'file_spp', 'lama' => 'sep_file', 'folderLama' => 'sep', 'label' => 'SEP'],
   'fkpp' => ['kolom' => 'file_fkpp', 'lama' => 'fkpp_file', 'folderLama' => 'fkpp', 'label' => 'FKPP'],
   'informed' => ['kolom' => 'file_informed', 'lama' => null, 'folderLama' => null, 'label' => 'Informed Consent'],
];
const DK_MAKS = 5 * 1024 * 1024;
$root = dirname(__DIR__, 2);

$no = trim((string) ($_POST['no'] ?? $_GET['no'] ?? ''));
$kunj = visitGuard($koneksi, 'visit_ID', $no);
$idUser = (string) ($_SESSION['username'] ?? '');
session_write_close();
$idVisit = (int) $kunj['id_visit'];
$idc = (int) $kunj['id_customer'];

function dkInfo(string $root, array $row, string $jenis): ?array
{
   $j = DK_JENIS[$jenis];
   $kandidat = [];
   if (!empty($row[$j['kolom']])) $kandidat[] = ['dokumen', basename((string) $row[$j['kolom']])];
   if ($j['lama'] && !empty($row[$j['lama']])) $kandidat[] = [$j['folderLama'], basename((string) $row[$j['lama']])];
   foreach ($kandidat as [$folder, $file]) {
      $path = "$root/uploads/$folder/$file";
      $ada = is_file($path);
      // waktu unggah dari nama file ({jenis}_{visit}_{unix}.pdf) atau mtime
      $ts = preg_match('/_(\d{10})\.[a-z]+$/i', $file, $m) ? (int) $m[1] : ($ada ? filemtime($path) : 0);
      return [
         'file' => $file,
         'url' => "uploads/$folder/" . rawurlencode($file),
         'ada' => $ada,
         'ukuran' => $ada ? filesize($path) : null,
         'waktu' => $ts ? date('Y-m-d H:i', $ts) : null,
         'ext' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
      ];
   }
   return null;
}

function dkBaris(mysqli $db, int $idVisit, int $idc): array
{
   $st = $db->prepare("SELECT visit_ID, file_spp, file_fkpp, file_informed, sep_file, fkpp_file FROM pasien_visit WHERE id_visit = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('ii', $idVisit, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc() ?: [];
   $st->close();
   return $r;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
   $row = dkBaris($koneksi, $idVisit, $idc);
   dkJawab(['status' => 'success', 'sep' => dkInfo($root, $row, 'sep'), 'fkpp' => dkInfo($root, $row, 'fkpp'), 'informed' => dkInfo($root, $row, 'informed')]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') dkJawab(['status' => 'error', 'message' => 'Metode tidak diizinkan.'], 405);

$jenis = (string) ($_POST['jenis'] ?? '');
if (!isset(DK_JENIS[$jenis])) dkJawab(['status' => 'error', 'message' => 'Jenis dokumen tidak valid.'], 422);
if ((int) $kunj['visit_status'] === 99) dkJawab(['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.'], 409);

$f = $_FILES['file'] ?? null;
$errUpload = [
   UPLOAD_ERR_INI_SIZE => 'File melebihi batas upload server.', UPLOAD_ERR_FORM_SIZE => 'File terlalu besar.',
   UPLOAD_ERR_PARTIAL => 'Upload terputus, coba lagi.', UPLOAD_ERR_NO_FILE => 'Pilih file terlebih dahulu.',
];
if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
   dkJawab(['status' => 'error', 'message' => $errUpload[$f['error'] ?? UPLOAD_ERR_NO_FILE] ?? 'File gagal diunggah.'], 422);
}
if ($f['size'] > DK_MAKS) dkJawab(['status' => 'error', 'message' => 'Ukuran file maksimal 5 MB.'], 422);
// isi file harus benar-benar PDF (bukan sekadar ekstensi)
$fi = new finfo(FILEINFO_MIME_TYPE);
$head = (string) @file_get_contents($f['tmp_name'], false, null, 0, 5);
if ($fi->file($f['tmp_name']) !== 'application/pdf' || $head !== '%PDF-') {
   dkJawab(['status' => 'error', 'message' => 'File harus berformat PDF.'], 422);
}

$dir = "$root/uploads/dokumen";
if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) dkJawab(['status' => 'error', 'message' => 'Folder dokumen tidak tersedia.'], 500);
// format nama sama dengan form_upload_klaim: {jenis}_{visit_ID}_{unix}.pdf
$aman = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $kunj['visit_ID']);
$nama = $jenis . '_' . $aman . '_' . time() . '.pdf';
if (!move_uploaded_file($f['tmp_name'], "$dir/$nama")) dkJawab(['status' => 'error', 'message' => 'Gagal menyimpan file.'], 500);

$kol = DK_JENIS[$jenis]['kolom'];
$dkLama = (logAmbilBaris($koneksi, 'pasien_visit', 'id_visit', $idVisit) ?? [])[$kol] ?? null;
$st = $koneksi->prepare("UPDATE pasien_visit SET `$kol` = ? WHERE id_visit = ? AND id_customer = ?");
$st->bind_param('sii', $nama, $idVisit, $idc);
if (!$st->execute()) {
   @unlink("$dir/$nama");
   error_log('[dokumenKlaim] ' . $st->error);
   dkJawab(['status' => 'error', 'message' => 'Gagal menyimpan ke database.'], 500);
}
$st->close();
// file lama tidak dihapus (dokumen klaim), hanya tidak lagi dirujuk
error_log("[dokumenKlaim] {$kunj['visit_ID']} $jenis -> $nama oleh $idUser");

logAktivitas($koneksi, 'dokumen', 'upload_file', ['tipe' => 'dokumen_klaim', 'id' => $idVisit, 'label' => DK_JENIS[$jenis]['label'], 'visit_ID' => $kunj['visit_ID']],
   DK_JENIS[$jenis]['label'] . ($dkLama ? ' diganti (file lama tetap disimpan)' : ' diunggah'), $dkLama ? [$kol => $dkLama] : null, [$kol => $nama]);

$row = dkBaris($koneksi, $idVisit, $idc);
dkJawab(['status' => 'success', 'message' => DK_JENIS[$jenis]['label'] . ' berhasil diunggah.', 'jenis' => $jenis, 'info' => dkInfo($root, $row, $jenis)]);
