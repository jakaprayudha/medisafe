<?php
/**
 * Penjaga pembatalan pendaftaran/antrean.
 *
 * Dulu pendaftaran bisa dibatalkan tanpa melihat apakah pasien sudah diperiksa (asesmen/CPPT/rawat inap) dan alasannya
 * hanya masuk error_log -> kunjungan yang sudah dilayani dibatalkan tanpa koordinasi.
 *
 *   batalCekKlinis($db, $kunj)              data klinis yang sudah ada pada kunjungan
 *   batalPastikan($db, $kunj, $alasan)      menolak (409/422 JSON + exit) bila alasan kosong / data klinis belum dikonfirmasi
 *   batalCatat($db, $kunj, $klinis, ...)    simpan alasan ke log_batal_pendaftaran (tabel opsional)
 */
function batalCekKlinis(mysqli $db, array $kunj): array
{
   $idv = (int) $kunj['id_visit'];
   $vid = (string) $kunj['visit_ID'];
   $idc = (int) $kunj['id_customer'];
   // visit_inspection.id_visit & pasien_billing.id_visit berisi visit_ID (teks), bukan pasien_visit.id_visit (angka).
   // Dulu dibandingkan dengan pv.id_visit -> permintaan lab & item tagihan tidak pernah terhitung, pembatalan lolos tanpa konfirmasi.
   $st = $db->prepare("SELECT pv.id_poli, pv.id_doctor, pv.visit_out, pv.kondisi_keluar, pv.triase,
         (COALESCE(pv.pemeriksaan_fisik,'') <> '') AS fisik, (COALESCE(pv.catatan_screening,'') <> '') AS screening,
         (SELECT COUNT(*) FROM visit_inspection i WHERE i.id_visit = pv.visit_ID AND i.id_customer = pv.id_customer) AS insp,
         (SELECT COUNT(*) FROM visit_anamnesa a WHERE a.nomor_visit = pv.visit_ID) AS anam,
         (SELECT COUNT(*) FROM visit_terapi t WHERE t.nomor_visit = pv.visit_ID) AS terapi,
         (SELECT COUNT(*) FROM visit_cppt c WHERE c.visit_ID = pv.visit_ID AND c.id_customer = pv.id_customer) AS cppt,
         (SELECT COUNT(*) FROM visit_cppt_dokter d WHERE d.visit_ID = pv.visit_ID AND d.id_customer = pv.id_customer) AS cppt_dokter,
         (SELECT COUNT(*) FROM visit_ranap r WHERE r.visit_ID = pv.visit_ID) AS ranap,
         (SELECT COUNT(*) FROM pasien_billing b WHERE b.id_visit = pv.visit_ID AND b.id_customer = pv.id_customer) AS billing
      FROM pasien_visit pv WHERE pv.id_visit = ? AND pv.id_customer = ? LIMIT 1");
   $st->bind_param('ii', $idv, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc() ?: [];
   $st->close();

   $rincian = [];
   $asesmen = false;
   if (!empty($r['fisik'])) { $asesmen = true; $rincian[] = 'Pemeriksaan fisik sudah diisi'; }
   if (!empty($r['screening']) || !empty($r['triase'])) { $asesmen = true; $rincian[] = 'Skrining/triase sudah diisi'; }
   if ((int) ($r['anam'] ?? 0) > 0) { $asesmen = true; $rincian[] = 'Anamnesa terstruktur sudah diisi'; }
   if ((int) ($r['insp'] ?? 0) > 0) { $asesmen = true; $rincian[] = (int) $r['insp'] . ' permintaan pemeriksaan penunjang (lab/radiologi)'; }
   if ((int) ($r['terapi'] ?? 0) > 0) { $asesmen = true; $rincian[] = 'Terapi sudah diisi'; }
   $jmlCppt = (int) ($r['cppt'] ?? 0) + (int) ($r['cppt_dokter'] ?? 0);
   if ($jmlCppt > 0) $rincian[] = $jmlCppt . ' catatan CPPT';
   $ranap = strcasecmp((string) ($r['visit_out'] ?? ''), 'Rawat Inap') === 0 || strcasecmp((string) ($r['kondisi_keluar'] ?? ''), 'Rawat Inap') === 0 || (int) ($r['ranap'] ?? 0) > 0;
   if ($ranap) array_unshift($rincian, 'Pasien berstatus RAWAT INAP');
   if ((int) ($r['billing'] ?? 0) > 0) $rincian[] = (int) $r['billing'] . ' item tagihan';

   return [
      'ada_asesmen' => $asesmen,
      'ada_cppt' => $jmlCppt > 0,
      'jumlah_cppt' => $jmlCppt,
      'rawat_inap' => $ranap,
      'ada_billing' => (int) ($r['billing'] ?? 0) > 0,
      'butuh_konfirmasi' => $asesmen || $jmlCppt > 0 || $ranap || (int) ($r['billing'] ?? 0) > 0,
      'rincian' => $rincian,
      'poli' => (string) ($r['id_poli'] ?? ''),
      'dokter' => (string) ($r['id_doctor'] ?? ''),
   ];
}

/** Alasan wajib (min. 10 karakter) dan, bila sudah ada data klinis, konfirmasi eksplisit (konfirmasi_klinis=1). */
function batalPastikan(mysqli $db, array $kunj, string $alasan): array
{
   $tolak = function (int $kode, array $isi) {
      http_response_code($kode);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode($isi + ['success' => false], JSON_UNESCAPED_UNICODE);
      exit;
   };
   $klinis = batalCekKlinis($db, $kunj);
   if (mb_strlen(trim($alasan)) < 10) {
      $tolak(422, ['message' => 'Alasan pembatalan wajib diisi (minimal 10 karakter) dan harus menyebut siapa yang sudah dikoordinasikan.', 'field' => 'alasan']);
   }
   if ($klinis['butuh_konfirmasi'] && (string) ($_POST['konfirmasi_klinis'] ?? '') !== '1') {
      $tolak(409, ['perlu_konfirmasi' => true, 'klinis' => $klinis,
         'message' => 'Kunjungan ini sudah memiliki data pemeriksaan. Konfirmasi pembatalan diperlukan.']);
   }
   return $klinis;
}

function batalCatat(mysqli $db, array $kunj, array $klinis, string $alasan, string $provider, string $hasilBpjs): void
{
   try {
      $vid = (string) $kunj['visit_ID'];
      $idc = (int) $kunj['id_customer'];
      $idp = (int) $kunj['id_patient'];
      $a = (int) $klinis['ada_asesmen'];
      $c = (int) $klinis['ada_cppt'];
      $ri = (int) $klinis['rawat_inap'];
      $ringkas = implode('; ', $klinis['rincian']);
      $konf = (int) $klinis['butuh_konfirmasi'];
      $oleh = (string) ($_SESSION['username'] ?? $_SESSION['nama'] ?? $_SESSION['uid_user'] ?? '-');
      $hasilBpjs = mb_substr($hasilBpjs, 0, 250);
      $st = $db->prepare("INSERT INTO log_batal_pendaftaran (visit_ID, id_customer, id_patient, alasan, ada_asesmen, ada_cppt, rawat_inap, ringkasan_klinis, dikonfirmasi, provider, hasil_bpjs, dibatalkan_oleh)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
      $st->bind_param('siisiiisisss', $vid, $idc, $idp, $alasan, $a, $c, $ri, $ringkas, $konf, $provider, $hasilBpjs, $oleh);
      $st->execute();
      $st->close();
   } catch (Throwable $e) {
      error_log('[batalCatat] ' . $e->getMessage());
   }
   error_log('[batalPendaftaran] ' . $kunj['visit_ID'] . ' oleh ' . ($_SESSION['username'] ?? '-') . ' alasan: ' . $alasan . ($klinis['rincian'] ? ' | klinis: ' . implode('; ', $klinis['rincian']) : ''));
}
