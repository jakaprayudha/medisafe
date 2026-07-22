<?php
include 'database/connect.php';
require_once __DIR__ . '/../socket/sendSocket.php';
header('Content-Type: application/json');
$uid = $_SESSION['uid_user'];
$text = strtolower($_POST['text']);
preg_match('/atas nama\s+(.*?)(?=,)/i', $text, $namaMatch);
if (preg_match('/dokter\s+(.*)$/i', $text, $dokterMatch)) {
    $tujuan = $dokterMatch[1];
} elseif (preg_match('/ruangan\s+(.*)$/i', $text, $ruanganMatch)) {
    $tujuan = $ruanganMatch[1];
} else {
    $tujuan = null;
}
$nama   = $namaMatch[1] ?? '';
$dokter = $tujuan ?? '';
$visit_id = $_POST['nomor_visit'];
$result = pemanggilanAntrian([
    "rs_id" => $kdRumahSakit,
    "target_role" => "pemeriksaan_DOCTOR_" . $uid,
    "text" => $text,
    "requestId" => $_POST['requestId'],
    "uid" => $uid,
    "nama" => $nama,
    "dokter" => $dokter
]);
mysqli_query($koneksi, "UPDATE `antrian_poli` SET `status` = '1' WHERE `nomor_visit` = '$visit_id'");

if (!$result['success']) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $result['message'],
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Panggilan berhasil dikirim',
    'text' => $text,
    'nama' => $nama,
    'dokter' => $dokter
]);
