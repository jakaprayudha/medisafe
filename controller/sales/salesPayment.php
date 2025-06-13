<?php
// Sertakan file koneksi database
include '../../database/connect.php';

// Mengambil method request
$method = $_SERVER['REQUEST_METHOD'];

// Handle request berdasarkan method (POST, GET, PUT, DELETE)
switch ($method) {
   case 'POST':
      // Create User
      createPayment();
      break;
   case 'GET':
      if (isset($_GET['id'])) {
         // Jika iduser ada di parameter, ambil data user berdasarkan iduser
         getProductID($_GET['id']);
      } else {
         // Jika tidak ada iduser, ambil semua data user
         getProduct();
      }
      break;
   case 'PATCH':
      // Update User
      updateProduct();
      break;

   case 'DELETE':
      // Delete User
      deleteProduct();
      break;

   default:
      echo json_encode([
         'status' => 'error',
         'message' => 'Method tidak diizinkan.'
      ]);
      break;
}

// Function untuk Create User
function createPayment()
{
   global $koneksi;

   // Ambil data dari request body
   $satuan = isset($_POST['satuan']) ? $_POST['satuan'] : '';
   $code = isset($_POST['kode']) ? $_POST['kode'] : '';
   $category = isset($_POST['kategori']) ? $_POST['kategori'] : '';
   $product = isset($_POST['produk']) ? $_POST['produk'] : '';
   $description = isset($_POST['description']) ? $_POST['description'] : '';
   $product_price = isset($_POST['harga_jual']) ? $_POST['harga_jual'] : '';
   $product_base = isset($_POST['harga_beli']) ? $_POST['harga_beli'] : '';
   if (empty($product || empty($satuan))) {
      echo json_encode([
         'status' => 'error',
         'message' => 'produk item dan satuan harus diisi.'
      ]);
      exit;
   }

   // Query untuk insert data user
   $query = "INSERT INTO ms_product (product_name, id_unit, product_description, product_code, product_price, product_base, id_category) VALUES (?, ?, ?, ?, ?, ?, ?)";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("sssssss", $product, $satuan, $description, $code, $product_price, $product_base, $category);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menambahkan.'
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query.'
      ]);
   }
}

// Function untuk Read User
function getProduct()
{
   global $koneksi;

   // Ambil parameter pagination dan pencarian dari request
   $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
   $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
   $search = isset($_GET['search']) && isset($_GET['search']['value']) ? $_GET['search']['value'] : '';

   // Query dasar untuk mengambil data user
   $query = "SELECT * FROM sales_order LEFT OUTER JOIN  ms_product ON ms_product.id_product = sales_order.id_product ";

   // Jika ada pencarian, tambahkan kondisi pencarian
   if ($search) {
      $query .= " WHERE product_name LIKE '%$search%' or product_code LIKE '%$search%'";
   }

   // Ambil data sesuai dengan pagination
   $query .= " LIMIT $start, $length";

   $result = mysqli_query($koneksi, $query);

   if (!$result) {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal mengambil data: ' . mysqli_error($koneksi)
      ]);
      exit;
   }

   $data = [];
   while ($row = mysqli_fetch_assoc($result)) {
      $data[] = $row;
   }

   // Query untuk menghitung total data
   $totalQuery = "SELECT COUNT(*) AS total FROM sales_order";
   $totalResult = mysqli_query($koneksi, $totalQuery);
   $totalData = mysqli_fetch_assoc($totalResult);
   $totalRecords = $totalData['total'];

   // Kirimkan data dalam format JSON untuk DataTables
   header('Content-Type: application/json');
   echo json_encode([
      'status' => 'success',
      'data' => $data,
      'recordsTotal' => $totalRecords,
      'recordsFiltered' => $totalRecords // Jika Anda ingin total yang difilter, buat query terpisah untuk menghitung total hasil pencarian
   ]);
}

// Function untuk Read User berdasarkan ID
function getProductID($iduser)
{
   global $koneksi;

   // Query untuk mengambil data user berdasarkan iduser
   $query = "SELECT * FROM sales_order WHERE id_order = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $iduser); // Bind parameter iduser
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
         $user = $result->fetch_assoc();
         echo json_encode([
            'status' => 'success',
            'user' => $user
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Data tidak ditemukan.'
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query.'
      ]);
   }
}

// Function untuk Update User
function updateProduct()
{
   global $koneksi;

   // Ambil data dari request body
   parse_str(file_get_contents("php://input"), $_PUT);
   $id = isset($_GET['id']) ? $_GET['id'] : '';
   $diskon1 = isset($_PUT['diskon_1']) ? (float)$_PUT['diskon_1'] : 0;
   $diskon2 = isset($_PUT['diskon_2']) ? (float)$_PUT['diskon_2'] : 0;
   $diskon3 = isset($_PUT['diskon_3']) ? (float)$_PUT['diskon_3'] : 0;

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID harus disertakan.'
      ]);
      exit;
   }

   // Ambil harga dan jumlah dari sales_order
   $select = "SELECT * FROM sales_order WHERE id_order = ?";
   if ($stmtSelect = $koneksi->prepare($select)) {
      $stmtSelect->bind_param("s", $id);
      $stmtSelect->execute();
      $result = $stmtSelect->get_result();

      if ($result->num_rows > 0) {
         $row = $result->fetch_assoc();
         $jumlah = $row['qty'];
         $harga = $row['harga_satuan'];
         $total = $jumlah * $harga;

         // Hitung diskon bertingkat
         $sisa1 = $total - ($diskon1 / 100) * $total;
         $sisa2 = $sisa1 - ($diskon2 / 100) * $sisa1;
         $sisa3 = $sisa2 - ($diskon3 / 100) * $sisa2;

         $total_diskon = $total - $sisa3;

         // Update ke database
         $update = "UPDATE sales_order SET diskon_1 = ?, diskon_2 = ?, diskon_3 = ?, total_diskon = ? WHERE id_order = ?";
         if ($stmtUpdate = $koneksi->prepare($update)) {
            $stmtUpdate->bind_param("dddss", $diskon1, $diskon2, $diskon3, $total_diskon, $id);
            if ($stmtUpdate->execute()) {
               echo json_encode([
                  'status' => 'success',
                  'message' => 'Diskon berhasil diperbarui.',
                  'total_diskon' => $total_diskon
               ]);
            } else {
               echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data.']);
            }
            $stmtUpdate->close();
         } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query update.']);
         }
      } else {
         echo json_encode(['status' => 'error', 'message' => 'Data order tidak ditemukan.']);
      }

      $stmtSelect->close();
   } else {
      echo json_encode(['status' => 'error', 'message' => 'Gagal menyiapkan query select.']);
   }
}

// Function untuk Delete User
function deleteProduct()
{
   global $koneksi;

   // Ambil ID user dari query parameter
   $id = isset($_GET['id']) ? $_GET['id'] : '';

   if (empty($id)) {
      echo json_encode([
         'status' => 'error',
         'message' => 'ID tidak ditemukan.'
      ]);
      exit;
   }

   // Query untuk menghapus data user
   $query = "DELETE FROM sales_order WHERE id_order = ?";

   if ($stmt = $koneksi->prepare($query)) {
      $stmt->bind_param("s", $id);

      if ($stmt->execute()) {
         echo json_encode([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.'
         ]);
      } else {
         echo json_encode([
            'status' => 'error',
            'message' => 'Gagal menghapus.'
         ]);
      }

      $stmt->close();
   } else {
      echo json_encode([
         'status' => 'error',
         'message' => 'Gagal menyiapkan query.'
      ]);
   }
}
