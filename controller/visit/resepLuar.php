<?php
/**
 * Resep luar per kunjungan (module/admin/resep, component_resep, farmasi_order_luar_detail).
 *   GET    ?no=<visit_ID>          -> { status, data:[...] }
 *   GET    ?no=..&id=<id_resep>    -> { status, data:{...} }
 *   POST   (form)                  -> tambah { status, message, id }
 *   PUT    (form, id_resep)        -> ubah
 *   DELETE ?id=<id_resep>          -> hapus
 * Perbaikan: tabel resep_luar tidak punya id_customer dan dulu tidak dicek sama sekali -> resep faskes lain bisa
 * dibaca/diubah/dihapus lewat id atau nomor kunjungan; resep bisa ditambahkan ke kunjungan faskes lain; PUT bisa
 * memindahkan resep ke kunjungan lain (id_visit); pesan error SQL dikirim ke browser; tanpa validasi isi.
 * Kepemilikan dicek lewat pasien_visit (visit_ID + id_customer sesi).
 */
include '../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();

function rlBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) rlBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

const RL_FIELD = ['prescriptio' => 'Prescriptio', 'signatura' => 'Signatura', 'subscriptio' => 'Subscriptio', 'pro' => 'Pro'];
const RL_WAJIB = ['prescriptio', 'signatura'];
const RL_SELECT = "SELECT r.id_resep, r.id_visit, r.resep_number, r.prescriptio, r.signatura, r.subscriptio, r.pro, r.created_at
   FROM resep_luar r INNER JOIN pasien_visit v ON v.visit_ID = r.id_visit AND v.id_customer = ?";

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         isset($_GET['id']) ? rlGetId((int) $_GET['id'], $idc) : rlGetData(trim((string) ($_GET['no'] ?? '')), $idc);
         break;
      case 'POST':
         rlCreate($_POST, $idc);
         break;
      case 'PUT':
         parse_str(file_get_contents('php://input'), $put);
         rlUpdate($put, $idc);
         break;
      case 'DELETE':
         rlDelete((int) ($_GET['id'] ?? 0), $idc);
         break;
      default:
         rlBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[visit/resepLuar] ' . $e->getMessage());
   rlBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

/** Ambil isi resep dari input + validasi. */
function rlIsi(array $in): array
{
   $d = [];
   foreach (RL_FIELD as $k => $label) {
      $v = is_scalar($in[$k] ?? null) ? trim((string) $in[$k]) : '';
      if (mb_strlen($v) > 2000) rlBalas(422, ['status' => 'error', 'message' => "$label maksimal 2000 karakter."]);
      if ($v === '' && in_array($k, RL_WAJIB, true)) rlBalas(422, ['status' => 'error', 'message' => "$label wajib diisi.", 'field' => $k]);
      $d[$k] = $v;
   }
   return $d;
}

function rlCari(int $id, int $idc): ?array
{
   global $koneksi;
   $st = $koneksi->prepare(RL_SELECT . " WHERE r.id_resep = ? LIMIT 1");
   $st->bind_param('ii', $idc, $id);
   $st->execute();
   $row = $st->get_result()->fetch_assoc();
   $st->close();
   return $row ?: null;
}

function rlGetData(string $no, int $idc): void
{
   global $koneksi;
   $st = $koneksi->prepare(RL_SELECT . " WHERE r.id_visit = ? ORDER BY r.id_resep ASC");
   $st->bind_param('is', $idc, $no);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   rlBalas(200, ['status' => 'success', 'data' => $data]);
}

function rlGetId(int $id, int $idc): void
{
   $row = rlCari($id, $idc);
   if (!$row) rlBalas(404, ['status' => 'error', 'message' => 'Resep tidak ditemukan.']);
   rlBalas(200, ['status' => 'success', 'data' => $row]);
}

function rlCreate(array $in, int $idc): void
{
   global $koneksi;
   $visit = trim((string) ($in['id_visit'] ?? ''));
   if ($visit === '') rlBalas(422, ['status' => 'error', 'message' => 'Nomor kunjungan tidak ditemukan.']);
   $d = rlIsi($in);

   $st = $koneksi->prepare("SELECT visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('si', $visit, $idc);
   $st->execute();
   $kunj = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$kunj) rlBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   if ((int) $kunj['visit_status'] === 99) rlBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);

   $nomor = rlNomor();
   $st = $koneksi->prepare("INSERT INTO resep_luar (resep_number, id_visit, prescriptio, signatura, subscriptio, pro) VALUES (?, ?, ?, ?, ?, ?)");
   $st->bind_param('ssssss', $nomor, $visit, $d['prescriptio'], $d['signatura'], $d['subscriptio'], $d['pro']);
   $st->execute();
   $id = $st->insert_id;
   $st->close();
   rlBalas(200, ['status' => 'success', 'message' => 'Resep luar ditambahkan.', 'id' => $id, 'resep_number' => $nomor]);
}

function rlUpdate(array $in, int $idc): void
{
   global $koneksi;
   $id = (int) ($in['id_resep'] ?? ($_GET['id'] ?? 0));
   if ($id <= 0 || !rlCari($id, $idc)) rlBalas(404, ['status' => 'error', 'message' => 'Resep tidak ditemukan.']);
   $d = rlIsi($in);
   // id_visit sengaja tidak ikut diubah (dulu bisa memindahkan resep ke kunjungan lain)
   $st = $koneksi->prepare("UPDATE resep_luar SET prescriptio = ?, signatura = ?, subscriptio = ?, pro = ? WHERE id_resep = ?");
   $st->bind_param('ssssi', $d['prescriptio'], $d['signatura'], $d['subscriptio'], $d['pro'], $id);
   $st->execute();
   $st->close();
   rlBalas(200, ['status' => 'success', 'message' => 'Resep luar diperbarui.']);
}

function rlDelete(int $id, int $idc): void
{
   global $koneksi;
   if ($id <= 0 || !rlCari($id, $idc)) rlBalas(404, ['status' => 'error', 'message' => 'Resep tidak ditemukan atau sudah terhapus.']);
   $st = $koneksi->prepare("DELETE FROM resep_luar WHERE id_resep = ?");
   $st->bind_param('i', $id);
   $st->execute();
   $st->close();
   rlBalas(200, ['status' => 'success', 'message' => 'Resep luar dihapus.']);
}

/** Nomor resep unik DCT-XXXXXX (format lama dipertahankan). */
function rlNomor(): string
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT 1 FROM resep_luar WHERE resep_number = ? LIMIT 1");
   for ($i = 0; $i < 20; $i++) {
      $nomor = 'DCT-' . random_int(100000, 999999);
      $st->bind_param('s', $nomor);
      $st->execute();
      if (!$st->get_result()->fetch_row()) {
         $st->close();
         return $nomor;
      }
   }
   $st->close();
   throw new RuntimeException('Gagal membuat nomor resep unik.');
}
