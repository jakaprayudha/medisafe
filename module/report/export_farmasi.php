<?php
// Excel Laporan Stok Farmasi - kolom, urutan, filter & ringkasan sama dengan module/admin/rpt_farmasi
require_once '../../vendor/autoload.php';
include '../../database/connect.php';
require_once __DIR__ . '/../../controller/report/farmasiStokService.php';
mysqli_report(MYSQLI_REPORT_OFF);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$id_customer = $_SESSION['id_customer'] ?? null;
if (!$id_customer) {
    http_response_code(403);
    exit('Session tidak ditemukan');
}

$fromDate = (string) ($_GET['fromDate'] ?? date('Y-m-d'));
$toDate = (string) ($_GET['toDate'] ?? date('Y-m-d'));
if (($error = farmasiStokValidasiPeriode($fromDate, $toDate)) !== null) {
    exit(htmlspecialchars($error));
}
$filter = farmasiStokParamFilter($_GET);

try {
    $hasil = farmasiStokReport($koneksi, (string) $id_customer, $fromDate, $toDate);
} catch (Throwable $e) {
    error_log('[export farmasi] ' . $e->getMessage());
    exit('Gagal mengambil data laporan farmasi');
}
$rows = farmasiStokFilter($hasil['data'], $filter);
$ringkasan = farmasiStokRingkasan($rows);

$stmt = $koneksi->prepare("SELECT clinic_name FROM setting_clinic WHERE id_customer = ? LIMIT 1");
$stmt->bind_param('s', $id_customer);
$stmt->execute();
$namaKlinik = $stmt->get_result()->fetch_assoc()['clinic_name'] ?? '';
$stmt->close();

// ================== SHEET ==================
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Stok Farmasi');
$spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

$kolomAkhir = 'K';
$fmtAngka = '#,##0';
$fmtDesimal = '#,##0.00';
// format per sel: bulat tanpa desimal, pecahan 2 desimal (setara tampilan layar)
$formatAngka = function (string $sel, $nilai) use ($sheet, $fmtAngka, $fmtDesimal) {
    $sheet->getStyle($sel)->getNumberFormat()->setFormatCode(fmod((float) $nilai, 1) == 0 ? $fmtAngka : $fmtDesimal);
};
$fmtRupiah = '"Rp "#,##0;-"Rp "#,##0;"Rp "0';
$tgl = fn($d) => date('d/m/Y', strtotime($d));

// kop
$sheet->mergeCells("A1:{$kolomAkhir}1")->setCellValue('A1', strtoupper($namaKlinik ?: 'Klinik'));
$sheet->mergeCells("A2:{$kolomAkhir}2")->setCellValue('A2', 'LAPORAN STOK FARMASI');
$sheet->mergeCells("A3:{$kolomAkhir}3")->setCellValue('A3', 'Periode ' . $tgl($fromDate) . ' s/d ' . $tgl($toDate) . ' • ' . farmasiStokLabelFilter($filter));
$sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

// ringkasan (sama dengan kartu di layar)
$ringkasanSel = [
    ['A5', 'Obat ditampilkan', $ringkasan['total_item'], $fmtAngka],
    ['C5', 'Stok habis', $ringkasan['habis'], $fmtAngka],
    ['E5', 'Di bawah minimum', $ringkasan['minimum'], $fmtAngka],
    ['G5', 'Di atas maksimum', $ringkasan['maximum'], $fmtAngka],
    ['I5', 'Masuk periode', $ringkasan['stok_masuk'], $fmtAngka],
    ['J5', 'Keluar periode', $ringkasan['stok_keluar'], $fmtAngka],
    ['K5', 'Nilai stok akhir', $ringkasan['nilai_stok'], $fmtRupiah],
];
foreach ($ringkasanSel as [$sel, $label, $nilai, $fmt]) {
    $kol = preg_replace('/\d/', '', $sel);
    $sheet->setCellValue($kol . '5', $label);
    $sheet->setCellValue($kol . '6', $nilai);
    $fmt === $fmtRupiah ? $sheet->getStyle($kol . '6')->getNumberFormat()->setFormatCode($fmt) : $formatAngka($kol . '6', $nilai);
    $sheet->getStyle($kol . '5')->getFont()->setSize(9)->getColor()->setRGB('666666');
    $sheet->getStyle($kol . '6')->getFont()->setBold(true)->setSize(12);
}

