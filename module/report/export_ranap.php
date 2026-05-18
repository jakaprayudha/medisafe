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
$status   = $_GET['status']   ?? '';

$sql = "
    SELECT pv.visit_status, pv.noKartu, ms.patient_nik AS nik,
           pv.visit_date, pv.visit_time, pv.patient_name_pcare,
           pv.id_doctor, mp.provider_name,
           CASE
               WHEN EXISTS (SELECT 1 FROM resume_medis rm
                            WHERE rm.visit_ID = pv.visit_ID
                              AND rm.tanggal_pulang IS NOT NULL
                              AND rm.tanggal_pulang != '') THEN 'Selesai'
               WHEN EXISTS (SELECT 1 FROM visit_cppt vc
                            WHERE vc.visit_ID = pv.visit_ID) THEN 'Pemeriksaan'
               ELSE 'Belum Dilayani'
           END AS status_label
    FROM pasien_visit pv
    LEFT JOIN ms_provider mp ON mp.id_provider = pv.id_provider
    LEFT JOIN ms_patient ms  ON ms.id_patient  = pv.id_patient
    WHERE pv.id_customer = '$id_customer'
      AND pv.status_rawatinap = 1
      AND DATE(pv.visit_date) BETWEEN '$fromDate' AND '$toDate'
";
if (!empty($doctor))   $sql .= " AND pv.id_doctor   = '$doctor'";
if (!empty($provider)) $sql .= " AND pv.id_provider = '$provider'";
if ($status === 'Belum') {
    $sql .= " AND NOT EXISTS (SELECT 1 FROM visit_cppt vc WHERE vc.visit_ID = pv.visit_ID)
              AND NOT EXISTS (SELECT 1 FROM resume_medis rm WHERE rm.visit_ID = pv.visit_ID AND rm.tanggal_pulang IS NOT NULL AND rm.tanggal_pulang != '')";
} elseif ($status === 'Pemeriksaan') {
    $sql .= " AND EXISTS (SELECT 1 FROM visit_cppt vc WHERE vc.visit_ID = pv.visit_ID)
              AND NOT EXISTS (SELECT 1 FROM resume_medis rm WHERE rm.visit_ID = pv.visit_ID AND rm.tanggal_pulang IS NOT NULL AND rm.tanggal_pulang != '')";
} elseif ($status === 'Selesai') {
    $sql .= " AND EXISTS (SELECT 1 FROM resume_medis rm WHERE rm.visit_ID = pv.visit_ID AND rm.tanggal_pulang IS NOT NULL AND rm.tanggal_pulang != '')";
}
$sql .= " ORDER BY pv.visit_date DESC, pv.visit_time DESC";

$query = mysqli_query($koneksi, $sql);
$rows  = [];
while ($row = mysqli_fetch_assoc($query)) {
    $rows[] = $row;
}

// ── Spreadsheet ───────────────────────────────────────────────
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rawat Inap');

$sheet->mergeCells('A1:G1');
$sheet->setCellValue('A1', 'LAPORAN RAWAT INAP');
$sheet->getStyle('A1')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 14],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

$sheet->mergeCells('A2:G2');
$sheet->setCellValue('A2', "Periode: $fromDate s/d $toDate");
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$headers = ['Status', 'No. BPJS', 'NIK', 'Tanggal', 'Nama Pasien', 'Dokter', 'Jenis Bayar'];
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
$sheet->getStyle('A3:G3')->applyFromArray($headerStyle);

$rowNum = 4;
foreach ($rows as $r) {
    $tanggal = trim($r['visit_date'] . ' ' . ($r['visit_time'] ?? ''));

    $sheet->setCellValue('A' . $rowNum, $r['status_label']);
    $sheet->setCellValue('B' . $rowNum, $r['noKartu'] ?? '-');
    $sheet->setCellValue('C' . $rowNum, $r['nik'] ?? '-');
    $sheet->setCellValue('D' . $rowNum, $tanggal);
    $sheet->setCellValue('E' . $rowNum, $r['patient_name_pcare'] ?? '-');
    $sheet->setCellValue('F' . $rowNum, $r['id_doctor'] ?? '-');
    $sheet->setCellValue('G' . $rowNum, $r['provider_name'] ?? '-');

    if ($rowNum % 2 === 0) {
        $sheet->getStyle("A{$rowNum}:G{$rowNum}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F0F4FF');
    }
    $sheet->getStyle("A{$rowNum}:G{$rowNum}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
    ]);

    $rowNum++;
}

foreach (range('A', 'G') as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

$filename = 'laporan_rawat_inap_' . $fromDate . '_' . $toDate . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
