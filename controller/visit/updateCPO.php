<?php
/**
 * Ubah 1 baris CPO. POST id + field (tanggal, nama_obat, dosis, signature, jam_*, id_user/petugas).
 * Perbaikan: tanpa cek sesi & faskes -> baris CPO faskes lain bisa diubah lewat id; tanpa validasi; selalu "success".
 */
include '../../database/connect.php';
require_once __DIR__ . '/cpoService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') cpoBalas(405, ['status' => 'error', 'message' => 'Method tidak diizinkan.']);
$idc = cpoCustomer();
$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) cpoBalas(422, ['status' => 'error', 'message' => 'ID tidak valid.']);

try {
   $st = $koneksi->prepare("SELECT petugas FROM pasien_cpo WHERE id = ? AND id_customer = ?");
   $st->bind_param('ii', $id, $idc);
   $st->execute();
   $lama = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$lama) cpoBalas(404, ['status' => 'error', 'message' => 'Data CPO tidak ditemukan.']);

   $in = $_POST;
   $in['petugas'] = $_POST['petugas'] ?? $_POST['id_user'] ?? '';
   $valid = cpoPetugasValid($koneksi, $idc);
   if ((string) $lama['petugas'] !== '') $valid[(string) $lama['petugas']] = true;   // petugas lama tetap boleh walau perannya berubah
   [$d, $err] = cpoBaris($in, $valid);
   if ($err) cpoBalas(422, ['status' => 'error', 'message' => ucfirst($err)]);

   $st = $koneksi->prepare("UPDATE pasien_cpo SET tanggal = ?, nama_item = ?, dosis = ?, signature = ?, jam_pagi = ?, jam_siang = ?,
         jam_sore = ?, jam_malam = ?, petugas = ?
      WHERE id = ? AND id_customer = ?");
   $st->bind_param('sssssssssii', $d['tanggal'], $d['nama_item'], $d['dosis'], $d['signature'], $d['jam_pagi'], $d['jam_siang'],
      $d['jam_sore'], $d['jam_malam'], $d['petugas'], $id, $idc);
   $st->execute();
   $st->close();
} catch (Throwable $e) {
   error_log('[visit/updateCPO] ' . $e->getMessage());
   cpoBalas(500, ['status' => 'error', 'message' => 'Gagal memperbarui CPO.']);
}
cpoBalas(200, ['status' => 'success', 'message' => 'Data CPO diperbarui.']);
