<?php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['uid_user']) || empty($_SESSION['id_customer'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session berakhir. Silakan login kembali.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}
if (!is_string($_POST['rme_cycle_token'] ?? null) ||
    !hash_equals($_SESSION['rme_cycle_token'] ?? '', $_POST['rme_cycle_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token formulir tidak valid. Muat ulang halaman.']);
    exit;
}
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/rmeCycleService.php';
require_once __DIR__ . '/../admisi/services/view.php';
require_once __DIR__ . '/../admisi/services/servicebpjs.php';

try {
    $result = saveRmeCycle($koneksi, $_POST, (int) $_SESSION['id_customer'], static function (array $post): array {
        global $koneksi;
        $_POST = $post;
        ob_start();
        try {
            require __DIR__ . '/../admisi/services/doctor/insertKunjungan.php';
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    });
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (InvalidArgumentException $error) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log('RME cycle save: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Simpan satu siklus gagal. Resep belum ditandai terkirim. Periksa status kunjungan sebelum mencoba kembali atau hubungi administrator.']);
}
