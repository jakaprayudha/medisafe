<?php
/**
 * Simpan form "Masuk & Keluar Pasien Rawat Inap" (tabel visit_ranap, 1 baris per kunjungan).
 *   POST JSON { visit_ID, tanggal_masuk, jam_masuk, ..., keadaan_keluar, cara_keluar } -> { status, message, lama_dirawat }
 * Perbaikan: semua isian disisipkan ke SQL (SQL injection) & tanpa cek login / batas faskes; tanggal/jam kosong dikirim
 * sebagai '' ke kolom DATE/TIME/INT -> query selalu gagal (tabel tidak pernah terisi); "Keadaan Pulang" tidak pernah
 * tersimpan (id kolom kondisi_pulang vs keadaan_keluar); nomor RM, ruang, diagnosa utama & dokter diambil dari kiriman
 * browser; tanpa validasi urutan tanggal; lama dirawat dari browser; pesan error SQL dikirim ke browser.
 */
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/visitGuard.php';
define('IO_RANAP_LIB', true);
require_once __DIR__ . '/getFormInap.php';
header('Content-Type: application/json; charset=utf-8');

function ioBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

const IO_KEADAAN = ['Sembuh', 'Membaik', 'Belum Sembuh', 'Lemah', 'Rujuk', 'Mati < 48 Jam', 'Mati > 48 Jam', 'Lainnya'];
const IO_CARA = ['Diizinkan Pulang', 'Pulang', 'Pulang Paksa', 'Lari', 'Pindah Ke FKTP Rawat Inap Lain', 'Dirujuk Ke RS', 'Lainnya'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') ioBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$in = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($in)) ioBalas(400, ['status' => 'error', 'message' => 'Data tidak valid.']);
$kunj = visitGuard($koneksi, 'visit_ID', (string) ($in['visit_ID'] ?? ''));
session_write_close();
if ((int) $kunj['visit_status'] === 99) ioBalas(409, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);

$tolak = fn($p, $f) => ioBalas(422, ['status' => 'error', 'message' => $p, 'field' => $f]);
$t = fn($k, $max) => mb_substr(trim((string) (is_scalar($in[$k] ?? null) ? $in[$k] : '')), 0, $max);
$tglOk = function ($k, $label) use ($t, $tolak) {
   $s = $t($k, 10);
   if ($s === '') return null;
   $d = DateTime::createFromFormat('!Y-m-d', $s);
   if (!$d || $d->format('Y-m-d') !== $s) $tolak("$label tidak valid.", $k);
   if ($s > date('Y-m-d')) $tolak("$label tidak boleh di masa depan.", $k);
   return $s;
};
$jamOk = function ($k, $label) use ($t, $tolak) {
   $s = $t($k, 8);
   if ($s === '') return null;
   if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $s)) $tolak("$label tidak valid (JJ:MM).", $k);
   return substr($s, 0, 5);
};

