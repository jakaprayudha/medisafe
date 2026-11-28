<?php
/**
 * CPPT per kunjungan (module/admin/cppt).
 *   GET    ?no=<visit_ID>              -> { status, data:[...], kunjungan:{batal}, akses:{verifikasi} }
 *   GET    ?no=..&id=<id_cppt>         -> 1 catatan
 *   POST   ?no=..  (form)              -> tambah
 *   PUT    ?no=..  (form, id_cppt)     -> ubah
 *   DELETE ?no=..&id=<id>              -> hapus
 *   POST   ?no=..&verify=<id>          -> verifikasi (dokter/admin);  &batal_verify=<id> -> batalkan verifikasi
 * Aturan: catatan terverifikasi terkunci (ubah/hapus harus batalkan verifikasi dulu); selain dokter/admin tidak bisa
 * mengubah/menghapus catatan dokter; profesi diambil dari role petugas di server.
 * Perbaikan: SELECT * JOIN ms_users ikut mengirim hash password & data pengguna ke browser; profesi dikirim dari
 * browser (bisa dipalsukan, huruf besar/kecil tidak konsisten -> catatan "Dokter" tetap muncul tombol verifikasi);
 * PUT bisa memindahkan catatan ke kunjungan/pasien lain; siapa pun bisa verifikasi; kunci "perawat tidak boleh ubah
 * catatan dokter" hanya di tampilan; tanpa validasi isi; petugas faskes lain bisa dipilih; urutan hanya per tanggal
 * (jam diabaikan); pesan error SQL ke browser; gagal hapus tanpa pesan.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$peran = strtolower((string) ($_SESSION['roles'] ?? ''));
session_write_close();

function cpBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) cpBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

const CP_KOLOM = 'c.id_cppt, c.cppt_number, c.visit_ID, c.cppt_date, c.cppt_time, c.subjective, c.objective, c.analysis, c.planning,
   c.instruction, c.users_entry, c.cppt_profesi, c.verifikasi, c.created_at';
/** Role yang boleh memverifikasi & mengelola catatan dokter. */
function cpPenyelia(string $peran): bool
{
   return in_array($peran, ['dokter', 'admin', 'superadmin'], true);
}

