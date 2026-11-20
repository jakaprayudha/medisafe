<?php
/**
 * Gabung RME (module/admin/merge_rme): satu pasien terdaftar dengan lebih dari satu nomor RM di faskes yang sama.
 *
 * GET  ?action=kandidat&page=1        daftar kelompok RM ganda (NIK / no. kartu BPJS sama) di faskes ini
 * GET  ?action=cari&keyword=...        cari pasien (RM / nama / NIK / no. BPJS) + jumlah RM ganda-nya
 * GET  ?action=grup&id_patient=...     detail kelompok: identitas tiap RM, jumlah & riwayat kunjungan
 * POST ?action=gabung  JSON {utama, gabung:[id...], konfirmasi:"<RM utama>", paksa:bool}
 *
 * Perbaikan dari versi lama:
 * - Lama mencari & menggabung LINTAS FASKES: kunjungan pasien faskes lain dipindah ke faskes ini lalu data pasien
 *   faskes lain dihapus. Sekarang hanya dalam faskes session.
 * - Lama mencocokkan NIK apa adanya: NIK "-", "0000000000" dst. dianggap sama -> pasien berbeda bisa tergabung.
 *   Sekarang hanya NIK 16 digit valid / no. kartu BPJS 13 digit valid.
 * - Lama memakai patient_number (kode internal "PCT-...") sebagai "nomor kartu"; no. kartu BPJS ada di patient_bpjs.
 * - Lama hanya memindah pasien_visit: CPPT, permintaan ranap, surat-surat, triase, resume, dll. tertinggal di
 *   id_patient / nomor RM yang sudah dihapus. Sekarang semua tabel terkait ikut dipindah.
 * - pasien_visit.id_patient bertipe VARCHAR tapi di-bind integer -> index tak terpakai (scan 200rb baris) dan
 *   UPDATE di dalam transaksi mengunci seluruh baris kunjungan. Sekarang di-bind string.
 * - Lama tanpa cek role, tanpa cadangan sebelum DELETE, dan bisa menggabung orang berbeda yang berbagi NIK
 *   (mis. bayi memakai NIK ibu) tanpa peringatan.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function gJawab(array $d, int $c = 200): void
{
   http_response_code($c);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}

requireRole(['admin', 'superadmin', 'administrator']);
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$oleh = (string) ($_SESSION['username'] ?? 'system');
session_write_close();
if ($idc <= 0) gJawab(['status' => 'error', 'message' => 'Session faskes tidak ditemukan.'], 401);

const G_MAKS_GABUNG = 10;
const G_KOLOM = 'id_patient, nomor_rm, nomor_rm_any, patient_name, patient_nik, patient_bpjs, patient_number, patient_datebirth, patient_gender,
   patient_phone, patient_address, idsh, created_at, registration_date';

// ---------------------------------------------------------------- helper

function gQ(mysqli $db, string $sql, string $t = '', array $p = []): array
{
   $st = $db->prepare($sql);
   if (!$st) throw new RuntimeException('Query gagal disiapkan: ' . $db->error);
   if ($t !== '') $st->bind_param($t, ...$p);
   if (!$st->execute()) throw new RuntimeException('Query gagal: ' . $st->error);
   $r = $st->get_result();
   $rows = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
   $st->close();
   return $rows;
}

function gExec(mysqli $db, string $sql, string $t, array $p): int
{
   $st = $db->prepare($sql);
   if (!$st) throw new RuntimeException('Query gagal disiapkan: ' . $db->error);
   $st->bind_param($t, ...$p);
   if (!$st->execute()) throw new RuntimeException('Query gagal: ' . $st->error);
   $n = $st->affected_rows;
   $st->close();
   return max(0, (int) $n);
}

/** NIK 16 digit, kode provinsi 11-94, bukan angka berulang (0000.../1111...). */
function gNik($v): string
{
   $v = trim((string) $v);
   if (!preg_match('/^\d{16}$/', $v) || preg_match('/^(\d)\1{15}$/', $v)) return '';
   $prov = (int) substr($v, 0, 2);
   return ($prov >= 11 && $prov <= 94) ? $v : '';
}

