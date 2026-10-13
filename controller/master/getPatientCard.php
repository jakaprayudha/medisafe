<?php
require_once __DIR__ . '/../../database/connect.php';

header("Content-Type: application/json; charset=utf-8");

$id_customer = $_SESSION['id_customer'] ?? null;
// $id_customer = $_GET['id_customer'] ?? null;
$no = $_GET['no'] ?? null;

if (!$id_customer || !$no) {
   http_response_code(400);
   echo json_encode(["status" => "error", "message" => "Session klinik atau nomor kunjungan tidak ditemukan"]);
   exit;
}

$stmt = $koneksi->prepare("SELECT 
	 v.id_visit,
      v.visit_ID,
      v.visit_status,
      v.patient_name_pcare,
      p.patient_name,
      p.nomor_rm,
      p.patient_foto,
      p.face_image,
      p.patient_gender,
      pr.provider_name,
      p.patient_datebirth,
      p.Sprolanis,
      p.SPRB,
      v.tinggi_badan,
      v.berat_badan,
      v.suhu,
v.noKartu AS patient_bpjs,
p.patient_nik,
p.idsh,
      v.id_doctor,
      CONCAT(
        FLOOR(DATEDIFF(CURDATE(), p.patient_datebirth)/365),
        ' Th'
      ) as usia
   FROM pasien_visit v
   LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
   LEFT JOIN ms_provider pr ON pr.id_provider = v.id_provider

   WHERE v.visit_ID=? AND v.id_customer=?
");

$stmt->bind_param("ss", $no, $id_customer);
$stmt->execute();

$data = $stmt->get_result()->fetch_assoc();

echo json_encode([
   "status" => "success",
   "data" => $data
]);
