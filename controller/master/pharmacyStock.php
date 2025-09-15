<?php
include '../../database/connect.php';
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

   $query = "
      SELECT 
         p.*, 
         -- Hitung penerimaan (stok masuk)
         (
            SELECT IFNULL(SUM(buy_qty), 0)
            FROM pharmacy_buy_detail d
            WHERE d.buy_item LIKE CONCAT('%', p.pharmacy_name_generic, '%')
         ) AS total_penerimaan,

         -- Hitung pengeluaran (stok keluar)
         (
            SELECT IFNULL(SUM(qty), 0)
            FROM permintaan_pharmacy m
            WHERE m.id_pharmacy = p.id_pharmacy
         ) AS total_pengeluaran,

         -- Stock akhir
         (
            (
               SELECT IFNULL(SUM(buy_qty), 0)
               FROM pharmacy_buy_detail d
               WHERE d.buy_item LIKE CONCAT('%', p.pharmacy_name_generic, '%')
            )
            -
            (
               SELECT IFNULL(SUM(qty), 0)
               FROM permintaan_pharmacy m
               WHERE m.id_pharmacy = p.id_pharmacy
            )
         ) AS stock_akhir

      FROM ms_pharmacy p
      ORDER BY p.pharmacy_name_generic DESC
   ";

   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
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
   $query = "SELECT * FROM ms_pharmacy WHERE id_pharmacy = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $iduser); // Bind parameter iduser
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
