<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../database/connect.php';
header('Content-Type: application/json');
$id_visit = $_POST['id'];
$id_customer = $_SESSION['id_customer'];
$sql1 = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT pv.id_visit, pv.code_doctor, pv.noKartu, d.icare_password, d.icare_username FROM pasien_visit AS pv INNER JOIN master_doctor_bpjs AS d ON pv.code_doctor = d.kdDokter WHERE pv.id_visit = '$id_visit' AND d.id_customer = '$id_customer'"));
// $nokartu = $sql1['noKartu'];
$nokartu = "0001488733424";
$sql = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM `setting_pcare` WHERE id_customer = '$id_customer'"));
$secretKey = $sql['secret_key'];
$userkey = $sql['user_key'];
$username = $sql1['icare_username'];
$password = $sql1['icare_password'];
$kdAplikasi = '095';
$const_id = $sql['cons_id'];
date_default_timezone_set('UTC');
$tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
$signature = hash_hmac('sha256', $const_id . "&" . $tStamp, $secretKey, true);
$encodedSignature = base64_encode($signature);
$encodedAuthorization = base64_encode($username . ":" . $password . ":" . $kdAplikasi);
$payload = [
    "param" => $nokartu,
];
$result = bpjsPostIcare($payload);
if ($result['code'] != '200') {
    $msg = $result['metadata'];
    if ($msg == null) {
        $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    }
    $response = [
        'success' => false,
        'message' => $msg,
    ];
} else {
    $response = [
        'success' => true,
        'message' => $result,
    ];
}
echo json_encode($response);


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
    $url = 'https://apijkn.bpjs-kesehatan.go.id/wsihs/api/pcare/validate';
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
function bpjsDecryptResponse($response, $consid, $secretKey, $tStamp, $decrypt = true){
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
function stringDecrypt($key, $dtdecrypt){
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
function bpjsError($message){
    return [
        'success' => false,
        'code' => '500',
        'message' => $message,
        'data' => null
    ];
}