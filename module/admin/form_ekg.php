<?php
/**
 * Pemeriksaan EKG per kunjungan — gambar EKG 1 (wajib) & EKG 2 (opsional), dokter, interpretasi.
 * API: controller/ranap/ekgController.php. Cetak: print/formulir_ekg.
 * Perbaikan: query pasien dirangkai dari URL (SQL injection) & tidak dipakai; $_GET no/rm/rme tanpa default; no RM &
 * id kunjungan dicetak mentah ke input (XSS); path gambar/interpretasi/dokter tanpa escape; EKG 2 lewat pilihan
 * "Pilih EKG" yang membuat isian lain diabaikan; tidak ada ubah; tombol di dalam <a>; modal di luar </body>;
 * kolom Aksi di kanan.
 */
$title = 'Pemeriksaan EKG';
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
    .ek .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
    .ek .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #fee2e2; color: #991b1b; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .ek .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .ek .kepala small { color: #475569; }
    .ek .periksa { border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; margin-bottom: 16px; background: #fff; }
    .ek .periksa-h { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 10px 14px; border-bottom: 1px solid #eef2f6; background: #f8fafc; }
    .ek .periksa-h .aksi { display: flex; gap: 4px; }
    .ek .periksa-h .aksi .btn { padding: 3px 9px; font-weight: 600; }
    .ek .periksa-h .tgl { font-weight: 700; color: #0f172a; }
    .ek .periksa-h .dr { color: #334155; font-size: .86rem; }
    .ek .badge-i { font-size: .7rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; margin-left: auto; }
    .ek .bi-ok { background: #dcfce7; color: #14532d; }
    .ek .bi-belum { background: #fef3c7; color: #92400e; }
    .ek .gambar { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; padding: 14px; }
    @media (max-width: 767.98px) { .ek .gambar { grid-template-columns: 1fr; } }
    .ek .slot { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
    .ek .slot .lbl { font-size: .72rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .04em; padding: 6px 10px; border-bottom: 1px solid #eef2f6; }
    .ek .slot .isi { aspect-ratio: 16 / 7; background: #f8fafc; display: flex; align-items: center; justify-content: center; }
    .ek .slot img { width: 100%; height: 100%; object-fit: contain; cursor: zoom-in; background: #fff; }
    .ek .slot .kosong-slot { color: #94a3b8; font-size: .82rem; }
    .ek .interp { padding: 0 14px 14px; }
    .ek .interp .lbl { font-size: .72rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
    .ek .interp div { white-space: pre-line; color: #1f2937; }
    .ek .kosong { text-align: center; padding: 40px 10px; color: #475569; border: 1px dashed #cbd5e1; border-radius: 12px; }
    .ek .kosong i { font-size: 2.2rem; color: #94a3b8; display: block; margin-bottom: 6px; }
    #ekModal .up { border: 2px dashed #cbd5e1; border-radius: 10px; background: #f8fafc; aspect-ratio: 16 / 7; display: flex; align-items: center; justify-content: center; text-align: center; cursor: pointer; position: relative; overflow: hidden; color: #475569; font-size: .85rem; }
    #ekModal .up.over { border-color: #2563eb; background: #eff6ff; }
    #ekModal .up.ada { border-style: solid; background: #fff; }
    #ekModal .up img { width: 100%; height: 100%; object-fit: contain; }
    #ekModal .up i { font-size: 1.8rem; color: #64748b; display: block; }
    #ekModal .up.is-invalid { border-color: #dc3545; }
    #lihatModal .modal-content { background: #0f172a; color: #fff; }
    #lihatModal img { max-width: 100%; max-height: 82vh; object-fit: contain; display: block; margin: 0 auto; background: #fff; }
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
                <div class="card-body p-4 ek">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-heart-rate-monitor"></i></span>
                    <div class="flex-grow-1">
                      <h5>Pemeriksaan EKG <span class="small text-muted fw-normal" id="ekJumlah"></span></h5>
                      <small>Gambar rekaman EKG (EKG 1 wajib, EKG 2 opsional) beserta interpretasi dokter.</small>
                    </div>
                    <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_ekg?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                    <button type="button" class="btn btn-primary" id="btnTambah" disabled><i class="ti ti-plus me-1"></i>Tambah pemeriksaan</button>
                  </div>
                  <div class="alert alert-danger d-none" id="ekBatal"><i class="ti ti-ban me-1"></i>Kunjungan ini sudah dibatalkan — pemeriksaan baru tidak bisa ditambahkan.</div>
                  <div id="ekIsi"><div class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</div></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL FORM -->
  <div class="modal fade" id="ekModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <form id="ekForm" class="modal-content" novalidate autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title" id="ekJudul">Tambah pemeriksaan EKG</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_ekg" id="id_ekg">
          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="tanggal_pemeriksaan" class="form-label small fw-semibold">Tanggal pemeriksaan <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_pemeriksaan" id="tanggal_pemeriksaan" class="form-control">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-8">
              <label for="dokter" class="form-label small fw-semibold">Dokter <span class="text-danger">*</span></label>
              <input type="text" name="dokter" id="dokter" class="form-control" maxlength="120" list="ekDokter" placeholder="Ketik atau pilih nama dokter">
              <datalist id="ekDokter"></datalist>
              <div class="invalid-feedback"></div>
            </div>
          </div>
          <div class="row g-3 mb-1">
            <?php foreach (['ekg1' => 'EKG 1', 'ekg2' => 'EKG 2'] as $f => $label): ?>
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-1">
                  <span class="form-label small fw-semibold mb-0"><?= $label ?><?= $f === 'ekg1' ? ' <span class="text-danger">*</span>' : ' <span class="text-muted fw-normal">(opsional)</span>' ?></span>
                  <button type="button" class="btn btn-link btn-sm text-danger ms-auto p-0 d-none" data-hapus-slot="<?= $f ?>">Hapus</button>
                </div>
                <div class="up" data-slot="<?= $f ?>" tabindex="0" role="button" aria-label="Pilih gambar <?= $label ?>"></div>
                <input type="file" class="d-none" data-file="<?= $f ?>" accept="image/jpeg,image/png,image/webp">
                <div class="small text-danger mt-1" data-err="<?= $f ?>"></div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="small text-muted mb-3">JPG / PNG / WEBP. Foto besar otomatis diperkecil (maks. 2400 px) agar cepat, garis EKG tetap terbaca.</div>
          <label for="interpretasi" class="form-label small fw-semibold">Interpretasi</label>
          <textarea name="interpretasi" id="interpretasi" class="form-control" rows="4" maxlength="5000" placeholder="mis. Irama sinus, HR 78 x/menit, aksis normal, tidak tampak ST elevasi"></textarea>
          <div class="form-text">Boleh diisi menyusul — pemeriksaan tanpa interpretasi ditandai <b>Belum diinterpretasi</b>.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary px-4" id="btnSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL LIHAT -->
  <div class="modal fade" id="lihatModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div class="fw-semibold" id="lihatJudul"></div>
          <a class="btn btn-sm btn-light ms-auto me-2" id="lihatAsli" target="_blank" rel="noopener"><i class="ti ti-external-link me-1"></i>Buka asli</a>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body"><img id="lihatImg" alt="Gambar EKG"></div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      'use strict';
      const API = 'controller/ranap/ekgController?no=' + encodeURIComponent(<?= json_encode($no) ?>);
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglPanjang = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); if (!m) return '-'; const d = new Date(Date.UTC(+m[1], m[2] - 1, +m[3])); return HARI[d.getUTCDay()] + ', ' + (+m[3]) + ' ' + BLN[m[2] - 1] + ' ' + m[1]; };
      const urlFile = p => String(p || '').split('/').map(encodeURIComponent).join('/');
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
      let DATA = [], K = { batal: true };

      async function api(url, opt) {
        const r = await fetch(url, opt);
        const j = await r.json().catch(() => ({ status: 'error', message: r.status === 413 ? 'Ukuran gambar melebihi batas server.' : 'Respon server tidak valid.' }));
        if (!r.ok || j.status !== 'success') { const e = new Error(j.message || 'Terjadi kesalahan.'); e.field = j.field; throw e; }
        return j;
      }

      /* ================= DAFTAR ================= */
      function muat() {
        return api(API).then(res => {
          DATA = res.data || [];
          K = res.kunjungan || {};
          $id('btnTambah').disabled = !!K.batal;
          $id('ekBatal').classList.toggle('d-none', !K.batal);
          $id('ekDokter').innerHTML = (res.dokter || []).map(n => `<option value="${esc(n)}">`).join('');
          render();
        }).catch(e => { $id('ekIsi').innerHTML = `<div class="kosong text-danger"><i class="ti ti-alert-triangle"></i>${esc(e.message)}</div>`; });
      }
      function slot(label, path, i, kol) {
        return `<div class="slot"><div class="lbl">${label}</div><div class="isi">${path
          ? `<img src="${esc(urlFile(path))}" alt="${label}" loading="lazy" data-lihat="${i}" data-kol="${kol}">`
          : '<span class="kosong-slot">Tidak ada gambar</span>'}</div></div>`;
      }
      function render() {
        $id('ekJumlah').textContent = DATA.length ? '· ' + DATA.length + ' pemeriksaan' : '';
        if (!DATA.length) {
          $id('ekIsi').innerHTML = '<div class="kosong"><i class="ti ti-heart-rate-monitor"></i>Belum ada pemeriksaan EKG untuk kunjungan ini.</div>';
          return;
        }
        $id('ekIsi').innerHTML = DATA.map((d, i) => {
          const ada = d.interpretasi && d.interpretasi.trim();
          return `<div class="periksa">
            <div class="periksa-h">
              <div class="aksi">
                <button type="button" class="btn btn-sm btn-warning" data-ubah="${i}" title="Ubah"><i class="ti ti-pencil"></i></button>
                <button type="button" class="btn btn-sm btn-danger" data-hapus="${i}" title="Hapus"><i class="ti ti-trash"></i></button>
              </div>
              <span class="tgl"><i class="ti ti-calendar me-1"></i>${esc(tglPanjang(d.tanggal_pemeriksaan))}</span>
              <span class="dr"><i class="ti ti-stethoscope me-1"></i>${esc(d.dokter || '-')}</span>
              <span class="badge-i ${ada ? 'bi-ok' : 'bi-belum'}">${ada ? 'Sudah diinterpretasi' : 'Belum diinterpretasi'}</span>
            </div>
            <div class="gambar">${slot('EKG 1', d.ekg1, i, 'ekg1')}${slot('EKG 2', d.ekg2, i, 'ekg2')}</div>
            ${ada ? `<div class="interp"><div class="lbl">Interpretasi</div><div>${esc(d.interpretasi)}</div></div>` : ''}
          </div>`;
        }).join('');
        $id('ekIsi').querySelectorAll('img[data-lihat]').forEach(img => img.addEventListener('error', () => {
          img.outerHTML = '<span class="kosong-slot"><i class="ti ti-photo-off me-1"></i>File tidak ditemukan</span>';
        }, { once: true }));
      }

      $id('ekIsi').addEventListener('click', e => {
        const l = e.target.closest('[data-lihat]'), u = e.target.closest('[data-ubah]'), h = e.target.closest('[data-hapus]');
        if (l) {
          const d = DATA[+l.dataset.lihat], p = d[l.dataset.kol];
          $id('lihatImg').src = urlFile(p);
          $id('lihatAsli').href = urlFile(p);
          $id('lihatJudul').textContent = (l.dataset.kol === 'ekg1' ? 'EKG 1' : 'EKG 2') + ' · ' + tglPanjang(d.tanggal_pemeriksaan);
          bootstrap.Modal.getOrCreateInstance($id('lihatModal')).show();
        }
        if (u) bukaForm(DATA[+u.dataset.ubah]);
        if (h) {
          const d = DATA[+h.dataset.hapus];
          Swal.fire({
            icon: 'warning', title: 'Hapus pemeriksaan EKG?', html: `Pemeriksaan <b>${esc(tglPanjang(d.tanggal_pemeriksaan))}</b> beserta gambar EKG-nya dihapus permanen.`,
            showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true
          }).then(x => {
            if (!x.isConfirmed) return;
            api(API + '&id=' + encodeURIComponent(d.id_ekg), { method: 'DELETE' }).then(res => { toast(res.message); muat(); }).catch(err => Swal.fire('Gagal', err.message, 'error'));
          });
        }
      });

      /* ================= FORM ================= */
      const modal = bootstrap.Modal.getOrCreateInstance($id('ekModal'));
      const form = $id('ekForm');
      let FILE = {}, LAMA = {}, HAPUS2 = false;

      function renderSlot(f) {
        const box = form.querySelector(`[data-slot="${f}"]`);
        const src = FILE[f] ? FILE[f].url : (f === 'ekg2' && HAPUS2 ? null : LAMA[f] ? urlFile(LAMA[f]) : null);
        box.classList.toggle('ada', !!src);
        box.innerHTML = src ? `<img src="${esc(src)}" alt="">` : `<div><i class="ti ti-photo-up"></i><div class="fw-semibold mt-1">Pilih / seret gambar</div><div class="small">klik untuk memilih</div></div>`;
        form.querySelector(`[data-hapus-slot="${f}"]`).classList.toggle('d-none', !(f === 'ekg2' && src));
      }
      function setFile(f, file) {
        form.querySelector(`[data-err="${f}"]`).textContent = '';
        form.querySelector(`[data-slot="${f}"]`).classList.remove('is-invalid');
        if (!file) return;
        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) { form.querySelector(`[data-err="${f}"]`).textContent = 'Harus gambar JPG, PNG, atau WEBP.'; return; }
        if (FILE[f]) URL.revokeObjectURL(FILE[f].url);
        FILE[f] = { file, url: URL.createObjectURL(file) };
        if (f === 'ekg2') HAPUS2 = false;
        renderSlot(f);
      }
      form.querySelectorAll('[data-slot]').forEach(box => {
        const f = box.dataset.slot, inp = form.querySelector(`[data-file="${f}"]`);
        box.addEventListener('click', () => inp.click());
        box.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); inp.click(); } });
        inp.addEventListener('change', () => { setFile(f, inp.files[0]); inp.value = ''; });
        ['dragenter', 'dragover'].forEach(ev => box.addEventListener(ev, e => { e.preventDefault(); box.classList.add('over'); }));
        ['dragleave', 'drop'].forEach(ev => box.addEventListener(ev, e => { e.preventDefault(); box.classList.remove('over'); }));
        box.addEventListener('drop', e => setFile(f, e.dataTransfer.files[0]));
      });
      form.querySelector('[data-hapus-slot="ekg2"]').addEventListener('click', () => {
        if (FILE.ekg2) { URL.revokeObjectURL(FILE.ekg2.url); delete FILE.ekg2; }
        if (LAMA.ekg2) HAPUS2 = true;
        renderSlot('ekg2');
      });

      function bersihkan() {
        form.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback, [data-err]').forEach(e => e.textContent = '');
      }
      function tandai(field, pesan) {
        const slotEl = form.querySelector(`[data-slot="${field}"]`);
        if (slotEl) { slotEl.classList.add('is-invalid'); form.querySelector(`[data-err="${field}"]`).textContent = pesan; slotEl.focus(); return true; }
        const el = $id(field);
        if (!el) return false;
        el.classList.add('is-invalid');
        const fb = el.parentElement.querySelector('.invalid-feedback');
        if (fb) fb.textContent = pesan;
        el.focus();
        return true;
      }
      function bukaForm(d) {
        form.reset();
        bersihkan();
        Object.values(FILE).forEach(x => URL.revokeObjectURL(x.url));
        FILE = {};
        HAPUS2 = false;
        LAMA = d ? { ekg1: d.ekg1, ekg2: d.ekg2 } : {};
        $id('tanggal_pemeriksaan').max = K.hari_ini || '';
        $id('tanggal_pemeriksaan').min = K.tanggal || '';
        if (d) {
          $id('ekJudul').textContent = 'Ubah pemeriksaan EKG';
          $id('id_ekg').value = d.id_ekg;
          $id('tanggal_pemeriksaan').value = d.tanggal_pemeriksaan || '';
          $id('dokter').value = d.dokter || '';
          $id('interpretasi').value = d.interpretasi || '';
        } else {
          $id('ekJudul').textContent = 'Tambah pemeriksaan EKG';
          $id('id_ekg').value = '';
          $id('tanggal_pemeriksaan').value = K.hari_ini || '';
          $id('dokter').value = K.dokter || '';
        }
        renderSlot('ekg1');
        renderSlot('ekg2');
        modal.show();
      }
      $id('btnTambah').addEventListener('click', () => bukaForm(null));

      // perkecil foto besar; batas sisi 2400 px & kualitas 0,9 agar garis EKG tetap tajam
      function perkecil(file) {
        return new Promise(resolve => {
          const img = new Image(), url = URL.createObjectURL(file);
          img.onload = () => {
            URL.revokeObjectURL(url);
            const skala = Math.min(1, 2400 / Math.max(img.naturalWidth, img.naturalHeight));
            if (skala === 1 && file.size <= 2 * 1024 * 1024) return resolve(file);
            const c = document.createElement('canvas');
            c.width = Math.round(img.naturalWidth * skala);
            c.height = Math.round(img.naturalHeight * skala);
            const ctx = c.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, c.width, c.height);
            ctx.drawImage(img, 0, 0, c.width, c.height);
            c.toBlob(b => resolve(b && b.size < file.size ? new File([b], 'ekg.jpg', { type: 'image/jpeg' }) : file), 'image/jpeg', 0.9);
          };
          img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
          img.src = url;
        });
      }

      let menyimpan = false;
      form.addEventListener('submit', async e => {
        e.preventDefault();
        if (menyimpan) return;
        bersihkan();
        if (!$id('tanggal_pemeriksaan').value) return tandai('tanggal_pemeriksaan', 'Tanggal wajib diisi.');
        if (!$id('dokter').value.trim()) return tandai('dokter', 'Nama dokter wajib diisi.');
        if (!FILE.ekg1 && !LAMA.ekg1) return tandai('ekg1', 'Unggah gambar EKG 1.');
        menyimpan = true;
        const btn = $id('btnSimpan'), label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const fd = new FormData(form);
          for (const f of ['ekg1', 'ekg2']) if (FILE[f]) { const x = await perkecil(FILE[f].file); fd.append(f, x, x.name || f + '.jpg'); }
          if (HAPUS2) fd.append('hapus_ekg2', '1');
          const res = await api(API, { method: 'POST', body: fd });
          modal.hide();
          toast(res.message);
          muat();
        } catch (err) {
          if (!(err.field && tandai(err.field, err.message))) Swal.fire('Gagal', err.message, 'error');
        } finally {
          menyimpan = false;
          btn.disabled = false;
          btn.innerHTML = label;
        }
      });

      muat();
    })();
  </script>
</body>

</html>
