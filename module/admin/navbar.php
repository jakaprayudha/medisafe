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

            <?php if ($_SESSION['username'] === 'alfi') { ?>
               <li class="nav-item" style="min-width: 280px;">
                  <select id="switchUserSelect" class="form-select" style="width: 100%;">
                     <option value="">🔄 Login sebagai...</option>
                  </select>
               </li>
            <?php } ?>

            <!-- ACTION DROPDOWN -->
            <!-- <li class="nav-item dropdown">
               <button class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                  + Pendaftaran
               </button>

               <ul class="dropdown-menu shadow border-0">
                  <li><a class="dropdown-item" href="javascript:;">Pendaftaran Baru</a></li>
                  <li><a class="dropdown-item" href="module/admin/patient">Pasien Baru</a></li>
                  <li><a class="dropdown-item" href="javascript:;">Surat Baru</a></li>
               </ul>
            </li> -->

            <!-- NOTIF -->
            <li class="nav-item">
               <a class="nav-link position-relative" href="javascript:;">
                  <iconify-icon icon="solar:bell-linear" width="22"></iconify-icon>
                  <span class="notif-dot"></span>
               </a>
            </li>

            <li class="nav-item">
               <a href="javascript:;"
                  id="btnLogUpdate"
                  class="btn btn-outline-primary btn-sm log-update-btn position-relative">

                  <i class="ti ti-sparkles me-1"></i>
                  Log Update

                  <span id="logUpdateBadge"
                     class="log-update-badge"
                     style="display:none;">
                     0
                  </span>

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

<!-- ==========================================
     MODAL LOG UPDATE
========================================== -->
<div class="modal fade"
   id="logUpdateModal"
   tabindex="-1"
   aria-hidden="true">

   <div class="modal-dialog modal-lg modal-dialog-scrollable">

      <div class="modal-content border-0 shadow-lg">

         <!-- HEADER -->
         <div class="modal-header">

            <div>
               <h5 class="modal-title fw-bold mb-1">
                  <i class="ti ti-sparkles text-primary me-2"></i>
                  Log Update
               </h5>

               <small class="text-muted">
                  Informasi pembaruan sistem
               </small>
            </div>

            <button type="button"
               class="btn-close"
               data-bs-dismiss="modal">
            </button>

         </div>


         <!-- BODY -->
         <div class="modal-body">

            <!-- LOADING -->
            <div id="logUpdateLoading"
               class="text-center py-5">

               <div class="spinner-border text-primary mb-3">
               </div>

               <div class="text-muted">
                  Memuat update...
               </div>

            </div>


            <!-- LIST -->
            <div id="logUpdateList">
            </div>


            <!-- EMPTY -->
            <div id="logUpdateEmpty"
               class="text-center py-5"
               style="display:none;">

               <div class="empty-icon">
                  <i class="ti ti-circle-check"></i>
               </div>

               <h6 class="fw-bold">
                  Tidak ada update baru
               </h6>

               <small class="text-muted">
                  Kamu sudah menggunakan versi terbaru.
               </small>

            </div>

         </div>


         <!-- FOOTER -->
         <div class="modal-footer bg-white border-top">

            <small class="text-muted me-auto">
               <i class="ti ti-info-circle me-1"></i>
               Log pembaruan sistem Medisafe
            </small>


            <a href="module/log/log_update">
               <button type="button"
                  class="btn btn-primary btn-sm">

                  Lihat Riwayat Log

               </button>
            </a>

            <button type="button"
               class="btn btn-light btn-sm"
               data-bs-dismiss="modal">

               Tutup

            </button>

         </div>

      </div>

   </div>

