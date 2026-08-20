<?php
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../vendor/autoload.php';
session_start();
$idcustomer = $_SESSION['id_customer'];
// $idcustomer = '3';
$sql = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM `setting_antrol` WHERE id_customer = '$idcustomer'"));
if ($sql) {
    $status_antrol = true;
} else {
    $status_antrol = false;
}
$base_url = $sql['base_url'];
$service = $sql['service'];
// date_default_timezone_set('Asia/Jakarta');
$kodeppk = $sql['kodePPK'];
$tanggal = date('Y-m-d');
$tglbulan = date('d') . ' ' . getNamaBulan(date('n')) . ' ' . date('Y');
$waktusekarang = date('Y-m-d H:i:s');
$secretKey = $sql['secretkey'];
$userkey = $sql['userkey'];
$const_id = $sql['constid'];
// $encodedSignature = base64_encode($signature);

function generateSignature($const_id, $secretKey)
{
    date_default_timezone_set('UTC');
    $tStamp = strval(time());
    $signature = hash_hmac('sha256', $const_id . "&" . $tStamp, $secretKey, true);
    return [
        'timestamp' => $tStamp,
        'signature' => base64_encode($signature)
    ];
}

function getNamaBulan($bulan)
{
    $daftarBulan = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    return $daftarBulan[$bulan - 1];
}

function getHeaders($const_id, $tStamp, $signature, $userkey){
    return [
        "X-cons-id: $const_id",
        "X-timestamp: $tStamp",
        "X-signature: $signature",
        "user_key: $userkey",
        // "Content-Type: application/json; charset=utf-8",
    ];
}

function bpjsGetService($endpoint)
{
    global $base_url, $service, $const_id, $secretKey, $userkey;
    $url = rtrim($base_url, '/') . '/' . trim($service, '/') . '/' . ltrim($endpoint, '/');
    $auth = generateSignature($const_id, $secretKey);
    $headers = getHeaders(
        $const_id,
        $auth['timestamp'],
        $auth['signature'],
        $userkey
    );
    // echo "<pre>";
    // print_r($headers);
    // echo "</pre>";die();
    // echo $url;die();
    // echo json_encode($payload, JSON_PRETTY_PRINT);die();
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    // echo $url;
    // echo $response;die();
    curl_close($ch);
    if ($err) {
        return bpjsError("cURL Error: " . $err);
    }
    if (!$response) {
        return bpjsError("Tidak ada response dari server BPJS");
    }
    return bpjsDecryptResponse(
        $response,
        $const_id,
        $secretKey,
        $auth['timestamp']
    );
}

function bpjsGet($endpoint, $config)
{
    $url = rtrim($config['base_url'], '/') . '/' . trim($config['service'], '/') . '/' . ltrim($endpoint, '/');
    $auth = generateSignature($config['const_id'], $config['secretKey']);
    $headers = getHeaders(
        $config['const_id'],
        $auth['timestamp'],
        $auth['signature'],
        $config['userkey']
    );
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    // echo $response;die();
    curl_close($ch);

    if ($err) {
        return bpjsError("cURL Error: " . $err);
    }

    if (!$response) {
        return bpjsError("Tidak ada response dari server BPJS");
    }

    return bpjsDecryptResponse(
        $response,
        $config['const_id'],
        $config['secretKey'],
        $auth['timestamp']
    );
}

function bpjsPost($endpoint, array $payload, $method = "POST")
{
    global $base_url, $service, $const_id, $secretKey, $userkey;
    $url = rtrim($base_url, '/') . '/' . trim($service, '/') . '/' . ltrim($endpoint, '/');
    $auth = generateSignature($const_id, $secretKey);
    $headers = getHeaders(
        $const_id,
        $auth['timestamp'],
        $auth['signature'],
        $userkey
    );
    // echo "<pre>";
    // print_r($headers);
    // echo "</pre>";
    // echo $url;die();
    // echo json_encode($payload, JSON_PRETTY_PRINT);die();
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    // echo $response;die();
    // echo $err;die();
    curl_close($ch);
    if ($err) {
        return bpjsError("cURL Error: " . $err);
    }
    if (!$response) {
        return bpjsError("Tidak ada response dari server BPJS");
    }
    return bpjsDecryptResponse(
        $response,
        $const_id,
        $secretKey,
        $auth['timestamp']
    );
}

function bpjsDecryptResponse($response, $consid, $secretKey, $tStamp)
{
    $json = json_decode($response, true);
    if (!$json || !isset($json['metadata'])) {
        return bpjsError("Format response tidak valid");
    }
    $code = (string)($json['metadata']['code'] ?? '');
    if ($code !== '200') {
        return [
            'success' => false,
            'code' => $code,
            'message' => $json['metadata']['message'] ?? 'Error BPJS',
            'data' => null
        ];
    }
    if (!isset($json['response'])) {
        return [
            'success' => true,
            'code' => $code,
            'message' => $json['metadata']['message'] ?? 'OK',
            'data' => null
        ];
    }
    $key = $consid . $secretKey . $tStamp;
    $rawResponse = $json['response'];
    if (is_array($rawResponse)) {
        return [
            'success' => true,
            'code' => $code,
            'message' => 'OK',
            'data' => $rawResponse
        ];
    }
    $decrypted = stringDecrypt($key, $rawResponse);
    if (!$decrypted) {
        return bpjsError("Decrypt gagal");
    }
    $decompressed = \LZCompressor\LZString::decompressFromEncodedURIComponent($decrypted);
    if (!$decompressed) {
        return bpjsError("Decompress gagal");
    }
    return [
        'success' => true,
        'code' => $code,
        'message' => 'OK',
        'data' => json_decode($decompressed, true)
    ];
}

function stringDecrypt($key, $dtdecrypt)
{
    $encrypt_method = 'AES-256-CBC';
    $key_hash = hex2bin(hash('sha256', $key));
    $iv = substr($key_hash, 0, 16);

    return openssl_decrypt(
        base64_decode($dtdecrypt),
        $encrypt_method,
        $key_hash,
        OPENSSL_RAW_DATA,
        $iv
    );
}

function bpjsError($message)
{
    return [
        'success' => false,
        'code' => '500',
        'message' => $message,
        'data' => null
    ];
}
function getConfigBPJS($idcustomer, $koneksi)
{
    $sql = mysqli_fetch_assoc(mysqli_query(
        $koneksi,
        "SELECT * FROM setting_antrol WHERE id_customer = '$idcustomer'"
    ));

    if (!$sql) {
        return null;
    }

    return [
        'base_url'  => $sql['base_url'],
        'service'   => $sql['service'],
        'const_id'  => $sql['constid'],
        'secretKey' => $sql['secretkey'],
        'userkey'   => $sql['userkey'],
    ];
}
function testingBPJS_POST($url, $payload)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        return [
            'success' => false,
            'code' => '500',
            'message' => 'Curl error: ' . curl_error($ch),
            'data' => null
        ];
    }

    curl_close($ch);

    $json = json_decode($response, true);

    // Ambil response sep jika ada
    $sepData = $json['response'] ?? null;

    return [
        'success' => $sepData ? true : false,
        'code' => $json['metaData']['code'] ?? '200',
        'message' => $json['metaData']['message'] ?? 'OK',
        'data' => $sepData
    ];
}
// 