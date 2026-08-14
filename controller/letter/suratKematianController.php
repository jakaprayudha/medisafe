<?php

session_start();

include '../../database/connect.php';

header('Content-Type: application/json');


/*
|--------------------------------------------------------------------------
| MYSQLI ERROR
|--------------------------------------------------------------------------
*/

mysqli_report(
   MYSQLI_REPORT_ERROR |
      MYSQLI_REPORT_STRICT
);


/*
|--------------------------------------------------------------------------
| VALIDASI SESSION
|--------------------------------------------------------------------------
*/

if (
   !isset($_SESSION['id_customer']) ||
   $_SESSION['id_customer'] === ''
) {

   echo json_encode([
      'status'  => 'error',
      'message' => 'Session faskes tidak ditemukan.'
   ]);

   exit;
}


$id_customer =
   (string) $_SESSION['id_customer'];


$method =
   $_SERVER['REQUEST_METHOD'];


/*
|--------------------------------------------------------------------------
| ROUTING
|--------------------------------------------------------------------------
*/

try {

   switch ($method) {

      case 'POST':

         createData(
            $id_customer
         );

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

            getData(
               $id_customer
            );
         }

         break;


      case 'PUT':

         updateData(
            $id_customer
         );

         break;


      case 'DELETE':

         deleteData(
            $id_customer
         );

         break;


      default:

         responseJson(
            'error',
            'Method tidak diizinkan.'
         );
   }
} catch (
   Throwable $e
) {

   echo json_encode([

      'status' =>
      'error',

      'message' =>
      $e->getMessage()

   ]);

   exit;
}


/*
|--------------------------------------------------------------------------
| RESPONSE JSON
|--------------------------------------------------------------------------
*/

function responseJson(
   $status,
   $message,
   $data = null
) {

   $response = [

      'status' =>
      $status,

      'message' =>
      $message

   ];


   if (
      $data !== null
   ) {

      $response['data'] =
         $data;
   }


   echo json_encode(
      $response
   );

   exit;
}


/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/

function getCurrentUser()
{

   return

      $_SESSION['uid_user']

      ??

      $_SESSION['id_user']

      ??

      $_SESSION['username']

      ??

      'system';
}


/*
|--------------------------------------------------------------------------
| GET SETTING SURAT KEMATIAN
|--------------------------------------------------------------------------
|
| Struktur setting_surat:
|
| mode_nomor
| format_kematian
| nomor_kematian
|
|--------------------------------------------------------------------------
*/

function getSettingSuratKematian(
   $id_customer
) {

   global $koneksi;


   $query = "

      SELECT

         id,
         id_customer,
         mode_nomor,
         format_kematian,
         nomor_kematian

      FROM setting_surat

      WHERE id_customer = ?

      LIMIT 1

   ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      return [

         'status' =>
         false,

         'message' =>
         'Prepare setting nomor surat gagal: '
            . $koneksi->error

      ];
   }


   $stmt->bind_param(
      "s",
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

         'status' =>
         false,

         'setting_not_found' =>
         true,

         'message' =>
         'Setting nomor Surat Keterangan Kematian belum dibuat.'

      ];
   }


   $setting =
      $result->fetch_assoc();


   $stmt->close();


   return [

      'status' =>
      true,

      'setting' =>
      $setting

   ];
}


/*
|--------------------------------------------------------------------------
| GENERATE NOMOR SURAT
|--------------------------------------------------------------------------
|
| AUTO:
|
| format_kematian = SKM/{NO}/{MM}/{YYYY}
|
| nomor_kematian = 10
|
| hasil:
|
| SKM/11/08/2026
|
|--------------------------------------------------------------------------
*/

