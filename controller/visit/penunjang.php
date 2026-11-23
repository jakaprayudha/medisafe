<?php
/**
 * Pemeriksaan penunjang (lab) per pasien — module/admin/penunjang & component_penunjang.
 *   GET    ?no=<visit_ID>                 -> semua pemeriksaan pasien kunjungan ini (riwayat lintas kunjungan, 1 faskes)
 *   GET    ?no=..&id=<id_inspection>      -> 1 pemeriksaan
 *   POST   (form, tanpa id_inspection)    -> tambah ke kunjungan ?no / id_visit
 *   POST   (form, id_inspection)          -> ubah nama/tanggal/catatan
 *   DELETE ?id=<id_inspection>            -> hapus pemeriksaan + hasilnya
 * Aturan: sumber hasil dikunci "Lab Klinik", catatan wajib teks tanpa angka.
 * Perbaikan: id_patient dulu diambil dari URL (dipercaya begitu saja) -> sekarang diturunkan dari kunjungan;
 * permintaan bisa ditambahkan ke kunjungan faskes lain / kunjungan batal; ubah bisa memindahkan pemeriksaan ke
 * kunjungan lain (id_visit); tanggal & nama tidak divalidasi; hapus meninggalkan hasil lab (laboratorium_result)
 * yatim; pesan error SQL dikirim ke browser.
 */
require_once __DIR__ . '/../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');
$id_customer = (int) ($_SESSION['id_customer'] ?? 0);
session_write_close();

function pjBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}
if ($id_customer <= 0) pjBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'POST':
         !empty($_POST['id_inspection']) ? updateData() : createData();
         break;
      case 'GET':
         isset($_GET['id']) ? getID((int) $_GET['id']) : getData();
         break;
      case 'DELETE':
         deleteData();
         break;
      default:
         pjBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   error_log('[visit/penunjang] ' . $e->getMessage());
   pjBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}

/** Validasi isi form; kembalikan [nama, tanggal, catatan]. */
function validasiPenunjang(): array
{
   $nama = mb_substr(trim((string) ($_POST['inspection_name'] ?? '')), 0, 255);
   if ($nama === '') pjBalas(422, ['status' => 'error', 'message' => 'Nama pemeriksaan wajib dipilih.', 'field' => 'inspection_name']);
   $tgl = trim((string) ($_POST['inspection_date'] ?? ''));
   $x = DateTime::createFromFormat('!Y-m-d', $tgl);
   if (!$x || $x->format('Y-m-d') !== $tgl) pjBalas(422, ['status' => 'error', 'message' => 'Tanggal pemeriksaan tidak valid.', 'field' => 'inspection_date']);
   if ($x > new DateTime('tomorrow')) pjBalas(422, ['status' => 'error', 'message' => 'Tanggal pemeriksaan tidak boleh di masa depan.', 'field' => 'inspection_date']);
   $catatan = trim((string) ($_POST['inspection_note'] ?? ''));
   $pesan = null;
   if ($catatan === '') $pesan = 'Catatan wajib diisi.';
   elseif (preg_match('/\d/', $catatan)) $pesan = 'Catatan tidak boleh berisi angka.';
   elseif (!preg_match('/\p{L}/u', $catatan)) $pesan = 'Catatan harus berupa teks.';
   if ($pesan) pjBalas(422, ['status' => 'error', 'message' => $pesan, 'field' => 'inspection_note']);
   return [$nama, $tgl, mb_substr($catatan, 0, 2000)];
}

