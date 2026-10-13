<?php
include '../../database/connect.php';
session_start();

header("Content-Type: application/json");

$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      "status" => "error",
      "message" => "Session tidak ditemukan"
   ]);
   exit;
}

// 🔥 HANDLE METHOD
$method = $_SERVER['REQUEST_METHOD'];

// support spoof PUT (kalau pakai fetch POST + _method)
if ($method === 'POST' && isset($_POST['_method'])) {
   $method = strtoupper($_POST['_method']);
}

switch ($method) {

   // =============================
   // 🔹 GET DATA BED
   // =============================
   case 'GET':

      $sql = "
SELECT 
    b.*,
    r.room_name,
    r.service_class,

    -- Data pasien aktif jika ada
    active_ranap.visit_ID,
    active_ranap.id_ranap,
    active_ranap.patient_name_pcare,
    active_ranap.patient_name,
    active_ranap.patient_gender,
    active_ranap.nomor_rm

FROM ms_room_bed b

LEFT JOIN ms_room r 
    ON r.id_room = b.id_room

LEFT JOIN (
    SELECT 
        pr.id_bed,
        pr.id_ranap,
        pr.visit_ID_inpatient AS visit_ID,
        COALESCE(mp.patient_name, v.patient_name_pcare) AS patient_name,
        v.patient_name_pcare,
        mp.patient_gender,
        mp.nomor_rm
    FROM permintaan_ranap pr
    INNER JOIN pasien_visit v 
        ON v.visit_ID = pr.visit_ID_inpatient
    LEFT JOIN ms_patient mp 
        ON mp.id_patient = v.id_patient
    WHERE pr.status = 'aktif'
      AND pr.ranap_booking = 1
      AND (v.visit_status IS NULL OR v.visit_status NOT IN (4, 99))
) active_ranap ON active_ranap.id_bed = b.id_bed

WHERE b.id_customer = '$id_customer'

ORDER BY r.service_class ASC, r.room_name ASC, b.bed_name ASC
";

      $query = mysqli_query($koneksi, $sql);

      if (!$query) {
         echo json_encode([
            "status" => "error",
            "message" => mysqli_error($koneksi),
            "data" => []
         ]);
         exit;
      }

      $data = [];

      while ($row = mysqli_fetch_assoc($query)) {
         $data[] = $row;
      }

      echo json_encode([
         "status" => "success",
         "data" => $data
      ]);

      break;


   // =============================
   // 🔥 TOGGLE STATUS BED
   // =============================
   case 'PUT':

      parse_str(file_get_contents("php://input"), $_PUT);

      if (isset($_GET['toggle_status'])) {

         $id_bed = $_PUT['id_bed'] ?? null;
         $status = isset($_PUT['bed_status']) ? (int)$_PUT['bed_status'] : 1;

         if (!$id_bed) {
            echo json_encode([
               "status" => "error",
               "message" => "ID bed tidak ditemukan"
            ]);
            exit;
         }

         // 🔥 VALIDASI MILIK CUSTOMER (PENTING!)
         $check = mysqli_query($koneksi, "
            SELECT id_bed 
            FROM ms_room_bed 
            WHERE id_bed = '$id_bed' 
            AND id_customer = '$id_customer'
         ");

         if (mysqli_num_rows($check) == 0) {
            echo json_encode([
               "status" => "error",
               "message" => "Data tidak valid"
            ]);
            exit;
         }

         // Jika status diubah menjadi 1 (kosong), lepaskan/pulangkan pasien aktif yang menggantung di bed ini
         if ($status == 1) {
            mysqli_query($koneksi, "
               UPDATE permintaan_ranap 
               SET status = 'pulang' 
               WHERE id_bed = '$id_bed' 
                 AND status = 'aktif'
            ");
         }

         // 🔥 UPDATE STATUS
         $update = mysqli_query($koneksi, "
            UPDATE ms_room_bed 
            SET bed_status = '$status'
            WHERE id_bed = '$id_bed'
         ");

         if ($update) {
            echo json_encode([
               "status" => "success",
               "message" => "Status berhasil diupdate"
            ]);
         } else {
            echo json_encode([
               "status" => "error",
               "message" => mysqli_error($koneksi)
            ]);
         }

         exit;
      }

      break;


   default:
      echo json_encode([
         "status" => "error",
         "message" => "Method tidak diizinkan"
      ]);
      break;
}
