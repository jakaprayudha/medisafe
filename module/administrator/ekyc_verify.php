<?php
// Verifikasi eKYC lintas faskes (superadmin/administrator). Memakai halaman eKYC yang sama dengan admin klinik
// (module/admin/ekyc_tte.php) dalam mode global: kolom & filter Faskes, tanpa tombol "Tambah pemohon".
define('EKYC_GLOBAL', true);
require __DIR__ . '/../admin/ekyc_tte.php';
