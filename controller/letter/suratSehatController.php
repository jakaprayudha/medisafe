<?php

session_start();

include '../../database/connect.php';

header('Content-Type: application/json');


/*
|--------------------------------------------------------------------------
| VALIDASI SESSION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id_customer'])) {

   echo json_encode([
      'status'  => 'error',
      'message' => 'Session faskes tidak ditemukan.'
   ]);

   exit;
}

$id_customer = $_SESSION['id_customer'];

$method = $_SERVER['REQUEST_METHOD'];


/*
|--------------------------------------------------------------------------
| ROUTING METHOD
|--------------------------------------------------------------------------
*/

switch ($method) {

   case 'POST':

      createData($id_customer);

      break;


   case 'GET':

      if (isset($_GET['id']) && $_GET['id'] !== '') {

         getID(
            $_GET['id'],
            $id_customer
         );
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

      echo json_encode([
         'status'  => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);

      break;
}


/*
|--------------------------------------------------------------------------
| CREATE
|--------------------------------------------------------------------------
*/

function createData($id_customer)
{
   global $koneksi;

   /*
    |--------------------------------------------------------------------------
    | AMBIL DATA POST
    |--------------------------------------------------------------------------
    */

   $id_patient = $_POST['id_patient'] ?? '';
   $id_visit   = $_POST['id_visit'] ?? '';

   $tanggal_surat = $_POST['tanggal_surat'] ?? date('Y-m-d');

   $tekanan_darah = $_POST['tekanan_darah'] ?? '';
   $nadi          = $_POST['nadi'] ?? '';
   $berat_badan   = $_POST['berat_badan'] ?? '';
   $tinggi_badan  = $_POST['tinggi_badan'] ?? '';

   $keperluan = $_POST['keperluan'] ?? '';
   $keterangan = $_POST['keterangan'] ?? '';


   /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

   if (empty($id_patient)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Pasien wajib dipilih.'
      ]);

      return;
   }


   if (empty($id_visit)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Visit pasien tidak ditemukan.'
      ]);

      return;
   }


   if (empty($tanggal_surat)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Tanggal surat wajib diisi.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | VALIDASI VISIT
    |
    | Pastikan visit memang milik pasien dan customer yang sedang login.
    |--------------------------------------------------------------------------
    */

   $checkVisit = $koneksi->prepare("
        SELECT
            pv.id_visit,
            pv.id_patient
        FROM pasien_visit pv
        WHERE pv.id_visit = ?
          AND pv.id_patient = ?
          AND pv.id_customer = ?
        LIMIT 1
    ");

   if (!$checkVisit) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Prepare validasi visit gagal: ' . $koneksi->error
      ]);

      return;
   }


   $checkVisit->bind_param(
      "ssi",
      $id_visit,
      $id_patient,
      $id_customer
   );

   $checkVisit->execute();

   $visitResult = $checkVisit->get_result();

   if ($visitResult->num_rows === 0) {

      $checkVisit->close();

      echo json_encode([
         'status'  => 'error',
         'message' => 'Visit pasien tidak valid atau bukan milik fasilitas kesehatan ini.'
      ]);

      return;
   }

   $checkVisit->close();


   /*
|--------------------------------------------------------------------------
| NOMOR SURAT OTOMATIS PER CUSTOMER
|--------------------------------------------------------------------------
|
| Contoh:
|
| Customer 1:
| SKS/20260812/0001
| SKS/20260812/0002
| SKS/20260812/0003
|
| Customer 2:
| SKS/20260812/0001
| SKS/20260812/0002
|
|--------------------------------------------------------------------------
*/

   $tanggalNomor = date(
      'Ymd',
      strtotime($tanggal_surat)
   );

   $prefix = "SKS/" . $tanggalNomor . "/";

   $likeNomor = $prefix . "%";


   /*
|--------------------------------------------------------------------------
| CARI NOMOR TERAKHIR
|
| Customer diambil dari pasien_visit
|--------------------------------------------------------------------------
*/

   $stmtNomor = $koneksi->prepare("
    SELECT
        ss.nomor_surat

    FROM surat_sehat ss

    INNER JOIN pasien_visit pv
        ON pv.id_visit = ss.id_visit

    WHERE pv.id_customer = ?
      AND ss.nomor_surat LIKE ?

    ORDER BY ss.id DESC

    LIMIT 1
");


   if (!$stmtNomor) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare nomor surat gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmtNomor->bind_param(
      "is",
      $id_customer,
      $likeNomor
   );


   $stmtNomor->execute();


   $resultNomor = $stmtNomor->get_result();


   /*
|--------------------------------------------------------------------------
| DEFAULT
|--------------------------------------------------------------------------
|
| Kalau customer belum punya nomor pada tanggal tersebut
| maka mulai dari 1.
|--------------------------------------------------------------------------
*/

   $nextNumber = 1;


   if ($resultNomor->num_rows > 0) {

      $rowNomor = $resultNomor->fetch_assoc();

      $nomorTerakhir = $rowNomor['nomor_surat'];


      /*
    |--------------------------------------------------------------------------
    | AMBIL ANGKA TERAKHIR
    |--------------------------------------------------------------------------
    |
    | SKS/20260812/0005
    |                  ↑
    |                ambil 5
    |--------------------------------------------------------------------------
    */

      $lastNumber = (int) substr(
         $nomorTerakhir,
         strrpos($nomorTerakhir, '/') + 1
      );


      $nextNumber = $lastNumber + 1;
   }


   $stmtNomor->close();


   /*
|--------------------------------------------------------------------------
| FORMAT NOMOR
|--------------------------------------------------------------------------
*/

   $nomor_surat = $prefix . str_pad(
      $nextNumber,
      4,
      '0',
      STR_PAD_LEFT
   );


   /*
    |--------------------------------------------------------------------------
    | CREATED BY
    |--------------------------------------------------------------------------
    */

   $created_by =
      $_SESSION['id_user']
      ?? $_SESSION['username']
      ?? 'system';


   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

   $query = "
        INSERT INTO surat_sehat (
            id_visit,
            id_patient,
            nomor_surat,
            tanggal_surat,
            tekanan_darah,
            nadi,
            berat_badan,
            tinggi_badan,
            keperluan,
            keterangan,
            created_by
        )
        VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Prepare insert gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "sssssssssss",
      $id_visit,
      $id_patient,
      $nomor_surat,
      $tanggal_surat,
      $tekanan_darah,
      $nadi,
      $berat_badan,
      $tinggi_badan,
      $keperluan,
      $keterangan,
      $created_by
   );


   if ($stmt->execute()) {

      $newId = $stmt->insert_id;

      echo json_encode([
         'status' => 'success',
         'message' => 'Surat keterangan sehat berhasil disimpan.',
         'id' => $newId,
         'nomor_surat' => $nomor_surat
      ]);
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menyimpan data: ' . $stmt->error
      ]);
   }


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| READ ALL
|--------------------------------------------------------------------------
*/

