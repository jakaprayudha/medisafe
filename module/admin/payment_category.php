<?php
require '../../controller/view.php';
require '../../controller/category.php';
$get_category = tampildata("SELECT * FROM category_payment");
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <?php
    require 'sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require 'navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Kategori</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                      <a href="module/admin/payment">
                        <button class="btn btn-light"><i class="fas fa-arrow-left"></i> Kembali</button>
                      </a>
                      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-category-payment"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="example">
                      <thead>
                        <tr>
                          <th scope="col" class="text-dark fw-normal ps-0">Kategori</th>
                          <th scope="col" class="text-dark fw-normal text-center">Status</th>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($get_category as $data_category): ?>
                          <tr>
                            <td><?= $data_category['category'] ?></td>
                            <td class="text-center">
                              <?php
                              if ($data_category['status'] == 1) { ?>
                                <span class="badge bg-success-subtle text-success">Active</span>
                              <?php } else { ?>
                                <span class="badge bg-danger-subtle text-danger">Non-Active</span>
                              <?php    }
                              ?>
                            </td>
                            <td class="text-center col-1">
                              <!-- Dropdown Actions -->
                              <div class="dropdown">
                                <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                  Actions
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                  <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#updateCategoryAset" data-id=<?= $data_category['id'] ?> data-name='<?= $data_category['category'] ?>'>Update</a></li>
                                  <li><a class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryAset" data-id="<?= $data_category['id'] ?>">Delete</a></li>
                                </ul>
                              </div>
                            </td>
                          </tr>

                        <?php endforeach ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



  <?php
  require 'library.php';
  ?>
</body>
<?php
require 'component/category.php';
?>
<script>
  document.getElementById('deleteCategoryPayment').addEventListener('show.bs.modal', function(event) {
    this.querySelector('.modal-title').textContent = 'Perubahan Data ' + event.relatedTarget.getAttribute('data-name');
    this.querySelector('#product-id').value = event.relatedTarget.getAttribute('data-id');
    this.querySelector('#category').value = event.relatedTarget.getAttribute('data-name');
  });
  document.getElementById('updateCategoryPayment').addEventListener('show.bs.modal', function(event) {
    this.querySelector('.modal-title').textContent = 'Perubahan Data ' + event.relatedTarget.getAttribute('data-name');
    this.querySelector('#product-id').value = event.relatedTarget.getAttribute('data-id');
    this.querySelector('#category').value = event.relatedTarget.getAttribute('data-name');
  });
</script>
<script>
  $(document).ready(function() {
    $('#example').DataTable();
  });
  $('#example').DataTable({
    paging: true, // Mengaktifkan pagination
    searching: true, // Mengaktifkan pencarian
    ordering: true, // Mengaktifkan pengurutan
    lengthChange: true, // Mengaktifkan perubahan jumlah item yang ditampilkan
    pageLength: 10, // Jumlah baris per halaman
    language: {
      lengthMenu: "Show  _MENU_  entries page",
      zeroRecords: "Data Tidak Ditemukan",
      info: "Showing _PAGE_ of _PAGES_ entries",
      infoEmpty: "Tidak Ada Data",
      search: "Search :",
    }
  });
</script>

</html>