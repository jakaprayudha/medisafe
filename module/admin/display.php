<?php
$title = 'Display';
require '../../controller/view.php';

$root = dirname(__DIR__, 2);
$videoCount = count(glob($root . '/video/*.mp4') ?: []);

// status: siap | contoh (data belum terhubung) | hilang (file display tidak ada)
$displays = [
  [
    'key' => 'admisi',
    'judul' => 'Display Admisi',
    'desc' => 'Panggilan antrean pendaftaran & poliklinik, lengkap dengan suara panggilan dan video.',
    'path' => 'module/display/display-admisi',
    'icon' => 'mdi:account-voice',
    'warna' => 'primary',
    'fitur' => ['Panggilan suara', 'Realtime', 'Video'],
  ],
  [
    'key' => 'farmasi',
    'judul' => 'Display Farmasi',
    'desc' => 'Status antrean resep dan panggilan pengambilan obat di apotek.',
    'path' => 'module/display/display-farmasi',
    'icon' => 'mdi:pill',
    'warna' => 'success',
    'fitur' => ['Panggilan suara', 'Realtime'],
  ],
  [
    'key' => 'poli',
    'judul' => 'Display Poliklinik',
    'desc' => 'Jadwal dokter dan antrean di depan ruang poli.',
    'path' => 'module/display/display-poliklinik',
    'icon' => 'mdi:stethoscope',
    'warna' => 'info',
    'fitur' => ['Jadwal dokter', 'Antrean poli'],
  ],
  [
    'key' => 'bed',
    'judul' => 'Display Tempat Tidur',
    'desc' => 'Ketersediaan tempat tidur rawat inap per ruangan.',
    'path' => 'module/display/display-applicare',
    'icon' => 'mdi:bed',
    'warna' => 'warning',
    'fitur' => ['Per ruangan', 'Tersedia / penuh'],
    'contoh' => true, // halaman masih memakai data contoh statis
  ],
];
foreach ($displays as &$d) {
  $d['status'] = !is_file($root . '/' . $d['path'] . '.php') ? 'hilang' : (!empty($d['contoh']) ? 'contoh' : 'siap');
}
unset($d);
$jmlSiap = count(array_filter($displays, fn($d) => $d['status'] === 'siap'));
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .dp-hero {
      border-radius: 16px;
      background: linear-gradient(120deg, #5d87ff 0%, #4570ea 55%, #49beff 100%);
      color: #fff;
      padding: 22px 24px;
      position: relative;
      overflow: hidden;
    }

    .dp-hero::after {
      content: '';
      position: absolute;
      right: -60px;
      top: -80px;
      width: 260px;
      height: 260px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .08);
    }

    .dp-hero h4 {
      color: #fff;
    }

    .dp-hero .dp-clock {
      font-size: 2rem;
      font-weight: 800;
      line-height: 1;
      letter-spacing: 1px;
      font-variant-numeric: tabular-nums;
    }

    .dp-hero .dp-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 255, 255, .16);
      border-radius: 20px;
      padding: 4px 12px;
      font-size: .78rem;
    }

    .dp-card {
      border: 1px solid #e5eaef;
      border-radius: 16px;
      background: #fff;
      height: 100%;
      display: flex;
      flex-direction: column;
      transition: box-shadow .2s, transform .2s, border-color .2s;
    }

    .dp-card:hover {
      box-shadow: 0 10px 26px rgba(42, 53, 71, .1);
      transform: translateY(-2px);
      border-color: var(--dp-c);
    }

    .dp-card.off:hover {
      transform: none;
      box-shadow: none;
      border-color: #e5eaef;
    }

    /* mockup monitor */
    .dp-screen-wrap {
      padding: 18px 18px 0;
      max-width: 420px;
      width: 100%;
      margin: 0 auto;
    }

    .dp-screen {
      aspect-ratio: 16 / 7;
      border-radius: 10px;
      border: 6px solid #2a3547;
      background: var(--dp-bg);
      position: relative;
      overflow: hidden;
      padding: 8px;
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .dp-card.off .dp-screen {
      filter: grayscale(1);
      opacity: .55;
    }

    .dp-stand {
      width: 26%;
      height: 9px;
      margin: 0 auto;
      background: #2a3547;
      clip-path: polygon(15% 0, 85% 0, 100% 100%, 0 100%);
    }

    .dp-bar {
      height: 9px;
      border-radius: 3px;
      background: var(--dp-c);
      opacity: .9;
    }

    .dp-row {
      display: flex;
      gap: 5px;
      flex: 1;
      min-height: 0;
    }

    .dp-box {
      border-radius: 4px;
      background: #fff;
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      color: var(--dp-c);
      font-size: .9rem;
      position: relative;
    }

    .dp-box.dark {
      background: #2a3547;
      color: #fff;
    }

    .dp-box.video::after {
      content: '';
      border-left: 9px solid rgba(255, 255, 255, .8);
      border-top: 6px solid transparent;
      border-bottom: 6px solid transparent;
    }

    .dp-lines {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 4px;
      background: #fff;
      border-radius: 4px;
      padding: 5px;
    }

    .dp-lines span {
      height: 5px;
      border-radius: 3px;
      background: #e5eaef;
    }

    .dp-lines span:first-child {
      background: var(--dp-c);
      opacity: .5;
      width: 60%;
    }

    .dp-grid {
      flex: 1;
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 4px;
    }

    .dp-grid i {
      border-radius: 4px;
      background: #fff;
      border-top: 3px solid #13deb9;
    }

    .dp-grid i:nth-child(2),
    .dp-grid i:nth-child(6) {
      border-top-color: #fa896b;
    }

    .dp-live {
      position: absolute;
      top: 6px;
      right: 6px;
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #fa896b;
      box-shadow: 0 0 0 0 rgba(250, 137, 107, .6);
      animation: dpPulse 1.6s infinite;
    }

    @keyframes dpPulse {
      70% {
        box-shadow: 0 0 0 7px rgba(250, 137, 107, 0);
      }

      100% {
        box-shadow: 0 0 0 0 rgba(250, 137, 107, 0);
      }
    }

    .dp-body {
      padding: 14px 18px 18px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .dp-title {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 6px;
    }

    .dp-ic {
      width: 36px;
      height: 36px;
      border-radius: 10px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      background: var(--dp-bg);
      color: var(--dp-c);
    }

    .dp-desc {
      font-size: .82rem;
      color: #7c8fac;
      margin-bottom: 10px;
    }

    .dp-chip {
      display: inline-block;
      font-size: .7rem;
      font-weight: 600;
      padding: .2em .6em;
      border-radius: 6px;
      background: #f2f5f9;
      color: #5a6a85;
      margin: 0 4px 4px 0;
    }

    .dp-status {
      font-size: .7rem;
      font-weight: 700;
      padding: .25em .6em;
      border-radius: 20px;
      margin-left: auto;
      white-space: nowrap;
    }

    .dp-note {
      font-size: .74rem;
      border-radius: 8px;
      padding: 6px 9px;
      margin-top: 6px;
    }

    .dp-actions {
      display: flex;
      gap: 8px;
      margin-top: auto;
      padding-top: 12px;
    }

    .dp-actions .btn-icon {
      width: 38px;
      flex-shrink: 0;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .dp-side {
      border: 1px solid #e5eaef;
      border-radius: 16px;
      background: #fff;
      padding: 18px;
    }

    .dp-step {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      font-size: .84rem;
      color: #5a6a85;
    }

    .dp-step+.dp-step {
      margin-top: 12px;
    }

    .dp-step b {
      color: #2a3547;
    }

    .dp-num {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: #ecf2ff;
      color: #5d87ff;
      font-weight: 700;
      font-size: .75rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    kbd {
      font-size: .72rem;
      padding: .1em .4em;
    }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HERO -->
          <div class="dp-hero mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 position-relative" style="z-index:1">
              <div>
                <h4 class="fw-bold mb-1">Display Layanan</h4>
                <div class="opacity-75 small mb-2">Tampilkan antrean dan informasi layanan di TV / monitor ruang tunggu.</div>
                <span class="dp-pill"><iconify-icon icon="mdi:television-play"></iconify-icon> <?= $jmlSiap ?> display siap dipakai</span>
                <span class="dp-pill ms-1"><iconify-icon icon="mdi:filmstrip"></iconify-icon> <?= $videoCount ?> video</span>
              </div>
              <div class="text-md-end">
                <div class="dp-clock" id="dpClock">--:--:--</div>
                <div class="small opacity-75 mt-1" id="dpDate"></div>
              </div>
            </div>
          </div>

          <div class="row g-4">
            <!-- DAFTAR DISPLAY -->
            <div class="col-xl-9">
              <div class="row g-3">
                <?php foreach ($displays as $d):
                  $warna = [
                    'primary' => ['#5d87ff', '#ecf2ff'],
                    'success' => ['#13deb9', '#e6fffa'],
                    'info' => ['#49beff', '#e8f7ff'],
                    'warning' => ['#ffae1f', '#fef5e5'],
                  ][$d['warna']];
                  $siap = $d['status'] !== 'hilang';
                ?>
                  <div class="col-md-6">
                    <div class="dp-card <?= $siap ? '' : 'off' ?>" style="--dp-c:<?= $warna[0] ?>;--dp-bg:<?= $warna[1] ?>">
                      <div class="dp-screen-wrap">
                        <div class="dp-screen">
                          <?php if ($d['status'] === 'siap'): ?><span class="dp-live"></span><?php endif; ?>
                          <div class="dp-bar"></div>
                          <?php if ($d['key'] === 'admisi'): ?>
                            <div class="dp-row">
                              <div class="dp-box" style="flex:1.2">A-012</div>
                              <div class="dp-box dark video" style="flex:1.5"></div>
                            </div>
                            <div class="dp-row" style="flex:.6">
                              <div class="dp-box" style="font-size:.6rem">B-004</div>
                              <div class="dp-box" style="font-size:.6rem">C-002</div>
                              <div class="dp-box" style="font-size:.6rem">A-013</div>
                            </div>
                          <?php elseif ($d['key'] === 'farmasi'): ?>
                            <div class="dp-row">
                              <div class="dp-box" style="flex:1">F-021</div>
                              <div class="dp-lines" style="flex:1.4"><span></span><span></span><span></span><span></span></div>
                            </div>
                          <?php elseif ($d['key'] === 'poli'): ?>
                            <div class="dp-row">
                              <div class="dp-lines"><span></span><span></span><span></span></div>
                              <div class="dp-lines"><span></span><span></span><span></span></div>
                              <div class="dp-box" style="flex:.8">07</div>
                            </div>
                          <?php else: ?>
                            <div class="dp-grid"><i></i><i></i><i></i><i></i><i></i><i></i></div>
                          <?php endif; ?>
                        </div>
                        <div class="dp-stand"></div>
                      </div>

                      <div class="dp-body">
                        <div class="dp-title">
                          <span class="dp-ic"><iconify-icon icon="<?= $d['icon'] ?>" width="20" height="20"></iconify-icon></span>
                          <h6 class="fw-semibold mb-0"><?= htmlspecialchars($d['judul']) ?></h6>
                          <?php if ($d['status'] === 'siap'): ?>
                            <span class="dp-status bg-success-subtle text-success">Siap</span>
                          <?php elseif ($d['status'] === 'contoh'): ?>
                            <span class="dp-status bg-warning-subtle text-warning">Data contoh</span>
                          <?php else: ?>
                            <span class="dp-status bg-danger-subtle text-danger">Tidak tersedia</span>
                          <?php endif; ?>
                        </div>
                        <div class="dp-desc"><?= htmlspecialchars($d['desc']) ?></div>
                        <div>
                          <?php foreach ($d['fitur'] as $f): ?><span class="dp-chip"><?= htmlspecialchars($f) ?></span><?php endforeach; ?>
                        </div>
                        <?php if ($d['status'] === 'contoh'): ?>
                          <div class="dp-note bg-warning-subtle text-warning-emphasis">Masih menampilkan data contoh, belum terhubung ke data ruangan &amp; tempat tidur.</div>
                        <?php elseif ($d['status'] === 'hilang'): ?>
                          <div class="dp-note bg-danger-subtle text-danger-emphasis">Halaman display belum tersedia di server.</div>
                        <?php endif; ?>

                        <div class="dp-actions">
                          <?php if ($siap): ?>
                            <a href="<?= $d['path'] ?>" target="_blank" rel="noopener" class="btn btn-sm flex-grow-1 text-white" style="background:<?= $warna[0] ?>">
                              <iconify-icon icon="mdi:open-in-new" class="me-1"></iconify-icon> Buka Display
                            </a>
                            <button type="button" class="btn btn-sm btn-light btn-icon dp-copy" data-path="<?= $d['path'] ?>" title="Salin link">
                              <iconify-icon icon="mdi:content-copy" width="16"></iconify-icon>
                            </button>
                          <?php else: ?>
                            <button type="button" class="btn btn-sm btn-light flex-grow-1" disabled>Tidak tersedia</button>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- SAMPING -->
            <div class="col-xl-3">
              <div class="dp-side mb-3">
                <div class="d-flex align-items-center gap-3">
                  <span class="dp-ic" style="--dp-bg:#efecfd;--dp-c:#7460ee;width:44px;height:44px"><iconify-icon icon="mdi:movie-open-play" width="22"></iconify-icon></span>
                  <div class="flex-grow-1">
                    <div class="fw-semibold">Video Display</div>
                    <div class="small text-muted"><?= $videoCount ? "$videoCount video diputar bergantian" : 'Belum ada video' ?></div>
                  </div>
                </div>
                <a href="module/admin/setting_display" class="btn btn-outline-primary btn-sm w-100 mt-3">
                  <iconify-icon icon="mdi:cog-outline" class="me-1"></iconify-icon> Atur Video
                </a>
              </div>

              <div class="dp-side">
                <div class="fw-semibold mb-3"><iconify-icon icon="mdi:lightbulb-on-outline" class="text-warning me-1"></iconify-icon> Cara memasang di TV</div>
                <div class="dp-step"><span class="dp-num">1</span><div>Buka display di browser komputer / TV box yang terhubung ke TV, <b>login</b> dengan akun faskes.</div></div>
                <div class="dp-step"><span class="dp-num">2</span><div>Tekan <kbd>F11</kbd> agar tampil <b>layar penuh</b>.</div></div>
                <div class="dp-step"><span class="dp-num">3</span><div><b>Klik sekali</b> di layar agar suara panggilan &amp; video diizinkan browser.</div></div>
                <div class="dp-step"><span class="dp-num">4</span><div>Matikan mode hemat daya / screensaver agar layar tetap menyala.</div></div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const jam = document.getElementById('dpClock');
      const tgl = document.getElementById('dpDate');
      const tick = () => {
        const n = new Date();
        jam.textContent = n.toLocaleTimeString('id-ID', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
        }).replace(/\./g, ':');
        tgl.textContent = n.toLocaleDateString('id-ID', {
          weekday: 'long',
          day: 'numeric',
          month: 'long',
          year: 'numeric'
        });
      };
      tick();
      setInterval(tick, 1000);

      const toast = (icon, title) => Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer: 2000
      });

      function salin(teks) {
        if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(teks);
        return new Promise((ok, gagal) => {
          const ta = Object.assign(document.createElement('textarea'), {
            value: teks
          });
          ta.style.cssText = 'position:fixed;opacity:0';
          document.body.appendChild(ta);
          ta.select();
          try {
            document.execCommand('copy') ? ok() : gagal();
          } catch (e) {
            gagal(e);
          } finally {
            ta.remove();
          }
        });
      }

      document.querySelectorAll('.dp-copy').forEach(btn => btn.addEventListener('click', () => {
        const url = new URL(btn.dataset.path, document.baseURI).href;
        salin(url).then(() => toast('success', 'Link display disalin'))
          .catch(() => Swal.fire({
            title: 'Salin link',
            input: 'text',
            inputValue: url,
            confirmButtonText: 'Tutup'
          }));
      }));
    })();
  </script>
</body>

</html>
