<?php
/**
 * Kirim data vaksinasi (pasien_vaksin) ke SATUSEHAT sebagai resource FHIR Immunization.
 * Terminologi mengikuti "Lampiran Terminologi Imunisasi" SATUSEHAT:
 *   - vaccineCode : KFA  (http://sys-ids.kemkes.go.id/kfa)
 *   - reasonCode  : http://terminology.kemkes.go.id/CodeSystem/immunization-reason (IM-Dasar, IM-Baduta, ...)
 *   - route       : http://www.whocc.no/atc (inj.intramuscular, inj.subcutaneous, O, ...)
 * Alur: data SELALU disimpan dulu di database, baru dikirim. Status kirim disimpan di pasien_vaksin
 * (ss_status belum|terkirim|gagal, ss_id, ss_error, ss_attempts, ss_sent_at, ss_last_try) + dicatat di satusehat_log,
 * sehingga yang gagal bisa dikirim ulang dari halaman vaksin.
 * Butuh migrasi migrations/20261123_pasien_vaksin_satusehat.sql.
 */

const SS_BASE = 'https://api-satusehat.kemkes.go.id';
const SS_FHIR = SS_BASE . '/fhir-r4/v1';
const SS_KFA = 'http://sys-ids.kemkes.go.id/kfa';

