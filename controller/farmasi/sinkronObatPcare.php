<?php
// Kirim ulang terapi obat ke PCare setelah obat ditambah/diubah/dihapus pada tiket
// yang SUDAH terkirim (status_permintaan >= 1). Tiket yang belum terkirim (0) tidak
// disinkronkan karena akan ikut terkirim lewat kirimObat.php.
//
// POST JSON { "id_permintaan_farmasi": 123 }
// Response status: success | skip (tidak perlu kirim) | error
ini_set('display_errors', '0'); // warning include setting PCare tidak boleh merusak JSON
require_once __DIR__ . '/../admisi/services/view.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../admisi/services/servicebpjs.php';
require_once __DIR__ . '/../doctor/rmeCycleService.php';
require_once __DIR__ . '/pcareTerapiService.php';
require_once __DIR__ . '/../../utility/activityLog.php';

header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_OFF);

function sinkronJawab(string $status, string $message, array $extra = []): void
{
   echo json_encode(array_merge(['status' => $status, 'message' => $message], $extra));
   exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$idTiket = (string)($input['id_permintaan_farmasi'] ?? '');
$id_customer = (string)($_SESSION['id_customer'] ?? '');

if ($idTiket === '' || !ctype_digit($idTiket) || $id_customer === '') {
   sinkronJawab('error', 'Data tidak valid');
}

$stmt = $koneksi->prepare("
   SELECT pp.id_visit, pp.status_permintaan, pv.id_provider, pv.status_rawatinap, pv.visit_status
   FROM permintaan_pharmacy pp
   LEFT JOIN pasien_visit pv ON pv.visit_ID = pp.id_visit AND pv.id_customer = pp.id_customer
   WHERE pp.id_permintaan_farmasi = ? AND pp.id_customer = ?
");
$stmt->bind_param('ss', $idTiket, $id_customer);
$stmt->execute();
$tiket = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tiket) sinkronJawab('error', 'Tiket tidak ditemukan');
if ((int)$tiket['status_permintaan'] < 1) sinkronJawab('skip', 'Tiket belum dikirim, obat akan terkirim saat tiket dikirim.');
if ($tiket['id_provider'] === null) sinkronJawab('skip', 'Data kunjungan tidak ditemukan.');
if ((int)$tiket['visit_status'] === 99) sinkronJawab('skip', 'Kunjungan sudah dibatalkan.');
if ((int)$tiket['id_provider'] !== 1) sinkronJawab('skip', 'Pasien non-BPJS, tidak perlu kirim PCare.');
if ($tiket['status_rawatinap'] == '1') sinkronJawab('skip', 'Pasien rawat inap, obat tidak dikirim lewat kunjungan PCare rawat jalan.');

$koneksi->begin_transaction();
try {
   // kunci tiket kunjungan agar tidak bentrok dengan kirimObat.php
   $stmt = $koneksi->prepare("SELECT id_permintaan_farmasi FROM permintaan_pharmacy WHERE id_visit = ? AND id_customer = ? FOR UPDATE");
   $stmt->bind_param('ss', $tiket['id_visit'], $id_customer);
   $stmt->execute();
   $stmt->close();

   $kirim = pcareKirimTerapiVisit($koneksi, $id_customer, (string)$tiket['id_visit']);
   $lgRef = ['tipe' => 'tiket_resep', 'id' => $idTiket, 'label' => 'Sinkron obat PCare', 'visit_ID' => $tiket['id_visit']];
   if (!$kirim['ok']) {
      $koneksi->rollback();
      logAktivitas($koneksi, 'farmasi', 'kirim_obat', $lgRef, 'GAGAL sinkron obat ke PCare: ' . mb_substr((string) $kirim['message'], 0, 300));
      sinkronJawab('error', 'Obat tersimpan, tetapi gagal dikirim ke PCare: ' . $kirim['message'], ['result' => $kirim['result']]);
   }
   $koneksi->commit();
   logAktivitas($koneksi, 'farmasi', 'kirim_obat', $lgRef, 'Obat disinkronkan ulang ke PCare (tiket sudah terkirim diubah)');
   sinkronJawab('success', 'Obat berhasil diperbarui di PCare.');
} catch (Throwable $e) {
   $koneksi->rollback();
   logAktivitas($koneksi, 'farmasi', 'kirim_obat', ['tipe' => 'tiket_resep', 'id' => $idTiket, 'label' => 'Sinkron obat PCare', 'visit_ID' => $tiket['id_visit']], 'GAGAL sinkron obat ke PCare: ' . mb_substr($e->getMessage(), 0, 300));
   sinkronJawab('error', 'Obat tersimpan, tetapi gagal dikirim ke PCare: ' . $e->getMessage());
}
