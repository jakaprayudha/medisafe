<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Display Admisi</title>
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
      background: #1e293b;
      color: #ffffff;
      font-size: 34px;
      font-weight: bold;
      position: relative;
    }

    .subtitle {
      font-size: 20px;
      margin-top: 6px;
      opacity: 0.9;
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
      background: #f8fafc;
      border-radius: 18px;
      padding: 32px;
      margin-bottom: 40px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    /* ===== NOMOR DIPANGGIL ===== */
    .call-card {
      text-align: center;
      border: 6px solid #d97706;
    }

    .call-label {
      font-size: 30px;
      font-weight: bold;
      margin-bottom: 16px;
    }

    .call-number {
      font-size: 140px;
      font-weight: 800;
      color: #d97706;
      letter-spacing: 10px;
      margin: 12px 0;
    }

    .call-counter {
      margin-top: 10px;
      font-size: 36px;
      font-weight: bold;
      background: #fde68a;
      color: #92400e;
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
      background: #e5e7eb;
      font-weight: bold;
    }

    .table tr:nth-child(even) {
      background: #f1f5f9;
    }

    .status-wait {
      font-weight: bold;
    }

    /* ===== FOOTER ===== */
    .footer {
      position: fixed;
      bottom: 0;
      width: 100%;
      text-align: center;
      padding: 18px;
      background: #1e293b;
      color: #ffffff;
      font-size: 18px;
    }
  </style>
</head>

<body>

  <!-- HEADER -->
  <div class="header">
    DISPLAY ADMISI
    <div class="subtitle">Antrian Pendaftaran Pasien</div>

    <!-- DATE TIME -->
    <div class="datetime">
      <div id="date"></div>
      <div id="time" class="time"></div>
    </div>
  </div>

  <!-- CONTENT -->
  <div class="container">

    <!-- NOMOR DIPANGGIL -->
    <div class="card call-card text-center">
      <div class="call-label">PANGGILAN PASIEN</div>
      <div class="call-number" id="callPatient">-</div>
      <div class="call-counter" id="callQueue">-</div>
      <div class="call-counter" id="callLoket">-</div>
    </div>

    <!-- DAFTAR ANTRIAN -->
    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>No Antrian</th>
            <th>Nama Pasien</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="tableBody">
        </tbody>
      </table>
    </div>

  </div>
  <div class="footer">
    Medisafe • Display Admisi
  </div>

  <!-- ===== REALTIME DATE & TIME SCRIPT ===== -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../../controller/socket/socket.js"></script>
  <script>
    APP.window = APP.window || {};
    let voices = [];
    $(function() {
      APP.showQueue = function() {
        $.ajax({
          url: '../../controller/queue/listAntriAdmisiDisplay',
          type: 'GET',
          dataType: 'json',
          success: function(res) {
            let html = '';
            if (res == null) {
              $('#tableBody').html(`
              <tr>
                <td colspan="3" class="text-center">Tidak ada antrian</td>
              </tr>
          `);
              $('#callPatient').html('-');
              $('#callQueue').html('-');
              $('#callLoket').html('-');
              return;
            }
            res.data.forEach(function(item) {
              html += `
                <tr>
                  <td>${item.no_antrian}</td>
                  <td>${item.nama_pasien}</td>
                  <td class="status-wait">${item.status}</td>
                </tr>
              `;
            });
            $('#tableBody').html(html);
          }
        })
      }
      APP.showQueue();
      APP.CallAntrian = function(nama, nomor, loket, kdantri, target) {
        APP.showQueue();
        $('#callPatient').html(nama);
        $('#callQueue').html(nomor);
        $('#callLoket').html(target.toUpperCase());
        speakQueue(nama, target, nomor);
      }

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

      function loadVoices() {
        voices = speechSynthesis.getVoices();
      }
      speechSynthesis.onvoiceschanged = loadVoices;
      document.addEventListener("DOMContentLoaded", () => {
        loadVoices();
      });

      function speakQueue(nama, target, nomor) {
        if (!('speechSynthesis' in window)) return;

        const text = `Kepada pasien atas nama ${nama}, dengan antrian ${nomor}, dipersilakan ke loket ${target}`;
        const utterance = new SpeechSynthesisUtterance(text);

        utterance.rate = 0.9;
        utterance.pitch = 1;
        utterance.volume = 1;

        // Cari voice Indonesia, fallback aman
        let voice =
          voices.find(v => v.lang === 'id-ID') ||
          voices.find(v => v.lang && v.lang.startsWith('id')) ||
          voices[0];

        if (voice) utterance.voice = voice;

        speechSynthesis.cancel();
        speechSynthesis.speak(utterance);
      }
    })
  </script>

</body>

</html>