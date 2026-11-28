<?php
/**
 * Detail tenaga kesehatan: GET ?no=nurse_number, PUT (POST + _method=PUT) nurse_number + field.
 *
 * Perbaikan: query ditempel langsung (SQL injection) dan tanpa filter faskes (nakes faskes lain bisa dibaca/diubah);
 * tidak ada log.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
mysqli_report(MYSQLI_REPORT_OFF);
header("Content-Type: application/json");

function ndBalas(array $d, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($d);
   exit;
}

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if ($idc <= 0) ndBalas(["success" => false, "message" => "Session tidak valid / expired"], 401);

function ndAmbil(mysqli $k, string $no, int $idc): ?array
{
   $st = $k->prepare("SELECT * FROM ms_nurse WHERE nurse_number = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('si', $no, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

$method = $_SERVER['REQUEST_METHOD'];

// Gunakan _method untuk spoof PUT/DELETE
if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}

switch ($method) {
   case 'GET': // get data by nurse_number
      if (!isset($_GET['no'])) ndBalas(["success" => false, "message" => "Parameter no tidak ditemukan"]);
      $row = ndAmbil($koneksi, (string) $_GET['no'], $idc);
      ndBalas($row ? ["success" => true, "data" => $row] : ["success" => false, "message" => "Dokter tidak ditemukan"]);
      break;

   case 'PUT': // update data
      if (!isset($_POST['nurse_number'])) ndBalas(["success" => false, "message" => "nurse_number tidak ditemukan"]);
      $no = (string) $_POST['nurse_number'];
      $lama = ndAmbil($koneksi, $no, $idc);
      if (!$lama) ndBalas(["success" => false, "message" => "Data tidak ditemukan"], 404);

      // daftar field yang boleh diupdate
      $allowedFields = [
         'nurse_name',
         'nurse_profesi',
         'nurse_category',
         'nurse_status',
         'nurse_phone',
         'nurse_mail',
         'nurse_birthdate',
         'nurse_gender',
         'nurse_address',
         'nurse_sip',
         'nurse_str',
         'nurse_expaired'
      ];

      $updates = $vals = [];
      foreach ($allowedFields as $field) {
         if (isset($_POST[$field])) {
            $updates[] = "$field = ?";
            $vals[] = (string) $_POST[$field];
         }
      }
      if (!$updates) ndBalas(["success" => false, "message" => "Tidak ada field yang dikirim"]);

      $updates[] = "updated_at = NOW()";
      $vals[] = $no;
      $vals[] = $idc;
      $st = $koneksi->prepare("UPDATE ms_nurse SET " . implode(", ", $updates) . " WHERE nurse_number = ? AND id_customer = ?");
      if (!$st) ndBalas(["success" => false, "message" => "Gagal update: " . $koneksi->error]);
      $st->bind_param(str_repeat('s', count($vals) - 1) . 'i', ...$vals);
      if (!$st->execute()) ndBalas(["success" => false, "message" => "Gagal update: " . $st->error]);
      $st->close();

      logBaris($koneksi, 'master', 'ms_nurse', 'id_nurse', $lama['id_nurse'], $lama, ['tipe' => 'nakes', 'label' => $lama['nurse_name']], 'Detail tenaga kesehatan diubah');
      ndBalas(["success" => true, "message" => "Data dokter berhasil diperbarui"]);
      break;

   default:
      ndBalas(["success" => false, "message" => "Method $method tidak diizinkan"]);
      break;
}
