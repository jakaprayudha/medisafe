<?php
/**
 * Helper bersama Catatan Pemberian Obat (CPO) — dipakai cpoController (tambah), updateCPO, deleteCpo, getCpo.
 */

function cpoBalas(int $kode, array $isi): void
{
   http_response_code($kode);
   header('Content-Type: application/json; charset=utf-8');
   echo json_encode($isi, JSON_UNESCAPED_UNICODE);
   exit;
}

/** id_customer sesi, atau balas 401. Sesi langsung ditutup supaya request paralel tidak saling menunggu. */
function cpoCustomer(): int
{
   if (session_status() === PHP_SESSION_NONE) @session_start();
   $idc = (int) ($_SESSION['id_customer'] ?? 0);
   session_write_close();
   if ($idc <= 0) cpoBalas(401, ['status' => 'error', 'message' => 'Sesi berakhir, silakan login ulang.']);
   return $idc;
}

/** Petugas yang boleh dipilih (sama dengan daftar di halaman): id_user => true */
function cpoPetugasValid(mysqli $koneksi, int $idc): array
{
   $st = $koneksi->prepare("SELECT id_user FROM ms_users WHERE id_customer = ? AND roles IN ('dokter','bidan','perawat','apoteker')");
   $st->bind_param('i', $idc);
   $st->execute();
   $ok = [];
   foreach ($st->get_result()->fetch_all(MYSQLI_NUM) as $r) $ok[(string) $r[0]] = true;
   $st->close();
   return $ok;
}

/**
 * Validasi 1 baris CPO. Kembalikan [data, null] atau [null, pesan error].
 * Jam kosong disimpan '' (kolom varchar; data lama juga memakai '').
 */
function cpoBaris(array $in, array $petugasValid, string $label = ''): array
{
   $t = fn($k, $max) => mb_substr(trim((string) (is_scalar($in[$k] ?? null) ? $in[$k] : '')), 0, $max);
   $d = [
      'tanggal' => $t('tanggal', 10),
      'nama_item' => $t('nama_obat', 255),
      'dosis' => $t('dosis', 100),
      'signature' => $t('signature', 100),
      'jam_pagi' => $t('jam_pagi', 8),
      'jam_siang' => $t('jam_siang', 8),
      'jam_sore' => $t('jam_sore', 8),
      'jam_malam' => $t('jam_malam', 8),
      'petugas' => $t('petugas', 20),
   ];
   $p = $label !== '' ? "$label: " : '';
   if ($d['nama_item'] === '') return [null, $p . 'nama obat wajib diisi.'];
   $tgl = DateTime::createFromFormat('!Y-m-d', $d['tanggal']);
   if (!$tgl || $tgl->format('Y-m-d') !== $d['tanggal']) return [null, $p . 'tanggal tidak valid.'];
   if ($tgl > new DateTime('tomorrow')) return [null, $p . 'tanggal tidak boleh di masa depan.'];
   // jam & petugas opsional: ±15% data lama dicatat dulu sebagai rencana lalu jamnya diisi belakangan
   foreach (['jam_pagi' => 'pagi', 'jam_siang' => 'siang', 'jam_sore' => 'sore', 'jam_malam' => 'malam'] as $k => $nm) {
      if ($d[$k] === '') continue;
      if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $d[$k])) return [null, $p . "jam $nm tidak valid."];
      $d[$k] = substr($d[$k], 0, 5);
   }
   if ($d['petugas'] !== '' && !isset($petugasValid[$d['petugas']])) return [null, $p . 'petugas tidak dikenal untuk faskes ini.'];
   return [$d, null];
}
