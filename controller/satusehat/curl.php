<?php

function get($apiUrl, $contentType = 'application/json')
{
    $token = json_decode(generateToken(), true);

    // Headers
    $headers = [
        'Content-Type: ' . $contentType,
        'Authorization: Bearer ' . $token['access_token']
    ];

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute cURL session and store the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
        die;
    }

    // Close cURL session
    curl_close($ch);

    return $response;
}

function post($apiUrl, $data, $contentType = 'application/json')
{
    $token = json_decode(generateToken(), true);

    // Headers
    $headers = [
        'Content-Type: ' . $contentType,
        'Authorization: Bearer ' . $token['access_token']
    ];

    // Convert the data to JSON format
    $jsonData = json_encode($data);

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // echo json_encode($headers);
    // die;
    // Execute cURL session and store the response
    $response = curl_exec($ch);

    // Check for cURL errors
    try {
        $res = json_decode($response, true);
        if (@$res['metadata']['code'] != 200) {
            // Log file path
            $logDir = dirname(__DIR__, 2) . "/logs/satusehat";
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            $logFilePath = $logDir . "/error-api-" . date('Y-m-d') . ".log";

            // Add timestamp to the message
            $messageWithTimestamp = "[" . date('Y-m-d H:i:s') . "] " . $response . ' | Data: ' . $jsonData . PHP_EOL;

            // Write message to the log file (append mode)
            file_put_contents($logFilePath, $messageWithTimestamp, FILE_APPEND | LOCK_EX);
        }
    } catch (\Throwable $th) {
        // throw $th;
    }

    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
        die;
    }

    // Close cURL session
    curl_close($ch);

    return $response;
}
