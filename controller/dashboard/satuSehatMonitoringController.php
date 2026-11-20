<?php
/**
 * Monitoring Satu Sehat (module/admin/monitoring-satusehat).
 *   GET action=dashboard&hari=7[&id_customer=]   -> ringkasan, per resource, tren, penyebab error, antrean data, konfigurasi
 *   GET action=errors&hari=7[&resource=&kategori=&id_customer=] -> 200 error terbaru (pesan sudah diringkas)
 *   GET action=by_faskes&hari=7                   -> ringkasan per faskes (superadmin)
 *
 * Performa: satusehat_log (500rb+ baris) hanya ber-index PRIMARY. id naik seiring waktu, jadi batas id awal periode
 * dicari dengan memindai mundur dari baris terbaru, lalu semua agregasi memakai rentang PRIMARY KEY (id > batas)
 * — hanya baris periode itu yang dibaca, bukan seluruh tabel. Hasil di-cache 60 detik.
 */
require_once __DIR__ . '/../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out($data, $code = 200)
{
   http_response_code($code);
   echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}

$roles = $_SESSION['roles'] ?? '';
if (!in_array($roles, ['superadmin', 'admin'], true)) {
   out(['status' => 'error', 'message' => 'Akses tidak diizinkan.'], 403);
}
$isSuper = $roles === 'superadmin';
// non-superadmin selalu faskes sendiri; 0 = semua faskes (superadmin)
$idc = $isSuper ? (int) ($_GET['id_customer'] ?? 0) : (int) $_SESSION['id_customer'];
// lepas kunci sesi agar beberapa request halaman ini berjalan paralel
session_write_close();

$hari = (int) ($_GET['hari'] ?? 7);
if (!in_array($hari, [1, 7, 30, 90], true)) $hari = 7;

