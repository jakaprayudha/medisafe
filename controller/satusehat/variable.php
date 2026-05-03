<?php
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/func.php';
require_once __DIR__ . '/curl.php';

date_default_timezone_set('Asia/Jakarta');

$baseUrl = 'https://api-satusehat.kemkes.go.id';
$fhirUrl = $baseUrl . '/fhir-r4/v1';
$consentUrl = $baseUrl . '/consent/v1';

// $clientId = getSetting('client_id', 'setting_satusehat');
// $clientSecret = getSetting('client_secret', $id_customer);
// $organizationId = getSetting('organization_id', $id_customer);

function generateToken($id_customer)
{
    $token = getSetting('token', $id_customer);
    $expireAt = getSetting('expire_at', $id_customer);
    if ($expireAt) {
        $expireAt = strtotime($expireAt);
        $now = time();
        if ($expireAt > $now) {
            return json_encode([
                'access_token' => $token
            ]);
        }
    }
    global $baseUrl;
    $clientId = getSetting('client_id', $id_customer);
    $clientSecret = getSetting('client_secret', $id_customer);
    $authUrl = $baseUrl . '/oauth2/v1/accesstoken?grant_type=client_credentials';

    $headers = [
        'Content-Type: application/x-www-form-urlencoded',
    ];

    $data = "client_id=$clientId&client_secret=$clientSecret";

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $authUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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

    $res = json_decode($response, true);
    if (@$res['access_token']) {
        global $koneksi;
        $newTimestamp = time() + $res['expires_in'];

        $expireAt = date('Y-m-d H:i:s', $newTimestamp);

        mysqli_query($koneksi, "UPDATE setting_satusehat SET token='$res[access_token]', expire_at='$expireAt' WHERE id_customer='$id_customer'");
    }

    return $response;
}

function getLocation($id_customer)
{
    global $koneksi;
    global $fhirUrl;
    $organizationId = getSetting('organization_id', $id_customer);

    $locationId = getSetting('location_id', $id_customer);
    $locationDisplay = getSetting('location_display', $id_customer);
    if ($locationId && $locationDisplay) {
        return [
            'id' => $locationId,
            'display' => $locationDisplay
        ];
    }

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

    $data = json_decode($response, true);
    $location = $data['entry'][0]['resource'] ?? null;
    $locationId = $location['id'] ?? null;
    $locationDisplay = $location['description'] ?? null;

    mysqli_query($koneksi, "UPDATE setting_satusehat SET location_id='$locationId', location_display='$locationDisplay' WHERE id_customer='$id_customer'");

    return [
        'id' => $locationId,
        'display' => $locationDisplay
    ];
}
