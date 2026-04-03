  <?php
   $current = basename($_SERVER['PHP_SELF']); // ambil nama file aktif, misalnya "permintaan_farmasi.php"
   ?>
  <nav>
     <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a href="module/admin/triase?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'triase.php') ? 'active' : '' ?>">Triase</button>
        </a>
        <a href="module/admin/rme_inap?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'rme_inap.php') ? 'active' : '' ?>">Pemeriksaan Medis</button>
        </a>
        <a href="module/admin/instruksi?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'instruksi.php') ? 'active' : '' ?>">Instruksi Dokter</button>
        </a>
        <a href="module/admin/permintaan_farmasi?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'permintaan_farmasi.php') ? 'active' : '' ?>">Permintaan Farmasi</button>
        </a>
        <a href="module/admin/resep?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'resep.php') ? 'active' : '' ?>">Resep Luar</button>
        </a>
        <a href="module/admin/penunjang?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'penunjang.php') ? 'active' : '' ?>">Penunjang Medis</button>
        </a>
        <a href="module/admin/vaksin?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'vaksin.php') ? 'active' : '' ?>">Vaksin</button>
        </a>
        <a href="module/admin/billing?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'billing.php') ? 'active' : '' ?>">Biaya</button>
        </a>
        <a href="module/admin/riwayat?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'riwayat.php') ? 'active' : '' ?>">Riwayat Pengobatan</button>
        </a>
        <a href="module/admin/cppt?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'cppt.php') ? 'active' : '' ?>">CPPT</button>
        </a>
        <a href="module/admin/form_sep?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_sep.php') ? 'active' : '' ?>">SEP</button>
        </a>
        <a href="module/admin/form_pernyataan?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_pernyataan.php') ? 'active' : '' ?>">Pernyataan Peserta</button>
        </a>
        <a href="module/admin/form_capture_patient?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_capture_patient.php') ? 'active' : '' ?>">Foto Pasien</button>
        </a>
        <a href="module/admin/form_dok_perawatan?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_dok_perawatan.php') ? 'active' : '' ?>">Foto Perawatan</button>
        </a>
        <a href="module/admin/form_dok_pasien?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_dok_pasien.php') ? 'active' : '' ?>">Dokumen Pasien</button>
        </a>
        <a href="module/admin/form_ekg?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_ekg.php') ? 'active' : '' ?>">EKG</button>
        </a>
        <!-- <a href="module/admin/form_usg?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_usg.php') ? 'active' : '' ?>">USG</button>
        </a> -->
        <a href="module/admin/form_io_ranap?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_io_ranap.php') ? 'active' : '' ?>">Form Masuk Keluar Rawat Inap</button>
        </a>
        <!-- <a href="module/admin/form_lbp?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_lbp.php') ? 'active' : '' ?>">LBP</button>
        </a> -->
        <!-- <a href="module/admin/form_rekap_persalinan?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_rekap_persalinan.php') ? 'active' : '' ?>">Pelayanan Persalinan</button>
        </a> -->
        <!-- <a href="module/admin/form_upload_buku_kia?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_upload_buku_kia.php') ? 'active' : '' ?>">Buku KIA</button>
        </a> -->
        <!-- <a href="module/admin/form_kb?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_kb.php') ? 'active' : '' ?>">Status KB</button>
        </a> -->
        <a href="module/admin/resume_medis?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=c">
           <button class="nav-link <?= ($current == 'resume_medis.php') ? 'active' : '' ?>">Resume Medis</button>
        </a>
        <button onclick="window.history.back()" class="nav-link <?= ($current == 'pemeriksaan-rawat-inap') ? '' : '' ?>">Kembali</button>
        <?php
         require 'trigger_pulang.php';
         ?>
     </div>
  </nav>