<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');

$tipe = $_GET['tipe'] ?? null;
$nomor_kartu = trim($_GET['nokartu'] ?? '');
$lengthkartu = strlen($nomor_kartu);
$kolom = (strlen($nomor_kartu) == 16) ? "patient_nik" : "patient_bpjs";
$stmt = $koneksi->prepare("SELECT * FROM ms_patient WHERE id_customer = ? AND $kolom = ?");
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
        if (($result['code'] ?? '') != "200" || $result['data']['aktif'] != 'true') {
            $response = [
                'success' => false,
                'code' => $result['data']['aktif'] ?? false,  
                'message' => $result['data']['ketAktif'] ?? "Layanan BPJS sedang tidak dapat diakses.",
                'data' => [
                    'noKartu' => $data['patient_bpjs'],
                    'nama' => $data['patient_name'] ?? null,
                    'sex' => $data['patient_gender'] ?? null,
                    'tglLahir' => $data['patient_datebirth'] ?? null,
                    'noHP' => $data['patient_phone'] ?? null,
                    'noKTP' => $data['patient_nik'] ?? null,
                    'rm' => $data['nomor_rm'] ?? null,
                ],
            ];
        } else {
            $response = [
                'success' => true,
                'code' => $result['data']['aktif'],
                'message' => $result['data']['ketAktif'],
                'data' => [
                    'noKartu' => $data['patient_bpjs'],
                    'id_patient' => $data['id_patient'],
                    'nama' => $data['patient_name'] ?? null,
                    'sex' => $data['patient_gender'] ?? null,
                    'tglLahir' => $data['patient_datebirth'] ?? null,
                    'noHP' => $data['patient_phone'] ?? null,
                    'noKTP' => $data['patient_nik'] ?? null,
                    'rm' => $data['nomor_rm'] ?? null,
                    'kdProvider' => $result['data']['kdProviderPst']['kdProvider'] ?? null,
                    'nmProvider' => $result['data']['kdProviderPst']['nmProvider'] ?? null,
                    'informasi' => [
                        'tunggakan' => $result['data']['tunggakan'],
                        'prb' => $result['data']['pstPrb'],
                        'prolanis' => $result['data']['pstProl'],
                    ]
                ]
            ];


            // Tambahkan informasi FKTP
            if ($result['data']['kdProviderPst']['kdProvider'] == '' || $result['data']['kdProviderPst']['kdProvider'] == null) {
                $response['warning'] = true;
                $response['message'] = "Pasien belum memiliki fasilitas kesehatan terdaftar.";
                $response['data']['kdProvider'] = $kodeppk;
            } elseif ($kodeppk != $result['data']['kdProviderPst']['kdProvider']) {
                $response['warning'] = true;
                $response['message'] = "Pasien terdaftar di fasilitas kesehatan lain: " .
                    $result['data']['kdProviderPst']['nmProvider'];
            } else {

                $response['warning'] = false;
            }
        }
    }
}

echo json_encode($response);