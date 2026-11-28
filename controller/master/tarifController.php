<?php
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';

/** Log perubahan master tarif. */
function tfLog(string $aksi, array $row, string $ket, ?array $lama, ?array $baru): void
{
   global $koneksi;
   logAktivitas($koneksi, 'tarif', $aksi, ['tipe' => 'tarif', 'id' => $row['id_tarif'] ?? null, 'label' => (string) ($row['tarif_name'] ?? '-')], $ket, $lama, $baru);
}

header('Content-Type: application/json');

// 🔐 VALIDASI SESSION
if (!isset($_SESSION['id_customer'])) {
   http_response_code(401);
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak valid / expired'
   ]);
   exit;
}

$id_customer = (int) $_SESSION['id_customer'];

const TARIF_LAYANAN = ['Poliklinik', 'UGD', 'Rawat Inap', '99'];

function jawab(array $data, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($data);
   exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
   case 'POST':
      createData($id_customer);
      break;

   case 'GET':
      if (isset($_GET['id'])) {
         getID((int) $_GET['id'], $id_customer);
      } else {
         getData($id_customer);
      }
      break;

   case 'PUT':
      updateData($id_customer);
      break;

   case 'DELETE':
      deleteData($id_customer);
      break;

   default:
      jawab(['status' => 'error', 'message' => 'Metode tidak didukung'], 405);
}

// ================= VALIDASI INPUT =================
/**
 * Ambil & validasi field form tarif. Mengembalikan [data, pesanError].
 */
function bacaForm(array $in): array
{
   $nama = trim((string) ($in['tarif_name'] ?? ''));
   if ($nama === '') {
      return [null, 'Nama tarif wajib diisi.'];
   }
   if (mb_strlen($nama) > 255) {
      return [null, 'Nama tarif terlalu panjang.'];
   }

   $layanan = (string) ($in['tarif_services'] ?? '99');
   if (!in_array($layanan, TARIF_LAYANAN, true)) {
      return [null, 'Layanan tidak valid.'];
   }

   $provider = (string) ($in['tarif_provider'] ?? '99');
   if ($provider !== '99' && !ctype_digit($provider)) {
      return [null, 'Jenis bayar tidak valid.'];
   }

   $angka = [];
   $label = [
      'tarif_amount'       => 'Tarif',
      'tarif_share_doctor' => 'Fee dokter',
      'tarif_share_nurse'  => 'Fee perawat',
      'tarif_share_admin'  => 'Fee admin',
   ];
   foreach ($label as $f => $lbl) {
      $v = trim((string) ($in[$f] ?? '0'));
      if ($v === '') {
         $v = '0';
      }
      if (!preg_match('/^\d{1,10}$/', $v) || (int) $v > 2000000000) {
         return [null, "$lbl harus berupa angka bulat ≥ 0."];
      }
      $angka[$f] = (int) $v;
   }

   $data = [
      'tarif_name'     => $nama,
      'tarif_services' => $layanan,
      'tarif_provider' => $provider,
   ] + $angka;

   if (array_key_exists('tarif_description', $in)) {
      $data['tarif_description'] = trim((string) $in['tarif_description']) ?: null;
   }

   return [$data, null];
}

function namaDipakai(mysqli $k, int $idc, string $nama, int $kecualiId = 0): bool
{
   $stmt = $k->prepare(
      "SELECT 1 FROM ms_tarif
       WHERE id_customer=? AND LOWER(TRIM(tarif_name))=LOWER(TRIM(?)) AND id_tarif<>?
       LIMIT 1"
   );
   $stmt->bind_param("isi", $idc, $nama, $kecualiId);
   $stmt->execute();
   $ada = (bool) $stmt->get_result()->fetch_row();
   $stmt->close();
   return $ada;
}

/** Jumlah baris billing yang memakai nama tarif ini (billing menyimpan nama, bukan id). */
function jumlahPemakaian(mysqli $k, int $idc, string $nama): int
{
   $idcStr = (string) $idc;
   $stmt = $k->prepare(
      "SELECT COUNT(*) FROM pasien_billing
       WHERE id_customer=? AND LOWER(TRIM(billing_item))=LOWER(TRIM(?))"
   );
   $stmt->bind_param("ss", $idcStr, $nama);
   $stmt->execute();
   $n = (int) $stmt->get_result()->fetch_row()[0];
   $stmt->close();
   return $n;
}

