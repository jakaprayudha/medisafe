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

// $clinics = tampildata("SELECT id_customer FROM setting_clinic where exists (select 1 from setting_satusehat where setting_satusehat.id_customer=setting_clinic.id_customer)");

$clinics = tampildata("SELECT id_customer, clinic_name FROM setting_clinic where exists (select 1 from setting_satusehat where setting_satusehat.id_customer=setting_clinic.id_customer and (setting_satusehat.client_id is not null and setting_satusehat.client_secret is not null and setting_satusehat.organization_id is not null)) order by id_customer desc");


$totalData = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM ms_patient WHERE idsh is null AND is_checked_satusehat='0' AND length(patient_nik) = 16"))['total'];

$processedCount = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM ms_patient WHERE idsh is not null"))['total'];

function printProgressBar($done, $total, $width = 50) {
    if ($total == 0) return;
    $perc = (float)$done / $total;
    $bar = floor($perc * $width);
    $statusBar = "[" . str_repeat("=", $bar);
    if ($bar < $width) {
        $statusBar .= ">" . str_repeat(" ", $width - $bar - 1);
    } else {
        $statusBar .= "=";
    }
    $disp = number_format($perc * 100, 1);
    $statusBar .= "] $disp% ($done/$total)";
    echo "\r$statusBar";
    if ($done >= $total) {
        echo "\n";
    }
}

foreach ($clinics as $clinic) {
    $id_customer = $clinic['id_customer'];

    $token = json_decode(generateToken($id_customer), true);
    if (!@$token['access_token']) {
        continue;
    }

    $patients = tampildata("SELECT * FROM ms_patient WHERE id_customer='$id_customer' AND idsh is null AND is_checked_satusehat='0' AND length(patient_nik) = 16 LIMIT 200");

    foreach ($patients as $patient) {
        $nik = $patient['patient_nik'];
        $cleanedNik = preg_replace('/\s+/', '', $nik);
        $url = $fhirUrl . "/Patient?identifier=https://fhir.kemkes.go.id/id/nik|$cleanedNik";
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
            mysqli_query($koneksi, "UPDATE ms_patient SET is_checked_satusehat='1', patient_nik='$cleanedNik' WHERE id_customer='$id_customer' AND patient_nik='$nik'");
            $processedCount++;
            printProgressBar($processedCount, $totalData);
            continue;
        }
        $resourceType = 'Patient';
        $referenceId = $nik;
        $resourceId = $idsh;
        $status = 'success';
        $message = mysqli_escape_string($koneksi, $response);
        mysqli_query($koneksi, "INSERT INTO satusehat_log (id_customer, resource_type, reference_id, resource_id, status, message) VALUES ('$id_customer', '$resourceType', '$referenceId', '$resourceId', '$status', '$message')");
        // update data
        mysqli_query($koneksi, "UPDATE ms_patient SET idsh='$idsh', patient_nik='$cleanedNik' WHERE id_customer='$id_customer' AND patient_nik='$nik'");
        $processedCount++;
        printProgressBar($processedCount, $totalData);
        // Optionally, keep the echo for details:
        echo "Processed patient IDSH: " . $idsh . " for clinic: " . $clinic['clinic_name'] . "\n";
    }
}
