<?php

session_start();

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| SESSION
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
| JENIS SURAT
|--------------------------------------------------------------------------
*/

const JENIS_SURAT = 'sehat';


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

      responseJson(
         'error',
         'Method tidak diizinkan.'
      );

      break;
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

      'status'  => $status,
      'message' => $message

   ];


   if ($data !== null) {

      $response['data'] =
         $data;
   }


   echo json_encode(
      $response,
      JSON_UNESCAPED_UNICODE
   );

   exit;
}


/*
|--------------------------------------------------------------------------
| GET SETTING NOMOR SURAT
|--------------------------------------------------------------------------
|
| Mengambil setting khusus customer.
|
| Surat Sehat:
|
| format_sehat
| nomor_sehat
|
|--------------------------------------------------------------------------
*/

function getSettingSurat(
   $id_customer
) {

   global $koneksi;


   $sql = "
      SELECT
         id,
         id_customer,
         mode_nomor,
         format_sehat,
         nomor_sehat
      FROM setting_surat
      WHERE id_customer = ?
      LIMIT 1
   ";


   $stmt =
      $koneksi->prepare($sql);


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare setting surat gagal: ' .
            $koneksi->error
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
         'Gagal mengambil setting surat: ' .
            $error
      );
   }


   $result =
      $stmt->get_result();


   $data =
      $result->fetch_assoc();


   $stmt->close();


   return $data;
}


/*
|--------------------------------------------------------------------------
| GENERATE NOMOR DARI FORMAT
|--------------------------------------------------------------------------
|
| Placeholder yang didukung:
|
| {NO}
| {DD}
| {MM}
| {YYYY}
|
| Contoh:
|
| SKH/{NO}/{MM}/{YYYY}
|
| menjadi:
|
| SKH/001/08/2026
|
|--------------------------------------------------------------------------
*/

