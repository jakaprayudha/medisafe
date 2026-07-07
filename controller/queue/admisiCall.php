<?php
header('Content-Type: application/json');
include '../../database/connect.php';

date_default_timezone_set('Asia/Jakarta');
$today = date('Y-m-d');

$input = json_decode(file_get_contents('php://input'), true);
$action  = $input['action'] ?? '';
$counter = (int)($input['counter'] ?? 0);

if (!$action || !$counter) {
   echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
   exit;
}

/* =========================
   PANGGIL BERIKUTNYA
========================= */
if ($action === 'call') {

   // Cek apakah counter sedang memanggil
   $check = mysqli_query($koneksi, "
      SELECT id_queue FROM transaction_queue
      WHERE counter = $counter
        AND status = 'dipanggil'
        AND queue_date = '$today'
      LIMIT 1
   ");

   if (mysqli_num_rows($check) > 0) {
      echo json_encode(['status'=>'error','message'=>'Masih ada antrean aktif']);
      exit;
   }

   // Ambil antrean berikutnya
   $next = mysqli_query($koneksi, "
      SELECT id_queue
      FROM transaction_queue
      WHERE queue_date = '$today'
        AND status = 'menunggu'
      ORDER BY created_at ASC
      LIMIT 1
   ");

   if ($row = mysqli_fetch_assoc($next)) {
      mysqli_query($koneksi, "
         UPDATE transaction_queue
         SET status='dipanggil', counter=$counter
         WHERE id_queue={$row['id_queue']}
      ");
   }
}

/* =========================
   LEWATI
========================= */
if ($action === 'skip') {
   mysqli_query($koneksi, "
      UPDATE transaction_queue
      SET status = 'skip'
      WHERE counter = $counter
        AND status = 'dipanggil'
        AND queue_date = '$today'
   ");
}

/* =========================
   SELESAI
========================= */
if ($action === 'finish') {
   mysqli_query($koneksi, "
      UPDATE transaction_queue
      SET status = 'selesai'
      WHERE counter = $counter
        AND status = 'dipanggil'
        AND queue_date = '$today'
   ");
}

echo json_encode(['status' => 'success']);