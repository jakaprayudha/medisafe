<?php

require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

function getheader($containType = "application/json; charset=utf-8")
{
    global $consid, $tStamp, $encodedSignature, $userkey, $encodedAuthorization;
    $headers = array(
        "X-cons-id: " . $consid,
        "X-timestamp: " . $tStamp,
        "X-signature: " . $encodedSignature,
        "X-authorization: Basic " . $encodedAuthorization,
        'user_key: ' . $userkey,
        "Content-Type: " . $containType
    );
    return $headers;
}
function bpjsGet($endpoint)
{
    global $base_url, $service, $consid, $secretKey, $tStamp;

    $url = rtrim($base_url, '/') . '/' . trim($service, '/') . '/' . ltrim($endpoint, '/');
    // echo trim($url);die();
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => getheader(),
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

    return bpjsDecryptResponse($response, $consid, $secretKey, $tStamp);
}
function bpjsPost($endpoint, array $payload, $method = "POST")
{
    global $base_url, $service, $consid, $secretKey, $tStamp;
    $url = rtrim($base_url, '/') . '/' . trim($service, '/') . '/' . ltrim($endpoint, '/');
    $containType = "text/plain";
    // echo trim($url);die();
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => getheader($containType),
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
    if (!$response) {
        return bpjsError("Tidak ada response dari server BPJS");
    }

    return bpjsDecryptResponse($response, $consid, $secretKey, $tStamp);
}

function bpjsDelete($endpoint)
{
    global $base_url, $service, $consid, $secretKey, $tStamp;

    $url = rtrim($base_url, '/') . '/' . trim($service, '/') . '/' . ltrim($endpoint, '/');
    // echo trim($url);die();
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => getheader("application/json; charset=utf-8"),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    // echo $response;die();
    if ($err) {
        return bpjsError("CURL Error: " . $err);
    }

    if (!$response) {
        return bpjsError("Tidak ada response dari server BPJS");
    }

    return bpjsDecryptResponse($response, $consid, $secretKey, $tStamp);
}

function bpjsDecryptResponse($response, $consid, $secretKey, $tStamp)
{
    $json = json_decode($response, true);

    if (!$json || !isset($json['metaData'])) {
        return bpjsError("Format response tidak valid");
    }

    if (!in_array($json['metaData']['code'], ["200", "201"])) {

        $groupedErrors = [];

        if (isset($json['response']) && is_array($json['response'])) {
            foreach ($json['response'] as $err) {
                $field = $err['field'] ?? 'Unknown Field';
                $message = $err['message'] ?? '';
                $label = ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $field));
                $groupedErrors[$label][] = $message;
            }
        }
        $finalMessage = "Terjadi Kesalahan:\n\n";
        foreach ($groupedErrors as $field => $messages) {
            $finalMessage .= "• $field\n";
            foreach ($messages as $msg) {
                $finalMessage .= "   - $msg\n";
            }
            $finalMessage .= "\n";
        }
        return [
            'success' => false,
            'code' => $json['metaData']['code'],
            'message' => $json['metaData']['message'],
            'errors' => $finalMessage,
            'data' => null
        ];
    }


    $key = $consid . $secretKey . $tStamp;
    $decrypted = stringDecrypt($key, $json['response']);
    $decompressed = \LZCompressor\LZString::decompressFromEncodedURIComponent($decrypted);

    return [
        'success' => true,
        'code' => '200',
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