function ambilTarif(mysqli $k, int $id, int $idc): ?array
{
   $stmt = $k->prepare("SELECT * FROM ms_tarif WHERE id_tarif=? AND id_customer=?");
   $stmt->bind_param("ii", $id, $idc);
   $stmt->execute();
   $row = $stmt->get_result()->fetch_assoc();
   $stmt->close();
   return $row ?: null;
}

// ================= CREATE =================
function createData($id_customer)
{
   global $koneksi;

   [$d, $err] = bacaForm($_POST);
   if ($err) {
      jawab(['status' => 'error', 'message' => $err], 422);
   }
   if (namaDipakai($koneksi, $id_customer, $d['tarif_name'])) {
      jawab(['status' => 'error', 'message' => 'Nama tarif "' . $d['tarif_name'] . '" sudah ada. Billing mencocokkan tarif berdasarkan nama, jadi nama harus unik.'], 409);
   }

   $fields = ['tarif_number', 'id_customer'];
   $values = [generateTarifNumber($koneksi), $id_customer];
   foreach ($d as $f => $v) {
      $fields[] = $f;
      $values[] = $v;
   }

   $sql = "INSERT INTO ms_tarif (" . implode(',', $fields) . ") VALUES (" . implode(',', array_fill(0, count($fields), '?')) . ")";
   $stmt = $koneksi->prepare($sql);
   $stmt->bind_param(str_repeat('s', count($values)), ...$values);

   if ($stmt->execute()) {
      tfLog('tambah', ['id_tarif' => $stmt->insert_id, 'tarif_name' => $d['tarif_name']], 'Tarif baru Rp' . number_format((int) ($d['tarif_amount'] ?? 0), 0, ',', '.'), null, $d);
      jawab(['status' => 'success', 'message' => 'Tarif berhasil ditambahkan.', 'id' => $stmt->insert_id]);
   }
   jawab(['status' => 'error', 'message' => 'Gagal menyimpan tarif.'], 500);
}

// ================= GENERATE NUMBER =================
function generateTarifNumber($koneksi)
{
   $count = 0;

   do {
      $number = "TRX-" . mt_rand(100000, 999999);

      $check = $koneksi->prepare("SELECT COUNT(*) FROM ms_tarif WHERE tarif_number=?");
      $check->bind_param("s", $number);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $number;
}

// ================= READ =================
function getData($id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare(
      "SELECT t.id_tarif, t.tarif_number, t.tarif_code, t.tarif_services, t.tarif_name,
              t.tarif_amount, t.tarif_status, t.tarif_provider, t.created_at,
              COALESCE(t.tarif_share_doctor,0) AS tarif_share_doctor,
              COALESCE(t.tarif_share_nurse,0)  AS tarif_share_nurse,
              COALESCE(t.tarif_share_admin,0)  AS tarif_share_admin,
              p.provider_name
       FROM ms_tarif t
       LEFT JOIN ms_provider p ON p.id_provider = t.tarif_provider
       WHERE t.id_customer=?
       ORDER BY t.tarif_name ASC"
   );
   $stmt->bind_param("i", $id_customer);
   $stmt->execute();
   $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
   $stmt->close();

   // Pemakaian di billing (dicocokkan lewat nama, sama seperti tindakan & laporan prosedur)
   $idcStr = (string) $id_customer;
   $stmt = $koneksi->prepare(
      "SELECT LOWER(TRIM(billing_item)) AS k, COUNT(*) AS n,
              SUM(COALESCE(billing_qty,1)) AS qty, MAX(created_at) AS terakhir
       FROM pasien_billing
       WHERE id_customer=?
       GROUP BY k"
   );
   $stmt->bind_param("s", $idcStr);
   $stmt->execute();
   $pakai = [];
   foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
      $pakai[$r['k']] = $r;
   }
   $stmt->close();

   foreach ($data as &$row) {
      $u = $pakai[mb_strtolower(trim((string) $row['tarif_name']))] ?? null;
      $row['jml_pakai'] = $u ? (int) $u['n'] : 0;
      $row['qty_pakai'] = $u ? (int) $u['qty'] : 0;
      $row['terakhir_dipakai'] = $u['terakhir'] ?? null;
   }
   unset($row);

   jawab(['status' => 'success', 'data' => $data]);
}

// ================= READ BY ID =================
function getID($id, $id_customer)
{
   global $koneksi;

   $row = ambilTarif($koneksi, $id, $id_customer);
   if (!$row) {
      jawab(['status' => 'error', 'message' => 'Tidak ditemukan'], 404);
   }
   $row['jml_pakai'] = jumlahPemakaian($koneksi, $id_customer, (string) $row['tarif_name']);
   jawab(['status' => 'success', 'data' => $row]);
}

