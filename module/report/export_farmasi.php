<?php

require_once '../../vendor/autoload.php';
include '../../database/connect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;


// ============================================================
// SESSION
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {

    http_response_code(403);

    exit('Session tidak ditemukan');
}


// ============================================================
// FILTER TANGGAL
// ============================================================

$fromDate = $_GET['fromDate'] ?? date('Y-m-d');
$toDate   = $_GET['toDate'] ?? date('Y-m-d');


// ============================================================
// VALIDASI TANGGAL
// ============================================================

$fromDateValid = DateTime::createFromFormat('Y-m-d', $fromDate);
$toDateValid   = DateTime::createFromFormat('Y-m-d', $toDate);

if (
    !$fromDateValid ||
    !$toDateValid ||
    $fromDateValid->format('Y-m-d') !== $fromDate ||
    $toDateValid->format('Y-m-d') !== $toDate
) {

    exit('Format tanggal tidak valid');
}


if ($fromDate > $toDate) {

    exit('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
}


// ============================================================
// QUERY FARMASI
// ============================================================

$sql = "

SELECT

    /* ========================================================
       MASTER OBAT
       ======================================================== */

    p.id_pharmacy,

    p.pharmacy_code,

    p.pharmacy_name_generic,

    p.pharmacy_name_trade,

    p.pharmacy_category,

    p.pharmacy_unit,


    /* ========================================================
       STOCK MIN / MAX
       ======================================================== */

    COALESCE(p.stok_min, 0) AS stok_min,

    COALESCE(p.stok_max, 0) AS stok_max,


    /* ========================================================
       STOCK SAAT INI
       ======================================================== */

    COALESCE(p.pharmacy_stock, 0) AS stok_saat_ini,


    /* ========================================================
       HARGA BELI
       ======================================================== */

    COALESCE(p.pharmacy_price_buy, 0) AS pharmacy_price_buy,


    /* ========================================================
       BARANG MASUK
       ======================================================== */

    COALESCE(

        (

            SELECT SUM(pb.buy_qty)

            FROM pharmacy_buy_detail pb

            WHERE

                (
                    pb.buy_item = p.pharmacy_code
                    OR pb.buy_item = p.pharmacy_name_generic
                    OR pb.buy_item = p.pharmacy_name_trade
                )

                AND pb.buy_status = 1

                AND pb.created_at >= CONCAT(?, ' 00:00:00')

                AND pb.created_at < DATE_ADD(?, INTERVAL 1 DAY)

        ),

        0

    ) AS stok_masuk,


    /* ========================================================
       BARANG KELUAR
       ======================================================== */

    COALESCE(

        (

            SELECT SUM(pd.qty)

            FROM permintaan_pharmacy_details pd

            WHERE

                pd.id_pharmacy = p.id_pharmacy

                AND pd.created_at >= CONCAT(?, ' 00:00:00')

                AND pd.created_at < DATE_ADD(?, INTERVAL 1 DAY)

        ),

        0

    ) AS stok_keluar


FROM ms_pharmacy p


/* ============================================================
   FILTER CUSTOMER
   ============================================================ */

WHERE

    p.id_customer = ?

    AND p.pharmacy_status = 1


/* ============================================================
   ORDER
   ============================================================ */

ORDER BY

    p.pharmacy_name_generic ASC

";


// ============================================================
// PREPARE
// ============================================================

$stmt = $koneksi->prepare($sql);

if (!$stmt) {

    exit('Prepare query gagal: ' .
        $koneksi->error);
}


// ============================================================
// BIND PARAMETER
// ============================================================

$stmt->bind_param(
    "sssss",
    $fromDate,
    $toDate,
    $fromDate,
    $toDate,
    $id_customer
);


// ============================================================
// EXECUTE
// ============================================================

if (!$stmt->execute()) {

    exit('Execute query gagal: ' .
        $stmt->error);
}


$result = $stmt->get_result();


// ============================================================
// SPREADSHEET
// ============================================================

$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Stok Farmasi');


// ============================================================
// JUDUL
// ============================================================

$sheet->mergeCells('A1:L1');

$sheet->setCellValue(
    'A1',
    'LAPORAN STOK FARMASI'
);

$sheet->getStyle('A1')->applyFromArray([

    'font' => [
        'bold' => true,
        'size' => 16
    ],

    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]

]);

$sheet->getRowDimension(1)->setRowHeight(25);


// ============================================================
// PERIODE
// ============================================================

$sheet->mergeCells('A2:L2');

$sheet->setCellValue(
    'A2',
    "Periode: $fromDate s/d $toDate"
);

$sheet->getStyle('A2')->applyFromArray([

    'font' => [
        'bold' => true,
        'size' => 11
    ],

    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ]

]);


// ============================================================
// HEADER
// ============================================================

