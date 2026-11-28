<?php
/**
 * Log aktivitas kunjungan pasien (menu admin: module/admin/log_kunjungan). Data dari tabel log_kunjungan (utility/visitLog.php).
 *
 *   GET ?dari=Y-m-d&sampai=Y-m-d&aksi=&q=&draw=&start=&length=  -> DataTables server-side + jumlah per aksi
 *   GET ?id=<id_log>                                             -> detail satu log (data lama / baru)
 *   GET ?export=csv&dari=&sampai=&aksi=&q=                       -> unduh CSV (maks. 20.000 baris)
 *
 * Hanya peran admin / superadmin / administrator, dan hanya log faskes di session.
 */
require_once '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitLog.php';

$IDC = (int) ($_SESSION['id_customer'] ?? 0);
$ROLE = (string) ($_SESSION['roles'] ?? '');
session_write_close();

function lkJawab(array $d, int $kode = 200): void
{
   http_response_code($kode);
   header('Content-Type: application/json; charset=utf-8');
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}
function lkQ(string $sql, string $t, array $p): array
{
   global $koneksi;
   $st = $koneksi->prepare($sql);
   if (!$st) throw new RuntimeException($koneksi->error);
   if ($t !== '') $st->bind_param($t, ...$p);
   $st->execute();
   $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   return $r;
}

