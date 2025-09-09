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

   // 🔹 Ambil nomor_rm_end dari setting_clinic
   $querySetting = "SELECT nomor_rm_end FROM setting_clinic WHERE id = 1 LIMIT 1";
   $result = $koneksi->query($querySetting);

   if ($result && $row = $result->fetch_assoc()) {
      $lastRM = intval($row['nomor_rm_end']);
   } else {
      $lastRM = 0;
      $koneksi->query("INSERT INTO setting_clinic (id, nomor_rm_end) VALUES (1, 0)
                       ON DUPLICATE KEY UPDATE nomor_rm_end = nomor_rm_end");
   }

   // 🔹 Tambah nomor RM
   $newRM = $lastRM + 1;
   $nomorRM = str_pad($newRM, 6, "0", STR_PAD_LEFT); // format 6 digit

   // 🔹 Generate random patient_number unik
   do {
      $patientNumber = "PCT-" . strtoupper(bin2hex(random_bytes(4))); // contoh: PCT-A1B2C3D4
      $check = $koneksi->prepare("SELECT COUNT(*) AS cnt FROM ms_patient WHERE patient_number = ?");
      $check->bind_param("s", $patientNumber);
      $check->execute();
      $res = $check->get_result()->fetch_assoc();
      $check->close();
   } while ($res['cnt'] > 0);

   // 🔹 Update setting_clinic
   $updateSetting = $koneksi->prepare("UPDATE setting_clinic SET nomor_rm_end = ? WHERE id = 1");
   $updateSetting->bind_param("i", $newRM);
   $updateSetting->execute();
   $updateSetting->close();

   // 🔹 Field yang diizinkan
   $allowedFields = [
      'patient_name',
      'patient_gender',
      'patient_religion',
      'patient_datebirth',
      'patient_place',
      'patient_phone',
      'patient_address'
   ];

   $fields = ['patient_number', 'nomor_rm'];
   $values = [$patientNumber, $nomorRM];
   $types  = "ss";

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
         $types   .= "s";
      }
   }

   if (count($fields) <= 2) { // hanya patient_number & nomor_rm
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada data pasien yang dikirim.'
      ]);
      exit;
   }

   $placeholders = implode(', ', array_fill(0, count($fields), '?'));
   $columns = implode(', ', $fields);

   $query = "INSERT INTO ms_patient ($columns) VALUES ($placeholders)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.',
            'patient_number' => $patientNumber,
            'nomor_rm' => $nomorRM
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
 * Generate patient_number unik dengan format DCT-XXXXXX
 */
function generateDoctorNumber($koneksi)
{
   $count = 0; // inisialisasi supaya tidak merah
   do {
      $random = mt_rand(100000, 999999); // 6 digit angka
      $doctorNumber = "PST-" . $random;

      // cek ke database apakah sudah ada
      $check = $koneksi->prepare("SELECT COUNT(*) FROM ms_patient WHERE patient_number = ?");
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

   $query = "SELECT * FROM ms_patient  ORDER BY patient_name DESC";
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
   $query = "SELECT * FROM ms_patient WHERE id_patient = ?";

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
   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_patient'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_PUT['id_patient'];
   $allowedFields = [
      'patient_nik',
      'nomor_rm',
      'patient_datebirth',
      'patient_religion',
      'patient_gender',
      'patient_address',
      'patient_place',
      'patient_notes',
      'patient_provinsi',
      'patient_kabupaten',
      'patient_kecamatan',
      'patient_kelurahan',
      'patient_desa',
      'patient_phone',
      'patient_name',
      'patient_blood',
      'patient_mail',
      'patient_marital_status',
      'patient_nationality',
      'patient_education',
      'patient_occupation',
      'patient_emergency_contact_name',
      'patient_emergency_contact_relation',
      'patient_emergency_contact_phone',
      'patient_allergy',
      'patient_disability',
      'nomor_rm_any',
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

   $query = "UPDATE ms_patient SET " . implode(',', $fields) . " WHERE id_patient=?";
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
   $query = "DELETE FROM ms_patient WHERE id_patient = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $id);

      if ($stmt->execute()) {
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
