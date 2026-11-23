<?php
/**
 * Tiket permintaan farmasi per kunjungan (module/admin/permintaan_farmasi, modal_farmasi, pemeriksaan_b).
 *   GET    ?no=<visit_ID>          -> { status, data:[tiket + items[] + jumlah_item], kunjungan:{visit_status} }
 *   GET    ?no=..&id=<id tiket>    -> { status, data:{tiket} }
 *   POST   (form)                  -> buat tiket { status, message, id }
 *   PUT    (form)                  -> ubah catatan tiket
 *   PUT    ?approve=1 (form)       -> status 1
 *   DELETE ?id=<id tiket>          -> hapus tiket + rinciannya (tiket Selesai tidak boleh dihapus)
 * Perbaikan: tiket bisa dibuat untuk kunjungan faskes lain / kunjungan batal / tipe sembarang, pesan error SQL
 * dikirim ke browser, hapus tidak dicek status (tiket Selesai bisa terhapus) & rincian obat tertinggal (yatim),
 * gagal hapus/ubah tidak mengirim respons apa pun.
 */
include '../../database/connect.php';
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../socket/sendSocket.php';
$id_customer = $_SESSION['id_customer'] ?? null;
session_write_close();

function pfBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

if (!$id_customer) {
   pfBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);
}

const PF_TIPE = ['Racikan', 'Non Racikan', 'BMHP'];

$method = $_SERVER['REQUEST_METHOD'];

