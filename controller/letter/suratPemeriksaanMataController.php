<?php

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

include '../../database/connect.php';

header('Content-Type: application/json');


/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id_customer']) || $_SESSION['id_customer'] === '') {

   echo json_encode([
      'status' => 'error',
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

      if (
         isset($_GET['check_setting']) &&
         $_GET['check_setting'] == '1'
      ) {

         checkSettingNomorSurat(
            $id_customer
         );

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

      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);

      break;
}


/*
|--------------------------------------------------------------------------
| FIELD YANG BOLEH DISIMPAN
|--------------------------------------------------------------------------
*/

function allowedFields()
{
   return [

      'id_visit',
      'id_patient',

      'nomor_surat',
      'tanggal_surat',

      'tekanan_darah',
      'nadi',
      'suhu',
      'respirasi',

      'gula_darah_sewaktu',
      'kolesterol_total',
      'asam_urat',
      'hemoglobin',

      'gula_darah_keterangan',
      'kolesterol_keterangan',
      'asam_urat_keterangan',
      'hemoglobin_keterangan',

      'visus_od_tanpa_koreksi_jauh',
      'visus_od_tanpa_koreksi_dekat',
      'visus_od_dengan_koreksi_jauh',
      'visus_od_dengan_koreksi_dekat',

      'visus_os_tanpa_koreksi_jauh',
      'visus_os_tanpa_koreksi_dekat',
      'visus_os_dengan_koreksi_jauh',
      'visus_os_dengan_koreksi_dekat',

      'refraksi_od_sph',
      'refraksi_od_cyl',
      'refraksi_od_axis',
      'refraksi_od_add',

      'refraksi_os_sph',
      'refraksi_os_cyl',
      'refraksi_os_axis',
      'refraksi_os_add',

      'pd',

      'tio_od',
      'tio_os',

      'segmen_anterior_od',
      'segmen_anterior_os',

      'segmen_posterior_od',
      'segmen_posterior_os',

      'kesimpulan',

      'rekomendasi'
   ];
}


/*
|--------------------------------------------------------------------------
| CHECK SETTING NOMOR SURAT
|--------------------------------------------------------------------------
|
| setting_surat:
|
| id_customer
| mode_nomor
| format_mata
| nomor_mata
|
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
         format_mata,
         nomor_mata

      FROM setting_surat

      WHERE id_customer = ?

      LIMIT 1

   ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      return [
         'status' => 'error',
         'message' => 'Prepare setting surat gagal: ' . $koneksi->error
      ];
   }


   $stmt->bind_param(
      "s",
      $id_customer
   );


   $stmt->execute();


   $result =
      $stmt->get_result();


   if ($result->num_rows === 0) {

      $stmt->close();

      return [
         'status' => 'setting_required',
         'message' => 'Setting nomor surat belum dibuat.'
      ];
   }


   $setting =
      $result->fetch_assoc();


   $stmt->close();


   return [
      'status' => 'success',
      'data' => $setting
   ];
}


/*
|--------------------------------------------------------------------------
| CHECK SETTING UNTUK JAVASCRIPT
|--------------------------------------------------------------------------
*/

function checkSettingNomorSurat($id_customer)
{
   $result =
      getSettingNomorSurat(
         $id_customer
      );


   echo json_encode(
      $result
   );
}


/*
|--------------------------------------------------------------------------
| BULAN ROMAWI
|--------------------------------------------------------------------------
*/

function getBulanRomawi($month)
{
   $bulan = [

      1  => 'I',
      2  => 'II',
      3  => 'III',
      4  => 'IV',
      5  => 'V',
      6  => 'VI',
      7  => 'VII',
      8  => 'VIII',
      9  => 'IX',
      10 => 'X',
      11 => 'XI',
      12 => 'XII'

   ];


   return $bulan[$month] ?? '';
}


/*
|--------------------------------------------------------------------------
| GENERATE NOMOR DARI FORMAT SETTING
|--------------------------------------------------------------------------
|
| Contoh format:
|
| SPM/{NO}/{MM}/{YYYY}
|
| hasil:
|
| SPM/001/08/2026
|
|--------------------------------------------------------------------------
*/

function generateNomorDariFormat(
   $format,
   $nomor
) {

   $tanggal =
      date('Y-m-d');


   $year =
      date('Y', strtotime($tanggal));


   $month =
      date('m', strtotime($tanggal));


   $monthRomawi =
      getBulanRomawi(
         (int) date(
            'n',
            strtotime($tanggal)
         )
      );


   /*
   |--------------------------------------------------------------------------
   | NOMOR 3 DIGIT
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
   | REPLACE FORMAT
   |--------------------------------------------------------------------------
   */

   $hasil =
      str_replace(
         [
            '{NO}',
            '{MM}',
            '{YYYY}',
            '{ROMAN}'
         ],
         [
            $nomorFormatted,
            $month,
            $year,
            $monthRomawi
         ],
         $format
      );


   return $hasil;
}


/*
|--------------------------------------------------------------------------
| VALIDASI VISIT
|--------------------------------------------------------------------------
*/

function validateVisit(
   $id_customer,
   $id_patient,
   $id_visit
) {

   global $koneksi;


   $query = "

      SELECT

         id_visit,
         id_patient,
         id_customer

      FROM pasien_visit

      WHERE id_visit = ?

      AND id_patient = ?

      AND id_customer = ?

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

function createData($id_customer)
{
   global $koneksi;


   /*
   |--------------------------------------------------------------------------
   | POST
   |--------------------------------------------------------------------------
   */

   if (empty($_POST)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);

      return;
   }


   /*
   |--------------------------------------------------------------------------
   | PATIENT
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


   if (
      $id_patient === '' ||
      $id_visit === ''
   ) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Pasien dan kunjungan wajib dipilih.'
      ]);

      return;
   }


   /*
   |--------------------------------------------------------------------------
   | VALIDASI VISIT
   |--------------------------------------------------------------------------
   */

   if (
      !validateVisit(
         $id_customer,
         $id_patient,
         $id_visit
      )
   ) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Kunjungan pasien tidak valid.'
      ]);

      return;
   }


   /*
   |--------------------------------------------------------------------------
   | CEK SETTING
   |--------------------------------------------------------------------------
   */

   $settingResult =
      getSettingNomorSurat(
         $id_customer
      );


   if (
      $settingResult['status'] !==
      'success'
   ) {

      echo json_encode(
         $settingResult
      );

      return;
   }


   $setting =
      $settingResult['data'];


   $mode =
      strtoupper(
         trim(
            $setting['mode_nomor'] ?? ''
         )
      );


   /*
   |--------------------------------------------------------------------------
   | MODE HARUS AUTO / MANUAL
   |--------------------------------------------------------------------------
   */

   if (
      $mode !== 'AUTO' &&
      $mode !== 'MANUAL'
   ) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Mode nomor surat belum valid.'
      ]);

      return;
   }


   /*
   |--------------------------------------------------------------------------
   | TANGGAL SURAT
   |--------------------------------------------------------------------------
   */

   $tanggal_surat =
      trim(
         $_POST['tanggal_surat'] ??
            date('Y-m-d')
      );


   /*
   |--------------------------------------------------------------------------
   | USER
   |--------------------------------------------------------------------------
   */

   $created_by =
      $_SESSION['uid_user']
      ?? $_SESSION['username']
      ?? 'system';


   /*
   |--------------------------------------------------------------------------
   | NOMOR SURAT
   |--------------------------------------------------------------------------
   */

   $nomor_surat = '';


   /*
   |--------------------------------------------------------------------------
   | ============================================================
   | AUTO
   | ============================================================
   |--------------------------------------------------------------------------
   */

   if ($mode === 'AUTO') {


      /*
      |--------------------------------------------------------------------------
      | TRANSACTION
      |--------------------------------------------------------------------------
      |
      | Supaya nomor_mata aman ketika ada dua user
      | melakukan input bersamaan.
      |--------------------------------------------------------------------------
      */

      $koneksi->begin_transaction();


      try {


         /*
         |--------------------------------------------------------------------------
         | LOCK SETTING
         |--------------------------------------------------------------------------
         */

         $querySetting = "

            SELECT

               id,
               mode_nomor,
               format_mata,
               nomor_mata

            FROM setting_surat

            WHERE id_customer = ?

            LIMIT 1

            FOR UPDATE

         ";


         $stmtSetting =
            $koneksi->prepare(
               $querySetting
            );


         if (!$stmtSetting) {

            throw new Exception(
               'Gagal membaca setting nomor surat: ' .
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
               'Setting nomor surat belum dibuat.'
            );
         }


         $setting =
            $resultSetting->fetch_assoc();


         $stmtSetting->close();


         /*
         |--------------------------------------------------------------------------
         | PASTIKAN MODE MASIH AUTO
         |--------------------------------------------------------------------------
         */

         if (
            strtoupper(
               trim(
                  $setting['mode_nomor']
               )
            ) !== 'AUTO'
         ) {

            throw new Exception(
               'Mode penomoran telah berubah. Silakan gunakan mode yang sesuai.'
            );
         }


         /*
         |--------------------------------------------------------------------------
         | FORMAT
         |--------------------------------------------------------------------------
         */

         $format =
            trim(
               $setting['format_mata'] ??
                  ''
            );


         if ($format === '') {

            throw new Exception(
               'Format nomor surat pemeriksaan mata belum diatur.'
            );
         }


         /*
         |--------------------------------------------------------------------------
         | NOMOR BERIKUTNYA
         |--------------------------------------------------------------------------
         */

         $nomorBerikutnya =
            ((int) (
               $setting['nomor_mata']
               ?? 0
            )) + 1;


         /*
         |--------------------------------------------------------------------------
         | GENERATE
         |--------------------------------------------------------------------------
         */

         $nomor_surat =
            generateNomorDariFormat(
               $format,
               $nomorBerikutnya
            );


         /*
         |--------------------------------------------------------------------------
         | INSERT DATA
         |--------------------------------------------------------------------------
         */

         $fields = [];

         $values = [];

         $types = '';


         foreach (
            allowedFields()
            as $field
         ) {

            /*
            |--------------------------------------------------------------------------
            | NOMOR DIBUAT CONTROLLER
            |--------------------------------------------------------------------------
            */

            if (
               $field === 'nomor_surat'
            ) {

               continue;
            }


            if (
               isset(
                  $_POST[$field]
               )
            ) {

               $fields[] =
                  $field;


               $values[] =
                  trim(
                     $_POST[$field]
                  );


               $types .= 's';
            }
         }


         /*
         |--------------------------------------------------------------------------
         | ID CUSTOMER
         |--------------------------------------------------------------------------
         */

         $fields[] =
            'id_customer';


         $values[] =
            $id_customer;


         $types .= 's';


         /*
         |--------------------------------------------------------------------------
         | NOMOR SURAT
         |--------------------------------------------------------------------------
         */

         $fields[] =
            'nomor_surat';


         $values[] =
            $nomor_surat;


         $types .= 's';


         /*
         |--------------------------------------------------------------------------
         | CREATED BY
         |--------------------------------------------------------------------------
         */

         $fields[] =
            'created_by';


         $values[] =
            $created_by;


         $types .= 's';


         /*
         |--------------------------------------------------------------------------
         | QUERY INSERT
         |--------------------------------------------------------------------------
         */

         $columns =
            implode(
               ',',
               $fields
            );


         $placeholders =
            implode(
               ',',
               array_fill(
                  0,
                  count($fields),
                  '?'
               )
            );


         $queryInsert = "

            INSERT INTO surat_pemeriksaan_mata

            (
               $columns
            )

            VALUES

            (
               $placeholders
            )

         ";


         $stmtInsert =
            $koneksi->prepare(
               $queryInsert
            );


         if (!$stmtInsert) {

            throw new Exception(
               'Prepare INSERT gagal: ' .
                  $koneksi->error
            );
         }


         $stmtInsert->bind_param(
            $types,
            ...$values
         );


         if (
            !$stmtInsert->execute()
         ) {

            throw new Exception(
               'INSERT gagal: ' .
                  $stmtInsert->error
            );
         }


         $insertId =
            $stmtInsert->insert_id;


         $stmtInsert->close();


         /*
         |--------------------------------------------------------------------------
         | UPDATE NOMOR MATA
         |--------------------------------------------------------------------------
         */

         $queryUpdateSetting = "

            UPDATE setting_surat

            SET

               nomor_mata = ?,

               updated_at = NOW(),

               updated_by = ?

            WHERE id_customer = ?

         ";


         $stmtUpdateSetting =
            $koneksi->prepare(
               $queryUpdateSetting
            );


         if (!$stmtUpdateSetting) {

            throw new Exception(
               'Prepare update setting gagal: ' .
                  $koneksi->error
            );
         }


         $stmtUpdateSetting->bind_param(
            "iss",
            $nomorBerikutnya,
            $created_by,
            $id_customer
         );


         if (
            !$stmtUpdateSetting->execute()
         ) {

            throw new Exception(
               'Gagal memperbarui nomor_mata: ' .
                  $stmtUpdateSetting->error
            );
         }


         $stmtUpdateSetting->close();


         /*
         |--------------------------------------------------------------------------
         | COMMIT
         |--------------------------------------------------------------------------
         */

         $koneksi->commit();


         echo json_encode([

            'status' =>
            'success',

            'message' =>
            'Surat hasil pemeriksaan mata berhasil ditambahkan.',

            'id' =>
            $insertId,

            'nomor_surat' =>
            $nomor_surat

         ]);

         return;
      } catch (
         Throwable $e
      ) {


         /*
         |--------------------------------------------------------------------------
         | ROLLBACK
         |--------------------------------------------------------------------------
         */

         $koneksi->rollback();


         echo json_encode([

            'status' =>
            'error',

            'message' =>
            $e->getMessage()

         ]);

         return;
      }
   }


   /*
   |--------------------------------------------------------------------------
   | ============================================================
   | MANUAL
   | ============================================================
   |--------------------------------------------------------------------------
   */

   if ($mode === 'MANUAL') {


      $nomor_surat =
         trim(
            $_POST['nomor_surat'] ??
               ''
         );


      if (
         $nomor_surat === ''
      ) {

         echo json_encode([

            'status' =>
            'error',

            'message' =>
            'Nomor surat wajib diisi karena mode penomoran adalah MANUAL.'

         ]);

         return;
      }


      /*
      |--------------------------------------------------------------------------
      | INSERT MANUAL
      |--------------------------------------------------------------------------
      */

      $fields = [];

      $values = [];

      $types = '';


      foreach (
         allowedFields()
         as $field
      ) {

         if (
            $field ===
            'nomor_surat'
         ) {

            continue;
         }


         if (
            isset(
               $_POST[$field]
            )
         ) {

            $fields[] =
               $field;


            $values[] =
               trim(
                  $_POST[$field]
               );


            $types .= 's';
         }
      }


      /*
      |--------------------------------------------------------------------------
      | ID CUSTOMER
      |--------------------------------------------------------------------------
      */

      $fields[] =
         'id_customer';


      $values[] =
         $id_customer;


      $types .= 's';


      /*
      |--------------------------------------------------------------------------
      | NOMOR MANUAL
      |--------------------------------------------------------------------------
      */

      $fields[] =
         'nomor_surat';


      $values[] =
         $nomor_surat;


      $types .= 's';


      /*
      |--------------------------------------------------------------------------
      | CREATED BY
      |--------------------------------------------------------------------------
      */

      $fields[] =
         'created_by';


      $values[] =
         $created_by;


      $types .= 's';


      /*
      |--------------------------------------------------------------------------
      | QUERY
      |--------------------------------------------------------------------------
      */

      $columns =
         implode(
            ',',
            $fields
         );


      $placeholders =
         implode(
            ',',
            array_fill(
               0,
               count($fields),
               '?'
            )
         );


      $query = "

         INSERT INTO surat_pemeriksaan_mata

         (
            $columns
         )

         VALUES

         (
            $placeholders
         )

      ";


      $stmt =
         $koneksi->prepare(
            $query
         );


      if (!$stmt) {

         echo json_encode([

            'status' =>
            'error',

            'message' =>
            'Prepare INSERT gagal: ' .
               $koneksi->error

         ]);

         return;
      }


      $stmt->bind_param(
         $types,
         ...$values
      );


      if (
         $stmt->execute()
      ) {

         echo json_encode([

            'status' =>
            'success',

            'message' =>
            'Surat hasil pemeriksaan mata berhasil ditambahkan.',

            'id' =>
            $stmt->insert_id,

            'nomor_surat' =>
            $nomor_surat

         ]);
      } else {

         echo json_encode([

            'status' =>
            'error',

            'message' =>
            $stmt->error

         ]);
      }


      $stmt->close();

      return;
   }
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

         spm.*,

         pv.id_doctor,
         pv.visit_ID,
         pv.visit_date,

         mp.patient_name,
         mp.nomor_rm,
         mp.patient_nik,
         mp.patient_datebirth,
         mp.patient_place,
         mp.patient_gender,
         mp.patient_address,

         md.doctor_name

      FROM surat_pemeriksaan_mata spm

      INNER JOIN pasien_visit pv

         ON pv.id_visit = spm.id_visit

      INNER JOIN ms_patient mp

         ON mp.id_patient = spm.id_patient

      LEFT JOIN ms_doctor md

         ON md.doctor_code = pv.id_doctor

      WHERE spm.id_customer = ?

      AND pv.id_customer = ?

      ORDER BY

         spm.id DESC

   ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'Prepare GET gagal: ' .
            $koneksi->error

      ]);

      return;
   }


   $stmt->bind_param(
      "ss",
      $id_customer,
      $id_customer
   );


   $stmt->execute();


   $result =
      $stmt->get_result();


   echo json_encode([

      'status' =>
      'success',

      'data' =>
      $result->fetch_all(
         MYSQLI_ASSOC
      )

   ]);


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| GET DETAIL
|--------------------------------------------------------------------------
*/

