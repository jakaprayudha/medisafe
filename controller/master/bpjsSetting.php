<?php
/**
 * Pengaturan Bridging BPJS (halaman module/admin/setting_bpjs).
 * Menggantikan endpoint lama: pcareSetting, pcareUpdate, sinkron_dokter, getDataIcare, updateDataIcare,
 * get_jadwal_dokter, simpan_jadwal, updateKuotaJKN, updateStatusJadwal, hapusJadwal,
 * get_dokter_internal, getDokterInternal, simpan_dokter_internal, hapus_dokter_internal.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';

header('Content-Type: application/json');

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) {
   http_response_code(401);
   echo json_encode(['success' => false, 'message' => 'Session tidak valid / expired']);
   exit;
}
requireRole(['admin', 'superadmin', 'administrator']);

const BS_HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

function bsJawab(array $d, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}

function bsGagal(string $msg, int $code = 422): void
{
   bsJawab(['success' => false, 'message' => $msg], $code);
}

function bsSemua(mysqli $k, string $sql, string $types = '', array $params = []): array
{
   $st = $k->prepare($sql);
   if ($types !== '') {
      $st->bind_param($types, ...$params);
   }
   $st->execute();
   $r = $st->get_result();
   $rows = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
   $st->close();
   return $rows;
}

function bsSatu(mysqli $k, string $sql, string $types = '', array $params = []): ?array
{
   return bsSemua($k, $sql, $types, $params)[0] ?? null;
}

function bsJalankan(mysqli $k, string $sql, string $types, array $params): int
{
   $st = $k->prepare($sql);
   $st->bind_param($types, ...$params);
   $st->execute();
   $n = $st->affected_rows;
   $st->close();
   return $n;
}

/** Tabel log akun PCare ada? (dibuat lewat migrations/20261123_setting_pcare_log.sql) */
function bsAdaLogPcare(mysqli $k): bool
{
   static $ada = null;
   if ($ada === null) {
      $r = $k->query("SHOW TABLES LIKE 'setting_pcare_log'");
      $ada = $r && $r->num_rows > 0;
   }
   return $ada;
}

/** Riwayat perubahan akun PCare terbaru (kosong bila tabel log belum dibuat). */
function bsLogPcare(mysqli $k, int $idc, int $batas = 10): array
{
   if (!bsAdaLogPcare($k)) return [];
   return bsSemua($k, "SELECT username_lama, username_baru, password_diganti, diubah_oleh, created_at FROM setting_pcare_log
      WHERE id_customer=? ORDER BY created_at DESC, id DESC LIMIT " . (int) $batas, 'i', [$idc]);
}

/** Tampilkan hanya 4 karakter terakhir. */
function bsMasker(?string $v): ?string
{
   $v = (string) $v;
   if ($v === '') {
      return null;
   }
   return str_repeat('•', 6) . (mb_strlen($v) > 6 ? mb_substr($v, -4) : '');
}

function bsDokterBpjs(mysqli $k, int $idc, string $kode): ?array
{
   return bsSatu($k, "SELECT id, kdDokter, nmDokter, status FROM master_doctor_bpjs WHERE id_customer=? AND kdDokter=? LIMIT 1", 'is', [$idc, $kode]);
}

$aksi = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['aksi'] ?? '') : ($_GET['aksi'] ?? '');

