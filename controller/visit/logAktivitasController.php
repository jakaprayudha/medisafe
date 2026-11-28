<?php
/**
 * Log aktivitas umum (menu admin: module/admin/log_aktivitas). Data dari tabel log_aktivitas (utility/activityLog.php).
 *
 *   GET ?dari=Y-m-d&sampai=Y-m-d&modul=&aksi=&q=&draw=&start=&length=  -> DataTables server-side + jumlah per modul
 *   GET ?id=<id_log>                                                    -> detail (data lama / baru)
 *   GET ?export=csv&...                                                 -> unduh CSV (maks. 20.000 baris)
 *
 * Hanya peran admin / superadmin / administrator, dan hanya log faskes di session.
 * Superadmin juga melihat log tanpa faskes (mis. login gagal dengan username yang tidak terdaftar).
 */
require_once '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';

$IDC = (int) ($_SESSION['id_customer'] ?? 0);
$ROLE = (string) ($_SESSION['roles'] ?? '');
session_write_close();

function laJawab(array $d, int $kode = 200): void
{
   http_response_code($kode);
   header('Content-Type: application/json; charset=utf-8');
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}
function laQ(string $sql, string $t, array $p): array
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

if ($IDC <= 0) laJawab(['status' => 'error', 'message' => 'Session tidak valid / expired'], 401);
if (!in_array($ROLE, ['admin', 'superadmin', 'administrator'], true)) laJawab(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
if ($_SERVER['REQUEST_METHOD'] !== 'GET') laJawab(['status' => 'error', 'message' => 'Metode tidak diizinkan.'], 405);

logAktivitasSiapkan($koneksi);
$KOLOM = "l.id_log, l.created_at, l.modul, l.aksi, l.ref_tipe, l.ref_id, l.ref_label, l.visit_ID, l.keterangan,
   l.username, l.fullname, l.roles, l.pelaku_asli, l.ip";
$FASKES = $ROLE === 'superadmin' ? '(l.id_customer = ? OR l.id_customer IS NULL)' : 'l.id_customer = ?';
$label = fn(string $a) => LOG_AKTIVITAS_AKSI[$a] ?? $a;

try {
   // ---------- detail ----------
   if (isset($_GET['id'])) {
      $id = (int) $_GET['id'];
      $r = laQ("SELECT $KOLOM, l.data_lama, l.data_baru, l.user_agent FROM log_aktivitas l WHERE l.id_log = ? AND $FASKES LIMIT 1", 'ii', [$id, $IDC])[0] ?? null;
      if (!$r) laJawab(['status' => 'error', 'message' => 'Log tidak ditemukan.'], 404);
      $r['data_lama'] = $r['data_lama'] !== null ? json_decode($r['data_lama'], true) : null;
      $r['data_baru'] = $r['data_baru'] !== null ? json_decode($r['data_baru'], true) : null;
      $r['aksi_label'] = $label($r['aksi']);
      $r['modul_label'] = LOG_AKTIVITAS_MODUL[$r['modul']] ?? $r['modul'];
      laJawab(['status' => 'success', 'data' => $r]);
   }

   // ---------- filter ----------
   $tglValid = fn($s) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $s) && strtotime($s) !== false;
   $sampai = $tglValid($_GET['sampai'] ?? '') ? $_GET['sampai'] : date('Y-m-d');
   $dari = $tglValid($_GET['dari'] ?? '') ? $_GET['dari'] : date('Y-m-d', strtotime($sampai . ' -6 days'));
   if ($dari > $sampai) [$dari, $sampai] = [$sampai, $dari];
   if ((strtotime($sampai) - strtotime($dari)) / 86400 > 366) laJawab(['status' => 'error', 'message' => 'Rentang tanggal maksimal 1 tahun.'], 422);
   $modul = (string) ($_GET['modul'] ?? '');
   if ($modul !== '' && !isset(LOG_AKTIVITAS_MODUL[$modul])) $modul = '';
   $aksi = (string) ($_GET['aksi'] ?? '');
   if ($aksi !== '' && !isset(LOG_AKTIVITAS_AKSI[$aksi])) $aksi = '';
   $q = trim(mb_substr((string) ($_GET['q'] ?? ''), 0, 100));

   $w = [$FASKES, 'l.created_at >= ?', 'l.created_at < ?'];
   $t = 'iss';
   $p = [$IDC, $dari . ' 00:00:00', date('Y-m-d', strtotime($sampai . ' +1 day')) . ' 00:00:00'];
   if ($q !== '') {
      $like = '%' . addcslashes($q, '%_\\') . '%';
      $w[] = '(l.ref_label LIKE ? OR l.ref_id LIKE ? OR l.visit_ID LIKE ? OR l.username LIKE ? OR l.fullname LIKE ? OR l.pelaku_asli LIKE ? OR l.keterangan LIKE ? OR l.ip LIKE ?)';
      $t .= str_repeat('s', 8);
      array_push($p, ...array_fill(0, 8, $like));
   }
   $whereDasar = implode(' AND ', $w);
   [$tF, $pF, $wF] = [$t, $p, $w];
   if ($modul !== '') { $wF[] = 'l.modul = ?'; $tF .= 's'; $pF[] = $modul; }
   if ($aksi !== '') { $wF[] = 'l.aksi = ?'; $tF .= 's'; $pF[] = $aksi; }
   $where = implode(' AND ', $wF);

   // ---------- export CSV ----------
   if (($_GET['export'] ?? '') === 'csv') {
      $rows = laQ("SELECT $KOLOM, l.data_lama, l.data_baru FROM log_aktivitas l WHERE $where ORDER BY l.id_log DESC LIMIT 20000", $tF, $pF);
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename="log-aktivitas-' . $dari . '_' . $sampai . '.csv"');
      $out = fopen('php://output', 'w');
      fwrite($out, "\xEF\xBB\xBF");
      fputcsv($out, ['Waktu', 'Modul', 'Aksi', 'Objek', 'ID Objek', 'No. Kunjungan', 'Keterangan', 'Username', 'Nama User', 'Peran', 'Pelaku Asli (switch)', 'IP', 'Data Lama', 'Data Baru'], ',', '"', '');
      foreach ($rows as $r) {
         fputcsv($out, [$r['created_at'], LOG_AKTIVITAS_MODUL[$r['modul']] ?? $r['modul'], $label($r['aksi']), $r['ref_label'], $r['ref_id'], $r['visit_ID'], $r['keterangan'],
            $r['username'], $r['fullname'], $r['roles'], $r['pelaku_asli'], $r['ip'], $r['data_lama'], $r['data_baru']], ',', '"', '');
      }
      fclose($out);
      exit;
   }

   // ---------- list ----------
   $start = max(0, (int) ($_GET['start'] ?? 0));
   $length = (int) ($_GET['length'] ?? 25);
   $length = $length < 1 || $length > 200 ? 25 : $length;

   $hitung = array_fill_keys(array_keys(LOG_AKTIVITAS_MODUL), 0);
   $semua = 0;
   foreach (laQ("SELECT l.modul, COUNT(*) n FROM log_aktivitas l WHERE $whereDasar GROUP BY l.modul", $t, $p) as $r) {
      $hitung[$r['modul']] = (int) $r['n'];
      $semua += (int) $r['n'];
   }
   // aksi tersedia untuk modul terpilih (dropdown filter aksi)
   $aksiAda = [];
   $tA = $t; $pA = $p; $wA = $w;
   if ($modul !== '') { $wA[] = 'l.modul = ?'; $tA .= 's'; $pA[] = $modul; }
   foreach (laQ("SELECT l.aksi, COUNT(*) n FROM log_aktivitas l WHERE " . implode(' AND ', $wA) . " GROUP BY l.aksi ORDER BY n DESC", $tA, $pA) as $r) {
      $aksiAda[] = ['aksi' => $r['aksi'], 'label' => $label($r['aksi']), 'n' => (int) $r['n']];
   }
   $ringkas = laQ("SELECT SUM(l.aksi IN ('login_gagal', 'login_terkunci')) gagal, SUM(l.aksi = 'switch_user') switchu, SUM(l.pelaku_asli IS NOT NULL) via_switch,
         COUNT(DISTINCT COALESCE(l.uid_user, l.username)) pengguna FROM log_aktivitas l WHERE $whereDasar", $t, $p)[0] ?? [];

   $total = (int) (laQ("SELECT COUNT(*) n FROM log_aktivitas l WHERE $where", $tF, $pF)[0]['n'] ?? 0);
   $data = $total ? laQ("SELECT $KOLOM, (l.data_lama IS NOT NULL OR l.data_baru IS NOT NULL) AS ada_data FROM log_aktivitas l WHERE $where ORDER BY l.id_log DESC LIMIT ?, ?",
      $tF . 'ii', array_merge($pF, [$start, $length])) : [];
   foreach ($data as &$r) {
      $r['aksi_label'] = $label($r['aksi']);
      $r['modul_label'] = LOG_AKTIVITAS_MODUL[$r['modul']] ?? $r['modul'];
      $r['ada_data'] = (bool) $r['ada_data'];
   }
   unset($r);

   laJawab([
      'status' => 'success',
      'draw' => (int) ($_GET['draw'] ?? 0),
      'recordsTotal' => $total,
      'recordsFiltered' => $total,
      'data' => $data,
      'hitung' => $hitung,
      'semua' => $semua,
      'aksi_ada' => $aksiAda,
      'ringkas' => array_map('intval', $ringkas),
      'modul' => LOG_AKTIVITAS_MODUL,
   ]);
} catch (Throwable $e) {
   error_log('[logAktivitasController] ' . $e->getMessage());
   laJawab(['status' => 'error', 'message' => 'Gagal memuat log. Pastikan tabel log_aktivitas sudah dibuat (migrations/20261128_log_aktivitas.sql).'], 500);
}
