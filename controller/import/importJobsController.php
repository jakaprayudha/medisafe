<?php
include '../../database/connect.php';
header('Content-Type: application/json');

// Return the 30 most recent import jobs for display in the jobs history table
$result = $koneksi->query("
   SELECT
      job_id,
      type,
      id_faskes,
      status,
      total_rows,
      processed_rows,
      success_count,
      duplicate_count,
      error_count,
      result,
      DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') AS created_at
   FROM import_jobs
   ORDER BY created_at DESC
   LIMIT 30
");

if (!$result) {
   echo json_encode(['jobs' => []]);
   exit;
}

$jobs = [];
while ($row = $result->fetch_assoc()) {
   // Don't send full result JSON in the list — only send it on detail request
   $row['result'] = !empty($row['result']) ? true : false;
   $jobs[]        = $row;
}

echo json_encode(['jobs' => $jobs]);
