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
              <span class="hide-menu">Data Pasien</span>
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
           <a class="sidebar-link <?php if ($title == 'Pasien MobileJKN' or $title == 'Pasien MobileJKN' and $_GET['type'] == 'Poliklinik') {
                                       echo 'active';
                                    } ?>"
              href="module/admin/listpasienadmisi.php"
              aria-expanded="false">
              <iconify-icon icon="material-symbols:mobile"></iconify-icon>
              <span class="hide-menu">MobileJKN</span>
           </a>
        </li>

        <!-- <li class="sidebar-item">
           <a class="sidebar-link <?php if ($title == 'Registrasi UGD' or $title == 'List Pasien' and $_GET['type'] == 'UGD') {
                                       echo 'active';
                                    } ?>"
              href="module/admisi/registrasi-ugd"
              aria-expanded="false">
              <iconify-icon icon="mdi:stethoscope"></iconify-icon>
              <span class="hide-menu">UGD</span>
           </a>
        </li> -->
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
              href="module/display/display-admisi" target="_blank"
              aria-expanded="false">
              <iconify-icon icon="solar:display-linear"></iconify-icon>
              <span class="hide-menu">Display Antrean</span>
           </a>
        </li>
        <li class="sidebar-item">
           <a class="sidebar-link <?php if ($title == 'Status Pulang Ranap') {
                                       echo 'active';
                                    } ?>"
              href="module/admin/status_pulang"
              aria-expanded="false">
              <iconify-icon icon="solar:logout-linear"></iconify-icon>
              <span class="hide-menu">Status Pulang Ranap</span>
           </a>
        </li>

        <li class="sidebar-item">
           <a class="sidebar-link <?php if ($title == 'Display Tiket') {
                                       echo 'active';
                                    } ?>"
              href="module/admisi/counter-ticket" target='_blank'
              aria-expanded="false">
              <iconify-icon icon="solar:printer-linear"></iconify-icon>
              <span class="hide-menu">Ambil Antrean</span>
           </a>
        </li>
        <li class="sidebar-item">
           <a class="sidebar-link <?php if ($title == 'Display Tiket') {
                                       echo 'active';
                                    } ?>"
              href="module/admisi/counter-call"
              aria-expanded="false">
              <iconify-icon icon="material-symbols:call"></iconify-icon>
              <span class="hide-menu">Panggilan</span>
           </a>
        </li>
        <li class="sidebar-item">
           <a class="sidebar-link <?php if ($title == 'Surat') {
                                       echo 'active';
                                    } ?>"
              href="module/admin/form-letter"
              aria-expanded="false">
              <iconify-icon icon="material-symbols:description-outline"></iconify-icon>
              <span class="hide-menu">Surat</span>
           </a>
        </li>
        <!-- <li class="nav-small-cap">
           <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
           <span class="hide-menu">Daftar/Layanan (Pcare)</span>
        </li> -->
        <!-- <li class="sidebar-item"><a class="sidebar-link <?php if ($title == 'Pendaftaran') {
                                                                  echo 'active';
                                                               } ?>"
              href="module/admisi/pendaftaran"
              aria-expanded="false">
              <iconify-icon icon="solar:file-broken"></iconify-icon>
              <span class="hide-menu">Pendaftaran BPJS</span>
           </a>
        </li> -->
        <!-- <li class="sidebar-item"><a class="sidebar-link <?php if ($title == 'List Pendaftaran') {
                                                                  echo 'active';
                                                               } ?>"
              href="module/admisi/listpasiendaftar"
              aria-expanded="false">
              <iconify-icon icon="solar:book-bookmark-line-duotone"></iconify-icon>
              <span class="hide-menu">List Pasien Terdaftar</span>
           </a>
        </li>
        <li class="sidebar-item"><a class="sidebar-link <?php if ($title == 'List Kunjungan') {
                                                            echo 'active';
                                                         } ?>"
              href="module/admisi/listdatakunjungan"
              aria-expanded="false">
              <iconify-icon icon="solar:checklist-bold-duotone"></iconify-icon>
              <span class="hide-menu">List Kunjungan</span>
           </a>
        </li>
        <li class="sidebar-item"><a class="sidebar-link <?php if ($title == 'Skrining') {
                                                            echo 'active';
                                                         } ?>"
              href="module/admisi/skrining"
              aria-expanded="false">
              <iconify-icon icon="material-symbols:lab-profile-outline"></iconify-icon>
              <span class="hide-menu">Skrining</span>
           </a>
        </li>
        <li class="sidebar-item"><a class="sidebar-link <?php if ($title == 'Kegiatan Kelompok') {
                                                            echo 'active';
                                                         } ?>"
              href="module/admisi/addkegiatankelompok"
              aria-expanded="false">
              <iconify-icon icon="solar:widget-add-broken"></iconify-icon>
              <span class="hide-menu">Kegiatan</span>
           </a>
        </li>
        <li class="sidebar-item"><a class="sidebar-link <?php if ($title == 'mcu') {
                                                            echo 'active';
                                                         } ?>"
              href="module/admisi/listmcu"
              aria-expanded="false">
              <iconify-icon icon="material-symbols:pulse-alert-outline"></iconify-icon>
              <span class="hide-menu">MCU</span>
           </a>
        </li> -->
     </ul>
  </nav>