// header tabel (urutan sama dengan layar, plus No)
$barisHeader = 8;
$headers = ['No', 'Nama Obat', 'Kategori', 'Satuan', 'Stok Awal', 'Masuk', 'Keluar', 'Stok Akhir', 'Min / Max', 'Nilai Stok', 'Status'];
$sheet->fromArray($headers, null, 'A' . $barisHeader);
$sheet->getStyle("A{$barisHeader}:{$kolomAkhir}{$barisHeader}")->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);
$sheet->getRowDimension($barisHeader)->setRowHeight(22);

// data
$warnaStatus = [
    'habis' => ['FDE2DC', 'C0392B'], 'minimum' => ['FFF1D6', 'B7791F'],
    'maximum' => ['DFF3FF', '1F78B4'], 'normal' => ['DCF7F0', '13A58B'],
];
$baris = $barisHeader + 1;
foreach ($rows as $i => $r) {
    $sub = farmasiStokSubNama($r);
    $nama = trim((string) $r['pharmacy_name_generic']) ?: '-';
    $minMax = ((float) $r['stok_min'] || (float) $r['stok_max'])
        ? farmasiStokAngka($r['stok_min']) . ' / ' . farmasiStokAngka($r['stok_max']) : '-';

    $sheet->fromArray([
        $i + 1,
        $sub !== '' ? $nama . "\n" . $sub : $nama,
        farmasiStokIsi($r['pharmacy_category']) ?: '-',
        farmasiStokIsi($r['pharmacy_unit']) ?: '-',
        (float) $r['stok_awal'],
        (float) $r['stok_masuk'],
        (float) $r['stok_keluar'],
        (float) $r['stok_akhir'],
        $minMax,
        (float) $r['nilai_stok'],
        $r['status_stock'],
    ], null, 'A' . $baris, true);

    if ($sub !== '') {
        // nama generik tebal, dagang • kode abu-abu (seperti di layar)
        $rich = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
        $rich->createTextRun($nama)->getFont()->setBold(true)->setName('Arial')->setSize(10);
        $run = $rich->createTextRun("\n" . $sub);
        $run->getFont()->setName('Arial')->setSize(9)->getColor()->setRGB('777777');
        $sheet->getCell('B' . $baris)->setValue($rich);
    } else {
        $sheet->getStyle('B' . $baris)->getFont()->setBold(true);
    }

    foreach (['E' => 'stok_awal', 'F' => 'stok_masuk', 'G' => 'stok_keluar', 'H' => 'stok_akhir'] as $kol => $k) {
        $formatAngka($kol . $baris, $r[$k]);
    }
    [$bg, $fg] = $warnaStatus[$r['status_code']] ?? $warnaStatus['normal'];
    $sheet->getStyle('K' . $baris)->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => $fg]],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
    ]);
    if ((float) $r['stok_masuk'] > 0) $sheet->getStyle('F' . $baris)->getFont()->setBold(true)->getColor()->setRGB('13A58B');
    if ((float) $r['stok_keluar'] > 0) $sheet->getStyle('G' . $baris)->getFont()->setBold(true)->getColor()->setRGB('D9534F');
    $sheet->getStyle('H' . $baris)->getFont()->setBold(true);
    if ((float) $r['stok_akhir'] <= 0) $sheet->getStyle('H' . $baris)->getFont()->getColor()->setRGB('D9534F');
    $baris++;
}
if (!$rows) {
    $sheet->mergeCells("A{$baris}:{$kolomAkhir}{$baris}")->setCellValue('A' . $baris, 'Tidak ada data.');
    $sheet->getStyle('A' . $baris)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $baris++;
}
$barisAkhirData = $baris - 1;

