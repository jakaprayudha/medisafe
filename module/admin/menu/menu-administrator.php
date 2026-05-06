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


      <li class="nav-small-cap">
         <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
         <span class="hide-menu">Aktivitas</span>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Data Faskes' or $title == 'Master Faskes Details') {
                                    echo 'active';
                                 } ?>"
            href="module/administrator/master-faskes"
            aria-expanded="false">
            <iconify-icon icon="mdi:folder-outline"></iconify-icon>
            <span class="hide-menu">Data Faskes</span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Data Import') {
                                    echo 'active';
                                 } ?>"
            href="module/administrator/data-import"
            aria-expanded="false">
            <iconify-icon icon="mdi:download-outline"></iconify-icon>
            <span class="hide-menu">Data Import</span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Master Data') {
                                    echo 'active';
                                 } ?>"
            href="module/administrator/master-data"
            aria-expanded="false">
            <iconify-icon icon="mdi:folder-outline"></iconify-icon>
            <span class="hide-menu">Master Data</span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Master Satu Sehat') {
                                    echo 'active';
                                 } ?>"
            href="module/administrator/master-satu-sehat"
            aria-expanded="false">
            <iconify-icon icon="mdi:link"></iconify-icon>
            <span class="hide-menu">Satu Sehat </span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Master Laboratorium') {
                                    echo 'active';
                                 } ?>"
            href="module/administrator/master-lab"
            aria-expanded="false">
            <iconify-icon icon="mdi:test-tube"></iconify-icon>
            <span class="hide-menu">Laboratorium </span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'IDSH Satu Sehat') {
                                    echo 'active';
                                 } ?>"
            href="module/administrator/doctor-idsh"
            aria-expanded="false">
            <iconify-icon icon="mdi:file-outline"></iconify-icon>
            <span class="hide-menu">IDSH Dokter </span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'IDSH Satu Sehat') {
                                    echo 'active';
                                 } ?>"
            href="module/administrator/patient-idsh"
            aria-expanded="false">
            <iconify-icon icon="mdi:file-outline"></iconify-icon>
            <span class="hide-menu">IDSH Pasien </span>
         </a>
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