function generateNomorKematian(
   $tanggal_surat,
   $setting
) {

   $format =
      trim(
         $setting['format_kematian']
            ?? ''
      );


   if (
      $format === ''
   ) {

      $format =
         'SKM/{NO}/{MM}/{YYYY}';
   }


   /*
   |--------------------------------------------------------------------------
   | NOMOR SAAT INI
   |--------------------------------------------------------------------------
   */

   $currentNumber =
      (int) (
         $setting['nomor_kematian']
         ?? 0
      );


   /*
   |--------------------------------------------------------------------------
   | NOMOR BERIKUTNYA
   |--------------------------------------------------------------------------
   */

   $nextNumber =
      $currentNumber + 1;


   /*
   |--------------------------------------------------------------------------
   | TANGGAL
   |--------------------------------------------------------------------------
   */

   $timestamp =
      strtotime(
         $tanggal_surat
      );


   if (
      !$timestamp
   ) {

      $timestamp =
         time();
   }


   $year =
      date(
         'Y',
         $timestamp
      );


   $month =
      date(
         'm',
         $timestamp
      );


   $day =
      date(
         'd',
         $timestamp
      );


   /*
   |--------------------------------------------------------------------------
   | FORMAT NOMOR
   |--------------------------------------------------------------------------
   |
   | Mendukung:
   |
   | {NO}
   | {NO:4}
   |
   |--------------------------------------------------------------------------
   */

   $numberFormatted =
      (string) $nextNumber;


   if (
      preg_match(
         '/\{NO:(\d+)\}/i',
         $format,
         $matches
      )
   ) {

      $length =
         (int) $matches[1];


      $numberFormatted =
         str_pad(
            $nextNumber,
            $length,
            '0',
            STR_PAD_LEFT
         );
   }


   /*
   |--------------------------------------------------------------------------
   | REPLACE NOMOR
   |--------------------------------------------------------------------------
   */

   $format =
      preg_replace(
         '/\{NO:\d+\}/i',
         $numberFormatted,
         $format
      );


   $format =
      str_replace(
         '{NO}',
         $numberFormatted,
         $format
      );


   /*
   |--------------------------------------------------------------------------
   | REPLACE TANGGAL
   |--------------------------------------------------------------------------
   */

   $format =
      str_replace(
         '{YYYY}',
         $year,
         $format
      );


   $format =
      str_replace(
         '{MM}',
         $month,
         $format
      );


   $format =
      str_replace(
         '{DD}',
         $day,
         $format
      );


   return [

      'status' =>
      true,

      'nomor_surat' =>
      $format,

      'nomor' =>
      $nextNumber

   ];
}


/*
|--------------------------------------------------------------------------
| VALIDASI VISIT
|--------------------------------------------------------------------------
*/

function validateVisit(
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
         pv.id_doctor,
         pv.id_poli

      FROM pasien_visit pv

      WHERE pv.id_visit = ?

      AND pv.id_patient = ?

      AND pv.id_customer = ?

      LIMIT 1

   ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      return false;
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


   $valid =
      $result->num_rows > 0;


   $stmt->close();


   return $valid;
}


/*
|--------------------------------------------------------------------------
| CREATE
|--------------------------------------------------------------------------
*/

