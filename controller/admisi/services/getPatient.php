<?php
require_once __DIR__ . '/view.php';
header('Content-Type: application/json');

$keyword = $_GET['nama'] ?? '';
$keyword = trim($keyword);

$stmt = $koneksi->prepare("
    SELECT 
        id_patient AS id,
        patient_name AS nama,
        nomor_rm AS no_rm,
        patient_nik AS nik,
        patient_datebirth AS tgl_lahir,
        patient_bpjs AS no_bpjs
    FROM ms_patient
    WHERE id_customer = ?
    AND (
        patient_name LIKE CONCAT('%', ?, '%')
        OR patient_nik LIKE CONCAT('%', ?, '%')
        OR patient_bpjs LIKE CONCAT('%', ?, '%')
    )
    ORDER BY patient_name ASC
    LIMIT 5
");

$stmt->bind_param("ssss", $idcustomer, $keyword, $keyword, $keyword);
$stmt->execute();

$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['tgl_lahir'])) {
        $row['tgl_lahir'] = date('d-m-Y', strtotime($row['tgl_lahir']));
    }
    $data[] = $row;
}

$result->free();
$stmt->close();

echo json_encode($data);