/** Katalog vaksin (KFA) dari lampiran terminologi imunisasi SATUSEHAT. kode => [display, grup] */
function imVaksinKatalog(): array
{
   return [
      '93003730' => ['Vaksin Hepatitis B Recombinant 20 ug/1 mL Suspensi Injeksi (Umum)', 'Hepatitis B'],
      '93005477' => ['Vaksin Hepatitis B Recombinant 20 ug/1 mL Suspensi Injeksi (BIO FARMA, 1)', 'Hepatitis B'],
      '93005208' => ['Vaksin BCG Kering 0.375 mg/mL Serbuk Injeksi (BIO FARMA, 20)', 'BCG'],
      '93004566' => ['Vaksin BCG Kering 0.75 mg Serbuk Injeksi (AJ Vaccine, 10)', 'BCG'],
      '93003837' => ['Vaksin BCG Kering 1x10^5 - 4x10^5 C.F.U 0.05 mL Serbuk Injeksi (BIO FARMA, 20)', 'BCG'],
      '93001282' => ['Vaksin DTP - HB - Hib 0,5 mL (PENTABIO, 1)', 'DPT-HB-Hib (Pentavalen)'],
      '93001283' => ['Vaksin DTP - HB - Hib 0,5 mL (PENTABIO, 5)', 'DPT-HB-Hib (Pentavalen)'],
      '93001284' => ['Vaksin DTP - HB - Hib 0,5 mL (PENTABIO, 10)', 'DPT-HB-Hib (Pentavalen)'],
      '93004972' => ['Vaksin Poliomyelitis Oral Bivalent Tipe 1 10^6 CCID_50 / Tipe 3 10^5,8 CCID_50 0,1 mL (BIO FARMA, 10)', 'Polio Oral (OPV)'],
      '93004968' => ['Vaksin Poliomyelitis Oral Bivalent Tipe 1 10^6 CCID_50 / Tipe 3 10^5,8 CCID_50 0,1 mL (BIO FARMA, 20)', 'Polio Oral (OPV)'],
      '93004891' => ['Vaksin Poliomyelitis Oral Monovalen Tipe 1 (BIO FARMA, 20)', 'Polio Oral (OPV)'],
      '93004996' => ['Vaksin Poliomyelitis Oral Monovalen Tipe 2 10^5.0 CCID_50/0.1 mL (BIO FARMA, 20)', 'Polio Oral (OPV)'],
      '93005778' => ['Vaksin Poliomyelitis Oral Monovalen Tipe 2 10^5.0 CCID_50/0.1 mL (BIO FARMA, 50)', 'Polio Oral (OPV)'],
      '93004980' => ['Vaksin Poliomyelitis Oral Trivalen 0,5 mL (BIO FARMA, 10)', 'Polio Oral (OPV)'],
      '93004983' => ['Vaksin Poliomyelitis Oral Trivalen 0,5 mL (BIO FARMA, 20)', 'Polio Oral (OPV)'],
      '93004736' => ['Vaksin Poliomyelitis Inaktif (IPV) 0,5 mL (Shan IPV, 10)', 'Polio Suntik (IPV)'],
      '93004947' => ['Vaksin Poliomyelitis Inaktif (IPV) 0,5 mL (Shan IPV, 5)', 'Polio Suntik (IPV)'],
      '93005779' => ['Vaksin M/R 1000 CCID_50, 0.5 mL (BIO FARMA, 1)', 'Campak Rubella (MR)'],
      '93005781' => ['Vaksin M/R 1000 CCID_50, 0.5 mL (BIO FARMA, 5)', 'Campak Rubella (MR)'],
      '93005780' => ['Vaksin M/R 1000 CCID_50, 0.5 mL (BIO FARMA, 10)', 'Campak Rubella (MR)'],
      '93001619' => ['Vaksin Streptococcus Pneumoniae Serotype 0,5mL (PNEUMOSIL, 1)', 'PCV'],
      '93001620' => ['Vaksin Streptococcus Pneumoniae Serotype 0,5mL (PNEUMOSIL, 5)', 'PCV'],
      '93001652' => ['Vaksin Rotavirus Live Attenuated, G1P[8] Human 89-12 Strain <106 CCID50 1,5 mL (ROTARIX, 1)', 'Rotavirus'],
      '93001653' => ['Vaksin Rotavirus Pentavalent 2 mL (ROTATEQ, 1)', 'Rotavirus'],
      '93001639' => ['Vaksin JE 0,5 mL (BIO FARMA, 5)', 'Japanese Encephalitis'],
      '93001642' => ['Vaksin Diphteri Toxoid 20 Lf / Tetanus toxoid 7,5 Lf 0,5 mL (BIO FARMA, 10)', 'DT'],
      '93001640' => ['Vaksin Diphteri Toxoid 2 Lf / Tetanus toxoid 7,5 Lf 0,5 mL (BIOFARMA, 1)', 'DT'],
      '93001641' => ['Vaksin Diphteri Toxoid 2 Lf / Tetanus toxoid 7,5 Lf 0,5 mL (BIOFARMA, 10)', 'DT'],
      '93006988' => ['Vaksin Diphteri Toxoid 2 Lf / Tetanus toxoid 7,5 Lf/0,5 mL Injeksi (BIO FARMA, 10)', 'Td'],
      '93006989' => ['Vaksin Diphteri Toxoid 2 Lf / Tetanus toxoid 7,5 Lf/0,5 mL Injeksi (BIO FARMA)', 'Td'],
      '93014731' => ['Vaksin Diphteri Toxoid 2 Lf / Tetanus toxoid 7,5 Lf/0,5 mL Injeksi (Bio Td, 1)', 'Td'],
      '93001623' => ['Vaksin HPV quadrivalent 0,5 mL (GARDASIL, 1)', 'HPV'],
      '93001278' => ['Vaksin Meningococcal Polysaccharide 50 ug 0,5 mL (MENIVAX ACYW)', 'Meningitis'],
      '93001279' => ['Vaksin Meningococcal Polysaccharide 50 ug 0,5 mL (FORMENING)', 'Meningitis'],
      '93001560' => ['Vaksin Yellow Fever 1000 IU 0,5 mL (STAMARIL PASTEUR, 1)', 'Yellow Fever'],
      '93001589' => ['Vaksin Rabies (Wistar PM/WI 38-1503-3M Strain) 2,5 IU 0,5 mL (VERORAB)', 'Rabies'],
      '93003763' => ['Vaksin Rabies Inactivated rabies virus (flury lep) NLT 2.5 IU/mL (CHIRORAB)', 'Rabies'],
      '93002539' => ['Vaksin Varicella 3.3 Log PFU 0.5 mL Serbuk Injeksi (Varicella Vaccine, Live)', 'Varisela'],
      '93004691' => ['Vaksin Hepatitis A 160 IU/0.5 mL Suspensi Injeksi (AVAXIM, 1)', 'Hepatitis A'],
   ];
}

function imAlasan(): array
{
   return [
      'IM-Dasar' => 'Imunisasi Program Rutin Dasar',
      'IM-Baduta' => 'Imunisasi Program Rutin Lanjutan Bawah Dua Tahun',
      'IM-SD' => 'Imunisasi Program Rutin Lanjutan Sekolah Dasar',
      'IM-WUS' => 'Imunisasi Program Rutin Lanjutan Wanita Usia Subur',
      'IM-Tambahan' => 'Imunisasi Program Tambahan',
      'IM-Khusus' => 'Imunisasi Program Khusus',
      'IM-Pilihan' => 'Imunisasi Pilihan',
   ];
}

/** Rute pemberian (WHO ATC). Intradermal (BCG) tidak ada di daftar SATUSEHAT -> Parenteral. */
function imRute(): array
{
   return [
      'inj.intramuscular' => 'Injection Intramuscular',
      'inj.subcutaneous' => 'Injection Subcutaneous',
      'p' => 'Parenteral',
      'O' => 'Oral',
      'N' => 'Nasal',
      'inj.intravenous' => 'Injection Intravenous',
   ];
}

