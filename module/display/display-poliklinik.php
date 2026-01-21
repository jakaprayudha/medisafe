<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Display Poliklinik</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    /* ===== BASE ===== */
    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      background: #ffffff;
      color: #111827;
    }

    /* ===== HEADER ===== */
    .header {
      padding: 28px;
      text-align: center;
      background: #0f766e; /* teal medis */
      color: #ffffff;
      font-size: 34px;
      font-weight: bold;
      position: relative;
    }

    .subtitle {
      font-size: 20px;
      margin-top: 6px;
      opacity: 0.95;
    }

    /* ===== DATE TIME ===== */
    .datetime {
      position: absolute;
      right: 24px;
      top: 24px;
      text-align: right;
      font-size: 20px;
      line-height: 1.3;
    }

    .time {
      font-size: 32px;
      font-weight: bold;
    }

    /* ===== LAYOUT ===== */
    .container {
      padding: 40px 48px;
    }

    .card {
      background: #f0fdfa;
      border-radius: 18px;
      padding: 32px;
      margin-bottom: 40px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    /* ===== ANTRIAN DIPANGGIL ===== */
    .call-card {
      text-align: center;
      border: 6px solid #0f766e;
    }

    .call-label {
      font-size: 30px;
      font-weight: bold;
      margin-bottom: 12px;
    }

    .call-number {
      font-size: 120px;
      font-weight: 800;
      color: #0f766e;
      letter-spacing: 6px;
      margin: 8px 0;
    }

    .call-counter {
      margin-top: 10px;
      font-size: 32px;
      font-weight: bold;
      background: #99f6e4;
      color: #065f46;
      display: inline-block;
      padding: 14px 36px;
      border-radius: 14px;
    }

    .doctor-name {
      margin-top: 12px;
      font-size: 26px;
      font-weight: bold;
    }

    /* ===== TABLE ANTRIAN ===== */
    .table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }

    .table th,
    .table td {
      padding: 22px;
      text-align: center;
      font-size: 28px;
    }

    .table th {
      background: #ccfbf1;
      font-weight: bold;
    }

    .table tr:nth-child(even) {
      background: #ecfeff;
    }

    .status-wait {
      font-weight: bold;
      color: #0f766e;
    }

    /* ===== FOOTER ===== */
    .footer {
      position: fixed;
      bottom: 0;
      width: 100%;
      text-align: center;
      padding: 18px;
      background: #0f766e;
      color: #ffffff;
      font-size: 18px;
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <div class="header">
    DISPLAY POLIKLINIK
    <div class="subtitle">Antrian Pemeriksaan Dokter</div>

    <!-- DATE TIME -->
    <div class="datetime">
      <div id="date"></div>
      <div id="time" class="time"></div>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="container">

    <!-- ANTRIAN DIPANGGIL -->
    <div class="card call-card">
      <div class="call-label">NOMOR ANTRIAN DIPANGGIL</div>
      <div class="call-number">-</div>
      <div class="call-counter">-</div>

      <!-- ✅ NAMA PASIEN -->
      <div class="patient-name" style="margin-top:8px;font-size:28px;font-weight:bold;">
        -
      </div>
      <div class="doctor-name">-</div>
    </div>

    <!-- DAFTAR ANTRIAN POLI -->
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Poliklinik</th>
            <th>No Antrian</th>
            <th>Nama Pasien</th>
            <th>Status</th>
          </tr>
        </thead>
       <tbody>
        <tr>
          <td colspan="3" style="text-align:center; font-weight:bold;">
            -
          </td>
        </tr>
      </tbody>
      </table>
    </div>

  </div>

  <!-- FOOTER -->
  <div class="footer">
    Medisafe • Display Poliklinik
  </div>

  <!-- ===== REALTIME DATE & TIME SCRIPT ===== -->
  <script>
    
    function updateDateTime() {
      const now = new Date();

      const optionsDate = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      };

      const date = now.toLocaleDateString('id-ID', optionsDate);
      const time = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });

      document.getElementById('date').textContent = date;
      document.getElementById('time').textContent = time;
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);
  </script>

</body>
</html>

