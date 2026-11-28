<?php
/**
 * Bed & status pulang pasien rawat inap (module/admin/status_pulang).
 *
 * GET  ?tab=aktif|pulang&dari&sampai  -> {data, bed:[ruang dengan bed & penghuni], hitung}
 * PUT  aksi=pulang     id_ranap, cara_keluar?, kondisi_pulang?   -> status pulang, bed dikosongkan
 *      aksi=pindah     id_ranap, id_bed                          -> pindah ke bed kosong
 *      aksi=aktifkan   id_ranap, id_bed?                         -> batalkan pulang (bed lama / bed lain yang kosong)
 *      aksi=sinkron_bed (admin)                                  -> status bed = sesuai pasien yang masih aktif
 *      (kompatibel: status=pulang|aktif + id_bed seperti versi lama)
 *
 * Perbaikan: dulu tanpa cek faskes (status & bed faskes lain bisa diubah), satu klik toggle langsung memulangkan
 * tanpa konfirmasi & respons gagal tetap ditampilkan "Berhasil", "aktifkan" tidak mengecek bed sudah dipakai pasien
 * lain (bed dobel) dan mengubah visit_status kunjungan yang sudah selesai ke 1, seluruh riwayat dimuat sekaligus.
 */
session_start();
include '../../database/connect.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');