try {
   $no = trim((string) ($_GET['no'] ?? ''));
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         isset($_GET['id']) ? cpGetId((int) $_GET['id'], $no, $idc, $peran) : cpList($no, $idc, $peran);
         break;
      case 'POST':
         if (isset($_GET['verify'])) cpVerifikasi((int) $_GET['verify'], $no, $idc, $peran, 1);
         if (isset($_GET['batal_verify'])) cpVerifikasi((int) $_GET['batal_verify'], $no, $idc, $peran, 0);
         cpSimpan($_POST, $no, $idc, $peran, false);
         break;
      case 'PUT':
         parse_str(file_get_contents('php://input'), $put);
         cpSimpan($put, $no, $idc, $peran, true);
         break;
      case 'DELETE':
         cpHapus((int) ($_GET['id'] ?? 0), $no, $idc, $peran);
         break;
      default:
         cpBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[visit/cpptController] ' . $e->getMessage());
   cpBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

/** Kunjungan milik faskes sesi -> [id_patient, visit_status] | null */
function cpKunjungan(string $no, int $idc): ?array
{
   global $koneksi;
   if ($no === '') return null;
   $st = $koneksi->prepare("SELECT id_patient, visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('si', $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

/** Catatan milik kunjungan & faskes ini (dengan nama petugas). */
function cpCari(int $id, string $no, int $idc): ?array
{
   global $koneksi;
   $c = (string) $idc;
   $st = $koneksi->prepare("SELECT " . CP_KOLOM . ", u.fullname, u.roles
      FROM visit_cppt c LEFT JOIN ms_users u ON u.id_user = c.users_entry
      WHERE c.id_cppt = ? AND c.visit_ID = ? AND c.id_customer = ? LIMIT 1");
   $st->bind_param('iss', $id, $no, $c);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

/** Hak ubah/hapus satu catatan untuk role sesi -> null bila boleh, atau alasan. */
function cpKunci(array $r, string $peran): ?string
{
   if ((int) $r['verifikasi'] === 1) return 'Catatan sudah diverifikasi. Batalkan verifikasi terlebih dahulu (dokter/admin).';
   if (strtolower((string) $r['cppt_profesi']) === 'dokter' && !cpPenyelia($peran)) return 'Catatan dokter hanya bisa diubah oleh dokter atau admin.';
   return null;
}

function cpList(string $no, int $idc, string $peran): void
{
   global $koneksi;
   $k = cpKunjungan($no, $idc);
   if (!$k) cpBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   $c = (string) $idc;
   $st = $koneksi->prepare("SELECT " . CP_KOLOM . ", u.fullname, u.roles
      FROM visit_cppt c LEFT JOIN ms_users u ON u.id_user = c.users_entry
      WHERE c.visit_ID = ? AND c.id_customer = ?
      ORDER BY c.cppt_date ASC, c.cppt_time ASC, c.id_cppt ASC");
   $st->bind_param('ss', $no, $c);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   foreach ($data as &$r) {
      $r['profesi'] = strtolower((string) ($r['cppt_profesi'] ?: $r['roles']));
      $r['verifikasi'] = (int) $r['verifikasi'];
      $r['kunci'] = cpKunci($r, $peran);
      // perawat/bidan/dll. tidak melihat tombol aksi catatan dokter (aturan lama dipertahankan)
   }
   unset($r);
   cpBalas(200, [
      'status' => 'success',
      'data' => $data,
      'kunjungan' => ['batal' => (int) $k['visit_status'] === 99],
      'akses' => ['verifikasi' => cpPenyelia($peran), 'peran' => $peran],
   ]);
}

function cpGetId(int $id, string $no, int $idc, string $peran): void
{
   $r = cpCari($id, $no, $idc);
   if (!$r) cpBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   $r['kunci'] = cpKunci($r, $peran);
   cpBalas(200, ['status' => 'success', 'data' => $r]);
}

/** Validasi isi form + petugas faskes ini -> nilai siap simpan. */
function cpIsi(array $in, int $idc): array
{
   global $koneksi;
   $t = fn($k, $max) => mb_substr(trim((string) (is_scalar($in[$k] ?? null) ? $in[$k] : '')), 0, $max);
   $tolak = fn($pesan, $f) => cpBalas(422, ['status' => 'error', 'message' => $pesan, 'field' => $f]);
   $d = [];
   $d['cppt_date'] = $t('cppt_date', 10);
   $tg = DateTime::createFromFormat('!Y-m-d', $d['cppt_date']);
   if (!$tg || $tg->format('Y-m-d') !== $d['cppt_date']) $tolak('Tanggal wajib diisi dengan tanggal yang valid.', 'cppt_date');
   if ($d['cppt_date'] > date('Y-m-d')) $tolak('Tanggal tidak boleh di masa depan.', 'cppt_date');
   $d['cppt_time'] = $t('cppt_time', 8);
   if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $d['cppt_time'])) $tolak('Jam wajib diisi (format JJ:MM).', 'cppt_time');
   if ($d['cppt_date'] === date('Y-m-d') && substr($d['cppt_time'], 0, 5) > date('H:i', time() + 300)) $tolak('Jam tidak boleh di masa depan.', 'cppt_time');
   foreach (['subjective', 'objective', 'analysis', 'planning', 'instruction'] as $k) {
      $d[$k] = $t($k, 10000);
   }
   if ($d['subjective'] === '' && $d['objective'] === '' && $d['analysis'] === '' && $d['planning'] === '') {
      $tolak('Isi minimal salah satu dari S, O, A, atau P.', 'subjective');
   }
   $u = (int) $t('users_entry', 11);
   $st = $koneksi->prepare("SELECT roles FROM ms_users WHERE id_user = ? AND id_customer = ? AND roles NOT IN ('admin','superadmin') LIMIT 1");
   $st->bind_param('ii', $u, $idc);
   $st->execute();
   $role = $st->get_result()->fetch_row()[0] ?? null;
   $st->close();
   if ($u <= 0 || $role === null) $tolak('Pilih petugas (PPA) dari faskes ini.', 'users_entry');
   $d['users_entry'] = (string) $u;
   $d['cppt_profesi'] = strtolower((string) $role);
   return $d;
}

function cpSimpan(array $in, string $no, int $idc, string $peran, bool $ubah): void
{
   global $koneksi;
   $k = cpKunjungan($no, $idc);
   if (!$k) cpBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   if ($ubah) {
      $id = (int) ($in['id_cppt'] ?? 0);
      $lama = $id > 0 ? cpCari($id, $no, $idc) : null;
      if (!$lama) cpBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
      if ($alasan = cpKunci($lama, $peran)) cpBalas(423, ['status' => 'error', 'message' => $alasan]);
   } elseif ((int) $k['visit_status'] === 99) {
      cpBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
   }
   $d = cpIsi($in, $idc);
   if ($ubah && $d['cppt_profesi'] === 'dokter' && !cpPenyelia($peran)) {
      cpBalas(403, ['status' => 'error', 'message' => 'Catatan dokter hanya bisa diubah oleh dokter atau admin.', 'field' => 'users_entry']);
   }
   $c = (string) $idc;
   if ($ubah) {
      // visit_ID / id_patient sengaja tidak ikut diubah
      $st = $koneksi->prepare("UPDATE visit_cppt SET cppt_date = ?, cppt_time = ?, subjective = ?, objective = ?, analysis = ?, planning = ?,
         instruction = ?, users_entry = ?, cppt_profesi = ? WHERE id_cppt = ? AND id_customer = ? AND verifikasi = 0");
      $st->bind_param('sssssssssis', $d['cppt_date'], $d['cppt_time'], $d['subjective'], $d['objective'], $d['analysis'], $d['planning'],
         $d['instruction'], $d['users_entry'], $d['cppt_profesi'], $id, $c);
      $st->execute();
      $kena = $st->affected_rows;
      $st->close();
      if ($kena > 0) cpLog('ubah', $id, $no, $lama, $d, 'Catatan ' . ($lama['cppt_profesi'] ?? '') . ' diubah');
      cpBalas(200, ['status' => 'success', 'message' => 'CPPT diperbarui.', 'id' => $id]);
   }
   $nomor = cpNomor();
   $pasien = (int) $k['id_patient'];
   $st = $koneksi->prepare("INSERT INTO visit_cppt (cppt_number, id_customer, id_patient, visit_ID, cppt_date, cppt_time, subjective, objective,
      analysis, planning, instruction, users_entry, cppt_profesi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
   $st->bind_param('ssissssssssss', $nomor, $c, $pasien, $no, $d['cppt_date'], $d['cppt_time'], $d['subjective'], $d['objective'],
      $d['analysis'], $d['planning'], $d['instruction'], $d['users_entry'], $d['cppt_profesi']);
   $st->execute();
   $id = $st->insert_id;
   $st->close();
   if ($id) cpLog('tambah', (int) $id, $no, null, $d + ['cppt_number' => $nomor], 'Catatan ' . $d['cppt_profesi'] . ' ditambahkan');
   cpBalas(200, ['status' => 'success', 'message' => 'CPPT ditambahkan.', 'id' => $id]);
}

function cpHapus(int $id, string $no, int $idc, string $peran): void
{
   global $koneksi;
   $lama = $id > 0 ? cpCari($id, $no, $idc) : null;
   if (!$lama) cpBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah terhapus.']);
   if ($alasan = cpKunci($lama, $peran)) cpBalas(423, ['status' => 'error', 'message' => $alasan]);
   $c = (string) $idc;
   $st = $koneksi->prepare("DELETE FROM visit_cppt WHERE id_cppt = ? AND id_customer = ? AND verifikasi = 0");
   $st->bind_param('is', $id, $c);
   $st->execute();
   $kena = $st->affected_rows;
   $st->close();
   if ($kena > 0) cpLog('hapus', $id, $no, $lama, null, 'Catatan ' . ($lama['cppt_profesi'] ?? '') . ' dihapus');
   cpBalas(200, ['status' => 'success', 'message' => 'CPPT dihapus.']);
}

function cpVerifikasi(int $id, string $no, int $idc, string $peran, int $nilai): void
{
   global $koneksi;
   if (!cpPenyelia($peran)) cpBalas(403, ['status' => 'error', 'message' => 'Verifikasi hanya bisa dilakukan oleh dokter atau admin.']);
   $lama = $id > 0 ? cpCari($id, $no, $idc) : null;
   if (!$lama) cpBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   $c = (string) $idc;
   $st = $koneksi->prepare("UPDATE visit_cppt SET verifikasi = ? WHERE id_cppt = ? AND id_customer = ?");
   $st->bind_param('iis', $nilai, $id, $c);
   $st->execute();
   $kena = $st->affected_rows;
   $st->close();
   if ($kena > 0) cpLog($nilai ? 'verifikasi' : 'batal_verifikasi', $id, $no, ['verifikasi' => (int) $lama['verifikasi'], 'cppt_number' => $lama['cppt_number'] ?? null], ['verifikasi' => $nilai],
      $nilai ? 'CPPT diverifikasi' : 'Verifikasi CPPT dibatalkan');
   cpBalas(200, ['status' => 'success', 'message' => $nilai ? 'CPPT diverifikasi.' : 'Verifikasi dibatalkan.']);
}

/** Catat aktivitas CPPT (isi SOAP lama/baru ikut disimpan agar perubahan catatan medis bisa ditelusuri). */
function cpLog(string $aksi, int $id, string $no, ?array $lama, ?array $baru, string $ket = ''): void
{
   global $koneksi;
   $isi = fn(?array $r) => $r === null ? null : array_intersect_key($r, array_flip(['cppt_number', 'cppt_date', 'cppt_time', 'subjective', 'objective',
      'analysis', 'planning', 'instruction', 'users_entry', 'cppt_profesi', 'verifikasi']));
   $l = $isi($lama);
   $b = $isi($baru);
   if ($l !== null && $b !== null) [$l, $b] = logBeda($l, $b);
   logAktivitas($koneksi, 'cppt', $aksi, ['tipe' => 'cppt', 'id' => $id, 'label' => ($lama['cppt_number'] ?? $baru['cppt_number'] ?? '#' . $id), 'visit_ID' => $no],
      $ket, $l, $b);
}

function cpNomor(): string
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT 1 FROM visit_cppt WHERE cppt_number = ? LIMIT 1");
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
   throw new RuntimeException('Gagal membuat nomor CPPT unik.');
}