$headers = [

    'Status',
    'Kode Obat',
    'Nama Obat',
    'Kategori',
    'Satuan',
    'Stok Awal',
    'Masuk',
    'Keluar',
    'Stok Akhir',
    'Stok Min',
    'Stok Max',
    'Nilai Stok'

];


$col = 'A';

foreach ($headers as $header) {

    $sheet->setCellValue(
        $col . '4',
        $header
    );

    $col++;
}


// ============================================================
// HEADER STYLE
// ============================================================

$headerStyle = [

    'font' => [
        'bold' => true,
        'color' => [
            'rgb' => 'FFFFFF'
        ]
    ],

    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => '2563EB'
        ]
    ],

    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],

    'borders' => [

        'allBorders' => [

            'borderStyle' => Border::BORDER_THIN,

            'color' => [
                'rgb' => 'FFFFFF'
            ]

        ]

    ]

];


$sheet
    ->getStyle('A4:L4')
    ->applyFromArray($headerStyle);


$sheet->getRowDimension(4)->setRowHeight(25);


// ============================================================
// DATA
// ============================================================

$rowNum = 5;


while ($row = $result->fetch_assoc()) {


    // ========================================================
    // DATA
    // ========================================================

    $stokSaatIni =
        (float) ($row['stok_saat_ini'] ?? 0);

    $stokMasuk =
        (float) ($row['stok_masuk'] ?? 0);

    $stokKeluar =
        (float) ($row['stok_keluar'] ?? 0);

    $stokMin =
        (float) ($row['stok_min'] ?? 0);

    $stokMax =
        (float) ($row['stok_max'] ?? 0);

    $hargaBeli =
        (float) ($row['pharmacy_price_buy'] ?? 0);


    // ========================================================
    // STOK AKHIR
    // ========================================================
    //
    // Karena pharmacy_stock adalah stok saat ini,
    // kita gunakan sebagai stok akhir.
    //

    $stokAkhir = $stokSaatIni;


    // ========================================================
    // STOK AWAL
    // ========================================================
    //
    // Stok Awal + Masuk - Keluar = Stok Akhir
    //
    // Stok Awal = Stok Akhir - Masuk + Keluar
    //

    $stokAwal =
        $stokAkhir
        - $stokMasuk
        + $stokKeluar;


    // Jangan sampai negatif

    if ($stokAwal < 0) {
        $stokAwal = 0;
    }


    // ========================================================
    // NILAI STOK
    // ========================================================

    $nilaiStok =
        $stokAkhir * $hargaBeli;


    // ========================================================
    // STATUS
    // ========================================================

    if ($stokAkhir <= 0) {

        $status = 'Habis';

        $statusColor = 'FFC7CE';
    } elseif (
        $stokMin > 0 &&
        $stokAkhir < $stokMin
    ) {

        $status = 'Di Bawah Minimum';

        $statusColor = 'FFF2CC';
    } elseif (
        $stokMax > 0 &&
        $stokAkhir > $stokMax
    ) {

        $status = 'Di Atas Maksimum';

        $statusColor = 'DDEBF7';
    } else {

        $status = 'Normal';

        $statusColor = 'C6EFCE';
    }


    // ========================================================
    // WRITE DATA
    // ========================================================

    $sheet->setCellValue(
        'A' . $rowNum,
        $status
    );

    $sheet->setCellValue(
        'B' . $rowNum,
        $row['pharmacy_code'] ?? '-'
    );


    // Nama obat

    $namaObat =
        $row['pharmacy_name_generic'] ?? '-';

    if (!empty($row['pharmacy_name_trade'])) {

        $namaObat .=
            ' (' .
            $row['pharmacy_name_trade'] .
            ')';
    }


    $sheet->setCellValue(
        'C' . $rowNum,
        $namaObat
    );


    $sheet->setCellValue(
        'D' . $rowNum,
        $row['pharmacy_category'] ?? '-'
    );


    $sheet->setCellValue(
        'E' . $rowNum,
        $row['pharmacy_unit'] ?? '-'
    );


    // ========================================================
    // STOCK
    // ========================================================

    $sheet->setCellValue(
        'F' . $rowNum,
        $stokAwal
    );

    $sheet->setCellValue(
        'G' . $rowNum,
        $stokMasuk
    );

    $sheet->setCellValue(
        'H' . $rowNum,
        $stokKeluar
    );

    $sheet->setCellValue(
        'I' . $rowNum,
        $stokAkhir
    );

    $sheet->setCellValue(
        'J' . $rowNum,
        $stokMin
    );

    $sheet->setCellValue(
        'K' . $rowNum,
        $stokMax
    );


    // ========================================================
    // NILAI STOCK
    // ========================================================

    $sheet->setCellValue(
        'L' . $rowNum,
        $nilaiStok
    );


    // ========================================================
    // FORMAT ANGKA
    // ========================================================

    $sheet
        ->getStyle("F{$rowNum}:K{$rowNum}")
        ->getNumberFormat()
        ->setFormatCode('#,##0');


    $sheet
        ->getStyle("L{$rowNum}")
        ->getNumberFormat()
        ->setFormatCode(
            '"Rp" #,##0'
        );


    // ========================================================
    // STATUS COLOR
    // ========================================================

    $sheet
        ->getStyle("A{$rowNum}")
        ->getFill()
        ->setFillType(Fill::FILL_SOLID);

    $sheet
        ->getStyle("A{$rowNum}")
        ->getFill()
        ->getStartColor()
        ->setRGB($statusColor);


    // ========================================================
    // ALIGNMENT
    // ========================================================

    $sheet
        ->getStyle("A{$rowNum}:L{$rowNum}")
        ->getAlignment()
        ->setVertical(
            Alignment::VERTICAL_CENTER
        );


    $sheet
        ->getStyle("A{$rowNum}")
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        );


    $sheet
        ->getStyle("E{$rowNum}")
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_CENTER
        );


    $sheet
        ->getStyle("F{$rowNum}:L{$rowNum}")
        ->getAlignment()
        ->setHorizontal(
            Alignment::HORIZONTAL_RIGHT
        );


    // ========================================================
    // BORDER
    // ========================================================

    $sheet
        ->getStyle("A{$rowNum}:L{$rowNum}")
        ->applyFromArray([

            'borders' => [

                'allBorders' => [

                    'borderStyle' =>
                    Border::BORDER_THIN,

                    'color' => [
                        'rgb' => 'CCCCCC'
                    ]

                ]

            ]

        ]);


    // ========================================================
    // ZEBRA ROW
    // ========================================================

    if ($rowNum % 2 === 0) {

        $sheet
            ->getStyle("B{$rowNum}:L{$rowNum}")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID);

        $sheet
            ->getStyle("B{$rowNum}:L{$rowNum}")
            ->getFill()
            ->getStartColor()
            ->setRGB('F8FAFC');
    }


    $rowNum++;
}


