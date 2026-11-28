<?php
// Master jenis bayar (ms_provider) — data GLOBAL untuk semua faskes.
// id_provider dipakai langsung di banyak fitur (mis. 1 = BPJS Kesehatan), jadi
// tambah/ubah/hapus hanya untuk superadmin.
//   GET                           -> daftar jenis bayar
//   GET    ?id=ID                 -> satu jenis bayar
//   GET    ?stats=1&tanggal=Y-m-d -> jumlah pasien faskes per jenis bayar (hari itu, 7 hari, bulan berjalan)
//   POST / PUT / DELETE           -> superadmin saja
session_start();
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
function provLog(mysqli $k, int $id, ?array $lama, string $ket, ?string $aksi = null): void
{
   $r = $lama ?? logAmbilBaris($k, 'ms_provider', 'id_provider', $id) ?? [];
   logBaris($k, 'master', 'ms_provider', 'id_provider', $id, $lama, ['tipe' => 'jenis_bayar', 'label' => trim(($r['provider_code'] ?? '') . ' · ' . ($r['provider_name'] ?? ''), ' ·')], $ket, $aksi);
}
mysqli_report(MYSQLI_REPORT_OFF);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function jawab(array $data, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($data);
   exit;
}

function semua(mysqli $k, string $sql, string $types = '', array $params = []): array
{
   $stmt = $k->prepare($sql);
   if (!$stmt) {
      error_log('[providerController] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal mengambil data.'], 500);
   }
   if ($types !== '') $stmt->bind_param($types, ...$params);
   $stmt->execute();
   $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();
   return $rows;
}

function eksekusi(mysqli $k, string $sql, string $types, array $params): int
{
   $stmt = $k->prepare($sql);
   if (!$stmt) {
      error_log('[providerController] ' . $k->error);
      jawab(['status' => 'error', 'message' => 'Gagal menyimpan data.'], 500);
   }
   $stmt->bind_param($types, ...$params);
   if (!$stmt->execute()) {
      error_log('[providerController] ' . $stmt->error);
      jawab(['status' => 'error', 'message' => 'Gagal menyimpan data.'], 500);
   }
   $n = $stmt->affected_rows;
   $stmt->close();
   return $n;
}

function providerAda(mysqli $k, int $id): array
{
   $row = semua($k, "SELECT * FROM ms_provider WHERE id_provider = ?", 'i', [$id]);
   if (!$row) jawab(['status' => 'error', 'message' => 'Data tidak ditemukan.'], 404);
   return $row[0];
}

function inputProvider(array $in): array
{
   $kode = trim((string) ($in['provider_code'] ?? ''));
   $nama = trim(preg_replace('/\s+/', ' ', (string) ($in['provider_name'] ?? '')));
   if ($kode === '' || mb_strlen($kode) > 255) jawab(['status' => 'error', 'message' => 'Kode wajib diisi.'], 422);
   if ($nama === '' || mb_strlen($nama) > 255) jawab(['status' => 'error', 'message' => 'Jenis bayar wajib diisi.'], 422);
   return [$kode, $nama];
}

$method = $_SERVER['REQUEST_METHOD'];
$idc = (int) ($_SESSION['id_customer'] ?? 0);

