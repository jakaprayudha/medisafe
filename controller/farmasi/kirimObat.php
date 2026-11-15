<?php
require_once __DIR__ . '/../admisi/services/view.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../admisi/services/servicebpjs.php';
require_once __DIR__ . '/../doctor/rmeCycleService.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

// ================== INPUT ==================
$data_obat = json_decode(file_get_contents("php://input"), true);
$ids = $data_obat['ids'] ?? (isset($data_obat['id']) ? [$data_obat['id']] : []);
$ids = array_values(array_unique(array_filter(array_map('strval', (array)$ids), 'strlen')));
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$ids || !$id_customer) {
   echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
   exit;
}

function updateStatusTiket($koneksi, $ids, $id_customer)
{
   $ph = implode(',', array_fill(0, count($ids), '?'));
   $st = $koneksi->prepare("
      UPDATE permintaan_pharmacy 
      SET status_permintaan = 1 
      WHERE id_permintaan_farmasi IN ($ph) 
        AND id_customer = ? 
        AND status_permintaan = 0
   ");
   $vals = array_merge($ids, [$id_customer]);
   $st->bind_param(str_repeat('s', count($vals)), ...$vals);
   $ok = $st->execute();
   $err = $st->error;
   $st->close();
   return [$ok, $err];
}

$koneksi->begin_transaction();

try {
   // ================== GET & LOCK VISIT TICKETS ==================
   // FOR UPDATE mencegah race condition (klik ganda / request paralel)
   $placeholders = implode(',', array_fill(0, count($ids), '?'));
   $stmt = $koneksi->prepare("
      SELECT pp.id_permintaan_farmasi, pp.id_visit, pp.status_permintaan, pp.rck_signa,
         (SELECT COUNT(*) FROM permintaan_pharmacy_details AS pd
            INNER JOIN ms_pharmacy AS mp ON mp.id_pharmacy = pd.id_pharmacy
            WHERE pd.id_permintaan_farmasi = pp.id_permintaan_farmasi) AS jumlah_item
      FROM permintaan_pharmacy AS pp 
      WHERE pp.id_permintaan_farmasi IN ($placeholders) 
        AND pp.id_customer = ?
      FOR UPDATE
   ");
   $bindVals = array_merge($ids, [$id_customer]);
   $stmt->bind_param(str_repeat('s', count($bindVals)), ...$bindVals);
   $stmt->execute();
   $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();

   if (count($rows) !== count($ids)) {
      throw new RuntimeException('Data permintaan tidak ditemukan');
   }

   $visits = array_unique(array_column($rows, 'id_visit'));
   if (count($visits) !== 1) {
      throw new RuntimeException('Tiket yang dikirim harus berasal dari kunjungan yang sama');
   }

   $nomor_visit = reset($visits);

   foreach ($rows as $row) {
      if ((int)$row['status_permintaan'] !== 0) {
         throw new RuntimeException('Ada tiket yang sudah dikirim/diproses. Silakan refresh halaman.');
      }
      if ((int)$row['jumlah_item'] === 0) {
         throw new RuntimeException('Ada tiket yang belum memiliki obat. Lengkapi rincian obat terlebih dahulu.');
      }
   }

   // ================== CEK PROVIDER ==================
   $stmtProv = $koneksi->prepare("
      SELECT id_provider, visit_status 
      FROM pasien_visit 
      WHERE visit_ID = ? AND id_customer = ?
   ");
   $stmtProv->bind_param("ss", $nomor_visit, $id_customer);
   $stmtProv->execute();
   $prov = $stmtProv->get_result()->fetch_assoc();
   $stmtProv->close();

   if (!$prov) {
      throw new RuntimeException('Data kunjungan tidak ditemukan');
   }
   if ((int)$prov['visit_status'] === 99) {
      throw new RuntimeException('Kunjungan sudah dibatalkan');
   }

   // ================== JIKA BUKAN BPJS ==================
   if ((int)$prov['id_provider'] !== 1) {
      [$okUpdate, $errUpdate] = updateStatusTiket($koneksi, $ids, $id_customer);
      if (!$okUpdate) {
         throw new RuntimeException($errUpdate ?: 'Gagal memperbarui status tiket');
      }

      $koneksi->commit();
      echo json_encode([
         'status' => 'success',
         'message' => 'Berhasil (pasien umum, tidak kirim BPJS)',
         'total' => count($ids)
      ]);
      exit;
   }

   // ================== GABUNGKAN TIKET & DETAIL OBAT ==================
   // Ambil seluruh header tiket yang terlibat (tiket baru + tiket sudah terkirim)
   // agar rmeCycleTherapy() memiliki referensi header yang lengkap.
   $stmtTickets = $koneksi->prepare("
      SELECT id_permintaan_farmasi, id_visit, status_permintaan, rck_signa
      FROM permintaan_pharmacy
      WHERE id_customer = ? AND id_visit = ?
        AND (id_permintaan_farmasi IN ($placeholders) OR status_permintaan >= 1)
      ORDER BY id_permintaan_farmasi ASC
   ");
   $valsTickets = array_merge([$id_customer, $nomor_visit], $ids);
   $stmtTickets->bind_param(str_repeat('s', count($valsTickets)), ...$valsTickets);
   $stmtTickets->execute();
   $allTickets = $stmtTickets->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmtTickets->close();

   $stmt2 = $koneksi->prepare("
      SELECT pd.*, p.pharmacy_name_generic, pp.rck_signa 
      FROM permintaan_pharmacy_details AS pd 
      INNER JOIN permintaan_pharmacy AS pp ON pp.id_permintaan_farmasi = pd.id_permintaan_farmasi 
      LEFT JOIN ms_pharmacy AS p ON p.id_pharmacy = pd.id_pharmacy 
      WHERE pp.id_customer = ? AND pp.id_visit = ? 
        AND (pp.id_permintaan_farmasi IN ($placeholders) OR pp.status_permintaan >= 1) 
      ORDER BY pp.id_permintaan_farmasi ASC, pd.id_pharmacy_details ASC
   ");
   $stmt2->bind_param(str_repeat('s', count($valsTickets)), ...$valsTickets);
   $stmt2->execute();
   $data_detailobat = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt2->close();

   $terapiObat = trim((string)rmeCycleTherapy($allTickets, $data_detailobat));
   if ($terapiObat === '') {
      throw new RuntimeException('Data terapi obat kosong setelah diproses. Periksa kelengkapan item obat.');
   }

   // Gunakan INNER JOIN id_patient agar tidak gagal jika format noKartu berbeda
   $stmt1 = $koneksi->prepare("
      SELECT pv.visit_ID, pv.id_patient, pk.* 
      FROM pasien_visit AS pv 
      INNER JOIN pcare_kunjungan AS pk ON pv.noKunjung = pk.noKunjungan 
      INNER JOIN ms_patient AS p ON p.id_patient = pv.id_patient 
      WHERE pv.visit_ID = ? AND pv.id_customer = ?
   ");
   $stmt1->bind_param('ss', $nomor_visit, $id_customer);
   $stmt1->execute();
   $data = $stmt1->get_result()->fetch_assoc();
   $stmt1->close();

   $noKunjungan = $data['noKunjungan'] ?? null;
   if (!$noKunjungan) {
      throw new RuntimeException('Kunjungan BPJS (PCare) belum tersedia untuk pasien ini. Simpan pemeriksaan terlebih dahulu.');
   }

   $rujukLanjut = null;
   if (!empty($data['rujukLanjut'])) {
      $decodedRujuk = is_string($data['rujukLanjut']) ? json_decode($data['rujukLanjut'], true) : $data['rujukLanjut'];
      $rujukLanjut = is_array($decodedRujuk) ? $decodedRujuk : null;
   }

   $payload = [
      "noKunjungan" => $noKunjungan,
      "noKartu" => $data['noKartu'] ?? null,
      "tglDaftar" => !empty($data['tglDaftar']) ? date("d-m-Y", strtotime($data['tglDaftar'])) : null,
      "kdPoli" => $data['kdPoli'] ?? null,
      "keluhan" => $data['keluhan'] ?? null,
      "kdSadar" => $data['kdSadar'] ?? null,
      "sistole" => $data['sistole'] ?? null,
      "diastole" => $data['diastole'] ?? null,
      "beratBadan" => isset($data['beratBadan']) ? (float)$data['beratBadan'] : 0,
      "tinggiBadan" => isset($data['tinggiBadan']) ? (float)$data['tinggiBadan'] : 0,
      "respRate" => $data['respRate'] ?? null,
      "heartRate" => $data['heartRate'] ?? null,
      "lingkarPerut" => $data['lingkarPerut'] ?? null,
      "kdStatusPulang" => $data['kdStatusPulang'] ?? null,
      "tglPulang" => !empty($data['tglPulang']) ? date("d-m-Y", strtotime($data['tglPulang'])) : null,
      "kdDokter" => $data['kdDokter'] ?? null,
      "kdDiag1" => $data['kdDiag1'] ?? null,
      "kdDiag2" => $data['kdDiag2'] ?? null,
      "kdDiag3" => $data['kdDiag3'] ?? null,
      "kdPoliRujukInternal" => $data['kdPoliRujukInternal'] ?? null,
      "rujukLanjut" => $rujukLanjut,
      "kdTacc" => (string)($data['kdTacc'] ?? ''),
      "alasanTacc" => $data['alasanTacc'] ?? null,
      "anamnesa" => $data['anamnesa'] ?? null,
      "alergiMakan" => $data['alergiMakan'] ?? null,
      "alergiUdara" => $data['alergiUdara'] ?? null,
      "alergiObat" => $data['alergiObat'] ?? null,
      "kdPrognosa" => $data['kdPrognosa'] ?? null,
      "terapiObat" => $terapiObat,
      "terapiNonObat" => $data['terapiNonObat'] ?? null,
      "bmhp" => $data['bmhp'] ?? null,
      "suhu" => $data['suhu'] ?? null
   ];

   $resultBpjs = bpjsPost("/kunjungan/v1", $payload, 'PUT');

   // ================== VALIDASI KETAT RESPONSE BPJS ==================
   $bpjsCode = null;
   $bpjsMsg = null;
   if (is_array($resultBpjs)) {
      $bpjsCode = $resultBpjs['code'] ?? ($resultBpjs['metaData']['code'] ?? null);
      $bpjsMsg = $resultBpjs['message'] ?? ($resultBpjs['metaData']['message'] ?? null);
   }

   $isSuccessBpjs = is_scalar($bpjsCode) && in_array((string)$bpjsCode, ['200', '201'], true);

   if (!$isSuccessBpjs) {
      $koneksi->rollback();
      echo json_encode([
         'status' => 'error',
         'message' => $bpjsMsg ?: 'Layanan BPJS sedang tidak dapat diakses atau menolak data. Status farmasi tidak diubah.',
         'result' => $resultBpjs
      ]);
      exit;
   }

   // Update status tiket & pcare_kunjungan dalam 1 transaksi atomik
   [$okUpdate, $errUpdate] = updateStatusTiket($koneksi, $ids, $id_customer);
   if (!$okUpdate) {
      throw new RuntimeException($errUpdate ?: 'Gagal update status permintaan farmasi');
   }

   $stmt4 = $koneksi->prepare("UPDATE pcare_kunjungan SET terapiObat = ? WHERE noKunjungan = ?");
   $stmt4->bind_param('ss', $terapiObat, $noKunjungan);
   $okKunjungan = $stmt4->execute();
   $errKunjungan = $stmt4->error;
   $stmt4->close();

   if (!$okKunjungan) {
      throw new RuntimeException($errKunjungan ?: 'Gagal memperbarui data kunjungan lokal');
   }

   $koneksi->commit();
   echo json_encode([
      'status' => 'success',
      'message' => 'Berhasil kirim ke farmasi dan PCare BPJS',
      'total' => count($ids)
   ]);

} catch (Throwable $e) {
   $koneksi->rollback();
   echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
   ]);
}