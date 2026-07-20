<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');

$nik = $_POST['noKartu'];
$nama = $_POST['nama'];
$date = DateTime::createFromFormat('d-m-Y', trim($_POST['tgl_lahir']));
$tanggalLahir = $date ? $date->format('Y-m-d') : null; 
$jenisKelamin = $_POST['jenis_kelamin'];
$noKartu = $_POST['no_bpjs'];

$stmt = $koneksi->prepare("SELECT * FROM ms_patient WHERE (patient_bpjs = ? OR patient_nik = ?) AND id_customer = ?");
$stmt->bind_param('sss', $noKartu, $nik, $idcustomer);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
if ($data) {
    $response = [
        'success'  => false,
        'message'  => "Data pasien dengan NIK atau nomor BPJS tersebut sudah terdaftar. Silakan periksa kembali.",
    ];
} else {
    $stmt = $koneksi->prepare("SELECT nomor_rm_end FROM setting_clinic WHERE id_customer=? FOR UPDATE");
    $stmt->bind_param("i", $idcustomer);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $lastRM = (int)$row['nomor_rm_end'];
    } else {
        $lastRM = 0;
        $insert = $koneksi->prepare(
            "INSERT INTO setting_clinic (id_customer, nomor_rm_end) VALUES (?,0)"
        );
        $insert->bind_param("i", $idcustomer);
        $insert->execute();
        $insert->close();
    }
    $stmt->close();
    $newRM   = $lastRM + 1;
    $nomorRM = str_pad($newRM, 6, "0", STR_PAD_LEFT);
    $count = 0;
    do {
        $patientNumber = "PCT-" . strtoupper(bin2hex(random_bytes(4)));
        $check = $koneksi->prepare(
            "SELECT COUNT(*) FROM ms_patient WHERE patient_number=?"
        );
        $check->bind_param("s", $patientNumber);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();
    } while ($count > 0);
    $update = $koneksi->prepare("UPDATE setting_clinic SET nomor_rm_end=? WHERE id_customer=?");
    $update->bind_param("ii", $newRM, $idcustomer);
    $update->execute();
    $update->close();


    $stmt = $koneksi->prepare("INSERT INTO ms_patient (patient_bpjs, patient_nik, patient_name, patient_gender, patient_datebirth, id_customer, nomor_rm) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('sssssss', $noKartu, $nik, $nama, $jenisKelamin, $tanggalLahir, $idcustomer, $nomorRM);
    $result = $stmt->execute();

    if ($result) {
        $response = [
            'success'  => true,
            'message'  => "Berhasil Menambahkan Pasien",
        ];
    } else {
        $response = [
            'success'  => false,
            'message'  => "Gagal Mendaftarkan Pasien",
        ];
    }
}
echo json_encode($response);
