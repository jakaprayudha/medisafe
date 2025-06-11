<?php
include '../../database/connect.php';

$search = $_GET['term'] ?? '';

$query = $koneksi->prepare("SELECT id_product, product_code, product_name, product_price FROM ms_product WHERE product_code LIKE ? OR product_name LIKE ? LIMIT 10");
$searchTerm = "%$search%";
$query->bind_param("ss", $searchTerm, $searchTerm);
$query->execute();

$result = $query->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
   $data[] = [
      'label' => "{$row['product_code']} - {$row['product_name']}",
      'value' => $row['product_code'],
      'name' => $row['product_name'],
      'unit' => $row['unit_name'],
      'price' => $row['product_price']
   ];
}

echo json_encode($data);
