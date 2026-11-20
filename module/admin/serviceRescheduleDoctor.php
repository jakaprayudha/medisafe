<?php
/**
 * Ganti dokter kunjungan (menu Aksi > Ganti dokter, registrasi poliklinik).
 * POST visit_id (pasien_visit.id_visit), doctor_new (master_doctor_bpjs.kdDokter)
 *
 * Perbaikan: kunjungan wajib milik faskes session & belum dilayani/batal; dokter sama ditolak;
 * "berhasil" hanya bila baris benar-benar ter-update; pesan error memakai statement yang benar.
 */
include "../../database/connect.php";
require_once __DIR__ . '/../../utility/visitGuard.php';
header("Content-Type: application/json; charset=utf-8");

function rsJawab(bool $ok, string $pesan, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $ok, 'message' => $pesan], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

$id_visit = (int) ($_POST['visit_id'] ?? 0);
$kdDokter = trim((string) ($_POST['doctor_new'] ?? ''));
if ($id_visit <= 0) rsJawab(false, 'ID kunjungan tidak valid.');
if ($kdDokter === '') rsJawab(false, 'Silakan pilih dokter tujuan.');

$kunj = visitGuard($koneksi, 'id_visit', $id_visit);
$oleh = (string) ($_SESSION['username'] ?? '-');
session_write_close();
$idc = (int) $kunj['id_customer'];
$status = (int) $kunj['visit_status'];
if (in_array($status, [3, 4], true)) rsJawab(false, 'Pasien sudah dilayani dokter, dokter tidak dapat diganti.');
if ($status === 99) rsJawab(false, 'Kunjungan sudah dibatalkan.');

$st = $koneksi->prepare("SELECT nmDokter, kdDokter FROM master_doctor_bpjs WHERE kdDokter = ? AND id_customer = ? AND status = '1' LIMIT 1");
$st->bind_param("si", $kdDokter, $idc);
$st->execute();
$dokter = $st->get_result()->fetch_assoc();
$st->close();
if (!$dokter) rsJawab(false, 'Dokter tidak ditemukan atau tidak aktif.');

$st = $koneksi->prepare("SELECT id_doctor, code_doctor FROM pasien_visit WHERE id_visit = ? AND id_customer = ? LIMIT 1");
$st->bind_param("ii", $id_visit, $idc);
$st->execute();
$lama = $st->get_result()->fetch_assoc() ?: [];
$st->close();
if ((string) ($lama['code_doctor'] ?? '') === (string) $dokter['kdDokter'] || trim((string) ($lama['id_doctor'] ?? '')) === trim((string) $dokter['nmDokter'])) {
    rsJawab(false, 'Pasien sudah terdaftar pada dokter ini.');
}

$st = $koneksi->prepare("UPDATE pasien_visit SET id_doctor = ?, code_doctor = ? WHERE id_visit = ? AND id_customer = ? AND visit_status NOT IN (3, 4, 99)");
$st->bind_param("ssii", $dokter['nmDokter'], $dokter['kdDokter'], $id_visit, $idc);
if (!$st->execute()) {
    error_log('[serviceRescheduleDoctor] ' . $st->error);
    rsJawab(false, 'Gagal menyimpan perubahan dokter.');
}
$n = $st->affected_rows;
$st->close();
if ($n < 1) rsJawab(false, 'Kunjungan tidak dapat diubah (mungkin baru saja dilayani). Muat ulang data.');

error_log("[serviceRescheduleDoctor] kunjungan #$id_visit: " . ($lama['id_doctor'] ?? '-') . " -> {$dokter['nmDokter']} oleh $oleh");
rsJawab(true, 'Dokter berhasil diganti ke ' . $dokter['nmDokter'] . '.', ['dokter' => $dokter['nmDokter']]);
