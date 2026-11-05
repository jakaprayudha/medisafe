<?php
include '../../database/connect.php';
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
      updateData();
      break;

   case 'DELETE':
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

   // Field valid untuk tabel pasien_imunisasi
   $allowedFields = [
      'id_visit',
      'imunisasi_name',
      'imunisasi_date',
      'imunisasi_dosis',
      'imunisasi_batch',
      'imunisasi_notes'
   ];

   $fields = ['imunisasi_number']; // tambahkan imunisasi_number
   $values = [generateImunisasiNumber($koneksi)];

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

   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);
   $types = str_repeat('s', count($fields)); // semua string

   $query = "INSERT INTO pasien_imunisasi ($columns) VALUES ($placeholders)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data imunisasi berhasil ditambahkan.'
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
 * Generate imunisasi_number unik dengan format IMN-XXXXXX
 */
function generateImunisasiNumber($koneksi)
{
   $count = 0;
   do {
      $random = mt_rand(100000, 999999); // 6 digit angka
      $number = "IMN-" . $random;

      $check = $koneksi->prepare("SELECT COUNT(*) FROM pasien_imunisasi WHERE imunisasi_number = ?");
      $check->bind_param("s", $number);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $number;
}

function getData()
{
   global $koneksi;
   // parameter "no" (nomor_visit)
   $no = isset($_GET['no']) ? mysqli_real_escape_string($koneksi, $_GET['no']) : '';
   $query = "SELECT * FROM pasien_imunisasi WHERE id_visit = '$no'
             ORDER BY id_imunisasi ASC";
   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);
      return;
   }

   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
   mysqli_free_result($result);

   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}

// Function untuk Read berdasarkan ID
function getID($iduser)
{
   global $koneksi;

   $query = "SELECT * FROM pasien_imunisasi WHERE id_imunisasi = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $iduser);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
         $data = $result->fetch_assoc();
         echo json_encode([
            'status' => 'success',
            'data' => $data
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Data tidak ditemukan.'
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

// Function untuk Update
function updateData()
{
   global $koneksi;
   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_imunisasi'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_PUT['id_imunisasi'];
   $allowedFields = [
      'imunisasi_name',
      'imunisasi_date',
      'imunisasi_dosis',
      'imunisasi_batch',
      'imunisasi_notes'
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
   $types = str_repeat('s', count($values));

   $query = "UPDATE pasien_imunisasi SET " . implode(',', $fields) . " WHERE id_imunisasi=?";
   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
         echo json_encode(['status' => 'success', 'message' => 'Data imunisasi berhasil diperbarui.']);
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Update gagal: ' . $stmt->error]);
      }
      $stmt->close();
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $koneksi->error]);
   }
}

// Function untuk Delete
function deleteData()
{
   global $koneksi;

   $id = isset($_GET['id']) ? $_GET['id'] : '';

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      exit;
   }

   $query = "DELETE FROM pasien_imunisasi WHERE id_imunisasi = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $id);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data imunisasi berhasil dihapus.'
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
