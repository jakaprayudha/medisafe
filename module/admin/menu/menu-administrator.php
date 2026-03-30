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
         <a class="sidebar-link <?php if ($title == 'Data Faskes') {
                                    echo 'active';
                                 } ?>"
            href="module/administrator/master-faskes"
            aria-expanded="false">
            <iconify-icon icon="mdi:folder-outline"></iconify-icon>
            <span class="hide-menu">Data Faskes</span>
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