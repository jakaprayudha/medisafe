<?php
include '../../database/connect.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

// 🔐 VALIDASI SESSION
if (!isset($_SESSION['id_customer'])) {
   http_response_code(401);
   echo json_encode([
      'status' => 'error',
      'message' => 'Session tidak valid / expired',
   ]);
   exit;
}

$id_customer = $_SESSION['id_customer'];

$method = $_SERVER['REQUEST_METHOD'];

// 🔥 HANDLE FAKE PUT (INI YANG BELUM ADA)
if ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
   updateData($id_customer);
   exit;
}
switch ($method) {
   case 'POST':
      createData($id_customer);
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id'], $id_customer);
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
}

// ================= CREATE =================
function createData($id_customer)
{
   global $koneksi;

   header('Content-Type: application/json');

   // ================== AMBIL DATA ==================
   $raw  = file_get_contents("php://input");
   $json = json_decode($raw, true);

   if (!empty($json)) {
      $data = $json;
   } else {
      $data = $_POST;
      if (empty($data) && !empty($raw)) {
         parse_str($raw, $data);
      }
   }

   if (empty($data)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data kosong'
      ]);
      exit;
   }

   // ================== VALIDASI ==================
   $required = ['patient_name', 'patient_gender'];

   foreach ($required as $r) {
      if (empty($data[$r])) {
         echo json_encode([
            'status' => 'error',
            'message' => "$r wajib diisi"
         ]);
         exit;
      }
   }

   // ================== TRANSACTION ==================
   $koneksi->begin_transaction();

   try {

      // ================== AMBIL & LOCK NOMOR RM ==================
      $stmt = $koneksi->prepare(
         "SELECT nomor_rm_end FROM setting_clinic 
          WHERE id_customer=? FOR UPDATE"
      );
      $stmt->bind_param("i", $id_customer);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($row = $result->fetch_assoc()) {
         $lastRM = (int)$row['nomor_rm_end'];
      } else {
         $lastRM = 0;

         $insert = $koneksi->prepare(
            "INSERT INTO setting_clinic (id_customer, nomor_rm_end) VALUES (?,0)"
         );
         $insert->bind_param("i", $id_customer);
         $insert->execute();
         $insert->close();
      }
      $stmt->close();

      // ================== GENERATE NOMOR RM ==================
      $newRM   = $lastRM + 1;
      $nomorRM = str_pad($newRM, 6, "0", STR_PAD_LEFT);

      // ================== GENERATE PATIENT NUMBER ==================
      $count = 0;
      do {
         $patientNumber = "PCT-" . strtoupper(bin2hex(random_bytes(4)));

         $check = $koneksi->prepare(
            "SELECT COUNT(*) FROM ms_patient WHERE patient_number=?"
         );
         $check->bind_param("s", $patientNumber);
         $check->execute();
         $check->bind_result($count);
         $check->fetch();
         $check->close();
      } while ($count > 0);

      // ================== UPDATE NOMOR RM ==================
      $update = $koneksi->prepare(
         "UPDATE setting_clinic 
          SET nomor_rm_end=? 
          WHERE id_customer=?"
      );
      $update->bind_param("ii", $newRM, $id_customer);
      $update->execute();
      $update->close();

      // ================== INSERT DATA PASIEN ==================
      $allowedFields = [
         'patient_name',
         'patient_gender',
         'patient_religion',
         'patient_datebirth',
         'patient_place',
         'patient_phone',
         'patient_address',
         'patient_nik',
         'patient_bpjs',

         // 🔥 TAMBAHAN
         'patient_provinsi',
         'patient_kabupaten',
         'patient_kecamatan',
         'patient_kelurahan'
      ];

      $fields = ['patient_number', 'nomor_rm', 'id_customer'];
      $values = [$patientNumber, $nomorRM, $id_customer];
      $types  = "ssi";

      foreach ($allowedFields as $f) {
         if (isset($data[$f])) {
            $fields[] = $f;
            $values[] = $data[$f];
            $types   .= "s";
         }
      }

      $placeholders = implode(',', array_fill(0, count($fields), '?'));
      $columns      = implode(',', $fields);

      $stmt = $koneksi->prepare("INSERT INTO ms_patient ($columns) VALUES ($placeholders)");
      $stmt->bind_param($types, ...$values);

      if (!$stmt->execute()) {
         throw new Exception($stmt->error);
      }

      $stmt->close();

      // ================== COMMIT ==================
      $koneksi->commit();
      unset($_SESSION['pasien_total']);

      echo json_encode([
         'status' => 'success',
         'message' => 'Data pasien berhasil disimpan',
         'patient_number' => $patientNumber,
         'nomor_rm' => $nomorRM
      ]);
   } catch (Exception $e) {

      $koneksi->rollback();

      echo json_encode([
         'status' => 'error',
         'message' => $e->getMessage()
      ]);
   }
}

