<?php
/**
 * Menu atas RME pasien persalinan. Semua tautan membawa &menu=persalinan sehingga halaman tujuan (yang memuat
 * menu_rme_inap.php untuk rme=c) tetap menampilkan menu ini — dulu setelah diklik menu berubah ke menu rawat inap.
 * Perbaikan tujuan: "Pemeriksaan Medis" ke rme_persalinan (dulu rme_inap), "Instruksi Dokter" ke instruksi (dulu rme_inap),
 * "Permintaan Farmasi" ke permintaan_farmasi (dulu instruksi), "SEP" ke form_upload_klaim (form_sep sudah tidak ada -> 404).
 */
// file aktif (SCRIPT_FILENAME selalu berakhiran .php; PHP_SELF bisa tanpa .php bila URL ditulis ulang)
$current = basename((string) ($_SERVER['SCRIPT_FILENAME'] ?: $_SERVER['PHP_SELF']));
$mpQuery = http_build_query(['no' => (string) ($_GET['no'] ?? ''), 'rm' => (string) ($_GET['rm'] ?? ''), 'rme' => 'c', 'menu' => 'persalinan']);
$mpMenu = [
   ['rme_persalinan', 'Pemeriksaan Medis'],
   ['triase', 'Triase'],
   ['instruksi', 'Instruksi Dokter'],
   ['permintaan_farmasi', 'Permintaan Farmasi'],
   ['resep', 'Resep Luar'],
   ['penunjang', 'Penunjang Medis'],
   ['vaksin', 'Vaksin'],
   ['billing', 'Biaya'],
   ['riwayat', 'Riwayat Pengobatan'],
   ['cppt', 'CPPT'],
   ['form_upload_klaim', 'SEP'],
   ['form_pernyataan', 'Pernyataan Peserta'],
   ['form_capture_patient', 'Foto Pasien'],
   ['form_dok_perawatan', 'Foto Perawatan'],
   ['form_dok_pasien', 'Dokumen Pasien'],
   ['form_ekg', 'EKG'],
   ['form_usg', 'USG'],
   ['form_io_ranap', 'Form Masuk Keluar Rawat Inap'],
   ['form_rekap_persalinan', 'Pelayanan Persalinan'],
   ['form_upload_buku_kia', 'Buku KIA'],
   ['form_kb', 'Status KB'],
   ['resume_medis', 'Resume Medis'],
];
?>
<nav>
   <div class="nav nav-tabs" id="nav-tab" role="tablist">
      <?php foreach ($mpMenu as [$mpPage, $mpLabel]): ?>
         <a href="module/admin/<?= $mpPage ?>?<?= htmlspecialchars($mpQuery, ENT_QUOTES, 'UTF-8') ?>">
            <button class="nav-link <?= $current === $mpPage . '.php' ? 'active' : '' ?>"><?= htmlspecialchars($mpLabel, ENT_QUOTES, 'UTF-8') ?></button>
         </a>
      <?php endforeach; ?>
      <button type="button" onclick="window.history.back()" class="nav-link">Kembali</button>
   </div>
</nav>
