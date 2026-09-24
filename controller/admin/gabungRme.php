<?php

require '../../database/connect.php';

header('Content-Type: application/json; charset=utf-8');


// ============================================================
// SESSION CUSTOMER
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
   session_start();
}


$id_customer = $_SESSION['id_customer'] ?? '';


if (
   $id_customer === '' ||
   !is_numeric($id_customer)
) {

   echo json_encode([
      'status'  => 'error',
      'message' => 'Session customer tidak ditemukan.'
   ], JSON_UNESCAPED_UNICODE);

   exit;
}


$id_customer = (int) $id_customer;


// ============================================================
// ACTION
// ============================================================

$action = $_GET['action'] ?? '';


// ============================================================
// SEARCH PATIENT
// ============================================================

if ($action === 'patients') {

   getPatients($id_customer);

   exit;
}


// ============================================================
// SEARCH RME
// ============================================================

if ($action === 'search') {

   searchRme($id_customer);

   exit;
}


// ============================================================
// INVALID ACTION
// ============================================================

echo json_encode([
   'status'  => 'error',
   'message' => 'Action tidak valid.'
], JSON_UNESCAPED_UNICODE);

exit;


// ============================================================
// FUNCTION : GET PATIENTS
// ============================================================

function getPatients($id_customer)
{
   global $koneksi;


   // ========================================================
   // KEYWORD
   // ========================================================

   $keyword = trim(
      (string)($_GET['keyword'] ?? '')
   );


   if ($keyword === '') {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Kata kunci pencarian wajib diisi.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // SEARCH PATIENT
   // ========================================================
   //
   // Search berdasarkan:
   //
   // - Nomor RM
   // - Nama
   // - NIK
   // - Nomor Kartu
   // - KTP
   // - BPJS
   //
   // WAJIB berdasarkan id_customer
   //


   $sql = "

        SELECT

            id_patient,
            nomor_rm,
            patient_name,
            patient_nik,
            patient_number,
            patient_ktp,
            patient_bpjs,
            patient_datebirth,
            patient_gender,
            patient_address,
            patient_phone

        FROM ms_patient

        WHERE

            id_customer = ?

            AND

            (

                nomor_rm LIKE ?

                OR patient_name LIKE ?

                OR patient_nik LIKE ?

                OR patient_number LIKE ?

                OR patient_ktp LIKE ?

                OR patient_bpjs LIKE ?

            )

        ORDER BY

            CASE

                WHEN nomor_rm = ? THEN 1

                WHEN patient_nik = ? THEN 2

                WHEN patient_number = ? THEN 3

                WHEN patient_ktp = ? THEN 4

                WHEN patient_bpjs = ? THEN 5

                ELSE 6

            END,

            patient_name ASC

        LIMIT 100

    ";


   // ========================================================
   // PREPARE
   // ========================================================

   $stmt = mysqli_prepare(
      $koneksi,
      $sql
   );


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menyiapkan query: ' . mysqli_error($koneksi)
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // PARAMETER
   // ========================================================

   $likeKeyword =
      '%' . $keyword . '%';


   /*
    |--------------------------------------------------------------------------
    | Parameter:
    |
    | 1  id_customer
    |
    | 2-7  LIKE
    |     nomor_rm
    |     patient_name
    |     patient_nik
    |     patient_number
    |     patient_ktp
    |     patient_bpjs
    |
    | 8-12 exact match ORDER BY
    |--------------------------------------------------------------------------
    */


   mysqli_stmt_bind_param(

      $stmt,

      'isssssssssss',

      $id_customer,

      $likeKeyword,
      $likeKeyword,
      $likeKeyword,
      $likeKeyword,
      $likeKeyword,
      $likeKeyword,

      $keyword,
      $keyword,
      $keyword,
      $keyword,
      $keyword

   );


   // ========================================================
   // EXECUTE
   // ========================================================

   if (!mysqli_stmt_execute($stmt)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menjalankan pencarian: ' . mysqli_stmt_error($stmt)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmt);

      return;
   }


   // ========================================================
   // RESULT
   // ========================================================

   $result =
      mysqli_stmt_get_result($stmt);


   if (!$result) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mengambil hasil pencarian: ' . mysqli_stmt_error($stmt)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmt);

      return;
   }


   // ========================================================
   // DATA
   // ========================================================

   $data = [];


   while (
      $row = mysqli_fetch_assoc($result)
   ) {

      $data[] = [

         'id_patient' =>
         $row['id_patient'],

         'nomor_rm' =>
         $row['nomor_rm'],

         'patient_name' =>
         $row['patient_name'],

         'patient_nik' =>
         $row['patient_nik'],

         'patient_number' =>
         $row['patient_number'],

         'patient_ktp' =>
         $row['patient_ktp'],

         'patient_bpjs' =>
         $row['patient_bpjs'],

         'patient_datebirth' =>
         $row['patient_datebirth'],

         'patient_gender' =>
         $row['patient_gender'],

         'patient_address' =>
         $row['patient_address'],

         'patient_phone' =>
         $row['patient_phone']

      ];
   }


   mysqli_stmt_close($stmt);


   // ========================================================
   // RESPONSE
   // ========================================================

   echo json_encode([

      'status' =>
      'success',

      'data' =>
      $data,

      'total' =>
      count($data)

   ], JSON_UNESCAPED_UNICODE);
}


