<?php
require_once __DIR__ . '/view.php';
header('Content-Type: application/json');
$tipe = $_GET['tipe'] ?? null;
$nomor_kartu = trim($_GET['nokartu'] ?? '');
$lengthkartu = strlen($nomor_kartu);
if (!in_array($lengthkartu, [16])) {
    $response = [
        'success' => false,
        'message' => '16 digit (NIK) atau 19 digit'
    ];
} elseif (!ctype_digit($nomor_kartu) && $lengthkartu != 19) {
    $response = [
        'success' => false,
        'message' => 'Nomor harus berupa angka'
    ];
} else {
    $stmt = $koneksi->prepare("SELECT * FROM ms_patient WHERE id_customer = ? AND patient_nik = ?");
    $stmt->bind_param("ss", $idcustomer, $nomor_kartu);
    if ($stmt->execute()) {
        $data = $stmt->get_result()->fetch_assoc();
        $response = [
            'success' => true,
            'code' => '200',
            'message' => 'OK',
            'data' => [
                'nama' => $data['patient_name'] ?? null,
                'sex' => $data['patient_gender'] ?? null,
                'tglLahir' => $data['patient_datebirth'] ?? null,
                'noHP' => $data['patient_phone'] ?? null,
                'noKTP' => $data['patient_nik'] ?? null,
                'rm' => $data['nomor_rm'] ?? null
            ]
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Pasien belum terdaftar disistem",
            'id' => $idcustomer
        ];
    }
}

echo json_encode($response);
