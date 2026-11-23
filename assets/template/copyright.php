<?php
/**
 * Footer hak cipta untuk semua halaman ber-layout (dimuat lewat module/admin/library.php).
 * Ditaruh di akhir .body-wrapper-inner supaya sejajar dengan konten (tidak tertutup sidebar).
 * Halaman yang sudah memakai assets/template/footer.php (footer tetap) tidak diberi footer kedua.
 */
if (!defined('MEDISAFE_COPYRIGHT')) {
   define('MEDISAFE_COPYRIGHT', true);
   $tahunCopy = (int) (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y');
   $rentangCopy = $tahunCopy > 2024 ? '2024–' . $tahunCopy : '2024';
?>
   <style>
      /* footer menempel di bawah area konten walau isi halaman pendek */
      .body-wrapper-inner.ms-ada-footer { display: flex; flex-direction: column; }
      .body-wrapper-inner.ms-ada-footer > .ms-copyright { margin-top: auto; }
      .ms-copyright { padding: 14px 24px 18px; text-align: center; font-size: 12.5px; color: #64748b; }
      .ms-copyright b { color: #475569; font-weight: 600; }
      @media print { .ms-copyright { display: none !important; } }
   </style>
   <footer class="ms-copyright" id="msCopyright" hidden>© <?= $rentangCopy ?> <b>Medisafe</b>. Hak cipta dilindungi.</footer>
   <script>
      (function() {
         var f = document.getElementById('msCopyright');
         var wadah = document.querySelector('.body-wrapper .body-wrapper-inner');
         if (!f || !wadah || document.querySelector('.app-footer')) { if (f) f.remove(); return; }
         wadah.appendChild(f);
         wadah.classList.add('ms-ada-footer');
         f.hidden = false;
      })();
   </script>
<?php } ?>
