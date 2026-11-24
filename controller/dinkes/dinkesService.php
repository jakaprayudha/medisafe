<?php
/**
 * Klien & sinkronisasi ke API Dinkes Deli Serdang.
 * Kunjungan < 5 tahun -> data balita (butuh isian "Data Dinkes Balita"), >= 5 tahun -> pemeriksaan pasien dewasa.
 * Data kunjungan SELALU tersimpan lebih dulu; fungsi dkKirimVisit/dkHapusVisit tidak pernah melempar exception
 * sehingga gangguan Dinkes tidak menghalangi petugas menyimpan kunjungan. Status & pesan gagal disimpan di dinkes_kirim.
 * Z-score & stunting dihitung Dinkes (dibaca balik lewat GET data-balita).
 */

const DK_BASE = 'https://api-dss.deliserdangkab.go.id/api/gateway/dinas-kesehatan/';

/** Nilai dari .env (getenv, lalu muat .env bila belum); '' bila tidak ada. */
function dkEnv(string $key): string
{
   static $dimuat = false;
   $v = (string) getenv($key);
   if ($v === '' && !$dimuat) {
      $dimuat = true;
      try {
         require_once __DIR__ . '/../../utility/env.php';
         loadEnv();
      } catch (Throwable $e) { // .env tidak ada
      }
      $v = (string) getenv($key);
   }
   return trim($v, " \t\n\r\0\x0B\"'");
}

