<?php
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';

require_once __DIR__ . '/../../utility/security.php';
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
// dulu tanpa cek login/role: siapa pun bisa melihat/menambah/mengubah/menghapus pembayaran kontrak faskes
requireRole(['superadmin', 'administrator']);

/**
 * Generate invoice number berurut
 */
function generateInvoiceNumber($koneksi)
{
   $datePart = date('ymd');
   $like = "INV-$datePart-%";

   $stmt = $koneksi->prepare("
      SELECT invoice_number 
      FROM ms_faskes_payment 
      WHERE invoice_number LIKE ?
      ORDER BY invoice_number DESC 
      LIMIT 1
   ");
   $stmt->bind_param("s", $like);
   $stmt->execute();
   $result = $stmt->get_result();

   $last = 0;
   if ($row = $result->fetch_assoc()) {
      $parts = explode('-', $row['invoice_number']);
      $last = (int) end($parts);
   }

   $next = str_pad($last + 1, 6, '0', STR_PAD_LEFT);
   return "INV-$datePart-$next";
}

switch ($method) {

   // 🔹 GET list / detail
   case 'GET':
      if (isset($_GET['id'])) {
         $stmt = $koneksi->prepare("SELECT * FROM ms_faskes_payment WHERE id_payment=?");
         $stmt->bind_param("i", $_GET['id']);
         $stmt->execute();
         $res = $stmt->get_result()->fetch_assoc();

         echo json_encode(['status' => 'success', 'data' => $res]);
         exit;
      }

      $no = $_GET['no'] ?? '';
      $stmt = $koneksi->prepare("
         SELECT * FROM ms_faskes_payment 
         WHERE order_number=? 
         ORDER BY created_at DESC
      ");
      $stmt->bind_param("s", $no);
      $stmt->execute();
      $res = $stmt->get_result();

      echo json_encode(['status' => 'success', 'data' => $res->fetch_all(MYSQLI_ASSOC)]);
      break;

   // 🔹 INSERT
   case 'POST':
      $invoice = generateInvoiceNumber($koneksi);

      $stmt = $koneksi->prepare("
         INSERT INTO ms_faskes_payment
         (invoice_number, order_number, payment_date, payment_method, payment_amount, payment_note)
         VALUES (?,?,?,?,?,?)
      ");

      $stmt->bind_param(
         "ssssis",
         $invoice,
         $_POST['order_number'],
         $_POST['tanggal'],
         $_POST['metode'],
         $_POST['nominal'],
         $_POST['keterangan']
      );

      $ok = $stmt->execute();
      if ($ok) logBaris($koneksi, 'pengaturan', 'ms_faskes_payment', 'id_payment', $stmt->insert_id, null, ['tipe' => 'pembayaran_faskes', 'label' => $invoice . ' · ' . ($_POST['order_number'] ?? '')], 'Pembayaran faskes ditambahkan');
      echo json_encode([
         'status' => $ok ? 'success' : 'error',
         'message' => 'Data disimpan'
      ]);
      break;

   // 🔹 UPDATE
   case 'PUT':
      parse_str(file_get_contents("php://input"), $_PUT);
      $lgLama = logAmbilBaris($koneksi, 'ms_faskes_payment', 'id_payment', $_PUT['id_payment'] ?? '');

      $stmt = $koneksi->prepare("
         UPDATE ms_faskes_payment SET
         payment_date=?,
         payment_method=?,
         payment_amount=?,
         payment_note=?
         WHERE id_payment=?
      ");

      $stmt->bind_param(
         "ssisi",
         $_PUT['tanggal'],
         $_PUT['metode'],
         $_PUT['nominal'],
         $_PUT['keterangan'],
         $_PUT['id_payment']
      );

      $ok = $stmt->execute();
      if ($ok && $lgLama) logBaris($koneksi, 'pengaturan', 'ms_faskes_payment', 'id_payment', $_PUT['id_payment'], $lgLama, ['tipe' => 'pembayaran_faskes', 'label' => $lgLama['invoice_number'] . ' · ' . $lgLama['order_number']], 'Pembayaran faskes diubah');
      echo json_encode([
         'status' => $ok ? 'success' : 'error',
         'message' => 'Data diupdate'
      ]);
      break;

   // 🔹 DELETE
   case 'DELETE':
      $id = $_GET['id'];
      $lgLama = logAmbilBaris($koneksi, 'ms_faskes_payment', 'id_payment', $id);

      $stmt = $koneksi->prepare("
         DELETE FROM ms_faskes_payment 
         WHERE id_payment=?
      ");
      $stmt->bind_param("i", $id);

      $ok = $stmt->execute();
      if ($ok && $lgLama && $stmt->affected_rows > 0) logBaris($koneksi, 'pengaturan', 'ms_faskes_payment', 'id_payment', $id, $lgLama, ['tipe' => 'pembayaran_faskes', 'label' => $lgLama['invoice_number'] . ' · ' . $lgLama['order_number']], 'Pembayaran faskes dihapus');
      echo json_encode([
         'status' => $ok ? 'success' : 'error'
      ]);
      break;
}
