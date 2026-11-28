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
 * Saat admin sedang switch user, pelaku asli ikut dicatat (kolom pelaku_asli).
 */
require_once __DIR__ . '/activityLog.php';

const LOG_KUNJUNGAN_AKSI = [
   'daftar'        => 'Pendaftaran',
   'ubah'          => 'Ubah data',
   'ubah_waktu'    => 'Ubah waktu',
   'ganti_dokter'  => 'Ganti dokter',
   'checkin'       => 'Check-in',
   'panggil'       => 'Panggil pasien',
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

/** Perubahan skema untuk tabel versi lama (lihat migrations/20261128b_log_kunjungan_pelaku_asli.sql). */
const LOG_KUNJUNGAN_UPGRADE = ["ALTER TABLE log_kunjungan ADD COLUMN pelaku_asli VARCHAR(150) NULL AFTER roles"];

/** Buat tabel log bila belum ada. Jangan dipanggil di dalam transaksi. */
function logKunjunganSiapkan(mysqli $db): bool
{
   return logBuatTabel($db, '20261128_log_kunjungan.sql');
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

      $p = logPelaku();
      $idc = (int) ($kunj['id_customer'] ?? $p['id_customer'] ?? 0);
      if ($idc <= 0) return;
      $cut = fn($v, int $n) => $v === null || $v === '' ? null : mb_substr((string) $v, 0, $n);
      logTulis($db, "INSERT INTO log_kunjungan (id_customer, visit_ID, id_visit, id_patient, nama_pasien, aksi, sumber, keterangan, data_lama, data_baru,
            uid_user, username, fullname, roles, pelaku_asli, ip, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
         'isissssssssssssss', [
            $idc, $cut($kunj['visit_ID'] ?? null, 100), isset($kunj['id_visit']) ? (int) $kunj['id_visit'] : null, $cut($kunj['id_patient'] ?? null, 50),
            $cut($kunj['nama_pasien'] ?? null, 150), mb_substr($aksi, 0, 30), $cut($sumber, 50), $keterangan !== '' ? $keterangan : null,
            logJson($lama), logJson($baru), $p['uid_user'], $p['username'], $p['fullname'], $p['roles'], $p['pelaku_asli'], $p['ip'],
            date('Y-m-d H:i:s'), // zona waktu PHP (Asia/Jakarta), sama dengan filter tanggal di menu log
         ], '20261128_log_kunjungan.sql', LOG_KUNJUNGAN_UPGRADE);
   } catch (Throwable $e) {
      error_log('[logKunjungan] ' . $aksi . ' ' . ($kunj['visit_ID'] ?? '') . ': ' . $e->getMessage());
   }
}