// ============================================================
// TOTAL
// ============================================================

$totalRow = $rowNum;


$sheet->setCellValue(
    "A{$totalRow}",
    'TOTAL'
);

$sheet->mergeCells(
    "A{$totalRow}:E{$totalRow}"
);


// Total stok masuk

$sheet->setCellValue(
    "G{$totalRow}",
    "=SUM(G5:G" . ($totalRow - 1) . ")"
);


// Total stok keluar

$sheet->setCellValue(
    "H{$totalRow}",
    "=SUM(H5:H" . ($totalRow - 1) . ")"
);


// Total stok akhir

$sheet->setCellValue(
    "I{$totalRow}",
    "=SUM(I5:I" . ($totalRow - 1) . ")"
);


// Total nilai stok

$sheet->setCellValue(
    "L{$totalRow}",
    "=SUM(L5:L" . ($totalRow - 1) . ")"
);


// ============================================================
// TOTAL STYLE
// ============================================================

$sheet
    ->getStyle("A{$totalRow}:L{$totalRow}")
    ->applyFromArray([

        'font' => [
            'bold' => true
        ],

        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => 'E2E8F0'
            ]
        ],

        'borders' => [

            'allBorders' => [

                'borderStyle' =>
                Border::BORDER_THIN,

                'color' => [
                    'rgb' => '94A3B8'
                ]

            ]

        ]

    ]);


// ============================================================
// FORMAT TOTAL
// ============================================================

$sheet
    ->getStyle("G{$totalRow}:K{$totalRow}")
    ->getNumberFormat()
    ->setFormatCode('#,##0');


$sheet
    ->getStyle("L{$totalRow}")
    ->getNumberFormat()
    ->setFormatCode(
        '"Rp" #,##0'
    );


// ============================================================
// WIDTH
// ============================================================

$widths = [

    'A' => 22,
    'B' => 18,
    'C' => 40,
    'D' => 22,
    'E' => 15,
    'F' => 15,
    'G' => 15,
    'H' => 15,
    'I' => 15,
    'J' => 15,
    'K' => 15,
    'L' => 22

];


foreach ($widths as $column => $width) {

    $sheet
        ->getColumnDimension($column)
        ->setWidth($width);
}


// ============================================================
// FREEZE HEADER
// ============================================================

$sheet->freezePane('A5');


// ============================================================
// AUTO FILTER
// ============================================================

$sheet->setAutoFilter(
    "A4:L" . ($totalRow - 1)
);


// ============================================================
// DOWNLOAD
// ============================================================

$filename =
    'laporan_stok_farmasi_' .
    $fromDate .
    '_' .
    $toDate .
    '.xlsx';


header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment; filename="' .
        $filename .
        '"'
);

header(
    'Cache-Control: max-age=0'
);

header(
    'Pragma: public'
);


// ============================================================
// WRITE EXCEL
// ============================================================

$writer = new Xlsx($spreadsheet);

$writer->save('php://output');

exit;
