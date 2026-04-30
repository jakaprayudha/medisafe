<?php
include '../../database/connect.php';
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {

   case 'GET':
      if (isset($_GET['id'])) {
         getID($_GET['id']);
      } else {
         getData();
      }
      break;

   case 'POST':

      if (isset($_POST['action']) && $_POST['action'] == 'update') {
         updateData();
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Action tidak dikenal']);
      }

      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
}


function getData()
{
   global $koneksi;

   $query = "SELECT sc.clinic_name, sc.id, sh.* FROM setting_clinic sc LEFT JOIN setting_satusehat sh ON sc.id_customer = sh.id_customer";
   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      http_response_code(500);
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);
      return;
   }

   // Ambil semua data dalam bentuk array asosiatif
   $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

   // Tutup hasil query
   mysqli_free_result($result);

   // Kirimkan data dalam format JSON
   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
   ]);
}

// Function untuk Read User berdasarkan ID
function getID($id)
{
   global $koneksi;

   $stmt = $koneksi->prepare("
      SELECT 
         sc.id,
         sc.clinic_name,
         sh.organization_id,
         sh.client_id,
         sh.client_secret,
         sh.latitude,
         sh.longitude,
         sh.address
      FROM setting_clinic sc
      LEFT JOIN setting_satusehat sh 
         ON sc.id = sh.id_customer
      WHERE sc.id = ?
   ");

   $stmt->bind_param("i", $id);
   $stmt->execute();

   $data = $stmt->get_result()->fetch_assoc();

   echo json_encode([
      'status' => 'success',
      'data' => $data
   ]);

   $stmt->close();
}


function updateData()
{
   global $koneksi;
   parse_str(file_get_contents("php://input"), $_PUT);

   if (empty($_PUT['id_clinic'])) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
      return;
   }
   $id = $_PUT['id_clinic'];
   $check = $koneksi->prepare("SELECT id_customer FROM setting_clinic WHERE id = ?");
   $check->bind_param("i", $id);
   $check->execute();

   $result = $check->get_result();
   $data = $result->fetch_assoc();

   $check->close();

   if (!$data) {
      echo json_encode(['status' => 'error', 'message' => 'ID tidak ditemukan']);
      return;
   }

   $id_customer = $data['id_customer'];

   $organization_id = $_PUT['organization_id'] ?? null;
   $client_id       = $_PUT['client_id'] ?? null;
   $client_secret   = $_PUT['client_secret'] ?? null;
   $latitude        = $_PUT['latitude'] ?? null;
   $longitude       = $_PUT['longitude'] ?? null;
   $address         = $_PUT['address'] ?? null;

   // cek sudah ada atau belum
   $cek = $koneksi->prepare("SELECT * FROM setting_satusehat WHERE id_customer=?");
   $cek->bind_param("i", $id_customer);
   $cek->execute();
   $exist = $cek->get_result()->fetch_assoc();
   $cek->close();

   if ($exist) {

      // UPDATE
      $stmt = $koneksi->prepare("UPDATE setting_satusehat 
         SET organization_id=?, client_id=?, client_secret=?, latitude=?, longitude=?, address=? 
         WHERE id_customer=?
      ");

      $stmt->bind_param("ssssssi", $organization_id, $client_id, $client_secret, $latitude, $longitude, $address, $id_customer);
   } else {

      // INSERT
      $stmt = $koneksi->prepare("
         INSERT INTO setting_satusehat 
         (id_customer, organization_id, client_id, client_secret, latitude, longitude, address)
         VALUES (?, ?, ?, ?, ?, ?, ?)
      ");

      $stmt->bind_param("issssss", $id_customer, $organization_id, $client_id, $client_secret, $latitude, $longitude, $address);
   }

   if ($stmt->execute()) {
      echo json_encode(['status' => 'success']);
   } else {
      echo json_encode(['status' => 'error', 'message' => $stmt->error]);
   }

   $stmt->close();
}