function getData($id_customer)
{
   global $koneksi;


   $query = "
        SELECT
            ss.*,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_nik,
            mp.patient_datebirth,

            pv.id_doctor,
            pv.visit_ID,
            pv.visit_date

        FROM surat_sehat ss

        INNER JOIN pasien_visit pv
            ON pv.id_visit = ss.id_visit
            AND pv.id_customer = ?

        INNER JOIN ms_patient mp
            ON mp.id_patient = ss.id_patient

        ORDER BY ss.id DESC
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Prepare query gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "i",
      $id_customer
   );


   $stmt->execute();

   $result = $stmt->get_result();

   $data = $result->fetch_all(
      MYSQLI_ASSOC
   );


   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| READ BY ID
|--------------------------------------------------------------------------
*/

function getID($id, $id_customer)
{
   global $koneksi;


   $query = "
        SELECT
            ss.*,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_nik,
            mp.patient_datebirth,

            pv.id_doctor,
            pv.visit_ID,
            pv.visit_date

        FROM surat_sehat ss

        INNER JOIN pasien_visit pv
            ON pv.id_visit = ss.id_visit
            AND pv.id_customer = ?

        INNER JOIN ms_patient mp
            ON mp.id_patient = ss.id_patient

        WHERE ss.id = ?

        LIMIT 1
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Prepare query gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "ii",
      $id_customer,
      $id
   );


   $stmt->execute();

   $result = $stmt->get_result();


   if ($result->num_rows > 0) {

      echo json_encode([
         'status' => 'success',
         'data' => $result->fetch_assoc()
      ]);
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Data surat tidak ditemukan.'
      ]);
   }


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

