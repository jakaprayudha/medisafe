<?php
/**
 * Pemeriksaan USG per kunjungan (module/admin/form_usg, cetak print/formulir_usg). Tabel usg_results, foto di uploads/usg/.
 * Satu baris = satu pemeriksaan dengan maksimal 3 foto (usg1..usg3).
 *   GET    ?no=<visit_ID>                -> { status, data:[...], kunjungan:{batal, tanggal, dokter, hari_ini} }
 *   POST   ?no=..  multipart              -> tambah / ubah (id_usg diisi)
 *          tanggal_pemeriksaan, usia_kandungan, dokter, interpretasi, file usg1|usg2|usg3, hapus_usg1..3=1
 *   DELETE ?no=..&id=<id_usg>            -> hapus pemeriksaan + foto
 * Perbaikan: tanpa cek login & batas faskes (tabel tanpa id_customer -> dibatasi lewat kunjungan); semua input
 * disisipkan ke SQL (SQL injection); hapus/ambil per id tanpa batas kunjungan; USG 2/3 selalu ditempel ke baris pertama
 * kunjungan (pemeriksaan kedua tidak bisa punya foto 2/3) dan bila belum ada USG 1 foto tetap tersimpan di folder;
 * USG 2/3 mengabaikan tanggal/usia/interpretasi yang diisi; isi file tidak divalidasi (hanya ekstensi) & tanpa batas
 * ukuran; file tidak ikut terhapus saat data dihapus; pesan error SQL dikirim ke browser.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

const USG_DIR = 'uploads/usg/';
const USG_MAKS = 5 * 1024 * 1024;
const USG_SLOT = ['usg1', 'usg2', 'usg3'];

function usBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

$kunj = visitGuard($koneksi, 'visit_ID', (string) ($_GET['no'] ?? ''));
session_write_close();
$no = (string) $kunj['visit_ID'];
$root = dirname(__DIR__, 2) . '/';

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         usList($no, (int) $kunj['id_visit']);
         break;
      case 'POST':
         usSimpan($no, $kunj, $root);
         break;
      case 'DELETE':
         usHapus((int) ($_GET['id'] ?? 0), $no, $root);
         break;
      default:
         usBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[ranap/usgController] ' . $e->getMessage());
   usBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

function usCari(int $id, string $no, bool $kunci = false): ?array
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT id_usg, usg1, usg2, usg3 FROM usg_results WHERE id_usg = ? AND visit_ID = ? LIMIT 1" . ($kunci ? ' FOR UPDATE' : ''));
   $st->bind_param('is', $id, $no);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function usList(string $no, int $idVisit): void
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT id_usg, tanggal_pemeriksaan, usia_kandungan, usg1, usg2, usg3, dokter, interpretasi, created_at
      FROM usg_results WHERE visit_ID = ? ORDER BY tanggal_pemeriksaan DESC, id_usg DESC");
   $st->bind_param('s', $no);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   $st = $koneksi->prepare("SELECT visit_status, visit_date, id_doctor FROM pasien_visit WHERE id_visit = ? LIMIT 1");
   $st->bind_param('i', $idVisit);
   $st->execute();
   $v = $st->get_result()->fetch_assoc() ?: [];
   $st->close();
   usBalas(200, ['status' => 'success', 'data' => $data, 'kunjungan' => [
      'batal' => (int) ($v['visit_status'] ?? 0) === 99,
      'tanggal' => substr((string) ($v['visit_date'] ?? ''), 0, 10),
      'dokter' => (string) ($v['id_doctor'] ?? ''),   // kolom id_doctor berisi nama dokter
      'hari_ini' => date('Y-m-d'),
   ]]);
}

/** Validasi & simpan sementara 1 foto; kembalikan nama file baru. */
function usFoto(array $f, string $slot, string $root): string
{
   $tolak = fn($p) => usBalas(422, ['status' => 'error', 'message' => $p, 'field' => $slot]);
   if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) $tolak('Foto ' . strtoupper($slot) . ' gagal diunggah, coba lagi.');
   if ($f['size'] > USG_MAKS) $tolak('Ukuran foto ' . strtoupper($slot) . ' maksimal 5 MB.');
   $info = @getimagesize($f['tmp_name']);
   $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? null;
   if (!$info || !$ext) $tolak('Foto ' . strtoupper($slot) . ' harus berupa gambar JPG / PNG / WEBP.');
   $dir = $root . USG_DIR;
   if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) usBalas(500, ['status' => 'error', 'message' => 'Folder foto USG tidak tersedia.']);
   $nama = 'USG_' . bin2hex(random_bytes(8)) . '.' . $ext;
   if (!move_uploaded_file($f['tmp_name'], $dir . $nama)) usBalas(500, ['status' => 'error', 'message' => 'Gagal menyimpan foto.']);
   return $nama;
}

function usHapusFile(string $root, ?string $path): void
{
   $path = (string) $path;
   if (strpos($path, USG_DIR) !== 0) return;
   $f = $root . USG_DIR . basename($path);
   if (is_file($f)) @unlink($f);
}