function rows(mysqli $k, string $sql, string $types = '', array $params = []): array
{
   $st = $k->prepare($sql);
   if (!$st) {
      error_log('[satuSehatMonitoring] ' . $k->error);
      return [];
   }
   if ($types !== '') $st->bind_param($types, ...$params);
   if (!$st->execute()) return [];
   $r = $st->get_result();
   return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

/** id terakhir SEBELUM awal periode (0 = semua). */
function batasId(mysqli $k, int $hari): int
{
   $awal = date('Y-m-d 00:00:00', strtotime('-' . ($hari - 1) . ' days'));
   $r = rows($k, "SELECT id FROM satusehat_log WHERE created_at < ? ORDER BY id DESC LIMIT 1", 's', [$awal]);
   return (int) ($r[0]['id'] ?? 0);
}

/** Kategori penyebab error dari isi pesan (SQL CASE, dipakai di agregasi & filter). */
const SS_KATEGORI_SQL = "CASE
   WHEN l.status <> 'error' THEN ''
   WHEN l.message LIKE '%Rate limit%' OR l.message LIKE '%quota%' THEN 'rate_limit'
   WHEN l.message LIKE '%duplicate%' THEN 'duplikat'
   WHEN l.message LIKE '%Practitioner/ %' OR l.message LIKE '%Practitioner/\"%' THEN 'dokter_ihs'
   WHEN l.message LIKE '%Patient/ %' OR l.message LIKE '%Patient/\"%' THEN 'pasien_ihs'
   WHEN l.message LIKE '%kfa%' OR l.message LIKE '%Code not found%' THEN 'kode'
   WHEN l.message LIKE '%401%' OR l.message LIKE '%nauthorized%' OR l.message LIKE '%invalid_client%' OR l.message LIKE '%token%' THEN 'auth'
   WHEN l.message LIKE '%timed out%' OR l.message LIKE '%cURL%' OR l.message LIKE '%Could not resolve%' OR l.message LIKE '%502%' OR l.message LIKE '%503%' THEN 'koneksi'
   ELSE 'lainnya' END";
const SS_SAMPEL = 20000;
const SS_KATEGORI = [
   'rate_limit' => ['Kuota API terlampaui', 'Pengiriman terlalu sering (rate limit). Jadwal sinkronisasi perlu diperlambat / dibatch; data akan terkirim di percobaan berikutnya.'],
   'duplikat' => ['Data sudah ada (duplikat)', 'Resource sudah pernah terkirim tetapi ID-nya tidak tersimpan, sehingga dikirim ulang. Tidak merugikan, tetapi membuang kuota.'],
   'dokter_ihs' => ['Dokter belum punya ID IHS', 'Kunjungan dikirim tanpa ID Practitioner. Lengkapi NIK & ID IHS dokter di Pengaturan Satu Sehat.'],
   'pasien_ihs' => ['Pasien belum punya ID IHS', 'Pasien belum ditemukan di Satu Sehat (NIK kosong/tidak valid).'],
   'kode' => ['Kode tidak dikenal (KFA/ICD)', 'Kode obat KFA atau kode lain tidak ada di master Kemenkes. Perbaiki kode di master obat.'],
   'auth' => ['Autentikasi / kredensial', 'Client ID / secret / organisasi salah atau token ditolak.'],
   'koneksi' => ['Koneksi ke server', 'Server Satu Sehat tidak merespons / jaringan terputus.'],
   'lainnya' => ['Lainnya', 'Periksa pesan lengkap pada daftar error.'],
];

/** Pesan error ringkas & mudah dibaca dari JSON OperationOutcome / fault. */
function ringkasPesan(?string $m): string
{
   $m = (string) $m;
   $j = json_decode($m, true);
   if (is_array($j)) {
      if (isset($j['fault']['faultstring'])) return preg_replace('/\s*Identifier\s*:.*/', '', (string) $j['fault']['faultstring']);
      $t = [];
      foreach (($j['issue'] ?? []) as $is) {
         $x = $is['details']['text'] ?? ($is['diagnostics'] ?? ($is['code'] ?? ''));
         if ($x !== '') $t[] = preg_replace('/\s*\(RuleNumber:\s*\d+\)/', '', (string) $x);
      }
      if ($t) return implode(' · ', array_unique($t));
   }
   return mb_substr(trim(preg_replace('/\s+/', ' ', $m)), 0, 300);
}

function cache(string $kunci, int $detik, callable $hitung)
{
   $f = sys_get_temp_dir() . '/medisafe_ssmon_' . md5($kunci) . '.json';
   if (empty($_GET['refresh']) && is_file($f) && filemtime($f) > time() - $detik) {
      $d = json_decode((string) file_get_contents($f), true);
      if (is_array($d)) return $d + ['cached' => true];
   }
   $d = $hitung();
   @file_put_contents($f, json_encode($d, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE), LOCK_EX);
   return $d;
}

$action = $_GET['action'] ?? 'dashboard';

/* ================= DASHBOARD ================= */
if ($action === 'dashboard' || $action === 'summary') {
   $data = cache("dash_{$idc}_{$hari}", 60, function () use ($koneksi, $idc, $hari) {
      $bid = batasId($koneksi, $hari);
      $wc = $idc ? ' AND l.id_customer = ?' : '';
      $t = 'i' . ($idc ? 'i' : '');
      $p = $idc ? [$bid, $idc] : [$bid];

      // 1 pemindaian rentang PK tanpa kolom pesan (TEXT): per tanggal × resource × status
      $agg = rows($koneksi, "SELECT DATE(l.created_at) tgl, l.resource_type res, l.status st, COUNT(*) n, MAX(l.created_at) akhir
                             FROM satusehat_log l WHERE l.id > ?$wc GROUP BY tgl, res, st", $t, $p);
      // penyebab error: membaca pesan (TEXT) mahal, jadi dari maksimal SS_SAMPEL error terbaru di periode
      $katRows = rows($koneksi, "SELECT x.res, x.kat, COUNT(*) n FROM (
                                    SELECT l.resource_type res, " . SS_KATEGORI_SQL . " kat FROM satusehat_log l
                                    WHERE l.id > ? AND l.status = 'error'$wc ORDER BY l.id DESC LIMIT " . SS_SAMPEL . "
                                 ) x GROUP BY x.res, x.kat", $t, $p);
      $tot = ['ok' => 0, 'error' => 0];
      $res = [];
      $tren = [];
      for ($i = $hari - 1; $i >= 0; $i--) $tren[date('Y-m-d', strtotime("-$i days"))] = ['ok' => 0, 'error' => 0];
      $kat = [];
      $akhir = null;
      foreach ($agg as $a) {
         $ok = $a['st'] === 'success';
         $n = (int) $a['n'];
         $tot[$ok ? 'ok' : 'error'] += $n;
         $r = &$res[$a['res']];
         $r ??= ['resource' => $a['res'], 'ok' => 0, 'error' => 0, 'last_success' => null, 'kategori' => []];
         $r[$ok ? 'ok' : 'error'] += $n;
         if ($ok && $a['akhir'] > ($r['last_success'] ?? '')) $r['last_success'] = $a['akhir'];
         unset($r);
         if (isset($tren[$a['tgl']])) $tren[$a['tgl']][$ok ? 'ok' : 'error'] += $n;
         if ($a['akhir'] > ($akhir ?? '')) $akhir = $a['akhir'];
      }
      $sampel = 0;
      foreach ($katRows as $k) {
         $kat[$k['kat']] = ($kat[$k['kat']] ?? 0) + (int) $k['n'];
         if (isset($res[$k['res']])) $res[$k['res']]['kategori'][$k['kat']] = ($res[$k['res']]['kategori'][$k['kat']] ?? 0) + (int) $k['n'];
         $sampel += (int) $k['n'];
      }
      arsort($kat);
      $res = array_values($res);
      usort($res, fn($x, $y) => ($y['ok'] + $y['error']) <=> ($x['ok'] + $x['error']));

      // antrean data: kunjungan pada periode (index tanggal) & master pasien/dokter (index faskes)
      $awal = date('Y-m-d', strtotime('-' . ($hari - 1) . ' days'));
      $wv = $idc ? ' AND id_customer = ?' : '';
      $pv = $idc ? ['si', [$awal, $idc]] : ['s', [$awal]];
      $kv = rows($koneksi, "SELECT COUNT(*) total,
                     SUM(id_encounter IS NOT NULL AND id_encounter <> '') enc_ok,
                     SUM((id_encounter IS NULL OR id_encounter = '') AND error_encounter IS NOT NULL AND error_encounter <> '') enc_err,
                     SUM(id_condition IS NOT NULL AND id_condition <> '') cond_ok,
                     SUM((id_condition IS NULL OR id_condition = '') AND error_condition IS NOT NULL AND error_condition <> '') cond_err
                   FROM pasien_visit WHERE visit_date >= ?$wv AND (visit_status IS NULL OR visit_status <> 99)", $pv[0], $pv[1])[0] ?? [];
      $total = (int) ($kv['total'] ?? 0);
      $antrean = [
         ['resource' => 'Encounter', 'label' => 'Kunjungan', 'total' => $total, 'ok' => (int) $kv['enc_ok'], 'error' => (int) $kv['enc_err'], 'pending' => $total - (int) $kv['enc_ok'] - (int) $kv['enc_err']],
         ['resource' => 'Condition', 'label' => 'Diagnosis', 'total' => $total, 'ok' => (int) $kv['cond_ok'], 'error' => (int) $kv['cond_err'], 'pending' => $total - (int) $kv['cond_ok'] - (int) $kv['cond_err']],
      ];
      $wm = $idc ? ' WHERE id_customer = ?' : '';
      $pm = $idc ? ['i', [$idc]] : ['', []];
      $pas = rows($koneksi, "SELECT COUNT(*) total, SUM(idsh IS NOT NULL AND idsh <> '') ok FROM ms_patient$wm", ...$pm)[0] ?? [];
      $antrean[] = ['resource' => 'Patient', 'label' => 'Pasien (semua)', 'total' => (int) $pas['total'], 'ok' => (int) $pas['ok'], 'error' => 0, 'pending' => (int) $pas['total'] - (int) $pas['ok']];

      // dokter tanpa ID IHS + jumlah kunjungannya di periode (penyebab "Practitioner/" kosong)
      $dok = rows($koneksi, "SELECT d.id_customer, d.doctor_name, d.doctor_nik, c.clinic_name FROM ms_doctor d
                             LEFT JOIN setting_clinic c ON c.id_customer = d.id_customer
                             WHERE (d.idsh IS NULL OR d.idsh = '')" . ($idc ? ' AND d.id_customer = ?' : ''), ...$pm);
      $dokOut = [];
      if ($dok) {
         $kunj = [];
         foreach (rows($koneksi, "SELECT id_customer, TRIM(id_doctor) dok, COUNT(*) n FROM pasien_visit WHERE visit_date >= ?$wv GROUP BY id_customer, dok", $pv[0], $pv[1]) as $x) {
            $kunj[$x['id_customer'] . '|' . mb_strtolower((string) $x['dok'])] = (int) $x['n'];
         }
         foreach ($dok as $d) {
            $dokOut[] = ['nama' => $d['doctor_name'], 'faskes' => $d['clinic_name'], 'nik' => (string) $d['doctor_nik'] !== '' && (string) $d['doctor_nik'] !== '0',
               'kunjungan' => $kunj[$d['id_customer'] . '|' . mb_strtolower(trim((string) $d['doctor_name']))] ?? 0];
         }
         usort($dokOut, fn($x, $y) => $y['kunjungan'] <=> $x['kunjungan']);
      }

      $cfg = rows($koneksi, "SELECT s.id_customer, c.clinic_name,
              (s.client_id <> '' AND s.client_id IS NOT NULL AND s.client_secret <> '' AND s.organization_id <> '') credentials_ok,
              (s.location_id <> '' AND s.location_id IS NOT NULL) location_ok
         FROM setting_satusehat s LEFT JOIN setting_clinic c ON c.id_customer = s.id_customer" . ($idc ? ' WHERE s.id_customer = ?' : ''), ...$pm);

      return [
         'status' => 'success',
         'hari' => $hari,
         'diperbarui' => date('Y-m-d H:i:s'),
         'total' => $tot + ['rate' => ($tot['ok'] + $tot['error']) ? round($tot['ok'] / ($tot['ok'] + $tot['error']) * 100, 1) : null, 'aktivitas_terakhir' => $akhir],
         'resource' => $res,
         'tren' => array_map(fn($k, $v) => ['tgl' => $k] + $v, array_keys($tren), $tren),
         'kategori_sampel' => $sampel,
         'kategori_semua' => $sampel >= $tot['error'],
         'kategori' => array_map(fn($k, $v) => ['kode' => $k, 'label' => SS_KATEGORI[$k][0] ?? $k, 'saran' => SS_KATEGORI[$k][1] ?? '', 'jumlah' => $v], array_keys($kat), $kat),
         'antrean' => $antrean,
         'dokter_tanpa_ihs' => array_slice($dokOut, 0, 50),
         'config' => [
            'jumlah' => count($cfg),
            'lengkap' => count(array_filter($cfg, fn($c) => (int) $c['credentials_ok'] && (int) $c['location_ok'])),
            'kredensial' => $cfg ? (bool) (int) $cfg[0]['credentials_ok'] : false,
            'lokasi' => $cfg ? (bool) (int) $cfg[0]['location_ok'] : false,
         ],
      ];
   });
   out($data);
}

/* ================= ERROR TERBARU ================= */
if ($action === 'errors') {
   $bid = batasId($koneksi, $hari);
   $sql = "SELECT l.id, l.created_at, l.id_customer, c.clinic_name, l.resource_type, l.reference_id, LEFT(l.message, 2000) message, " . SS_KATEGORI_SQL . " kat
           FROM satusehat_log l LEFT JOIN setting_clinic c ON c.id_customer = l.id_customer
           WHERE l.id > ? AND l.status = 'error'";
   $t = 'i';
   $p = [$bid];
   if ($idc) { $sql .= ' AND l.id_customer = ?'; $t .= 'i'; $p[] = $idc; }
   $res = trim((string) ($_GET['resource'] ?? ''));
   if ($res !== '') { $sql .= ' AND l.resource_type = ?'; $t .= 's'; $p[] = mb_substr($res, 0, 50); }
   $kat = (string) ($_GET['kategori'] ?? '');
   if (isset(SS_KATEGORI[$kat])) { $sql = "SELECT * FROM ($sql) x WHERE x.kat = ?"; $t .= 's'; $p[] = $kat; }
   $sql .= ' ORDER BY ' . (isset(SS_KATEGORI[$kat]) ? 'x.id' : 'l.id') . ' DESC LIMIT 200';
   $data = array_map(fn($r) => [
      'created_at' => $r['created_at'],
      'clinic_name' => $r['clinic_name'],
      'resource_type' => $r['resource_type'],
      'reference_id' => $r['reference_id'],
      'kategori' => $r['kat'],
      'kategori_label' => SS_KATEGORI[$r['kat']][0] ?? $r['kat'],
      'ringkas' => ringkasPesan($r['message']),
      'message' => mb_substr((string) $r['message'], 0, 2000),
   ], rows($koneksi, $sql, $t, $p));
   out(['status' => 'success', 'data' => $data]);
}

/* ================= PER FASKES (superadmin) ================= */
if ($action === 'by_faskes') {
   if (!$isSuper) out(['status' => 'error', 'message' => 'Akses tidak diizinkan.'], 403);
   $data = cache("faskes_{$hari}", 60, function () use ($koneksi, $hari) {
      $bid = batasId($koneksi, $hari);
      $log = [];
      foreach (rows($koneksi, "SELECT id_customer, SUM(status = 'success') ok, SUM(status = 'error') error, MAX(created_at) akhir
                               FROM satusehat_log WHERE id > ? GROUP BY id_customer", 'i', [$bid]) as $r) {
         $log[(int) $r['id_customer']] = $r;
      }
      $out = [];
      foreach (rows($koneksi, "SELECT c.id_customer, c.clinic_name,
                   (s.client_id <> '' AND s.client_id IS NOT NULL AND s.client_secret <> '' AND s.organization_id <> '' AND s.location_id <> '' AND s.location_id IS NOT NULL) lengkap,
                   s.id_customer IS NOT NULL ada
            FROM setting_clinic c LEFT JOIN setting_satusehat s ON s.id_customer = c.id_customer ORDER BY c.clinic_name") as $c) {
         $l = $log[(int) $c['id_customer']] ?? ['ok' => 0, 'error' => 0, 'akhir' => null];
         $tot = (int) $l['ok'] + (int) $l['error'];
         $out[] = ['id_customer' => (int) $c['id_customer'], 'clinic_name' => $c['clinic_name'], 'ok' => (int) $l['ok'], 'error' => (int) $l['error'],
            'rate' => $tot ? round((int) $l['ok'] / $tot * 100, 1) : null, 'last_activity' => $l['akhir'],
            'config' => !(int) $c['ada'] ? 'belum' : ((int) $c['lengkap'] ? 'lengkap' : 'kurang')];
      }
      usort($out, fn($x, $y) => [$y['error'], $y['ok']] <=> [$x['error'], $x['ok']]);
      return ['status' => 'success', 'data' => $out];
   });
   out($data);
}

out(['status' => 'error', 'message' => 'Action tidak dikenal.'], 400);
