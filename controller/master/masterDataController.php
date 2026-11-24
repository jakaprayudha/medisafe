<?php
/**
 * Master Data global (module/administrator/master-data). Khusus superadmin/administrator.
 *   GET aksi=ringkasan                         -> jumlah data & tautan ke halaman master lain
 *   GET aksi=poli                              -> daftar poli master (master_poli) + pemakaian
 *   GET aksi=icd&jenis=10|9&start&length&q&draw -> daftar ICD per halaman (DataTables server-side, hanya baca)
 *   POST aksi=poli_simpan|poli_status|poli_hapus
 * Catatan data: kunjungan menyimpan NAMA poli (pasien_visit.id_poli = master_poli.nmPoli) dan pendaftaran memakai kdPoli,
 * sehingga nama/kode poli yang sudah dipakai kunjungan tidak boleh diubah/dihapus (riwayat jadi tidak cocok).
 * Pemakaian poli (GROUP BY seluruh kunjungan, ~0,25 dtk) di-cache 5 menit.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
requireRole(['superadmin', 'administrator']);
$method = $_SERVER['REQUEST_METHOD'];
session_write_close();

function mdJson(string $status, string $message, $data = null, int $code = 200, array $extra = []): void
{
   http_response_code($code);
   echo json_encode(['status' => $status, 'message' => $message, 'data' => $data] + $extra, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}
function mdQ(mysqli $k, string $sql, string $t = '', array $a = []): array
{
   $st = $k->prepare($sql);
   if (!$st) {
      error_log('[masterData] ' . $k->error);
      mdJson('error', 'Terjadi kesalahan server.', null, 500);
   }
   if ($t !== '') $st->bind_param($t, ...$a);
   if (!$st->execute()) {
      error_log('[masterData] ' . $st->error);
      mdJson('error', 'Terjadi kesalahan server.', null, 500);
   }
   $r = $st->get_result();
   $out = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
   $st->close();
   return $out;
}
function mdExec(mysqli $k, string $sql, string $t, array $a): void
{
   $st = $k->prepare($sql);
   if (!$st || !$st->bind_param($t, ...$a) || !$st->execute()) {
      error_log('[masterData] ' . ($st ? $st->error : $k->error));
      mdJson('error', 'Gagal menyimpan data.', null, 500);
   }
   $st->close();
}
/** Jumlah kunjungan per nama poli, di-cache 5 menit. */
function mdPakaiPoli(mysqli $k, bool $segar = false): array
{
   $f = sys_get_temp_dir() . '/medisafe_masterdata_poli.json';
   if (!$segar && is_file($f) && time() - filemtime($f) < 300) {
      $c = json_decode((string) file_get_contents($f), true);
      if (is_array($c)) return $c;
   }
   $out = [];
   foreach (mdQ($k, "SELECT LOWER(TRIM(id_poli)) p, COUNT(*) n FROM pasien_visit WHERE id_poli IS NOT NULL AND id_poli <> '' GROUP BY p") as $r) $out[$r['p']] = (int) $r['n'];
   $tmp = $f . '.' . getmypid();
   if (@file_put_contents($tmp, json_encode($out)) !== false) @rename($tmp, $f);
   return $out;
}

/* ------------------------------ GET ------------------------------ */
if ($method === 'GET') {
   $aksi = (string) ($_GET['aksi'] ?? 'ringkasan');

   if ($aksi === 'ringkasan') {
      $n = fn($sql) => (int) (mdQ($koneksi, $sql)[0]['n'] ?? 0);
      mdJson('success', 'OK', [
         'faskes' => $n("SELECT COUNT(*) n FROM setting_clinic WHERE status != 99"),
         'lab' => $n("SELECT COUNT(*) n FROM laboratorium_detail"),
         'lab_aktif' => $n("SELECT COUNT(*) n FROM laboratorium_detail WHERE status = 1"),
         'satusehat' => $n("SELECT COUNT(*) n FROM setting_satusehat"),
         'poli' => $n("SELECT COUNT(*) n FROM master_poli"),
         'poli_aktif' => $n("SELECT COUNT(*) n FROM master_poli WHERE status_poli = 1"),
         'icd10' => $n("SELECT COUNT(*) n FROM icd_10"),
         'icd9' => $n("SELECT COUNT(*) n FROM icd_09"),
      ]);
   }

   if ($aksi === 'poli') {
      $rows = mdQ($koneksi, 'SELECT id, kdPoli, nmPoli, status_poli, poliSakit FROM master_poli ORDER BY status_poli DESC, kdPoli');
      $pakai = mdPakaiPoli($koneksi, !empty($_GET['refresh']));
      foreach ($rows as &$r) {
         $r['id'] = (int) $r['id'];
         $r['status_poli'] = (int) $r['status_poli'];
         $r['poliSakit'] = (int) $r['poliSakit'];
         $r['dipakai'] = $pakai[mb_strtolower(trim((string) $r['nmPoli']))] ?? 0;
      }
      unset($r);
      mdJson('success', 'OK', $rows);
   }

   if ($aksi === 'icd') {
      $jenis = ($_GET['jenis'] ?? '10') === '9' ? 9 : 10;
      $start = max(0, (int) ($_GET['start'] ?? 0));
      $length = min(100, max(1, (int) ($_GET['length'] ?? 25)));
      $cari = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 80);
      if ($jenis === 10) {
         $tbl = 'icd_10';
         $kode = 'code';
         $nama = 'icd10';
      } else {
         $tbl = 'icd_09';
         $kode = 'kode';
         $nama = 'nama';
      }
      $where = '1';
      $t = '';
      $a = [];
      if ($cari !== '') {
         // kode diawali teks (mis. "J06"), atau nama memuat teks
         $where = "($kode LIKE ? OR $nama LIKE ?)";
         $t = 'ss';
         $a = [$cari . '%', '%' . $cari . '%'];
      }
      $total = (int) (mdQ($koneksi, "SELECT COUNT(*) n FROM $tbl")[0]['n'] ?? 0);
      $filtered = $cari === '' ? $total : (int) (mdQ($koneksi, "SELECT COUNT(*) n FROM $tbl WHERE $where", $t, $a)[0]['n'] ?? 0);
      $rows = mdQ($koneksi, "SELECT id, $kode AS kode, $nama AS nama FROM $tbl WHERE $where ORDER BY $kode LIMIT ? OFFSET ?", $t . 'ii', [...$a, $length, $start]);
      mdJson('success', 'OK', $rows, 200, ['draw' => (int) ($_GET['draw'] ?? 0), 'recordsTotal' => $total, 'recordsFiltered' => $filtered]);
   }

   mdJson('error', 'Aksi tidak dikenal.', null, 400);
}

