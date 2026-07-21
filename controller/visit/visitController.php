<?php
include '../../database/connect.php';
while (ob_get_level()) ob_end_clean();

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
   createVisit();
} else {
   echo json_encode([
      'status' => 'error',
      'message' => 'Method tidak diizinkan'
   ]);
   exit;
}

// ================= CREATE VISIT =================
function createVisit()
{
   global $koneksi;
   try {
      $id_customer = $_SESSION['id_customer'] ?? null;
      $created_user = $_SESSION['fullname'] ?? null;

      if (!$id_customer) {
         echo json_encode([
            'status' => 'error',
            'message' => 'Session habis'
         ]);
         exit;
      }

      $patient_name_pcare = $_POST['patient_name_pcare'] ?? '';
      $namaDokter = $_POST['doctor_name'] ?? '';
      $poli_name = $_POST['poli_name'] ?? '';
      $id_patient = $_POST['id_patient'] ?? '';
      $visit_date = $_POST['visit_date'] ?? '';
      $visit_time = $_POST['visit_time'] ?? '';
      $id_doctor  = $_POST['id_doctor'] ?? '';
      $id_provider = $_POST['id_provider'] ?? '';
      $kdPoli = $_POST['kdPoli'] ?? '';
      $source_hub = !empty($_POST['source_hub']) ? $_POST['source_hub'] : 'Poliklinik';
      if (!$id_patient || !$visit_date || !$visit_time || !$id_doctor) {
         echo json_encode([
            'status' => 'error',
            'message' => 'Data tidak lengkap'
         ]);
         exit;
      }
      $doctor_name = '';
      $jampraktek = '';
      if (preg_match('/^(.*?)\s*\((.*?)\)$/', $namaDokter, $matches)) {
         $doctor_name = trim($matches[1]);
         $jampraktek = trim($matches[2]);
      }
      $visit_ID = generateVisitID($koneksi, $id_customer);
      $resultAntrian = createAntrian($koneksi, $kdPoli, $id_customer, $visit_ID, $id_doctor, $visit_date, $jampraktek);
      $nomorantrean = $resultAntrian['display'];
      $stmt = $koneksi->prepare("INSERT INTO pasien_visit (id_patient,visit_ID,visit_date,visit_time,id_doctor,id_poli,source_hub,visit_status,created_user,visit_antrian,status_antrian,id_customer,patient_name_pcare,id_provider) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
      $visit_status = 0;
      $status_antrian = 0;
      $stmt->bind_param(
         "sssssssiisiisi",
         $id_patient,
         $visit_ID,
         $visit_date,
         $visit_time,
         $doctor_name,
         $poli_name,
         $source_hub,
         $visit_status,
         $created_user,
         $nomorantrean,
         $status_antrian,
         $id_customer,
         $patient_name_pcare,
         $id_provider
      );

      if (!$stmt->execute()) {
         throw new Exception($stmt->error);
      }

      echo json_encode([
         'status' => 'success',
         'message' => 'Berhasil daftar',
         'data' => [
            'visit_ID' => $visit_ID,
            'antrian' => $nomorantrean
         ]
      ]);
      exit;
   } catch (Exception $e) {

      echo json_encode([
         'status' => 'error',
         'message' => $e->getMessage()
      ]);
      exit;
   }
}

// ================= GENERATE VISIT ID =================
function generateVisitID($koneksi, $idcustomer)
{
   do {
      $date = date('ymd');
      $random = strtoupper(bin2hex(random_bytes(3)));
      $visitID = "VIS-" . $idcustomer . "-" . $date . "-" . $random;
      $count = '';
      $check = $koneksi->prepare("SELECT COUNT(*) FROM pasien_visit WHERE visit_ID=?");
      $check->bind_param("s", $visitID);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $visitID;
}
function createAntrian($koneksi, $kdPoli, $idcustomer, $visit_ID, $kdDokter, $tglDaftarDB, $jampraktek){
   $cekantrian = $koneksi->prepare("SELECT COALESCE(MAX(a.nomor), 0) AS last, (SELECT d.doctor_antrean FROM ms_doctor d WHERE d.doctor_code = ? AND d.id_customer = ? LIMIT 1) AS kode_antrian FROM antrian_poli a WHERE a.poli = ? AND a.tanggal = ? AND a.id_customer = ? AND a.kode_antri = (SELECT d.doctor_antrean FROM ms_doctor d WHERE d.doctor_code = ? AND d.id_customer = ? LIMIT 1) FOR UPDATE");
   $cekantrian->bind_param("sssssss", $kdDokter, $idcustomer, $kdPoli, $tglDaftarDB, $idcustomer, $kdDokter, $idcustomer);
   $cekantrian->execute();
   $rowantrian = $cekantrian->get_result()->fetch_assoc();
   $next = (int)$rowantrian['last'] + 1;
   $kode_antrian = $rowantrian['kode_antrian'];
   $createantrian = $koneksi->prepare("INSERT INTO antrian_poli (nomor, poli, tanggal, id_customer, nomor_visit,id_dokter, kode_antri, jampraktek)VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
   $createantrian->bind_param("isssssss", $next, $kdPoli, $tglDaftarDB, $idcustomer, $visit_ID, $kdDokter, $kode_antrian, $jampraktek);
   $createantrian->execute();
   return [
      'nomor' => $next,
      'kode' => $kode_antrian,
      'display' => $kode_antrian . $next
   ];
}
