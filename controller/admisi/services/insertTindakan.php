<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
require_once __DIR__ . '/../../../utility/activityLog.php';
header('Content-Type: application/json');
$kdTindakanSK = $_POST['kdTindakanSK'] ?? '0';
$noKunjungan = $_POST['noKunjungan'];
$kdTindakan = $_POST['kdTindakan'];
$biaya = preg_replace('/[^0-9]/', '', $_POST['biaya']);
$keterangan = $_POST['keterangan'];
$hasil = $_POST['hasil'] ?? '0';
$nmtindakan = $_POST['nmTindakan'];
$payload = [
    "kdTindakanSK" => $kdTindakanSK,
    "noKunjungan" => $noKunjungan,
    "kdTindakan" => $kdTindakan,
    "biaya" => $biaya,
    "keterangan" => $keterangan,
    "hasil" => $hasil
];

$statusEdit = false;
$method = "POST";
if ($kdTindakanSK > 0) {
    $statusEdit = true;
    $method = "PUT";
}
// echo json_encode($payload, JSON_PRETTY_PRINT);die();
$lgLama = null;
if ($kdTindakanSK > 0) {
    $lgSt = $koneksi->prepare("SELECT * FROM pcare_tindakan WHERE kdTindakanSK = ? AND noKunjungan = ? LIMIT 1");
    if ($lgSt) {
        $lgSt->bind_param('ss', $kdTindakanSK, $noKunjungan);
        $lgSt->execute();
        $lgLama = $lgSt->get_result()->fetch_assoc() ?: null;
        $lgSt->close();
    }
}
$result = bpjsPost('/tindakan', $payload, $method);
// echo json_encode($result, JSON_PRETTY_PRINT);die();
if ($result['code'] != '200') {
    $msg = $result['metadata'];
    if ($msg == null) {
        $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    }
    if($result['code'] == "428"){
        $msg = "Tindakan yang sama tidak diperbolehkan pada kunjungan yang sama";
    }
    if($result['code'] == "412"){
        $msg = $result['message'];
    }
    $response = [
        'success' => false,
        'message' => $msg,
    ];
} else {
    if ($statusEdit) {
        $message = "Berhasil Update Tindakan";
        $kdTindakanSKBaru = $result['data']['message'];
        $stmt = $koneksi->prepare("UPDATE pcare_tindakan SET kdTindakanSK = ? ,kdTindakan = ?, nmTindakan = ?,biaya = ?,keterangan = ?, hasil = ? WHERE noKunjungan = ? AND kdTindakanSK = ?");
        $stmt->bind_param("ssssssss", $kdTindakanSKBaru, $kdTindakan, $nmtindakan, $biaya, $keterangan, $hasil, $noKunjungan, $kdTindakanSK);
        $hasil = $stmt->execute();
        $stmt->close();
    } else {
        $message = "Berhasil Membuat Tindakan";
        $kdTindakanSKBaru = $result['data']['message'];
        $stmt = $koneksi->prepare("INSERT INTO pcare_tindakan(kdTindakanSK, noKunjungan, kdTindakan, nmTindakan, biaya, keterangan, hasil)VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $kdTindakanSKBaru, $noKunjungan, $kdTindakan, $nmtindakan, $biaya, $keterangan, $hasil);
        $hasil = $stmt->execute();
        $stmt->close();
    }
    if ($hasil) {
        $response = [
            'success'  => true,
            'message'  => $message,
            'kode' => $kdTindakanSKBaru,
            'result' => $result
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal",
        ];
    }
}
// log aktivitas PCare (berhasil maupun ditolak BPJS)
$lgBaru = ['kdTindakanSK' => $kdTindakanSKBaru ?? $kdTindakanSK, 'noKunjungan' => $noKunjungan, 'kdTindakan' => $kdTindakan, 'nmTindakan' => $nmtindakan, 'biaya' => $biaya, 'keterangan' => $keterangan, 'hasil' => $_POST['hasil'] ?? '0'];
if ($lgLama) [$lgLama, $lgBaru] = logBeda(array_intersect_key($lgLama, $lgBaru), $lgBaru);
logAktivitas($koneksi, 'diagnosa', 'kirim_pcare', ['tipe' => 'pcare_tindakan', 'id' => $kdTindakanSKBaru ?? $kdTindakanSK, 'label' => 'Tindakan PCare · ' . $nmtindakan . ' · kunjungan ' . $noKunjungan, 'visit_ID' => (logAmbilBaris($koneksi, 'pcare_kunjungan', 'noKunjungan', $noKunjungan) ?? [])['nomor_visit'] ?? null],
    (!empty($response['success']) ? ($statusEdit ? 'Tindakan diubah di PCare' : 'Tindakan dikirim ke PCare') : ($statusEdit ? 'Ubah' : 'Kirim') . ' tindakan PCare GAGAL: ' . mb_substr((is_string($response['message'] ?? null) ? $response['message'] : json_encode($response['message'] ?? '')), 0, 300)), $lgLama ?: null, $lgBaru);
echo json_encode($response);
