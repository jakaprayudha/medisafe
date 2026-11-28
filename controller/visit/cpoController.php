<?php
/**
 * Tambah baris Catatan Pemberian Obat (module/admin/cpo). POST form: nomor_visit + array tanggal[], nama_obat[], dosis[],
 * signature[], jam_pagi[], jam_siang[], jam_sore[], jam_malam[], petugas[].
 * Perbaikan: baris tidak divalidasi (tanggal/jam sembarang, petugas faskes lain), sebagian tersimpan bila ada yang gagal,
 * tetap "success" walau tidak ada baris yang disimpan.
 */
include '../../database/connect.php';
require_once __DIR__ . '/cpoService.php';
require_once __DIR__ . '/../../utility/activityLog.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') cpoBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$idc = cpoCustomer();
$visit = trim((string) ($_POST['nomor_visit'] ?? ''));
if ($visit === '') cpoBalas(422, ['status' => 'error', 'message' => 'Nomor kunjungan tidak ditemukan.']);

try {
   $st = $koneksi->prepare("SELECT visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('si', $visit, $idc);
   $st->execute();
   $kunj = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$kunj) cpoBalas(404, ['status' => 'error', 'message' => 'Kunjungan tidak ditemukan untuk faskes ini.']);
   if ((int) $kunj['visit_status'] === 99) cpoBalas(422, ['status' => 'error', 'message' => 'Kunjungan sudah dibatalkan.']);

   $kol = ['tanggal', 'nama_obat', 'dosis', 'signature', 'jam_pagi', 'jam_siang', 'jam_sore', 'jam_malam', 'petugas'];
   $arr = [];
   foreach ($kol as $k) $arr[$k] = is_array($_POST[$k] ?? null) ? array_values($_POST[$k]) : [];
   $n = count($arr['nama_obat']);
   if ($n > 200) cpoBalas(422, ['status' => 'error', 'message' => 'Maksimal 200 baris sekali simpan.']);

   $petugasValid = cpoPetugasValid($koneksi, $idc);
   $baris = [];
   for ($i = 0; $i < $n; $i++) {
      $in = [];
      foreach ($kol as $k) $in[$k] = $arr[$k][$i] ?? '';
      if (trim(implode('', array_map(fn($v) => is_scalar($v) ? (string) $v : '', [$in['nama_obat'], $in['dosis'], $in['signature']]))) === '') continue;   // baris kosong dilewati
      [$d, $err] = cpoBaris($in, $petugasValid, 'Baris ' . ($i + 1));
      if ($err) cpoBalas(422, ['status' => 'error', 'message' => $err, 'baris' => $i]);
      $baris[] = $d;
   }
   if (!$baris) cpoBalas(422, ['status' => 'error', 'message' => 'Belum ada obat yang diisi.']);

   $koneksi->begin_transaction();
   $st = $koneksi->prepare("INSERT INTO pasien_cpo
      (visit_ID, id_customer, tanggal, nama_item, dosis, signature, jam_pagi, jam_siang, jam_sore, jam_malam, petugas)
      VALUES (?,?,?,?,?,?,?,?,?,?,?)");
   foreach ($baris as $d) {
      $st->bind_param('sisssssssss', $visit, $idc, $d['tanggal'], $d['nama_item'], $d['dosis'], $d['signature'],
         $d['jam_pagi'], $d['jam_siang'], $d['jam_sore'], $d['jam_malam'], $d['petugas']);
      $st->execute();
   }
   $st->close();
   $koneksi->commit();
} catch (Throwable $e) {
   try { $koneksi->rollback(); } catch (Throwable $x) {}
   error_log('[visit/cpoController] ' . $e->getMessage());
   cpoBalas(500, ['status' => 'error', 'message' => 'Gagal menyimpan CPO.']);
}
logAktivitas($koneksi, 'klinis', 'tambah', ['tipe' => 'cpo', 'label' => 'CPO ' . count($baris) . ' obat', 'visit_ID' => $visit],
   'Catatan pemberian obat: ' . implode(', ', array_map(fn($d) => $d['nama_item'] . ' ' . $d['dosis'], $baris)), null, ['baris' => $baris]);
cpoBalas(200, ['status' => 'success', 'message' => count($baris) . ' baris CPO tersimpan.', 'total' => count($baris)]);
