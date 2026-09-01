<?php
header("Content-Type: application/json");

require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/validateToken.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "metadata" => [
            "message" => "Method not allowed",
            "code" => 405
        ]
    ]);
    exit;
}
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$token = $headers['x-token'] ?? null;
$username = $headers['x-username'] ?? null;
$id_customer = validateBpjsToken($username);
$json = file_get_contents("php://input");
$data = json_decode($json, true);

$noKartu        = $data['nomorkartu'] ?? null;
$nik            = $data['nik'] ?? null;
$noKK           = $data['nomorkk'] ?? null;
$nama           = $data['nama'] ?? null;
$jenisKelamin   = $data['jeniskelamin'] == "P" ? "Perempuan" : "Laki - laki";
$tanggalLahir   = $data['tanggallahir'] ?? null;
$alamat         = $data['alamat'] ?? null;
$kodeProp       = $data['kodeprop'] ?? null;
$namaProp       = $data['namaprop'] ?? null;
$kodeDati2      = $data['kodedati2'] ?? null;
$namaDati2      = $data['namadati2'] ?? null;
$kodeKec        = $data['kodekec'] ?? null;
$namaKec        = $data['namakec'] ?? null;
$kodeKel        = $data['kodekel'] ?? null;
$namaKel        = $data['namakel'] ?? null;
$rw             = $data['rw'] ?? null;
$rt             = $data['rt'] ?? null;

$stmt1 = $koneksi->prepare("SELECT * FROM ms_patient WHERE id_customer = ? AND (patient_bpjs = ? OR patient_nik = ?) LIMIT 1");
$stmt1->bind_param('sss', $id_customer,$noKartu, $nik);
$stmt1->execute();
$result = $stmt1->get_result();
if ($result->num_rows > 0) {
    http_response_code(201);
    echo json_encode([
        "metadata" => [
            "message" => "Pasien sudah terdaftar di sistem klinik, tidak dapat melakukan pendaftaran ulang.",
            "code" => 201
        ]
    ]);
} else {
    $stmt = $koneksi->prepare("SELECT nomor_rm_end FROM setting_clinic WHERE id_customer=? FOR UPDATE");
    $stmt->bind_param("i", $id_customer);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $lastRM = (int)$row['nomor_rm_end'];
    } else {
        $lastRM = 0;

        $insert = $koneksi->prepare(
            "INSERT INTO setting_clinic (id_customer, nomor_rm_end) VALUES (?,0)"
        );
        $insert->bind_param("i", $id_customer);
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
    $update->bind_param("ii", $newRM, $id_customer);
    $update->execute();
    $update->close();


    $stmt = $koneksi->prepare("INSERT INTO ms_patient (patient_bpjs, patient_nik, patient_kk, patient_name, patient_gender, patient_datebirth, patient_address,id_customer, nomor_rm, patient_number) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('ssssssssss', $noKartu, $nik, $noKK, $nama, $jenisKelamin, $tanggalLahir, $alamat, $id_customer, $nomorRM, $patientNumber);
    $result = $stmt->execute();

    if ($result) {
        echo json_encode([
            "metadata" => [
                "message" => "Ok",
                "code" => 200
            ]
        ]);
    } else {
        http_response_code(201);
        echo json_encode([
            "metadata" => [
                "message" => "Gagal Mendaftar",
                "code" => 201
            ]
        ]);
    }
}





// // ================== INSERT DATA PASIEN ==================
// $allowedFields = [
//     'patient_name',
//     'patient_gender',
//     'patient_religion',
//     'patient_datebirth',
//     'patient_place',
//     'patient_phone',
//     'patient_address'
// ];

// $fields = ['patient_number', 'nomor_rm', 'id_customer'];
// $values = [$patientNumber, $nomorRM, $id_customer];
// $types  = "ssi";

// foreach ($allowedFields as $f) {
//     if (isset($data[$f])) {
//         $fields[] = $f;
//         $values[] = $data[$f];
//         $types   .= "s";
//     }
// }

// $placeholders = implode(',', array_fill(0, count($fields), '?'));
// $columns      = implode(',', $fields);

// $stmt = $koneksi->prepare("INSERT INTO ms_patient ($columns) VALUES ($placeholders)");
// $stmt->bind_param($types, ...$values);

// if (!$stmt->execute()) {
//     throw new Exception($stmt->error);
// }
// $stmt->close();
// $koneksi->commit();
// echo json_encode([
//     'status' => 'success',
//     'message' => 'Data pasien berhasil disimpan',
//     'patient_number' => $patientNumber,
//     'nomor_rm' => $nomorRM
// ]);
