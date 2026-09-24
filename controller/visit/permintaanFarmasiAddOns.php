<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

   case 'POST':
      createData();
      break;

   case 'GET':

      if (isset($_GET['id'])) {
         getID($_GET['id']);
      } else {
         getData();
      }

      break;

   case 'PUT':

      parse_str(file_get_contents("php://input"), $_PUT);

      if (isset($_GET['approve'])) {
         approveData($_PUT);
      } else {
         updateData();
      }

      break;

   case 'DELETE':
      deleteData();
      break;

   default:

      http_response_code(405);

      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);

      break;
}


/**
 * ============================================================
 * CREATE
 * ============================================================
 */
function createData()
{
   global $koneksi;

   // --------------------------------------------------------
   // Ambil POST
   // --------------------------------------------------------
   if (empty($_POST)) {

      $raw = file_get_contents("php://input");

      if (!empty($raw)) {
         parse_str($raw, $_POST);
      }
   }

   if (empty($_POST)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);

      exit;
   }


   // --------------------------------------------------------
   // ID PERMINTAAN FARMASI
   // --------------------------------------------------------
   $id_permintaan_farmasi = $_POST['id_permintaan_farmasi'] ?? '';

   // Trim
   $id_permintaan_farmasi = trim((string) $id_permintaan_farmasi);


   // Validasi tidak boleh kosong
   if ($id_permintaan_farmasi === '') {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID Permintaan Farmasi kosong.'
      ]);

      exit;
   }


   // Harus angka
   if (!ctype_digit($id_permintaan_farmasi)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID Permintaan Farmasi harus berupa angka.'
      ]);

      exit;
   }


   $id_permintaan_farmasi = (int) $id_permintaan_farmasi;


   // --------------------------------------------------------
   // ID PHARMACY
   // --------------------------------------------------------
   $id_pharmacy = $_POST['id_pharmacy'] ?? '';

   $id_pharmacy = trim((string) $id_pharmacy);

   if ($id_pharmacy === '') {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID Pharmacy kosong.'
      ]);

      exit;
   }


   if (!ctype_digit($id_pharmacy)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID Pharmacy harus berupa angka.'
      ]);

      exit;
   }


   $id_pharmacy = (int) $id_pharmacy;


   // --------------------------------------------------------
   // QTY
   // --------------------------------------------------------
   $qty = isset($_POST['qty']) && $_POST['qty'] !== ''
      ? (int) $_POST['qty']
      : 1;


   if ($qty <= 0) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Qty harus lebih besar dari 0.'
      ]);

      exit;
   }


   // --------------------------------------------------------
   // SIGN
   // --------------------------------------------------------
   $signa = $_POST['signa'] ?? null;


   // --------------------------------------------------------
   // CATATAN
   // --------------------------------------------------------
   $catatan = $_POST['catatan'] ?? null;


   // --------------------------------------------------------
   // CREATED USER
   // --------------------------------------------------------
   $created_user = $_POST['created_user'] ?? null;


   // ========================================================
   // AMBIL DATA PHARMACY DARI MASTER
   // ========================================================

   $getPharmacy = $koneksi->prepare("
        SELECT
            pharmacy_name_generic,
            pharmacy_name_trade,
            pharmacy_sale
        FROM ms_pharmacy
        WHERE id_pharmacy = ?
        LIMIT 1
    ");

   if (!$getPharmacy) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query pharmacy: ' . $koneksi->error
      ]);

      exit;
   }


   $getPharmacy->bind_param(
      "i",
      $id_pharmacy
   );


   $getPharmacy->execute();

   $result = $getPharmacy->get_result();


   if ($result->num_rows === 0) {

      $getPharmacy->close();

      echo json_encode([
         'status' => 'error',
         'message' => 'Data pharmacy tidak ditemukan.'
      ]);

      exit;
   }


   $pharmacy = $result->fetch_assoc();

   $getPharmacy->close();


   // ========================================================
   // ITEM NAME
   // ========================================================

   $item_name = '';

   if (!empty($pharmacy['pharmacy_name_generic'])) {

      $item_name = $pharmacy['pharmacy_name_generic'];
   } elseif (!empty($pharmacy['pharmacy_name_trade'])) {

      $item_name = $pharmacy['pharmacy_name_trade'];
   }


   // ========================================================
   // HARGA
   // ========================================================

   $harga = isset($pharmacy['pharmacy_sale'])
      ? (int) $pharmacy['pharmacy_sale']
      : 0;


   // ========================================================
   // INSERT
   // ========================================================

   $query = "
        INSERT INTO permintaan_pharmacy_details
        (
            id_permintaan_farmasi,
            id_pharmacy,
            signa,
            qty,
            catatan,
            harga,
            created_user,
            item_name
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query INSERT: ' . $koneksi->error
      ]);

      exit;
   }


   /*
     * i = integer
     * i = integer
     * s = string
     * i = integer
     * s = string
     * i = integer
     * s = string
     * s = string
     */

   $stmt->bind_param(
      "iisisi ss",
      $id_permintaan_farmasi,
      $id_pharmacy,
      $signa,
      $qty,
      $catatan,
      $harga,
      $created_user,
      $item_name
   );


   /*
     * Hapus spasi dari type string di atas.
     *
     * Type yang benar:
     *
     * i i s i s i s s
     *
     * menjadi:
     *
     * iisisiss
     */

   $stmt->bind_param(
      "iisisiss",
      $id_permintaan_farmasi,
      $id_pharmacy,
      $signa,
      $qty,
      $catatan,
      $harga,
      $created_user,
      $item_name
   );


   if ($stmt->execute()) {

      $insertId = $stmt->insert_id;


      echo json_encode([
         'status' => 'success',
         'message' => 'Data berhasil ditambahkan.',
         'id' => $insertId,
         'id_permintaan_farmasi' => $id_permintaan_farmasi,
         'id_pharmacy' => $id_pharmacy,
         'item_name' => $item_name,
         'harga' => $harga
      ]);
   } else {

      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menambahkan data: ' . $stmt->error
      ]);
   }


   $stmt->close();
}