// ================= GET =================
if ($method === 'GET') {
   if (isset($_GET['stats'])) {
      if ($idc <= 0) jawab(['status' => 'error', 'message' => 'Session faskes tidak ditemukan.'], 401);

      $dt = DateTime::createFromFormat('!Y-m-d', (string) ($_GET['tanggal'] ?? '')) ?: new DateTime('today');
      $tgl = $dt->format('Y-m-d');
      $awalBulan = $dt->format('Y-m-01');
      $hari7 = [];
      for ($i = 6; $i >= 0; $i--) $hari7[] = (clone $dt)->modify("-$i day")->format('Y-m-d');
      $mulai = min($awalBulan, $hari7[0]);

      $kosong = fn() => ['hari_ini' => 0, 'antre' => 0, 'selesai' => 0, 'batal' => 0, 'bulan' => 0, 'tren' => array_fill(0, 7, 0)];
      $master = array_map('intval', array_column(semua($koneksi, "SELECT id_provider FROM ms_provider"), 'id_provider'));
      $stat = [];
      foreach ($master as $id) $stat[$id] = $kosong();
      $lain = $kosong(); // id_provider kosong / tidak ada di master

      $rows = semua(
         $koneksi,
         "SELECT id_provider, visit_date, visit_status, COUNT(*) n
          FROM pasien_visit
          WHERE id_customer = ? AND visit_date BETWEEN ? AND ?
          GROUP BY id_provider, visit_date, visit_status",
         'iss',
         [$idc, $mulai, $tgl]
      );
      foreach ($rows as $r) {
         $id = (int) $r['id_provider'];
         if (isset($stat[$id])) $s = &$stat[$id];
         else $s = &$lain;
         $n = (int) $r['n'];
         $batal = (int) $r['visit_status'] === 99;
         $d = $r['visit_date'];
         if ($d === $tgl) {
            if ($batal) $s['batal'] += $n;
            else {
               $s['hari_ini'] += $n;
               if ((int) $r['visit_status'] === 4) $s['selesai'] += $n;
               else $s['antre'] += $n;
            }
         }
         if (!$batal) {
            if ($d >= $awalBulan) $s['bulan'] += $n;
            $idx = array_search($d, $hari7, true);
            if ($idx !== false) $s['tren'][$idx] += $n;
         }
         unset($s);
      }

      jawab([
         'status' => 'success',
         'tanggal' => $tgl,
         'hari' => $hari7,
         'data' => (object) $stat,
         'lain' => ($lain['hari_ini'] || $lain['bulan'] || $lain['batal']) ? $lain : null,
      ]);
   }

   if (isset($_GET['id'])) {
      jawab(['status' => 'success', 'data' => providerAda($koneksi, (int) $_GET['id'])]);
   }

   jawab([
      'status' => 'success',
      'data' => semua($koneksi, "SELECT * FROM ms_provider ORDER BY provider_name DESC"),
   ]);
}

// ================= TULIS (superadmin) =================
if (!in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
   jawab(['status' => 'error', 'message' => 'Method tidak diizinkan.'], 405);
}
if (($_SESSION['roles'] ?? '') !== 'superadmin') {
   jawab(['status' => 'error', 'message' => 'Jenis bayar adalah data master bersama, hanya superadmin yang dapat mengubahnya.'], 403);
}

if ($method === 'POST') {
   [$kode, $nama] = inputProvider($_POST);
   eksekusi($koneksi, "INSERT INTO ms_provider (provider_code, provider_name) VALUES (?, ?)", 'ss', [$kode, $nama]);
   provLog($koneksi, (int) $koneksi->insert_id, null, 'Jenis bayar ditambahkan');
   jawab(['status' => 'success', 'message' => 'Jenis bayar berhasil ditambahkan.']);
}

if ($method === 'PUT') {
   parse_str(file_get_contents('php://input'), $in);
   $id = (int) ($in['id_provider'] ?? 0);
   $lama = providerAda($koneksi, $id);

   if (isset($in['provider_status'])) {
      $status = (int) $in['provider_status'];
      if (!in_array($status, [0, 1], true)) jawab(['status' => 'error', 'message' => 'Status tidak valid.'], 422);
      eksekusi($koneksi, "UPDATE ms_provider SET provider_status = ? WHERE id_provider = ?", 'ii', [$status, $id]);
      provLog($koneksi, $id, $lama, $status ? 'Jenis bayar diaktifkan' : 'Jenis bayar dinonaktifkan', $status ? 'aktifkan' : 'nonaktifkan');
      jawab(['status' => 'success', 'message' => 'Status jenis bayar diperbarui.']);
   }

   [$kode, $nama] = inputProvider($in);
   eksekusi($koneksi, "UPDATE ms_provider SET provider_code = ?, provider_name = ? WHERE id_provider = ?", 'ssi', [$kode, $nama, $id]);
   provLog($koneksi, $id, $lama, 'Jenis bayar diubah');
   jawab(['status' => 'success', 'message' => 'Jenis bayar berhasil diperbarui.']);
}

// DELETE
$id = (int) ($_GET['id'] ?? 0);
$lama = providerAda($koneksi, $id);
$dipakai = semua($koneksi, "SELECT 1 FROM pasien_visit WHERE id_provider = ? LIMIT 1", 's', [(string) $id]);
if ($dipakai) jawab(['status' => 'error', 'message' => 'Jenis bayar sudah dipakai pada kunjungan pasien, tidak dapat dihapus.'], 409);
eksekusi($koneksi, "DELETE FROM ms_provider WHERE id_provider = ?", 'i', [$id]);
provLog($koneksi, $id, $lama, 'Jenis bayar dihapus');
jawab(['status' => 'success', 'message' => 'Jenis bayar berhasil dihapus.']);
