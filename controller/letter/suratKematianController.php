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
| ROUTING
|--------------------------------------------------------------------------
*/

switch ($method) {

   case 'POST':

      createData($id_customer);

      break;


   case 'GET':

      if (
         isset($_GET['id']) &&
         $_GET['id'] !== ''
      ) {

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
    | DATA POST
    |--------------------------------------------------------------------------
    */

   $id_patient =
      trim($_POST['id_patient'] ?? '');


   $id_visit =
      trim($_POST['id_visit'] ?? '');


   $tanggal_surat =
      $_POST['tanggal_surat']
      ?? date('Y-m-d');


   $tanggal_kematian =
      $_POST['tanggal_kematian']
      ?? '';


   $waktu_kematian =
      $_POST['waktu_kematian']
      ?? '';


   $ruangan =
      trim($_POST['ruangan'] ?? '');


   $dokter_menyatakan =
      trim($_POST['dokter_menyatakan'] ?? '');


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


   if (empty($tanggal_kematian)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Tanggal kematian wajib diisi.'
      ]);

      return;
   }


   if (empty($waktu_kematian)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Waktu kematian wajib diisi.'
      ]);

      return;
   }


   if (empty($ruangan)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Ruangan wajib diisi.'
      ]);

      return;
   }


   if (empty($dokter_menyatakan)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Dokter yang menyatakan wajib dipilih.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | VALIDASI VISIT
    |--------------------------------------------------------------------------
    |
    | Pastikan:
    | - visit ada
    | - patient sesuai
    | - customer sesuai
    |--------------------------------------------------------------------------
    */

   $checkVisit = $koneksi->prepare("
        SELECT
            pv.id_visit,
            pv.id_patient,
            pv.id_customer

        FROM pasien_visit pv

        WHERE pv.id_visit = ?
          AND pv.id_patient = ?
          AND pv.id_customer = ?

        LIMIT 1
    ");


   if (!$checkVisit) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare validasi visit gagal: '
            . $koneksi->error
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


   $visitResult =
      $checkVisit->get_result();


   if (
      $visitResult->num_rows === 0
   ) {

      $checkVisit->close();

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Visit pasien tidak valid atau bukan milik fasilitas kesehatan ini.'
      ]);

      return;
   }


   $checkVisit->close();


   /*
    |--------------------------------------------------------------------------
    | NOMOR SURAT OTOMATIS
    |--------------------------------------------------------------------------
    |
    | SKM = Surat Keterangan Kematian
    |
    | Contoh:
    |
    | SKM/20260813/0001
    | SKM/20260813/0002
    |
    | Nomor kembali ke 0001 untuk customer berbeda.
    |--------------------------------------------------------------------------
    */

   $tanggalNomor =
      date(
         'Ymd',
         strtotime($tanggal_surat)
      );


   $prefix =
      "SKM/"
      . $tanggalNomor
      . "/";


   $likeNomor =
      $prefix . "%";


   /*
    |--------------------------------------------------------------------------
    | CARI NOMOR TERAKHIR PER CUSTOMER
    |--------------------------------------------------------------------------
    */

   $stmtNomor = $koneksi->prepare("
        SELECT
            sk.nomor_surat

        FROM surat_kematian sk

        INNER JOIN pasien_visit pv
            ON pv.id_visit = sk.id_visit

        WHERE pv.id_customer = ?
          AND sk.nomor_surat LIKE ?

        ORDER BY sk.id DESC

        LIMIT 1
    ");


   if (!$stmtNomor) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare nomor surat gagal: '
            . $koneksi->error
      ]);

      return;
   }


   $stmtNomor->bind_param(
      "is",
      $id_customer,
      $likeNomor
   );


   $stmtNomor->execute();


   $resultNomor =
      $stmtNomor->get_result();


   /*
    |--------------------------------------------------------------------------
    | NOMOR AWAL
    |--------------------------------------------------------------------------
    */

   $nextNumber = 1;


   if (
      $resultNomor->num_rows > 0
   ) {

      $rowNomor =
         $resultNomor->fetch_assoc();


      $nomorTerakhir =
         $rowNomor['nomor_surat'];


      $lastNumber =
         (int) substr(
            $nomorTerakhir,
            strrpos(
               $nomorTerakhir,
               '/'
            ) + 1
         );


      $nextNumber =
         $lastNumber + 1;
   }


   $stmtNomor->close();


   /*
    |--------------------------------------------------------------------------
    | FORMAT NOMOR
    |--------------------------------------------------------------------------
    */

   $nomor_surat =
      $prefix
      .
      str_pad(
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
      $_SESSION['uid_user']
      ??
      $_SESSION['username']
      ??
      'system';


   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

   $query = "

        INSERT INTO surat_kematian (

            id_visit,
            id_patient,
            nomor_surat,
            tanggal_surat,
            tanggal_kematian,
            waktu_kematian,
            ruangan,
            dokter_menyatakan,
            created_by

        )

        VALUES (

            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?

        )

    ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare insert gagal: '
            . $koneksi->error
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | 9 FIELD = 9 STRING
    |--------------------------------------------------------------------------
    */

   $stmt->bind_param(
      "sssssssss",
      $id_visit,
      $id_patient,
      $nomor_surat,
      $tanggal_surat,
      $tanggal_kematian,
      $waktu_kematian,
      $ruangan,
      $dokter_menyatakan,
      $created_by
   );


   if ($stmt->execute()) {

      $newId =
         $stmt->insert_id;


      echo json_encode([
         'status'      => 'success',
         'message'     =>
         'Surat keterangan kematian berhasil disimpan.',
         'id'          => $newId,
         'nomor_surat' => $nomor_surat
      ]);
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Gagal menyimpan data: '
            . $stmt->error
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

            sk.*,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_nik,
            mp.patient_datebirth,

            pv.id_doctor,
            pv.id_poli,
            pv.visit_ID,
            pv.visit_date,

            md.doctor_name

        FROM surat_kematian sk

        INNER JOIN pasien_visit pv
            ON pv.id_visit = sk.id_visit
            AND pv.id_customer = ?

        INNER JOIN ms_patient mp
            ON mp.id_patient = sk.id_patient

        LEFT JOIN ms_doctor md
            ON md.id_doctor = sk.dokter_menyatakan

        ORDER BY sk.id DESC

    ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare query gagal: '
            . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "i",
      $id_customer
   );


   $stmt->execute();


   $result =
      $stmt->get_result();


   $data =
      $result->fetch_all(
         MYSQLI_ASSOC
      );


   echo json_encode([
      'status' => 'success',
      'data'   => $data
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

            sk.*,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_nik,
            mp.patient_datebirth,

            pv.id_doctor,
            pv.id_poli,
            pv.visit_ID,
            pv.visit_date,

            md.doctor_name

        FROM surat_kematian sk

        INNER JOIN pasien_visit pv
            ON pv.id_visit = sk.id_visit
            AND pv.id_customer = ?

        INNER JOIN ms_patient mp
            ON mp.id_patient = sk.id_patient

        LEFT JOIN ms_doctor md
            ON md.id_doctor = sk.dokter_menyatakan

        WHERE sk.id = ?

        LIMIT 1

    ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare query gagal: '
            . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "ii",
      $id_customer,
      $id
   );


   $stmt->execute();


   $result =
      $stmt->get_result();


   if (
      $result->num_rows > 0
   ) {

      echo json_encode([
         'status' => 'success',
         'data'   =>
         $result->fetch_assoc()
      ]);
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Data surat tidak ditemukan.'
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


   /*
    |--------------------------------------------------------------------------
    | PARSE PUT
    |--------------------------------------------------------------------------
    */

   parse_str(
      file_get_contents(
         "php://input"
      ),
      $_PUT
   );


   /*
    |--------------------------------------------------------------------------
    | ID
    |--------------------------------------------------------------------------
    */

   $id =
      $_PUT['id']
      ??
      $_GET['id']
      ??
      '';


   if (empty($id)) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'ID surat tidak ditemukan.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

   $tanggal_surat =
      $_PUT['tanggal_surat']
      ?? '';


   $tanggal_kematian =
      $_PUT['tanggal_kematian']
      ?? '';


   $waktu_kematian =
      $_PUT['waktu_kematian']
      ?? '';


   $ruangan =
      trim(
         $_PUT['ruangan']
            ?? ''
      );


   $dokter_menyatakan =
      trim(
         $_PUT['dokter_menyatakan']
            ?? ''
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

   if (empty($tanggal_surat)) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Tanggal surat wajib diisi.'
      ]);

      return;
   }


   if (empty($tanggal_kematian)) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Tanggal kematian wajib diisi.'
      ]);

      return;
   }


   if (empty($waktu_kematian)) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Waktu kematian wajib diisi.'
      ]);

      return;
   }


   if (empty($ruangan)) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Ruangan wajib diisi.'
      ]);

      return;
   }


   if (empty($dokter_menyatakan)) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Dokter yang menyatakan wajib dipilih.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | UPDATED BY
    |--------------------------------------------------------------------------
    */

   $updated_by =
      $_SESSION['uid_user']
      ??
      $_SESSION['username']
      ??
      'system';


   /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

   $query = "

        UPDATE surat_kematian sk

        INNER JOIN pasien_visit pv
            ON pv.id_visit = sk.id_visit

        SET

            sk.tanggal_surat = ?,
            sk.tanggal_kematian = ?,
            sk.waktu_kematian = ?,
            sk.ruangan = ?,
            sk.dokter_menyatakan = ?,
            sk.updated_at = NOW(),
            sk.updated_by = ?

        WHERE sk.id = ?
          AND pv.id_customer = ?

    ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare update gagal: '
            . $koneksi->error
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | 8 PARAMETER
    |--------------------------------------------------------------------------
    */

   $stmt->bind_param(
      "ssssssii",
      $tanggal_surat,
      $tanggal_kematian,
      $waktu_kematian,
      $ruangan,
      $dokter_menyatakan,
      $updated_by,
      $id,
      $id_customer
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status'  => 'success',
         'message' =>
         'Surat keterangan kematian berhasil diperbarui.'
      ]);
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Gagal memperbarui surat: '
            . $stmt->error
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


   $id =
      $_GET['id']
      ?? '';


   if (empty($id)) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'ID surat tidak ditemukan.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | DELETE AMAN PER CUSTOMER
    |--------------------------------------------------------------------------
    */

   $query = "

        DELETE sk

        FROM surat_kematian sk

        INNER JOIN pasien_visit pv
            ON pv.id_visit = sk.id_visit

        WHERE sk.id = ?
          AND pv.id_customer = ?

    ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare delete gagal: '
            . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "ii",
      $id,
      $id_customer
   );


   if ($stmt->execute()) {

      if (
         $stmt->affected_rows > 0
      ) {

         echo json_encode([
            'status'  => 'success',
            'message' =>
            'Surat keterangan kematian berhasil dihapus.'
         ]);
      } else {

         echo json_encode([
            'status'  => 'error',
            'message' =>
            'Data tidak ditemukan atau tidak memiliki akses.'
         ]);
      }
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Gagal menghapus surat: '
            . $stmt->error
      ]);
   }


   $stmt->close();
}
