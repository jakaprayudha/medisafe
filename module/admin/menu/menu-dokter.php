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
               <a class="sidebar-link <?php if ($title == 'Pemeriksaan' or $title == 'Permintan Farmasi' or $title == 'Resep Luar' or $title == 'Vaksin' or $title == 'Biaya Transaksi' or $title == 'Riwayat'  or $title == 'Permintaan Rawat Inap' or $title == 'Penunjang') {
                                          echo 'active';
                                       } ?>"
                  href="module/doctor/pemeriksaan"
                  aria-expanded="false">
                  <iconify-icon icon="mdi:register-outline"></iconify-icon>
                  <span class="hide-menu">Poliklinik</span>
               </a>
            </li>
              <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Pemeriksaan Rawat Inap') {
                                          echo 'active';
                                       } ?>"
                  href="module/doctor/pemeriksaan-rawat-inap"
                  aria-expanded="false">
                  <iconify-icon icon="mdi:register-outline"></iconify-icon>
                  <span class="hide-menu">Rawat Inap</span>
               </a>
            </li>
            <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Riwayat Visit Pasien') {
                                          echo 'active';
                                       } ?>"
                  href="module/doctor/riwayat-visit"
                  aria-expanded="false">
                  <iconify-icon icon="mdi:stethoscope"></iconify-icon>
                  <span class="hide-menu">Riwayat Pasien</span>
               </a>
            </li>
             <li class="sidebar-item">
               <a class="sidebar-link <?php if ($title == 'Display Poliklinik') {
                                          echo 'active';
                                       } ?>"
                  href="module/display/display-poliklinik" target='_blank'
                  aria-expanded="false">
                  <iconify-icon icon="solar:printer-linear"></iconify-icon>
                  <span class="hide-menu">Antrean</span>
               </a>
            </li>
         </ul>
      </nav>