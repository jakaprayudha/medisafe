<?php
/**
 * Log aktivitas umum -> tabel log_aktivitas (migrations/20261128_log_aktivitas.sql).
 *
 *   logAktivitas($koneksi, 'pasien', 'ubah', ['tipe' => 'pasien', 'id' => $id, 'label' => $nama], 'Ubah identitas', $lama, $baru);
 *   logAktivitas($koneksi, 'auth', 'login_gagal', ['tipe' => 'user', 'label' => $username], '', null, null, 0);
 *
 * - Pencatatan tidak pernah menggagalkan proses utama (error hanya masuk error_log).
 * - Panggil SETELAH commit transaksi: pembuatan tabel otomatis (CREATE TABLE) memicu implicit commit.
 * - Kolom sensitif (password, token, secret, ...) selalu dibuang dari data lama/baru.
 * - Saat admin sedang "switch user", pelaku asli ikut dicatat (kolom pelaku_asli), lihat logPelaku().
 */

const LOG_AKTIVITAS_MODUL = [
   'auth'   => 'Login & sesi',
   'user'   => 'User & hak akses',
   'pasien' => 'Data pasien',
   'rme'    => 'Gabung RME',
   'cppt'   => 'CPPT',
   'resume' => 'Resume medis',
   'diagnosa' => 'Diagnosa & PCare',
   'lab'    => 'Hasil lab',
];

const LOG_AKTIVITAS_AKSI = [
   'login' => 'Login', 'login_gagal' => 'Login gagal', 'login_terkunci' => 'Login ditolak (terkunci)', 'login_nonaktif' => 'Login akun nonaktif',
   'logout' => 'Logout', 'switch_user' => 'Switch user', 'switch_kembali' => 'Kembali ke akun asal', 'switch_tolak' => 'Switch user ditolak',
   'tambah' => 'Tambah', 'ubah' => 'Ubah', 'hapus' => 'Hapus', 'ubah_peran' => 'Ubah peran', 'reset_password' => 'Reset password',
   'aktifkan' => 'Aktifkan', 'nonaktifkan' => 'Nonaktifkan', 'gabung' => 'Gabung RME', 'verifikasi' => 'Verifikasi', 'batal_verifikasi' => 'Batal verifikasi',
   'simpan' => 'Simpan', 'kirim_pcare' => 'Kirim PCare', 'hapus_file' => 'Hapus file', 'upload_file' => 'Upload file',
];

/** Kunci yang tidak boleh masuk log. */
function logBersihkan(?array $a): ?array
{
   if ($a === null) return null;
   $out = [];
   foreach ($a as $k => $v) {
      if (is_string($k) && preg_match('/pass|pwd|token|secret|signature_key|cons_?id|user_?key|otp/i', $k)) {
         $out[$k] = '[disembunyikan]';
         continue;
      }
      $out[$k] = is_array($v) ? logBersihkan($v) : $v;
   }
   return $out;
}

/** Hanya kolom yang berubah: [lama, baru]. Nilai dibandingkan sebagai teks (angka '1' == 1). */
function logBeda(?array $lama, array $baru): array
{
   $l = [];
   $b = [];
   foreach ($baru as $k => $v) {
      $lv = $lama[$k] ?? null;
      $sama = (is_scalar($lv) || $lv === null) && (is_scalar($v) || $v === null) ? trim((string) $lv) === trim((string) $v) : $lv == $v;
      if (!$sama) {
         $l[$k] = $lv;
         $b[$k] = $v;
      }
   }
   return [$l, $b];
}

/** User pelaku dari session (+ pelaku asli bila sedang switch user). Tanpa session -> SISTEM. */
function logPelaku(): array
{
   if (!isset($_SESSION) && session_status() === PHP_SESSION_NONE && !headers_sent()) @session_start();
   $s = $_SESSION ?? [];
   $uid = isset($s['uid_user']) && $s['uid_user'] !== '' ? (string) $s['uid_user'] : null;
   $asli = $s['switch_dari'] ?? null;
   $ip = trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))[0]) ?: (string) ($_SERVER['REMOTE_ADDR'] ?? '');
   return [
      'uid_user' => $uid,
      'username' => (string) ($s['username'] ?? '') ?: ($uid ? null : 'SISTEM'),
      'fullname' => isset($s['fullname']) ? (string) $s['fullname'] : null,
      'roles' => isset($s['roles']) ? (string) $s['roles'] : null,
      'pelaku_asli' => is_array($asli) ? mb_substr(trim(($asli['username'] ?? '') . ' (' . ($asli['fullname'] ?? '') . ')'), 0, 150) : null,
      'id_customer' => isset($s['id_customer']) ? (int) $s['id_customer'] : null,
      'ip' => mb_substr($ip, 0, 45) ?: null,
      'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
   ];
}

