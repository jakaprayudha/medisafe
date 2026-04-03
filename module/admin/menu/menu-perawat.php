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
           <a class="sidebar-link <?php if ($title == 'Riwayat Visit Pasien') {
                                       echo 'active';
                                    } ?>"
              href="module/doctor/riwayat-visit"
              aria-expanded="false">
              <iconify-icon icon="mdi:stethoscope"></iconify-icon>
              <span class="hide-menu">Data Pasien</span>
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