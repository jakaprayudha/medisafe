<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

   case 'POST':
      createData();
      break;

   case 'GET':

      if (isset($_GET['id']) && $_GET['id'] !== '') {

         getID($_GET['id']);
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

      http_response_code(405);

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
function createData()
{
   global $koneksi;

   if (empty($_POST)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Data tidak ditemukan.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | GENERATE ID CUSTOMER
    |--------------------------------------------------------------------------
    */

   $q = mysqli_query(
      $koneksi,
      "SELECT COUNT(*) AS total FROM setting_clinic"
   );

   if (!$q) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Query gagal: ' . mysqli_error($koneksi)
      ]);

      return;
   }

   $d = mysqli_fetch_assoc($q);

   $total = (int) ($d['total'] ?? 0);

   $id_customer = $total + 1;


   /*
    |--------------------------------------------------------------------------
    | FIELD YANG DIIZINKAN
    |--------------------------------------------------------------------------
    */

   $allowedFields = [
      'clinic_name'
   ];

   $fields = [
      'id_customer'
   ];

   $values = [
      $id_customer
   ];


   foreach ($allowedFields as $field) {

      if (isset($_POST[$field])) {

         $fields[] = $field;
         $values[] = $_POST[$field];
      }
   }


   if (count($fields) <= 1) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Tidak ada data yang dikirim.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

   $placeholders = implode(
      ', ',
      array_fill(0, count($fields), '?')
   );

   $columns = implode(
      ', ',
      $fields
   );

   $types = 'i' . str_repeat(
      's',
      count($fields) - 1
   );

   $query = "
        INSERT INTO setting_clinic
        ($columns)
        VALUES
        ($placeholders)
    ";

   $stmt = $koneksi->prepare($query);

   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Prepare gagal: ' . $koneksi->error
      ]);

      return;
   }

   $stmt->bind_param(
      $types,
      ...$values
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status'      => 'success',
         'message'     => 'Data berhasil ditambahkan.',
         'id_customer' => $id_customer
      ]);
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal insert: ' . $stmt->error
      ]);
   }

   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| GET ALL
|--------------------------------------------------------------------------
*/
function getData()
{
   global $koneksi;

   $query = "
        SELECT
            setting_clinic.*,

            ms_faskes.contract_number,
            ms_faskes.faskes_code,
            ms_faskes.contract_start,
            ms_faskes.contract_end,
            ms_faskes.contract_amount,

            ms_users.fullname,
            ms_users.username,
            ms_users.password

        FROM setting_clinic

        LEFT JOIN ms_faskes
            ON setting_clinic.id = ms_faskes.id_clinic

        LEFT JOIN ms_users
            ON ms_users.id_customer = setting_clinic.id_customer
            AND ms_users.roles = 'admin'

        WHERE setting_clinic.status != 99

        GROUP BY setting_clinic.id

        ORDER BY setting_clinic.id DESC
    ";


   $result = mysqli_query(
      $koneksi,
      $query
   );


   if (!$result) {

      http_response_code(500);

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);

      return;
   }


   $data = mysqli_fetch_all(
      $result,
      MYSQLI_ASSOC
   );


   mysqli_free_result($result);


   /*
    |--------------------------------------------------------------------------
    | Jangan kirim password
    |--------------------------------------------------------------------------
    */

   foreach ($data as &$row) {

      unset($row['password']);
   }

   unset($row);


   echo json_encode([
      'status' => 'success',
      'data'   => $data
   ]);
}