function getID(
   $id,
   $id_customer
) {

   global $koneksi;


   $query = "

      SELECT

         spm.*,

         pv.id_doctor,
         pv.visit_ID,
         pv.visit_date,

         mp.patient_name,
         mp.nomor_rm,
         mp.patient_nik,
         mp.patient_datebirth,
         mp.patient_place,
         mp.patient_gender,
         mp.patient_address,

         md.doctor_name

      FROM surat_pemeriksaan_mata spm

      INNER JOIN pasien_visit pv

         ON pv.id_visit = spm.id_visit

      INNER JOIN ms_patient mp

         ON mp.id_patient = spm.id_patient

      LEFT JOIN ms_doctor md

         ON md.doctor_code = pv.id_doctor

      WHERE spm.id = ?

      AND spm.id_customer = ?

      AND pv.id_customer = ?

      LIMIT 1

   ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'Prepare GET ID gagal: ' .
            $koneksi->error

      ]);

      return;
   }


   $stmt->bind_param(
      "iss",
      $id,
      $id_customer,
      $id_customer
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
         'Data surat tidak ditemukan.'

      ]);
   }


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
|
| NOMOR SURAT TIDAK DIUBAH
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
      $_PUT
   );


   $id =
      trim(
         $_PUT['id'] ?? ''
      );


   if ($id === '') {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'ID surat tidak ditemukan.'

      ]);

      return;
   }


   /*
   |--------------------------------------------------------------------------
   | VALIDASI DATA BERDASARKAN CUSTOMER
   |--------------------------------------------------------------------------
   */

   $check = $koneksi->prepare("

      SELECT

         spm.id

      FROM surat_pemeriksaan_mata spm

      INNER JOIN pasien_visit pv

         ON pv.id_visit = spm.id_visit

      WHERE spm.id = ?

      AND spm.id_customer = ?

      AND pv.id_customer = ?

      LIMIT 1

   ");


   if (!$check) {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'Prepare validasi gagal: ' .
            $koneksi->error

      ]);

      return;
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


      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'Data surat tidak ditemukan.'

      ]);

      return;
   }


   $check->close();


   /*
   |--------------------------------------------------------------------------
   | BUILD UPDATE
   |--------------------------------------------------------------------------
   */

   $fields = [];

   $values = [];

   $types = '';


   foreach (
      allowedFields()
      as $field
   ) {

      /*
      |--------------------------------------------------------------------------
      | NOMOR SURAT TIDAK BOLEH DIUBAH
      |--------------------------------------------------------------------------
      */

      if (
         $field ===
         'nomor_surat'
      ) {

         continue;
      }


      if (
         isset(
            $_PUT[$field]
         )
      ) {

         /*
         |--------------------------------------------------------------------------
         | PREFIX spm
         |--------------------------------------------------------------------------
         |
         | Mencegah:
         |
         | Column 'id_visit' is ambiguous
         |
         |--------------------------------------------------------------------------
         */

         $fields[] =
            "spm.`$field` = ?";


         $values[] =
            trim(
               $_PUT[$field]
            );


         $types .= 's';
      }
   }


   /*
   |--------------------------------------------------------------------------
   | UPDATED BY
   |--------------------------------------------------------------------------
   */

   $updated_by =
      $_SESSION['uid_user']
      ?? $_SESSION['username']
      ?? 'system';


   $fields[] =
      "spm.updated_by = ?";


   $values[] =
      $updated_by;


   $types .= 's';


   /*
   |--------------------------------------------------------------------------
   | UPDATED AT
   |--------------------------------------------------------------------------
   */

   $fields[] =
      "spm.updated_at = ?";


   $values[] =
      date(
         'Y-m-d H:i:s'
      );


   $types .= 's';


   /*
   |--------------------------------------------------------------------------
   | JIKA TIDAK ADA FIELD
   |--------------------------------------------------------------------------
   */

   if (
      empty($fields)
   ) {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'Tidak ada data yang diubah.'

      ]);

      return;
   }


   /*
   |--------------------------------------------------------------------------
   | WHERE PARAMETER
   |--------------------------------------------------------------------------
   */

   $values[] =
      $id;


   $values[] =
      $id_customer;


   $values[] =
      $id_customer;


   $types .= 'iss';


   /*
   |--------------------------------------------------------------------------
   | UPDATE
   |--------------------------------------------------------------------------
   */

   $query = "

      UPDATE surat_pemeriksaan_mata spm

      INNER JOIN pasien_visit pv

         ON pv.id_visit = spm.id_visit

      SET

         " .
      implode(
         ',',
         $fields
      ) . "

      WHERE spm.id = ?

      AND spm.id_customer = ?

      AND pv.id_customer = ?

   ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'Prepare UPDATE gagal: ' .
            $koneksi->error

      ]);

      return;
   }


   $stmt->bind_param(
      $types,
      ...$values
   );


   if (
      $stmt->execute()
   ) {

      echo json_encode([

         'status' =>
         'success',

         'message' =>
         'Surat hasil pemeriksaan mata berhasil diperbarui.'

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
      trim(
         $_GET['id'] ?? ''
      );


   if ($id === '') {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'ID surat kosong.'

      ]);

      return;
   }


   $stmt =
      $koneksi->prepare("

         DELETE spm

         FROM surat_pemeriksaan_mata spm

         INNER JOIN pasien_visit pv

            ON pv.id_visit = spm.id_visit

         WHERE spm.id = ?

         AND spm.id_customer = ?

         AND pv.id_customer = ?

      ");


   if (!$stmt) {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         'Prepare DELETE gagal: ' .
            $koneksi->error

      ]);

      return;
   }


   $stmt->bind_param(
      "iss",
      $id,
      $id_customer,
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
            'Surat berhasil dihapus.'

         ]);
      } else {

         echo json_encode([

            'status' =>
            'error',

            'message' =>
            'Data surat tidak ditemukan.'

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