function usSimpan(string $no, array $kunj, string $root): void
{
   global $koneksi;
   $id = (int) ($_POST['id_usg'] ?? 0);
   if (!$id && (int) $kunj['visit_status'] === 99) usBalas(409, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
   if ($id && !usCari($id, $no)) usBalas(404, ['status' => 'error', 'message' => 'Data USG tidak ditemukan.']);

   $t = fn($k, $max) => mb_substr(trim((string) (is_scalar($_POST[$k] ?? null) ? $_POST[$k] : '')), 0, $max);
   $tolak = fn($p, $f) => usBalas(422, ['status' => 'error', 'message' => $p, 'field' => $f]);
   $d = ['tanggal' => $t('tanggal_pemeriksaan', 10), 'usia' => $t('usia_kandungan', 50), 'dokter' => $t('dokter', 100), 'interpretasi' => $t('interpretasi', 10000)];
   $tg = DateTime::createFromFormat('!Y-m-d', $d['tanggal']);
   if (!$tg || $tg->format('Y-m-d') !== $d['tanggal']) $tolak('Tanggal pemeriksaan wajib diisi dengan tanggal yang valid.', 'tanggal_pemeriksaan');
   if ($d['tanggal'] > date('Y-m-d')) $tolak('Tanggal pemeriksaan tidak boleh di masa depan.', 'tanggal_pemeriksaan');
   if ($d['usia'] === '') $tolak('Usia kandungan wajib diisi.', 'usia_kandungan');
   if ($d['dokter'] === '') $tolak('Dokter pemeriksa wajib diisi.', 'dokter');
   if ($d['interpretasi'] === '') $tolak('Interpretasi wajib diisi.', 'interpretasi');

   $ada = array_filter(USG_SLOT, fn($s) => isset($_FILES[$s]) && ($_FILES[$s]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);
   $hapus = array_filter(USG_SLOT, fn($s) => !empty($_POST['hapus_' . $s]));
   if (!$id && !$ada) $tolak('Unggah minimal 1 foto USG.', 'usg1');

   $baru = [];
   try {
      foreach ($ada as $s) $baru[$s] = USG_DIR . usFoto($_FILES[$s], $s, $root);
      $rm = '';
      $st = $koneksi->prepare("SELECT p.nomor_rm FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient WHERE v.id_visit = ? LIMIT 1");
      $idv = (int) $kunj['id_visit'];
      $st->bind_param('i', $idv);
      $st->execute();
      $rm = mb_substr((string) ($st->get_result()->fetch_row()[0] ?? ''), 0, 50);
      $st->close();

      $dibuang = [];
      $koneksi->begin_transaction();
      try {
         if ($id) {
            $lama = usCari($id, $no, true);
            if (!$lama) throw new DomainException('hilang');
            $foto = [];
            foreach (USG_SLOT as $s) {
               $foto[$s] = $baru[$s] ?? (in_array($s, $hapus, true) ? null : $lama[$s]);
               if ($lama[$s] && $foto[$s] !== $lama[$s]) $dibuang[] = $lama[$s];
            }
            // foto dirapatkan ke depan (usg1 selalu terisi)
            $isi = array_values(array_filter($foto));
            if (!$isi) throw new LengthException('kosong');
            [$f1, $f2, $f3] = array_pad($isi, 3, null);
            $st = $koneksi->prepare("UPDATE usg_results SET tanggal_pemeriksaan = ?, usia_kandungan = ?, dokter = ?, interpretasi = ?, usg1 = ?, usg2 = ?, usg3 = ? WHERE id_usg = ? AND visit_ID = ?");
            $st->bind_param('sssssssis', $d['tanggal'], $d['usia'], $d['dokter'], $d['interpretasi'], $f1, $f2, $f3, $id, $no);
         } else {
            [$f1, $f2, $f3] = array_pad(array_values($baru), 3, null);
            $st = $koneksi->prepare("INSERT INTO usg_results (visit_ID, nomor_rm, usia_kandungan, tanggal_pemeriksaan, usg1, usg2, usg3, dokter, interpretasi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $st->bind_param('sssssssss', $no, $rm, $d['usia'], $d['tanggal'], $f1, $f2, $f3, $d['dokter'], $d['interpretasi']);
         }
         $st->execute();
         if (!$id) $id = $st->insert_id;
         $st->close();
         $koneksi->commit();
      } catch (Throwable $e) {
         $koneksi->rollback();
         throw $e;
      }
   } catch (Throwable $e) {
      foreach ($baru as $p) usHapusFile($root, $p);   // jangan tinggalkan file yatim
      if ($e instanceof DomainException) usBalas(404, ['status' => 'error', 'message' => 'Data USG tidak ditemukan.']);
      if ($e instanceof LengthException) usBalas(422, ['status' => 'error', 'message' => 'Minimal 1 foto USG harus tetap ada.', 'field' => 'usg1']);
      throw $e;
   }
   foreach ($dibuang as $p) usHapusFile($root, $p);
   usBalas(200, ['status' => 'success', 'message' => 'Pemeriksaan USG tersimpan.', 'id' => $id]);
}

function usHapus(int $id, string $no, string $root): void
{
   global $koneksi;
   $r = $id ? usCari($id, $no) : null;
   if (!$r) usBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah terhapus.']);
   $st = $koneksi->prepare("DELETE FROM usg_results WHERE id_usg = ? AND visit_ID = ?");
   $st->bind_param('is', $id, $no);
   $st->execute();
   $st->close();
   foreach (USG_SLOT as $s) usHapusFile($root, $r[$s]);
   usBalas(200, ['status' => 'success', 'message' => 'Pemeriksaan USG dihapus.']);
}
