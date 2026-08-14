<?php

$title = 'Setting Bisnis';

require '../../controller/view.php';

?>

<!doctype html>

<html lang="en">

<head>

   <base href="../../">

   <?php
   require '../../assets/template/head.php';
   ?>


   <style>
      /* =========================================================
         GENERAL
      ========================================================= */

      .setting-section {

         margin-top: 20px;

      }


      /* =========================================================
         HEADER SETTING
      ========================================================= */

      .setting-header {

         display: flex;

         align-items: center;

         gap: 15px;

      }


      .setting-header-icon {

         width: 50px;

         height: 50px;

         border-radius: 12px;

         display: flex;

         align-items: center;

         justify-content: center;

         background: #e7f1ff;

         color: #0d6efd;

         flex-shrink: 0;

      }


      /* =========================================================
         MODE PENOMORAN
      ========================================================= */

      .mode-card {

         border: 1px solid #e5e7eb;

         border-radius: 12px;

         padding: 18px;

         cursor: pointer;

         transition: all .2s ease;

         height: 100%;

         background: #fff;

      }


      .mode-card:hover {

         border-color: #adb5bd;

         box-shadow:
            0 5px 18px rgba(0, 0, 0, .05);

      }


      .mode-card input {

         display: none;

      }


      /* =========================================================
         AUTO ACTIVE
      ========================================================= */

      .mode-card.auto input:checked+.mode-content {

         border-color: #0d6efd;

         background: #f0f6ff;

      }


      /* =========================================================
         MANUAL ACTIVE
      ========================================================= */

      .mode-card.manual input:checked+.mode-content {

         border-color: #198754;

         background: #f0fff7;

      }


      .mode-content {

         border: 2px solid transparent;

         border-radius: 10px;

         padding: 15px;

         display: flex;

         align-items: center;

         gap: 15px;

         transition: all .2s ease;

      }


      .mode-icon {

         width: 48px;

         height: 48px;

         border-radius: 10px;

         display: flex;

         align-items: center;

         justify-content: center;

         flex-shrink: 0;

      }


      .mode-icon.auto {

         background: #e7f1ff;

         color: #0d6efd;

      }


      .mode-icon.manual {

         background: #e8f7ef;

         color: #198754;

      }


      .mode-title {

         font-size: 15px;

         font-weight: 600;

         margin-bottom: 3px;

      }


      .mode-description {

         font-size: 12px;

         color: #6c757d;

         line-height: 1.4;

      }


      .mode-check {

         margin-left: auto;

         opacity: 0;

      }


      .mode-card input:checked+.mode-content .mode-check {

         opacity: 1;

      }


      .mode-card.auto input:checked+.mode-content .mode-check {

         color: #0d6efd;

      }


      .mode-card.manual input:checked+.mode-content .mode-check {

         color: #198754;

      }


      /* =========================================================
         INFO MODE
      ========================================================= */

      .mode-info {

         border-radius: 10px;

         padding: 12px 15px;

         font-size: 13px;

      }


      /* =========================================================
         MANUAL SETTING AREA
      ========================================================= */

      #manualSetting {

         display: none;

      }


      #manualSetting.active {

         display: block;

      }


      /* =========================================================
         SURAT CARD
      ========================================================= */

      .surat-card {

         border: 1px solid #e5e7eb;

         border-radius: 12px;

         transition: all .2s ease;

         height: 100%;

      }


      .surat-card:hover {

         transform: translateY(-2px);

         box-shadow:
            0 8px 25px rgba(0, 0, 0, .06);

      }


      .surat-icon {

         width: 46px;

         height: 46px;

         border-radius: 10px;

         display: flex;

         align-items: center;

         justify-content: center;

         background: #e7f1ff;

         color: #0d6efd;

         flex-shrink: 0;

      }


      .surat-title {

         font-size: 15px;

         font-weight: 600;

      }


      .surat-description {

         font-size: 12px;

         color: #6c757d;

      }


      /* =========================================================
         FORMAT PREVIEW
      ========================================================= */

      .format-preview {

         background: #f8f9fa;

         border: 1px dashed #ced4da;

         border-radius: 8px;

         padding: 10px 12px;

         margin-top: 10px;

      }


      .format-preview-label {

         font-size: 10px;

         color: #6c757d;

         margin-bottom: 3px;

      }


      .format-preview-value {

         font-family: monospace;

         font-size: 12px;

         font-weight: 600;

         color: #212529;

         word-break: break-word;

      }


      /* =========================================================
         FORM
      ========================================================= */

      .form-label {

         font-size: 12px;

         font-weight: 500;

         margin-bottom: 5px;

      }


      .form-control {

         font-size: 13px;

      }


      .form-text {

         font-size: 10px;

      }


      /* =========================================================
         BUTTON
      ========================================================= */

      .save-button {

         min-width: 140px;

      }


      /* =========================================================
         HIDDEN
      ========================================================= */

      .d-none-setting {

         display: none !important;

      }
   </style>

</head>