<script>
function loadDisplay() {
  fetch('../../controller/queue/poliDisplay.php')
    .then(res => res.json())
    .then(res => {

      /* ===== DEFAULT VALUE ===== */
      const defaultText = '-';

      /* ===== ANTRIAN DIPANGGIL ===== */
      const patientNameEl = document.querySelector('.patient-name');
      const callNumberEl = document.querySelector('.call-number');
      const callCounterEl = document.querySelector('.call-counter');
      const doctorNameEl = document.querySelector('.doctor-name');

      if (res.called && res.called.visit_antrian) {
        callNumberEl.textContent = res.called.visit_antrian || defaultText;
        callCounterEl.textContent = res.called.poli_name || defaultText;
        doctorNameEl.textContent = res.called.doctor_name || defaultText;
       patientNameEl.textContent = res.called.patient_name || defaultText;
      } else {
        callNumberEl.textContent = defaultText;
        callCounterEl.textContent = defaultText;
        doctorNameEl.textContent = defaultText;
         patientNameEl.textContent = defaultText;
      }

      /* ===== DAFTAR ANTRIAN ===== */
      const tbody = document.querySelector('.table tbody');
      tbody.innerHTML = '';

      if (res.waiting && res.waiting.length > 0) {
        res.waiting.forEach(r => {
          tbody.innerHTML += `
            <tr>
              <td>${r.poli_name || defaultText}</td>
              <td>${r.visit_antrian || defaultText}</td>
              <td>${r.patient_name || defaultText}</td>
              <td class="status-wait">Menunggu</td>
            </tr>
          `;
        });
      } else {
        tbody.innerHTML = `
          <tr>
            <td colspan="3" style="text-align:center; font-weight:bold;">
              -
            </td>
          </tr>
        `;
      }
    })
    .catch(() => {
      /* Jika API error */
      document.querySelector('.call-number').textContent = '-';
      document.querySelector('.call-counter').textContent = '-';
      document.querySelector('.doctor-name').textContent = '-';

      document.querySelector('.table tbody').innerHTML = `
        <tr>
          <td colspan="3" style="text-align:center; font-weight:bold;">
            -
          </td>
        </tr>
      `;
    });
}

loadDisplay();
setInterval(loadDisplay, 3000);
</script><script>
function loadDisplay() {
  fetch('../../controller/queue/poliDisplay.php')
    .then(res => res.json())
    .then(res => {

      /* ===== DEFAULT VALUE ===== */
      const defaultText = '-';

      /* ===== ANTRIAN DIPANGGIL ===== */
      const callNumberEl = document.querySelector('.call-number');
      const callCounterEl = document.querySelector('.call-counter');
      const doctorNameEl = document.querySelector('.doctor-name');

      if (res.called && res.called.visit_antrian) {
        callNumberEl.textContent = res.called.visit_antrian || defaultText;
        callCounterEl.textContent = res.called.poli_name || defaultText;
        doctorNameEl.textContent = res.called.doctor_name || defaultText;
      } else {
        callNumberEl.textContent = defaultText;
        callCounterEl.textContent = defaultText;
        doctorNameEl.textContent = defaultText;
      }

      /* ===== DAFTAR ANTRIAN ===== */
      const tbody = document.querySelector('.table tbody');
      tbody.innerHTML = '';

      if (res.waiting && res.waiting.length > 0) {
        res.waiting.forEach(r => {
          tbody.innerHTML += `
            <tr>
              <td>${r.poli_name || defaultText}</td>
              <td>${r.visit_antrian || defaultText}</td>
              <td>${r.patient_name || defaultText}</td>
              <td class="status-wait">Menunggu</td>
            </tr>
          `;
        });
      } else {
        tbody.innerHTML = `
          <tr>
            <td colspan="3" style="text-align:center; font-weight:bold;">
              -
            </td>
          </tr>
        `;
      }
    })
    .catch(() => {
      /* Jika API error */
      document.querySelector('.call-number').textContent = '-';
      document.querySelector('.call-counter').textContent = '-';
      document.querySelector('.doctor-name').textContent = '-';

      document.querySelector('.table tbody').innerHTML = `
        <tr>
          <td colspan="3" style="text-align:center; font-weight:bold;">
            -
          </td>
        </tr>
      `;
    });
}

loadDisplay();
setInterval(loadDisplay, 3000);
</script>