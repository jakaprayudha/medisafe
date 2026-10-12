<?php
if (PHP_SAPI !== 'cli') {
   http_response_code(405);
   exit('CLI only');
}
session_start();
$_SESSION['uid_user'] = 'marker-history-test';
require_once __DIR__ . '/../database/connect.php';
require_once __DIR__ . '/../controller/visit/operationMarkerHistory.php';

function expectMarkerHistory(bool $condition, string $message): void
{
   if (!$condition) throw new RuntimeException($message);
}

$created = [];
try {
   $koneksi->query("CREATE TEMPORARY TABLE pasien_visit (
      visit_ID VARCHAR(50), id_customer INT, id_patient VARCHAR(50), visit_status INT
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
   $created[] = 'pasien_visit';
   $koneksi->query("CREATE TEMPORARY TABLE operation_markers (
      id INT, visit_ID VARCHAR(50), id_customer INT, marker_type VARCHAR(20),
      notes TEXT, created_by VARCHAR(100), created_at DATETIME, image_png BLOB
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
   $created[] = 'operation_markers';
   $koneksi->query("INSERT INTO pasien_visit VALUES
      ('TEST-VISIT-A', 1, 'TEST-PATIENT', 0), ('TEST-VISIT-B', 1, 'TEST-PATIENT', NULL),
      ('TEST-CANCELED', 1, 'TEST-PATIENT', 99), ('TEST-OTHER-CLINIC', 2, 'TEST-PATIENT', 0),
      ('TEST-OTHER-PATIENT', 1, 'OTHER-PATIENT', 0)");
   $stmt = $koneksi->prepare("INSERT INTO operation_markers VALUES (?, ?, ?, 'gigi', ?, 'Test recorder', '2026-10-12 19:00:00', ?)");
   foreach ([
      [1, 'TEST-VISIT-A', 1, 'First version', 'PNG'],
      [2, 'TEST-VISIT-A', 1, '<script>alert("x")</script>', 'PNG'],
      [3, 'TEST-VISIT-B', 1, 'No image', ''],
      [4, 'TEST-CANCELED', 1, 'Canceled', 'PNG'],
      [5, 'TEST-OTHER-CLINIC', 2, 'Other clinic', 'PNG'],
      [6, 'TEST-OTHER-PATIENT', 1, 'Other patient', 'PNG'],
   ] as [$id, $visit, $clinic, $notes, $image]) {
      $stmt->bind_param('isiss', $id, $visit, $clinic, $notes, $image);
      $stmt->execute();
   }
   $history = operationMarkerPatientHistory($koneksi, 'TEST-PATIENT', 1);
   expectMarkerHistory(count($history) === 2, 'Only noncanceled visits in clinic for this patient');
   expectMarkerHistory(array_column($history['TEST-VISIT-A'], 'id') === [2, 1], 'All versions, newest first');
   expectMarkerHistory(!isset($history['TEST-CANCELED'], $history['TEST-OTHER-CLINIC'], $history['TEST-OTHER-PATIENT']), 'Excluded records');
   expectMarkerHistory(!array_key_exists('image_png', $history['TEST-VISIT-A'][0]), 'Do not preload image blobs into history');
   expectMarkerHistory(operationMarkerHistoryHtml([], 'TEST-RM') === '', 'No section without documents');
   $html = operationMarkerHistoryHtml($history['TEST-VISIT-A'], 'TEST-RM');
   expectMarkerHistory(substr_count($html, '<article ') === 2, 'Render every saved version');
   expectMarkerHistory(substr_count($html, '<img ') === 2, 'Render saved images');
   expectMarkerHistory(str_contains($html, 'Gigi &amp; Mulut'), 'Template label');
   expectMarkerHistory(!str_contains($html, '<script>') && str_contains($html, '&lt;script&gt;'), 'Escape clinical notes');
   expectMarkerHistory(str_contains($html, 'visit_ID=TEST-VISIT-A&amp;id=2'), 'Image URL tied to document visit');
   expectMarkerHistory(str_contains($html, 'no=TEST-VISIT-A&amp;rm=TEST-RM&amp;id=2'), 'PDF URL tied to document visit');
   expectMarkerHistory(!str_contains(operationMarkerHistoryHtml($history['TEST-VISIT-B'], 'TEST-RM'), '<img '), 'Skip missing image');
   expectMarkerHistory(operationMarkerPatientHistory($koneksi, 'TEST-PATIENT', 3) === [], 'No foreign clinic documents');
   if (in_array('--html', $argv, true)) {
      echo '<!doctype html><html><head><meta charset="utf-8"><base href="https://medisafe.test/">';
      echo '<link rel="stylesheet" href="assets/css/styles.min.css"></head><body>';
      echo $html;
      echo '</body></html>';
   } else {
      echo "PASS: operation marker history grouping, versions, images, escaping, visit/clinic/patient scoping and canceled filtering. Temporary fixtures only.\n";
   }
} finally {
   foreach (array_reverse($created) as $table) {
      $koneksi->query("DROP TEMPORARY TABLE $table");
   }
   $koneksi->close();
}
