<?php
require_once __DIR__ . '/view.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/servicebpjs.php';

header('Content-Type: application/json');
$kdTkp        = $_GET['kd'] ?? '';
$jkRaw = $_GET['jnskelamin'] ?? '';
$jenisKelamin = ($jkRaw === 'Laki-laki') ? 'L' : 'P';
$result = bpjsGet('/tindakan/kdTkp/' . $kdTkp . '/0/100');
$data = $result['data']['list'] ?? [];
$filtered = [];
foreach ($data as $row) {
    if ($jenisKelamin === 'L') {
        if (preg_match('/persalinan|vaginam|melahirkan/i', $row['nmTindakan'])) {
            continue;
        }
    }
    $filtered[] = $row;
}
echo json_encode([
    'success' => true,
    'data' => $filtered
], JSON_PRETTY_PRINT);