/*
|--------------------------------------------------------------------------
| GET BY ID
|--------------------------------------------------------------------------
|
| URL:
|
| faskesController?id=123
|
| ID di sini adalah setting_clinic.id
|
|--------------------------------------------------------------------------
*/
function getID($id)
{
   global $koneksi;

   $id = (int) $id;


   if ($id <= 0) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'ID tidak valid.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | Ambil data faskes berdasarkan id_clinic
    |--------------------------------------------------------------------------
    */

   $query = "
        SELECT
            sc.*,

            mf.id_faskes,
            mf.id_clinic,
            mf.faskes_code,
            mf.pic_name,
            mf.pic_phone,
            mf.pic_email,
            mf.faskes_address,
            mf.faskes_prov,
            mf.faskes_city,
            mf.faskes_district,
            mf.faskes_village,
            mf.faskes_status,
            mf.contract_date,
            mf.faskes_payment,
            mf.contract_amount,
            mf.contract_start,
            mf.contract_end,
            mf.contract_number,
            mf.order_number

        FROM setting_clinic sc

        LEFT JOIN ms_faskes mf
            ON mf.id_clinic = sc.id

        WHERE sc.id = ?

        LIMIT 1
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menyiapkan query: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "i",
      $id
   );

   $stmt->execute();


   $result = $stmt->get_result();


   if ($result->num_rows > 0) {

      $data = $result->fetch_assoc();


      echo json_encode([
         'status' => 'success',
         'data'   => $data
      ]);
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Data faskes tidak ditemukan.'
      ]);
   }


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| UPDATE FASKES
|--------------------------------------------------------------------------
|
| View mengirim:
|
| PUT controller/master/faskesController
|
| id_faskes = setting_clinic.id
|
|--------------------------------------------------------------------------
*/
function updateData()
{
   global $koneksi;


   /*
    |--------------------------------------------------------------------------
    | Ambil PUT
    |--------------------------------------------------------------------------
    */

   $rawInput = file_get_contents(
      "php://input"
   );

   $_PUT = [];

   parse_str(
      $rawInput,
      $_PUT
   );


   if (empty($_PUT)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Data PUT kosong.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | ID
    |--------------------------------------------------------------------------
    */

   $id = $_PUT['id_faskes'] ?? $_GET['id'] ?? '';


   if ($id === '') {

      echo json_encode([
         'status'  => 'error',
         'message' => 'ID faskes tidak ditemukan.'
      ]);

      return;
   }


   $id = (int) $id;


   if ($id <= 0) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'ID faskes tidak valid.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | CEK SETTING CLINIC
    |--------------------------------------------------------------------------
    */

   $stmtClinic = $koneksi->prepare("
        SELECT
            id,
            id_customer
        FROM setting_clinic
        WHERE id = ?
        LIMIT 1
    ");


   if (!$stmtClinic) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Prepare clinic gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmtClinic->bind_param(
      "i",
      $id
   );

   $stmtClinic->execute();


   $resultClinic = $stmtClinic->get_result();


   if ($resultClinic->num_rows === 0) {

      $stmtClinic->close();

      echo json_encode([
         'status'  => 'error',
         'message' => 'Data clinic tidak ditemukan.'
      ]);

      return;
   }


   $clinic = $resultClinic->fetch_assoc();

   $id_clinic = (int) $clinic['id'];

   $id_customer = $clinic['id_customer'] ?? null;

   $stmtClinic->close();


   /*
    |--------------------------------------------------------------------------
    | CEK DATA MS_FASKES
    |--------------------------------------------------------------------------
    */

   $stmtFaskes = $koneksi->prepare("
        SELECT id_faskes
        FROM ms_faskes
        WHERE id_clinic = ?
        LIMIT 1
    ");


   if (!$stmtFaskes) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Prepare faskes gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmtFaskes->bind_param(
      "i",
      $id_clinic
   );

   $stmtFaskes->execute();


   $resultFaskes = $stmtFaskes->get_result();


   /*
    |--------------------------------------------------------------------------
    | FIELD YANG BOLEH DIUPDATE
    |--------------------------------------------------------------------------
    |
    | faskes_name TIDAK dimasukkan karena berdasarkan schema ms_faskes
    | yang tersedia tidak ada kolom faskes_name.
    |
    */

   $allowedFields = [

      'faskes_code',

      'pic_name',
      'pic_phone',
      'pic_email',

      'faskes_address',
      'faskes_prov',
      'faskes_city',
      'faskes_district',
      'faskes_village',

      'faskes_status',

      'contract_date',
      'faskes_payment',
      'contract_amount',
      'contract_start',
      'contract_end',
      'contract_number'

   ];


   $fields = [];
   $values = [];
   $types  = '';


   foreach ($allowedFields as $field) {

      if (array_key_exists($field, $_PUT)) {

         $fields[] = "$field = ?";
         $values[] = $_PUT[$field];

         /*
            |--------------------------------------------------------------------------
            | Semua field dibuat string agar aman dengan
            | decimal/date/varchar/int di schema existing.
            |--------------------------------------------------------------------------
            */

         $types .= "s";
      }
   }


   /*
    |--------------------------------------------------------------------------
    | TIDAK ADA DATA
    |--------------------------------------------------------------------------
    */

   if (empty($fields)) {

      $stmtFaskes->close();

      echo json_encode([
         'status'  => 'error',
         'message' => 'Tidak ada data yang diupdate.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | JIKA DATA FASKES BELUM ADA
    |--------------------------------------------------------------------------
    */

   if ($resultFaskes->num_rows === 0) {

      $stmtFaskes->close();

      /*
        |--------------------------------------------------------------------------
        | INSERT MS_FASKES
        |--------------------------------------------------------------------------
        |
        | Karena relasi menggunakan id_clinic.
        |
        */

      $insertFields = [
         'id_clinic'
      ];

      $insertValues = [
         $id_clinic
      ];

      $insertTypes = "i";


      foreach ($allowedFields as $field) {

         if (array_key_exists($field, $_PUT)) {

            $insertFields[] = $field;
            $insertValues[] = $_PUT[$field];
            $insertTypes .= "s";
         }
      }


      $columns = implode(
         ', ',
         $insertFields
      );

      $placeholders = implode(
         ', ',
         array_fill(
            0,
            count($insertFields),
            '?'
         )
      );


      $insertQuery = "
            INSERT INTO ms_faskes
            ($columns)
            VALUES
            ($placeholders)
        ";


      $stmtInsert = $koneksi->prepare(
         $insertQuery
      );


      if (!$stmtInsert) {

         echo json_encode([
            'status'  => 'error',
            'message' => 'Prepare insert faskes gagal: ' . $koneksi->error
         ]);

         return;
      }


      $stmtInsert->bind_param(
         $insertTypes,
         ...$insertValues
      );


      if ($stmtInsert->execute()) {

         echo json_encode([
            'status'      => 'success',
            'message'     => 'Data faskes berhasil disimpan.',
            'id_faskes'   => $stmtInsert->insert_id,
            'id_clinic'   => $id_clinic,
            'id_customer' => $id_customer
         ]);
      } else {

         echo json_encode([
            'status'  => 'error',
            'message' => 'Gagal menyimpan faskes: ' . $stmtInsert->error
         ]);
      }


      $stmtInsert->close();

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | DATA FASKES SUDAH ADA
    |--------------------------------------------------------------------------
    */

   $stmtFaskes->close();


   /*
    |--------------------------------------------------------------------------
    | UPDATE BERDASARKAN id_clinic
    |--------------------------------------------------------------------------
    */

   $values[] = $id_clinic;

   $types .= "i";


   $query = "
        UPDATE ms_faskes
        SET " . implode(", ", $fields) . "
        WHERE id_clinic = ?
    ";


   $stmt = $koneksi->prepare(
      $query
   );


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Prepare update gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      $types,
      ...$values
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status'      => 'success',
         'message'     => 'Data faskes berhasil diperbarui.',
         'id_clinic'   => $id_clinic,
         'id_customer' => $id_customer
      ]);
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Update gagal: ' . $stmt->error
      ]);
   }


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
|
| Soft delete:
| setting_clinic.status = 99
|
|--------------------------------------------------------------------------
*/
function deleteData()
{
   global $koneksi;


   $id = $_GET['id'] ?? '';


   if ($id === '') {

      echo json_encode([
         'status'  => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);

      return;
   }


   $id = (int) $id;


   $query = "
        UPDATE setting_clinic
        SET status = 99
        WHERE id = ?
    ";


   $stmt = $koneksi->prepare(
      $query
   );


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menyiapkan query.'
      ]);

      return;
   }


   $stmt->bind_param(
      "i",
      $id
   );


   if ($stmt->execute()) {

      if ($stmt->affected_rows > 0) {

         echo json_encode([
            'status'  => 'success',
            'message' => 'Data berhasil dihapus.'
         ]);
      } else {

         echo json_encode([
            'status'  => 'error',
            'message' => 'Data tidak ditemukan.'
         ]);
      }
   } else {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menghapus: ' . $stmt->error
      ]);
   }


   $stmt->close();
}
