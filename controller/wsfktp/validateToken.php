<?php

require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Token\Plain;

function validateBpjsToken($usernameParam)
{
    global $koneksi;

    // ambil config user
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
                "code" => 201
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
                "code" => 201
            ]
        ]);
        exit;
    }

    // JWT config
    $config = Configuration::forSymmetricSigner(
        new Sha256(),
        InMemory::plainText($secret_key)
    );

    try {
        // parse token
        $token = $config->parser()->parse($tokenString);

        // clock untuk validasi exp/iat
        $clock = new SystemClock(new DateTimeZone('Asia/Jakarta'));

        // validasi signature + expired
        $config->validator()->assert(
            $token,
            new SignedWith($config->signer(), $config->verificationKey()),
            new ValidAt($clock)
        );

        /** @var Plain $token */
        $claims = $token->claims()->all();

        $username = $claims['username'] ?? null;

        if (!$username) {
            echo json_encode([
                "metadata" => [
                    "message" => "Username claim missing in token",
                    "code" => 201
                ]
            ]);
            exit;
        }

        // cocokkan username header vs token
        if ($username !== $headerUsername) {
            echo json_encode([
                "metadata" => [
                    "message" => "Username does not match token",
                    "code" => 201
                ]
            ]);
            exit;
        }

        return $user['id_customer'];

    } catch (\Throwable $e) {
        echo json_encode([
            "metadata" => [
                "message" => "Invalid or expired token",
                "code" => 201,
            ]
        ]);
        exit;
    }
}