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

   $keterangan =
      trim($_POST['keterangan'] ?? '');


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
    | VALIDASI VISIT RAWAT INAP
    |--------------------------------------------------------------------------
    |
    | Pastikan:
    |
    | 1. Visit ada
    | 2. Milik pasien
    | 3. Milik customer
    | 4. Status rawat inap aktif
    |
    |--------------------------------------------------------------------------
    */

   $checkVisit = $koneksi->prepare("
        SELECT

            pv.id_visit,
            pv.id_patient,
            pv.id_customer,
            pv.id_doctor,
            pv.visit_date,

            rsm.tanggal_pulang,
            rsm.diagnosa

        FROM pasien_visit pv

        LEFT JOIN resume_medis rsm
            ON rsm.visit_ID = pv.visit_ID

        WHERE pv.id_visit = ?

          AND pv.id_patient = ?

          AND pv.id_customer = ?

          AND pv.status_rawatinap = 1

        LIMIT 1
    ");


   if (!$checkVisit) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare validasi visit gagal: ' .
            $koneksi->error
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
         'Visit rawat inap tidak valid atau bukan milik fasilitas kesehatan ini.'
      ]);

      return;
   }


   $visitData =
      $visitResult->fetch_assoc();


   $checkVisit->close();


   /*
    |--------------------------------------------------------------------------
    | DATA RAWAT INAP DARI DATABASE
    |--------------------------------------------------------------------------
    */

   $tanggal_masuk =
      $visitData['visit_date']
      ?? null;


   $tanggal_pulang =
      $visitData['tanggal_pulang']
      ?? null;


   $diagnosa =
      $visitData['diagnosa']
      ?? '';


   $id_doctor =
      $visitData['id_doctor']
      ?? '';


   /*
    |--------------------------------------------------------------------------
    | NOMOR SURAT OTOMATIS
    |--------------------------------------------------------------------------
    |
    | Format:
    |
    | SKRI/20260814/0001
    | SKRI/20260814/0002
    | SKRI/20260814/0003
    |
    | Setiap customer mempunyai sequence sendiri.
    |
    |--------------------------------------------------------------------------
    */

   $tanggalNomor =
      date(
         'Ymd',
         strtotime($tanggal_surat)
      );


   $prefix =
      "SKRI/" .
      $tanggalNomor .
      "/";


   $likeNomor =
      $prefix . "%";


   /*
    |--------------------------------------------------------------------------
    | CARI NOMOR TERAKHIR
    |--------------------------------------------------------------------------
    */

   $stmtNomor =
      $koneksi->prepare("
            SELECT
                nomor_surat

            FROM surat_rawat_inap

            WHERE id_customer = ?

              AND nomor_surat LIKE ?

            ORDER BY id DESC

            LIMIT 1
        ");


   if (!$stmtNomor) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare nomor surat gagal: ' .
            $koneksi->error
      ]);

      return;
   }


   $stmtNomor->bind_param(
      "ss",
      $id_customer,
      $likeNomor
   );


   $stmtNomor->execute();


   $resultNomor =
      $stmtNomor->get_result();


   /*
    |--------------------------------------------------------------------------
    | DEFAULT NOMOR
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
        |
        | SKRI/20260814/0005
        |                  ↑
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
    | FORMAT NOMOR SURAT
    |--------------------------------------------------------------------------
    */

   $nomor_surat =
      $prefix .
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
      ?? $_SESSION['username']
      ?? 'system';


   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

   $query = "

        INSERT INTO surat_rawat_inap (

            id_visit,

            id_patient,

            id_customer,

            nomor_surat,

            tanggal_surat,

            tanggal_masuk,

            tanggal_pulang,

            diagnosa,

            id_doctor,

            keterangan,

            created_by

        )

        VALUES (

            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?

        )

    ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare insert gagal: ' .
            $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "sssssssssss",

      $id_visit,

      $id_patient,

      $id_customer,

      $nomor_surat,

      $tanggal_surat,

      $tanggal_masuk,

      $tanggal_pulang,

      $diagnosa,

      $id_doctor,

      $keterangan,

      $created_by
   );


   /*
    |--------------------------------------------------------------------------
    | EXECUTE
    |--------------------------------------------------------------------------
    */

   if ($stmt->execute()) {

      $newId =
         $stmt->insert_id;


      echo json_encode([

         'status' =>
         'success',

         'message' =>
         'Surat keterangan rawat inap berhasil disimpan.',

         'id' =>
         $newId,

         'nomor_surat' =>
         $nomor_surat

      ]);
   } else {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'Gagal menyimpan data: ' .
            $stmt->error

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

            sri.*,

            mp.patient_name,

            mp.nomor_rm,

            mp.patient_nik,

            mp.patient_datebirth,

            pv.visit_ID,

            pv.visit_date,

            pv.visit_time,

            pv.id_doctor AS visit_doctor,

            /*
            |----------------------------------------------------------
            | NAMA DOKTER
            |----------------------------------------------------------
            */

            md.doctor_name


        FROM surat_rawat_inap sri


        INNER JOIN pasien_visit pv

            ON pv.id_visit =
               sri.id_visit

            AND pv.id_customer = ?


        INNER JOIN ms_patient mp

            ON mp.id_patient =
               sri.id_patient


        LEFT JOIN ms_doctor md

            ON md.doctor_code =
               sri.id_doctor


        ORDER BY

            sri.id DESC

    ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare query gagal: ' .
            $koneksi->error
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

      'status' =>
      'success',

      'data' =>
      $data

   ]);


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| READ BY ID
|--------------------------------------------------------------------------
*/

