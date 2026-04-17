<?php
require '../../../database/connect.php';

$no = $_GET['no'];
$rm = $_GET['rm'];
$id_customer = $_SESSION['id_customer'];

// ===============================
// DATA PASIEN
// ===============================
$query = "SELECT * FROM pasien_visit pv 
LEFT JOIN ms_patient mp ON pv.id_patient = mp.id_patient 
LEFT JOIN permintaan_ranap pr ON pv.visit_ID = pr.visit_ID_inpatient
LEFT JOIN ms_room r ON pr.id_room = r.id_room
LEFT JOIN ms_room_bed b ON pr.id_bed = b.id_bed
LEFT JOIN icd_10 icd ON icd.code = pv.diagnosa
LEFT JOIN ms_users ON ms_users.fullname = pv.id_doctor
WHERE pv.visit_ID = '$no' 
AND pv.id_customer = '$id_customer'";

$result = $koneksi->query($query);
$data = $result->fetch_assoc();


// // ===============================
// // TERAPI (AMBIL OBAT PERTAMA)
// // ===============================
// $qFirst = mysqli_query($koneksi, "
//    SELECT id_permintaan_farmasi
//    FROM permintaan_pharmacy
//    WHERE id_visit = '$no'
//    ORDER BY created_at ASC
//    LIMIT 1
// ");

// $first = mysqli_fetch_assoc($qFirst);
// $id_permintaan = $first['id_permintaan_farmasi'] ?? null;
// $terapi = "-";

// if ($id_permintaan) {

//    $qDetail = mysqli_query($koneksi, "SELECT *
//       FROM permintaan_pharmacy_details
//       INNER JOIN ms_pharmacy ON ms_pharmacy.id_pharmacy = permintaan_pharmacy_details.id_pharmacy
//       WHERE permintaan_pharmacy_details.id_permintaan_farmasi = '$id_permintaan'
//       ORDER BY permintaan_pharmacy_details.created_at ASC
//    ");

//    $list = [];

//    while ($row = mysqli_fetch_assoc($qDetail)) {

//       $nama = $row['pharmacy_name_trade'] ?: "-";

//       $text = $nama;

//       if (!empty($row['signa'])) {
//          $text .= " (" . $row['signa'] . ")";
//       }

//       if (!empty($row['qty'])) {
//          $text .= " x " . $row['qty'];
//       }

//       $list[] = $text;
//    }

//    if (!empty($list)) {
//       $terapi = implode(", ", $list);
//    }
// }


// // inject terapi ke response
// $data['terapi'] = $terapi;

// ===============================
// OUTPUT (HARUS TERAKHIR)
// ===============================
echo json_encode($data);
