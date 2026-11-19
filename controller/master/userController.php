<?php
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/security.php';

header('Content-Type: application/json');

$id_customer = (int) ($_SESSION['id_customer'] ?? 0);

// 🔐 VALIDASI SESSION
if (!$id_customer) {
   http_response_code(401);
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak valid / expired'
   ]);
   exit;
}

requireRole(['admin', 'superadmin', 'administrator']);

/** Role yang boleh dibuat dari halaman ini (selain dokter lewat form akses dokter). */
const ROLE_STAF = ['bidan', 'perawat', 'receptionis', 'kasir', 'apoteker', 'analislab'];
/** Akun role ini hanya boleh diubah oleh pemiliknya sendiri. */
const ROLE_TERKUNCI = ['admin', 'superadmin', 'administrator'];

function jawab(array $data, int $code = 200): void
{
   http_response_code($code);
   echo json_encode($data);
   exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
   case 'POST':
      createData($id_customer);
      break;
   case 'GET':
      if (isset($_GET['dokter'])) {
         getDokter($id_customer);
      } elseif (isset($_GET['id'])) {
         getID((int) $_GET['id'], $id_customer);
      } else {
         getData($id_customer);
      }
      break;
   case 'PUT':
      updateData($id_customer);
      break;
   case 'DELETE':
      deleteData($id_customer);
      break;
   default:
      jawab(['status' => 'error', 'message' => 'Metode tidak didukung'], 405);
}

// ================= HELPER =================
function kunciNamaDokter(?string $nama): string
{
   $s = strtoupper(trim((string) $nama));
   $s = preg_replace('/,.*$/', '', $s);
   $s = preg_replace('/^(DRG|DR)\b\.?\s*/', '', $s);
   return preg_replace('/[^A-Z0-9]/', '', $s);
}

function validUsername(string $u): ?string
{
   if ($u === '') {
      return 'Username wajib diisi.';
   }
   if (!preg_match('/^[A-Za-z0-9._@-]{3,50}$/', $u)) {
      return 'Username 3–50 karakter, hanya huruf, angka, titik, garis bawah, @ atau tanda minus (tanpa spasi).';
   }
   return null;
}

/** Username dipakai untuk login di semua faskes, jadi harus unik secara global. */
function usernameDipakai(mysqli $k, string $u, int $kecualiId = 0): bool
{
   $st = $k->prepare("SELECT 1 FROM ms_users WHERE LOWER(username)=LOWER(?) AND id_user<>? LIMIT 1");
   $st->bind_param("si", $u, $kecualiId);
   $st->execute();
   $ada = (bool) $st->get_result()->fetch_row();
   $st->close();
   return $ada;
}

