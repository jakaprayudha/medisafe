<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';

header('Content-Type: application/json');

$tipe = $_GET['tipe'] ?? null;
$nomor_kartu = trim($_GET['nokartu'] ?? '');
$lengthkartu = strlen($nomor_kartu);
if (!in_array($lengthkartu, [13, 16, 19])) {
    $response = [
        'success' => false,
        'message' => 'Nomor harus 13 digit (BPJS) atau 16 digit (NIK) atau 19 digit (NO RUJUKAN)'
    ];
} elseif (!ctype_digit($nomor_kartu) && $lengthkartu != 19) {
    $response = [
        'success' => false,
        'message' => 'Nomor harus berupa angka'
    ];
} else {
    if ($lengthkartu == 19) {
        $respon = bpjsGet('/kunjungan/rujukan/' . $nomor_kartu);
        $result = [
            "success" => true,
            "code" => "200",
            "message" => "OK",
            'data' => [
                'noKartu' => $respon['data']['nokaPst'],
                'nama' => $respon['data']['nmPst'],
                'hubunganKeluarga' => $respon['data']['ketPisa'],
                'sex' => $respon['data']['sex'],
                'noHP' => $respon['data']['noHP'] ?? "",
                'tglLahir' => $respon['data']['tglLahir'],
                'tglMulaiAktif' => $respon['data']['tglEstRujuk'],
                'tglAkhirBerlaku' => $respon['data']['tglAkhirRujuk'],
                'kdProviderPst' => [
                    'kdProvider' => $respon['data']['ppk']['kdPPK'],
                    'nmProvider' => $respon['data']['ppk']['nmPPK'],
                ],
                'tunggakan' => $respon['data']['infoDenda'],
            ],
        ];
    } else {
        $result = bpjsGet('/peserta/' . $tipe . '/' . $nomor_kartu);
    }
    // $result = testingBPJS_GET("http://localhost/medisafe/controller/admisi/api/getpeserta.php");

    if (($result['code'] ?? '') != "200") {
        $msg = $result['message'] ??
            "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";

        $response = [
            'success' => false,
            'message' => $msg
        ];
    } else {
        $response = $result;
    }
}

echo json_encode($response);
