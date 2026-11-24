<?php
/**
 * Mode sematan (?embed=1) untuk halaman RME yang ditampilkan di dalam View RME Baru (module/admin/rme_new):
 * tanpa sidebar, navbar, menu RME, dan kartu pasien — halaman induk sudah menampilkannya.
 * Pemakaian: setelah require head.php -> require __DIR__ . '/embed_mode.php'; lalu bungkus sidebar/navbar/menu/kartu
 * dengan if (!$EMBED). $EMBED didefinisikan di sini bila belum.
 */
if (!isset($EMBED)) $EMBED = !empty($_GET['embed']);
if ($EMBED): ?>
  <style>
    html, body { background: #fff !important; }
    .page-wrapper { overflow-x: clip; }
    .body-wrapper { margin-left: 0 !important; padding-top: 0 !important; min-height: 0 !important; background: #fff !important; }
    .body-wrapper-inner { padding: 0 !important; margin-top: 0 !important; }   /* ruang navbar tidak diperlukan */
    .ms-ada-foot { padding-bottom: 0 !important; }
    .body-wrapper-inner, .body-wrapper-inner > .container-fluid, .page-wrapper { min-height: 0 !important; background: #fff !important; border-radius: 0 !important; }
    .body-wrapper > .container-fluid, .body-wrapper-inner > .container-fluid { padding: 4px 2px !important; max-width: none !important; }
    .body-wrapper .card { box-shadow: none !important; border: 0 !important; margin-bottom: 0 !important; }
    .body-wrapper .card-body.p-4 { padding: 4px 2px !important; }
    footer, .app-footer, .copyright { display: none !important; }
  </style>
<?php endif;
