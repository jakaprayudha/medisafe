<?php
/**
 * Klien & sinkronisasi ke API Dinkes Deli Serdang.
 * Kunjungan < 5 tahun -> data balita (butuh isian "Data Dinkes Balita"), >= 5 tahun -> pemeriksaan pasien dewasa.
 * Data kunjungan SELALU tersimpan lebih dulu; fungsi dkKirimVisit/dkHapusVisit tidak pernah melempar exception
 * sehingga gangguan Dinkes tidak menghalangi petugas menyimpan kunjungan. Status & pesan gagal disimpan di dinkes_kirim.
 * Z-score & stunting dihitung Dinkes (dibaca balik lewat GET data-balita).
 */

const DK_BASE = 'https://dinkesds-simpus.deliserdangkab.go.id/api/klinik/v1/';

/**
 * Login per klinik (setting_clinic.dinkes_username / dinkes_client_secret) -> ['username'=>, 'token'=>]; null bila
 * kredensial kosong atau login gagal. Token (berlaku 1 jam) di-cache per proses; $segar=true memaksa login ulang.
 * ponytail: cache hanya selama request, tidak disimpan ke DB; simpan bila login per request jadi beban.
 */
function dkAuth(mysqli $k, int $idc, bool $segar = false): ?array
{
   static $cache = [];
   if ($segar) unset($cache[$idc]);
   if (isset($cache[$idc])) return $cache[$idc];
   $st = $k->prepare("SELECT dinkes_username, dinkes_client_secret FROM setting_clinic WHERE id_customer=? LIMIT 1");
   $st->bind_param('i', $idc);
   $st->execute();
   $r = $st->get_result()->fetch_row();
   $st->close();
   $u = trim((string) ($r[0] ?? ''));
   $sec = trim((string) ($r[1] ?? ''));
   if ($u === '' || $sec === '') return null;
   $ch = curl_init(DK_BASE . 'login');
   curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_CONNECTTIMEOUT => 5,
      CURLOPT_TIMEOUT => 12,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
      CURLOPT_POSTFIELDS => json_encode(['username' => $u, 'client_secret' => $sec]),
   ]);
   $res = json_decode((string) curl_exec($ch), true);
   curl_close($ch);
   $t = (string) ($res['data']['token'] ?? '');
   return $t === '' ? null : ($cache[$idc] = ['username' => $u, 'token' => $t]);
}

/** Puskesmas faskes dari setting_clinic.puskes_id. 0 = faskes ini tidak ikut integrasi Dinkes. */
function dkPuskesClinic(mysqli $k, int $idc): int
{
   $st = $k->prepare("SELECT puskes_id FROM setting_clinic WHERE id_customer=? LIMIT 1");
   $st->bind_param('i', $idc);
   $st->execute();
   $v = (int) ($st->get_result()->fetch_row()[0] ?? 0);
   $st->close();
   return $v;
}

/** [http_code, json|null, error_curl]. Timeout pendek agar simpan kunjungan tidak menggantung saat Dinkes down. 401 -> login ulang sekali. */
function dkHttp(mysqli $k, int $idc, string $method, string $path, array $query = [], ?array $body = null, bool $ulang = true): array
{
   $auth = dkAuth($k, $idc, !$ulang);
   if (!$auth) return [0, null, 'login Dinkes gagal / kredensial klinik belum diisi'];
   $url = DK_BASE . $path . ($query ? '?' . http_build_query($query) : '');
   $h = ['x-username: ' . $auth['username'], 'x-token: ' . $auth['token'], 'Accept: application/json'];
   $ch = curl_init($url);
   curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_CONNECTTIMEOUT => 5,
      CURLOPT_TIMEOUT => 12,
   ]);
   if ($body !== null) {
      $h[] = 'Content-Type: application/json';
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
   }
   curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
   $raw = curl_exec($ch);
   $err = curl_error($ch);
   $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
   curl_close($ch);
   if ($code === 401 && $ulang) return dkHttp($k, $idc, $method, $path, $query, $body, false);
   return [$code, $raw === false ? null : json_decode($raw, true), $err];
}

function dkLog(mysqli $k, int $idc, ?string $visit, string $aksi, int $code, bool $ok, string $msg): void
{
   $status = $ok ? 'ok' : 'gagal';
   $msg = mb_substr($msg, 0, 500);
   $st = $k->prepare("INSERT INTO dinkes_log (id_customer, visit_ID, aksi, http_status, status, message) VALUES (?,?,?,?,?,?)");
   $st->bind_param('ississ', $idc, $visit, $aksi, $code, $status, $msg);
   $st->execute();
   $st->close();
}

