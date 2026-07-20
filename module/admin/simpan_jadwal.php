<?php
include "../../database/connect.php";
header("Content-Type: application/json");
$id_customer=$_SESSION['id_customer'];
$id_schedule=trim($_POST['schedule_id'] ?? '');
$id_doctor=trim($_POST['doctor_code'] ?? '');
$day_of_week=trim($_POST['day_of_week'] ?? '');
$id_poli=trim($_POST['id_poli'] ?? '');
$start_time=trim($_POST['start_time'] ?? '');
$end_time=trim($_POST['end_time'] ?? '');
$kuota=(int)($_POST['kuota'] ?? 30);

if($id_doctor==""){
    echo json_encode(["success"=>false,"message"=>"Dokter tidak ditemukan."]);
    exit;
}

if($day_of_week==""){
    echo json_encode(["success"=>false,"message"=>"Hari wajib dipilih."]);
    exit;
}

if($id_poli==""){
    echo json_encode(["success"=>false,"message"=>"Poliklinik wajib dipilih."]);
    exit;
}

if($start_time==""){
    echo json_encode(["success"=>false,"message"=>"Jam mulai wajib diisi."]);
    exit;
}

if($end_time==""){
    echo json_encode(["success"=>false,"message"=>"Jam selesai wajib diisi."]);
    exit;
}

if(strtotime($start_time)>=strtotime($end_time)){
    echo json_encode(["success"=>false,"message"=>"Jam selesai harus lebih besar dari jam mulai."]);
    exit;
}

if($kuota<=0){
    $kuota=30;
}

mysqli_begin_transaction($koneksi);

try{

    if($id_schedule==""){

        $cek=mysqli_query($koneksi,"
            SELECT id_schedule
            FROM ms_doctor_schedule
            WHERE id_doctor='$id_doctor'
            AND day_of_week='$day_of_week'
            AND id_poli='$id_poli'
            AND start_time='$start_time'
            AND end_time='$end_time'
            AND id_customer='$id_customer'
        ");

        if(mysqli_num_rows($cek)>0){
            throw new Exception("Jadwal sudah tersedia.");
        }

        $sql=mysqli_query($koneksi,"
            INSERT INTO ms_doctor_schedule
            (
                id_doctor,
                day_of_week,
                start_time,
                end_time,
                sch_status,
                id_poli,
                kuota,
                id_customer
            )
            VALUES
            (
                '$id_doctor',
                '$day_of_week',
                '$start_time',
                '$end_time',
                '1',
                '$id_poli',
                '$kuota',
                '$id_customer'
            )
        ");

        if(!$sql){
            throw new Exception(mysqli_error($koneksi));
        }

    }else{

        $cek=mysqli_query($koneksi,"
            SELECT id_schedule
            FROM ms_doctor_schedule
            WHERE id_doctor='$id_doctor'
            AND day_of_week='$day_of_week'
            AND id_poli='$id_poli'
            AND start_time='$start_time'
            AND end_time='$end_time'
            AND id_customer='$id_customer'
            AND id_schedule<>'$id_schedule'
        ");

        if(mysqli_num_rows($cek)>0){
            throw new Exception("Jadwal sudah tersedia.");
        }

        $sql=mysqli_query($koneksi,"
            UPDATE ms_doctor_schedule
            SET
                day_of_week='$day_of_week',
                start_time='$start_time',
                end_time='$end_time',
                id_poli='$id_poli',
                kuota='$kuota',
                updated_at=NOW()
            WHERE id_schedule='$id_schedule'
            AND id_customer='$id_customer'
        ");

        if(!$sql){
            throw new Exception(mysqli_error($koneksi));
        }

    }

    mysqli_commit($koneksi);

    echo json_encode([
        "success"=>true,
        "message"=>"Jadwal berhasil disimpan."
    ]);

}catch(Exception $e){

    mysqli_rollback($koneksi);

    echo json_encode([
        "success"=>false,
        "message"=>$e->getMessage()
    ]);

}