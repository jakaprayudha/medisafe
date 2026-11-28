<?php
include '../../database/connect.php';
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
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



function getData()
{
    global $koneksi;

    // =========================
    // PARAMETER FILTER
    // =========================
    $fromDate   = $_GET['fromDate']   ?? null;
    $toDate     = $_GET['toDate']     ?? null;
    $source_hub = $_GET['source_hub'] ?? null;

    // =========================
    // BASE QUERY
    // =========================
    $query = "
        SELECT 
            pasien_visit.*, 
            ms_patient.*, 
            ms_doctor.*, 
            ms_poli.*,
            ms_provider.provider_name,
            CASE 
                WHEN pasien_visit.tekanan_darah IS NOT NULL AND pasien_visit.tekanan_darah <> '' THEN 1
                ELSE 0
            END AS status_dilayani
        FROM pasien_visit
        INNER JOIN ms_patient 
            ON ms_patient.id_patient = pasien_visit.id_patient
        LEFT JOIN ms_doctor 
            ON ms_doctor.id_doctor = pasien_visit.id_doctor
        LEFT JOIN ms_poli 
            ON ms_poli.id_poli = pasien_visit.id_poli
      LEFT JOIN ms_provider 
            ON ms_provider.id_provider = pasien_visit.id_provider
        WHERE 1=1
    ";

    // =========================
    // PREPARED PARAM
    // =========================
    $params = [];
    $types  = "";

    // =========================
    // FILTER TANGGAL
    // =========================
    if ($fromDate && $toDate) {
        $query   .= " AND DATE(pasien_visit.visit_date) BETWEEN ? AND ?";
        $params[] = $fromDate;
        $params[] = $toDate;
        $types   .= "ss";
    }

    // =========================
    // FILTER SOURCE HUB
    // =========================
    if (!empty($source_hub)) {
        $query   .= " AND pasien_visit.source_hub = ?";
        $params[] = $source_hub;
        $types   .= "s";
    }

    // =========================
    // ORDER
    // =========================
    $query .= " ORDER BY pasien_visit.visit_date ASC";

    // =========================
    // PREPARE & EXECUTE
    // =========================
    $stmt = $koneksi->prepare($query);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Prepare failed: ' . $koneksi->error
        ]);
        return;
    }

    // Bind param hanya jika ada
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data   = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // =========================
    // RESPONSE
    // =========================
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'data'   => $data
    ]);
}

// Function untuk Read User berdasarkan ID
function  getID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM pasien_visit WHERE id_visit = ? AND id_customer = ?"; // hanya kunjungan faskes sesi

   if ($stmt = $koneksi->prepare($query)) {
      $idcSesi = (int) ($_SESSION['id_customer'] ?? 0);
      $stmt->bind_param("si", $iduser, $idcSesi);
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

   if (empty($_PUT['id_visit'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id = $_PUT['id_visit'];
   $allowedFields = [
      'id_doctor',
      'id_poli',
      'visit_notes',
      'id_provider'
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
   $values[] = (int) ($_SESSION['id_customer'] ?? 0);
   $types = str_repeat('s', count($values) - 2) . "ii";

   // hanya kunjungan faskes sesi (dulu kunjungan faskes mana pun bisa diubah lewat id_visit)
   $query = "UPDATE pasien_visit SET " . implode(',', $fields) . " WHERE id_visit=? AND id_customer=?";
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
   require_once __DIR__ . '/../dinkes/dinkesService.php';
   $dkVid = dkVisitIdDikirim($koneksi, (int) ($_SESSION['id_customer'] ?? 0), $id); // Dinkes
   require_once __DIR__ . '/../../utility/visitLog.php';
   $idcHapus = (int) ($_SESSION['id_customer'] ?? 0);
   $snapHapus = logKunjunganSnapshot($koneksi, 'id_visit', $id, $idcHapus); // data kunjungan sebelum dihapus
   // hanya kunjungan faskes sesi (dulu kunjungan faskes mana pun bisa dihapus lewat id_visit)
   $query = "DELETE FROM pasien_visit WHERE id_visit = ? AND id_customer = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("si", $id, $idcHapus);

      if ($stmt->execute() && $stmt->affected_rows > 0) { // 0 baris = bukan kunjungan faskes ini / sudah terhapus
      if ($dkVid) dkHapusVisit($koneksi, (int) ($_SESSION['id_customer'] ?? 0), $dkVid); // Dinkes
      if ($stmt->affected_rows > 0 && $snapHapus) logKunjungan($koneksi, 'hapus', $snapHapus, 'Kunjungan dihapus permanen dari menu Registrasi admisi', $snapHapus);
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Data tidak ditemukan di faskes ini atau gagal dihapus.'
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