/** Catat 1 percobaan kirim ke satusehat_log (pola sama dengan command fill-*). */
function imLog(mysqli $k, int $idc, string $ref, ?string $resId, string $status, string $pesan): void
{
   try {
      $tipe = 'Immunization';
      $st = $k->prepare("INSERT INTO satusehat_log (resource_type, reference_id, resource_id, id_customer, status, message) VALUES (?, ?, ?, ?, ?, ?)");
      $st->bind_param('sssiss', $tipe, $ref, $resId, $idc, $status, $pesan);
      $st->execute();
      $st->close();
   } catch (Throwable $e) {
      error_log('[satusehat/immunization] log: ' . $e->getMessage());
   }
}

/** HTTP ke SATUSEHAT dengan batas waktu (fungsi lama memakai echo+die saat gagal -> merusak respons JSON). */
function imHttp(string $method, string $url, array $headers, ?string $body = null, int $timeout = 25): array
{
   $ch = curl_init($url);
   curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => $timeout,
   ]);
   if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
   $resp = curl_exec($ch);
   $err = curl_errno($ch) ? curl_error($ch) : null;
   $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
   curl_close($ch);
   return [$code, $resp === false ? '' : (string) $resp, $err];
}

/** Setting SATUSEHAT faskes + token aktif (diperbarui bila kedaluwarsa). [setting, error] */
function imSetting(mysqli $k, int $idc): array
{
   $st = $k->prepare("SELECT organization_id, client_id, client_secret, token, expire_at, location_id, location_display FROM setting_satusehat WHERE id_customer = ? LIMIT 1");
   $st->bind_param('i', $idc);
   $st->execute();
   $s = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$s || trim((string) $s['client_id']) === '' || trim((string) $s['client_secret']) === '' || trim((string) $s['organization_id']) === '') {
      return [null, 'Setting SATUSEHAT faskes belum lengkap (client id / secret / organization id).'];
   }
   // expire_at disimpan dalam waktu Asia/Jakarta (sama dengan generateToken() lama)
   $tz = new DateTimeZone('Asia/Jakarta');
   $habis = $s['expire_at'] ? (new DateTime((string) $s['expire_at'], $tz))->getTimestamp() : 0;
   if (!$s['token'] || $habis <= time() + 60) {
      [$code, $resp, $err] = imHttp('POST', SS_BASE . '/oauth2/v1/accesstoken?grant_type=client_credentials',
         ['Content-Type: application/x-www-form-urlencoded'],
         http_build_query(['client_id' => trim($s['client_id']), 'client_secret' => trim($s['client_secret'])]), 20);
      $j = json_decode($resp, true);
      if ($err || empty($j['access_token'])) {
         return [null, 'Gagal mendapatkan token SATUSEHAT' . ($err ? " ($err)" : ($code ? " (HTTP $code)" : '')) . '.'];
      }
      $s['token'] = $j['access_token'];
      $s['expire_at'] = (new DateTime('@' . (time() + (int) ($j['expires_in'] ?? 3599))))->setTimezone($tz)->format('Y-m-d H:i:s');
      $st = $k->prepare("UPDATE setting_satusehat SET token = ?, expire_at = ? WHERE id_customer = ?");
      $st->bind_param('ssi', $s['token'], $s['expire_at'], $idc);
      $st->execute();
      $st->close();
   }
   if (trim((string) $s['location_id']) === '') {
      // sama dengan getLocation(): ambil lokasi pertama organisasi lalu simpan
      [$code, $resp] = imHttp('GET', SS_FHIR . '/Location?organization=' . rawurlencode(trim($s['organization_id'])),
         ['Content-Type: application/json', 'Authorization: Bearer ' . $s['token']]);
      $loc = json_decode($resp, true)['entry'][0]['resource'] ?? null;
      if (!$loc || empty($loc['id'])) return [null, 'Lokasi (Location) faskes belum terdaftar di SATUSEHAT.'];
      $s['location_id'] = $loc['id'];
      $s['location_display'] = $loc['description'] ?? ($loc['name'] ?? '');
      $st = $k->prepare("UPDATE setting_satusehat SET location_id = ?, location_display = ? WHERE id_customer = ?");
      $st->bind_param('ssi', $s['location_id'], $s['location_display'], $idc);
      $st->execute();
      $st->close();
   }
   return [$s, null];
}

