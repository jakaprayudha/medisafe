<?php
include '../../database/connect.php';
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
   case 'POST':
      if (!empty($_POST['id_inspection'])) {
         updateData();  // kalau ada id → update
      } else {
         createData();  // kalau tidak ada id → create
      }
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id']);
      } else {
         getData();
      }
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

   if (empty($_POST) && empty($_FILES)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);
      exit;
   }

   $allowedFields = [
      'id_visit',
      'inspection_name',
      'inspection_date',
      'inspection_source',
      'inspection_summary'
   ];

   $fields = ['inspection_number'];
   $values = [generateDoctorNumber($koneksi)];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   // 🔹 Handle file upload (image/pdf)
   if (!empty($_FILES['inspection_results']['name'])) {
      $uploadDir = __DIR__ . '/../../uploads/rme/'; // path folder upload
      if (!is_dir($uploadDir)) {
         mkdir($uploadDir, 0777, true);
      }

      $fileName = time() . '_' . basename($_FILES['inspection_results']['name']);
      $targetFile = $uploadDir . $fileName;

      $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
      $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];

      if (in_array($fileType, $allowedTypes)) {
         if (move_uploaded_file($_FILES['inspection_results']['tmp_name'], $targetFile)) {
            $fields[] = 'inspection_results';
            $values[] = 'uploads/rme/' . $fileName; // ✅ path sesuai folder
         } else {
            echo json_encode([
               'status' => 'error',
               'message' => 'Gagal mengunggah file ke server.'
            ]);
            exit;
         }
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Format file tidak didukung. Hanya JPG, PNG, PDF.'
         ]);
         exit;
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
   $types = str_repeat('s', count($fields));

   $query = "INSERT INTO visit_inspection ($columns) VALUES ($placeholders)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
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
 * Generate inspection_number unik dengan format DCT-XXXXXX
 */
function generateDoctorNumber($koneksi)
{
   $count = 0; // inisialisasi supaya tidak merah
   do {
      $random = mt_rand(100000, 999999); // 6 digit angka
      $doctorNumber = "DCT-" . $random;

      // cek ke database apakah sudah ada
      $check = $koneksi->prepare("SELECT COUNT(*) FROM visit_inspection WHERE inspection_number = ?");
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
   $no = isset($_GET['no']) ? mysqli_real_escape_string($koneksi, $_GET['no']) : '';
   $query = "SELECT * FROM visit_inspection WHERE id_visit = '$no'
             ORDER BY id_inspection  ASC";
   $result = mysqli_query($koneksi, $query);

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
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM visit_inspection   WHERE id_inspection = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $iduser); // Bind parameter iduser
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


function updateData()
{
   global $koneksi;

   if (empty($_POST['id_inspection'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_POST['id_inspection'];

   // Ambil data lama dulu
   $oldFile = null;
   $check = $koneksi->prepare("SELECT inspection_results FROM visit_inspection WHERE id_inspection=?");
   $check->bind_param("i", $id);
   $check->execute();
   $res = $check->get_result();
   if ($res->num_rows > 0) {
      $oldFile = $res->fetch_assoc()['inspection_results'];
   }
   $check->close();

   $allowedFields = [
      'id_visit',
      'inspection_name',
      'inspection_date',
      'inspection_source',
      'inspection_summary'
   ];
   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = "$f=?";
         $values[] = $_POST[$f];
      }
   }

   // 🔹 Handle file upload
   if (!empty($_FILES['inspection_results']['name'])) {
      $uploadDir = __DIR__ . '/../../uploads/rme/';
      if (!is_dir($uploadDir)) {
         mkdir($uploadDir, 0777, true);
      }

      $fileName = time() . '_' . basename($_FILES['inspection_results']['name']);
      $targetFile = $uploadDir . $fileName;

      $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
      $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];

      if (in_array($fileType, $allowedTypes)) {
         if (move_uploaded_file($_FILES['inspection_results']['tmp_name'], $targetFile)) {
            // hapus file lama kalau ada
            if ($oldFile && file_exists(__DIR__ . '/../../' . $oldFile)) {
               unlink(__DIR__ . '/../../' . $oldFile);
            }
            $fields[] = 'inspection_results=?';
            $values[] = 'uploads/rme/' . $fileName;
         } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengunggah file baru.']);
            return;
         }
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Format file tidak didukung.']);
         return;
      }
   }

   if (empty($fields)) {
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang diupdate.']);
      return;
   }

   $values[] = $id;
   $types = str_repeat('s', count($values) - 1) . "i";

   $query = "UPDATE visit_inspection SET " . implode(',', $fields) . " WHERE id_inspection=?";
   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);
      if ($stmt->execute()) {
         echo json_encode(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Update gagal: ' . $stmt->error]);
      }
      $stmt->close();
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $koneksi->error]);
   }
}

function deleteData()
{
   global $koneksi;

   $id = isset($_GET['id']) ? $_GET['id'] : '';

   if (empty($id)) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   // ambil file lama
   $oldFile = null;
   $check = $koneksi->prepare("SELECT inspection_results FROM visit_inspection WHERE id_inspection=?");
   $check->bind_param("i", $id);
   $check->execute();
   $res = $check->get_result();
   if ($res->num_rows > 0) {
      $oldFile = $res->fetch_assoc()['inspection_results'];
   }
   $check->close();

   $query = "DELETE FROM visit_inspection WHERE id_inspection=?";
   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("i", $id);
      if ($stmt->execute()) {
         // hapus file kalau ada
         if ($oldFile && file_exists(__DIR__ . '/../../' . $oldFile)) {
            unlink(__DIR__ . '/../../' . $oldFile);
         }
         echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus.']);
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus.']);
      }
      $stmt->close();
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query.']);
   }
}
