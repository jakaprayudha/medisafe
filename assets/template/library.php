<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js" integrity="sha384-YB/DdIkloKoRpclWB8bNcYXWakt57USgtQPDzvnIDHYU0lasD5eWlXVo1S4ODukY" crossorigin="anonymous"></script>

<?php if (isset($_SESSION['sukses']) || isset($_SESSION['error'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {

      const isSuccess = <?= isset($_SESSION['sukses']) ? 'true' : 'false' ?>;

      Swal.fire({
        icon: isSuccess ? 'success' : 'error',
        title: isSuccess ? 'Berhasil' : 'Perhatian',
        text: <?= json_encode(isset($_SESSION['sukses']) ? $_SESSION['sukses'] : $_SESSION['error'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,

        showClass: {
          popup: 'swal2-noanimation'
        },
        hideClass: {
          popup: ''
        },

        confirmButtonColor: '#0f9b8e'
      }).then(() => {
        window.location = <?= json_encode($_SESSION['redirectlogin'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
      });

    });
  </script>
<?php
  unset($_SESSION['sukses']);
  unset($_SESSION['error']);
endif;
?>