/**
 * ============================================================
 * GET DATA
 * ============================================================
 */
function getData()
{
   global $koneksi;


   $no = $_GET['no'] ?? '';

   $no = trim($no);


   if ($no === '') {

      echo json_encode([
         'status' => 'error',
         'message' => 'Parameter no tidak ditemukan.'
      ]);

      return;
   }


   if (!ctype_digit($no)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Parameter no harus berupa angka.'
      ]);

      return;
   }


   $no = (int) $no;


   $query = "
        SELECT

            permintaan_pharmacy_details.*,

            ms_pharmacy.pharmacy_name_generic,

            ms_pharmacy.pharmacy_name_trade,

            permintaan_pharmacy.status_permintaan,

            (
                permintaan_pharmacy_details.qty
                *
                permintaan_pharmacy_details.harga
            ) AS total_item

        FROM permintaan_pharmacy_details

        LEFT JOIN ms_pharmacy
            ON permintaan_pharmacy_details.id_pharmacy
            = ms_pharmacy.id_pharmacy

        LEFT JOIN permintaan_pharmacy
            ON permintaan_pharmacy_details.id_permintaan_farmasi
            = permintaan_pharmacy.id_permintaan_farmasi

        WHERE permintaan_pharmacy_details.id_permintaan_farmasi = ?

        ORDER BY permintaan_pharmacy_details.id_pharmacy_details ASC
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      http_response_code(500);

      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "i",
      $no
   );


   $stmt->execute();

   $result = $stmt->get_result();


   $data = $result->fetch_all(MYSQLI_ASSOC);


   $stmt->close();


   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);
}


/**
 * ============================================================
 * GET BY ID
 * ============================================================
 */
function getID($iduser)
{
   global $koneksi;


   $iduser = trim((string) $iduser);


   if ($iduser === '' || !ctype_digit($iduser)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak valid.'
      ]);

      return;
   }


   $iduser = (int) $iduser;


   $query = "
        SELECT *
        FROM permintaan_pharmacy_details
        WHERE id_pharmacy_details = ?
        LIMIT 1
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query.'
      ]);

      return;
   }


   $stmt->bind_param(
      "i",
      $iduser
   );


   $stmt->execute();


   $result = $stmt->get_result();


   if ($result->num_rows > 0) {

      $data = $result->fetch_assoc();


      echo json_encode([
         'status' => 'success',
         'data' => $data
      ]);
   } else {

      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
   }


   $stmt->close();
}


/**
 * ============================================================
 * UPDATE
 * ============================================================
 */
