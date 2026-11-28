<?php
/**
 * Pemeriksaan EKG per kunjungan (module/admin/form_ekg). Tabel ekg_results: 1 baris = 1 pemeriksaan (EKG 1 wajib, EKG 2 opsional).
 *   GET    ?no=<visit_ID>     -> { status, data:[...], kunjungan:{batal, dokter, tanggal, hari_ini}, dokter:[nama...] }
 *   POST   ?no=..  (multipart: tanggal_pemeriksaan, dokter, interpretasi, ekg1, ekg2, [id_ekg], [hapus_ekg2])
 *                             -> tambah (tanpa id_ekg, ekg1 wajib) / ubah (dengan id_ekg; file opsional = ganti)
 *   DELETE ?no=..&id=<id_ekg> -> hapus (+ file)
 * Path file tetap "uploads/ekg/<nama>" (dipakai print/formulir_ekg).
 * Perbaikan: semua query dirangkai dari input (SQL injection); tanpa cek login & batas faskes (lihat/hapus EKG faskes
 * lain lewat id); daftar difilter nomor RM dari URL (format "BPJS (004048)" vs "004048" -> data tidak tampil);
 * "EKG 2" menempel ke baris sembarang (LIMIT 1) dan tanggal/dokter/interpretasi yang diisi diabaikan diam-diam,
 * file EKG 2 lama tertimpa tanpa dihapus; tidak ada fitur ubah; hanya cek ekstensi file; hapus data tidak menghapus
 * file; pesan error SQL ke browser.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();

function ekBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) ekBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

const EK_DIR_WEB = 'uploads/ekg/';
const EK_DIR = __DIR__ . '/../../' . EK_DIR_WEB;
const EK_MAKS = 10 * 1024 * 1024;

try {
   $no = trim((string) ($_GET['no'] ?? ''));
   $k = ekKunjungan($no, $idc);
   if (!$k) ekBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         ekList($no, $k, $idc);
         break;
      case 'POST':
         ekSimpan($no, $k);
         break;
      case 'DELETE':
         ekHapus((int) ($_GET['id'] ?? 0), $no);
         break;
      default:
         ekBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[ranap/ekgController] ' . $e->getMessage());
   ekBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

/** Kunjungan milik faskes sesi + nomor RM & dokter kunjungan -> array | null */
function ekKunjungan(string $no, int $idc): ?array
{
   global $koneksi;
   if ($no === '') return null;
   $st = $koneksi->prepare("SELECT v.visit_status, v.visit_date, v.id_doctor, p.nomor_rm
      FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
      WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1");
   $st->bind_param('si', $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function ekCari(int $id, string $no): ?array
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT id_ekg, tanggal_pemeriksaan, ekg1, ekg2, interpretasi, dokter FROM ekg_results WHERE id_ekg = ? AND visit_ID = ? LIMIT 1");
   $st->bind_param('is', $id, $no);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function ekList(string $no, array $k, int $idc): void
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT id_ekg, tanggal_pemeriksaan, ekg1, ekg2, interpretasi, dokter, created_at FROM ekg_results
      WHERE visit_ID = ? ORDER BY tanggal_pemeriksaan DESC, id_ekg DESC");
   $st->bind_param('s', $no);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   // saran nama dokter: dokter faskes ini (ketik bebas tetap boleh)
   $st = $koneksi->prepare("SELECT fullname FROM ms_users WHERE id_customer = ? AND roles = 'dokter' AND fullname <> '' ORDER BY fullname");
   $st->bind_param('i', $idc);
   $st->execute();
   $dokter = array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'fullname');
   $st->close();
   ekBalas(200, [
      'status' => 'success',
      'data' => $data,
      'kunjungan' => ['batal' => (int) $k['visit_status'] === 99, 'dokter' => (string) $k['id_doctor'], 'tanggal' => substr((string) $k['visit_date'], 0, 10), 'hari_ini' => date('Y-m-d')],
      'dokter' => $dokter,
   ]);
}

/** Cek file unggahan (belum dipindah) -> [tmp, ext] | null bila tidak ada. */
function ekCekFile(string $field, string $label): ?array
{
   $f = $_FILES[$field] ?? null;
   if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
   $tolak = fn($p) => ekBalas(422, ['status' => 'error', 'message' => $p, 'field' => $field]);
   if (in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) $tolak("Ukuran $label melebihi batas unggah server.");
   if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) $tolak("$label gagal diunggah, coba lagi.");
   if ($f['size'] > EK_MAKS) $tolak("Ukuran $label maksimal 10 MB.");
   $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
   $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
   if (!$ext || !@getimagesize($f['tmp_name'])) $tolak("$label harus gambar JPG, PNG, atau WEBP.");
   return [$f['tmp_name'], $ext];
}

