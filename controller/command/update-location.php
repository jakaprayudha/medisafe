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

$clinics = tampildata("SELECT setting_clinic.id_customer, clinic_name, organization_id, location_display, location_id FROM setting_clinic JOIN  setting_satusehat ON setting_satusehat.id_customer=setting_clinic.id_customer where (setting_satusehat.client_id is not null and setting_satusehat.client_secret is not null and setting_satusehat.organization_id is not null) order by id_customer desc");

// Load location.json
$locationData = json_decode(file_get_contents(__DIR__ . '/location.json'), true);

// Build a map of organization_id to alamat
$orgAlamatMap = [];
foreach ($locationData as $loc) {
    if (isset($loc['organization_id']) && isset($loc['alamat'])) {
        $orgAlamatMap[$loc['organization_id']] = $loc['alamat'];
    }
}

foreach ($clinics as $clinic) {
    $id_customer = $clinic['id_customer'];
    $organizationId = $clinic['organization_id'];
    $locationId = $clinic['location_id'];
    $url = $fhirUrl . "/Location/$locationId";

    $token = json_decode(generateToken($id_customer), true);

    $headers = [
        'Content-Type: application/json-patch+json',
        'Authorization: Bearer ' . $token['access_token']
    ];

    // Use alamat from location.json if available, otherwise fallback to clinic_name
    $displayValue = isset($orgAlamatMap[$organizationId]) ? $orgAlamatMap[$organizationId] : $clinic['clinic_name'];

    $jsonData = json_encode([
        [
            'op' => 'replace',
            'path' => '/description',
            'value' => $displayValue
        ]
    ]);

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
        die;
    }

    curl_close($ch);

    $data = json_decode($response, true);

    $description = $data['description'];

    // update data
    mysqli_query($koneksi, "UPDATE setting_satusehat SET location_display='$description' WHERE id_customer='$id_customer'");
}
