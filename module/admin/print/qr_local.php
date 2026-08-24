<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

if (!function_exists('qrDataUri')) {
    function qrDataUri(string $data, int $size = 100): string
    {
        static $cache = [];
        $key = $size . '|' . $data;
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        try {
            $result = (new Builder(
                writer: new PngWriter(),
                data: $data,
                size: $size,
                margin: 4
            ))->build();
            return $cache[$key] = $result->getDataUri();
        } catch (Throwable $e) {
            error_log('[qr_local] Gagal generate QR: ' . $e->getMessage());
            return '';
        }
    }
}
