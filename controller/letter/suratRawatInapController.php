<?php

session_start();

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| VALIDASI SESSION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id_customer']) || $_SESSION['id_customer'] === '') {

   echo json_encode([
      'status'  => 'error',
      'message' => 'Session faskes tidak ditemukan.'
   ]);

   exit;
}


$id_customer = (string) $_SESSION['id_customer'];

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

      /*
        |--------------------------------------------------------------------------
        | CHECK SETTING NOMOR SURAT
        |--------------------------------------------------------------------------
        */

      if (isset($_GET['check_setting'])) {

         checkSettingNomorSurat($id_customer);

         break;
      }


      /*
        |--------------------------------------------------------------------------
        | GET DETAIL
        |--------------------------------------------------------------------------
        */

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
| HELPER RESPONSE
|--------------------------------------------------------------------------
*/

function responseJson($data)
{
   echo json_encode(
      $data,
      JSON_UNESCAPED_UNICODE
   );

   exit;
}


/*
|--------------------------------------------------------------------------
| GET SETTING NOMOR SURAT RAWAT INAP
|--------------------------------------------------------------------------
*/

function getSettingNomorSurat($id_customer)
{
   global $koneksi;


   $query = "

        SELECT

            id,

            id_customer,

            mode_nomor,

            format_rawat_inap,

            nomor_rawat_inap

        FROM setting_surat

        WHERE id_customer = ?

        LIMIT 1

    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      return [
         'status'  => 'error',
         'message' =>
         'Prepare setting nomor surat gagal: ' .
            $koneksi->error
      ];
   }


   $stmt->bind_param(
      "s",
      $id_customer
   );


   $stmt->execute();


   $result = $stmt->get_result();


   if ($result->num_rows === 0) {

      $stmt->close();

      return [
         'status' => 'setting_required'
      ];
   }


   $setting =
      $result->fetch_assoc();


   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | VALIDASI MODE
    |--------------------------------------------------------------------------
    */

   $mode =
      strtoupper(
         trim(
            $setting['mode_nomor'] ?? ''
         )
      );


   if (
      $mode !== 'AUTO' &&
      $mode !== 'MANUAL'
   ) {

      return [
         'status'  => 'error',
         'message' =>
         'Mode penomoran surat rawat inap belum valid.'
      ];
   }


   return [
      'status' => 'success',
      'data'   => $setting
   ];
}


/*
|--------------------------------------------------------------------------
| CHECK SETTING
|--------------------------------------------------------------------------
|
| Dipanggil JavaScript:
|
| GET ?check_setting=1
|
|--------------------------------------------------------------------------
*/

function checkSettingNomorSurat($id_customer)
{
   $setting =
      getSettingNomorSurat(
         $id_customer
      );


   if (
      $setting['status'] ===
      'setting_required'
   ) {

      responseJson([
         'status'  => 'setting_required',
         'message' =>
         'Setting nomor surat rawat inap belum dibuat.'
      ]);
   }


   if (
      $setting['status'] !==
      'success'
   ) {

      responseJson(
         $setting
      );
   }


   $data =
      $setting['data'];


   responseJson([

      'status' =>
      'success',

      'data' => [

         'mode_nomor' =>
         $data['mode_nomor'],

         'format_rawat_inap' =>
         $data['format_rawat_inap'],

         'nomor_rawat_inap' =>
         $data['nomor_rawat_inap']

      ]

   ]);
}


/*
|--------------------------------------------------------------------------
| FORMAT NOMOR SURAT
|--------------------------------------------------------------------------
|
| Contoh:
|
| SRI/{NO}/{MM}/{YYYY}
|
| menjadi:
|
| SRI/001/08/2026
|
|--------------------------------------------------------------------------
*/

