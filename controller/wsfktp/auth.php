<?php
header("Content-Type: application/json");
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/JWT.php';
require_once __DIR__ . '/Key.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "e90a6b842d211f2b010e84c025bcbed2c62e6e92";

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

if (!$username || !$password){
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

if (!$user || !password_verify($password, $user['password'])){
    echo json_encode([
        "metadata" => [
            "message" => "Username atau Password salah",
            "code" => 401,
            'username' => $username,
            'paddword' => $password
        ]
    ]);
    exit;
}
$stmt->close();
$payload = [
    "iat" => time(),
    "exp" => time() + 300,
    "data" => [
        "id" => $user['id'],
        "username" => $user['username']
    ]
];

$token = JWT::encode($payload, $secret_key, 'HS256');
echo json_encode([
    "response" => [
        "token" => $token
    ],
    "metadata" => [
        "message" => "Ok",
        "code" => 200
    ]
]);