// ================= READ =================
// Server-side DataTables (module/admin/patient = module/admisi/registrasi-new).
// Parameter: draw, start, length, q (cari), filter (bpjs|umum|wajah|tanpa_wajah), sort (baru|lama|nama|rm)
// ?stats=1 -> ringkasan jumlah pasien faskes.
function getData($id_customer)
{
   global $koneksi;
   mysqli_report(MYSQLI_REPORT_OFF);
   $idc = (int) $id_customer;

   $ambil = function (string $sql, string $types, array $params) use ($koneksi): array {
      $stmt = $koneksi->prepare($sql);
      if (!$stmt) {
         error_log('[patientContrroller] ' . $koneksi->error);
         http_response_code(500);
         echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil data pasien.']);
         exit;
      }
      $stmt->bind_param($types, ...$params);
      $stmt->execute();
      $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
      $stmt->close();
      return $rows;
   };

   if (isset($_GET['stats'])) {
      $r = $ambil(
         "SELECT COUNT(*) total,
                 SUM(patient_bpjs IS NOT NULL AND patient_bpjs <> '') bpjs,
                 SUM(face_image IS NOT NULL AND face_image <> '') wajah,
                 SUM(idsh IS NOT NULL AND idsh <> '') ihs,
                 SUM(patient_nik IS NOT NULL AND patient_nik <> '') nik
          FROM ms_patient WHERE id_customer = ?",
         'i',
         [$idc]
      )[0];
      $_SESSION['pasien_total'] = ['idc' => $idc, 'n' => (int) $r['total'], 't' => time()];
      echo json_encode(['status' => 'success', 'data' => array_map('intval', $r)]);
      return;
   }

   $draw = (int) ($_GET['draw'] ?? 1);
   $start = max(0, (int) ($_GET['start'] ?? 0));
   $length = (int) ($_GET['length'] ?? 10);
   $length = $length < 1 ? 10 : min($length, 100);

   // ---------- filter ----------
   $where = ['id_customer = ?'];
   $types = 'i';
   $params = [$idc];

   switch ($_GET['filter'] ?? '') {
      case 'bpjs':
         $where[] = "patient_bpjs IS NOT NULL AND patient_bpjs <> ''";
         break;
      case 'umum':
         $where[] = "(patient_bpjs IS NULL OR patient_bpjs = '')";
         break;
      case 'wajah':
         $where[] = "face_image IS NOT NULL AND face_image <> ''";
         break;
      case 'tanpa_wajah':
         $where[] = "(face_image IS NULL OR face_image = '')";
         break;
   }

   // ---------- pencarian ----------
   $q = trim((string) ($_GET['q'] ?? ($_GET['search']['value'] ?? '')));
   $q = mb_substr(preg_replace('/\s+/', ' ', $q), 0, 60);
   if ($q !== '') {
      $esc = fn($v) => addcslashes($v, '%_\\');
      $digit = preg_replace('/[\s.\-]/', '', $q);
      if ($digit !== '' && ctype_digit($digit)) {
         // angka: NIK / BPJS dari depan, RM & HP mengandung
         $where[] = '(patient_nik LIKE ? OR patient_bpjs LIKE ? OR nomor_rm LIKE ? OR patient_phone LIKE ?)';
         $types .= 'ssss';
         array_push($params, $esc($digit) . '%', $esc($digit) . '%', '%' . $esc($q) . '%', '%' . $esc($digit) . '%');
      } else {
         // teks: setiap kata harus ada di nama (atau cocok dengan No. RM)
         $kata = [];
         foreach (explode(' ', $q) as $w) {
            $kata[] = 'patient_name LIKE ?';
            $types .= 's';
            $params[] = '%' . $esc($w) . '%';
         }
         $where[] = '((' . implode(' AND ', $kata) . ') OR nomor_rm LIKE ?)';
         $types .= 's';
         $params[] = '%' . $esc($q) . '%';
      }
   }
   $whereSql = implode(' AND ', $where);
   $adaFilter = count($where) > 1;

   // ---------- urutan (whitelist) ----------
   $urut = [
      'baru' => 'id_patient DESC',
      'lama' => 'id_patient ASC',
      'nama' => 'patient_name ASC, id_patient DESC',
      'rm' => 'nomor_rm ASC, id_patient DESC',
   ];
   $sort = $urut[$_GET['sort'] ?? 'baru'] ?? $urut['baru'];

   // ---------- hitung ----------
   // total pasien faskes di-cache 60 detik di sesi (dibuang saat tambah/hapus pasien)
   $cache = $_SESSION['pasien_total'] ?? null;
   if (is_array($cache) && $cache['idc'] === $idc && $cache['t'] > time() - 60) {
      $recordsTotal = $cache['n'];
   } else {
      $recordsTotal = (int) $ambil("SELECT COUNT(*) n FROM ms_patient WHERE id_customer = ?", 'i', [$idc])[0]['n'];
      $_SESSION['pasien_total'] = ['idc' => $idc, 'n' => $recordsTotal, 't' => time()];
   }
   $recordsFiltered = $adaFilter
      ? (int) $ambil("SELECT COUNT(*) n FROM ms_patient WHERE $whereSql", $types, $params)[0]['n']
      : $recordsTotal;

   // ---------- data (deferred join: urut & potong di id saja, baru ambil kolom) ----------
   $sortP = preg_replace('/\b(id_patient|patient_name|nomor_rm)\b/', 'p.$1', $sort);
   $data = $recordsFiltered === 0 ? [] : $ambil(
      "SELECT p.id_patient, p.nomor_rm, p.patient_name, p.patient_nik, p.patient_bpjs, p.patient_gender,
              p.patient_datebirth, p.patient_phone, p.face_image, p.idsh, p.patient_address, p.patient_kabupaten
       FROM (SELECT id_patient FROM ms_patient WHERE $whereSql ORDER BY $sort LIMIT ?, ?) x
       JOIN ms_patient p ON p.id_patient = x.id_patient
       ORDER BY $sortP",
      $types . 'ii',
      array_merge($params, [$start, $length])
   );

   // ---------- kunjungan per pasien di halaman ini ----------
   if ($data) {
      $ids = array_map(fn($r) => (string) $r['id_patient'], $data);
      $ph = implode(',', array_fill(0, count($ids), '?'));
      $kunj = [];
      foreach ($ambil(
         "SELECT id_patient, COUNT(*) n, MAX(visit_date) terakhir FROM pasien_visit
          WHERE id_customer = ? AND id_patient IN ($ph) GROUP BY id_patient",
         'i' . str_repeat('s', count($ids)),
         array_merge([$idc], $ids)
      ) as $k) {
         $kunj[(string) $k['id_patient']] = $k;
      }
      foreach ($data as &$r) {
         $k = $kunj[(string) $r['id_patient']] ?? null;
         $r['jml_kunjungan'] = $k ? (int) $k['n'] : 0;
         $r['kunjungan_terakhir'] = $k['terakhir'] ?? null;
      }
      unset($r);
   }

   echo json_encode([
      'draw' => $draw,
      'recordsTotal' => $recordsTotal,
      'recordsFiltered' => $recordsFiltered,
      'data' => $data,
   ]);
}