$IDC = (int) ($_SESSION['id_customer'] ?? 0);
$ROLE = (string) ($_SESSION['roles'] ?? '');
$USER = (string) ($_SESSION['username'] ?? '-');
session_write_close();
function lbJawab(array $d, int $kode = 200): void
{
   http_response_code($kode);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($IDC <= 0) lbJawab(['status' => 'error', 'message' => 'Session tidak valid'], 401);

function lbQ(string $sql, string $t = '', array $p = []): array
{
   global $koneksi;
   $st = $koneksi->prepare($sql);
   if (!$st) throw new RuntimeException($koneksi->error);
   if ($t !== '') $st->bind_param($t, ...$p);
   $st->execute();
   $r = $st->get_result();
   $out = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
   $st->close();
   return $out;
}
function lbExec(string $sql, string $t, array $p): int
{
   global $koneksi;
   $st = $koneksi->prepare($sql);
   if (!$st) throw new RuntimeException($koneksi->error);
   $st->bind_param($t, ...$p);
   if (!$st->execute()) throw new RuntimeException($st->error);
   $n = $st->affected_rows;
   $st->close();
   return $n;
}
/** Bed milik faskes? */
function lbBed(int $idc, int $idBed): ?array
{
   return lbQ("SELECT b.id_bed, b.bed_name, b.bed_status, r.room_name, r.id_room FROM ms_room_bed b JOIN ms_room r ON r.id_room = b.id_room
      WHERE b.id_bed = ? AND r.id_customer = ? LIMIT 1", 'ii', [$idBed, $idc])[0] ?? null;
}
/** Pasien aktif lain yang memakai bed ini? */
function lbPenghuni(int $idc, int $idBed, int $kecuali): ?array
{
   return lbQ("SELECT pr.id_ranap, COALESCE(mp.patient_name, pv.patient_name_pcare) nama FROM permintaan_ranap pr
      JOIN pasien_visit pv ON pv.visit_ID = pr.visit_ID_inpatient LEFT JOIN ms_patient mp ON mp.id_patient = pr.id_patient
      WHERE pr.id_bed = ? AND pr.status = 'aktif' AND pr.id_ranap <> ? AND pv.id_customer = ? LIMIT 1", 'iii', [$idBed, $kecuali, $idc])[0] ?? null;
}

try {
   if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $hariIni = date('Y-m-d');
      $tgl = fn($v, $d) => (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : $d;
      $dari = $tgl($_GET['dari'] ?? null, date('Y-m-d', strtotime('-29 days')));
      $sampai = $tgl($_GET['sampai'] ?? null, $hariIni);
      $tab = ($_GET['tab'] ?? '') === 'pulang' ? 'pulang' : 'aktif';

      $sel = "SELECT pr.id_ranap, pr.status, pr.id_bed, pr.id_room, pr.diagnosa_awal, pv.id_visit, pv.visit_ID, pv.visit_date, pv.visit_time, pv.id_doctor,
            pv.cara_keluar, pv.kondisi_pulang, COALESCE(mp.patient_name, pv.patient_name_pcare) AS patient_name, mp.patient_gender, mp.nomor_rm,
            mp.patient_datebirth, r.room_name, r.service_class, b.bed_name
         FROM permintaan_ranap pr
         JOIN pasien_visit pv ON pv.visit_ID = pr.visit_ID_inpatient
         LEFT JOIN ms_patient mp ON mp.id_patient = pr.id_patient
         LEFT JOIN ms_room r ON r.id_room = pr.id_room
         LEFT JOIN ms_room_bed b ON b.id_bed = pr.id_bed
         WHERE pr.ranap_booking = 1 AND pv.id_customer = ?";
      // pasien aktif selalu diambil: dipakai daftar, peta bed & jumlah (satu query, bukan tiga)
      $aktifRows = lbQ($sel . " AND pr.status = 'aktif' ORDER BY pv.visit_date, pv.visit_time", 'i', [$IDC]);
      $rows = $tab === 'aktif' ? $aktifRows
         : lbQ($sel . " AND pr.status = 'pulang' AND pv.visit_date BETWEEN ? AND ? ORDER BY pv.visit_date DESC, pv.visit_time DESC LIMIT 2000", 'iss', [$IDC, $dari, $sampai]);
      foreach ($rows as &$r) {
         $r['id_ranap'] = (int) $r['id_ranap'];
         $r['id_bed'] = $r['id_bed'] !== null ? (int) $r['id_bed'] : null;
         $r['lama_hari'] = $r['visit_date'] ? (new DateTime($r['visit_date']))->diff(new DateTime($hariIni))->days + 1 : null;
      }
      unset($r);

      // peta bed: semua bed faskes + penghuni aktif
      $beds = lbQ("SELECT r.id_room, r.room_name, r.service_class, b.id_bed, b.bed_name, b.bed_status
         FROM ms_room r JOIN ms_room_bed b ON b.id_room = r.id_room WHERE r.id_customer = ? AND COALESCE(r.room_status, 1) = 1 ORDER BY r.id_room, b.bed_name, b.id_bed", 'i', [$IDC]);
      $penghuni = [];
      foreach ($aktifRows as $p) {
         if ($p['id_bed'] !== null && $p['id_bed'] !== '') $penghuni[(int) $p['id_bed']][] = ['id_ranap' => $p['id_ranap'], 'nama' => $p['patient_name'], 'visit_date' => $p['visit_date']];
      }
      $ruang = [];
      $hitBed = ['total' => 0, 'terisi_data' => 0, 'terisi_pasien' => 0, 'tidak_sesuai' => 0];
      foreach ($beds as $b) {
         $id = (int) $b['id_bed'];
         $ph = $penghuni[$id] ?? [];
         $terisiData = (int) $b['bed_status'] === 0;
         $hitBed['total']++;
         if ($terisiData) $hitBed['terisi_data']++;
         if ($ph) $hitBed['terisi_pasien']++;
         if ($terisiData !== (bool) $ph) $hitBed['tidak_sesuai']++;
         $ruang[$b['id_room']]['id_room'] = (int) $b['id_room'];
         $ruang[$b['id_room']]['nama'] = $b['room_name'];
         $ruang[$b['id_room']]['kelas'] = $b['service_class'];
         $ruang[$b['id_room']]['bed'][] = ['id_bed' => $id, 'nama' => $b['bed_name'], 'terisi_data' => $terisiData,
            'pasien' => array_map(fn($x) => ['id_ranap' => (int) $x['id_ranap'], 'nama' => $x['nama'], 'masuk' => $x['visit_date']], $ph)];
      }
      $aktif = count($aktifRows);
      lbJawab(['status' => 'success', 'tab' => $tab, 'data' => $rows, 'ruang' => array_values($ruang), 'bed' => $hitBed,
         'hitung' => ['aktif' => $aktif, 'pulang' => $tab === 'pulang' ? count($rows) : null], 'boleh_sinkron' => in_array($ROLE, ['admin', 'superadmin', 'administrator'], true)]);
   }

   if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') lbJawab(['status' => 'error', 'message' => 'Metode tidak diizinkan.'], 405);
   parse_str((string) file_get_contents('php://input'), $in);
   if (!$in) $in = $_POST;
   $aksi = (string) ($in['aksi'] ?? '');
   if ($aksi === '' && isset($in['status'])) {
      // format lama
      $aksi = $in['status'] === 'pulang' ? 'pulang' : 'aktifkan';
   }

   if ($aksi === 'sinkron_bed') {
      if (!in_array($ROLE, ['admin', 'superadmin', 'administrator'], true)) lbJawab(['status' => 'error', 'message' => 'Hanya admin yang dapat menyinkronkan bed.'], 403);
      $koneksi->begin_transaction();
      // bed dengan pasien aktif = terisi (0), lainnya = kosong (1); hanya bed faskes ini
      $isi = lbExec("UPDATE ms_room_bed b JOIN ms_room r ON r.id_room = b.id_room
         SET b.bed_status = IF(EXISTS (SELECT 1 FROM permintaan_ranap pr JOIN pasien_visit pv ON pv.visit_ID = pr.visit_ID_inpatient
            WHERE pr.id_bed = b.id_bed AND pr.status = 'aktif' AND pv.id_customer = r.id_customer), 0, 1)
         WHERE r.id_customer = ?", 'i', [$IDC]);
      $koneksi->commit();
      error_log("[ListBookingRanap] sinkron bed faskes $IDC oleh $USER ($isi bed diubah)");
      lbJawab(['status' => 'success', 'message' => "$isi bed disesuaikan dengan pasien yang masih dirawat."]);
   }

   $idRanap = (int) ($in['id_ranap'] ?? 0);
   $ranap = lbQ("SELECT pr.id_ranap, pr.status, pr.id_bed, pr.visit_ID_inpatient, pv.id_visit FROM permintaan_ranap pr
      JOIN pasien_visit pv ON pv.visit_ID = pr.visit_ID_inpatient WHERE pr.id_ranap = ? AND pv.id_customer = ? LIMIT 1", 'ii', [$idRanap, $IDC])[0] ?? null;
   if (!$ranap) lbJawab(['status' => 'error', 'message' => 'Data rawat inap tidak ditemukan di faskes ini.'], 404);
   $bedLama = $ranap['id_bed'] !== null ? (int) $ranap['id_bed'] : 0;

   $koneksi->begin_transaction();
   if ($aksi === 'pulang') {
      if ($ranap['status'] === 'pulang') { $koneksi->rollback(); lbJawab(['status' => 'error', 'message' => 'Pasien sudah berstatus pulang.']); }
      lbExec("UPDATE permintaan_ranap SET status = 'pulang' WHERE id_ranap = ?", 'i', [$idRanap]);
      // kosongkan bed hanya bila tidak ada pasien aktif lain di bed yang sama
      if ($bedLama && !lbPenghuni($IDC, $bedLama, $idRanap)) lbExec("UPDATE ms_room_bed SET bed_status = 1 WHERE id_bed = ?", 'i', [$bedLama]);
      $cara = mb_substr(trim((string) ($in['cara_keluar'] ?? '')), 0, 50);
      $kondisi = mb_substr(trim((string) ($in['kondisi_pulang'] ?? '')), 0, 50);
      $set = ['visit_status = 4'];
      $t = '';
      $p = [];
      if ($cara !== '') { $set[] = 'cara_keluar = ?'; $t .= 's'; $p[] = $cara; }
      if ($kondisi !== '') { $set[] = 'kondisi_pulang = ?'; $t .= 's'; $p[] = $kondisi; }
      $p[] = (int) $ranap['id_visit'];
      lbExec("UPDATE pasien_visit SET " . implode(', ', $set) . " WHERE id_visit = ?", $t . 'i', $p);
      $koneksi->commit();
      require_once __DIR__ . '/../../utility/visitLog.php';
      logKunjungan($koneksi, 'pulang', (int) $ranap['id_visit'], 'Dipulangkan dari menu Bed & status pulang',
         ['status_ranap' => 'aktif', 'id_bed' => $bedLama ?: null], ['status_ranap' => 'pulang', 'visit_status' => 4, 'cara_keluar' => $cara, 'kondisi_pulang' => $kondisi], 'Rawat Inap');
      error_log("[ListBookingRanap] ranap #$idRanap dipulangkan oleh $USER");
      lbJawab(['status' => 'success', 'message' => 'Pasien dipulangkan, bed dikosongkan.']);
   }

   if ($aksi === 'pindah' || $aksi === 'aktifkan') {
      if ($aksi === 'pindah' && $ranap['status'] !== 'aktif') { $koneksi->rollback(); lbJawab(['status' => 'error', 'message' => 'Hanya pasien yang sedang dirawat yang dapat pindah bed.']); }
      $bedBaru = (int) ($in['id_bed'] ?? 0) ?: $bedLama;
      if (!$bedBaru) { $koneksi->rollback(); lbJawab(['status' => 'error', 'message' => 'Pilih bed terlebih dahulu.'], 422); }
      $bed = lbBed($IDC, $bedBaru);
      if (!$bed) { $koneksi->rollback(); lbJawab(['status' => 'error', 'message' => 'Bed tidak ditemukan di faskes ini.'], 404); }
      $lain = lbPenghuni($IDC, $bedBaru, $idRanap);
      if ($lain) { $koneksi->rollback(); lbJawab(['status' => 'error', 'message' => "Bed {$bed['bed_name']} sedang dipakai {$lain['nama']}. Pilih bed lain."], 409); }
      if ($bedLama && $bedLama !== $bedBaru && !lbPenghuni($IDC, $bedLama, $idRanap)) lbExec("UPDATE ms_room_bed SET bed_status = 1 WHERE id_bed = ?", 'i', [$bedLama]);
      lbExec("UPDATE ms_room_bed SET bed_status = 0 WHERE id_bed = ?", 'i', [$bedBaru]);
      lbExec("UPDATE permintaan_ranap SET id_bed = ?, id_room = ?, status = 'aktif' WHERE id_ranap = ?", 'iii', [$bedBaru, (int) $bed['id_room'], $idRanap]);
      // dulu "aktifkan" juga mengubah visit_status kunjungan (selesai -> 1); status kunjungan tidak lagi diubah
      $koneksi->commit();
      require_once __DIR__ . '/../../utility/visitLog.php';
      logKunjungan($koneksi, $aksi === 'pindah' ? 'pindah_bed' : 'ranap_aktif', (int) $ranap['id_visit'], 'Bed ' . $bed['bed_name'],
         ['status_ranap' => $ranap['status'], 'id_bed' => $bedLama ?: null], ['status_ranap' => 'aktif', 'id_bed' => $bedBaru, 'id_room' => (int) $bed['id_room']], 'Rawat Inap');
      error_log("[ListBookingRanap] ranap #$idRanap $aksi ke bed $bedBaru oleh $USER");
      lbJawab(['status' => 'success', 'message' => $aksi === 'pindah' ? "Pasien dipindah ke bed {$bed['bed_name']}." : "Pasien kembali berstatus dirawat di bed {$bed['bed_name']}."]);
   }

   $koneksi->rollback();
   lbJawab(['status' => 'error', 'message' => 'Aksi tidak dikenal.'], 400);
} catch (Throwable $e) {
   try { $koneksi->rollback(); } catch (Throwable $x) {}
   error_log('[ListBookingRanap] ' . $e->getMessage());
   lbJawab(['status' => 'error', 'message' => 'Terjadi kesalahan server.'], 500);
}
