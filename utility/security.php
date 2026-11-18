<?php

const LOGIN_MAX_ATTEMPTS = 3;
const LOGIN_LOCK_MINUTES = 5;

/** Pesan error bila password tidak memenuhi policy, null bila valid. */
function passwordPolicyError(string $pw): ?string
{
   if (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $pw)) {
      return null;
   }
   return 'Password minimal 8 karakter dan wajib mengandung huruf besar, huruf kecil, angka, dan karakter khusus (contoh: *#$!&).';
}

function hashPassword(string $pw): string
{
   return password_hash($pw, PASSWORD_DEFAULT);
}

/** Verifikasi bcrypt; hash md5 lama (32 hex) masih diterima lalu di-rehash saat login. */
function verifyPassword(string $pw, string $stored): bool
{
   if (preg_match('/^[a-f0-9]{32}$/i', $stored)) {
      return hash_equals(strtolower($stored), md5($pw));
   }
   return password_verify($pw, $stored);
}

function passwordNeedsRehash(string $stored): bool
{
   return !preg_match('/^\$2y\$/', $stored) || password_needs_rehash($stored, PASSWORD_DEFAULT);
}

// ---- Lockout login

function loginKey(string $username): string
{
   return mb_strtolower(mb_substr(trim($username), 0, 100));
}

/** Sisa detik akun terkunci (0 = tidak terkunci). */
function loginLockRemaining(mysqli $db, string $username): int
{
   $key = loginKey($username);
   $st = $db->prepare("SELECT COALESCE(GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), locked_until)), 0) FROM login_attempts WHERE username = ?");
   $st->bind_param('s', $key);
   $st->execute();
   $st->bind_result($sec);
   $st->fetch();
   $st->close();
   return (int) $sec;
}

function loginRecordFailure(mysqli $db, string $username): void
{
   $key = loginKey($username);
   $max = LOGIN_MAX_ATTEMPTS;
   $min = LOGIN_LOCK_MINUTES;
   // Urutan SET dievaluasi kiri-ke-kanan: attempts direset dulu bila kunci sudah kedaluwarsa.
   $st = $db->prepare("INSERT INTO login_attempts (username, attempts, last_attempt) VALUES (?, 1, NOW())
      ON DUPLICATE KEY UPDATE
         attempts = IF(locked_until IS NOT NULL AND locked_until <= NOW(), 1, attempts + 1),
         locked_until = IF(attempts >= $max, DATE_ADD(NOW(), INTERVAL $min MINUTE), NULL),
         last_attempt = NOW()");
   $st->bind_param('s', $key);
   $st->execute();
   $st->close();
}

function loginClearFailures(mysqli $db, string $username): void
{
   $key = loginKey($username);
   $st = $db->prepare("DELETE FROM login_attempts WHERE username = ?");
   $st->bind_param('s', $key);
   $st->execute();
   $st->close();
}

// ---- Guard akses ----

/** Hentikan request (403 JSON) bila role session tidak termasuk $roles. */
function requireRole(array $roles): void
{
   if (!isset($_SESSION['uid_user'])) {
      http_response_code(401);
      header('Content-Type: application/json');
      echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Session tidak valid / expired']);
      exit;
   }
   if (!in_array($_SESSION['roles'] ?? '', $roles, true)) {
      http_response_code(403);
      header('Content-Type: application/json');
      echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Akses ditolak']);
      exit;
   }
}