<body>


   <!-- =========================================================
        BODY WRAPPER
   ========================================================= -->

   <div
      class="page-wrapper"
      id="main-wrapper"
      data-layout="vertical"
      data-navbarbg="skin6"
      data-sidebartype="full"
      data-sidebar-position="fixed"
      data-header-position="fixed">


      <!-- ======================================================
           SIDEBAR
      ======================================================= -->

      <?php

      require 'sidebar.php';

      ?>


      <!-- ======================================================
           MAIN BODY
      ======================================================= -->

      <div class="body-wrapper">


         <!-- ====================================================
              NAVBAR
         ===================================================== -->

         <?php

         require 'navbar.php';

         ?>


         <!-- ====================================================
              CONTENT
         ===================================================== -->

         <div class="body-wrapper-inner">

            <div class="container-fluid">


               <!-- =================================================
                    PAGE HEADER
               ================================================== -->

               <div class="row">

                  <div class="col-12">

                     <div class="card border-0 shadow-sm">

                        <div class="card-body">

                           <div class="setting-header">


                              <div class="setting-header-icon">

                                 <iconify-icon
                                    icon="material-symbols:settings-outline"
                                    width="27">
                                 </iconify-icon>

                              </div>


                              <div>

                                 <h5 class="fw-semibold mb-1">

                                    Pengaturan Nomor Surat

                                 </h5>


                                 <div class="text-muted small">

                                    Atur metode penomoran surat
                                    dan konfigurasi nomor surat
                                    fasilitas kesehatan.

                                 </div>

                              </div>


                           </div>

                        </div>

                     </div>

                  </div>

               </div>


               <!-- =================================================
                    MODE PENOMORAN
               ================================================== -->

               <div class="row setting-section">


                  <!-- =================================================
                       OTOMATIS
                  ================================================== -->

                  <div class="col-md-6 mb-3">

                     <label class="mode-card auto w-100">


                        <input
                           type="radio"
                           name="mode_nomor"
                           id="mode_auto"
                           value="AUTO"
                           checked>


                        <div class="mode-content">


                           <div class="mode-icon auto">

                              <iconify-icon
                                 icon="material-symbols:auto-awesome"
                                 width="26">
                              </iconify-icon>

                           </div>


                           <div>

                              <div class="mode-title">

                                 Nomor Otomatis

                              </div>


                              <div class="mode-description">

                                 Sistem akan membuat nomor surat
                                 secara otomatis berdasarkan format
                                 dan nomor terakhir yang telah
                                 dikonfigurasi.

                              </div>

                           </div>


                           <div class="mode-check">

                              <iconify-icon
                                 icon="material-symbols:check-circle"
                                 width="23">
                              </iconify-icon>

                           </div>


                        </div>


                     </label>

                  </div>


                  <!-- =================================================
                       MANUAL
                  ================================================== -->

                  <div class="col-md-6 mb-3">

                     <label class="mode-card manual w-100">


                        <input
                           type="radio"
                           name="mode_nomor"
                           id="mode_manual"
                           value="MANUAL">


                        <div class="mode-content">


                           <div class="mode-icon manual">

                              <iconify-icon
                                 icon="material-symbols:edit-document"
                                 width="26">
                              </iconify-icon>

                           </div>


                           <div>

                              <div class="mode-title">

                                 Nomor Manual

                              </div>


                              <div class="mode-description">

                                 Nomor surat akan dimasukkan
                                 secara manual saat membuat
                                 masing-masing surat.

                              </div>

                           </div>


                           <div class="mode-check">

                              <iconify-icon
                                 icon="material-symbols:check-circle"
                                 width="23">
                              </iconify-icon>

                           </div>


                        </div>


                     </label>

                  </div>


               </div>


               <!-- =================================================
                    INFO
               ================================================== -->

               <div class="row">

                  <div class="col-12">

                     <div
                        class="mode-info bg-primary-subtle text-primary"
                        id="modeInfo">

                        <iconify-icon
                           icon="material-symbols:info-outline"
                           style="vertical-align:middle;">
                        </iconify-icon>


                        <strong>Mode Otomatis</strong>

                        aktif.

                        Sistem akan menggunakan format nomor
                        dan nomor terakhir dari masing-masing
                        jenis surat.

                     </div>

                  </div>

               </div>


               <!-- =================================================
                    MANUAL SETTING
               ================================================== -->

               <div
                  id="manualSetting"
                  class="setting-section">


                  <!-- =================================================
                       HEADER MANUAL
                  ================================================== -->

                  <div class="row mb-3">

                     <div class="col-12">

                        <div class="d-flex align-items-center">


                           <div
                              class="surat-icon me-3"
                              style="
                                 background:#e8f7ef;
                                 color:#198754;
                              ">

                              <iconify-icon
                                 icon="material-symbols:edit-document"
                                 width="25">
                              </iconify-icon>

                           </div>


                           <div>

                              <h5 class="fw-semibold mb-1">

                                 Konfigurasi Nomor Surat

                              </h5>


                              <div class="text-muted small">

                                 Atur format nomor dan nomor
                                 tertinggi untuk masing-masing
                                 jenis surat.

                              </div>

                           </div>


                        </div>

                     </div>

                  </div>


                  <!-- =================================================
                       SURAT CARDS
                  ================================================== -->

                  <div class="row g-3">


                     <!-- =================================================
                          SURAT SAKIT
                     ================================================== -->

                     <div class="col-xl-4 col-md-6">

                        <div class="card surat-card">

                           <div class="card-body">


                              <div
                                 class="d-flex align-items-center mb-3">

                                 <div class="surat-icon">

                                    <iconify-icon
                                       icon="material-symbols:sick-outline"
                                       width="25">
                                    </iconify-icon>

                                 </div>


                                 <div class="ms-3">

                                    <div class="surat-title">

                                       Surat Keterangan Sakit

                                    </div>


                                    <div class="surat-description">

                                       Pengaturan nomor surat sakit

                                    </div>

                                 </div>

                              </div>


                              <!-- FORMAT -->

                              <div class="mb-3">

                                 <label class="form-label">

                                    Format Nomor Surat

                                 </label>


                                 <input
                                    type="text"
                                    class="form-control"
                                    id="format_sakit"
                                    placeholder="Contoh: SKS/{NO}/{MM}/{YYYY}"
                                    value="SKS/{NO}/{MM}/{YYYY}">


                                 <div class="form-text">

                                    Gunakan
                                    <strong>{NO}</strong>
                                    untuk nomor urut.

                                 </div>

                              </div>


                              <!-- NOMOR -->

                              <div class="mb-3">

                                 <label class="form-label">

                                    Nomor Tertinggi / Terakhir

                                 </label>


                                 <input
                                    type="number"
                                    class="form-control"
                                    id="nomor_sakit"
                                    min="0"
                                    value="0">

                              </div>


                              <!-- PREVIEW -->

                              <div class="format-preview">

                                 <div class="format-preview-label">

                                    Contoh nomor berikutnya

                                 </div>


                                 <div
                                    class="format-preview-value"
                                    id="preview_sakit">

                                    SKS/001/08/2026

                                 </div>

                              </div>


                           </div>

                        </div>

                     </div>


                     <!-- =================================================
                          SURAT BEROBAT
                     ================================================== -->

                     <div class="col-xl-4 col-md-6">

                        <div class="card surat-card">

                           <div class="card-body">


                              <div
                                 class="d-flex align-items-center mb-3">

                                 <div
                                    class="surat-icon"
                                    style="
                                       background:#fff3cd;
                                       color:#d39e00;
                                    ">

                                    <iconify-icon
                                       icon="material-symbols:medical-services-outline"
                                       width="25">
                                    </iconify-icon>

                                 </div>


                                 <div class="ms-3">

                                    <div class="surat-title">

                                       Surat Keterangan Berobat

                                    </div>


                                    <div class="surat-description">

                                       Pengaturan nomor surat berobat

                                    </div>

                                 </div>

                              </div>


                              <div class="mb-3">

                                 <label class="form-label">

                                    Format Nomor Surat

                                 </label>


                                 <input
                                    type="text"
                                    class="form-control"
                                    id="format_berobat"
                                    placeholder="Contoh: SKB/{NO}/{MM}/{YYYY}"
                                    value="SKB/{NO}/{MM}/{YYYY}">

                                 <div class="form-text">

                                    Gunakan
                                    <strong>{NO}</strong>
                                    untuk nomor urut.

                                 </div>

                              </div>


                              <div class="mb-3">

                                 <label class="form-label">

                                    Nomor Tertinggi / Terakhir

                                 </label>


                                 <input
                                    type="number"
                                    class="form-control"
                                    id="nomor_berobat"
                                    min="0"
                                    value="0">

                              </div>


                              <div class="format-preview">

                                 <div class="format-preview-label">

                                    Contoh nomor berikutnya

                                 </div>


                                 <div
                                    class="format-preview-value"
                                    id="preview_berobat">

                                    SKB/001/08/2026

                                 </div>

                              </div>


                           </div>

                        </div>

                     </div>


                     <!-- =================================================
                          SURAT SEHAT
                     ================================================== -->

                     <div class="col-xl-4 col-md-6">

                        <div class="card surat-card">

                           <div class="card-body">


                              <div
                                 class="d-flex align-items-center mb-3">

                                 <div
                                    class="surat-icon"
                                    style="
                                       background:#e7f1ff;
                                       color:#0d6efd;
                                    ">

                                    <iconify-icon
                                       icon="material-symbols:health-and-safety-outline"
                                       width="25">
                                    </iconify-icon>

                                 </div>


                                 <div class="ms-3">

                                    <div class="surat-title">

                                       Surat Keterangan Sehat

                                    </div>


                                    <div class="surat-description">

                                       Pengaturan nomor surat sehat

                                    </div>

                                 </div>

                              </div>


                              <div class="mb-3">

                                 <label class="form-label">

                                    Format Nomor Surat

                                 </label>


                                 <input
                                    type="text"
                                    class="form-control"
                                    id="format_sehat"
                                    placeholder="Contoh: SKH/{NO}/{MM}/{YYYY}"
                                    value="SKH/{NO}/{MM}/{YYYY}">

                              </div>


                              <div class="mb-3">

                                 <label class="form-label">

                                    Nomor Tertinggi / Terakhir

                                 </label>


                                 <input
                                    type="number"
                                    class="form-control"
                                    id="nomor_sehat"
                                    min="0"
                                    value="0">

                              </div>


                              <div class="format-preview">

                                 <div class="format-preview-label">

                                    Contoh nomor berikutnya

                                 </div>


                                 <div
                                    class="format-preview-value"
                                    id="preview_sehat">

                                    SKH/001/08/2026

                                 </div>

                              </div>


                           </div>

                        </div>

                     </div>


                     <!-- =================================================
                          RAWAT INAP
                     ================================================== -->

                     <div class="col-xl-4 col-md-6">

                        <div class="card surat-card">

                           <div class="card-body">


                              <div
                                 class="d-flex align-items-center mb-3">

                                 <div
                                    class="surat-icon"
                                    style="
                                       background:#f3e8ff;
                                       color:#7c3aed;
                                    ">

                                    <iconify-icon
                                       icon="material-symbols:bed-outline"
                                       width="25">
                                    </iconify-icon>

                                 </div>


                                 <div class="ms-3">

                                    <div class="surat-title">

                                       Surat Keterangan Rawat Inap

                                    </div>


                                    <div class="surat-description">

                                       Pengaturan nomor surat rawat inap

                                    </div>

                                 </div>

                              </div>


                              <div class="mb-3">

                                 <label class="form-label">

                                    Format Nomor Surat

                                 </label>


                                 <input
                                    type="text"
                                    class="form-control"
                                    id="format_rawat_inap"
                                    placeholder="Contoh: SRI/{NO}/{MM}/{YYYY}"
                                    value="SRI/{NO}/{MM}/{YYYY}">

                              </div>


                              <div class="mb-3">

                                 <label class="form-label">

                                    Nomor Tertinggi / Terakhir

                                 </label>


                                 <input
                                    type="number"
                                    class="form-control"
                                    id="nomor_rawat_inap"
                                    min="0"
                                    value="0">

                              </div>


                              <div class="format-preview">

                                 <div class="format-preview-label">

                                    Contoh nomor berikutnya

                                 </div>


                                 <div
                                    class="format-preview-value"
                                    id="preview_rawat_inap">

                                    SRI/001/08/2026

                                 </div>

                              </div>


                           </div>

                        </div>

                     </div>


                     <!-- =================================================
                          SURAT KEMATIAN
                     ================================================== -->

                     <div class="col-xl-4 col-md-6">

                        <div class="card surat-card">

                           <div class="card-body">


                              <div
                                 class="d-flex align-items-center mb-3">

                                 <div
                                    class="surat-icon"
                                    style="
                                       background:#f8d7da;
                                       color:#dc3545;
                                    ">

                                    <iconify-icon
                                       icon="material-symbols:deceased-outline"
                                       width="25">
                                    </iconify-icon>

                                 </div>


                                 <div class="ms-3">

                                    <div class="surat-title">

                                       Surat Keterangan Kematian

                                    </div>


                                    <div class="surat-description">

                                       Pengaturan nomor surat kematian

                                    </div>

                                 </div>

                              </div>


                              <div class="mb-3">

                                 <label class="form-label">

                                    Format Nomor Surat

                                 </label>


                                 <input
                                    type="text"
                                    class="form-control"
                                    id="format_kematian"
                                    placeholder="Contoh: SKM/{NO}/{MM}/{YYYY}"
                                    value="SKM/{NO}/{MM}/{YYYY}">

                              </div>


                              <div class="mb-3">

                                 <label class="form-label">

                                    Nomor Tertinggi / Terakhir

                                 </label>


                                 <input
                                    type="number"
                                    class="form-control"
                                    id="nomor_kematian"
                                    min="0"
                                    value="0">

                              </div>


                              <div class="format-preview">

                                 <div class="format-preview-label">

                                    Contoh nomor berikutnya

                                 </div>


                                 <div
                                    class="format-preview-value"
                                    id="preview_kematian">

                                    SKM/001/08/2026

                                 </div>

                              </div>


                           </div>

                        </div>

                     </div>


                     <!-- =================================================
                          PEMERIKSAAN MATA
                     ================================================== -->

                     <div class="col-xl-4 col-md-6">

                        <div class="card surat-card">

                           <div class="card-body">


                              <div
                                 class="d-flex align-items-center mb-3">

                                 <div
                                    class="surat-icon"
                                    style="
                                       background:#fff3cd;
                                       color:#fd7e14;
                                    ">

                                    <iconify-icon
                                       icon="material-symbols:visibility-outline"
                                       width="25">
                                    </iconify-icon>

                                 </div>


                                 <div class="ms-3">

                                    <div class="surat-title">

                                       Surat Hasil Pemeriksaan Mata

                                    </div>


                                    <div class="surat-description">

                                       Pengaturan nomor pemeriksaan mata

                                    </div>

                                 </div>

                              </div>


                              <div class="mb-3">

                                 <label class="form-label">

                                    Format Nomor Surat

                                 </label>


                                 <input
                                    type="text"
                                    class="form-control"
                                    id="format_mata"
                                    placeholder="Contoh: SPM/{NO}/{MM}/{YYYY}"
                                    value="SPM/{NO}/{MM}/{YYYY}">

                              </div>


                              <div class="mb-3">

                                 <label class="form-label">

                                    Nomor Tertinggi / Terakhir

                                 </label>


                                 <input
                                    type="number"
                                    class="form-control"
                                    id="nomor_mata"
                                    min="0"
                                    value="0">

                              </div>


                              <div class="format-preview">

                                 <div class="format-preview-label">

                                    Contoh nomor berikutnya

                                 </div>


                                 <div
                                    class="format-preview-value"
                                    id="preview_mata">

                                    SPM/001/08/2026

                                 </div>

                              </div>


                           </div>

                        </div>

                     </div>


                  </div>


                  <!-- =================================================
                       SAVE BUTTON
                  ================================================== -->

                  <div class="row mt-4 mb-4">

                     <div class="col-12 text-end">

                        <button
                           type="button"
                           class="btn btn-primary save-button"
                           id="btnSaveSetting">

                           <iconify-icon
                              icon="material-symbols:save-outline"
                              class="me-1">
                           </iconify-icon>

                           Simpan Pengaturan

                        </button>

                     </div>

                  </div>


               </div>


            </div>

         </div>

      </div>

   </div>


   <?php

   require 'library.php';

   ?>
   <script>
      /* =========================================================
   CONFIG
========================================================= */

      const settingSuratApi =
         'controller/letter/settingSuratController.php';


      /* =========================================================
         ELEMENT
      ========================================================= */

      const manualSetting =
         document.getElementById(
            'manualSetting'
         );


      const modeInfo =
         document.getElementById(
            'modeInfo'
         );


      /* =========================================================
         PREVIEW CONFIGURATION
      ========================================================= */

      const previewConfig = [

         [
            'format_sakit',
            'nomor_sakit',
            'preview_sakit'
         ],

         [
            'format_berobat',
            'nomor_berobat',
            'preview_berobat'
         ],

         [
            'format_sehat',
            'nomor_sehat',
            'preview_sehat'
         ],

         [
            'format_rawat_inap',
            'nomor_rawat_inap',
            'preview_rawat_inap'
         ],

         [
            'format_kematian',
            'nomor_kematian',
            'preview_kematian'
         ],

         [
            'format_mata',
            'nomor_mata',
            'preview_mata'
         ]

      ];


      /* =========================================================
         MODE UI
      ========================================================= */

      function updateModeUI(mode) {

         mode =
            String(mode || 'AUTO')
            .toUpperCase();


         /*
         |--------------------------------------------------------------------------
         | RADIO
         |--------------------------------------------------------------------------
         */

         const radio =
            document.querySelector(
               'input[name="mode_nomor"][value="' +
               mode +
               '"]'
            );


         if (radio) {

            radio.checked = true;

         }


         /*
         |--------------------------------------------------------------------------
         | AUTO
         |--------------------------------------------------------------------------
         */

         if (mode === 'AUTO') {


            /*
            |--------------------------------------------------------------------------
            | TAMPILKAN SETTING AUTO
            |--------------------------------------------------------------------------
            */

            manualSetting
               .classList
               .add('active');


            /*
            |--------------------------------------------------------------------------
            | INFO
            |--------------------------------------------------------------------------
            */

            modeInfo
               .classList
               .remove(
                  'bg-success-subtle',
                  'text-success'
               );


            modeInfo
               .classList
               .add(
                  'bg-primary-subtle',
                  'text-primary'
               );


            modeInfo.innerHTML = `

         <iconify-icon
            icon="material-symbols:info-outline"
            style="vertical-align:middle;">
         </iconify-icon>

         <strong>Mode Otomatis</strong>
         aktif.

         Sistem akan membuat nomor surat
         berdasarkan format dan nomor terakhir
         masing-masing jenis surat.

      `;


            /*
            |--------------------------------------------------------------------------
            | ENABLE INPUT
            |--------------------------------------------------------------------------
            */

            setAutoSettingDisabled(
               false
            );


         }


         /*
         |--------------------------------------------------------------------------
         | MANUAL
         |--------------------------------------------------------------------------
         */
         else {


            /*
            |--------------------------------------------------------------------------
            | HILANGKAN SETTING AUTO
            |--------------------------------------------------------------------------
            */

            manualSetting
               .classList
               .remove('active');


            /*
            |--------------------------------------------------------------------------
            | INFO
            |--------------------------------------------------------------------------
            */

            modeInfo
               .classList
               .remove(
                  'bg-primary-subtle',
                  'text-primary'
               );


            modeInfo
               .classList
               .add(
                  'bg-success-subtle',
                  'text-success'
               );


            modeInfo.innerHTML = `

         <iconify-icon
            icon="material-symbols:info-outline"
            style="vertical-align:middle;">
         </iconify-icon>

         <strong>Mode Manual</strong>
         aktif.

         Nomor surat akan diisi langsung
         oleh pengguna pada masing-masing
         form surat.

      `;


            /*
            |--------------------------------------------------------------------------
            | DISABLE INPUT AUTO
            |--------------------------------------------------------------------------
            */

            setAutoSettingDisabled(
               true
            );

         }

      }


      /* =========================================================
         DISABLE / ENABLE AUTO SETTING
      ========================================================= */

      function setAutoSettingDisabled(
         disabled
      ) {

         previewConfig.forEach(
            function(config) {

               const formatInput =
                  document.getElementById(
                     config[0]
                  );


               const numberInput =
                  document.getElementById(
                     config[1]
                  );


               if (formatInput) {

                  formatInput.disabled =
                     disabled;

               }


               if (numberInput) {

                  numberInput.disabled =
                     disabled;

               }

            }
         );

      }


      /* =========================================================
         MODE CHANGE
         KLIK AUTO / MANUAL
         LANGSUNG SIMPAN
      ========================================================= */

      document
         .querySelectorAll(
            'input[name="mode_nomor"]'
         )
         .forEach(function(input) {

            input.addEventListener(
               'change',
               function() {

                  const selectedMode =
                     this.value;


                  /*
                  |--------------------------------------------------------------------------
                  | AMBIL MODE DATABASE SAAT INI
                  |--------------------------------------------------------------------------
                  */

                  const currentRadio =
                     document.querySelector(
                        'input[name="mode_nomor"][data-current="true"]'
                     );


                  const oldMode =
                     currentRadio ?
                     currentRadio.value :
                     null;


                  /*
                  |--------------------------------------------------------------------------
                  | TANYA USER
                  |--------------------------------------------------------------------------
                  */

                  Swal.fire({

                        icon: 'question',

                        title: selectedMode === 'MANUAL' ?
                           'Gunakan Nomor Manual?' :
                           'Gunakan Nomor Otomatis?',

                        html: selectedMode === 'MANUAL' ?
                           `
                     Nomor surat akan
                     <strong>diisi manual</strong>
                     pada masing-masing form surat.
                     ` :
                           `
                     Nomor surat akan
                     <strong>dibuat otomatis</strong>
                     menggunakan format dan nomor
                     terakhir yang telah dikonfigurasi.
                     `,

                        showCancelButton: true,

                        confirmButtonText: 'Ya, Simpan',

                        cancelButtonText: 'Batal',

                        reverseButtons: true

                     })

                     .then(function(result) {


                        /*
                        |--------------------------------------------------------------------------
                        | USER SETUJU
                        |--------------------------------------------------------------------------
                        */

                        if (
                           result.isConfirmed
                        ) {

                           saveModeOnly(
                              selectedMode
                           );

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | USER BATAL
                        |--------------------------------------------------------------------------
                        */
                        else {

                           loadSettingSurat();

                        }

                     });

               }
            );

         });


      /* =========================================================
         SAVE MODE ONLY
      ========================================================= */

      function saveModeOnly(
         mode
      ) {

         const formData =
            new FormData();


         formData.append(
            'action',
            'update_mode'
         );


         formData.append(
            'mode_nomor',
            mode
         );


         /*
         |--------------------------------------------------------------------------
         | LOADING
         |--------------------------------------------------------------------------
         */

         Swal.fire({

            title: 'Menyimpan Mode...',

            text: 'Mohon tunggu.',

            allowOutsideClick: false,

            allowEscapeKey: false,

            didOpen: function() {

               Swal.showLoading();

            }

         });


         /*
         |--------------------------------------------------------------------------
         | POST
         |--------------------------------------------------------------------------
         */

         fetch(
               settingSuratApi, {

                  method: 'POST',

                  body: formData

               }
            )

            .then(function(response) {

               if (!response.ok) {

                  throw new Error(
                     'HTTP Error ' +
                     response.status
                  );

               }

               return response.json();

            })

            .then(function(response) {


               console.log(
                  'SAVE MODE RESPONSE:',
                  response
               );


               if (
                  response.status !==
                  'success'
               ) {

                  throw new Error(
                     response.message ||
                     'Mode gagal disimpan.'
                  );

               }


               /*
               |--------------------------------------------------------------------------
               | UPDATE UI
               |--------------------------------------------------------------------------
               */

               updateModeUI(
                  mode
               );


               /*
               |--------------------------------------------------------------------------
               | MARK CURRENT MODE
               |--------------------------------------------------------------------------
               */

               document
                  .querySelectorAll(
                     'input[name="mode_nomor"]'
                  )
                  .forEach(
                     function(item) {

                        item.removeAttribute(
                           'data-current'
                        );

                     }
                  );


               const selectedRadio =
                  document.querySelector(
                     'input[name="mode_nomor"][value="' +
                     mode +
                     '"]'
                  );


               if (selectedRadio) {

                  selectedRadio.setAttribute(
                     'data-current',
                     'true'
                  );

               }


               /*
               |--------------------------------------------------------------------------
               | SUCCESS
               |--------------------------------------------------------------------------
               */

               Swal.fire({

                  icon: 'success',

                  title: 'Berhasil',

                  text: mode === 'MANUAL' ?
                     'Mode manual berhasil diaktifkan.' :
                     'Mode otomatis berhasil diaktifkan.',

                  timer: 1600,

                  showConfirmButton: false

               });


               /*
               |--------------------------------------------------------------------------
               | LOAD DATABASE
               |--------------------------------------------------------------------------
               */

               setTimeout(
                  function() {

                     loadSettingSurat();

                  },
                  300
               );


            })

            .catch(function(error) {


               console.error(
                  'SAVE MODE ERROR:',
                  error
               );


               /*
               |--------------------------------------------------------------------------
               | KEMBALIKAN DATABASE
               |--------------------------------------------------------------------------
               */

               loadSettingSurat();


               Swal.fire({

                  icon: 'error',

                  title: 'Gagal',

                  text: error.message ||
                     'Mode penomoran gagal disimpan.'

               });

            });

      }


      /* =========================================================
         FORMAT PREVIEW
      ========================================================= */

      function generatePreview(
         formatId,
         numberId,
         previewId
      ) {

         const formatElement =
            document.getElementById(
               formatId
            );


         const numberElement =
            document.getElementById(
               numberId
            );


         const previewElement =
            document.getElementById(
               previewId
            );


         if (
            !formatElement ||
            !numberElement ||
            !previewElement
         ) {

            return;

         }


         const format =
            formatElement.value || '';


         let number =
            parseInt(
               numberElement.value || 0,
               10
            );


         if (
            isNaN(number) ||
            number < 0
         ) {

            number = 0;

         }


         /*
         |--------------------------------------------------------------------------
         | NOMOR BERIKUTNYA
         |--------------------------------------------------------------------------
         */

         const nextNumber =
            String(
               number + 1
            ).padStart(
               3,
               '0'
            );


         /*
         |--------------------------------------------------------------------------
         | DATE
         |--------------------------------------------------------------------------
         */

         const now =
            new Date();


         const yyyy =
            now.getFullYear();


         const yy =
            String(
               yyyy
            ).slice(-2);


         const mm =
            String(
               now.getMonth() + 1
            ).padStart(
               2,
               '0'
            );


         const dd =
            String(
               now.getDate()
            ).padStart(
               2,
               '0'
            );


         /*
         |--------------------------------------------------------------------------
         | REPLACE
         |--------------------------------------------------------------------------
         */

         let result =
            format;


         result =
            result.replace(
               /\{NO\}/gi,
               nextNumber
            );


         result =
            result.replace(
               /\{YYYY\}/gi,
               yyyy
            );


         result =
            result.replace(
               /\{YY\}/gi,
               yy
            );


         result =
            result.replace(
               /\{MM\}/gi,
               mm
            );


         result =
            result.replace(
               /\{DD\}/gi,
               dd
            );


         previewElement.innerText =
            result;

      }


      /* =========================================================
         UPDATE ALL PREVIEW
      ========================================================= */

      function updateAllPreview() {

         previewConfig.forEach(
            function(config) {

               generatePreview(
                  config[0],
                  config[1],
                  config[2]
               );

            }
         );

      }


      /* =========================================================
         BIND PREVIEW
      ========================================================= */

      previewConfig.forEach(
         function(config) {

            const formatInput =
               document.getElementById(
                  config[0]
               );


            const numberInput =
               document.getElementById(
                  config[1]
               );


            if (formatInput) {

               formatInput.addEventListener(
                  'input',
                  function() {

                     generatePreview(
                        config[0],
                        config[1],
                        config[2]
                     );

                  }
               );

            }


            if (numberInput) {

               numberInput.addEventListener(
                  'input',
                  function() {

                     generatePreview(
                        config[0],
                        config[1],
                        config[2]
                     );

                  }
               );

            }

         }
      );


      /* =========================================================
         GET SETTING
      ========================================================= */

      function loadSettingSurat() {

         fetch(
               settingSuratApi, {

                  method: 'GET',

                  cache: 'no-store'

               }
            )

            .then(function(response) {

               if (!response.ok) {

                  throw new Error(
                     'HTTP Error ' +
                     response.status
                  );

               }

               return response.json();

            })

            .then(function(response) {


               console.log(
                  'GET SETTING:',
                  response
               );


               if (
                  response.status !==
                  'success'
               ) {

                  throw new Error(
                     response.message ||
                     'Gagal mengambil setting.'
                  );

               }


               const d =
                  response.data || {};


               /*
               |--------------------------------------------------------------------------
               | MODE
               |--------------------------------------------------------------------------
               */

               updateModeUI(
                  d.mode_nomor ||
                  'AUTO'
               );


               /*
               |--------------------------------------------------------------------------
               | MARK CURRENT MODE
               |--------------------------------------------------------------------------
               */

               document
                  .querySelectorAll(
                     'input[name="mode_nomor"]'
                  )
                  .forEach(
                     function(item) {

                        item.removeAttribute(
                           'data-current'
                        );

                     }
                  );


               const currentRadio =
                  document.querySelector(
                     'input[name="mode_nomor"][value="' +
                     (
                        d.mode_nomor ||
                        'AUTO'
                     ) +
                     '"]'
                  );


               if (currentRadio) {

                  currentRadio.setAttribute(
                     'data-current',
                     'true'
                  );

               }


               /*
               |--------------------------------------------------------------------------
               | SURAT SAKIT
               |--------------------------------------------------------------------------
               */

               setValue(
                  'format_sakit',
                  d.format_sakit
               );


               setValue(
                  'nomor_sakit',
                  d.nomor_sakit
               );


               /*
               |--------------------------------------------------------------------------
               | BEROBAT
               |--------------------------------------------------------------------------
               */

               setValue(
                  'format_berobat',
                  d.format_berobat
               );


               setValue(
                  'nomor_berobat',
                  d.nomor_berobat
               );


               /*
               |--------------------------------------------------------------------------
               | SEHAT
               |--------------------------------------------------------------------------
               */

               setValue(
                  'format_sehat',
                  d.format_sehat
               );


               setValue(
                  'nomor_sehat',
                  d.nomor_sehat
               );


               /*
               |--------------------------------------------------------------------------
               | RAWAT INAP
               |--------------------------------------------------------------------------
               */

               setValue(
                  'format_rawat_inap',
                  d.format_rawat_inap
               );


               setValue(
                  'nomor_rawat_inap',
                  d.nomor_rawat_inap
               );


               /*
               |--------------------------------------------------------------------------
               | KEMATIAN
               |--------------------------------------------------------------------------
               */

               setValue(
                  'format_kematian',
                  d.format_kematian
               );


               setValue(
                  'nomor_kematian',
                  d.nomor_kematian
               );


               /*
               |--------------------------------------------------------------------------
               | MATA
               |--------------------------------------------------------------------------
               */

               setValue(
                  'format_mata',
                  d.format_mata
               );


               setValue(
                  'nomor_mata',
                  d.nomor_mata
               );


               /*
               |--------------------------------------------------------------------------
               | PREVIEW
               |--------------------------------------------------------------------------
               */

               updateAllPreview();

            })

            .catch(function(error) {

               console.error(
                  'GET SETTING ERROR:',
                  error
               );


               Swal.fire({

                  icon: 'error',

                  title: 'Gagal',

                  text: error.message ||
                     'Gagal mengambil pengaturan nomor surat.'

               });

            });

      }


      /* =========================================================
         SET VALUE
      ========================================================= */

      function setValue(
         elementId,
         value
      ) {

         const element =
            document.getElementById(
               elementId
            );


         if (!element) {

            return;

         }


         if (
            value === null ||
            value === undefined
         ) {

            return;

         }


         element.value =
            value;

      }


      /* =========================================================
         GET INPUT VALUE
      ========================================================= */

      function getInputValue(
         elementId
      ) {

         const element =
            document.getElementById(
               elementId
            );


         if (!element) {

            return '';

         }


         return element.value.trim();

      }


      /* =========================================================
         COLLECT DATA
      ========================================================= */

      function collectSettingData() {

         const modeElement =
            document.querySelector(
               'input[name="mode_nomor"]:checked'
            );


         const mode =
            modeElement ?
            modeElement.value :
            'AUTO';


         const formData =
            new FormData();


         formData.append(
            'action',
            'save_setting'
         );


         formData.append(
            'mode_nomor',
            mode
         );


         formData.append(
            'format_sakit',
            getInputValue(
               'format_sakit'
            )
         );


         formData.append(
            'nomor_sakit',
            getInputValue(
               'nomor_sakit'
            )
         );


         formData.append(
            'format_berobat',
            getInputValue(
               'format_berobat'
            )
         );


         formData.append(
            'nomor_berobat',
            getInputValue(
               'nomor_berobat'
            )
         );


         formData.append(
            'format_sehat',
            getInputValue(
               'format_sehat'
            )
         );


         formData.append(
            'nomor_sehat',
            getInputValue(
               'nomor_sehat'
            )
         );


         formData.append(
            'format_rawat_inap',
            getInputValue(
               'format_rawat_inap'
            )
         );


         formData.append(
            'nomor_rawat_inap',
            getInputValue(
               'nomor_rawat_inap'
            )
         );


         formData.append(
            'format_kematian',
            getInputValue(
               'format_kematian'
            )
         );


         formData.append(
            'nomor_kematian',
            getInputValue(
               'nomor_kematian'
            )
         );


         formData.append(
            'format_mata',
            getInputValue(
               'format_mata'
            )
         );


         formData.append(
            'nomor_mata',
            getInputValue(
               'nomor_mata'
            )
         );


         return formData;

      }


      /* =========================================================
         SAVE FULL SETTING
      ========================================================= */

      function saveSettingSurat() {

         /*
         |--------------------------------------------------------------------------
         | HANYA AUTO
         |--------------------------------------------------------------------------
         */

         const mode =
            document.querySelector(
               'input[name="mode_nomor"]:checked'
            );


         if (!mode) {

            Swal.fire(
               'Perhatian',
               'Pilih mode penomoran terlebih dahulu.',
               'warning'
            );

            return;

         }


         if (
            mode.value !== 'AUTO'
         ) {

            Swal.fire({

               icon: 'info',

               title: 'Mode Manual',

               text: 'Pada mode manual tidak perlu mengatur format dan nomor terakhir.'

            });

            return;

         }


         /*
         |--------------------------------------------------------------------------
         | BUTTON
         |--------------------------------------------------------------------------
         */

         const button =
            document.getElementById(
               'btnSaveSetting'
            );


         const oldButtonHtml =
            button ?
            button.innerHTML :
            '';


         if (button) {

            button.disabled =
               true;


            button.innerHTML = `

         <span
            class="spinner-border spinner-border-sm me-1">
         </span>

         Menyimpan...

      `;

         }


         /*
         |--------------------------------------------------------------------------
         | DATA
         |--------------------------------------------------------------------------
         */

         const formData =
            collectSettingData();


         /*
         |--------------------------------------------------------------------------
         | POST
         |--------------------------------------------------------------------------
         */

         fetch(
               settingSuratApi, {

                  method: 'POST',

                  body: formData

               }
            )

            .then(function(response) {

               if (!response.ok) {

                  throw new Error(
                     'HTTP Error ' +
                     response.status
                  );

               }

               return response.json();

            })

            .then(function(response) {


               console.log(
                  'SAVE SETTING:',
                  response
               );


               if (
                  response.status !==
                  'success'
               ) {

                  throw new Error(
                     response.message ||
                     'Gagal menyimpan setting.'
                  );

               }


               /*
               |--------------------------------------------------------------------------
               | UPDATE FORM
               |--------------------------------------------------------------------------
               */

               updateFormFromResponse(
                  response.data || {}
               );


               /*
               |--------------------------------------------------------------------------
               | PREVIEW
               |--------------------------------------------------------------------------
               */

               updateAllPreview();


               /*
               |--------------------------------------------------------------------------
               | SUCCESS
               |--------------------------------------------------------------------------
               */

               Swal.fire({

                  icon: 'success',

                  title: 'Berhasil',

                  text: 'Konfigurasi nomor surat berhasil disimpan.',

                  timer: 1600,

                  showConfirmButton: false

               });


               /*
               |--------------------------------------------------------------------------
               | LOAD ULANG
               |--------------------------------------------------------------------------
               */

               setTimeout(
                  function() {

                     loadSettingSurat();

                  },
                  300
               );

            })

            .catch(function(error) {

               console.error(
                  'SAVE SETTING ERROR:',
                  error
               );


               Swal.fire({

                  icon: 'error',

                  title: 'Gagal',

                  text: error.message ||
                     'Gagal menyimpan konfigurasi nomor surat.'

               });

            })

            .finally(function() {

               if (button) {

                  button.disabled =
                     false;


                  button.innerHTML =
                     oldButtonHtml;

               }

            });

      }


      /* =========================================================
         UPDATE FORM FROM RESPONSE
      ========================================================= */

      function updateFormFromResponse(
         d
      ) {

         updateModeUI(
            d.mode_nomor ||
            'AUTO'
         );


         const fields = [

            'format_sakit',
            'nomor_sakit',

            'format_berobat',
            'nomor_berobat',

            'format_sehat',
            'nomor_sehat',

            'format_rawat_inap',
            'nomor_rawat_inap',

            'format_kematian',
            'nomor_kematian',

            'format_mata',
            'nomor_mata'

         ];


         fields.forEach(
            function(field) {

               if (
                  d[field] !==
                  undefined
               ) {

                  setValue(
                     field,
                     d[field]
                  );

               }

            }
         );

      }


      /* =========================================================
         SAVE BUTTON
      ========================================================= */

      const saveButton =
         document.getElementById(
            'btnSaveSetting'
         );


      if (saveButton) {

         saveButton.addEventListener(
            'click',
            function() {

               saveSettingSurat();

            }
         );

      }


      /* =========================================================
         INITIAL LOAD
      ========================================================= */

      document.addEventListener(
         'DOMContentLoaded',
         function() {

            updateAllPreview();

            loadSettingSurat();

         }
      );
   </script>

</body>

</html>