<?php
/**
 * Vaksinasi pasien per kunjungan (module/admin/vaksin) + kirim ke SATUSEHAT (FHIR Immunization).
 *   GET    ?no=<visit_ID>               -> { status, data:[...], ringkas:{...} }
 *   POST   (form)                       -> simpan baru lalu langsung kirim ke SATUSEHAT
 *   PUT    (form, id_vaksin)            -> ubah lalu kirim ulang (PUT ke SATUSEHAT bila sudah terkirim)
 *   POST   ?kirim=<id_vaksin>           -> kirim ulang 1 data (aksi "Kirim ulang")
 *   POST   ?kirim=semua                 -> kirim ulang semua data kunjungan ini yang belum/gagal
 *   DELETE ?id=<id_vaksin>              -> hapus; bila sudah terkirim, dibatalkan dulu di SATUSEHAT (entered-in-error)
 * Data SELALU tersimpan di database walau pengiriman gagal; status & pesan gagal ditampilkan untuk dikirim ulang.
 * Perbaikan: tabel pasien_vaksin tidak punya id_customer dan dulu tidak dicek -> data vaksin faskes lain bisa
 * dibaca/diubah/dihapus; vaksin bisa ditambahkan ke kunjungan faskes lain; PUT bisa memindahkan data ke kunjungan
 * lain; tanpa validasi; pesan error SQL dikirim ke browser.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
require_once __DIR__ . '/../satusehat/immunizationService.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();

function vkBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) vkBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

const VK_SELECT = "SELECT v.id_vaksin, v.id_visit, v.vaksin_number, v.vaksin_name, v.kfa_code, v.vaksin_date, v.vaksin_time, v.vaksin_dosis,
      v.reason_code, v.route_code, v.dose_ml, v.lot_number, v.expiration_date, v.vaksin_notes, v.created_at,
      v.ss_status, v.ss_id, v.ss_error, v.ss_attempts, v.ss_last_try, v.ss_sent_at
   FROM pasien_vaksin v INNER JOIN pasien_visit pv ON pv.visit_ID = v.id_visit AND pv.id_customer = ?";

/** Kolom SATUSEHAT sudah ada? (migrations/20261123_pasien_vaksin_satusehat.sql) */
function vkSiapDb(): bool
{
   global $koneksi;
   $r = $koneksi->query("SHOW COLUMNS FROM pasien_vaksin LIKE 'ss_status'");
   return $r && $r->num_rows > 0;
}

