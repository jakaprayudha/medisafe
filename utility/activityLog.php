<?php
/**
 * Log aktivitas umum -> tabel log_aktivitas (migrations/20261128_log_aktivitas.sql).
 *
 *   logAktivitas($koneksi, 'pasien', 'ubah', ['tipe' => 'pasien', 'id' => $id, 'label' => $nama], 'Ubah identitas', $lama, $baru);
 *   logAktivitas($koneksi, 'auth', 'login_gagal', ['tipe' => 'user', 'label' => $username], '', null, null, 0);
 *
 * - Pencatatan tidak pernah menggagalkan proses utama (error hanya masuk error_log).
 * - Panggil SETELAH commit transaksi: pembuatan tabel otomatis (CREATE TABLE) memicu implicit commit.
 * - Kolom sensitif (password, token, secret, ...) selalu dibuang dari data lama/baru.
 * - Saat admin sedang "switch user", pelaku asli ikut dicatat (kolom pelaku_asli), lihat logPelaku().
 */

const LOG_AKTIVITAS_MODUL = [
   'auth'   => 'Login & sesi',
   'user'   => 'User & hak akses',
   'pasien' => 'Data pasien',
   'rme'    => 'Gabung RME',
   'cppt'   => 'CPPT',
   'resume' => 'Resume medis',
   'diagnosa' => 'Diagnosa & PCare',
   'lab'    => 'Hasil lab',
   'billing' => 'Tagihan / tindakan',
   'tarif'  => 'Tarif',
   'farmasi' => 'Farmasi & resep',
   'stok'   => 'Stok & pembelian',
   'klinis' => 'Pemeriksaan & form klinis',
   'ranap'  => 'Rawat inap',
   'surat'  => 'Surat',
   'dokumen' => 'Dokumen & tanda tangan',
];

const LOG_AKTIVITAS_AKSI = [
   'login' => 'Login', 'login_gagal' => 'Login gagal', 'login_terkunci' => 'Login ditolak (terkunci)', 'login_nonaktif' => 'Login akun nonaktif',
   'logout' => 'Logout', 'switch_user' => 'Switch user', 'switch_kembali' => 'Kembali ke akun asal', 'switch_tolak' => 'Switch user ditolak',
   'tambah' => 'Tambah', 'ubah' => 'Ubah', 'hapus' => 'Hapus', 'ubah_peran' => 'Ubah peran', 'reset_password' => 'Reset password',
   'aktifkan' => 'Aktifkan', 'nonaktifkan' => 'Nonaktifkan', 'gabung' => 'Gabung RME', 'verifikasi' => 'Verifikasi', 'batal_verifikasi' => 'Batal verifikasi',
   'simpan' => 'Simpan', 'kirim_pcare' => 'Kirim PCare', 'hapus_file' => 'Hapus file', 'upload_file' => 'Upload file',
   'approve' => 'Approve / serahkan', 'batal_approve' => 'Batal approve', 'kirim_obat' => 'Kirim obat PCare', 'ubah_status' => 'Ubah status',
   'cetak' => 'Cetak', 'tanda_tangan' => 'Tanda tangan', 'hapus_pcare' => 'Hapus di PCare',
   'stok_masuk' => 'Stok masuk', 'stok_keluar' => 'Stok keluar', 'penyesuaian' => 'Penyesuaian stok', 'terima' => 'Terima barang', 'ubah_harga' => 'Ubah harga',
];

/** Kunci yang tidak boleh masuk log. */
function logBersihkan(?array $a): ?array
{
   if ($a === null) return null;
   $out = [];
   foreach ($a as $k => $v) {
      if (is_string($k) && preg_match('/pass|pwd|token|secret|signature_key|cons_?id|user_?key|otp/i', $k)) {
         $out[$k] = '[disembunyikan]';
         continue;
      }
      if (is_array($v)) $out[$k] = logBersihkan($v);
      // nilai sangat panjang (tanda tangan / gambar base64, dokumen) tidak disimpan utuh di log
      elseif (is_string($v) && strlen($v) > 3000) $out[$k] = (str_starts_with($v, 'data:') ? '[data ' . strtok(substr($v, 5), ';,') . '] ' : '[teks] ') . strlen($v) . ' byte';
      else $out[$k] = $v;
   }
   return $out;
}

/** Hanya kolom yang berubah: [lama, baru]. Nilai dibandingkan sebagai teks (angka '1' == 1). */
function logBeda(?array $lama, array $baru): array
{
   $l = [];
   $b = [];
   foreach ($baru as $k => $v) {
      $lv = $lama[$k] ?? null;
      $sama = (is_scalar($lv) || $lv === null) && (is_scalar($v) || $v === null) ? trim((string) $lv) === trim((string) $v) : $lv == $v;
      if (!$sama) {
         $l[$k] = $lv;
         $b[$k] = $v;
      }
   }
   return [$l, $b];
}

