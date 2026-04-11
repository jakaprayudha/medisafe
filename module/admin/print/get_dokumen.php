<?php
header("Content-Type: application/json");
require '../../../database/connect.php';

$id = $_GET['rm'] ?? 0;
$no = $_GET['no'] ?? 0;
$id_customer = $_SESSION['id_customer'] ?? null;
$q = mysqli_query($koneksi, "SELECT * FROM pasien_dokumen pd
LEFT JOIN ms_patient mp ON pd.nomor_rm = mp.nomor_rm
LEFT JOIN pasien_visit pv ON pd.visit_ID = pv.visit_ID
WHERE pv.id_customer ='$id_customer' AND pv.visit_ID = '$no' 
AND (
        -- FOTO PERAWATAN/PASIEN Wajib sesuai visit_ID
        (
            pd.jenis_dokumen IN ('FOTO_PERAWATAN','FOTO_PASIEN')
            AND pd.visit_ID = '$no'
        )
        
        OR
        
        -- Selain itu bebas (KTP, KK, AKTA, NIKAH, dll)
        pd.jenis_dokumen NOT IN ('FOTO_PERAWATAN','FOTO_PASIEN')
    )
");

if (!$q) {
   echo json_encode([
      "status" => "error",
      "message" => mysqli_error($koneksi)
   ]);
   exit;
}

// Ambil semua baris (BUKAN 1 baris)
$data = mysqli_fetch_all($q, MYSQLI_ASSOC);

echo json_encode([
   "status" => "success",
   "count" => count($data),
   "data" => $data
]);
