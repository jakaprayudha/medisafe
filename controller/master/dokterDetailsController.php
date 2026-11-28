<?php
/**
 * Detail dokter (module/admin/doctor_details): GET ?no=doctor_number, PUT (POST + _method=PUT) doctor_number + field.
 *
 * Perbaikan: query ditempel langsung (SQL injection) dan tanpa filter faskes (dokter faskes lain bisa dibaca/diubah);
 * tidak ada log.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
mysqli_report(MYSQLI_REPORT_OFF);
header("Content-Type: application/json");

function ddBalas(array $d, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($d);
   exit;
}

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if ($idc <= 0) ddBalas(["success" => false, "message" => "Session tidak valid / expired"], 401);

/** Dokter milik faskes session, atau null. */
function ddAmbil(mysqli $k, string $no, int $idc): ?array
{
   $st = $k->prepare("SELECT * FROM ms_doctor WHERE doctor_number = ? AND id_customer = ? LIMIT 1");
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
   case 'GET': // get data by doctor_number
      if (!isset($_GET['no'])) ddBalas(["success" => false, "message" => "Parameter no tidak ditemukan"]);
      $row = ddAmbil($koneksi, (string) $_GET['no'], $idc);
      ddBalas($row ? ["success" => true, "data" => $row] : ["success" => false, "message" => "Dokter tidak ditemukan"]);
      break;

   case 'PUT': // update data
      if (!isset($_POST['doctor_number'])) ddBalas(["success" => false, "message" => "doctor_number tidak ditemukan"]);
      $doctorNo = (string) $_POST['doctor_number'];
      $lama = ddAmbil($koneksi, $doctorNo, $idc);
      if (!$lama) ddBalas(["success" => false, "message" => "Dokter tidak ditemukan"], 404);

      // daftar field yang boleh diupdate
      $allowedFields = [
         'doctor_name',
         'id_poli',
         'doctor_category',
         'doctor_status',
         'doctor_phone',
         'doctor_mail',
         'doctor_birthdate',
         'doctor_gender',
         'doctor_address',
         'doctor_sip',
         'doctor_str',
         'doctor_nik',
         'doctor_expaired',
         'doctor_his',
         'str_lembaga',
         'str_number',
         'str_date_expaired',
         'sip_lembaga',
         'sip_number',
         'sip_date_expaired',
         'doctor_title_front',
         'doctor_title_back',
         'doctor_antrean',
         'estimated_services',
      ];

      // handle STR lifetime: override jadi NULL
      $strLifetime = isset($_POST['str_lifetime']);

      $updates = $vals = [];
      foreach ($allowedFields as $field) {
         if ($field === 'str_date_expaired' && $strLifetime) continue;
         if (isset($_POST[$field])) {
            $updates[] = "$field = ?";
            $vals[] = (string) $_POST[$field];
         }
      }
      if ($strLifetime) $updates[] = "str_date_expaired = NULL";

      if (!$updates) ddBalas(["success" => false, "message" => "Tidak ada field yang dikirim"]);
      $updates[] = "updated_at = NOW()";
      $vals[] = $doctorNo;
      $vals[] = $idc;
      $st = $koneksi->prepare("UPDATE ms_doctor SET " . implode(", ", $updates) . " WHERE doctor_number = ? AND id_customer = ?");
      if (!$st) ddBalas(["success" => false, "message" => "Gagal update: " . $koneksi->error]);
      $st->bind_param(str_repeat('s', count($vals) - 1) . 'i', ...$vals);
      if (!$st->execute()) ddBalas(["success" => false, "message" => "Gagal update: " . $st->error]);
      $st->close();

      logBaris($koneksi, 'master', 'ms_doctor', 'id_doctor', $lama['id_doctor'], $lama, ['tipe' => 'dokter', 'label' => $lama['doctor_name']], 'Detail dokter diubah');
      ddBalas(["success" => true, "message" => "Data dokter berhasil diperbarui"]);
      break;

   default:
      ddBalas(["success" => false, "message" => "Method $method tidak diizinkan"]);
      break;
}
