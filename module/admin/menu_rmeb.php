  <?php
   $current = basename($_SERVER['PHP_SELF']); // ambil nama file aktif, misalnya "permintaan_farmasi.php"
   ?>
  <nav>
     <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <a href="module/admin/pemeriksaan_b?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=b">
           <button class="nav-link <?= ($current == 'pemeriksaan_b.php') ? 'active' : '' ?>">Pemeriksaan Medis</button>
        </a>
        <a href="module/admin/permintaan_farmasi?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=b">
           <button class="nav-link <?= ($current == 'permintaan_farmasi.php') ? 'active' : '' ?>">Permintaan Farmasi</button>
        </a>
        <a href="module/admin/resep?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=b">
           <button class="nav-link <?= ($current == 'resep.php') ? 'active' : '' ?>">Resep Luar</button>
        </a>
        <a href="module/admin/penunjang?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=b">
           <button class="nav-link <?= ($current == 'penunjang.php') ? 'active' : '' ?>">Penunjang Medis</button>
        </a>
        <a href="module/admin/vaksin?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=b">
           <button class="nav-link <?= ($current == 'vaksin.php') ? 'active' : '' ?>">Vaksin</button>
        </a>
        <a href="module/admin/billing?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=b">
           <button class="nav-link <?= ($current == 'billing.php') ? 'active' : '' ?>">Biaya</button>
        </a>
        <a href="module/admin/riwayat?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=b">
           <button class="nav-link <?= ($current == 'riwayat.php') ? 'active' : '' ?>">Riwayat Pengobatan</button>
        </a>
        <a href="module/admin/rawat_inap?no=<?= $_GET['no'] ?>&rm=<?= $_GET['rm'] ?>&rme=b">
           <button class="nav-link <?= ($current == 'rawat_inap.php') ? 'active' : '' ?>">Rawat Inap</button>
        </a>
     </div>
  </nav>