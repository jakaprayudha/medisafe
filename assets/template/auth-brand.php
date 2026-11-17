<?php
// Panel brand kiri untuk halaman Login & Reset Password.
// Variabel opsional: $brandBadge, $brandTitle (boleh HTML <span>), $brandText
$brandBadge = $brandBadge ?? 'Sistem Informasi Klinik';
$brandTitle = $brandTitle ?? 'Kelola klinik <span>lebih cepat</span> &amp; terintegrasi';
$brandText = $brandText ?? 'Data pasien, dokter, farmasi, dan layanan klinik dalam satu sistem untuk mendukung pelayanan kesehatan yang optimal.';
?>
<aside class="auth-brand" aria-hidden="true">
  <div class="brand-logo">
    <img src="assets/images/logos/medisafe_logo.png" alt="Medisafe">
  </div>

  <div class="brand-hero">
    <div class="brand-badge"><span class="dot"></span><?= htmlspecialchars($brandBadge) ?></div>
    <h1><?= $brandTitle ?></h1>
    <p><?= htmlspecialchars($brandText) ?></p>

    <ul class="brand-features">
      <li>
        <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <path d="M14 2v6h6M12 18v-6M9 15h6" />
          </svg></span>
        Rekam Medis Elektronik
      </li>
      <li>
        <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            <path d="m9 12 2 2 4-4" />
          </svg></span>
        Bridging BPJS &amp; SATUSEHAT
      </li>
      <li>
        <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7z" />
            <path d="m8.5 8.5 7 7" />
          </svg></span>
        Farmasi &amp; Stok Obat
      </li>
      <li>
        <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 3v18h18" />
            <path d="m19 9-5 5-4-4-3 3" />
          </svg></span>
        Laporan &amp; Monitoring
      </li>
    </ul>
  </div>

  <div class="brand-foot">&copy; <?= date('Y') ?> Medisafe &middot; Clinic Management System</div>
</aside>
