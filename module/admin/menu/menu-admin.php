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
      </li>
   </ul>
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