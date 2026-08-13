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
    | DATA FORM
    |--------------------------------------------------------------------------
    */

   $id_patient = trim(
      $_POST['id_patient'] ?? ''
   );


   $id_visit = trim(
      $_POST['id_visit'] ?? ''
   );


   $tanggal_surat =
      $_POST['tanggal_surat']
      ?? date('Y-m-d');


   $keterangan =
      trim(
         $_POST['keterangan'] ?? ''
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI PASIEN
    |--------------------------------------------------------------------------
    */

   if (empty($id_patient)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Pasien wajib dipilih.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | VALIDASI VISIT
    |--------------------------------------------------------------------------
    */

   if (empty($id_visit)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Visit pasien tidak ditemukan.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | VALIDASI TANGGAL
    |--------------------------------------------------------------------------
    */

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
            pv.id_customer,
            pv.id_doctor,
            pv.id_poli

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
    | NOMOR SURAT
    |--------------------------------------------------------------------------
    |
    | Format:
    |
    | SKB/20260812/0001
    |
    | SKB = Surat Keterangan Berobat
    |--------------------------------------------------------------------------
    */

   $tanggalNomor = date(
      'Ymd',
      strtotime($tanggal_surat)
   );


   $prefix =
      "SKB/"
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
            sb.nomor_surat

        FROM surat_berobat sb

        INNER JOIN pasien_visit pv
            ON pv.id_visit = sb.id_visit

        WHERE pv.id_customer = ?
          AND sb.nomor_surat LIKE ?

        ORDER BY sb.id DESC

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


      /*
        |--------------------------------------------------------------------------
        | AMBIL ANGKA TERAKHIR
        |--------------------------------------------------------------------------
        */

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
    |
    | Sesuai tabel surat_berobat:
    |
    | id_visit
    | id_patient
    | nomor_surat
    | tanggal_surat
    | keterangan
    | created_by
    |--------------------------------------------------------------------------
    */

   $query = "

        INSERT INTO surat_berobat (

            id_visit,
            id_patient,
            nomor_surat,
            tanggal_surat,
            keterangan,
            created_by

        )

        VALUES (

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
    | 6 FIELD = 6 STRING
    |--------------------------------------------------------------------------
    */

   $stmt->bind_param(
      "ssssss",
      $id_visit,
      $id_patient,
      $nomor_surat,
      $tanggal_surat,
      $keterangan,
      $created_by
   );


   if ($stmt->execute()) {

      $newId =
         $stmt->insert_id;


      echo json_encode([
         'status'      => 'success',
         'message'     =>
         'Surat keterangan berobat berhasil disimpan.',
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

            sb.*,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_nik,
            mp.patient_datebirth,

            pv.id_doctor,
            pv.id_poli,
            pv.visit_ID,
            pv.visit_date

        FROM surat_berobat sb

        INNER JOIN pasien_visit pv
            ON pv.id_visit = sb.id_visit
            AND pv.id_customer = ?

        INNER JOIN ms_patient mp
            ON mp.id_patient = sb.id_patient

        ORDER BY sb.id DESC

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

            sb.*,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_nik,
            mp.patient_datebirth,

            pv.id_doctor,
            pv.id_poli,
            pv.visit_ID,
            pv.visit_date

        FROM surat_berobat sb

        INNER JOIN pasien_visit pv
            ON pv.id_visit = sb.id_visit
            AND pv.id_customer = ?

        INNER JOIN ms_patient mp
            ON mp.id_patient = sb.id_patient

        WHERE sb.id = ?

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


   $keterangan =
      trim(
         $_PUT['keterangan']
            ?? ''
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI TANGGAL
    |--------------------------------------------------------------------------
    */

   if (
      empty($tanggal_surat)
   ) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Tanggal surat wajib diisi.'
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

        UPDATE surat_berobat sb

        INNER JOIN pasien_visit pv
            ON pv.id_visit = sb.id_visit

        SET

            sb.tanggal_surat = ?,
            sb.keterangan = ?,
            sb.updated_at = NOW(),
            sb.updated_by = ?

        WHERE sb.id = ?
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
    | 5 PARAMETER
    |--------------------------------------------------------------------------
    |
    | tanggal_surat = string
    | keterangan    = string
    | updated_by    = string
    | id            = integer
    | id_customer   = integer
    |--------------------------------------------------------------------------
    */

   $stmt->bind_param(
      "sssii",
      $tanggal_surat,
      $keterangan,
      $updated_by,
      $id,
      $id_customer
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status'  => 'success',
         'message' =>
         'Surat keterangan berobat berhasil diperbarui.'
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


   /*
    |--------------------------------------------------------------------------
    | ID
    |--------------------------------------------------------------------------
    */

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

        DELETE sb

        FROM surat_berobat sb

        INNER JOIN pasien_visit pv
            ON pv.id_visit = sb.id_visit

        WHERE sb.id = ?
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
            'Surat keterangan berobat berhasil dihapus.'
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
