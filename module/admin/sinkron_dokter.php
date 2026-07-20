<?php
include "../../database/connect.php";
header("Content-Type: application/json");
session_start();
$id_customer = $_SESSION['id_customer'];
if ($id_customer == '') {
    echo json_encode([
        "success" => false,
        "message" => "Session customer tidak ditemukan."
    ]);
    exit;
}
$list = json_decode($_POST['dokter'] ?? '[]', true);
if (empty($list)) {
    echo json_encode([
        "success" => false,
        "message" => "Data dokter kosong."
    ]);
    exit;
}
mysqli_begin_transaction($koneksi);
try {
    $dataAPI = [];
    $tambah = 0;
    $update = 0;
    $hapus  = 0;
    foreach ($list as $dokter) {
        $kdDokter = mysqli_real_escape_string(
            $koneksi,
            $dokter['kdDokter']
        );
        $nmDokter = mysqli_real_escape_string(
            $koneksi,
            $dokter['nmDokter']
        );
        $dataAPI[] = $kdDokter;
        $cek = mysqli_query($koneksi,"
            SELECT id 
            FROM master_doctor_bpjs
            WHERE kdDokter='$kdDokter'
            AND id_customer='$id_customer'
        ");
        if(mysqli_num_rows($cek) > 0){
            mysqli_query($koneksi,"
                UPDATE master_doctor_bpjs
                SET
                    nmDokter='$nmDokter',
                    status='1'
                WHERE kdDokter='$kdDokter'
                AND id_customer='$id_customer'
            ");
            $update++;
        }else{
            mysqli_query($koneksi,"
                INSERT INTO master_doctor_bpjs
                (
                    kdDokter,
                    nmDokter,
                    id_customer,
                    status
                )
                VALUES
                (
                    '$kdDokter',
                    '$nmDokter',
                    '$id_customer',
                    '1'
                )
            ");
            $tambah++;
        }
    }
    if(count($dataAPI) > 0){
        $listKode = "'" . implode("','",$dataAPI) . "'";
        $hapusQuery = mysqli_query($koneksi,"
            DELETE FROM master_doctor_bpjs
            WHERE id_customer='$id_customer'
            AND kdDokter NOT IN ($listKode)
        ");
        $hapus = mysqli_affected_rows($koneksi);
    }
    mysqli_commit($koneksi);
    echo json_encode([
        "success"=>true,
        "message"=>"Sinkron selesai. Tambah: $tambah, Update: $update, Hapus: $hapus"
    ]);
} catch(Exception $e){
    mysqli_rollback($koneksi);
    echo json_encode([
        "success"=>false,
        "message"=>$e->getMessage()
    ]);

}