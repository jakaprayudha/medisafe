<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

session_start();

$id_customer = $_SESSION['id_customer'] ?? null;
$username    = $_SESSION['username'] ?? 'system';

$method = $_SERVER['REQUEST_METHOD'];

if (!$id_customer) {
   echo json_encode([
      'status'  => false,
      'message' => 'Session customer tidak ditemukan.'
   ]);
   exit;
}

switch ($method) {

   case 'GET':

      $action = $_GET['action'] ?? 'detail';

      switch ($action) {

         case 'detail':
            getDetailStock();
            break;

         case 'penerimaan':
            getPenerimaan();
            break;

         default:
            echo json_encode([
               'status'  => false,
               'message' => 'Action GET tidak ditemukan.'
            ]);
      }

      break;


   case 'POST':

      $action = $_POST['action'] ?? '';

      switch ($action) {

         case 'stock_awal':
            setStockAwal();
            break;

         case 'penerimaan':
            savePenerimaan();
            break;

         default:
            echo json_encode([
               'status'  => false,
               'message' => 'Action POST tidak ditemukan.'
            ]);
      }

      break;


   case 'PUT':

      echo json_encode([
         'status'  => false,
         'message' => 'Method PUT belum digunakan.'
      ]);

      break;


   case 'DELETE':

      deletePenerimaan();

      break;


   default:

      echo json_encode([
         'status'  => false,
         'message' => 'Method request tidak didukung.'
      ]);

      break;
}


/**
 * ============================================================
 * GET DETAIL STOCK
 * ============================================================
 *
 * Stok awal:
 *      ms_pharmacy.pharmacy_stock
 *
 * Stok masuk:
 *      pharmacy_stock_penerimaan.jumlah
 *
 * Stok keluar:
 *      permintaan_pharmacy_details.qty
 *
 * Stok tersedia:
 *      stok awal + stok masuk - stok keluar
 */
function getDetailStock()
{
   global $koneksi, $id_customer;

   $id_pharmacy = $_GET['id_pharmacy'] ?? '';

   if ($id_pharmacy === '') {
      echo json_encode([
         'status'  => false,
         'message' => 'ID pharmacy tidak ditemukan.'
      ]);
      return;
   }

   /**
    * Ambil data obat
    */
   $sql = "
        SELECT
            mp.id_pharmacy,
            mp.pharmacy_code,
            mp.pharmacy_name_generic,
            mp.pharmacy_name_trade,
            mp.pharmacy_category,
            mp.pharmacy_sub_category,
            mp.pharmacy_unit,
            mp.pharmacy_kemasan,
            mp.pharmacy_supplier,
            mp.pharmacy_factory,
            mp.pharmacy_buy,
            mp.pharmacy_sale,
            mp.stok_min,
            mp.stok_max,
            COALESCE(mp.pharmacy_stock, 0) AS stok_awal

        FROM ms_pharmacy mp

        WHERE mp.id_pharmacy = ?
        AND mp.id_customer = ?

        LIMIT 1
    ";

   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {
      echo json_encode([
         'status'  => false,
         'message' => $koneksi->error
      ]);
      return;
   }

   $stmt->bind_param(
      "is",
      $id_pharmacy,
      $id_customer
   );

   $stmt->execute();

   $result = $stmt->get_result();

   if ($result->num_rows === 0) {

      echo json_encode([
         'status'  => false,
         'message' => 'Data obat tidak ditemukan.'
      ]);

      $stmt->close();
      return;
   }

   $data = $result->fetch_assoc();

   $stmt->close();


   /**
    * ========================================================
    * TOTAL STOK MASUK
    * ========================================================
    */

   $sqlMasuk = "
        SELECT
            COALESCE(SUM(jumlah), 0) AS stok_masuk

        FROM pharmacy_stock_penerimaan

        WHERE id_pharmacy = ?
        AND id_customer = ?
    ";

   $stmtMasuk = $koneksi->prepare($sqlMasuk);

   if (!$stmtMasuk) {
      echo json_encode([
         'status'  => false,
         'message' => $koneksi->error
      ]);
      return;
   }

   $stmtMasuk->bind_param(
      "ss",
      $id_pharmacy,
      $id_customer
   );

   $stmtMasuk->execute();

   $resultMasuk = $stmtMasuk->get_result();

   $dataMasuk = $resultMasuk->fetch_assoc();

   $stok_masuk = (int) ($dataMasuk['stok_masuk'] ?? 0);

   $stmtMasuk->close();


   /**
    * ========================================================
    * TOTAL STOK KELUAR
    * ========================================================
    *
    * Untuk sementara menggunakan status_item = 1
    * sebagai item yang sudah keluar.
    */

   $sqlKeluar = "
        SELECT
            COALESCE(SUM(qty), 0) AS stok_keluar

        FROM permintaan_pharmacy_details

        WHERE id_pharmacy = ?
        AND status_item = 1
    ";

   $stmtKeluar = $koneksi->prepare($sqlKeluar);

   if (!$stmtKeluar) {
      echo json_encode([
         'status'  => false,
         'message' => $koneksi->error
      ]);
      return;
   }

   $stmtKeluar->bind_param(
      "i",
      $id_pharmacy
   );

   $stmtKeluar->execute();

   $resultKeluar = $stmtKeluar->get_result();

   $dataKeluar = $resultKeluar->fetch_assoc();

   $stok_keluar = (int) ($dataKeluar['stok_keluar'] ?? 0);

   $stmtKeluar->close();


   /**
    * ========================================================
    * HITUNG STOK TERSEDIA
    * ========================================================
    */

   $stok_awal = (int) ($data['stok_awal'] ?? 0);

   $stok_tersedia =
      $stok_awal
      + $stok_masuk
      - $stok_keluar;


   /**
    * Masukkan hasil ke response
    */

   $data['stok_awal']     = $stok_awal;
   $data['stok_masuk']    = $stok_masuk;
   $data['stok_keluar']   = $stok_keluar;
   $data['stok_tersedia'] = $stok_tersedia;


   echo json_encode([
      'status' => true,
      'data'   => $data
   ]);
}


