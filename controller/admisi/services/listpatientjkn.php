<?php
/**
 * Booking Mobile JKN (module/admin/listpasienadmisi).
 * GET fromDate, toDate, doctor, poli, status (semua|belum|sudah|selesai|batal)
 * -> {data:[...], hitung:{total, belum, sudah, selesai, batal}}
 * Dulu: DATE(visit_date) (index tak terpakai), tanpa jumlah per status, DISTINCT yang tidak perlu.
 */
require_once __DIR__ . '/view.php';
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');
$idc = (int) ($idcustomer ?? 0);
session_write_close();
if ($idc <= 0) { http_response_code(401); echo json_encode(['data' => [], 'message' => 'Session tidak valid']); exit; }

$tgl = fn($v, $d) => (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) ? $v : $d;
$from = $tgl($_GET['fromDate'] ?? null, date('Y-m-d'));
$to = $tgl($_GET['toDate'] ?? null, date('Y-m-d'));
if ($from > $to) [$from, $to] = [$to, $from];
$w = "pv.id_customer = ? AND pv.visit_date BETWEEN ? AND ? AND pv.created_user = 'MobileJKN'";
$t = 'iss';
$p = [$idc, $from, $to];
foreach (['doctor' => 'pv.id_doctor', 'poli' => 'pv.id_poli'] as $k => $kol) {
    $v = trim((string) ($_GET[$k] ?? ''));
    if ($v !== '') { $w .= " AND $kol = ?"; $t .= 's'; $p[] = $v; }
}
$st = $koneksi->prepare("SELECT COUNT(*) total, SUM(pv.visit_status = 10) belum, SUM(pv.visit_status IN (0,1,2,3)) sudah, SUM(pv.visit_status = 4) selesai,
    SUM(pv.visit_status = 99) batal FROM pasien_visit pv WHERE $w");
$st->bind_param($t, ...$p);
$st->execute();
$hitung = array_map('intval', $st->get_result()->fetch_assoc() ?: []);
$st->close();

$filter = ['belum' => ' AND pv.visit_status = 10', 'sudah' => ' AND pv.visit_status IN (0,1,2,3)', 'selesai' => ' AND pv.visit_status = 4', 'batal' => ' AND pv.visit_status = 99'];
$status = (string) ($_GET['status'] ?? '');
$st = $koneksi->prepare("SELECT pv.visit_ID, pv.visit_status, pv.visit_antrian, pv.visit_date, pv.visit_time, pv.noKartu, pv.id_doctor, pv.id_poli,
        mp.patient_name, mp.patient_gender, mp.nomor_rm, mp.patient_phone, md.nmDokter
    FROM pasien_visit pv
    LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
    LEFT JOIN master_doctor_bpjs md ON md.kdDokter = pv.code_doctor AND md.id_customer = pv.id_customer
    WHERE $w" . ($filter[$status] ?? '') . "
    ORDER BY pv.visit_date, pv.id_poli, pv.code_doctor, LEFT(pv.visit_antrian, 1), CAST(REGEXP_SUBSTR(pv.visit_antrian, '[0-9]+$') AS UNSIGNED), pv.id_visit
    LIMIT 3000");
$st->bind_param($t, ...$p);
$st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

$data = array_map(fn($r) => [
    'visit_ID' => $r['visit_ID'],
    'visit_status' => (int) $r['visit_status'],
    'visit_antrian' => $r['visit_antrian'],
    'visit_date' => $r['visit_date'],
    'visit_time' => $r['visit_time'],
    'nomor_rm' => $r['nomor_rm'],
    'no_bpjs' => $r['noKartu'],
    'patient_name' => $r['patient_name'],
    'patient_gender' => $r['patient_gender'],
    'patient_phone' => $r['patient_phone'],
    'doctor_name' => $r['nmDokter'] ?: $r['id_doctor'],
    'poli_name' => $r['id_poli'],
], $rows);

if (!ini_get('zlib.output_compression') && extension_loaded('zlib')) ob_start('ob_gzhandler');
echo json_encode(['data' => $data, 'hitung' => $hitung, 'hari_ini' => date('Y-m-d')], JSON_UNESCAPED_UNICODE);
