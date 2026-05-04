<?php
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../vendor/autoload.php';
session_start();
$idcustomer = $_SESSION['id_customer'];
$sql = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM `setting_antrol` WHERE id_customer = '$idcustomer'"));
$base_url = 'https://apijkn-dev.bpjs-kesehatan.go.id/';
$service = 'antreanfktp_dev';
// date_default_timezone_set('Asia/Jakarta');
$kodeppk = $sql['kodePPK'];
$tanggal = date('Y-m-d');
$tglbulan = date('d') . ' ' . getNamaBulan(date('n')) . ' ' . date('Y');
$waktusekarang = date('Y-m-d H:i:s');
$secretKey = $sql['secretkey'];
$userkey = $sql['userkey'];
$const_id = $sql['constid'];
$encodedSignature = base64_encode($signature);

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

function getHeaders($const_id, $tStamp, $signature, $userkey)
{
    return [
        "X-cons-id: $const_id",
        "X-timestamp: $tStamp",
        "X-signature: $signature",
        "user_key: $userkey",
        "Content-Type: application/json; charset=utf-8",
    ];
}

function bpjsGet($endpoint){
    global $base_url, $service, $const_id, $secretKey, $userkey;
    $url = rtrim($base_url, '/') . '/' . trim($service, '/') . '/' . ltrim($endpoint, '/');
    $auth = generateSignature($const_id, $secretKey);
    $headers = getHeaders(
        $const_id,
        $auth['timestamp'],
        $auth['signature'],
        $userkey
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
    // echo $url;
    // echo $response;
    // die();
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

function bpjsPost($endpoint, array $payload, $method = "POST"){
    global $base_url, $service, $const_id, $secretKey, $userkey;
    $url = rtrim($base_url, '/') . '/' . trim($service, '/') . '/' . ltrim($endpoint, '/');
    $auth = generateSignature($const_id, $secretKey);
    $headers = getHeaders(
        $const_id,
        $auth['timestamp'],
        $auth['signature'],
        $userkey
    );
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

    // ❗ hanya 200 yang dianggap sukses
    if ($code !== '200') {
        return [
            'success' => false,
            'code' => $code,
            'message' => $json['metadata']['message'] ?? 'Error BPJS',
            'data' => null
        ];
    }

    if (!isset($json['response'])) {
        return bpjsError("Response kosong");
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

    return json_decode($decompressed, true);
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