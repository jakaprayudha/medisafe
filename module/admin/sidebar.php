<?php
$role = $_SESSION['roles']; // ambil dari session login
var_dump($role);
?>
<!-- Sidebar Start -->
<aside class="left-sidebar">
   <!-- Sidebar scroll-->
   <div>
      <div class="brand-logo d-flex align-items-center justify-content-between">
         <a href="module/admin" class="text-nowrap logo-img">
            <img src="assets/images/logos/logo.svg" alt="" />
         </a>
         <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
         </div>
      </div>
      <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
         <ul id="sidebarnav">
            <li class="nav-small-cap">
               <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
               <span class="hide-menu">Home</span>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link" href="module/admin/" aria-expanded="false">
                  <iconify-icon icon="solar:widget-add-line-duotone"></iconify-icon>
                  <span class="hide-menu">Dashboard</span>
               </a>
            </li>
            <li>
               <span class="sidebar-divider lg"></span>
            </li>
            <li class="nav-small-cap">
               <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
               <span class="hide-menu">Master Data</span>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Poliklinik') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/poli"
                  aria-expanded="false">
                  <iconify-icon icon="fa6-solid:hospital-user"></iconify-icon>
                  <span class="hide-menu">Poliklinik</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Jenis Bayar') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/provider"
                  aria-expanded="false">
                  <iconify-icon icon="streamline-ultimate:cash-payment-bills-bold"></iconify-icon>
                  <span class="hide-menu">Jenis Bayar</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Dokter' or $title == 'Dokter Details') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/doctor"
                  aria-expanded="false">
                  <iconify-icon icon="fontisto:doctor"></iconify-icon>
                  <span class="hide-menu">Dokter</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Tenaga Kesehatan' or $title == 'Tenaga Kesehatan Details') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/nurse"
                  aria-expanded="false">
                  <iconify-icon icon="mdi:doctor"></iconify-icon>
                  <span class="hide-menu">Tenaga Kesehatan</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Pasien' or $title == 'Pasien Details') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/patient"
                  aria-expanded="false">
                  <iconify-icon icon="mdi:patient"></iconify-icon>
                  <span class="hide-menu">Pasien</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Farmasi' or $title == 'Farmasi Details') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/pharmacy"
                  aria-expanded="false">
                  <iconify-icon icon="healthicons:pharmacy"></iconify-icon>
                  <span class="hide-menu">Farmasi</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Tarif') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/tarif"
                  aria-expanded="false">
                  <iconify-icon icon="jam:book"></iconify-icon>
                  <span class="hide-menu">Tarif</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'User') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/user"
                  aria-expanded="false">
                  <iconify-icon icon="mynaui:users-solid"></iconify-icon>
                  <span class="hide-menu">User Management</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link" href="module/admin/setting" aria-expanded="false">
                  <iconify-icon icon="lets-icons:setting-line"></iconify-icon>
                  <span class="hide-menu">Pengaturan Klinik</span>
               </a>
            </li>
            <li>
               <span class="sidebar-divider lg"></span>
            </li>
            <li class="nav-small-cap">
               <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
               <span class="hide-menu">Aktivitas</span>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link" href="module/admin/registrasi" aria-expanded="false">
                  <iconify-icon icon="mdi:register-outline"></iconify-icon>
                  <span class="hide-menu">Registrasi Pasien</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Pemeriksaan') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/pemeriksaan"
                  aria-expanded="false">
                  <iconify-icon icon="mdi:stethoscope"></iconify-icon>
                  <span class="hide-menu">Pemeriksaan Dokter</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Farmasi Order') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/farmasi_order"
                  aria-expanded="false">
                  <iconify-icon icon="covid:vaccine-protection-medicine-pill"></iconify-icon>
                  <span class="hide-menu">Permintaan Farmasi</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Kasir') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/kasir"
                  aria-expanded="false">
                  <iconify-icon icon="hugeicons:cashier-02"></iconify-icon>
                  <span class="hide-menu">Kasir</span>
               </a>
            </li>
            <li>
               <span class="sidebar-divider lg"></span>
            </li>
            <li class="nav-small-cap">
               <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
               <span class="hide-menu">Laporan</span>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link" href="module/admin/report" aria-expanded="false">
                  <iconify-icon icon="tdesign:undertake-transaction"></iconify-icon>
                  <span class="hide-menu">Transaksi</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link" href="module/admin/profit" aria-expanded="false">
                  <iconify-icon icon="game-icons:profit"></iconify-icon>
                  <span class="hide-menu">Laba Rugi</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link" href="module/admin/report" aria-expanded="false">
                  <iconify-icon icon="vaadin:stock"></iconify-icon>
                  <span class="hide-menu">Persediaan Farmasi</span>
               </a>
            </li>
         </ul>
         <div
            class="unlimited-access d-flex align-items-center hide-menu bg-primary-subtle position-relative mb-7 mt-4 p-3 rounded">
            <div class="me-2 flex-shrink-0">
               <h6 class="fw-semibold fs-4 mb-6 text-dark w-75">Upgrade to Pro</h6>
               <a href="https://imzack.id/" target="_blank" class="btn btn-primary fs-2 fw-semibold lh-sm">Buy Pro</a>
            </div>
            <div class="unlimited-access-img">
               <img src="assets/images/backgrounds/rupee.png" alt="" class="img-fluid">
            </div>
         </div>
      </nav>

   </div>
   <!-- End Sidebar scroll-->
</aside>