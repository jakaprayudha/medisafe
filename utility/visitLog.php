<?php
/**
 * Log aktivitas kunjungan pasien -> tabel log_kunjungan (migrations/20261128_log_kunjungan.sql).
 *
 *   $snap = logKunjunganSnapshot($koneksi, 'id_visit', $id);        // ambil data kunjungan (wajib SEBELUM hapus)
 *   logKunjungan($koneksi, 'hapus', $snap, 'Dihapus dari registrasi poli');
 *   logKunjungan($koneksi, 'ubah_waktu', $visitID, '', ['jam' => $lama], ['jam' => $baru]);
 *
 * Pencatatan tidak pernah menggagalkan proses utama (semua error hanya masuk error_log).
 * Panggil SETELAH commit transaksi: pembuatan tabel otomatis (CREATE TABLE) memicu implicit commit di MySQL.
 * User diambil dari session; tanpa session (webservice BPJS / Mobile JKN) tercatat sebagai SISTEM.
 */

const LOG_KUNJUNGAN_AKSI = [
   'daftar'        => 'Pendaftaran',
   'ubah'          => 'Ubah data',
   'ubah_waktu'    => 'Ubah waktu',
   'ganti_dokter'  => 'Ganti dokter',
   'checkin'       => 'Check-in',
   'selesai'       => 'Selesai pemeriksaan',
   'ranap_masuk'   => 'Masuk rawat inap',
   'ranap_batal'   => 'Batal rawat inap',
   'pulang'        => 'Pulang rawat inap',
   'pindah_bed'    => 'Pindah bed',
   'ranap_aktif'   => 'Rawat inap diaktifkan',
   'batal'         => 'Batal pendaftaran',
   'tidak_hadir'   => 'Tidak hadir',
   'aktif_kembali' => 'Aktifkan kembali',
   'hapus'         => 'Hapus kunjungan',
   'bayar'         => 'Pembayaran',
];

/** Buat tabel log bila belum ada. Jangan dipanggil di dalam transaksi. */
function logKunjunganSiapkan(mysqli $db): bool
{
   static $siap = false;
   if ($siap) return true;
   try {
      $sql = file_get_contents(__DIR__ . '/../migrations/20261128_log_kunjungan.sql');
      if ($sql === false) return false;
      $sql = trim(preg_replace('/^\s*--.*$/m', '', $sql));
      $db->query(rtrim($sql, "; \n"));
      return $siap = true;
   } catch (Throwable $e) {
      error_log('[logKunjungan] gagal membuat tabel: ' . $e->getMessage());
      return false;
   }
}

/** Data ringkas kunjungan untuk log. $kolom: id_visit | visit_ID. */
function logKunjunganSnapshot(mysqli $db, string $kolom, $nilai, ?int $idc = null): ?array
{
   if (!in_array($kolom, ['id_visit', 'visit_ID'], true) || trim((string) $nilai) === '') return null;
   try {
      $sql = "SELECT v.id_visit, v.visit_ID, v.id_patient, v.id_customer, v.visit_date, v.visit_time, v.id_poli, v.id_doctor,
            v.id_provider, v.visit_status, v.source_hub, v.created_user, v.status_rawatinap, v.visit_notes, v.code_doctor,
            COALESCE(NULLIF(p.patient_name, ''), v.patient_name_pcare) AS nama_pasien, p.nomor_rm
         FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
         WHERE v.$kolom = ?" . ($idc ? ' AND v.id_customer = ?' : '') . ' LIMIT 1';
      $st = $db->prepare($sql);
      if (!$st) return null;
      $v = $kolom === 'id_visit' ? (int) $nilai : (string) $nilai;
      $tipe = $kolom === 'id_visit' ? 'i' : 's';
      if ($idc) $st->bind_param($tipe . 'i', $v, $idc);
      else $st->bind_param($tipe, $v);
      $st->execute();
      $row = $st->get_result()->fetch_assoc();
      $st->close();
      return $row ?: null;
   } catch (Throwable $e) {
      error_log('[logKunjungan] snapshot: ' . $e->getMessage());
      return null;
   }
}

/**
 * Catat satu aktivitas kunjungan.
 * $kunj: hasil logKunjunganSnapshot()/visitGuard() (array) atau visit_ID (string) atau id_visit (int).
 */
