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
    SELECT pv.visit_status, pv.noKartu, ms.patient_nik, pv.visit_date, pv.visit_time,
           pv.patient_name_pcare, pv.id_doctor, pv.id_poli, mp.provider_name
    FROM pasien_visit pv
    LEFT JOIN ms_provider mp ON mp.id_provider = pv.id_provider
    LEFT JOIN ms_patient ms  ON ms.id_patient  = pv.id_patient
    WHERE pv.id_customer = '$id_customer'
      AND DATE(pv.visit_date) BETWEEN '$fromDate' AND '$toDate'
";
if (!empty($doctor))   $sql .= " AND pv.id_doctor   = '$doctor'";
if (!empty($provider)) $sql .= " AND pv.id_provider = '$provider'";
$sql .= " ORDER BY pv.visit_date DESC, pv.visit_time DESC";

$query = mysqli_query($koneksi, $sql);
$rows  = [];
while ($row = mysqli_fetch_assoc($query)) {
    $rows[] = $row;
}

// ── Spreadsheet ───────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Appointment');

// Judul
$sheet->mergeCells('A1:H1');
$sheet->setCellValue('A1', 'LAPORAN APPOINTMENT');
$sheet->getStyle('A1')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 14],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Sub-judul periode
$sheet->mergeCells('A2:H2');
$sheet->setCellValue('A2', "Periode: $fromDate s/d $toDate");
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Header kolom
$headers = ['Status', 'No. BPJS', 'NIK', 'Tanggal', 'Nama Pasien', 'Dokter', 'Poli', 'Jenis Bayar'];
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
$sheet->getStyle('A3:H3')->applyFromArray($headerStyle);

// Data
$rowNum = 4;
foreach ($rows as $r) {
    $status   = $r['visit_status'] == 4 ? 'Selesai' : 'Belum Selesai';
    $tanggal  = $r['visit_date'] . ' ' . ($r['visit_time'] ?? '');

    $sheet->setCellValue('A' . $rowNum, $status);
    $sheet->setCellValue('B' . $rowNum, $r['noKartu']);
    $sheet->setCellValue('C' . $rowNum, $r['patient_nik']);
    $sheet->setCellValue('D' . $rowNum, trim($tanggal));
    $sheet->setCellValue('E' . $rowNum, $r['patient_name_pcare']);
    $sheet->setCellValue('F' . $rowNum, $r['id_doctor']);
    $sheet->setCellValue('G' . $rowNum, $r['id_poli']);
    $sheet->setCellValue('H' . $rowNum, $r['provider_name'] ?? '-');

    // Warna baris zebra
    if ($rowNum % 2 === 0) {
        $sheet->getStyle("A{$rowNum}:H{$rowNum}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F0F4FF');
    }

    // Border tiap baris
    $sheet->getStyle("A{$rowNum}:H{$rowNum}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
    ]);

    $rowNum++;
}

// Auto-width kolom
foreach (range('A', 'H') as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

// ── Output ────────────────────────────────────────────────────
$filename = 'laporan_appointment_' . $fromDate . '_' . $toDate . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
