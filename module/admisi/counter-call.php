<?php
$title = 'Counter Admisi';
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
    require '../admin/sidebar.php';
    ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php
      require '../admin/navbar.php';
      ?>
      <!--  Header End -->
     <div class="body-wrapper-inner">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
          <div class="card-body p-4">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title fw-semibold mb-0">Panggil Antrean Admisi</h5>

              <select class="form-select w-auto" id="counterSelect">
                <option value="">Pilih Counter</option>
                <option value="1">Counter 1</option>
                <option value="2">Counter 2</option>
                <option value="3">Counter 3</option>
              </select>
            </div>

            <div class="row g-4">

              <!-- CURRENT QUEUE -->
              <div class="col-md-4">
                <div class="card admisi-card text-center">
                  <div class="card-body">
                    <small class="text-muted">Antrean Saat Ini</small>
                    <h1 class="fw-bold my-3" id="currentQueue">-</h1>
                    <span class="badge bg-info" id="currentPoli">-</span>
                  </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="d-grid gap-2 mt-3">
                  <button class="btn btn-primary" onclick="callNext()">
                    <i class="ti ti-volume"></i> Panggil Berikutnya
                  </button>
                  <button class="btn btn-warning" onclick="skipQueue()">
                    <i class="ti ti-player-skip-forward"></i> Lewati
                  </button>
                  <button class="btn btn-success" onclick="finishQueue()">
                    <i class="ti ti-check"></i> Selesai
                  </button>
                </div>
              </div>

              <!-- QUEUE LIST -->
              <div class="col-md-8">
                <div class="card admisi-card">
                  <div class="card-body">
                    <h6 class="fw-semibold mb-3">Antrean Menunggu</h6>

                    <div class="table-responsive">
                      <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                          <tr>
                            <th>No</th>
                            <th>No Antrean</th>
                            <th>Nama Pasien</th>
                            <th>Poli</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody id="queueList">
                          <tr>
                            <td colspan="5" class="text-center text-muted">
                              Memuat data...
                            </td>
                          </tr>
                        </tbody>
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
  </div>
</div>
    </div>
  </div>



  <?php
  require '../admin/library.php';
  ?>
</body>
</html>

<script>

   
document.addEventListener("DOMContentLoaded", () => {
   loadQueue();
});
document.getElementById('counterSelect').addEventListener('change', loadQueue);

function loadQueue() {
   const counter = document.getElementById('counterSelect').value;
   if (!counter) return;

   fetch(`controller/queue/admisi.php?counter=${counter}`)
      .then(res => res.json())
      .then(res => {
         renderQueueList(res.data);
         renderCurrent(res.current);
      });
}

function renderCurrent(data) {
   if (!data) return;
   document.getElementById('currentQueue').textContent = data.no_antrian;
   document.getElementById('currentPoli').textContent  = data.poli;
}

function renderQueueList(rows) {
   const tbody = document.getElementById('queueList');
   tbody.innerHTML = '';

   if (!rows.length) {
      tbody.innerHTML = `
         <tr>
            <td colspan="5" class="text-center text-muted">
               Tidak ada antrean
            </td>
         </tr>`;
      return;
   }

   rows.forEach((r, i) => {
      tbody.innerHTML += `
         <tr>
            <td>${i + 1}</td>
            <td><strong>${r.no_antrian}</strong></td>
            <td>${r.nama_pasien}</td>
            <td>${r.poli}</td>
            <td><span class="badge bg-warning">Menunggu</span></td>
         </tr>
      `;
   });
}

/* ================= ACTION ================= */

function callNext() {
   actionQueue('call');
}

function skipQueue() {
   actionQueue('skip');
}

function finishQueue() {
   actionQueue('finish');
}

function actionQueue(type) {
   const counter = document.getElementById('counterSelect').value;
   if (!counter) {
      alert('Pilih counter terlebih dahulu');
      return;
   }

   fetch('controller/queue/admisiCall.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: type, counter: counter })
   })
   .then(res => res.json())
   .then(() => loadQueue());
}
</script>