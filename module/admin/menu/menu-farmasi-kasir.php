<?php
/* Menu role apoteker / kasir (mode farmasi + kasir digabung): Aktivitas lalu Laporan. */
?>
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
           <a class="sidebar-link <?= ($title == 'Permintaan Resep') ? 'active' : '' ?>" href="module/pharmacy/permintaan-resep" aria-expanded="false">
              <iconify-icon icon="mdi:register-outline"></iconify-icon>
              <span class="hide-menu">Permintaan Resep</span>
           </a>
        </li>
        <li class="sidebar-item">
           <a class="sidebar-link <?= ($title == 'Kasir') ? 'active' : '' ?>" href="module/admin/kasir" aria-expanded="false">
              <iconify-icon icon="mdi:file-outline"></iconify-icon>
              <span class="hide-menu">Penerimaan (Kasir)</span>
           </a>
        </li>
        <?php if (($_SESSION['roles'] ?? '') === 'apoteker'): ?>
        <li class="sidebar-item">
           <a class="sidebar-link <?= ($title == 'Master Obat & BMHP' || $title == 'Farmasi') ? 'active' : '' ?>" href="module/admin/pharmacy" aria-expanded="false">
              <iconify-icon icon="mdi:drugs"></iconify-icon>
              <span class="hide-menu">Master Obat & BMHP</span>
           </a>
        </li>
        <?php endif ?>
        <li class="sidebar-item">
           <a class="sidebar-link <?= (false) ? 'active' : '' ?>" href="module/display/display-farmasi" target="_blank" aria-expanded="false">
              <iconify-icon icon="mdi:monitor"></iconify-icon>
              <span class="hide-menu">Display Farmasi</span>
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
           <a class="sidebar-link <?= ($title == 'Laporan Pelayanan Resep') ? 'active' : '' ?>" href="module/admin/rpt_resep" aria-expanded="false">
              <iconify-icon icon="mdi:file-chart-outline"></iconify-icon>
              <span class="hide-menu">Pelayanan Resep</span>
           </a>
        </li>
        <li class="sidebar-item">
           <a class="sidebar-link <?= ($title == 'Laporan Farmasi') ? 'active' : '' ?>" href="module/admin/rpt_farmasi" aria-expanded="false">
              <iconify-icon icon="mdi:clipboard-text-outline"></iconify-icon>
              <span class="hide-menu">Stok Farmasi</span>
           </a>
        </li>
        <li class="sidebar-item">
           <a class="sidebar-link <?= ($title == 'Pemakaian Obat Pasien') ? 'active' : '' ?>" href="module/admin/pharmacy_usage" aria-expanded="false">
              <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
              <span class="hide-menu">Pemakaian Obat Pasien</span>
           </a>
        </li>
        <li class="sidebar-item">
           <a class="sidebar-link <?= ($title == 'Expired Obat') ? 'active' : '' ?>" href="module/admin/pharmacy_expired" aria-expanded="false">
              <iconify-icon icon="solar:calendar-date-bold-duotone"></iconify-icon>
              <span class="hide-menu">Expired Obat</span>
           </a>
        </li>
        <li class="sidebar-item">
           <a class="sidebar-link <?= ($title == 'Laporan Kasir') ? 'active' : '' ?>" href="module/admin/rpt_kasir" aria-expanded="false">
              <iconify-icon icon="mdi:chart-box-outline"></iconify-icon>
              <span class="hide-menu">Laporan Kasir</span>
           </a>
        </li>
     </ul>
  </nav>
