<?php

include '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];


/*
|--------------------------------------------------------------------------
| RESPONSE HELPER
|--------------------------------------------------------------------------
*/

function responseJson(
   $status,
   $message = '',
   $data = null,
   $extra = []
) {
   $response = [
      'status'  => $status,
      'message' => $message
   ];

   if ($data !== null) {
      $response['data'] = $data;
   }

   if (!empty($extra)) {
      $response = array_merge($response, $extra);
   }

   echo json_encode(
      $response,
      JSON_UNESCAPED_UNICODE
   );

   exit;
}


/*
|--------------------------------------------------------------------------
| HELPER: BIND PARAM DINAMIS
|--------------------------------------------------------------------------
| Dipakai agar jumlah/type parameter selalu konsisten.
*/

function bindDynamicParams($stmt, $types, &$values)
{
   if ($types === '') {
      return true;
   }

   $refs = [];
   $refs[] = $types;

   foreach ($values as $key => &$value) {
      $refs[] = &$value;
   }

   return call_user_func_array(
      [$stmt, 'bind_param'],
      $refs
   );
}


/*
|--------------------------------------------------------------------------
| HELPER: NORMALISASI NILAI
|--------------------------------------------------------------------------
*/

function postString($field, $default = '')
{
   return isset($_POST[$field])
      ? trim((string) $_POST[$field])
      : $default;
}

function putString($data, $field, $default = '')
{
   return isset($data[$field])
      ? trim((string) $data[$field])
      : $default;
}


/*
|--------------------------------------------------------------------------
| ROUTING
|--------------------------------------------------------------------------
*/

switch ($method) {

   /*
    |--------------------------------------------------------------------------
    | POST
    |--------------------------------------------------------------------------
    |
    | 1. INSERT setting_clinic
    | 2. UPSERT ms_faskes
    |
    */

   case 'POST':

      /*
        |--------------------------------------------------------------------------
        | Form Profile Faskes
        |--------------------------------------------------------------------------
        |
        | Jangan hanya bergantung pada faskes_code karena field tersebut
        | bisa kosong. order_number/id_clinic menjadi identitas utama halaman.
        |
        */

      if (
         isset($_POST['order_number']) ||
         isset($_POST['id_clinic']) ||
         isset($_POST['id_faskes']) ||
         isset($_POST['faskes_code']) ||
         isset($_POST['pic_name']) ||
         isset($_POST['pic_phone']) ||
         isset($_POST['pic_email']) ||
         isset($_POST['faskes_address']) ||
         isset($_POST['contract_number'])
      ) {
         saveFaskes();
      } else {

         /*
            |--------------------------------------------------------------------------
            | POST lama untuk setting_clinic
            |--------------------------------------------------------------------------
            */

         createData();
      }

      break;


   /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */

   case 'GET':

      if (
         isset($_GET['id']) &&
         $_GET['id'] !== ''
      ) {
         getID($_GET['id']);
      } else {
         getData();
      }

      break;


   /*
    |--------------------------------------------------------------------------
    | PUT
    |--------------------------------------------------------------------------
    | Tetap dipertahankan untuk kompatibilitas dengan proses lama.
    */

   case 'PUT':

      updateData();

      break;


   /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

   case 'DELETE':

      deleteData();

      break;


   /*
    |--------------------------------------------------------------------------
    | METHOD TIDAK DIIZINKAN
    |--------------------------------------------------------------------------
    */

   default:

      http_response_code(405);

      responseJson(
         'error',
         'Method tidak diizinkan.'
      );
}


/*
|--------------------------------------------------------------------------
| CREATE SETTING CLINIC
|--------------------------------------------------------------------------
| Fungsi lama dipertahankan.
*/

