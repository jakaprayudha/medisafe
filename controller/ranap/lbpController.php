<?php
include '../../database/connect.php';
require_once __DIR__ . '/../../utility/activityLog.php';
/*
 * Perbaikan: tanpa cek login & tanpa filter faskes (lbp_bpjs tidak punya id_customer: LBP faskes lain bisa dibaca,
 * diubah, dihapus lewat id_lbp / visit_ID); tidak ada log. Kini kepemilikan dicek lewat pasien_visit.
 */
if (empty($_SESSION['id_customer'])) {
   http_response_code(401);
   echo json_encode(['status' => 'error', 'message' => 'Session tidak valid / expired']);
   exit;
}
function lbpIdc(): int
{
   return (int) $_SESSION['id_customer'];
}
function lbpVisitMilik(string $visitID): bool
{
   global $koneksi;
   $idc = lbpIdc();
   $st = $koneksi->prepare("SELECT 1 FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
   $st->bind_param('si', $visitID, $idc);
   $st->execute();
   $ada = (bool) $st->get_result()->fetch_row();
   $st->close();
   return $ada;
}
/** Baris LBP milik faskes session (lewat kunjungannya), atau null. */
function lbpAmbil($id): ?array
{
   global $koneksi;
   $idc = lbpIdc();
   $id = (string) $id;
   $st = $koneksi->prepare("SELECT l.* FROM lbp_bpjs l JOIN pasien_visit v ON v.visit_ID = l.visit_ID AND v.id_customer = ? WHERE l.id_lbp = ? LIMIT 1");
   $st->bind_param('is', $idc, $id);
   $st->execute();
   $r = $st->get_result()->fetch_assoc();
   $st->close();
   return $r ?: null;
}
function lbpLog($id, ?array $lama, string $ket): void
{
   global $koneksi;
   $r = $lama ?? logAmbilBaris($koneksi, 'lbp_bpjs', 'id_lbp', $id) ?? [];
   logBaris($koneksi, 'ranap', 'lbp_bpjs', 'id_lbp', $id, $lama, ['tipe' => 'lbp', 'label' => trim('LBP · ' . ($r['tgl_pelayanan'] ?? ''), ' ·'), 'visit_ID' => $r['visit_ID'] ?? null], $ket);
}
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
   case 'POST':
      createData();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id']);
      } else {
         getData();
      }
      break;
   case 'PUT':
      // Update User
      updateData();
      break;

   case 'DELETE':
      // Delete User
      deleteData();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create
function createData()
{
   global $koneksi;

   if (empty($_POST)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
      exit;
   }

   // Ambil semua field yang valid untuk tabel lbp_bpjs
   $allowedFields = [
      'nomor_rm',
      'visit_ID',
      'tgl_pelayanan',
      'uraian'
   ];

   if (!lbpVisitMilik((string) ($_POST['visit_ID'] ?? ''))) {
      http_response_code(404);
      echo json_encode(['status' => 'error', 'message' => 'Kunjungan tidak ditemukan di faskes ini.']);
      exit;
   }

   $fields = ['lbp_number']; // tambahkan CPPT ID
   $values = [generateDoctorNumber($koneksi)];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   if (empty($fields)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada data yang dikirim.'
      ]);
      exit;
   }

   // Buat placeholder dan tipe untuk prepared statement
   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);
   $types = str_repeat('s', count($fields)); // semua string

   $query = "INSERT INTO lbp_bpjs ($columns) VALUES ($placeholders)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         lbpLog($stmt->insert_id, null, 'LBP ditambahkan');
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menambahkan data: ' . $stmt->error
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query: ' . $koneksi->error
      ]);
   }
}

/**
 * Generate resep_number unik dengan format DCT-XXXXXX
 */
function generateDoctorNumber($koneksi)
{
   $count = 0; // inisialisasi supaya tidak merah
   do {
      $random = mt_rand(100000, 999999); // 6 digit angka
      $doctorNumber = "DCT-" . $random;

      // cek ke database apakah sudah ada
      $check = $koneksi->prepare("SELECT COUNT(*) FROM lbp_bpjs WHERE lbp_number = ?");
      $check->bind_param("s", $doctorNumber);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0); // ulang jika sudah ada

   return $doctorNumber;
}

function getData()
{
   global $koneksi;
   // pastikan ada parameter "no" (nomor_visit)
   $no = (string) ($_GET['no'] ?? '');
   $rm = (string) ($_GET['rm'] ?? '');
   $idc = lbpIdc();
   $st = $koneksi->prepare("SELECT l.* FROM lbp_bpjs l JOIN pasien_visit v ON v.visit_ID = l.visit_ID AND v.id_customer = ?
             WHERE l.visit_ID = ? AND l.nomor_rm = ? ORDER BY l.tgl_pelayanan ASC");
   $st->bind_param('iss', $idc, $no, $rm);
   $st->execute();
   $result = $st->get_result();

   if (!$result) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);
      return;
   }

   // Ambil semua data dalam bentuk array asosiatif
   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

   // Tutup hasil query
   mysqli_free_result($result);

   // Kirimkan data dalam format JSON
   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}

// Function untuk Read User berdasarkan ID
function  getID($iduser)
{
   $data = lbpAmbil($iduser);
   echo json_encode($data ? ['status' => 'success', 'data' => $data] : ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
}



function updateData()
{
   global $koneksi;
   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_lbp'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_PUT['id_lbp'];
   $allowedFields = [
      'nomor_rm',
      'visit_ID',
      'tgl_pelayanan',
      'uraian'
   ];
   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_PUT[$f])) {
         $fields[] = "$f=?";
         $values[] = $_PUT[$f];
      }
   }

   if (empty($fields)) {
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada data diupdate.']);
      return;
   }

   $values[] = $id;
   $types = str_repeat('s', count($values) - 1) . "i";

   $query = "UPDATE lbp_bpjs SET " . implode(',', $fields) . " WHERE id_lbp=?";
   $lbpLama = lbpAmbil($id);
   if (!$lbpLama || (isset($_PUT['visit_ID']) && !lbpVisitMilik((string) $_PUT['visit_ID']))) {
      http_response_code(404);
      echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
      return;
   }
   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
         lbpLog($id, $lbpLama, 'LBP diubah');
         echo json_encode(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Update gagal: ' . $stmt->error]);
      }
      $stmt->close();
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $koneksi->error]);
   }
}



// Function untuk Delete User
function deleteData()
{
   global $koneksi;

   // Ambil ID user dari query parameter
   $id = isset($_GET['id']) ? $_GET['id'] : '';

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      exit;
   }

   // Query untuk menghapus data user
   $query = "DELETE FROM lbp_bpjs WHERE id_lbp = ?";
   $lbpLama = lbpAmbil($id);
   if (!$lbpLama) {
      http_response_code(404);
      echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
      exit;
   }

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $id);

      if ($stmt->execute()) {
         lbpLog($id, $lbpLama, 'LBP dihapus');
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menghapus.'
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query.'
      ]);
   }
}
