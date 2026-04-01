<?php
header('Content-Type: application/json');
include '../../database/connect.php';
require_once __DIR__ . '/../socket/sendSocket.php';
$kdRumahSakit = $_SESSION['id_customer'];
$idAntri = $_POST['idAntri'];

$sql = mysqli_query($koneksi, "UPDATE transaction_queue SET status = 'selesai' WHERE status = 'dipanggil' AND id_queue = '$idAntri'");
if ($sql) {
    trigger([
        "rs_id" => $kdRumahSakit,
        "target_role" => "display-admisi",
    ]);

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => mysqli_error($koneksi)
    ]);
}
