<?php
/**
 * Approve permintaan rawat inap: tempatkan pasien ke bed (module/admin/registrasi_booking_ranap).
 * POST id_ranap, id_bed (atau bed_name = id_bed, format lama). Ruang diambil dari bed, bukan dari input.
 *
 * Perbaikan: dulu tanpa session/cek faskes (permintaan & bed faskes lain bisa dipakai), tanpa validasi (id kosong tetap
 * "berhasil"), bed yang sedang dipakai pasien lain tetap bisa dipilih (bed dobel), permintaan yang sudah di-approve
 * bisa di-approve ulang, tiga UPDATE tanpa transaksi, dan pesan error berisi detail SQL.
 */
session_start();
require_once '../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');

$IDC = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();
function apJawab(array $d, int $kode = 200): void
{
   http_response_code($kode);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}
function apQ(string $sql, string $t, array $p): ?array
{
   global $koneksi;
   $st = $koneksi->prepare($sql);
   if (!$st) throw new RuntimeException($koneksi->error);
   $st->bind_param($t, ...$p);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}
function apExec(string $sql, string $t, array $p): int
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
if ($IDC <= 0) apJawab(['status' => 'error', 'message' => 'Session tidak valid / expired'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') apJawab(['status' => 'error', 'message' => 'Metode tidak diizinkan.'], 405);

$idRanap = (int) ($_POST['id_ranap'] ?? 0);
$idBed = (int) ($_POST['id_bed'] ?? ($_POST['bed_name'] ?? 0));
if ($idRanap <= 0 || $idBed <= 0) apJawab(['status' => 'error', 'message' => 'Pilih tempat tidur terlebih dahulu.'], 422);

try {
   $koneksi->begin_transaction();
   $rq = apQ("SELECT rn.id_ranap, rn.ranap_booking, rn.visit_ID_inpatient FROM permintaan_ranap rn
      JOIN pasien_visit pv ON pv.visit_ID = rn.visit_ID_inpatient
      WHERE rn.id_ranap = ? AND pv.id_customer = ? LIMIT 1 FOR UPDATE", 'ii', [$idRanap, $IDC]);
   if (!$rq) { $koneksi->rollback(); apJawab(['status' => 'error', 'message' => 'Permintaan tidak ditemukan.'], 404); }
   if ((int) $rq['ranap_booking'] !== 0) { $koneksi->rollback(); apJawab(['status' => 'error', 'message' => 'Permintaan ini sudah di-approve.'], 409); }

   // kunci bed -> dua approve bersamaan ke bed yang sama tidak bisa lolos dua-duanya
   $bed = apQ("SELECT b.id_bed, b.id_room, b.bed_name, r.room_name FROM ms_room_bed b JOIN ms_room r ON r.id_room = b.id_room
      WHERE b.id_bed = ? AND r.id_customer = ? AND COALESCE(r.room_status, 1) = 1 LIMIT 1 FOR UPDATE", 'ii', [$idBed, $IDC]);
   if (!$bed) { $koneksi->rollback(); apJawab(['status' => 'error', 'message' => 'Tempat tidur tidak ditemukan.'], 404); }

   $isi = apQ("SELECT COALESCE(NULLIF(mp.patient_name, ''), pv.patient_name_pcare) nama FROM permintaan_ranap pr
      JOIN pasien_visit pv ON pv.visit_ID = pr.visit_ID_inpatient LEFT JOIN ms_patient mp ON mp.id_patient = pr.id_patient
      WHERE pr.id_bed = ? AND pr.status = 'aktif' AND pr.ranap_booking = 1 AND pv.id_customer = ? LIMIT 1", 'si', [(string) $idBed, $IDC]);
   if ($isi) { $koneksi->rollback(); apJawab(['status' => 'error', 'message' => 'Tempat tidur sedang dipakai ' . ($isi['nama'] ?: 'pasien lain') . '. Pilih bed lain.'], 409); }

   $dobel = apQ("SELECT id_ranap FROM permintaan_ranap WHERE visit_ID_inpatient = ? AND ranap_booking = 1 AND status = 'aktif' LIMIT 1", 's', [$rq['visit_ID_inpatient']]);
   if ($dobel) { $koneksi->rollback(); apJawab(['status' => 'error', 'message' => 'Kunjungan ini sudah dirawat inap (permintaan lain sudah di-approve).'], 409); }

   apExec("UPDATE pasien_visit SET status_rawatinap = 1 WHERE visit_ID = ? AND id_customer = ?", 'si', [$rq['visit_ID_inpatient'], $IDC]);
   $n = apExec("UPDATE permintaan_ranap SET ranap_booking = 1, id_room = ?, id_bed = ?, status = 'aktif' WHERE id_ranap = ? AND ranap_booking = 0",
      'ssi', [(string) $bed['id_room'], (string) $idBed, $idRanap]);
   if ($n !== 1) throw new RuntimeException('permintaan tidak terupdate');
   apExec("UPDATE ms_room_bed SET bed_status = 0 WHERE id_bed = ?", 'i', [$idBed]);
   $koneksi->commit();
   $nmBed = preg_match('/[0-9A-Za-z]/', (string) $bed['bed_name']) ? $bed['bed_name'] : 'bed #' . $idBed;
   apJawab(['status' => 'success', 'message' => 'Pasien ditempatkan di ' . $nmBed . ' — ' . ($bed['room_name'] ?: 'ruang') . ' #' . $bed['id_room'] . '.',
      'visit_ID' => $rq['visit_ID_inpatient'], 'id_bed' => $idBed, 'id_room' => (int) $bed['id_room']]);
} catch (Throwable $e) {
   try { $koneksi->rollback(); } catch (Throwable $x) {}
   error_log('[approveRawatInap] ' . $e->getMessage());
   apJawab(['status' => 'error', 'message' => 'Gagal menyetujui permintaan rawat inap.'], 500);
}
