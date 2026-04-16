<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';
header('Content-Type: application/json');
$visit = $_POST['novisit'];
$stmt = $koneksi->prepare("SELECT * FROM pcare_pendaftaran WHERE nomor_visit = ?");
$stmt->bind_param("s", $visit);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$tglDB = $result['tanggal_daftar'];
$nomor_kartu = $result['noKartu'];
$noUrut = $result['noUrut'];
$kdpoli = $result['kdPoli'];
$tanggal = date("d-m-Y", strtotime($tglDB));
$result = bpjsDelete('/pendaftaran/peserta/' . $nomor_kartu . '/tglDaftar/' . $tanggal . '/noUrut/' . $noUrut . '/kdPoli/' . $kdpoli);
if ($result['code'] != "200") {
    $msg = $result['message'];
    if ($msg == null) {
        $msg = "Layanan BPJS sedang tidak dapat diakses. Mohon dicoba beberapa saat lagi.";
    }
    $response = [
        'success' => false,
        'message' => $msg,
    ];
} else {
    $stmt = $koneksi->prepare("DELETE FROM pcare_pendaftaran WHERE nomor_visit = ?");
    $stmt->bind_param("s", $visit);
    $hasil = $stmt->execute();

    $stmt1 = $koneksi->prepare("DELETE FROM pasien_visit WHERE visit_ID = ?");
    $stmt1->bind_param("s", $visit);
    $hasil1 = $stmt1->execute();

    $stmt->close();
    $stmt1->close();
    if ($hasil AND $hasil1) {
        $response = [
            'success' => true,
            'message' => "Berhasil Hapus Pendaftaran",
        ];
    } else {
        $response = [
            'success' => false,
            'message' => "Gagal Hapus Pendaftaran" . mysqli_error($koneksi),
        ];
    }
}
echo json_encode($response);
