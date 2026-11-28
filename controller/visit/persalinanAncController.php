<?php
/**
 * Persalinan / ANC (module/admin/persalinan_anc).
 *   GET    ?no=<visit_ID>            -> { status, data:[riwayat ANC pasien di faskes ini], kunjungan:{...} }
 *   GET    ?no=..&id=<id_anc>        -> 1 catatan
 *   POST   ?no=..  (multipart)        -> tambah; dengan id_anc -> ubah (multipart tidak bisa lewat PUT di PHP)
 *   DELETE ?no=..&id=<id_anc>        -> hapus (+ file gambar)
 * Catatan hanya bisa diubah/dihapus dari kunjungan tempat ia dibuat.
 * Perbaikan: tanpa cek login & tanpa batas faskes (list/detail/hapus catatan faskes lain bisa lewat id);
 * tanggal kosong ("") ditolak MySQL STRICT -> gagal simpan bila HPHT/taksiran tidak diisi; HPL & usia kehamilan
 * diketik manual (ada data HPL meleset 9 bulan) -> kini otomatis dari HPHT (Naegele, +280 hari) bila kosong;
 * nilai TD/BB/TFU/DJJ/G-P-A tidak divalidasi; gambar gagal diunggah (terlalu besar / bukan gambar) diam-diam
 * diabaikan tapi tetap "berhasil"; file dihapus sebelum baris terhapus; pesan error SQL dikirim ke browser;
 * tidak ada fitur ubah.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$pengguna = (string) ($_SESSION['fullname'] ?? ($_SESSION['username'] ?? 'system'));
session_write_close();

function ancBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) ancBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

const ANC_DIR = __DIR__ . '/../../uploads/persalinan/';
const ANC_KOLOM = 'a.id_anc, a.anc_number, a.id_visit, a.tanggal_kontrol, a.hpht, a.taksiran_persalinan, a.usia_gestasi, a.gravida, a.para, a.abortus,
   a.tinggi_fundus, a.presentasi, a.djj, a.tekanan_darah, a.berat_badan, a.protein_urine, a.edema, a.catatan, a.gambar, a.created_at, a.created_by';
const ANC_PILIHAN = [
   'presentasi' => ['Sefalik', 'Bokong', 'Lintang', 'Belum Menetap'],
   'protein_urine' => ['Negatif', 'Trace', '+1', '+2', '+3'],
   'edema' => ['Tidak Ada', 'Ringan', 'Sedang', 'Berat'],
];

try {
   $no = trim((string) ($_GET['no'] ?? ''));
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         isset($_GET['id']) ? ancGetId((int) $_GET['id'], $no, $idc) : ancList($no, $idc);
         break;
      case 'POST':
         ancSimpan($no, $idc, $pengguna);
         break;
      case 'DELETE':
         ancHapus((int) ($_GET['id'] ?? 0), $no, $idc);
         break;
      default:
         ancBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[visit/persalinanAncController] ' . $e->getMessage());
   ancBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

/** Kunjungan milik faskes sesi -> [id_patient, visit_status, visit_date] atau null */
function ancKunjungan(string $no, int $idc): ?array
{
   global $koneksi;
   if ($no === '') return null;
   $st = $koneksi->prepare("SELECT id_patient, visit_status, visit_date FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('si', $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function ancCari(int $id, int $idc): ?array
{
   global $koneksi;
   $c = (string) $idc;
   $st = $koneksi->prepare("SELECT " . ANC_KOLOM . " FROM pasien_persalinan_anc a WHERE a.id_anc = ? AND a.id_customer = ? LIMIT 1");
   $st->bind_param('is', $id, $c);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function ancList(string $no, int $idc): void
{
   global $koneksi;
   $k = ancKunjungan($no, $idc);
   if (!$k) ancBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   $c = (string) $idc;
   $pasien = (string) $k['id_patient'];
   // semua catatan ANC pasien ini di faskes ini (riwayat kehamilan), terbaru di atas
   $st = $koneksi->prepare("SELECT " . ANC_KOLOM . ", v.visit_date
      FROM pasien_persalinan_anc a
      JOIN pasien_visit v ON v.visit_ID = a.id_visit COLLATE utf8mb4_general_ci AND v.id_customer = ?   -- collation kedua tabel berbeda
      WHERE v.id_patient = ? AND a.id_customer = ?
      ORDER BY a.tanggal_kontrol DESC, a.id_anc DESC LIMIT 300");
   $st->bind_param('iss', $idc, $pasien, $c);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   foreach ($data as &$r) $r['ini'] = $r['id_visit'] === $no;
   unset($r);
   ancBalas(200, [
      'status' => 'success',
      'data' => $data,
      'kunjungan' => ['batal' => (int) $k['visit_status'] === 99, 'tanggal' => $k['visit_date']],
   ]);
}

function ancGetId(int $id, string $no, int $idc): void
{
   $r = ancCari($id, $idc);
   if (!$r) ancBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   $r['ini'] = $r['id_visit'] === $no;
   ancBalas(200, ['status' => 'success', 'data' => $r]);
}

/** Tanggal Y-m-d valid atau null; "" -> null. */
function ancTgl(string $v): ?string
{
   if ($v === '') return null;
   $d = DateTime::createFromFormat('!Y-m-d', $v);
   return ($d && $d->format('Y-m-d') === $v) ? $v : 'X';
}

/** Validasi isi form -> nilai siap simpan (string|null). */
function ancIsi(array $in): array
{
   $t = fn($k, $max) => mb_substr(trim((string) (is_scalar($in[$k] ?? null) ? $in[$k] : '')), 0, $max);
   $tolak = fn($pesan, $f) => ancBalas(422, ['status' => 'error', 'message' => $pesan, 'field' => $f]);
   // angka pertama dari teks ("150x/i" -> 150, "20 cm" -> 20, "73,9" -> 73.9)
   $angka = function ($k, $min, $max, $label, $satuan = '') use ($t, $tolak) {
      $v = $t($k, 50);
      if ($v === '') return null;
      if (!preg_match('/^\s*(\d+(?:[.,]\d+)?)/', $v, $m)) $tolak($label . ' harus berupa angka.', $k);
      $n = (float) str_replace(',', '.', $m[1]);
      if ($n < $min || $n > $max) $tolak("$label harus {$min}–{$max}{$satuan}.", $k);
      return rtrim(rtrim(number_format($n, 1, '.', ''), '0'), '.');
   };

   $d = [];
   $d['tanggal_kontrol'] = ancTgl($t('tanggal_kontrol', 10));
   if ($d['tanggal_kontrol'] === null || $d['tanggal_kontrol'] === 'X') $tolak('Tanggal kontrol wajib diisi dengan tanggal yang valid.', 'tanggal_kontrol');
   if ($d['tanggal_kontrol'] > date('Y-m-d')) $tolak('Tanggal kontrol tidak boleh di masa depan.', 'tanggal_kontrol');

   $d['hpht'] = ancTgl($t('hpht', 10));
   if ($d['hpht'] === 'X') $tolak('HPHT tidak valid.', 'hpht');
   $hari = null;
   if ($d['hpht'] !== null) {
      if ($d['hpht'] > $d['tanggal_kontrol']) $tolak('HPHT tidak boleh setelah tanggal kontrol.', 'hpht');
      $hari = (int) (new DateTime($d['hpht']))->diff(new DateTime($d['tanggal_kontrol']))->days;
      if ($hari > 45 * 7) $tolak('HPHT lebih dari 45 minggu sebelum tanggal kontrol — periksa kembali.', 'hpht');
   }

   $d['taksiran_persalinan'] = ancTgl($t('taksiran_persalinan', 10));
   if ($d['taksiran_persalinan'] === 'X') $tolak('Taksiran persalinan tidak valid.', 'taksiran_persalinan');
   if ($d['taksiran_persalinan'] === null && $d['hpht'] !== null) {
      $d['taksiran_persalinan'] = (new DateTime($d['hpht']))->modify('+280 days')->format('Y-m-d');   // Naegele
   }
   if ($d['taksiran_persalinan'] !== null && $d['hpht'] !== null) {
      $selisih = (int) (new DateTime($d['hpht']))->diff(new DateTime($d['taksiran_persalinan']))->format('%r%a');
      if ($selisih < 200 || $selisih > 320) $tolak('Taksiran persalinan tidak sesuai HPHT (normalnya HPHT + 280 hari). Kosongkan agar dihitung otomatis.', 'taksiran_persalinan');
   }

   $d['usia_gestasi'] = $t('usia_gestasi', 50);
   if ($d['usia_gestasi'] === '' && $hari !== null) {
      $d['usia_gestasi'] = intdiv($hari, 7) . ' minggu' . ($hari % 7 ? ' ' . ($hari % 7) . ' hari' : '');
   }
   if ($d['usia_gestasi'] === '') $d['usia_gestasi'] = null;

   foreach (['gravida' => 'G', 'para' => 'P', 'abortus' => 'A'] as $k => $lbl) {
      $v = $t($k, 5);
      if ($v !== '' && !preg_match('/^\d{1,2}$/', $v)) $tolak("$lbl (" . ucfirst($k) . ") harus angka 0–20.", $k);
      $d[$k] = $v === '' ? null : (string) (int) $v;
   }
   if ($d['gravida'] !== null && (int) $d['gravida'] < 1) $tolak('Gravida minimal 1 (kehamilan ini).', 'gravida');
   if ($d['gravida'] !== null && (int) ($d['para'] ?? 0) + (int) ($d['abortus'] ?? 0) > (int) $d['gravida'] - 1) {
      $tolak('Para + Abortus tidak boleh melebihi Gravida − 1 (kehamilan sekarang belum dihitung).', 'para');
   }

   $td = preg_replace('/\s+/', '', $t('tekanan_darah', 20));
   if ($td === '') $d['tekanan_darah'] = null;
   else {
      if (!preg_match('/^(\d{2,3})\/(\d{2,3})$/', $td, $m)) $tolak('Tekanan darah ditulis sistole/diastole, mis. 120/80.', 'tekanan_darah');
      if ($m[1] < 60 || $m[1] > 260 || $m[2] < 30 || $m[2] > 160 || $m[2] >= $m[1]) $tolak('Nilai tekanan darah tidak wajar.', 'tekanan_darah');
      $d['tekanan_darah'] = (int) $m[1] . '/' . (int) $m[2];
   }
   $d['berat_badan'] = $angka('berat_badan', 25, 250, 'Berat badan', ' kg');
   $d['tinggi_fundus'] = $angka('tinggi_fundus', 0, 50, 'Tinggi fundus', ' cm');
   $d['djj'] = $angka('djj', 60, 220, 'DJJ', ' x/menit');

   foreach (ANC_PILIHAN as $k => $opsi) {
      $v = $t($k, 50);
      if ($v !== '' && !in_array($v, $opsi, true)) $tolak('Pilihan ' . str_replace('_', ' ', $k) . ' tidak valid.', $k);
      $d[$k] = $v === '' ? null : $v;
   }
   $d['catatan'] = $t('catatan', 2000);
   $d['catatan'] = $d['catatan'] === '' ? null : $d['catatan'];
   return $d;
}

/** Cek file unggahan sebelum menyentuh database -> [tmp, ext] | null bila tidak ada file. */
function ancCekGambar(): ?array
{
   $f = $_FILES['gambar'] ?? null;
   if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
   $tolak = fn($p) => ancBalas(422, ['status' => 'error', 'message' => $p, 'field' => 'gambar']);
   if (in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) $tolak('Ukuran gambar melebihi batas server.');
   if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) $tolak('Gambar gagal diunggah, coba lagi.');
   if ($f['size'] > 5 * 1024 * 1024) $tolak('Ukuran gambar maksimal 5 MB.');
   $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
   $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
   if (!$ext) $tolak('File harus gambar JPG, PNG, atau WEBP.');
   return [$f['tmp_name'], $ext];
}

function ancSimpan(string $no, int $idc, string $pengguna): void
{
   global $koneksi;
   if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
      ancBalas(413, ['status' => 'error', 'message' => 'Data terlalu besar (gambar melebihi batas unggah server).', 'field' => 'gambar']);
   }
   $id = (int) ($_POST['id_anc'] ?? 0);
   $lama = null;
   if ($id > 0) {
      $lama = ancCari($id, $idc);
      if (!$lama) ancBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
      if ($lama['id_visit'] !== $no) ancBalas(403, ['status' => 'error', 'message' => 'Catatan dari kunjungan lain hanya bisa diubah dari kunjungan tersebut.']);
   }
   $k = ancKunjungan($no, $idc);
   if (!$k) ancBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   if (!$lama && (int) $k['visit_status'] === 99) ancBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);

   $d = ancIsi($_POST);
   $gbr = ancCekGambar();
   $hapusGambar = !$gbr && !empty($_POST['hapus_gambar']);

   $koneksi->begin_transaction();
   $fileBaru = null;
   try {
      if ($lama) {
         $set = implode(', ', array_map(fn($c) => "$c = ?", array_keys($d)));
         $nilai = array_values($d);
         $nilai[] = $id;
         $nilai[] = (string) $idc;
         $__lg ??= logAmbilBaris($koneksi, 'pasien_persalinan_anc', 'id_anc', $id); // log: kondisi sebelum diubah/dihapus
         $st = $koneksi->prepare("UPDATE pasien_persalinan_anc SET $set WHERE id_anc = ? AND id_customer = ?");
         $st->bind_param(str_repeat('s', count($d)) . 'is', ...$nilai);
         $st->execute();
         $st->close();
      } else {
         $rm = mb_substr(trim((string) ($_POST['nomor_rm'] ?? '')), 0, 50);
         $kol = array_merge(['anc_number', 'id_visit', 'id_customer', 'nomor_rm', 'created_by'], array_keys($d));
         $nilai = array_merge([ancNomor(), $no, (string) $idc, $rm, $pengguna], array_values($d));
         $st = $koneksi->prepare("INSERT INTO pasien_persalinan_anc (" . implode(', ', $kol) . ") VALUES (" . implode(', ', array_fill(0, count($kol), '?')) . ")");
         $st->bind_param(str_repeat('s', count($kol)), ...$nilai);
         $st->execute();
         $id = $st->insert_id;
         $st->close();
      }
      if ($gbr || $hapusGambar) {
         if ($gbr) {
            if (!is_dir(ANC_DIR) && !mkdir(ANC_DIR, 0755, true) && !is_dir(ANC_DIR)) throw new RuntimeException('Folder unggahan tidak bisa dibuat.');
            $fileBaru = 'anc_' . $id . '_' . bin2hex(random_bytes(6)) . '.' . $gbr[1];
            if (!move_uploaded_file($gbr[0], ANC_DIR . $fileBaru)) throw new RuntimeException('Gagal memindahkan file gambar.');
         }
         $__lg ??= logAmbilBaris($koneksi, 'pasien_persalinan_anc', 'id_anc', $id); // log: kondisi sebelum diubah/dihapus
         $st = $koneksi->prepare("UPDATE pasien_persalinan_anc SET gambar = ? WHERE id_anc = ?");
         $st->bind_param('si', $fileBaru, $id);
         $st->execute();
         $st->close();
      }
      $koneksi->commit();
   } catch (Throwable $e) {
      $koneksi->rollback();
      if ($fileBaru && is_file(ANC_DIR . $fileBaru)) @unlink(ANC_DIR . $fileBaru);
      throw $e;
   }
   // file lama dibuang setelah data tersimpan
   if ($lama && ($gbr || $hapusGambar) && !empty($lama['gambar']) && is_file(ANC_DIR . basename($lama['gambar']))) @unlink(ANC_DIR . basename($lama['gambar']));
   logBaris($koneksi, 'klinis', 'pasien_persalinan_anc', 'id_anc', $id, $__lg ?? null, ['label' => 'ANC / persalinan', 'visit_ID' => $no], 'ANC / persalinan');
   ancBalas(200, ['status' => 'success', 'message' => $lama ? 'Catatan ANC diperbarui.' : 'Catatan ANC disimpan.', 'id' => $id]);
}

function ancHapus(int $id, string $no, int $idc): void
{
   global $koneksi;
   $lama = $id > 0 ? ancCari($id, $idc) : null;
   if (!$lama) ancBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah terhapus.']);
   if ($lama['id_visit'] !== $no) ancBalas(403, ['status' => 'error', 'message' => 'Catatan dari kunjungan lain hanya bisa dihapus dari kunjungan tersebut.']);
   $c = (string) $idc;
   $__lg ??= logAmbilBaris($koneksi, 'pasien_persalinan_anc', 'id_anc', $id); // log: kondisi sebelum diubah/dihapus
   $st = $koneksi->prepare("DELETE FROM pasien_persalinan_anc WHERE id_anc = ? AND id_customer = ?");
   $st->bind_param('is', $id, $c);
   $st->execute();
   $st->close();
   if (!empty($lama['gambar']) && is_file(ANC_DIR . basename($lama['gambar']))) @unlink(ANC_DIR . basename($lama['gambar']));
   logBaris($koneksi, 'klinis', 'pasien_persalinan_anc', 'id_anc', $id, $__lg ?? null, ['label' => 'ANC / persalinan', 'visit_ID' => $no], 'ANC / persalinan');
   ancBalas(200, ['status' => 'success', 'message' => 'Catatan ANC dihapus.']);
}

function ancNomor(): string
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT 1 FROM pasien_persalinan_anc WHERE anc_number = ? LIMIT 1");
   for ($i = 0; $i < 20; $i++) {
      $n = 'ANC-' . random_int(100000, 999999);
      $st->bind_param('s', $n);
      $st->execute();
      if (!$st->get_result()->fetch_row()) {
         $st->close();
         return $n;
      }
   }
   $st->close();
   throw new RuntimeException('Gagal membuat nomor ANC unik.');
}
