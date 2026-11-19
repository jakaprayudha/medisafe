  <?php
   $current = basename($_SERVER['PHP_SELF']); // ambil nama file aktif, misalnya "permintaan_farmasi.php"
   ?>
  <nav>
     <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a href="module/admin/triase?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'triase.php') ? 'active' : '' ?>">Triase</button>
        </a>
        <a href="module/admin/rme_inap?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'rme_inap.php') ? 'active' : '' ?>">Pemeriksaan Medis</button>
        </a>
        <a href="module/admin/rme_inap?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'rme_inap.php') ? 'active' : '' ?>">Instruksi Dokter</button>
        </a>
        <a href="module/admin/instruksi?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'instruksi.php') ? 'active' : '' ?>">Permintaan Farmasi</button>
        </a>
        <a href="module/admin/resep?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'resep.php') ? 'active' : '' ?>">Resep Luar</button>
        </a>
        <a href="module/admin/penunjang?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'penunjang.php') ? 'active' : '' ?>">Penunjang Medis</button>
        </a>
        <a href="module/admin/vaksin?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'vaksin.php') ? 'active' : '' ?>">Vaksin</button>
        </a>
        <a href="module/admin/billing?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'billing.php') ? 'active' : '' ?>">Biaya</button>
        </a>
        <a href="module/admin/riwayat?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'riwayat.php') ? 'active' : '' ?>">Riwayat Pengobatan</button>
        </a>
        <a href="module/admin/cppt?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'cppt.php') ? 'active' : '' ?>">CPPT</button>
        </a>
        <a href="module/admin/form_sep?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_sep.php') ? 'active' : '' ?>">SEP</button>
        </a>
        <a href="module/admin/form_pernyataan?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_pernyataan.php') ? 'active' : '' ?>">Pernyataan Peserta</button>
        </a>
        <a href="module/admin/form_capture_patient?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_capture_patient.php') ? 'active' : '' ?>">Foto Pasien</button>
        </a>
        <a href="module/admin/form_dok_perawatan?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_dok_perawatan.php') ? 'active' : '' ?>">Foto Perawatan</button>
        </a>
        <a href="module/admin/form_dok_pasien?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_dok_pasien.php') ? 'active' : '' ?>">Dokumen Pasien</button>
        </a>
        <a href="module/admin/form_ekg?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_ekg.php') ? 'active' : '' ?>">EKG</button>
        </a>
        <a href="module/admin/form_usg?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_usg.php') ? 'active' : '' ?>">USG</button>
        </a>
        <a href="module/admin/form_io_ranap?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_io_ranap.php') ? 'active' : '' ?>">Form Masuk Keluar Rawat Inap</button>
        </a>
        <!-- <a href="module/admin/form_lbp?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_lbp.php') ? 'active' : '' ?>">LBP</button>
        </a> -->
        <a href="module/admin/form_rekap_persalinan?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_rekap_persalinan.php') ? 'active' : '' ?>">Pelayanan Persalinan</button>
        </a>
        <a href="module/admin/form_upload_buku_kia?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_upload_buku_kia.php') ? 'active' : '' ?>">Buku KIA</button>
        </a>
        <a href="module/admin/form_kb?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'form_kb.php') ? 'active' : '' ?>">Status KB</button>
        </a>
        <a href="module/admin/resume_medis?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=c">
           <button class="nav-link <?= ($current == 'resume_medis.php') ? 'active' : '' ?>">Resume Medis</button>
        </a>
        <button onclick="window.history.back()" class="nav-link <?= ($current == 'pemeriksaan-rawat-inap') ? '' : '' ?>">Kembali</button>
     </div>
  </nav>