/**
 * ============================================================
 * SET STOCK AWAL
 * ============================================================
 *
 * Update:
 * ms_pharmacy.pharmacy_stock
 */
function setStockAwal()
{
   global $koneksi, $id_customer, $username;

   $id_pharmacy = $_POST['id_pharmacy'] ?? '';
   $stock_awal  = $_POST['stock_awal'] ?? '';

   if ($id_pharmacy === '') {
      echo json_encode([
         'status'  => false,
         'message' => 'ID pharmacy tidak ditemukan.'
      ]);
      return;
   }

   if ($stock_awal === '' || !is_numeric($stock_awal)) {
      echo json_encode([
         'status'  => false,
         'message' => 'Stock awal tidak valid.'
      ]);
      return;
   }

   $stock_awal = (int) $stock_awal;


   /**
    * Update stock langsung ke ms_pharmacy
    */

   $sql = "
        UPDATE ms_pharmacy

        SET
            pharmacy_stock = ?

        WHERE id_pharmacy = ?
        AND id_customer = ?
    ";

   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {
      echo json_encode([
         'status'  => false,
         'message' => $koneksi->error
      ]);
      return;
   }

   $stmt->bind_param(
      "iis",
      $stock_awal,
      $id_pharmacy,
      $id_customer
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status'  => true,
         'message' => 'Stock awal berhasil disimpan.',
         'data'    => [
            'id_pharmacy' => $id_pharmacy,
            'stock_awal'  => $stock_awal
         ]
      ]);
   } else {

      echo json_encode([
         'status'  => false,
         'message' => $stmt->error
      ]);
   }

   $stmt->close();
}


/**
 * ============================================================
 * SIMPAN PENERIMAAN STOCK
 * ============================================================
 *
 * Table:
 * pharmacy_stock_penerimaan
 */
