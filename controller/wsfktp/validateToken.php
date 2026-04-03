<?php

require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

function validateBpjsToken($usernameParam)
{
    global $koneksi;

    // ambil user dari DB
    $stmt = $koneksi->prepare("SELECT * FROM setting_antrol WHERE username = ?");
    $stmt->bind_param('s', $usernameParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo json_encode([
            "metadata" => [
                "message" => "User configuration not found",
                "code" => 401
            ]
        ]);
        exit;
    }

    $secret_key = $user['secret_key'];

    // ambil header
    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    $tokenString = $headers['x-token'] ?? null;
    $headerUsername = $headers['x-username'] ?? null;

    if (!$tokenString || !$headerUsername) {
        echo json_encode([
            "metadata" => [
                "message" => "Missing required headers",
                "code" => 400
            ]
        ]);
        exit;
    }

    // konfigurasi JWT
    $config = Configuration::forSymmetricSigner(
        new Sha256(),
        InMemory::plainText($secret_key)
    );

    try {
        $token = $config->parser()->parse($tokenString);

        // validasi signature
        $config->validator()->assert(
            $token,
            new SignedWith($config->signer(), $config->verificationKey())
        );

        $token = $config->parser()->parse($tokenString);
        /** @var \Lcobucci\JWT\Token\Plain $token */
        $claims = $token->claims();
        $username = $claims->get('username');

        // cek username
        if ($username !== $headerUsername) {
            echo json_encode([
                "metadata" => [
                    "message" => "Username does not match token",
                    "code" => 401
                ]
            ]);
            exit;
        }

        return $token->claims();
    } catch (Exception $e) {
        echo json_encode([
            "metadata" => [
                "message" => "Invalid or expired token",
                "code" => 401,
                "error" => $e->getMessage()
            ]
        ]);
        exit;
    }
}
