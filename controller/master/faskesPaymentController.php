<?php
include '../../database/connect.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

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

      echo json_encode([
         'status' => $stmt->execute() ? 'success' : 'error',
         'message' => 'Data disimpan'
      ]);
      break;

   // 🔹 UPDATE
   case 'PUT':
      parse_str(file_get_contents("php://input"), $_PUT);

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

      echo json_encode([
         'status' => $stmt->execute() ? 'success' : 'error',
         'message' => 'Data diupdate'
      ]);
      break;

   // 🔹 DELETE
   case 'DELETE':
      $id = $_GET['id'];

      $stmt = $koneksi->prepare("
         DELETE FROM ms_faskes_payment 
         WHERE id_payment=?
      ");
      $stmt->bind_param("i", $id);

      echo json_encode([
         'status' => $stmt->execute() ? 'success' : 'error'
      ]);
      break;
}