function logJson(?array $a): ?string
{
   if ($a === null) return null;
   $j = json_encode(logBersihkan($a), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
   return $j === false ? null : $j;
}

/**
 * Jalankan INSERT; bila tabel belum ada (1146) buat dari file migrasi lalu ulangi.
 * Bila kolom belum ada (1054, tabel versi lama), jalankan $upgrade (ALTER TABLE ...) lalu ulangi.
 */
function logTulis(mysqli $db, string $sql, string $tipe, array $nilai, string $migrasi, array $upgrade = []): void
{
   $jalan = function () use ($db, $sql, $tipe, $nilai) {
      $st = $db->prepare($sql);
      if (!$st) throw new RuntimeException($db->error, $db->errno); // mode mysqli tanpa exception
      $st->bind_param($tipe, ...$nilai);
      if (!$st->execute()) throw new RuntimeException($st->error, $st->errno);
      $st->close();
   };
   try {
      $jalan();
   } catch (Throwable $e) {
      $kode = (int) $e->getCode();
      if ($kode === 1146 && logBuatTabel($db, $migrasi)) {
         $jalan();
      } elseif ($kode === 1054 && $upgrade && logUpgrade($db, $upgrade)) {
         $jalan();
      } else {
         throw $e;
      }
   }
}

/** ALTER TABLE untuk tabel log versi lama (sekali per request; "Duplicate column" diabaikan). Jangan di dalam transaksi. */
function logUpgrade(mysqli $db, array $sqls): bool
{
   static $sudah = [];
   $kunci = md5(implode(';', $sqls));
   if (isset($sudah[$kunci])) return $sudah[$kunci];
   $ok = true;
   foreach ($sqls as $sql) {
      try {
         if (!$db->query($sql) && $db->errno !== 1060) $ok = false;
      } catch (Throwable $e) {
         if ((int) $e->getCode() !== 1060) { // 1060 = kolom sudah ada
            error_log('[log] upgrade gagal: ' . $e->getMessage());
            $ok = false;
         }
      }
   }
   return $sudah[$kunci] = $ok;
}

/** Buat tabel dari file migrasi (CREATE TABLE IF NOT EXISTS). Jangan dipanggil di dalam transaksi. */
function logBuatTabel(mysqli $db, string $migrasi): bool
{
   static $sudah = [];
   if (isset($sudah[$migrasi])) return $sudah[$migrasi];
   try {
      $sql = file_get_contents(__DIR__ . '/../migrations/' . $migrasi);
      if ($sql === false) return $sudah[$migrasi] = false;
      $sql = rtrim(trim(preg_replace('/^\s*--.*$/m', '', $sql)), "; \n");
      return $sudah[$migrasi] = (bool) $db->query($sql);
   } catch (Throwable $e) {
      error_log('[log] gagal membuat tabel ' . $migrasi . ': ' . $e->getMessage());
      return $sudah[$migrasi] = false;
   }
}

function logAktivitasSiapkan(mysqli $db): bool
{
   return logBuatTabel($db, '20261128_log_aktivitas.sql');
}

/**
 * Catat aktivitas.
 * $ref: ['tipe' => 'pasien|user|cppt|...', 'id' => ..., 'label' => 'nama/nomor', 'visit_ID' => ...]
 * $idc: id faskes; null = dari session; 0 = tidak diketahui (mis. login gagal).
 */
function logAktivitas(mysqli $db, string $modul, string $aksi, array $ref = [], string $keterangan = '', ?array $lama = null, ?array $baru = null, ?int $idc = null): void
{
   try {
      $p = logPelaku();
      $idc = $idc ?? $p['id_customer'];
      $idc = $idc ? (int) $idc : null;
      $cut = fn($v, int $n) => $v === null || $v === '' ? null : mb_substr((string) $v, 0, $n);
      logTulis($db, "INSERT INTO log_aktivitas (id_customer, modul, aksi, ref_tipe, ref_id, ref_label, visit_ID, keterangan, data_lama, data_baru,
            uid_user, username, fullname, roles, pelaku_asli, ip, user_agent, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
         'isssssssssssssssss', [
            $idc, mb_substr($modul, 0, 30), mb_substr($aksi, 0, 40), $cut($ref['tipe'] ?? null, 30), $cut($ref['id'] ?? null, 100), $cut($ref['label'] ?? null, 200),
            $cut($ref['visit_ID'] ?? null, 100), $keterangan !== '' ? $keterangan : null, logJson($lama), logJson($baru),
            $p['uid_user'], $p['username'], $p['fullname'], $p['roles'], $p['pelaku_asli'], $p['ip'], $p['user_agent'], date('Y-m-d H:i:s'),
         ], '20261128_log_aktivitas.sql');
   } catch (Throwable $e) {
      error_log('[logAktivitas] ' . $modul . '/' . $aksi . ': ' . $e->getMessage());
   }
}
