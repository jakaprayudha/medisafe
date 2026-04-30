<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        "metadata" => [
            "message" => "Method not allowed",
            "code" => 201
        ]
    ]);
    exit;
}

$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$username = $headers['x-username'] ?? null;
$password = $headers['x-password'] ?? null;

if (!$username || !$password) {
    echo json_encode([
        "metadata" => [
            "message" => "Header tidak lengkap",
            "code" => 201
        ]
    ]);
    exit;
}

// 🔥 GET USER
$stmt = $koneksi->prepare("SELECT * FROM setting_antrol WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ❌ USER TIDAK ADA
if (!$user) {
    echo json_encode([
        "metadata" => [
            "message" => "Username tidak ditemukan",
            "code" => 201
        ]
    ]);
    exit;
}

// ❌ PASSWORD CHECK
// NOTE: pastikan ini sesuai sistem kamu (lihat catatan bawah)
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "metadata" => [
            "message" => "Username atau Password Tidak Sesuai",
            "code" => 201
        ]
    ]);
    exit;
}

// 🔐 JWT CONFIG
$secret_key = $user['secret_key'];

$config = Configuration::forSymmetricSigner(
    new Sha256(),
    InMemory::plainText($secret_key)
);

$now = new DateTimeImmutable();

// 🔥 GENERATE TOKEN
$token = $config->builder()
    ->issuedAt($now)
    ->expiresAt($now->modify('+60 minutes'))
    ->withClaim('id', $user['id'])
    ->withClaim('username', $user['username'])
    ->getToken($config->signer(), $config->signingKey());

echo json_encode([
    "response" => [
        "token" => $token->toString()
    ],
    "metadata" => [
        "message" => "OK",
        "code" => 200
    ]
]);