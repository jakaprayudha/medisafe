<?php
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// $idcustomer = $_SESSION['id_customer'];
$idcustomer = '19';

$sql_antrol = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM `setting_antrol` WHERE id_customer = '$idcustomer'"));
if ($sql_antrol) {
    $status_antrol = true;
} else {
    $status_antrol = false;
}

// UBAH VARIABEL GLOBAL MENGGUNAKAN PREFIX $antrol_
$antrol_base_url = $sql_antrol['base_url'];
$antrol_service = $sql_antrol['service'];
// date_default_timezone_set('Asia/Jakarta');
$antrol_kodeppk = $sql_antrol['kodePPK'];
$antrol_tanggal = date('Y-m-d');

// Pengecekan agar tidak bentrok dengan view.php
if (!function_exists('getNamaBulan')) {
    function getNamaBulan($bulan)
    {
        $daftarBulan = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        return $daftarBulan[$bulan - 1];
    }
}

$antrol_tglbulan = date('d') . ' ' . getNamaBulan(date('n')) . ' ' . date('Y');
$antrol_waktusekarang = date('Y-m-d H:i:s');
$antrol_secretKey = $sql_antrol['secretkey'];
$antrol_userkey = $sql_antrol['userkey'];
$antrol_const_id = $sql_antrol['constid'];


// UBAH NAMA FUNGSI MENGGUNAKAN PREFIX antrol_
function antrolGenerateSignature($const_id, $secretKey){
    date_default_timezone_set('UTC');
    $tStamp = strval(time());
    $signature = hash_hmac('sha256', $const_id . "&" . $tStamp, $secretKey, true);
    return [
        'timestamp' => $tStamp,
        'signature' => base64_encode($signature)
    ];
}

function antrolGetHeaders($const_id, $tStamp, $signature, $userkey){
    return [
        "X-cons-id: $const_id",
        "X-timestamp: $tStamp",
        "X-signature: $signature",
        "user_key: $userkey",
        // "Content-Type: application/json; charset=utf-8",
    ];
}

function antrolGetService($endpoint){
    global $antrol_base_url, $antrol_service, $antrol_const_id, $antrol_secretKey, $antrol_userkey;
    $url = rtrim($antrol_base_url, '/') . '/' . trim($antrol_service, '/') . '/' . ltrim($endpoint, '/');
    $auth = antrolGenerateSignature($antrol_const_id, $antrol_secretKey);
    $headers = antrolGetHeaders(
        $antrol_const_id,
        $auth['timestamp'],
        $auth['signature'],
        $antrol_userkey
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
    curl_close($ch);
    if ($err) {
        return antrolError("cURL Error: " . $err);
    }
    if (!$response) {
        return antrolError("Tidak ada response dari server BPJS");
    }
    return antrolDecryptResponse(
        $response,
        $antrol_const_id,
        $antrol_secretKey,
        $auth['timestamp']
    );
}

function antrolGet($endpoint, $config){
    $url = rtrim($config['base_url'], '/') . '/' . trim($config['service'], '/') . '/' . ltrim($endpoint, '/');
    $auth = antrolGenerateSignature($config['const_id'], $config['secretKey']);
    $headers = antrolGetHeaders(
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
    curl_close($ch);

    if ($err) {
        return antrolError("cURL Error: " . $err);
    }

    if (!$response) {
        return antrolError("Tidak ada response dari server BPJS");
    }

    return antrolDecryptResponse(
        $response,
        $config['const_id'],
        $config['secretKey'],
        $auth['timestamp']
    );
}

function antrolPost($endpoint, array $payload, $method = "POST"){
    global $antrol_base_url, $antrol_service, $antrol_const_id, $antrol_secretKey, $antrol_userkey;
    $url = rtrim($antrol_base_url, '/') . '/' . trim($antrol_service, '/') . '/' . ltrim($endpoint, '/');
    $auth = antrolGenerateSignature($antrol_const_id, $antrol_secretKey);
    $headers = antrolGetHeaders(
        $antrol_const_id,
        $auth['timestamp'],
        $auth['signature'],
        $antrol_userkey
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
    curl_close($ch);
    
    if ($err) {
        return antrolError("cURL Error: " . $err);
    }
    if (!$response) {
        return antrolError("Tidak ada response dari server BPJS");
    }
    return antrolDecryptResponse(
        $response,
        $antrol_const_id,
        $antrol_secretKey,
        $auth['timestamp']
    );
}

function antrolDecryptResponse($response, $consid, $secretKey, $tStamp){
    $json = json_decode($response, true);
    if (!$json || !isset($json['metadata'])) {
        return antrolError("Format response tidak valid");
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
    $decrypted = antrolStringDecrypt($key, $rawResponse);
    if (!$decrypted) {
        return antrolError("Decrypt gagal");
    }
    $decompressed = \LZCompressor\LZString::decompressFromEncodedURIComponent($decrypted);
    if (!$decompressed) {
        return antrolError("Decompress gagal");
    }
    return [
        'success' => true,
        'code' => $code,
        'message' => 'OK',
        'data' => json_decode($decompressed, true)
    ];
}

function antrolStringDecrypt($key, $dtdecrypt){
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

function antrolError($message){
    return [
        'success' => false,
        'code' => '500',
        'message' => $message,
        'data' => null
    ];
}

function antrolGetConfigBPJS($idcustomer, $koneksi){
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

function antrolTestingBPJS_POST($url, $payload){
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