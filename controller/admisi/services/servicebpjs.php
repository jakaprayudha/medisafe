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
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 30,
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
        $errorMessage = bpjsPesanError($code, (string) ($json['metaData']['message'] ?? ''), $json['response'] ?? null);
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
/**
 * Susun pesan error BPJS/PCare yang bisa dipahami user.
 * Rincian per kolom dari BPJS (response = [{field, message}] / {field, message} / string) diutamakan.
 * Bila BPJS tidak mengirim rincian, dulu yang tampil hanya kode teknis (mis. "PRECONDITION_FAILED");
 * kini diberi penjelasan sesuai kode + kode teknisnya tetap disertakan untuk dilaporkan ke BPJS.
 */
function bpjsPesanError(string $code, string $metaMessage, $response): string
{
    $rinci = [];
    $tambah = function ($err) use (&$rinci) {
        if (is_string($err)) {
            if (trim($err) !== '') $rinci[] = trim($err);
            return;
        }
        if (!is_array($err)) return;
        $msg = trim((string) ($err['message'] ?? ''));
        if ($msg === '') return;
        $field = trim((string) ($err['field'] ?? ''));
        $label = [
            'kddiag1' => 'Diagnosa utama', 'kddiag2' => 'Diagnosa 2', 'kddiag3' => 'Diagnosa 3', 'kdtacc' => 'TACC', 'alasantacc' => 'Alasan TACC',
            'kddokter' => 'Dokter', 'kdpoli' => 'Poli', 'kdpolirujukinternal' => 'Poli rujuk internal', 'kdstatuspulang' => 'Status pulang',
            'tglpulang' => 'Tanggal pulang', 'tgldaftar' => 'Tanggal daftar', 'nokartu' => 'No. kartu BPJS', 'nokunjungan' => 'No. kunjungan',
            'kdsadar' => 'Kesadaran', 'kdprognosa' => 'Prognosa', 'kdppk' => 'Faskes rujukan', 'tglestrujuk' => 'Tanggal estimasi rujuk',
            'kdsubspesialis1' => 'Subspesialis', 'kdsarana' => 'Sarana', 'kdkhusus' => 'Rujukan khusus', 'keluhan' => 'Keluhan', 'anamnesa' => 'Anamnesa',
            'sistole' => 'Sistole', 'diastole' => 'Diastole', 'beratbadan' => 'Berat badan', 'tinggibadan' => 'Tinggi badan', 'resprate' => 'Respirasi',
            'heartrate' => 'Nadi', 'lingkarperut' => 'Lingkar perut', 'suhu' => 'Suhu', 'terapiobat' => 'Terapi obat', 'terapinonobat' => 'Terapi non-obat',
            'kdtindakan' => 'Tindakan', 'biaya' => 'Biaya',
        ][strtolower($field)] ?? ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', $field));
        $rinci[] = $field !== '' ? $label . ': ' . $msg : $msg;
    };
    if (is_string($response)) {
        $tambah($response);
    } elseif (is_array($response)) {
        // satu objek {field, message} atau daftar objek
        if (isset($response['message']) || isset($response['field'])) $tambah($response);
        else foreach ($response as $err) $tambah($err);
    }

    $penjelasan = [
        '400' => 'Permintaan ke BPJS tidak valid. Periksa kembali data yang diisi.',
        '401' => 'Akses ke BPJS ditolak (kredensial PCare tidak valid atau kedaluwarsa). Hubungi admin untuk memeriksa pengaturan bridging BPJS.',
        '404' => 'Data tidak ditemukan di BPJS (mis. no. kunjungan / no. kartu / kode referensi tidak terdaftar).',
        '412' => 'Data ditolak BPJS karena belum memenuhi syarat PCare. Periksa kembali: diagnosa (kode ICD-10 valid), dokter & poli sesuai jadwal PCare, tanggal kunjungan/pulang, status pulang, isian TACC bila diagnosa non-spesialistik dirujuk, serta kepesertaan BPJS pasien aktif.',
        '424' => 'Data terkait di BPJS belum tersedia (mis. pendaftaran PCare belum ada). Pastikan pasien sudah terdaftar di PCare.',
        '428' => 'Data yang sama sudah tercatat di BPJS (duplikat). Periksa apakah data ini sudah pernah dikirim.',
        '500' => 'Server BPJS sedang mengalami gangguan. Coba lagi beberapa saat lagi.',
        '503' => 'Layanan BPJS sedang tidak tersedia / sibuk. Coba lagi beberapa saat lagi.',
    ];
    $kodeTeknis = 'Kode BPJS: ' . $code . ($metaMessage !== '' ? ' ' . $metaMessage : '');

    if ($rinci) {
        // rincian dari BPJS sudah spesifik; tambahkan kode teknis untuk laporan
        return implode("\n", $rinci) . "\n(" . $kodeTeknis . ')';
    }
    $teks = $penjelasan[$code] ?? ($metaMessage !== '' && !preg_match('/^[A-Z_]+$/', $metaMessage)
        ? $metaMessage
        : 'BPJS menolak permintaan tanpa keterangan rinci.');
    return $teks . "\n(" . $kodeTeknis . ')';
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
