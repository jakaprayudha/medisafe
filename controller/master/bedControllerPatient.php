<?php
include '../../database/connect.php';

header("Content-Type: application/json; charset=utf-8");

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
      AND v.id_customer = ?
) active_ranap ON active_ranap.id_bed = b.id_bed

WHERE b.id_customer = ?

ORDER BY r.service_class ASC, r.room_name ASC, b.bed_name ASC
";

      $idc = (int) $id_customer;
      $stq = $koneksi->prepare($sql);
      $stq->bind_param('ii', $idc, $idc);
      $stq->execute();
      $query = $stq->get_result();

      if (!$query) {
         echo json_encode([
            "status" => "error",
            "message" => "Gagal memuat data tempat tidur",
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
         $id_bed = (int) $id_bed;
         $idc = (int) $id_customer;
         $status = $status === 0 ? 0 : 1;
         $stc = $koneksi->prepare("SELECT id_bed FROM ms_room_bed WHERE id_bed = ? AND id_customer = ?");
         $stc->bind_param('ii', $id_bed, $idc);
         $stc->execute();
         if (!$stc->get_result()->fetch_assoc()) {
            echo json_encode([
               "status" => "error",
               "message" => "Data tidak valid"
            ]);
            exit;
         }

         // Jika status diubah menjadi 1 (kosong), lepaskan/pulangkan pasien aktif (faskes ini) yang menggantung di bed ini
         // log: pasien aktif di bed ini yang akan ikut dipulangkan
         require_once __DIR__ . '/../../utility/visitLog.php';
         $bedPasien = [];
         if ($status == 1) {
            $stl = $koneksi->prepare("SELECT pr.id_ranap, pr.visit_ID_inpatient FROM permintaan_ranap pr JOIN pasien_visit pv ON pv.visit_ID = pr.visit_ID_inpatient
               WHERE pr.id_bed = ? AND pr.status = 'aktif' AND pv.id_customer = ?");
            $sbl = (string) $id_bed;
            $stl->bind_param('si', $sbl, $idc);
            $stl->execute();
            $bedPasien = $stl->get_result()->fetch_all(MYSQLI_ASSOC);
            $stl->close();
         }
         $bedLama = logAmbilBaris($koneksi, 'ms_room_bed', 'id_bed', $id_bed);
         if ($status == 1) {
            $stp = $koneksi->prepare("UPDATE permintaan_ranap pr JOIN pasien_visit pv ON pv.visit_ID = pr.visit_ID_inpatient
               SET pr.status = 'pulang' WHERE pr.id_bed = ? AND pr.status = 'aktif' AND pv.id_customer = ?");
            $sb = (string) $id_bed;
            $stp->bind_param('si', $sb, $idc);
            $stp->execute();
         }

         $stu = $koneksi->prepare("UPDATE ms_room_bed SET bed_status = ? WHERE id_bed = ? AND id_customer = ?");
         $stu->bind_param('iii', $status, $id_bed, $idc);
         $update = $stu->execute();

         if ($update) {
            logBaris($koneksi, 'ranap', 'ms_room_bed', 'id_bed', $id_bed, $bedLama, ['tipe' => 'bed', 'label' => 'Bed ' . ($bedLama['bed_name'] ?? '#' . $id_bed)],
               'Status bed diubah jadi ' . ($status ? 'kosong' : 'terisi') . ($bedPasien ? ' · ' . count($bedPasien) . ' pasien aktif ikut dipulangkan' : ''));
            foreach ($bedPasien as $bp) logKunjungan($koneksi, 'pulang', (string) $bp['visit_ID_inpatient'],
               'Dipulangkan otomatis karena bed ' . ($bedLama['bed_name'] ?? '#' . $id_bed) . ' diset kosong dari master bed', ['status_ranap' => 'aktif'], ['status_ranap' => 'pulang', 'id_ranap' => $bp['id_ranap']], 'Rawat Inap');
            echo json_encode([
               "status" => "success",
               "message" => "Status berhasil diupdate"
            ]);
         } else {
            echo json_encode([
               "status" => "error",
               "message" => "Gagal mengubah status bed"
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
