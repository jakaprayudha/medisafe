<?php
include '../../database/connect.php';

$search = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT id_product, product_code, product_name, product_price
        FROM ms_product
        WHERE product_code LIKE ? OR product_name LIKE ?
        LIMIT 20";

$stmt = $koneksi->prepare($sql);
$like = "%$search%";
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
   $data[] = $row;
}

echo json_encode($data);
