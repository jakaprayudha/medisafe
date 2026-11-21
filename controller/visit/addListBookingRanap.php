<?php
/**
 * Permintaan rawat inap yang belum di-approve (module/admin/registrasi_booking_ranap).
 *
 * GET        -> {data:[permintaan], ruang:[{id_room, nama, kelas, bed:[{id_bed, nama, gender, terisi_data, pasien}]}], hitung}
 *               Bed "terpakai" = ada permintaan_ranap aktif yang sudah di-approve (bukan dari visit_status, lihat catatan).
 * GET ?id=   -> satu permintaan (hanya faskes session).
 *
 * Perbaikan: dulu SELECT * dari 3 tabel (kolom id_doctor/status/created_at saling menimpa), ?id= tanpa cek faskes,
 * PUT/DELETE mengubah/menghapus pasien_visit mana pun tanpa cek faskes (tidak dipakai halaman mana pun -> dihapus).
 */
session_start();
include '../../database/connect.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

$IDC = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();
function brJawab(array $d, int $kode = 200): void
{
   http_response_code($kode);
   if ($kode === 200 && !ini_get('zlib.output_compression') && extension_loaded('zlib')) ob_start('ob_gzhandler');
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}
function brQ(string $sql, string $t = '', array $p = []): array
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
if ($IDC <= 0) brJawab(['status' => 'error', 'message' => 'Session tidak valid / expired'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'GET') brJawab(['status' => 'error', 'message' => 'Metode tidak diizinkan.'], 405);

$SEL = "SELECT rn.id_ranap, rn.ranap_booking, rn.status AS status_ranap, rn.id_bed, rn.id_patient, rn.id_doctor, rn.ranap_date, rn.ranap_time, rn.diagnosa_awal, rn.ranap_notes, rn.created_at,
      pv.visit_ID, pv.visit_date, pv.id_poli, pv.status_rawatinap, pv.noKartu, prov.provider_name,
      COALESCE(NULLIF(mp.patient_name, ''), pv.patient_name_pcare) AS patient_name, mp.nomor_rm, mp.patient_gender, mp.patient_datebirth
   FROM permintaan_ranap rn
   JOIN pasien_visit pv ON pv.visit_ID = rn.visit_ID_inpatient
   LEFT JOIN ms_patient mp ON mp.id_patient = rn.id_patient
   LEFT JOIN ms_provider prov ON prov.id_provider = pv.id_provider
   WHERE pv.id_customer = ?";

try {
   if (isset($_GET['id'])) {
      $r = brQ($SEL . " AND rn.ranap_booking = 0 AND rn.id_ranap = ? LIMIT 1", 'ii', [$IDC, (int) $_GET['id']])[0] ?? null;
      brJawab($r ? ['status' => 'success', 'data' => $r] : ['status' => 'error', 'message' => 'Data tidak ditemukan.'], $r ? 200 : 404);
   }

   $hariIni = date('Y-m-d');
   // satu query: permintaan yang belum di-approve + pasien ranap aktif (untuk bed terpakai & penanda kunjungan dobel).
   // DB remote -> tiap round-trip mahal, jadi digabung.
   $semua = brQ($SEL . " AND (rn.ranap_booking = 0 OR (rn.ranap_booking = 1 AND rn.status = 'aktif'))
      ORDER BY rn.ranap_date DESC, rn.ranap_time DESC, rn.id_ranap DESC LIMIT 3000", 'i', [$IDC]);
   $rows = [];
   $visitDirawat = [];
   $penghuni = [];
   foreach ($semua as $a) {
      if ((int) $a['ranap_booking'] === 0) { $rows[] = $a; continue; }
      $visitDirawat[$a['visit_ID']] = true;
      if ((string) $a['id_bed'] !== '') $penghuni[(int) $a['id_bed']][] = ['nama' => $a['patient_name'], 'gender' => $a['patient_gender'], 'masuk' => $a['visit_date']];
   }
   unset($semua);

   $hit = ['total' => 0, 'hari_ini' => 0, 'lama' => 0, 'sudah_dirawat' => 0];
   foreach ($rows as &$r) {
      $r['id_ranap'] = (int) $r['id_ranap'];
      unset($r['ranap_booking'], $r['status_ranap'], $r['id_bed']);
      $r['umur_permintaan'] = $r['ranap_date'] ? (int) (new DateTime($r['ranap_date']))->diff(new DateTime($hariIni))->format('%r%a') : null;
      $r['sudah_dirawat'] = isset($visitDirawat[$r['visit_ID']]) || (int) $r['status_rawatinap'] === 1;
      $hit['total']++;
      if ($r['ranap_date'] === $hariIni) $hit['hari_ini']++;
      if ($r['umur_permintaan'] !== null && $r['umur_permintaan'] > 3) $hit['lama']++;
      if ($r['sudah_dirawat']) $hit['sudah_dirawat']++;
   }
   unset($r);

   $beds = brQ("SELECT r.id_room, r.room_name, r.service_class, b.id_bed, b.bed_name, b.bed_gender, b.bed_status
      FROM ms_room r JOIN ms_room_bed b ON b.id_room = r.id_room
      WHERE r.id_customer = ? AND COALESCE(r.room_status, 1) = 1 ORDER BY r.service_class, r.id_room, b.bed_name, b.id_bed", 'i', [$IDC]);
   $ruang = [];
   $kosong = 0;
   foreach ($beds as $b) {
      $id = (int) $b['id_bed'];
      $ph = $penghuni[$id] ?? [];
      if (!$ph) $kosong++;
      $k = $b['id_room'];
      $ruang[$k] ??= ['id_room' => (int) $k, 'nama' => $b['room_name'], 'kelas' => $b['service_class'], 'bed' => []];
      $ruang[$k]['bed'][] = ['id_bed' => $id, 'nama' => $b['bed_name'], 'gender' => $b['bed_gender'], 'terisi_data' => (int) $b['bed_status'] === 0, 'pasien' => $ph];
   }
   $hit['bed_total'] = count($beds);
   $hit['bed_kosong'] = $kosong;

   brJawab(['status' => 'success', 'hari_ini' => $hariIni, 'data' => $rows, 'ruang' => array_values($ruang), 'hitung' => $hit]);
} catch (Throwable $e) {
   error_log('[addListBookingRanap] ' . $e->getMessage());
   brJawab(['status' => 'error', 'message' => 'Gagal memuat data permintaan rawat inap.'], 500);
}