function updateData()
{
   global $koneksi;


   parse_str(
      file_get_contents("php://input"),
      $_PUT
   );


   // --------------------------------------------------------
   // ID DETAIL
   // --------------------------------------------------------

   $id = $_PUT['id_pharmacy_details'] ?? '';

   $id = trim((string) $id);


   if ($id === '' || !ctype_digit($id)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID detail tidak valid.'
      ]);

      return;
   }


   $id = (int) $id;


   // --------------------------------------------------------
   // ID PHARMACY
   // --------------------------------------------------------

   $id_pharmacy = $_PUT['id_pharmacy'] ?? '';

   $id_pharmacy = trim((string) $id_pharmacy);


   if ($id_pharmacy === '' || !ctype_digit($id_pharmacy)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID Pharmacy tidak valid.'
      ]);

      return;
   }


   $id_pharmacy = (int) $id_pharmacy;


   // --------------------------------------------------------
   // ID PERMINTAAN FARMASI
   // --------------------------------------------------------

   $id_permintaan_farmasi = $_PUT['id_permintaan_farmasi'] ?? '';

   $id_permintaan_farmasi = trim((string) $id_permintaan_farmasi);


   if ($id_permintaan_farmasi === '') {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID Permintaan Farmasi tidak boleh kosong.'
      ]);

      return;
   }


   if (!ctype_digit($id_permintaan_farmasi)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID Permintaan Farmasi harus berupa angka.'
      ]);

      return;
   }


   $id_permintaan_farmasi = (int) $id_permintaan_farmasi;


   // --------------------------------------------------------
   // QTY
   // --------------------------------------------------------

   $qty = isset($_PUT['qty']) && $_PUT['qty'] !== ''
      ? (int) $_PUT['qty']
      : 1;


   if ($qty <= 0) {
      $qty = 1;
   }


   // --------------------------------------------------------
   // DATA LAIN
   // --------------------------------------------------------

   $signa = $_PUT['signa'] ?? null;

   $catatan = $_PUT['catatan'] ?? null;

   $created_user = $_PUT['created_user'] ?? null;


   // ========================================================
   // AMBIL MASTER PHARMACY
   // ========================================================

   $getPharmacy = $koneksi->prepare("
        SELECT
            pharmacy_name_generic,
            pharmacy_name_trade,
            pharmacy_sale
        FROM ms_pharmacy
        WHERE id_pharmacy = ?
        LIMIT 1
    ");


   if (!$getPharmacy) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query pharmacy.'
      ]);

      return;
   }


   $getPharmacy->bind_param(
      "i",
      $id_pharmacy
   );


   $getPharmacy->execute();


   $result = $getPharmacy->get_result();


   if ($result->num_rows === 0) {

      $getPharmacy->close();

      echo json_encode([
         'status' => 'error',
         'message' => 'Data pharmacy tidak ditemukan.'
      ]);

      return;
   }


   $pharmacy = $result->fetch_assoc();


   $getPharmacy->close();


   // ========================================================
   // ITEM NAME
   // ========================================================

   if (!empty($pharmacy['pharmacy_name_generic'])) {

      $item_name = $pharmacy['pharmacy_name_generic'];
   } else {

      $item_name = $pharmacy['pharmacy_name_trade'];
   }


   // ========================================================
   // HARGA
   // ========================================================

   $harga = isset($pharmacy['pharmacy_sale'])
      ? (int) $pharmacy['pharmacy_sale']
      : 0;


   // ========================================================
   // UPDATE
   // ========================================================

   $query = "
        UPDATE permintaan_pharmacy_details

        SET
            id_permintaan_farmasi = ?,
            id_pharmacy = ?,
            signa = ?,
            qty = ?,
            catatan = ?,
            harga = ?,
            created_user = ?,
            item_name = ?

        WHERE id_pharmacy_details = ?
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Query update gagal disiapkan: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "iisisissi",
      $id_permintaan_farmasi,
      $id_pharmacy,
      $signa,
      $qty,
      $catatan,
      $harga,
      $created_user,
      $item_name,
      $id
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status' => 'success',
         'message' => 'Data berhasil diperbarui.',
         'id' => $id,
         'id_permintaan_farmasi' => $id_permintaan_farmasi,
         'item_name' => $item_name,
         'harga' => $harga
      ]);
   } else {

      echo json_encode([
         'status' => 'error',
         'message' => 'Update gagal: ' . $stmt->error
      ]);
   }


   $stmt->close();
}


/**
 * ============================================================
 * DELETE
 * ============================================================
 */
function deleteData()
{
   global $koneksi;


   $id = $_GET['id'] ?? '';

   $id = trim((string) $id);


   if ($id === '' || !ctype_digit($id)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan atau tidak valid.'
      ]);

      return;
   }


   $id = (int) $id;


   $query = "
        DELETE FROM permintaan_pharmacy_details
        WHERE id_pharmacy_details = ?
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query.'
      ]);

      return;
   }


   $stmt->bind_param(
      "i",
      $id
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status' => 'success',
         'message' => 'Data berhasil dihapus.'
      ]);
   } else {

      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menghapus: ' . $stmt->error
      ]);
   }


   $stmt->close();
}


/**
 * ============================================================
 * APPROVE
 * ============================================================
 */
function approveData($data)
{
   global $koneksi;


   $id = $data['id_pharmacy_details'] ?? '';

   $id = trim((string) $id);


   if ($id === '' || !ctype_digit($id)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID permintaan tidak ditemukan atau tidak valid.'
      ]);

      return;
   }


   $id = (int) $id;


   $query = "
        UPDATE permintaan_pharmacy_details

        SET status_item = 1

        WHERE id_pharmacy_details = ?
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Query gagal disiapkan.'
      ]);

      return;
   }


   $stmt->bind_param(
      "i",
      $id
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status' => 'success',
         'message' => 'Permintaan farmasi berhasil di-approve.'
      ]);
   } else {

      echo json_encode([
         'status' => 'error',
         'message' => 'Approve gagal: ' . $stmt->error
      ]);
   }


   $stmt->close();
}
