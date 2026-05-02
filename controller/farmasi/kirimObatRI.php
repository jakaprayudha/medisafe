<?php
require_once __DIR__ . '/../admisi/services/view.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../admisi/services/servicebpjs.php';

header('Content-Type: application/json');

// 🔥 SAFE SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ================== INPUT ==================
$data_obat = json_decode(file_get_contents("php://input"), true);
$id = $data_obat['id'] ?? null;
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id || !$id_customer) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data tidak valid'
    ]);
    exit;
}

// ================== GET VISIT ==================
$stmt = $koneksi->prepare("
   SELECT id_visit 
   FROM permintaan_pharmacy 
   WHERE id_permintaan_farmasi = ? 
   AND id_customer = ?
");
$stmt->bind_param('ss', $id, $id_customer);
$stmt->execute();
$cek = $stmt->get_result()->fetch_assoc();

if (!$cek) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data permintaan tidak ditemukan'
    ]);
    exit;
}

$nomor_visit = $cek['id_visit'];

$stmt3 = $koneksi->prepare("
      UPDATE permintaan_pharmacy 
      SET status_permintaan = 1 
      WHERE id_permintaan_farmasi = ? 
      AND id_customer = ?
   ");
$stmt3->bind_param("ii", $id, $id_customer);
if ($stmt3->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Berhasil Simpan Obat'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => $stmt3->error
    ]);
}
