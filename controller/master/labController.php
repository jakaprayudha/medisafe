<?php
/**
 * Master pemeriksaan laboratorium (module/administrator/master-lab). Data global (bukan per faskes): khusus superadmin.
 *   GET [id=]                      -> daftar / detail
 *   POST aksi=simpan|status|hapus  -> tambah/ubah, aktif/nonaktif, hapus
 * Perbaikan: sebelumnya TANPA cek login/role dan semua query disisipi langsung (SQL injection). Juga:
 *  - kunjungan menyimpan NAMA pemeriksaan (visit_inspection.inspection_name) dan hasil dicari lewat nama itu, sehingga nama
 *    pemeriksaan yang sudah dipakai tidak boleh diganti / dihapus (riwayat hasil pasien akan hilang);
 *  - nama pemeriksaan harus unik; hapus master ikut membersihkan parameternya (dulu memakai kolom yang tidak ada);
 *  - tarif kini bisa diatur (dulu hanya dari database); pesan error SQL tidak lagi bocor.
 */
require_once __DIR__ . '/../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';
require_once __DIR__ . '/../../utility/activityLog.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
requireRole(['superadmin', 'administrator']);
$method = $_SERVER['REQUEST_METHOD'];
session_write_close();

function lbJson(string $status, string $message, $data = null, int $code = 200): void
{
   http_response_code($code);
   echo json_encode(['status' => $status, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
   exit;
}
function lbQ(mysqli $k, string $sql, string $t = '', array $a = []): array
{
   $st = $k->prepare($sql);
   if (!$st) {
      error_log('[labMaster] ' . $k->error);
      lbJson('error', 'Terjadi kesalahan server.', null, 500);
   }
   if ($t !== '') $st->bind_param($t, ...$a);
   if (!$st->execute()) {
      error_log('[labMaster] ' . $st->error);
      lbJson('error', 'Terjadi kesalahan server.', null, 500);
   }
   $r = $st->get_result();
   $out = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
   $st->close();
   return $out;
}
function lbExec(mysqli $k, string $sql, string $t, array $a): int
{
   $st = $k->prepare($sql);
   if (!$st || !$st->bind_param($t, ...$a) || !$st->execute()) {
      error_log('[labMaster] ' . ($st ? $st->error : $k->error));
      lbJson('error', 'Gagal menyimpan data.', null, 500);
   }
   $n = $st->affected_rows;
   $st->close();
   return $n;
}
/** Berapa kunjungan memakai pemeriksaan ini (berdasarkan nama). */
function lbDipakai(mysqli $k, string $assemen): int
{
   return (int) (lbQ($k, 'SELECT COUNT(*) n FROM visit_inspection WHERE inspection_name = ?', 's', [$assemen])[0]['n'] ?? 0);
}

/* ------------------------------ GET ------------------------------ */
if ($method === 'GET') {
   if (isset($_GET['id'])) {
      $row = lbQ($koneksi, 'SELECT id_lab, kode, assemen, tarif, status, template FROM laboratorium_detail WHERE id_lab = ? LIMIT 1', 'i', [(int) $_GET['id']])[0] ?? null;
      if (!$row) lbJson('error', 'Data tidak ditemukan.', null, 404);
      $row['dipakai'] = lbDipakai($koneksi, (string) $row['assemen']);
      lbJson('success', 'OK', $row);
   }
   $rows = lbQ($koneksi, 'SELECT id_lab, kode, assemen, tarif, status, template FROM laboratorium_detail ORDER BY status = 1 DESC, assemen ASC');
   $param = [];
   foreach (lbQ($koneksi, 'SELECT kode, COUNT(*) n FROM laboratorium_item GROUP BY kode') as $r) $param[mb_strtolower((string) $r['kode'])] = (int) $r['n'];
   $pakai = [];
   foreach (lbQ($koneksi, 'SELECT inspection_name, COUNT(*) n FROM visit_inspection GROUP BY inspection_name') as $r) $pakai[mb_strtolower((string) $r['inspection_name'])] = (int) $r['n'];
   foreach ($rows as &$r) {
      $r['id_lab'] = (int) $r['id_lab'];
      $r['tarif'] = (int) $r['tarif'];
      $r['status'] = (int) $r['status'];
      $r['n_param'] = $param[mb_strtolower((string) $r['kode'])] ?? 0;
      $r['dipakai'] = $pakai[mb_strtolower((string) $r['assemen'])] ?? 0;
   }
   unset($r);
   lbJson('success', 'OK', $rows);
}

if ($method !== 'POST') lbJson('error', 'Method tidak didukung.', null, 405);

$aksi = (string) ($_POST['aksi'] ?? 'simpan');
$id = (int) ($_POST['id_lab'] ?? 0);
$lbLama = $id > 0 ? logAmbilBaris($koneksi, 'laboratorium_detail', 'id_lab', $id) : null;
function lbLog(mysqli $k, int $id, ?array $lama, string $ket, ?string $aksi = null): void
{
   $r = $lama ?? logAmbilBaris($k, 'laboratorium_detail', 'id_lab', $id) ?? [];
   logBaris($k, 'master', 'laboratorium_detail', 'id_lab', $id, $lama, ['tipe' => 'lab', 'label' => trim(($r['kode'] ?? '') . ' · ' . ($r['assemen'] ?? ''), ' ·')], $ket, $aksi);
}

/* --------------------------- status ----------------------------- */
if ($aksi === 'status') {
   if ($id <= 0) lbJson('error', 'ID tidak valid.', null, 422);
   $s = (int) (($_POST['status'] ?? '0') === '1');
   lbExec($koneksi, 'UPDATE laboratorium_detail SET status = ? WHERE id_lab = ?', 'ii', [$s, $id]);
   if ($lbLama) lbLog($koneksi, $id, $lbLama, $s ? 'Pemeriksaan lab diaktifkan' : 'Pemeriksaan lab dinonaktifkan', $s ? 'aktifkan' : 'nonaktifkan');
   lbJson('success', $s ? 'Pemeriksaan diaktifkan.' : 'Pemeriksaan dinonaktifkan.');
}

/* ---------------------------- hapus ----------------------------- */
if ($aksi === 'hapus') {
   if ($id <= 0) lbJson('error', 'ID tidak valid.', null, 422);
   $row = lbQ($koneksi, 'SELECT kode, assemen FROM laboratorium_detail WHERE id_lab = ? LIMIT 1', 'i', [$id])[0] ?? null;
   if (!$row) lbJson('error', 'Data tidak ditemukan.', null, 404);
   $n = lbDipakai($koneksi, (string) $row['assemen']);
   if ($n > 0) lbJson('error', "Pemeriksaan \"{$row['assemen']}\" sudah dipakai di $n kunjungan sehingga tidak bisa dihapus. Nonaktifkan saja.", null, 409);
   $koneksi->begin_transaction();
   // parameter hanya dihapus bila tidak ada hasil pasien yang merujuknya
   $ada = (int) (lbQ($koneksi, 'SELECT COUNT(*) n FROM laboratorium_result lr JOIN laboratorium_item li ON li.id = lr.id_item WHERE li.kode = ?', 's', [$row['kode']])[0]['n'] ?? 0);
   if ($ada > 0) {
      $koneksi->rollback();
      lbJson('error', 'Parameter pemeriksaan ini sudah punya hasil pasien sehingga tidak bisa dihapus. Nonaktifkan saja.', null, 409);
   }
   $nParam = lbExec($koneksi, 'DELETE FROM laboratorium_item WHERE kode = ?', 's', [$row['kode']]);
   lbExec($koneksi, 'DELETE FROM laboratorium_detail WHERE id_lab = ?', 'i', [$id]);
   $koneksi->commit();
   lbLog($koneksi, $id, $lbLama, "Pemeriksaan lab dihapus (beserta $nParam parameter)");
   lbJson('success', 'Pemeriksaan dihapus.');
}

/* ------------------------ tambah / ubah ------------------------- */
$assemen = trim((string) ($_POST['assemen'] ?? ''));
$tarifIn = trim((string) ($_POST['tarif'] ?? ''));
if ($assemen === '') lbJson('error', 'Nama pemeriksaan wajib diisi.', ['field' => 'assemen'], 422);
if (mb_strlen($assemen) > 100) lbJson('error', 'Nama pemeriksaan maksimal 100 karakter.', ['field' => 'assemen'], 422);
if ($tarifIn !== '' && !preg_match('/^\d{1,9}$/', $tarifIn)) lbJson('error', 'Tarif harus berupa angka bulat (tanpa titik/koma).', ['field' => 'tarif'], 422);
$tarif = (int) $tarifIn;

$dup = lbQ($koneksi, 'SELECT id_lab FROM laboratorium_detail WHERE assemen = ? AND id_lab <> ? LIMIT 1', 'si', [$assemen, $id]);
if ($dup) lbJson('error', 'Nama pemeriksaan sudah ada.', ['field' => 'assemen'], 422);

if ($id > 0) {
   $row = lbQ($koneksi, 'SELECT assemen FROM laboratorium_detail WHERE id_lab = ? LIMIT 1', 'i', [$id])[0] ?? null;
   if (!$row) lbJson('error', 'Data tidak ditemukan.', null, 404);
   if (mb_strtolower((string) $row['assemen']) !== mb_strtolower($assemen) || (string) $row['assemen'] !== $assemen) {
      $n = lbDipakai($koneksi, (string) $row['assemen']);
      if ($n > 0) lbJson('error', "Nama tidak bisa diganti: pemeriksaan ini sudah dipakai di $n kunjungan dan hasilnya dicari berdasarkan nama. Anda masih bisa mengubah tarif atau menonaktifkannya.", ['field' => 'assemen'], 409);
   }
   lbExec($koneksi, 'UPDATE laboratorium_detail SET assemen = ?, tarif = ? WHERE id_lab = ?', 'sii', [$assemen, $tarif, $id]);
   lbLog($koneksi, $id, $lbLama, 'Pemeriksaan lab diubah');
   lbJson('success', 'Pemeriksaan diperbarui.');
}

for ($i = 0; $i < 5; $i++) {
   $kode = 'LAB-' . strtoupper(bin2hex(random_bytes(3)));
   if (!lbQ($koneksi, 'SELECT 1 FROM laboratorium_detail WHERE kode = ? LIMIT 1', 's', [$kode])) break;
}
lbExec($koneksi, 'INSERT INTO laboratorium_detail (kode, assemen, tarif, status) VALUES (?, ?, ?, 1)', 'ssi', [$kode, $assemen, $tarif]);
lbLog($koneksi, (int) $koneksi->insert_id, null, 'Pemeriksaan lab ditambahkan');
lbJson('success', 'Pemeriksaan ditambahkan. Lanjutkan dengan mengisi parameternya.', ['kode' => $kode]);