// ---- aksi yang memanggil API BPJS: muat konfigurasi service di scope global ----
if (in_array($aksi, ['tes', 'sinkron'], true) && $_SERVER['REQUEST_METHOD'] === 'POST') {
   $cfg = bsSatu($koneksi, "SELECT base_url, cons_id, secret_key, user_key, username, password FROM setting_pcare WHERE id_customer=? LIMIT 1", 'i', [$idc]);
   if (!$cfg || !$cfg['base_url'] || !$cfg['cons_id'] || !$cfg['secret_key']) {
      bsGagal('Kredensial PCare faskes ini belum lengkap. Hubungi tim IT.', 409);
   }
   require_once __DIR__ . '/../admisi/services/view.php';
   require_once __DIR__ . '/../../vendor/autoload.php';
   require_once __DIR__ . '/../admisi/services/servicebpjs.php';

   $t0 = microtime(true);
   $res = bpjsGet($aksi === 'tes' ? 'dokter/0/1' : 'dokter/0/100');
   $ms = (int) round((microtime(true) - $t0) * 1000);

   if (empty($res['success'])) {
      $pesan = trim((string) ($res['message'] ?? 'Gagal terhubung ke PCare'));
      bsJawab(['success' => false, 'message' => 'PCare menolak / tidak merespons: ' . $pesan, 'ms' => $ms], 502);
   }

   if ($aksi === 'tes') {
      bsJawab(['success' => true, 'message' => 'Terhubung ke PCare', 'ms' => $ms]);
   }

   // ---- sinkron dokter ----
   $list = $res['data']['list'] ?? [];
   if (!is_array($list) || !$list) {
      bsGagal('PCare tidak mengembalikan data dokter. Data lokal tidak diubah.', 502);
   }

   $koneksi->begin_transaction();
   try {
      $tambah = $update = 0;
      $kodeApi = [];
      foreach ($list as $d) {
         $kd = trim((string) ($d['kdDokter'] ?? ''));
         $nm = trim((string) ($d['nmDokter'] ?? ''));
         if ($kd === '' || $nm === '') {
            continue;
         }
         $kodeApi[$kd] = true;
         if (bsDokterBpjs($koneksi, $idc, $kd)) {
            bsJalankan($koneksi, "UPDATE master_doctor_bpjs SET nmDokter=?, status='1' WHERE id_customer=? AND kdDokter=?", 'sis', [$nm, $idc, $kd]);
            $update++;
         } else {
            bsJalankan($koneksi, "INSERT INTO master_doctor_bpjs (kdDokter, nmDokter, id_customer, status) VALUES (?,?,?,'1')", 'ssi', [$kd, $nm, $idc]);
            $tambah++;
         }
      }

      // dokter yang tidak ada lagi di PCare dinonaktifkan (bukan dihapus) agar kredensial iCare & jadwal tidak hilang
      $nonaktif = 0;
      foreach (bsSemua($koneksi, "SELECT kdDokter FROM master_doctor_bpjs WHERE id_customer=? AND status='1'", 'i', [$idc]) as $r) {
         if (!isset($kodeApi[$r['kdDokter']])) {
            $nonaktif += bsJalankan($koneksi, "UPDATE master_doctor_bpjs SET status='0' WHERE id_customer=? AND kdDokter=?", 'is', [$idc, $r['kdDokter']]);
         }
      }
      $koneksi->commit();
   } catch (Throwable $e) {
      $koneksi->rollback();
      bsGagal('Gagal menyimpan hasil sinkron.', 500);
   }

   bsJawab([
      'success' => true,
      'message' => "Sinkron selesai: $tambah baru, $update diperbarui, $nonaktif dinonaktifkan.",
      'ms' => $ms
   ]);
}

// ---- tes koneksi Antrean Online (GET ref/poli: ringan, hanya baca) ----
if ($aksi === 'tes_antrol' && $_SERVER['REQUEST_METHOD'] === 'POST') {
   $a = bsSatu($koneksi, "SELECT 1 FROM setting_antrol WHERE id_customer=? AND base_url<>'' AND constid<>'' AND secretkey<>'' AND userkey<>'' LIMIT 1", 'i', [$idc]);
   if (!$a) {
      bsGagal('Kredensial Antrean Online faskes ini belum lengkap. Hubungi tim IT.', 409);
   }
   require_once __DIR__ . '/../../vendor/autoload.php';
   require_once __DIR__ . '/../wsbpjs/serviceantrian.php';

   $t0 = microtime(true);
   $res = antrolGet('ref/poli', antrolGetConfigBPJS($idc, $koneksi));
   $ms = (int) round((microtime(true) - $t0) * 1000);

   if (empty($res['success'])) {
      bsJawab(['success' => false, 'message' => 'Antrean Online menolak / tidak merespons: ' . trim((string) ($res['message'] ?? 'Gagal terhubung')), 'ms' => $ms], 502);
   }
   bsJawab(['success' => true, 'message' => 'Terhubung ke Antrean Online', 'ms' => $ms]);
}