function generateNomorSurat(
   $format,
   $nomor,
   $tanggal
) {

   $timestamp =
      strtotime($tanggal);


   if (!$timestamp) {

      $timestamp =
         time();
   }


   $dd =
      date(
         'd',
         $timestamp
      );


   $mm =
      date(
         'm',
         $timestamp
      );


   $yyyy =
      date(
         'Y',
         $timestamp
      );


   $yy =
      date(
         'y',
         $timestamp
      );


   /*
    |--------------------------------------------------------------------------
    | JUMLAH DIGIT NOMOR
    |--------------------------------------------------------------------------
    |
    | Default 3 digit.
    |
    | Jika format:
    |
    | SRI/{NO}/{MM}/{YYYY}
    |
    | hasil:
    |
    | SRI/001/08/2026
    |
    |--------------------------------------------------------------------------
    */

   $nomorFormatted =
      str_pad(
         (int) $nomor,
         3,
         '0',
         STR_PAD_LEFT
      );


   /*
    |--------------------------------------------------------------------------
    | REPLACE PLACEHOLDER
    |--------------------------------------------------------------------------
    */

   $format =
      str_replace(
         '{NO}',
         $nomorFormatted,
         $format
      );


   $format =
      str_replace(
         '{DD}',
         $dd,
         $format
      );


   $format =
      str_replace(
         '{MM}',
         $mm,
         $format
      );


   $format =
      str_replace(
         '{YYYY}',
         $yyyy,
         $format
      );


   $format =
      str_replace(
         '{YY}',
         $yy,
         $format
      );


   return trim(
      $format
   );
}


/*
|--------------------------------------------------------------------------
| VALIDASI VISIT RAWAT INAP
|--------------------------------------------------------------------------
*/

