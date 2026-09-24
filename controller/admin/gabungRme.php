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
// GABUNG RME
// ============================================================

if ($action === 'gabung') {

   gabungRme($id_customer);

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
// 5. Tahap kedua TIDAK dibatasi id_customer.
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
   // Pencarian awal tetap menggunakan customer aktif.
   //
   // Setelah mendapatkan NIK / nomor kartu,
   // pencarian tahap kedua dilakukan lintas customer.
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


   $resultSeed =
      mysqli_stmt_get_result($stmtSeed);


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
   // CARI SELURUH DATA BERDASARKAN IDENTITAS
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


   $whereIdentity =
      implode(
         ' OR ',
         $conditions
      );


   // ========================================================
   // FINAL QUERY
   // ========================================================
   //
   // TIDAK menggunakan id_customer.
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


   mysqli_stmt_bind_param(
      $stmt,
      $types,
      ...$params
   );


   if (!mysqli_stmt_execute($stmt)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mengambil seluruh data pasien: ' . mysqli_stmt_error($stmt)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmt);

      return;
   }


   $result =
      mysqli_stmt_get_result($stmt);


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
// User memilih pasien sumber.
//
// Source:
//     id_patient = pasien yang dipilih
//
// Sistem mengambil:
//     patient_nik
//     patient_number
//
// Kemudian mencari seluruh ms_patient lintas customer.
//
// Setelah mendapatkan seluruh id_patient,
// semua visit ditampilkan.
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


   $idPatient = (int)$idPatient;


   // ========================================================
   // SOURCE PATIENT
   // ========================================================
   //
   // Source wajib berasal dari customer aktif.
   //
   // ========================================================

   $sqlSource = "

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

    ";


   $stmtSource =
      mysqli_prepare(
         $koneksi,
         $sqlSource
      );


   if (!$stmtSource) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal menyiapkan query pasien sumber: ' . mysqli_error($koneksi)
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   mysqli_stmt_bind_param(
      $stmtSource,
      'ii',
      $idPatient,
      $id_customer
   );


   if (!mysqli_stmt_execute($stmtSource)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mengambil pasien sumber: ' . mysqli_stmt_error($stmtSource)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmtSource);

      return;
   }


   $resultSource =
      mysqli_stmt_get_result($stmtSource);


   $source =
      mysqli_fetch_assoc($resultSource);


   mysqli_stmt_close($stmtSource);


   if (!$source) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Data pasien tidak ditemukan atau bukan milik customer ini.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // IDENTITAS SOURCE
   // ========================================================

   $nik =
      trim(
         (string)($source['patient_nik'] ?? '')
      );


   $noKartu =
      trim(
         (string)($source['patient_number'] ?? '')
      );


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

      $identityParams[] =
         $nik;

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

      $identityParams[] =
         $noKartu;

      $identityTypes .= 's';
   }


   if (empty($identityConditions)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Tidak ada parameter identitas untuk pencarian RME.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   $whereIdentity =
      implode(
         ' OR ',
         $identityConditions
      );


   // ========================================================
   // GET ALL RELATED PATIENTS
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


   $stmtPatients =
      mysqli_prepare(
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
      $patient =
      mysqli_fetch_assoc($resultPatients)
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
   // JIKA TIDAK ADA PASIEN TERKAIT
   // ========================================================

   if (empty($patientIds)) {

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

   $visitPlaceholders =
      implode(
         ',',
         array_fill(
            0,
            count($patientIds),
            '?'
         )
      );


   $visitTypes =
      str_repeat(
         'i',
         count($patientIds)
      );


   $visitParams =
      $patientIds;


   // ========================================================
   // GET VISIT
   // ========================================================
   //
   // Semua visit dari seluruh pasien terkait.
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


   $stmtVisits =
      mysqli_prepare(
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


   mysqli_stmt_bind_param(
      $stmtVisits,
      $visitTypes,
      ...$visitParams
   );


   if (!mysqli_stmt_execute($stmtVisits)) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Gagal mengambil visit: ' . mysqli_stmt_error($stmtVisits)
      ], JSON_UNESCAPED_UNICODE);

      mysqli_stmt_close($stmtVisits);

      return;
   }


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


   while (
      $row =
      mysqli_fetch_assoc($resultVisits)
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


// ============================================================
// FUNCTION : GABUNG RME
// ============================================================
//
// SOURCE
// ------------------------------------------------------------
// Pasien yang dipertahankan.
//
// TARGET
// ------------------------------------------------------------
// Pasien yang akan digabung.
//
// PROSES:
//
// 1. Validasi request POST.
// 2. Validasi source.
// 3. Validasi target.
// 4. Source harus customer aktif.
// 5. Target harus ada.
// 6. Source != target.
// 7. Validasi NIK / Nomor Kartu MATCH.
// 8. Hitung visit target.
// 9. UPDATE seluruh pasien_visit target
//       menjadi source.
// 10. Pastikan target sudah tidak punya visit.
// 11. DELETE ms_patient target.
// 12. COMMIT.
//
// Jika terjadi error:
// ROLLBACK.
//
// ============================================================

function gabungRme($id_customer)
{
   global $koneksi;


   // ========================================================
   // METHOD
   // ========================================================

   if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Method harus POST.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // PARAMETER
   // ========================================================

   $sourceId = trim(
      (string)(
         $_POST['source_id_patient'] ?? ''
      )
   );


   $targetId = trim(
      (string)(
         $_POST['target_id_patient'] ?? ''
      )
   );


   // ========================================================
   // VALIDASI SOURCE
   // ========================================================

   if (
      $sourceId === '' ||
      !ctype_digit($sourceId)
   ) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'ID pasien sumber tidak valid.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // VALIDASI TARGET
   // ========================================================

   if (
      $targetId === '' ||
      !ctype_digit($targetId)
   ) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'ID pasien target tidak valid.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   $sourceId =
      (int)$sourceId;


   $targetId =
      (int)$targetId;


   // ========================================================
   // SOURCE != TARGET
   // ========================================================

   if ($sourceId === $targetId) {

      echo json_encode([
         'status'  => 'error',
         'message' => 'Pasien sumber dan pasien target tidak boleh sama.'
      ], JSON_UNESCAPED_UNICODE);

      return;
   }


   // ========================================================
   // START TRANSACTION
   // ========================================================

   mysqli_begin_transaction($koneksi);


   try {


      // ====================================================
      // 1. LOCK SOURCE
      // ====================================================

      $sqlSource = "

            SELECT

                id_patient,
                id_customer,
                nomor_rm,
                patient_name,
                patient_nik,
                patient_number

            FROM ms_patient

            WHERE

                id_patient = ?

                AND id_customer = ?

            LIMIT 1

            FOR UPDATE

        ";


      $stmtSource =
         mysqli_prepare(
            $koneksi,
            $sqlSource
         );


      if (!$stmtSource) {

         throw new Exception(
            'Gagal menyiapkan pasien sumber: ' .
               mysqli_error($koneksi)
         );
      }


      mysqli_stmt_bind_param(
         $stmtSource,
         'ii',
         $sourceId,
         $id_customer
      );


      if (!mysqli_stmt_execute($stmtSource)) {

         throw new Exception(
            'Gagal mengambil pasien sumber: ' .
               mysqli_stmt_error($stmtSource)
         );
      }


      $resultSource =
         mysqli_stmt_get_result(
            $stmtSource
         );


      $source =
         mysqli_fetch_assoc(
            $resultSource
         );


      mysqli_stmt_close(
         $stmtSource
      );


      if (!$source) {

         throw new Exception(
            'Pasien sumber tidak ditemukan atau bukan milik customer aktif.'
         );
      }


      // ====================================================
      // 2. LOCK TARGET
      // ====================================================

      $sqlTarget = "

            SELECT

                id_patient,
                id_customer,
                nomor_rm,
                patient_name,
                patient_nik,
                patient_number

            FROM ms_patient

            WHERE

                id_patient = ?

            LIMIT 1

            FOR UPDATE

        ";


      $stmtTarget =
         mysqli_prepare(
            $koneksi,
            $sqlTarget
         );


      if (!$stmtTarget) {

         throw new Exception(
            'Gagal menyiapkan pasien target: ' .
               mysqli_error($koneksi)
         );
      }


      mysqli_stmt_bind_param(
         $stmtTarget,
         'i',
         $targetId
      );


      if (!mysqli_stmt_execute($stmtTarget)) {

         throw new Exception(
            'Gagal mengambil pasien target: ' .
               mysqli_stmt_error($stmtTarget)
         );
      }


      $resultTarget =
         mysqli_stmt_get_result(
            $stmtTarget
         );


      $target =
         mysqli_fetch_assoc(
            $resultTarget
         );


      mysqli_stmt_close(
         $stmtTarget
      );


      if (!$target) {

         throw new Exception(
            'Pasien target tidak ditemukan.'
         );
      }


      // ====================================================
      // 3. IDENTITAS SOURCE
      // ====================================================

      $sourceNik =
         trim(
            (string)(
               $source['patient_nik'] ?? ''
            )
         );


      $targetNik =
         trim(
            (string)(
               $target['patient_nik'] ?? ''
            )
         );


      $sourceCard =
         trim(
            (string)(
               $source['patient_number'] ?? ''
            )
         );


      $targetCard =
         trim(
            (string)(
               $target['patient_number'] ?? ''
            )
         );


      // ====================================================
      // 4. VALIDASI MATCH
      // ====================================================

      $nikMatch = (

         $sourceNik !== '' &&

         $targetNik !== '' &&

         $sourceNik === $targetNik

      );


      $cardMatch = (

         $sourceCard !== '' &&

         $targetCard !== '' &&

         $sourceCard === $targetCard

      );


      if (
         !$nikMatch &&
         !$cardMatch
      ) {

         throw new Exception(
            'Pasien target tidak memiliki NIK atau Nomor Kartu yang sama dengan pasien sumber.'
         );
      }


      // ====================================================
      // 5. HITUNG VISIT TARGET
      // ====================================================

      $sqlCountVisit = "

            SELECT

                COUNT(*) AS total

            FROM pasien_visit

            WHERE

                id_patient = ?

        ";


      $stmtCountVisit =
         mysqli_prepare(
            $koneksi,
            $sqlCountVisit
         );


      if (!$stmtCountVisit) {

         throw new Exception(
            'Gagal menyiapkan perhitungan visit target: ' .
               mysqli_error($koneksi)
         );
      }


      mysqli_stmt_bind_param(
         $stmtCountVisit,
         'i',
         $targetId
      );


      if (!mysqli_stmt_execute($stmtCountVisit)) {

         throw new Exception(
            'Gagal menghitung visit target: ' .
               mysqli_stmt_error($stmtCountVisit)
         );
      }


      $resultCountVisit =
         mysqli_stmt_get_result(
            $stmtCountVisit
         );


      $countVisitRow =
         mysqli_fetch_assoc(
            $resultCountVisit
         );


      mysqli_stmt_close(
         $stmtCountVisit
      );


      $totalVisitBefore =
         (int)(
            $countVisitRow['total'] ?? 0
         );


      // ====================================================
      // 6. PINDAHKAN SELURUH VISIT
      // ====================================================

      $sqlMoveVisit = "

            UPDATE pasien_visit

            SET

                id_patient = ?

            WHERE

                id_patient = ?

        ";


      $stmtMoveVisit =
         mysqli_prepare(
            $koneksi,
            $sqlMoveVisit
         );


      if (!$stmtMoveVisit) {

         throw new Exception(
            'Gagal menyiapkan pemindahan visit: ' .
               mysqli_error($koneksi)
         );
      }


      mysqli_stmt_bind_param(
         $stmtMoveVisit,
         'ii',
         $sourceId,
         $targetId
      );


      if (!mysqli_stmt_execute($stmtMoveVisit)) {

         throw new Exception(
            'Gagal memindahkan visit: ' .
               mysqli_stmt_error($stmtMoveVisit)
         );
      }


      $affectedVisit =
         mysqli_stmt_affected_rows(
            $stmtMoveVisit
         );


      mysqli_stmt_close(
         $stmtMoveVisit
      );


      // ====================================================
      // 7. VALIDASI ULANG VISIT TARGET
      // ====================================================

      $sqlCheckVisit = "

            SELECT

                COUNT(*) AS total

            FROM pasien_visit

            WHERE

                id_patient = ?

        ";


      $stmtCheckVisit =
         mysqli_prepare(
            $koneksi,
            $sqlCheckVisit
         );


      if (!$stmtCheckVisit) {

         throw new Exception(
            'Gagal menyiapkan validasi visit target: ' .
               mysqli_error($koneksi)
         );
      }


      mysqli_stmt_bind_param(
         $stmtCheckVisit,
         'i',
         $targetId
      );


      if (!mysqli_stmt_execute($stmtCheckVisit)) {

         throw new Exception(
            'Gagal melakukan validasi visit target: ' .
               mysqli_stmt_error($stmtCheckVisit)
         );
      }


      $resultCheckVisit =
         mysqli_stmt_get_result(
            $stmtCheckVisit
         );


      $checkVisitRow =
         mysqli_fetch_assoc(
            $resultCheckVisit
         );


      mysqli_stmt_close(
         $stmtCheckVisit
      );


      $remainingVisit =
         (int)(
            $checkVisitRow['total'] ?? 0
         );


      if ($remainingVisit > 0) {

         throw new Exception(
            'Masih terdapat ' .
               $remainingVisit .
               ' visit pada pasien target. Data pasien tidak dihapus.'
         );
      }


      // ====================================================
      // 8. DELETE TARGET
      // ====================================================

      $sqlDeletePatient = "

            DELETE FROM ms_patient

            WHERE

                id_patient = ?

            LIMIT 1

        ";


      $stmtDeletePatient =
         mysqli_prepare(
            $koneksi,
            $sqlDeletePatient
         );


      if (!$stmtDeletePatient) {

         throw new Exception(
            'Gagal menyiapkan penghapusan pasien target: ' .
               mysqli_error($koneksi)
         );
      }


      mysqli_stmt_bind_param(
         $stmtDeletePatient,
         'i',
         $targetId
      );


      if (!mysqli_stmt_execute($stmtDeletePatient)) {

         throw new Exception(
            'Gagal menghapus pasien target: ' .
               mysqli_stmt_error($stmtDeletePatient)
         );
      }


      $deletedPatient =
         mysqli_stmt_affected_rows(
            $stmtDeletePatient
         );


      mysqli_stmt_close(
         $stmtDeletePatient
      );


      if ($deletedPatient !== 1) {

         throw new Exception(
            'Data pasien target tidak berhasil dihapus.'
         );
      }


      // ====================================================
      // 9. COMMIT
      // ====================================================

      mysqli_commit(
         $koneksi
      );


      // ====================================================
      // RESPONSE SUCCESS
      // ====================================================

      echo json_encode([

         'status' =>
         'success',

         'message' =>
         'RME berhasil digabung.',

         'source_patient' => [

            'id_patient' =>
            $source['id_patient'],

            'id_customer' =>
            $source['id_customer'],

            'nomor_rm' =>
            $source['nomor_rm'],

            'patient_name' =>
            $source['patient_name'],

            'patient_nik' =>
            $source['patient_nik'],

            'patient_number' =>
            $source['patient_number']

         ],

         'merged_patient' => [

            'id_patient' =>
            $target['id_patient'],

            'id_customer' =>
            $target['id_customer'],

            'nomor_rm' =>
            $target['nomor_rm'],

            'patient_name' =>
            $target['patient_name'],

            'patient_nik' =>
            $target['patient_nik'],

            'patient_number' =>
            $target['patient_number']

         ],

         'total_visit_before' =>
         $totalVisitBefore,

         'total_visit_moved' =>
         $affectedVisit,

         'deleted_patient' =>
         true

      ], JSON_UNESCAPED_UNICODE);
   } catch (Throwable $e) {


      // ====================================================
      // ROLLBACK
      // ====================================================

      mysqli_rollback(
         $koneksi
      );


      // ====================================================
      // RESPONSE ERROR
      // ====================================================

      echo json_encode([

         'status' =>
         'error',

         'message' =>
         $e->getMessage()

      ], JSON_UNESCAPED_UNICODE);
   }
}