function getID(
   $id,
   $id_customer
) {
   global $koneksi;


   $query = "

        SELECT

            sri.*,

            mp.patient_name,

            mp.nomor_rm,

            mp.patient_nik,

            mp.patient_datebirth,

            pv.visit_ID,

            pv.visit_date,

            pv.visit_time,

            pv.id_doctor AS visit_doctor,

            md.doctor_name


        FROM surat_rawat_inap sri


        INNER JOIN pasien_visit pv

            ON pv.id_visit =
               sri.id_visit

            AND pv.id_customer = ?


        INNER JOIN ms_patient mp

            ON mp.id_patient =
               sri.id_patient


        LEFT JOIN ms_doctor md

            ON md.doctor_code =
               sri.id_doctor


        WHERE sri.id = ?

        LIMIT 1

    ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare query gagal: ' .
            $koneksi->error
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

         'status' =>
         'success',

         'data' =>
         $result->fetch_assoc()

      ]);
   } else {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'Data surat rawat inap tidak ditemukan.'

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
    | AMBIL PUT
    |--------------------------------------------------------------------------
    */

   parse_str(
      file_get_contents(
         "php://input"
      ),
      $data
   );


   /*
    |--------------------------------------------------------------------------
    | ID
    |--------------------------------------------------------------------------
    */

   $id =
      $data['id']
      ?? $_GET['id']
      ?? '';


   if (empty($id)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'ID surat tidak ditemukan.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | FIELD
    |--------------------------------------------------------------------------
    */

   $tanggal_surat =
      $data['tanggal_surat']
      ?? '';


   $keterangan =
      trim(
         $data['keterangan']
            ?? ''
      );


   /*
    |--------------------------------------------------------------------------
    | UPDATED BY
    |--------------------------------------------------------------------------
    */

   $updated_by =
      $_SESSION['uid_user']
      ?? $_SESSION['username']
      ?? 'system';


   /*
    |--------------------------------------------------------------------------
    | VALIDASI DATA SURAT
    |--------------------------------------------------------------------------
    */

   $check =
      $koneksi->prepare("

            SELECT

                sri.id,

                sri.id_visit,

                sri.id_patient

            FROM surat_rawat_inap sri

            INNER JOIN pasien_visit pv

                ON pv.id_visit =
                   sri.id_visit

            WHERE sri.id = ?

              AND pv.id_customer = ?

            LIMIT 1

        ");


   if (!$check) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare validasi update gagal: ' .
            $koneksi->error
      ]);

      return;
   }


   $check->bind_param(
      "ii",
      $id,
      $id_customer
   );


   $check->execute();


   $checkResult =
      $check->get_result();


   if (
      $checkResult->num_rows === 0
   ) {

      $check->close();


      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Data surat tidak ditemukan atau bukan milik fasilitas kesehatan ini.'
      ]);

      return;
   }


   $checkData =
      $checkResult->fetch_assoc();


   $check->close();


   /*
    |--------------------------------------------------------------------------
    | UPDATE DATA
    |--------------------------------------------------------------------------
    |
    | Data medis:
    |
    | tanggal_masuk
    | tanggal_pulang
    | diagnosa
    | id_doctor
    |
    | TIDAK diubah dari form.
    |
    | Karena sumbernya berasal dari visit/resume medis.
    |
    |--------------------------------------------------------------------------
    */

   $query = "

        UPDATE surat_rawat_inap

        SET

            tanggal_surat = ?,

            keterangan = ?,

            updated_at = NOW(),

            updated_by = ?

        WHERE id = ?

    ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare update gagal: ' .
            $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "sssi",

      $tanggal_surat,

      $keterangan,

      $updated_by,

      $id
   );


   if (
      $stmt->execute()
   ) {

      echo json_encode([

         'status' =>
         'success',

         'message' =>
         'Surat keterangan rawat inap berhasil diperbarui.'

      ]);
   } else {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'Gagal memperbarui surat: ' .
            $stmt->error

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
         'message' => 'ID surat tidak ditemukan.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | DELETE + VALIDASI CUSTOMER
    |--------------------------------------------------------------------------
    */

   $query = "

        DELETE sri

        FROM surat_rawat_inap sri

        INNER JOIN pasien_visit pv

            ON pv.id_visit =
               sri.id_visit

        WHERE sri.id = ?

          AND pv.id_customer = ?

    ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' =>
         'Prepare delete gagal: ' .
            $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "ii",
      $id,
      $id_customer
   );


   if (
      $stmt->execute()
   ) {

      if (
         $stmt->affected_rows > 0
      ) {

         echo json_encode([

            'status' =>
            'success',

            'message' =>
            'Surat keterangan rawat inap berhasil dihapus.'

         ]);
      } else {

         echo json_encode([

            'status' =>
            'error',

            'message' =>
            'Data tidak ditemukan atau tidak memiliki akses.'

         ]);
      }
   } else {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'Gagal menghapus surat: ' .
            $stmt->error

      ]);
   }


   $stmt->close();
}
