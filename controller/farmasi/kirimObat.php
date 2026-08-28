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

$stmt_statusRI = $koneksi->prepare("SELECT status_rawatinap FROM pasien_visit WHERE id_customer = ? AND visit_ID = ?");
$stmt_statusRI->bind_param("ss", $id_customer, $nomor_visit);
$stmt_statusRI->execute();
$resultRI = $stmt_statusRI->get_result()->fetch_assoc();

$status_RI = $resultRI['status_rawatinap'] ?? null;

// Deklarasi variabel statement agar aman saat .close()
$stmt1 = null;
$stmt2 = null;
$stmt3 = null;

// ================== JIKA BUKAN BPJS ==================
if ($id_provider != 1 || $status_RI == '1') {
   $stmt3 = $koneksi->prepare("
      UPDATE permintaan_pharmacy 
      SET status_permintaan = 1 
      WHERE id_permintaan_farmasi = ? 
      AND id_customer = ?
   ");
   // Ubah "ii" menjadi "ss" (atau sesuaikan jika kolom bertipe integer di DB, misal "is")
   $stmt3->bind_param("ss", $id, $id_customer);
   
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
} else {
   $stmt2 = $koneksi->prepare("SELECT p.pharmacy_name_generic, pd.qty, pd.signa FROM permintaan_pharmacy_details AS pd INNER JOIN ms_pharmacy AS p ON p.id_pharmacy = pd.id_pharmacy WHERE id_permintaan_farmasi = ?");
   $stmt2->bind_param('s', $id);
   $stmt2->execute();
   $result = $stmt2->get_result();
   $data_detailobat = $result->fetch_all(MYSQLI_ASSOC);
   
   $terapiObatArr = [];
   foreach ($data_detailobat as $row) {
      $terapiObatArr[] = $row['pharmacy_name_generic'] . '/' . $row['signa'] . '/' . $row['qty'];
   }
   $terapiObat = implode(', ', $terapiObatArr);

   $stmt1 = $koneksi->prepare("SELECT pv.visit_ID,pv.id_patient,pv.visit_notes,pv.saturasi,pv.tindakan,p.patient_datebirth,CONCAT(TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()), ' Tahun ',TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12, ' Bulan ',DATEDIFF(CURDATE(),DATE_ADD(DATE_ADD(p.patient_datebirth,INTERVAL TIMESTAMPDIFF(YEAR, p.patient_datebirth, CURDATE()) YEAR),INTERVAL (TIMESTAMPDIFF(MONTH, p.patient_datebirth, CURDATE()) % 12) MONTH)), ' Hari') AS umur, pk.* FROM pasien_visit AS pv INNER JOIN pcare_kunjungan AS pk ON pv.noKunjung = pk.noKunjungan INNER JOIN ms_patient AS p ON p.patient_bpjs = pv.noKartu WHERE pv.visit_ID = ? AND pv.id_customer = ?");
   $stmt1->bind_param('ss', $nomor_visit, $id_customer);
   $stmt1->execute();
   $data = $stmt1->get_result()->fetch_assoc();
   $noKunjungan = $data['noKunjungan'];

   $payload = [
      "noKunjungan" => $noKunjungan,
      "noKartu" => $data['noKartu'] ?? null,
      "tglDaftar" => !empty($data['tglDaftar']) ? date("d-m-Y", strtotime($data['tglDaftar'])) : null,
      "kdPoli" => $data['kdPoli'] ?? null,
      "keluhan" => $data['keluhan'] ?? null,
      "kdSadar" => $data['kdSadar'] ?? null,
      "sistole" => $data['sistole'] ?? null,
      "diastole" => $data['diastole'] ?? null,
      "beratBadan" => (int)($data['beratBadan'] ?? 0),
      "tinggiBadan" => (int)($data['tinggiBadan'] ?? 0),
      "respRate" => $data['respRate'] ?? null,
      "heartRate" => $data['heartRate'] ?? null,
      "lingkarPerut" => $data['lingkarPerut'] ?? null,
      "kdStatusPulang" => $data['kdStatusPulang'] ?? null,
      "tglPulang" => !empty($data['tglPulang']) ? date("d-m-Y", strtotime($data['tglPulang'])) : null,
      "kdDokter" => $data['kdDokter'] ?? null,
      "kdDiag1" => $data['kdDiag1'] ?? null,
      "kdDiag2" => $data['kdDiag2'] ?? null,
      "kdDiag3" => $data['kdDiag3'] ?? null,
      "kdPoliRujukInternal" => $data['kdPoliRujukInternal'] ?? null,
      "rujukLanjut" => null,
      "kdTacc" => (string)($data['kdTacc'] ?? ''),
      "alasanTacc" => $data['alasanTacc'] ?? null,
      "anamnesa" => $data['anamnesa'] ?? null,
      "alergiMakan" => $data['alergiMakan'] ?? null,
      "alergiUdara" => $data['alergiUdara'] ?? null,
      "alergiObat" => $data['alergiObat'] ?? null,
      "kdPrognosa" => $data['kdPrognosa'] ?? null,
      "terapiObat" => $terapiObat,
      "terapiNonObat" => $data['terapiNonObat'] ?? null,
      "bmhp" => $data['bmhp'] ?? null,
      "suhu" => $data['suhu'] ?? null
   ];

   $resultBpjs = bpjsPost("/kunjungan/v1", $payload, 'PUT');
   
   if (($resultBpjs['code'] ?? '') != "200") {
      $msg = $resultBpjs['message'] ?? null;
      if ($msg == null) {
         $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
      }
      echo json_encode([
         'status' => 'error',
         'message' => $msg,
         'result' => $resultBpjs
      ]);
   } else {
      $stmt3 = $koneksi->prepare("UPDATE permintaan_pharmacy SET status_permintaan = 1 WHERE id_permintaan_farmasi = ? AND id_customer = ?");
      $stmt3->bind_param("ss", $id, $id_customer);

      $stmt4 = $koneksi->prepare("UPDATE pcare_kunjungan SET terapiObat = ? WHERE noKunjungan = ?");
      $stmt4->bind_param('ss', $terapiObat, $noKunjungan);
      if ($stmt3->execute() AND $stmt4->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Berhasil kirim ke farmasi'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => $stmt3->error
         ]);
      }
   }
}

// ================== CLOSE ==================
if ($stmt) $stmt->close();
if ($stmt1) $stmt1->close();
if ($stmt2) $stmt2->close();
if ($stmt3) $stmt3->close();
if ($stmtProv) $stmtProv->close();
if (isset($stmt_statusRI)) $stmt_statusRI->close();