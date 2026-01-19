<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Display Farmasi</title>
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
      background: #065f46; /* hijau farmasi */
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
      background: #f0fdf4;
      border-radius: 18px;
      padding: 32px;
      margin-bottom: 40px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    /* ===== NOMOR RESEP DIPANGGIL ===== */
    .call-card {
      text-align: center;
      border: 6px solid #16a34a;
    }

    .call-label {
      font-size: 30px;
      font-weight: bold;
      margin-bottom: 16px;
    }

    .call-number {
      font-size: 120px;
      font-weight: 800;
      color: #16a34a;
      letter-spacing: 6px;
      margin: 12px 0;
    }

    .call-counter {
      margin-top: 10px;
      font-size: 34px;
      font-weight: bold;
      background: #bbf7d0;
      color: #14532d;
      display: inline-block;
      padding: 14px 40px;
      border-radius: 14px;
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
      background: #dcfce7;
      font-weight: bold;
    }

    .table tr:nth-child(even) {
      background: #ecfdf5;
    }

    .status-ready {
      font-weight: bold;
      color: #15803d;
    }

    .status-process {
      font-weight: bold;
      color: #92400e;
    }

    /* ===== FOOTER ===== */
    .footer {
      position: fixed;
      bottom: 0;
      width: 100%;
      text-align: center;
      padding: 18px;
      background: #065f46;
      color: #ffffff;
      font-size: 18px;
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <div class="header">
    DISPLAY FARMASI
    <div class="subtitle">Pengambilan Obat Pasien</div>

    <!-- DATE TIME -->
    <div class="datetime">
      <div id="date"></div>
      <div id="time" class="time"></div>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="container">

    <!-- NOMOR RESEP DIPANGGIL -->
    <div class="card call-card">
      <div class="call-label">NOMOR RESEP DIPANGGIL</div>
      <div class="call-number">R015</div>
      <div class="call-counter">COUNTER FARMASI</div>
    </div>

    <!-- DAFTAR ANTRIAN FARMASI -->
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>No Resep</th>
            <th>Nama Pasien</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>R016</td>
            <td>Dewi Lestari</td>
            <td class="status-process">Diproses</td>
          </tr>
          <tr>
            <td>R017</td>
            <td>Ahmad Fauzi</td>
            <td class="status-ready">Siap Diambil</td>
          </tr>
          <tr>
            <td>R018</td>
            <td>Siti Aminah</td>
            <td class="status-process">Diproses</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>

  <!-- FOOTER -->
  <div class="footer">
    Medisafe • Display Farmasi
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