try {
   $src = ioAmbil($koneksi, (int) $kunj['id_visit']);
   $p = $src['pasien'];
   $awal = $src['awal'];

   $d = [
      'tanggal_masuk' => $tglOk('tanggal_masuk', 'Tanggal masuk'), 'jam_masuk' => $jamOk('jam_masuk', 'Jam masuk'),
      'tanggal_pindah' => $tglOk('tanggal_pindah', 'Tanggal pindah'), 'jam_pindah' => $jamOk('jam_pindah', 'Jam pindah'),
      'tanggal_keluar' => $tglOk('tanggal_keluar', 'Tanggal keluar'), 'jam_keluar' => $jamOk('jam_keluar', 'Jam keluar'),
   ];
   if (!$d['tanggal_masuk']) $tolak('Tanggal masuk wajib diisi.', 'tanggal_masuk');
   if ($d['tanggal_pindah'] && $d['tanggal_pindah'] < $d['tanggal_masuk']) $tolak('Tanggal pindah tidak boleh sebelum tanggal masuk.', 'tanggal_pindah');
   if ($d['tanggal_keluar']) {
      if ($d['tanggal_keluar'] < $d['tanggal_masuk']) $tolak('Tanggal keluar tidak boleh sebelum tanggal masuk.', 'tanggal_keluar');
      if ($d['tanggal_pindah'] && $d['tanggal_keluar'] < $d['tanggal_pindah']) $tolak('Tanggal keluar tidak boleh sebelum tanggal pindah.', 'tanggal_keluar');
      if ($d['tanggal_keluar'] === $d['tanggal_masuk'] && $d['jam_masuk'] && $d['jam_keluar'] && $d['jam_keluar'] < $d['jam_masuk']) $tolak('Jam keluar tidak boleh sebelum jam masuk.', 'jam_keluar');
   }
   foreach (['status_perkawinan' => 20, 'penanggung_jawab' => 255, 'alamat_pj' => 2000, 'diagnosa_medik' => 10000, 'diagnosa_komplikasi' => 10000,
      'penyebab_keracunan' => 5000, 'nama_operasi' => 5000, 'infeksi_nosokomial' => 10, 'penyebab_infeksi' => 2000, 'alergi' => 2000,
      'radioterapi' => 2000, 'imunisasi' => 2000, 'transfusi' => 2000, 'keadaan_keluar' => 50, 'cara_keluar' => 50] as $k => $max) {
      $d[$k] = $t($k, $max);
   }
   if (!in_array($d['infeksi_nosokomial'], ['', 'Ya', 'Tidak'], true)) $tolak('Pilihan infeksi nosokomial tidak valid.', 'infeksi_nosokomial');
   if ($d['infeksi_nosokomial'] === 'Ya' && $d['penyebab_infeksi'] === '') $tolak('Isi penyebab infeksi nosokomial.', 'penyebab_infeksi');
   if ($d['keadaan_keluar'] !== '' && !in_array($d['keadaan_keluar'], IO_KEADAAN, true)) $tolak('Pilihan keadaan keluar tidak valid.', 'keadaan_keluar');
   if ($d['cara_keluar'] !== '' && !in_array($d['cara_keluar'], IO_CARA, true)) $tolak('Pilihan cara keluar tidak valid.', 'cara_keluar');
   // lama dirawat dihitung di server: selisih hari keluar - masuk, minimal 1 hari
   $d['lama_dirawat'] = $d['tanggal_keluar'] ? max(1, (int) (new DateTime($d['tanggal_masuk']))->diff(new DateTime($d['tanggal_keluar']))->days) : null;
   // dari data kunjungan (read-only di form)
   $d['ruang_rawat'] = mb_substr($awal['ruang_rawat'], 0, 100);
   $d['diagnosa_utama'] = $awal['diagnosa_utama'];
   $d['dokter_merawat'] = mb_substr($awal['dokter_merawat'], 0, 255);

   $no = (string) $kunj['visit_ID'];
   $snap = [
      'nomor_rm' => mb_substr($p['nomor_rm'], 0, 20), 'nama_pasien' => $p['nama_pasien'], 'tanggal_lahir' => $p['tanggal_lahir'] ?: null,
      'agama' => mb_substr($p['agama'], 0, 50), 'pendidikan' => mb_substr($p['pendidikan'], 0, 100), 'pekerjaan' => mb_substr($p['pekerjaan'], 0, 100),
      'nomor_bpjs' => mb_substr($p['nomor_bpjs'], 0, 50), 'alamat' => $p['alamat'],
   ];
   $kolom = array_merge($snap, $d);

   $koneksi->begin_transaction();
   try {
      $st = $koneksi->prepare("SELECT id_inap FROM visit_ranap WHERE visit_ID = ? ORDER BY id_inap DESC LIMIT 1 FOR UPDATE");
      $st->bind_param('s', $no);
      $st->execute();
      $id = (int) ($st->get_result()->fetch_row()[0] ?? 0);
      $st->close();
      $nama = array_keys($kolom);
      $nilai = array_values($kolom);
      if ($id) {
         $sql = 'UPDATE visit_ranap SET ' . implode(', ', array_map(fn($k) => "`$k` = ?", $nama)) . ' WHERE id_inap = ?';
         $nilai[] = $id;
         $tipe = str_repeat('s', count($kolom)) . 'i';
      } else {
         $nama[] = 'visit_ID';
         $nilai[] = $no;
         $sql = 'INSERT INTO visit_ranap (`' . implode('`, `', $nama) . '`) VALUES (' . implode(', ', array_fill(0, count($nama), '?')) . ')';
         $tipe = str_repeat('s', count($nama));
      }
      $st = $koneksi->prepare($sql);
      $st->bind_param($tipe, ...$nilai);
      $st->execute();
      $st->close();
      $koneksi->commit();
   } catch (Throwable $e) {
      $koneksi->rollback();
      throw $e;
   }
   ioBalas(200, ['status' => 'success', 'message' => $id ? 'Data rawat inap diperbarui.' : 'Data rawat inap disimpan.', 'lama_dirawat' => $d['lama_dirawat']]);
} catch (Throwable $e) {
   error_log('[ranap/saveInap] ' . $e->getMessage());
   ioBalas(500, ['status' => 'error', 'message' => 'Terjadi kesalahan pada server.']);
}