</div>
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
                        <a class="dropdown-item" href="module/admin/patient_details?pt=${p.id_patient}&tabs=5">
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

   <?php if ($_SESSION['username'] === 'alfi') { ?>
      $(document).ready(function() {
         $('#switchUserSelect').select2({
            placeholder: '🔄 Login sebagai...',
            allowClear: true,
            width: '100%',
            ajax: {
               url: 'controller/session/switchUser',
               type: 'GET',
               dataType: 'json',
               delay: 300,
               data: function(params) {
                  return {
                     search: params.term || ''
                  };
               },
               processResults: function(data) {
                  return {
                     results: data.results || []
                  };
               },
               cache: true
            },
            minimumInputLength: 0,
            templateResult: function(data) {
               if (data.loading) return data.text;
               return $('<span>' + data.text + '</span>');
            },
            templateSelection: function(data) {
               return data.text || data.id;
            }
         });

         $('#switchUserSelect').on('select2:select', function(e) {
            var selectedUser = e.params.data;
            if (!selectedUser.id) return;

            Swal.fire({
               title: 'Switch User?',
               text: 'Login sebagai ' + selectedUser.text + '?',
               icon: 'question',
               showCancelButton: true,
               confirmButtonColor: '#0f9b8e',
               cancelButtonColor: '#d33',
               confirmButtonText: 'Ya, Switch!',
               cancelButtonText: 'Batal'
            }).then((result) => {
               if (result.isConfirmed) {
                  $.ajax({
                     url: 'controller/session/switchUser',
                     type: 'POST',
                     data: {
                        uid_user: selectedUser.id
                     },
                     dataType: 'json',
                     success: function(res) {
                        if (res.status === 'success') {
                           Swal.fire({
                              title: 'Berhasil!',
                              text: res.message,
                              icon: 'success',
                              timer: 1500,
                              showConfirmButton: false
                           }).then(() => {
                              window.location.href = res.redirect;
                           });
                        } else {
                           Swal.fire('Gagal', res.message, 'error');
                        }
                     },
                     error: function() {
                        Swal.fire('Error', 'Gagal switch user', 'error');
                     }
                  });
               } else {
                  $('#switchUserSelect').val(null).trigger('change');
               }
            });
         });
      });
   <?php } ?>