function savePenerimaan()
{
   global $koneksi, $id_customer, $username;

   $id_pharmacy  = $_POST['id_pharmacy'] ?? '';
   $tanggal      = $_POST['tanggal'] ?? '';
   $nomor_faktur = $_POST['nomor_faktur'] ?? '';
   $supplier     = $_POST['supplier'] ?? '';
   $batch        = $_POST['batch'] ?? '';
   $expired      = $_POST['expired'] ?? null;
   $jumlah       = $_POST['jumlah'] ?? 0;
   $harga_beli   = $_POST['harga_beli'] ?? 0;
   $keterangan   = $_POST['keterangan'] ?? '';


   /**
    * Validasi
    */

   if ($id_pharmacy === '') {
      echo json_encode([
         'status'  => false,
         'message' => 'ID pharmacy wajib diisi.'
      ]);
      return;
   }

   if ($tanggal === '') {
      echo json_encode([
         'status'  => false,
         'message' => 'Tanggal penerimaan wajib diisi.'
      ]);
      return;
   }

   if (!is_numeric($jumlah) || (int)$jumlah <= 0) {
      echo json_encode([
         'status'  => false,
         'message' => 'Jumlah penerimaan harus lebih dari 0.'
      ]);
      return;
   }

   $jumlah = (int) $jumlah;

   $harga_beli = is_numeric($harga_beli)
      ? (float) $harga_beli
      : 0;


   /**
    * Validasi obat
    */

   $sqlCheck = "
        SELECT id_pharmacy

        FROM ms_pharmacy

        WHERE id_pharmacy = ?
        AND id_customer = ?

        LIMIT 1
    ";

   $stmtCheck = $koneksi->prepare($sqlCheck);

   $stmtCheck->bind_param(
      "is",
      $id_pharmacy,
      $id_customer
   );

   $stmtCheck->execute();

   $resultCheck = $stmtCheck->get_result();

   if ($resultCheck->num_rows === 0) {

      echo json_encode([
         'status'  => false,
         'message' => 'Obat tidak ditemukan.'
      ]);

      $stmtCheck->close();
      return;
   }

   $stmtCheck->close();


   /**
    * ========================================================
    * SIMPAN PENERIMAAN
    * ========================================================
    */

   $sql = "
        INSERT INTO pharmacy_stock_penerimaan
        (
            id_pharmacy,
            id_customer,
            tanggal,
            nomor_faktur,
            supplier,
            batch,
            expired,
            jumlah,
            harga_beli,
            keterangan,
            created_by
        )

        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ";

   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {

      echo json_encode([
         'status'  => false,
         'message' => $koneksi->error
      ]);

      return;
   }


   /**
    * 11 parameter:
    *
    * id_pharmacy
    * id_customer
    * tanggal
    * nomor_faktur
    * supplier
    * batch
    * expired
    * jumlah
    * harga_beli
    * keterangan
    * created_by
    *
    * s s s s s s s i d s s
    */

   $stmt->bind_param(
      "sssssssidss",
      $id_pharmacy,
      $id_customer,
      $tanggal,
      $nomor_faktur,
      $supplier,
      $batch,
      $expired,
      $jumlah,
      $harga_beli,
      $keterangan,
      $username
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status'  => true,
         'message' => 'Penerimaan stock berhasil disimpan.',
         'id'      => $stmt->insert_id
      ]);
   } else {

      echo json_encode([
         'status'  => false,
         'message' => $stmt->error
      ]);
   }

   $stmt->close();
}


/**
 * ============================================================
 * GET HISTORI PENERIMAAN
 * ============================================================
 */
function getPenerimaan()
{
   global $koneksi, $id_customer;

   $id_pharmacy = $_GET['id_pharmacy'] ?? '';

   if ($id_pharmacy === '') {
      echo json_encode([
         'status'  => false,
         'message' => 'ID pharmacy tidak ditemukan.'
      ]);
      return;
   }


   $sql = "
        SELECT
            id_penerimaan,
            id_pharmacy,
            id_customer,
            tanggal,
            nomor_faktur,
            supplier,
            batch,
            expired,
            jumlah,
            harga_beli,
            keterangan,
            created_at,
            created_by

        FROM pharmacy_stock_penerimaan

        WHERE id_pharmacy = ?
        AND id_customer = ?

        ORDER BY tanggal DESC, id_penerimaan DESC
    ";

   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {
      echo json_encode([
         'status'  => false,
         'message' => $koneksi->error
      ]);
      return;
   }

   $stmt->bind_param(
      "ss",
      $id_pharmacy,
      $id_customer
   );

   $stmt->execute();

   $result = $stmt->get_result();

   $data = [];

   while ($row = $result->fetch_assoc()) {
      $data[] = $row;
   }

   $stmt->close();


   echo json_encode([
      'status' => true,
      'data'   => $data
   ]);
}


/**
 * ============================================================
 * DELETE PENERIMAAN
 * ============================================================
 */
function deletePenerimaan()
{
   global $koneksi, $id_customer;

   // Ambil ID dari query string
   $id_penerimaan = $_GET['id_penerimaan'] ?? '';

   if ($id_penerimaan === '') {
      echo json_encode([
         'status'  => false,
         'message' => 'ID penerimaan tidak ditemukan.'
      ]);
      return;
   }

   $id_penerimaan = (int) $id_penerimaan;

   if ($id_penerimaan <= 0) {
      echo json_encode([
         'status'  => false,
         'message' => 'ID penerimaan tidak valid.'
      ]);
      return;
   }

   $sql = "
        DELETE FROM pharmacy_stock_penerimaan
        WHERE id_penerimaan = ?
        AND id_customer = ?
    ";

   $stmt = $koneksi->prepare($sql);

   if (!$stmt) {
      echo json_encode([
         'status'  => false,
         'message' => 'Prepare statement gagal: ' . $koneksi->error
      ]);
      return;
   }

   $stmt->bind_param(
      "is",
      $id_penerimaan,
      $id_customer
   );

   if ($stmt->execute()) {

      if ($stmt->affected_rows > 0) {

         echo json_encode([
            'status'  => true,
            'message' => 'Data penerimaan berhasil dihapus.'
         ]);
      } else {

         echo json_encode([
            'status'  => false,
            'message' => 'Data penerimaan tidak ditemukan atau bukan milik customer ini.'
         ]);
      }
   } else {

      echo json_encode([
         'status'  => false,
         'message' => 'Gagal menghapus data: ' . $stmt->error
      ]);
   }

   $stmt->close();
}