function createData(
   $id_customer
) {

   global $koneksi;


   /*
   |--------------------------------------------------------------------------
   | INPUT
   |--------------------------------------------------------------------------
   */

   $id_patient =
      trim(
         $_POST['id_patient']
            ?? ''
      );


   $id_visit =
      trim(
         $_POST['id_visit']
            ?? ''
      );


   $nomorManual =
      trim(
         $_POST['nomor_surat']
            ?? ''
      );


   $tanggal_surat =
      trim(
         $_POST['tanggal_surat']
            ?? date('Y-m-d')
      );


   $tanggal_kematian =
      trim(
         $_POST['tanggal_kematian']
            ?? ''
      );


   $waktu_kematian =
      trim(
         $_POST['waktu_kematian']
            ?? ''
      );


   $ruangan =
      trim(
         $_POST['ruangan']
            ?? ''
      );


   $dokter_menyatakan =
      trim(
         $_POST['dokter_menyatakan']
            ?? ''
      );


   /*
   |--------------------------------------------------------------------------
   | VALIDASI
   |--------------------------------------------------------------------------
   */

   if (
      $id_patient === ''
   ) {

      responseJson(
         'error',
         'Pasien wajib dipilih.'
      );
   }


   if (
      $id_visit === ''
   ) {

      responseJson(
         'error',
         'Visit pasien tidak ditemukan.'
      );
   }


   if (
      $tanggal_surat === ''
   ) {

      responseJson(
         'error',
         'Tanggal surat wajib diisi.'
      );
   }


   if (
      $tanggal_kematian === ''
   ) {

      responseJson(
         'error',
         'Tanggal kematian wajib diisi.'
      );
   }


   if (
      $waktu_kematian === ''
   ) {

      responseJson(
         'error',
         'Waktu kematian wajib diisi.'
      );
   }


   if (
      $ruangan === ''
   ) {

      responseJson(
         'error',
         'Ruangan wajib diisi.'
      );
   }


   if (
      $dokter_menyatakan === ''
   ) {

      responseJson(
         'error',
         'Dokter yang menyatakan wajib dipilih.'
      );
   }


   /*
   |--------------------------------------------------------------------------
   | VALIDASI VISIT
   |--------------------------------------------------------------------------
   */

   if (
      !validateVisit(
         $id_visit,
         $id_patient,
         $id_customer
      )
   ) {

      responseJson(
         'error',
         'Visit pasien tidak valid atau bukan milik fasilitas kesehatan ini.'
      );
   }


   /*
   |--------------------------------------------------------------------------
   | SETTING NOMOR SURAT
   |--------------------------------------------------------------------------
   */

   $settingResult =
      getSettingSuratKematian(
         $id_customer
      );


   if (
      !$settingResult['status']
   ) {

      responseJson(

         'setting_required',

         $settingResult['message'],

         [

            'redirect' =>
            'module/letter/setting-surat'

         ]

      );
   }


   $setting =
      $settingResult['setting'];


   /*
   |--------------------------------------------------------------------------
   | MODE NOMOR
   |--------------------------------------------------------------------------
   */

   $mode =
      strtoupper(
         trim(
            $setting['mode_nomor']
               ?? ''
         )
      );


   if (
      $mode !== 'AUTO'
      &&
      $mode !== 'MANUAL'
   ) {

      responseJson(

         'setting_required',

         'Mode penomoran surat belum ditentukan.',

         [

            'redirect' =>
            'module/letter/setting-surat'

         ]

      );
   }


   /*
   |--------------------------------------------------------------------------
   | NOMOR SURAT
   |--------------------------------------------------------------------------
   */

   $nomor_surat =
      '';


   $generatedNumber =
      null;


   if (
      $mode === 'MANUAL'
   ) {

      /*
      |--------------------------------------------------------------------------
      | MANUAL
      |--------------------------------------------------------------------------
      */

      if (
         $nomorManual === ''
      ) {

         responseJson(
            'error',
            'Nomor surat wajib diisi karena menggunakan mode manual.'
         );
      }


      $nomor_surat =
         $nomorManual;
   } else {

      /*
      |--------------------------------------------------------------------------
      | AUTO
      |--------------------------------------------------------------------------
      */

      $generatedNumber =
         generateNomorKematian(
            $tanggal_surat,
            $setting
         );


      if (
         !$generatedNumber['status']
      ) {

         responseJson(
            'error',
            'Gagal membuat nomor surat.'
         );
      }


      $nomor_surat =
         $generatedNumber['nomor_surat'];
   }


   /*
   |--------------------------------------------------------------------------
   | CEK DUPLIKAT NOMOR
   |--------------------------------------------------------------------------
   */

   $checkNomor =
      $koneksi->prepare("

         SELECT id

         FROM surat_kematian

         WHERE nomor_surat = ?

         AND id_customer = ?

         LIMIT 1

      ");


   if (!$checkNomor) {

      responseJson(
         'error',
         'Prepare cek nomor surat gagal: '
            . $koneksi->error
      );
   }


   $checkNomor->bind_param(
      "ss",
      $nomor_surat,
      $id_customer
   );


   $checkNomor->execute();


   $nomorResult =
      $checkNomor->get_result();


   if (
      $nomorResult->num_rows > 0
   ) {

      $checkNomor->close();


      responseJson(
         'error',
         'Nomor surat sudah digunakan.'
      );
   }


   $checkNomor->close();


   /*
   |--------------------------------------------------------------------------
   | USER
   |--------------------------------------------------------------------------
   */

   $created_by =
      getCurrentUser();


   /*
   |--------------------------------------------------------------------------
   | TRANSACTION
   |--------------------------------------------------------------------------
   */

   $koneksi->begin_transaction();


   try {

      /*
      |--------------------------------------------------------------------------
      | INSERT SURAT
      |--------------------------------------------------------------------------
      */

      $query = "

         INSERT INTO surat_kematian (

            id_visit,
            id_patient,
            id_customer,
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
            ?,
            ?

         )

      ";


      $stmt =
         $koneksi->prepare(
            $query
         );


      if (!$stmt) {

         throw new Exception(
            'Prepare insert gagal: '
               . $koneksi->error
         );
      }


      $stmt->bind_param(
         "ssssssssss",
         $id_visit,
         $id_patient,
         $id_customer,
         $nomor_surat,
         $tanggal_surat,
         $tanggal_kematian,
         $waktu_kematian,
         $ruangan,
         $dokter_menyatakan,
         $created_by
      );


      $stmt->execute();


      $newId =
         $stmt->insert_id;


      $stmt->close();


      /*
      |--------------------------------------------------------------------------
      | UPDATE COUNTER AUTO
      |--------------------------------------------------------------------------
      */

      if (
         $mode === 'AUTO'
      ) {

         $nextNumber =
            (int)
            $generatedNumber['nomor'];


         $updateCounter =
            $koneksi->prepare("

               UPDATE setting_surat

               SET

                  nomor_kematian = ?,

                  updated_at = NOW(),

                  updated_by = ?

               WHERE id_customer = ?

            ");


         if (!$updateCounter) {

            throw new Exception(
               'Prepare update counter gagal: '
                  . $koneksi->error
            );
         }


         $updateCounter->bind_param(
            "iss",
            $nextNumber,
            $created_by,
            $id_customer
         );


         $updateCounter->execute();


         if (
            $updateCounter->affected_rows < 0
         ) {

            throw new Exception(
               'Counter nomor kematian gagal diperbarui.'
            );
         }


         $updateCounter->close();
      }


      /*
      |--------------------------------------------------------------------------
      | COMMIT
      |--------------------------------------------------------------------------
      */

      $koneksi->commit();


      responseJson(

         'success',

         'Surat keterangan kematian berhasil disimpan.',

         [

            'id' =>
            $newId,

            'id_customer' =>
            $id_customer,

            'nomor_surat' =>
            $nomor_surat,

            'mode_nomor' =>
            $mode

         ]

      );
   } catch (
      Throwable $e
   ) {

      /*
      |--------------------------------------------------------------------------
      | ROLLBACK
      |--------------------------------------------------------------------------
      */

      $koneksi->rollback();


      responseJson(

         'error',

         'Gagal menyimpan surat: '
            . $e->getMessage()

      );
   }
}


/*
|--------------------------------------------------------------------------
| GET ALL
|--------------------------------------------------------------------------
*/

function getData(
   $id_customer
) {

   global $koneksi;


   $query = "

      SELECT

         sk.id,
         sk.id_visit,
         sk.id_patient,
         sk.id_customer,
         sk.nomor_surat,
         sk.tanggal_surat,
         sk.tanggal_kematian,
         sk.waktu_kematian,
         sk.ruangan,
         sk.dokter_menyatakan,
         sk.created_at,
         sk.updated_at,
         sk.created_by,
         sk.updated_by,

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

      INNER JOIN ms_patient mp

         ON mp.id_patient =
            sk.id_patient

      INNER JOIN pasien_visit pv

         ON pv.id_visit =
            sk.id_visit

         AND pv.id_customer =
            sk.id_customer

      LEFT JOIN ms_doctor md

         ON md.id_doctor =
            sk.dokter_menyatakan

      WHERE sk.id_customer = ?

      ORDER BY

         sk.id DESC

   ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare GET gagal: '
            . $koneksi->error
      );
   }


   $stmt->bind_param(
      "s",
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


   responseJson(
      'success',
      'Data berhasil diambil.',
      $data
   );
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


   $id =
      (int) $id;


   if (
      $id <= 0
   ) {

      responseJson(
         'error',
         'ID surat tidak valid.'
      );
   }


   $query = "

      SELECT

         sk.id,
         sk.id_visit,
         sk.id_patient,
         sk.id_customer,
         sk.nomor_surat,
         sk.tanggal_surat,
         sk.tanggal_kematian,
         sk.waktu_kematian,
         sk.ruangan,
         sk.dokter_menyatakan,
         sk.created_at,
         sk.updated_at,
         sk.created_by,
         sk.updated_by,

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

      INNER JOIN ms_patient mp

         ON mp.id_patient =
            sk.id_patient

      INNER JOIN pasien_visit pv

         ON pv.id_visit =
            sk.id_visit

         AND pv.id_customer =
            sk.id_customer

      LEFT JOIN ms_doctor md

         ON md.id_doctor =
            sk.dokter_menyatakan

      WHERE sk.id = ?

      AND sk.id_customer = ?

      LIMIT 1

   ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare GET ID gagal: '
            . $koneksi->error
      );
   }


   $stmt->bind_param(
      "is",
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


      responseJson(
         'error',
         'Data surat tidak ditemukan.'
      );
   }


   $data =
      $result->fetch_assoc();


   $stmt->close();


   /*
   |--------------------------------------------------------------------------
   | SETTING NOMOR
   |--------------------------------------------------------------------------
   */

   $settingResult =
      getSettingSuratKematian(
         $id_customer
      );


   if (
      $settingResult['status']
   ) {

      $setting =
         $settingResult['setting'];


      $data['mode_nomor'] =
         $setting['mode_nomor']
         ?? '';


      $data['format_kematian'] =
         $setting['format_kematian']
         ?? '';


      $data['nomor_kematian'] =
         $setting['nomor_kematian']
         ?? 0;
   } else {

      $data['mode_nomor'] =
         '';

      $data['format_kematian'] =
         '';

      $data['nomor_kematian'] =
         0;
   }


   responseJson(
      'success',
      'Data surat berhasil diambil.',
      $data
   );
}


