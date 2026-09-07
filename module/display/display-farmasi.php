<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Display Antrian Farmasi</title>
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
      flex: 5;
      /* Sedikit dikecilkan agar tabel kanan lebih lega */
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

    .call-number {
      font-size: 90px;
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

    /* --- SISI KANAN (LIST DAFTAR ANTRIAN) --- */
    .right-panel {
      flex: 7;
      /* Dilebarkan untuk memuat 4 kolom */
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
      font-size: 20px;
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
      padding: 15px 10px;
      text-align: left;
      font-size: 18px;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: middle;
    }

    .table th {
      color: #64748b;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 15px;
    }

    .table tr:nth-child(even) {
      background: #f8fafc;
    }

    /* Badge Status */
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: bold;
      text-transform: uppercase;
      display: inline-block;
    }

    .badge-menunggu {
      background: #fef08a;
      /* Kuning */
      color: #854d0e;
    }

    .badge-proses {
      background: #bfdbfe;
      /* Biru */
      color: #1e40af;
    }

    /* Animasi Fade/Geser */
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
  <div class="header">
    <div class="header-title">
      <div class="clinic-name">Antrian Pengambilan Obat (Farmasi)</div>
    </div>
    <div class="datetime">
      <div id="time" class="time"></div>
      <div id="date" class="date"></div>
    </div>
  </div>

  <div class="container">
    <div class="left-panel">
      <div class="card-call">
        <div class="call-label">PANGGILAN PASIEN</div>
        <div class="call-number" id="callPatient">-</div>
        <div>
          <div class="call-counter" id="callQueue">Menunggu...</div>
        </div>
      </div>
    </div>
    <div class="right-panel">
      <div class="right-header">
        DAFTAR PASIEN MENUNGGU
      </div>
      <div class="poli-title-display" id="farmasiDisplayTitle">Memuat Data...</div>
      <div class="table-container fade-transition" id="tableContainer">
        <table class="table">
          <thead>
            <tr>
              <th width="40%">Nama Pasien</th>
              <th width="25%">Poli</th>
              <th width="20%">Status</th>
            </tr>
          </thead>
          <tbody id="tableBody">
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="footer">
    <div class="footer-label">INFORMASI</div>
    <div class="marquee-container">
      <marquee scrollamount="6">Silakan duduk di ruang tunggu. Harap bersabar menunggu panggilan penyerahan obat Anda. Obat yang sedang disiapkan memiliki status "PROSES".</marquee>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../../controller/socket/socket.js"></script>
  <script>
    APP.window = APP.window || {};
    let queueData = [];
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

      function getStatusBadge(status) {
        let statStr = String(status).trim();
        if (statStr === "0" || statStr === "1") {
          return `<span class="status-badge badge-menunggu">Menunggu</span>`;
        } else if (statStr === "2") {
          return `<span class="status-badge badge-proses">Proses</span>`;
        }
        return `<span class="status-badge" style="background:#e2e8f0;">${status}</span>`;
      }

      APP.showQueue = function() {
        $.ajax({
          url: '../../controller/queue/listAntriFarmasiDisplay',
          type: 'GET',
          dataType: 'json',
          success: function(res) {
            if (res == null || !res.data || res.data.length === 0) {
              $('#tableBody').html(`<tr><td colspan="3" style="text-align:center;">Tidak ada antrian farmasi</td></tr>`);
              $('#farmasiDisplayTitle').text("ANTRIAN KOSONG");
              clearInterval(rotationInterval);
              return;
            }

            queueData = res.data.filter(item => {
              let s = String(item.status).trim();
              return (s === "0" || s === "1" || s === "2");
            });
            clearInterval(rotationInterval);
            currentPageIndex = 0;
            // console.log(queueData);
            if (queueData.length === 0) {
              $('#tableBody').html(`<tr><td colspan="3" style="text-align:center;">Tidak ada antrian aktif</td></tr>`);
              $('#farmasiDisplayTitle').text("SEMUA ANTRIAN SELESAI");
            } else {
              renderTablePage();
              startRotation();
            }
          }
        });
      }

      function startRotation() {
        clearInterval(rotationInterval);
        if (queueData.length > itemsPerPage) {
          rotationInterval = setInterval(renderTablePage, 10000);
        }
      }

      function renderTablePage() {
        if (queueData.length === 0) {
          $('#tableBody').html(`<tr><td colspan="3" style="text-align:center;">Tidak ada antrian aktif</td></tr>`);
          $('#farmasiDisplayTitle').text("SEMUA ANTRIAN SELESAI");
          return;
        }

        let totalPages = Math.ceil(queueData.length / itemsPerPage);
        if (totalPages === 0) totalPages = 1;

        if (currentPageIndex >= totalPages) {
          currentPageIndex = 0;
        }

        let startIndex = currentPageIndex * itemsPerPage;
        let endIndex = startIndex + itemsPerPage;
        let paginatedPatients = queueData.slice(startIndex, endIndex);

        let container = $('#tableContainer');
        container.removeClass('fade-transition');
        void container[0].offsetWidth;
        container.addClass('fade-transition');

        let displayTitle = "DAFTAR ANTRIAN FARMASI";
        if (totalPages > 1) {
          displayTitle += ` (Halaman ${currentPageIndex + 1}/${totalPages})`;
        }
        $('#farmasiDisplayTitle').text(displayTitle);

        let html = '';
        paginatedPatients.forEach(function(item) {
          let poliAsal = item.poli ? item.poli.toUpperCase() : '-';

          html += `
              <tr>
                <td><b>${item.nama_pasien}</b></td>
                <td>${poliAsal}</td>
                <td>${getStatusBadge(item.status)}</td>
              </tr>
          `;
        });

        $('#tableBody').html(html);

        currentPageIndex++;
        if (currentPageIndex >= totalPages) {
          currentPageIndex = 0;
        }
      }

      APP.showQueue();
      APP.updateDataLokal = function(payload) {
        let index = queueData.findIndex(item => item.visit_id === payload.visit_id);
        let statusStr = String(payload.status).trim();
        let isSelesai = (statusStr === "3"); // 3 = Selesai (dihapus)

        if (isSelesai) {
          if (index !== -1) {
            queueData.splice(index, 1); // Hapus dari array
          }
        } else {
          if (index !== -1) {
            // JIKA DATA SUDAH ADA: Cukup update statusnya saja! 
            // Nama pasien dan poli yang lama dibiarkan aman di memori.
            queueData[index].status = payload.status;

            // Opsional: Jika payload membawa nama/poli baru, silakan update juga
            if (payload.nama_pasien) queueData[index].nama_pasien = payload.nama_pasien;
            if (payload.poli) queueData[index].poli = payload.poli;
          } else {
            // JIKA DATA BELUM ADA (Antrean Baru): Wajib ada nama & poli dari server
            queueData.push({
              visit_id: payload.visit_id,
              nama_pasien: payload.nama_pasien,
              poli: payload.poli || '-',
              status: payload.status
            });
          }
        }

        if (currentPageIndex > 0) currentPageIndex--;
        console.log(queueData);
        renderTablePage();
        startRotation();

        // Update tampilan kartu panggilan di sebelah kiri
        if (payload.is_panggilan) {
          // Ambil nama pasien langsung dari array lokal berdasarkan visit_id yang sedang dipanggil
          let currentItem = queueData.find(item => item.visit_id === payload.visit_id);
          let namaTampil = currentItem ? currentItem.nama_pasien : (payload.nama_pasien || 'PASIEN');

          $('#callPatient').html(namaTampil.toUpperCase());
          $('#callQueue').html("Silakan Mengambil Obat");
        }
      }

      // Opsional: Fungsi ini bisa dipanggil oleh socket (sesuaikan dengan status baru Anda, misalnya default proses = 2)
      APP.showPanggilanFarmasi = function(visit_id, nama, poli = '-', status = "2") {
        APP.updateDataLokal({
          visit_id: visit_id,
          nama_pasien: nama,
          poli: poli,
          status: status,
          is_panggilan: true
        });
      }

    });
  </script>
</body>

</html>