/** Pesan error yang bisa dibaca dari OperationOutcome SATUSEHAT. */
function imPesanError(int $code, string $resp, ?string $err): string
{
   if ($err) return "Koneksi ke SATUSEHAT gagal: $err";
   $j = json_decode($resp, true);
   $msg = [];
   foreach (($j['issue'] ?? []) as $is) {
      $t = trim((string) ($is['details']['text'] ?? ($is['diagnostics'] ?? '')));
      if ($t !== '') $msg[] = $t . (!empty($is['expression'][0]) ? ' [' . $is['expression'][0] . ']' : '');
   }
   if ($msg) return mb_substr(implode('; ', array_unique($msg)), 0, 900);
   return 'SATUSEHAT menolak data' . ($code ? " (HTTP $code)" : '') . '.';
}

/**
 * Data pendukung + payload Immunization untuk 1 baris vaksin. [payload, error]
 * $status: 'completed' atau 'entered-in-error' (dipakai saat data lokal dihapus).
 */
function imPayload(mysqli $k, int $idc, array $s, array $v, string $status = 'completed'): array
{
   $st = $k->prepare("SELECT pv.id_encounter, pv.id_doctor, mp.idsh AS idsh_pasien, mp.patient_name
      FROM pasien_visit pv INNER JOIN ms_patient mp ON mp.id_patient = pv.id_patient
      WHERE pv.visit_ID = ? AND pv.id_customer = ? LIMIT 1");
   $st->bind_param('si', $v['id_visit'], $idc);
   $st->execute();
   $kv = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$kv) return [null, 'Kunjungan tidak ditemukan.'];
   if (trim((string) $kv['idsh_pasien']) === '') return [null, 'Pasien belum punya ID SATUSEHAT (IHS). Lengkapi NIK pasien lalu kirim ulang.'];
   if (trim((string) $kv['id_encounter']) === '') return [null, 'Encounter kunjungan belum terbentuk di SATUSEHAT (dibuat otomatis setelah diagnosa diisi). Kirim ulang nanti.'];

   // nakes pemberi = dokter kunjungan (pasien_visit.id_doctor menyimpan nama dokter)
   $dok = trim((string) $kv['id_doctor']);
   $st = $k->prepare("SELECT idsh, doctor_name FROM ms_doctor WHERE id_customer = ? AND idsh IS NOT NULL AND idsh <> ''
      AND (doctor_name = ? OR ? LIKE CONCAT('%', doctor_name, '%')) ORDER BY doctor_name = ? DESC LIMIT 1");
   $st->bind_param('isss', $idc, $dok, $dok, $dok);
   $st->execute();
   $nakes = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$nakes) return [null, 'ID SATUSEHAT dokter kunjungan (' . ($dok ?: '-') . ') belum ada. Lengkapi di master dokter.'];

   $kat = imVaksinKatalog();
   if (empty($v['kfa_code']) || !isset($kat[$v['kfa_code']])) return [null, 'Jenis vaksin (kode KFA) belum dipilih. Ubah data lalu pilih vaksin dari daftar.'];
   if (trim((string) $v['lot_number']) === '') return [null, 'Nomor batch (lot) vaksin wajib diisi.'];
   $alasan = imAlasan();
   $rute = imRute();
   if (!isset($alasan[$v['reason_code']])) return [null, 'Jenis imunisasi (alasan) belum dipilih.'];
   if (!isset($rute[$v['route_code']])) return [null, 'Rute pemberian belum dipilih.'];
   $dosisKe = (int) $v['vaksin_dosis'];
   if ($dosisKe < 1) return [null, 'Dosis ke- harus berupa angka 1 atau lebih.'];

   $tz = new DateTimeZone('Asia/Jakarta');
   $waktu = new DateTime($v['vaksin_date'] . ' ' . ($v['vaksin_time'] ?: '08:00:00'), $tz);
   $p = [
      'resourceType' => 'Immunization',
      'status' => $status,
      'vaccineCode' => ['coding' => [['system' => SS_KFA, 'code' => $v['kfa_code'], 'display' => $kat[$v['kfa_code']][0]]]],
      'patient' => ['reference' => 'Patient/' . trim($kv['idsh_pasien']), 'display' => (string) $kv['patient_name']],
      'encounter' => ['reference' => 'Encounter/' . trim($kv['id_encounter'])],
      'occurrenceDateTime' => $waktu->format('Y-m-d\TH:i:sP'),
      'recorded' => (new DateTime('now', $tz))->format('Y-m-d\TH:i:sP'),
      'primarySource' => true,
      'location' => ['reference' => 'Location/' . trim($s['location_id']), 'display' => (string) $s['location_display']],
      'lotNumber' => trim((string) $v['lot_number']),
      'route' => ['coding' => [['system' => 'http://www.whocc.no/atc', 'code' => $v['route_code'], 'display' => $rute[$v['route_code']]]]],
      'performer' => [[
         'function' => ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v2-0443', 'code' => 'AP', 'display' => 'Administering Provider']]],
         'actor' => ['reference' => 'Practitioner/' . trim($nakes['idsh']), 'display' => (string) $nakes['doctor_name']],
      ]],
      'reasonCode' => [['coding' => [['system' => 'http://terminology.kemkes.go.id/CodeSystem/immunization-reason', 'code' => $v['reason_code'], 'display' => $alasan[$v['reason_code']]]]]],
      'protocolApplied' => [['doseNumberPositiveInt' => $dosisKe]],
   ];
   if (!empty($v['expiration_date'])) $p['expirationDate'] = $v['expiration_date'];
   if ((float) $v['dose_ml'] > 0) $p['doseQuantity'] = ['value' => (float) $v['dose_ml'], 'unit' => 'mL', 'system' => 'http://unitsofmeasure.org', 'code' => 'mL'];
   if (trim((string) $v['vaksin_notes']) !== '') $p['note'] = [['text' => trim((string) $v['vaksin_notes'])]];
   if (!empty($v['ss_id'])) $p['id'] = $v['ss_id'];
   return [$p, null];
}

