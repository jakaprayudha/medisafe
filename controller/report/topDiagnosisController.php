<?php

require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/topDiagnosisService.php';

$format = $_GET['format'] ?? 'json';
if (!in_array($format, ['json', 'pdf', 'excel'], true)) {
   http_response_code(400);
   exit('Format laporan tidak valid.');
}
if (empty($_SESSION['id_customer']) || ($_SESSION['roles'] ?? '') !== 'admin') {
   http_response_code(403);
   header('Content-Type: application/json; charset=utf-8');
   echo json_encode(['status' => 'error', 'message' => 'Akses laporan tidak diizinkan.', 'data' => []]);
   exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
   http_response_code(405);
   exit('Metode tidak diizinkan.');
}

try {
   $report = topDiagnosisReport($koneksi, (string) $_SESSION['id_customer'], $_GET);
   $title = '10 Diagnosis ICD-10 Tertinggi';
   $period = 'Periode: ' . $report['fromDate'] . ' s/d ' . $report['toDate'];
   $filename = '10_diagnosis_icd10_' . $report['fromDate'] . '_' . $report['toDate'];
   if ($format === 'json') {
      header('Content-Type: application/json; charset=utf-8');
      header('Cache-Control: no-store');
      echo json_encode(array_merge(['status' => 'success'], $report), JSON_UNESCAPED_UNICODE);
      exit;
   }

   require_once __DIR__ . '/../../vendor/autoload.php';
   if ($format === 'excel') {
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('10 Diagnosis ICD-10');
      $sheet->mergeCells('A1:D1')->setCellValue('A1', $title);
      $sheet->mergeCells('A2:D2')->setCellValue('A2', $period);
      $sheet->mergeCells('A3:D3')->setCellValue('A3', 'Jumlah kunjungan berdasarkan diagnosis utama; kunjungan batal tidak dihitung.');
      $sheet->fromArray(['Peringkat', 'Kode ICD-10', 'Diagnosis', 'Jumlah Kunjungan'], null, 'A5');
      foreach ($report['data'] as $index => $row) {
         $excelRow = $index + 6;
         $sheet->setCellValue('A' . $excelRow, $row['rank']);
         $sheet->setCellValueExplicit('B' . $excelRow, $row['icd_code'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
         $sheet->setCellValueExplicit('C' . $excelRow, $row['diagnosis_name'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
         $sheet->setCellValue('D' . $excelRow, $row['total_visits']);
      }
      if (!$report['data']) {
         $sheet->mergeCells('A6:D6')->setCellValue('A6', 'Tidak ada data diagnosis pada periode ini.');
      }
      $sheet->getStyle('A1:D1')->getFont()->setBold(true)->setSize(14);
      $sheet->getStyle('A5:D5')->getFont()->setBold(true);
      $sheet->getColumnDimension('A')->setWidth(12);
      $sheet->getColumnDimension('B')->setWidth(18);
      $sheet->getColumnDimension('C')->setWidth(65);
      $sheet->getColumnDimension('D')->setWidth(22);
      $sheet->getStyle('C6:C16')->getAlignment()->setWrapText(true);
      $sheet->freezePane('A6');
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
      header('Cache-Control: no-store');
      (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
      exit;
   }

   $escape = static function ($value) {
      return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
   };
   $tableRows = '';
   foreach ($report['data'] as $row) {
      $tableRows .= '<tr><td>' . $row['rank'] . '</td><td>' . $escape($row['icd_code']) . '</td><td>' .
         $escape($row['diagnosis_name']) . '</td><td class="number">' . $row['total_visits'] . '</td></tr>';
   }
   if ($tableRows === '') {
      $tableRows = '<tr><td colspan="4">Tidak ada data diagnosis pada periode ini.</td></tr>';
   }
   $html = '<!doctype html><html lang="id"><head><meta charset="utf-8"><style>
      body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
      h1 { font-size: 18px; } table { width: 100%; border-collapse: collapse; margin-top: 18px; }
      th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
      th { background: #eee; } .number { text-align: right; }
      </style></head><body><h1>' . $title . '</h1><p>' . $escape($period) . '</p>
      <p>Jumlah kunjungan berdasarkan diagnosis utama; kunjungan batal tidak dihitung.</p>
      <table><thead><tr><th>Peringkat</th><th>Kode ICD-10</th><th>Diagnosis</th><th>Jumlah Kunjungan</th></tr></thead>
      <tbody>' . $tableRows . '</tbody></table><p>Total kunjungan pada 10 diagnosis: ' . $report['total_top10'] . '</p>
      <p>Dicetak: ' . date('d/m/Y H:i') . '</p></body></html>';
   $options = new \Dompdf\Options();
   $options->set('isRemoteEnabled', false);
   $pdf = new \Dompdf\Dompdf($options);
   $pdf->loadHtml($html);
   $pdf->setPaper('A4', 'portrait');
   $pdf->render();
   $pdf->stream($filename . '.pdf', ['Attachment' => false]);
} catch (Throwable $error) {
   $invalid = $error instanceof InvalidArgumentException;
   http_response_code($invalid ? 400 : 500);
   if (!$invalid) error_log('Top diagnosis report failed: ' . get_class($error) . ' (' . $error->getCode() . ')');
   $message = $invalid ? $error->getMessage() : 'Gagal membuat laporan diagnosis. Silakan hubungi administrator.';
   if ($format === 'json') {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['status' => 'error', 'message' => $message, 'data' => []]);
   } else {
      header('Content-Type: text/plain; charset=utf-8');
      echo $message;
   }
}