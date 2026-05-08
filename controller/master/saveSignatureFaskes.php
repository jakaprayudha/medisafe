<?php
header('Content-Type: application/json');
include '../../database/connect.php';

$input = json_decode(file_get_contents('php://input'), true);

$id_user = (int) ($input['id_user'] ?? 0);
$image   = $input['image'] ?? null;

if (!$id_user || !$image) {
   echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
   exit;
}

// ================== AMBIL FILE LAMA ==================
$get = mysqli_query($koneksi, "SELECT signature_user FROM ms_users WHERE id_user = $id_user");
$old = mysqli_fetch_assoc($get);
$oldFile = $old['signature_user'] ?? null;

// ================== DECODE BASE64 ==================
$image = str_replace('data:image/png;base64,', '', $image);
$image = str_replace(' ', '+', $image);
$data = base64_decode($image);

// ================== PATH ==================
$folder = '../../uploads/ttd_faskes/';
if (!is_dir($folder)) {
   mkdir($folder, 0777, true);
}

// ================== HAPUS FILE LAMA ==================
if ($oldFile) {
   $oldPath = $folder . $oldFile;

   if (file_exists($oldPath)) {
      unlink($oldPath); // 🔥 hapus file lama
   }
}

// ================== SIMPAN FILE BARU ==================

$filename = 'ttd_' . time() . '.png';
$filePath = $folder . $filename;

// Handle error simpan file
if (file_put_contents($filePath, $data) === false) {
   $error = error_get_last();
   echo json_encode([
      'status' => 'error',
      'message' => 'Gagal menyimpan file',
      'error_detail' => $error['message'] ?? 'Unknown error'
   ]);
   exit;
}

// ================== UPDATE DB ==================
$now = date('Y-m-d H:i:s');

$result = mysqli_query($koneksi, "UPDATE ms_users 
    SET 
        signature_user = '$filename',
        signature_timestamp = '$now'
    WHERE id_user = $id_user
");

if (!$result) {
   echo json_encode([
      'status' => 'error',
      'message' => mysqli_error($koneksi)
   ]);
   exit;
}

// ================== RESPONSE ==================
echo json_encode([
   'status' => 'success',
   'file' => $filename
]);
