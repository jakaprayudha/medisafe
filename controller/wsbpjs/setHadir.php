<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/serviceantrian.php';
$visit_id = $_POST['visit_id'] ?? '';
$type = $_POST['type'];
$status_hadir = $_POST['statushadir'] ?? '1';
if (str_contains($type, "BPJS")) {
    $stmt = $koneksi->prepare("SELECT pv.visit_date, pv.noKartu, ms.kdPoli FROM pasien_visit AS pv INNER JOIN master_poli AS ms ON ms.nmPoli = pv.id_poli WHERE visit_ID = ? AND id_customer = ?");
    $stmt->bind_param('ss', $visit_id, $idcustomer);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    $payload = [
        "tanggalperiksa" => $data['visit_date'],
        "kodepoli" => $data['kdPoli'],
        "nomorkartu" => $data['noKartu'],
        "status" => (int)$status_hadir,
        "waktu" => round(microtime(true) * 1000)
    ];
    // echo json_encode($payload, JSON_PRETTY_PRINT);
    $result = bpjsPost('/antrean/panggil', $payload);
}
$stmt1 = $koneksi->prepare("UPDATE antrian_poli SET `status` = ? WHERE nomor_visit = ? AND id_customer = ?");
$stmt1->bind_param("sss", $status_hadir, $visit_id, $idcustomer);
if ($status_hadir != '1') {
    $stmt2 = $koneksi->prepare("UPDATE pasien_visit SET visit_status = '99' WHERE visit_ID = ? AND id_customer = ?");
} else {
    $stmt2 = $koneksi->prepare("UPDATE pasien_visit SET visit_status = '1' WHERE visit_ID = ? AND id_customer = ?");
}
$stmt2->bind_param("ss", $visit_id, $idcustomer);
if ($stmt1->execute() && $stmt2->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Berhasil',
        'result' => $result
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal update',
        'result' => $result
    ]);
}