<?php
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../satusehat/variable.php';

function tampildata($query)
{
    global $koneksi;
    $result = mysqli_query($koneksi, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// header('Content-Type: application/json');

// $clinics = tampildata("SELECT id_customer FROM setting_clinic where exists (select 1 from setting_satusehat where setting_satusehat.id_customer=setting_clinic.id_customer and (setting_satusehat.client_id is not null and setting_satusehat.client_secret is not null and setting_satusehat.organization_id is not null))");

$clinics = tampildata("SELECT * FROM setting_satusehat where client_id is not null and client_secret is not null and organization_id is not null");

foreach ($clinics as $clinic) {
    $id_customer = $clinic['id_customer'];
    echo $id_customer . "\n";

    $patients = tampildata("SELECT * FROM ms_doctor WHERE idsh IS NULL AND LENGTH(doctor_nik) = 16 AND id_customer = '$id_customer'");

    foreach ($patients as $patient) {
        $nik = $patient['doctor_nik'];

        $cleanedNik = preg_replace('/\s+/', '', $nik);

        $url = $fhirUrl . "/Practitioner?identifier=https://fhir.kemkes.go.id/id/nik|$cleanedNik";

        $token = json_decode(generateToken($id_customer), true);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token['access_token']
        ];

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        // Execute cURL session and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
            die;
        }

        // Close cURL session
        curl_close($ch);

        $dataPatient = json_decode($response, true);

        $idsh = $dataPatient['entry'][0]['resource']['id'] ?? null;
        if (!$idsh) {
            echo "Gagal mendapatkan ID SH untuk dokter dengan NIK $nik di clinic $id_customer\n";
            continue;
        }

        // update data
        mysqli_query($koneksi, "UPDATE ms_doctor SET idsh='$idsh', doctor_nik='$cleanedNik' WHERE doctor_nik='$nik' AND id_customer='$id_customer'");
        echo "Berhasil mengupdate dokter dengan NIK $nik menjadi ID SH $idsh di clinic $id_customer\n";
    }
}
