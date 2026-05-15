<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';

header('Content-Type: application/json');

$tipe = $_GET['type'] ?? null;
$nomor_kartu = trim($_GET['nomor'] ?? '');
$lengthkartu = strlen($nomor_kartu);
if (!in_array($lengthkartu, [13, 16])) {
    $response = [
        'success' => false,
        'message' => 'Nomor harus 13 digit (BPJS) atau 16 digit (NIK)'
    ];
} elseif (!ctype_digit($nomor_kartu) && $lengthkartu != 19) {
    $response = [
        'success' => false,
        'message' => 'Nomor harus berupa angka'
    ];
} else {
    $result = bpjsGet('/peserta/' . $tipe . '/' . $nomor_kartu);
    if (($result['code'] ?? '') != "200") {
        $msg = $result['message'] ?? "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
        $response = [
            'success' => false,
            'code' => $result['data']['aktif'],
            'message' => $msg,
        ];
    } else {
        $response = [
            'success' => true,
            'code' => $result['data']['aktif'],
            'message' => $result['data']['ketAktif'],
            'noKartu' => $result['data']['noKartu'],
            'nik' => $result['data']['noKTP'],
            'nama' => $result['data']['nama'],
            'sex' => $result['data']['sex'],
            'tglLahir' => date('Y-m-d', strtotime(str_replace('-', '/', $result['data']['tglLahir']))), 
            'tglLahirsebelum' => $result['data']['tglLahir'], 
            'golDarah' => $result['data']['golDarah'],
            'noHP' => $result['data']['noHP']
        ];
    }
}

echo json_encode($response);
