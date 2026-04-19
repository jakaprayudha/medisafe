<?php
require_once __DIR__ . '/../admisi/services/view.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../admisi/services/servicebpjs.php';
header('Content-Type: application/json');
$data_obat = json_decode(file_get_contents("php://input"), true);
$id = $data_obat['id'] ?? null;
// $id = "37";
$id_customer = $_SESSION['id_customer'] ?? null;
if (!$id || !$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Data tidak valid'
   ]);
   exit;
}
$stmt = $koneksi->prepare("SELECT id_visit FROM permintaan_pharmacy WHERE id_permintaan_farmasi = ? AND id_customer = ?");
$stmt->bind_param('ss', $id, $id_customer);
$stmt->execute();
$cek = $stmt->get_result()->fetch_assoc();
$nomor_visit = $cek['id_visit'];

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

$payload = [
   "noKunjungan" => $data['noKunjungan'] ?? null,
   "noKartu" => $data['noKartu'] ?? null,
   "tglDaftar" => !empty($data['tglDaftar']) ? date("d-m-Y", strtotime($data['tglDaftar'])) : null,
   "kdPoli" => $data['kdPoli'] ?? null,
   "keluhan" => $data['keluhan'] ?? null,
   "kdSadar" => $data['kdSadar'] ?? null,
   "sistole" => $data['sistole'] ?? null,
   "diastole" => $data['diastole'] ?? null,
   "beratBadan" => (int)$data['beratBadan'] ?? null,
   "tinggiBadan" => (int)$data['tinggiBadan'] ?? null,
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
   "kdTacc" => (string)$data['kdTacc'] ?? null,
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
// echo json_encode($payload, JSON_PRETTY_PRINT);die();
$result = bpjsPost("/kunjungan/v1", $payload, 'PUT');
if ($result['code'] != "200") {
   $msg = $result['message'];
   if ($msg == null) {
      $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
   }
   $response = [
      'status' => 'error',
      'message' => $msg,
      'result' => $result
   ];
} else {
   $stmt3 = $koneksi->prepare(" UPDATE permintaan_pharmacy SET status_permintaan = 1 WHERE id_permintaan_farmasi = ? AND id_customer = ?");
   $stmt3->bind_param("ii", $id, $id_customer);
   if ($stmt3->execute()) {
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
$stmt->close();
$stmt1->close();
$stmt2->close();
$stmt3->close();
// echo json_encode($hasil);

// // 🔥 UPDATE STATUS PERMINTAAN JADI 1
// $stmt = $koneksi->prepare("
//     UPDATE permintaan_pharmacy 
//     SET status_permintaan = 1
//     WHERE id_permintaan_farmasi = ?
//     AND id_customer = ?
// ");

// $stmt->bind_param("ii", $id, $id_customer);

// if ($stmt->execute()) {
//    echo json_encode([
//       'status' => 'success',
//       'message' => 'Berhasil kirim ke farmasi'
//    ]);
// } else {
//    echo json_encode([
//       'status' => 'error',
//       'message' => $stmt->error
//    ]);
// }

// $stmt->close();
// echo json_encode($hasil);
