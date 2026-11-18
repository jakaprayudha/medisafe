<?php
require_once __DIR__ . '/../admisi/services/view.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../admisi/services/servicebpjs.php';
require_once __DIR__ . '/../doctor/rmeCycleService.php';
require_once __DIR__ . '/pcareTerapiService.php';

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
   $stmtProv = $koneksi->prepare("SELECT id_provider, status_rawatinap, visit_status FROM pasien_visit  WHERE visit_ID = ? AND id_customer = ? ");
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

   if ((int)$prov['id_provider'] !== 1 || $prov['status_rawatinap'] == '1') {
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

   // ================== KIRIM TERAPI KE PCARE ==================
   // Gabungkan tiket baru + tiket yang sudah terkirim, lalu PUT /kunjungan/v1
   // (lihat pcareTerapiService.php, juga dipakai sinkronObatPcare.php).
   $kirim = pcareKirimTerapiVisit($koneksi, (string)$id_customer, (string)$nomor_visit, $ids);

   if (!$kirim['ok']) {
      $koneksi->rollback();
      echo json_encode([
         'status' => 'error',
         'message' => $kirim['message'] . ' Status farmasi tidak diubah.',
         'result' => $kirim['result']
      ]);
      exit;
   }

   // Update status tiket (pcare_kunjungan sudah diperbarui oleh service) dalam 1 transaksi
   [$okUpdate, $errUpdate] = updateStatusTiket($koneksi, $ids, $id_customer);
   if (!$okUpdate) {
      throw new RuntimeException($errUpdate ?: 'Gagal update status permintaan farmasi');
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
