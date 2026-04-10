<?php
include '../../database/connect.php';
session_start();

header('Content-Type: application/json');

// Ambil id_customer dari session
$id_customer = "19";

if (!$id_customer) {
   echo json_encode([
      'status' => 'error',
      'message' => 'Session id_customer tidak ditemukan'
   ]);
   exit;
}

// Ambil method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
   case 'POST':
      createTarif($id_customer);
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         getTarifID($_GET['id'], $id_customer);
      } else {
         getTarif($id_customer);
      }
      break;
   case 'PUT':
      updateFarmasi($id_customer);
      break;
   case 'DELETE':
      deleteTarif($id_customer);
      break;
   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

/* ================= CREATE ================= */
function createTarif($id_customer)
{
   global $koneksi;

   $nomor_rm     = $_POST['nomor_rm'] ?? '';
   $nomor_visit  = $_POST['nomor_visit'] ?? '';
   $item         = $_POST['item'] ?? '';
   $diskon       = $_POST['diskon'] ?? 0;
   $jumlah       = $_POST['qty'] ?? 0;
   $catatan      = $_POST['catatan'] ?? '';
   $kategori      = $_POST['kategori'] ?? '';

   // Ambil harga
   $checkharga = mysqli_query($koneksi, "SELECT * FROM ms_tarif WHERE tarif_name='$item' AND id_customer='$id_customer'");
   $dataharga  = mysqli_fetch_array($checkharga);

   $hargaitem = $dataharga['tarif_amount'] ?? 0;

   if (empty($item)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Nama Tarif harus diisi.'
      ]);
      exit;
   }

   $query = "INSERT INTO pasien_billing 
      (id_customer, id_visit, billing_item, billing_price, billing_qty, billing_discount, billing_category, billing_notes) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param(
      "isssssss",
      $id_customer,
      $nomor_visit,
      $item,
      $hargaitem,
      $jumlah,
      $diskon,
      $kategori,
      $catatan
   );

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Data berhasil ditambahkan.'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal insert'
      ]);
   }
}

/* ================= READ ================= */
function getTarif($id_customer)
{
   global $koneksi;

   $start  = $_GET['start'] ?? 0;
   $length = $_GET['length'] ?? 10;
   $search = $_GET['search']['value'] ?? '';
   $no     = $_GET['no'] ?? '';

   $query = "SELECT * FROM pasien_billing WHERE id_customer='$id_customer'";

   if (!empty($no)) {
      $query .= " AND id_visit='$no'";
   }

   if (!empty($search)) {
      $query .= " AND billing_item LIKE '%$search%'";
   }

   $totalQuery  = "SELECT COUNT(*) as total FROM ($query) as x";
   $totalResult = mysqli_query($koneksi, $totalQuery);
   $totalData   = mysqli_fetch_assoc($totalResult);

   $query .= " LIMIT $start, $length";
   $result = mysqli_query($koneksi, $query);

   $data = [];
   while ($row = mysqli_fetch_assoc($result)) {
      $data[] = $row;
   }

   echo json_encode([
      'status' => 'success',
      'data' => $data,
      'recordsTotal' => $totalData['total'],
      'recordsFiltered' => $totalData['total']
   ]);
}

/* ================= READ BY ID ================= */
function getTarifID($id, $id_customer)
{
   global $koneksi;

   $query = "SELECT * FROM pasien_billing WHERE id_billing=? AND id_customer=?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("ss", $id, $id_customer);
   $stmt->execute();

   $result = $stmt->get_result();

   if ($result->num_rows > 0) {
      echo json_encode([
         'status' => 'success',
         'user' => $result->fetch_assoc() // 🔥 tetap user karena frontend pakai ini
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Data tidak ditemukan'
      ]);
   }
}

/* ================= UPDATE ================= */
function updateFarmasi($id_customer)
{
   global $koneksi;

   parse_str(file_get_contents("php://input"), $_PUT);

   $id     = $_PUT['iduser'] ?? '';
   $diskon = $_PUT['diskon'] ?? '';
   $jumlah = $_PUT['jumlah'] ?? '';

   if (!$id || !$diskon || !$jumlah) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID dan diskon dan Jumlah wajib diisi'
      ]);
      exit;
   }

   $query = "UPDATE pasien_billing 
             SET billing_discount=?, billing_price=? 
             WHERE id_billing=? AND id_customer=?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("iiss", $diskon, $jumlah, $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Update berhasil'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal update'
      ]);
   }
}

/* ================= DELETE ================= */
function deleteTarif($id_customer)
{
   global $koneksi;

   $id = $_GET['id'] ?? '';

   if (!$id) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID kosong'
      ]);
      exit;
   }

   $query = "DELETE FROM pasien_billing 
             WHERE id_billing=? AND id_customer=?";

   $stmt = $koneksi->prepare($query);
   $stmt->bind_param("ss", $id, $id_customer);

   if ($stmt->execute()) {
      echo json_encode([
         'status' => 'success',
         'message' => 'Berhasil dihapus'
      ]);
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal hapus'
      ]);
   }
}
