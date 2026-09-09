<?php

include '../../database/connect.php';

header("Content-Type: application/json; charset=UTF-8");

/* ============================================================
   SESSION
============================================================ */

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {

   echo json_encode([
      "status"  => "error",
      "message" => "Session tidak ditemukan"
   ]);

   exit;
}


/* ============================================================
   FILTER TANGGAL
============================================================ */

$fromDate = $_GET['fromDate'] ?? date('Y-m-d');
$toDate   = $_GET['toDate'] ?? date('Y-m-d');


/* ============================================================
   VALIDASI FORMAT TANGGAL
============================================================ */

$fromDateValid = DateTime::createFromFormat('Y-m-d', $fromDate);
$toDateValid   = DateTime::createFromFormat('Y-m-d', $toDate);

if (
   !$fromDateValid ||
   !$toDateValid ||
   $fromDateValid->format('Y-m-d') !== $fromDate ||
   $toDateValid->format('Y-m-d') !== $toDate
) {

   echo json_encode([
      "status"  => "error",
      "message" => "Format tanggal tidak valid"
   ]);

   exit;
}


/* ============================================================
   VALIDASI PERIODE
============================================================ */

if ($fromDate > $toDate) {

   echo json_encode([
      "status"  => "error",
      "message" => "Tanggal mulai tidak boleh lebih besar dari tanggal akhir"
   ]);

   exit;
}


/* ============================================================
   QUERY
============================================================ */