/** Token Authorization Dinkes: satu untuk semua faskes, DINKES_TOKEN di .env. */
function dkToken(): string
{
   return dkEnv('DINKES_TOKEN');
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

/** [http_code, json|null, error_curl]. Timeout pendek agar simpan kunjungan tidak menggantung saat Dinkes down. */
function dkHttp(string $token, string $method, string $path, array $query = [], ?array $body = null): array
{
   $token = trim($token);
   if (stripos($token, 'bearer ') !== 0) {
      $token = 'Bearer ' . $token; // ponytail: asumsi bearer standar; bila Dinkes minta token mentah, hapus baris ini
   }
   $url = DK_BASE . $path . ($query ? '?' . http_build_query($query) : '');
   $h = ['Authorization: ' . $token, 'Accept: application/json'];
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
         vp.diagnosa AS dx_periksa, vp.anamnesa, vp.tekanan_darah, vp.suhu, vp.nadi, vp.respirasi, vp.tinggi, vp.berat, vp.bmi, vp.bmi_ket
      FROM pasien_visit pv
      INNER JOIN ms_patient mp ON mp.id_patient = pv.id_patient
      LEFT JOIN visit_pemeriksaan vp ON vp.nomor_visit = pv.visit_ID
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

/** Bentuk update-balita berbeda dari tambah (lihat collection); z-score diambil dari GET terakhir. */
function dkMapBalitaUpdate(array $c, array $f, int $puskes, int $remoteId, ?array $respon): array
{
   $d = dkBalitaDasar($c, $f);
   $z = $respon['detail_zscore'] ?? [];
   $u = $d['ukur'];
   return [
      'id' => $remoteId,
      'nik' => preg_replace('/\D/', '', (string) $c['patient_nik']),
      'identitas' => ['jenis_kelamin' => $c['jk'], 'tanggal_lahir' => $c['patient_datebirth']] + $d['identitas'],
      'tanggal_ukur' => $c['visit_date'],
      'data_ukur' => [
         'bb_lahir_kg' => $u['bb_lahir'],
         'tb_lahir_cm' => $u['tb_lahir'],
         'lila_cm' => $u['lila_cm'],
         'lingkar_kepala_cm' => $u['lingkar_kepala_cm'],
         'bb_terupdate_kg' => (float) $c['berat'],
         'tb_terupdate_cm' => (float) $c['tinggi'],
         'z_score_bb_u' => $z['bb_u']['zscore'] ?? null,
         'interpretasi_bb_u' => $z['bb_u']['interpretasi'] ?? null,
         'z_score_tb_u' => $z['tb_u']['zscore'] ?? null,
         'interpretasi_tb_u' => $z['tb_u']['interpretasi'] ?? null,
         'z_score_bb_tb' => $z['bb_tb']['zscore'] ?? null,
         'interpretasi_bb_tb' => $z['bb_tb']['interpretasi'] ?? null,
      ],
      'riwayat' => $d['riwayat'],
      'puskes' => $puskes,
   ];
}

// ---------------------------------------------------------------- aksi

/** Baca balik data balita dari Dinkes (berisi detail_zscore) dan simpan di dk_respon. Best-effort. */
function dkAmbilRespon(mysqli $k, int $idc, string $token, array $c, int $rowId): void
{
   [$code, $res] = dkHttp($token, 'GET', 'data-balita', [
      'nik' => preg_replace('/\D/', '', (string) $c['patient_nik']),
      'tanggal_ukur' => $c['visit_date'],
   ]);
   if (!dkOk($code, $res) || empty($res['data'][0])) return;
   $json = json_encode($res['data'][0], JSON_UNESCAPED_UNICODE);
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
      if ($puskesDefault <= 0 || dkToken() === '') {
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

      $token = dkToken();
      if ($jenis === 'dewasa') {
         $body = dkMapDewasa($c, $puskes);
         $path = $remote ? 'update-pasien-dewasa' : 'tambah-pasien-dewasa';
         if ($remote) $body = ['id' => $remote] + $body;
         $aksi = $remote ? 'update' : 'tambah';
         [$code, $res, $err] = dkHttp($token, $remote ? 'PUT' : 'POST', $path, [], $body);
      } else {
         $form = $baris['data'] ? (json_decode($baris['data'], true) ?: []) : [];
         if (!$form) return $lewati('Menunggu pengisian Data Dinkes Balita.', 'belum');
         $respon = $baris['dk_respon'] ? json_decode($baris['dk_respon'], true) : null;
         $aksi = $remote ? 'update' : 'tambah';
         if ($remote) {
            [$code, $res, $err] = dkHttp($token, 'PUT', 'update-balita', [], dkMapBalitaUpdate($c, $form, $puskes, $remote, $respon));
         } else {
            [$code, $res, $err] = dkHttp($token, 'POST', 'tambah-balita', [], dkMapBalitaTambah($c, $form, $puskes));
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
      if ($jenis === 'balita') dkAmbilRespon($k, $idc, $token, $c, $rid);
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
      if (dkToken() === '') {
         return ['status' => 'nonaktif', 'message' => 'DINKES_TOKEN belum diisi di .env.'];
      }
      $path = $baris['jenis'] === 'balita' ? 'hapus-balita' : 'hapus-pemeriksaan-pasien-dewasa';
      // ponytail: body hapus-balita belum terdokumentasi di collection; diasumsikan sama dengan dewasa ({"id": ...})
      [$code, $res, $err] = dkHttp(dkToken(), 'DELETE', $path, [], ['id' => (int) $baris['dk_id']]);
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
      if (dkToken() === '') return ['status' => 'error', 'message' => 'DINKES_TOKEN belum diisi di .env.'];
      $c = dkKonteks($k, $idc, $visitID);
      if (!$c) return ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan.'];
      $nik = preg_replace('/\D/', '', (string) $c['patient_nik']);
      $balita = $c['umur'] !== null && $c['umur'] < 5;
      [$code, $res, $err] = $balita
         ? dkHttp(dkToken(), 'GET', 'data-balita', ['nik' => $nik, 'tanggal_ukur' => $c['visit_date']])
         : dkHttp(dkToken(), 'GET', 'data-pasien-dewasa', ['tanggal_periksa' => $c['visit_date'], 'no_periksa' => $visitID, 'nik' => $nik]);
      $ok = dkOk($code, $res);
      $pesan = $ok ? (string) ($res['message'] ?? 'OK') : dkPesan($code, $res, (string) $err);
      dkLog($k, $idc, $visitID, 'get', $code, $ok, $pesan);
      if (!$ok) return ['status' => 'error', 'message' => $pesan];
      return empty($res['data'])
         ? ['status' => 'tidak_ada', 'message' => 'Belum ada di Dinkes.']
         : ['status' => 'ada', 'message' => 'Sudah terkirim ke Dinkes.'];
   } catch (Throwable $e) {
      error_log('[dinkesService] ' . $e->getMessage());
      return ['status' => 'error', 'message' => 'Kesalahan internal saat cek ke Dinkes.'];
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
