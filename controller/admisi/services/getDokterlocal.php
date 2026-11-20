<?php
/**
 * Dokter praktik per poli & tanggal (jadwal ms_doctor_schedule) untuk pendaftaran poliklinik.
 * GET kdpoli, tanggal (Y-m-d) -> {success, data:[{namadokter, kodedokter, jampraktek, nmDokterBpjs, exp, mulai, selesai}]}
 * Dulu: kdpoli & tanggal disisipkan langsung ke SQL (SQL injection) dan tanggal tidak valid membuat error 500.
 */
require_once __DIR__ . '/view.php';
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');
session_write_close();

$kdpoli = trim((string) ($_GET['kdpoli'] ?? ''));
$tanggal = (string) ($_GET['tanggal'] ?? '');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || !strtotime($tanggal)) $tanggal = date('Y-m-d');
$namaHari = ["Sunday" => "Minggu", "Monday" => "Senin", "Tuesday" => "Selasa", "Wednesday" => "Rabu", "Thursday" => "Kamis", "Friday" => "Jumat", "Saturday" => "Sabtu"];
$hari = $namaHari[(new DateTime($tanggal))->format('l')];
$idc = (int) $idcustomer;

$data = [];
if ($kdpoli !== '' && $idc > 0) {
    $st = $koneksi->prepare("SELECT d.doctor_name, d.doctor_code, j.start_time, j.end_time, db.nmDokter
        FROM ms_doctor AS d
        INNER JOIN ms_doctor_schedule AS j ON j.id_doctor = d.doctor_code AND j.id_customer = d.id_customer
        INNER JOIN master_doctor_bpjs AS db ON db.kdDokter = d.doctor_code AND db.id_customer = d.id_customer
        WHERE d.id_customer = ? AND j.day_of_week = ? AND j.id_poli = ? AND d.id_poli = ?
        ORDER BY j.start_time, d.doctor_name");
    $st->bind_param('isss', $idc, $hari, $kdpoli, $kdpoli);
    $st->execute();
    $now = new DateTime();
    foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $selesai = new DateTime($tanggal . ' ' . $row['end_time']);
        $data[] = [
            "namadokter" => $row['doctor_name'],
            "kodedokter" => (int) $row['doctor_code'],
            "jampraktek" => substr($row['start_time'], 0, 5) . "-" . substr($row['end_time'], 0, 5),
            "nmDokterBpjs" => $row['nmDokter'],
            "exp" => $now > $selesai,
        ];
    }
    $st->close();
}

echo json_encode(["success" => true, "code" => "200", "message" => "OK", "data" => $data, "hari" => $hari], JSON_UNESCAPED_UNICODE);
