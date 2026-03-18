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

      <!-- MASTER DATA -->
      <li class="sidebar-item has-sub">
         <a href="javascript:void(0)" class="sidebar-link" onclick="toggleSidebarMenu(this)">
            <iconify-icon icon="solar:database-bold"></iconify-icon>
            <span class="hide-menu">Master Data</span>
            <i class="ti ti-chevron-down arrow"></i>
         </a>

         <ul class="collapse-menu">
            <li><a href="module/admin/poli">Poliklinik</a></li>
            <li><a href="module/admin/display">Display</a></li>
            <li><a href="module/admin/provider">Jenis Bayar</a></li>
            <li><a href="module/admin/room">Kamar Rawat Inap</a></li>
            <li><a href="module/admin/doctor">Dokter</a></li>
            <li><a href="module/admin/nurse">Tenaga Kesehatan</a></li>
            <li><a href="module/admin/patient">Pasien</a></li>
            <li><a href="module/admin/pharmacy">Farmasi</a></li>
            <li><a href="module/admin/tarif">Tarif</a></li>
            <li><a href="module/admin/user">User</a></li>
         </ul>
      </li>

      <!-- PENGATURAN -->
      <li class="sidebar-item has-sub">
         <a href="javascript:void(0)" class="sidebar-link" onclick="toggleSidebarMenu(this)">
            <iconify-icon icon="solar:settings-bold"></iconify-icon>
            <span class="hide-menu">Pengaturan</span>
            <i class="ti ti-chevron-down arrow"></i>
         </a>

         <ul class="collapse-menu">
            <li><a href="module/admin/setting_bpjs">Integrasi BPJS</a></li>
            <li><a href="module/admin/setting_satusehat">Integrasi Satu Sehat</a></li>
            <li><a href="module/admin/setting_faskes">Faskes</a></li>
         </ul>
      </li> <!-- LAPORAN -->

      <li class="nav-small-cap">
         <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
         <span class="hide-menu">Aktivitas</span>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Registrasi' or $title == 'List Pasien') {
                                    echo 'active';
                                 } ?>"
            href="module/admin/registrasi"
            aria-expanded="false">
            <iconify-icon icon="mdi:register-outline"></iconify-icon>
            <span class="hide-menu">Registrasi Pasien</span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Pemeriksaan' or $title == 'Permintan Farmasi' or $title == 'Resep Luar' or $title == 'Vaksin' or $title == 'Biaya Transaksi' or $title == 'Riwayat'  or $title == 'Permintaan Rawat Inap') {
                                    echo 'active';
                                 } ?>"
            href="module/admin/pemeriksaan"
            aria-expanded="false">
            <iconify-icon icon="mdi:stethoscope"></iconify-icon>
            <span class="hide-menu">Poliklinik</span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Pemeriksaan Rawat Inap' or $title == 'Permintaan Pasien Rawat Inap') {
                                    echo 'active';
                                 } ?>"
            href="module/admin/pemeriksaan_inap"
            aria-expanded="false">
            <iconify-icon icon="solar:bed-linear"></iconify-icon>
            <span class="hide-menu">Rawat Inap</span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Farmasi Order' or $title == 'Farmasi Order Luar'  or $title == 'Farmasi Pembelian') {
                                    echo 'active';
                                 } ?>"
            href="module/admin/farmasi_order"
            aria-expanded="false">
            <iconify-icon icon="covid:vaccine-protection-medicine-pill"></iconify-icon>
            <span class="hide-menu">Farmasi</span>
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
      <li class="sidebar-item has-sub">
         <a href="javascript:void(0)" class="sidebar-link" onclick="toggleSidebarMenu(this)">
            <iconify-icon icon="solar:chart-bold"></iconify-icon>
            <span class="hide-menu">Laporan Medis</span>
            <i class="ti ti-chevron-down arrow"></i>
         </a>

         <ul class="collapse-menu">
            <li><a href="module/admin/report">Transaksi</a></li>
            <li><a href="module/admin/profit">Laba Rugi</a></li>
            <li><a href="module/admin/persediaan">Persediaan</a></li>
         </ul>
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

<script>
   function toggleSidebarMenu(el) {
      let parent = el.parentElement;
      let allMenus = document.querySelectorAll(".sidebar-item.has-sub");

      // close semua
      allMenus.forEach(item => {
         if (item !== parent) {
            item.classList.remove("open");
            let m = item.querySelector(".collapse-menu");
            if (m) m.style.maxHeight = null;
         }
      });

      let menu = parent.querySelector(".collapse-menu");

      parent.classList.toggle("open");

      if (menu.style.maxHeight) {
         menu.style.maxHeight = null;
      } else {
         menu.style.maxHeight = menu.scrollHeight + "px";
      }
   }

   /* AUTO OPEN SAAT LOAD */
   document.addEventListener("DOMContentLoaded", function() {
      document.querySelectorAll(".sidebar-item.open").forEach(item => {
         let menu = item.querySelector(".collapse-menu");
         if (menu) {
            menu.style.maxHeight = menu.scrollHeight + "px";
         }
      });
   });
</script>