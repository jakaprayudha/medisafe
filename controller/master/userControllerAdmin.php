<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];


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
         'status' => 'error',
         'message' => 'Method tidak diizinkan.',
         'data'   => []
      ]);

      break;
}


/*
|--------------------------------------------------------------------------
| AMBIL ID CUSTOMER BERDASARKAN NO / ID CLINIC
|--------------------------------------------------------------------------
|
| View mengirim:
|
| userController?no=123
|
| no = setting_clinic.id
|
| kemudian:
|
| setting_clinic.id_customer
|        ↓
| ms_users.id_customer
|
|--------------------------------------------------------------------------
*/

function getCustomerFromNo()
{
   global $koneksi;

   $no = $_GET['no'] ?? '';

   if ($no === '') {

      echo json_encode([
         'status' => 'error',
         'message' => 'Parameter no tidak ditemukan.',
         'data' => []
      ]);

      exit;
   }

   $no = (int) $no;

   if ($no <= 0) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Parameter no tidak valid.',
         'data' => []
      ]);

      exit;
   }


   $stmt = $koneksi->prepare("
        SELECT
            id,
            id_customer
        FROM setting_clinic
        WHERE id = ?
        LIMIT 1
    ");


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare setting clinic gagal: ' . $koneksi->error,
         'data' => []
      ]);

      exit;
   }


   $stmt->bind_param(
      "i",
      $no
   );

   $stmt->execute();

   $result = $stmt->get_result();


   if (!$result || $result->num_rows === 0) {

      $stmt->close();

      echo json_encode([
         'status' => 'error',
         'message' => 'Data faskes tidak ditemukan.',
         'data' => []
      ]);

      exit;
   }


   $data = $result->fetch_assoc();

   $stmt->close();


   return (int) $data['id_customer'];
}


/*
|--------------------------------------------------------------------------
| CREATE USER
|--------------------------------------------------------------------------
*/

function createData()
{
   global $koneksi;

   $id_customer = getCustomerFromNo();


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


   $fullname = trim($_POST['fullname'] ?? '');
   $username = trim($_POST['username'] ?? '');
   $password = trim($_POST['password'] ?? '');
   $roles    = trim($_POST['roles'] ?? '');
   $path     = trim($_POST['path'] ?? '');
   $kdDokter = trim($_POST['kdDokter'] ?? '');


   if ($fullname === '') {

      echo json_encode([
         'status' => 'error',
         'message' => 'Fullname wajib diisi.'
      ]);

      return;
   }


   if ($username === '') {

      echo json_encode([
         'status' => 'error',
         'message' => 'Username wajib diisi.'
      ]);

      return;
   }


   if ($password === '') {

      echo json_encode([
         'status' => 'error',
         'message' => 'Password wajib diisi.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | CEK USERNAME
    |--------------------------------------------------------------------------
    */

   $count = 0;

   $check = $koneksi->prepare("
        SELECT COUNT(*)
        FROM ms_users
        WHERE username = ?
        AND id_customer = ?
    ");


   if (!$check) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare cek username gagal: ' . $koneksi->error
      ]);

      return;
   }


   $check->bind_param(
      "si",
      $username,
      $id_customer
   );


   $check->execute();

   $check->bind_result($count);

   $check->fetch();

   $check->close();


   if ($count > 0) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Username sudah digunakan.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | GENERATE UID
    |--------------------------------------------------------------------------
    */

   $uid_user = generateUserUID($koneksi);

   $password_md5 = md5($password);


   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

   $stmt = $koneksi->prepare("
        INSERT INTO ms_users
        (
            uid_user,
            fullname,
            username,
            password,
            roles,
            path,
            status,
            id_customer,
            kdDokter
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            1,
            ?,
            ?
        )
    ");


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare insert gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "ssssssis",
      $uid_user,
      $fullname,
      $username,
      $password_md5,
      $roles,
      $path,
      $id_customer,
      $kdDokter
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status' => 'success',
         'message' => 'User berhasil ditambahkan.',
         'id_user' => $stmt->insert_id
      ]);
   } else {

      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal insert user: ' . $stmt->error
      ]);
   }


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| GENERATE UID
|--------------------------------------------------------------------------
*/

