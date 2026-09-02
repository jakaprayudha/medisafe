<?php
$title = 'User';
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
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title fw-semibold">Data Tanda Tangan Digital</h5>
                    <!-- Grup tombol di sisi kanan -->
                    <div class="d-flex ms-auto gap-2">
                    </div>
                  </div>
                  <div class="table-responsive" data-simplebar>
                    <table class="table text-nowrap align-middle table-custom mb-0" id="periodeTable">
                      <thead>
                        <tr>
                          <th class="text-dark fw-normal">Nama Lengkap</th>
                          <th scope="col" class="text-dark fw-normal">Username</th>
                          <th scope="col" class="text-dark fw-normal">Roles</th>
                          <th scope="col" class="text-dark fw-normal">Signature</th>
                          <th scope="col" class="text-dark fw-normal text-center">Actions</th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                    </table>
                  </div>
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

<div class="modal fade" id="ttdModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">✍️ Tanda Tangan Pasien</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center">

        <input type="hidden" id="ttd_id_user">

        <canvas id="signaturePad"
          style="border:1px solid #ccc; width:100%; height:400px;">
        </canvas>

        <div class="mt-3 d-flex justify-content-between">
          <button class="btn btn-warning" id="clearSignature">🧹 Clear</button>
          <button class="btn btn-primary" id="saveSignature">💾 Simpan</button>
        </div>

      </div>

    </div>
  </div>
</div>

<script>
  const apiUrl = 'controller/master/userController';

  $(document).ready(function() {
    var table = $('#periodeTable').DataTable({
      processing: true,
      serverSide: false,
      scrollX: true,
      ajax: {
        url: apiUrl,
        type: "GET",
        dataSrc: function(json) {
          return json.data.map(function(row) {

            // Masking password: tampilkan 3 karakter awal, sisanya bintang
            let maskedPassword = "-";
            if (row.password) {
              const visible = row.password.substring(0, 3);
              const hidden = "*".repeat(Math.max(row.password.length - 3, 5));
              maskedPassword = visible + hidden;
            }

            return {
              "actions": `
            <div class="text-center">
              <div class="btn-group btn-group-sm" role="group">
                  <a class="btn btn-info ttd-btn" href="javascript:;" data-id="${row.id_user}">
                    <i class="fas fa-signature"></i>
                  </a>
              </div>
            </div>
          `,
              "name": row.fullname ?? "-",
              "username": row.username ?? "-",
              "roles": row.roles ?? "-",
              "signature_user": row.signature_user ?
                `<img src="uploads/ttd_faskes/${row.signature_user}" height="40"/>` : '-',
            };
          });
        }
      },
      columns: [{
          data: "name"
        },
        {
          data: "username"
        },
        {
          data: "roles"
        },
        {
          data: "signature_user"
        },
        {
          data: "actions",
          orderable: false,
          searchable: false
        },
      ],
      footerCallback: function(row, data, start, end, display) {
        var api = this.api();

        // Hitung total bobot
        let total = api
          .column(3, {
            page: 'current'
          })
          .data()
          .reduce((a, b) => {
            return (parseFloat(a) || 0) + (parseFloat(b) || 0);
          }, 0);

        // Tampilkan di footer
        $(api.column(3).footer()).html(total.toFixed(2) + " %");
      }
    });

    $('#customSearch').on('keyup', function() {
      table.search(this.value).draw();
    });

  });
</script>
<script>
  let canvas = document.getElementById('signaturePad');
  let ctx = canvas.getContext('2d');

  let drawing = false;

  // resize canvas biar presisi
  function resizeCanvas() {
    canvas.width = canvas.offsetWidth;
    canvas.height = 400;
  }
  resizeCanvas();

  // start drawing
  canvas.addEventListener('mousedown', () => drawing = true);
  canvas.addEventListener('mouseup', () => {
    drawing = false;
    ctx.beginPath();
  });
  canvas.addEventListener('mousemove', draw);

  // support touch (HP)
  canvas.addEventListener('touchstart', (e) => {
    drawing = true;
  });
  canvas.addEventListener('touchend', () => {
    drawing = false;
    ctx.beginPath();
  });
  canvas.addEventListener('touchmove', drawTouch);

  function draw(e) {
    if (!drawing) return;

    ctx.lineWidth = 6;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    ctx.lineTo(e.offsetX, e.offsetY);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(e.offsetX, e.offsetY);
  }

  function drawTouch(e) {
    e.preventDefault();
    if (!drawing) return;

    const rect = canvas.getBoundingClientRect();
    const touch = e.touches[0];

    const x = touch.clientX - rect.left;
    const y = touch.clientY - rect.top;

    ctx.lineWidth = 6;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';

    ctx.lineTo(x, y);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(x, y);
  }

  // clear
  document.getElementById('clearSignature').onclick = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  };

  // open modal
  $(document).on('click', '.ttd-btn', function() {
    let id = $(this).data('id');

    $('#ttd_id_user').val(id);
    console.log(id);
    $('#ttdModal').modal('show');

    setTimeout(resizeCanvas, 400);
  });

  // save
  document.getElementById('saveSignature').onclick = function() {
    const saveBtn = document.getElementById('saveSignature');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Menyimpan...';

    const image = canvas.toDataURL('image/png');

    fetch('controller/master/saveSignatureFaskes.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          id_user: $('#ttd_id_user').val(),
          image: image
        })
      })
      .then(res => res.json())
      .then(resp => {
        if (resp.status === 'success') {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Tanda tangan berhasil disimpan',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
          });
          $('#ttdModal').modal('hide');
          $('#periodeTable').DataTable().ajax.reload(null, false);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal menyimpan tanda tangan',
            text: resp.message || 'Terjadi kesalahan'
          });
        }
      })
      .catch(() => {
        Swal.fire({
          icon: 'error',
          title: 'Gagal menyimpan tanda tangan',
          text: 'Terjadi kesalahan jaringan'
        });
      })
      .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
      });
  };
</script>

</html>