<?php

include '../../database/connect.php';

header("Content-Type: application/json; charset=UTF-8");


// ============================================================
// SESSION
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {

   echo json_encode([
      "status" => "error",
      "message" => "Session tidak ditemukan"
   ]);

   exit;
}


// ============================================================
// FILTER TANGGAL
// ============================================================

$fromDate = $_GET['fromDate'] ?? date('Y-m-d');
$toDate   = $_GET['toDate'] ?? date('Y-m-d');


// Validasi format tanggal
$fromDateValid = DateTime::createFromFormat('Y-m-d', $fromDate);
$toDateValid   = DateTime::createFromFormat('Y-m-d', $toDate);

if (
   !$fromDateValid ||
   !$toDateValid ||
   $fromDateValid->format('Y-m-d') !== $fromDate ||
   $toDateValid->format('Y-m-d') !== $toDate
) {

   echo json_encode([
      "status" => "error",
      "message" => "Format tanggal tidak valid"
   ]);

   exit;
}


// Validasi periode
if ($fromDate > $toDate) {

   echo json_encode([
      "status" => "error",
      "message" => "Tanggal mulai tidak boleh lebih besar dari tanggal akhir"
   ]);

   exit;
}


// ============================================================
// BASE QUERY
// ============================================================
//
// Semua obat aktif dari ms_pharmacy ditampilkan.
//
// TRANSAKSI MASUK
// pharmacy_buy_detail
//
// TRANSAKSI KELUAR
// permintaan_pharmacy_details
//
// CATATAN:
// pharmacy_buy_detail tidak memiliki id_pharmacy.
// Oleh karena itu matching barang masuk sementara berdasarkan:
// - pharmacy_code
// - pharmacy_name_generic
// - pharmacy_name_trade
//
// ============================================================

$sql = "

SELECT

    /* ========================================================
       MASTER FARMASI
       ======================================================== */

    p.id_pharmacy,

    p.pharmacy_code,

    p.pharmacy_name_generic,

    p.pharmacy_name_trade,

    p.pharmacy_category,

    p.pharmacy_sub_category,

    p.pharmcy_golongan,

    p.pharmcy_jenis_drugs,

    p.pharmacy_bentuk_sediaan,

    p.pharmacy_dosis,

    p.pharmacy_unit,

    p.pharmacy_kemasan,

    p.pharmacy_supplier,

    p.pharmacy_factory,


    /* ========================================================
       HARGA
       ======================================================== */

    COALESCE(p.pharmacy_price_buy, 0) AS pharmacy_price_buy,

    COALESCE(p.pharmacy_buy, 0) AS pharmacy_buy,

    COALESCE(p.pharmacy_sale, 0) AS pharmacy_sale,


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

                AND pb.created_at <= CONCAT(?, ' 23:59:59')

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

                AND pd.created_at <= CONCAT(?, ' 23:59:59')

        ),

        0

    ) AS stok_keluar


FROM ms_pharmacy p


/* ============================================================
   FILTER MASTER
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

   echo json_encode([
      "status" => "error",
      "message" => "Prepare query gagal",
      "error" => $koneksi->error
   ]);

   exit;
}


// ============================================================
// BIND PARAMETER
// ============================================================
//
// Ada 5 parameter:
//
// 1. fromDate barang masuk
// 2. toDate barang masuk
// 3. fromDate barang keluar
// 4. toDate barang keluar
// 5. id_customer
//
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

   echo json_encode([
      "status" => "error",
      "message" => "Execute query gagal",
      "error" => $stmt->error
   ]);

   $stmt->close();

   exit;
}


$result = $stmt->get_result();

$data = [];


// ============================================================
// DATA PROCESSING
// ============================================================

while ($row = $result->fetch_assoc()) {


   // --------------------------------------------------------
   // STOCK
   // --------------------------------------------------------

   $stokSaatIni = (float) ($row['stok_saat_ini'] ?? 0);

   $stokMasuk = (float) ($row['stok_masuk'] ?? 0);

   $stokKeluar = (float) ($row['stok_keluar'] ?? 0);

   $stokMin = (float) ($row['stok_min'] ?? 0);

   $stokMax = (float) ($row['stok_max'] ?? 0);


   // --------------------------------------------------------
   // HARGA BELI
   // --------------------------------------------------------

   $hargaBeli = (float) ($row['pharmacy_price_buy'] ?? 0);


   // --------------------------------------------------------
   // NILAI STOCK
   // --------------------------------------------------------

   $nilaiStok = $stokSaatIni * $hargaBeli;


   // --------------------------------------------------------
   // STATUS STOCK
   // --------------------------------------------------------

   if ($stokSaatIni <= 0) {

      $statusStock = 'Habis';
   } elseif (
      $stokMin > 0 &&
      $stokSaatIni < $stokMin
   ) {

      $statusStock = 'Di Bawah Minimum';
   } elseif (
      $stokMax > 0 &&
      $stokSaatIni > $stokMax
   ) {

      $statusStock = 'Di Atas Maksimum';
   } else {

      $statusStock = 'Normal';
   }


   // --------------------------------------------------------
   // STATUS CODE
   // --------------------------------------------------------

   if ($stokSaatIni <= 0) {

      $statusCode = 'habis';
   } elseif (
      $stokMin > 0 &&
      $stokSaatIni < $stokMin
   ) {

      $statusCode = 'minimum';
   } elseif (
      $stokMax > 0 &&
      $stokSaatIni > $stokMax
   ) {

      $statusCode = 'maximum';
   } else {

      $statusCode = 'normal';
   }


   // --------------------------------------------------------
   // FORMAT DATA
   // --------------------------------------------------------

   $row['stok_saat_ini'] = $stokSaatIni;

   $row['stok_masuk'] = $stokMasuk;

   $row['stok_keluar'] = $stokKeluar;

   $row['stok_min'] = $stokMin;

   $row['stok_max'] = $stokMax;

   $row['pharmacy_price_buy'] = $hargaBeli;

   $row['nilai_stok'] = $nilaiStok;

   $row['status_stock'] = $statusStock;

   $row['status_code'] = $statusCode;


   // --------------------------------------------------------
   // PERIODE
   // --------------------------------------------------------

   $row['from_date'] = $fromDate;

   $row['to_date'] = $toDate;


   // --------------------------------------------------------
   // PUSH
   // --------------------------------------------------------

   $data[] = $row;
}


// ============================================================
// CLOSE
// ============================================================

$stmt->close();


// ============================================================
// RESPONSE
// ============================================================

echo json_encode([
   "status" => "success",

   "filter" => [
      "fromDate" => $fromDate,
      "toDate" => $toDate
   ],

   "total" => count($data),

   "data" => $data

], JSON_UNESCAPED_UNICODE);
