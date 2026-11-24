<?php
/**
 * Instruksi dokter / catatan perjalanan penyakit rawat inap (module/admin/instruksi). Tabel visit_cppt_dokter.
 *   GET    ?no=<visit_ID>         -> { status, data:[...], kunjungan:{batal, tanggal, hari_ini} }
 *   GET    ?no=..&id=<id_cppt>    -> 1 catatan
 *   POST   ?no=..  (form)          -> tambah
 *   PUT    ?no=..  (form, id_cppt) -> ubah
 *   DELETE ?no=..&id=<id>         -> hapus
 * Perbaikan: tanpa cek login & batas faskes (id_customer tidak pernah diisi; baca/ubah/hapus catatan faskes lain lewat id);
 * daftar difilter nomor RM dari URL (format beda -> kosong); PUT bisa memindahkan catatan ke kunjungan/RM lain; kolom
 * "user_entry" salah ketik saat ubah (kolomnya users_entry); petugas diketik bebas; tanpa validasi tanggal/isi;
 * pesan error SQL dikirim ke browser.
 */
include '../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$pengguna = (string) ($_SESSION['fullname'] ?? ($_SESSION['username'] ?? ''));
session_write_close();

function inBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) inBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

const IN_KOLOM = 'id_cppt, cppt_number, visit_ID, cppt_date, cppt_time, perjalanan, tindakan, ku, pemeriksaan_fisik, diagnosa, pengobatan, users_entry, created_at';
const IN_TEKS = ['perjalanan', 'tindakan', 'ku', 'pemeriksaan_fisik', 'diagnosa', 'pengobatan'];

