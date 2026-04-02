<?php
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/JWT.php';
require_once __DIR__ . '/Key.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function validateBpjsToken($username)
{
    global $koneksi;
    $stmt = $koneksi->prepare("SELECT * FROM setting_antrol WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    $token = $headers['x-token'] ?? null;
    $username = $headers['x-username'] ?? null;
    if (!$token || !$username) {
        echo json_encode([
            "metadata" => [
                "message" => "Header tidak lengkap",
                "code" => 400
            ]
        ]);
        exit;
    }
    try {
        $decoded = JWT::decode($token, new Key($user['secret_key'], 'HS256'));
        $tokenUser = $decoded->data->username ?? null;
        if ($tokenUser !== $username) {
            echo json_encode([
                "metadata" => [
                    "message" => "Username tidak sesuai dengan token",
                    "code" => 401
                ]
            ]);
            exit;
        }
        if ($decoded->exp < time()) {
            echo json_encode([
                "metadata" => [
                    "message" => "Token expired",
                    "code" => 401
                ]
            ]);
            exit;
        }
        return $decoded->data;
    } catch (Exception $e) {
        echo json_encode([
            "metadata" => [
                "message" => "Token tidak valid",
                "code" => 401,
                "error" => $e->getMessage()
            ]
        ]);
        exit;
    }
}
