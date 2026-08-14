<?php

date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

require_once '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| RESPONSE HELPER
|--------------------------------------------------------------------------
*/

function responseJson(
   string $status,
   string $message = '',
   array $data = []
) {
   echo json_encode([
      'status'  => $status,
      'message' => $message,
      'data'    => $data
   ]);

   exit;
}


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

if (!$koneksi) {

   responseJson(
      'error',
      'Koneksi database gagal.'
   );
}


/*
|--------------------------------------------------------------------------
| SESSION CUSTOMER
|--------------------------------------------------------------------------
*/

if (
   !isset($_SESSION['id_customer']) ||
   $_SESSION['id_customer'] === ''
) {

   responseJson(
      'error',
      'Session faskes/customer tidak ditemukan.'
   );
}


$id_customer =
   (string) $_SESSION['id_customer'];


/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/

$created_by =
   $_SESSION['id_user']
   ?? $_SESSION['username']
   ?? 'system';


$updated_by =
   $_SESSION['id_user']
   ?? $_SESSION['username']
   ?? 'system';


/*
|--------------------------------------------------------------------------
| METHOD
|--------------------------------------------------------------------------
*/

$method =
   $_SERVER['REQUEST_METHOD'];


/*
|--------------------------------------------------------------------------
| ROUTING
|--------------------------------------------------------------------------
*/

switch ($method) {

   case 'GET':

      getSetting();

      break;


   case 'POST':

      $action =
         $_POST['action'] ?? 'save_setting';


      /*
        |--------------------------------------------------------------------------
        | UPDATE MODE ONLY
        |--------------------------------------------------------------------------
        */

      if ($action === 'update_mode') {

         updateMode();
      }


      /*
        |--------------------------------------------------------------------------
        | SAVE FULL SETTING
        |--------------------------------------------------------------------------
        */

      saveSetting();

      break;


   default:

      responseJson(
         'error',
         'Method tidak diizinkan.'
      );
}


/*
|--------------------------------------------------------------------------
| GET SETTING
|--------------------------------------------------------------------------
*/

function getSetting()
{

   global $koneksi;
   global $id_customer;


   $sql = "

        SELECT

            id,
            id_customer,

            mode_nomor,

            format_sakit,
            nomor_sakit,

            format_berobat,
            nomor_berobat,

            format_sehat,
            nomor_sehat,

            format_rawat_inap,
            nomor_rawat_inap,

            format_kematian,
            nomor_kematian,

            format_mata,
            nomor_mata,

            created_at,
            updated_at,
            created_by,
            updated_by

        FROM setting_surat

        WHERE id_customer = ?

        LIMIT 1

    ";


   $stmt =
      $koneksi->prepare($sql);


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare query gagal: ' .
            $koneksi->error
      );
   }


   $stmt->bind_param(
      's',
      $id_customer
   );


   if (!$stmt->execute()) {

      responseJson(
         'error',
         'Gagal mengambil setting: ' .
            $stmt->error
      );
   }


   $result =
      $stmt->get_result();


   $data =
      $result->fetch_assoc();


   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | DEFAULT JIKA BELUM ADA
    |--------------------------------------------------------------------------
    */

   if (!$data) {

      $data = [

         'id' => null,

         'id_customer' =>
         $id_customer,

         'mode_nomor' =>
         'AUTO',

         'format_sakit' =>
         'SKS/{NO}/{MM}/{YYYY}',

         'nomor_sakit' =>
         0,

         'format_berobat' =>
         'SKB/{NO}/{MM}/{YYYY}',

         'nomor_berobat' =>
         0,

         'format_sehat' =>
         'SKH/{NO}/{MM}/{YYYY}',

         'nomor_sehat' =>
         0,

         'format_rawat_inap' =>
         'SRI/{NO}/{MM}/{YYYY}',

         'nomor_rawat_inap' =>
         0,

         'format_kematian' =>
         'SKM/{NO}/{MM}/{YYYY}',

         'nomor_kematian' =>
         0,

         'format_mata' =>
         'SPM/{NO}/{MM}/{YYYY}',

         'nomor_mata' =>
         0

      ];
   }


   responseJson(
      'success',
      'Setting nomor surat berhasil diambil.',
      $data
   );
}


