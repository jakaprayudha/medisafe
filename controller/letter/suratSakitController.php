<?php

date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| HELPER RESPONSE
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


/*
|--------------------------------------------------------------------------
| CUSTOMER
|--------------------------------------------------------------------------
|
| Sengaja string.
| id_customer surat_sakit TIDAK disimpan.
| Customer diambil melalui pasien_visit.
|
|--------------------------------------------------------------------------
*/

$id_customer =
   (string) $_SESSION['id_customer'];


/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/

$created_by =
   $_SESSION['id_user']
   ??
   $_SESSION['uid_user']
   ??
   $_SESSION['username']
   ??
   'system';


$updated_by =
   $_SESSION['id_user']
   ??
   $_SESSION['uid_user']
   ??
   $_SESSION['username']
   ??
   'system';


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


   case 'POST':

      createData();

      break;


   case 'GET':

      if (
         isset($_GET['id']) &&
         $_GET['id'] !== ''
      ) {

         getID(
            $_GET['id']
         );
      } else {

         getData();
      }

      break;


   case 'PUT':

      updateData();

      break;


   case 'DELETE':

      deleteData();

      break;


   default:

      responseJson(
         'error',
         'Method tidak diizinkan.'
      );
}


/*
|--------------------------------------------------------------------------
| CREATE
|--------------------------------------------------------------------------
*/

