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

      <li class="sidebar-item has-sub">
         <a href="javascript:void(0)" class="sidebar-link" onclick="toggleSidebarMenu(this)">
            <iconify-icon icon="solar:folder-bold"></iconify-icon>
            <span class="hide-menu">Laporan</span>
            <i class="ti ti-chevron-down arrow"></i>
         </a>

         <ul class="collapse-menu">
            <li><a href="module/admin/rpt_appointment">Appointment</a></li>
         </ul>
      </li>

      <li class="sidebar-item">
         <a class="sidebar-link" href="module/admin/signature" aria-expanded="false">
            <iconify-icon icon="mdi:signature-freehand"></iconify-icon>
            <span class="hide-menu">Tanda Tangan Digital</span>
         </a>
      </li>

      <li class="nav-small-cap">
         <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
         <span class="hide-menu">Admisi</span>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Registrasi Pasien Baru' or $title == 'Pasien Details') {
                                    echo 'active';
                                 } ?>"
            href="module/admisi/registrasi-new"
            aria-expanded="false">
            <iconify-icon icon="mdi:register-outline"></iconify-icon>
            <span class="hide-menu">Data Pasien</span>
         </a>
      </li>
      <li class="sidebar-item"><a class="sidebar-link <?php if ($title == 'Jadwal Dokter') {
                                                         echo 'active';
                                                      } ?>"
            href="module/admisi/schedule"
            aria-expanded="false">
            <iconify-icon icon="solar:calendar-outline"></iconify-icon>
            <span class="hide-menu">Jadwal Dokter</span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Registrasi Polilklinik' or $title == 'Pendaftaran Pasien' or $title == 'List Pasien' and $_GET['type'] == 'Poliklinik') {
                                    echo 'active';
                                 } ?>"
            href="module/admisi/registrasi-poliklinik"
            aria-expanded="false">
            <iconify-icon icon="mdi:stethoscope"></iconify-icon>
            <span class="hide-menu">Poliklinik</span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Registrasi Rawat Inap' or $title == 'Permintaan Pasien Rawat Inap') {
                                    echo 'active';
                                 } ?>"
            href="module/admisi/registrasi-inap"
            aria-expanded="false">
            <iconify-icon icon="solar:bed-linear"></iconify-icon>
            <span class="hide-menu">Rawat Inap</span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Counter Admisi') {
                                    echo 'active';
                                 } ?>"
            href="module/display/display-poliklinik" target="_blank"
            aria-expanded="false">
            <iconify-icon icon="solar:display-linear"></iconify-icon>
            <span class="hide-menu">Display Antrean</span>
         </a>
      </li>
      <li class="nav-small-cap">
         <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
         <span class="hide-menu">RME</span>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Pemeriksaan' or ($title == 'Permintan Farmasi' and (isset($_GET['rme']) && $_GET['rme'] == 'a')) or $title == 'Resep Luar' or $title == 'Vaksin' or $title == 'Biaya Transaksi' or $title == 'Riwayat'  or $title == 'Permintaan Rawat Inap' or $title == 'Penunjang') {
                                    echo 'active';
                                 } ?>"
            href="module/doctor/pemeriksaan"
            aria-expanded="false">
            <iconify-icon icon="mdi:register-outline"></iconify-icon>
            <span class="hide-menu">Poliklinik</span>
         </a>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Pemeriksaan Rawat Inap' or (isset($_GET['rme']) && $_GET['rme'] == 'c')) {
                                    echo 'active';
                                 } ?>"
            href="module/doctor/pemeriksaan-rawat-inap"
            aria-expanded="false">
            <iconify-icon icon="mdi:bed-outline"></iconify-icon>
            <span class="hide-menu">Rawat Inap</span>
         </a>
      </li>
      <li class="nav-small-cap">
         <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
         <span class="hide-menu">Farmasi</span>
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
         <a class="sidebar-link <?php if ($title == 'Persediaan') {
                                    echo 'active';
                                 } ?>"
            href="module/display/display-farmasi" target="_blank"
            aria-expanded="false">
            <iconify-icon icon="mdi:monitor"></iconify-icon>
            <span class="hide-menu">Display Farmasi</span>
         </a>
      </li>
      <li class="nav-small-cap">
         <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
         <span class="hide-menu">Kasir</span>
      </li>
      <li class="sidebar-item">
         <a class="sidebar-link <?php if ($title == 'Farmasi Order') {
                                    echo 'active';
                                 } ?>"
            href="module/admin/kasir"
            aria-expanded="false">
            <iconify-icon icon="mdi:register-outline"></iconify-icon>
            <span class="hide-menu">Penerimaan</span>
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