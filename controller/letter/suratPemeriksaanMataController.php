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

if (!isset($_SESSION['id_customer'])) {

   echo json_encode([
      'status' => 'error',
      'message' => 'Session faskes tidak ditemukan.'
   ]);

   exit;
}

$id_customer = $_SESSION['id_customer'];


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

      if (isset($_GET['id']) && $_GET['id'] !== '') {

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
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);

      break;
}


/*
|--------------------------------------------------------------------------
| FIELD
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
| GENERATE NOMOR SURAT
|--------------------------------------------------------------------------
|
| Contoh:
|
| 001/SPM/VIII/2026
| 002/SPM/VIII/2026
| 003/SPM/VIII/2026
|
| Nomor berbeda berdasarkan id_customer.
|--------------------------------------------------------------------------
*/

function generateNomorSurat($id_customer)
{
   global $koneksi;


   /*
   |--------------------------------------------------------------------------
   | BULAN ROMAWI
   |--------------------------------------------------------------------------
   */

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


   $month = (int) date('n');

   $year = date('Y');


   /*
   |--------------------------------------------------------------------------
   | AMBIL NOMOR TERAKHIR
   |--------------------------------------------------------------------------
   */

   $query = "

      SELECT nomor_surat

      FROM surat_pemeriksaan_mata

      WHERE id_customer = ?

      AND nomor_surat IS NOT NULL

      AND nomor_surat <> ''

      ORDER BY id DESC

      LIMIT 1

   ";


   $stmt =
      $koneksi->prepare($query);


   if (!$stmt) {

      return false;
   }


   $stmt->bind_param(
      "i",
      $id_customer
   );


   $stmt->execute();


   $result =
      $stmt->get_result();


   $lastNumber = 0;


   if (
      $result->num_rows > 0
   ) {

      $row =
         $result->fetch_assoc();


      $nomorTerakhir =
         trim(
            $row['nomor_surat'] ?? ''
         );


      /*
      |--------------------------------------------------------------------------
      | AMBIL ANGKA DEPAN
      |--------------------------------------------------------------------------
      |
      | 001/SPM/VIII/2026
      | 002/SPM/VIII/2026
      |--------------------------------------------------------------------------
      */

      if (
         preg_match(
            '/^(\d+)/',
            $nomorTerakhir,
            $matches
         )
      ) {

         $lastNumber =
            (int) $matches[1];
      }
   }


   $stmt->close();


   /*
   |--------------------------------------------------------------------------
   | NOMOR BERIKUTNYA
   |--------------------------------------------------------------------------
   */

   $nextNumber =
      $lastNumber + 1;


   /*
   |--------------------------------------------------------------------------
   | FORMAT NOMOR
   |--------------------------------------------------------------------------
   */

   $nomor =

      str_pad(
         $nextNumber,
         3,
         '0',
         STR_PAD_LEFT
      )

      . '/SPM/'

      . $bulan[$month]

      . '/'

      . $year;


   return $nomor;
}


/*
|--------------------------------------------------------------------------
| CREATE
|--------------------------------------------------------------------------
*/

function createData($id_customer)
{
   global $koneksi;


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
      !$id_patient ||
      !$id_visit
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

   $check =
      $koneksi->prepare("

         SELECT id_visit

         FROM pasien_visit

         WHERE id_visit = ?

         AND id_patient = ?

         AND id_customer = ?

         LIMIT 1

      ");


   if (!$check) {

      echo json_encode([
         'status' => 'error',
         'message' => $koneksi->error
      ]);

      return;
   }


   $check->bind_param(
      "ssi",
      $id_visit,
      $id_patient,
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
         'status' => 'error',
         'message' => 'Kunjungan pasien tidak valid.'
      ]);

      return;
   }


   $check->close();


   /*
   |--------------------------------------------------------------------------
   | BUILD DATA
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
      | NOMOR SURAT JANGAN DIAMBIL DARI FORM
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
   |
   | Disimpan ke surat agar nomor surat dapat
   | dibuat berdasarkan customer masing-masing.
   |--------------------------------------------------------------------------
   */

   $fields[] =
      'id_customer';


   $values[] =
      $id_customer;


   $types .= 'i';


   /*
   |--------------------------------------------------------------------------
   | GENERATE NOMOR SURAT
   |--------------------------------------------------------------------------
   */

   $nomor_surat =
      generateNomorSurat(
         $id_customer
      );


   if (
      empty($nomor_surat)
   ) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal membuat nomor surat.'
      ]);

      return;
   }


   /*
   |--------------------------------------------------------------------------
   | SIMPAN NOMOR SURAT
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

   $created_by =
      $_SESSION['uid_user']
      ?? $_SESSION['username']
      ?? 'system';


   $fields[] =
      'created_by';


   $values[] =
      $created_by;


   $types .= 's';


   /*
   |--------------------------------------------------------------------------
   | INSERT
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
         'status' => 'error',
         'message' => $koneksi->error
      ]);

      return;
   }


   /*
   |--------------------------------------------------------------------------
   | BIND
   |--------------------------------------------------------------------------
   */

   $stmt->bind_param(
      $types,
      ...$values
   );


   /*
   |--------------------------------------------------------------------------
   | EXECUTE
   |--------------------------------------------------------------------------
   */

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

      WHERE pv.id_customer = ?

      ORDER BY spm.id DESC

   ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => $koneksi->error
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
| GET ID
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

      AND pv.id_customer = ?

      LIMIT 1

   ";


   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "ii",
      $id,
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
*/