function generateNomorFromFormat(
   $format,
   $nomor,
   $tanggal
) {

   $timestamp =
      strtotime($tanggal);


   $no =
      str_pad(
         (int) $nomor,
         4,
         '0',
         STR_PAD_LEFT
      );


   $replace = [

      '{NO}' =>
      $no,

      '{DD}' =>
      date(
         'd',
         $timestamp
      ),

      '{MM}' =>
      date(
         'm',
         $timestamp
      ),

      '{YYYY}' =>
      date(
         'Y',
         $timestamp
      )

   ];


   return strtr(
      $format,
      $replace
   );
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


   $sql = "
      SELECT
         pv.id_visit,
         pv.id_patient,
         pv.id_customer
      FROM pasien_visit pv
      WHERE pv.id_visit = ?
        AND pv.id_patient = ?
        AND pv.id_customer = ?
      LIMIT 1
   ";


   $stmt =
      $koneksi->prepare($sql);


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare validasi visit gagal: ' .
            $koneksi->error
      );
   }


   $stmt->bind_param(
      'sss',
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
   | DATA POST
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


   $nomor_surat =
      trim(
         $_POST['nomor_surat']
            ?? ''
      );


   $tanggal_surat =
      trim(
         $_POST['tanggal_surat']
            ?? date('Y-m-d')
      );


   $tekanan_darah =
      trim(
         $_POST['tekanan_darah']
            ?? ''
      );


   $nadi =
      trim(
         $_POST['nadi']
            ?? ''
      );


   $berat_badan =
      trim(
         $_POST['berat_badan']
            ?? ''
      );


   $tinggi_badan =
      trim(
         $_POST['tinggi_badan']
            ?? ''
      );


   $keperluan =
      trim(
         $_POST['keperluan']
            ?? ''
      );


   $keterangan =
      trim(
         $_POST['keterangan']
            ?? ''
      );


   /*
   |--------------------------------------------------------------------------
   | VALIDASI DASAR
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
   | AMBIL SETTING
   |--------------------------------------------------------------------------
   */

   $setting =
      getSettingSurat(
         $id_customer
      );


   /*
   |--------------------------------------------------------------------------
   | SETTING BELUM ADA
   |--------------------------------------------------------------------------
   */

   if (
      !$setting
   ) {

      responseJson(
         'error',
         'Setting nomor surat belum dibuat untuk fasilitas kesehatan ini.'
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
   | TRANSACTION
   |--------------------------------------------------------------------------
   */

   $koneksi->begin_transaction();


   try {


      /*
      |--------------------------------------------------------------------------
      | MANUAL
      |--------------------------------------------------------------------------
      */

      if (
         $mode_nomor === 'MANUAL'
      ) {


         if (
            $nomor_surat === ''
         ) {

            throw new Exception(
               'Nomor surat wajib diisi karena mode penomoran adalah MANUAL.'
            );
         }


         /*
         |--------------------------------------------------------------------------
         | CEK DUPLIKAT NOMOR
         |--------------------------------------------------------------------------
         */

         $checkNomor =
            $koneksi->prepare("
               SELECT id
               FROM surat_sehat
               WHERE id_customer = ?
                 AND nomor_surat = ?
               LIMIT 1
            ");


         if (!$checkNomor) {

            throw new Exception(
               'Prepare pengecekan nomor surat gagal: ' .
                  $koneksi->error
            );
         }


         $checkNomor->bind_param(
            'ss',
            $id_customer,
            $nomor_surat
         );


         $checkNomor->execute();


         $resultCheck =
            $checkNomor->get_result();


         if (
            $resultCheck->num_rows > 0
         ) {

            $checkNomor->close();

            throw new Exception(
               'Nomor surat sudah digunakan.'
            );
         }


         $checkNomor->close();
      }


      /*
      |--------------------------------------------------------------------------
      | AUTO
      |--------------------------------------------------------------------------
      */ elseif (
         $mode_nomor === 'AUTO'
      ) {


         $format =
            trim(
               $setting['format_sehat']
                  ?? ''
            );


         if (
            $format === ''
         ) {

            throw new Exception(
               'Format nomor Surat Keterangan Sehat belum diatur.'
            );
         }


         /*
         |--------------------------------------------------------------------------
         | LOCK SETTING
         |--------------------------------------------------------------------------
         |
         | Ambil nomor_sehat terbaru.
         |
         */

         $stmtSetting =
            $koneksi->prepare("
               SELECT
                  nomor_sehat,
                  format_sehat
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
            's',
            $id_customer
         );


         $stmtSetting->execute();


         $resultSetting =
            $stmtSetting->get_result();


         $settingLocked =
            $resultSetting->fetch_assoc();


         $stmtSetting->close();


         if (
            !$settingLocked
         ) {

            throw new Exception(
               'Setting nomor surat tidak ditemukan.'
            );
         }


         /*
         |--------------------------------------------------------------------------
         | NOMOR TERAKHIR
         |--------------------------------------------------------------------------
         */

         $nomorTerakhir =
            (int) (
               $settingLocked['nomor_sehat']
               ?? 0
            );


         /*
         |--------------------------------------------------------------------------
         | NOMOR BERIKUTNYA
         |--------------------------------------------------------------------------
         */

         $nomorBerikutnya =
            $nomorTerakhir + 1;


         /*
         |--------------------------------------------------------------------------
         | FORMAT
         |--------------------------------------------------------------------------
         */

         $format =
            trim(
               $settingLocked['format_sehat']
                  ?? ''
            );


         /*
         |--------------------------------------------------------------------------
         | GENERATE
         |--------------------------------------------------------------------------
         */

         $nomor_surat =
            generateNomorFromFormat(
               $format,
               $nomorBerikutnya,
               $tanggal_surat
            );


         /*
         |--------------------------------------------------------------------------
         | UPDATE NOMOR TERTINGGI
         |--------------------------------------------------------------------------
         */

         $updateSetting =
            $koneksi->prepare("
               UPDATE setting_surat
               SET
                  nomor_sehat = ?,
                  updated_at = NOW(),
                  updated_by = ?
               WHERE id_customer = ?
            ");


         if (!$updateSetting) {

            throw new Exception(
               'Prepare update nomor setting gagal: ' .
                  $koneksi->error
            );
         }


         $updated_by =
            $_SESSION['id_user']
            ?? $_SESSION['uid_user']
            ?? $_SESSION['username']
            ?? 'system';


         $updateSetting->bind_param(
            'iss',
            $nomorBerikutnya,
            $updated_by,
            $id_customer
         );


         if (
            !$updateSetting->execute()
         ) {

            $error =
               $updateSetting->error;

            $updateSetting->close();

            throw new Exception(
               'Gagal memperbarui nomor tertinggi: ' .
                  $error
            );
         }


         $updateSetting->close();
      }


      /*
      |--------------------------------------------------------------------------
      | MODE TIDAK VALID
      |--------------------------------------------------------------------------
      */ else {

         throw new Exception(
            'Mode penomoran tidak valid. Gunakan AUTO atau MANUAL.'
         );
      }


      /*
      |--------------------------------------------------------------------------
      | CREATED BY
      |--------------------------------------------------------------------------
      */

      $created_by =
         $_SESSION['id_user']
         ?? $_SESSION['fullname']
         ?? $_SESSION['username']
         ?? 'system';


      /*
      |--------------------------------------------------------------------------
      | INSERT
      |--------------------------------------------------------------------------
      */

      $sql = "
         INSERT INTO surat_sehat (

            id_customer,
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

            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?

         )
      ";


      $stmt =
         $koneksi->prepare($sql);


      if (!$stmt) {

         throw new Exception(
            'Prepare insert gagal: ' .
               $koneksi->error
         );
      }


      $stmt->bind_param(
         'ssssssssssss',

         $id_customer,
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


      if (
         !$stmt->execute()
      ) {

         $error =
            $stmt->error;

         $stmt->close();

         throw new Exception(
            'Gagal menyimpan surat: ' .
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


      responseJson(
         'success',
         'Surat Keterangan Sehat berhasil disimpan.',
         [
            'id' =>
            $newId,

            'id_customer' =>
            $id_customer,

            'nomor_surat' =>
            $nomor_surat,

            'mode_nomor' =>
            $mode_nomor
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
         $e->getMessage()
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


   $sql = "
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
         ON pv.id_visit =
            ss.id_visit

         AND pv.id_customer =
            ss.id_customer

      INNER JOIN ms_patient mp
         ON mp.id_patient =
            ss.id_patient

      WHERE ss.id_customer = ?

      ORDER BY
         ss.id DESC
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
   $id,
   $id_customer
) {

   global $koneksi;


   $sql = "
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
         ON pv.id_visit =
            ss.id_visit

         AND pv.id_customer =
            ss.id_customer

      INNER JOIN ms_patient mp
         ON mp.id_patient =
            ss.id_patient

      WHERE ss.id = ?
        AND ss.id_customer = ?

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
      'ss',
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
         'Data surat tidak ditemukan atau bukan milik fasilitas kesehatan ini.'
      );
   }


   $data =
      $result->fetch_assoc();


   $stmt->close();


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

function updateData(
   $id_customer
) {

   global $koneksi;


   /*
   |--------------------------------------------------------------------------
   | BACA PUT
   |--------------------------------------------------------------------------
   */

   parse_str(
      file_get_contents(
         'php://input'
      ),
      $put
   );


   $id =
      trim(
         $put['id']
            ?? $_GET['id']
            ?? ''
      );


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

   $id_patient =
      trim(
         $put['id_patient']
            ?? ''
      );


   $id_visit =
      trim(
         $put['id_visit']
            ?? ''
      );


   $nomor_surat =
      trim(
         $put['nomor_surat']
            ?? ''
      );


   $tanggal_surat =
      trim(
         $put['tanggal_surat']
            ?? ''
      );


   $tekanan_darah =
      trim(
         $put['tekanan_darah']
            ?? ''
      );


   $nadi =
      trim(
         $put['nadi']
            ?? ''
      );


   $berat_badan =
      trim(
         $put['berat_badan']
            ?? ''
      );


   $tinggi_badan =
      trim(
         $put['tinggi_badan']
            ?? ''
      );


   $keperluan =
      trim(
         $put['keperluan']
            ?? ''
      );


   $keterangan =
      trim(
         $put['keterangan']
            ?? ''
      );


   /*
   |--------------------------------------------------------------------------
   | VALIDASI DASAR
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
   | CHECK DATA SURAT
   |--------------------------------------------------------------------------
   */

   $check =
      $koneksi->prepare("
         SELECT
            id,
            nomor_surat
         FROM surat_sehat
         WHERE id = ?
           AND id_customer = ?
         LIMIT 1
      ");


   if (!$check) {

      responseJson(
         'error',
         'Prepare pengecekan surat gagal: ' .
            $koneksi->error
      );
   }


   $check->bind_param(
      'ss',
      $id,
      $id_customer
   );


   $check->execute();


   $resultCheck =
      $check->get_result();


   $existing =
      $resultCheck->fetch_assoc();


   $check->close();


   if (
      !$existing
   ) {

      responseJson(
         'error',
         'Data surat tidak ditemukan atau bukan milik fasilitas kesehatan ini.'
      );
   }


   /*
   |--------------------------------------------------------------------------
   | SETTING
   |--------------------------------------------------------------------------
   */

   $setting =
      getSettingSurat(
         $id_customer
      );


   if (
      !$setting
   ) {

      responseJson(
         'error',
         'Setting nomor surat belum dibuat.'
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
   | NOMOR UPDATE
   |--------------------------------------------------------------------------
   |
   | MANUAL:
   | nomor boleh diubah.
   |
   | AUTO:
   | nomor lama dipertahankan.
   |
   |--------------------------------------------------------------------------
   */

   if (
      $mode_nomor === 'MANUAL'
   ) {


      if (
         $nomor_surat === ''
      ) {

         responseJson(
            'error',
            'Nomor surat wajib diisi.'
         );
      }


      /*
      |--------------------------------------------------------------------------
      | CEK NOMOR DUPLIKAT
      |--------------------------------------------------------------------------
      */

      $checkNomor =
         $koneksi->prepare("
            SELECT id
            FROM surat_sehat
            WHERE id_customer = ?
              AND nomor_surat = ?
              AND id <> ?
            LIMIT 1
         ");


      if (!$checkNomor) {

         responseJson(
            'error',
            'Prepare pengecekan nomor gagal: ' .
               $koneksi->error
         );
      }


      $checkNomor->bind_param(
         'sss',
         $id_customer,
         $nomor_surat,
         $id
      );


      $checkNomor->execute();


      $resultNomor =
         $checkNomor->get_result();


      if (
         $resultNomor->num_rows > 0
      ) {

         $checkNomor->close();

         responseJson(
            'error',
            'Nomor surat sudah digunakan.'
         );
      }


      $checkNomor->close();
   } elseif (
      $mode_nomor === 'AUTO'
   ) {

      /*
      |--------------------------------------------------------------------------
      | AUTO
      |--------------------------------------------------------------------------
      |
      | Jangan ambil nomor dari browser.
      | Pertahankan nomor yang sudah tersimpan.
      |
      */

      $nomor_surat =
         $existing['nomor_surat'];
   } else {

      responseJson(
         'error',
         'Mode penomoran tidak valid.'
      );
   }


   /*
   |--------------------------------------------------------------------------
   | UPDATED BY
   |--------------------------------------------------------------------------
   */

   $updated_by =
      $_SESSION['id_user']
      ?? $_SESSION['fullname']
      ?? $_SESSION['username']
      ?? 'system';


   /*
   |--------------------------------------------------------------------------
   | UPDATE
   |--------------------------------------------------------------------------
   */

   $sql = "
      UPDATE surat_sehat

      SET

         id_patient = ?,
         id_visit = ?,
         nomor_surat = ?,
         tanggal_surat = ?,
         tekanan_darah = ?,
         nadi = ?,
         berat_badan = ?,
         tinggi_badan = ?,
         keperluan = ?,
         keterangan = ?,
         updated_at = NOW(),
         updated_by = ?

      WHERE id = ?
        AND id_customer = ?
   ";


   $stmt =
      $koneksi->prepare($sql);


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare update gagal: ' .
            $koneksi->error
      );
   }


   $stmt->bind_param(
      'sssssssssssss',

      $id_patient,
      $id_visit,
      $nomor_surat,
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


   if (
      !$stmt->execute()
   ) {

      $error =
         $stmt->error;

      $stmt->close();

      responseJson(
         'error',
         'Gagal memperbarui surat: ' .
            $error
      );
   }


   $stmt->close();


   responseJson(
      'success',
      'Surat Keterangan Sehat berhasil diperbarui.',
      [
         'id' =>
         $id,

         'nomor_surat' =>
         $nomor_surat,

         'mode_nomor' =>
         $mode_nomor
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
      trim(
         $_GET['id']
            ?? ''
      );


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
   | DELETE
   |--------------------------------------------------------------------------
   */

   $sql = "
      DELETE FROM surat_sehat

      WHERE id = ?
        AND id_customer = ?
   ";


   $stmt =
      $koneksi->prepare($sql);


   if (!$stmt) {

      responseJson(
         'error',
         'Prepare delete gagal: ' .
            $koneksi->error
      );
   }


   $stmt->bind_param(
      'ss',
      $id,
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
         'Gagal menghapus surat: ' .
            $error
      );
   }


   $affected =
      $stmt->affected_rows;


   $stmt->close();


   if (
      $affected <= 0
   ) {

      responseJson(
         'error',
         'Data tidak ditemukan atau bukan milik fasilitas kesehatan ini.'
      );
   }


   responseJson(
      'success',
      'Surat Keterangan Sehat berhasil dihapus.'
   );
}
