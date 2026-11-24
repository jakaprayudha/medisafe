<?php
/**
 * Foto / dokumentasi perawatan per kunjungan (menu "Foto Perawatan"). API: controller/ranap/dokumentasiPerawatan.php.
 * Perbaikan: query pasien dirangkai dari URL (SQL injection) & tidak dipakai; $_GET no/rm/rme tanpa default; no RM &
 * id kunjungan dicetak mentah ke input (XSS); path foto disisipkan tanpa escape; tombol di dalam <a>; modal di luar
 * </body>; hanya 1 foto per unggahan, foto kamera HP (5–10 MB) diunggah apa adanya; tidak ada pratinjau besar.
 */
$title = 'Foto Perawatan';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .fp .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .fp .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #dcfce7; color: #14532d; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .fp .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .fp .kepala small { color: #475569; }
    .fp .hari { display: flex; align-items: center; gap: 8px; font-weight: 700; color: #0f172a; margin: 18px 0 10px; font-size: .9rem; }
    .fp .hari:first-child { margin-top: 0; }
    .fp .hari .n { font-size: .72rem; font-weight: 700; background: #f1f5f9; color: #334155; border-radius: 999px; padding: 1px 8px; }
    .fp .grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
    @media (max-width: 991.98px) { .fp .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .fp .foto { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; display: flex; flex-direction: column; }
    .fp .foto .aksi { display: flex; align-items: center; gap: 4px; padding: 6px 8px; border-bottom: 1px solid #eef2f6; }
    .fp .foto .tgl { font-size: .78rem; font-weight: 700; color: #0f172a; white-space: nowrap; }
    .fp .foto .aksi .btn { padding: 2px 8px; font-weight: 600; }
    .fp .foto img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; cursor: zoom-in; background: #f1f5f9; display: block; }
    .fp .foto .rusak { aspect-ratio: 4 / 3; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: .8rem; background: #f8fafc; }
    .fp .foto .ket { font-size: .72rem; color: #475569; padding: 6px 8px; }
    .fp .kosong { text-align: center; padding: 40px 10px; color: #475569; border: 1px dashed #cbd5e1; border-radius: 12px; }
    .fp .kosong i { font-size: 2.2rem; color: #94a3b8; display: block; margin-bottom: 6px; }
    #upModal .drop { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 22px; text-align: center; color: #475569; cursor: pointer; background: #f8fafc; }
    #upModal .drop.over { border-color: #2563eb; background: #eff6ff; }
    #upModal .drop i { font-size: 2rem; color: #64748b; display: block; }
    #upModal .antri { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 8px; margin-top: 12px; }
    #upModal .antri .it { position: relative; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
    #upModal .antri img { width: 100%; aspect-ratio: 1; object-fit: cover; display: block; }
    #upModal .antri .x { position: absolute; top: 4px; right: 4px; width: 24px; height: 24px; border-radius: 50%; border: 0; background: rgba(15, 23, 42, .75); color: #fff; line-height: 1; }
    #upModal .antri .st { position: absolute; left: 0; right: 0; bottom: 0; font-size: .68rem; font-weight: 700; padding: 2px 6px; background: rgba(255, 255, 255, .92); color: #334155; }
    #upModal .antri .st.ok { color: #14532d; }
    #upModal .antri .st.err { color: #b91c1c; }
    #lihatModal .modal-content { background: #0f172a; color: #fff; }
    #lihatModal img { max-width: 100%; max-height: 78vh; object-fit: contain; display: block; margin: 0 auto; }
    #lihatModal .nav-btn { position: absolute; top: 50%; transform: translateY(-50%); border: 0; background: rgba(255, 255, 255, .15); color: #fff; width: 44px; height: 44px; border-radius: 50%; font-size: 1.3rem; }
    #lihatModal .nav-btn:hover { background: rgba(255, 255, 255, .3); }
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
                      <h5>Foto Perawatan <span class="small text-muted fw-normal" id="fpJumlah"></span></h5>
                      <small>Dokumentasi foto selama perawatan (luka, tindakan, kondisi pasien), terbaru di depan.</small>
                    </div>
                    <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_dokumen?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                    <button type="button" class="btn btn-primary" id="btnTambah" disabled><i class="ti ti-camera-plus me-1"></i>Tambah foto</button>
                  </div>
                  <div class="alert alert-danger d-none" id="fpBatal"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah dibatalkan — foto baru tidak bisa ditambahkan.</div>
                  <div id="fpIsi"><div class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</div></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL UNGGAH -->
  <div class="modal fade" id="upModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah foto perawatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" id="upTutup"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
              <label for="upTanggal" class="form-label small fw-semibold">Tanggal foto <span class="text-danger">*</span></label>
              <input type="date" id="upTanggal" class="form-control">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-8 small text-muted">Foto besar otomatis diperkecil (maks. 1920 px) sebelum diunggah agar cepat. JPG / PNG / WEBP.</div>
          </div>
          <div class="drop" id="upDrop" tabindex="0" role="button" aria-label="Pilih foto">
            <i class="ti ti-photo-up"></i>
            <div class="fw-semibold mt-1">Klik untuk memilih foto, atau seret &amp; lepas di sini</div>
            <div class="small">Bisa beberapa foto sekaligus · di HP bisa langsung dari kamera</div>
          </div>
          <input type="file" id="upFile" accept="image/jpeg,image/png,image/webp" multiple class="d-none">
          <div class="antri" id="upAntri"></div>
        </div>
        <div class="modal-footer">
          <span class="small text-muted me-auto" id="upInfo"></span>
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" id="upBatal">Batal</button>
          <button type="button" class="btn btn-primary px-4" id="upSimpan" disabled><i class="ti ti-upload me-1"></i>Unggah</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL UBAH TANGGAL -->
  <div class="modal fade" id="tglModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <form class="modal-content" id="tglForm" novalidate>
        <div class="modal-header"><h6 class="modal-title fw-bold">Ubah tanggal foto</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" id="tglId">
          <img id="tglFoto" class="w-100 rounded mb-2" style="aspect-ratio:4/3;object-fit:cover" alt="">
          <input type="date" id="tglNilai" class="form-control">
          <div class="invalid-feedback"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="tglSimpan">Simpan</button></div>
      </form>
    </div>
  </div>

  <!-- MODAL LIHAT -->
  <div class="modal fade" id="lihatModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div class="small" id="lihatKet"></div>
          <a class="btn btn-sm btn-light ms-auto me-2" id="lihatAsli" target="_blank" rel="noopener"><i class="ti ti-external-link me-1"></i>Buka asli</a>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body position-relative">
          <img id="lihatImg" alt="Foto perawatan">
          <button type="button" class="nav-btn" id="lihatPrev" style="left:12px" aria-label="Sebelumnya"><i class="ti ti-chevron-left"></i></button>
          <button type="button" class="nav-btn" id="lihatNext" style="right:12px" aria-label="Berikutnya"><i class="ti ti-chevron-right"></i></button>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      'use strict';
      const API = 'controller/ranap/dokumentasiPerawatan?no=' + encodeURIComponent(<?= json_encode($no) ?>);
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglPanjang = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); if (!m) return 'Tanpa tanggal'; const d = new Date(Date.UTC(+m[1], m[2] - 1, +m[3])); return HARI[d.getUTCDay()] + ', ' + (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1]; };
      const jamUnggah = s => { const m = String(s || '').match(/(\d{2}:\d{2})/); return m ? m[1] : ''; };
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
      const urlFoto = p => String(p || '').split('/').map(encodeURIComponent).join('/');

      let DATA = [], K = { batal: true };

      async function api(url, opt) {
        const r = await fetch(url, opt);
        const j = await r.json().catch(() => ({ status: 'error', message: r.status === 413 ? 'Ukuran foto melebihi batas server.' : 'Respon server tidak valid.' }));
        if (!r.ok || j.status !== 'success') { const e = new Error(j.message || 'Terjadi kesalahan.'); e.field = j.field; throw e; }
        return j;
      }

      /* ================= GALERI ================= */
      function muat() {
        return api(API).then(res => {
          DATA = res.data || [];
          K = res.kunjungan || {};
          $id('btnTambah').disabled = !!K.batal;
          $id('fpBatal').classList.toggle('d-none', !K.batal);
          render();
        }).catch(e => { $id('fpIsi').innerHTML = `<div class="kosong text-danger"><i class="ti ti-alert-triangle"></i>${esc(e.message)}</div>`; });
      }
      function render() {
        $id('fpJumlah').textContent = DATA.length ? '· ' + DATA.length + ' foto' : '';
        if (!DATA.length) {
          $id('fpIsi').innerHTML = '<div class="kosong"><i class="ti ti-photo"></i>Belum ada foto perawatan untuk kunjungan ini.</div>';
          return;
        }
        // satu grid 4 kolom (terbaru dulu); tanggal foto tampil di tiap kartu
        $id('fpIsi').innerHTML = `<div class="grid">${DATA.map((d, i) => {
          const tgl = String(d.rilis || '').slice(0, 10);
          return `
            <div class="foto">
              <div class="aksi">
                <button type="button" class="btn btn-sm btn-warning" data-tgl="${i}" title="Ubah tanggal"><i class="ti ti-calendar-event"></i></button>
                <button type="button" class="btn btn-sm btn-danger" data-hapus="${i}" title="Hapus"><i class="ti ti-trash"></i></button>
                <span class="tgl ms-auto"><i class="ti ti-calendar me-1"></i>${esc(tglPanjang(tgl).replace(/^\w+, /, ''))}</span>
              </div>
              <img src="${esc(urlFoto(d.foto_path))}" alt="Foto perawatan ${esc(tglPanjang(tgl))}" loading="lazy" data-lihat="${i}">
              <div class="ket">${esc(tglPanjang(tgl).split(',')[0])} · diunggah ${esc(jamUnggah(d.tgl_upload) || '-')}${String(d.tgl_upload || '').slice(0, 10) !== tgl ? ', ' + esc(tglPanjang(d.tgl_upload).replace(/^\w+, /, '')) : ''}</div>
            </div>`;
        }).join('')}</div>`;
        // foto yang file-nya hilang
        $id('fpIsi').querySelectorAll('img[data-lihat]').forEach(img => img.addEventListener('error', () => {
          const div = document.createElement('div');
          div.className = 'rusak';
          div.innerHTML = '<span><i class="ti ti-photo-off me-1"></i>File tidak ditemukan</span>';
          img.replaceWith(div);
        }, { once: true }));
      }

      /* ================= LIHAT (lightbox) ================= */
      const lihat = bootstrap.Modal.getOrCreateInstance($id('lihatModal'));
      let posisi = 0;
      function tampil(i) {
        posisi = (i + DATA.length) % DATA.length;
        const d = DATA[posisi];
        $id('lihatImg').src = urlFoto(d.foto_path);
        $id('lihatAsli').href = urlFoto(d.foto_path);
        $id('lihatKet').textContent = `${tglPanjang(d.rilis)} · foto ${posisi + 1} dari ${DATA.length}`;
        $id('lihatPrev').classList.toggle('d-none', DATA.length < 2);
        $id('lihatNext').classList.toggle('d-none', DATA.length < 2);
      }
      $id('lihatPrev').addEventListener('click', () => tampil(posisi - 1));
      $id('lihatNext').addEventListener('click', () => tampil(posisi + 1));
      $id('lihatModal').addEventListener('keydown', e => { if (e.key === 'ArrowLeft') tampil(posisi - 1); if (e.key === 'ArrowRight') tampil(posisi + 1); });

      $id('fpIsi').addEventListener('click', e => {
        const l = e.target.closest('[data-lihat]'), t = e.target.closest('[data-tgl]'), hps = e.target.closest('[data-hapus]');
        if (l) { tampil(+l.dataset.lihat); lihat.show(); }
        if (t) {
          const d = DATA[+t.dataset.tgl];
          $id('tglId').value = d.id_dokumen;
          $id('tglNilai').value = String(d.rilis || '').slice(0, 10);
          $id('tglNilai').max = K.hari_ini || '';
          $id('tglNilai').min = String(K.tanggal || '').slice(0, 10);
          $id('tglNilai').classList.remove('is-invalid');
          $id('tglFoto').src = urlFoto(d.foto_path);
          bootstrap.Modal.getOrCreateInstance($id('tglModal')).show();
        }
        if (hps) {
          const d = DATA[+hps.dataset.hapus];
          Swal.fire({
            title: 'Hapus foto ini?', text: 'Foto dan file-nya dihapus permanen.', imageUrl: urlFoto(d.foto_path), imageHeight: 160, imageAlt: 'Foto',
            showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true
          }).then(x => {
            if (!x.isConfirmed) return;
            api(API + '&id=' + encodeURIComponent(d.id_dokumen), { method: 'DELETE' }).then(res => { toast(res.message); muat(); }).catch(err => Swal.fire('Gagal', err.message, 'error'));
          });
        }
      });

      $id('tglForm').addEventListener('submit', e => {
        e.preventDefault();
        const inp = $id('tglNilai'), fb = inp.nextElementSibling, btn = $id('tglSimpan');
        inp.classList.remove('is-invalid');
        if (!inp.value) { inp.classList.add('is-invalid'); fb.textContent = 'Tanggal wajib diisi.'; return; }
        const fd = new FormData();
        fd.append('tgl_upload', inp.value);
        btn.disabled = true;
        api(API + '&id=' + encodeURIComponent($id('tglId').value), { method: 'POST', body: fd })
          .then(res => { bootstrap.Modal.getInstance($id('tglModal')).hide(); toast(res.message); muat(); })
          .catch(err => { inp.classList.add('is-invalid'); fb.textContent = err.message; })
          .finally(() => { btn.disabled = false; });
      });

      /* ================= UNGGAH ================= */
      const up = bootstrap.Modal.getOrCreateInstance($id('upModal'));
      let antri = [], mengunggah = false;
      const MAKS_SISI = 1920, MAKS_BYTE = 10 * 1024 * 1024;

      // perkecil foto besar di browser (JPEG 0,85) — PNG kecil dibiarkan
      function perkecil(file) {
        return new Promise(resolve => {
          if (!/^image\/(jpeg|png|webp)$/.test(file.type)) return resolve(file);
          const img = new Image(), url = URL.createObjectURL(file);
          img.onload = () => {
            URL.revokeObjectURL(url);
            const skala = Math.min(1, MAKS_SISI / Math.max(img.naturalWidth, img.naturalHeight));
            if (skala === 1 && file.size <= 1.5 * 1024 * 1024) return resolve(file);
            const c = document.createElement('canvas');
            c.width = Math.round(img.naturalWidth * skala);
            c.height = Math.round(img.naturalHeight * skala);
            const ctx = c.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, c.width, c.height);
            ctx.drawImage(img, 0, 0, c.width, c.height);
            c.toBlob(b => resolve(b && b.size < file.size ? new File([b], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : file), 'image/jpeg', 0.85);
          };
          img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
          img.src = url;
        });
      }
      function tambahFile(files) {
        [...files].forEach(f => {
          const it = { file: f, url: URL.createObjectURL(f), status: '', ok: false, err: '' };
          if (!/^image\/(jpeg|png|webp)$/.test(f.type)) { it.err = 'Bukan JPG/PNG/WEBP'; }
          antri.push(it);
        });
        renderAntri();
      }
      function renderAntri() {
        $id('upAntri').innerHTML = antri.map((it, i) => `
          <div class="it">
            ${/^image\//.test(it.file.type) ? `<img src="${it.url}" alt="">` : `<div style="aspect-ratio:1" class="d-flex align-items-center justify-content-center bg-light text-muted small px-2 text-center"><span><i class="ti ti-file-alert d-block fs-4"></i>${esc(it.file.name)}</span></div>`}
            ${mengunggah || it.ok ? '' : `<button type="button" class="x" data-buang="${i}" aria-label="Buang">&times;</button>`}
            ${it.err ? `<div class="st err">${esc(it.err)}</div>` : it.ok ? '<div class="st ok"><i class="ti ti-check"></i> Terunggah</div>' : it.status ? `<div class="st">${esc(it.status)}</div>` : ''}
          </div>`).join('');
        const siap = antri.filter(it => !it.ok && !it.err).length;
        $id('upSimpan').disabled = mengunggah || !siap;
        $id('upSimpan').innerHTML = mengunggah ? '<span class="spinner-border spinner-border-sm me-1"></span>Mengunggah…' : `<i class="ti ti-upload me-1"></i>Unggah${siap ? ' ' + siap + ' foto' : ''}`;
        $id('upInfo').textContent = antri.length ? antri.length + ' foto dipilih' : '';
      }
      $id('upAntri').addEventListener('click', e => {
        const b = e.target.closest('[data-buang]');
        if (!b) return;
        URL.revokeObjectURL(antri[+b.dataset.buang].url);
        antri.splice(+b.dataset.buang, 1);
        renderAntri();
      });
      const drop = $id('upDrop');
      drop.addEventListener('click', () => $id('upFile').click());
      drop.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); $id('upFile').click(); } });
      $id('upFile').addEventListener('change', function() { tambahFile(this.files); this.value = ''; });
      ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('over'); }));
      ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('over'); }));
      drop.addEventListener('drop', e => tambahFile(e.dataTransfer.files));

      $id('btnTambah').addEventListener('click', () => {
        antri.forEach(it => URL.revokeObjectURL(it.url));
        antri = [];
        renderAntri();
        $id('upTanggal').value = K.hari_ini || '';
        $id('upTanggal').max = K.hari_ini || '';
        $id('upTanggal').min = String(K.tanggal || '').slice(0, 10);
        $id('upTanggal').classList.remove('is-invalid');
        up.show();
      });

      $id('upSimpan').addEventListener('click', async () => {
        const tgl = $id('upTanggal');
        tgl.classList.remove('is-invalid');
        if (!tgl.value) { tgl.classList.add('is-invalid'); tgl.nextElementSibling.textContent = 'Tanggal wajib diisi.'; return; }
        mengunggah = true;
        $id('upBatal').disabled = $id('upTutup').disabled = true;
        let berhasil = 0, gagalTgl = false;
        for (const it of antri) {
          if (it.ok || it.err) continue;
          it.status = 'Memproses…';
          renderAntri();
          try {
            const f = await perkecil(it.file);
            if (f.size > MAKS_BYTE) throw new Error('Lebih dari 10 MB');
            it.status = 'Mengunggah…';
            renderAntri();
            const fd = new FormData();
            fd.append('tgl_upload', tgl.value);
            fd.append('foto_path', f, f.name);
            await api(API, { method: 'POST', body: fd });
            it.ok = true;
            berhasil++;
          } catch (err) {
            it.err = err.message;
            if (err.field === 'tgl_upload') { gagalTgl = true; tgl.classList.add('is-invalid'); tgl.nextElementSibling.textContent = err.message; it.err = ''; it.status = ''; break; }
          }
          renderAntri();
        }
        mengunggah = false;
        $id('upBatal').disabled = $id('upTutup').disabled = false;
        renderAntri();
        if (berhasil) muat();
        const sisaGagal = antri.filter(it => it.err).length;
        if (!gagalTgl && !sisaGagal && berhasil) { up.hide(); toast(berhasil + ' foto tersimpan.'); }
        else if (berhasil) toast(`${berhasil} foto tersimpan, ${sisaGagal} gagal.`, 'warning');
      });

      muat();
    })();
  </script>
</body>

</html>
