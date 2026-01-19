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
               <span class="hide-menu">Aktivitas</span>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Registrasi Pasien Baru' or $title == 'Pasien Details') {
                                          echo 'active';
                                       } ?>"
                  href="module/admisi/registrasi-new"
                  aria-expanded="false">
                  <iconify-icon icon="mdi:register-outline"></iconify-icon>
                  <span class="hide-menu">Pasien Baru</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Registrasi Polilklinik' or $title == 'List Pasien' AND $_GET['type'] == 'Poliklinik') {
                                          echo 'active';
                                       } ?>"
                  href="module/admisi/registrasi-poliklinik"
                  aria-expanded="false">
                  <iconify-icon icon="mdi:stethoscope"></iconify-icon>
                  <span class="hide-menu">Poliklinik</span>
               </a>
            </li>
              <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Registrasi UGD' or $title == 'List Pasien' AND $_GET['type'] == 'UGD') {
                                          echo 'active';
                                       } ?>"
                  href="module/admisi/registrasi-ugd"
                  aria-expanded="false">
                  <iconify-icon icon="mdi:stethoscope"></iconify-icon>
                  <span class="hide-menu">UGD</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Registrasi Rawat Inap' or $title =='Permintaan Pasien Rawat Inap') {
                                          echo 'active';
                                       } ?>"
                  href="module/admisi/registrasi-inap"
                  aria-expanded="false">
                  <iconify-icon icon="solar:bed-linear"></iconify-icon>
                  <span class="hide-menu">Rawat Inap</span>
               </a>
            </li>
         </ul>
      </nav>