/*
    SUMBER DATA:

    MASTER OBAT
    ms_pharmacy
        pharmacy_stock = STOCK AWAL

    STOCK MASUK
    pharmacy_stock_penerimaan
        jumlah

    STOCK KELUAR
    permintaan_pharmacy_details
        qty

    STOCK AKHIR
    pharmacy_stock
    + stok masuk
    - stok keluar
*/


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

    COALESCE(
        p.pharmacy_price_buy,
        0
    ) AS pharmacy_price_buy,

    COALESCE(
        p.pharmacy_buy,
        0
    ) AS pharmacy_buy,

    COALESCE(
        p.pharmacy_sale,
        0
    ) AS pharmacy_sale,


    /* ========================================================
       STOCK AWAL
    ======================================================== */

    COALESCE(
        p.pharmacy_stock,
        0
    ) AS pharmacy_stock,


    /* ========================================================
       STOCK MIN / MAX
    ======================================================== */

    COALESCE(
        p.stok_min,
        0
    ) AS stok_min,

    COALESCE(
        p.stok_max,
        0
    ) AS stok_max,


    /* ========================================================
       STOCK MASUK
       TABLE:
       pharmacy_stock_penerimaan
    ======================================================== */

    COALESCE(

        (

            SELECT
                SUM(psp.jumlah)

            FROM pharmacy_stock_penerimaan psp

            WHERE
                psp.id_pharmacy = CAST(p.id_pharmacy AS CHAR)

                AND psp.id_customer = CAST(p.id_customer AS CHAR)

                AND psp.tanggal >= ?

                AND psp.tanggal <= ?

        ),

        0

    ) AS stok_masuk,


    /* ========================================================
       STOCK KELUAR
       TABLE:
       permintaan_pharmacy_details
    ======================================================== */

    COALESCE(

        (

            SELECT
                SUM(ppd.qty)

            FROM permintaan_pharmacy_details ppd

            WHERE
                ppd.id_pharmacy = p.id_pharmacy

                AND ppd.created_at >= CONCAT(
                    ?,
                    ' 00:00:00'
                )

                AND ppd.created_at <= CONCAT(
                    ?,
                    ' 23:59:59'
                )

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


/* ============================================================
   PREPARE
============================================================ */

$stmt = $koneksi->prepare($sql);

if (!$stmt) {

   echo json_encode([
      "status"  => "error",
      "message" => "Prepare query gagal",
      "error"   => $koneksi->error
   ]);

   exit;
}


/* ============================================================
   BIND PARAMETER
============================================================ */

/*
    1. fromDate STOCK MASUK
    2. toDate   STOCK MASUK

    3. fromDate STOCK KELUAR
    4. toDate   STOCK KELUAR

    5. id_customer
*/

$stmt->bind_param(
   "sssss",
   $fromDate,
   $toDate,
   $fromDate,
   $toDate,
   $id_customer
);


/* ============================================================
   EXECUTE
============================================================ */

if (!$stmt->execute()) {

   echo json_encode([
      "status"  => "error",
      "message" => "Execute query gagal",
      "error"   => $stmt->error
   ]);

   $stmt->close();

   exit;
}


$result = $stmt->get_result();

$data = [];


/* ============================================================
   DATA PROCESSING
============================================================ */

while ($row = $result->fetch_assoc()) {


   /* ========================================================
       STOCK AWAL
    ======================================================== */

   $stokAwal = (float) (
      $row['pharmacy_stock'] ?? 0
   );


   /* ========================================================
       STOCK MASUK
    ======================================================== */

   $stokMasuk = (float) (
      $row['stok_masuk'] ?? 0
   );


   /* ========================================================
       STOCK KELUAR
    ======================================================== */

   $stokKeluar = (float) (
      $row['stok_keluar'] ?? 0
   );


   /* ========================================================
       STOCK MIN / MAX
    ======================================================== */

   $stokMin = (float) (
      $row['stok_min'] ?? 0
   );

   $stokMax = (float) (
      $row['stok_max'] ?? 0
   );


   /* ========================================================
       STOCK AKHIR
    ======================================================== */

   $stokAkhir =
      $stokAwal
      + $stokMasuk
      - $stokKeluar;


   /* ========================================================
       HARGA BELI
    ======================================================== */

   $hargaBeli = (float) (
      $row['pharmacy_price_buy'] ?? 0
   );


   /*
       Kalau pharmacy_price_buy kosong,
       fallback ke pharmacy_buy
    */

   if ($hargaBeli <= 0) {

      $hargaBeli = (float) (
         $row['pharmacy_buy'] ?? 0
      );
   }


   /* ========================================================
       NILAI STOCK
    ======================================================== */

   $nilaiStok =
      $stokAkhir * $hargaBeli;


   /* ========================================================
       STATUS STOCK
    ======================================================== */

   if ($stokAkhir <= 0) {

      $statusStock = 'Habis';
   } elseif (

      $stokMin > 0 &&
      $stokAkhir < $stokMin

   ) {

      $statusStock = 'Di Bawah Minimum';
   } elseif (

      $stokMax > 0 &&
      $stokAkhir > $stokMax

   ) {

      $statusStock = 'Di Atas Maksimum';
   } else {

      $statusStock = 'Normal';
   }


   /* ========================================================
       STATUS CODE
    ======================================================== */

   if ($stokAkhir <= 0) {

      $statusCode = 'habis';
   } elseif (

      $stokMin > 0 &&
      $stokAkhir < $stokMin

   ) {

      $statusCode = 'minimum';
   } elseif (

      $stokMax > 0 &&
      $stokAkhir > $stokMax

   ) {

      $statusCode = 'maximum';
   } else {

      $statusCode = 'normal';
   }


   /* ========================================================
       FORMAT DATA
    ======================================================== */

   $row['pharmacy_stock'] =
      $stokAwal;

   $row['stok_awal'] =
      $stokAwal;

   $row['stok_masuk'] =
      $stokMasuk;

   $row['stok_keluar'] =
      $stokKeluar;

   $row['stok_akhir'] =
      $stokAkhir;

   $row['stok_min'] =
      $stokMin;

   $row['stok_max'] =
      $stokMax;

   $row['pharmacy_price_buy'] =
      $hargaBeli;

   $row['nilai_stok'] =
      $nilaiStok;

   $row['status_stock'] =
      $statusStock;

   $row['status_code'] =
      $statusCode;


   /* ========================================================
       PERIODE
    ======================================================== */

   $row['from_date'] =
      $fromDate;

   $row['to_date'] =
      $toDate;


   /* ========================================================
       PUSH DATA
    ======================================================== */

   $data[] = $row;
}


/* ============================================================
   CLOSE
============================================================ */

$stmt->close();


/* ============================================================
   RESPONSE
============================================================ */

echo json_encode([

   "status" => "success",

   "filter" => [

      "fromDate" => $fromDate,

      "toDate" => $toDate

   ],

   "total" => count($data),

   "data" => $data

], JSON_UNESCAPED_UNICODE);