/** Kunjungan milik faskes sesi -> ['id_patient','visit_status'] atau null */
function kunjungan(string $visit): ?array
{
   global $koneksi, $id_customer;
   $st = $koneksi->prepare("SELECT id_patient, visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('si', $visit, $id_customer);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function createData()
{
   global $koneksi, $id_customer;
   $visit = trim((string) ($_POST['id_visit'] ?? ($_GET['no'] ?? '')));
   $k = $visit !== '' ? kunjungan($visit) : null;
   if (!$k) pjBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   if ((int) $k['visit_status'] === 99) pjBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);
   [$nama, $tgl, $catatan] = validasiPenunjang();

   $nomor = generateDoctorNumber($koneksi);
   $sumber = 'Lab Klinik';
   $st = $koneksi->prepare("INSERT INTO visit_inspection (inspection_number, id_customer, id_visit, inspection_name, inspection_date, inspection_source, inspection_note)
      VALUES (?, ?, ?, ?, ?, ?, ?)");
   $st->bind_param('sisssss', $nomor, $id_customer, $visit, $nama, $tgl, $sumber, $catatan);
   $st->execute();
   $id = $st->insert_id;
   $st->close();
   pjBalas(200, ['status' => 'success', 'message' => 'Permintaan pemeriksaan ditambahkan.', 'id' => $id]);
}

function getData()
{
   global $koneksi, $id_customer;
   $no = trim((string) ($_GET['no'] ?? ''));
   $k = $no !== '' ? kunjungan($no) : null;
   if (!$k) pjBalas(200, ['status' => 'success', 'data' => []]);
   $id_patient = (string) $k['id_patient'];

   $st = $koneksi->prepare("
SELECT
    vi.id_inspection, vi.id_visit, vi.inspection_name, vi.inspection_date, vi.inspection_source, vi.inspection_note,
    vi.inspection_number, vi.created_at,
    pv.visit_ID,
    pv.visit_date,
    -- 1 baris per pemeriksaan (pemeriksaan multi-parameter mis. Widal tidak berulang per parameter)
    CASE WHEN COALESCE(lr.terisi, 0) = 1 THEN lr.hasil_satu
         WHEN COALESCE(lr.terisi, 0) > 1 THEN CONCAT(lr.terisi, ' parameter terisi') END AS hasil,
    CASE WHEN COALESCE(lr.terisi, 0) = 1 THEN lr.keterangan_satu END AS keterangan,
    COALESCE(lr.terisi, 0) AS hasil_terisi,
    CASE WHEN pv.visit_ID = ? THEN 1 ELSE 0 END AS is_current
FROM visit_inspection vi
INNER JOIN pasien_visit pv ON pv.visit_ID = vi.id_visit
LEFT JOIN (
    SELECT id_inspection,
           COUNT(CASE WHEN TRIM(COALESCE(hasil, '')) <> '' THEN 1 END) AS terisi,
           MAX(CASE WHEN TRIM(COALESCE(hasil, '')) <> '' THEN hasil END) AS hasil_satu,
           MAX(CASE WHEN TRIM(COALESCE(hasil, '')) <> '' THEN keterangan END) AS keterangan_satu
    FROM laboratorium_result
    WHERE id_inspection IN (
        SELECT vi2.id_inspection FROM visit_inspection vi2
        INNER JOIN pasien_visit pv2 ON pv2.visit_ID = vi2.id_visit
        WHERE pv2.id_patient = ? AND pv2.id_customer = ?
    )
    GROUP BY id_inspection
) lr ON lr.id_inspection = vi.id_inspection
WHERE pv.id_patient = ? AND pv.id_customer = ?
ORDER BY is_current DESC, vi.inspection_date DESC, vi.id_inspection DESC");
   $st->bind_param('ssisi', $no, $id_patient, $id_customer, $id_patient, $id_customer);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   pjBalas(200, ['status' => 'success', 'data' => $data]);
}

function getID(int $id)
{
   global $koneksi, $id_customer;
   $st = $koneksi->prepare("SELECT id_inspection, id_visit, inspection_name, inspection_date, inspection_source, inspection_note, inspection_number
      FROM visit_inspection WHERE id_inspection = ? AND id_customer = ?");
   $st->bind_param('ii', $id, $id_customer);
   $st->execute();
   $row = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$row) pjBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   pjBalas(200, ['status' => 'success', 'data' => $row]);
}

function updateData()
{
   global $koneksi, $id_customer;
   $id = (int) ($_POST['id_inspection'] ?? 0);
   [$nama, $tgl, $catatan] = validasiPenunjang();
   $sumber = 'Lab Klinik';
   // id_visit sengaja tidak ikut diubah (dulu bisa memindahkan pemeriksaan ke kunjungan lain)
   $st = $koneksi->prepare("UPDATE visit_inspection SET inspection_name = ?, inspection_date = ?, inspection_source = ?, inspection_note = ?
      WHERE id_inspection = ? AND id_customer = ?");
   $st->bind_param('ssssii', $nama, $tgl, $sumber, $catatan, $id, $id_customer);
   $st->execute();
   $st->close();
   $st = $koneksi->prepare("SELECT 1 FROM visit_inspection WHERE id_inspection = ? AND id_customer = ?");
   $st->bind_param('ii', $id, $id_customer);
   $st->execute();
   $ada = (bool) $st->get_result()->fetch_row();
   $st->close();
   if (!$ada) pjBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
   pjBalas(200, ['status' => 'success', 'message' => 'Pemeriksaan diperbarui.']);
}

function deleteData()
{
   global $koneksi, $id_customer;
   $id = (int) ($_GET['id'] ?? 0);
   if ($id <= 0) pjBalas(422, ['status' => 'error', 'message' => 'ID tidak valid.']);

   $koneksi->begin_transaction();
   try {
      $st = $koneksi->prepare("SELECT 1 FROM visit_inspection WHERE id_inspection = ? AND id_customer = ? FOR UPDATE");
      $st->bind_param('ii', $id, $id_customer);
      $st->execute();
      $ada = (bool) $st->get_result()->fetch_row();
      $st->close();
      if (!$ada) {
         $koneksi->rollback();
         pjBalas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan atau sudah terhapus.']);
      }
      $st = $koneksi->prepare("DELETE FROM laboratorium_result WHERE id_inspection = ?");
      $st->bind_param('i', $id);
      $st->execute();
      $nHasil = $st->affected_rows;
      $st->close();
      $st = $koneksi->prepare("DELETE FROM visit_inspection WHERE id_inspection = ? AND id_customer = ?");
      $st->bind_param('ii', $id, $id_customer);
      $st->execute();
      $st->close();
      $koneksi->commit();
   } catch (Throwable $e) {
      $koneksi->rollback();
      throw $e;
   }
   pjBalas(200, ['status' => 'success', 'message' => 'Pemeriksaan dihapus' . ($nHasil > 0 ? " beserta $nHasil hasil." : '.')]);
}

function generateDoctorNumber($koneksi)
{
   $st = $koneksi->prepare("SELECT 1 FROM visit_inspection WHERE inspection_number = ? LIMIT 1");
   for ($i = 0; $i < 20; $i++) {
      $number = 'DCT-' . random_int(100000, 999999);
      $st->bind_param('s', $number);
      $st->execute();
      if (!$st->get_result()->fetch_row()) {
         $st->close();
         return $number;
      }
   }
   $st->close();
   throw new RuntimeException('Gagal membuat nomor pemeriksaan unik.');
}
