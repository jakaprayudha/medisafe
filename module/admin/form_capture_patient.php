<?php
/**
 * Foto pasien kunjungan (ambil dari kamera / unggah file). API controller/visit/getFoto.php & saveFoto.php.
 * Perbaikan: query pasien dirangkai dari URL (SQL injection) dan INNER JOIN ms_doctor ON id_doctor (kolom berisi nama
 * dokter) -> data kosong, usia memicu error, nama/gender kosong; difilter nomor RM dari URL; assesmen.php dimuat tanpa
 * perlu; modal tanda tangan tanpa tombol pembuka -> error JS "openModal null" (+ library signature_pad dari CDN tanpa
 * versi pasti); kamera tidak pernah dimatikan setelah modal ditutup; getUserMedia tanpa penanganan error (izin
 * ditolak / bukan HTTPS -> diam saja); "Simpan" tanpa foto mengirim kanvas kosong; foto besar dikirim mentah;
 * tanpa hapus foto; tanpa indikator simpan.
 */
$title = 'Foto Pasien';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);

$st = $koneksi->prepare("SELECT visit_status FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$kunj = $st->get_result()->fetch_assoc();
$st->close();
$batal = $kunj && (int) $kunj['visit_status'] === 99;
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .fp .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
    .fp .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #e0f2fe; color: #075985; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .fp .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .fp .kepala small { color: #475569; }
    .fp .tata { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 20px; align-items: start; }
    @media (max-width: 991.98px) { .fp .tata { grid-template-columns: 1fr; } .fp .bingkai { height: 320px; } }
    .fp .bingkai { border: 1px solid #e2e8f0; border-radius: 14px; background: #f8fafc; height: 420px; width: 100%; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
    .fp .bingkai img { width: 100%; height: 100%; object-fit: contain; cursor: zoom-in; }
    .fp .kosong { text-align: center; color: #334155; padding: 20px; }
    .fp .kosong i { font-size: 3rem; color: #64748b; display: block; margin-bottom: 6px; }
    .fp .bingkai .muat { position: absolute; inset: 0; background: rgba(255, 255, 255, .85); display: flex; align-items: center; justify-content: center; color: #1e293b; }
    .fp .panel { border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 10px; }
    .fp .panel .btn { font-weight: 600; text-align: left; }
    .fp .panel .ket { font-size: .82rem; color: #334155; }
    .fp .st { display: inline-block; font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; }
    .fp .st-ok { background: #dcfce7; color: #14532d; }
    .fp .st-belum { background: #fee2e2; color: #991b1b; }
    #modalFoto .layar { position: relative; background: #0f172a; border-radius: 12px; overflow: hidden; aspect-ratio: 4 / 3; display: flex; align-items: center; justify-content: center; }
    #modalFoto video, #modalFoto .layar img { width: 100%; height: 100%; object-fit: contain; }
    #modalFoto .pesan { color: #e2e8f0; text-align: center; padding: 20px; }
    #modalFoto .pesan i { font-size: 2.4rem; display: block; margin-bottom: 6px; }
    #lihatModal .modal-content { background: #0f172a; }
    #lihatModal img { max-width: 100%; max-height: 82vh; object-fit: contain; display: block; margin: 0 auto; }
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
          <?php
          if ($rme === 'a') include 'menu_rme.php';
          elseif ($rme === 'b') include 'menu_rmeb.php';
          elseif ($rme === 'c') include 'menu_rme_inap.php';
          ?>
          <div class="row">
            <div class="col-12"><?php require 'card-pasien.php'; ?></div>
            <div class="col-12">
              <div class="card w-100">
                <div class="card-body p-4 fp">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-camera"></i></span>
                    <div class="flex-grow-1">
                      <h5>Foto Pasien <span class="st" id="fpStatus"></span></h5>
                      <small>Foto pasien saat kunjungan ini — untuk verifikasi identitas & kelengkapan klaim.</small>
                    </div>
                  </div>

                  <?php if (!$kunj): ?>
                    <div class="alert alert-danger mb-0"><i class="ti ti-alert-triangle me-1"></i>Kunjungan tidak ditemukan untuk faskes ini.</div>
                  <?php else: ?>
                    <div class="tata">
                      <div class="bingkai" id="fpBingkai"><div class="muat"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</div></div>
                      <div class="panel">
                        <?php if ($batal): ?>
                          <div class="alert alert-warning mb-0 small"><i class="ti ti-ban me-1"></i>Kunjungan sudah dibatalkan — foto hanya dapat dilihat.</div>
                        <?php else: ?>
                          <button type="button" class="btn btn-primary" id="btnFoto"><i class="ti ti-camera me-2"></i>Ambil dari kamera</button>
                          <button type="button" class="btn btn-outline-dark" id="btnUnggah"><i class="ti ti-upload me-2"></i>Unggah file foto</button>
                          <button type="button" class="btn btn-outline-danger d-none" id="btnHapus"><i class="ti ti-trash me-2"></i>Hapus foto</button>
                          <input type="file" id="uploadFile" class="d-none" accept="image/jpeg,image/png,image/webp">
                        <?php endif; ?>
                        <div class="ket" id="fpKet"></div>
                        <div class="ket"><i class="ti ti-info-circle me-1"></i>Satu foto per kunjungan; foto baru menggantikan foto lama. Foto otomatis diperkecil (maks. 1600 px) sebelum disimpan.</div>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($kunj): ?>
  <!-- MODAL KAMERA / PRATINJAU -->
  <div class="modal fade" id="modalFoto" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="fpJudul">Ambil Foto Pasien</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="layar" id="fpLayar">
            <video id="webcam" autoplay playsinline muted></video>
          </div>
          <canvas id="canvas" class="d-none"></canvas>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark me-auto d-none" id="btnBalik" title="Ganti kamera depan / belakang"><i class="ti ti-camera-rotate me-1"></i>Ganti kamera</button>
          <button type="button" class="btn btn-outline-dark d-none" id="btnUlang"><i class="ti ti-refresh me-1"></i>Ulangi</button>
          <button type="button" class="btn btn-primary d-none" id="captureBtn"><i class="ti ti-camera me-1"></i>Ambil Foto</button>
          <button type="button" class="btn btn-success d-none" id="saveFoto"><i class="ti ti-device-floppy me-1"></i>Simpan Foto</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL LIHAT -->
  <div class="modal fade" id="lihatModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <a class="btn btn-sm btn-light ms-auto me-2" id="lihatAsli" target="_blank" rel="noopener"><i class="ti ti-external-link me-1"></i>Buka asli</a>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body"><img id="lihatImg" alt="Foto pasien"></div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php require 'library.php'; ?>
  <?php if ($kunj): ?>
  <script>
    (function() {
      'use strict';
      const NO = <?= json_encode($no) ?>;
      const BATAL = <?= json_encode($batal) ?>;
      const SISI = 1600;   // sisi terpanjang foto yang disimpan
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tgl = s => { const m = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}:\d{2}))?/.exec(s || ''); return m ? `${+m[3]} ${BLN[m[2] - 1]} ${m[1]}${m[4] ? ' ' + m[4] : ''}` : ''; };
      let FOTO = null;

      /* ===== Tampilan foto tersimpan ===== */
      function muat() {
        $.ajax({ url: 'controller/visit/getFoto.php', data: { no: NO }, dataType: 'json', cache: false })
          .done(res => { FOTO = res && res.foto ? res : null; render(); })
          .fail(x => { $id('fpBingkai').innerHTML = `<div class="kosong text-danger"><i class="ti ti-alert-triangle"></i>${esc((x.responseJSON && x.responseJSON.message) || 'Gagal memuat foto.')}</div>`; });
      }
      function render() {
        const b = $id('fpBingkai'), st = $id('fpStatus'), hapus = $id('btnHapus');
        st.className = 'st ' + (FOTO ? 'st-ok' : 'st-belum');
        st.textContent = FOTO ? 'Tersedia' : 'Belum ada foto';
        if (hapus) hapus.classList.toggle('d-none', !FOTO);
        $id('fpKet').innerHTML = FOTO && FOTO.waktu ? `<i class="ti ti-clock me-1"></i>Diambil ${esc(tgl(FOTO.waktu))}` : '';
        if (!FOTO) {
          b.innerHTML = `<div class="kosong"><i class="ti ti-user"></i><b>Belum ada foto pasien</b><div class="small mt-1">${BATAL ? '' : 'Ambil dari kamera atau unggah file foto.'}</div></div>`;
          return;
        }
        b.innerHTML = `<img src="${esc(FOTO.foto)}" alt="Foto pasien" id="fpImg">`;
        $id('fpImg').addEventListener('error', () => { b.innerHTML = '<div class="kosong"><i class="ti ti-photo-off"></i>File foto tidak ditemukan di server — silakan ambil ulang.</div>'; }, { once: true });
        $id('fpImg').addEventListener('click', () => {
          $id('lihatImg').src = FOTO.foto;
          $id('lihatAsli').href = FOTO.foto;
          bootstrap.Modal.getOrCreateInstance($id('lihatModal')).show();
        });
      }

      if (!BATAL) {
        /* ===== Kamera ===== */
        const modalEl = $id('modalFoto'), video = $id('webcam'), layar = $id('fpLayar');
        let stream = null, hadap = 'user', hasil = null, terbuka = false, giliran = 0;   // hasil = dataURL JPEG siap simpan

        const tombol = (...ids) => ['btnBalik', 'btnUlang', 'captureBtn', 'saveFoto'].forEach(i => $id(i).classList.toggle('d-none', !ids.includes(i)));
        function matikan() { if (stream) stream.getTracks().forEach(t => t.stop()); stream = null; video.srcObject = null; }
        function pesan(ikon, teks) { layar.innerHTML = `<div class="pesan"><i class="ti ${ikon}"></i>${teks}</div>`; }
        function tampilVideo() { layar.innerHTML = ''; layar.appendChild(video); }

        async function nyalakan() {
          matikan();
          hasil = null;
          tampilVideo();
          tombol();
          if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            pesan('ti-camera-off', 'Kamera tidak tersedia di browser ini.<br><small>Akses kamera membutuhkan HTTPS. Gunakan "Unggah file foto".</small>');
            return;
          }
          const ke = ++giliran;
          try {
            const s = await navigator.mediaDevices.getUserMedia({ video: { facingMode: hadap, width: { ideal: 1280 }, height: { ideal: 960 } }, audio: false });
            // modal sudah ditutup / kamera diganti saat menunggu izin -> matikan stream ini
            if (!terbuka || ke !== giliran) { s.getTracks().forEach(t => t.stop()); return; }
            stream = s;
            video.srcObject = stream;
            const banyak = (await navigator.mediaDevices.enumerateDevices()).filter(d => d.kind === 'videoinput').length > 1;
            tombol('captureBtn', ...(banyak ? ['btnBalik'] : []));
          } catch (e) {
            if (!terbuka || ke !== giliran) return;
            const ditolak = e && (e.name === 'NotAllowedError' || e.name === 'SecurityError');
            pesan('ti-camera-off', ditolak ? 'Izin kamera ditolak.<br><small>Izinkan akses kamera di pengaturan browser, lalu coba lagi.</small>'
              : 'Kamera tidak dapat dibuka.<br><small>' + esc(e && e.message || 'Pastikan kamera terpasang & tidak dipakai aplikasi lain.') + '</small>');
          }
        }

        // perkecil + JPEG; sumber: video / gambar
        function keJpeg(src, w, h) {
          const s = Math.min(1, SISI / Math.max(w, h)), c = $id('canvas');
          c.width = Math.round(w * s);
          c.height = Math.round(h * s);
          const k = c.getContext('2d');
          k.fillStyle = '#fff';
          k.fillRect(0, 0, c.width, c.height);
          k.drawImage(src, 0, 0, c.width, c.height);
          return c.toDataURL('image/jpeg', 0.86);
        }
        function pratinjau(dataUrl, dariKamera) {
          hasil = dataUrl;
          layar.innerHTML = `<img src="${dataUrl}" alt="Pratinjau">`;
          tombol('saveFoto', ...(dariKamera ? ['btnUlang'] : []));
        }

        $id('btnFoto').addEventListener('click', () => {
          $id('fpJudul').textContent = 'Ambil Foto Pasien';
          terbuka = true;
          bootstrap.Modal.getOrCreateInstance(modalEl).show();
          nyalakan();
        });
        $id('captureBtn').addEventListener('click', () => {
          if (!video.videoWidth) return toast('Kamera belum siap, tunggu sebentar.', 'warning');
          const d = keJpeg(video, video.videoWidth, video.videoHeight);
          matikan();
          pratinjau(d, true);
        });
        $id('btnUlang').addEventListener('click', nyalakan);
        $id('btnBalik').addEventListener('click', () => { hadap = hadap === 'user' ? 'environment' : 'user'; nyalakan(); });
        modalEl.addEventListener('hidden.bs.modal', () => { terbuka = false; giliran++; matikan(); hasil = null; });

        /* ===== Unggah file ===== */
        $id('btnUnggah').addEventListener('click', () => $id('uploadFile').click());
        $id('uploadFile').addEventListener('change', function() {
          const f = this.files[0];
          this.value = '';
          if (!f) return;
          if (!/^image\/(jpeg|png|webp)$/.test(f.type)) return Swal.fire('Format tidak didukung', 'Gunakan foto JPG, PNG, atau WEBP.', 'warning');
          if (f.size > 20 * 1024 * 1024) return Swal.fire('File terlalu besar', 'Maksimal 20 MB sebelum diperkecil.', 'warning');
          const url = URL.createObjectURL(f), img = new Image();
          img.onload = () => {
            URL.revokeObjectURL(url);
            $id('fpJudul').textContent = 'Pratinjau Foto';
            matikan();
            terbuka = true;
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            pratinjau(keJpeg(img, img.naturalWidth, img.naturalHeight), false);
          };
          img.onerror = () => { URL.revokeObjectURL(url); Swal.fire('Gagal', 'File gambar tidak dapat dibaca.', 'error'); };
          img.src = url;
        });

        /* ===== Simpan / hapus ===== */
        const kirim = body => fetch('controller/visit/saveFoto.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) })
          .then(r => r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' })));

        $id('saveFoto').addEventListener('click', function() {
          if (!hasil) return Swal.fire('Belum ada foto', 'Ambil foto atau pilih file terlebih dahulu.', 'warning');
          const btn = this;
          btn.disabled = true;
          btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
          kirim({ visit: NO, image: hasil })
            .then(res => {
              if (res.status !== 'success') throw new Error(res.message || 'Gagal menyimpan foto.');
              bootstrap.Modal.getInstance(modalEl).hide();
              toast(res.message || 'Foto tersimpan.');
              muat();
            })
            .catch(e => Swal.fire('Gagal', e.message === 'Failed to fetch' ? 'Tidak dapat terhubung ke server.' : e.message, 'error'))
            .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan Foto'; });
        });

        $id('btnHapus').addEventListener('click', () => {
          Swal.fire({ icon: 'warning', title: 'Hapus foto pasien?', text: 'Foto kunjungan ini akan dihapus permanen.', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true })
            .then(x => {
              if (!x.isConfirmed) return;
              kirim({ visit: NO, hapus: 1 })
                .then(res => res.status === 'success' ? toast('Foto dihapus.') : Swal.fire('Gagal', res.message || 'Gagal menghapus foto.', 'error'))
                .catch(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
                .finally(muat);
            });
        });
      }

      muat();
    })();
  </script>
  <?php endif; ?>
</body>

</html>
