<?php
include '../../../database/connect.php';
session_start();

$no = $_GET['no'] ?? '';
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$no || !$id_customer) {
   echo json_encode(['status' => 'error']);
   exit;
}

// ================== PASIEN ==================
$qPasien = mysqli_query($koneksi, "
  SELECT p.*, v.visit_date
  FROM pasien_visit v
  LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
  WHERE v.visit_ID='$no' AND v.id_customer='$id_customer'
");

$p = mysqli_fetch_assoc($qPasien);


// ================== PASIEN ==================
$quser = mysqli_query($koneksi, "SELECT fullname, signature_user
  FROM ms_users
  WHERE id_customer='$id_customer' AND roles ='analislab'
  LIMIT 1
");
$puser = mysqli_fetch_assoc($quser);

// ================== INSPECTION ==================
$qIns = mysqli_query($koneksi, "
  SELECT * FROM visit_inspection 
  WHERE id_visit='$no' AND id_customer='$id_customer'
  ORDER BY id_inspection DESC
");

$group = [];

while ($ins = mysqli_fetch_assoc($qIns)) {

   $id_inspection = $ins['id_inspection'];
   $assemen = $ins['inspection_name'];

   $qLab = mysqli_query($koneksi, "
      SELECT 
        ld.assemen AS group_name,
        li.assemen,
        li.satuan,
        li.minimum,
        li.maksimum,
        lr.hasil
      FROM laboratorium_detail ld
      JOIN laboratorium_item li ON li.kode = ld.kode
      LEFT JOIN laboratorium_result lr 
        ON lr.id_item = li.id AND lr.id_inspection = '$id_inspection'
      WHERE ld.assemen = '$assemen'
      ORDER BY li.urutan ASC
   ");

   while ($row = mysqli_fetch_assoc($qLab)) {

      $group_name = $row['group_name'];

      $group[$group_name][] = [
         "nama" => $row['assemen'],
         "hasil" => $row['hasil'],
         "satuan" => $row['satuan'],
         "normal" => ($row['minimum'] ?? '-') . " - " . ($row['maksimum'] ?? '-')
      ];
   }
}

// ================== RESPONSE ==================
$data = [
   "pasien" => [
      "nama" => $p['patient_name'] ?? '',
      "tgl_periksa" => date('d-m-Y', strtotime($p['visit_date'] ?? date('Y-m-d'))),
      "tgllahir" => $p['patient_datebirth'] ?? '',
      // "umur" => hitungUmur($p['patient_datebirth'] ?? ''),
      "jk" => $p['patient_gender'] ?? '',
      "alamat" => $p['patient_address'] ?? '',
   ],
   "hasil" => $group,
   // 🔥 USER
   "petugas" => $puser['fullname'] ?? 'Laboratorium',
   "ttd" => !empty($puser['signature_user'])
      ? "../../../uploads/ttd_faskes/" . $puser['signature_user']
      : "../../../uploads/ttd/default.png"
];

echo json_encode([
   "status" => "success",
   "data" => $data
]);
