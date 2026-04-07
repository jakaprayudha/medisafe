<?php
$id_customer = $_SESSION['id_customer'];
$cust = mysqli_query($koneksi, "SELECT * FROM setting_clinic  WHERE id_customer='$id_customer'");
$dataCust = mysqli_fetch_array($cust);
?>
<style>
   /* =========================
   NAVBAR MODERN STYLE
========================= */

   .app-header {
      padding: 10px 16px;
      background: transparent;
   }

   .navbar {
      border-radius: 16px;
      padding: 10px 20px;
      background: #ffffff;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
      border: 1px solid #f1f1f1;
      backdrop-filter: blur(10px);
   }

   .main-content .navbar {
      margin: 10px 16px 0 16px;
   }

   /* =========================
   LOGO (FIX FULL CIRCLE)
========================= */
   .navbar img.rounded-circle {
      width: 40px;
      height: 40px;
      object-fit: cover;
      border-radius: 50%;
      border: 2px solid #e9ecef;
   }

   /* =========================
   SEARCH (FIX ANIMASI + BORDER)
========================= */
   .navbar input {
      border-radius: 20px;
      width: 250px;
      transition: all 0.3s ease;
      background: #f8fafc;
      border: 1px solid transparent;
   }

   .navbar input:focus {
      width: 320px;
      background: #ffffff;
      outline: none;
      border: 1px solid #0f9b8e;
      box-shadow: 0 0 0 3px rgba(15, 155, 142, 0.15);
   }

   /* ICON SEARCH */
   .navbar .position-relative iconify-icon {
      pointer-events: none;
   }

   .search-dropdown {
      position: absolute;
      top: 110%;
      left: 0;
      width: 100%;
      max-height: 300px;
      overflow-y: auto;

      background: #fff;
      border-radius: 12px;
      border: 1px solid #eee;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);

      display: none;
      z-index: 9999;
   }

   /* item */
   .search-dropdown a {
      padding: 10px 14px;
      display: block;
      border-bottom: 1px solid #f1f1f1;
   }

   .search-dropdown a:hover {
      background: #f8f9fa;
   }

   /* nama */
   .search-dropdown strong {
      font-size: 14px;
   }

   /* detail */
   .search-dropdown small {
      color: #888;
      font-size: 12px;
   }

   /* =========================
   NAV LINK + ICON
========================= */
   .nav-link {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
   }

   .nav-link iconify-icon {
      display: block;
   }

   /* =========================
   NOTIFICATION DOT
========================= */
   .notif-dot {
      position: absolute;
      top: 6px;
      right: 6px;
      width: 10px;
      height: 10px;
      background: #ff3b3b;
      border-radius: 50%;
      border: 2px solid #fff;
      animation: pulse 1.5s infinite;
   }

   @keyframes pulse {
      0% {
         transform: scale(1);
      }

      50% {
         transform: scale(1.3);
      }

      100% {
         transform: scale(1);
      }
   }

   /* =========================
   DROPDOWN
========================= */
   .dropdown-menu {
      border-radius: 12px;
      border: none;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
   }

   /* =========================
   BUTTON
========================= */
   .btn-success {
      background: linear-gradient(135deg, #0f9b8e, #38ef7d);
      border: none;
      border-radius: 12px;
      padding: 8px 16px;
      font-weight: 500;
   }

   /* =========================
   NAV ITEM ALIGNMENT
========================= */
   .navbar .nav-item {
      display: flex;
      align-items: center;
   }
</style>
<header class="app-header">
   <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-3">

      <!-- LEFT -->
      <ul class="navbar-nav align-items-center">
         <!-- Toggle -->
         <li class="nav-item d-block d-xl-none me-2">
            <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
               <i class="ti ti-menu-2"></i>
            </a>
         </li>

         <!-- Logo Klinik -->
         <li class="nav-item d-flex align-items-center me-3">
            <?php
            if ($dataCust['image_clinic'] == null) { ?>
               <img src="assets/images/logos/default.png" width="35" class="rounded-circle me-2">
            <?php } else { ?>
               <img src="uploads/<?= $dataCust['image_clinic'] ?>" width="35" class="rounded-circle me-2">
            <?php  }
            ?>

            <strong class="text-primary"><?= $dataCust['clinic_name'] ?></strong>
         </li>

         <!-- SEARCH PASIEN -->
         <li class="nav-item">
            <div class="position-relative" style="width:300px;">

               <input type="text"
                  class="form-control ps-5"
                  id="searchPatientNavbar"
                  placeholder="Cari pasien...">

               <iconify-icon icon="solar:magnifer-linear"
                  class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></iconify-icon>

               <!-- 🔥 RESULT -->
               <div id="searchResultNavbar" class="search-dropdown"></div>

            </div>
         </li>
      </ul>

      <!-- RIGHT -->
      <div class="navbar-collapse justify-content-end">

         <ul class="navbar-nav align-items-center gap-2">

            <!-- ACTION DROPDOWN -->
            <li class="nav-item dropdown">
               <button class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                  + Pendaftaran
               </button>

               <ul class="dropdown-menu shadow border-0">
                  <li><a class="dropdown-item" href="javascript:;">Pendaftaran Baru</a></li>
                  <li><a class="dropdown-item" href="module/admin/patient">Pasien Baru</a></li>
                  <li><a class="dropdown-item" href="javascript:;">Surat Baru</a></li>
               </ul>
            </li>

            <!-- NOTIF -->
            <li class="nav-item">
               <a class="nav-link position-relative" href="javascript:;">
                  <iconify-icon icon="solar:bell-linear" width="22"></iconify-icon>
                  <span class="notif-dot"></span>
               </a>
            </li>

            <!-- HELP -->
            <li class="nav-item">
               <a href="javascript:;" class="btn btn-outline-primary btn-sm">Bantuan</a>
            </li>

            <!-- USER -->
            <li class="nav-item dropdown">
               <a class="nav-link" data-bs-toggle="dropdown">
                  <img src="assets/images/profile/user-1.jpg" width="35" class="rounded-circle">
               </a>

               <div class="dropdown-menu dropdown-menu-end shadow border-0">
                  <a class="dropdown-item" href="module/admin/profile">Profile</a>
                  <a class="dropdown-item" href="javascript:;">Pengaturan</a>
                  <div class="dropdown-divider"></div>
                  <a href="" class="dropdown-item text-danger">Logout</a>
               </div>
            </li>

         </ul>
      </div>
   </nav>
</header>
<script>
   let debounceTimer;

   $('#searchPatientNavbar').on('keyup', function() {
      let keyword = $(this).val();

      clearTimeout(debounceTimer);

      if (keyword.length < 2) {
         $('#searchResultNavbar').hide();
         return;
      }

      debounceTimer = setTimeout(() => {

         fetch(`controller/master/patientSearchNavbar?search=${keyword}`)
            .then(res => res.json())
            .then(res => {

               let html = '';

               if (res.data.length === 0) {
                  html = `<div class="dropdown-item text-muted">Tidak ditemukan</div>`;
               } else {
                  res.data.forEach(p => {

                     let detail = [];

                     // RM selalu tampil kalau ada
                     if (p.nomor_rm) {
                        detail.push(`RM: ${p.nomor_rm}`);
                     }

                     // NIK hanya kalau tidak null/kosong
                     if (p.patient_nik && p.patient_nik !== 'null') {
                        detail.push(`NIK: ${p.patient_nik}`);
                     }

                     // BPJS hanya kalau tidak null/kosong
                     if (p.patient_bpjs && p.patient_bpjs !== 'null') {
                        detail.push(`BPJS: ${p.patient_bpjs}`);
                     }

                     html += `
                        <a class="dropdown-item" href="module/admin/patient_details?pt=${p.id_patient}">
                           <strong>${p.patient_name}</strong><br>
                           <small>${detail.join(' | ')}</small>
                        </a>
                     `;
                  });
               }

               $('#searchResultNavbar')
                  .html(html)
                  .fadeIn(150);

            });

      }, 300); // 🔥 debounce 300ms
   });

   $(document).on('click', function(e) {
      if (!$(e.target).closest('.position-relative').length) {
         $('#searchResultNavbar').fadeOut(100);
      }
   });
</script>