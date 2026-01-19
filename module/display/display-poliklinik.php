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
      <div class="call-number">U010</div>
      <div class="call-counter">POLI UMUM</div>
      <div class="doctor-name">dr. Ahmad Santoso</div>
    </div>

    <!-- DAFTAR ANTRIAN POLI -->
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>Poliklinik</th>
            <th>No Antrian</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Poli Umum</td>
            <td>U011</td>
            <td class="status-wait">Menunggu</td>
          </tr>
          <tr>
            <td>Poli Umum</td>
            <td>U012</td>
            <td class="status-wait">Menunggu</td>
          </tr>
          <tr>
            <td>Poli Gigi</td>
            <td>G006</td>
            <td class="status-wait">Menunggu</td>
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