/** No. kartu BPJS 13 digit, bukan angka berulang. */
function gBpjs($v): string
{
   $v = trim((string) $v);
   return (preg_match('/^\d{13}$/', $v) && !preg_match('/^(\d)\1{12}$/', $v) && ltrim($v, '0') !== '' && strlen(ltrim($v, '0')) >= 6) ? $v : '';
}

function gIn(int $n): string
{
   return implode(',', array_fill(0, $n, '?'));
}

/** Kunci identitas valid sebuah baris pasien. */
function gKunci(array $p): array
{
   $k = [];
   if ($n = gNik($p['patient_nik'] ?? '')) $k[] = 'N' . $n;
   if ($b = gBpjs($p['patient_bpjs'] ?? '')) $k[] = 'B' . $b;
   return $k;
}

/** Statistik kunjungan per id_patient (id_patient di pasien_visit VARCHAR -> bind string agar index terpakai). */
function gStatKunjungan(mysqli $db, int $idc, array $ids): array
{
   $out = [];
   foreach (array_chunk(array_values(array_unique(array_map('strval', $ids))), 500) as $c) {
      $rows = gQ($db, "SELECT id_patient, COUNT(*) n, MIN(visit_date) awal, MAX(visit_date) akhir
         FROM pasien_visit WHERE id_patient IN (" . gIn(count($c)) . ") AND id_customer = ? AND visit_status <> 99 GROUP BY id_patient",
         str_repeat('s', count($c)) . 'i', array_merge($c, [$idc]));
      foreach ($rows as $r) $out[(int) $r['id_patient']] = $r;
   }
   return $out;
}

function gBentuk(array $p, array $stat): array
{
   $s = $stat[(int) $p['id_patient']] ?? null;
   return [
      'id' => (int) $p['id_patient'],
      'rm' => (string) $p['nomor_rm'],
      'rm_lain' => (string) ($p['nomor_rm_any'] ?? ''),
      'nama' => (string) $p['patient_name'],
      'nik' => (string) ($p['patient_nik'] ?? ''),
      'nik_valid' => gNik($p['patient_nik'] ?? '') !== '',
      'bpjs' => (string) ($p['patient_bpjs'] ?? ''),
      'bpjs_valid' => gBpjs($p['patient_bpjs'] ?? '') !== '',
      'lahir' => (string) ($p['patient_datebirth'] ?? ''),
      'jk' => (string) ($p['patient_gender'] ?? ''),
      'hp' => (string) ($p['patient_phone'] ?? ''),
      'alamat' => (string) ($p['patient_address'] ?? ''),
      'ihs' => (string) ($p['idsh'] ?? ''),
      'daftar' => (string) (($p['registration_date'] ?? '') ?: ($p['created_at'] ?? '')),
      'kunjungan' => $s ? (int) $s['n'] : 0,
      'awal' => $s['awal'] ?? null,
      'akhir' => $s['akhir'] ?? null,
   ];
}

function gCacheFile(int $idc): string
{
   return sys_get_temp_dir() . '/medisafe_rm_ganda_' . $idc . '.json';
}