try {
   switch ($method) {
      case 'POST':
         createData($id_customer);
         break;
      case 'GET':
         if (isset($_GET['id'])) {
            getID($_GET['id'], $id_customer);
         } else {
            getData($id_customer);
         }
         break;
      case 'PUT':
         parse_str(file_get_contents("php://input"), $_PUT);
         if (isset($_GET['approve'])) {
            approveData($_PUT, $id_customer);
         } else {
            updateData($_PUT, $id_customer);
         }
         break;
      case 'DELETE':
         deleteData($id_customer);
         break;
      default:
         pfBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[visit/permintaanFarmasi] ' . $e->getMessage());
   pfBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

function createData($id_customer)
{
   global $koneksi;

   $visit = trim((string) ($_POST['id_visit'] ?? ''));
   // form lama (pemeriksaan_b) tidak mengirim tipe_obat -> anggap Non Racikan
   $tipe = isset($_POST['tipe_obat']) ? trim((string) $_POST['tipe_obat']) : 'Non Racikan';
   $catatan = mb_substr(trim((string) ($_POST['catatan_permintaan'] ?? '')), 0, 5000);
   if ($visit === '') pfBalas(422, ['status' => 'error', 'message' => 'Nomor kunjungan tidak ditemukan.']);
   if (!in_array($tipe, PF_TIPE, true)) pfBalas(422, ['status' => 'error', 'message' => 'Pilih tipe obat: Racikan, Non Racikan, atau BMHP.']);

   $jumlah = $satuan = $signa = null;
   if ($tipe === 'Racikan') {
      $jumlah = trim((string) ($_POST['rck_jumlah'] ?? ''));
      if ($jumlah !== '' && !(ctype_digit($jumlah) && (int) $jumlah > 0 && (int) $jumlah <= 10000)) {
         pfBalas(422, ['status' => 'error', 'message' => 'Jumlah racikan harus bilangan bulat 1–10000.']);
      }
      $jumlah = $jumlah === '' ? null : (string) (int) $jumlah;
      $satuan = mb_substr(trim((string) ($_POST['rck_satuan'] ?? '')), 0, 100) ?: null;
      $signa = mb_substr(trim((string) ($_POST['rck_signa'] ?? '')), 0, 255) ?: null;
   }

   // kunjungan harus milik faskes ini & tidak batal
   $st = $koneksi->prepare("SELECT visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('ss', $visit, $id_customer);
   $st->execute();
   $kunj = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$kunj) pfBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   if ((int) $kunj['visit_status'] === 99) pfBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan, tidak bisa membuat permintaan farmasi.']);

   $st = $koneksi->prepare("INSERT INTO permintaan_pharmacy (id_customer, id_visit, catatan_permintaan, rck_jumlah, rck_satuan, rck_signa, tipe_obat)
      VALUES (?, ?, ?, ?, ?, ?, ?)");
   $st->bind_param('sssssss', $id_customer, $visit, $catatan, $jumlah, $satuan, $signa, $tipe);
   $st->execute();
   $id = $st->insert_id;
   $st->close();

   try {
      sendSocketAntrianBaru($id_customer, $visit);
   } catch (Throwable $e) {
      error_log('[visit/permintaanFarmasi] socket: ' . $e->getMessage());
   }
   pfBalas(200, ['status' => 'success', 'message' => "Tiket {$tipe} dibuat. Lanjutkan isi rincian obat.", 'id' => $id]);
}

function getData($id_customer)
{
   global $koneksi;

   $no = trim((string) ($_GET['no'] ?? ''));

   $st = $koneksi->prepare("SELECT p.*, pv.patient_name_pcare, pv.visit_status AS _visit_status
      FROM permintaan_pharmacy p
      LEFT JOIN pasien_visit pv ON pv.visit_ID = p.id_visit AND pv.id_customer = p.id_customer
      WHERE p.id_visit = ? AND p.id_customer = ?
      ORDER BY p.id_permintaan_farmasi ASC");
   $st->bind_param("ss", $no, $id_customer);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();

   // rincian obat semua tiket sekaligus (1 query; tabel rincian belum ber-index id_permintaan_farmasi,
   // jadi jangan per tiket)
   $byId = [];
   foreach ($data as $i => $row) {
      $data[$i]['items'] = [];
      $byId[(int) $row['id_permintaan_farmasi']] = $i;
   }
   if ($byId) {
      // id dari DB (int) -> aman dirangkai langsung; query biasa menghemat 1 round-trip prepare ke DB remote
      $ids = implode(',', array_map('intval', array_keys($byId)));
      $res = $koneksi->query("SELECT pd.id_permintaan_farmasi, pd.qty, pd.qty_racik, pd.signa, pd.catatan,
            COALESCE(NULLIF(mp.pharmacy_name_generic, ''), pd.item_name) AS nama, mp.pharmacy_name_trade AS nama_dagang, mp.pharmacy_unit AS satuan
         FROM permintaan_pharmacy_details pd
         LEFT JOIN ms_pharmacy mp ON mp.id_pharmacy = pd.id_pharmacy
         WHERE pd.id_permintaan_farmasi IN ($ids)
         ORDER BY pd.id_pharmacy_details ASC");
      while ($it = $res->fetch_assoc()) {
         $i = $byId[(int) $it['id_permintaan_farmasi']];
         unset($it['id_permintaan_farmasi']);
         $data[$i]['items'][] = $it;
      }
      $res->free();
   }
   foreach ($data as $i => $row) $data[$i]['jumlah_item'] = count($row['items']);

   // status kunjungan ikut dari JOIN di atas; query terpisah hanya bila belum ada tiket (hemat 1 round-trip DB)
   $kunj = null;
   if ($data && $data[0]['_visit_status'] !== null) {
      $kunj = ['visit_status' => $data[0]['_visit_status']];
   } elseif (!$data) {
      $st = $koneksi->prepare("SELECT visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
      $st->bind_param('ss', $no, $id_customer);
      $st->execute();
      $kunj = $st->get_result()->fetch_assoc();
      $st->close();
   }
   foreach ($data as $i => $row) unset($data[$i]['_visit_status']);

   pfBalas(200, [
      'status' => 'success',
      'data' => $data,
      'kunjungan' => $kunj ? ['visit_status' => (int) $kunj['visit_status']] : null,
   ]);
}

function getID($id, $id_customer)
{
   global $koneksi;

   $st = $koneksi->prepare("SELECT * FROM permintaan_pharmacy WHERE id_permintaan_farmasi = ? AND id_customer = ?");
   $id = (int) $id;
   $st->bind_param("is", $id, $id_customer);
   $st->execute();
   $row = $st->get_result()->fetch_assoc();
   $st->close();

   if ($row) pfBalas(200, ['status' => 'success', 'data' => $row]);
   pfBalas(404, ['status' => 'error', 'message' => 'Tiket tidak ditemukan.']);
}

function updateData($put, $id_customer)
{
   global $koneksi;

   $id = (int) ($put['id_permintaan_farmasi'] ?? ($_GET['id'] ?? 0));
   $catatan = mb_substr(trim((string) ($put['catatan_permintaan'] ?? '')), 0, 5000);

   $st = $koneksi->prepare("UPDATE permintaan_pharmacy SET catatan_permintaan = ? WHERE id_permintaan_farmasi = ? AND id_customer = ?");
   $st->bind_param("sis", $catatan, $id, $id_customer);
   $st->execute();
   $st->close();
   $st = $koneksi->prepare("SELECT 1 FROM permintaan_pharmacy WHERE id_permintaan_farmasi = ? AND id_customer = ?");
   $st->bind_param("is", $id, $id_customer);
   $st->execute();
   $ok = (bool) $st->get_result()->fetch_row();
   $st->close();
   pfBalas($ok ? 200 : 404, $ok ? ['status' => 'success', 'message' => 'Catatan tiket diperbarui.'] : ['status' => 'error', 'message' => 'Tiket tidak ditemukan.']);
}

function deleteData($id_customer)
{
   global $koneksi;

   $id = (int) ($_GET['id'] ?? 0);
   if ($id <= 0) pfBalas(422, ['status' => 'error', 'message' => 'ID tiket tidak valid.']);

   $koneksi->begin_transaction();
   try {
      $st = $koneksi->prepare("SELECT status_permintaan FROM permintaan_pharmacy WHERE id_permintaan_farmasi = ? AND id_customer = ? FOR UPDATE");
      $st->bind_param("is", $id, $id_customer);
      $st->execute();
      $tiket = $st->get_result()->fetch_assoc();
      $st->close();
      if (!$tiket) {
         $koneksi->rollback();
         pfBalas(404, ['status' => 'error', 'message' => 'Tiket tidak ditemukan atau sudah terhapus.']);
      }
      if ((int) $tiket['status_permintaan'] === 3) {
         $koneksi->rollback();
         pfBalas(403, ['status' => 'error', 'message' => 'Tiket sudah Selesai (obat sudah diserahkan), tidak dapat dihapus.']);
      }

      // rincian dihapus lewat PRIMARY KEY (kolom id_permintaan_farmasi belum ber-index -> hindari DELETE ... WHERE
      // id_permintaan_farmasi yang mengunci seluruh tabel rincian)
      $st = $koneksi->prepare("SELECT id_pharmacy_details FROM permintaan_pharmacy_details WHERE id_permintaan_farmasi = ?");
      $st->bind_param("i", $id);
      $st->execute();
      $detailIds = array_map('intval', array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'id_pharmacy_details'));
      $st->close();
      if ($detailIds) {
         $ph = implode(',', array_fill(0, count($detailIds), '?'));
         $st = $koneksi->prepare("DELETE FROM permintaan_pharmacy_details WHERE id_pharmacy_details IN ($ph) AND id_permintaan_farmasi = ?");
         $vals = array_merge($detailIds, [$id]);
         $st->bind_param(str_repeat('i', count($vals)), ...$vals);
         $st->execute();
         $st->close();
      }

      $st = $koneksi->prepare("DELETE FROM permintaan_pharmacy WHERE id_permintaan_farmasi = ? AND id_customer = ?");
      $st->bind_param("is", $id, $id_customer);
      $st->execute();
      $st->close();
      $koneksi->commit();
   } catch (Throwable $e) {
      $koneksi->rollback();
      throw $e;
   }
   pfBalas(200, ['status' => 'success', 'message' => 'Tiket dan ' . count($detailIds) . ' rincian obat dihapus.']);
}

function approveData($data, $id_customer)
{
   global $koneksi;

   $id = (int) ($data['id_permintaan_farmasi'] ?? 0);
   $st = $koneksi->prepare("UPDATE permintaan_pharmacy SET status_permintaan = 1 WHERE id_permintaan_farmasi = ? AND id_customer = ?");
   $st->bind_param("is", $id, $id_customer);
   $st->execute();
   $n = $st->affected_rows;
   $st->close();
   pfBalas($n > 0 ? 200 : 404, $n > 0 ? ['status' => 'success'] : ['status' => 'error', 'message' => 'Tiket tidak ditemukan.']);
}

function sendSocketAntrianBaru($id_customer, $visit_id)
{
   global $koneksi;
   $query = "SELECT pv.id_visit, pv.patient_name_pcare, pv.id_poli, pp.status_permintaan FROM pasien_visit AS pv INNER JOIN permintaan_pharmacy AS pp ON pv.visit_ID = pp.id_visit AND pp.id_customer = pv.id_customer WHERE pv.id_customer = ? AND pv.visit_ID = ? LIMIT 1";
   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("ss", $id_customer, $visit_id);
   $stmt->execute();
   $data = $stmt->get_result()->fetch_assoc();
   $stmt->close();

   if (!$data) {
      return false;
   }

   return farmasiupdate([
      "rs_id" => $id_customer,
      "target_role" => "display-farmasi_DISPLAY",
      "status" => $data['status_permintaan'],
      "visit_id" => $visit_id,
      "nama_pasien" => $data['patient_name_pcare'],
      "poli" => $data['id_poli'],
      "is_panggilan" => false
   ]);
}
