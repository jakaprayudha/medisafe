<?php
/**
 * Penjaga akses data kunjungan: memastikan kunjungan milik faskes di session.
 * Dipakai endpoint yang menerima id kunjungan dari request (getDetailPemeriksaan, saveScreening, saveSignature,
 * updateVisitTime, deletePendaftaran, ...). Sebelumnya endpoint-endpoint itu bisa membaca / mengubah kunjungan
 * faskes mana pun cukup dengan mengganti id.
 *
 *   $kunj = visitGuard($koneksi, 'id_visit', $id);     // atau 'visit_ID'
 *
 * Mengembalikan baris ringkas kunjungan (id_visit, visit_ID, id_patient, visit_status, id_customer).
 * Bila tidak login -> 401, bila bukan milik faskes / tidak ada -> 404 (JSON) lalu exit.
 */
function visitGuard(mysqli $db, string $kolom, $nilai, bool $jsonStatusBool = false): array
{
   if (session_status() === PHP_SESSION_NONE) @session_start();
   $idc = (int) ($_SESSION['id_customer'] ?? 0);
   $tolak = function (int $kode, string $pesan) use ($jsonStatusBool) {
      http_response_code($kode);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['status' => $jsonStatusBool ? false : 'error', 'success' => false, 'message' => $pesan], JSON_UNESCAPED_UNICODE);
      exit;
   };
   if ($idc <= 0) $tolak(401, 'Session tidak valid / expired');
   if (!in_array($kolom, ['id_visit', 'visit_ID'], true)) $tolak(500, 'Kolom kunjungan tidak valid.');
   $nilai = trim((string) $nilai);
   if ($nilai === '') $tolak(400, 'ID kunjungan tidak ditemukan.');

   $st = $db->prepare("SELECT id_visit, visit_ID, id_patient, visit_status, id_customer FROM pasien_visit WHERE $kolom = ? AND id_customer = ? LIMIT 1");
   $tipe = $kolom === 'id_visit' ? 'ii' : 'si';
   $v = $kolom === 'id_visit' ? (int) $nilai : $nilai;
   $st->bind_param($tipe, $v, $idc);
   $st->execute();
   $row = $st->get_result()->fetch_assoc();
   $st->close();
   if (!$row) $tolak(404, 'Kunjungan tidak ditemukan di faskes ini.');
   return $row;
}

/**
 * Kunci kunjungan selesai (visit_status 3/4): ubah waktu, ganti dokter, hapus, batal pendaftaran, selesaikan ulang.
 * Hanya peran admin yang boleh membuka kunci; peran lain tetap terkunci.
 */
const VISIT_ROLE_BUKA_KUNCI = ['admin', 'superadmin', 'administrator'];

function bolehBukaKunciSelesai(): bool
{
   // session_write_close() tidak menghapus $_SESSION -> jangan buka ulang sesi bila sudah terbaca
   if (!isset($_SESSION) && session_status() === PHP_SESSION_NONE) @session_start();
   return in_array((string) ($_SESSION['roles'] ?? ''), VISIT_ROLE_BUKA_KUNCI, true);
}

function kunjunganSelesai($status): bool
{
   return in_array((int) $status, [3, 4], true);
}
