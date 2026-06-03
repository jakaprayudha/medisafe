<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');

$tipe = $_GET['tipe'] ?? null;
$nomor_kartu = trim($_GET['nokartu'] ?? '');
$lengthkartu = strlen($nomor_kartu);
$stmt = $koneksi->prepare("SELECT * FROM ms_patient WHERE id_customer = ? AND patient_nik = ?");
$stmt->bind_param("ss", $idcustomer, $nomor_kartu);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
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
        if ($respon['data']['aktif'] == 'true') {
            $result = [
                "success" => true,
                "code" => "200",
                "message" => "OK",
                'data' => [
                    'noKartu' => $respon['data']['nokaPst'],
                    'rm' => $data['nomor_rm'] ?? null,
                    'noHP' => $data['patient_phone'] ?? null,
                    'nama' => $respon['data']['nmPst'],
                    'hubunganKeluarga' => $respon['data']['ketPisa'],
                    'sex' => $respon['data']['sex'],
                    'noHP' => $respon['data']['noHP'] ?? "",
                    'tglLahir' => $respon['data']['tglLahir'],
                    'tglMulaiAktif' => $respon['data']['tglEstRujuk'],
                    'tglAkhirBerlaku' => $respon['data']['tglAkhirRujuk'],
                    'kdProvider' => $respon['data']['ppk']['kdPPK'],
                    'nmProvider' => $respon['data']['ppk']['nmPPK'],
                    'tunggakan' => $respon['data']['infoDenda'],
                ],
            ];
        } else {
            $response = [
                'success' => false,
                'message' => $respon['data']['ketAktif']
            ];
        }
    } else {
        $result = bpjsGet('/peserta/' . $tipe . '/' . $nomor_kartu);
        if ($kodeppk == $result['data']['ppk']['kdPPK']) {
            if (($result['code'] ?? '') != "200" || $result['data']['aktif'] != 'true') {
                $msg = $result['message'] ?? "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
                $response = [
                    'success' => false,
                    'code' => $result['data']['aktif'],
                    'message' => $result['data']['ketAktif'],
                    'data' => [
                        'noKartu' => $data['patient_bpjs'],
                        'nama' => $data['patient_name'] ?? null,
                        'sex' => $data['patient_gender'] ?? null,
                        'tglLahir' => $data['patient_datebirth'] ?? null,
                        'noHP' => $data['patient_phone'] ?? null,
                        'noKTP' => $data['patient_nik'] ?? null,
                        'rm' => $data['nomor_rm'] ?? null,
                        'kdProvider' => $result['data']['ppk']['kdPPK'],
                        'nmProvider' => $result['data']['ppk']['nmPPK'],
                    ]
                ];
            } else {
                $response = [
                    'success' => true,
                    'code' => $result['data']['aktif'],
                    'message' => $result['data']['ketAktif'],
                    'data' => [
                        'noKartu' => $data['patient_bpjs'],
                        'nama' => $data['patient_name'] ?? null,
                        'sex' => $data['patient_gender'] ?? null,
                        'tglLahir' => $data['patient_datebirth'] ?? null,
                        'noHP' => $data['patient_phone'] ?? null,
                        'noKTP' => $data['patient_nik'] ?? null,
                        'rm' => $data['nomor_rm'] ?? null,
                        'kdProvider' => $result['data']['kdProviderPst']['kdProvider'],
                        'nmProvider' => $result['data']['kdProviderPst']['nmProvider'],
                    ]
                ];
            }
        } else if ($kodeppk == '' || $kodeppk == null) {
            $response = [
                'success' => false,
                'message' => "Pasien belum memiliki fasilitas kesehatan terdaftar. Silakan lakukan pendaftaran terlebih dahulu."
            ];
        } else {
            $response = [
                'success' => false,
                'message' => "Pasien bukan berasal dari fasilitas kesehatan ini." . $result['data']['ppk']['nmPPK']
            ];
        }
    }
}

echo json_encode($response);