if ($method !== 'POST') mdJson('error', 'Method tidak didukung.', null, 405);

$aksi = (string) ($_POST['aksi'] ?? '');
$id = (int) ($_POST['id'] ?? 0);

/* ----------------------------- status ---------------------------- */
if ($aksi === 'poli_status') {
   if ($id <= 0) mdJson('error', 'ID tidak valid.', null, 422);
   mdExec($koneksi, 'UPDATE master_poli SET status_poli = ? WHERE id = ?', 'ii', [(int) (($_POST['status'] ?? '0') === '1'), $id]);
   mdJson('success', 'Status poli diperbarui.');
}

/* ----------------------------- hapus ----------------------------- */
if ($aksi === 'poli_hapus') {
   $row = mdQ($koneksi, 'SELECT nmPoli FROM master_poli WHERE id = ? LIMIT 1', 'i', [$id])[0] ?? null;
   if (!$row) mdJson('error', 'Poli tidak ditemukan.', null, 404);
   $n = mdPakaiPoli($koneksi, true)[mb_strtolower(trim((string) $row['nmPoli']))] ?? 0;
   if ($n > 0) mdJson('error', "Poli \"{$row['nmPoli']}\" sudah dipakai di " . number_format($n, 0, ',', '.') . ' kunjungan sehingga tidak bisa dihapus. Nonaktifkan saja.', null, 409);
   mdExec($koneksi, 'DELETE FROM master_poli WHERE id = ?', 'i', [$id]);
   mdJson('success', 'Poli dihapus.');
}

/* ------------------------ tambah / ubah poli --------------------- */
if ($aksi === 'poli_simpan') {
   $kd = trim((string) ($_POST['kdPoli'] ?? ''));
   $nm = trim((string) ($_POST['nmPoli'] ?? ''));
   $aktif = (int) (($_POST['status_poli'] ?? '1') === '1');
   $sakit = (int) (($_POST['poliSakit'] ?? '0') === '1');
   if ($kd === '' || !preg_match('/^[A-Za-z0-9]{1,5}$/', $kd)) mdJson('error', 'Kode poli wajib diisi: 1–5 huruf/angka (kode PCare).', ['field' => 'kdPoli'], 422);
   if ($nm === '') mdJson('error', 'Nama poli wajib diisi.', ['field' => 'nmPoli'], 422);
   if (mb_strlen($nm) > 100) mdJson('error', 'Nama poli maksimal 100 karakter.', ['field' => 'nmPoli'], 422);
   if (mdQ($koneksi, 'SELECT 1 FROM master_poli WHERE kdPoli = ? AND id <> ? LIMIT 1', 'si', [$kd, $id])) mdJson('error', 'Kode poli sudah dipakai poli lain.', ['field' => 'kdPoli'], 422);

   if ($id > 0) {
      $row = mdQ($koneksi, 'SELECT kdPoli, nmPoli FROM master_poli WHERE id = ? LIMIT 1', 'i', [$id])[0] ?? null;
      if (!$row) mdJson('error', 'Poli tidak ditemukan.', null, 404);
      if ((string) $row['nmPoli'] !== $nm || (string) $row['kdPoli'] !== $kd) {
         $n = mdPakaiPoli($koneksi, true)[mb_strtolower(trim((string) $row['nmPoli']))] ?? 0;
         if ($n > 0) mdJson('error', 'Nama/kode tidak bisa diganti: poli ini sudah dipakai di ' . number_format($n, 0, ',', '.') . ' kunjungan (riwayat mencatat nama poli). Anda masih bisa mengubah status atau jenisnya.', ['field' => 'nmPoli'], 409);
      }
      mdExec($koneksi, 'UPDATE master_poli SET kdPoli = ?, nmPoli = ?, status_poli = ?, poliSakit = ? WHERE id = ?', 'ssiii', [$kd, $nm, $aktif, $sakit, $id]);
      mdJson('success', 'Poli diperbarui.');
   }
   if (mdQ($koneksi, 'SELECT 1 FROM master_poli WHERE nmPoli = ? LIMIT 1', 's', [$nm])) mdJson('error', 'Nama poli sudah ada.', ['field' => 'nmPoli'], 422);
   mdExec($koneksi, 'INSERT INTO master_poli (kdPoli, nmPoli, status_poli, poliSakit) VALUES (?, ?, ?, ?)', 'ssii', [$kd, $nm, $aktif, $sakit]);
   mdJson('success', 'Poli ditambahkan.');
}

mdJson('error', 'Aksi tidak dikenal.', null, 400);
