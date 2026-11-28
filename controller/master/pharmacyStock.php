<?php
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/pembelianGuard.php';
pembelianWajibLogin();
pembelianSiapkan($koneksi);
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id']);
      } else {
         getData();
      }
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}



function getData()
{
   global $koneksi;
   $idc = pembelianIdc();
   [$w, $t, $p] = pembelianFilter('d');

   // dulu: semua obat semua faskes + pembelian/pengeluaran semua faskes ikut terjumlah
   $query = "
      SELECT 
         p.*, 
         -- Hitung penerimaan (stok masuk) dari pembelian faskes ini
         (
            SELECT IFNULL(SUM(buy_qty), 0)
            FROM pharmacy_buy_detail d
            WHERE d.buy_item LIKE CONCAT('%', p.pharmacy_name_generic, '%') AND $w
         ) AS total_penerimaan,

         -- Hitung pengeluaran (stok keluar) faskes ini
         (
            SELECT IFNULL(SUM(qty), 0)
            FROM permintaan_pharmacy m
            WHERE m.id_pharmacy = p.id_pharmacy AND m.id_customer = ?
         ) AS total_pengeluaran,

         -- Stock akhir
         (
            (
               SELECT IFNULL(SUM(buy_qty), 0)
               FROM pharmacy_buy_detail d
               WHERE d.buy_item LIKE CONCAT('%', p.pharmacy_name_generic, '%') AND $w
            )
            -
            (
               SELECT IFNULL(SUM(qty), 0)
               FROM permintaan_pharmacy m
               WHERE m.id_pharmacy = p.id_pharmacy AND m.id_customer = ?
            )
         ) AS stock_akhir

      FROM ms_pharmacy p
      LEFT JOIN ms_pharmacy_parrent lg ON lg.parent_id = p.id_pharmacy AND lg.id_customer_real = ?
      WHERE p.id_customer = ? OR (p.id_customer = 0 AND lg.id IS NULL)
      ORDER BY p.pharmacy_name_generic DESC
   ";

   $st = $koneksi->prepare($query);
   $result = false;
   if ($st) {
      $st->bind_param($t . 'i' . $t . 'iii', ...array_merge($p, [$idc], $p, [$idc, $idc, $idc]));
      $result = $st->execute() ? $st->get_result() : false;
   }

   if (!$result) {
      http_response_code(500);
      error_log('[pharmacyStock] ' . ($st ? $st->error : $koneksi->error));
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data stok.'
      ]);
      return;
   }
   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
   mysqli_free_result($result);
   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}

// Function untuk Read User berdasarkan ID
function  getID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   // hanya obat yang bisa diakses faskes ini (milik sendiri / master bawaan yang tidak disembunyikan)
   $idc = pembelianIdc();
   $query = "SELECT p.* FROM ms_pharmacy p
      LEFT JOIN ms_pharmacy_parrent lg ON lg.parent_id = p.id_pharmacy AND lg.id_customer_real = ?
      WHERE p.id_pharmacy = ? AND (p.id_customer = ? OR (p.id_customer = 0 AND lg.id IS NULL))";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("isi", $idc, $iduser, $idc);
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
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query.'
      ]);
   }
}
