<?php
require_once __DIR__ . '/view.php';
header('Content-Type: application/json');
$tipe = $_GET['tipe'] ?? null;
$nomor_kartu = trim($_GET['nokartu'] ?? '');
$lengthkartu = strlen($nomor_kartu);
if (!in_array($lengthkartu, [16, 19])) {
    $response = [
        'success' => false,
        'message' => 'Nomor harus 16 digit (NIK) atau 19 digit (BPJS)'
    ];
    echo json_encode($response);
    exit;
}
if (!ctype_digit($nomor_kartu)) {
    $response = [
        'success' => false,
        'message' => 'Nomor harus berupa angka'
    ];
    echo json_encode($response);
    exit;
}
if ($lengthkartu == 16) {
    $query = "SELECT * FROM ms_patient WHERE id_customer = ? AND patient_nik = ?";
} else {
    $query = "SELECT * FROM ms_patient WHERE id_customer = ? AND patient_bpjs = ?";
}
$stmt = $koneksi->prepare($query);
$stmt->bind_param("ss", $idcustomer, $nomor_kartu);
if ($stmt->execute()) {
    $data = $stmt->get_result()->fetch_assoc();

    // get last visit
    $stmt = $koneksi->prepare("SELECT setting_clinic.clinic_name FROM pasien_visit JOIN setting_clinic ON setting_clinic.id_customer = pasien_visit.id_customer WHERE pasien_visit.id_patient = ? ORDER BY pasien_visit.created_at DESC LIMIT 1");
    $stmt->bind_param("s", $data['id_patient']);
    if ($stmt->execute()) {
        $clinic = $stmt->get_result()->fetch_assoc();
    }
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
            'rm' => $data['nomor_rm'] ?? null,
            'kdProviderPst' => [
                'nmProvider' => $clinic['clinic_name'] ?? null,
            ],
        ]
    ];
} else {
    $response = [
        'success' => false,
        'message' => "Pasien belum terdaftar disistem",
        'id' => $idcustomer
    ];
}


echo json_encode($response);
