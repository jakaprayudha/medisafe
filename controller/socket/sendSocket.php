<?php
include '../../database/connect.php';
session_start();
$kdRumahSakit = $_SESSION['id_customer'];

// $urlsocket = "http://localhost:3001";
$urlsocket = "https://websocketservermedicine.online";

function trigger($data)
{
    global $urlsocket;
    $payload = json_encode($data);
    $ch = curl_init($urlsocket . "/event");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 3
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode($res, true);
}

function callAntrian($data)
{
    global $urlsocket;
    $payload = json_encode($data);
    $ch = curl_init($urlsocket . "/panggil");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 3
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode($res, true);
}
function pemanggilanAntrian($data)
{
    global $urlsocket;
    $payload = json_encode($data);
    $ch = curl_init($urlsocket . "/suara");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 30
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode($res, true);
}

