<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Display Ketersediaan Tempat Tidur</title>
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
      background: #7c2d12; /* coklat-oranye medis */
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
      padding: 40px 48px 120px;
    }

    /* ===== GRID CARD ===== */
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 28px;
    }

    .bed-card {
      background: #fff7ed;
      border-radius: 20px;
      padding: 28px;
      box-shadow: 0 6px 16px rgba(0,0,0,0.1);
      border-left: 10px solid #ea580c;
    }

    .room-name {
      font-size: 28px;
      font-weight: bold;
      margin-bottom: 16px;
    }

    .bed-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .bed-total {
      font-size: 22px;
      opacity: 0.9;
    }

    .bed-available {
      font-size: 52px;
      font-weight: bold;
      color: #15803d;
    }

    .bed-empty {
      color: #b91c1c;
    }

    .status-label {
      margin-top: 14px;
      font-size: 20px;
      font-weight: bold;
      padding: 10px 20px;
      border-radius: 999px;
      display: inline-block;
    }

    .status-available {
      background: #dcfce7;
      color: #166534;
    }

    .status-full {
      background: #fee2e2;
      color: #991b1b;
    }

    /* ===== FOOTER ===== */
    .footer {
      position: fixed;
      bottom: 0;
      width: 100%;
      text-align: center;
      padding: 18px;
      background: #7c2d12;
      color: #ffffff;
      font-size: 18px;
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <div class="header">
    DISPLAY KETERSEDIAAN TEMPAT TIDUR
    <div class="subtitle">Informasi Bed Rawat Inap</div>

    <!-- DATE TIME -->
    <div class="datetime">
      <div id="date"></div>
      <div id="time" class="time"></div>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="container">
    <div class="grid">

      <!-- CARD RUANG MAWAR -->
      <div class="bed-card">
        <div class="room-name">Ruang Mawar</div>
        <div class="bed-info">
          <div class="bed-total">Total Bed: 20</div>
          <div class="bed-available">5</div>
        </div>
        <div class="status-label status-available">TERSEDIA</div>
      </div>

      <!-- CARD RUANG MELATI -->
      <div class="bed-card">
        <div class="room-name">Ruang Melati</div>
        <div class="bed-info">
          <div class="bed-total">Total Bed: 15</div>
          <div class="bed-available bed-empty">0</div>
        </div>
        <div class="status-label status-full">PENUH</div>
      </div>

      <!-- CARD RUANG ANGGREK -->
      <div class="bed-card">
        <div class="room-name">Ruang Anggrek</div>
        <div class="bed-info">
          <div class="bed-total">Total Bed: 10</div>
          <div class="bed-available">3</div>
        </div>
        <div class="status-label status-available">TERSEDIA</div>
      </div>

      <!-- CARD RUANG ICU -->
      <div class="bed-card">
        <div class="room-name">ICU</div>
        <div class="bed-info">
          <div class="bed-total">Total Bed: 6</div>
          <div class="bed-available bed-empty">0</div>
        </div>
        <div class="status-label status-full">PENUH</div>
      </div>

    </div>
  </div>

  <!-- FOOTER -->
  <div class="footer">
    Medisafe • Display Tempat Tidur
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