switch ($aksi) {
   // ================= STATUS / KONFIGURASI =================
   case 'status':
      $p = bsSatu($koneksi, "SELECT username, password, KodePPK, cons_id, service_name, base_url, secret_key, user_key FROM setting_pcare WHERE id_customer=? LIMIT 1", 'i', [$idc]);
      $a = bsSatu($koneksi, "SELECT username, base_url, service, kodePPK, constid, secretkey, userkey FROM setting_antrol WHERE id_customer=? LIMIT 1", 'i', [$idc]);
      $host = $p && $p['base_url'] ? (parse_url($p['base_url'], PHP_URL_HOST) ?: $p['base_url']) : null;
      bsJawab([
         'success' => true,
         'pcare' => $p ? [
            'username' => $p['username'],
            'ada_password' => (string) $p['password'] !== '',
            'kode_ppk' => $p['KodePPK'],
            'cons_id' => $p['cons_id'],
            'service' => $p['service_name'],
            'server' => $host,
            'secret_key' => bsMasker($p['secret_key']),
            'user_key' => bsMasker($p['user_key']),
            'log_aktif' => bsAdaLogPcare($koneksi),
            'log' => bsLogPcare($koneksi, $idc),
         ] : null,
         'antrol' => $a ? [
            'username' => $a['username'],
            'base_url' => $a['base_url'],
            'service' => $a['service'],
            'kode_ppk' => $a['kodePPK'],
            'cons_id' => $a['constid'],
            'secret_key' => bsMasker($a['secretkey']),
            'user_key' => bsMasker($a['userkey']),
         ] : null,
      ]);

   case 'simpan_pcare':
      $user = trim((string) ($_POST['username'] ?? ''));
      $pass = (string) ($_POST['password'] ?? '');
      if ($user === '' || mb_strlen($user) > 100) {
         bsGagal('Username PCare wajib diisi.');
      }
      if (!bsSatu($koneksi, "SELECT id FROM setting_pcare WHERE id_customer=? LIMIT 1", 'i', [$idc])) {
         bsGagal('Konfigurasi PCare faskes belum dibuat. Hubungi tim IT.', 409);
      }
      $lama = bsSatu($koneksi, "SELECT username, password FROM setting_pcare WHERE id_customer=? LIMIT 1", 'i', [$idc]);
      if ($pass !== '') {
         bsJalankan($koneksi, "UPDATE setting_pcare SET username=?, password=? WHERE id_customer=?", 'ssi', [$user, $pass, $idc]);
      } else {
         bsJalankan($koneksi, "UPDATE setting_pcare SET username=? WHERE id_customer=?", 'si', [$user, $idc]);
      }
      // catat perubahan (password tidak ikut disimpan di log, hanya penanda "diganti")
      $gantiUser = (string) $lama['username'] !== $user;
      $gantiPass = $pass !== '' && $pass !== (string) $lama['password'];
      if (($gantiUser || $gantiPass) && bsAdaLogPcare($koneksi)) {
         $oleh = mb_substr((string) ($_SESSION['fullname'] ?? $_SESSION['username'] ?? '-'), 0, 150);
         $uid = mb_substr((string) ($_SESSION['uid_user'] ?? ''), 0, 100);
         $ip = mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
         bsJalankan($koneksi, "INSERT INTO setting_pcare_log (id_customer, username_lama, username_baru, password_diganti, diubah_oleh, uid_user, ip) VALUES (?, ?, ?, ?, ?, ?, ?)",
            'ississs', [$idc, (string) $lama['username'], $user, $gantiPass ? 1 : 0, $oleh, $uid, $ip]);
      }
      bsJawab(['success' => true, 'message' => ($gantiUser || $gantiPass) ? 'Akun PCare disimpan.' : 'Tidak ada perubahan pada akun PCare.']);

   // password PCare hanya dikirim saat diminta (tombol mata), tidak ikut di data status
   case 'lihat_password':
      if ($_SERVER['REQUEST_METHOD'] !== 'POST') bsGagal('Method tidak diizinkan.', 405);
      $p = bsSatu($koneksi, "SELECT password FROM setting_pcare WHERE id_customer=? LIMIT 1", 'i', [$idc]);
      if (!$p) bsGagal('Konfigurasi PCare faskes belum dibuat.', 404);
      bsJawab(['success' => true, 'password' => (string) $p['password']]);

   // ================= DOKTER BPJS + iCARE =================
   case 'dokter':
      $dok = bsSemua($koneksi, "SELECT id, kdDokter, nmDokter, status, icare_username, icare_password FROM master_doctor_bpjs WHERE id_customer=? ORDER BY status DESC, nmDokter ASC", 'i', [$idc]);
      $jad = [];
      foreach (bsSemua($koneksi, "SELECT id_doctor, COUNT(*) n, SUM(sch_status='1') aktif FROM ms_doctor_schedule WHERE id_customer=? GROUP BY id_doctor", 'i', [$idc]) as $r) {
         $jad[$r['id_doctor']] = $r;
      }
      $map = [];
      foreach (bsSemua($koneksi, "SELECT doctor_code, doctor_name FROM ms_doctor WHERE id_customer=? AND doctor_code IS NOT NULL AND doctor_code<>'' ORDER BY doctor_name", 'i', [$idc]) as $r) {
         $map[$r['doctor_code']][] = $r['doctor_name'];
      }
      foreach ($dok as &$d) {
         $j = $jad[$d['kdDokter']] ?? null;
         $d['jadwal'] = $j ? (int) $j['n'] : 0;
         $d['jadwal_aktif'] = $j ? (int) $j['aktif'] : 0;
         $d['internal'] = array_values(array_unique($map[$d['kdDokter']] ?? []));
      }
      unset($d);
      bsJawab(['success' => true, 'data' => $dok]);

   case 'simpan_icare':
      $id = (int) ($_POST['id'] ?? 0);
      $user = trim((string) ($_POST['username'] ?? ''));
      $pass = (string) ($_POST['password'] ?? '');
      if ($id <= 0) {
         bsGagal('ID dokter tidak ditemukan.');
      }
      if ($user === '' || $pass === '') {
         bsGagal('Username dan password iCare wajib diisi.');
      }
      if (!bsSatu($koneksi, "SELECT id FROM master_doctor_bpjs WHERE id=? AND id_customer=?", 'ii', [$id, $idc])) {
         bsGagal('Dokter tidak ditemukan.', 404);
      }
      bsJalankan($koneksi, "UPDATE master_doctor_bpjs SET icare_username=?, icare_password=? WHERE id=? AND id_customer=?", 'ssii', [$user, $pass, $id, $idc]);
      bsJawab(['success' => true, 'message' => 'Akun iCare disimpan.']);

   // ================= JADWAL =================
   case 'poli':
      bsJawab(['success' => true, 'data' => bsSemua($koneksi, "SELECT kdPoli, nmPoli FROM master_poli WHERE poliSakit='1' ORDER BY nmPoli")]);

   case 'jadwal':
      $kode = trim((string) ($_GET['kode'] ?? ''));
      if ($kode === '') {
         bsGagal('Kode dokter kosong.');
      }
      $rows = bsSemua(
         $koneksi,
         "SELECT s.id_schedule AS id, s.day_of_week AS hari, s.start_time AS mulai, s.end_time AS selesai,
                 s.kuota, s.sch_status AS status, s.id_poli AS kdPoli, p.nmPoli
          FROM ms_doctor_schedule s
          LEFT JOIN master_poli p ON p.kdPoli = s.id_poli
          WHERE s.id_doctor=? AND s.id_customer=?
          ORDER BY FIELD(s.day_of_week,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), s.start_time",
         'si',
         [$kode, $idc]
      );
      bsJawab(['success' => true, 'data' => $rows]);

   case 'simpan_jadwal':
      $id = (int) ($_POST['id'] ?? 0);
      $kode = trim((string) ($_POST['kode'] ?? ''));
      $hari = (string) ($_POST['hari'] ?? '');
      $poli = trim((string) ($_POST['poli'] ?? ''));
      $mulai = trim((string) ($_POST['mulai'] ?? ''));
      $selesai = trim((string) ($_POST['selesai'] ?? ''));
      $kuota = (int) ($_POST['kuota'] ?? 0);

      if (!bsDokterBpjs($koneksi, $idc, $kode)) {
         bsGagal('Dokter BPJS tidak ditemukan.', 404);
      }
      if (!in_array($hari, BS_HARI, true)) {
         bsGagal('Hari wajib dipilih.');
      }
      if (!bsSatu($koneksi, "SELECT kdPoli FROM master_poli WHERE kdPoli=? LIMIT 1", 's', [$poli])) {
         bsGagal('Poliklinik wajib dipilih.');
      }
      $jam = '/^([01]\d|2[0-3]):[0-5]\d$/';
      if (!preg_match($jam, $mulai) || !preg_match($jam, $selesai)) {
         bsGagal('Format jam harus HH:MM (24 jam).');
      }
      if (strcmp($mulai, $selesai) >= 0) {
         bsGagal('Jam selesai harus lebih besar dari jam mulai.');
      }
      if ($kuota < 1 || $kuota > 9999) {
         bsGagal('Kuota JKN harus 1–9999.');
      }

      $bentrok = bsSatu(
         $koneksi,
         "SELECT start_time, end_time, day_of_week FROM ms_doctor_schedule
          WHERE id_customer=? AND id_doctor=? AND day_of_week=? AND id_schedule<>?
            AND start_time < ? AND end_time > ? LIMIT 1",
         'ississ',
         [$idc, $kode, $hari, $id, $selesai, $mulai]
      );
      if ($bentrok) {
         bsGagal("Jam bertabrakan dengan jadwal {$bentrok['day_of_week']} " . substr($bentrok['start_time'], 0, 5) . '–' . substr($bentrok['end_time'], 0, 5) . '.', 409);
      }

      if ($id > 0) {
         $n = bsSatu($koneksi, "SELECT id_schedule FROM ms_doctor_schedule WHERE id_schedule=? AND id_customer=? AND id_doctor=?", 'iis', [$id, $idc, $kode]);
         if (!$n) {
            bsGagal('Jadwal tidak ditemukan.', 404);
         }
         bsJalankan(
            $koneksi,
            "UPDATE ms_doctor_schedule SET day_of_week=?, start_time=?, end_time=?, id_poli=?, kuota=?, updated_at=NOW() WHERE id_schedule=? AND id_customer=?",
            'ssssiii',
            [$hari, $mulai, $selesai, $poli, $kuota, $id, $idc]
         );
         bsJawab(['success' => true, 'message' => 'Jadwal diperbarui.']);
      }
      bsJalankan(
         $koneksi,
         "INSERT INTO ms_doctor_schedule (id_doctor, day_of_week, start_time, end_time, sch_status, id_poli, kuota, id_customer) VALUES (?,?,?,?,'1',?,?,?)",
         'sssssii',
         [$kode, $hari, $mulai, $selesai, $poli, $kuota, $idc]
      );
      bsJawab(['success' => true, 'message' => 'Jadwal ditambahkan.']);

   case 'kuota':
      $id = (int) ($_POST['id'] ?? 0);
      $kuota = $_POST['kuota'] ?? '';
      if ($id <= 0 || !ctype_digit((string) $kuota) || (int) $kuota > 9999) {
         bsGagal('Kuota harus angka 0–9999.');
      }
      $kuota = (int) $kuota;
      if (!bsSatu($koneksi, "SELECT id_schedule FROM ms_doctor_schedule WHERE id_schedule=? AND id_customer=?", 'ii', [$id, $idc])) {
         bsGagal('Jadwal tidak ditemukan.', 404);
      }
      bsJalankan($koneksi, "UPDATE ms_doctor_schedule SET kuota=?, updated_at=NOW() WHERE id_schedule=? AND id_customer=?", 'iii', [$kuota, $id, $idc]);
      bsJawab(['success' => true, 'message' => 'Kuota diperbarui.']);

   case 'status_jadwal':
      $id = (int) ($_POST['id'] ?? 0);
      $s = (string) ($_POST['status'] ?? '');
      if ($id <= 0 || ($s !== '0' && $s !== '1')) {
         bsGagal('Data tidak valid.');
      }
      if (!bsSatu($koneksi, "SELECT id_schedule FROM ms_doctor_schedule WHERE id_schedule=? AND id_customer=?", 'ii', [$id, $idc])) {
         bsGagal('Jadwal tidak ditemukan.', 404);
      }
      bsJalankan($koneksi, "UPDATE ms_doctor_schedule SET sch_status=?, updated_at=NOW() WHERE id_schedule=? AND id_customer=?", 'sii', [$s, $id, $idc]);
      bsJawab(['success' => true, 'message' => $s === '1' ? 'Jadwal diaktifkan.' : 'Jadwal dinonaktifkan.']);

   case 'hapus_jadwal':
      $id = (int) ($_POST['id'] ?? 0);
      if ($id <= 0 || !bsSatu($koneksi, "SELECT id_schedule FROM ms_doctor_schedule WHERE id_schedule=? AND id_customer=?", 'ii', [$id, $idc])) {
         bsGagal('Jadwal tidak ditemukan.', 404);
      }
      bsJalankan($koneksi, "DELETE FROM ms_doctor_schedule WHERE id_schedule=? AND id_customer=?", 'ii', [$id, $idc]);
      bsJawab(['success' => true, 'message' => 'Jadwal dihapus.']);

   // ================= DOKTER INTERNAL (pemetaan ke dokter BPJS) =================
   case 'internal':
      $rows = bsSemua(
         $koneksi,
         "SELECT a.id_doctor, a.doctor_name, a.doctor_code, a.doctor_status, b.nmDokter, b.status AS bpjs_status, c.nmPoli
          FROM ms_doctor a
          LEFT JOIN master_doctor_bpjs b ON b.kdDokter = a.doctor_code AND b.id_customer = a.id_customer
          LEFT JOIN master_poli c ON c.kdPoli = a.id_poli
          WHERE a.id_customer=?
          ORDER BY a.doctor_status DESC, a.doctor_name ASC",
         'i',
         [$idc]
      );
      bsJawab(['success' => true, 'data' => $rows]);

   case 'simpan_internal':
      $id = (int) ($_POST['id'] ?? 0);
      $nama = trim((string) ($_POST['nama'] ?? ''));
      $kode = trim((string) ($_POST['kode'] ?? ''));
      if ($nama === '' || mb_strlen($nama) > 255) {
         bsGagal('Nama dokter wajib diisi.');
      }
      $bpjs = bsDokterBpjs($koneksi, $idc, $kode);
      if (!$bpjs || $bpjs['status'] != 1) {
         bsGagal('Pilih dokter BPJS yang aktif.');
      }
      $lama = null;
      if ($id > 0) {
         $lama = bsSatu($koneksi, "SELECT id_doctor, doctor_code FROM ms_doctor WHERE id_doctor=? AND id_customer=?", 'ii', [$id, $idc]);
         if (!$lama) {
            bsGagal('Dokter tidak ditemukan.', 404);
         }
      }
      // satu dokter BPJS hanya dipetakan ke satu dokter internal (dicek hanya bila kode berubah)
      if (!$lama || $lama['doctor_code'] !== $kode) {
         $pakai = bsSatu($koneksi, "SELECT doctor_name FROM ms_doctor WHERE id_customer=? AND doctor_code=? AND id_doctor<>? LIMIT 1", 'isi', [$idc, $kode, $id]);
         if ($pakai) {
            bsGagal("Dokter BPJS ini sudah dipetakan ke {$pakai['doctor_name']}.", 409);
         }
      }
      if ($lama) {
         bsJalankan($koneksi, "UPDATE ms_doctor SET doctor_name=?, doctor_code=? WHERE id_doctor=? AND id_customer=?", 'ssii', [$nama, $kode, $id, $idc]);
         bsJawab(['success' => true, 'message' => 'Dokter internal diperbarui.']);
      }
      do {
         $no = 'DCT-' . date('YmdHis') . '-' . random_int(100000, 999999);
      } while (bsSatu($koneksi, "SELECT id_doctor FROM ms_doctor WHERE doctor_number=?", 's', [$no]));
      bsJalankan($koneksi, "INSERT INTO ms_doctor (doctor_code, doctor_name, id_customer, doctor_number) VALUES (?,?,?,?)", 'ssis', [$kode, $nama, $idc, $no]);
      bsJawab(['success' => true, 'message' => 'Dokter internal ditambahkan.']);

   case 'hapus_internal':
      $id = (int) ($_POST['id'] ?? 0);
      $d = $id > 0 ? bsSatu($koneksi, "SELECT doctor_name FROM ms_doctor WHERE id_doctor=? AND id_customer=?", 'ii', [$id, $idc]) : null;
      if (!$d) {
         bsGagal('Dokter tidak ditemukan.', 404);
      }
      if (bsSatu($koneksi, "SELECT 1 AS x FROM pasien_visit WHERE id_customer=? AND id_doctor=? LIMIT 1", 'is', [$idc, $d['doctor_name']])) {
         bsGagal('Dokter ini sudah punya riwayat kunjungan pasien sehingga tidak bisa dihapus. Nonaktifkan di menu Master Dokter.', 409);
      }
      bsJalankan($koneksi, "DELETE FROM ms_doctor WHERE id_doctor=? AND id_customer=?", 'ii', [$id, $idc]);
      bsJawab(['success' => true, 'message' => 'Dokter internal dihapus.']);

   default:
      bsGagal('Aksi tidak dikenal.', 400);
}
