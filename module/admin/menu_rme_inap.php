<?php
// pasien persalinan: halaman yang dibuka dari menu persalinan tetap menampilkan menu persalinan (bukan menu rawat inap)
if (($_GET['menu'] ?? '') === 'persalinan') {
   require __DIR__ . '/menu_persalinan.php';
   return;
}
?>
  <?php
   $current = basename($_SERVER['PHP_SELF']); // ambil nama file aktif, misalnya "permintaan_farmasi.php"
   $roles = $_SESSION['roles'];
   ?>
  <nav>
     <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <?php
         if ($roles == "dokter" or $roles == "admin") { ?>
           <a href="module/admin/rme_inap?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'rme_inap.php') ? 'active' : '' ?>">Pemeriksaan Medis</button>
           </a>
              <a href="module/admin/pemeriksaan_generalis_tubuh?no=<?= urlencode($_GET['no'] ?? '') ?>&rm=<?= urlencode($_GET['rm'] ?? '') ?>&rme=c">
                 <button class="nav-link <?= ($current == 'pemeriksaan_generalis_tubuh.php') ? 'active' : '' ?>">Pemeriksaan Generalis Tubuh</button>
              </a>
           <a href="module/admin/triase?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'triase.php') ? 'active' : '' ?>">Triase</button>
           </a>
           <!-- <a href="module/admin/instruksi?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'instruksi.php') ? 'active' : '' ?>">Instruksi Dokter</button>
           </a> -->
           <a href="module/admin/permintaan_farmasi?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'permintaan_farmasi.php') ? 'active' : '' ?>">Permintaan Farmasi</button>
           </a>
           <a href="module/admin/cpo?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'cpo.php') ? 'active' : '' ?>">CPO</button>
           </a>
           <a href="module/admin/resep?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'resep.php') ? 'active' : '' ?>">Resep Luar</button>
           </a>
           <a href="module/admin/penunjang?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'penunjang.php') ? 'active' : '' ?>">Pemeriksaan Laboratorium</button>
           </a>
           <a href="module/admin/vaksin?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'vaksin.php') ? 'active' : '' ?>">Vaksin</button>
           </a>
           <a href="module/admin/billing?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'billing.php') ? 'active' : '' ?>">Tindakan</button>
           </a>
           <a href="module/admin/riwayat?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'riwayat.php') ? 'active' : '' ?>">Riwayat Pengobatan</button>
           </a>
           <a href="module/admin/persalinan_anc?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'persalinan_anc.php') ? 'active' : '' ?>">Persalinan (ANC)</button>
           </a>
           <a href="module/admin/penanda_operasi?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'penanda_operasi.php') ? 'active' : '' ?>">Penanda Operasi</button>
           </a>
           <a href="module/admin/cppt?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'cppt.php') ? 'active' : '' ?>">CPPT</button>
           </a>
           <!-- <a href="module/admin/form_capture_patient?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_capture_patient.php') ? 'active' : '' ?>">Foto Pasien</button>
            </a> -->
           <a href="module/admin/form_dok_perawatan?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'form_dok_perawatan.php') ? 'active' : '' ?>">Foto Perawatan</button>
           </a>
           <a href="module/admin/form_dok_pasien?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'form_dok_pasien.php') ? 'active' : '' ?>">Dokumen Pasien</button>
           </a>
           <a href="module/admin/form_ekg?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'form_ekg.php') ? 'active' : '' ?>">EKG</button>
           </a>
           <a href="module/admin/form_gizi?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'form_gizi.php') ? 'active' : '' ?>">Gizi & Nutrisi</button>
           </a>

           <!-- <a href="module/admin/form_usg?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
               <button class="nav-link <?= ($current == 'form_usg.php') ? 'active' : '' ?>">USG</button>
            </a> -->
           <!-- <a href="module/admin/form_io_ranap?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_io_ranap.php') ? 'active' : '' ?>">Form Masuk Keluar Rawat Inap</button>
             </a> -->
           <!-- <a href="module/admin/form_lbp?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_lbp.php') ? 'active' : '' ?>">LBP</button>
            </a> -->
           <!-- <a href="module/admin/form_rekap_persalinan?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
               <button class="nav-link <?= ($current == 'form_rekap_persalinan.php') ? 'active' : '' ?>">Pelayanan Persalinan</button>
            </a> -->
           <!-- <a href="module/admin/form_upload_buku_kia?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
               <button class="nav-link <?= ($current == 'form_upload_buku_kia.php') ? 'active' : '' ?>">Buku KIA</button>
            </a> -->
           <!-- <a href="module/admin/form_kb?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
               <button class="nav-link <?= ($current == 'form_kb.php') ? 'active' : '' ?>">Status KB</button>
            </a> -->

           <a href="module/admin/resume_medis?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'resume_medis.php') ? 'active' : '' ?>">Resume Medis</button>
           </a>
           <a href="module/doctor/pemeriksaan-rawat-inap">
              <button class="nav-link text-primary">Kembali</button>
           </a>
        <?php   } else { ?>
           <a href="module/admin/permintaan_farmasi?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'permintaan_farmasi.php') ? 'active' : '' ?>">Permintaan Farmasi</button>
           </a>
           <a href="module/admin/cppt?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'cppt.php') ? 'active' : '' ?>">CPPT</button>
           </a>
           <a href="module/admin/cpo?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
              <button class="nav-link <?= ($current == 'cpo.php') ? 'active' : '' ?>">CPO</button>
           </a>
        <?php  }
         ?>

        <?php
         if ($roles == 'dokter' or $roles == 'admin') {
            require 'trigger_pulang.php';
         }
         ?>
     </div>
  </nav>