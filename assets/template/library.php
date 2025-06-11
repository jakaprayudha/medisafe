<script src="assets/libs/jquery/dist/jquery.min.js"></script>
<script src="assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<!-- solar icons -->
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

<?php
// Cek jika ada pesan sukses
if (isset($_SESSION['sukses'])) {
   echo "<script>
    Swal.fire('Good job!', '" . $_SESSION['sukses'] . "', 'success').then(function() {
      window.location = '" . $_SESSION['redirectlogin'] . "';
    });
  </script>";
   unset($_SESSION['sukses']);
}
// Cek jika ada pesan error
if (isset($_SESSION['error'])) {
   echo "<script>
    Swal.fire('Perhatian!', '" . $_SESSION['error'] . "', 'error').then(function() {
      window.location = '" . $_SESSION['redirectlogin'] . "';
    });
  </script>";
   unset($_SESSION['error']);
}
?>