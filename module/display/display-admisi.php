<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Display Admisi Poliklinik</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    /* ===== BASE ===== */
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
      background: #e2e8f0;
      color: #111827;
      height: 100vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    /* ===== HEADER ===== */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 30px;
      background: #ffffff;
      color: #1e293b;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      z-index: 10;
    }

    .header-title {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .logo-placeholder {
      font-size: 28px;
      font-weight: 900;
      color: #ef4444;
      letter-spacing: 1px;
    }

    .clinic-name {
      font-size: 24px;
      font-weight: bold;
      color: #64748b;
      border-left: 2px solid #cbd5e1;
      padding-left: 15px;
    }

    .datetime {
      text-align: right;
      color: #334155;
    }

    .time {
      font-size: 28px;
      font-weight: bold;
      color: #0f172a;
    }

    .date {
      font-size: 14px;
      font-weight: 600;
    }

    /* ===== MAIN LAYOUT (SPLIT SCREEN) ===== */
    .container {
      display: flex;
      flex: 1;
      padding: 20px;
      gap: 20px;
      height: calc(100vh - 130px);
    }

    /* --- SISI KIRI (PANGGILAN UTAMA) --- */
    .left-panel {
      flex: 6;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .card-call {
      background: #ffffff;
      border-radius: 12px;
      border-top: 6px solid #3b82f6;
      padding: 30px;
      text-align: center;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .call-label {
      font-size: 28px;
      font-weight: bold;
      color: #64748b;
      text-transform: uppercase;
      margin-bottom: 20px;
    }

    /* Menggunakan class call-number untuk text besar (sesuai ID aslinya) */
    .call-number {
      font-size: 100px;
      font-weight: 900;
      color: #1e40af;
      line-height: 1.2;
      margin: 10px 0 30px 0;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .call-counter {
      display: inline-block;
      font-size: 32px;
      font-weight: bold;
      background: #dbeafe;
      color: #1d4ed8;
      padding: 15px 40px;
      border-radius: 50px;
    }

    /* --- SISI KANAN (LIST ROTASI POLI) --- */
    .right-panel {
      flex: 4;
      background: #ffffff;
      border-radius: 12px;
      border-top: 6px solid #10b981;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .right-header {
      background: #10b981;
      color: white;
      padding: 20px;
      text-align: center;
      font-size: 24px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .poli-title-display {
      background: #ecfdf5;
      color: #065f46;
      text-align: center;
      padding: 15px;
      font-size: 22px;
      font-weight: bold;
      border-bottom: 2px solid #d1fae5;
    }

    .table-container {
      flex: 1;
      padding: 0 20px 20px 20px;
      overflow: hidden;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .table th,
    .table td {
      padding: 18px 10px;
      text-align: left;
      font-size: 20px;
      border-bottom: 1px solid #f1f5f9;
    }

    .table th {
      color: #64748b;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 16px;
    }

    .table tr:nth-child(even) {
      background: #f8fafc;
    }

    /* Animasi Fade/Geser untuk Rotasi Poli */
    .fade-transition {
      animation: fadeInOut 0.5s ease-in-out;
    }

    @keyframes fadeInOut {
      0% {
        opacity: 0;
        transform: translateY(10px);
      }

      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* ===== FOOTER (RUNNING TEXT) ===== */
    .footer {
      background: #1e293b;
      color: #f8fafc;
      padding: 12px 20px;
      font-size: 22px;
      font-weight: bold;
      display: flex;
      align-items: center;
    }

    .footer-label {
      background: #ef4444;
      padding: 5px 15px;
      border-radius: 4px;
      margin-right: 15px;
      white-space: nowrap;
    }

    .marquee-container {
      flex: 1;
      overflow: hidden;
    }
  </style>
</head>

<body>

  <!-- HEADER -->
  <div class="header">
    <div class="header-title">
      <!-- <div class="logo-placeholder">DISPLAY ADMISI</div> -->
      <div class="clinic-name">Antrian Poliklinik Pasien</div>
    </div>
    <div class="datetime">
      <div id="time" class="time"></div>
      <div id="date" class="date"></div>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="container">

    <!-- KIRI: PANGGILAN UTAMA -->
    <div class="left-panel">
      <div class="card-call">
        <div class="call-label">PANGGILAN PASIEN</div>
        <!-- ID callPatient dan callQueue DIPERTAHANKAN sesuai bawaan socket Anda -->
        <div class="call-number" id="callPatient">-</div>
        <div>
          <div class="call-counter" id="callQueue">Menunggu...</div>
        </div>
      </div>
    </div>

    <!-- KANAN: DAFTAR TUNGGU ROTASI -->
    <div class="right-panel">
      <div class="right-header">
        DAFTAR PASIEN MENUNGGU
      </div>
      <!-- Judul Poli yang sedang tampil -->
      <div class="poli-title-display" id="currentPoliDisplay">Memuat Data...</div>

      <div class="table-container fade-transition" id="tableContainer">
        <table class="table">
          <thead>
            <tr>
              <th width="30%">No Antrian</th>
              <th width="70%">Nama Pasien</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <!-- Data akan di-inject lewat jQuery secara bergantian -->
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- FOOTER -->
  <div class="footer">
    <div class="footer-label">INFORMASI</div>
    <div class="marquee-container">
      <marquee scrollamount="6">Silakan duduk di ruang tunggu. Harap bersabar menunggu panggilan dari dokter pada poliklinik tujuan Anda. Terima kasih.</marquee>
    </div>
  </div>

  <!-- SCRIPTS -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../../controller/socket/socket.js"></script>

  <script>
    APP.window = APP.window || {};
    let groupedQueueData = {};
    let poliKeys = [];
    let currentPoliIndex = 0;
    let currentPageIndex = 0;
    let itemsPerPage = 10;

    let rotationInterval;
    $(function() {
      function updateClock() {
        const now = new Date();
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        let h = now.getHours().toString().padStart(2, '0');
        let m = now.getMinutes().toString().padStart(2, '0');
        let s = now.getSeconds().toString().padStart(2, '0');
        $('#time').text(`${h}:${m}:${s}`);
        $('#date').text(`${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`);
      }
      setInterval(updateClock, 1000);
      updateClock();
      APP.showQueue = function() {
        $.ajax({
          url: '../../controller/queue/listAntriAdmisiDisplay',
          type: 'GET',
          dataType: 'json',
          success: function(res) {
            if (res == null || !res.data || res.data.length === 0) {
              $('#tableBody').html(`<tr><td colspan="2" style="text-align:center;">Tidak ada antrian</td></tr>`);
              $('#currentPoliDisplay').text("ANTRIAN KOSONG");
              clearInterval(rotationInterval);
              return;
            }

            groupedQueueData = {};
            res.data.forEach(function(item) {
              // Menambahkan toUpperCase() dan default value agar lebih aman
              let poliName = item.poli ? item.poli.toUpperCase() : 'POLIKLINIK UMUM';
              if (!groupedQueueData[poliName]) {
                groupedQueueData[poliName] = [];
              }
              if (item.status == "0" || item.status == 0) {
                groupedQueueData[poliName].push(item);
              }
            });

            poliKeys = Object.keys(groupedQueueData);

            // --- BAGIAN YANG DIUBAH: Reset rotasi dan halaman ---
            clearInterval(rotationInterval);
            currentPoliIndex = 0;
            currentPageIndex = 0;

            renderCurrentPoli();

            if (poliKeys.length > 0) {
              rotationInterval = setInterval(renderCurrentPoli, 10000);
            }
          }
        });
      }

      function renderCurrentPoli() {
        if (poliKeys.length === 0) return;

        let currentPoliName = poliKeys[currentPoliIndex];
        let patients = groupedQueueData[currentPoliName];

        // --- LOGIKA BARU: Hitung Halaman & Potong Data ---
        let totalPages = Math.ceil(patients.length / itemsPerPage);
        if (totalPages === 0) totalPages = 1;

        let startIndex = currentPageIndex * itemsPerPage;
        let endIndex = startIndex + itemsPerPage;
        let paginatedPatients = patients.slice(startIndex, endIndex);

        // Trigger animasi CSS
        let container = $('#tableContainer');
        container.removeClass('fade-transition');
        void container[0].offsetWidth;
        container.addClass('fade-transition');

        // --- LOGIKA BARU: Update Judul Poli + Info Halaman (Jika > 1) ---
        let displayTitle = currentPoliName;
        if (totalPages > 1) {
          displayTitle += ` (Hal ${currentPageIndex + 1}/${totalPages})`;
        }
        $('#currentPoliDisplay').text(displayTitle);

        // Update Tabel Pasien dengan data yang sudah dipotong (paginatedPatients)
        let html = '';
        if (!paginatedPatients || paginatedPatients.length === 0) {
          html = `<tr><td colspan="2" style="text-align:center;">Semua pasien telah dipanggil</td></tr>`;
        } else {
          paginatedPatients.forEach(function(item) {
            html += `
                    <tr>
                    <td><b>${item.no_antrian}</b></td>
                    <td>${item.nama_pasien}</td>
                    </tr>
                `;
          });
        }
        $('#tableBody').html(html);

        // --- LOGIKA BARU: Pergantian Halaman Dulu, Baru Ganti Poli ---
        currentPageIndex++;

        if (currentPageIndex >= totalPages) {
          currentPageIndex = 0; // Reset halaman ke awal
          currentPoliIndex++; // Ganti ke Poli selanjutnya

          if (currentPoliIndex >= poliKeys.length) {
            currentPoliIndex = 0; // Kembali ke poli pertama jika sudah habis
          }
        }
      }

      // Inisialisasi Panggilan Tabel Pertama Kali
      APP.showQueue();

      // =================================================================
      // FUNGSI INI SAYA KEMBALIKAN 100% PERSIS SEPERTI MILIK ANDA
      // AGAR SOCKET YANG SUDAH TERPASANG TETAP BERFUNGSI DENGAN NORMAL
      // =================================================================
      APP.showAntrianPoli = function(nama, dokter) {
        console.log(nama);
        console.log(dokter);
        $('#callPatient').html(nama.toUpperCase());
        let namaDokter = dokter.toUpperCase().replace(/^DR\./i, 'Dr.');
        $('#callQueue').html("Ruangan : " + namaDokter);
        APP.showQueue();
      }

    });
  </script>

</body>

</html>