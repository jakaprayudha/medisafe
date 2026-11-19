<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Display Admisi Poliklinik</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
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

    /* Tambahan ruang video di kiri bawah */
    .container {
      min-height: 0;
    }

    .left-panel {
      min-width: 0;
      min-height: 0;
    }

    .left-panel .card-call {
      min-height: 0;
      padding: 15px;
      overflow: auto;
    }

    .left-panel .call-label {
      font-size: clamp(18px, 2.6vh, 28px);
      margin-bottom: 10px;
    }

    .left-panel .call-number {
      font-size: clamp(28px, 6vh, 70px);
      margin: 10px 0;
      overflow-wrap: anywhere;
    }

    .left-panel .call-counter {
      font-size: clamp(18px, 3vh, 32px);
      padding: 10px 20px;
    }

    .admisi-video-panel {
      flex: 0 0 50%;
      min-height: 0;
      position: relative;
      overflow: hidden;
      border-radius: 12px;
      background: #000;
      box-shadow: 0 4px 15px rgba(0, 0, 0, .1);
    }

    .admisi-video-panel video {
      display: block;
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

    .admisi-video-message {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      color: white;
      text-align: center;
    }

    .admisi-video-message[hidden] {
      display: none;
    }

    .admisi-video-start {
      position: absolute;
      bottom: 12px;
      left: 12px;
      padding: 10px 18px;
      border: 0;
      border-radius: 6px;
      cursor: pointer;
    }
  </style>
</head>

<body>
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
  <div class="container">
    <div class="left-panel">
      <div class="card-call">
        <div class="call-label">PANGGILAN PASIEN</div>
        <div class="call-number" id="callPatient">-</div>
        <div>
          <div class="call-counter" id="callQueue">Menunggu...</div>
        </div>
      </div>
      <?php
      require_once dirname(__DIR__, 2) . '/controller/display/videoDisplay.php';
      $admisiVideoPlaylist = vdPlaylist((int) ($_SESSION['id_customer'] ?? 0), '../../')['urls'];
      ?>
      <div class="admisi-video-panel">
        <video id="admisiVideo" autoplay playsinline preload="auto"></video>
        <div id="admisiVideoMessage" class="admisi-video-message" hidden></div>
        <button id="admisiVideoStart" class="admisi-video-start" type="button" hidden>Putar video</button>
      </div>
    </div>
    <div class="right-panel">
      <div class="right-header">
        DAFTAR PASIEN MENUNGGU
      </div>
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
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="footer">
    <div class="footer-label">INFORMASI</div>
    <div class="marquee-container">
      <marquee scrollamount="6">Silakan duduk di ruang tunggu. Harap bersabar menunggu panggilan dari dokter pada poliklinik tujuan Anda. Terima kasih.</marquee>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs" crossorigin="anonymous"></script>
  <script src="../../controller/socket/socket.js"></script>
  <script>
    APP.window = APP.window || {};
    let groupedQueueData = {};
    let poliKeys = [];
    let currentPoliIndex = 0;
    let currentPageIndex = 0;
    let itemsPerPage = 10;
    let rotationInterval;

    const NORMAL_VOLUME = 0.8;
    const DUCKED_VOLUME = 0.1;
    const FALLBACK_DURATION = 8000; 

    let restoreTimer = null;
    let fadeInterval = null;
    let activeAudios = new Set();

    function fadeVideoVolume(targetVolume, duration = 300) {
      const video = document.getElementById('admisiVideo');
      if (!video) return;

      clearInterval(fadeInterval);
      const startVolume = video.volume;
      const diff = targetVolume - startVolume;
      if (Math.abs(diff) < 0.01) {
        video.volume = targetVolume;
        return;
      }

      const steps = 15;
      const stepTime = duration / steps;
      let currentStep = 0;

      fadeInterval = setInterval(() => {
        currentStep++;
        const nextVol = startVolume + (diff * (currentStep / steps));
        video.volume = Math.max(0, Math.min(1, nextVol));
        if (currentStep >= steps) {
          video.volume = targetVolume;
          clearInterval(fadeInterval);
        }
      }, stepTime);
    }

    function isVoicePlaying() {
      const ttsActive = ('speechSynthesis' in window) &&
        (window.speechSynthesis.speaking || window.speechSynthesis.pending);
      return activeAudios.size > 0 || ttsActive;
    }

    function duckVideoVolume(useFallbackTimer = false) {
      clearTimeout(restoreTimer);
      fadeVideoVolume(DUCKED_VOLUME);

      if (useFallbackTimer) {
        restoreTimer = setTimeout(() => {
          if (!isVoicePlaying()) {
            fadeVideoVolume(NORMAL_VOLUME);
          }
        }, FALLBACK_DURATION);
      }
    }

    function scheduleRestoreVolume() {
      clearTimeout(restoreTimer);
      // Beri jeda 700ms agar tidak naik-turun jika panggilan terdiri dari beberapa file audio berurutan
      restoreTimer = setTimeout(() => {
        if (!isVoicePlaying()) {
          fadeVideoVolume(NORMAL_VOLUME);
        } else {
          scheduleRestoreVolume();
        }
      }, 700);
    }

    // 1. Deteksi otomatis jika socket.js memutar audio (new Audio / <audio>)
    const origAudioPlay = HTMLAudioElement.prototype.play;
    HTMLAudioElement.prototype.play = function() {
      const audioEl = this;
      activeAudios.add(audioEl);
      duckVideoVolume(false);

      const onFinish = () => {
        activeAudios.delete(audioEl);
        scheduleRestoreVolume();
      };

      audioEl.addEventListener('ended', onFinish, {
        once: true
      });
      audioEl.addEventListener('pause', onFinish, {
        once: true
      });
      audioEl.addEventListener('error', onFinish, {
        once: true
      });

      return origAudioPlay.apply(this, arguments);
    };

    // 2. Deteksi otomatis jika socket.js memakai Text-to-Speech (speechSynthesis)
    if ('speechSynthesis' in window) {
      const origSpeak = window.speechSynthesis.speak.bind(window.speechSynthesis);
      window.speechSynthesis.speak = function(utterance) {
        duckVideoVolume(false);
        utterance.addEventListener('end', scheduleRestoreVolume, {
          once: true
        });
        utterance.addEventListener('error', scheduleRestoreVolume, {
          once: true
        });
        return origSpeak(utterance);
      };
    }

    // Opsional: bisa dipanggil manual dari socket.js jika diperlukan
    APP.duckVideoVolume = duckVideoVolume;
    APP.restoreVideoVolume = scheduleRestoreVolume;

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
              let poliName = item.poli ? item.poli.toUpperCase() : 'POLIKLINIK UMUM';
              if (!groupedQueueData[poliName]) {
                groupedQueueData[poliName] = [];
              }
              if (item.status == "0" || item.status == 0) {
                groupedQueueData[poliName].push(item);
              }
            });
            poliKeys = Object.keys(groupedQueueData);
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
        let totalPages = Math.ceil(patients.length / itemsPerPage);
        if (totalPages === 0) totalPages = 1;
        let startIndex = currentPageIndex * itemsPerPage;
        let endIndex = startIndex + itemsPerPage;
        let paginatedPatients = patients.slice(startIndex, endIndex);
        let container = $('#tableContainer');
        container.removeClass('fade-transition');
        void container[0].offsetWidth;
        container.addClass('fade-transition');
        let displayTitle = currentPoliName;
        if (totalPages > 1) {
          displayTitle += ` (${currentPageIndex + 1}/${totalPages})`;
        }
        $('#currentPoliDisplay').text(displayTitle);
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
        currentPageIndex++;
        if (currentPageIndex >= totalPages) {
          currentPageIndex = 0;
          currentPoliIndex++;
          if (currentPoliIndex >= poliKeys.length) {
            currentPoliIndex = 0;
          }
        }
      }

      APP.showQueue();

      APP.showAntrianPoli = function(nama, dokter) {
        // 3. Pelankan video ke 10% saat panggilan masuk (dengan timer cadangan 8 detik)
        duckVideoVolume(true);

        $('#callPatient').html(nama.toUpperCase());
        let namaDokter = dokter.toUpperCase().replace(/^DR\./i, 'Dr.');
        $('#callQueue').html("Ruangan : " + namaDokter);
        APP.showQueue();
      }
    });
  </script>
  <script>
    (function() {
      let playlist = <?= json_encode($admisiVideoPlaylist, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>;
      const video = document.getElementById('admisiVideo');
      const message = document.getElementById('admisiVideoMessage');
      const startButton = document.getElementById('admisiVideoStart');
      let index = 0,
        failures = 0,
        retryTimer;

      function showMessage(text) {
        message.textContent = text;
        message.hidden = false;
      }

      function playVideo() {
        const attempt = video.play();
        if (attempt) attempt.catch(function(error) {
          if (error.name === 'NotAllowedError') startButton.hidden = false;
        });
      }

      function loadVideo() {
        clearTimeout(retryTimer);
        message.hidden = true;
        startButton.hidden = true;
        video.src = playlist[index];
        video.load();
        playVideo();
      }

      video.muted = false;
      video.volume = 1.0;

      video.addEventListener('playing', function() {
        failures = 0;
        message.hidden = true;
        startButton.hidden = true;
      });
      // Segarkan daftar putar dari server (video baru / dihapus / urutan diubah) tanpa reload layar.
      const PLAYLIST_API = '../../controller/display/videoPlaylist.php';
      async function segarkanPlaylist() {
        try {
          const r = await fetch(PLAYLIST_API, {
            cache: 'no-store',
            credentials: 'same-origin'
          });
          const j = await r.json();
          if (j.status !== 'success' || !Array.isArray(j.urls)) return false;
          const berubah = JSON.stringify(j.urls) !== JSON.stringify(playlist);
          playlist = j.urls;
          return berubah;
        } catch (e) {
          return false;
        }
      }

      video.addEventListener('ended', async function() {
        const sekarang = playlist[index];
        index = index + 1;
        if (index >= playlist.length) {
          index = 0;
          await segarkanPlaylist();
        } else if (await segarkanPlaylist()) {
          // lanjut ke video sesudah video yang barusan diputar pada urutan baru
          const i = playlist.indexOf(sekarang);
          index = i >= 0 ? i + 1 : 0;
          if (index >= playlist.length) index = 0;
        }
        if (!playlist.length) {
          video.removeAttribute('src');
          video.load();
          showMessage('Belum ada video di folder video.');
          return;
        }
        loadVideo();
      });
      video.addEventListener('error', function() {
        if (!playlist.length) return;
        failures++;
        if (failures >= playlist.length) {
          showMessage('Video tidak dapat diputar. Periksa file dan alamat folder video.');
          return;
        }
        index = (index + 1) % playlist.length;
        retryTimer = setTimeout(loadVideo, 500);
      });
      startButton.addEventListener('click', playVideo);

      if (playlist.length) loadVideo();
      else showMessage('Belum ada video di folder video.');

      // saat kosong, cek tiap 60 detik apakah sudah ada video baru
      setInterval(async function() {
        if (playlist.length) return;
        await segarkanPlaylist();
        if (playlist.length) {
          index = 0;
          failures = 0;
          loadVideo();
        }
      }, 60000);
    })();
  </script>


</body>

</html>