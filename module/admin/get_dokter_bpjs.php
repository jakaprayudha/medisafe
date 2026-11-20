<?php
/**
 * Daftar dokter/tenaga (master_doctor_bpjs) faskes session untuk modal "Ganti dokter".
 * kdPoli di master_doctor_bpjs umumnya kosong, jadi poli tiap dokter diambil dari kunjungan 30 hari terakhir,
 * plus jumlah pasien yang masih menunggu hari ini (membantu memilih dokter dengan antrean lebih sedikit).
 */
include "../../database/connect.php";
header("Content-Type: application/json; charset=utf-8");

$idc = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();

$q = function (string $sql, string $t, array $p) use ($koneksi): array {
    $st = $koneksi->prepare($sql);
    $st->bind_param($t, ...$p);
    $st->execute();
    $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    return $r;
};
$norm = fn($s) => preg_replace('/[^a-z0-9]/', '', strtolower((string) $s));

$dokter = $q("SELECT d.kdDokter, d.nmDokter, d.kdPoli,
      (SELECT mp.nmPoli FROM master_poli mp WHERE mp.kdPoli = d.kdPoli AND d.kdPoli <> '' LIMIT 1) AS nmPoli
   FROM master_doctor_bpjs d WHERE d.id_customer = ? AND d.status = '1' ORDER BY d.nmDokter ASC", 'i', [$idc]);

$hariIni = date('Y-m-d');
$dari = date('Y-m-d', strtotime('-30 days'));
$poli = [];
foreach ($q("SELECT id_doctor, id_poli, COUNT(*) n FROM pasien_visit WHERE id_customer = ? AND visit_date BETWEEN ? AND ? AND visit_status <> 99
      GROUP BY id_doctor, id_poli ORDER BY n DESC", 'iss', [$idc, $dari, $hariIni]) as $r) {
    if (trim((string) $r['id_poli']) !== '') $poli[$norm($r['id_doctor'])][] = $r['id_poli'];
}
$tunggu = [];
foreach ($q("SELECT id_doctor, SUM(visit_status NOT IN (3,4,99,10)) menunggu FROM pasien_visit WHERE id_customer = ? AND visit_date = ?
      GROUP BY id_doctor", 'is', [$idc, $hariIni]) as $r) {
    $tunggu[$norm($r['id_doctor'])] = (int) $r['menunggu'];
}

foreach ($dokter as &$d) {
    $k = $norm($d['nmDokter']);
    $d['poli_list'] = array_values(array_unique($poli[$k] ?? []));
    if (empty($d['nmPoli']) && $d['poli_list']) $d['nmPoli'] = $d['poli_list'][0];
    $d['menunggu'] = $tunggu[$k] ?? 0;
}
unset($d);

echo json_encode(["success" => true, "data" => $dokter], JSON_UNESCAPED_UNICODE);
