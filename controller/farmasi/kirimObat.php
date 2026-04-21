<?php
require_once __DIR__ . '/../admisi/services/view.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../admisi/services/servicebpjs.php';

header('Content-Type: application/json');

// 🔥 SAFE SESSION
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

// ================== INPUT ==================
$data_obat = json_decode(file_get_contents("php://input"), true);
$id = $data_obat['id'] ?? null;
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id || !$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data tidak valid'
   ]);
   exit;
}

// ================== GET VISIT ==================
$stmt = $koneksi->prepare("
   SELECT id_visit 
   FROM permintaan_pharmacy 
   WHERE id_permintaan_farmasi = ? 
   AND id_customer = ?
");
$stmt->bind_param('ss', $id, $id_customer);
$stmt->execute();
$cek = $stmt->get_result()->fetch_assoc();

if (!$cek) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data permintaan tidak ditemukan'
   ]);
   exit;
}

$nomor_visit = $cek['id_visit'];

// ================== CEK PROVIDER ==================
$stmtProv = $koneksi->prepare("
   SELECT id_provider 
   FROM pasien_visit 
   WHERE visit_ID = ? AND id_customer = ?
");
$stmtProv->bind_param("ss", $nomor_visit, $id_customer);
$stmtProv->execute();
$prov = $stmtProv->get_result()->fetch_assoc();

$id_provider = $prov['id_provider'] ?? null;

// ================== DETAIL OBAT ==================
$stmt2 = $koneksi->prepare("
   SELECT p.pharmacy_name_generic, pd.qty, pd.signa 
   FROM permintaan_pharmacy_details AS pd 
   INNER JOIN ms_pharmacy AS p 
   ON p.id_pharmacy = pd.id_pharmacy 
   WHERE id_permintaan_farmasi = ?
");
$stmt2->bind_param('s', $id);
$stmt2->execute();

$result = $stmt2->get_result();
$data_detailobat = $result->fetch_all(MYSQLI_ASSOC);

$terapiObatArr = [];
foreach ($data_detailobat as $row) {
   $terapiObatArr[] = $row['pharmacy_name_generic'] . '/' . $row['signa'] . '/' . $row['qty'];
}
$terapiObat = implode(', ', $terapiObatArr);

// ================== JIKA BUKAN BPJS ==================
if ($id_provider != 1) {

   $stmt3 = $koneksi->prepare("
      UPDATE permintaan_pharmacy 
      SET status_permintaan = 1 
      WHERE id_permintaan_farmasi = ? 
      AND id_customer = ?
   ");

   $stmt3->bind_param("ii", $id, $id_customer);

   if ($stmt3->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Berhasil (pasien umum, tidak kirim BPJS)'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => $stmt3->error
      ]);
   }

   // close
   $stmt->close();
   $stmt2->close();
   $stmtProv->close();
   $stmt3->close();
   exit;
}

// ================== DATA PASIEN (KHUSUS BPJS) ==================
$stmt1 = $koneksi->prepare("
   SELECT pv.*, pk.*, p.patient_datebirth
   FROM pasien_visit AS pv
   INNER JOIN pcare_kunjungan AS pk ON pv.noKunjung = pk.noKunjungan
   INNER JOIN ms_patient AS p ON p.patient_bpjs = pv.noKartu
   WHERE pv.visit_ID = ? AND pv.id_customer = ?
");
$stmt1->bind_param('ss', $nomor_visit, $id_customer);
$stmt1->execute();

$data = $stmt1->get_result()->fetch_assoc();

if (!$data) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data BPJS tidak ditemukan'
   ]);
   exit;
}

// ================== PAYLOAD ==================
$payload = [
   "noKunjungan" => $data['noKunjungan'] ?? null,
   "noKartu" => $data['noKartu'] ?? null,
   "tglDaftar" => !empty($data['tglDaftar']) ? date("d-m-Y", strtotime($data['tglDaftar'])) : null,
   "kdPoli" => $data['kdPoli'] ?? null,
   "keluhan" => $data['keluhan'] ?? null,
   "kdSadar" => $data['kdSadar'] ?? null,
   "sistole" => $data['sistole'] ?? null,
   "diastole" => $data['diastole'] ?? null,
   "terapiObat" => $terapiObat
];

// ================== HIT BPJS ==================
$result = bpjsPost("/kunjungan/v1", $payload, 'PUT');

if ($result['code'] != "200") {
   echo json_encode([
      'status' => 'error',
      'message' => $result['message'] ?? 'BPJS tidak tersedia'
   ]);
   exit;
}

// ================== UPDATE STATUS ==================
$stmt3 = $koneksi->prepare("
   UPDATE permintaan_pharmacy 
   SET status_permintaan = 1 
   WHERE id_permintaan_farmasi = ? 
   AND id_customer = ?
");

$stmt3->bind_param("ii", $id, $id_customer);

if ($stmt3->execute()) {
   echo json_encode([
      'status' => 'success',
      'message' => 'Berhasil kirim ke farmasi & BPJS'
   ]);
} else {
   echo json_encode([
      'status' => 'error',
      'message' => $stmt3->error
   ]);
}

// ================== CLOSE ==================
$stmt->close();
$stmt1->close();
$stmt2->close();
$stmtProv->close();

if (isset($stmt3)) {
   $stmt3->close();
}
