<?php
session_start();
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
// 🔹 CREATE VISIT
function createData()
{
   global $koneksi;

   if (empty($_POST['id_patient']) || empty($_POST['id_doctor']) || empty($_POST['id_poli'])) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data wajib diisi.'
      ]);
      return;
   }

   $id_patient   = $_POST['id_patient'];
   $id_doctor    = $_POST['id_doctor'];
   $id_poli      = $_POST['id_poli'];
   $source_hub   = $_POST['source_hub'];
   $visit_notes  = $_POST['visit_notes'] ?? null;
   $visit_date   = date('Y-m-d');
   $visit_time   = date('H:i:s');
   $user         = $_POST['user'];
   $checkrm = mysqli_query($koneksi, "SELECT nomor_rm FROM ms_patient WHERE id_patient='$id_patient' ");
   $datarm = mysqli_fetch_array($checkrm);
   $nomor_rm = $datarm['nomor_rm'];

   // 🔹 Generate kode unik
   $visit_ID = "VIS-" . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

   // 🔹 Hitung nomor antrian hari ini
   $sql_antrian = "SELECT COUNT(*) as total FROM pasien_visit WHERE visit_date = ?";
   $stmt_antrian = $koneksi->prepare($sql_antrian);
   $stmt_antrian->bind_param("s", $visit_date);
   $stmt_antrian->execute();
   $result = $stmt_antrian->get_result()->fetch_assoc();
   $stmt_antrian->close();

   $nomor_urut = $result['total'] + 1;
   $visit_antrian = str_pad($nomor_urut, 3, "0", STR_PAD_LEFT);

   // 🔹 Vital Sign
   $kondisi_masuk  = $_POST['kondisi_masuk'];
   $tekanan_darah  = $_POST['tekanan_darah'];
   $suhu           = $_POST['suhu'];
   $nadi           = $_POST['nadi'];
   $respirasi      = $_POST['respirasi'];
   $tinggi         = $_POST['tinggi'];
   $berat          = $_POST['berat'];
   $bmi            = $_POST['bmi'];
   $bmi_ket        = $_POST['bmi_ket'];

   // 🔹 Mulai transaksi
   $koneksi->begin_transaction();

   try {
      // Simpan ke pasien_visit
      $query1 = "INSERT INTO pasien_visit 
                   (id_patient, visit_ID, visit_date, visit_time, id_doctor, id_poli, visit_notes, created_at, created_user, visit_status, source_hub, visit_antrian) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, 0, ?, ?)";
      $stmt1 = $koneksi->prepare($query1);
      $stmt1->bind_param(
         "ssssssssss",
         $id_patient,
         $visit_ID,
         $visit_date,
         $visit_time,
         $id_doctor,
         $id_poli,
         $visit_notes,
         $user,
         $source_hub,
         $visit_antrian
      );
      if (!$stmt1->execute()) {
         throw new Exception("Gagal simpan pasien_visit: " . $stmt1->error);
      }
      $stmt1->close();

      // Simpan ke visit_pemeriksaan
      $query2 = "INSERT INTO visit_pemeriksaan 
           (nomor_rm, nomor_visit, kondisi_masuk, tekanan_darah, suhu, nadi, respirasi, tinggi, berat, created_at, bmi, bmi_ket) 
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";

      $stmt2 = $koneksi->prepare($query2);

      $stmt2->bind_param(
         "ssssdiiddds",   // ✅ 11 format sesuai 11 variabel
         $nomor_rm,     // varchar → s
         $visit_ID,       // varchar → s
         $kondisi_masuk,  // varchar → s
         $tekanan_darah,  // varchar → s
         $suhu,           // decimal → d
         $nadi,           // int → i
         $respirasi,      // int → i
         $tinggi,         // decimal → d
         $berat,          // decimal → d
         $bmi,            // float → d
         $bmi_ket         // varchar → s
      );
      if (!$stmt2->execute()) {
         throw new Exception("Gagal simpan vital sign: " . $stmt2->error);
      }
      $stmt2->close();

      // ✅ Commit transaksi
      $koneksi->commit();

      echo json_encode([
         'status'  => 'success',
         'message' => 'Kunjungan & vital sign berhasil ditambahkan.',
         'antrian' => $visit_antrian
      ]);
   } catch (Exception $e) {
      // ❌ Rollback jika ada error
      $koneksi->rollback();
      echo json_encode([
         'status'  => 'error',
         'message' => $e->getMessage()
      ]);
   }
}


function getData()
{
   global $koneksi;

   $query = "SELECT * FROM permintaan_ranap rn INNER JOIN ms_patient mp ON rn.id_patient = mp.id_patient INNER JOIN pasien_visit pv ON rn.visit_ID_inpatient = pv.visit_ID INNER JOIN ms_doctor dc ON rn.id_doctor = dc.id_doctor WHERE rn.ranap_booking=0 ORDER BY rn.id_ranap DESC";
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
   $query = "SELECT * FROM permintaan_ranap WHERE id_ranap = ?";

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
// 🔹 UPDATE VISIT
function updateData()
{
   global $koneksi;
   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_visit'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $id         = $_PUT['id_visit'];
   $id_doctor  = $_PUT['id_doctor'] ?? null;
   $id_poli    = $_PUT['id_poli'] ?? null;
   $notes      = $_PUT['visit_notes'] ?? null;

   $query = "UPDATE pasien_visit SET id_doctor=?, id_poli=?, visit_notes=? WHERE id_visit=?";
   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("sssi", $id_doctor, $id_poli, $notes, $id);

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

// 🔹 DELETE VISIT
function deleteData()
{
   global $koneksi;
   $id = $_GET['id'] ?? null;

   if (!$id) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan.']);
      return;
   }

   $query = "DELETE FROM pasien_visit WHERE id_visit=?";
   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("i", $id);

      if ($stmt->execute()) {
         echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus.']);
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus: ' . $stmt->error]);
      }
      $stmt->close();
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $koneksi->error]);
   }
}