function updateData($id_customer)
{
   global $koneksi;


   parse_str(
      file_get_contents("php://input"),
      $_PUT
   );


   $id = $_PUT['id'] ?? $_GET['id'] ?? '';


   if (empty($id)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'ID surat tidak ditemukan.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | DATA UPDATE
    |--------------------------------------------------------------------------
    */

   $tanggal_surat = $_PUT['tanggal_surat'] ?? '';
   $tekanan_darah = $_PUT['tekanan_darah'] ?? '';
   $nadi          = $_PUT['nadi'] ?? '';
   $berat_badan   = $_PUT['berat_badan'] ?? '';
   $tinggi_badan  = $_PUT['tinggi_badan'] ?? '';
   $keperluan     = $_PUT['keperluan'] ?? '';
   $keterangan    = $_PUT['keterangan'] ?? '';


   /*
    |--------------------------------------------------------------------------
    | UPDATED BY
    |--------------------------------------------------------------------------
    */

   $updated_by =
      $_SESSION['id_user']
      ?? $_SESSION['username']
      ?? 'system';


   /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

   $query = "
        UPDATE surat_sehat ss

        INNER JOIN pasien_visit pv
            ON pv.id_visit = ss.id_visit

        SET
            ss.tanggal_surat = ?,
            ss.tekanan_darah = ?,
            ss.nadi = ?,
            ss.berat_badan = ?,
            ss.tinggi_badan = ?,
            ss.keperluan = ?,
            ss.keterangan = ?,
            ss.updated_at = NOW(),
            ss.updated_by = ?

        WHERE ss.id = ?
          AND pv.id_customer = ?
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Prepare update gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "ssssssssii",
      $tanggal_surat,
      $tekanan_darah,
      $nadi,
      $berat_badan,
      $tinggi_badan,
      $keperluan,
      $keterangan,
      $updated_by,
      $id,
      $id_customer
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status' => 'success',
         'message' => 'Surat berhasil diperbarui.'
      ]);
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal memperbarui surat: ' . $stmt->error
      ]);
   }


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

function deleteData($id_customer)
{
   global $koneksi;


   $id = $_GET['id'] ?? '';


   if (empty($id)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'ID surat tidak ditemukan.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | DELETE DENGAN VALIDASI CUSTOMER
    |--------------------------------------------------------------------------
    */

   $query = "
        DELETE ss

        FROM surat_sehat ss

        INNER JOIN pasien_visit pv
            ON pv.id_visit = ss.id_visit

        WHERE ss.id = ?
          AND pv.id_customer = ?
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Prepare delete gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "ii",
      $id,
      $id_customer
   );


   if ($stmt->execute()) {

      if ($stmt->affected_rows > 0) {

         echo json_encode([
            'status' => 'success',
            'message' => 'Surat berhasil dihapus.'
         ]);
      } else {

         echo json_encode([
            'status'  => 'error',
            'message' => 'Data tidak ditemukan atau tidak memiliki akses.'
         ]);
      }
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menghapus surat: ' . $stmt->error
      ]);
   }


   $stmt->close();
}
