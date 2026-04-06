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
            "code" => 405
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
            "code" => 400
        ]
    ]);
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM setting_antrol WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$secret_key = $user['secret_key'];

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode([
        "metadata" => [
            "message" => "Username atau Password salah",
            "code" => 401,
        ]
    ]);
    exit;
}
$stmt->close();
$config = Configuration::forSymmetricSigner(
    new Sha256(),
    InMemory::plainText($secret_key)
);

$now = new DateTimeImmutable();

$token = $config->builder()
    ->issuedAt($now)
    ->expiresAt($now->modify('+5 minutes'))
    ->withClaim('id', $user['id'])
    ->withClaim('username', $user['username'])
    ->getToken($config->signer(), $config->signingKey());

echo json_encode([
    "response" => [
        "token" => $token->toString()
    ],
    "metadata" => [
        "message" => "Ok",
        "code" => 200
    ]
]);