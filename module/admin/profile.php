<?php
$title = 'Profile';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="en">

<head>
  <base href="../../">
  <?php
  require '../../assets/template/head.php';
  ?>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <?php
    require 'sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require 'navbar.php';
      ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-12 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body">
                  <h5 class="card-title mb-4">Update Profile</h5>

                  <form id="formProfile">
                    <input type="hidden" name="id_user" value="<?= $_SESSION['uid_user'] ?>">

                    <div class="mb-3">
                      <label class="form-label">Full Name</label>
                      <input type="text" name="fullname" class="form-control"
                        value="<?= $_SESSION['fullname'] ?>" required>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Username</label>
                      <input type="text" name="username" class="form-control"
                        value="<?= $_SESSION['username'] ?>" required>
                    </div>
                    <div id="usernameFeedback" class="mt-1"></div>

                    <div class="mb-3">
                      <label class="form-label">Password Baru</label>
                      <input type="password" name="password" class="form-control"
                        placeholder="Kosongkan jika tidak ingin ubah">
                    </div>

                    <div class="text-end">
                      <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



  <?php
  require 'library.php';
  ?>
</body>

<script>
  document.getElementById("formProfile").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch("controller/master/profileController.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: "success",
            title: "Berhasil",
            text: data.message
          }).then(() => {
            location.reload(); // refresh biar session update
          });
        } else {
          Swal.fire("Gagal", data.message, "error");
        }
      })
      .catch(err => {
        console.error(err);
        Swal.fire("Error", "Terjadi kesalahan", "error");
      });
  });
</script>
<script>
  const usernameInput = document.querySelector('[name="username"]');
  const feedback = document.getElementById('usernameFeedback');
  const submitBtn = document.querySelector('#formProfile button[type="submit"]');

  let timeout = null;

  usernameInput.addEventListener('input', function() {
    clearTimeout(timeout);

    const username = this.value;
    const id_user = document.querySelector('[name="id_user"]').value;

    if (username.length < 3) {
      feedback.innerHTML = '<small class="text-muted">Minimal 3 karakter</small>';
      submitBtn.disabled = true;
      return;
    }

    // debounce biar tidak spam request 🔥
    timeout = setTimeout(() => {
      fetch(`controller/master/checkUsername.php?username=${username}&id_user=${id_user}`)
        .then(res => res.json())
        .then(data => {
          if (data.exists) {
            feedback.innerHTML = '<small class="text-danger">❌ Username sudah dipakai</small>';
            submitBtn.disabled = true;
          } else {
            feedback.innerHTML = '<small class="text-success">✅ Username tersedia</small>';
            submitBtn.disabled = false;
          }
        })
        .catch(() => {
          feedback.innerHTML = '<small class="text-warning">⚠️ Gagal cek username</small>';
        });
    }, 500); // delay 500ms
  });
</script>

</html>