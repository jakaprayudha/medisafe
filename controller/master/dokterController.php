<?php
include '../../database/connect.php'; // pastikan $koneksi = mysqli_connect(...);

$method = $_SERVER['REQUEST_METHOD'];
$table = "ms_doctor"; // nama tabel target

switch ($method) {
   case 'POST':
      saveData();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         getDataById($_GET['id']);
      } else {
         getData();
      }
      break;
   case 'DELETE':
      deleteData();
      break;
   default:
      echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
      break;
}

function saveData()
{
   global $koneksi, $table;

   // Ambil struktur tabel
   $cols = [];
   $result = mysqli_query($koneksi, "DESCRIBE $table");
   while ($row = mysqli_fetch_assoc($result)) {
      if ($row['Field'] != 'id') { // skip primary key auto increment
         $cols[] = $row['Field'];
      }
   }

   $data = [];
   foreach ($cols as $col) {
      $data[$col] = $_POST[$col] ?? null;
   }

   // Jika ada id → update
   if (!empty($_POST['id_doctor'])) {
      $id = $_POST['id_doctor'];
      $set = implode(", ", array_map(function ($c) {
         return "$c = ?";
      }, array_keys($data)));

      $sql = "UPDATE $table SET $set WHERE id_doctor = ?";
      $stmt = mysqli_prepare($koneksi, $sql);

      $types = str_repeat("s", count($data)) . "i"; // semua string, terakhir int untuk id
      $values = array_values($data);
      $values[] = $id;

      mysqli_stmt_bind_param($stmt, $types, ...$values);
      mysqli_stmt_execute($stmt);

      echo json_encode(['status' => 'success', 'message' => 'Data berhasil diperbarui']);
   } else {
      // Insert
      $fields = implode(", ", array_keys($data));
      $placeholders = implode(", ", array_fill(0, count($data), "?"));

      $sql = "INSERT INTO $table ($fields) VALUES ($placeholders)";
      $stmt = mysqli_prepare($koneksi, $sql);

      $types = str_repeat("s", count($data));
      $values = array_values($data);

      mysqli_stmt_bind_param($stmt, $types, ...$values);
      mysqli_stmt_execute($stmt);

      echo json_encode(['status' => 'success', 'message' => 'Data berhasil ditambahkan']);
   }
}

function getData()
{
   global $koneksi, $table;

   // Hitung total data
   $sqlCount = "SELECT COUNT(*) as total FROM ms_doctor";
   $resultCount = mysqli_query($koneksi, $sqlCount);
   $rowCount = mysqli_fetch_assoc($resultCount);
   $total = $rowCount['total'];

   // Query utama join dokter + poli
   $sql = "SELECT d.*, s.poliklinik 
           FROM ms_doctor d 
           INNER JOIN ms_poli s ON d.doctor_spesialis = s.id_poli";
   $result = mysqli_query($koneksi, $sql);
   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

   echo json_encode([
      "draw" => intval($_GET['draw'] ?? 1),
      "recordsTotal" => $total,
      "recordsFiltered" => $total, // bisa ditambah filter kalau pakai pencarian
      "data" => $data
   ]);
}
function getDataById($id)
{
   global $koneksi;
   $sql = "SELECT d.*, s.poliklinik 
           FROM ms_doctor d 
          INNER JOIN ms_poli s ON d.doctor_spesialis = s.id_poli
           WHERE d.id_doctor = ?";
   $stmt = mysqli_prepare($koneksi, $sql);
   mysqli_stmt_bind_param($stmt, "i", $id);
   mysqli_stmt_execute($stmt);
   $result = mysqli_stmt_get_result($stmt);
   $data = mysqli_fetch_assoc($result);
   echo json_encode(['status' => 'success', 'data' => $data]);
}
function deleteData()
{
   global $koneksi, $table;
   $id = $_GET['id'] ?? '';
   $stmt = mysqli_prepare($koneksi, "DELETE FROM $table WHERE id_doctor = ?");
   mysqli_stmt_bind_param($stmt, "i", $id);
   mysqli_stmt_execute($stmt);
   echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus']);
}