function logKunjungan(mysqli $db, string $aksi, $kunj, string $keterangan = '', ?array $lama = null, ?array $baru = null, ?string $sumber = null): void
{
   try {
      if (is_int($kunj)) $kunj = logKunjunganSnapshot($db, 'id_visit', $kunj) ?? ['id_visit' => $kunj];
      elseif (is_string($kunj)) $kunj = logKunjunganSnapshot($db, 'visit_ID', $kunj) ?? ['visit_ID' => $kunj];
      elseif (!is_array($kunj)) $kunj = [];
      // lengkapi nama / pasien bila yang dikirim hanya baris ringkas (mis. dari visitGuard)
      if (!isset($kunj['nama_pasien']) && (!empty($kunj['id_visit']) || !empty($kunj['visit_ID']))) {
         $lengkap = !empty($kunj['id_visit']) ? logKunjunganSnapshot($db, 'id_visit', $kunj['id_visit']) : logKunjunganSnapshot($db, 'visit_ID', $kunj['visit_ID']);
         if ($lengkap) $kunj = array_merge($lengkap, array_filter($kunj, fn($x) => $x !== null && $x !== ''));
      }

      if (session_status() === PHP_SESSION_NONE && !isset($_SESSION) && !headers_sent()) @session_start();
      $s = $_SESSION ?? [];
      $idc = (int) ($kunj['id_customer'] ?? $s['id_customer'] ?? 0);
      if ($idc <= 0) return;
      $uid = isset($s['uid_user']) ? (string) $s['uid_user'] : null;
      $username = (string) ($s['username'] ?? '') ?: ($uid ? null : 'SISTEM');
      $fullname = isset($s['fullname']) ? (string) $s['fullname'] : null;
      $roles = isset($s['roles']) ? (string) $s['roles'] : null;
      $ip = mb_substr((string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null;
      $json = fn(?array $a) => $a === null ? null : json_encode($a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);

      $vid = isset($kunj['visit_ID']) ? (string) $kunj['visit_ID'] : null;
      $idv = isset($kunj['id_visit']) ? (int) $kunj['id_visit'] : null;
      $idp = isset($kunj['id_patient']) ? mb_substr((string) $kunj['id_patient'], 0, 50) : null;
      $nama = isset($kunj['nama_pasien']) ? mb_substr((string) $kunj['nama_pasien'], 0, 150) : null;
      $aksi = mb_substr($aksi, 0, 30);
      $sumber = $sumber !== null ? mb_substr($sumber, 0, 50) : null;
      $ket = $keterangan !== '' ? $keterangan : null;
      $dl = $json($lama);
      $db2 = $json($baru);

      $sql = "INSERT INTO log_kunjungan (id_customer, visit_ID, id_visit, id_patient, nama_pasien, aksi, sumber, keterangan, data_lama, data_baru,
            uid_user, username, fullname, roles, ip, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
      $waktu = date('Y-m-d H:i:s'); // zona waktu PHP (Asia/Jakarta), sama dengan filter tanggal di menu log
      $tulis = function () use ($db, $sql, $idc, $vid, $idv, $idp, $nama, $aksi, $sumber, $ket, $dl, $db2, $uid, $username, $fullname, $roles, $ip, $waktu) {
         $st = $db->prepare($sql);
         if (!$st) throw new RuntimeException($db->error, $db->errno); // mode mysqli tanpa exception
         $st->bind_param('isisssssssssssss', $idc, $vid, $idv, $idp, $nama, $aksi, $sumber, $ket, $dl, $db2, $uid, $username, $fullname, $roles, $ip, $waktu);
         if (!$st->execute()) throw new RuntimeException($st->error, $st->errno);
         $st->close();
      };
      try {
         $tulis();
      } catch (Throwable $e) {
         if ((int) $e->getCode() !== 1146 || !logKunjunganSiapkan($db)) throw $e; // 1146 = tabel belum ada
         $tulis();
      }
   } catch (Throwable $e) {
      error_log('[logKunjungan] ' . $aksi . ' ' . ($kunj['visit_ID'] ?? '') . ': ' . $e->getMessage());
   }
}