function ambilUser(mysqli $k, int $id, int $idc): ?array
{
   $st = $k->prepare("SELECT id_user, uid_user, fullname, username, roles, status, kdDokter FROM ms_users WHERE id_user=? AND id_customer=?");
   $st->bind_param("ii", $id, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

function dokterFaskes(mysqli $k, int $idc, bool $aktifSaja = false): array
{
   $sql = "SELECT id_doctor, doctor_name, doctor_code, doctor_status FROM ms_doctor WHERE id_customer=?"
      . ($aktifSaja ? " AND doctor_status='1'" : "") . " ORDER BY doctor_status DESC, doctor_name ASC, id_doctor ASC";
   $st = $k->prepare($sql);
   $st->bind_param("i", $idc);
   $st->execute();
   $r = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();
   return $r;
}

function ambilDokter(mysqli $k, int $idDoctor, int $idc): ?array
{
   $st = $k->prepare("SELECT id_doctor, doctor_name, doctor_code FROM ms_doctor WHERE id_doctor=? AND id_customer=? AND doctor_status='1'");
   $st->bind_param("ii", $idDoctor, $idc);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}

/** Status keterhubungan akun dokter dengan master dokter faskes. */
function linkDokter(array $u, array $byCode, array $byName): array
{
   $kode = trim((string) ($u['kdDokter'] ?? ''));
   $dc = $kode !== '' ? ($byCode[$kode] ?? null) : null;
   $dn = $byName[kunciNamaDokter($u['fullname'])] ?? null;

   if ($dc && $dn && $dc['doctor_code'] !== $dn['doctor_code']) {
      return ['status' => 'beda', 'nama' => $dc['doctor_name'], 'kode' => $dc['doctor_code'], 'id_doctor' => $dn['id_doctor']];
   }
   if ($dc) {
      return ['status' => 'ok', 'nama' => $dc['doctor_name'], 'kode' => $dc['doctor_code'], 'id_doctor' => $dc['id_doctor']];
   }
   if ($dn) {
      return ['status' => $kode === '' ? 'nama' : 'kode', 'nama' => $dn['doctor_name'], 'kode' => $dn['doctor_code'], 'id_doctor' => $dn['id_doctor']];
   }
   return ['status' => 'putus', 'nama' => null, 'kode' => $kode ?: null, 'id_doctor' => null];
}

function petaDokter(array $dokter): array
{
   $byCode = [];
   $byName = [];
   foreach ($dokter as $d) {
      $c = trim((string) $d['doctor_code']);
      if ($c !== '' && !isset($byCode[$c])) {
         $byCode[$c] = $d;
      }
      $n = kunciNamaDokter($d['doctor_name']);
      if ($n !== '' && !isset($byName[$n])) {
         $byName[$n] = $d;
      }
   }
   return [$byCode, $byName];
}

function bolehUbah(array $u): bool
{
   return !in_array($u['roles'], ROLE_TERKUNCI, true) || $u['uid_user'] === ($_SESSION['uid_user'] ?? null);
}

// ================= CREATE =================
function createData($id_customer)
{
   global $koneksi;

   $roles = (string) ($_POST['roles'] ?? '');
   $username = trim((string) ($_POST['username'] ?? ''));
   $password = (string) ($_POST['password'] ?? '');
   $kdDokter = null;

   if ($roles === 'dokter') {
      $dok = ambilDokter($koneksi, (int) ($_POST['id_doctor'] ?? 0), $id_customer);
      if (!$dok) {
         jawab(['status' => 'error', 'message' => 'Pilih dokter aktif dari master dokter faskes.'], 422);
      }
      $fullname = $dok['doctor_name'];
      $kdDokter = $dok['doctor_code'] !== null && $dok['doctor_code'] !== '' ? mb_substr($dok['doctor_code'], 0, 10) : null;
   } elseif (in_array($roles, ROLE_STAF, true)) {
      $fullname = trim((string) ($_POST['fullname'] ?? ''));
      if ($fullname === '' || mb_strlen($fullname) > 100) {
         jawab(['status' => 'error', 'message' => 'Nama lengkap wajib diisi (maks. 100 karakter).'], 422);
      }
   } else {
      jawab(['status' => 'error', 'message' => 'Role tidak valid.'], 422);
   }

   if ($err = validUsername($username)) {
      jawab(['status' => 'error', 'message' => $err], 422);
   }
   if (usernameDipakai($koneksi, $username)) {
      jawab(['status' => 'error', 'message' => 'Username sudah digunakan (username berlaku untuk login di semua faskes).'], 409);
   }
   if ($err = passwordPolicyError($password)) {
      jawab(['status' => 'error', 'message' => $err], 422);
   }

   $uid = generateUserUID($koneksi);
   $hash = hashPassword($password);
   $path = 'admin';
   $by = (string) ($_SESSION['username'] ?? '');
   $st = $koneksi->prepare(
      "INSERT INTO ms_users (uid_user, id_customer, fullname, username, password, roles, path, kdDokter, created_by)
       VALUES (?,?,?,?,?,?,?,?,?)"
   );
   $st->bind_param("sisssssss", $uid, $id_customer, $fullname, $username, $hash, $roles, $path, $kdDokter, $by);

   if ($st->execute()) {
      jawab(['status' => 'success', 'message' => 'User berhasil ditambahkan']);
   }
   jawab(['status' => 'error', 'message' => 'Gagal menyimpan user.'], 500);
}

// ================= GENERATE UID =================
function generateUserUID($koneksi)
{
   do {
      $uid = "USR-" . md5(random_bytes(16));
      $count = 0;
      $check = $koneksi->prepare("SELECT COUNT(*) FROM ms_users WHERE uid_user=?");
      $check->bind_param("s", $uid);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $uid;
}

// ================= READ =================
function getData($id_customer)
{
   global $koneksi;

   // password & uid sengaja TIDAK dikirim ke browser
   $st = $koneksi->prepare(
      "SELECT id_user, uid_user, fullname, username, roles, status, created_at, signature_user, kdDokter
       FROM ms_users
       WHERE id_customer=?
       ORDER BY fullname ASC"
   );
   $st->bind_param("i", $id_customer);
   $st->execute();
   $data = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();

   // username yang juga dipakai akun lain (login hanya mengambil salah satunya)
   $dobel = [];
   if ($data) {
      $st = $koneksi->prepare(
         "SELECT LOWER(b.username) AS u, COUNT(*) AS n
          FROM ms_users a JOIN ms_users b ON LOWER(b.username)=LOWER(a.username) AND b.id_user<>a.id_user
          WHERE a.id_customer=?
          GROUP BY LOWER(b.username)"
      );
      $st->bind_param("i", $id_customer);
      $st->execute();
      foreach ($st->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
         $dobel[$r['u']] = (int) $r['n'];
      }
      $st->close();
   }

   [$byCode, $byName] = petaDokter(dokterFaskes($koneksi, $id_customer));
   $self = $_SESSION['uid_user'] ?? null;

   foreach ($data as &$r) {
      $r['is_self'] = $r['uid_user'] === $self;
      $r['terkunci'] = !bolehUbah($r);
      $r['ttd'] = !empty($r['signature_user']);
      $r['username_dobel'] = $dobel[mb_strtolower((string) $r['username'])] ?? 0;
      $r['dokter'] = $r['roles'] === 'dokter' ? linkDokter($r, $byCode, $byName) : null;
      unset($r['uid_user']);
   }
   unset($r);

   jawab(['status' => 'success', 'data' => $data]);
}

// ================= DAFTAR DOKTER (untuk form akses dokter) =================
function getDokter($id_customer)
{
   global $koneksi;

   $dokter = dokterFaskes($koneksi, $id_customer, true);

   $st = $koneksi->prepare("SELECT fullname, kdDokter FROM ms_users WHERE id_customer=? AND roles='dokter'");
   $st->bind_param("i", $id_customer);
   $st->execute();
   $akun = $st->get_result()->fetch_all(MYSQLI_ASSOC);
   $st->close();

   $punyaKode = [];
   $punyaNama = [];
   foreach ($akun as $a) {
      if (trim((string) $a['kdDokter']) !== '') {
         $punyaKode[trim($a['kdDokter'])] = true;
      }
      $punyaNama[kunciNamaDokter($a['fullname'])] = true;
   }

   $out = [];
   $seen = [];
   foreach ($dokter as $d) {
      $key = trim((string) $d['doctor_code']) . '|' . kunciNamaDokter($d['doctor_name']);
      if (isset($seen[$key])) {
         continue; // baris dokter dobel di master
      }
      $seen[$key] = true;
      $out[] = [
         'id_doctor' => (int) $d['id_doctor'],
         'doctor_name' => $d['doctor_name'],
         'doctor_code' => $d['doctor_code'],
         'punya_akun' => isset($punyaKode[trim((string) $d['doctor_code'])]) || isset($punyaNama[kunciNamaDokter($d['doctor_name'])]),
      ];
   }

   jawab(['status' => 'success', 'data' => $out]);
}

// ================= READ BY ID =================
function getID($id, $id_customer)
{
   global $koneksi;

   $u = ambilUser($koneksi, $id, $id_customer);
   if (!$u) {
      jawab(['status' => 'error', 'message' => 'Tidak ditemukan'], 404);
   }
   if (!bolehUbah($u)) {
      jawab(['status' => 'error', 'message' => 'Akun admin hanya bisa diubah oleh pemiliknya.'], 403);
   }
   if ($u['roles'] === 'dokter') {
      [$byCode, $byName] = petaDokter(dokterFaskes($koneksi, $id_customer));
      $u['dokter'] = linkDokter($u, $byCode, $byName);
   }
   $u['is_self'] = $u['uid_user'] === ($_SESSION['uid_user'] ?? null);
   unset($u['uid_user']);
   jawab(['status' => 'success', 'data' => $u]);
}

// ================= UPDATE =================
function updateData($id_customer)
{
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   $id = (int) ($_PUT['id_user'] ?? 0);
   if ($id <= 0) {
      jawab(['status' => 'error', 'message' => 'ID tidak ditemukan'], 422);
   }

   $lama = ambilUser($koneksi, $id, $id_customer);
   if (!$lama) {
      jawab(['status' => 'error', 'message' => 'User tidak ditemukan'], 404);
   }
   if (!bolehUbah($lama)) {
      jawab(['status' => 'error', 'message' => 'Akun admin hanya bisa diubah oleh pemiliknya.'], 403);
   }
   $isSelf = $lama['uid_user'] === ($_SESSION['uid_user'] ?? null);

   // ================= TOGGLE STATUS =================
   if (isset($_GET['toggle_status'])) {
      $status = (string) ($_PUT['status'] ?? '');
      if ($status !== '0' && $status !== '1') {
         jawab(['status' => 'error', 'message' => 'Status tidak valid.'], 422);
      }
      if ($isSelf && $status === '0') {
         jawab(['status' => 'error', 'message' => 'Anda tidak bisa menonaktifkan akun sendiri.'], 409);
      }
      $s = (int) $status;
      $st = $koneksi->prepare("UPDATE ms_users SET status=? WHERE id_user=? AND id_customer=?");
      $st->bind_param("iii", $s, $id, $id_customer);
      if ($st->execute()) {
         jawab(['status' => 'success', 'message' => $s ? 'User diaktifkan.' : 'User dinonaktifkan, tidak bisa login lagi.']);
      }
      jawab(['status' => 'error', 'message' => 'Gagal update status.'], 500);
   }

   $set = [];
   $vals = [];

   // nama / tautan dokter
   if ($lama['roles'] === 'dokter' && !empty($_PUT['id_doctor'])) {
      $dok = ambilDokter($koneksi, (int) $_PUT['id_doctor'], $id_customer);
      if (!$dok) {
         jawab(['status' => 'error', 'message' => 'Dokter tidak ditemukan / tidak aktif.'], 422);
      }
      $set[] = 'fullname=?';
      $vals[] = $dok['doctor_name'];
      $set[] = 'kdDokter=?';
      $vals[] = $dok['doctor_code'] !== null && $dok['doctor_code'] !== '' ? mb_substr($dok['doctor_code'], 0, 10) : null;
   } elseif (isset($_PUT['fullname'])) {
      $fullname = trim((string) $_PUT['fullname']);
      if ($fullname === '' || mb_strlen($fullname) > 100) {
         jawab(['status' => 'error', 'message' => 'Nama lengkap wajib diisi (maks. 100 karakter).'], 422);
      }
      $set[] = 'fullname=?';
      $vals[] = $fullname;
   }

   // username: validasi hanya bila berubah (data lama bisa tidak sesuai format)
   if (isset($_PUT['username'])) {
      $username = trim((string) $_PUT['username']);
      if (mb_strtolower($username) !== mb_strtolower((string) $lama['username'])) {
         if ($err = validUsername($username)) {
            jawab(['status' => 'error', 'message' => $err], 422);
         }
         if (usernameDipakai($koneksi, $username, $id)) {
            jawab(['status' => 'error', 'message' => 'Username sudah digunakan (username berlaku untuk login di semua faskes).'], 409);
         }
      }
      if ($username !== $lama['username']) {
         $set[] = 'username=?';
         $vals[] = $username;
      }
   }

   // password (opsional)
   $pw = (string) ($_PUT['password'] ?? '');
   if (trim($pw) !== '') {
      if ($err = passwordPolicyError($pw)) {
         jawab(['status' => 'error', 'message' => $err], 422);
      }
      $set[] = 'password=?';
      $vals[] = hashPassword($pw);
   }

   // role & path sengaja tidak bisa diubah dari halaman ini

   if (!$set) {
      jawab(['status' => 'error', 'message' => 'Tidak ada perubahan'], 422);
   }

   $set[] = 'udpated_at=?';
   $vals[] = date('Y-m-d H:i:s');
   $vals[] = $id;
   $vals[] = $id_customer;

   $st = $koneksi->prepare("UPDATE ms_users SET " . implode(',', $set) . " WHERE id_user=? AND id_customer=?");
   $st->bind_param(str_repeat('s', count($vals) - 2) . "ii", ...$vals);

   if ($st->execute()) {
      jawab(['status' => 'success', 'message' => 'Data berhasil diupdate']);
   }
   jawab(['status' => 'error', 'message' => 'Gagal memperbarui user.'], 500);
}

// ================= DELETE =================
function deleteData($id_customer)
{
   global $koneksi;

   $id = (int) ($_GET['id'] ?? 0);
   if ($id <= 0) {
      jawab(['status' => 'error', 'message' => 'ID kosong'], 422);
   }

   $u = ambilUser($koneksi, $id, $id_customer);
   if (!$u) {
      jawab(['status' => 'error', 'message' => 'User tidak ditemukan'], 404);
   }
   if ($u['uid_user'] === ($_SESSION['uid_user'] ?? null)) {
      jawab(['status' => 'error', 'message' => 'Anda tidak bisa menghapus akun sendiri.'], 409);
   }
   if (!bolehUbah($u)) {
      jawab(['status' => 'error', 'message' => 'Akun admin tidak bisa dihapus dari halaman ini.'], 403);
   }

   $st = $koneksi->prepare("DELETE FROM ms_users WHERE id_user=? AND id_customer=?");
   $st->bind_param("ii", $id, $id_customer);
   if ($st->execute()) {
      jawab(['status' => 'success', 'message' => 'User dihapus.']);
   }
   jawab(['status' => 'error', 'message' => 'Gagal menghapus user.'], 500);
}