function generateUserUID($koneksi)
{
   do {

      $random = mt_rand(100000, 999999);

      $uid = 'USR-' . md5(
         $random . microtime(true)
      );


      $count = 0;


      $check = $koneksi->prepare("
            SELECT COUNT(*)
            FROM ms_users
            WHERE uid_user = ?
        ");


      if (!$check) {
         return $uid;
      }


      $check->bind_param(
         "s",
         $uid
      );


      $check->execute();

      $check->bind_result($count);

      $check->fetch();

      $check->close();
   } while ($count > 0);


   return $uid;
}


/*
|--------------------------------------------------------------------------
| GET ALL USER
|--------------------------------------------------------------------------
*/

function getData()
{
   global $koneksi;

   $id_customer = getCustomerFromNo();


   /*
    |--------------------------------------------------------------------------
    | QUERY USER
    |--------------------------------------------------------------------------
    */

   $stmt = $koneksi->prepare("
        SELECT
            id_user,
            uid_user,
            fullname,
            username,
            roles,
            path,
            status,
            created_at,
            kdDokter
        FROM ms_users
        WHERE id_customer = ?
        ORDER BY username DESC
    ");


   if (!$stmt) {

      http_response_code(500);

      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare GET user gagal: ' . $koneksi->error,
         'data' => []
      ]);

      return;
   }


   $stmt->bind_param(
      "i",
      $id_customer
   );


   if (!$stmt->execute()) {

      http_response_code(500);

      echo json_encode([
         'status' => 'error',
         'message' => 'Execute GET user gagal: ' . $stmt->error,
         'data' => []
      ]);

      $stmt->close();

      return;
   }


   $result = $stmt->get_result();

   $data = [];


   while ($row = $result->fetch_assoc()) {

      $data[] = $row;
   }


   /*
    |--------------------------------------------------------------------------
    | FORMAT DATATABLES
    |--------------------------------------------------------------------------
    */

   echo json_encode([
      'status' => 'success',
      'data'   => $data
   ]);


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| GET USER BY ID
|--------------------------------------------------------------------------
*/

function getID($id)
{
   global $koneksi;

   $id_customer = getCustomerFromNo();


   $id = (int) $id;


   if ($id <= 0) {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID user tidak valid.'
      ]);

      return;
   }


   $stmt = $koneksi->prepare("
        SELECT
            id_user,
            uid_user,
            fullname,
            username,
            roles,
            path,
            status,
            created_at,
            kdDokter
        FROM ms_users
        WHERE id_user = ?
        AND id_customer = ?
        LIMIT 1
    ");


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare GET ID gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "ii",
      $id,
      $id_customer
   );


   if (!$stmt->execute()) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Execute GET ID gagal: ' . $stmt->error
      ]);

      $stmt->close();

      return;
   }


   $result = $stmt->get_result();


   if ($result && $result->num_rows > 0) {

      echo json_encode([
         'status' => 'success',
         'data' => $result->fetch_assoc()
      ]);
   } else {

      echo json_encode([
         'status' => 'error',
         'message' => 'Data user tidak ditemukan.'
      ]);
   }


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| UPDATE USER
|--------------------------------------------------------------------------
*/

