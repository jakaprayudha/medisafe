<?php
include '../../database/connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$type    = $data['type']      ?? null;
$rows    = $data['data']      ?? [];
$id_faskes = $data['id_faskes'] ?? null;

if (!$type || !$rows) {
   echo json_encode(['status' => 'error', 'message' => 'Data kosong']);
   exit;
}

if (!$id_faskes) {
   echo json_encode(['status' => 'error', 'message' => 'Faskes wajib dipilih']);
   exit;
}

// Ensure temp directory exists
$tempDir = __DIR__ . '/temp';
if (!is_dir($tempDir)) {
   mkdir($tempDir, 0775, true);
}

// Generate unique job ID
$job_id = uniqid('import_', true);

// Persist payload to temp file (background process will read it)
$dataFile = $tempDir . '/' . $job_id . '.json';
file_put_contents($dataFile, json_encode([
   'type'      => $type,
   'id_faskes' => $id_faskes,
   'data'      => $rows
]));

// Insert job record
$totalRows = count($rows);
$stmt = $koneksi->prepare("
   INSERT INTO import_jobs (job_id, type, id_faskes, status, total_rows, data_file)
   VALUES (?, ?, ?, 'pending', ?, ?)
");
$stmt->bind_param("sssis", $job_id, $type, $id_faskes, $totalRows, $dataFile);
$stmt->execute();
$stmt->close();

// Spawn background process using the current PHP binary
$phpBin      = PHP_BINARY;
$scriptPath  = escapeshellarg(__DIR__ . '/processImportJob.php');
$jobArg      = escapeshellarg($job_id);
exec("$phpBin $scriptPath $jobArg > /dev/null 2>&1 &");

echo json_encode([
   'status'     => 'queued',
   'job_id'     => $job_id,
   'total_rows' => $totalRows,
   'message'    => 'Import sedang diproses di background'
]);