function ekSimpan(string $no, array $k): void
{
   global $koneksi;
   if (empty($_POST) && empty($_FILES) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
      ekBalas(413, ['status' => 'error', 'message' => 'Ukuran gambar melebihi batas unggah server.', 'field' => 'ekg1']);
   }
   $id = (int) ($_POST['id_ekg'] ?? 0);
   $lama = null;
   if ($id > 0) {
      $lama = ekCari($id, $no);
      if (!$lama) ekBalas(404, ['status' => 'error', 'message' => 'Data EKG tidak ditemukan.']);
   } elseif ((int) $k['visit_status'] === 99) {
      ekBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
   }

   $t = fn($f, $max) => mb_substr(trim((string) (is_scalar($_POST[$f] ?? null) ? $_POST[$f] : '')), 0, $max);
   $tolak = fn($p, $f) => ekBalas(422, ['status' => 'error', 'message' => $p, 'field' => $f]);
   $tgl = $t('tanggal_pemeriksaan', 10);
   $d = DateTime::createFromFormat('!Y-m-d', $tgl);
   if (!$d || $d->format('Y-m-d') !== $tgl) $tolak('Tanggal pemeriksaan wajib diisi dengan tanggal yang valid.', 'tanggal_pemeriksaan');
   if ($tgl > date('Y-m-d')) $tolak('Tanggal pemeriksaan tidak boleh di masa depan.', 'tanggal_pemeriksaan');
   if ($k['visit_date'] && $tgl < substr((string) $k['visit_date'], 0, 10)) $tolak('Tanggal pemeriksaan tidak boleh sebelum tanggal kunjungan.', 'tanggal_pemeriksaan');
   $dokter = $t('dokter', 120);
   if ($dokter === '') $tolak('Nama dokter wajib diisi.', 'dokter');
   $interp = $t('interpretasi', 5000);

   $f1 = ekCekFile('ekg1', 'EKG 1');
   $f2 = ekCekFile('ekg2', 'EKG 2');
   if (!$lama && !$f1) $tolak('Unggah gambar EKG 1.', 'ekg1');
   $hapus2 = $lama && !$f2 && !empty($_POST['hapus_ekg2']);

   if (($f1 || $f2) && !is_dir(EK_DIR) && !mkdir(EK_DIR, 0755, true) && !is_dir(EK_DIR)) throw new RuntimeException('Folder unggahan tidak bisa dibuat.');
   $baru = [];
   try {
      foreach (['ekg1' => $f1, 'ekg2' => $f2] as $kol => $f) {
         if (!$f) continue;
         $nama = 'ekg_' . bin2hex(random_bytes(12)) . '.' . $f[1];
         if (!move_uploaded_file($f[0], EK_DIR . $nama)) throw new RuntimeException('Gagal memindahkan file EKG.');
         $baru[$kol] = EK_DIR_WEB . $nama;
      }
      $p1 = $baru['ekg1'] ?? ($lama['ekg1'] ?? null);
      $p2 = $baru['ekg2'] ?? ($hapus2 ? null : ($lama['ekg2'] ?? null));
      if ($lama) {
         $__lg ??= logAmbilBaris($koneksi, 'ekg_results', 'id_ekg', $id); // log: kondisi sebelum diubah/dihapus
         $st = $koneksi->prepare("UPDATE ekg_results SET tanggal_pemeriksaan = ?, dokter = ?, interpretasi = ?, ekg1 = ?, ekg2 = ? WHERE id_ekg = ? AND visit_ID = ?");
         $st->bind_param('sssssis', $tgl, $dokter, $interp, $p1, $p2, $id, $no);
      } else {
         $rm = (string) ($k['nomor_rm'] ?? '');
         $st = $koneksi->prepare("INSERT INTO ekg_results (nomor_rm, visit_ID, tanggal_pemeriksaan, ekg1, ekg2, interpretasi, dokter) VALUES (?, ?, ?, ?, ?, ?, ?)");
         $st->bind_param('sssssss', $rm, $no, $tgl, $p1, $p2, $interp, $dokter);
      }
      $st->execute();
      if (!$lama) $id = $st->insert_id;
      $st->close();
   } catch (Throwable $e) {
      foreach ($baru as $p) @unlink(EK_DIR . basename($p));   // jangan tinggalkan file tanpa data
      throw $e;
   }
   // file lama yang diganti / dihapus dibuang setelah data tersimpan
   if ($lama) {
      foreach (['ekg1', 'ekg2'] as $kol) {
         if ((isset($baru[$kol]) || ($kol === 'ekg2' && $hapus2)) && $lama[$kol]) ekBuangFile($lama[$kol]);
      }
   }
   logBaris($koneksi, 'klinis', 'ekg_results', 'id_ekg', $id, $__lg ?? null, ['label' => 'EKG', 'visit_ID' => $no], 'EKG');
   ekBalas(200, ['status' => 'success', 'message' => $lama ? 'Pemeriksaan EKG diperbarui.' : 'Pemeriksaan EKG disimpan.', 'id' => $id]);
}

function ekBuangFile(?string $path): void
{
   $path = (string) $path;
   if (str_starts_with($path, EK_DIR_WEB) && is_file(EK_DIR . basename($path))) @unlink(EK_DIR . basename($path));
}

function ekHapus(int $id, string $no): void
{
   global $koneksi;
   $lama = $id > 0 ? ekCari($id, $no) : null;
   if (!$lama) ekBalas(404, ['status' => 'error', 'message' => 'Data EKG tidak ditemukan atau sudah terhapus.']);
   $__lg ??= logAmbilBaris($koneksi, 'ekg_results', 'id_ekg', $id); // log: kondisi sebelum diubah/dihapus
   $st = $koneksi->prepare("DELETE FROM ekg_results WHERE id_ekg = ? AND visit_ID = ?");
   $st->bind_param('is', $id, $no);
   $st->execute();
   $st->close();
   ekBuangFile($lama['ekg1']);
   ekBuangFile($lama['ekg2']);
   logBaris($koneksi, 'klinis', 'ekg_results', 'id_ekg', $id, $__lg ?? null, ['label' => 'EKG', 'visit_ID' => $no], 'EKG');
   ekBalas(200, ['status' => 'success', 'message' => 'Pemeriksaan EKG dihapus.']);
}
