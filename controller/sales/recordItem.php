<?php
include '../../database/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   if (isset($_POST['product']) && is_array($_POST['product'])) {
      $products = $_POST['product'];
      $errors = [];

      mysqli_begin_transaction($koneksi);

      try {
         foreach ($products as $product) {
            $productId = $product['id'];
            $unit = $product['unit'];
            $price = $product['price'];
            $qty = $product['qty'];
            $idquotation = $product['idquotation'];
            $disc1 = isset($product['disc1']) ? (float)$product['disc1'] : 0;
            $disc2 = isset($product['disc2']) ? (float)$product['disc2'] : 0;
            $disc3 = isset($product['disc3']) ? (float)$product['disc3'] : 0;

            $totalHarga = $qty * $price;

            // Diskon berjenjang
            $afterDisc1 = $totalHarga * (1 - $disc1 / 100);
            $afterDisc2 = $afterDisc1 * (1 - $disc2 / 100);
            $afterDisc3 = $afterDisc2 * (1 - $disc3 / 100);

            $total = $afterDisc3;
            $totladiscount = $afterDisc1 + $afterDisc2 + $afterDisc3;

            $sql = "
                INSERT INTO sales_order (
                    id_product, unit, harga_satuan, qty, total, id_quotation,
                    diskon_1, diskon_2, diskon_3, total_diskon
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";

            if ($stmt = mysqli_prepare($koneksi, $sql)) {
               mysqli_stmt_bind_param(
                  $stmt,
                  "isdididddd",
                  $productId,
                  $unit,
                  $price,
                  $qty,
                  $totalHarga,
                  $idquotation,
                  $disc1,
                  $disc2,
                  $disc3,
                  $totladiscount
               );

               mysqli_stmt_execute($stmt);
            } else {
               throw new Exception('Query preparation failed: ' . mysqli_error($koneksi));
            }
         }

         mysqli_commit($koneksi);
         echo json_encode([
            'status' => 'success',
            'message' => 'Produk berhasil disimpan.',
            'redirect' => 'module/admin/sales_order'
         ]);
      } catch (Exception $e) {
         mysqli_rollback($koneksi);
         echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan produk: ' . $e->getMessage()]);
      }
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Tidak ada produk yang dikirim.']);
   }
} else {
   echo json_encode(['status' => 'error', 'message' => 'Request tidak valid.']);
}
