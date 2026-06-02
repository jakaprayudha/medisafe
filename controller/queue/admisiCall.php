<?php
header('Content-Type: application/json');
include '../../database/connect.php';
require_once __DIR__ . '/../socket/sendSocket.php';
$kdRumahSakit = $_SESSION['id_customer'];
$type = $_POST['type'];
$nomor = $_POST['nomor'];
$loket = $_POST['loket'];
$name = $_POST['name'];
$id = (int)$_POST['id'];
$idAntri = $_POST['idAntri'];

/* =========================
   PANGGIL BERIKUTNYA
========================= */
if ($type === 'call') {

   $check = mysqli_query($koneksi, "SELECT * FROM `transaction_queue` WHERE `id_queue` = '$idAntri' ORDER BY created_at ASC LIMIT 1");
   $result = mysqli_fetch_assoc($check);
   if ($result['counter'] == NULL) {
      mysqli_query($koneksi, "UPDATE `transaction_queue` SET status='dipanggil', counter='$id' WHERE `id_queue`='$idAntri'");
   }
   callAntrian([
      "rs_id" => $kdRumahSakit,
      "target_role" => "display-admisi",
      "idantrian" => $idAntri,
      "type" => $name,
      "nomor"  => $nomor,
      "loket" => $loket
   ]);
   echo json_encode(['status' => 'success']);
}

/* =========================
   LEWATI
========================= */
// if ($action === 'skip') {
//    mysqli_query($koneksi, "
//       UPDATE transaction_queue
//       SET status = 'skip'
//       WHERE counter = $counter
//         AND status = 'dipanggil'
//         AND queue_date = '$today'
//    ");
// }


// echo json_encode(['status' => 'success']);