/*
|--------------------------------------------------------------------------
| UPDATE MODE ONLY
|--------------------------------------------------------------------------
|
| AUTO / MANUAL langsung disimpan ketika radio diklik.
|
*/

function updateMode()
{

   global $koneksi;
   global $id_customer;
   global $created_by;
   global $updated_by;


   /*
    |--------------------------------------------------------------------------
    | MODE
    |--------------------------------------------------------------------------
    */

   $mode_nomor =
      strtoupper(
         trim(
            $_POST['mode_nomor']
               ?? ''
         )
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

   if (
      !in_array(
         $mode_nomor,
         [
            'AUTO',
            'MANUAL'
         ],
         true
      )
   ) {

      responseJson(
         'error',
         'Mode penomoran tidak valid.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | CEK EXISTING
    |--------------------------------------------------------------------------
    */

   $sqlCheck = "

        SELECT id

        FROM setting_surat

        WHERE id_customer = ?

        LIMIT 1

    ";


   $stmtCheck =
      $koneksi->prepare(
         $sqlCheck
      );


   if (!$stmtCheck) {

      responseJson(
         'error',
         'Prepare check gagal: ' .
            $koneksi->error
      );
   }


   $stmtCheck->bind_param(
      's',
      $id_customer
   );


   if (!$stmtCheck->execute()) {

      responseJson(
         'error',
         'Check setting gagal: ' .
            $stmtCheck->error
      );
   }


   $result =
      $stmtCheck->get_result();


   $existing =
      $result->fetch_assoc();


   $stmtCheck->close();


   /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

   if ($existing) {

      $id =
         (int) $existing['id'];


      $sql = "

            UPDATE setting_surat

            SET

                mode_nomor = ?,

                updated_at = NOW(),

                updated_by = ?

            WHERE id = ?

              AND id_customer = ?

        ";


      $stmt =
         $koneksi->prepare(
            $sql
         );


      if (!$stmt) {

         responseJson(
            'error',
            'Prepare update mode gagal: ' .
               $koneksi->error
         );
      }


      /*
        |--------------------------------------------------------------------------
        | s = mode
        | s = user
        | i = id
        | s = customer
        |--------------------------------------------------------------------------
        */

      $stmt->bind_param(
         'ssis',
         $mode_nomor,
         $updated_by,
         $id,
         $id_customer
      );


      if (!$stmt->execute()) {

         responseJson(
            'error',
            'Gagal menyimpan mode: ' .
               $stmt->error
         );
      }


      $stmt->close();
   }


   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */ else {

      $sql = "

            INSERT INTO setting_surat
            (
                id_customer,
                mode_nomor,
                created_at,
                created_by
            )

            VALUES
            (
                ?,
                ?,
                NOW(),
                ?
            )

        ";


      $stmt =
         $koneksi->prepare(
            $sql
         );


      if (!$stmt) {

         responseJson(
            'error',
            'Prepare insert mode gagal: ' .
               $koneksi->error
         );
      }


      $stmt->bind_param(
         'sss',
         $id_customer,
         $mode_nomor,
         $created_by
      );


      if (!$stmt->execute()) {

         responseJson(
            'error',
            'Gagal membuat setting mode: ' .
               $stmt->error
         );
      }


      $stmt->close();
   }


   /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

   responseJson(

      'success',

      'Mode penomoran berhasil disimpan.',

      [

         'id_customer' =>
         $id_customer,

         'mode_nomor' =>
         $mode_nomor

      ]

   );
}


/*
|--------------------------------------------------------------------------
| SAVE FULL SETTING
|--------------------------------------------------------------------------
|
| Digunakan untuk AUTO:
|
| format + nomor terakhir
|
*/

function saveSetting()
{

   global $koneksi;
   global $id_customer;
   global $created_by;
   global $updated_by;


   /*
    |--------------------------------------------------------------------------
    | MODE
    |--------------------------------------------------------------------------
    */

   $mode_nomor =
      strtoupper(
         trim(
            $_POST['mode_nomor']
               ?? ''
         )
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI MODE
    |--------------------------------------------------------------------------
    */

   if (
      !in_array(
         $mode_nomor,
         [
            'AUTO',
            'MANUAL'
         ],
         true
      )
   ) {

      responseJson(
         'error',
         'Mode penomoran tidak valid.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | FORMAT
    |--------------------------------------------------------------------------
    */

   $format_sakit =
      trim(
         $_POST['format_sakit']
            ?? 'SKS/{NO}/{MM}/{YYYY}'
      );


   $format_berobat =
      trim(
         $_POST['format_berobat']
            ?? 'SKB/{NO}/{MM}/{YYYY}'
      );


   $format_sehat =
      trim(
         $_POST['format_sehat']
            ?? 'SKH/{NO}/{MM}/{YYYY}'
      );


   $format_rawat_inap =
      trim(
         $_POST['format_rawat_inap']
            ?? 'SRI/{NO}/{MM}/{YYYY}'
      );


   $format_kematian =
      trim(
         $_POST['format_kematian']
            ?? 'SKM/{NO}/{MM}/{YYYY}'
      );


   $format_mata =
      trim(
         $_POST['format_mata']
            ?? 'SPM/{NO}/{MM}/{YYYY}'
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI FORMAT
    |--------------------------------------------------------------------------
    */

   $formatList = [

      'Surat Sakit' =>
      $format_sakit,

      'Surat Berobat' =>
      $format_berobat,

      'Surat Sehat' =>
      $format_sehat,

      'Surat Rawat Inap' =>
      $format_rawat_inap,

      'Surat Kematian' =>
      $format_kematian,

      'Pemeriksaan Mata' =>
      $format_mata

   ];


   foreach (
      $formatList as $nama => $format
   ) {

      if ($format === '') {

         responseJson(
            'error',
            'Format nomor untuk ' .
               $nama .
               ' tidak boleh kosong.'
         );
      }
   }


   /*
    |--------------------------------------------------------------------------
    | NOMOR
    |--------------------------------------------------------------------------
    */

   $nomor_sakit =
      getNomor(
         $_POST['nomor_sakit']
            ?? 0
      );


   $nomor_berobat =
      getNomor(
         $_POST['nomor_berobat']
            ?? 0
      );


   $nomor_sehat =
      getNomor(
         $_POST['nomor_sehat']
            ?? 0
      );


   $nomor_rawat_inap =
      getNomor(
         $_POST['nomor_rawat_inap']
            ?? 0
      );


   $nomor_kematian =
      getNomor(
         $_POST['nomor_kematian']
            ?? 0
      );


   $nomor_mata =
      getNomor(
         $_POST['nomor_mata']
            ?? 0
      );


   /*
    |--------------------------------------------------------------------------
    | CEK EXISTING
    |--------------------------------------------------------------------------
    */

   $sqlCheck = "

        SELECT id

        FROM setting_surat

        WHERE id_customer = ?

        LIMIT 1

    ";


   $stmtCheck =
      $koneksi->prepare(
         $sqlCheck
      );


   if (!$stmtCheck) {

      responseJson(
         'error',
         'Prepare check gagal: ' .
            $koneksi->error
      );
   }


   $stmtCheck->bind_param(
      's',
      $id_customer
   );


   $stmtCheck->execute();


   $result =
      $stmtCheck->get_result();


   $existing =
      $result->fetch_assoc();


   $stmtCheck->close();


   /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

   if ($existing) {

      $id =
         (int) $existing['id'];


      $sql = "

            UPDATE setting_surat

            SET

                mode_nomor = ?,

                format_sakit = ?,
                nomor_sakit = ?,

                format_berobat = ?,
                nomor_berobat = ?,

                format_sehat = ?,
                nomor_sehat = ?,

                format_rawat_inap = ?,
                nomor_rawat_inap = ?,

                format_kematian = ?,
                nomor_kematian = ?,

                format_mata = ?,
                nomor_mata = ?,

                updated_at = NOW(),

                updated_by = ?

            WHERE id = ?

              AND id_customer = ?

        ";


      $stmt =
         $koneksi->prepare(
            $sql
         );


      if (!$stmt) {

         responseJson(
            'error',
            'Prepare update gagal: ' .
               $koneksi->error
         );
      }


      /*
        |--------------------------------------------------------------------------
        | 17 PARAMETER
        |--------------------------------------------------------------------------
        */

      $stmt->bind_param(

         'ssisisisisisisiis',

         $mode_nomor,

         $format_sakit,
         $nomor_sakit,

         $format_berobat,
         $nomor_berobat,

         $format_sehat,
         $nomor_sehat,

         $format_rawat_inap,
         $nomor_rawat_inap,

         $format_kematian,
         $nomor_kematian,

         $format_mata,
         $nomor_mata,

         $updated_by,

         $id,

         $id_customer

      );


      if (!$stmt->execute()) {

         responseJson(
            'error',
            'Gagal update setting: ' .
               $stmt->error
         );
      }


      $stmt->close();
   }


   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */ else {

      $sql = "

            INSERT INTO setting_surat
            (

                id_customer,

                mode_nomor,

                format_sakit,
                nomor_sakit,

                format_berobat,
                nomor_berobat,

                format_sehat,
                nomor_sehat,

                format_rawat_inap,
                nomor_rawat_inap,

                format_kematian,
                nomor_kematian,

                format_mata,
                nomor_mata,

                created_at,
                created_by

            )

            VALUES
            (

                ?,
                ?,

                ?,
                ?,

                ?,
                ?,

                ?,
                ?,

                ?,
                ?,

                ?,
                ?,

                ?,
                ?,

                NOW(),
                ?

            )

        ";


      $stmt =
         $koneksi->prepare(
            $sql
         );


      if (!$stmt) {

         responseJson(
            'error',
            'Prepare insert gagal: ' .
               $koneksi->error
         );
      }


      /*
        |--------------------------------------------------------------------------
        | 15 PARAMETER
        |--------------------------------------------------------------------------
        */

      $stmt->bind_param(

         'ssisisisisisisis',

         $id_customer,

         $mode_nomor,

         $format_sakit,
         $nomor_sakit,

         $format_berobat,
         $nomor_berobat,

         $format_sehat,
         $nomor_sehat,

         $format_rawat_inap,
         $nomor_rawat_inap,

         $format_kematian,
         $nomor_kematian,

         $format_mata,
         $nomor_mata,

         $created_by

      );


      if (!$stmt->execute()) {

         responseJson(
            'error',
            'Gagal insert setting: ' .
               $stmt->error
         );
      }


      $stmt->close();
   }


   /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

   responseJson(

      'success',

      'Pengaturan nomor surat berhasil disimpan.',

      [

         'id_customer' =>
         $id_customer,

         'mode_nomor' =>
         $mode_nomor,

         'format_sakit' =>
         $format_sakit,

         'nomor_sakit' =>
         $nomor_sakit,

         'format_berobat' =>
         $format_berobat,

         'nomor_berobat' =>
         $nomor_berobat,

         'format_sehat' =>
         $format_sehat,

         'nomor_sehat' =>
         $nomor_sehat,

         'format_rawat_inap' =>
         $format_rawat_inap,

         'nomor_rawat_inap' =>
         $nomor_rawat_inap,

         'format_kematian' =>
         $format_kematian,

         'nomor_kematian' =>
         $nomor_kematian,

         'format_mata' =>
         $format_mata,

         'nomor_mata' =>
         $nomor_mata

      ]

   );
}


/*
|--------------------------------------------------------------------------
| NORMALIZE NOMOR
|--------------------------------------------------------------------------
*/

function getNomor($value): int
{

   if (
      $value === null ||
      $value === ''
   ) {

      return 0;
   }


   $value =
      filter_var(
         $value,
         FILTER_VALIDATE_INT
      );


   if (
      $value === false ||
      $value < 0
   ) {

      return 0;
   }


   return (int) $value;
}