// total (sama dengan baris total di layar)
$sheet->mergeCells("A{$baris}:E{$baris}")->setCellValue('A' . $baris, 'Total (sesuai filter)');
$sheet->setCellValue('F' . $baris, $ringkasan['stok_masuk']);
$sheet->setCellValue('G' . $baris, $ringkasan['stok_keluar']);
$sheet->setCellValue('J' . $baris, $ringkasan['nilai_stok']);
$sheet->getStyle("A{$baris}:{$kolomAkhir}{$baris}")->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2F7']],
]);
$sheet->getStyle('F' . $baris)->getFont()->getColor()->setRGB('13A58B');
$sheet->getStyle('G' . $baris)->getFont()->getColor()->setRGB('D9534F');

// format kolom
$formatAngka('F' . $baris, $ringkasan['stok_masuk']);
$formatAngka('G' . $baris, $ringkasan['stok_keluar']);
$sheet->getStyle("J" . ($barisHeader + 1) . ":J{$baris}")->getNumberFormat()->setFormatCode($fmtRupiah);
$sheet->getStyle("E" . ($barisHeader + 1) . ":J{$baris}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle("A" . ($barisHeader + 1) . ":A{$baris}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("D" . ($barisHeader + 1) . ":D{$baris}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("K" . ($barisHeader + 1) . ":K{$baris}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("B" . ($barisHeader + 1) . ":B{$baris}")->getAlignment()->setWrapText(true);
$sheet->getStyle("A" . ($barisHeader + 1) . ":{$kolomAkhir}{$baris}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
$sheet->getStyle("A{$barisHeader}:{$kolomAkhir}{$baris}")->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D0D7E2');

foreach (['A' => 6, 'B' => 38, 'C' => 16, 'D' => 10, 'E' => 12, 'F' => 12, 'G' => 12, 'H' => 12, 'I' => 14, 'J' => 16, 'K' => 18] as $kol => $lebar) {
    $sheet->getColumnDimension($kol)->setWidth($lebar);
}

// keterangan
$baris += 2;
$sheet->mergeCells("A{$baris}:{$kolomAkhir}{$baris}")->setCellValue(
    'A' . $baris,
    'Keterangan: Stok Awal = stok pada awal periode. Stok Akhir = Stok Awal + Masuk − Keluar, posisi di tanggal akhir periode. '
        . 'Masuk dari penerimaan obat, keluar dari resep klinik ini. Status dihitung dari stok akhir.'
);
$sheet->getStyle('A' . $baris)->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('555555');
$sheet->setCellValue('A' . ($baris + 1), 'Dicetak ' . date('d/m/Y H:i'));
$sheet->getStyle('A' . ($baris + 1))->getFont()->setSize(9)->getColor()->setRGB('777777');

$sheet->freezePane('C' . ($barisHeader + 1));
if ($rows) {
    $sheet->setAutoFilter("A{$barisHeader}:{$kolomAkhir}{$barisAkhirData}");
}
$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
    ->setFitToWidth(1)->setFitToHeight(0);
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($barisHeader, $barisHeader);

// ================== SHEET ANALISIS ==================
try {
    $dash = farmasiStokDashboard($koneksi, (string) $id_customer, $fromDate, $toDate, $hasil['data']);
} catch (Throwable $e) {
    error_log('[export farmasi] analisis: ' . $e->getMessage());
    $dash = null;
}
if ($dash) {
    $an = $spreadsheet->createSheet();
    $an->setTitle('Analisis');
    $judulBlok = function (int $b, array $kolom) use ($an) {
        $an->fromArray($kolom, null, 'A' . $b);
        $akhir = chr(64 + count($kolom));
        $an->getStyle("A{$b}:{$akhir}{$b}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
        ]);
    };
    $an->setCellValue('A1', 'ANALISIS FARMASI' . ($namaKlinik ? ' — ' . mb_strtoupper($namaKlinik) : ''));
    $an->getStyle('A1')->getFont()->setBold(true)->setSize(13);
    $an->setCellValue('A2', 'Periode ' . $tgl($fromDate) . ' s/d ' . $tgl($toDate) . ' (semua obat, tanpa filter tampilan)');
    $b = 4;
    $judulBlok($b, ['Indikator', 'Nilai']);
    foreach ([
        ['Nilai obat keluar (harga jual saat diserahkan)', $hasil['ringkasan']['nilai_keluar'], true],
        ['Resep dilayani', $dash['resep'], false],
        ['Jenis obat keluar', $dash['obat_keluar'], false],
        ['Obat berstok yang tidak keluar', $dash['mati'], false],
        ['Nilai stok tidak bergerak (harga beli)', $dash['mati_nilai'], true],
        ['Batch kedaluwarsa / ED ≤ 90 hari', $dash['ed_lewat'] + $dash['ed_dekat'], false],
        ['Obat dengan stok akhir minus', $dash['negatif'], false],
    ] as [$k, $v, $uang]) {
        $b++;
        $an->setCellValue("A{$b}", $k)->setCellValue("B{$b}", $v);
        $an->getStyle("B{$b}")->getNumberFormat()->setFormatCode($uang ? $fmtRupiah : $fmtAngka);
    }
    $blok = function (string $judul, array $kolom, array $data, array $format) use ($an, &$b, $judulBlok) {
        $b += 2;
        $an->setCellValue("A{$b}", $judul);
        $an->getStyle("A{$b}")->getFont()->setBold(true);
        $judulBlok(++$b, $kolom);
        if (!$data) {
            $an->setCellValue('A' . (++$b), 'Tidak ada data');
            return;
        }
        foreach ($data as $baris) {
            $b++;
            $an->fromArray(array_values($baris), null, "A{$b}", true);
            foreach ($format as $kol => $f) $an->getStyle("{$kol}{$b}")->getNumberFormat()->setFormatCode($f);
        }
    };
    $blok('10 obat paling banyak keluar', ['Obat', 'Satuan', 'Jumlah keluar', 'Nilai keluar', 'Stok akhir'],
        array_map(fn($x) => [$x['nama'], $x['satuan'], $x['keluar'], $x['nilai'], $x['stok_akhir']], $dash['top_keluar']), ['C' => $fmtAngka, 'D' => $fmtRupiah, 'E' => $fmtAngka]);
    $blok('10 obat dengan nilai keluar terbesar', ['Obat', 'Satuan', 'Jumlah keluar', 'Nilai keluar', 'Stok akhir'],
        array_map(fn($x) => [$x['nama'], $x['satuan'], $x['keluar'], $x['nilai'], $x['stok_akhir']], $dash['top_nilai']), ['C' => $fmtAngka, 'D' => $fmtRupiah, 'E' => $fmtAngka]);
    $blok('Kedaluwarsa (ED lewat / ≤ 90 hari, obat masih berstok)', ['Obat', 'Batch', 'Tanggal ED', 'Sisa hari', 'Diterima', 'Stok saat ini'],
        array_map(fn($x) => [$x['obat'], $x['batch'], $tgl($x['expired']), $x['sisa_hari'], $x['diterima'], $x['stok_saat_ini']], $dash['ed']), ['E' => $fmtAngka, 'F' => $fmtAngka]);
    $blok('Stok diperkirakan habis < 7 hari', ['Obat', 'Satuan', 'Keluar periode', 'Stok akhir', 'Perkiraan hari'],
        array_map(fn($x) => [$x['nama'], $x['satuan'], $x['keluar'], $x['stok_akhir'], $x['hari_stok']], $dash['kritis']), ['C' => $fmtAngka, 'D' => $fmtAngka]);
    $blok('Stok tidak bergerak (nilai terbesar)', ['Obat', 'Stok akhir', 'Nilai stok'],
        array_map(fn($x) => [$x['nama'], $x['stok_akhir'], $x['nilai_stok']], $dash['mati_top']), ['B' => $fmtAngka, 'C' => $fmtRupiah]);
    $blok('Nilai keluar per kategori', ['Kategori', 'Nilai keluar'],
        array_map(fn($x) => [$x['nama'], $x['jumlah']], $dash['kategori']), ['B' => $fmtRupiah]);
    foreach (['A' => 44, 'B' => 18, 'C' => 16, 'D' => 16, 'E' => 14, 'F' => 14] as $kol => $lebar) $an->getColumnDimension($kol)->setWidth($lebar);
    $spreadsheet->setActiveSheetIndex(0);
}

// ================== DOWNLOAD ==================
$filename = 'laporan_stok_farmasi_' . $fromDate . '_' . $toDate . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

(new Xlsx($spreadsheet))->save('php://output');
exit;
