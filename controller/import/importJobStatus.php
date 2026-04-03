<?php
include '../../database/connect.php';
header('Content-Type: application/json');

$job_id = $_GET['job_id'] ?? null;

if (!$job_id) {
   echo json_encode(['status' => 'error', 'message' => 'job_id diperlukan']);
   exit;
}

$stmt = $koneksi->prepare("
   SELECT status, total_rows, processed_rows, success_count, duplicate_count, error_count, result
   FROM import_jobs
   WHERE job_id = ?
");
$stmt->bind_param("s", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$job) {
   echo json_encode(['status' => 'error', 'message' => 'Job tidak ditemukan']);
   exit;
}

$response = [
   'status'         => $job['status'],
   'total_rows'     => (int) $job['total_rows'],
   'processed_rows' => (int) $job['processed_rows'],
   'success_count'  => (int) $job['success_count'],
   'duplicate_count' => (int) $job['duplicate_count'],
   'error_count'    => (int) $job['error_count'],
];

// Include full result when done or failed
if ($job['status'] === 'done' && $job['result']) {
   $response['result'] = json_decode($job['result'], true);
}

if ($job['status'] === 'failed') {
   $response['message'] = $job['result'] ?? 'Terjadi kesalahan saat memproses';
}

echo json_encode($response);