/** User pelaku dari session (+ pelaku asli bila sedang switch user). Tanpa session -> SISTEM. */
function logPelaku(): array
{
   if (!isset($_SESSION) && session_status() === PHP_SESSION_NONE && !headers_sent()) @session_start();
   $s = $_SESSION ?? [];
   $uid = isset($s['uid_user']) && $s['uid_user'] !== '' ? (string) $s['uid_user'] : null;
   $asli = $s['switch_dari'] ?? null;
   $ip = trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))[0]) ?: (string) ($_SERVER['REMOTE_ADDR'] ?? '');
   return [
      'uid_user' => $uid,
      'username' => (string) ($s['username'] ?? '') ?: ($uid ? null : 'SISTEM'),
      'fullname' => isset($s['fullname']) ? (string) $s['fullname'] : null,
      'roles' => isset($s['roles']) ? (string) $s['roles'] : null,
      'pelaku_asli' => is_array($asli) ? mb_substr(trim(($asli['username'] ?? '') . ' (' . ($asli['fullname'] ?? '') . ')'), 0, 150) : null,
      'id_customer' => isset($s['id_customer']) ? (int) $s['id_customer'] : null,
      'ip' => mb_substr($ip, 0, 45) ?: null,
      'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
   ];
}

function logJson(?array $a): ?string
{
   if ($a === null) return null;
   $j = json_encode(logBersihkan($a), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
   return $j === false ? null : $j;
}

/**
 * Jalankan INSERT; bila tabel belum ada (1146) buat dari file migrasi lalu ulangi.
 * Bila kolom belum ada (1054, tabel versi lama), jalankan $upgrade (ALTER TABLE ...) lalu ulangi.
 */
function logTulis(mysqli $db, string $sql, string $tipe, array $nilai, string $migrasi, array $upgrade = []): void
{
   $jalan = function () use ($db, $sql, $tipe, $nilai) {
      $st = $db->prepare($sql);
      if (!$st) throw new RuntimeException($db->error, $db->errno); // mode mysqli tanpa exception
      $st->bind_param($tipe, ...$nilai);
      if (!$st->execute()) throw new RuntimeException($st->error, $st->errno);
      $st->close();
   };
   try {
      $jalan();
   } catch (Throwable $e) {
      $kode = (int) $e->getCode();
      if ($kode === 1146 && logBuatTabel($db, $migrasi)) {
         $jalan();
      } elseif ($kode === 1054 && $upgrade && logUpgrade($db, $upgrade)) {
         $jalan();
      } else {
         throw $e;
      }
   }
}

/** ALTER TABLE untuk tabel log versi lama (sekali per request; "Duplicate column" diabaikan). Jangan di dalam transaksi. */
function logUpgrade(mysqli $db, array $sqls): bool
{
   static $sudah = [];
   $kunci = md5(implode(';', $sqls));
   if (isset($sudah[$kunci])) return $sudah[$kunci];
   $ok = true;
   foreach ($sqls as $sql) {
      try {
         if (!$db->query($sql) && $db->errno !== 1060) $ok = false;
      } catch (Throwable $e) {
         if ((int) $e->getCode() !== 1060) { // 1060 = kolom sudah ada
            error_log('[log] upgrade gagal: ' . $e->getMessage());
            $ok = false;
         }
      }
   }
   return $sudah[$kunci] = $ok;
}

/** Buat tabel dari file migrasi (CREATE TABLE IF NOT EXISTS). Jangan dipanggil di dalam transaksi. */
function logBuatTabel(mysqli $db, string $migrasi): bool
{
   static $sudah = [];
   if (isset($sudah[$migrasi])) return $sudah[$migrasi];
   try {
      $sql = file_get_contents(__DIR__ . '/../migrations/' . $migrasi);
      if ($sql === false) return $sudah[$migrasi] = false;
      $sql = rtrim(trim(preg_replace('/^\s*--.*$/m', '', $sql)), "; \n");
      return $sudah[$migrasi] = (bool) $db->query($sql);
   } catch (Throwable $e) {
      error_log('[log] gagal membuat tabel ' . $migrasi . ': ' . $e->getMessage());
      return $sudah[$migrasi] = false;
   }
}

function logAktivitasSiapkan(mysqli $db): bool
{
   return logBuatTabel($db, '20261128_log_aktivitas.sql');
}

/**
 * Catat aktivitas.
 * $ref: ['tipe' => 'pasien|user|cppt|...', 'id' => ..., 'label' => 'nama/nomor', 'visit_ID' => ...]
 * $idc: id faskes; null = dari session; 0 = tidak diketahui (mis. login gagal).
 */
function logAktivitas(mysqli $db, string $modul, string $aksi, array $ref = [], string $keterangan = '', ?array $lama = null, ?array $baru = null, ?int $idc = null): void
{
   try {
      $p = logPelaku();
      $idc = $idc ?? $p['id_customer'];
      $idc = $idc ? (int) $idc : null;
      $cut = fn($v, int $n) => $v === null || $v === '' ? null : mb_substr((string) $v, 0, $n);
      logTulis($db, "INSERT INTO log_aktivitas (id_customer, modul, aksi, ref_tipe, ref_id, ref_label, visit_ID, keterangan, data_lama, data_baru,
            uid_user, username, fullname, roles, pelaku_asli, ip, user_agent, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
         'isssssssssssssssss', [
            $idc, mb_substr($modul, 0, 30), mb_substr($aksi, 0, 40), $cut($ref['tipe'] ?? null, 30), $cut($ref['id'] ?? null, 100), $cut($ref['label'] ?? null, 200),
            $cut($ref['visit_ID'] ?? null, 100), $keterangan !== '' ? $keterangan : null, logJson($lama), logJson($baru),
            $p['uid_user'], $p['username'], $p['fullname'], $p['roles'], $p['pelaku_asli'], $p['ip'], $p['user_agent'], date('Y-m-d H:i:s'),
         ], '20261128_log_aktivitas.sql');
   } catch (Throwable $e) {
      error_log('[logAktivitas] ' . $modul . '/' . $aksi . ': ' . $e->getMessage());
   }
}

/** Log item tagihan pasien_billing (dipakai controller/visit/billingController.php & tindakan.php). */
function logTagihan(mysqli $db, string $aksi, int $id, string $visit, ?array $lama, ?array $baru, string $sumber = ''): void
{
   $kol = ['billing_number', 'billing_item', 'billing_category', 'billing_price', 'billing_qty', 'billing_discount', 'billing_notes'];
   $l = $lama ? array_intersect_key($lama, array_flip($kol)) : null;
   $b = $baru ? array_intersect_key($baru, array_flip($kol)) : null;
   if ($l && $b) [$l, $b] = logBeda($l, $b);
   if ($aksi === 'ubah' && !$b) return;
   $r = ($baru ?? []) + ($lama ?? []);
   $rp = fn($n) => 'Rp' . number_format((int) $n, 0, ',', '.');
   $total = (int) ($r['billing_price'] ?? 0) * (int) ($r['billing_qty'] ?? 0) - (int) ($r['billing_discount'] ?? 0);
   logAktivitas($db, 'billing', $aksi, ['tipe' => 'billing', 'id' => $id, 'label' => (string) ($r['billing_item'] ?? '-'), 'visit_ID' => $visit],
      implode(' · ', array_filter([$sumber, (string) ($r['billing_category'] ?? ''), ($r['billing_qty'] ?? 0) . ' × ' . $rp($r['billing_price'] ?? 0)
         . (!empty($r['billing_discount']) ? ' − diskon ' . $rp($r['billing_discount']) : '') . ' = ' . $rp($total)], 'strlen')), $l, $b);
}

/** Item resep + nama obat + tiket (untuk log farmasi). */
function farmasiItemSnapshot(mysqli $db, int $idDetail): ?array
{
   try {
      $st = $db->prepare("SELECT pd.id_pharmacy_details, pd.id_permintaan_farmasi, pd.id_pharmacy,
            COALESCE(NULLIF(mp.pharmacy_name_trade, ''), NULLIF(mp.pharmacy_name_generic, ''), pd.item_name) AS nama_obat, pd.qty, pd.signa, pd.catatan, pd.harga,
            pp.id_visit, pp.tipe_obat, pp.status_permintaan
         FROM permintaan_pharmacy_details pd
         LEFT JOIN ms_pharmacy mp ON mp.id_pharmacy = pd.id_pharmacy
         LEFT JOIN permintaan_pharmacy pp ON pp.id_permintaan_farmasi = pd.id_permintaan_farmasi
         WHERE pd.id_pharmacy_details = ? LIMIT 1");
      if (!$st) return null;
      $st->bind_param('i', $idDetail);
      $st->execute();
      $r = $st->get_result()->fetch_assoc();
      $st->close();
      return $r ?: null;
   } catch (Throwable $e) {
      error_log('[farmasiItemSnapshot] ' . $e->getMessage());
      return null;
   }
}

/** Log item resep: $lama/$baru = snapshot (farmasiItemSnapshot) sebelum/sesudah. */
function logResepItem(mysqli $db, string $aksi, int $idDetail, ?array $lama, ?array $baru, string $sumber = ''): void
{
   $r = $baru ?? $lama ?? [];
   $kol = ['nama_obat', 'id_pharmacy', 'qty', 'signa', 'catatan', 'harga'];
   $l = $lama ? array_intersect_key($lama, array_flip($kol)) : null;
   $b = $baru ? array_intersect_key($baru, array_flip($kol)) : null;
   if ($l && $b) {
      [$l, $b] = logBeda($l, $b);
      if (!$b) return;
   }
   $status = (int) ($r['status_permintaan'] ?? 0);
   logAktivitas($db, 'farmasi', $aksi, ['tipe' => 'resep_item', 'id' => $idDetail, 'label' => (string) ($r['nama_obat'] ?? 'Obat #' . ($r['id_pharmacy'] ?? '?')),
      'visit_ID' => $r['id_visit'] ?? null],
      trim(($sumber !== '' ? $sumber . ' · ' : '') . 'Tiket #' . ($r['id_permintaan_farmasi'] ?? '-') . ' ' . ($r['tipe_obat'] ?? '') . ' · qty ' . ($r['qty'] ?? '-')
         . ($r['signa'] ?? '' ? ' · ' . $r['signa'] : '') . ($status === 3 ? ' · tiket sudah selesai' : '')), $l, $b);
}

/** Log tiket resep (permintaan_pharmacy). */
function logTiketResep(mysqli $db, string $aksi, int $idTiket, string $visit, string $ket, ?array $lama = null, ?array $baru = null): void
{
   logAktivitas($db, 'farmasi', $aksi, ['tipe' => 'resep_tiket', 'id' => $idTiket ?: null, 'label' => 'Tiket resep #' . $idTiket, 'visit_ID' => $visit], $ket, $lama, $baru);
}

/** Data ringkas master obat untuk log stok / harga. */
function obatSnapshot(mysqli $db, int $idObat): ?array
{
   try {
      $st = $db->prepare("SELECT id_pharmacy, pharmacy_code, COALESCE(NULLIF(pharmacy_name_trade, ''), pharmacy_name_generic) AS nama_obat, pharmacy_category,
            pharmacy_unit, pharmacy_stock, pharmacy_buy, pharmacy_price_buy, pharmacy_sale, pharmacy_status
         FROM ms_pharmacy WHERE id_pharmacy = ? LIMIT 1");
      if (!$st) return null;
      $st->bind_param('i', $idObat);
      $st->execute();
      $r = $st->get_result()->fetch_assoc();
      $st->close();
      return $r ?: null;
   } catch (Throwable $e) {
      error_log('[obatSnapshot] ' . $e->getMessage());
      return null;
   }
}

/** Satu baris tabel (untuk dibandingkan sebelum/sesudah). $tabel & $kolom harus konstanta dari kode, bukan input. */
function logAmbilBaris(mysqli $db, string $tabel, string $kolom, $nilai): ?array
{
   if (!preg_match('/^[a-z0-9_]+$/i', $tabel . $kolom) || $nilai === null || $nilai === '') return null;
   try {
      $st = $db->prepare("SELECT * FROM `$tabel` WHERE `$kolom` = ? LIMIT 1");
      if (!$st) return null;
      $v = (string) $nilai;
      $st->bind_param('s', $v);
      $st->execute();
      $r = $st->get_result()->fetch_assoc();
      $st->close();
      return $r ?: null;
   } catch (Throwable $e) {
      error_log('[logAmbilBaris] ' . $tabel . ': ' . $e->getMessage());
      return null;
   }
}

/**
 * Log perubahan satu baris: ambil kondisi terbaru dari DB lalu bandingkan dengan $lama.
 *   $lama = logAmbilBaris($koneksi, 'ekg_results', 'id_ekg', $id);   // sebelum ubah/hapus (null untuk tambah)
 *   ... INSERT/UPDATE/DELETE ...
 *   logBaris($koneksi, 'klinis', 'ekg_results', 'id_ekg', $id, $lama, ['label' => 'EKG', 'visit_ID' => $no], 'EKG disimpan');
 * Aksi otomatis: tambah (tidak ada $lama), hapus (baris sudah tidak ada), ubah (hanya kolom yang berubah; tidak dicatat bila sama).
 */
function logBaris(mysqli $db, string $modul, string $tabel, string $kolom, $nilai, ?array $lama, array $ref, string $ket = '', ?string $aksi = null): void
{
   $baru = logAmbilBaris($db, $tabel, $kolom, $nilai);
   $abaikan = ['updated_at', 'udpated_at', 'update_at', 'modified_at'];
   $bersih = fn(?array $r) => $r === null ? null : array_diff_key($r, array_flip($abaikan));
   $l = $bersih($lama);
   $b = $bersih($baru);
   if ($aksi === null) $aksi = $l === null ? 'tambah' : ($b === null ? 'hapus' : 'ubah');
   if ($l !== null && $b !== null) {
      [$l, $b] = logBeda($l, $b);
      if (!$b && $aksi === 'ubah') return;
   }
   $ref += ['tipe' => $tabel, 'id' => $nilai];
   logAktivitas($db, $modul, $aksi, $ref, $ket, $l, $b);
}
