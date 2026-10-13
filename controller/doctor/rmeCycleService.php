<?php

function rmeCycleTherapy(array $requests, array $details): string
{
    $items = [];
    $counts = [];
    foreach ($details as $detail) {
        $requestId = (int) $detail['id_permintaan_farmasi'];
        $name = trim((string) ($detail['pharmacy_name_generic'] ?? ''));
        $qty = $detail['qty'] ?? null;
        $signa = trim((string) ($detail['signa'] ?? ''));
        if ($signa === '' || $signa === '-') {
            $signa = trim((string) ($detail['rck_signa'] ?? ''));
        }
        if ($name === '' || !is_numeric($qty) || (float) $qty <= 0 || $signa === '') {
            throw new InvalidArgumentException("Rincian resep #$requestId belum lengkap. Periksa nama obat, jumlah, dan signa.");
        }
        $counts[$requestId] = ($counts[$requestId] ?? 0) + 1;
        $items[] = $name . '/' . $signa . '/' . $qty;
    }
    foreach ($requests as $request) {
        if ((int) $request['status_permintaan'] === 0 && empty($counts[(int) $request['id_permintaan_farmasi']])) {
            throw new InvalidArgumentException("Resep #" . $request['id_permintaan_farmasi'] . " belum memiliki rincian obat. Lengkapi atau hapus tiket kosong sebelum menyimpan pemeriksaan.");
        }
    }
    return implode(', ', $items);
}

function saveRmeCycle(mysqli $db, array $post, int $clinic, callable $saveVisit): array
{
    $visitId = $post['nomor_visit'] ?? '';
    if (!is_string($visitId) || $visitId === '') {
        throw new InvalidArgumentException('Nomor kunjungan wajib diisi.');
    }
    $db->begin_transaction();
    try {
        $stmt = $db->prepare("SELECT v.*, p.nomor_rm FROM pasien_visit v
            JOIN ms_patient p ON p.id_patient = v.id_patient
            WHERE v.visit_ID = ? AND v.id_customer = ? FOR UPDATE");
        $stmt->bind_param('si', $visitId, $clinic);
        $stmt->execute();
        $visit = $stmt->get_result()->fetch_assoc();
        if (!$visit || $visit['nomor_rm'] !== ($post['nomor_rm'] ?? null)) {
            throw new InvalidArgumentException('Kunjungan dan nomor RM tidak sesuai dengan klinik yang sedang login.');
        }
        if ((int) $visit['visit_status'] === 99) {
            throw new InvalidArgumentException('Kunjungan sudah dibatalkan.');
        }
        $stmt = $db->prepare("SELECT * FROM permintaan_pharmacy
            WHERE id_visit = ? AND id_customer = ? ORDER BY id_permintaan_farmasi FOR UPDATE");
        $stmt->bind_param('si', $visitId, $clinic);
        $stmt->execute();
        $requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt = $db->prepare("SELECT d.*, m.pharmacy_name_generic, r.rck_signa
            FROM permintaan_pharmacy_details d
            JOIN permintaan_pharmacy r ON r.id_permintaan_farmasi = d.id_permintaan_farmasi
            LEFT JOIN ms_pharmacy m ON m.id_pharmacy = d.id_pharmacy
            WHERE r.id_visit = ? AND r.id_customer = ?
            ORDER BY r.id_permintaan_farmasi, d.id_pharmacy_details FOR UPDATE");
        $stmt->bind_param('si', $visitId, $clinic);
        $stmt->execute();
        $therapy = rmeCycleTherapy($requests, $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
        $post['id_patient'] = $visit['id_patient'];
        $post['noKunjungan'] = $visit['noKunjung'] ?? '';
        $post['status_pasien'] = (int) $visit['id_provider'] === 1 ? 'BPJS' : 'UMUM';
        if ($therapy !== '') {
            $post['terapiObat'] = $therapy;
        }
        if ($post['status_pasien'] === 'BPJS') {
            $stmt = $db->prepare("SELECT 1 FROM pcare_pendaftaran WHERE nomor_visit = ? LIMIT 1");
            $stmt->bind_param('s', $visitId);
            $stmt->execute();
            if (!$stmt->get_result()->fetch_row()) {
                throw new InvalidArgumentException('Pendaftaran BPJS belum dibuat. Selesaikan pendaftaran sebelum menyimpan pemeriksaan.');
            }
        }
        $result = $saveVisit($post);
        if (($result['success'] ?? false) !== true) {
            $db->rollback();
            return $result;
        }
        if ($post['status_pasien'] === 'BPJS' && empty($result['noKunjung'])) {
            throw new RuntimeException('BPJS tidak mengembalikan nomor kunjungan. Resep belum dikirim; periksa status kunjungan sebelum mencoba kembali.');
        }
        $sent = 0;
        $stmt = $db->prepare("UPDATE permintaan_pharmacy SET status_permintaan = 1
            WHERE id_permintaan_farmasi = ? AND id_visit = ? AND id_customer = ? AND status_permintaan = 0");
        foreach ($requests as $request) {
            if ((int) $request['status_permintaan'] !== 0) continue;
            $id = (int) $request['id_permintaan_farmasi'];
            $stmt->bind_param('isi', $id, $visitId, $clinic);
            $stmt->execute();
            $sent += $stmt->affected_rows;
        }
        $db->commit();
        $result['prescriptions_sent'] = $sent;
        if ($sent > 0) $result['message'] .= ". $sent resep berhasil dikirim ke farmasi.";
        return $result;
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
}
