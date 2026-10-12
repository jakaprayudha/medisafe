<?php

require_once __DIR__ . '/../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
   http_response_code(400);
   echo json_encode(["status" => "error", "message" => "Data JSON tidak valid"]);
   exit;
}

$action = $data['action'] ?? '';
$id_customer = filter_var($_SESSION['id_customer'] ?? null, FILTER_VALIDATE_INT);
$visit_ID = $data['visit_ID'] ?? '';

if (!$id_customer) {
   http_response_code(401);
   echo json_encode(["status" => "error", "message" => "Session klinik tidak ditemukan, silakan login kembali"]);
   exit;
}

if (!in_array($action, ['insert', 'update', 'delete'], true)) {
   http_response_code(400);
   echo json_encode(["status" => "error", "message" => "Action tidak ditemukan"]);
   exit;
}

if (!is_string($visit_ID) || trim($visit_ID) === '') {
   http_response_code(400);
   echo json_encode(["status" => "error", "message" => "visit_ID wajib diisi"]);
   exit;
}

$no_gigi = filter_var($data['no_gigi'] ?? null, FILTER_VALIDATE_INT);
$valid_teeth = array_merge(
   range(11, 18), range(21, 28), range(31, 38), range(41, 48),
   range(51, 55), range(61, 65), range(71, 75), range(81, 85)
);

if (!in_array($no_gigi, $valid_teeth, true)) {
   http_response_code(400);
   echo json_encode(["status" => "error", "message" => "Nomor gigi tidak valid"]);
   exit;
}

$old_no_gigi = filter_var($data['old_no_gigi'] ?? null, FILTER_VALIDATE_INT);
if ($action === 'update' && !in_array($old_no_gigi, $valid_teeth, true)) {
   http_response_code(400);
   echo json_encode(["status" => "error", "message" => "Nomor gigi yang akan diubah tidak valid"]);
   exit;
}

$fields = [];
foreach (['elemen', 'elemen_gigi', 'diagnosa', 'prosedur', 'keterangan'] as $field) {
   $value = $data[$field] ?? '';
   if (!is_string($value)) {
      http_response_code(400);
      echo json_encode(["status" => "error", "message" => "Nilai $field tidak valid"]);
      exit;
   }
   $fields[$field] = trim($value);
}

if ($action !== 'delete' && $fields['diagnosa'] === '') {
   http_response_code(400);
   echo json_encode(["status" => "error", "message" => "Diagnosa wajib diisi"]);
   exit;
}

$elemen = $fields['elemen'];
$elemen_gigi = $fields['elemen_gigi'];
$diagnosa = $fields['diagnosa'];
$prosedur = $fields['prosedur'];
$keterangan = $fields['keterangan'];

try {
   $visit = $koneksi->prepare("SELECT 1 FROM pasien_visit WHERE visit_ID = ? AND id_customer = ?");
   $visit->bind_param("si", $visit_ID, $id_customer);
   $visit->execute();

   if (!$visit->get_result()->fetch_row()) {
      http_response_code(404);
      echo json_encode(["status" => "error", "message" => "Kunjungan tidak ditemukan untuk klinik ini"]);
      exit;
   }

   if ($action !== 'insert') {
      $existing_no_gigi = $action === 'update' ? $old_no_gigi : $no_gigi;
      $existing = $koneksi->prepare("SELECT 1 FROM odontogram WHERE visit_ID = ? AND no_gigi = ? AND id_customer = ?");
      $existing->bind_param("sii", $visit_ID, $existing_no_gigi, $id_customer);
      $existing->execute();
      if (!$existing->get_result()->fetch_row()) {
         http_response_code(404);
         echo json_encode(["status" => "error", "message" => "Data odontogram tidak ditemukan"]);
         exit;
      }
   }

   if ($action === 'insert') {
      $query = $koneksi->prepare("
         INSERT INTO odontogram
         (visit_ID, id_customer, no_gigi, elemen, elemen_gigi, diagnosa, prosedur, keterangan)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
      ");
      $query->bind_param(
         "siisssss",
         $visit_ID,
         $id_customer,
         $no_gigi,
         $elemen,
         $elemen_gigi,
         $diagnosa,
         $prosedur,
         $keterangan
      );
      $message = "Data berhasil disimpan";
   } elseif ($action === 'update') {
      $query = $koneksi->prepare("
         UPDATE odontogram
         SET no_gigi = ?, elemen = ?, elemen_gigi = ?, diagnosa = ?, prosedur = ?, keterangan = ?
         WHERE visit_ID = ? AND no_gigi = ? AND id_customer = ?
      ");
      $query->bind_param(
         "issssssii",
         $no_gigi,
         $elemen,
         $elemen_gigi,
         $diagnosa,
         $prosedur,
         $keterangan,
         $visit_ID,
         $old_no_gigi,
         $id_customer
      );
      $message = "Data berhasil diubah";
   } else {
      $query = $koneksi->prepare("
         DELETE FROM odontogram
         WHERE visit_ID = ? AND no_gigi = ? AND id_customer = ?
      ");
      $query->bind_param("sii", $visit_ID, $no_gigi, $id_customer);
      $message = "Data berhasil dihapus";
   }

   $query->execute();
   echo json_encode(["status" => "success", "message" => $message]);
} catch (mysqli_sql_exception $error) {
   error_log('Odontogram ' . $action . ': ' . $error->getMessage());
   http_response_code(500);
   echo json_encode(["status" => "error", "message" => "Gagal menyimpan perubahan odontogram. Silakan coba kembali atau hubungi administrator."]);
}