/**
 * Kirim (POST baru / PUT bila sudah punya ss_id) 1 baris vaksin dan simpan statusnya.
 * Kembalikan ['ok'=>bool, 'pesan'=>string, 'ss_id'=>?string].
 */
function imKirim(mysqli $k, int $idc, int $idVaksin, string $status = 'completed'): array
{
   $st = $k->prepare("SELECT v.* FROM pasien_vaksin v INNER JOIN pasien_visit pv ON pv.visit_ID = v.id_visit AND pv.id_customer = ?
      WHERE v.id_vaksin = ? LIMIT 1");
   $st->bind_param('ii', $idc, $idVaksin);
   $st->execute();
   $v = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$v) return ['ok' => false, 'pesan' => 'Data vaksin tidak ditemukan.', 'ss_id' => null];

   [$s, $err] = imSetting($k, $idc);
   $payload = null;
   if (!$err) [$payload, $err] = imPayload($k, $idc, $s, $v, $status);

   $code = 0;
   $resp = '';
   if (!$err) {
      $put = !empty($v['ss_id']);
      [$code, $resp, $cerr] = imHttp($put ? 'PUT' : 'POST', SS_FHIR . '/Immunization' . ($put ? '/' . rawurlencode($v['ss_id']) : ''),
         ['Content-Type: application/json', 'Authorization: Bearer ' . $s['token']],
         json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
      $j = json_decode($resp, true);
      $idBaru = ($j['resourceType'] ?? '') === 'Immunization' ? ($j['id'] ?? null) : null;
      if ($code >= 200 && $code < 300 && ($idBaru || $put)) {
         $ssId = $idBaru ?: $v['ss_id'];
         $stt = $status === 'completed' ? 'terkirim' : 'dibatalkan';
         $st = $k->prepare("UPDATE pasien_vaksin SET ss_status = ?, ss_id = ?, ss_error = NULL, ss_sent_at = NOW(), ss_last_try = NOW(), ss_attempts = ss_attempts + 1 WHERE id_vaksin = ?");
         $st->bind_param('ssi', $stt, $ssId, $idVaksin);
         $st->execute();
         $st->close();
         imLog($k, $idc, (string) $v['id_visit'], $ssId, 'success', mb_substr($resp, 0, 4000));
         return ['ok' => true, 'pesan' => $status === 'completed' ? 'Terkirim ke SATUSEHAT.' : 'Dibatalkan di SATUSEHAT.', 'ss_id' => $ssId];
      }
      $err = imPesanError($code, $resp, $cerr);
   }

   // gagal juga untuk data yang sudah pernah terkirim: perubahan lokal belum sampai SATUSEHAT (kirim ulang = PUT ss_id)
   $gagal = 'gagal';
   $st = $k->prepare("UPDATE pasien_vaksin SET ss_status = ?, ss_error = ?, ss_last_try = NOW(), ss_attempts = ss_attempts + 1 WHERE id_vaksin = ?");
   $st->bind_param('ssi', $gagal, $err, $idVaksin);
   $st->execute();
   $st->close();
   imLog($k, $idc, (string) $v['id_visit'], $v['ss_id'] ?: null, 'error', mb_substr($resp !== '' ? $resp : $err, 0, 4000));
   return ['ok' => false, 'pesan' => $err, 'ss_id' => $v['ss_id'] ?: null];
}
