<?php
/**
 * Permintaan lab per kunjungan (module/lab/permintaan-lab-detail).
 *
 * GET    ?no=<visit_ID>            -> { status, data:[...pemeriksaan + harus/isi/status...], ringkas }
 * GET    ?no=..&id=<id_inspection> -> { status, data:{...} }
 * POST   (form) tanpa id_inspection -> tambah; dengan id_inspection -> ubah
 * DELETE ?id=<id_inspection>        -> hapus pemeriksaan + hasilnya
 *
 * Semua dibatasi faskes yang login. Perbaikan dari versi lama:
 *  - tambah: id_visit tidak dicek milik faskes; ubah: id_visit bisa dipindah ke kunjungan mana pun
 *  - hapus: hasil (laboratorium_result) tertinggal jadi yatim (±2 rb baris di prod)
 *  - ganti jenis pemeriksaan padahal hasil sudah diisi -> hasil lama tak cocok lagi dengan parameternya
 *  - session_start() ganda (Notice sebelum JSON), pesan error SQL dikirim ke browser
 */
require '../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');

function balas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

$idc = (string) ($_SESSION['id_customer'] ?? '');
session_write_close();
if ($idc === '') balas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);

function ambilSatu(mysqli $k, int $id, string $idc): ?array
{
   $st = $k->prepare("SELECT vi.*, (SELECT COUNT(*) FROM laboratorium_result lr WHERE lr.id_inspection = vi.id_inspection AND TRIM(COALESCE(lr.hasil, '')) <> '') AS isi
      FROM visit_inspection vi WHERE vi.id_inspection = ? AND vi.id_customer = ? LIMIT 1");
   $st->bind_param('is', $id, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function bacaForm(): array
{
   $nama = trim((string) ($_POST['inspection_name'] ?? ''));
   $tgl = trim((string) ($_POST['inspection_date'] ?? ''));
   $sumber = trim((string) ($_POST['inspection_source'] ?? ''));
   // form lama mengirim "inspection_summary" (tidak pernah tersimpan)
   $catatan = trim((string) ($_POST['inspection_note'] ?? $_POST['inspection_summary'] ?? ''));
   if ($nama === '' || mb_strlen($nama) > 255) balas(422, ['status' => 'error', 'message' => 'Nama pemeriksaan wajib diisi (maks 255 karakter).']);
   $d = DateTime::createFromFormat('!Y-m-d', $tgl);
   if (!$d || $d->format('Y-m-d') !== $tgl) balas(422, ['status' => 'error', 'message' => 'Tanggal pemeriksaan tidak valid.']);
   if ($sumber === '') $sumber = 'Lab Klinik';
   return [$nama, $tgl, mb_substr($sumber, 0, 255), mb_substr($catatan, 0, 5000)];
}

try {
   switch ($_SERVER['REQUEST_METHOD']) {
      case 'GET':
         if (isset($_GET['id'])) {
            $r = ambilSatu($koneksi, (int) $_GET['id'], $idc);
            if (!$r) balas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
            balas(200, ['status' => 'success', 'data' => $r]);
         }
         $no = trim((string) ($_GET['no'] ?? ''));
         if ($no === '') balas(422, ['status' => 'error', 'message' => 'Nomor kunjungan tidak valid.']);
         $st = $koneksi->prepare("SELECT vi.id_inspection, vi.id_visit, vi.inspection_number, vi.inspection_name, vi.inspection_date,
               vi.inspection_source, vi.inspection_note, vi.created_at,
               COALESCE(e.n, 0) AS harus, COALESCE(r.isi, 0) AS isi, r.terakhir
            FROM visit_inspection vi
            LEFT JOIN (SELECT ld.assemen, COUNT(DISTINCT li.id) n FROM laboratorium_detail ld JOIN laboratorium_item li ON li.kode = ld.kode GROUP BY ld.assemen) e
               ON e.assemen = vi.inspection_name
            LEFT JOIN (SELECT lr.id_inspection, COUNT(DISTINCT CASE WHEN TRIM(COALESCE(lr.hasil, '')) <> '' THEN lr.id_item END) isi, MAX(lr.created_at) terakhir
                  FROM laboratorium_result lr
                  WHERE lr.id_inspection IN (SELECT x.id_inspection FROM visit_inspection x WHERE x.id_visit = ? AND x.id_customer = ?)
                  GROUP BY lr.id_inspection) r
               ON r.id_inspection = vi.id_inspection
            WHERE vi.id_visit = ? AND vi.id_customer = ?
            ORDER BY vi.id_inspection ASC");
         $st->bind_param('ssss', $no, $idc, $no, $idc);
         $st->execute();
         $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
         $st->close();
         $ringkas = ['jumlah' => 0, 'ada_hasil' => 0, 'belum' => 0];
         foreach ($data as &$d) {
            $d['id_inspection'] = (int) $d['id_inspection'];
            $d['harus'] = (int) $d['harus'];
            $d['isi'] = (int) $d['isi'];
            // sama dengan permintaanLabList: ada hasil tersimpan = selesai (x/y info saja)
            $d['status'] = $d['isi'] > 0 ? 'lengkap' : 'belum';
            $ringkas['jumlah']++;
            $ringkas[$d['isi'] > 0 ? 'ada_hasil' : 'belum']++;
         }
         unset($d);
         balas(200, ['status' => 'success', 'data' => $data, 'ringkas' => $ringkas]);

      case 'POST':
         [$nama, $tgl, $sumber, $catatan] = bacaForm();
         $id = (int) ($_POST['id_inspection'] ?? 0);
         if ($id > 0) {
            $lama = ambilSatu($koneksi, $id, $idc);
            if (!$lama) balas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
            if ((int) $lama['isi'] > 0 && $lama['inspection_name'] !== $nama) {
               balas(409, ['status' => 'error', 'message' => 'Hasil "' . $lama['inspection_name'] . '" sudah diisi, jenis pemeriksaan tidak bisa diganti. Tambahkan pemeriksaan baru.']);
            }
            $st = $koneksi->prepare("UPDATE visit_inspection SET inspection_name = ?, inspection_date = ?, inspection_source = ?, inspection_note = ?
               WHERE id_inspection = ? AND id_customer = ?");
            $st->bind_param('ssssis', $nama, $tgl, $sumber, $catatan, $id, $idc);
            $st->execute();
            $st->close();
            balas(200, ['status' => 'success', 'message' => 'Pemeriksaan diperbarui.']);
         }
         $visit = trim((string) ($_POST['id_visit'] ?? ''));
         $st = $koneksi->prepare("SELECT 1 FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
         $st->bind_param('ss', $visit, $idc);
         $st->execute();
         $ada = (bool) $st->get_result()->fetch_row();
         $st->close();
         if ($visit === '' || !$ada) balas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
         $nomor = nomorBaru($koneksi);
         $st = $koneksi->prepare("INSERT INTO visit_inspection (inspection_number, id_customer, id_visit, inspection_name, inspection_date, inspection_source, inspection_note)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
         $st->bind_param('sssssss', $nomor, $idc, $visit, $nama, $tgl, $sumber, $catatan);
         $st->execute();
         $baru = (int) $koneksi->insert_id;
         $st->close();
         balas(200, ['status' => 'success', 'message' => 'Pemeriksaan ditambahkan.', 'id_inspection' => $baru]);

      case 'DELETE':
         $id = (int) ($_GET['id'] ?? 0);
         if ($id <= 0 || !ambilSatu($koneksi, $id, $idc)) balas(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
         $koneksi->begin_transaction();
         $st = $koneksi->prepare("DELETE FROM laboratorium_result WHERE id_inspection = ?");
         $st->bind_param('i', $id);
         $st->execute();
         $st->close();
         $st = $koneksi->prepare("DELETE FROM visit_inspection WHERE id_inspection = ? AND id_customer = ?");
         $st->bind_param('is', $id, $idc);
         $st->execute();
         $st->close();
         $koneksi->commit();
         balas(200, ['status' => 'success', 'message' => 'Pemeriksaan dan hasilnya dihapus.']);

      default:
         balas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
   }
} catch (Throwable $e) {
   try { $koneksi->rollback(); } catch (Throwable $x) {}
   error_log('[visit/penunjangLabRoles] ' . $e->getMessage());
   balas(500, ['status' => 'error', 'message' => 'Gagal memproses permintaan lab.']);
}

function nomorBaru(mysqli $k): string
{
   $st = $k->prepare("SELECT 1 FROM visit_inspection WHERE inspection_number = ? LIMIT 1");
   do {
      $nomor = 'DCT-' . random_int(100000, 999999);
      $st->bind_param('s', $nomor);
      $st->execute();
      $ada = (bool) $st->get_result()->fetch_row();
   } while ($ada);
   $st->close();
   return $nomor;
}
