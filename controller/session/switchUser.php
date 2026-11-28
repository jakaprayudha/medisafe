<?php
/**
 * Switch user ("login sebagai") untuk admin/superadmin (dropdown di module/admin/navbar.php).
 *
 *   GET  ?search=            -> daftar user yang boleh dipilih (Select2)
 *   POST uid_user=           -> pindah ke user tersebut
 *   POST aksi=kembali        -> kembali ke akun asal (tanpa login ulang)
 *
 * Aturan:
 *  - superadmin boleh pindah ke user non-superadmin/administrator di faskes mana pun;
 *  - admin hanya ke user di faskesnya sendiri (termasuk admin lain di faskes itu), tidak boleh ke superadmin/administrator;
 *  - akun asal disimpan di $_SESSION['switch_dari'] dan dipakai sebagai "pelaku asli" di semua log
 *    (utility/activityLog.php -> logPelaku), jadi aksi selama switch tetap tercatat atas nama admin yang melakukan.
 *
 * Perbaikan dari versi lama: admin bisa masuk ke user faskes mana pun termasuk superadmin, tidak ada log sama sekali,
 * setelah switch ke peran non-admin tidak bisa kembali, kode dokter tidak diset (fitur dokter salah), SQL dirakit manual.
 */
session_start();
require '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
header('Content-Type: application/json; charset=utf-8');

const SU_TUJUAN_TERLARANG = ['superadmin', 'administrator'];

function suJawab(array $d, int $kode = 200): void
{
   http_response_code($kode);
   echo json_encode($d, JSON_UNESCAPED_UNICODE);
   exit;
}

/** Akun yang sebenarnya melakukan switch: akun asal bila sedang switch, selain itu akun session. */
function suPelaku(): array
{
   $a = $_SESSION['switch_dari'] ?? null;
   if (is_array($a) && !empty($a['uid_user'])) return $a;
   return [
      'uid_user' => (string) ($_SESSION['uid_user'] ?? ''),
      'username' => (string) ($_SESSION['username'] ?? ''),
      'fullname' => (string) ($_SESSION['fullname'] ?? ''),
      'roles' => (string) ($_SESSION['roles'] ?? ''),
      'id_customer' => (int) ($_SESSION['id_customer'] ?? 0),
      'status' => (string) ($_SESSION['status'] ?? '1'),
      'kode_dokter' => $_SESSION['kode_dokter'] ?? null,
   ];
}

/** Pasang session untuk user $u (format baris ms_users / suPelaku). */
function suPasang(mysqli $db, array $u): void
{
   session_regenerate_id(true);
   $_SESSION['uid_user'] = $u['uid_user'];
   $_SESSION['username'] = $u['username'];
   $_SESSION['roles'] = $u['roles'];
   $_SESSION['id_customer'] = $u['id_customer'];
   $_SESSION['status'] = $u['status'] ?? '1';
   $_SESSION['fullname'] = $u['fullname'];
   unset($_SESSION['kode_dokter']);
   if (array_key_exists('kode_dokter', $u)) {
      if ($u['kode_dokter'] !== null) $_SESSION['kode_dokter'] = $u['kode_dokter'];
   } elseif ($u['roles'] === 'dokter') {
      // sama dengan controller/auth.php
      $nama = (string) $u['fullname'];
      $kd = (string) ($u['kdDokter'] ?? '');
      $st = $db->prepare("SELECT doctor_code FROM ms_doctor WHERE doctor_name = ? OR doctor_code = ? LIMIT 1");
      $st->bind_param('ss', $nama, $kd);
      $st->execute();
      $_SESSION['kode_dokter'] = $st->get_result()->fetch_assoc()['doctor_code'] ?? null;
      $st->close();
   }
}

$pelaku = suPelaku();
$sedangSwitch = isset($_SESSION['switch_dari']);
if ($pelaku['uid_user'] === '' || !in_array($pelaku['roles'], ['admin', 'superadmin'], true)) {
   suJawab(['status' => 'error', 'message' => 'Akses ditolak'], 403);
}
$superadmin = $pelaku['roles'] === 'superadmin';
$idcPelaku = (int) $pelaku['id_customer'];