/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
|
| Nomor surat TIDAK DIUBAH.
|
|--------------------------------------------------------------------------
*/

function updateData(
   $id_customer
) {

   global $koneksi;


   /*
   |--------------------------------------------------------------------------
   | BACA PUT
   |--------------------------------------------------------------------------
   */

   $rawInput =
      file_get_contents(
         'php://input'
      );


   parse_str(
      $rawInput,
      $putData
   );


   /*
   |--------------------------------------------------------------------------
   | ID
   |--------------------------------------------------------------------------
   */

   $id =
      $putData['id']
      ??
      $_GET['id']
      ??
      '';


   $id =
      (int) $id;


   if (
      $id <= 0
   ) {

      responseJson(
         'error',
         'ID surat tidak ditemukan.'
      );
   }


   /*
   |--------------------------------------------------------------------------
   | DATA
   |--------------------------------------------------------------------------
   */

   $tanggal_surat =
      trim(
         $putData['tanggal_surat']
            ?? ''
      );


   $tanggal_kematian =
      trim(
         $putData['tanggal_kematian']
            ?? ''
      );


   $waktu_kematian =
      trim(
         $putData['waktu_kematian']
            ?? ''
      );


   $ruangan =
      trim(
         $putData['ruangan']
            ?? ''
      );


   $dokter_menyatakan =
      trim(
         $putData['dokter_menyatakan']
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

      responseJson(
         'error',
         'Tanggal surat wajib diisi.'
      );
   }


   if (
      $tanggal_kematian === ''
   ) {

      responseJson(
         'error',
         'Tanggal kematian wajib diisi.'
      );
   }


   if (
      $waktu_kematian === ''
   ) {

      responseJson(
         'error',
         'Waktu kematian wajib diisi.'
      );
   }


   if (
      $ruangan === ''
   ) {

      responseJson(
         'error',
         'Ruangan wajib diisi.'
      );
   }


   if (
      $dokter_menyatakan === ''
   ) {

      responseJson(
         'error',
         'Dokter yang menyatakan wajib dipilih.'
      );
   }


   /*
   |--------------------------------------------------------------------------
   | UPDATED BY
   |--------------------------------------------------------------------------
   */

   $updated_by =
      getCurrentUser();


   /*
   |--------------------------------------------------------------------------
   | UPDATE
   |--------------------------------------------------------------------------
   |
   | Nomor surat sengaja TIDAK dimasukkan.
   |
   |--------------------------------------------------------------------------
   */

   $query = "

      UPDATE surat_kematian

      SET

         tanggal_surat = ?,

         tanggal_kematian = ?,

         waktu_kematian = ?,

         ruangan = ?,

         dokter_menyatakan = ?,

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

      responseJson(
         'error',
         'Prepare UPDATE gagal: '
            . $koneksi->error
      );
   }


   /*
   |--------------------------------------------------------------------------
   | PARAMETER
   |--------------------------------------------------------------------------
   |
   | tanggal_surat      = s
   | tanggal_kematian   = s
   | waktu_kematian     = s
   | ruangan            = s
   | dokter_menyatakan  = s
   | updated_by         = s
   | id                 = i
   | id_customer        = s
   |
   |--------------------------------------------------------------------------
   */

   $stmt->bind_param(
      "ssssss is",
      $tanggal_surat,
      $tanggal_kematian,
      $waktu_kematian,
      $ruangan,
      $dokter_menyatakan,
      $updated_by,
      $id,
      $id_customer
   );


   /*
   |--------------------------------------------------------------------------
   | IMPORTANT
   |--------------------------------------------------------------------------
   | mysqli bind_param TIDAK BOLEH memiliki spasi.
   |
   */

   $stmt->bind_param(
      "ssssssis",
      $tanggal_surat,
      $tanggal_kematian,
      $waktu_kematian,
      $ruangan,
      $dokter_menyatakan,
      $updated_by,
      $id,
      $id_customer
   );


   $stmt->execute();


   $affected =
      $stmt->affected_rows;


   $stmt->close();


   responseJson(

      'success',

      'Surat keterangan kematian berhasil diperbarui.',

      [

         'id' =>
         $id,

         'affected_rows' =>
         $affected

      ]

   );
}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

function deleteData(
   $id_customer
) {

   global $koneksi;


   $id =
      $_GET['id']
      ?? '';


   $id =
      (int) $id;


   if (
      $id <= 0
   ) {

      responseJson(
         'error',
         'ID surat tidak ditemukan.'
      );
   }


   /*
   |--------------------------------------------------------------------------
   | DELETE
   |--------------------------------------------------------------------------
   */

   $query = "

      DELETE FROM surat_kematian

      WHERE id = ?

      AND id_customer = ?

   ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare DELETE gagal: '
            . $koneksi->error
      );
   }


   $stmt->bind_param(
      "is",
      $id,
      $id_customer
   );


   $stmt->execute();


   $affected =
      $stmt->affected_rows;


   $stmt->close();


   if (
      $affected > 0
   ) {

      responseJson(
         'success',
         'Surat keterangan kematian berhasil dihapus.'
      );
   }


   responseJson(
      'error',
      'Data tidak ditemukan atau bukan milik fasilitas kesehatan ini.'
   );
}