function createData()
{

   global $koneksi;
   global $id_customer;
   global $created_by;


   /*
    |--------------------------------------------------------------------------
    | DATA FORM
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


   $nomor_surat_manual =
      trim(
         $_POST['nomor_surat']
            ?? ''
      );


   $tanggal_surat =
      $_POST['tanggal_surat']
      ??
      date('Y-m-d');


   $tanggal_mulai =
      $_POST['tanggal_mulai']
      ?? '';


   $tanggal_selesai =
      $_POST['tanggal_selesai']
      ?? '';


   $keterangan =
      trim(
         $_POST['keterangan']
            ?? ''
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI PATIENT
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


   /*
    |--------------------------------------------------------------------------
    | VALIDASI VISIT
    |--------------------------------------------------------------------------
    */

   if (
      $id_visit === ''
   ) {

      responseJson(
         'error',
         'Visit pasien tidak ditemukan.'
      );
   }


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


   if (
      $tanggal_mulai === ''
   ) {

      responseJson(
         'error',
         'Tanggal mulai wajib diisi.'
      );
   }


   if (
      $tanggal_selesai === ''
   ) {

      responseJson(
         'error',
         'Tanggal selesai wajib diisi.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | VALIDASI RANGE
    |--------------------------------------------------------------------------
    */

   if (
      strtotime($tanggal_selesai)
      <
      strtotime($tanggal_mulai)
   ) {

      responseJson(
         'error',
         'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | HITUNG LAMA
    |--------------------------------------------------------------------------
    */

   $lama =
      (
         strtotime($tanggal_selesai)
         -
         strtotime($tanggal_mulai)
      )
      / 86400
      + 1;


   /*
    |--------------------------------------------------------------------------
    | VALIDASI VISIT
    |--------------------------------------------------------------------------
    |
    | Customer diambil dari pasien_visit.
    |
    |--------------------------------------------------------------------------
    */

   $checkVisit =
      $koneksi->prepare("

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

      responseJson(
         'error',
         'Prepare validasi visit gagal: '
            . $koneksi->error
      );
   }


   /*
    |--------------------------------------------------------------------------
    | SEMUA STRING
    |--------------------------------------------------------------------------
    */

   $checkVisit->bind_param(
      'sss',
      $id_visit,
      $id_patient,
      $id_customer
   );


   if (
      !$checkVisit->execute()
   ) {

      $error =
         $checkVisit->error;

      $checkVisit->close();

      responseJson(
         'error',
         'Validasi visit gagal: '
            . $error
      );
   }


   $visitResult =
      $checkVisit->get_result();


   if (
      $visitResult->num_rows === 0
   ) {

      $checkVisit->close();

      responseJson(
         'error',
         'Visit pasien tidak valid atau bukan milik fasilitas kesehatan ini.'
      );
   }


   $checkVisit->close();


   /*
    |--------------------------------------------------------------------------
    | AMBIL SETTING NOMOR SURAT
    |--------------------------------------------------------------------------
    */

   $setting =
      getSettingNomorSakit();


   /*
    |--------------------------------------------------------------------------
    | SETTING BELUM ADA
    |--------------------------------------------------------------------------
    */

   if (
      !$setting
   ) {

      responseJson(
         'setting_required',
         'Pengaturan nomor surat belum dibuat.',
         [
            'redirect' =>
            'module/letter/setting-surat'
         ]
      );
   }


   /*
    |--------------------------------------------------------------------------
    | MODE
    |--------------------------------------------------------------------------
    */

   $mode_nomor =
      strtoupper(
         trim(
            $setting['mode_nomor']
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
         'Mode nomor surat belum valid.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | NOMOR SURAT
    |--------------------------------------------------------------------------
    */

   if (
      $mode_nomor === 'MANUAL'
   ) {


      /*
        |--------------------------------------------------------------------------
        | NOMOR WAJIB DIISI
        |--------------------------------------------------------------------------
        */

      if (
         $nomor_surat_manual === ''
      ) {

         responseJson(
            'error',
            'Nomor surat wajib diisi karena mode penomoran adalah MANUAL.'
         );
      }


      /*
        |--------------------------------------------------------------------------
        | CEK NOMOR DUPLIKAT
        |--------------------------------------------------------------------------
        */

      if (
         nomorSuratExists(
            $nomor_surat_manual
         )
      ) {

         responseJson(
            'error',
            'Nomor surat tersebut sudah digunakan.'
         );
      }


      $nomor_surat =
         $nomor_surat_manual;
   } else {


      /*
        |--------------------------------------------------------------------------
        | AUTO
        |--------------------------------------------------------------------------
        */

      $nomor_surat =
         generateNomorSuratSakit(
            $setting
         );
   }


   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

   $query = "

        INSERT INTO surat_sakit
        (

            id_visit,
            id_patient,
            nomor_surat,
            tanggal_surat,
            tanggal_mulai,
            tanggal_selesai,
            lama,
            keterangan,
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
         'Prepare insert gagal: '
            . $koneksi->error
      );
   }


   $stmt->bind_param(
      'ssssssiss',
      $id_visit,
      $id_patient,
      $nomor_surat,
      $tanggal_surat,
      $tanggal_mulai,
      $tanggal_selesai,
      $lama,
      $keterangan,
      $created_by
   );


   if (
      $stmt->execute()
   ) {

      $newId =
         $stmt->insert_id;


      /*
        |--------------------------------------------------------------------------
        | UPDATE COUNTER AUTO
        |--------------------------------------------------------------------------
        */

      if (
         $mode_nomor === 'AUTO'
      ) {

         updateNomorSakit(
            $setting['id'],
            $setting['nomor_sakit'] ?? 0
         );
      }


      $stmt->close();


      responseJson(
         'success',
         'Surat keterangan sakit berhasil disimpan.',
         [
            'id' =>
            $newId,

            'nomor_surat' =>
            $nomor_surat,

            'lama' =>
            $lama,

            'mode_nomor' =>
            $mode_nomor
         ]
      );
   }


   $error =
      $stmt->error;


   $stmt->close();


   responseJson(
      'error',
      'Gagal menyimpan data: '
         . $error
   );
}


/*
|--------------------------------------------------------------------------
| GET ALL
|--------------------------------------------------------------------------
*/

function getData()
{

   global $koneksi;
   global $id_customer;


   $query = "

        SELECT

            ss.*,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_nik,
            mp.patient_datebirth,

            pv.id_doctor,
            pv.visit_ID,
            pv.visit_date,
            pv.id_customer

        FROM surat_sakit ss

        INNER JOIN pasien_visit pv

            ON pv.id_visit =
               ss.id_visit

            AND pv.id_customer =
               ?

        INNER JOIN ms_patient mp

            ON mp.id_patient =
               ss.id_patient

        ORDER BY
            ss.id DESC

    ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare query gagal: '
            . $koneksi->error
      );
   }


   $stmt->bind_param(
      's',
      $id_customer
   );


   if (
      !$stmt->execute()
   ) {

      $error =
         $stmt->error;

      $stmt->close();

      responseJson(
         'error',
         'Gagal mengambil data: '
            . $error
      );
   }


   $result =
      $stmt->get_result();


   $data =
      $result->fetch_all(
         MYSQLI_ASSOC
      );


   $stmt->close();


   responseJson(
      'success',
      'Data surat berhasil diambil.',
      $data
   );
}


/*
|--------------------------------------------------------------------------
| GET BY ID
|--------------------------------------------------------------------------
*/

function getID(
   $id
) {

   global $koneksi;
   global $id_customer;


   $query = "

        SELECT

            ss.*,

            mp.patient_name,
            mp.nomor_rm,
            mp.patient_nik,
            mp.patient_datebirth,

            pv.id_doctor,
            pv.visit_ID,
            pv.visit_date,
            pv.id_customer

        FROM surat_sakit ss

        INNER JOIN pasien_visit pv

            ON pv.id_visit =
               ss.id_visit

            AND pv.id_customer =
               ?

        INNER JOIN ms_patient mp

            ON mp.id_patient =
               ss.id_patient

        WHERE ss.id = ?

        LIMIT 1

    ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare query gagal: '
            . $koneksi->error
      );
   }


   $stmt->bind_param(
      'si',
      $id_customer,
      $id
   );


   if (
      !$stmt->execute()
   ) {

      $error =
         $stmt->error;

      $stmt->close();

      responseJson(
         'error',
         'Gagal mengambil data: '
            . $error
      );
   }


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
    | TAMBAHKAN INFO SETTING
    |--------------------------------------------------------------------------
    */

   $setting =
      getSettingNomorSakit();


   if ($setting) {

      $data['mode_nomor'] =
         $setting['mode_nomor'];

      $data['format_sakit'] =
         $setting['format_sakit'];

      $data['nomor_sakit'] =
         $setting['nomor_sakit'];
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
*/

function updateData()
{

   global $koneksi;
   global $id_customer;
   global $updated_by;


   /*
    |--------------------------------------------------------------------------
    | AMBIL PUT
    |--------------------------------------------------------------------------
    */

   parse_str(
      file_get_contents(
         'php://input'
      ),
      $put
   );


   $id =
      $put['id']
      ??
      $_GET['id']
      ??
      '';


   /*
    |--------------------------------------------------------------------------
    | VALIDASI ID
    |--------------------------------------------------------------------------
    */

   if (
      $id === ''
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
      $put['tanggal_surat']
      ?? '';


   $tanggal_mulai =
      $put['tanggal_mulai']
      ?? '';


   $tanggal_selesai =
      $put['tanggal_selesai']
      ?? '';


   $keterangan =
      trim(
         $put['keterangan']
            ?? ''
      );


   /*
    |--------------------------------------------------------------------------
    | NOMOR MANUAL
    |--------------------------------------------------------------------------
    */

   $nomor_surat_input =
      trim(
         $put['nomor_surat']
            ?? ''
      );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI TANGGAL
    |--------------------------------------------------------------------------
    */

   if (
      $tanggal_surat === ''
      ||
      $tanggal_mulai === ''
      ||
      $tanggal_selesai === ''
   ) {

      responseJson(
         'error',
         'Tanggal surat, mulai, dan selesai wajib diisi.'
      );
   }


   if (
      strtotime($tanggal_selesai)
      <
      strtotime($tanggal_mulai)
   ) {

      responseJson(
         'error',
         'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | HITUNG LAMA
    |--------------------------------------------------------------------------
    */

   $lama =
      (
         strtotime($tanggal_selesai)
         -
         strtotime($tanggal_mulai)
      )
      / 86400
      + 1;


   /*
    |--------------------------------------------------------------------------
    | GET SETTING
    |--------------------------------------------------------------------------
    */

   $setting =
      getSettingNomorSakit();


   if (
      !$setting
   ) {

      responseJson(
         'setting_required',
         'Pengaturan nomor surat belum dibuat.',
         [
            'redirect' =>
            'module/letter/setting-surat'
         ]
      );
   }


   $mode_nomor =
      strtoupper(
         trim(
            $setting['mode_nomor']
               ?? ''
         )
      );


   /*
    |--------------------------------------------------------------------------
    | NOMOR SURAT
    |--------------------------------------------------------------------------
    |
    | AUTO:
    | nomor surat tidak diubah.
    |
    | MANUAL:
    | user boleh mengganti nomor.
    |
    |--------------------------------------------------------------------------
    */

   if (
      $mode_nomor === 'MANUAL'
   ) {


      if (
         $nomor_surat_input === ''
      ) {

         responseJson(
            'error',
            'Nomor surat wajib diisi karena mode penomoran adalah MANUAL.'
         );
      }


      /*
        |--------------------------------------------------------------------------
        | CEK DUPLIKAT KECUALI ID SENDIRI
        |--------------------------------------------------------------------------
        */

      if (
         nomorSuratExists(
            $nomor_surat_input,
            $id
         )
      ) {

         responseJson(
            'error',
            'Nomor surat tersebut sudah digunakan.'
         );
      }


      $nomor_surat =
         $nomor_surat_input;


      /*
        |--------------------------------------------------------------------------
        | UPDATE DENGAN NOMOR
        |--------------------------------------------------------------------------
        */

      $query = "

            UPDATE surat_sakit ss

            INNER JOIN pasien_visit pv

                ON pv.id_visit =
                   ss.id_visit

            SET

                ss.nomor_surat = ?,
                ss.tanggal_surat = ?,
                ss.tanggal_mulai = ?,
                ss.tanggal_selesai = ?,
                ss.lama = ?,
                ss.keterangan = ?,
                ss.updated_at = NOW(),
                ss.updated_by = ?

            WHERE ss.id = ?

              AND pv.id_customer = ?

        ";


      $stmt =
         $koneksi->prepare(
            $query
         );


      if (!$stmt) {

         responseJson(
            'error',
            'Prepare update gagal: '
               . $koneksi->error
         );
      }


      $stmt->bind_param(
         'ssssissis',
         $nomor_surat,
         $tanggal_surat,
         $tanggal_mulai,
         $tanggal_selesai,
         $lama,
         $keterangan,
         $updated_by,
         $id,
         $id_customer
      );
   } else {


      /*
        |--------------------------------------------------------------------------
        | AUTO
        |--------------------------------------------------------------------------
        |
        | Nomor lama dipertahankan.
        |
        |--------------------------------------------------------------------------
        */

      $query = "

            UPDATE surat_sakit ss

            INNER JOIN pasien_visit pv

                ON pv.id_visit =
                   ss.id_visit

            SET

                ss.tanggal_surat = ?,
                ss.tanggal_mulai = ?,
                ss.tanggal_selesai = ?,
                ss.lama = ?,
                ss.keterangan = ?,
                ss.updated_at = NOW(),
                ss.updated_by = ?

            WHERE ss.id = ?

              AND pv.id_customer = ?

        ";


      $stmt =
         $koneksi->prepare(
            $query
         );


      if (!$stmt) {

         responseJson(
            'error',
            'Prepare update gagal: '
               . $koneksi->error
         );
      }


      $stmt->bind_param(
         'sssissis',
         $tanggal_surat,
         $tanggal_mulai,
         $tanggal_selesai,
         $lama,
         $keterangan,
         $updated_by,
         $id,
         $id_customer
      );
   }


   /*
    |--------------------------------------------------------------------------
    | EXECUTE
    |--------------------------------------------------------------------------
    */

   if (
      $stmt->execute()
   ) {

      $affected =
         $stmt->affected_rows;


      $stmt->close();


      responseJson(
         'success',
         'Surat keterangan sakit berhasil diperbarui.',
         [
            'id' =>
            $id,

            'mode_nomor' =>
            $mode_nomor,

            'affected_rows' =>
            $affected
         ]
      );
   }


   $error =
      $stmt->error;


   $stmt->close();


   responseJson(
      'error',
      'Gagal memperbarui surat: '
         . $error
   );
}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

function deleteData()
{

   global $koneksi;
   global $id_customer;


   $id =
      $_GET['id']
      ?? '';


   if (
      $id === ''
   ) {

      responseJson(
         'error',
         'ID surat tidak ditemukan.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | DELETE PER CUSTOMER
    |--------------------------------------------------------------------------
    */

   $query = "

        DELETE ss

        FROM surat_sakit ss

        INNER JOIN pasien_visit pv

            ON pv.id_visit =
               ss.id_visit

        WHERE ss.id = ?

          AND pv.id_customer = ?

    ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare delete gagal: '
            . $koneksi->error
      );
   }


   $stmt->bind_param(
      'is',
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

         responseJson(
            'success',
            'Surat keterangan sakit berhasil dihapus.'
         );
      }


      responseJson(
         'error',
         'Data tidak ditemukan atau tidak memiliki akses.'
      );
   }


   $error =
      $stmt->error;


   $stmt->close();


   responseJson(
      'error',
      'Gagal menghapus surat: '
         . $error
   );
}


/*
|--------------------------------------------------------------------------
| GET SETTING NOMOR SAKIT
|--------------------------------------------------------------------------
*/

function getSettingNomorSakit()
{

   global $koneksi;
   global $id_customer;


   $sql = "

        SELECT

            id,
            id_customer,
            mode_nomor,
            format_sakit,
            nomor_sakit

        FROM setting_surat

        WHERE id_customer = ?

        LIMIT 1

    ";


   $stmt =
      $koneksi->prepare(
         $sql
      );


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare setting nomor surat gagal: '
            . $koneksi->error
      );
   }


   /*
    |--------------------------------------------------------------------------
    | CUSTOMER STRING
    |--------------------------------------------------------------------------
    */

   $stmt->bind_param(
      's',
      $id_customer
   );


   if (
      !$stmt->execute()
   ) {

      $error =
         $stmt->error;

      $stmt->close();

      responseJson(
         'error',
         'Gagal mengambil setting nomor surat: '
            . $error
      );
   }


   $result =
      $stmt->get_result();


   $setting =
      $result->fetch_assoc();


   $stmt->close();


   return $setting ?: null;
}


/*
|--------------------------------------------------------------------------
| CEK NOMOR SURAT SUDAH ADA
|--------------------------------------------------------------------------
*/

function nomorSuratExists(
   string $nomor_surat,
   $excludeId = null
) {

   global $koneksi;
   global $id_customer;


   /*
    |--------------------------------------------------------------------------
    | DENGAN EXCLUDE ID
    |--------------------------------------------------------------------------
    */

   if (
      $excludeId !== null &&
      $excludeId !== ''
   ) {


      $sql = "

            SELECT
                ss.id

            FROM surat_sakit ss

            INNER JOIN pasien_visit pv

                ON pv.id_visit =
                   ss.id_visit

            WHERE pv.id_customer = ?

              AND ss.nomor_surat = ?

              AND ss.id <> ?

            LIMIT 1

        ";


      $stmt =
         $koneksi->prepare(
            $sql
         );


      if (!$stmt) {

         responseJson(
            'error',
            'Prepare pengecekan nomor gagal: '
               . $koneksi->error
         );
      }


      $stmt->bind_param(
         'ssi',
         $id_customer,
         $nomor_surat,
         $excludeId
      );
   } else {


      /*
        |--------------------------------------------------------------------------
        | TANPA EXCLUDE
        |--------------------------------------------------------------------------
        */

      $sql = "

            SELECT
                ss.id

            FROM surat_sakit ss

            INNER JOIN pasien_visit pv

                ON pv.id_visit =
                   ss.id_visit

            WHERE pv.id_customer = ?

              AND ss.nomor_surat = ?

            LIMIT 1

        ";


      $stmt =
         $koneksi->prepare(
            $sql
         );


      if (!$stmt) {

         responseJson(
            'error',
            'Prepare pengecekan nomor gagal: '
               . $koneksi->error
         );
      }


      $stmt->bind_param(
         'ss',
         $id_customer,
         $nomor_surat
      );
   }


   if (
      !$stmt->execute()
   ) {

      $error =
         $stmt->error;

      $stmt->close();

      responseJson(
         'error',
         'Gagal mengecek nomor surat: '
            . $error
      );
   }


   $result =
      $stmt->get_result();


   $exists =
      $result->num_rows > 0;


   $stmt->close();


   return $exists;
}


/*
|--------------------------------------------------------------------------
| GENERATE NOMOR SURAT SAKIT
|--------------------------------------------------------------------------
*/

function generateNomorSuratSakit(
   array $setting
) {

   global $koneksi;
   global $id_customer;


   /*
    |--------------------------------------------------------------------------
    | FORMAT
    |--------------------------------------------------------------------------
    */

   $format =
      trim(
         $setting['format_sakit']
            ??
            'SKS/{NO}/{MM}/{YYYY}'
      );


   /*
    |--------------------------------------------------------------------------
    | NOMOR TERAKHIR DARI MASTER
    |--------------------------------------------------------------------------
    */

   $nomorTerakhir =
      (int) (
         $setting['nomor_sakit']
         ?? 0
      );


   /*
    |--------------------------------------------------------------------------
    | NEXT NUMBER
    |--------------------------------------------------------------------------
    */

   $nextNumber =
      $nomorTerakhir + 1;


   /*
    |--------------------------------------------------------------------------
    | PADDING
    |--------------------------------------------------------------------------
    |
    | Setting UI menggunakan 3 digit.
    |
    |--------------------------------------------------------------------------
    */

   $nomor =
      str_pad(
         $nextNumber,
         3,
         '0',
         STR_PAD_LEFT
      );


   /*
    |--------------------------------------------------------------------------
    | DATE
    |--------------------------------------------------------------------------
    */

   $now =
      new DateTime(
         'now',
         new DateTimeZone(
            'Asia/Jakarta'
         )
      );


   $yyyy =
      $now->format('Y');


   $yy =
      $now->format('y');


   $mm =
      $now->format('m');


   $dd =
      $now->format('d');


   /*
    |--------------------------------------------------------------------------
    | REPLACE FORMAT
    |--------------------------------------------------------------------------
    */

   $result =
      $format;


   $result =
      preg_replace(
         '/\{NO\}/i',
         $nomor,
         $result
      );


   $result =
      preg_replace(
         '/\{YYYY\}/i',
         $yyyy,
         $result
      );


   $result =
      preg_replace(
         '/\{YY\}/i',
         $yy,
         $result
      );


   $result =
      preg_replace(
         '/\{MM\}/i',
         $mm,
         $result
      );


   $result =
      preg_replace(
         '/\{DD\}/i',
         $dd,
         $result
      );


   /*
    |--------------------------------------------------------------------------
    | CEK DUPLIKAT
    |--------------------------------------------------------------------------
    */

   $counter =
      0;


   $originalNumber =
      $nextNumber;


   while (
      nomorSuratExists(
         $result
      )
   ) {


      $counter++;


      /*
        |--------------------------------------------------------------------------
        | SAFETY
        |--------------------------------------------------------------------------
        */

      if (
         $counter > 1000
      ) {

         responseJson(
            'error',
            'Gagal membuat nomor surat otomatis karena nomor terlalu banyak duplikat.'
         );
      }


      $nextNumber =
         $originalNumber +
         $counter;


      $nomor =
         str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
         );


      $result =
         $format;


      $result =
         preg_replace(
            '/\{NO\}/i',
            $nomor,
            $result
         );


      $result =
         preg_replace(
            '/\{YYYY\}/i',
            $yyyy,
            $result
         );


      $result =
         preg_replace(
            '/\{YY\}/i',
            $yy,
            $result
         );


      $result =
         preg_replace(
            '/\{MM\}/i',
            $mm,
            $result
         );


      $result =
         preg_replace(
            '/\{DD\}/i',
            $dd,
            $result
         );
   }


   return $result;
}


/*
|--------------------------------------------------------------------------
| UPDATE NOMOR MASTER
|--------------------------------------------------------------------------
*/

function updateNomorSakit(
   $settingId,
   $nomorLama
) {

   global $koneksi;


   /*
    |--------------------------------------------------------------------------
    | NEXT
    |--------------------------------------------------------------------------
    */

   $nomorBaru =
      (
         (int)
         $nomorLama
      )
      + 1;


   /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

   $sql = "

        UPDATE setting_surat

        SET

            nomor_sakit = ?,
            updated_at = NOW()

        WHERE id = ?

    ";


   $stmt =
      $koneksi->prepare(
         $sql
      );


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare update nomor master gagal: '
            . $koneksi->error
      );
   }


   $stmt->bind_param(
      'ii',
      $nomorBaru,
      $settingId
   );


   if (
      !$stmt->execute()
   ) {

      $error =
         $stmt->error;

      $stmt->close();

      responseJson(
         'error',
         'Gagal memperbarui nomor terakhir: '
            . $error
      );
   }


   $stmt->close();
}