function createData()
{
   global $koneksi;

   if (empty($_POST)) {
      responseJson(
         'error',
         'Data tidak ditemukan.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | GENERATE ID CUSTOMER
    |--------------------------------------------------------------------------
    */

   $q = mysqli_query(
      $koneksi,
      "
            SELECT COUNT(*) AS total
            FROM setting_clinic
        "
   );

   if (!$q) {
      responseJson(
         'error',
         'Query gagal: ' . mysqli_error($koneksi)
      );
   }

   $d = mysqli_fetch_assoc($q);

   $total = (int) ($d['total'] ?? 0);

   $id_customer = $total + 1;


   /*
    |--------------------------------------------------------------------------
    | FIELD SETTING CLINIC
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

      if (
         isset($_POST[$field]) &&
         $_POST[$field] !== ''
      ) {
         $fields[] = $field;
         $values[] = $_POST[$field];
      }
   }

   if (count($fields) <= 1) {
      responseJson(
         'error',
         'Tidak ada data yang dikirim.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

   $placeholders = implode(
      ', ',
      array_fill(
         0,
         count($fields),
         '?'
      )
   );

   $columns = implode(
      ', ',
      $fields
   );

   $types =
      'i' .
      str_repeat(
         's',
         count($fields) - 1
      );

   $query = "
        INSERT INTO setting_clinic
        (
            $columns
        )
        VALUES
        (
            $placeholders
        )
    ";

   $stmt = $koneksi->prepare($query);

   if (!$stmt) {
      responseJson(
         'error',
         'Prepare gagal: ' . $koneksi->error
      );
   }

   bindDynamicParams(
      $stmt,
      $types,
      $values
   );

   if (!$stmt->execute()) {
      responseJson(
         'error',
         'Gagal insert: ' . $stmt->error
      );
   }

   $newId = $koneksi->insert_id;

   $stmt->close();

   responseJson(
      'success',
      'Data berhasil ditambahkan.',
      null,
      [
         'action'     => 'insert',
         'id_customer' => $id_customer,
         'id_clinic'   => $newId
      ]
   );
}


/*
|--------------------------------------------------------------------------
| SAVE FASKES
|--------------------------------------------------------------------------
|
| POST Profile Faskes.
|
| LOGIC:
|
| ms_faskes belum ada:
|     INSERT
|
| ms_faskes sudah ada:
|     UPDATE
|
| IDENTITAS:
|
| - id_clinic = setting_clinic.id
| - id_faskes = primary key ms_faskes
|
| Keterangan:
| id_faskes TIDAK dipakai sebagai id_clinic.
| Jika id_clinic belum dikirim, order_number digunakan sebagai fallback
| karena halaman detail saat ini menggunakan ?no= sebagai ID clinic.
|
*/

function saveFaskes()
{
   global $koneksi;

   if (empty($_POST)) {
      responseJson(
         'error',
         'Data tidak ditemukan.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | AMBIL ID FASKES JIKA SUDAH ADA
    |--------------------------------------------------------------------------
    */

   $id_faskes = 0;

   if (
      isset($_POST['id_faskes']) &&
      $_POST['id_faskes'] !== '' &&
      ctype_digit((string) $_POST['id_faskes'])
   ) {
      $id_faskes = (int) $_POST['id_faskes'];
   }


   /*
    |--------------------------------------------------------------------------
    | AMBIL ID CLINIC
    |--------------------------------------------------------------------------
    |
    | PRIORITAS:
    | 1. id_clinic
    | 2. order_number
    |
    | id_faskes TIDAK digunakan di sini.
    |
    */

   $id_clinic = 0;

   if (
      isset($_POST['id_clinic']) &&
      $_POST['id_clinic'] !== '' &&
      ctype_digit((string) $_POST['id_clinic'])
   ) {
      $id_clinic = (int) $_POST['id_clinic'];
   }

   if ($id_clinic <= 0) {

      $orderNumberInput =
         postString('order_number');

      if (
         $orderNumberInput !== '' &&
         ctype_digit($orderNumberInput)
      ) {
         $id_clinic =
            (int) $orderNumberInput;
      }
   }

   if ($id_clinic <= 0) {
      responseJson(
         'error',
         'ID Clinic / Order Number tidak ditemukan.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | CEK SETTING CLINIC
    |--------------------------------------------------------------------------
    */

   $stmtClinic = $koneksi->prepare(
      "
            SELECT
                id,
                id_customer
            FROM setting_clinic
            WHERE id = ?
            LIMIT 1
        "
   );

   if (!$stmtClinic) {
      responseJson(
         'error',
         'Prepare clinic gagal: ' .
            $koneksi->error
      );
   }

   $stmtClinic->bind_param(
      'i',
      $id_clinic
   );

   if (!$stmtClinic->execute()) {
      responseJson(
         'error',
         'Gagal mengecek clinic: ' .
            $stmtClinic->error
      );
   }

   $resultClinic =
      $stmtClinic->get_result();

   if ($resultClinic->num_rows === 0) {

      $stmtClinic->close();

      responseJson(
         'error',
         'Data clinic dengan ID ' .
            $id_clinic .
            ' tidak ditemukan.'
      );
   }

   $clinic =
      $resultClinic->fetch_assoc();

   $id_customer =
      $clinic['id_customer'] ?? null;

   $stmtClinic->close();


   /*
    |--------------------------------------------------------------------------
    | AMBIL DATA FORM
    |--------------------------------------------------------------------------
    */

   $faskes_code =
      postString('faskes_code');

   $pic_name =
      postString('pic_name');

   $pic_phone =
      postString('pic_phone');

   $pic_email =
      postString('pic_email');

   $faskes_address =
      postString('faskes_address');

   $faskes_prov =
      postString('faskes_prov');

   $faskes_city =
      postString('faskes_city');

   $faskes_district =
      postString('faskes_district');

   $faskes_village =
      postString('faskes_village');

   /*
    |--------------------------------------------------------------------------
    | FASKES STATUS
    |--------------------------------------------------------------------------
    | Kolom faskes_status bertipe INTEGER.
    | Jika form mengirim string kosong, jangan kirim '' ke MySQL.
    | Gunakan 0 sebagai default.
    |--------------------------------------------------------------------------
    */
   $faskes_status =
      isset($_POST['faskes_status']) &&
      $_POST['faskes_status'] !== '' &&
      is_numeric($_POST['faskes_status'])
      ? (int) $_POST['faskes_status']
      : 0;

   $faskes_payment =
      postString('faskes_payment');

   $contract_date =
      postString('contract_date');

   $contract_amount =
      isset($_POST['contract_amount'])
      ? $_POST['contract_amount']
      : '';

   $contract_start =
      postString('contract_start');

   $contract_end =
      postString('contract_end');

   $contract_number =
      postString('contract_number');

   $order_number =
      postString('order_number');


   /*
    |--------------------------------------------------------------------------
    | NORMALISASI DATE
    |--------------------------------------------------------------------------
    */

   $contract_date =
      $contract_date !== ''
      ? $contract_date
      : null;

   $contract_start =
      $contract_start !== ''
      ? $contract_start
      : null;

   $contract_end =
      $contract_end !== ''
      ? $contract_end
      : null;


   /*
    |--------------------------------------------------------------------------
    | CONTRACT AMOUNT
    |--------------------------------------------------------------------------
    */

   if (
      $contract_amount === '' ||
      !is_numeric($contract_amount)
   ) {
      $contract_amount = 0;
   }

   $contract_amount =
      (float) $contract_amount;


   /*
    |--------------------------------------------------------------------------
    | ORDER NUMBER
    |--------------------------------------------------------------------------
    |
    | Jika kosong, gunakan id_clinic.
    |
    */

   if ($order_number === '') {
      $order_number =
         (string) $id_clinic;
   }


   /*
    |--------------------------------------------------------------------------
    | JIKA ID FASKES SUDAH ADA
    |--------------------------------------------------------------------------
    |
    | Bila form mengirim id_faskes, kita prioritaskan record tersebut,
    | tetapi tetap memastikan record itu milik id_clinic yang benar.
    |
    */

   if ($id_faskes > 0) {

      $stmtCheckId =
         $koneksi->prepare(
            "
                    SELECT
                        id_faskes,
                        id_clinic
                    FROM ms_faskes
                    WHERE id_faskes = ?
                    LIMIT 1
                "
         );

      if (!$stmtCheckId) {
         responseJson(
            'error',
            'Prepare cek ID faskes gagal: ' .
               $koneksi->error
         );
      }

      $stmtCheckId->bind_param(
         'i',
         $id_faskes
      );

      if (!$stmtCheckId->execute()) {
         responseJson(
            'error',
            'Gagal mengecek ID faskes: ' .
               $stmtCheckId->error
         );
      }

      $resultId =
         $stmtCheckId->get_result();

      if ($resultId->num_rows > 0) {

         $existingById =
            $resultId->fetch_assoc();

         /*
            |--------------------------------------------------------------------------
            | Pastikan ID faskes memang milik clinic ini.
            |--------------------------------------------------------------------------
            */

         if (
            (int) $existingById['id_clinic']
            === $id_clinic
         ) {

            $stmtCheckId->close();

            updateFaskesRecord(
               $koneksi,
               $id_faskes,
               $id_clinic,
               $id_customer,
               $faskes_code,
               $pic_name,
               $pic_phone,
               $pic_email,
               $faskes_address,
               $faskes_prov,
               $faskes_city,
               $faskes_district,
               $faskes_village,
               $faskes_status,
               $contract_date,
               $faskes_payment,
               $contract_amount,
               $contract_start,
               $contract_end,
               $contract_number,
               $order_number
            );
         }
      }

      $stmtCheckId->close();

      /*
        |--------------------------------------------------------------------------
        | Jika id_faskes dikirim tetapi record tidak ditemukan,
        | jangan gagal. Lanjutkan mencari berdasarkan id_clinic.
        |--------------------------------------------------------------------------
        */
   }


   /*
    |--------------------------------------------------------------------------
    | CEK MS_FASKES BERDASARKAN ID CLINIC
    |--------------------------------------------------------------------------
    */

   $stmtFaskes =
      $koneksi->prepare(
         "
                SELECT
                    id_faskes
                FROM ms_faskes
                WHERE id_clinic = ?
                LIMIT 1
            "
      );

   if (!$stmtFaskes) {
      responseJson(
         'error',
         'Prepare faskes gagal: ' .
            $koneksi->error
      );
   }

   $stmtFaskes->bind_param(
      'i',
      $id_clinic
   );

   if (!$stmtFaskes->execute()) {
      responseJson(
         'error',
         'Gagal mengecek data faskes: ' .
            $stmtFaskes->error
      );
   }

   $resultFaskes =
      $stmtFaskes->get_result();


   /*
    |--------------------------------------------------------------------------
    | BELUM ADA → INSERT
    |--------------------------------------------------------------------------
    */

   if ($resultFaskes->num_rows === 0) {

      $stmtFaskes->close();


      $stmtInsert =
         $koneksi->prepare(
            "
                    INSERT INTO ms_faskes
                    (
                        id_clinic,
                        faskes_code,
                        pic_name,
                        pic_phone,
                        pic_email,
                        faskes_address,
                        faskes_prov,
                        faskes_city,
                        faskes_district,
                        faskes_village,
                        faskes_status,
                        contract_date,
                        faskes_payment,
                        contract_amount,
                        contract_start,
                        contract_end,
                        contract_number,
                        order_number
                    )
                    VALUES
                    (
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?
                    )
                "
         );

      if (!$stmtInsert) {
         responseJson(
            'error',
            'Prepare INSERT faskes gagal: ' .
               $koneksi->error
         );
      }


      /*
        |--------------------------------------------------------------------------
        | 18 PARAMETER
        |--------------------------------------------------------------------------
        | i + 13s + d + 3s
        |
        | id_clinic       = i
        | 13 field        = s
        | contract_amount = d
        | 3 field terakhir = s
        |--------------------------------------------------------------------------
        */

      $insertValues = [
         $id_clinic,
         $faskes_code,
         $pic_name,
         $pic_phone,
         $pic_email,
         $faskes_address,
         $faskes_prov,
         $faskes_city,
         $faskes_district,
         $faskes_village,
         $faskes_status,
         $contract_date,
         $faskes_payment,
         $contract_amount,
         $contract_start,
         $contract_end,
         $contract_number,
         $order_number
      ];

      $insertTypes =
         'isssssssssissdssss';

      if (
         !bindDynamicParams(
            $stmtInsert,
            $insertTypes,
            $insertValues
         )
      ) {
         responseJson(
            'error',
            'Gagal bind parameter INSERT faskes.'
         );
      }

      if (!$stmtInsert->execute()) {
         responseJson(
            'error',
            'Gagal INSERT faskes: ' .
               $stmtInsert->error
         );
      }

      $newId =
         $stmtInsert->insert_id;

      $stmtInsert->close();

      responseJson(
         'success',
         'Data faskes berhasil disimpan.',
         null,
         [
            'action'      => 'insert',
            'id_faskes'   => $newId,
            'id_clinic'   => $id_clinic,
            'id_customer' => $id_customer,
            'order_number' => $order_number
         ]
      );
   }


   /*
    |--------------------------------------------------------------------------
    | SUDAH ADA → UPDATE
    |--------------------------------------------------------------------------
    */

   $existing =
      $resultFaskes->fetch_assoc();

   $id_faskes =
      (int) $existing['id_faskes'];

   $stmtFaskes->close();


   updateFaskesRecord(
      $koneksi,
      $id_faskes,
      $id_clinic,
      $id_customer,
      $faskes_code,
      $pic_name,
      $pic_phone,
      $pic_email,
      $faskes_address,
      $faskes_prov,
      $faskes_city,
      $faskes_district,
      $faskes_village,
      $faskes_status,
      $contract_date,
      $faskes_payment,
      $contract_amount,
      $contract_start,
      $contract_end,
      $contract_number,
      $order_number
   );
}


/*
|--------------------------------------------------------------------------
| UPDATE FASKES RECORD
|--------------------------------------------------------------------------
*/

function updateFaskesRecord(
   $koneksi,
   $id_faskes,
   $id_clinic,
   $id_customer,
   $faskes_code,
   $pic_name,
   $pic_phone,
   $pic_email,
   $faskes_address,
   $faskes_prov,
   $faskes_city,
   $faskes_district,
   $faskes_village,
   $faskes_status,
   $contract_date,
   $faskes_payment,
   $contract_amount,
   $contract_start,
   $contract_end,
   $contract_number,
   $order_number
) {

   $stmtUpdate =
      $koneksi->prepare(
         "
                UPDATE ms_faskes
                SET
                    id_clinic = ?,
                    faskes_code = ?,
                    pic_name = ?,
                    pic_phone = ?,
                    pic_email = ?,
                    faskes_address = ?,
                    faskes_prov = ?,
                    faskes_city = ?,
                    faskes_district = ?,
                    faskes_village = ?,
                    faskes_status = ?,
                    contract_date = ?,
                    faskes_payment = ?,
                    contract_amount = ?,
                    contract_start = ?,
                    contract_end = ?,
                    contract_number = ?,
                    order_number = ?
                WHERE id_faskes = ?
                LIMIT 1
            "
      );

   if (!$stmtUpdate) {
      responseJson(
         'error',
         'Prepare UPDATE faskes gagal: ' .
            $koneksi->error
      );
   }


   $values = [
      $id_clinic,
      $faskes_code,
      $pic_name,
      $pic_phone,
      $pic_email,
      $faskes_address,
      $faskes_prov,
      $faskes_city,
      $faskes_district,
      $faskes_village,
      $faskes_status,
      $contract_date,
      $faskes_payment,
      $contract_amount,
      $contract_start,
      $contract_end,
      $contract_number,
      $order_number,
      $id_faskes
   ];

   /*
    |--------------------------------------------------------------------------
    | 19 PARAMETER
    |--------------------------------------------------------------------------
    | 1 integer id_clinic
    | 13 string field
    | 1 decimal
    | 3 string field
    | 1 integer id_faskes
    |--------------------------------------------------------------------------
    */

   $types =
      'isssssssssissdssssi';

   if (
      !bindDynamicParams(
         $stmtUpdate,
         $types,
         $values
      )
   ) {
      responseJson(
         'error',
         'Gagal bind parameter UPDATE faskes.'
      );
   }


   if (!$stmtUpdate->execute()) {
      responseJson(
         'error',
         'Update faskes gagal: ' .
            $stmtUpdate->error
      );
   }


   $affected =
      $stmtUpdate->affected_rows;

   $stmtUpdate->close();


   responseJson(
      'success',
      'Data faskes berhasil diperbarui.',
      null,
      [
         'action'       => 'update',
         'id_faskes'    => $id_faskes,
         'id_clinic'    => $id_clinic,
         'id_customer'  => $id_customer,
         'order_number' => $order_number,
         'affected_rows' => $affected
      ]
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

   $query = "
        SELECT
            setting_clinic.*,

            ms_faskes.id_faskes,
            ms_faskes.id_clinic,
            ms_faskes.contract_number,
            ms_faskes.faskes_code,
            ms_faskes.pic_name,
            ms_faskes.pic_phone,
            ms_faskes.pic_email,
            ms_faskes.faskes_address,
            ms_faskes.faskes_prov,
            ms_faskes.faskes_city,
            ms_faskes.faskes_district,
            ms_faskes.faskes_village,
            ms_faskes.faskes_status,
            ms_faskes.contract_date,
            ms_faskes.faskes_payment,
            ms_faskes.contract_start,
            ms_faskes.contract_end,
            ms_faskes.contract_amount,
            ms_faskes.order_number,

            ms_users.fullname,
            ms_users.username,
            ms_users.password

        FROM setting_clinic

        LEFT JOIN ms_faskes
            ON setting_clinic.id =
               ms_faskes.id_clinic

        LEFT JOIN ms_users
            ON ms_users.id_customer =
               setting_clinic.id_customer
            AND ms_users.roles = 'admin'

        WHERE setting_clinic.status != 99

        GROUP BY setting_clinic.id

        ORDER BY setting_clinic.id DESC
    ";

   $result =
      mysqli_query(
         $koneksi,
         $query
      );

   if (!$result) {

      http_response_code(500);

      responseJson(
         'error',
         'Gagal mengambil data: ' .
            mysqli_error($koneksi)
      );
   }

   $data =
      mysqli_fetch_all(
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

      unset(
         $row['password']
      );
   }

   unset($row);


   responseJson(
      'success',
      '',
      $data
   );
}


/*
|--------------------------------------------------------------------------
| GET BY ID
|--------------------------------------------------------------------------
|
| ID = setting_clinic.id
|
| LEFT JOIN memastikan halaman Profile tetap bisa dibuka
| walaupun ms_faskes belum memiliki record.
|
*/

function getID($id)
{
   global $koneksi;

   $id =
      (int) $id;

   if ($id <= 0) {
      responseJson(
         'error',
         'ID tidak valid.'
      );
   }


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

   $stmt =
      $koneksi->prepare($query);

   if (!$stmt) {
      responseJson(
         'error',
         'Gagal menyiapkan query: ' .
            $koneksi->error
      );
   }

   $stmt->bind_param(
      'i',
      $id
   );

   if (!$stmt->execute()) {
      responseJson(
         'error',
         'Gagal mengambil data: ' .
            $stmt->error
      );
   }

   $result =
      $stmt->get_result();


   /*
    |--------------------------------------------------------------------------
    | IMPORTANT
    |--------------------------------------------------------------------------
    | LEFT JOIN:
    | setting_clinic tetap ditemukan meskipun ms_faskes kosong.
    |--------------------------------------------------------------------------
    */

   if ($result->num_rows > 0) {

      $data =
         $result->fetch_assoc();

      $stmt->close();

      responseJson(
         'success',
         'Data berhasil ditemukan.',
         $data
      );
   }


   $stmt->close();

   responseJson(
      'not_found',
      'Data clinic tidak ditemukan.'
   );
}


/*
|--------------------------------------------------------------------------
| UPDATE FASKES
|--------------------------------------------------------------------------
|
| PUT tetap dipertahankan.
|
| Jika ms_faskes ada:
|     UPDATE
|
| Jika belum ada:
|     INSERT
|
*/

function updateData()
{
   global $koneksi;


   /*
    |--------------------------------------------------------------------------
    | Ambil PUT
    |--------------------------------------------------------------------------
    */

   $rawInput =
      file_get_contents(
         'php://input'
      );

   $_PUT = [];

   parse_str(
      $rawInput,
      $_PUT
   );

   if (empty($_PUT)) {
      responseJson(
         'error',
         'Data PUT kosong.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | ID FASKES
    |--------------------------------------------------------------------------
    */

   $id_faskes = 0;

   if (
      isset($_PUT['id_faskes']) &&
      $_PUT['id_faskes'] !== '' &&
      ctype_digit((string) $_PUT['id_faskes'])
   ) {
      $id_faskes =
         (int) $_PUT['id_faskes'];
   }


   /*
    |--------------------------------------------------------------------------
    | ID CLINIC
    |--------------------------------------------------------------------------
    |
    | id_clinic harus berasal dari id_clinic.
    | Jika tidak ada, gunakan order_number.
    |
    */

   $id_clinic = 0;

   if (
      isset($_PUT['id_clinic']) &&
      $_PUT['id_clinic'] !== '' &&
      ctype_digit((string) $_PUT['id_clinic'])
   ) {
      $id_clinic =
         (int) $_PUT['id_clinic'];
   }

   if ($id_clinic <= 0) {

      $orderNumberInput =
         putString(
            $_PUT,
            'order_number'
         );

      if (
         $orderNumberInput !== '' &&
         ctype_digit($orderNumberInput)
      ) {
         $id_clinic =
            (int) $orderNumberInput;
      }
   }

   if ($id_clinic <= 0) {
      responseJson(
         'error',
         'ID Clinic / Order Number tidak ditemukan.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | CEK CLINIC
    |--------------------------------------------------------------------------
    */

   $stmtClinic =
      $koneksi->prepare(
         "
                SELECT
                    id,
                    id_customer
                FROM setting_clinic
                WHERE id = ?
                LIMIT 1
            "
      );

   if (!$stmtClinic) {
      responseJson(
         'error',
         'Prepare clinic gagal: ' .
            $koneksi->error
      );
   }

   $stmtClinic->bind_param(
      'i',
      $id_clinic
   );

   if (!$stmtClinic->execute()) {
      responseJson(
         'error',
         'Gagal mengecek clinic: ' .
            $stmtClinic->error
      );
   }

   $resultClinic =
      $stmtClinic->get_result();

   if ($resultClinic->num_rows === 0) {

      $stmtClinic->close();

      responseJson(
         'error',
         'Data clinic tidak ditemukan.'
      );
   }

   $clinic =
      $resultClinic->fetch_assoc();

   $id_customer =
      $clinic['id_customer'] ?? null;

   $stmtClinic->close();


   /*
    |--------------------------------------------------------------------------
    | AMBIL FIELD PUT
    |--------------------------------------------------------------------------
    */

   $fields = [
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
      'contract_number',
      'order_number'
   ];

   $data = [];

   foreach ($fields as $field) {

      if (
         array_key_exists(
            $field,
            $_PUT
         )
      ) {
         $data[$field] =
            $_PUT[$field];
      }
   }


   /*
    |--------------------------------------------------------------------------
    | DEFAULT ORDER NUMBER
    |--------------------------------------------------------------------------
    */

   if (
      !isset($data['order_number']) ||
      trim(
         (string) $data['order_number']
      ) === ''
   ) {
      $data['order_number'] =
         (string) $id_clinic;
   }


   /*
    |--------------------------------------------------------------------------
    | NORMALISASI
    |--------------------------------------------------------------------------
    */

   foreach (
      [
         'contract_date',
         'contract_start',
         'contract_end'
      ] as $dateField
   ) {

      if (
         isset($data[$dateField]) &&
         trim((string) $data[$dateField]) === ''
      ) {
         $data[$dateField] = null;
      }
   }


   if (
      !isset($data['contract_amount']) ||
      $data['contract_amount'] === '' ||
      !is_numeric($data['contract_amount'])
   ) {
      $data['contract_amount'] = 0;
   }

   $data['contract_amount'] =
      (float) $data['contract_amount'];


   /*
    |--------------------------------------------------------------------------
    | FASKES STATUS
    |--------------------------------------------------------------------------
    | Kolom faskes_status bertipe INTEGER.
    |--------------------------------------------------------------------------
    */
   if (
      !isset($data['faskes_status']) ||
      $data['faskes_status'] === '' ||
      !is_numeric($data['faskes_status'])
   ) {
      $data['faskes_status'] = 0;
   } else {
      $data['faskes_status'] =
         (int) $data['faskes_status'];
   }


   /*
    |--------------------------------------------------------------------------
    | Jika PUT memiliki id_faskes, cek record tersebut.
    |--------------------------------------------------------------------------
    */

   if ($id_faskes > 0) {

      $stmtCheckId =
         $koneksi->prepare(
            "
                    SELECT
                        id_faskes,
                        id_clinic
                    FROM ms_faskes
                    WHERE id_faskes = ?
                    LIMIT 1
                "
         );

      if (!$stmtCheckId) {
         responseJson(
            'error',
            'Prepare cek ID faskes gagal: ' .
               $koneksi->error
         );
      }

      $stmtCheckId->bind_param(
         'i',
         $id_faskes
      );

      $stmtCheckId->execute();

      $resultId =
         $stmtCheckId->get_result();

      if ($resultId->num_rows > 0) {

         $existingById =
            $resultId->fetch_assoc();

         if (
            (int) $existingById['id_clinic']
            === $id_clinic
         ) {

            $stmtCheckId->close();

            updateFaskesFromPut(
               $koneksi,
               $id_faskes,
               $id_clinic,
               $id_customer,
               $data
            );
         }
      }

      $stmtCheckId->close();
   }


   /*
    |--------------------------------------------------------------------------
    | CEK BERDASARKAN ID CLINIC
    |--------------------------------------------------------------------------
    */

   $stmtCheck =
      $koneksi->prepare(
         "
                SELECT
                    id_faskes
                FROM ms_faskes
                WHERE id_clinic = ?
                LIMIT 1
            "
      );

   if (!$stmtCheck) {
      responseJson(
         'error',
         'Prepare cek faskes gagal: ' .
            $koneksi->error
      );
   }

   $stmtCheck->bind_param(
      'i',
      $id_clinic
   );

   $stmtCheck->execute();

   $resultCheck =
      $stmtCheck->get_result();


   /*
    |--------------------------------------------------------------------------
    | BELUM ADA → INSERT
    |--------------------------------------------------------------------------
    */

   if ($resultCheck->num_rows === 0) {

      $stmtCheck->close();

      insertFaskesFromPut(
         $koneksi,
         $id_clinic,
         $id_customer,
         $data
      );
   }


   /*
    |--------------------------------------------------------------------------
    | SUDAH ADA → UPDATE
    |--------------------------------------------------------------------------
    */

   $existing =
      $resultCheck->fetch_assoc();

   $id_faskes =
      (int) $existing['id_faskes'];

   $stmtCheck->close();


   updateFaskesFromPut(
      $koneksi,
      $id_faskes,
      $id_clinic,
      $id_customer,
      $data
   );
}


/*
|--------------------------------------------------------------------------
| INSERT FASKES DARI PUT
|--------------------------------------------------------------------------
*/

function insertFaskesFromPut(
   $koneksi,
   $id_clinic,
   $id_customer,
   $data
) {

   $insertData = [
      $data['faskes_code'] ?? '',
      $data['pic_name'] ?? '',
      $data['pic_phone'] ?? '',
      $data['pic_email'] ?? '',
      $data['faskes_address'] ?? '',
      $data['faskes_prov'] ?? '',
      $data['faskes_city'] ?? '',
      $data['faskes_district'] ?? '',
      $data['faskes_village'] ?? '',
      $data['faskes_status'] ?? '',
      $data['contract_date'] ?? null,
      $data['faskes_payment'] ?? '',
      $data['contract_amount'] ?? 0,
      $data['contract_start'] ?? null,
      $data['contract_end'] ?? null,
      $data['contract_number'] ?? '',
      $data['order_number'] ?? (string) $id_clinic
   ];

   $stmt =
      $koneksi->prepare(
         "
                INSERT INTO ms_faskes
                (
                    id_clinic,
                    faskes_code,
                    pic_name,
                    pic_phone,
                    pic_email,
                    faskes_address,
                    faskes_prov,
                    faskes_city,
                    faskes_district,
                    faskes_village,
                    faskes_status,
                    contract_date,
                    faskes_payment,
                    contract_amount,
                    contract_start,
                    contract_end,
                    contract_number,
                    order_number
                )
                VALUES
                (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?
                )
            "
      );

   if (!$stmt) {
      responseJson(
         'error',
         'Prepare INSERT faskes gagal: ' .
            $koneksi->error
      );
   }

   $values = array_merge(
      [$id_clinic],
      $insertData
   );

   $types =
      'isssssssssissdssss';

   bindDynamicParams(
      $stmt,
      $types,
      $values
   );

   if (!$stmt->execute()) {
      responseJson(
         'error',
         'Gagal INSERT faskes: ' .
            $stmt->error
      );
   }

   $newId =
      $stmt->insert_id;

   $stmt->close();

   responseJson(
      'success',
      'Data faskes berhasil disimpan.',
      null,
      [
         'action'       => 'insert',
         'id_faskes'    => $newId,
         'id_clinic'    => $id_clinic,
         'id_customer'  => $id_customer,
         'order_number' => $data['order_number']
      ]
   );
}


/*
|--------------------------------------------------------------------------
| UPDATE FASKES DARI PUT
|--------------------------------------------------------------------------
*/

function updateFaskesFromPut(
   $koneksi,
   $id_faskes,
   $id_clinic,
   $id_customer,
   $data
) {

   if ($id_faskes <= 0) {
      responseJson(
         'error',
         'ID faskes tidak valid.'
      );
   }


   $set = [];
   $values = [];
   $types = '';


   foreach ($data as $field => $value) {

      if (
         !in_array(
            $field,
            [
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
               'contract_number',
               'order_number'
            ],
            true
         )
      ) {
         continue;
      }

      $set[] =
         "$field = ?";

      $values[] =
         $value;

      if ($field === 'contract_amount') {
         $types .= 'd';
      } else {
         $types .= 's';
      }
   }


   /*
    |--------------------------------------------------------------------------
    | Jika tidak ada field profile yang dikirim,
    | tetap tidak merusak data.
    |--------------------------------------------------------------------------
    */

   if (empty($set)) {

      responseJson(
         'success',
         'Tidak ada perubahan data faskes.',
         null,
         [
            'action'       => 'no_change',
            'id_faskes'    => $id_faskes,
            'id_clinic'    => $id_clinic,
            'id_customer'  => $id_customer,
            'order_number' =>
            $data['order_number'] ??
               (string) $id_clinic
         ]
      );
   }


   $values[] =
      $id_faskes;

   $types .= 'i';


   $query = "
        UPDATE ms_faskes
        SET
            " .
      implode(
         ', ',
         $set
      ) .
      "
        WHERE id_faskes = ?
        LIMIT 1
    ";

   $stmt =
      $koneksi->prepare($query);

   if (!$stmt) {
      responseJson(
         'error',
         'Prepare UPDATE gagal: ' .
            $koneksi->error
      );
   }

   bindDynamicParams(
      $stmt,
      $types,
      $values
   );

   if (!$stmt->execute()) {
      responseJson(
         'error',
         'Update faskes gagal: ' .
            $stmt->error
      );
   }

   $affected =
      $stmt->affected_rows;

   $stmt->close();

   responseJson(
      'success',
      'Data faskes berhasil diperbarui.',
      null,
      [
         'action'        => 'update',
         'id_faskes'     => $id_faskes,
         'id_clinic'     => $id_clinic,
         'id_customer'   => $id_customer,
         'order_number'  =>
         $data['order_number'] ??
            (string) $id_clinic,
         'affected_rows' => $affected
      ]
   );
}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
|
| Soft delete setting_clinic.
| Fungsi lama dipertahankan.
|
*/

function deleteData()
{
   global $koneksi;

   $id =
      $_GET['id']
      ?? '';

   if ($id === '') {
      responseJson(
         'error',
         'ID tidak ditemukan.'
      );
   }

   $id =
      (int) $id;

   if ($id <= 0) {
      responseJson(
         'error',
         'ID tidak valid.'
      );
   }


   $query = "
        UPDATE setting_clinic
        SET status = 99
        WHERE id = ?
    ";

   $stmt =
      $koneksi->prepare($query);

   if (!$stmt) {
      responseJson(
         'error',
         'Gagal menyiapkan query: ' .
            $koneksi->error
      );
   }

   $stmt->bind_param(
      'i',
      $id
   );

   if (!$stmt->execute()) {
      responseJson(
         'error',
         'Gagal menghapus: ' .
            $stmt->error
      );
   }

   if ($stmt->affected_rows > 0) {

      $stmt->close();

      responseJson(
         'success',
         'Data berhasil dihapus.'
      );
   }

   $stmt->close();

   responseJson(
      'error',
      'Data tidak ditemukan.'
   );
}