</script>
<script>
   $(document).ready(function() {

      /* =========================================================
         ESCAPE HTML
      ========================================================= */

      function escapeHtml(text) {

         return $('<div>')
            .text(text ?? '')
            .html();

      }


      /* =========================================================
         FORMAT TANGGAL
      ========================================================= */

      function formatDate(dateString) {

         if (!dateString) {
            return '-';
         }

         const date = new Date(
            dateString.replace(' ', 'T')
         );

         if (isNaN(date.getTime())) {
            return dateString;
         }

         return date.toLocaleDateString('id-ID', {
               day: '2-digit',
               month: 'short',
               year: 'numeric'
            }) + ' • ' +
            date.toLocaleTimeString('id-ID', {
               hour: '2-digit',
               minute: '2-digit'
            });

      }


      /* =========================================================
         ICON BERDASARKAN TYPE
      ========================================================= */

      function getUpdateIcon(type) {

         type = (type || 'update')
            .toLowerCase()
            .trim();

         switch (type) {

            case 'feature':
               return 'ti ti-sparkles';

            case 'improvement':
               return 'ti ti-trending-up';

            case 'bug':
            case 'fix':
               return 'ti ti-bug';

            case 'security':
               return 'ti ti-shield-check';

            case 'maintenance':
               return 'ti ti-tool';

            case 'update':
            default:
               return 'ti ti-refresh';

         }

      }


      /* =========================================================
         CEK LOG UPDATE
      ========================================================= */

      function checkLogUpdate() {

         $.ajax({

            url: 'controller/system/getLogUpdate.php',

            type: 'GET',

            dataType: 'json',

            cache: false,

            success: function(res) {

               if (
                  !res ||
                  res.status !== 'success'
               ) {
                  return;
               }


               /*
               |--------------------------------------------------------------------------
               | Gunakan unread_count dari API
               |--------------------------------------------------------------------------
               */

               const unread = parseInt(
                  res.unread_count || 0
               );


               const $badge =
                  $('#logUpdateBadge');

               const $button =
                  $('#btnLogUpdate');


               /*
               |--------------------------------------------------------------------------
               | ADA UPDATE BARU
               |--------------------------------------------------------------------------
               */

               if (unread > 0) {

                  $badge
                     .text(
                        unread > 99 ?
                        '99+' :
                        unread
                     )
                     .show();

                  $button
                     .addClass('has-update');

               }


               /*
               |--------------------------------------------------------------------------
               | TIDAK ADA UPDATE BARU
               |--------------------------------------------------------------------------
               */
               else {

                  $badge.hide();

                  $button
                     .removeClass('has-update');

               }

            },

            error: function(xhr) {

               console.error(
                  'Gagal mengambil log update:',
                  xhr.responseText
               );

            }

         });

      }


      /* =========================================================
         LOAD LOG UPDATE
      ========================================================= */

      function loadLogUpdate() {

         /*
         |--------------------------------------------------------------------------
         | Loading
         |--------------------------------------------------------------------------
         */

         $('#logUpdateLoading').show();

         $('#logUpdateList').html('');

         $('#logUpdateEmpty').hide();


         $.ajax({

            url: 'controller/system/getLogUpdate.php',

            type: 'GET',

            dataType: 'json',

            cache: false,

            success: function(res) {

               $('#logUpdateLoading').hide();


               /*
               |--------------------------------------------------------------------------
               | RESPONSE ERROR
               |--------------------------------------------------------------------------
               */

               if (
                  !res ||
                  res.status !== 'success'
               ) {

                  $('#logUpdateList').html(`
                        <div class="alert alert-danger border-0 shadow-sm">
                            <i class="ti ti-alert-circle me-2"></i>
                            Gagal memuat log update.
                        </div>
                    `);

                  return;

               }


               const updates =
                  res.data || [];


               /*
               |--------------------------------------------------------------------------
               | TIDAK ADA DATA
               |--------------------------------------------------------------------------
               */

               if (updates.length === 0) {

                  $('#logUpdateEmpty').show();

                  return;

               }


               /*
               |--------------------------------------------------------------------------
               | RENDER UPDATE
               |--------------------------------------------------------------------------
               */

               let html = '';


               updates.forEach(function(item) {

                  const isNew =
                     parseInt(item.is_read || 0) === 0;


                  const type =
                     (item.type || 'update')
                     .toLowerCase()
                     .trim();


                  const icon =
                     getUpdateIcon(type);


                  const title =
                     escapeHtml(
                        item.title ||
                        'Update Sistem'
                     );


                  const description =
                     escapeHtml(
                        item.description ||
                        'Tidak ada deskripsi update.'
                     );


                  const version =
                     escapeHtml(
                        item.version || ''
                     );


                  const date =
                     formatDate(
                        item.created_at
                     );


                  /*
                  |--------------------------------------------------------------------------
                  | VERSION
                  |--------------------------------------------------------------------------
                  */

                  let versionHtml = '';

                  if (version) {

                     versionHtml = `
                            <span class="log-update-version">
                                <i class="ti ti-tag me-1"></i>
                                v${version}
                            </span>
                        `;

                  }


                  /*
                  |--------------------------------------------------------------------------
                  | BADGE BARU
                  |--------------------------------------------------------------------------
                  */

                  let newHtml = '';

                  if (isNew) {

                     newHtml = `
                            <span class="log-update-new">
                                BARU
                            </span>
                        `;

                  }


                  /*
                  |--------------------------------------------------------------------------
                  | HTML CARD
                  |--------------------------------------------------------------------------
                  */

                  html += `

                        <div
                            class="log-update-item ${isNew ? 'is-new' : ''}"
                            data-update-id="${item.id_update}"
                        >

                            <!-- ICON -->

                            <div class="log-update-icon">

                                <i class="${icon}"></i>

                            </div>


                            <!-- CONTENT -->

                            <div class="log-update-content">


                                <!-- TITLE + DATE -->

                                <div class="log-update-top">

                                    <div>

                                        <h6 class="log-update-title">

                                            ${title}

                                        </h6>

                                    </div>


                                    <div class="log-update-date">

                                        <i class="ti ti-calendar-event me-1"></i>

                                        ${date}

                                    </div>

                                </div>


                                <!-- META -->

                                <div class="log-update-meta">


                                    <!-- TYPE -->

                                    <span class="log-update-type ${escapeHtml(type)}">

                                        ${escapeHtml(
                                            item.type ||
                                            'Update'
                                        )}

                                    </span>


                                    <!-- VERSION -->

                                    ${versionHtml}


                                    <!-- BARU -->

                                    ${newHtml}

                                </div>


                                <!-- DESCRIPTION -->

                                <div class="log-update-description">

                                    ${description}

                                </div>


                            </div>

                        </div>

                    `;

               });


               /*
               |--------------------------------------------------------------------------
               | TAMPILKAN DATA
               |--------------------------------------------------------------------------
               */

               $('#logUpdateList')
                  .html(html);


               /*
               |--------------------------------------------------------------------------
               | UPDATE STATUS BADGE
               |--------------------------------------------------------------------------
               */

               const unread =
                  parseInt(
                     res.unread_count || 0
                  );


               if (unread > 0) {

                  $('#logUpdateBadge')
                     .text(
                        unread > 99 ?
                        '99+' :
                        unread
                     )
                     .show();

                  $('#btnLogUpdate')
                     .addClass('has-update');

               }


               /*
               |--------------------------------------------------------------------------
               | MARK SEMUA SUDAH DIBACA
               |--------------------------------------------------------------------------
               |
               | Delay sedikit supaya user sempat melihat
               | status BARU sebelum berubah menjadi sudah dibaca.
               |
               */

               if (unread > 0) {

                  setTimeout(function() {

                     markLogUpdateAsRead();

                  }, 800);

               }

            },

            error: function(xhr) {

               $('#logUpdateLoading').hide();

               $('#logUpdateList').html(`

                    <div class="alert alert-danger border-0 shadow-sm">

                        <i class="ti ti-alert-circle me-2"></i>

                        Tidak dapat mengambil log update.

                    </div>

                `);


               console.error(
                  'Gagal mengambil log update:',
                  xhr.responseText
               );

            }

         });

      }


      /* =========================================================
         MARK SEMUA UPDATE SEBAGAI SUDAH DIBACA
      ========================================================= */

      function markLogUpdateAsRead() {

         $.ajax({

            url: 'controller/system/readLogUpdate.php',

            type: 'POST',

            dataType: 'json',

            success: function(res) {

               if (
                  res &&
                  res.status === 'success'
               ) {

                  /*
                  |--------------------------------------------------------------------------
                  | Hilangkan badge
                  |--------------------------------------------------------------------------
                  */

                  $('#logUpdateBadge')
                     .hide();


                  /*
                  |--------------------------------------------------------------------------
                  | Hilangkan efek attention
                  |--------------------------------------------------------------------------
                  */

                  $('#btnLogUpdate')
                     .removeClass('has-update');

               }

            },

            error: function(xhr) {

               console.error(
                  'Gagal menandai log update:',
                  xhr.responseText
               );

            }

         });

      }


      /* =========================================================
         KLIK LOG UPDATE
      ========================================================= */

      $('#btnLogUpdate').on('click', function() {

         /*
         |--------------------------------------------------------------------------
         | Buka modal
         |--------------------------------------------------------------------------
         */

         $('#logUpdateModal')
            .modal('show');


         /*
         |--------------------------------------------------------------------------
         | Load data
         |--------------------------------------------------------------------------
         */

         loadLogUpdate();

      });


      /* =========================================================
         CEK PERTAMA SAAT HALAMAN LOAD
      ========================================================= */

      checkLogUpdate();


      /* =========================================================
         CEK BERKALA
      ========================================================= */

      setInterval(
         checkLogUpdate,
         5 * 60 * 1000
      );


   });
</script>