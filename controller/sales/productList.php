<?php
include '../../database/connect.php';

$query = "SELECT id_product, product_code, product_name, product_price FROM ms_product";
$result = mysqli_query($koneksi, $query);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
   $products[] = $row;
}

echo json_encode($products);
