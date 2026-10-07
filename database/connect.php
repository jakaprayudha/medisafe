<?php

date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . '/utility/env.php';

loadEnv();

$requiredDatabaseSettings = ['DB_HOST', 'DB_NAME', 'DB_USER'];
$databaseSettings = [];

foreach ($requiredDatabaseSettings as $setting) {
    $value = getenv($setting);
    if ($value === false || trim($value) === '') {
        throw new RuntimeException("Pengaturan $setting belum diisi di file .env.");
    }

    $databaseSettings[$setting] = $value;
}

$databasePort = getenv('DB_PORT');
if ($databasePort === false || $databasePort === '') {
    $databasePort = 3306;
} elseif (filter_var($databasePort, FILTER_VALIDATE_INT) === false || (int) $databasePort < 1) {
    throw new RuntimeException('Pengaturan DB_PORT di file .env harus berupa nomor port yang valid.');
} else {
    $databasePort = (int) $databasePort;
}

$databasePassword = getenv('DB_PASSWORD');
$koneksi = new mysqli(
    $databaseSettings['DB_HOST'],
    $databaseSettings['DB_USER'],
    $databasePassword === false ? '' : $databasePassword,
    $databaseSettings['DB_NAME'],
    $databasePort
);
$koneksi->set_charset('utf8mb4');
