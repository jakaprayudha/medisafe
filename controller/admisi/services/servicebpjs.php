<?php

require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

function bpjsGet($endpoint){
    global $base_url, $service, $const_id, $secretKey, $tStamp, $encodedSignature, $userkey, $encodedAuthorization;
    $headers = array(
        "X-cons-id: " . $const_id,
        "X-timestamp: " . $tStamp,
        "X-signature: " . $encodedSignature,
        "X-authorization: Basic " . $encodedAuthorization,
        "user_key: " . $userkey,
        "Content-Type: application/json; charset=utf-8",
    );
    $url = rtrim($base_url, '/') . '/' . trim($service, '/') . '/' . ltrim($endpoint, '/');
    $ch = curl_init();
    // echo "<pre>";
    // print_r($headers);
    // echo "</pre>";
    // echo "\n";
    // echo $url;
    // echo "\n";
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    // echo($response);die();
    if (!$response) {
        return bpjsError("Tidak ada response dari server BPJS");
    }

    return bpjsDecryptResponse($response, $const_id, $secretKey, $tStamp);
}
function bpjsPost($endpoint, array $payload, $method = "POST"){
    global $base_url, $service, $const_id, $secretKey, $tStamp, $encodedSignature, $userkey, $encodedAuthorization;
    $headers = array(
        "X-cons-id: " . $const_id,
        "X-timestamp: " . $tStamp,
        "X-signature: " . $encodedSignature,
        "X-authorization: Basic " . $encodedAuthorization,
        "user_key: " . $userkey,
        "Content-Type: text/plain",
    );
    $url = rtrim($base_url, '/') . '/' . trim($service, '/') . '/' . ltrim($endpoint, '/');
    // echo trim($url);die();
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    // echo $response;die();
    // echo $err;die();
    if (!$response) {
        return bpjsError("Tidak ada response dari server BPJS");
    }

    return bpjsDecryptResponse($response, $const_id, $secretKey, $tStamp);
}
function bpjsPostIcare(array $payload){
    global $const_id, $secretKey, $tStamp, $encodedSignature, $userkey, $encodedAuthorization;
    $headers = array(
        "X-cons-id: " . $const_id,
        "X-timestamp: " . $tStamp,
        "X-signature: " . $encodedSignature,
        "X-authorization: Basic " . $encodedAuthorization,
        "user_key: " . $userkey,
        "Content-Type: application/json",
    );
    // $url = 'https://apijkn.bpjs-kesehatan.go.id/ihs/api/pcare/validate';
    $url = 'https://apijkn-dev.bpjs-kesehatan.go.id/ihs_dev/api/pcare/validate';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    // echo $response;die();
    // echo $err;die();
    if (!$response) {
        return bpjsError("Tidak ada response dari server BPJS");
    }

    return bpjsDecryptResponse($response, $const_id, $secretKey, $tStamp);
}
function bpjsDelete($endpoint)
{
    global $base_url, $service, $const_id, $secretKey, $tStamp, $encodedSignature, $userkey, $encodedAuthorization;
    $headers = array(
        "X-cons-id: " . $const_id,
        "X-timestamp: " . $tStamp,
        "X-signature: " . $encodedSignature,
        "X-authorization: Basic " . $encodedAuthorization,
        "user_key: " . $userkey,
        "Content-Type: application/json; charset=utf-8",
    );
    $url = rtrim($base_url, '/') . '/' . trim($service, '/') . '/' . ltrim($endpoint, '/');
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        return bpjsError("CURL Error: " . $err);
    }

    if (!$response) {
        return bpjsError("Tidak ada response dari server BPJS");
    }

    return bpjsDecryptResponse($response, $const_id, $secretKey, $tStamp);
}
function bpjsDecryptResponse($response, $consid, $secretKey, $tStamp, $decrypt = true)
{
    $json = json_decode($response, true);
    if (!$json || !isset($json['metaData'])) {
        return bpjsError("Format response tidak valid");
    }
    $code = (string) $json['metaData']['code'];
    if (!in_array($code, ["200", "201"])) {
        $errorMessage = 'Terjadi kesalahan';
        if (isset($json['response']) && is_string($json['response'])) {
            $errorMessage = $json['response'];
        } elseif (isset($json['response']) && is_array($json['response'])) {
            $messages = [];
            foreach ($json['response'] as $err) {
                $field = $err['field'] ?? '';
                $msg   = $err['message'] ?? 'Kesalahan tidak diketahui';
                if ($field) {
                    $label = ucfirst(
                        preg_replace('/([a-z])([A-Z])/', '$1 $2', $field)
                    );
                    $messages[] = "{$label}: {$msg}";
                } else {
                    $messages[] = $msg;
                }
            }
            $errorMessage = implode("\n", $messages);
        }
        return [
            'success' => false,
            'code'    => $code,
            'message' => $errorMessage,
            'metadata' => $json['metaData']['message'],
            'data'    => null,
            'response' => $response,
            'json' => $json
        ];
    }
    $key = $consid . $secretKey . $tStamp;
    $rawResponse = $json['response'];
    $data = null;
    if (is_string($rawResponse)) {
        if (strlen($rawResponse) > 80) {
            $decrypted = stringDecrypt($key, $rawResponse);
            $decompressed = \LZCompressor\LZString::decompressFromEncodedURIComponent($decrypted);
            $data = json_decode($decompressed, true);
        } else {
            $data = json_decode($rawResponse, true);
        }
    } elseif (is_array($rawResponse)) {
        $data = $rawResponse;
    } else {
        $data = $rawResponse;
    }
    return [
        'success' => true,
        'code'    => '200',
        'message' => 'OK',
        'data'    => $data
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
function testingBPJS_GET($url)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        return [
            'success' => false,
            'code'    => 500,
            'message' => 'Curl error: ' . curl_error($ch),
            'data'    => null
        ];
    }

    curl_close($ch);

    $json = json_decode($response, true);

    $data = $json['response'] ?? null;

    return [
        'success' => !empty($data),
        'code'    => $json['metaData']['code'] ?? 200,
        'message' => $json['metaData']['message'] ?? 'OK',
        'data'    => $data
    ];
}
