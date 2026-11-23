<?php
require_once __DIR__ . '/../../database/connect.php';
header('Content-Type: application/json; charset=utf-8');
$id_customer = $_SESSION['id_customer'] ?? null;

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak ditemukan'
   ]);
   exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'POST':
      if (!empty($_POST['id_inspection'])) {
         updateData();
      } else {
         createData();
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
      deleteData();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

/**
 * Aturan form pemeriksaan penunjang: sumber hasil selalu "Lab Klinik" (dikunci) dan catatan wajib
 * berupa teks tanpa angka. Mengembalikan pesan error, atau null bila valid (nilai $_POST dinormalkan).
 */
function validasiPenunjang(): ?string
{
   $_POST['inspection_source'] = 'Lab Klinik';
   $catatan = trim((string) ($_POST['inspection_note'] ?? ''));
   if ($catatan === '') return 'Catatan wajib diisi.';
   if (preg_match('/\d/', $catatan)) return 'Catatan tidak boleh berisi angka.';
   if (!preg_match('/\p{L}/u', $catatan)) return 'Catatan harus berupa teks.';
   $_POST['inspection_note'] = mb_substr($catatan, 0, 2000);
   return null;
}

# ================= CREATE =================
function createData()
{
   global $koneksi, $id_customer;

   if ($pesan = validasiPenunjang()) {
      echo json_encode(['status' => 'error', 'message' => $pesan]);
      return;
   }

   $allowedFields = [
      'id_visit',
      'inspection_name',
      'inspection_date',
      'inspection_source',
      'inspection_note'
   ];

   $fields = ['inspection_number', 'id_customer'];
   $values = [generateDoctorNumber($koneksi), $id_customer];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = $f;
         $values[] = $_POST[$f];
      }
   }

   $placeholders = implode(',', array_fill(0, count($fields), '?'));
   $columns = implode(',', $fields);
   $types = str_repeat('s', count($fields));

   $query = "INSERT INTO visit_inspection ($columns) VALUES ($placeholders)";
   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => $stmt->error
         ]);
      }

      $stmt->close();
   }
}

# ================= READ ALL =================
function getData()
{
   global $koneksi, $id_customer;

   $id_patient = $_GET['id_patient'] ?? '';
   $no = $_GET['no'] ?? '';
   $stmt = $koneksi->prepare("
SELECT
    vi.*,
    pv.visit_ID,
    pv.visit_date,

    -- 1 baris per pemeriksaan. Dulu LEFT JOIN laboratorium_result langsung -> pemeriksaan multi-parameter
    -- (mis. Widal) muncul berulang, 1 baris per parameter.
    CASE WHEN COALESCE(lr.terisi, 0) = 1 THEN lr.hasil_satu
         WHEN COALESCE(lr.terisi, 0) > 1 THEN CONCAT(lr.terisi, ' parameter terisi') END AS hasil,
    CASE WHEN COALESCE(lr.terisi, 0) = 1 THEN lr.keterangan_satu END AS keterangan,
    COALESCE(lr.terisi, 0) AS hasil_terisi,

    CASE
        WHEN pv.visit_ID = ? THEN 1
        ELSE 0
    END AS is_current

FROM visit_inspection vi

INNER JOIN pasien_visit pv
    ON pv.visit_ID = vi.id_visit

LEFT JOIN (
    SELECT id_inspection,
           COUNT(CASE WHEN TRIM(COALESCE(hasil, '')) <> '' THEN 1 END) AS terisi,
           MAX(CASE WHEN TRIM(COALESCE(hasil, '')) <> '' THEN hasil END) AS hasil_satu,
           MAX(CASE WHEN TRIM(COALESCE(hasil, '')) <> '' THEN keterangan END) AS keterangan_satu
    FROM laboratorium_result
    WHERE id_inspection IN (
        SELECT vi2.id_inspection FROM visit_inspection vi2
        INNER JOIN pasien_visit pv2 ON pv2.visit_ID = vi2.id_visit
        WHERE pv2.id_patient = ? AND pv2.id_customer = ?
    )
    GROUP BY id_inspection
) lr ON lr.id_inspection = vi.id_inspection
WHERE pv.id_patient = ?
  AND pv.id_customer = ?

ORDER BY
    is_current DESC,
    vi.inspection_date DESC,
    vi.id_inspection DESC
");

   $stmt->bind_param("sssss", $no, $id_patient, $id_customer, $id_patient, $id_customer);
   $stmt->execute();

   $result = $stmt->get_result();

   echo json_encode([
      'status' => 'success',
      'data' => $result->fetch_all(MYSQLI_ASSOC)
   ]);

   $stmt->close();
}
# ================= READ BY ID =================
function getID($id)
{
   global $koneksi, $id_customer;

   $stmt = $koneksi->prepare("
      SELECT * FROM visit_inspection 
      WHERE id_inspection=? AND id_customer=?
   ");

   $stmt->bind_param("is", $id, $id_customer);
   $stmt->execute();

   $result = $stmt->get_result();

   if ($result->num_rows > 0) {
      echo json_encode([
         'status' => 'success',
         'data' => $result->fetch_assoc()
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan'
      ]);
   }

   $stmt->close();
}

# ================= UPDATE =================
function updateData()
{
   global $koneksi, $id_customer;

   $id = $_POST['id_inspection'] ?? null;

   if (!$id) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan'
      ]);
      return;
   }

   if ($pesan = validasiPenunjang()) {
      echo json_encode(['status' => 'error', 'message' => $pesan]);
      return;
   }

   $allowedFields = [
      'id_visit',
      'inspection_name',
      'inspection_date',
      'inspection_source',
      'inspection_note'
   ];

   $fields = [];
   $values = [];

   foreach ($allowedFields as $f) {
      if (isset($_POST[$f])) {
         $fields[] = "$f=?";
         $values[] = $_POST[$f];
      }
   }

   if (empty($fields)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada data diupdate'
      ]);
      return;
   }

   $values[] = $id;
   $values[] = $id_customer;

   $types = str_repeat('s', count($fields)) . "is";

   $query = "UPDATE visit_inspection SET " . implode(',', $fields) . " 
             WHERE id_inspection=? AND id_customer=?";

   $stmt = $koneksi->prepare($query);

   if ($stmt) {
      $stmt->bind_param($types, ...$values);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil diperbarui'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => $stmt->error
         ]);
      }

      $stmt->close();
   }
}

# ================= DELETE =================
function deleteData()
{
   global $koneksi, $id_customer;

   $id = $_GET['id'] ?? null;

   if (!$id) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan'
      ]);
      return;
   }

   $stmt = $koneksi->prepare("
      DELETE FROM visit_inspection 
      WHERE id_inspection=? AND id_customer=?
   ");

   $stmt->bind_param("is", $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Data berhasil dihapus'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => $stmt->error
      ]);
   }

   $stmt->close();
}

# ================= GENERATE NUMBER =================
function generateDoctorNumber($koneksi)
{
   $count = 0;
   do {
      $random = mt_rand(100000, 999999);
      $number = "DCT-" . $random;

      $check = $koneksi->prepare("
         SELECT COUNT(*) FROM visit_inspection 
         WHERE inspection_number=?
      ");

      $check->bind_param("s", $number);
      $check->execute();
      $check->bind_result($count);
      $check->fetch();
      $check->close();
   } while ($count > 0);

   return $number;
}
