<?php
require_once '../../vendor/autoload.php';
include '../../database/connect.php';

if (session_status() === PHP_SESSION_NONE) session_start();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$id_customer = $_SESSION['id_customer'] ?? null;
if (!$id_customer) {
    http_response_code(403);
    exit('Session tidak ditemukan');
}

$fromDate = $_GET['fromDate'] ?? date('Y-m-d');
$toDate   = $_GET['toDate']   ?? date('Y-m-d');
$doctor   = $_GET['doctor']   ?? '';
$provider = $_GET['provider'] ?? '';

$sql = "
    SELECT
        pv.id_doctor,
        COUNT(DISTINCT pv.visit_ID) AS total_kunjungan,
        SUM(COALESCE(b.fee_dokter,  0)) AS fee_dokter,
        SUM(COALESCE(b.fee_perawat, 0)) AS fee_perawat,
        SUM(COALESCE(b.fee_admin,   0)) AS fee_admin
    FROM pasien_visit pv
    LEFT JOIN (
        SELECT pb.id_visit,
               SUM(COALESCE(mt.tarif_share_doctor, 0)) AS fee_dokter,
               SUM(COALESCE(mt.tarif_share_nurse,  0)) AS fee_perawat,
               SUM(COALESCE(mt.tarif_share_admin,  0)) AS fee_admin
        FROM pasien_billing pb
        LEFT JOIN ms_tarif mt ON TRIM(LOWER(mt.tarif_name)) = TRIM(LOWER(pb.billing_item))
        GROUP BY pb.id_visit
    ) b ON b.id_visit = pv.visit_ID
    WHERE pv.id_customer = ?
      AND DATE(pv.visit_date) BETWEEN ? AND ?
";

$params = [$id_customer, $fromDate, $toDate];
$types  = "iss";

if (!empty($provider)) { $sql .= " AND pv.id_provider = ?"; $params[] = $provider; $types .= "i"; }
if (!empty($doctor))   { $sql .= " AND pv.id_doctor   = ?"; $params[] = $doctor;   $types .= "s"; }

$sql .= " GROUP BY pv.id_doctor ORDER BY MAX(pv.visit_date) DESC";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();

// ── Spreadsheet ───────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Tindakan (Prosedur)');

$sheet->mergeCells('A1:E1');
$sheet->setCellValue('A1', 'LAPORAN TINDAKAN (PROSEDUR)');
$sheet->getStyle('A1')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 14],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

$sheet->mergeCells('A2:E2');
$sheet->setCellValue('A2', "Periode: $fromDate s/d $toDate");
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$headers = ['Dokter', 'Total Pasien', 'Fee Dokter', 'Fee Perawat', 'Fee Admin'];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '3', $h);
    $col++;
}

$headerStyle = [
    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];
$sheet->getStyle('A3:E3')->applyFromArray($headerStyle);

$rowNum       = 4;
$totPasien    = 0;
$totFeeDokter = 0;
$totFeePerawat = 0;
$totFeeAdmin  = 0;

foreach ($rows as $r) {
    $sheet->setCellValue('A' . $rowNum, $r['id_doctor']);
    $sheet->setCellValue('B' . $rowNum, (int)$r['total_kunjungan']);
    $sheet->setCellValue('C' . $rowNum, (float)$r['fee_dokter']);
    $sheet->setCellValue('D' . $rowNum, (float)$r['fee_perawat']);
    $sheet->setCellValue('E' . $rowNum, (float)$r['fee_admin']);

    // Format rupiah untuk kolom C, D, E
    $sheet->getStyle("C{$rowNum}:E{$rowNum}")->getNumberFormat()
        ->setFormatCode('"Rp "#,##0');

    if ($rowNum % 2 === 0) {
        $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F0F4FF');
    }
    $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
    ]);

    $totPasien     += (int)$r['total_kunjungan'];
    $totFeeDokter  += (float)$r['fee_dokter'];
    $totFeePerawat += (float)$r['fee_perawat'];
    $totFeeAdmin   += (float)$r['fee_admin'];
    $rowNum++;
}

// Baris total
$sheet->setCellValue('A' . $rowNum, 'TOTAL');
$sheet->setCellValue('B' . $rowNum, $totPasien);
$sheet->setCellValue('C' . $rowNum, $totFeeDokter);
$sheet->setCellValue('D' . $rowNum, $totFeePerawat);
$sheet->setCellValue('E' . $rowNum, $totFeeAdmin);

$sheet->getStyle("C{$rowNum}:E{$rowNum}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
$sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '198754']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
]);

foreach (range('A', 'E') as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

$filename = 'laporan_prosedur_' . $fromDate . '_' . $toDate . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
