<?php
/**
 * Monitoring pengiriman Dinkes (module/admin/monitoring-dinkes).
 *   GET  [?status=&jenis=]  -> { ringkas:{status:n}, data:[...] } maks 300 terbaru
 *   POST aksi=kirim  visit=<visit_ID>  -> kirim ulang (tambah/update)
 *   POST aksi=hapus  visit=<visit_ID>  -> ulangi hapus di Dinkes (status hapus_gagal)
 *   POST aksi=cek    visit=<visit_ID>  -> cek ke API Dinkes apakah sudah ada (ada|tidak_ada|error)
 *   POST aksi=kirim_lama dari=Y-m-d sampai=Y-m-d -> kirim 1 batch kunjungan lama yang belum pernah diproses; ulangi sampai hasil.sisa=0
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
require_once __DIR__ . '/../dinkes/dinkesService.php';
header('Content-Type: application/json; charset=utf-8');

$idc = (int) ($_SESSION['id_customer'] ?? 0);
if ($idc <= 0) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);
   exit;
}
requireRole(['admin', 'superadmin', 'administrator']);

function dmBalas(array $d, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}

try {
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $visit = trim((string) ($_POST['visit'] ?? ''));
      $aksi = (string) ($_POST['aksi'] ?? '');
      if ($aksi === 'kirim_lama') {
         $dari = (string) ($_POST['dari'] ?? '');
         $sampai = (string) ($_POST['sampai'] ?? '');
         foreach ([$dari, $sampai] as $t) {
            $d = DateTime::createFromFormat('Y-m-d', $t);
            if (!$d || $d->format('Y-m-d') !== $t) dmBalas(['status' => 'error', 'message' => 'Rentang tanggal tidak valid.'], 422);
         }
         dmBalas(['status' => 'success', 'hasil' => dkKirimLama($koneksi, $idc, $dari, $sampai)]);
      }
      if ($visit === '' || !in_array($aksi, ['kirim', 'hapus', 'cek'], true)) dmBalas(['status' => 'error', 'message' => 'Permintaan tidak valid.'], 422);
      $hasil = match ($aksi) {
         'kirim' => dkKirimVisit($koneksi, $idc, $visit),
         'hapus' => dkHapusVisit($koneksi, $idc, $visit),
         'cek' => dkCekRemote($koneksi, $idc, $visit),
      };
      dmBalas(['status' => 'success', 'hasil' => $hasil]);
   }

   $status = (string) ($_GET['status'] ?? '');
   $jenis = (string) ($_GET['jenis'] ?? '');
   $sql = "SELECT d.visit_ID, d.jenis, d.dk_status, d.dk_id, d.dk_error, d.dk_attempts, d.dk_last_try, d.dk_sent_at,
         pv.visit_date, mp.patient_name, mp.patient_nik
      FROM dinkes_kirim d
      LEFT JOIN pasien_visit pv ON pv.visit_ID = d.visit_ID AND pv.id_customer = d.id_customer
      LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
      WHERE d.id_customer = ?";
   $tipe = 'i';
   $arg = [$idc];
   if (in_array($status, ['belum', 'terkirim', 'gagal', 'dilewati', 'dihapus', 'hapus_gagal'], true)) {
      $sql .= " AND d.dk_status = ?";
      $tipe .= 's';
      $arg[] = $status;
   }
   if (in_array($jenis, ['dewasa', 'balita'], true)) {
      $sql .= " AND d.jenis = ?";
      $tipe .= 's';
      $arg[] = $jenis;
   }
   $sql .= " ORDER BY COALESCE(d.dk_last_try, d.created_at) DESC LIMIT 300";
   $st = $koneksi->prepare($sql);
   $st->bind_param($tipe, ...$arg);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();

   $ringkas = [];
   $st = $koneksi->prepare("SELECT dk_status, COUNT(*) FROM dinkes_kirim WHERE id_customer=? GROUP BY dk_status");
   $st->bind_param('i', $idc);
   $st->execute();
   foreach ($st->get_result()->fetch_all() as [$s, $n]) $ringkas[$s] = (int) $n;
   $st->close();
   dmBalas(['status' => 'success', 'ringkas' => $ringkas, 'data' => $data]);
} catch (Throwable $e) {
   error_log('[dashboard/dinkesMonitoring] ' . $e->getMessage());
   dmBalas(['status' => 'error', 'message' => 'Terjadi kesalahan pada server.'], 500);
}
