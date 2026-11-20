<?php
$title = 'Tanda Tangan Digital';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .sg-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .sg-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; }
    .sg-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.2; }
    .sg-label { font-size: .75rem; color: #6c757d; }
    .sg-prog { height: 8px; border-radius: 4px; background: #eef1f4; overflow: hidden; }
    .sg-prog > div { height: 100%; background: #13deb9; transition: width .3s; }
    .sg-chips .btn { border-radius: 20px; font-size: .75rem; padding: .2rem .75rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .sg-chips .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .sg-card { border: 1px solid #e5eaef; border-radius: 14px; background: #fff; height: 100%; display: flex; flex-direction: column; transition: box-shadow .15s, border-color .15s; }
    .sg-card:hover { box-shadow: 0 6px 18px rgba(0, 0, 0, .06); border-color: #d6dde6; }
    .sg-card.nonaktif { opacity: .55; }
    .sg-ava { width: 38px; height: 38px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .8rem; flex-shrink: 0; }
    .sg-name { font-weight: 600; color: #2a3547; font-size: .9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sg-prev { height: 92px; margin: 0 14px; border: 1px dashed #d6dde6; border-radius: 10px; display: flex; align-items: center; justify-content: center; position: relative;
      background-image: linear-gradient(45deg, #f8fafc 25%, transparent 25%), linear-gradient(-45deg, #f8fafc 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f8fafc 75%), linear-gradient(-45deg, transparent 75%, #f8fafc 75%);
      background-size: 14px 14px; background-position: 0 0, 0 7px, 7px -7px, -7px 0; }
    .sg-prev img { max-height: 80px; max-width: 92%; object-fit: contain; }
    .sg-prev .kosong { color: #adb5bd; font-size: .78rem; text-align: center; }
    .sg-prev::after { content: ''; position: absolute; left: 14%; right: 14%; bottom: 18px; border-bottom: 1px solid #e5eaef; }
    .sg-badge { font-size: .66rem; font-weight: 600; padding: .2em .6em; border-radius: 20px; white-space: nowrap; }
    .sg-pad-wrap { position: relative; border: 1px solid #d6dde6; border-radius: 12px; background: #fff; overflow: hidden; touch-action: none; }
    #sgPad { display: block; width: 100%; height: 300px; cursor: crosshair; }
    .sg-pad-line { position: absolute; left: 8%; right: 8%; bottom: 60px; border-bottom: 1.5px dashed #cbd5e1; pointer-events: none; }
    .sg-pad-hint { position: absolute; left: 8%; bottom: 36px; font-size: .72rem; color: #94a3b8; pointer-events: none; }
    .sg-tools .btn { font-size: .78rem; }
    .sg-color { width: 26px; height: 26px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 1px #d6dde6; cursor: pointer; }
    .sg-color.on { box-shadow: 0 0 0 2px #5d87ff; }
    .sg-drop { border: 1.5px dashed #b9c8e6; background: #f7faff; border-radius: 12px; padding: 28px 16px; text-align: center; cursor: pointer; }
    .sg-drop.over { border-color: #5d87ff; background: #ecf2ff; }
    #sgUnggahPrev { max-height: 160px; max-width: 100%; }
    .skel { height: 210px; border-radius: 14px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: sk 1.2s infinite; }
    @keyframes sk { to { background-position: -200% 0; } }
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

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="sg-icon bg-primary-subtle text-primary"><i class="fas fa-signature"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Tanda Tangan Digital</h5>
                  <div class="text-muted small">Dipakai otomatis di dokumen cetak: resume medis, CPPT, triase, hasil lab, surat persetujuan, dan lainnya.</div>
                </div>
                <button type="button" class="btn btn-light btn-sm" id="sgRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
              </div>
            </div>
          </div>

          <!-- KPI -->
          <div class="row g-3 mb-3">
            <div class="col-md-4"><div class="sg-kpi"><div class="d-flex align-items-center gap-2"><span class="sg-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></span>
              <div><div class="sg-val" id="kTotal">-</div><div class="sg-label">User aktif</div></div></div></div></div>
            <div class="col-md-4"><div class="sg-kpi"><div class="d-flex align-items-center gap-2"><span class="sg-icon bg-success-subtle text-success"><i class="fas fa-check-circle"></i></span>
              <div class="flex-grow-1"><div class="sg-val" id="kSudah">-</div><div class="sg-label">Sudah punya tanda tangan</div></div></div>
              <div class="sg-prog mt-2"><div id="kBar" style="width:0"></div></div></div></div>
            <div class="col-md-4"><div class="sg-kpi"><div class="d-flex align-items-center gap-2"><span class="sg-icon bg-warning-subtle text-warning"><i class="fas fa-user-md"></i></span>
              <div><div class="sg-val" id="kKlinis">-</div><div class="sg-label">Tenaga klinis belum bertanda tangan</div></div></div>
              <div class="small text-muted mt-1">Dokter, perawat, bidan, apoteker & analis lab</div></div></div>
          </div>

          <!-- FILTER + GRID -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                <div class="sg-chips d-flex gap-1" id="sgStatus">
                  <button type="button" class="btn active" data-s="">Semua</button>
                  <button type="button" class="btn" data-s="belum">Belum</button>
                  <button type="button" class="btn" data-s="sudah">Sudah</button>
                </div>
                <select id="sgRole" class="form-select form-select-sm" style="width:auto"><option value="">Semua role</option></select>
                <div class="position-relative flex-grow-1" style="max-width:300px">
                  <i class="fas fa-search position-absolute text-muted" style="left:11px;top:50%;transform:translateY(-50%);font-size:.78rem"></i>
                  <input type="search" id="sgCari" class="form-control form-control-sm ps-5" placeholder="Cari nama / username…">
                </div>
                <div class="form-check form-switch mb-0 ms-auto"><input class="form-check-input" type="checkbox" id="sgNonaktif"><label class="form-check-label small" for="sgNonaktif">Tampilkan user nonaktif</label></div>
              </div>
              <div class="row g-3" id="sgGrid">
                <?php for ($i = 0; $i < 8; $i++): ?><div class="col-sm-6 col-lg-4 col-xxl-3"><div class="skel"></div></div><?php endfor; ?>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL TANDA TANGAN -->
  <div class="modal fade" id="sgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0"><h5 class="modal-title mb-0">Tanda Tangan</h5><div class="small text-muted text-truncate" id="sgNama"></div></div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <ul class="nav nav-pills nav-fill mb-3 small" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#sgTabGambar" type="button" id="sgTabGambarBtn"><i class="fas fa-pen-nib me-1"></i>Gambar langsung</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#sgTabUnggah" type="button" id="sgTabUnggahBtn"><i class="fas fa-upload me-1"></i>Unggah gambar / scan</button></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane fade show active" id="sgTabGambar">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-2 sg-tools">
                <span class="small text-muted">Warna</span>
                <span class="sg-color on" data-c="#111827" style="background:#111827" title="Hitam"></span>
                <span class="sg-color" data-c="#1d4ed8" style="background:#1d4ed8" title="Biru"></span>
                <span class="small text-muted ms-2">Tebal</span>
                <input type="range" class="form-range" id="sgTebal" min="1.5" max="6" step="0.5" value="3" style="width:110px">
                <div class="ms-auto d-flex gap-1">
                  <button type="button" class="btn btn-light btn-sm" id="sgUndo" disabled><i class="fas fa-undo me-1"></i>Urungkan</button>
                  <button type="button" class="btn btn-light btn-sm" id="sgClear" disabled><i class="fas fa-eraser me-1"></i>Bersihkan</button>
                </div>
              </div>
              <div class="sg-pad-wrap">
                <canvas id="sgPad"></canvas>
                <div class="sg-pad-line"></div>
                <div class="sg-pad-hint">Tanda tangan di atas garis</div>
              </div>
              <div class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Bisa memakai mouse, layar sentuh, atau stylus. Tepi kosong otomatis dirapikan saat disimpan.</div>
            </div>
            <div class="tab-pane fade" id="sgTabUnggah">
              <input type="file" id="sgFile" accept="image/png,image/jpeg" hidden>
              <div class="sg-drop" id="sgDrop" role="button" tabindex="0">
                <div class="text-primary mb-2"><i class="fas fa-file-image fa-2x"></i></div>
                <div class="fw-semibold">Klik atau seret gambar tanda tangan ke sini</div>
                <div class="small text-muted mt-1">PNG / JPG, maks 2 MB. Latar putih hasil scan otomatis dibuat transparan.</div>
              </div>
              <div class="text-center mt-3 d-none" id="sgUnggahWrap"><img id="sgUnggahPrev" alt="Pratinjau"><div class="small text-muted mt-1" id="sgUnggahInfo"></div></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-danger me-auto d-none" id="sgHapus"><i class="fas fa-trash-alt me-1"></i>Hapus tanda tangan</button>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="sgSimpan" disabled><i class="fas fa-save me-1"></i>Simpan</button>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const API_USER = 'controller/master/userController.php';
      const API_TTD = 'controller/master/saveSignatureFaskes.php';
      const KLINIS = ['dokter', 'perawat', 'bidan', 'apoteker', 'analislab'];
      const ROLE = { admin: 'Admin', dokter: 'Dokter', perawat: 'Perawat', bidan: 'Bidan', apoteker: 'Apoteker', kasir: 'Kasir', receptionis: 'Pendaftaran', analislab: 'Analis Lab' };
      const WARNA = ['#5d87ff', '#13deb9', '#ffae1f', '#fa896b', '#a855f7', '#539bff', '#14b8a6', '#f472b6'];
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const inisial = s => String(s || '?').replace(/^\s*drg?\.?\s*/i, '').replace(/[^\p{L}\s]/gu, ' ').trim().split(/\s+/).slice(0, 2).map(w => w[0] || '').join('').toUpperCase() || '?';
      const waktu = s => { if (!s) return ''; const d = new Date(String(s).replace(' ', 'T')); return isNaN(d) ? '' : d.getDate() + ' ' + BLN[d.getMonth()] + ' ' + d.getFullYear(); };
      const aktif = u => String(u.status) !== '0';
      let D = [], fStatus = '', ver = Date.now();

      /* ---------- data ---------- */
      async function muat() {
        $id('sgRefresh').querySelector('i').classList.add('fa-spin');
        try {
          const r = await fetch(API_USER, { credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json();
          if (j.status !== 'success') throw new Error(j.message || 'Gagal memuat user.');
          D = j.data;
          const roles = [...new Set(D.map(u => u.roles).filter(Boolean))].sort();
          const lama = $id('sgRole').value;
          $id('sgRole').innerHTML = '<option value="">Semua role</option>' + roles.map(r => `<option value="${esc(r)}">${esc(ROLE[r] || r)}</option>`).join('');
          $id('sgRole').value = roles.includes(lama) ? lama : '';
          tampil();
        } catch (e) {
          $id('sgGrid').innerHTML = `<div class="col-12 text-center text-danger py-5"><i class="fas fa-exclamation-circle fa-2x d-block mb-2"></i>${esc(e.message || 'Tidak dapat terhubung ke server.')}</div>`;
        } finally {
          $id('sgRefresh').querySelector('i').classList.remove('fa-spin');
        }
      }

      function tampil() {
        const ak = D.filter(aktif);
        const sudah = ak.filter(u => u.signature_user).length;
        const klinisBelum = ak.filter(u => KLINIS.includes(u.roles) && !u.signature_user).length;
        $id('kTotal').textContent = fmt(ak.length);
        $id('kSudah').textContent = fmt(sudah) + (ak.length ? ' (' + Math.round(sudah / ak.length * 100) + '%)' : '');
        $id('kBar').style.width = (ak.length ? sudah / ak.length * 100 : 0) + '%';
        $id('kKlinis').textContent = fmt(klinisBelum);
        $id('kKlinis').className = 'sg-val ' + (klinisBelum ? 'text-warning' : 'text-success');

        const role = $id('sgRole').value, cari = $id('sgCari').value.trim().toLowerCase(), non = $id('sgNonaktif').checked;
        const list = D.filter(u => (non || aktif(u)) && (!role || u.roles === role) && (!fStatus || (fStatus === 'sudah') === !!u.signature_user) &&
            (!cari || (u.fullname + ' ' + u.username).toLowerCase().includes(cari)))
          // tenaga klinis tanpa tanda tangan ditaruh paling atas
          .sort((a, b) => (+!!a.signature_user - +!!b.signature_user) || (+!KLINIS.includes(a.roles) - +!KLINIS.includes(b.roles)) || String(a.fullname).localeCompare(String(b.fullname)));
        $id('sgGrid').innerHTML = !list.length ? '<div class="col-12 text-center text-muted py-5">Tidak ada user yang cocok.</div>' : list.map(u => {
          const w = WARNA[u.id_user % WARNA.length];
          const ada = !!u.signature_user;
          return `<div class="col-sm-6 col-lg-4 col-xxl-3"><div class="sg-card ${aktif(u) ? '' : 'nonaktif'}">
            <div class="d-flex align-items-center gap-2 p-3 pb-2">
              <span class="sg-ava" style="background:${w}22;color:${w}">${esc(inisial(u.fullname))}</span>
              <div class="min-w-0 flex-grow-1"><div class="sg-name" title="${esc(u.fullname)}">${esc(u.fullname || '-')}</div>
                <div class="small text-muted text-truncate">${esc(ROLE[u.roles] || u.roles || '-')} · ${esc(u.username || '')}</div></div>
              ${ada ? '<span class="sg-badge bg-success-subtle text-success">Ada</span>' : (KLINIS.includes(u.roles) && aktif(u) ? '<span class="sg-badge bg-warning-subtle text-warning">Perlu</span>' : '<span class="sg-badge bg-light text-muted">Belum</span>')}
            </div>
            <div class="sg-prev">${ada ? `<img src="uploads/ttd_faskes/${encodeURIComponent(u.signature_user)}?v=${ver}" alt="Tanda tangan ${esc(u.fullname)}" loading="lazy">` : '<div class="kosong"><i class="fas fa-signature d-block mb-1"></i>Belum ada tanda tangan</div>'}</div>
            <div class="d-flex align-items-center gap-2 p-3 pt-2 mt-auto">
              <span class="small text-muted flex-grow-1">${ada && u.signature_timestamp ? 'Diperbarui ' + esc(waktu(u.signature_timestamp)) : '&nbsp;'}</span>
              <button type="button" class="btn btn-sm ${ada ? 'btn-light' : 'btn-primary'} sg-buka" data-id="${u.id_user}"><i class="fas fa-${ada ? 'pen' : 'signature'} me-1"></i>${ada ? 'Ganti' : 'Buat'}</button>
            </div></div></div>`;
        }).join('');
      }
      document.querySelectorAll('#sgStatus .btn').forEach(b => b.addEventListener('click', () => {
        fStatus = b.dataset.s;
        document.querySelectorAll('#sgStatus .btn').forEach(x => x.classList.toggle('active', x === b));
        tampil();
      }));
      let tCari;
      $id('sgCari').addEventListener('input', () => { clearTimeout(tCari); tCari = setTimeout(tampil, 150); });
      $id('sgRole').addEventListener('change', tampil);
      $id('sgNonaktif').addEventListener('change', tampil);
      $id('sgRefresh').addEventListener('click', muat);

      /* ---------- kanvas: Pointer Events (mouse, sentuh, stylus), tajam di layar retina, garis halus ---------- */
      const pad = $id('sgPad');
      const ctx = pad.getContext('2d');
      let goresan = [], aktifG = null, warna = '#111827', tebal = 3;
      function ukuran() {
        const r = pad.getBoundingClientRect();
        if (!r.width) return;
        const dpr = Math.min(window.devicePixelRatio || 1, 2.5);
        pad.width = Math.round(r.width * dpr);
        pad.height = Math.round(r.height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        gambarUlang();
      }
      function gambarG(g) {
        const p = g.p;
        ctx.strokeStyle = g.c;
        ctx.fillStyle = g.c;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        if (p.length === 1) { ctx.beginPath(); ctx.arc(p[0][0], p[0][1], g.w / 2, 0, Math.PI * 2); ctx.fill(); return; }
        for (let i = 1; i < p.length; i++) {
          // tebal mengikuti tekanan stylus bila ada
          ctx.lineWidth = g.w * (0.6 + 0.8 * (p[i][2] ?? 0.5));
          ctx.beginPath();
          const a = p[i - 1], b = p[i];
          const m0 = i > 1 ? [(p[i - 2][0] + a[0]) / 2, (p[i - 2][1] + a[1]) / 2] : a;
          const m1 = [(a[0] + b[0]) / 2, (a[1] + b[1]) / 2];
          ctx.moveTo(m0[0], m0[1]);
          ctx.quadraticCurveTo(a[0], a[1], m1[0], m1[1]);
          ctx.stroke();
        }
      }
      function gambarUlang() {
        ctx.clearRect(0, 0, pad.width, pad.height);
        goresan.forEach(gambarG);
        $id('sgUndo').disabled = $id('sgClear').disabled = !goresan.length;
        perbaruiSimpan();
      }
      const titik = e => { const r = pad.getBoundingClientRect(); return [e.clientX - r.left, e.clientY - r.top, e.pointerType === 'pen' && e.pressure ? e.pressure : 0.5]; };
      pad.addEventListener('pointerdown', e => {
        e.preventDefault();
        pad.setPointerCapture(e.pointerId);
        aktifG = { c: warna, w: tebal, p: [titik(e)] };
        goresan.push(aktifG);
        gambarUlang();
      });
      pad.addEventListener('pointermove', e => {
        if (!aktifG) return;
        const ev = e.getCoalescedEvents ? e.getCoalescedEvents() : [e];
        ev.forEach(x => aktifG.p.push(titik(x)));
        gambarUlang();
      });
      const selesai = () => { aktifG = null; };
      pad.addEventListener('pointerup', selesai);
      pad.addEventListener('pointercancel', selesai);
      $id('sgUndo').addEventListener('click', () => { goresan.pop(); gambarUlang(); });
      $id('sgClear').addEventListener('click', () => { goresan = []; gambarUlang(); });
      document.querySelectorAll('.sg-color').forEach(c => c.addEventListener('click', () => {
        warna = c.dataset.c;
        document.querySelectorAll('.sg-color').forEach(x => x.classList.toggle('on', x === c));
      }));
      $id('sgTebal').addEventListener('input', function() { tebal = +this.value; });
      window.addEventListener('resize', () => { if ($id('sgModal').classList.contains('show')) ukuran(); });

      /* ---------- unggah ---------- */
      let fileUnggah = null;
      const tabAktif = () => $id('sgTabUnggah').classList.contains('active') ? 'unggah' : 'gambar';
      function perbaruiSimpan() {
        $id('sgSimpan').disabled = tabAktif() === 'gambar' ? !goresan.length : !fileUnggah;
      }
      function pilihFile(f) {
        if (!f) return;
        if (!/^image\/(png|jpeg)$/.test(f.type)) return Swal.fire('Format tidak didukung', 'Pilih gambar PNG atau JPG.', 'warning');
        if (f.size > 2 * 1024 * 1024) return Swal.fire('File terlalu besar', 'Maksimal 2 MB.', 'warning');
        fileUnggah = f;
        $id('sgUnggahPrev').src = URL.createObjectURL(f);
        $id('sgUnggahInfo').textContent = f.name + ' · ' + Math.round(f.size / 1024) + ' KB';
        $id('sgUnggahWrap').classList.remove('d-none');
        perbaruiSimpan();
      }
      const drop = $id('sgDrop');
      drop.addEventListener('click', () => $id('sgFile').click());
      drop.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $id('sgFile').click(); } });
      $id('sgFile').addEventListener('change', function() { pilihFile(this.files[0]); this.value = ''; });
      ['dragenter', 'dragover'].forEach(t => drop.addEventListener(t, e => { e.preventDefault(); drop.classList.add('over'); }));
      ['dragleave', 'drop'].forEach(t => drop.addEventListener(t, () => drop.classList.remove('over')));
      drop.addEventListener('drop', e => { e.preventDefault(); pilihFile(e.dataTransfer.files[0]); });
      document.querySelectorAll('#sgModal [data-bs-toggle=pill]').forEach(b => b.addEventListener('shown.bs.tab', () => { perbaruiSimpan(); if (tabAktif() === 'gambar') ukuran(); }));

      /* ---------- modal ---------- */
      const modal = bootstrap.Modal.getOrCreateInstance($id('sgModal'));
      let userAktif = null;
      $id('sgGrid').addEventListener('click', e => {
        const b = e.target.closest('.sg-buka');
        if (!b) return;
        userAktif = D.find(u => +u.id_user === +b.dataset.id);
        $id('sgNama').textContent = (userAktif.fullname || '-') + ' · ' + (ROLE[userAktif.roles] || userAktif.roles || '');
        $id('sgHapus').classList.toggle('d-none', !userAktif.signature_user);
        goresan = [];
        fileUnggah = null;
        $id('sgUnggahWrap').classList.add('d-none');
        bootstrap.Tab.getOrCreateInstance($id('sgTabGambarBtn')).show();
        modal.show();
      });
      // ukuran kanvas baru bisa dihitung setelah modal tampil
      $id('sgModal').addEventListener('shown.bs.modal', ukuran);

      async function kirim(body, isForm) {
        const r = await fetch(API_TTD, isForm ? { method: 'POST', body, credentials: 'same-origin' } :
          { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body), credentials: 'same-origin' });
        const j = await r.json().catch(() => null);
        if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Permintaan gagal (HTTP ' + r.status + ').');
        return j;
      }
      $id('sgSimpan').addEventListener('click', async () => {
        if (!userAktif) return;
        const btn = $id('sgSimpan'), label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          let j;
          if (tabAktif() === 'gambar') {
            j = await kirim({ aksi: 'simpan', id_user: userAktif.id_user, image: pad.toDataURL('image/png') });
          } else {
            const fd = new FormData();
            fd.append('aksi', 'unggah');
            fd.append('id_user', userAktif.id_user);
            fd.append('file', fileUnggah);
            j = await kirim(fd, true);
          }
          userAktif.signature_user = j.file;
          userAktif.signature_timestamp = j.waktu;
          ver = Date.now();
          modal.hide();
          tampil();
          Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Tanda tangan disimpan', showConfirmButton: false, timer: 1800 });
        } catch (e) {
          Swal.fire('Gagal menyimpan', e.message, 'error');
        } finally {
          btn.innerHTML = label;
          perbaruiSimpan();
        }
      });
      $id('sgHapus').addEventListener('click', async () => {
        if (!userAktif) return;
        const r = await Swal.fire({ icon: 'warning', title: 'Hapus tanda tangan?', text: (userAktif.fullname || '') + ' tidak akan punya tanda tangan di dokumen cetak sampai dibuat lagi.',
          showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545', reverseButtons: true });
        if (!r.isConfirmed) return;
        try {
          await kirim({ aksi: 'hapus', id_user: userAktif.id_user });
          userAktif.signature_user = null;
          modal.hide();
          tampil();
          Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Tanda tangan dihapus', showConfirmButton: false, timer: 1800 });
        } catch (e) {
          Swal.fire('Gagal menghapus', e.message, 'error');
        }
      });

      muat();
    })();
  </script>
</body>

</html>