// ================= READ BY ID =================
function getID($id, $id_customer)
{
   global $koneksi;

   $stmt = $koneksi->prepare(
      "SELECT * FROM ms_patient 
       WHERE id_patient=? AND id_customer=?"
   );

   $stmt->bind_param("ii", $id, $id_customer);
   $stmt->execute();

   $res = $stmt->get_result();

   if ($res->num_rows > 0) {
      echo json_encode([
         'status' => 'success',
         'data' => $res->fetch_assoc()
      ]);
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Tidak ditemukan']);
   }

   $stmt->close();
}

// ================= UPDATE =================
function updateData($id_customer)
{
   global $koneksi;

   $_PUT = $_POST; // 🔥 FIX UTAMA

   if (empty($_PUT['id_patient'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
      return;
   }

   $id = $_PUT['id_patient'];

   $allowedFields = [
      'patient_name',
      'patient_phone',
      'patient_address',
      'patient_gender',
      'patient_datebirth',
      'patient_nik',
      'patient_bpjs',
      'patient_religion',
      'patient_place',
      'patient_provinsi',
      'patient_kabupaten',
      'patient_kecamatan',
      'patient_kelurahan'
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
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada update']);
      return;
   }

   $values[] = $id;
   $values[] = $id_customer;

   $types = str_repeat('s', count($values) - 2) . "ii";

   $query = "UPDATE ms_patient SET " . implode(',', $fields) . " 
             WHERE id_patient=? AND id_customer=?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param($types, ...$values);

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success', 'message' => 'Update berhasil']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
   }

   $stmt->close();
}
// ================= DELETE =================
// Pasien yang sudah punya kunjungan TIDAK boleh dihapus: kunjungan, resep, billing dan
// rekam medis wajib tersimpan. (Sebelumnya ada mode force yang ikut menghapus semuanya.)
function deleteData($id_customer)
{
   global $koneksi;

   $id = (int) ($_GET['id'] ?? 0);
   $idc = (int) $id_customer;
   if ($id <= 0) {
      echo json_encode(['status' => 'error', 'message' => 'ID kosong']);
      return;
   }

   $stmt = $koneksi->prepare("SELECT patient_name FROM ms_patient WHERE id_patient = ? AND id_customer = ?");
   $stmt->bind_param('ii', $id, $idc);
   $stmt->execute();
   $pasien = $stmt->get_result()->fetch_assoc();
   $stmt->close();
   if (!$pasien) {
      http_response_code(404);
      echo json_encode(['status' => 'error', 'message' => 'Data pasien tidak ditemukan']);
      return;
   }

   $idStr = (string) $id;
   $stmt = $koneksi->prepare("SELECT COUNT(*) n FROM pasien_visit WHERE id_patient = ? AND id_customer = ?");
   $stmt->bind_param('si', $idStr, $idc);
   $stmt->execute();
   $jml = (int) $stmt->get_result()->fetch_assoc()['n'];
   $stmt->close();
   if ($jml > 0) {
      http_response_code(409);
      echo json_encode([
         'status' => 'error',
         'message' => "Pasien {$pasien['patient_name']} sudah memiliki $jml kunjungan. Data rekam medis wajib disimpan sehingga pasien tidak dapat dihapus.",
      ]);
      return;
   }

   $stmt = $koneksi->prepare("DELETE FROM ms_patient WHERE id_patient = ? AND id_customer = ?");
   $stmt->bind_param('ii', $id, $idc);
   $ok = $stmt->execute();
   $stmt->close();
   if ($ok) unset($_SESSION['pasien_total']);
   echo json_encode($ok
      ? ['status' => 'success', 'message' => 'Data pasien berhasil dihapus']
      : ['status' => 'error', 'message' => 'Gagal menghapus data']);
}