try {
   $no = trim((string) ($_GET['no'] ?? ''));
   $k = inKunjungan($no, $idc);
   if (!$k) inBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         isset($_GET['id']) ? inGetId((int) $_GET['id'], $no, $idc) : inList($no, $k, $idc);
         break;
      case 'POST':
         inSimpan($_POST, $no, $k, $idc, $pengguna, false);
         break;
      case 'PUT':
         parse_str(file_get_contents('php://input'), $put);
         inSimpan($put, $no, $k, $idc, $pengguna, true);
         break;
      case 'DELETE':
         inHapus((int) ($_GET['id'] ?? 0), $no, $idc);
         break;
      default:
         inBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[ranap/instruksiController] ' . $e->getMessage());
   inBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

function inKunjungan(string $no, int $idc): ?array
{
   global $koneksi;
   if ($no === '') return null;
   $st = $koneksi->prepare("SELECT v.visit_status, v.visit_date, p.nomor_rm FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
      WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1");
   $st->bind_param('si', $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function inCari(int $id, string $no, int $idc): ?array
{
   global $koneksi;
   $c = (string) $idc;
   $st = $koneksi->prepare("SELECT " . IN_KOLOM . " FROM visit_cppt_dokter WHERE id_cppt = ? AND visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('iss', $id, $no, $c);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function inList(string $no, array $k, int $idc): void
{
   global $koneksi;
   $c = (string) $idc;
   $st = $koneksi->prepare("SELECT " . IN_KOLOM . " FROM visit_cppt_dokter WHERE visit_ID = ? AND id_customer = ?
      ORDER BY cppt_date ASC, cppt_time ASC, id_cppt ASC");
   $st->bind_param('ss', $no, $c);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   inBalas(200, [
      'status' => 'success',
      'data' => $data,
      'kunjungan' => ['batal' => (int) $k['visit_status'] === 99, 'tanggal' => substr((string) $k['visit_date'], 0, 10), 'hari_ini' => date('Y-m-d')],
   ]);
}

function inGetId(int $id, string $no, int $idc): void
{
   $r = inCari($id, $no, $idc);
   if (!$r) inBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   inBalas(200, ['status' => 'success', 'data' => $r]);
}

/** Validasi isi form. */
function inIsi(array $in, array $k): array
{
   $t = fn($f, $max) => mb_substr(trim((string) (is_scalar($in[$f] ?? null) ? $in[$f] : '')), 0, $max);
   $tolak = fn($p, $f) => inBalas(422, ['status' => 'error', 'message' => $p, 'field' => $f]);
   $d = ['cppt_date' => $t('cppt_date', 10), 'cppt_time' => $t('cppt_time', 8)];
   $tg = DateTime::createFromFormat('!Y-m-d', $d['cppt_date']);
   if (!$tg || $tg->format('Y-m-d') !== $d['cppt_date']) $tolak('Tanggal wajib diisi dengan tanggal yang valid.', 'cppt_date');
   if ($d['cppt_date'] > date('Y-m-d')) $tolak('Tanggal tidak boleh di masa depan.', 'cppt_date');
   $awal = substr((string) $k['visit_date'], 0, 10);
   if ($awal !== '' && $d['cppt_date'] < $awal) $tolak('Tanggal tidak boleh sebelum tanggal kunjungan (' . $awal . ').', 'cppt_date');
   if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $d['cppt_time'])) $tolak('Jam wajib diisi (JJ:MM).', 'cppt_time');
   if ($d['cppt_date'] === date('Y-m-d') && substr($d['cppt_time'], 0, 5) > date('H:i', time() + 300)) $tolak('Jam tidak boleh di masa depan.', 'cppt_time');
   foreach (IN_TEKS as $f) $d[$f] = $t($f, 10000);
   if ($d['perjalanan'] === '' && $d['tindakan'] === '') $tolak('Isi perjalanan penyakit atau instruksi dokter.', 'perjalanan');
   return $d;
}

function inSimpan(array $in, string $no, array $k, int $idc, string $pengguna, bool $ubah): void
{
   global $koneksi;
   $c = (string) $idc;
   if ($ubah) {
      $id = (int) ($in['id_cppt'] ?? 0);
      if (!$id || !inCari($id, $no, $idc)) inBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   } elseif ((int) $k['visit_status'] === 99) {
      inBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
   }
   $d = inIsi($in, $k);
   if ($ubah) {
      // visit_ID / nomor_rm / petugas pencatat tidak ikut diubah
      $st = $koneksi->prepare("UPDATE visit_cppt_dokter SET cppt_date = ?, cppt_time = ?, perjalanan = ?, tindakan = ?, ku = ?, pemeriksaan_fisik = ?, diagnosa = ?, pengobatan = ?
         WHERE id_cppt = ? AND visit_ID = ? AND id_customer = ?");
      $st->bind_param('ssssssssiss', $d['cppt_date'], $d['cppt_time'], $d['perjalanan'], $d['tindakan'], $d['ku'], $d['pemeriksaan_fisik'], $d['diagnosa'], $d['pengobatan'], $id, $no, $c);
      $st->execute();
      $st->close();
      inBalas(200, ['status' => 'success', 'message' => 'Instruksi diperbarui.', 'id' => $id]);
   }
   $nomor = inNomor();
   $rm = mb_substr((string) $k['nomor_rm'], 0, 20);
   $st = $koneksi->prepare("INSERT INTO visit_cppt_dokter (cppt_number, id_customer, visit_ID, nomor_rm, cppt_date, cppt_time, perjalanan, tindakan, ku, pemeriksaan_fisik, diagnosa, pengobatan, users_entry)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
   $st->bind_param('sssssssssssss', $nomor, $c, $no, $rm, $d['cppt_date'], $d['cppt_time'], $d['perjalanan'], $d['tindakan'], $d['ku'], $d['pemeriksaan_fisik'], $d['diagnosa'], $d['pengobatan'], $pengguna);
   $st->execute();
   $id = $st->insert_id;
   $st->close();
   inBalas(200, ['status' => 'success', 'message' => 'Instruksi ditambahkan.', 'id' => $id]);
}

function inHapus(int $id, string $no, int $idc): void
{
   global $koneksi;
   if (!$id || !inCari($id, $no, $idc)) inBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah terhapus.']);
   $c = (string) $idc;
   $st = $koneksi->prepare("DELETE FROM visit_cppt_dokter WHERE id_cppt = ? AND visit_ID = ? AND id_customer = ?");
   $st->bind_param('iss', $id, $no, $c);
   $st->execute();
   $st->close();
   inBalas(200, ['status' => 'success', 'message' => 'Instruksi dihapus.']);
}

function inNomor(): string
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT 1 FROM visit_cppt_dokter WHERE cppt_number = ? LIMIT 1");
   for ($i = 0; $i < 20; $i++) {
      $n = 'DCT-' . random_int(100000, 999999);
      $st->bind_param('s', $n);
      $st->execute();
      if (!$st->get_result()->fetch_row()) {
         $st->close();
         return $n;
      }
   }
   $st->close();
   throw new RuntimeException('Gagal membuat nomor instruksi unik.');
}
