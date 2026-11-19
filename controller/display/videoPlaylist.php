<?php
/** GET -> playlist video display untuk faskes di session (dipakai display-admisi untuk menyegarkan daftar putar). */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/videoDisplay.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if (!$idc) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Session tidak ditemukan']);
   exit;
}

// display berada di module/display/, jadi URL video relatif ke sana
echo json_encode(['status' => 'success'] + vdPlaylist($idc, '../../'), JSON_UNESCAPED_SLASHES);
