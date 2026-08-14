<?php

session_start();

include '../../database/connect.php';

header('Content-Type: application/json');

/*
|--------------------------------------------------------------------------
| DEVELOPMENT ERROR REPORTING
|--------------------------------------------------------------------------
*/

mysqli_report(
   MYSQLI_REPORT_ERROR |
      MYSQLI_REPORT_STRICT
);


/*
|--------------------------------------------------------------------------
| SESSION CUSTOMER
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id_customer'])) {

   echo json_encode([
      'status'  => 'error',
      'message' => 'Session faskes tidak ditemukan.'
   ]);

   exit;
}


$id_customer = (string) $_SESSION['id_customer'];


/*
|--------------------------------------------------------------------------
| METHOD
|--------------------------------------------------------------------------
*/

$method = $_SERVER['REQUEST_METHOD'];


/*
|--------------------------------------------------------------------------
| ROUTING
|--------------------------------------------------------------------------
*/

try {

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
} catch (Throwable $e) {

   /*
    |--------------------------------------------------------------------------
    | ERROR RESPONSE
    |--------------------------------------------------------------------------
    */

   echo json_encode([
      'status'  => 'error',
      'message' => $e->getMessage()
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


   if ($data !== null) {

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
| CURRENT USER
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
| GET SETTING NOMOR SURAT
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
            format_berobat,
            nomor_berobat
        FROM setting_surat
        WHERE id_customer = ?
        LIMIT 1
    ";

   $stmt = $koneksi->prepare($query);

   if (!$stmt) {
      return [
         'status' => false,
         'message' => 'Prepare setting surat gagal: ' . $koneksi->error
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
         'status' => false,
         'setting_not_found' => true,
         'message' => 'Setting nomor surat belum dibuat.'
      ];
   }

   $setting = $result->fetch_assoc();

   $stmt->close();

   return [
      'status' => true,
      'setting' => $setting
   ];
}


/*
|--------------------------------------------------------------------------
| GENERATE NOMOR SURAT
|--------------------------------------------------------------------------
*/

function generateNomorSurat(
   $id_customer,
   $tanggal_surat,
   $setting
) {

   global $koneksi;


   /*
    |--------------------------------------------------------------------------
    | FORMAT
    |--------------------------------------------------------------------------
    */

   $format =
      trim(
         $setting['format_nomor']
            ?? ''
      );


   if (
      $format === ''
   ) {

      $format =
         'SKB/{YYYY}/{MM}/{NO}';
   }


   /*
    |--------------------------------------------------------------------------
    | NOMOR TERTINGGI
    |--------------------------------------------------------------------------
    */

   $nomor_tertinggi =
      (int) (
         $setting['nomor_tertinggi']
         ?? 0
      );


   /*
    |--------------------------------------------------------------------------
    | CARI NOMOR TERAKHIR
    |--------------------------------------------------------------------------
    */

   $query = "

        SELECT nomor_surat

        FROM surat_berobat

        WHERE id_customer = ?

        ORDER BY id DESC

        LIMIT 1000

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
         'Prepare pencarian nomor terakhir gagal: '
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


   $lastNumber =
      $nomor_tertinggi;


   while (
      $row =
      $result->fetch_assoc()
   ) {

      $nomor =
         trim(
            $row['nomor_surat']
               ?? ''
         );


      if (
         $nomor === ''
      ) {

         continue;
      }


      /*
        |--------------------------------------------------------------------------
        | AMBIL ANGKA PALING BELAKANG
        |--------------------------------------------------------------------------
        */

      if (
         preg_match(
            '/(\d+)\s*$/',
            $nomor,
            $matches
         )
      ) {

         $number =
            (int) $matches[1];


         if (
            $number >
            $lastNumber
         ) {

            $lastNumber =
               $number;
         }
      }
   }


   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | NEXT NUMBER
    |--------------------------------------------------------------------------
    */

   $nextNumber =
      $lastNumber + 1;


   /*
    |--------------------------------------------------------------------------
    | DATE
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


   $yearShort =
      date(
         'y',
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
    | PANJANG NOMOR
    |--------------------------------------------------------------------------
    */

   $numberLength =
      4;


   if (
      preg_match(
         '/\{NO:(\d+)\}/i',
         $format,
         $matches
      )
   ) {

      $numberLength =
         (int) $matches[1];
   }


   /*
    |--------------------------------------------------------------------------
    | FORMAT ANGKA
    |--------------------------------------------------------------------------
    */

   $numberFormatted =
      str_pad(
         $nextNumber,
         $numberLength,
         '0',
         STR_PAD_LEFT
      );


   /*
    |--------------------------------------------------------------------------
    | REPLACE NO
    |--------------------------------------------------------------------------
    */

   $format =
      preg_replace(
         '/\{NO:\d+\}/i',
         $numberFormatted,
         $format
      );


   $format =
      preg_replace(
         '/\{NO\}/i',
         $numberFormatted,
         $format
      );


   /*
    |--------------------------------------------------------------------------
    | REPLACE DATE
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
         '{YY}',
         $yearShort,
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
| CREATE DATA
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


   $tanggal_surat =
      trim(
         $_POST['tanggal_surat']
            ?? date('Y-m-d')
      );


   $nomorManual =
      trim(
         $_POST['nomor_surat']
            ?? ''
      );


   $keterangan =
      trim(
         $_POST['keterangan']
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
    | SETTING NOMOR
    |--------------------------------------------------------------------------
    */

   $settingResult =
      getSettingNomorSurat(
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


   $mode =
      strtoupper(
         trim(
            $setting['mode_nomor']
               ?? ''
         )
      );


   if (
      $mode !== 'MANUAL'
      &&
      $mode !== 'AUTO'
   ) {

      responseJson(
         'setting_required',
         'Mode nomor Surat Keterangan Berobat belum ditentukan.',
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

   if (
      $mode === 'MANUAL'
   ) {

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

      $generated =
         generateNomorSurat(
            $id_customer,
            $tanggal_surat,
            $setting
         );


      if (
         !$generated['status']
      ) {

         responseJson(
            'error',
            $generated['message']
         );
      }


      $nomor_surat =
         $generated['nomor_surat'];
   }


   /*
    |--------------------------------------------------------------------------
    | CEK DUPLIKAT NOMOR
    |--------------------------------------------------------------------------
    */

   $check =
      $koneksi->prepare("

            SELECT id

            FROM surat_berobat

            WHERE nomor_surat = ?

            AND id_customer = ?

            LIMIT 1

        ");


   $check->bind_param(
      "ss",
      $nomor_surat,
      $id_customer
   );


   $check->execute();


   $result =
      $check->get_result();


   if (
      $result->num_rows > 0
   ) {

      $check->close();


      responseJson(
         'error',
         'Nomor surat sudah digunakan.'
      );
   }


   $check->close();


   /*
    |--------------------------------------------------------------------------
    | CREATED BY
    |--------------------------------------------------------------------------
    */

   $created_by =
      getCurrentUser();


   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

   $query = "

        INSERT INTO surat_berobat (

            id_visit,
            id_patient,
            id_customer,
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
            ?,
            ?

        )

    ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare INSERT gagal: '
            . $koneksi->error
      );
   }


   $stmt->bind_param(
      "sssssss",
      $id_visit,
      $id_patient,
      $id_customer,
      $nomor_surat,
      $tanggal_surat,
      $keterangan,
      $created_by
   );


   $stmt->execute();


   $newId =
      $stmt->insert_id;


   $stmt->close();


   responseJson(
      'success',
      'Surat keterangan berobat berhasil disimpan.',
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
}


/*
|--------------------------------------------------------------------------
| GET DATA
|--------------------------------------------------------------------------
*/

function getData(
   $id_customer
) {

   global $koneksi;


   $query = "

        SELECT

            sb.*,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_nik,
            mp.patient_bpjs,
            mp.patient_datebirth,

            pv.id_doctor,
            pv.id_poli,
            pv.visit_ID,
            pv.visit_date,
            pv.visit_time

        FROM surat_berobat sb

        INNER JOIN ms_patient mp

            ON mp.id_patient =
               sb.id_patient

        LEFT JOIN pasien_visit pv

            ON pv.id_visit =
               sb.id_visit

        WHERE sb.id_customer = ?

        ORDER BY sb.id DESC

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

            sb.*,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_nik,
            mp.patient_bpjs,
            mp.patient_datebirth,

            pv.id_doctor,
            pv.id_poli,
            pv.visit_ID,
            pv.visit_date,
            pv.visit_time

        FROM surat_berobat sb

        INNER JOIN ms_patient mp

            ON mp.id_patient =
               sb.id_patient

        LEFT JOIN pasien_visit pv

            ON pv.id_visit =
               sb.id_visit

        WHERE sb.id = ?

        AND sb.id_customer = ?

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
    | SETTING
    |--------------------------------------------------------------------------
    */

   $settingResult =
      getSettingNomorSurat(
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


      $data['format_nomor'] =
         $setting['format_nomor']
         ?? '';


      $data['nomor_tertinggi'] =
         $setting['nomor_tertinggi']
         ?? 0;
   } else {

      $data['mode_nomor'] =
         '';

      $data['format_nomor'] =
         '';

      $data['nomor_tertinggi'] =
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
| UPDATE DATA
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


   $keterangan =
      trim(
         $putData['keterangan']
            ?? ''
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI TANGGAL
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


   /*
    |--------------------------------------------------------------------------
    | VALIDASI FORMAT TANGGAL
    |--------------------------------------------------------------------------
    */

   $dateObject =
      DateTime::createFromFormat(
         'Y-m-d',
         $tanggal_surat
      );


   if (
      !$dateObject ||
      $dateObject->format('Y-m-d')
      !==
      $tanggal_surat
   ) {

      responseJson(
         'error',
         'Format tanggal surat tidak valid.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | CEK DATA
    |--------------------------------------------------------------------------
    */

   $check =
      $koneksi->prepare("

            SELECT

                id,
                id_visit,
                id_patient,
                id_customer,
                nomor_surat

            FROM surat_berobat

            WHERE id = ?

            AND id_customer = ?

            LIMIT 1

        ");


   if (!$check) {

      responseJson(
         'error',
         'Prepare validasi UPDATE gagal: '
            . $koneksi->error
      );
   }


   $check->bind_param(
      "is",
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


      responseJson(
         'error',
         'Data surat tidak ditemukan atau bukan milik fasilitas kesehatan ini.'
      );
   }


   $existing =
      $checkResult->fetch_assoc();


   $check->close();


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
    | NOMOR SURAT TIDAK DIUBAH
    |
    */

   $query = "

        UPDATE surat_berobat

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

      responseJson(
         'error',
         'Prepare UPDATE gagal: '
            . $koneksi->error
      );
   }


   /*
    |--------------------------------------------------------------------------
    | s = tanggal_surat
    | s = keterangan
    | s = updated_by
    | i = id
    | s = id_customer
    |--------------------------------------------------------------------------
    */

   $stmt->bind_param(
      "sssis",
      $tanggal_surat,
      $keterangan,
      $updated_by,
      $id,
      $id_customer
   );


   $stmt->execute();


   $stmt->close();


   responseJson(
      'success',
      'Surat keterangan berobat berhasil diperbarui.',
      [

         'id' =>
         $id,

         'nomor_surat' =>
         $existing['nomor_surat']

      ]
   );
}


/*
|--------------------------------------------------------------------------
| DELETE DATA
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

        DELETE FROM surat_berobat

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
         'Surat keterangan berobat berhasil dihapus.'
      );
   }


   responseJson(
      'error',
      'Data tidak ditemukan atau bukan milik fasilitas kesehatan ini.'
   );
}
