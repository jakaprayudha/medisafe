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
           <a class="sidebar-link <?php if ($title == 'Farmasi Order') {
                                       echo 'active';
                                    } ?>"
              href="module/pharmacy/permintaan-resep"
              aria-expanded="false">
              <iconify-icon icon="mdi:register-outline"></iconify-icon>
              <span class="hide-menu">Permintaan Resep</span>
           </a>
        </li>

        <li class="sidebar-item">
           <a class="sidebar-link <?php if ($title == 'Penerimaan') {
                                       echo 'active';
                                    } ?>"
              href="module/admin/kasir"
              aria-expanded="false">
              <iconify-icon icon="mdi:file-outline"></iconify-icon>
              <span class="hide-menu">Penerimaan</span>
           </a>
        </li>

        <li class="sidebar-item">
           <a class="sidebar-link <?php if ($title == 'Persediaan') {
                                       echo 'active';
                                    } ?>"
              href="module/display/display-farmasi" target="_blank"
              aria-expanded="false">
              <iconify-icon icon="mdi:monitor"></iconify-icon>
              <span class="hide-menu">Display Farmasi</span>
           </a>
        </li>


     </ul>
  </nav>