try {
   $m = $_SERVER['REQUEST_METHOD'];
   if (!vkSiapDb()) {
      // migrasi belum dijalankan: tampilkan data lama apa adanya, tolak simpan dengan pesan jelas
      if ($m === 'GET') {
         $no = trim((string) ($_GET['no'] ?? ''));
         $st = $koneksi->prepare("SELECT v.id_vaksin, v.id_visit, v.vaksin_number, v.vaksin_name, v.vaksin_date, v.vaksin_dosis, v.vaksin_notes, v.created_at
            FROM pasien_vaksin v INNER JOIN pasien_visit pv ON pv.visit_ID = v.id_visit AND pv.id_customer = ?
            WHERE v.id_visit = ? ORDER BY v.vaksin_date DESC, v.id_vaksin DESC");
         $st->bind_param('is', $idc, $no);
         $st->execute();
         $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
         $st->close();
         vkBalas(200, ['status' => 'success', 'data' => $data, 'migrasi' => false]);
      }
      vkBalas(503, ['status' => 'error', 'message' => 'Database vaksin belum diperbarui untuk SATUSEHAT (migrasi 20261123_pasien_vaksin_satusehat.sql belum dijalankan). Hubungi admin sistem.']);
   }
   if ($m === 'GET') vkList(trim((string) ($_GET['no'] ?? '')), $idc);
   if ($m === 'POST' && isset($_GET['kirim'])) vkKirimUlang((string) $_GET['kirim'], trim((string) ($_GET['no'] ?? '')), $idc);
   if ($m === 'POST') vkSimpan($_POST, $idc, false);
   if ($m === 'PUT') {
      parse_str(file_get_contents('php://input'), $put);
      vkSimpan($put, $idc, true);
   }
   if ($m === 'DELETE') vkHapus((int) ($_GET['id'] ?? 0), $idc);
   vkBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
} catch (Throwable $e) {
   error_log('[visit/vaksin] ' . $e->getMessage());
   vkBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

function vkCari(int $id, int $idc): ?array
{
   global $koneksi;
   $st = $koneksi->prepare(VK_SELECT . " WHERE v.id_vaksin = ? LIMIT 1");
   $st->bind_param('ii', $idc, $id);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function vkList(string $no, int $idc): void
{
   global $koneksi;
   $st = $koneksi->prepare(VK_SELECT . " WHERE v.id_visit = ? ORDER BY v.vaksin_date DESC, v.id_vaksin DESC");
   $st->bind_param('is', $idc, $no);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();

   // kesiapan kirim (ditampilkan sebagai peringatan sebelum petugas menyimpan)
   $st = $koneksi->prepare("SELECT pv.id_encounter, pv.id_doctor, mp.idsh FROM pasien_visit pv INNER JOIN ms_patient mp ON mp.id_patient = pv.id_patient
      WHERE pv.visit_ID = ? AND pv.id_customer = ? LIMIT 1");
   $st->bind_param('si', $no, $idc);
   $st->execute();
   $kv = $st->get_result()->fetch_assoc() ?: [];
   $st->close();
   $st = $koneksi->prepare("SELECT client_id <> '' AND client_secret <> '' AND organization_id <> '' FROM setting_satusehat WHERE id_customer = ? LIMIT 1");
   $st->bind_param('i', $idc);
   $st->execute();
   $setting = (bool) (($st->get_result()->fetch_row() ?: [0])[0]);
   $st->close();

   vkBalas(200, ['status' => 'success', 'data' => $data, 'kesiapan' => [
      'setting' => $setting,
      'pasien_ihs' => trim((string) ($kv['idsh'] ?? '')) !== '',
      'encounter' => trim((string) ($kv['id_encounter'] ?? '')) !== '',
   ]]);
}

/** Validasi & normalisasi isi form. */
function vkIsi(array $in): array
{
   $t = fn($k, $max) => mb_substr(trim((string) (is_scalar($in[$k] ?? null) ? $in[$k] : '')), 0, $max);
   $kat = imVaksinKatalog();
   $d = [
      'kfa_code' => $t('kfa_code', 20),
      'vaksin_name' => $t('vaksin_name', 255),
      'vaksin_date' => $t('vaksin_date', 10),
      'vaksin_time' => $t('vaksin_time', 8),
      'vaksin_dosis' => $t('vaksin_dosis', 3),
      'reason_code' => $t('reason_code', 20),
      'route_code' => $t('route_code', 30),
      'dose_ml' => str_replace(',', '.', $t('dose_ml', 10)),
      'lot_number' => $t('lot_number', 50),
      'expiration_date' => $t('expiration_date', 10),
      'vaksin_notes' => $t('vaksin_notes', 2000),
   ];
   $tolak = fn($pesan, $field) => vkBalas(422, ['status' => 'error', 'message' => $pesan, 'field' => $field]);
   if (!isset($kat[$d['kfa_code']])) $tolak('Pilih jenis vaksin dari daftar (kode KFA SATUSEHAT).', 'kfa_code');
   if ($d['vaksin_name'] === '') $d['vaksin_name'] = $kat[$d['kfa_code']][1];
   $x = DateTime::createFromFormat('!Y-m-d', $d['vaksin_date']);
   if (!$x || $x->format('Y-m-d') !== $d['vaksin_date']) $tolak('Tanggal vaksinasi tidak valid.', 'vaksin_date');
   if ($x > new DateTime('tomorrow')) $tolak('Tanggal vaksinasi tidak boleh di masa depan.', 'vaksin_date');
   if ($d['vaksin_time'] === '') $d['vaksin_time'] = null;
   elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $d['vaksin_time'])) $tolak('Jam pemberian tidak valid.', 'vaksin_time');
   if (!ctype_digit($d['vaksin_dosis']) || (int) $d['vaksin_dosis'] < 1 || (int) $d['vaksin_dosis'] > 10) $tolak('Dosis ke- harus angka 1–10.', 'vaksin_dosis');
   if (!isset(imAlasan()[$d['reason_code']])) $tolak('Pilih jenis imunisasi.', 'reason_code');
   if (!isset(imRute()[$d['route_code']])) $tolak('Pilih rute pemberian.', 'route_code');
   if ($d['dose_ml'] === '') $d['dose_ml'] = null;
   elseif (!is_numeric($d['dose_ml']) || (float) $d['dose_ml'] <= 0 || (float) $d['dose_ml'] > 10) $tolak('Jumlah dosis harus 0,01–10 mL.', 'dose_ml');
   if ($d['lot_number'] === '') $tolak('Nomor batch (lot) vaksin wajib diisi.', 'lot_number');
   if ($d['expiration_date'] === '') $d['expiration_date'] = null;
   else {
      $e = DateTime::createFromFormat('!Y-m-d', $d['expiration_date']);
      if (!$e || $e->format('Y-m-d') !== $d['expiration_date']) $tolak('Tanggal kedaluwarsa tidak valid.', 'expiration_date');
      if ($e < $x) $tolak('Vaksin sudah kedaluwarsa pada tanggal pemberian.', 'expiration_date');
   }
   return $d;
}

function vkSimpan(array $in, int $idc, bool $ubah): void
{
   global $koneksi;
   $d = vkIsi($in);

   if ($ubah) {
      $id = (int) ($in['id_vaksin'] ?? ($_GET['id'] ?? 0));
      $lama = $id > 0 ? vkCari($id, $idc) : null;
      if (!$lama) vkBalas(404, ['status' => 'error', 'message' => 'Data vaksin tidak ditemukan.']);
      if ($lama['ss_status'] === 'dibatalkan') vkBalas(422, ['status' => 'error', 'message' => 'Data ini sudah dibatalkan di SATUSEHAT.']);
      // id_visit sengaja tidak ikut diubah
      $__lg ??= logAmbilBaris($koneksi, 'pasien_vaksin', 'id_vaksin', $id); // log: kondisi sebelum diubah/dihapus
      $st = $koneksi->prepare("UPDATE pasien_vaksin SET kfa_code = ?, vaksin_name = ?, vaksin_date = ?, vaksin_time = ?, vaksin_dosis = ?, reason_code = ?,
            route_code = ?, dose_ml = ?, lot_number = ?, expiration_date = ?, vaksin_notes = ? WHERE id_vaksin = ?");
      $st->bind_param('sssssssssssi', $d['kfa_code'], $d['vaksin_name'], $d['vaksin_date'], $d['vaksin_time'], $d['vaksin_dosis'], $d['reason_code'],
         $d['route_code'], $d['dose_ml'], $d['lot_number'], $d['expiration_date'], $d['vaksin_notes'], $id);
      $st->execute();
      $st->close();
      $pesanDb = 'Perubahan tersimpan.';
   } else {
      $visit = trim((string) ($in['id_visit'] ?? ($_GET['no'] ?? '')));
      $st = $koneksi->prepare("SELECT visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
      $st->bind_param('si', $visit, $idc);
      $st->execute();
      $kv = $st->get_result()->fetch_assoc();
      $st->close();
      if (!$kv) vkBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
      if ((int) $kv['visit_status'] === 99) vkBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
      $nomor = vkNomor();
      $st = $koneksi->prepare("INSERT INTO pasien_vaksin (vaksin_number, id_visit, kfa_code, vaksin_name, vaksin_date, vaksin_time, vaksin_dosis, reason_code,
            route_code, dose_ml, lot_number, expiration_date, vaksin_notes, ss_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'belum')");
      $st->bind_param('sssssssssssss', $nomor, $visit, $d['kfa_code'], $d['vaksin_name'], $d['vaksin_date'], $d['vaksin_time'], $d['vaksin_dosis'],
         $d['reason_code'], $d['route_code'], $d['dose_ml'], $d['lot_number'], $d['expiration_date'], $d['vaksin_notes']);
      $st->execute();
      $id = $st->insert_id;
      $st->close();
      $pesanDb = 'Data vaksin tersimpan.';
   }

   // data sudah aman di database -> baru kirim ke SATUSEHAT
   $k = imKirim($koneksi, $idc, $id);
   $vkBaru = logAmbilBaris($koneksi, 'pasien_vaksin', 'id_vaksin', $id);
   logBaris($koneksi, 'klinis', 'pasien_vaksin', 'id_vaksin', $id, $__lg ?? null, ['label' => 'Vaksin ' . ($vkBaru['vaksin_name'] ?? ''), 'visit_ID' => $vkBaru['id_visit'] ?? null],
      $pesanDb . ' SATUSEHAT: ' . ($k['ok'] ? 'terkirim' : 'belum terkirim (' . mb_substr((string) $k['pesan'], 0, 150) . ')'));
   vkBalas(200, [
      'status' => 'success',
      'id' => $id,
      'satusehat' => $k,
      'message' => $pesanDb . ' ' . ($k['ok'] ? $k['pesan'] : 'Belum terkirim ke SATUSEHAT: ' . $k['pesan']),
   ]);
}

function vkKirimUlang(string $target, string $no, int $idc): void
{
   global $koneksi;
   if ($target !== 'semua') {
      $v = vkCari((int) $target, $idc);
      if (!$v) vkBalas(404, ['status' => 'error', 'message' => 'Data vaksin tidak ditemukan.']);
      if ($v['ss_status'] === 'dibatalkan') vkBalas(422, ['status' => 'error', 'message' => 'Data ini sudah dibatalkan di SATUSEHAT.']);
      if ($v['ss_status'] === 'terkirim') vkBalas(200, ['status' => 'success', 'satusehat' => ['ok' => true, 'pesan' => 'Sudah terkirim.'], 'message' => 'Data ini sudah terkirim ke SATUSEHAT.']);
      $k = imKirim($koneksi, $idc, (int) $v['id_vaksin']);
      vkBalas(200, ['status' => $k['ok'] ? 'success' : 'error', 'satusehat' => $k, 'message' => $k['pesan']]);
   }
   $st = $koneksi->prepare("SELECT v.id_vaksin FROM pasien_vaksin v INNER JOIN pasien_visit pv ON pv.visit_ID = v.id_visit AND pv.id_customer = ?
      WHERE v.id_visit = ? AND v.ss_status IN ('belum', 'gagal') ORDER BY v.id_vaksin LIMIT 50");
   $st->bind_param('is', $idc, $no);
   $st->execute();
   $ids = array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'id_vaksin');
   $st->close();
   $ok = 0;
   $gagal = [];
   foreach ($ids as $id) {
      $k = imKirim($koneksi, $idc, (int) $id);
      $k['ok'] ? $ok++ : $gagal[] = $k['pesan'];
   }
   vkBalas(200, [
      'status' => $gagal ? ($ok ? 'partial' : 'error') : 'success',
      'terkirim' => $ok,
      'gagal' => count($gagal),
      'message' => $ids ? "$ok dari " . count($ids) . ' data terkirim.' . ($gagal ? ' Gagal: ' . implode(' | ', array_unique($gagal)) : '') : 'Tidak ada data yang perlu dikirim.',
   ]);
}

function vkHapus(int $id, int $idc): void
{
   global $koneksi;
   $v = $id > 0 ? vkCari($id, $idc) : null;
   if (!$v) vkBalas(404, ['status' => 'error', 'message' => 'Data vaksin tidak ditemukan atau sudah terhapus.']);
   // sudah ada di SATUSEHAT -> tandai entered-in-error dulu; bila gagal, data lokal tidak dihapus
   if (!empty($v['ss_id']) && $v['ss_status'] !== 'dibatalkan') {
      $k = imKirim($koneksi, $idc, $id, 'entered-in-error');
      if (!$k['ok']) vkBalas(422, ['status' => 'error', 'message' => 'Data sudah terkirim ke SATUSEHAT dan gagal dibatalkan di sana, jadi belum dihapus: ' . $k['pesan']]);
   }
   $__lg ??= logAmbilBaris($koneksi, 'pasien_vaksin', 'id_vaksin', $id); // log: kondisi sebelum diubah/dihapus
   $st = $koneksi->prepare("DELETE FROM pasien_vaksin WHERE id_vaksin = ?");
   $st->bind_param('i', $id);
   $st->execute();
   $st->close();
   logBaris($koneksi, 'klinis', 'pasien_vaksin', 'id_vaksin', $id, $__lg ?? null, ['label' => 'Vaksin', 'visit_ID' => ($__lg['id_visit'] ?? null)], 'Vaksin');
   vkBalas(200, ['status' => 'success', 'message' => !empty($v['ss_id']) ? 'Data vaksin dihapus dan dibatalkan di SATUSEHAT.' : 'Data vaksin dihapus.']);
}

function vkNomor(): string
{
   global $koneksi;
   $st = $koneksi->prepare("SELECT 1 FROM pasien_vaksin WHERE vaksin_number = ? LIMIT 1");
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
   throw new RuntimeException('Gagal membuat nomor vaksin unik.');
}