function updateData($id_customer)
{
   global $koneksi;


   /*
   |--------------------------------------------------------------------------
   | GET PUT DATA
   |--------------------------------------------------------------------------
   */

   parse_str(
      file_get_contents("php://input"),
      $_PUT
   );


   $id = $_PUT['id'] ?? '';


   if (!$id) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID surat tidak ditemukan.'
      ]);

      return;
   }


   /*
   |--------------------------------------------------------------------------
   | VALIDASI DATA
   |--------------------------------------------------------------------------
   */

   $check = $koneksi->prepare("

      SELECT
         spm.id

      FROM surat_pemeriksaan_mata spm

      INNER JOIN pasien_visit pv
         ON pv.id_visit = spm.id_visit

      WHERE spm.id = ?

      AND pv.id_customer = ?

      LIMIT 1

   ");


   if (!$check) {

      echo json_encode([
         'status' => 'error',
         'message' => $koneksi->error
      ]);

      return;
   }


   $check->bind_param(
      "ii",
      $id,
      $id_customer
   );


   $check->execute();


   $result = $check->get_result();


   if ($result->num_rows === 0) {

      $check->close();

      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan.'
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

      if ($field === 'nomor_surat') {
         continue;
      }


      if (
         isset($_PUT[$field])
      ) {

         /*
         |--------------------------------------------------------------------------
         | PREFIX TABLE
         |--------------------------------------------------------------------------
         |
         | Penting karena UPDATE menggunakan JOIN.
         |--------------------------------------------------------------------------
         */

         $fields[] =
            "spm.$field=?";


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
      "spm.updated_by=?";


   $values[] =
      $updated_by;


   $types .= 's';


   /*
   |--------------------------------------------------------------------------
   | UPDATED AT
   |--------------------------------------------------------------------------
   */

   $fields[] =
      "spm.updated_at=?";


   $values[] =
      date('Y-m-d H:i:s');


   $types .= 's';


   /*
   |--------------------------------------------------------------------------
   | CEK FIELD UPDATE
   |--------------------------------------------------------------------------
   */

   if (empty($fields)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada data yang diubah.'
      ]);

      return;
   }


   /*
   |--------------------------------------------------------------------------
   | WHERE
   |--------------------------------------------------------------------------
   */

   $values[] =
      $id;


   $values[] =
      $id_customer;


   $types .= 'ii';


   /*
   |--------------------------------------------------------------------------
   | UPDATE QUERY
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

      AND pv.id_customer = ?

   ";


   /*
   |--------------------------------------------------------------------------
   | PREPARE
   |--------------------------------------------------------------------------
   */

   $stmt =
      $koneksi->prepare(
         $query
      );


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => $koneksi->error
      ]);

      return;
   }


   /*
   |--------------------------------------------------------------------------
   | BIND
   |--------------------------------------------------------------------------
   */

   $stmt->bind_param(
      $types,
      ...$values
   );


   /*
   |--------------------------------------------------------------------------
   | EXECUTE
   |--------------------------------------------------------------------------
   */

   if (
      $stmt->execute()
   ) {

      echo json_encode([

         'status' =>
         'success',

         'message' =>
         'Surat berhasil diperbarui.'

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
      $_GET['id'] ?? '';


   if (!$id) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID surat kosong.'
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

         AND pv.id_customer = ?

      ");


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => $koneksi->error
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
            'Surat berhasil dihapus.'

         ]);
      } else {

         echo json_encode([

            'status' =>
            'error',

            'message' =>
            'Data tidak ditemukan.'

         ]);
      }
   } else {

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         $stmt->error

      ]);
   }


   $stmt->close();
}
