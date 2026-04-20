<?php
$role = $_SESSION['roles']; // ambil dari session login
$id_customer = $_SESSION['id_customer'];
$checkstatus = mysqli_query($koneksi, "SELECT status_farmasi_kasir FROM setting_clinic LEFT JOIN ms_faskes ON ms_faskes.id_clinic = setting_clinic.id WHERE setting_clinic.id_customer = '$id_customer' LIMIT 1");
$datafaskes = mysqli_fetch_array($checkstatus);
$statusfarmasikasir = $datafaskes['status_farmasi_kasir'];
?>
<!-- Sidebar Start -->
<aside class="left-sidebar">
   <!-- Sidebar scroll-->
   <div>
      <div class="brand-logo d-flex align-items-center justify-content-between">
         <a href="module/admin" class="text-nowrap logo-img">
            <img src="assets/images/logos/medisafe_logo.png" width="100" alt="" />
         </a>
         <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
         </div>
      </div>
      <?php
      if ($role == 'admin') {
         require 'menu/menu-admin.php';
      } else if ($role == 'receptionis') {
         require 'menu/menu-admisi.php';
      } else if ($role == 'dokter') {
         require 'menu/menu-dokter.php';
      } else if ($role == 'apoteker' || $role == 'kasir') {
         if ($statusfarmasikasir == 1) {
            // 🔥 MODE GABUNG
            require 'menu/menu-farmasi-kasir.php';
         } else {
            // 🔹 MODE NORMAL
            if ($role == 'apoteker') {
               require 'menu/menu-farmasi.php';
            } else {
               require 'menu/menu-kasir.php';
            }
         }
      } else if ($role == 'superadmin') {
         require 'menu/menu-administrator.php';
      } else if ($role == 'perawat' or $role == 'bidan') {
         require 'menu/menu-perawat.php';
      } else if ($role == 'analislab') {
         require 'menu/menu-analislab.php';
      } else {
         require 'menu/menu-admin.php';
      }
      ?>
   </div>
   <!-- End Sidebar scroll-->
</aside>