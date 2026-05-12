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

$clinics = tampildata("SELECT setting_clinic.id_customer, clinic_name, organization_id FROM setting_clinic JOIN  setting_satusehat ON setting_satusehat.id_customer=setting_clinic.id_customer where (setting_satusehat.client_id is not null and setting_satusehat.client_secret is not null and setting_satusehat.organization_id is not null) order by id_customer desc");

foreach ($clinics as $clinic) {
    $id_customer = $clinic['id_customer'];
    $organizationId = $clinic['organization_id'];
    $url = $fhirUrl . "/Location?organization=$organizationId";

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

    // echo json_encode($headers);
    // die;
    // Execute cURL session and store the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
        die;
    }

    // Close cURL session
    curl_close($ch);

    // var_dump($response);die;

    $data = json_decode($response, true);

    $id = $data['entry'][0]['resource']['id'] ?? null;
    if (!$id) {
        echo "Gagal mendapatkan ID lokasi untuk clinic $id_customer\n";
        continue;
    }

    $display = $data['entry'][0]['resource']['description'] ?? null;
    $latitude = $data['entry'][0]['resource']['position']['latitude'] ?? null;
    $longitude = $data['entry'][0]['resource']['position']['longitude'] ?? null;
    $city = $data['entry'][0]['resource']['address']['city'] ?? null;
    $postal = $data['entry'][0]['resource']['address']['postalCode'] ?? null;

    $resourceType = 'Location';
    $referenceId = $id_customer;
    $resourceId = $id;
    $status = 'success';
    $message = mysqli_escape_string($koneksi, $response);
    mysqli_query($koneksi, "INSERT INTO satusehat_log (id_customer, resource_type, reference_id, resource_id, status, message) VALUES ('$id_customer', '$resourceType', '$referenceId', '$resourceId', '$status', '$message')");

    // update data
    mysqli_query($koneksi, "UPDATE setting_satusehat SET location_id='$id', location_display='$display', latitude='$latitude', longitude='$longitude', city='$city', postal_code='$postal' WHERE id_customer='$id_customer'");
    echo "Updated Location with ID: $id for clinic: $clinic[clinic_name]\n";
}
