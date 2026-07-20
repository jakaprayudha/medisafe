<?php

require_once __DIR__ . '/view.php';
// require_once __DIR__ . '/../../../vendor/autoload.php';
// require_once __DIR__ . '/servicebpjs.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');

$visit_id = trim($_POST['visit_ID'] ?? '');

if ($visit_id == "") {
    echo json_encode([
        "success" => false,
        "message" => "Visit ID tidak ditemukan."
    ]);
    exit;
}

mysqli_begin_transaction($koneksi);

try {

    $cek = $koneksi->prepare("
        SELECT visit_ID
        FROM pasien_visit
        WHERE visit_ID = ?
        AND id_customer = ?
    ");

    $cek->bind_param("ss", $visit_id, $idcustomer);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows == 0) {
        throw new Exception("Data kunjungan tidak ditemukan.");
    }

    $cek->close();

    $stmt = $koneksi->prepare("
        UPDATE pasien_visit
        SET visit_status = '99'
        WHERE visit_ID = ?
        AND id_customer = ?
    ");

    $stmt->bind_param("ss", $visit_id, $idcustomer);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $stmt1 = $koneksi->prepare("
        UPDATE antrian_poli
        SET status = '99'
        WHERE nomor_visit = ?
        AND id_customer = ?
    ");

    $stmt1->bind_param("ss", $visit_id, $idcustomer);

    if (!$stmt1->execute()) {
        throw new Exception($stmt1->error);
    }

    mysqli_commit($koneksi);

    echo json_encode([
        "success" => true,
        "message" => "Antrean berhasil dibatalkan."
    ]);

    $stmt->close();
    $stmt1->close();
} catch (Exception $e) {

    mysqli_rollback($koneksi);

    if (isset($stmt)) {
        $stmt->close();
    }

    if (isset($stmt1)) {
        $stmt1->close();
    }

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
