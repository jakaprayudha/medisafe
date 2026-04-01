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
                                          <table id="tableQueue" class="table table-sm align-middle mb-0">
                                             <thead class="table-light">
                                                <tr>
                                                   <th>No</th>
                                                   <th>No Antrean</th>
                                                   <th>Nama Pasien</th>
                                                   <th>Poli</th>
                                                </tr>
                                             </thead>
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
   APP.window = APP.window || {};
   $(function() {

      $('#tableQueue').DataTable({
         processing: true,
         serverSide: false,
         ajax: {
            url: 'controller/queue/listAntrianAdmisi',
            type: 'GET',
         },
         columns: [{
               data: null,
               render: function(data, type, row, meta) {
                  return meta.row + 1;
               }
            },
            {
               data: 'no_antrian'
            },
            {
               data: 'nama_pasien'
            },
            {
               data: 'poli'
            },
         ],
         language: {
            processing: "Memuat antrian...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            loadingRecords: "Memuat...",
            zeroRecords: "Tidak Ada Antrian",
            emptyTable: "Tidak Ada Antrian",
            paginate: {
               first: "Awal",
               last: "Akhir",
               next: "Selanjutnya",
               previous: "Sebelumnya"
            }
         }
      });

      APP.resetTable = function(){
         $('#tableQueue').DataTable().ajax.reload();
      }
      // /* =========================
      //    GLOBAL STATE
      //    ========================= */
      // let lastCalled = null;
      // let voices = [];

      // /* =========================
      //    LOAD VOICES (WAJIB)
      // ========================= */
      // function loadVoices() {
      //    voices = speechSynthesis.getVoices();
      // }

      // // Chrome butuh event ini
      // speechSynthesis.onvoiceschanged = loadVoices;

      // /* =========================
      //    INIT
      // ========================= */
      // document.addEventListener("DOMContentLoaded", () => {
      //    loadVoices();
      // });

      // /* =========================
      //    LOAD QUEUE
      // ========================= */
      // function loadQueue() {
      //    const counter = document.getElementById('counterSelect').value;
      //    if (!counter) return;

      //    fetch(`controller/queue/admisi.php?counter=${counter}`)
      //       .then(res => res.json())
      //       .then(res => {
      //          renderQueueList(res.data || []);
      //          renderCurrent(res.current);
      //       })
      //       .catch(err => console.error(err));
      // }

      // /* =========================
      //    RENDER CURRENT
      // ========================= */
      // function renderCurrent(data) {
      //    if (!data) return;

      //    const counter = document.getElementById('counterSelect').value;

      //    document.getElementById('currentQueue').textContent = data.no_antrian;
      //    document.getElementById('currentPoli').textContent = data.poli;

      //    // 🔊 Bicara hanya jika nomor baru
      //    if (data.no_antrian !== lastCalled) {
      //       setTimeout(() => {
      //          speakQueue(data.no_antrian, counter);
      //       }, 500); // jeda sopan
      //       lastCalled = data.no_antrian;
      //    }
      // }

      // /* =========================
      //    RENDER LIST
      // ========================= */
      // function renderQueueList(rows) {
      //    const tbody = document.getElementById('queueList');
      //    tbody.innerHTML = '';

      //    if (!rows.length) {
      //       tbody.innerHTML = `
      //    <tr>
      //       <td colspan="5" class="text-center text-muted">
      //          Tidak ada antrean
      //       </td>
      //    </tr>
      // `;
      //       return;
      //    }

      //    rows.forEach((r, i) => {
      //       tbody.innerHTML += `
      //    <tr>
      //       <td>${i + 1}</td>
      //       <td><strong>${r.no_antrian}</strong></td>
      //       <td>${r.nama_pasien}</td>
      //       <td>${r.poli}</td>
      //       <td><span class="badge bg-warning">Menunggu</span></td>
      //    </tr>
      // `;
      //    });
      // }

      // /* =========================
      //    ACTION BUTTONS
      // ========================= */
      // function callNext() {
      //    actionQueue('call');
      // }

      // function skipQueue() {
      //    actionQueue('skip');
      // }

      // function finishQueue() {
      //    actionQueue('finish');
      // }

      // function actionQueue(type) {
      //    const counter = document.getElementById('counterSelect').value;
      //    if (!counter) {
      //       alert('Pilih counter terlebih dahulu');
      //       return;
      //    }

      //    fetch('controller/queue/admisiCall.php', {
      //          method: 'POST',
      //          headers: {
      //             'Content-Type': 'application/json'
      //          },
      //          body: JSON.stringify({
      //             action: type,
      //             counter
      //          })
      //       })
      //       .then(res => res.json())
      //       .then(res => {
      //          if (res.status === 'error') {
      //             alert(res.message);
      //             return;
      //          }
      //          loadQueue();
      //       });
      // }

      // /* =========================
      //    TEXT TO SPEECH
      // ========================= */
      // function speakQueue(noAntrian, counter) {
      //    if (!('speechSynthesis' in window)) return;

      //    const text = `Nomor antrean ${noAntrian}, silakan menuju loket ${counter}`;
      //    const utterance = new SpeechSynthesisUtterance(text);

      //    utterance.rate = 0.9;
      //    utterance.pitch = 1;
      //    utterance.volume = 1;

      //    // Cari voice Indonesia, fallback aman
      //    let voice =
      //       voices.find(v => v.lang === 'id-ID') ||
      //       voices.find(v => v.lang && v.lang.startsWith('id')) ||
      //       voices[0];

      //    if (voice) utterance.voice = voice;

      //    speechSynthesis.cancel();
      //    speechSynthesis.speak(utterance);
      // }
   })
</script>