/** Kelompok RM ganda di faskes: union-find atas NIK valid & no. BPJS valid. Di-cache 2 menit (query ~1-2 dtk di faskes besar). */
function gKelompok(mysqli $db, int $idc): array
{
   $f = gCacheFile($idc);
   if (is_file($f) && filemtime($f) > time() - 120) {
      $c = json_decode((string) @file_get_contents($f), true);
      if (is_array($c)) return $c;
   }
   $induk = [];
   $cari = function ($x) use (&$induk) {
      while ($induk[$x] !== $x) $x = $induk[$x] = $induk[$induk[$x]];
      return $x;
   };
   foreach (['N' => ['patient_nik', '^[0-9]{16}$', 'gNik'], 'B' => ['patient_bpjs', '^[0-9]{13}$', 'gBpjs']] as $jenis => [$kol, $re, $fn]) {
      $rows = gQ($db, "SELECT $kol k, GROUP_CONCAT(id_patient) ids FROM ms_patient WHERE id_customer = ? AND $kol REGEXP '$re'
         GROUP BY $kol HAVING COUNT(*) > 1", 'i', [$idc]);
      foreach ($rows as $r) {
         if ($fn($r['k']) === '') continue;
         $ids = array_map('intval', explode(',', $r['ids']));
         foreach ($ids as $i) $induk[$i] = $induk[$i] ?? $i;
         $a = $cari($ids[0]);
         foreach ($ids as $i) {
            $b = $cari($i);
            if ($a !== $b) $induk[$b] = $a;
         }
      }
   }
   $grup = [];
   foreach (array_keys($induk) as $i) $grup[$cari($i)][] = $i;
   $hasil = [];
   foreach ($grup as $ids) {
      sort($ids);
      $hasil[] = $ids;
   }
   // terbanyak dulu, lalu yang terbaru didaftarkan
   usort($hasil, fn($a, $b) => (count($b) <=> count($a)) ?: (max($b) <=> max($a)));
   @file_put_contents($f, json_encode($hasil), LOCK_EX);
   return $hasil;
}

/** Anggota kelompok sebuah pasien (dalam faskes): ikuti NIK/BPJS valid sampai tidak bertambah. */
function gAnggota(mysqli $db, int $idc, array $seed): array
{
   $angg = [(int) $seed['id_patient'] => $seed];
   $nik = [];
   $bpjs = [];
   for ($i = 0; $i < 3; $i++) {
      $nikBaru = $bpjsBaru = [];
      foreach ($angg as $p) {
         if (($n = gNik($p['patient_nik'] ?? '')) && !isset($nik[$n])) $nikBaru[$n] = $nik[$n] = 1;
         if (($b = gBpjs($p['patient_bpjs'] ?? '')) && !isset($bpjs[$b])) $bpjsBaru[$b] = $bpjs[$b] = 1;
      }
      if (!$nikBaru && !$bpjsBaru) break;
      $w = [];
      $t = 'i';
      $p = [$idc];
      if ($nikBaru) {
         $w[] = 'patient_nik IN (' . gIn(count($nikBaru)) . ')';
         $t .= str_repeat('s', count($nikBaru));
         array_push($p, ...array_map('strval', array_keys($nikBaru)));
      }
      if ($bpjsBaru) {
         $w[] = 'patient_bpjs IN (' . gIn(count($bpjsBaru)) . ')';
         $t .= str_repeat('s', count($bpjsBaru));
         array_push($p, ...array_map('strval', array_keys($bpjsBaru)));
      }
      foreach (gQ($db, "SELECT " . G_KOLOM . " FROM ms_patient WHERE id_customer = ? AND (" . implode(' OR ', $w) . ") LIMIT 50", $t, $p) as $r) {
         // pastikan cocok setelah trim/validasi (bukan hanya cocok string mentah)
         if (array_intersect(gKunci($r), array_merge(array_map(fn($x) => 'N' . $x, array_keys($nik)), array_map(fn($x) => 'B' . $x, array_keys($bpjs))))) {
            $angg[(int) $r['id_patient']] = $r;
         }
      }
      if (count($angg) >= 30) break;
   }
   return $angg;
}

// ---------------------------------------------------------------- aksi

$aksi = (string) ($_GET['action'] ?? '');

try {
   if ($aksi === 'kandidat') {
      $grup = gKelompok($koneksi, $idc);
      $per = 12;
      $total = count($grup);
      $hal = max(1, min((int) ($_GET['page'] ?? 1), max(1, (int) ceil($total / $per))));
      $irisan = array_slice($grup, ($hal - 1) * $per, $per);
      $ids = array_merge([], ...$irisan);
      $pasien = [];
      if ($ids) {
         foreach (gQ($koneksi, "SELECT " . G_KOLOM . " FROM ms_patient WHERE id_customer = ? AND id_patient IN (" . gIn(count($ids)) . ")",
            'i' . str_repeat('i', count($ids)), array_merge([$idc], $ids)) as $r) $pasien[(int) $r['id_patient']] = $r;
      }
      $stat = $ids ? gStatKunjungan($koneksi, $idc, $ids) : [];
      $data = [];
      foreach ($irisan as $g) {
         $angg = [];
         foreach ($g as $i) if (isset($pasien[$i])) $angg[] = gBentuk($pasien[$i], $stat);
         if (count($angg) > 1) $data[] = $angg;
      }
      $totalPasien = array_sum(array_map('count', $grup));
      gJawab(['status' => 'success', 'data' => $data, 'total' => $total, 'total_pasien' => $totalPasien, 'page' => $hal, 'pages' => max(1, (int) ceil($total / $per))]);
   }

   if ($aksi === 'cari') {
      $kw = trim((string) ($_GET['keyword'] ?? ''));
      if (mb_strlen($kw) < 3) gJawab(['status' => 'error', 'message' => 'Ketik minimal 3 karakter.'], 422);
      if (preg_match('/^\d{13,16}$/', $kw)) {
         // NIK / no. BPJS: pencarian tepat memakai index
         $rows = gQ($koneksi, "SELECT " . G_KOLOM . " FROM ms_patient WHERE id_customer = ? AND (patient_nik = ? OR patient_bpjs = ?) LIMIT 30", 'iss', [$idc, $kw, $kw]);
      } else {
         $like = '%' . addcslashes($kw, '%_\\') . '%';
         $rows = gQ($koneksi, "SELECT " . G_KOLOM . " FROM ms_patient WHERE id_customer = ? AND (nomor_rm = ? OR nomor_rm LIKE ? OR patient_name LIKE ? OR patient_nik LIKE ? OR patient_bpjs LIKE ?)
            ORDER BY (nomor_rm = ?) DESC, patient_name LIMIT 30", 'issssss', [$idc, $kw, $like, $like, $like, $like, $kw]);
      }
      // jumlah RM lain dengan identitas valid yang sama
      $nik = $bpjs = [];
      foreach ($rows as $r) {
         if ($n = gNik($r['patient_nik'])) $nik[$n] = 1;
         if ($b = gBpjs($r['patient_bpjs'])) $bpjs[$b] = 1;
      }
      $hit = [];
      if ($nik) foreach (gQ($koneksi, "SELECT patient_nik k, COUNT(*) n FROM ms_patient WHERE id_customer = ? AND patient_nik IN (" . gIn(count($nik)) . ") GROUP BY patient_nik",
         'i' . str_repeat('s', count($nik)), array_merge([$idc], array_map('strval', array_keys($nik)))) as $r) $hit['N' . $r['k']] = (int) $r['n'];
      if ($bpjs) foreach (gQ($koneksi, "SELECT patient_bpjs k, COUNT(*) n FROM ms_patient WHERE id_customer = ? AND patient_bpjs IN (" . gIn(count($bpjs)) . ") GROUP BY patient_bpjs",
         'i' . str_repeat('s', count($bpjs)), array_merge([$idc], array_map('strval', array_keys($bpjs)))) as $r) $hit['B' . $r['k']] = (int) $r['n'];
      $stat = gStatKunjungan($koneksi, $idc, array_column($rows, 'id_patient'));
      $data = [];
      foreach ($rows as $r) {
         $x = gBentuk($r, $stat);
         $x['ganda'] = max(0, max(array_map(fn($k) => $hit[$k] ?? 1, gKunci($r) ?: ['-'])) - 1);
         $data[] = $x;
      }
      gJawab(['status' => 'success', 'data' => $data]);
   }

   if ($aksi === 'grup') {
      $id = (int) ($_GET['id_patient'] ?? 0);
      $seed = gQ($koneksi, "SELECT " . G_KOLOM . " FROM ms_patient WHERE id_patient = ? AND id_customer = ? LIMIT 1", 'ii', [$id, $idc])[0] ?? null;
      if (!$seed) gJawab(['status' => 'error', 'message' => 'Pasien tidak ditemukan di faskes ini.'], 404);
      $angg = gAnggota($koneksi, $idc, $seed);
      $stat = gStatKunjungan($koneksi, $idc, array_keys($angg));
      $data = [];
      foreach ($angg as $p) $data[] = gBentuk($p, $stat);
      // RM utama yang disarankan: kunjungan terbanyak, lalu terdaftar paling awal
      usort($data, fn($a, $b) => ($b['kunjungan'] <=> $a['kunjungan']) ?: ($a['id'] <=> $b['id']));
      $ids = array_map('strval', array_column($data, 'id'));
      $kunj = gQ($koneksi, "SELECT visit_ID, visit_date, visit_status, id_poli, id_doctor, id_patient FROM pasien_visit
         WHERE id_patient IN (" . gIn(count($ids)) . ") AND id_customer = ? ORDER BY visit_date DESC, id_visit DESC LIMIT 300",
         str_repeat('s', count($ids)) . 'i', array_merge($ids, [$idc]));
      $alasan = null;
      if (count($data) < 2) {
         $alasan = gNik($seed['patient_nik']) === '' && gBpjs($seed['patient_bpjs']) === ''
            ? 'Pasien ini tidak memiliki NIK (16 digit) atau no. kartu BPJS (13 digit) yang valid, jadi RM gandanya tidak dapat dicari otomatis. Lengkapi dulu identitasnya di data pasien.'
            : 'Tidak ada RM lain dengan NIK / no. kartu BPJS yang sama di faskes ini.';
      }
      gJawab(['status' => 'success', 'data' => $data, 'kunjungan' => array_map(fn($v) => [
         'id' => $v['visit_ID'], 'tgl' => $v['visit_date'], 'status' => (int) $v['visit_status'], 'poli' => $v['id_poli'], 'dokter' => $v['id_doctor'], 'pasien' => (int) $v['id_patient'],
      ], $kunj), 'kunjungan_terpotong' => count($kunj) >= 300, 'pesan' => $alasan]);
   }

   if ($aksi === 'gabung') {
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') gJawab(['status' => 'error', 'message' => 'Metode tidak diizinkan.'], 405);
      $in = json_decode((string) file_get_contents('php://input'), true);
      if (!is_array($in)) $in = $_POST;
      $utamaId = (int) ($in['utama'] ?? 0);
      $gabungIds = array_values(array_unique(array_filter(array_map('intval', (array) ($in['gabung'] ?? [])), fn($x) => $x > 0 && $x !== $utamaId)));
      $paksa = !empty($in['paksa']);
      if ($utamaId <= 0 || !$gabungIds) gJawab(['status' => 'error', 'message' => 'Pilih RM utama dan minimal satu RM yang digabung.'], 422);
      if (count($gabungIds) > G_MAKS_GABUNG) gJawab(['status' => 'error', 'message' => 'Maksimal ' . G_MAKS_GABUNG . ' RM sekali gabung.'], 422);
      require __DIR__ . '/gabungRmeProses.php';
      $hasil = gProsesGabung($koneksi, $idc, $utamaId, $gabungIds, trim((string) ($in['konfirmasi'] ?? '')), $paksa, $oleh);
      $kode = (int) ($hasil['_kode'] ?? 200);
      unset($hasil['_kode']);
      if ($hasil['status'] === 'success') @unlink(gCacheFile($idc));
      gJawab($hasil, $kode);
   }

   gJawab(['status' => 'error', 'message' => 'Aksi tidak dikenal.'], 400);
} catch (Throwable $e) {
   error_log('[gabungRme] ' . $e->getMessage());
   gJawab(['status' => 'error', 'message' => 'Terjadi kesalahan server. Silakan coba lagi.'], 500);
}
