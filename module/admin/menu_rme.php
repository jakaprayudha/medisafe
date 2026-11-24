  <?php
   $current = basename($_SERVER['PHP_SELF']); // ambil nama file aktif, misalnya "permintaan_farmasi.php"
   ?>
  <nav>
     <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <?php ?>
        <a href="module/admin/kunjungan?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'kunjungan.php') ? 'active' : '' ?>">Pemeriksaan Medis</button>
        </a>
        <a href="module/admin/odontogram?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'odontogram.php') ? 'active' : '' ?>">Odontogram</button>
        </a>
        <a href="module/admin/permintaan_farmasi?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'permintaan_farmasi.php') ? 'active' : '' ?>">Permintaan Farmasi</button>
        </a>
        <a href="module/admin/resep?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'resep.php') ? 'active' : '' ?>">Resep Luar</button>
        </a>
        <a href="module/admin/penunjang?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'penunjang.php') ? 'active' : '' ?>">Pemeriksaan Laboratorium</button>
        </a>
        <a href="module/admin/vaksin?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'vaksin.php') ? 'active' : '' ?>">Vaksin</button>
        </a>
        <a href="module/admin/imunisasi?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'imunisasi.php') ? 'active' : '' ?>">Imunisasi</button>
        </a>
        <a href="module/admin/form_dinkes_balita?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'form_dinkes_balita.php') ? 'active' : '' ?>">Dinkes Balita</button>
        </a>
        <a href="module/admin/billing?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'billing.php') ? 'active' : '' ?>">Tindakan</button>
        </a>
        <a href="module/admin/riwayat?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'riwayat.php') ? 'active' : '' ?>">Riwayat Pengobatan</button>
        </a>
        <a href="module/admin/persalinan_anc?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'persalinan_anc.php') ? 'active' : '' ?>">Persalinan (ANC)</button>
        </a>
        <a href="module/admin/penanda_operasi?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link <?= ($current == 'penanda_operasi.php') ? 'active' : '' ?>">Penanda Operasi</button>
        </a>
        <?php
         $roles = $_SESSION['roles'];
         if ($roles == "dokter" or $roles == "admin") { ?>
           <a href="module/admin/rawat_inap?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
              <button class="nav-link <?= ($current == 'rawat_inap.php') ? 'active' : '' ?>">Rawat Inap</button>
           </a>
        <?php }
         if ($roles == "dokter" || $roles == 'admin') {
            require 'trigger_pulang.php';
         }
         ?>
        <a href="module/admin/rme_new?no=<?= urlencode((string) ($_GET['no'] ?? '')) ?>&rm=<?= urlencode((string) ($_GET['rm'] ?? '')) ?>&rme=a">
           <button class="nav-link text-primary <?= ($current == 'rme_new.php') ? 'active' : '' ?>">View RME (Baru)</button>
        </a>
     </div>
  </nav>