// ---------- GET: daftar user ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
   $cari = '%' . addcslashes(trim(mb_substr((string) ($_GET['search'] ?? ''), 0, 60)), '%_\\') . '%';
   $sql = "SELECT uid_user, fullname, username, roles, id_customer FROM ms_users
      WHERE status = '1' AND roles NOT IN ('superadmin', 'administrator') AND uid_user <> ?
        AND (fullname LIKE ? OR username LIKE ? OR roles LIKE ?)" . ($superadmin ? '' : ' AND id_customer = ?') . "
      ORDER BY fullname ASC LIMIT 50";
   $st = $koneksi->prepare($sql);
   $saya = (string) ($_SESSION['uid_user'] ?? '');
   if ($superadmin) $st->bind_param('ssss', $saya, $cari, $cari, $cari);
   else $st->bind_param('ssssi', $saya, $cari, $cari, $cari, $idcPelaku);
   $st->execute();
   $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   $hasil = [];
   if ($sedangSwitch) $hasil[] = ['id' => '__kembali__', 'text' => '↩ Kembali ke akun asal (' . $pelaku['fullname'] . ')'];
   foreach ($rows as $r) {
      $hasil[] = ['id' => $r['uid_user'], 'text' => $r['fullname'] . ' (' . $r['roles'] . ' - ' . $r['username'] . ($superadmin ? ' · faskes ' . $r['id_customer'] : '') . ')'];
   }
   suJawab(['results' => $hasil]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') suJawab(['status' => 'error', 'message' => 'Metode tidak diizinkan.'], 405);

// ---------- POST: kembali ke akun asal ----------
$uidTujuan = trim((string) ($_POST['uid_user'] ?? ''));
if (($_POST['aksi'] ?? '') === 'kembali' || $uidTujuan === '__kembali__' || ($sedangSwitch && $uidTujuan === $pelaku['uid_user'])) {
   if (!$sedangSwitch) suJawab(['status' => 'error', 'message' => 'Anda tidak sedang menggunakan akun lain.'], 409);
   $dari = ['username' => $_SESSION['username'] ?? null, 'fullname' => $_SESSION['fullname'] ?? null, 'roles' => $_SESSION['roles'] ?? null, 'id_customer' => $_SESSION['id_customer'] ?? null];
   unset($_SESSION['switch_dari']);
   suPasang($koneksi, $pelaku);
   logAktivitas($koneksi, 'auth', 'switch_kembali', ['tipe' => 'user', 'id' => $pelaku['uid_user'], 'label' => $pelaku['username']],
      'Kembali dari akun ' . ($dari['username'] ?? '-'), $dari, ['username' => $pelaku['username'], 'roles' => $pelaku['roles'], 'id_customer' => $pelaku['id_customer']]);
   suJawab(['status' => 'success', 'message' => 'Kembali ke akun ' . $pelaku['fullname'], 'redirect' => 'module/admin']);
}

// ---------- POST: switch ke user lain ----------
if ($uidTujuan === '') suJawab(['status' => 'error', 'message' => 'User tidak dipilih'], 422);
$st = $koneksi->prepare("SELECT uid_user, username, fullname, roles, id_customer, status, kdDokter FROM ms_users WHERE uid_user = ? AND status = '1' LIMIT 1");
$st->bind_param('s', $uidTujuan);
$st->execute();
$tujuan = $st->get_result()->fetch_assoc();
$st->close();
if (!$tujuan) suJawab(['status' => 'error', 'message' => 'User tidak ditemukan / nonaktif'], 404);

$tolak = null;
if (in_array($tujuan['roles'], SU_TUJUAN_TERLARANG, true)) $tolak = 'Tidak boleh masuk sebagai akun ' . $tujuan['roles'] . '.';
elseif (!$superadmin && (int) $tujuan['id_customer'] !== $idcPelaku) $tolak = 'Admin hanya bisa masuk sebagai user di faskesnya sendiri.';
if ($tolak) {
   logAktivitas($koneksi, 'auth', 'switch_tolak', ['tipe' => 'user', 'id' => $tujuan['uid_user'], 'label' => $tujuan['username']], $tolak,
      null, ['username' => $tujuan['username'], 'roles' => $tujuan['roles'], 'id_customer' => $tujuan['id_customer']]);
   suJawab(['status' => 'error', 'message' => $tolak], 403);
}

$sebelum = ['username' => $_SESSION['username'] ?? null, 'roles' => $_SESSION['roles'] ?? null, 'id_customer' => $_SESSION['id_customer'] ?? null];
if (!$sedangSwitch) $_SESSION['switch_dari'] = $pelaku;   // simpan akun asal sekali saja (switch berantai tetap kembali ke akun pertama)
suPasang($koneksi, $tujuan);
// dicatat di faskes tujuan (tempat aksi selama switch terjadi); pelaku_asli = akun asal
logAktivitas($koneksi, 'auth', 'switch_user', ['tipe' => 'user', 'id' => $tujuan['uid_user'], 'label' => $tujuan['username']],
   $pelaku['username'] . ' masuk sebagai ' . $tujuan['username'] . ' (' . $tujuan['roles'] . ')', $sebelum,
   ['username' => $tujuan['username'], 'roles' => $tujuan['roles'], 'id_customer' => $tujuan['id_customer']], (int) $tujuan['id_customer']);
if ((int) $tujuan['id_customer'] !== $idcPelaku) {
   // jejak juga di faskes asal pelaku
   logAktivitas($koneksi, 'auth', 'switch_user', ['tipe' => 'user', 'id' => $tujuan['uid_user'], 'label' => $tujuan['username']],
      $pelaku['username'] . ' masuk sebagai ' . $tujuan['username'] . ' di faskes ' . $tujuan['id_customer'], $sebelum, null, $idcPelaku);
}
suJawab(['status' => 'success', 'message' => 'Berhasil switch ke ' . $tujuan['fullname'], 'redirect' => 'module/admin']);