function getVisitRawatInap(
   $id_visit,
   $id_patient,
   $id_customer
) {

   global $koneksi;


   $query = "

        SELECT

            pv.id_visit,

            pv.id_patient,

            pv.id_customer,

            pv.visit_ID,

            pv.visit_date,

            pv.visit_time,

            pv.id_doctor,

            pv.id_poli,

            pv.status_rawatinap,

            rsm.tanggal_pulang,

            rsm.diagnosa

        FROM pasien_visit pv

        LEFT JOIN resume_medis rsm

            ON rsm.visit_ID =
               pv.visit_ID

        WHERE pv.id_visit = ?

          AND pv.id_patient = ?

          AND pv.id_customer = ?

          AND pv.status_rawatinap = 1

        LIMIT 1

    ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      return [
         'status'  => 'error',
         'message' =>
         'Prepare validasi visit gagal: ' .
            $koneksi->error
      ];
   }


   $stmt->bind_param(
      "sss",
      $id_visit,
      $id_patient,
      $id_customer
   );


   $stmt->execute();


   $result =
      $stmt->get_result();


   if (
      $result->num_rows === 0
   ) {

      $stmt->close();

      return [
         'status'  => 'error',
         'message' =>
         'Visit rawat inap tidak valid atau bukan milik fasilitas kesehatan ini.'
      ];
   }


   $data =
      $result->fetch_assoc();


   $stmt->close();


   return [
      'status' => 'success',
      'data'   => $data
   ];
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
      trim(
         $_POST['id_patient'] ?? ''
      );


   $id_visit =
      trim(
         $_POST['id_visit'] ?? ''
      );


   $tanggal_surat =
      trim(
         $_POST['tanggal_surat']
            ?? date('Y-m-d')
      );


   $keterangan =
      trim(
         $_POST['keterangan'] ?? ''
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI PASIEN
    |--------------------------------------------------------------------------
    */

   if ($id_patient === '') {

      responseJson([
         'status'  => 'error',
         'message' =>
         'Pasien wajib dipilih.'
      ]);
   }


   /*
    |--------------------------------------------------------------------------
    | VALIDASI VISIT
    |--------------------------------------------------------------------------
    */

   if ($id_visit === '') {

      responseJson([
         'status'  => 'error',
         'message' =>
         'Visit pasien tidak ditemukan.'
      ]);
   }


   /*
    |--------------------------------------------------------------------------
    | VALIDASI TANGGAL
    |--------------------------------------------------------------------------
    */

   if ($tanggal_surat === '') {

      responseJson([
         'status'  => 'error',
         'message' =>
         'Tanggal surat wajib diisi.'
      ]);
   }


   /*
    |--------------------------------------------------------------------------
    | CEK SETTING
    |--------------------------------------------------------------------------
    */

   $setting =
      getSettingNomorSurat(
         $id_customer
      );


   /*
    |--------------------------------------------------------------------------
    | SETTING BELUM ADA
    |--------------------------------------------------------------------------
    */

   if (
      $setting['status'] ===
      'setting_required'
   ) {

      responseJson([
         'status'  =>
         'setting_required',

         'message' =>
         'Setting nomor surat rawat inap belum dibuat.'
      ]);
   }


   /*
    |--------------------------------------------------------------------------
    | ERROR SETTING
    |--------------------------------------------------------------------------
    */

   if (
      $setting['status'] !==
      'success'
   ) {

      responseJson(
         $setting
      );
   }


   $settingData =
      $setting['data'];


   $modeNomor =
      strtoupper(
         trim(
            $settingData['mode_nomor']
         )
      );


   $formatNomor =
      trim(
         $settingData['format_rawat_inap']
            ?? ''
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI FORMAT AUTO
    |--------------------------------------------------------------------------
    */

   if (
      $modeNomor === 'AUTO' &&
      $formatNomor === ''
   ) {

      responseJson([
         'status'  => 'error',
         'message' =>
         'Format nomor surat rawat inap belum diatur.'
      ]);
   }


   /*
    |--------------------------------------------------------------------------
    | VALIDASI VISIT
    |--------------------------------------------------------------------------
    */

   $visit =
      getVisitRawatInap(
         $id_visit,
         $id_patient,
         $id_customer
      );


   if (
      $visit['status'] !==
      'success'
   ) {

      responseJson(
         $visit
      );
   }


   $visitData =
      $visit['data'];


   /*
    |--------------------------------------------------------------------------
    | DATA RAWAT INAP
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
    | NOMOR SURAT
    |--------------------------------------------------------------------------
    */

   $nomor_surat = '';


   /*
    |--------------------------------------------------------------------------
    | MODE MANUAL
    |--------------------------------------------------------------------------
    */

   if (
      $modeNomor ===
      'MANUAL'
   ) {

      $nomor_surat =
         trim(
            $_POST['nomor_surat']
               ?? ''
         );


      if (
         $nomor_surat === ''
      ) {

         responseJson([
            'status'  => 'error',
            'message' =>
            'Nomor surat wajib diisi karena mode penomoran MANUAL.'
         ]);
      }


      /*
        |--------------------------------------------------------------------------
        | CEK NOMOR DUPLIKAT
        |--------------------------------------------------------------------------
        */

      $checkNomor =
         $koneksi->prepare("

                SELECT id

                FROM surat_rawat_inap

                WHERE id_customer = ?

                  AND nomor_surat = ?

                LIMIT 1

            ");


      if (!$checkNomor) {

         responseJson([
            'status'  => 'error',
            'message' =>
            'Prepare pengecekan nomor surat gagal: ' .
               $koneksi->error
         ]);
      }


      $checkNomor->bind_param(
         "ss",
         $id_customer,
         $nomor_surat
      );


      $checkNomor->execute();


      $resultNomor =
         $checkNomor->get_result();


      if (
         $resultNomor->num_rows > 0
      ) {

         $checkNomor->close();

         responseJson([
            'status'  => 'error',
            'message' =>
            'Nomor surat tersebut sudah digunakan.'
         ]);
      }


      $checkNomor->close();
   }


   /*
    |--------------------------------------------------------------------------
    | MODE AUTO
    |--------------------------------------------------------------------------
    */

   if (
      $modeNomor ===
      'AUTO'
   ) {

      /*
        |--------------------------------------------------------------------------
        | TRANSACTION
        |--------------------------------------------------------------------------
        */

      $koneksi->begin_transaction();


      try {

         /*
            |--------------------------------------------------------------------------
            | LOCK SETTING
            |--------------------------------------------------------------------------
            */

         $stmtSetting =
            $koneksi->prepare("

                    SELECT

                        id,

                        mode_nomor,

                        format_rawat_inap,

                        nomor_rawat_inap

                    FROM setting_surat

                    WHERE id_customer = ?

                    LIMIT 1

                    FOR UPDATE

                ");


         if (!$stmtSetting) {

            throw new Exception(
               'Prepare lock setting gagal: ' .
                  $koneksi->error
            );
         }


         $stmtSetting->bind_param(
            "s",
            $id_customer
         );


         $stmtSetting->execute();


         $resultSetting =
            $stmtSetting->get_result();


         if (
            $resultSetting->num_rows === 0
         ) {

            $stmtSetting->close();

            throw new Exception(
               'SETTING_REQUIRED'
            );
         }


         $lockedSetting =
            $resultSetting->fetch_assoc();


         $stmtSetting->close();


         /*
            |--------------------------------------------------------------------------
            | NOMOR TERAKHIR
            |--------------------------------------------------------------------------
            */

         $currentNumber =
            (int) (
               $lockedSetting['nomor_rawat_inap'] ?? 0
            );


         $nextNumber =
            $currentNumber + 1;


         /*
            |--------------------------------------------------------------------------
            | FORMAT TERBARU DARI DATABASE
            |--------------------------------------------------------------------------
            */

         $formatNomor =
            trim(
               $lockedSetting['format_rawat_inap'] ?? ''
            );


         if (
            $formatNomor === ''
         ) {

            throw new Exception(
               'Format nomor surat rawat inap belum diatur.'
            );
         }


         /*
            |--------------------------------------------------------------------------
            | GENERATE NOMOR
            |--------------------------------------------------------------------------
            */

         $nomor_surat =
            generateNomorSurat(
               $formatNomor,
               $nextNumber,
               $tanggal_surat
            );


         /*
            |--------------------------------------------------------------------------
            | UPDATE COUNTER
            |--------------------------------------------------------------------------
            */

         $stmtUpdateSetting =
            $koneksi->prepare("

                    UPDATE setting_surat

                    SET

                        nomor_rawat_inap = ?,

                        updated_at = NOW()

                    WHERE id_customer = ?

                ");


         if (!$stmtUpdateSetting) {

            throw new Exception(
               'Prepare update counter gagal: ' .
                  $koneksi->error
            );
         }


         $stmtUpdateSetting->bind_param(
            "is",
            $nextNumber,
            $id_customer
         );


         if (
            !$stmtUpdateSetting->execute()
         ) {

            $error =
               $stmtUpdateSetting->error;


            $stmtUpdateSetting->close();


            throw new Exception(
               'Gagal update nomor surat: ' .
                  $error
            );
         }


         $stmtUpdateSetting->close();


         /*
            |--------------------------------------------------------------------------
            | INSERT
            |--------------------------------------------------------------------------
            */

         $created_by =
            $_SESSION['uid_user']
            ??
            $_SESSION['username']
            ??
            'system';


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
            $koneksi->prepare(
               $query
            );


         if (!$stmt) {

            throw new Exception(
               'Prepare insert gagal: ' .
                  $koneksi->error
            );
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


         if (
            !$stmt->execute()
         ) {

            $error =
               $stmt->error;


            $stmt->close();


            throw new Exception(
               'Gagal menyimpan data: ' .
                  $error
            );
         }


         $newId =
            $stmt->insert_id;


         $stmt->close();


         /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

         $koneksi->commit();


         responseJson([

            'status' =>
            'success',

            'message' =>
            'Surat keterangan rawat inap berhasil disimpan.',

            'id' =>
            $newId,

            'nomor_surat' =>
            $nomor_surat,

            'mode_nomor' =>
            'AUTO'

         ]);
      } catch (Throwable $e) {

         /*
            |--------------------------------------------------------------------------
            | ROLLBACK
            |--------------------------------------------------------------------------
            */

         $koneksi->rollback();


         if (
            $e->getMessage() ===
            'SETTING_REQUIRED'
         ) {

            responseJson([
               'status'  =>
               'setting_required',

               'message' =>
               'Setting nomor surat rawat inap belum dibuat.'
            ]);
         }


         responseJson([
            'status'  =>
            'error',

            'message' =>
            $e->getMessage()
         ]);
      }
   }


   /*
    |--------------------------------------------------------------------------
    | MODE MANUAL INSERT
    |--------------------------------------------------------------------------
    */

   if (
      $modeNomor ===
      'MANUAL'
   ) {

      $created_by =
         $_SESSION['uid_user']
         ??
         $_SESSION['username']
         ??
         'system';


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
         $koneksi->prepare(
            $query
         );


      if (!$stmt) {

         responseJson([
            'status'  => 'error',
            'message' =>
            'Prepare insert gagal: ' .
               $koneksi->error
         ]);
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


      if (
         $stmt->execute()
      ) {

         $newId =
            $stmt->insert_id;


         $stmt->close();


         responseJson([

            'status' =>
            'success',

            'message' =>
            'Surat keterangan rawat inap berhasil disimpan.',

            'id' =>
            $newId,

            'nomor_surat' =>
            $nomor_surat,

            'mode_nomor' =>
            'MANUAL'

         ]);
      }


      $error =
         $stmt->error;


      $stmt->close();


      responseJson([
         'status'  => 'error',
         'message' =>
         'Gagal menyimpan data: ' .
            $error
      ]);
   }


   /*
    |--------------------------------------------------------------------------
    | MODE TIDAK VALID
    |--------------------------------------------------------------------------
    */

   responseJson([
      'status'  => 'error',
      'message' =>
      'Mode penomoran surat tidak valid.'
   ]);
}


/*
|--------------------------------------------------------------------------
| GET ALL
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

        WHERE sri.id_customer = ?

        ORDER BY

            sri.id DESC

    ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      responseJson([
         'status'  => 'error',
         'message' =>
         'Prepare query gagal: ' .
            $koneksi->error
      ]);
   }


   $stmt->bind_param(
      "ss",
      $id_customer,
      $id_customer
   );


   $stmt->execute();


   $result =
      $stmt->get_result();


   $data =
      $result->fetch_all(
         MYSQLI_ASSOC
      );


   $stmt->close();


   responseJson([

      'status' =>
      'success',

      'data' =>
      $data

   ]);
}


/*
|--------------------------------------------------------------------------
| GET BY ID
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

          AND sri.id_customer = ?

        LIMIT 1

    ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      responseJson([
         'status'  => 'error',
         'message' =>
         'Prepare query gagal: ' .
            $koneksi->error
      ]);
   }


   $stmt->bind_param(
      "sis",
      $id_customer,
      $id,
      $id_customer
   );


   $stmt->execute();


   $result =
      $stmt->get_result();


   if (
      $result->num_rows === 0
   ) {

      $stmt->close();


      responseJson([
         'status'  => 'error',
         'message' =>
         'Data surat rawat inap tidak ditemukan.'
      ]);
   }


   $data =
      $result->fetch_assoc();


   $stmt->close();


   responseJson([

      'status' =>
      'success',

      'data' =>
      $data

   ]);
}


/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
|
| NOMOR SURAT TIDAK DIUBAH
|
|--------------------------------------------------------------------------
*/

function updateData($id_customer)
{
   global $koneksi;


   /*
    |--------------------------------------------------------------------------
    | READ PUT
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
      ??
      $_GET['id']
      ??
      '';


   if ($id === '') {

      responseJson([
         'status'  => 'error',
         'message' =>
         'ID surat tidak ditemukan.'
      ]);
   }


   /*
    |--------------------------------------------------------------------------
    | FIELD
    |--------------------------------------------------------------------------
    */

   $tanggal_surat =
      trim(
         $data['tanggal_surat']
            ?? ''
      );


   $keterangan =
      trim(
         $data['keterangan']
            ?? ''
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

   if (
      $tanggal_surat === ''
   ) {

      responseJson([
         'status'  => 'error',
         'message' =>
         'Tanggal surat wajib diisi.'
      ]);
   }


   /*
    |--------------------------------------------------------------------------
    | CEK DATA
    |--------------------------------------------------------------------------
    */

   $check =
      $koneksi->prepare("

            SELECT

                sri.id,

                sri.id_visit,

                sri.id_patient,

                sri.nomor_surat

            FROM surat_rawat_inap sri

            INNER JOIN pasien_visit pv

                ON pv.id_visit =
                   sri.id_visit

            WHERE sri.id = ?

              AND sri.id_customer = ?

              AND pv.id_customer = ?

            LIMIT 1

        ");


   if (!$check) {

      responseJson([
         'status'  => 'error',
         'message' =>
         'Prepare validasi update gagal: ' .
            $koneksi->error
      ]);
   }


   $check->bind_param(
      "iss",
      $id,
      $id_customer,
      $id_customer
   );


   $check->execute();


   $result =
      $check->get_result();


   if (
      $result->num_rows === 0
   ) {

      $check->close();


      responseJson([
         'status'  => 'error',
         'message' =>
         'Data surat tidak ditemukan atau bukan milik fasilitas kesehatan ini.'
      ]);
   }


   $check->close();


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
    |
    | NOMOR SURAT SENGAJA TIDAK DIUPDATE.
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

          AND id_customer = ?

    ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      responseJson([
         'status'  => 'error',
         'message' =>
         'Prepare update gagal: ' .
            $koneksi->error
      ]);
   }


   $stmt->bind_param(
      "sssis",
      $tanggal_surat,
      $keterangan,
      $updated_by,
      $id,
      $id_customer
   );


   if (
      $stmt->execute()
   ) {

      $stmt->close();


      responseJson([

         'status' =>
         'success',

         'message' =>
         'Surat keterangan rawat inap berhasil diperbarui.'

      ]);
   }


   $error =
      $stmt->error;


   $stmt->close();


   responseJson([
      'status'  => 'error',
      'message' =>
      'Gagal memperbarui surat: ' .
         $error
   ]);
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


   if ($id === '') {

      responseJson([
         'status'  => 'error',
         'message' =>
         'ID surat tidak ditemukan.'
      ]);
   }


   /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

   $query = "

        DELETE FROM surat_rawat_inap

        WHERE id = ?

          AND id_customer = ?

    ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      responseJson([
         'status'  => 'error',
         'message' =>
         'Prepare delete gagal: ' .
            $koneksi->error
      ]);
   }


   $stmt->bind_param(
      "is",
      $id,
      $id_customer
   );


   if (
      $stmt->execute()
   ) {

      $affected =
         $stmt->affected_rows;


      $stmt->close();


      if (
         $affected > 0
      ) {

         responseJson([

            'status' =>
            'success',

            'message' =>
            'Surat keterangan rawat inap berhasil dihapus.'

         ]);
      }


      responseJson([

         'status' =>
         'error',

         'message' =>
         'Data tidak ditemukan atau tidak memiliki akses.'

      ]);
   }


   $error =
      $stmt->error;


   $stmt->close();


   responseJson([

      'status'  =>
      'error',

      'message' =>
      'Gagal menghapus surat: ' .
         $error

   ]);
}
