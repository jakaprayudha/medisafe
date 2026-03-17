<script src="assets/libs/jquery/dist/jquery.min.js"></script>
<script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

<?php if (isset($_SESSION['sukses']) || isset($_SESSION['error'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {

      const isSuccess = <?= isset($_SESSION['sukses']) ? 'true' : 'false' ?>;

      Swal.fire({
        icon: isSuccess ? 'success' : 'error',
        title: isSuccess ? 'Berhasil' : 'Perhatian',
        text: "<?= isset($_SESSION['sukses']) ? $_SESSION['sukses'] : $_SESSION['error']; ?>",

        showClass: {
          popup: 'swal2-noanimation'
        },
        hideClass: {
          popup: ''
        },

        confirmButtonColor: '#0f9b8e'
      }).then(() => {
        window.location = "<?= $_SESSION['redirectlogin']; ?>";
      });

    });
  </script>
<?php
  unset($_SESSION['sukses']);
  unset($_SESSION['error']);
endif;
?>