// ================= UPDATE =================
function updateData($id_customer)
{
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   $id = (int) ($_PUT['id_tarif'] ?? 0);
   if ($id <= 0) {
      jawab(['status' => 'error', 'message' => 'ID tidak ditemukan'], 422);
   }

   $lama = ambilTarif($koneksi, $id, $id_customer);
   if (!$lama) {
      jawab(['status' => 'error', 'message' => 'Tarif tidak ditemukan'], 404);
   }

   // ================= TOGGLE STATUS =================
   if (isset($_GET['toggle_status'])) {
      $status = (string) ($_PUT['tarif_status'] ?? '');
      if ($status !== '0' && $status !== '1') {
         jawab(['status' => 'error', 'message' => 'Status tidak valid.'], 422);
      }
      $status = (int) $status;

      $stmt = $koneksi->prepare("UPDATE ms_tarif SET tarif_status=? WHERE id_tarif=? AND id_customer=?");
      $stmt->bind_param("iii", $status, $id, $id_customer);
      if ($stmt->execute()) {
         tfLog($status ? 'aktifkan' : 'nonaktifkan', $lama, '', ['tarif_status' => $lama['tarif_status'] ?? null], ['tarif_status' => $status]);
         jawab(['status' => 'success', 'message' => $status ? 'Tarif diaktifkan.' : 'Tarif dinonaktifkan.']);
      }
      jawab(['status' => 'error', 'message' => 'Gagal update status.'], 500);
   }

   // ================= UPDATE NORMAL =================
   [$d, $err] = bacaForm($_PUT);
   if ($err) {
      jawab(['status' => 'error', 'message' => $err], 422);
   }

   // cek duplikat hanya bila nama berubah (data lama mungkin sudah ada duplikat)
   $namaBerubah = mb_strtolower(trim((string) $lama['tarif_name'])) !== mb_strtolower($d['tarif_name']);
   if ($namaBerubah && namaDipakai($koneksi, $id_customer, $d['tarif_name'], $id)) {
      jawab(['status' => 'error', 'message' => 'Nama tarif "' . $d['tarif_name'] . '" sudah dipakai tarif lain.'], 409);
   }

   $set = [];
   $values = [];
   foreach ($d as $f => $v) {
      $set[] = "$f=?";
      $values[] = $v;
   }
   $values[] = $id;
   $values[] = $id_customer;

   $stmt = $koneksi->prepare("UPDATE ms_tarif SET " . implode(',', $set) . " WHERE id_tarif=? AND id_customer=?");
   $stmt->bind_param(str_repeat('s', count($values) - 2) . "ii", ...$values);

   if ($stmt->execute()) {
      [$l, $b] = logBeda($lama, $d);
      if ($b) {
         $hargaBerubah = array_key_exists('tarif_amount', $b);
         tfLog($hargaBerubah ? 'ubah_harga' : 'ubah', $lama, $hargaBerubah
            ? 'Harga Rp' . number_format((int) $lama['tarif_amount'], 0, ',', '.') . ' → Rp' . number_format((int) $b['tarif_amount'], 0, ',', '.') : 'Ubah data tarif', $l, $b);
      }
      jawab(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
   }
   jawab(['status' => 'error', 'message' => 'Gagal memperbarui tarif.'], 500);
}

// ================= DELETE =================
function deleteData($id_customer)
{
   global $koneksi;

   $id = (int) ($_GET['id'] ?? 0);
   if ($id <= 0) {
      jawab(['status' => 'error', 'message' => 'ID kosong'], 422);
   }

   $row = ambilTarif($koneksi, $id, $id_customer);
   if (!$row) {
      jawab(['status' => 'error', 'message' => 'Tarif tidak ditemukan'], 404);
   }

   $n = jumlahPemakaian($koneksi, $id_customer, (string) $row['tarif_name']);
   if ($n > 0) {
      jawab([
         'status' => 'error',
         'message' => "Tarif ini sudah dipakai di $n tagihan pasien sehingga tidak bisa dihapus. Nonaktifkan saja agar tidak muncul lagi di billing."
      ], 409);
   }

   $stmt = $koneksi->prepare("DELETE FROM ms_tarif WHERE id_tarif=? AND id_customer=?");
   $stmt->bind_param("ii", $id, $id_customer);
   if ($stmt->execute()) {
      tfLog('hapus', $row, 'Tarif dihapus', $row, null);
      jawab(['status' => 'success', 'message' => 'Tarif dihapus.']);
   }
   jawab(['status' => 'error', 'message' => 'Gagal menghapus tarif.'], 500);
}
