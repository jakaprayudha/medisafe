<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once '../../database/connect.php';

$sql = "
    SELECT
        id_update,
        title,
        description,
        type,
        version,
        created_at,
        is_read
    FROM system_update_log
    ORDER BY created_at DESC
";

$result = $koneksi->query($sql);

if (!$result) {
   echo json_encode([
      'status' => 'error',
      'message' => $koneksi->error,
      'data' => [],
      'unread_count' => 0
   ]);
   exit;
}

$data = [];
$unread_count = 0;

while ($row = $result->fetch_assoc()) {

   $row['id_update'] = (int) $row['id_update'];
   $row['is_read'] = (int) $row['is_read'];

   if ($row['is_read'] === 0) {
      $unread_count++;
   }

   $data[] = $row;
}

echo json_encode([
   'status' => 'success',
   'message' => 'Data log update berhasil diambil.',
   'unread_count' => $unread_count,
   'total' => count($data),
   'data' => $data
], JSON_UNESCAPED_UNICODE);
