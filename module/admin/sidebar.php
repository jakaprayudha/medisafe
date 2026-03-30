<?php
$role = $_SESSION['roles']; // ambil dari session login
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
      } else if ($role == 'apoteker') {
         require 'menu/menu-farmasi.php';
      } else if ($role == 'kasir') {
         require 'menu/menu-kasir.php';
      } else if ($role == 'superadmin') {
         require 'menu/menu-administrator.php';
      } else {
         require 'menu/menu-admin.php';
      }
      ?>
   </div>
   <!-- End Sidebar scroll-->
</aside>