/** Pesan ringkas dari respon gagal Dinkes. */
function dkPesan(int $code, ?array $res, string $err): string
{
   if ($err !== '') return 'Tidak dapat terhubung ke Dinkes: ' . $err;
   $m = (string) ($res['message'] ?? ('HTTP ' . $code));
   if (!empty($res['errors'])) $m .= ' ' . mb_substr(json_encode($res['errors'], JSON_UNESCAPED_UNICODE), 0, 300);
   return $m;
}

function dkOk(int $code, ?array $res): bool
{
   return $code >= 200 && $code < 300 && ($res['status'] ?? '') === 'success';
}

/** Umur dalam tahun penuh pada tanggal kunjungan; null bila tanggal lahir tidak valid. */
function dkUmurTahun(?string $lahir, ?string $kunjungan): ?int
{
   if (!$lahir || !$kunjungan || $lahir === '0000-00-00') return null;
   try {
      return (int) (new DateTime($lahir))->diff(new DateTime($kunjungan))->y;
   } catch (Throwable $e) {
      return null;
   }
}

/** Kunjungan + pasien + vital, dibatasi id_customer. */
function dkKonteks(mysqli $k, int $idc, string $visitID): ?array
{
   $st = $k->prepare("SELECT pv.id_visit, pv.visit_ID, pv.visit_date, pv.diagnosa AS dx_visit,
         mp.id_patient, mp.patient_nik, mp.patient_kk, mp.patient_name, mp.patient_gender, mp.patient_datebirth, mp.patient_place,
         mp.patient_address, mp.patient_provinsi, mp.patient_kabupaten, mp.patient_kecamatan, mp.patient_kelurahan,
         pv.diagnosa AS dx_periksa, pv.anamnesa, pv.tekanan_darah, pv.suhu, pv.nadi, pv.respirasi, pv.tinggi_badan AS tinggi, pv.berat_badan AS berat, pv.bmi, NULL AS bmi_ket
      FROM pasien_visit pv
      INNER JOIN ms_patient mp ON mp.id_patient = pv.id_patient
      WHERE pv.visit_ID = ? AND pv.id_customer = ? LIMIT 1");
   $st->bind_param('si', $visitID, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$r) return null;
   // kode ICD10 bisa tersimpan sebagai "A09" atau "A09 - Diarrhoea ..."
   $dx = '';
   foreach ([$r['dx_periksa'], $r['dx_visit']] as $v) {
      if (preg_match('/\b([A-Z]\d{2}(?:\.\d{1,2})?)\b/', strtoupper((string) $v), $m)) {
         $dx = $m[1];
         break;
      }
   }
   $r['icd'] = $dx;
   $r['umur'] = dkUmurTahun($r['patient_datebirth'], $r['visit_date']);
   $r['jk'] = strtoupper(substr(trim((string) $r['patient_gender']), 0, 1)) === 'P' ? 'P' : 'L';
   return $r;
}

function dkBaris(mysqli $k, int $idc, string $visitID): ?array
{
   $st = $k->prepare("SELECT * FROM dinkes_kirim WHERE id_customer=? AND visit_ID=? LIMIT 1");
   $st->bind_param('is', $idc, $visitID);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

/** Simpan/ubah isian form balita (JSON) tanpa menyentuh status kirim. */
function dkSimpanBalita(mysqli $k, int $idc, string $visitID, array $data, ?int $puskes): void
{
   $json = json_encode($data, JSON_UNESCAPED_UNICODE);
   $st = $k->prepare("INSERT INTO dinkes_kirim (id_customer, visit_ID, jenis, data, puskes_id) VALUES (?,?, 'balita', ?, ?)
      ON DUPLICATE KEY UPDATE jenis='balita', data=VALUES(data), puskes_id=VALUES(puskes_id)");
   $st->bind_param('issi', $idc, $visitID, $json, $puskes);
   $st->execute();
   $st->close();
}

function dkSet(mysqli $k, int $id, string $status, ?int $dkId, string $err, bool $terkirim): void
{
   $st = $k->prepare("UPDATE dinkes_kirim SET dk_status=?, dk_id=COALESCE(?, dk_id), dk_error=?, dk_attempts=dk_attempts+1, dk_last_try=NOW(),
      dk_sent_at=IF(?=1, NOW(), dk_sent_at) WHERE id=?");
   $t = $terkirim ? 1 : 0;
   $st->bind_param('sisii', $status, $dkId, $err, $t, $id);
   $st->execute();
   $st->close();
}

// ---------------------------------------------------------------- mapper (satu tempat bila Dinkes mengubah nama kunci)

function dkMapDewasa(array $c, int $puskes): array
{
   $n = fn($v) => ($v === null || $v === '') ? null : (is_numeric($v) ? $v + 0 : $v);
   return [
      'no_periksa' => $c['visit_ID'],
      'tanggal_periksa' => $c['visit_date'],
      'nik' => preg_replace('/\D/', '', (string) $c['patient_nik']),
      'nama' => $c['patient_name'],
      'tanggal_lahir' => $c['patient_datebirth'],
      'tempat_lahir' => $c['patient_place'],
      'jenis_kelamin' => $c['jk'],
      'detail_data' => array_filter([
         'tekanan_darah' => $c['tekanan_darah'],
         'suhu' => $n($c['suhu']),
         'nadi' => $n($c['nadi']),
         'respirasi' => $n($c['respirasi']),
         'tinggi' => $n($c['tinggi']),
         'berat' => $n($c['berat']),
         'bmi' => $n($c['bmi']),
         'bmi_ket' => $c['bmi_ket'],
         'anamnesa' => $c['anamnesa'],
      ], fn($v) => $v !== null && $v !== ''),
      'diagnosa' => $c['icd'],
      'alamat' => $c['patient_address'],
      'provinsi' => $c['patient_provinsi'],
      'kabupaten' => $c['patient_kabupaten'],
      'kecamatan' => $c['patient_kecamatan'],
      'desa' => $c['patient_kelurahan'],
      'puskes' => $puskes,
   ];
}

/** Bagian yang sama untuk tambah & update balita, dari isian form (array) + kunjungan. */
function dkBalitaDasar(array $c, array $f): array
{
   $n = fn($v) => ($v === null || $v === '') ? null : (is_numeric($v) ? $v + 0 : $v);
   $b = fn($v) => $v === true || $v === 1 || $v === '1' || $v === 'true' || $v === 'Ya';
   return [
      'identitas' => [
         'nama_balita' => $c['patient_name'],
         'nama_orang_tua' => $f['nama_orang_tua'] ?? '',
         'nomor_kk' => $f['nomor_kk'] ?? ($c['patient_kk'] ?? ''),
         'alamat_lengkap' => $c['patient_address'],
         'desa' => $c['patient_kelurahan'],
         'kecamatan' => $c['patient_kecamatan'],
      ],
      'ukur' => [
         'bb_lahir' => $n($f['bb_lahir'] ?? null),
         'tb_lahir' => $n($f['tb_lahir'] ?? null),
         'lila_cm' => $n($f['lila_cm'] ?? null),
         'lingkar_kepala_cm' => $n($f['lingkar_kepala_cm'] ?? null),
      ],
      'riwayat' => [
         'diagnosa' => $f['diagnosa'] ?? '',
         'data_kesehatan_dan_riwayat_sakit' => [
            'riwayat_diare_1_bulan_terakhir' => $b($f['riwayat_diare'] ?? false),
            'riwayat_ispa' => $b($f['riwayat_ispa'] ?? false),
            'status_imunisasi_dasar_lengkap' => $b($f['imunisasi_lengkap'] ?? false),
            'riwayat_sakit_kronis' => ($f['riwayat_sakit_kronis'] ?? '') !== '' ? $f['riwayat_sakit_kronis'] : '-',
         ],
         'data_pola_asuh_dan_konsumsi' => [
            'asi_eksklusif_0_6_bulan' => $b($f['asi_eksklusif'] ?? false),
            'usia_mulai_mpasi_bulan' => $n($f['usia_mpasi'] ?? null),
            'pemberian_tablet_tambah_darah_jumlah' => $n($f['ttd_jumlah'] ?? 0) ?? 0,
         ],
         'data_ibu_dan_kehamilan' => [
            'status_bumil' => $b($f['status_bumil'] ?? false),
            'usia_saat_hamil' => $n($f['usia_saat_hamil'] ?? null),
            'riwayat_anemia' => $b($f['riwayat_anemia'] ?? false),
         ],
      ],
   ];
}

function dkMapBalitaTambah(array $c, array $f, int $puskes): array
{
   $d = dkBalitaDasar($c, $f);
   return [
      'nik' => preg_replace('/\D/', '', (string) $c['patient_nik']),
      'tanggal_lahir' => $c['patient_datebirth'],
      'jenis_kelamin' => $c['jk'],
      'tanggal_ukur' => $c['visit_date'],
      'bb_ukur' => (float) $c['berat'],
      'tb_ukur' => (float) $c['tinggi'],
      'detail_data' => ['identitas' => $d['identitas'], 'data_ukur' => $d['ukur'], 'riwayat' => $d['riwayat']],
      'puskes' => $puskes,
   ];
}

/** Update balita = bentuk tambah + id; z-score dihitung Dinkes. */
function dkMapBalitaUpdate(array $c, array $f, int $puskes, int $remoteId): array
{
   return ['id' => $remoteId] + dkMapBalitaTambah($c, $f, $puskes);
}

// ---------------------------------------------------------------- aksi

/** Baris balita pada tanggal ukur tertentu (GET balita mengembalikan semua pengukuran per NIK). */
function dkCariUkur(array $data, string $tanggal): ?array
{
   foreach ($data as $r) {
      if (substr((string) ($r['tanggal_ukur'] ?? ''), 0, 10) === substr($tanggal, 0, 10)) return $r;
   }
   return null;
}

/** Baca balik data balita dari Dinkes (berisi detail_zscore) dan simpan di dk_respon. Best-effort. */
function dkAmbilRespon(mysqli $k, int $idc, array $c, int $rowId): void
{
   [$code, $res] = dkHttp($k, $idc, 'GET', 'balita', ['nik' => preg_replace('/\D/', '', (string) $c['patient_nik'])]);
   $row = dkOk($code, $res) ? dkCariUkur($res['data'] ?? [], (string) $c['visit_date']) : null;
   if (!$row) return;
   $json = json_encode($row, JSON_UNESCAPED_UNICODE);
   $st = $k->prepare("UPDATE dinkes_kirim SET dk_respon=? WHERE id=?");
   $st->bind_param('si', $json, $rowId);
   $st->execute();
   $st->close();
}

/**
 * Kirim (tambah, atau update bila sudah punya dk_id) satu kunjungan. Tidak pernah melempar exception.
 * @return array{status:string, message:string}
 */
function dkKirimVisit(mysqli $k, int $idc, string $visitID): array
{
   try {
      $puskesDefault = dkPuskesClinic($k, $idc);
      if ($puskesDefault <= 0 || dkAuth($k, $idc) === null) {
         return ['status' => 'nonaktif', 'message' => 'Integrasi Dinkes tidak aktif.'];
      }
      $c = dkKonteks($k, $idc, $visitID);
      if (!$c) return ['status' => 'gagal', 'message' => 'Kunjungan tidak ditemukan.'];

      $umur = $c['umur'];
      $jenis = ($umur !== null && $umur < 5) ? 'balita' : 'dewasa';
      $baris = dkBaris($k, $idc, $visitID);
      if (!$baris) {
         $st = $k->prepare("INSERT INTO dinkes_kirim (id_customer, visit_ID, jenis) VALUES (?,?,?)");
         $st->bind_param('iss', $idc, $visitID, $jenis);
         $st->execute();
         $st->close();
         $baris = dkBaris($k, $idc, $visitID);
      }
      $rid = (int) $baris['id'];
      $remote = $baris['dk_id'] !== null ? (int) $baris['dk_id'] : null;

      $lewati = function (string $alasan, string $status = 'dilewati') use ($k, $rid): array {
         dkSet($k, $rid, $status, null, $alasan, false);
         return ['status' => $status, 'message' => $alasan];
      };
      if (!preg_match('/^\d{16}$/', (string) preg_replace('/\D/', '', (string) $c['patient_nik']))) {
         return $lewati('NIK pasien harus 16 digit.');
      }
      if ($umur === null) return $lewati('Tanggal lahir pasien tidak valid.');
      if ($jenis === 'dewasa' && $c['icd'] === '') return $lewati('Diagnosa (kode ICD10) belum diisi.');
      if ($jenis === 'balita' && ((float) $c['berat'] <= 0 || (float) $c['tinggi'] <= 0)) {
         return $lewati('Berat/tinggi badan belum diisi.', 'belum');
      }
      $puskes = (int) ($baris['puskes_id'] ?: $puskesDefault);
      if ($puskes <= 0) return $lewati('Puskesmas faskes belum diatur (setting_clinic.puskes_id).', 'belum');

      if ($jenis === 'dewasa') {
         $body = dkMapDewasa($c, $puskes);
         $path = $remote ? 'dewasa/update' : 'dewasa/insert';
         if ($remote) $body = ['id' => $remote] + $body;
         $aksi = $remote ? 'update' : 'tambah';
         [$code, $res, $err] = dkHttp($k, $idc, $remote ? 'PUT' : 'POST', $path, [], $body);
      } else {
         $form = $baris['data'] ? (json_decode($baris['data'], true) ?: []) : [];
         if (!$form) return $lewati('Menunggu pengisian Data Dinkes Balita.', 'belum');
         $aksi = $remote ? 'update' : 'tambah';
         if ($remote) {
            [$code, $res, $err] = dkHttp($k, $idc, 'PUT', 'balita/update', [], dkMapBalitaUpdate($c, $form, $puskes, $remote));
         } else {
            [$code, $res, $err] = dkHttp($k, $idc, 'POST', 'balita/insert', [], dkMapBalitaTambah($c, $form, $puskes));
         }
      }

      $ok = dkOk($code, $res);
      $pesan = $ok ? (string) ($res['message'] ?? 'OK') : dkPesan($code, $res, (string) $err);
      dkLog($k, $idc, $visitID, $aksi, $code, $ok, $pesan);
      if (!$ok) {
         dkSet($k, $rid, 'gagal', null, $pesan, false);
         return ['status' => 'gagal', 'message' => $pesan];
      }
      $newId = isset($res['data']['id_insert']) ? (int) $res['data']['id_insert'] : null;
      dkSet($k, $rid, 'terkirim', $newId, '', true);
      if ($jenis === 'balita') dkAmbilRespon($k, $idc, $c, $rid);
      return ['status' => 'terkirim', 'message' => $pesan];
   } catch (Throwable $e) {
      error_log('[dinkesService] ' . $e->getMessage());
      return ['status' => 'gagal', 'message' => 'Kesalahan internal saat mengirim ke Dinkes.'];
   }
}

/** Hapus data di Dinkes (bila pernah terkirim). Dipanggil sebelum kunjungan dihapus; tidak pernah melempar exception. */
function dkHapusVisit(mysqli $k, int $idc, string $visitID): array
{
   try {
      $baris = dkBaris($k, $idc, $visitID);
      if (!$baris || $baris['dk_id'] === null || in_array($baris['dk_status'], ['dihapus'], true)) {
         return ['status' => 'tidak_ada', 'message' => 'Tidak ada data di Dinkes.'];
      }
      if (dkAuth($k, $idc) === null) {
         return ['status' => 'nonaktif', 'message' => 'Kredensial Dinkes klinik belum diisi / login gagal.'];
      }
      $path = ($baris['jenis'] === 'balita' ? 'balita/delete/' : 'dewasa/delete/') . (int) $baris['dk_id'];
      [$code, $res, $err] = dkHttp($k, $idc, 'DELETE', $path);
      $ok = dkOk($code, $res);
      $pesan = $ok ? (string) ($res['message'] ?? 'OK') : dkPesan($code, $res, (string) $err);
      dkLog($k, $idc, $visitID, 'hapus', $code, $ok, $pesan);
      dkSet($k, (int) $baris['id'], $ok ? 'dihapus' : 'hapus_gagal', null, $ok ? '' : $pesan, false);
      return ['status' => $ok ? 'dihapus' : 'hapus_gagal', 'message' => $pesan];
   } catch (Throwable $e) {
      error_log('[dinkesService] ' . $e->getMessage());
      return ['status' => 'hapus_gagal', 'message' => 'Kesalahan internal saat menghapus di Dinkes.'];
   }
}

/**
 * Cek ke API Dinkes (GET) apakah kunjungan sudah ada di sana. Hanya membaca; tidak mengubah dk_status.
 * @return array{status:string, message:string}  status: ada | tidak_ada | error
 */
function dkCekRemote(mysqli $k, int $idc, string $visitID): array
{
   try {
      if (dkAuth($k, $idc) === null) return ['status' => 'error', 'message' => 'Kredensial Dinkes klinik belum diisi / login gagal.'];
      $c = dkKonteks($k, $idc, $visitID);
      if (!$c) return ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan.'];
      $nik = preg_replace('/\D/', '', (string) $c['patient_nik']);
      $balita = $c['umur'] !== null && $c['umur'] < 5;
      [$code, $res, $err] = $balita
         ? dkHttp($k, $idc, 'GET', 'balita', ['nik' => $nik])
         : dkHttp($k, $idc, 'GET', 'dewasa', ['tanggal_periksa' => $c['visit_date'], 'no_periksa' => $visitID, 'nik' => $nik]);
      $ok = dkOk($code, $res);
      $pesan = $ok ? (string) ($res['message'] ?? 'OK') : dkPesan($code, $res, (string) $err);
      dkLog($k, $idc, $visitID, 'get', $code, $ok, $pesan);
      if (!$ok) return ['status' => 'error', 'message' => $pesan];
      $ada = $balita ? dkCariUkur($res['data'] ?? [], (string) $c['visit_date']) !== null : !empty($res['data']);
      return !$ada
         ? ['status' => 'tidak_ada', 'message' => 'Belum ada di Dinkes.']
         : ['status' => 'ada', 'message' => 'Sudah terkirim ke Dinkes.'];
   } catch (Throwable $e) {
      error_log('[dinkesService] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Kesalahan internal saat cek ke Dinkes.'];
   }
}

/**
 * Kirim kunjungan lama (sudah diperiksa, tapi belum punya baris dinkes_kirim) pada rentang tanggal, maks $limit per panggilan.
 * Baris dinkes_kirim dibuat oleh dkKirimVisit, jadi panggilan berikutnya otomatis maju. Ulangi sampai sisa = 0.
 * @return array{diproses:int, terkirim:int, sisa:int, message:string}
 */
function dkKirimLama(mysqli $k, int $idc, string $dari, string $sampai, int $limit = 20): array
{
   try {
      if (dkPuskesClinic($k, $idc) <= 0 || dkAuth($k, $idc) === null) {
         return ['diproses' => 0, 'terkirim' => 0, 'sisa' => 0, 'message' => 'Integrasi Dinkes tidak aktif.'];
      }
      $from = "FROM pasien_visit pv
         WHERE pv.tekanan_darah IS NOT NULL AND pv.tekanan_darah <> '' AND pv.id_customer=? AND DATE(pv.visit_date) BETWEEN ? AND ?
         AND NOT EXISTS (SELECT 1 FROM dinkes_kirim d WHERE d.id_customer=pv.id_customer AND d.visit_ID=pv.visit_ID)";
      $st = $k->prepare("SELECT DISTINCT pv.visit_ID $from ORDER BY pv.visit_date LIMIT ?");
      $st->bind_param('issi', $idc, $dari, $sampai, $limit);
      $st->execute();
      $ids = array_column($st->get_result()->fetch_all(MYSQLI_NUM), 0);
      $st->close();
      $terkirim = 0;
      foreach ($ids as $v) {
         if (dkKirimVisit($k, $idc, (string) $v)['status'] === 'terkirim') $terkirim++;
      }
      $st = $k->prepare("SELECT COUNT(DISTINCT pv.visit_ID) $from");
      $st->bind_param('iss', $idc, $dari, $sampai);
      $st->execute();
      $sisa = (int) $st->get_result()->fetch_row()[0];
      $st->close();
      return ['diproses' => count($ids), 'terkirim' => $terkirim, 'sisa' => $sisa, 'message' => ''];
   } catch (Throwable $e) {
      error_log('[dinkesService] ' . $e->getMessage());
      return ['diproses' => 0, 'terkirim' => 0, 'sisa' => 0, 'message' => 'Kesalahan internal saat mengirim kunjungan lama.'];
   }
}

/**
 * visit_ID untuk id_visit (PK pasien_visit) bila kunjungan itu pernah dikirim ke Dinkes, selain itu null.
 * Dipanggil SEBELUM DELETE pasien_visit; setelah DELETE sukses panggil dkHapusVisit() dengan hasilnya.
 */
function dkVisitIdDikirim(mysqli $k, int $idc, $idVisit): ?string
{
   try {
      $iv = (int) $idVisit;
      $st = $k->prepare("SELECT d.visit_ID FROM dinkes_kirim d INNER JOIN pasien_visit pv ON pv.visit_ID=d.visit_ID AND pv.id_customer=d.id_customer
         WHERE pv.id_visit=? AND d.id_customer=? AND d.dk_id IS NOT NULL LIMIT 1");
      $st->bind_param('ii', $iv, $idc);
      $st->execute();
      $v = $st->get_result()->fetch_row();
      $st->close();
      return $v ? (string) $v[0] : null;
   } catch (Throwable $e) { // tabel dinkes belum dimigrasi dsb.: jangan ganggu penghapusan kunjungan
      error_log('[dinkesService] ' . $e->getMessage());
      return null;
   }
}
