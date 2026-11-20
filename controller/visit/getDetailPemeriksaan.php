<?php
/**
 * Detail hasil pemeriksaan satu kunjungan (modal "Hasil pemeriksaan" di registrasi poliklinik / UGD / ranap,
 * dan isian awal modal vital sign).  GET ?id={pasien_visit.id_visit}
 *
 * Kunci output lama dipertahankan (status, data.*, tindakan, obat, tindakan_text, obat_text, tindakan_obat_text).
 *
 * Perbaikan:
 * - Hanya kunjungan faskes di session (visitGuard).
 * - Nama obat: item_name selalu kosong di data (100% dari 5rb detail terakhir), dan kode lama mencari nama di kolom
 *   yang tidak ada (nama_obat, nama_barang, ...) -> semua obat tampil "Obat ID 123". Sekarang pharmacy_name_trade /
 *   pharmacy_name_generic.
 * - Query obat tidak lagi mengambil ms_pharmacy.* (44 kolom per baris).
 * - Tambahan untuk modal: identitas pasien, penjamin, diagnosa sekunder, pemeriksaan fisik, rencana tindak lanjut,
 *   daftar obat & tindakan terstruktur.
 */
include '../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');

$id = $_GET['id'] ?? '';
if ($id === '' || $id === null) {
    echo json_encode(['status' => false, 'message' => 'ID visit tidak ditemukan.']);
    exit;
}

// hanya kunjungan faskes di session (dulu: data medis kunjungan faskes mana pun bisa dibaca)
require_once __DIR__ . '/../../utility/visitGuard.php';
visitGuard($koneksi, 'id_visit', $id, true);
session_write_close();

function dpQ(mysqli $db, string $sql, string $t, array $p): array
{
    $st = $db->prepare($sql);
    if (!$st) throw new RuntimeException($db->error);
    $st->bind_param($t, ...$p);
    if (!$st->execute()) throw new RuntimeException($st->error);
    $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    return $r;
}

try {
    $data = dpQ($koneksi, "
        SELECT pv.id_visit, pv.visit_ID, pv.id_patient, pv.id_doctor, pv.visit_date, pv.visit_time, pv.id_poli, pv.visit_status,
            pv.patient_name_pcare, pv.anamnesa, pv.keluhan_penyerta, pv.catatan_screening, pv.pemeriksaan_fisik, pv.rencana_tindak_lanjut,
            pv.alergi_obat, pv.diagnosa, pv.diagnosa_sekunder,
            icd.code AS diagnosa_code, icd.icd10 AS diagnosa_keterangan, icd.icd10_ind AS diagnosa_keterangan_ind,
            pv.kdDiag1, pv.kdDiag2, pv.kdDiag3, pv.nmDiag1, pv.nmDiag2, pv.nmDiag3,
            pv.tindakan, pv.tekanan_darah, pv.sistole, pv.diastole, pv.suhu, pv.nadi, pv.respirasi, pv.tinggi_badan, pv.berat_badan,
            pv.bmi, pv.bmi_keterangan, pv.saturasi, pv.lingkar_perut, pv.kondisi_masuk,
            mp.patient_datebirth, mp.patient_name, mp.nomor_rm, mp.patient_gender, mp.patient_bpjs,
            pr.provider_name
        FROM pasien_visit AS pv
        LEFT JOIN ms_patient AS mp ON mp.id_patient = pv.id_patient
        LEFT JOIN ms_provider AS pr ON pr.id_provider = pv.id_provider
        LEFT JOIN icd_10 AS icd ON icd.code = pv.diagnosa
        WHERE pv.id_visit = ?
        LIMIT 1", 'i', [(int) $id])[0] ?? null;

    if (!$data) {
        echo json_encode(['status' => false, 'message' => 'Data pemeriksaan tidak ditemukan.']);
        exit;
    }
    $visitID = (string) ($data['visit_ID'] ?? '');

    // tindakan / billing (pasien_billing.id_visit = visit_ID)
    $tindakan = [];
    if ($visitID !== '') {
        foreach (dpQ($koneksi, "SELECT id_billing, id_visit, billing_item, billing_category, billing_qty
            FROM pasien_billing WHERE id_visit = ? ORDER BY id_billing", 's', [$visitID]) as $row) {
            if (trim((string) $row['billing_item']) !== '') $tindakan[] = $row;
        }
    }

    // obat (permintaan_pharmacy.id_visit = visit_ID)
    $obat = [];
    if ($visitID !== '') {
        $rows = dpQ($koneksi, "
            SELECT pp.id_permintaan_farmasi, pp.id_visit, pp.created_at, pp.status_permintaan, pp.permintaan_number,
                pp.status_obat_pulang, pp.tipe_obat, pp.rck_jumlah, pp.rck_satuan, pp.rck_signa,
                ppd.id_pharmacy_details, ppd.id_pharmacy, ppd.item_name, ppd.signa, ppd.qty, ppd.qty_racik, ppd.catatan,
                ppd.harga, ppd.status_item, ppd.created_at AS detail_created_at, ppd.created_user,
                ph.pharmacy_name_trade, ph.pharmacy_name_generic, ph.pharmacy_unit
            FROM permintaan_pharmacy AS pp
            -- LEFT JOIN sengaja: tanpa index di ppd.id_permintaan_farmasi, INNER JOIN membuat MySQL memindai 600rb+
            -- baris detail lalu lookup per baris (lebih lambat); LEFT JOIN memakai hash join
            LEFT JOIN permintaan_pharmacy_details AS ppd ON ppd.id_permintaan_farmasi = pp.id_permintaan_farmasi
            LEFT JOIN ms_pharmacy AS ph ON ph.id_pharmacy = ppd.id_pharmacy
            WHERE pp.id_visit = ?
            ORDER BY pp.id_permintaan_farmasi, ppd.id_pharmacy_details", 's', [$visitID]);
        foreach ($rows as $row) {
            if (empty($row['id_pharmacy_details'])) continue;
            // nama dagang sering diisi "-" -> pakai nama generik
            $nama = '';
            foreach (['item_name', 'pharmacy_name_trade', 'pharmacy_name_generic'] as $k) {
                $v = trim((string) ($row[$k] ?? ''));
                if ($v !== '' && trim($v, '- ') !== '') {
                    $nama = $v;
                    break;
                }
            }
            $row['nama_obat'] = $nama;
            $obat[] = $row;
        }
    }

    $data['tindakan'] = $tindakan;
    $data['obat'] = $obat;

    $tindakanText = array_map(fn($t) => trim($t['billing_item']), $tindakan);
    $obatText = [];
    foreach ($obat as $o) {
        $t = $o['nama_obat'] !== '' ? $o['nama_obat'] : 'Obat ID ' . ($o['id_pharmacy'] ?? '-');
        if (!empty($o['signa'])) $t .= ' (' . trim($o['signa']) . ')';
        if (isset($o['qty']) && $o['qty'] !== '' && $o['qty'] !== null) $t .= ' x ' . $o['qty'];
        $obatText[] = $t;
    }
    $data['tindakan_text'] = $tindakanText ? implode(', ', $tindakanText) : '-';
    $data['obat_text'] = $obatText ? implode(', ', $obatText) : '-';
    $gab = [];
    if ($tindakanText) $gab[] = 'Tindakan: ' . implode(', ', $tindakanText);
    if ($obatText) $gab[] = 'Obat: ' . implode(', ', $obatText);
    $data['tindakan_obat_text'] = $gab ? implode('; ', $gab) : '-';

    echo json_encode(['status' => true, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    error_log('[getDetailPemeriksaan] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Gagal mengambil data pemeriksaan.']);
}