// ============================================================
// FUNCTION : SEARCH RME
// ============================================================

function searchRme($id_customer)
{
   global $koneksi;


   // ========================================================
   // ID PATIENT
   // ========================================================

   $idPatient = trim(
      (string)($_GET['id_patient'] ?? '')
   );


   if (
      $idPatient === '' ||
      !ctype_digit($idPatient)
   ) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'ID pasien tidak valid.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   $idPatient = (int) $idPatient;


   // ========================================================
   // SOURCE PATIENT
   // ========================================================
   //
   // PENTING:
   // id_patient HARUS milik id_customer session.
   //


   $stmt = mysqli_prepare(
      $koneksi,

      "

        SELECT

            id_patient,
            id_customer,
            nomor_rm,
            patient_name,
            patient_nik,
            patient_number,
            patient_ktp,
            patient_bpjs,
            patient_datebirth,
            patient_gender,
            patient_address,
            patient_phone

        FROM ms_patient

        WHERE

            id_patient = ?

            AND id_customer = ?

        LIMIT 1

        "
   );


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menyiapkan query pasien: ' . mysqli_error($koneksi)
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   mysqli_stmt_bind_param(

      $stmt,

      'ii',

      $idPatient,
      $id_customer

   );


   if (!mysqli_stmt_execute($stmt)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mengambil pasien sumber: ' . mysqli_stmt_error($stmt)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmt);

      return;
   }


   $result =
      mysqli_stmt_get_result($stmt);


   $source =
      mysqli_fetch_assoc($result);


   mysqli_stmt_close($stmt);


   // ========================================================
   // SOURCE NOT FOUND
   // ========================================================

   if (!$source) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Data pasien tidak ditemukan atau bukan milik customer ini.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // IDENTITAS PASIEN
   // ========================================================

   $nik = trim(
      (string)($source['patient_nik'] ?? '')
   );


   $noKartu = trim(
      (string)($source['patient_number'] ?? '')
   );


   // ========================================================
   // VALIDASI IDENTITAS
   // ========================================================

   if (
      $nik === '' &&
      $noKartu === ''
   ) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Pasien sumber tidak memiliki NIK maupun Nomor Kartu.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // BUILD MATCH CONDITION
   // ========================================================

   $conditions = [];

   $params = [];

   $types = '';


   // ========================================================
   // MATCH NIK
   // ========================================================

   if ($nik !== '') {

      $conditions[] = "

            (

                mp.patient_nik = ?

                AND mp.patient_nik IS NOT NULL

                AND TRIM(mp.patient_nik) <> ''

            )

        ";

      $params[] = $nik;

      $types .= 's';
   }


   // ========================================================
   // MATCH NOMOR KARTU
   // ========================================================

   if ($noKartu !== '') {

      $conditions[] = "

            (

                mp.patient_number = ?

                AND mp.patient_number IS NOT NULL

                AND TRIM(mp.patient_number) <> ''

            )

        ";

      $params[] = $noKartu;

      $types .= 's';
   }


   // ========================================================
   // JIKA TIDAK ADA CONDITION
   // ========================================================

   if (empty($conditions)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Tidak ada parameter identitas untuk pencarian RME.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   $whereMatch =
      implode(
         ' OR ',
         $conditions
      );


   // ========================================================
   // GET VISIT
   // ========================================================
   //
   // PENTING:
   //
   // mp.id_customer = ?
   //
   // Jadi walaupun NIK / nomor kartu sama,
   // data customer lain TIDAK ikut masuk.
   //


   $sql = "

        SELECT

            pv.visit_ID,

            pv.visit_date,

            pv.id_patient,

            pv.id_doctor,

            pv.id_poli,

            mp.id_customer,

            mp.nomor_rm,

            mp.patient_name,

            mp.patient_nik,

            mp.patient_number

        FROM pasien_visit pv

        INNER JOIN ms_patient mp

            ON mp.id_patient = pv.id_patient

        WHERE

            mp.id_customer = ?

            AND

            (

                $whereMatch

            )

        ORDER BY

            pv.visit_date DESC,

            pv.visit_ID DESC

    ";


   // ========================================================
   // PREPARE
   // ========================================================

   $stmt = mysqli_prepare(
      $koneksi,
      $sql
   );


   if (!$stmt) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menyiapkan query visit: ' . mysqli_error($koneksi)
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // BIND DINAMIS
   // ========================================================
   //
   // Parameter pertama:
   // id_customer
   //
   // Setelah itu:
   // NIK dan/atau nomor kartu
   //


   $bindTypes =
      'i' . $types;


   $bindParams =
      array_merge(
         [$id_customer],
         $params
      );


   mysqli_stmt_bind_param(
      $stmt,
      $bindTypes,
      ...$bindParams
   );


   // ========================================================
   // EXECUTE
   // ========================================================

   if (!mysqli_stmt_execute($stmt)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mengambil visit: ' . mysqli_stmt_error($stmt)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmt);

      return;
   }


   // ========================================================
   // RESULT
   // ========================================================

   $result =
      mysqli_stmt_get_result($stmt);


   if (!$result) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal membaca hasil visit: ' . mysqli_stmt_error($stmt)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmt);

      return;
   }


   $visits = [];


   // ========================================================
   // LOOP VISIT
   // ========================================================

   while (
      $row = mysqli_fetch_assoc($result)
   ) {

      $visits[] = [

         'visit_ID' =>
         $row['visit_ID'],

         'visit_date' =>
         $row['visit_date'],

         'id_patient' =>
         $row['id_patient'],

         'id_doctor' =>
         $row['id_doctor'],

         'id_poli' =>
         $row['id_poli'],

         'id_customer' =>
         $row['id_customer'],

         'nomor_rm' =>
         $row['nomor_rm'],

         'patient_name' =>
         $row['patient_name'],

         'patient_nik' =>
         $row['patient_nik'],

         'patient_number' =>
         $row['patient_number']

      ];
   }


   mysqli_stmt_close($stmt);


   // ========================================================
   // RESPONSE
   // ========================================================

   echo json_encode([

      'status' =>
      'success',

      'source_patient' =>
      $source,

      'total_visit' =>
      count($visits),

      'visits' =>
      $visits

   ], JSON_UNESCAPED_UNICODE);
}
