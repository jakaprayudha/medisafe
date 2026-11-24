<?php
/**
 * Surat pernyataan peserta BPJS (persetujuan pemanfaatan data medis) + tanda tangan pasien.
 * API: controller/visit/getTTD.php (baca), controller/admisi/saveSignature.php (simpan, pasien_visit.signature_path).
 * Perbaikan: spasi sebelum <?php (output terkirim sebelum session/header); query kunjungan dirangkai dari URL
 * (SQL injection); $rm tidak didefinisikan (warning) & no/rme tanpa default; tombol "Setuju" membuka modal DUA kali
 * (onclick + listener dokumen -> dua backdrop); tanda tangan kosong tetap tersimpan (cek "data:," tidak pernah benar);
 * tinggi kanvas "400" tanpa satuan & ukuran kanvas dihitung ulang tiap modal dibuka (goresan hilang); goresan
 * tersambung dari titik terakhir & tetap menggambar saat kursor keluar kanvas; tidak bisa tanda tangan ulang;
 * gagal simpan tanpa pesan; nama file disisipkan ke HTML tanpa escape.
 */
$title = 'Surat Pernyataan Peserta';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$st = $koneksi->prepare("SELECT v.id_visit, v.visit_status, p.patient_name FROM pasien_visit v LEFT JOIN ms_patient p ON p.id_patient = v.id_patient
  WHERE v.visit_ID = ? AND v.id_customer = ? LIMIT 1");
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
    .sp .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
    .sp .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #e0f2fe; color: #075985; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .sp .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .sp .kepala small { color: #475569; }
    .sp .surat { border: 1px solid #e2e8f0; border-left: 4px solid #2563eb; border-radius: 12px; padding: 18px 20px; background: #f8fafc; }
    .sp .surat h6 { font-weight: 700; color: #0f172a; margin-bottom: 8px; }
    .sp .surat p { color: #1e293b; line-height: 1.65; margin: 0; }
    .sp .status { border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 18px; margin-top: 16px; display: flex; flex-wrap: wrap; align-items: center; gap: 16px; }
    .sp .status.ok { border-color: #86efac; background: #f0fdf4; }
    .sp .status .tanda { width: 44px; height: 44px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
    .sp .status.ok .tanda { background: #dcfce7; color: #15803d; }
    .sp .status.belum .tanda { background: #fef3c7; color: #92400e; }
    .sp .status .teks { flex: 1 1 220px; color: #0f172a; }
    .sp .status .teks small { color: #334155; display: block; }
    .sp .ttd-img { height: 72px; max-width: 220px; object-fit: contain; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 4px; cursor: zoom-in; }
    #ttdModal .kanvas { position: relative; border: 2px dashed #94a3b8; border-radius: 12px; background: #fff; touch-action: none; }
    #ttdModal canvas { display: block; width: 100%; height: 320px; cursor: crosshair; touch-action: none; }
    #ttdModal .garis { position: absolute; left: 10%; right: 10%; bottom: 56px; border-bottom: 1px solid #cbd5e1; pointer-events: none; }
    #ttdModal .petunjuk { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #64748b; pointer-events: none; font-size: .95rem; }
    @media (max-width: 575.98px) { #ttdModal canvas { height: 240px; } }
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
                <div class="card-body p-4 sp">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-file-check"></i></span>
                    <div class="flex-grow-1">
                      <h5>Surat Pernyataan Peserta</h5>
                      <small>Persetujuan pasien atas pemanfaatan data rekam medis untuk pelayanan BPJS Kesehatan.</small>
                    </div>
                    <?php if ($kunj): ?>
                      <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_pernyataan?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                    <?php endif; ?>
                  </div>

                  <?php if (!$kunj): ?>
                    <div class="alert alert-danger mb-0"><i class="ti ti-alert-triangle me-1"></i>Kunjungan tidak ditemukan untuk faskes ini.</div>
                  <?php else: ?>
                    <div class="surat">
                      <h6><i class="ti ti-shield-check me-1 text-primary"></i>Pernyataan Pemanfaatan Data Medis</h6>
                      <p>
                        Dengan sadar, terkait pemanfaatan jaminan pelayanan kesehatan <strong>BPJS Kesehatan</strong>, saya
                        <strong><?= $h($kunj['patient_name'] ?: 'pasien') ?></strong> menyatakan kesediaan atas data medis
                        (<em>rekam medis</em>) diri saya untuk dipergunakan oleh <strong>Dokter, Rumah Sakit,</strong> dan
                        <strong>BPJS Kesehatan</strong> sesuai dengan kepentingan pelayanan dan administrasi kesehatan yang berlaku.
                      </p>
                    </div>
                    <div class="status" id="ttdSection">
                      <span class="spinner-border spinner-border-sm text-secondary"></span><span class="text-dark">Memeriksa tanda tangan…</span>
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
  <!-- MODAL TANDA TANGAN -->
  <div class="modal fade" id="ttdModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="ti ti-signature me-1"></i>Tanda Tangan Pasien</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="small text-dark mb-2">Pasien / keluarga menandatangani di kotak bawah ini (mouse, jari, atau stylus) sebagai tanda menyetujui pernyataan.</p>
          <div class="kanvas">
            <canvas id="signaturePad"></canvas>
            <div class="garis"></div>
            <div class="petunjuk" id="ttdPetunjuk"><i class="ti ti-writing me-1"></i>Tanda tangan di sini</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-danger me-auto" id="clearSignature"><i class="ti ti-eraser me-1"></i>Hapus goresan</button>
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="saveSignature"><i class="ti ti-device-floppy me-1"></i>Simpan tanda tangan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL LIHAT -->
  <div class="modal fade" id="lihatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tanda Tangan Pasien</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center"><img id="lihatImg" alt="Tanda tangan pasien" class="img-fluid border rounded"></div>
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
      const ID_VISIT = <?= (int) $kunj['id_visit'] ?>;
      const BATAL = <?= json_encode($batal) ?>;
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tgl = s => { const m = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}:\d{2}))?/.exec(s || ''); return m ? `${+m[3]} ${BLN[m[2] - 1]} ${m[1]}${m[4] ? ' ' + m[4] : ''}` : ''; };
      const sec = $id('ttdSection');
      let TTD = null;

      function muat() {
        $.ajax({ url: 'controller/visit/getTTD.php', data: { no: NO }, dataType: 'json', cache: false })
          .done(res => { TTD = res && res.data; render(); })
          .fail(x => {
            sec.className = 'status';
            sec.innerHTML = `<span class="text-danger"><i class="ti ti-alert-triangle me-1"></i>${esc((x.responseJSON && x.responseJSON.message) || 'Gagal memeriksa tanda tangan.')}</span>
              <button type="button" class="btn btn-sm btn-outline-dark ms-auto" id="ttdUlangMuat"><i class="ti ti-refresh me-1"></i>Coba lagi</button>`;
            $id('ttdUlangMuat').onclick = muat;
          });
      }

      function render() {
        if (TTD) {
          const url = TTD.url + '?v=' + encodeURIComponent(TTD.file);
          sec.className = 'status ok';
          sec.innerHTML = `
            <span class="tanda"><i class="ti ti-circle-check"></i></span>
            <div class="teks"><b>Sudah ditandatangani</b><small>Pasien telah menyetujui pernyataan${TTD.waktu ? ' · ' + esc(tgl(TTD.waktu)) : ''}.</small>
              ${TTD.ada ? '' : '<small class="text-danger">File tanda tangan tidak ditemukan di server — silakan tanda tangan ulang.</small>'}</div>
            ${TTD.ada ? `<img class="ttd-img" src="${esc(url)}" alt="Tanda tangan" data-lihat>` : ''}
            <div class="d-flex gap-2">
              ${TTD.ada ? '<button type="button" class="btn btn-sm btn-primary" data-lihat><i class="ti ti-eye me-1"></i>Lihat</button>' : ''}
              ${BATAL ? '' : '<button type="button" class="btn btn-sm btn-outline-dark" data-ttd><i class="ti ti-signature me-1"></i>Tanda tangan ulang</button>'}
            </div>`;
        } else {
          sec.className = 'status belum';
          sec.innerHTML = `
            <span class="tanda"><i class="ti ti-signature"></i></span>
            <div class="teks"><b>Belum ditandatangani</b><small>${BATAL ? 'Kunjungan sudah dibatalkan.' : 'Bacakan pernyataan di atas kepada pasien, lalu minta pasien menandatangani.'}</small></div>
            ${BATAL ? '' : '<button type="button" class="btn btn-primary" data-ttd><i class="ti ti-check me-1"></i>Saya Mengerti dan Setuju</button>'}`;
        }
      }

      sec.addEventListener('click', e => {
        if (e.target.closest('[data-lihat]')) {
          $id('lihatImg').src = TTD.url + '?v=' + encodeURIComponent(TTD.file);
          bootstrap.Modal.getOrCreateInstance($id('lihatModal')).show();
        }
        if (e.target.closest('[data-ttd]')) {
          if (!TTD) return bukaTtd();
          Swal.fire({ icon: 'question', title: 'Tanda tangan ulang?', text: 'Tanda tangan yang tersimpan akan diganti.', showCancelButton: true, confirmButtonText: 'Ya, ulangi', cancelButtonText: 'Batal', cancelButtonColor: '#475569', reverseButtons: true })
            .then(x => x.isConfirmed && bukaTtd());
        }
      });

      /* ===== Kanvas tanda tangan (pointer events: mouse, sentuh, stylus) ===== */
      const canvas = $id('signaturePad'), ctx = canvas.getContext('2d');
      let menggambar = false, ada = false, titik = null;

      function siapkanKanvas() {
        const r = Math.max(window.devicePixelRatio || 1, 1), b = canvas.getBoundingClientRect();
        canvas.width = Math.round(b.width * r);
        canvas.height = Math.round(b.height * r);
        ctx.setTransform(r, 0, 0, r, 0, 0);
        ctx.lineWidth = 2.4;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#0f172a';
        ada = false;
        $id('ttdPetunjuk').classList.remove('d-none');
      }
      const pos = e => { const b = canvas.getBoundingClientRect(); return { x: e.clientX - b.left, y: e.clientY - b.top }; };

      canvas.addEventListener('pointerdown', e => {
        e.preventDefault();
        canvas.setPointerCapture(e.pointerId);
        menggambar = true;
        titik = pos(e);
        ctx.beginPath();
        ctx.arc(titik.x, titik.y, ctx.lineWidth / 2, 0, Math.PI * 2);   // titik tunggal tetap terlihat
        ctx.fillStyle = ctx.strokeStyle;
        ctx.fill();
        if (!ada) { ada = true; $id('ttdPetunjuk').classList.add('d-none'); }
      });
      canvas.addEventListener('pointermove', e => {
        if (!menggambar) return;
        e.preventDefault();
        const p = pos(e);
        ctx.beginPath();
        ctx.moveTo(titik.x, titik.y);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        titik = p;
      });
      ['pointerup', 'pointercancel'].forEach(ev => canvas.addEventListener(ev, () => { menggambar = false; titik = null; }));

      function bukaTtd() { bootstrap.Modal.getOrCreateInstance($id('ttdModal')).show(); }
      $id('ttdModal').addEventListener('shown.bs.modal', siapkanKanvas);   // ukuran baru terbaca setelah modal tampil
      $id('clearSignature').addEventListener('click', siapkanKanvas);

      // PNG dengan latar putih & dipotong ke area goresan agar file kecil dan rapi di cetakan
      function hasilPng() {
        const w = canvas.width, hgt = canvas.height, d = ctx.getImageData(0, 0, w, hgt).data;
        let x0 = w, y0 = hgt, x1 = -1, y1 = -1;
        for (let y = 0; y < hgt; y++) for (let x = 0; x < w; x++) if (d[(y * w + x) * 4 + 3] > 0) { if (x < x0) x0 = x; if (x > x1) x1 = x; if (y < y0) y0 = y; if (y > y1) y1 = y; }
        if (x1 < 0) return null;
        const pad = 16, sx = Math.max(0, x0 - pad), sy = Math.max(0, y0 - pad), sw = Math.min(w, x1 + pad) - sx, sh = Math.min(hgt, y1 + pad) - sy;
        const c = document.createElement('canvas');
        c.width = sw;
        c.height = sh;
        const k = c.getContext('2d');
        k.fillStyle = '#fff';
        k.fillRect(0, 0, sw, sh);
        k.drawImage(canvas, sx, sy, sw, sh, 0, 0, sw, sh);
        return c.toDataURL('image/png');
      }

      $id('saveSignature').addEventListener('click', function() {
        const img = ada ? hasilPng() : null;
        if (!img) return Swal.fire('Tanda tangan kosong', 'Minta pasien menandatangani di kotak terlebih dahulu.', 'warning');
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        fetch('controller/admisi/saveSignature.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id_visit: ID_VISIT, image: img }) })
          .then(r => r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' })))
          .then(res => {
            if (res.status !== 'success') throw new Error(res.message || 'Gagal menyimpan tanda tangan.');
            bootstrap.Modal.getInstance($id('ttdModal')).hide();
            toast('Tanda tangan tersimpan.');
            muat();
          })
          .catch(e => Swal.fire('Gagal', e.message === 'Failed to fetch' ? 'Tidak dapat terhubung ke server.' : e.message, 'error'))
          .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan tanda tangan'; });
      });

      muat();
    })();
  </script>
  <?php endif; ?>
</body>

</html>
