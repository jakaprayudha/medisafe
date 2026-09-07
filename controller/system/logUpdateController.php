<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require '../../database/connect.php';


/*
|--------------------------------------------------------------------------
| CONFIG
|--------------------------------------------------------------------------
*/

$uploadDir = '../../uploads/system-update/';

$uploadUrl = 'uploads/system-update/';


/*
|--------------------------------------------------------------------------
| BUAT FOLDER JIKA BELUM ADA
|--------------------------------------------------------------------------
*/

if (!is_dir($uploadDir)) {

   mkdir(
      $uploadDir,
      0755,
      true
   );
}


/*
|--------------------------------------------------------------------------
| HELPER RESPONSE
|--------------------------------------------------------------------------
*/

function responseJson(
   $status,
   $message,
   $data = null
) {

   echo json_encode([
      'status' => $status,
      'message' => $message,
      'data' => $data
   ], JSON_UNESCAPED_UNICODE);

   exit;
}


/*
|--------------------------------------------------------------------------
| METHOD
|--------------------------------------------------------------------------
*/

$method = $_SERVER['REQUEST_METHOD'];


/*
|--------------------------------------------------------------------------
| GET
|--------------------------------------------------------------------------
*/

if ($method === 'GET') {

   /*
    |--------------------------------------------------------------------------
    | DETAIL
    |--------------------------------------------------------------------------
    */

   if (isset($_GET['id'])) {

      $id = (int) $_GET['id'];

      $stmt = $koneksi->prepare("
            SELECT
                id_update,
                title,
                description,
                type,
                version,
                guide_type,
                guide_url,
                guide_file,
                created_at,
                is_read
            FROM system_update_log
            WHERE id_update = ?
            LIMIT 1
        ");

      $stmt->bind_param(
         "i",
         $id
      );

      $stmt->execute();

      $result = $stmt->get_result();

      $data = $result->fetch_assoc();

      $stmt->close();

      if (!$data) {

         responseJson(
            'error',
            'Data log update tidak ditemukan.'
         );
      }

      responseJson(
         'success',
         'Data berhasil diambil.',
         $data
      );
   }


   /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */

   $result = $koneksi->query("
        SELECT
            id_update,
            title,
            description,
            type,
            version,
            guide_type,
            guide_url,
            guide_file,
            created_at,
            is_read
        FROM system_update_log
        ORDER BY created_at DESC
    ");


   if (!$result) {

      responseJson(
         'error',
         'Gagal mengambil data: ' . $koneksi->error
      );
   }


   $data = [];

   while ($row = $result->fetch_assoc()) {

      $data[] = $row;
   }


   responseJson(
      'success',
      'Data berhasil diambil.',
      $data
   );
}


/*
|--------------------------------------------------------------------------
| POST
|--------------------------------------------------------------------------
*/

if ($method === 'POST') {

   $title = trim(
      $_POST['title'] ?? ''
   );

   $description = trim(
      $_POST['description'] ?? ''
   );

   $type = trim(
      $_POST['type'] ?? 'update'
   );

   $version = trim(
      $_POST['version'] ?? ''
   );

   $guideType = trim(
      $_POST['guide_type'] ?? 'none'
   );

   $guideUrl = trim(
      $_POST['guide_url'] ?? ''
   );


   /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

   if ($title === '') {

      responseJson(
         'error',
         'Judul update wajib diisi.'
      );
   }


   $allowedTypes = [
      'update',
      'feature',
      'improvement',
      'bug',
      'fix',
      'security',
      'maintenance'
   ];


   if (!in_array(
      $type,
      $allowedTypes,
      true
   )) {

      $type = 'update';
   }


   $allowedGuideTypes = [
      'none',
      'url',
      'video',
      'pdf'
   ];


   if (!in_array(
      $guideType,
      $allowedGuideTypes,
      true
   )) {

      $guideType = 'none';
   }


   /*
    |--------------------------------------------------------------------------
    | GUIDE URL
    |--------------------------------------------------------------------------
    */

   if (
      in_array(
         $guideType,
         ['url', 'video'],
         true
      )
   ) {

      if ($guideUrl === '') {

         responseJson(
            'error',
            'URL panduan wajib diisi.'
         );
      }


      if (!filter_var(
         $guideUrl,
         FILTER_VALIDATE_URL
      )) {

         responseJson(
            'error',
            'URL panduan tidak valid.'
         );
      }
   } else {

      $guideUrl = null;
   }


   /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

   $guideFile = null;


   if (
      $guideType === 'pdf'
      &&
      isset($_FILES['guide_file'])
      &&
      $_FILES['guide_file']['error'] !== UPLOAD_ERR_NO_FILE
   ) {

      $file = $_FILES['guide_file'];


      if (
         $file['error'] !== UPLOAD_ERR_OK
      ) {

         responseJson(
            'error',
            'Gagal upload file PDF.'
         );
      }


      /*
        |--------------------------------------------------------------------------
        | EXTENSION
        |--------------------------------------------------------------------------
        */

      $extension = strtolower(
         pathinfo(
            $file['name'],
            PATHINFO_EXTENSION
         )
      );


      if ($extension !== 'pdf') {

         responseJson(
            'error',
            'File panduan harus PDF.'
         );
      }


      /*
        |--------------------------------------------------------------------------
        | MIME
        |--------------------------------------------------------------------------
        */

      $finfo = finfo_open(
         FILEINFO_MIME_TYPE
      );

      $mime = finfo_file(
         $finfo,
         $file['tmp_name']
      );

      finfo_close($finfo);


      if ($mime !== 'application/pdf') {

         responseJson(
            'error',
            'File yang diupload bukan PDF.'
         );
      }


      /*
        |--------------------------------------------------------------------------
        | SIZE MAX 20 MB
        |--------------------------------------------------------------------------
        */

      if (
         $file['size'] >
         20 * 1024 * 1024
      ) {

         responseJson(
            'error',
            'Ukuran PDF maksimal 20 MB.'
         );
      }


      /*
        |--------------------------------------------------------------------------
        | FILE NAME
        |--------------------------------------------------------------------------
        */

      $safeName =
         'guide_' .
         date('YmdHis') .
         '_' .
         bin2hex(
            random_bytes(5)
         ) .
         '.pdf';


      $destination =
         $uploadDir .
         $safeName;


      if (!move_uploaded_file(
         $file['tmp_name'],
         $destination
      )) {

         responseJson(
            'error',
            'Gagal menyimpan file PDF.'
         );
      }


      $guideFile =
         $uploadUrl .
         $safeName;
   }


   /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

   $stmt = $koneksi->prepare("
        INSERT INTO system_update_log
        (
            title,
            description,
            type,
            version,
            guide_type,
            guide_url,
            guide_file,
            is_read
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, 0)
    ");


   $stmt->bind_param(
      "sssssss",
      $title,
      $description,
      $type,
      $version,
      $guideType,
      $guideUrl,
      $guideFile
   );


   if (!$stmt->execute()) {

      responseJson(
         'error',
         'Gagal menyimpan data: ' .
            $stmt->error
      );
   }


   $stmt->close();


   responseJson(
      'success',
      'Log update berhasil ditambahkan.'
   );
}


/*
|--------------------------------------------------------------------------
| PUT
|--------------------------------------------------------------------------
*/

if ($method === 'PUT') {

   parse_str(
      file_get_contents('php://input'),
      $putData
   );


   $id = (int) (
      $putData['id'] ?? 0
   );


   if ($id <= 0) {

      responseJson(
         'error',
         'ID update tidak valid.'
      );
   }


   $title = trim(
      $putData['title'] ?? ''
   );

   $description = trim(
      $putData['description'] ?? ''
   );

   $type = trim(
      $putData['type'] ?? 'update'
   );

   $version = trim(
      $putData['version'] ?? ''
   );

   $guideType = trim(
      $putData['guide_type'] ?? 'none'
   );

   $guideUrl = trim(
      $putData['guide_url'] ?? ''
   );


   if ($title === '') {

      responseJson(
         'error',
         'Judul update wajib diisi.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | GUIDE URL
    |--------------------------------------------------------------------------
    */

   if (
      in_array(
         $guideType,
         ['url', 'video'],
         true
      )
   ) {

      if (
         $guideUrl === '' ||
         !filter_var(
            $guideUrl,
            FILTER_VALIDATE_URL
         )
      ) {

         responseJson(
            'error',
            'URL panduan tidak valid.'
         );
      }
   } else {

      $guideUrl = null;
   }


   /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

   $stmt = $koneksi->prepare("
        UPDATE system_update_log
        SET
            title = ?,
            description = ?,
            type = ?,
            version = ?,
            guide_type = ?,
            guide_url = ?
        WHERE id_update = ?
    ");


   $stmt->bind_param(
      "ssssssi",
      $title,
      $description,
      $type,
      $version,
      $guideType,
      $guideUrl,
      $id
   );


   if (!$stmt->execute()) {

      responseJson(
         'error',
         'Gagal mengubah data: ' .
            $stmt->error
      );
   }


   $stmt->close();


   responseJson(
      'success',
      'Log update berhasil diperbarui.'
   );
}


/*
|--------------------------------------------------------------------------
| DELETE
|--------------------------------------------------------------------------
*/

if ($method === 'DELETE') {

   parse_str(
      file_get_contents('php://input'),
      $deleteData
   );


   $id = (int) (
      $deleteData['id'] ?? 0
   );


   if ($id <= 0) {

      responseJson(
         'error',
         'ID update tidak valid.'
      );
   }


   /*
    |--------------------------------------------------------------------------
    | AMBIL FILE PDF
    |--------------------------------------------------------------------------
    */

   $stmt = $koneksi->prepare("
        SELECT guide_file
        FROM system_update_log
        WHERE id_update = ?
        LIMIT 1
    ");

   $stmt->bind_param(
      "i",
      $id
   );

   $stmt->execute();

   $result = $stmt->get_result();

   $row = $result->fetch_assoc();

   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | DELETE DATABASE
    |--------------------------------------------------------------------------
    */

   $stmt = $koneksi->prepare("
        DELETE FROM system_update_log
        WHERE id_update = ?
    ");

   $stmt->bind_param(
      "i",
      $id
   );


   if (!$stmt->execute()) {

      responseJson(
         'error',
         'Gagal menghapus data: ' .
            $stmt->error
      );
   }


   $stmt->close();


   /*
    |--------------------------------------------------------------------------
    | DELETE FILE PDF
    |--------------------------------------------------------------------------
    */

   if (
      !empty($row['guide_file'])
   ) {

      $filePath =
         '../../' .
         $row['guide_file'];


      if (
         file_exists($filePath)
      ) {

         unlink($filePath);
      }
   }


   responseJson(
      'success',
      'Log update berhasil dihapus.'
   );
}


/*
|--------------------------------------------------------------------------
| METHOD TIDAK DIDUKUNG
|--------------------------------------------------------------------------
*/

responseJson(
   'error',
   'Method tidak didukung.'
);
