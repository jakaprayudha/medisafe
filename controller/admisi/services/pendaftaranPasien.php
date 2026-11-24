<?php
/**
 * Data pasien untuk halaman module/admisi/pendaftaran.
 *
 * GET ?aksi=cari&q=...      cari pasien faskes: nama / RM / NIK / no. BPJS (15 hasil, terakhir berkunjung)
 * GET ?aksi=detail&id=...   data lokal pasien (cepat, tanpa BPJS) + kunjungan aktif hari ini (cegah daftar dobel)
 * GET ?aksi=bpjs&id=...     cek kepesertaan BPJS (PCare /peserta) berdasarkan no. kartu / NIK pasien
 *
 * Dulu: memilih pasien selalu menunggu API BPJS (s.d. 10 dtk) memakai NIK/BPJS hasil pencarian -> pasien tanpa NIK &
 * BPJS (nomor "-") tidak bisa didaftarkan, dan fallback "Daftar Umum" mewajibkan NIK 16 digit. Sekarang memakai
 * id_patient; data lokal tampil langsung, cek BPJS berjalan terpisah.
 */
require_once __DIR__ . '/view.php';
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');

$idc = (int) ($idcustomer ?? 0);
session_write_close();
function ppJawab(array $d, int $kode = 200): void
{
   http_response_code($kode);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($idc <= 0) ppJawab(['success' => false, 'message' => 'Session tidak valid'], 401);

function ppQ(mysqli $db, string $sql, string $t, array $p): array
{
   $st = $db->prepare($sql);
   $st->bind_param($t, ...$p);
   $st->execute();
   $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   return $r;
}
function ppUmur(?string $lahir): ?int
{
   if (!$lahir || !preg_match('/^\d{4}-\d{2}-\d{2}/', $lahir) || substr($lahir, 0, 4) < '1900') return null;
   return (new DateTime(substr($lahir, 0, 10)))->diff(new DateTime('today'))->y;
}
function ppJk($v): string
{
   $v = strtoupper(substr(trim((string) $v), 0, 1));
   return $v === 'L' ? 'Laki-laki' : ($v === 'P' ? 'Perempuan' : '');
}
function ppNomor($v, int $panjang): string
{
   $v = trim((string) $v);
   return (preg_match('/^\d{' . $panjang . '}$/', $v) && !preg_match('/^(\d)\1+$/', $v)) ? $v : '';
}
function ppPasien(mysqli $db, int $idc, int $id): ?array
{
   return ppQ($db, "SELECT id_patient, nomor_rm, patient_name, patient_nik, patient_bpjs, patient_datebirth, patient_gender, patient_phone,
         patient_address, Sprolanis, SPRB FROM ms_patient WHERE id_patient = ? AND id_customer = ? LIMIT 1", 'ii', [$id, $idc])[0] ?? null;
}

$aksi = (string) ($_GET['aksi'] ?? '');

if ($aksi === 'cari') {
   $q = trim((string) ($_GET['q'] ?? ''));
   if (mb_strlen($q) < 2) ppJawab(['success' => true, 'data' => []]);
   if (preg_match('/^\d{4,}$/', $q)) {
      // angka: RM tepat / awalan NIK, BPJS, RM (index NIK & BPJS terpakai)
      $rows = ppQ($koneksi, "SELECT id_patient, nomor_rm, patient_name, patient_nik, patient_bpjs, patient_datebirth, patient_gender
         FROM ms_patient WHERE id_customer = ? AND (nomor_rm = ? OR patient_nik LIKE CONCAT(?, '%') OR patient_bpjs LIKE CONCAT(?, '%') OR nomor_rm LIKE CONCAT(?, '%'))
         ORDER BY (nomor_rm = ?) DESC, (patient_nik = ? OR patient_bpjs = ?) DESC, patient_name LIMIT 15", 'isssssss', [$idc, $q, $q, $q, $q, $q, $q, $q]);
   } else {
      $like = '%' . addcslashes($q, '%_\\') . '%';
      $awal = addcslashes($q, '%_\\') . '%';
      $rows = ppQ($koneksi, "SELECT id_patient, nomor_rm, patient_name, patient_nik, patient_bpjs, patient_datebirth, patient_gender
         FROM ms_patient WHERE id_customer = ? AND (patient_name LIKE ? OR nomor_rm = ?)
         ORDER BY (nomor_rm = ?) DESC, (patient_name LIKE ?) DESC, patient_name LIMIT 15", 'issss', [$idc, $like, $q, $q, $awal]);
   }
   // kunjungan terakhir (pasien_visit.id_patient VARCHAR -> bind string agar index terpakai)
   $akhir = [];
   if ($rows) {
      $ids = array_map(fn($r) => (string) $r['id_patient'], $rows);
      foreach (ppQ($koneksi, "SELECT id_patient, MAX(visit_date) t FROM pasien_visit WHERE id_patient IN (" . implode(',', array_fill(0, count($ids), '?')) . ")
         AND id_customer = ? AND visit_status <> 99 GROUP BY id_patient", str_repeat('s', count($ids)) . 'i', array_merge($ids, [$idc])) as $r) $akhir[$r['id_patient']] = $r['t'];
   }
   ppJawab(['success' => true, 'data' => array_map(fn($r) => [
      'id' => (int) $r['id_patient'],
      'nama' => (string) $r['patient_name'],
      'rm' => (string) $r['nomor_rm'],
      'nik' => (string) $r['patient_nik'],
      'bpjs' => (string) $r['patient_bpjs'],
      'lahir' => (string) $r['patient_datebirth'],
      'umur' => ppUmur($r['patient_datebirth']),
      'jk' => ppJk($r['patient_gender']),
      'terakhir' => $akhir[(string) $r['id_patient']] ?? null,
   ], $rows)]);
}

$id = (int) ($_GET['id'] ?? 0);
$p = $id > 0 ? ppPasien($koneksi, $idc, $id) : null;
if (!$p) ppJawab(['success' => false, 'message' => 'Pasien tidak ditemukan di faskes ini.'], 404);

if ($aksi === 'detail') {
   $hariIni = date('Y-m-d');
   $aktif = ppQ($koneksi, "SELECT visit_ID, id_poli, id_doctor, visit_antrian, visit_status, visit_time FROM pasien_visit
      WHERE id_patient = ? AND id_customer = ? AND visit_date = ? AND visit_status <> 99 ORDER BY id_visit", 'sis', [(string) $id, $idc, $hariIni]);
   ppJawab(['success' => true, 'data' => [
      'id_patient' => (int) $p['id_patient'],
      'nama' => (string) $p['patient_name'],
      'rm' => (string) $p['nomor_rm'],
      'nik' => ppNomor($p['patient_nik'], 16),
      'nik_mentah' => (string) $p['patient_nik'],
      'bpjs' => ppNomor($p['patient_bpjs'], 13),
      'bpjs_mentah' => (string) $p['patient_bpjs'],
      'lahir' => (string) $p['patient_datebirth'],
      'umur' => ppUmur($p['patient_datebirth']),
      'jk' => ppJk($p['patient_gender']),
      'hp' => (string) $p['patient_phone'],
      'alamat' => (string) $p['patient_address'],
      'prolanis' => (string) $p['Sprolanis'],
      'prb' => (string) $p['SPRB'],
      'kunjungan_hari_ini' => $aktif,
   ]]);
}

if ($aksi === 'bpjs') {
   require_once __DIR__ . '/../../../vendor/autoload.php';
   require_once __DIR__ . '/servicebpjs.php';
   $noka = ppNomor($p['patient_bpjs'], 13);
   $nik = ppNomor($p['patient_nik'], 16);
   if ($noka === '' && $nik === '') ppJawab(['success' => false, 'tanpa_nomor' => true, 'message' => 'Pasien tidak memiliki no. kartu BPJS (13 digit) atau NIK (16 digit) yang valid.']);
   $r = bpjsGet('/peserta/' . ($noka !== '' ? 'noka/' . $noka : 'nik/' . $nik));
   $d = $r['data'] ?? null;
   if (($r['code'] ?? '') != '200' || !is_array($d)) {
      ppJawab(['success' => false, 'message' => $r['message'] ?? ($d['ketAktif'] ?? 'Layanan BPJS sedang tidak dapat diakses.')]);
   }
   $aktif = ($d['aktif'] ?? '') === true || ($d['aktif'] ?? '') === 'true';
   $kdProv = trim((string) ($d['kdProviderPst']['kdProvider'] ?? ''));
   $nmProv = trim((string) ($d['kdProviderPst']['nmProvider'] ?? ''));
   $ppkSaya = trim((string) ($kodeppk ?? ''));
   // FKTP peserta dibanding faskes login (setting_pcare.KodePPK). Dulu bila KodePPK kosong semua pasien dianggap "FKTP lain".
   $fktpLain = $aktif && $kdProv !== '' && $ppkSaya !== '' && strcasecmp($kdProv, $ppkSaya) !== 0;
   $peringatan = '';
   if ($aktif && $kdProv === '') $peringatan = 'Pasien belum memiliki FKTP terdaftar.';
   elseif ($fktpLain) $peringatan = 'Pasien terdaftar di FKTP lain: ' . ($nmProv !== '' ? $nmProv : $kdProv);
   $faskes = ppQ($koneksi, "SELECT clinic_name FROM setting_clinic WHERE id_customer = ? LIMIT 1", 'i', [$idc])[0]['clinic_name'] ?? '';
   ppJawab(['success' => true, 'aktif' => $aktif, 'message' => $aktif ? ($d['ketAktif'] ?? 'AKTIF') : ($d['ketAktif'] ?? 'Peserta tidak aktif'),
      'peringatan' => $peringatan,
      'fktp_lain' => $fktpLain,
      'faskes_login' => ['kode' => $ppkSaya, 'nama' => (string) $faskes],
      'data' => [
         'noKartu' => (string) ($d['noKartu'] ?? $noka),
         'nama' => (string) ($d['nama'] ?? ''),
         'kdProvider' => $kdProv !== '' ? $kdProv : $ppkSaya,
         'nmProvider' => $nmProv,
         'jnsPeserta' => (string) ($d['jnsPeserta']['nama'] ?? ''),
         'kelas' => (string) ($d['jnsKelas']['nama'] ?? ''),
         'hubunganKeluarga' => (string) ($d['hubunganKeluarga'] ?? ''),
         'tunggakan' => $d['tunggakan'] ?? 0,
         'prolanis' => (string) ($d['pstProl'] ?? ''),
         'prb' => (string) ($d['pstPrb'] ?? ''),
         'noHP' => (string) ($d['noHP'] ?? ''),
      ]]);
}

ppJawab(['success' => false, 'message' => 'Aksi tidak dikenal.'], 400);
