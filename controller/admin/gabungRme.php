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
//
// ALUR:
//
// 1. User mencari berdasarkan:
//    - nomor RM
//    - nama
//    - NIK
//    - nomor kartu
//    - KTP
//    - BPJS
//
// 2. Sistem mencari DATA AWAL berdasarkan customer aktif.
//
// 3. Dari data awal mendapatkan:
//    - patient_nik
//    - patient_number
//
// 4. Sistem mencari SELURUH ms_patient
//    yang mempunyai NIK / nomor kartu yang sama.
//
// 5. Tidak dibatasi id_customer pada tahap kedua.
//
// Contoh:
//
// Cari:
// DEDY ZAKARIA PULUNGAN
//
// Data awal:
// 55003
// customer 1
//
// Identitas:
// NIK       = 1271091605990002
// No Kartu  = 0001296157443
//
// Kemudian dicari seluruh ms_patient:
//
// 55003   customer 1
// 200535  customer 3
// 205560  customer 3
// 206541  customer 36
//
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


   $likeKeyword = '%' . $keyword . '%';


   // ========================================================
   // STEP 1
   // CARI DATA AWAL
   // ========================================================
   //
   // Tahap pertama tetap menggunakan id_customer aktif.
   //
   // Tujuannya supaya pencarian awal tetap berasal
   // dari customer yang sedang login.
   //
   // Setelah NIK / nomor kartu ditemukan,
   // tahap kedua akan mencari lintas customer.
   //
   // ========================================================

   $sqlSeed = "

        SELECT DISTINCT

            patient_nik,
            patient_number

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

        LIMIT 100

    ";


   $stmtSeed = mysqli_prepare(
      $koneksi,
      $sqlSeed
   );


   if (!$stmtSeed) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menyiapkan pencarian pasien: ' . mysqli_error($koneksi)
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   mysqli_stmt_bind_param(
      $stmtSeed,
      'issssss',
      $id_customer,
      $likeKeyword,
      $likeKeyword,
      $likeKeyword,
      $likeKeyword,
      $likeKeyword,
      $likeKeyword
   );


   if (!mysqli_stmt_execute($stmtSeed)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menjalankan pencarian pasien: ' . mysqli_stmt_error($stmtSeed)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmtSeed);

      return;
   }


   $resultSeed = mysqli_stmt_get_result($stmtSeed);


   if (!$resultSeed) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal membaca hasil pencarian pasien.'
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmtSeed);

      return;
   }


   // ========================================================
   // AMBIL SEMUA IDENTITAS
   // ========================================================

   $nikList = [];

   $noKartuList = [];


   while (
      $row = mysqli_fetch_assoc($resultSeed)
   ) {

      $nik = trim(
         (string)($row['patient_nik'] ?? '')
      );

      $noKartu = trim(
         (string)($row['patient_number'] ?? '')
      );


      // ====================================================
      // NIK
      // ====================================================

      if (
         $nik !== '' &&
         !in_array($nik, $nikList, true)
      ) {

         $nikList[] = $nik;
      }


      // ====================================================
      // NOMOR KARTU
      // ====================================================

      if (
         $noKartu !== '' &&
         !in_array($noKartu, $noKartuList, true)
      ) {

         $noKartuList[] = $noKartu;
      }
   }


   mysqli_stmt_close($stmtSeed);


   // ========================================================
   // TIDAK ADA HASIL
   // ========================================================

   if (
      empty($nikList) &&
      empty($noKartuList)
   ) {

      echo json_encode([
         'status' => 'success',
         'data'   => [],
         'total'  => 0
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // STEP 2
   // CARI SEMUA DATA BERDASARKAN IDENTITAS
   // ========================================================
   //
   // PENTING:
   //
   // DI SINI TIDAK ADA:
   //
   //     id_customer = ?
   //
   // Karena tujuan Gabung RME adalah mencari seluruh
   // record pasien yang memiliki identitas sama.
   //
   // ========================================================

   $conditions = [];

   $params = [];

   $types = '';


   // ========================================================
   // CONDITION NIK
   // ========================================================

   if (!empty($nikList)) {

      $placeholders = implode(
         ',',
         array_fill(
            0,
            count($nikList),
            '?'
         )
      );


      $conditions[] = "

            (

                patient_nik IN ($placeholders)

                AND patient_nik IS NOT NULL

                AND TRIM(patient_nik) <> ''

            )

        ";


      foreach ($nikList as $nik) {

         $params[] = $nik;

         $types .= 's';
      }
   }


   // ========================================================
   // CONDITION NOMOR KARTU
   // ========================================================

   if (!empty($noKartuList)) {

      $placeholders = implode(
         ',',
         array_fill(
            0,
            count($noKartuList),
            '?'
         )
      );


      $conditions[] = "

            (

                patient_number IN ($placeholders)

                AND patient_number IS NOT NULL

                AND TRIM(patient_number) <> ''

            )

        ";


      foreach ($noKartuList as $noKartu) {

         $params[] = $noKartu;

         $types .= 's';
      }
   }


   // ========================================================
   // SAFETY
   // ========================================================

   if (empty($conditions)) {

      echo json_encode([
         'status' => 'success',
         'data'   => [],
         'total'  => 0
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   $whereIdentity = implode(
      ' OR ',
      $conditions
   );


   // ========================================================
   // FINAL QUERY
   // ========================================================
   //
   // TIDAK menggunakan id_customer.
   //
   // Sehingga:
   //
   // 55003
   // 200535
   // 205560
   // 206541
   //
   // semuanya dapat ditampilkan.
   //
   // ========================================================

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
            patient_phone,
            id_customer

        FROM ms_patient

        WHERE

            (

                $whereIdentity

            )

        ORDER BY

            patient_name ASC,

            id_patient ASC

        LIMIT 500

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
         'message' => 'Gagal menyiapkan query identitas pasien: ' . mysqli_error($koneksi)
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // BIND DINAMIS
   // ========================================================

   mysqli_stmt_bind_param(
      $stmt,
      $types,
      ...$params
   );


   // ========================================================
   // EXECUTE
   // ========================================================

   if (!mysqli_stmt_execute($stmt)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mengambil seluruh data pasien: ' . mysqli_stmt_error($stmt)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmt);

      return;
   }


   // ========================================================
   // RESULT
   // ========================================================

   $result = mysqli_stmt_get_result($stmt);


   if (!$result) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal membaca hasil pasien.'
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

         'id_customer' =>
         $row['id_customer'],

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
//
// ALUR:
//
// User memilih salah satu patient:
//
//     id_patient = 55003
//
// Sistem mengambil identitas:
//
//     NIK       = 1271091605990002
//     No Kartu  = 0001296157443
//
// Kemudian:
//
//     ms_patient
//
// dicari berdasarkan:
//
//     patient_nik
//     ATAU
//     patient_number
//
// TANPA membatasi id_customer.
//
// Setelah mendapatkan semua id_patient:
//
//     55003
//     200535
//     205560
//     206541
//
// Semua visit dari pasien tersebut ditampilkan.
//
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
   // Source tetap harus berasal dari customer aktif.
   //
   // Ini hanya untuk memastikan pasien yang dipilih
   // memang berasal dari hasil pencarian awal.
   //
   // Setelah identitas ditemukan, pencarian visit
   // dilakukan lintas customer.
   //
   // ========================================================

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


   $result = mysqli_stmt_get_result($stmt);


   $source = mysqli_fetch_assoc($result);


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
   // BUILD IDENTITY CONDITION
   // ========================================================

   $identityConditions = [];

   $identityParams = [];

   $identityTypes = '';


   // ========================================================
   // MATCH NIK
   // ========================================================

   if ($nik !== '') {

      $identityConditions[] = "

            (

                patient_nik = ?

                AND patient_nik IS NOT NULL

                AND TRIM(patient_nik) <> ''

            )

        ";

      $identityParams[] = $nik;

      $identityTypes .= 's';
   }


   // ========================================================
   // MATCH NOMOR KARTU
   // ========================================================

   if ($noKartu !== '') {

      $identityConditions[] = "

            (

                patient_number = ?

                AND patient_number IS NOT NULL

                AND TRIM(patient_number) <> ''

            )

        ";

      $identityParams[] = $noKartu;

      $identityTypes .= 's';
   }


   if (empty($identityConditions)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Tidak ada parameter identitas untuk pencarian RME.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   $whereIdentity = implode(
      ' OR ',
      $identityConditions
   );


   // ========================================================
   // GET ALL RELATED PATIENTS
   // ========================================================
   //
   // Ini mengambil seluruh ms_patient yang mempunyai
   // NIK / nomor kartu sama.
   //
   // TIDAK ADA FILTER id_customer.
   //
   // ========================================================

   $sqlPatients = "

        SELECT

            id_patient,
            id_customer,
            nomor_rm,
            patient_name,
            patient_nik,
            patient_number

        FROM ms_patient

        WHERE

            (

                $whereIdentity

            )

        ORDER BY

            id_patient ASC

    ";


   $stmtPatients = mysqli_prepare(
      $koneksi,
      $sqlPatients
   );


   if (!$stmtPatients) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menyiapkan query pasien terkait: ' . mysqli_error($koneksi)
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   mysqli_stmt_bind_param(
      $stmtPatients,
      $identityTypes,
      ...$identityParams
   );


   if (!mysqli_stmt_execute($stmtPatients)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mengambil pasien terkait: ' . mysqli_stmt_error($stmtPatients)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmtPatients);

      return;
   }


   $resultPatients =
      mysqli_stmt_get_result($stmtPatients);


   if (!$resultPatients) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal membaca pasien terkait.'
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmtPatients);

      return;
   }


   $relatedPatients = [];

   $patientIds = [];


   while (
      $patient = mysqli_fetch_assoc($resultPatients)
   ) {

      $patientId =
         (int)$patient['id_patient'];


      $patientIds[] =
         $patientId;


      $relatedPatients[] = [

         'id_patient' =>
         $patientId,

         'id_customer' =>
         $patient['id_customer'],

         'nomor_rm' =>
         $patient['nomor_rm'],

         'patient_name' =>
         $patient['patient_name'],

         'patient_nik' =>
         $patient['patient_nik'],

         'patient_number' =>
         $patient['patient_number']

      ];
   }


   mysqli_stmt_close($stmtPatients);


   // ========================================================
   // TIDAK ADA PASIEN TERKAIT
   // ========================================================

   if (empty($patientIds)) {

      echo json_encode([

         'status' =>
         'success',

         'source_patient' =>
         $source,

         'related_patients' =>
         [],

         'total_related_patient' =>
         0,

         'total_visit' =>
         0,

         'visits' =>
         []

      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // BUILD ID PATIENT CONDITION
   // ========================================================

   $visitPlaceholders = implode(
      ',',
      array_fill(
         0,
         count($patientIds),
         '?'
      )
   );


   $visitTypes = str_repeat(
      'i',
      count($patientIds)
   );


   $visitParams =
      $patientIds;


   // ========================================================
   // GET VISIT
   // ========================================================
   //
   // Semua visit dari seluruh id_patient yang mempunyai
   // identitas sama.
   //
   // Tidak ada filter id_customer.
   //
   // visit_status 99 = BATAL
   //
   // ========================================================

   $sqlVisits = "

        SELECT

            pv.visit_ID,

            pv.visit_date,

            pv.visit_status,

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

            pv.id_patient IN ($visitPlaceholders)

            AND pv.visit_status <> 99

        ORDER BY

            pv.visit_date DESC,

            pv.visit_ID DESC

    ";


   $stmtVisits = mysqli_prepare(
      $koneksi,
      $sqlVisits
   );


   if (!$stmtVisits) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menyiapkan query visit: ' . mysqli_error($koneksi)
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // BIND ID PATIENT
   // ========================================================

   mysqli_stmt_bind_param(
      $stmtVisits,
      $visitTypes,
      ...$visitParams
   );


   // ========================================================
   // EXECUTE
   // ========================================================

   if (!mysqli_stmt_execute($stmtVisits)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mengambil visit: ' . mysqli_stmt_error($stmtVisits)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmtVisits);

      return;
   }


   // ========================================================
   // RESULT
   // ========================================================

   $resultVisits =
      mysqli_stmt_get_result($stmtVisits);


   if (!$resultVisits) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal membaca hasil visit.'
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmtVisits);

      return;
   }


   $visits = [];


   // ========================================================
   // LOOP VISIT
   // ========================================================

   while (
      $row = mysqli_fetch_assoc($resultVisits)
   ) {

      $visits[] = [

         'visit_ID' =>
         $row['visit_ID'],

         'visit_date' =>
         $row['visit_date'],

         'visit_status' =>
         $row['visit_status'],

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


   mysqli_stmt_close($stmtVisits);


   // ========================================================
   // RESPONSE
   // ========================================================

   echo json_encode([

      'status' =>
      'success',

      'source_patient' =>
      $source,

      'identity' => [

         'patient_nik' =>
         $nik,

         'patient_number' =>
         $noKartu

      ],

      'related_patients' =>
      $relatedPatients,

      'total_related_patient' =>
      count($relatedPatients),

      'total_visit' =>
      count($visits),

      'visits' =>
      $visits

   ], JSON_UNESCAPED_UNICODE);
}
