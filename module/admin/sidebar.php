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
      <!-- Sidebar navigation-->
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
               <a class="sidebar-link <?php if ($title == 'Product' || $title == 'Satuan' || $title == "Kategori") {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/product"
                  aria-expanded="false">
                  <iconify-icon icon="solar:layers-minimalistic-bold-duotone"></iconify-icon>
                  <span class="hide-menu">Produk</span>
               </a>
            </li>
            <li class="sidebar-item">
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Supplier' || $title == 'Kategori Supplier') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/supplier"
                  aria-expanded="false">
                  <iconify-icon icon="solar:danger-circle-line-duotone"></iconify-icon>
                  <span class="hide-menu">Supplier</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Pelanggan' || $title == 'Kategori Pelanggan') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/customer"
                  aria-expanded="false">
                  <iconify-icon icon="solar:bookmark-square-minimalistic-line-duotone"></iconify-icon>
                  <span class="hide-menu">Customer</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Gudang') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/warehouse"
                  aria-expanded="false">
                  <iconify-icon icon="solar:file-text-line-duotone"></iconify-icon>
                  <span class="hide-menu">Gudang</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Karyawan') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/employee"
                  aria-expanded="false">
                  <iconify-icon icon="solar:text-field-focus-line-duotone"></iconify-icon>
                  <span class="hide-menu">Karyawan</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'User') {
                                          echo 'active';
                                       } ?>"
                  href="module/admin/user"
                  aria-expanded="false">
                  <iconify-icon icon="solar:user-line-duotone"></iconify-icon>
                  <span class="hide-menu">User Management</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link" href="module/admin/setting" aria-expanded="false">
                  <iconify-icon icon="solar:key-line-duotone"></iconify-icon>
                  <span class="hide-menu">Pengaturan Bisnis</span>
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
               <a class="sidebar-link" href="module/admin/sales" aria-expanded="false">
                  <iconify-icon icon="solar:login-3-line-duotone"></iconify-icon>
                  <span class="hide-menu">Penjualan</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link" href="module/admin/purchase" aria-expanded="false">
                  <iconify-icon icon="solar:user-plus-rounded-line-duotone"></iconify-icon>
                  <span class="hide-menu">Pembelian</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link" href="module/admin/stock" aria-expanded="false">
                  <iconify-icon icon="solar:folder-line-duotone"></iconify-icon>
                  <span class="hide-menu">Persediaan</span>
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
                  <iconify-icon icon="solar:sticker-smile-circle-2-line-duotone"></iconify-icon>
                  <span class="hide-menu">Transaksi</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link" href="module/admin/profit" aria-expanded="false">
                  <iconify-icon icon="solar:planet-3-line-duotone"></iconify-icon>
                  <span class="hide-menu">Laba Rugi</span>
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
      <!-- End Sidebar navigation -->
   </div>
   <!-- End Sidebar scroll-->
</aside>