if ($IDC <= 0) lkJawab(['status' => 'error', 'message' => 'Session tidak valid / expired'], 401);
if (!in_array($ROLE, ['admin', 'superadmin', 'administrator'], true)) lkJawab(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
if ($_SERVER['REQUEST_METHOD'] !== 'GET') lkJawab(['status' => 'error', 'message' => 'Metode tidak diizinkan.'], 405);

logKunjunganSiapkan($koneksi);
// tabel versi lama belum punya kolom pelaku_asli -> tambahkan (abaikan bila sudah ada)
try {
   if (!$koneksi->query("SHOW COLUMNS FROM log_kunjungan LIKE 'pelaku_asli'")->num_rows) logUpgrade($koneksi, LOG_KUNJUNGAN_UPGRADE);
} catch (Throwable $e) {
   error_log('[logKunjunganController] cek kolom: ' . $e->getMessage());
}
$KOLOM = "l.id_log, l.created_at, l.aksi, l.visit_ID, l.id_visit, l.id_patient, l.nama_pasien, l.sumber, l.keterangan,
   l.username, l.fullname, l.roles, l.pelaku_asli, l.ip, p.nomor_rm";
$DARI = "FROM log_kunjungan l LEFT JOIN ms_patient p ON p.id_patient = l.id_patient";

try {
   // ---------- detail ----------
   if (isset($_GET['id'])) {
      $id = (int) $_GET['id'];
      $r = lkQ("SELECT $KOLOM, l.data_lama, l.data_baru $DARI WHERE l.id_log = ? AND l.id_customer = ? LIMIT 1", 'ii', [$id, $IDC])[0] ?? null;
      if (!$r) lkJawab(['status' => 'error', 'message' => 'Log tidak ditemukan.'], 404);
      $r['data_lama'] = $r['data_lama'] !== null ? json_decode($r['data_lama'], true) : null;
      $r['data_baru'] = $r['data_baru'] !== null ? json_decode($r['data_baru'], true) : null;
      $r['aksi_label'] = LOG_KUNJUNGAN_AKSI[$r['aksi']] ?? $r['aksi'];
      lkJawab(['status' => 'success', 'data' => $r]);
   }

   // ---------- filter ----------
   $tglValid = fn($s) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $s) && strtotime($s) !== false;
   $sampai = $tglValid($_GET['sampai'] ?? '') ? $_GET['sampai'] : date('Y-m-d');
   $dari = $tglValid($_GET['dari'] ?? '') ? $_GET['dari'] : date('Y-m-d', strtotime($sampai . ' -6 days'));
   if ($dari > $sampai) [$dari, $sampai] = [$sampai, $dari];
   if ((strtotime($sampai) - strtotime($dari)) / 86400 > 366) lkJawab(['status' => 'error', 'message' => 'Rentang tanggal maksimal 1 tahun.'], 422);
   $aksi = (string) ($_GET['aksi'] ?? '');
   if ($aksi !== '' && !isset(LOG_KUNJUNGAN_AKSI[$aksi])) $aksi = '';
   $q = trim(mb_substr((string) ($_GET['q'] ?? ($_GET['search']['value'] ?? '')), 0, 100));

   $w = ['l.id_customer = ?', 'l.created_at >= ?', 'l.created_at < ?'];
   $t = 'iss';
   $p = [$IDC, $dari . ' 00:00:00', date('Y-m-d', strtotime($sampai . ' +1 day')) . ' 00:00:00'];
   if ($q !== '') {
      $like = '%' . addcslashes($q, '%_\\') . '%';
      $w[] = '(l.visit_ID LIKE ? OR l.nama_pasien LIKE ? OR l.username LIKE ? OR l.fullname LIKE ? OR l.pelaku_asli LIKE ? OR l.keterangan LIKE ? OR p.nomor_rm LIKE ?)';
      $t .= 'sssssss';
      array_push($p, $like, $like, $like, $like, $like, $like, $like);
   }
   $whereTanpaAksi = implode(' AND ', $w);
   [$tA, $pA] = [$t, $p];
   if ($aksi !== '') { $w[] = 'l.aksi = ?'; $tA .= 's'; $pA[] = $aksi; }
   $where = implode(' AND ', $w);

   // ---------- export CSV ----------
   if (($_GET['export'] ?? '') === 'csv') {
      $rows = lkQ("SELECT $KOLOM, l.data_lama, l.data_baru $DARI WHERE $where ORDER BY l.id_log DESC LIMIT 20000", $tA, $pA);
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename="log-kunjungan-' . $dari . '_' . $sampai . '.csv"');
      $out = fopen('php://output', 'w');
      fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
      fputcsv($out, ['Waktu', 'Aksi', 'No. Kunjungan', 'No. RM', 'Pasien', 'Sumber', 'Keterangan', 'Username', 'Nama User', 'Peran', 'Pelaku Asli (switch)', 'IP', 'Data Lama', 'Data Baru'], ',', '"', '');
      foreach ($rows as $r) {
         fputcsv($out, [$r['created_at'], LOG_KUNJUNGAN_AKSI[$r['aksi']] ?? $r['aksi'], $r['visit_ID'], $r['nomor_rm'], $r['nama_pasien'], $r['sumber'], $r['keterangan'],
            $r['username'], $r['fullname'], $r['roles'], $r['pelaku_asli'], $r['ip'], $r['data_lama'], $r['data_baru']], ',', '"', '');
      }
      fclose($out);
      exit;
   }

   // ---------- list (DataTables server-side) ----------
   $start = max(0, (int) ($_GET['start'] ?? 0));
   $length = (int) ($_GET['length'] ?? 25);
   $length = $length < 1 || $length > 200 ? 25 : $length;

   $hitung = array_fill_keys(array_keys(LOG_KUNJUNGAN_AKSI), 0);
   $semua = 0;
   foreach (lkQ("SELECT l.aksi, COUNT(*) n $DARI WHERE $whereTanpaAksi GROUP BY l.aksi", $t, $p) as $r) {
      $hitung[$r['aksi']] = (int) $r['n'];
      $semua += (int) $r['n'];
   }
   $total = $aksi !== '' ? ($hitung[$aksi] ?? 0) : $semua;
   $data = $total ? lkQ("SELECT $KOLOM, (l.data_lama IS NOT NULL OR l.data_baru IS NOT NULL) AS ada_data $DARI WHERE $where ORDER BY l.id_log DESC LIMIT ?, ?", $tA . 'ii', array_merge($pA, [$start, $length])) : [];
   foreach ($data as &$r) {
      $r['aksi_label'] = LOG_KUNJUNGAN_AKSI[$r['aksi']] ?? $r['aksi'];
      $r['ada_data'] = (bool) $r['ada_data'];
   }
   unset($r);
   $pengguna = (int) (lkQ("SELECT COUNT(DISTINCT COALESCE(l.uid_user, l.username)) n $DARI WHERE $whereTanpaAksi", $t, $p)[0]['n'] ?? 0);

   lkJawab([
      'status' => 'success',
      'draw' => (int) ($_GET['draw'] ?? 0),
      'recordsTotal' => $total,
      'recordsFiltered' => $total,
      'data' => $data,
      'hitung' => $hitung,
      'semua' => $semua,
      'pengguna' => $pengguna,
      'label' => LOG_KUNJUNGAN_AKSI,
      'rentang' => ['dari' => $dari, 'sampai' => $sampai],
   ]);
} catch (Throwable $e) {
   error_log('[logKunjunganController] ' . $e->getMessage());
   lkJawab(['status' => 'error', 'message' => 'Gagal memuat log. Pastikan tabel log_kunjungan sudah dibuat (migrations/20261128_log_kunjungan.sql).'], 500);
}
