<?php
/**
 * Daftar CPO satu kunjungan. GET ?visit_ID=<visit_ID> (atau ?no=).
 * Perbaikan: SELECT * ikut JOIN ms_users -> hash password & data akun petugas terkirim ke browser, dan kolom
 * created_at/status CPO tertimpa kolom ms_users.
 */
include '../../database/connect.php';
require_once __DIR__ . '/cpoService.php';

$idc = cpoCustomer();
$visit = trim((string) ($_GET['visit_ID'] ?? $_GET['no'] ?? ''));
if ($visit === '') cpoBalas(422, ['status' => 'error', 'message' => 'Nomor kunjungan kosong.']);

$st = $koneksi->prepare("SELECT c.id, c.tanggal, c.nama_item, c.dosis, c.signature, c.jam_pagi, c.jam_siang, c.jam_sore, c.jam_malam,
      c.petugas, c.created_at, u.fullname AS petugas_nama, u.roles AS petugas_role
   FROM pasien_cpo c
   LEFT JOIN ms_users u ON u.id_user = c.petugas AND u.id_customer = c.id_customer
   WHERE c.visit_ID = ? AND c.id_customer = ?
   ORDER BY c.tanggal ASC, c.id ASC");
$st->bind_param('si', $visit, $idc);
$st->execute();
$data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

cpoBalas(200, ['status' => 'success', 'data' => $data]);