function updateData()
{
   global $koneksi;

   $id_customer = getCustomerFromNo();


   /*
    |--------------------------------------------------------------------------
    | PARSE PUT
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
         'status' => 'error',
         'message' => 'Data PUT kosong.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | ID USER
    |--------------------------------------------------------------------------
    */

   $id = $_PUT['id_user'] ?? $_GET['id'] ?? '';


   if ($id === '') {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID user tidak ditemukan.'
      ]);

      return;
   }


   $id = (int) $id;


   /*
    |--------------------------------------------------------------------------
    | TOGGLE STATUS
    |--------------------------------------------------------------------------
    */

   if (
      isset($_GET['toggle_status']) &&
      isset($_PUT['status'])
   ) {

      $status = (int) $_PUT['status'];


      $stmt = $koneksi->prepare("
            UPDATE ms_users
            SET status = ?
            WHERE id_user = ?
            AND id_customer = ?
        ");


      if (!$stmt) {

         echo json_encode([
            'status' => 'error',
            'message' => 'Prepare toggle status gagal: ' . $koneksi->error
         ]);

         return;
      }


      $stmt->bind_param(
         "iii",
         $status,
         $id,
         $id_customer
      );


      if ($stmt->execute()) {

         echo json_encode([
            'status' => 'success',
            'message' => 'Status user berhasil diubah.'
         ]);
      } else {

         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal mengubah status: ' . $stmt->error
         ]);
      }


      $stmt->close();

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | CEK USER
    |--------------------------------------------------------------------------
    */

   $checkUser = $koneksi->prepare("
        SELECT id_user
        FROM ms_users
        WHERE id_user = ?
        AND id_customer = ?
        LIMIT 1
    ");


   if (!$checkUser) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare validasi user gagal: ' . $koneksi->error
      ]);

      return;
   }


   $checkUser->bind_param(
      "ii",
      $id,
      $id_customer
   );


   $checkUser->execute();

   $resultUser = $checkUser->get_result();


   if (!$resultUser || $resultUser->num_rows === 0) {

      $checkUser->close();

      echo json_encode([
         'status' => 'error',
         'message' => 'User tidak ditemukan.'
      ]);

      return;
   }


   $checkUser->close();


   /*
    |--------------------------------------------------------------------------
    | CEK USERNAME DUPLIKAT
    |--------------------------------------------------------------------------
    */

   if (isset($_PUT['username'])) {

      $username = trim($_PUT['username']);

      $count = 0;


      $check = $koneksi->prepare("
            SELECT COUNT(*)
            FROM ms_users
            WHERE username = ?
            AND id_user != ?
            AND id_customer = ?
        ");


      if (!$check) {

         echo json_encode([
            'status' => 'error',
            'message' => 'Prepare cek username gagal: ' . $koneksi->error
         ]);

         return;
      }


      $check->bind_param(
         "sii",
         $username,
         $id,
         $id_customer
      );


      $check->execute();

      $check->bind_result($count);

      $check->fetch();

      $check->close();


      if ($count > 0) {

         echo json_encode([
            'status' => 'error',
            'message' => 'Username sudah digunakan.'
         ]);

         return;
      }
   }


   /*
    |--------------------------------------------------------------------------
    | FIELD UPDATE
    |--------------------------------------------------------------------------
    */

   $fields = [];
   $values = [];
   $types  = '';


   if (isset($_PUT['fullname'])) {

      $fields[] = "fullname = ?";
      $values[] = trim($_PUT['fullname']);
      $types .= "s";
   }


   if (isset($_PUT['username'])) {

      $fields[] = "username = ?";
      $values[] = trim($_PUT['username']);
      $types .= "s";
   }


   /*
    |--------------------------------------------------------------------------
    | PASSWORD
    |--------------------------------------------------------------------------
    */

   if (
      isset($_PUT['password']) &&
      trim($_PUT['password']) !== ''
   ) {

      $fields[] = "password = ?";
      $values[] = md5(trim($_PUT['password']));
      $types .= "s";
   }


   /*
    |--------------------------------------------------------------------------
    | ROLE
    |--------------------------------------------------------------------------
    */

   if (isset($_PUT['roles'])) {

      $fields[] = "roles = ?";
      $values[] = trim($_PUT['roles']);
      $types .= "s";
   }


   /*
    |--------------------------------------------------------------------------
    | PATH
    |--------------------------------------------------------------------------
    */

   if (isset($_PUT['path'])) {

      $fields[] = "path = ?";
      $values[] = trim($_PUT['path']);
      $types .= "s";
   }


   /*
    |--------------------------------------------------------------------------
    | KD DOKTER
    |--------------------------------------------------------------------------
    */

   if (isset($_PUT['kdDokter'])) {

      $fields[] = "kdDokter = ?";
      $values[] = trim($_PUT['kdDokter']);
      $types .= "s";
   }


   if (empty($fields)) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Tidak ada perubahan.'
      ]);

      return;
   }


   /*
    |--------------------------------------------------------------------------
    | WHERE
    |--------------------------------------------------------------------------
    */

   $values[] = $id;
   $values[] = $id_customer;

   $types .= "ii";


   $query = "
        UPDATE ms_users
        SET " . implode(", ", $fields) . "
        WHERE id_user = ?
        AND id_customer = ?
    ";


   $stmt = $koneksi->prepare($query);


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
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
         'status' => 'success',
         'message' => 'Data user berhasil diupdate.'
      ]);
   } else {

      echo json_encode([
         'status' => 'error',
         'message' => 'Update user gagal: ' . $stmt->error
      ]);
   }


   $stmt->close();
}


/*
|--------------------------------------------------------------------------
| DELETE USER
|--------------------------------------------------------------------------
*/

function deleteData()
{
   global $koneksi;

   $id_customer = getCustomerFromNo();


   $id = $_GET['id'] ?? '';


   if ($id === '') {

      echo json_encode([
         'status' => 'error',
         'message' => 'ID user tidak ditemukan.'
      ]);

      return;
   }


   $id = (int) $id;


   $stmt = $koneksi->prepare("
        DELETE FROM ms_users
        WHERE id_user = ?
        AND id_customer = ?
    ");


   if (!$stmt) {

      echo json_encode([
         'status' => 'error',
         'message' => 'Prepare delete gagal: ' . $koneksi->error
      ]);

      return;
   }


   $stmt->bind_param(
      "ii",
      $id,
      $id_customer
   );


   if ($stmt->execute()) {

      echo json_encode([
         'status' => 'success',
         'message' => 'User berhasil dihapus.'
      ]);
   } else {

      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menghapus user: ' . $stmt->error
      ]);
   }


   $stmt->close();
}
