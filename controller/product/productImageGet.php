<?php
header('Content-Type: application/json');
require '../../database/connect.php'; // pastikan path ini sesuai

$id = $_GET['id'] ?? '';

if (!$id) {
   echo json_encode(['status' => 'error', 'message' => 'ID produk tidak ditemukan']);
   exit;
}

$query = $koneksi->prepare("SELECT product_image FROM ms_product WHERE id_product = ?");
$query->bind_param('s', $id);
$query->execute();
$result = $query->get_result()->fetch_assoc();

$image = $result['product_image'] ?? '';
$imagePath = ($image && file_exists("" . $image)) ? $image : 'uploads/default.png';

echo json_encode([
   'status' => 'success',
   'url' => $imagePath
]);
