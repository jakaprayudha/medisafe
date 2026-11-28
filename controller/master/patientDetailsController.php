<?php
// Detail & ubah data pasien (module/admin/patient_details).
//   GET  ?pt=ID_PATIENT            -> { success, data }
//   POST _method=PUT, id_patient   -> ubah field yang dikirim
// Hanya pasien milik faskes sesi.
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
mysqli_report(MYSQLI_REPORT_OFF);
header("Content-Type: application/json; charset=utf-8");

function jawab(array $data, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($data);
   exit;
}

if (empty($_SESSION['id_customer'])) {
   jawab(["success" => false, "message" => "Session tidak valid / expired"], 401);
}
$idc = (int) $_SESSION['id_customer'];

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}

function pasienFaskes(mysqli $k, int $id, int $idc): ?array
{
   $stmt = $k->prepare("SELECT * FROM ms_patient WHERE id_patient = ? AND id_customer = ? LIMIT 1");
   $stmt->bind_param('ii', $id, $idc);
   $stmt->execute();
   $row = $stmt->get_result()->fetch_assoc();
   $stmt->close();
   return $row ?: null;
}

if ($method === 'GET') {
   $id = (int) ($_GET['pt'] ?? 0);
   $row = $id > 0 ? pasienFaskes($koneksi, $id, $idc) : null;
   jawab($row ? ["success" => true, "data" => $row] : ["success" => false, "message" => "Pasien tidak ditemukan"], $row ? 200 : 404);
}

if ($method === 'PUT') {
   $id = (int) ($_POST['id_patient'] ?? 0);
   $lama = $id > 0 ? pasienFaskes($koneksi, $id, $idc) : null;
   if (!$lama) jawab(["success" => false, "message" => "Pasien tidak ditemukan"], 404);

   $allowedFields = [
      'patient_nik', 'patient_kk', 'patient_bpjs', 'nomor_rm', 'patient_datebirth', 'patient_religion',
      'patient_gender', 'patient_address', 'patient_place', 'patient_notes', 'patient_provinsi',
      'patient_kabupaten', 'patient_kecamatan', 'patient_kelurahan', 'patient_desa', 'patient_phone',
      'patient_name', 'patient_blood', 'patient_mail', 'patient_marital_status', 'patient_nationality',
      'patient_education', 'patient_occupation', 'patient_emergency_contact_name',
      'patient_emergency_contact_relation', 'patient_emergency_contact_phone', 'patient_allergy',
      'patient_disability', 'nomor_rm_any',
   ];
   $data = [];
   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) $data[$f] = trim((string) $_POST[$f]);
   }
   if (!$data) jawab(["success" => false, "message" => "Tidak ada field yang dikirim"], 422);

   // validasi field wajib bila dikirim
   foreach (['patient_name' => 'Nama pasien', 'nomor_rm' => 'Nomor rekam medis'] as $f => $label) {
      if (array_key_exists($f, $data) && $data[$f] === '') jawab(["success" => false, "message" => "$label wajib diisi"], 422);
   }
   if (!empty($data['patient_mail']) && !filter_var($data['patient_mail'], FILTER_VALIDATE_EMAIL)) {
      jawab(["success" => false, "message" => "Format email tidak valid"], 422);
   }
   if (!empty($data['patient_datebirth']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['patient_datebirth'])) {
      jawab(["success" => false, "message" => "Format tanggal lahir tidak valid"], 422);
   }
   // nomor RM tidak boleh dipakai pasien lain di faskes yang sama
   if (isset($data['nomor_rm']) && trim((string) $lama['nomor_rm']) !== $data['nomor_rm']) {
      $stmt = $koneksi->prepare("SELECT patient_name FROM ms_patient WHERE id_customer = ? AND nomor_rm = ? AND id_patient <> ? LIMIT 1");
      $stmt->bind_param('isi', $idc, $data['nomor_rm'], $id);
      $stmt->execute();
      $dup = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      if ($dup) jawab(["success" => false, "message" => "Nomor RM {$data['nomor_rm']} sudah dipakai pasien {$dup['patient_name']}"], 409);
   }

   $set = implode(', ', array_map(fn($f) => "$f = ?", array_keys($data))) . ', updated_at = NOW()';
   $stmt = $koneksi->prepare("UPDATE ms_patient SET $set WHERE id_patient = ? AND id_customer = ?");
   $vals = array_merge(array_values($data), [$id, $idc]);
   $stmt->bind_param(str_repeat('s', count($data)) . 'ii', ...$vals);
   $ok = $stmt->execute();
   $err = $stmt->error;
   $stmt->close();
   if (!$ok) {
      error_log('[patientDetailsController] ' . $err);
      jawab(["success" => false, "message" => "Gagal menyimpan data pasien"], 500);
   }
   [$l, $b] = logBeda($lama, $data);
   if ($b) {
      $penting = array_intersect(array_keys($b), ['nomor_rm', 'patient_name', 'patient_nik', 'patient_kk', 'patient_bpjs', 'patient_datebirth', 'patient_gender']);
      logAktivitas($koneksi, 'pasien', 'ubah', ['tipe' => 'pasien', 'id' => $id, 'label' => $lama['nomor_rm'] . ' · ' . $lama['patient_name']],
         $penting ? 'Ubah identitas: ' . implode(', ', $penting) : 'Ubah data pasien (detail)', $l, $b);
   }
   jawab(["success" => true, "message" => "Data pasien berhasil diperbarui"]);
}

jawab(["success" => false, "message" => "Method $method tidak diizinkan"], 405);
