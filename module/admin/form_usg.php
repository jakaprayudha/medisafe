<?php
/**
 * Pemeriksaan USG kunjungan (maks. 3 foto per pemeriksaan). API controller/ranap/usgController.php.
 * Perbaikan: query pasien dirangkai dari URL (SQL injection) & hasilnya tidak dipakai; $_GET tanpa default; nomor
 * kunjungan/RM dicetak ke input tanpa escape; kolom "USG 3" menampilkan foto USG 2; USG 2/3 selalu menempel ke
 * pemeriksaan pertama dan isian tanggal/usia/interpretasi diabaikan; tidak bisa mengubah data; hapus tanpa pesan
 * gagal; interpretasi bertanda wajib tapi tidak divalidasi; dokter diketik bebas tanpa isian awal; foto tidak bisa
 * diperbesar; data dari server disisipkan ke HTML tanpa escape (XSS); kolom Aksi di belakang.
 */
$title = 'Foto USG';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$st = $koneksi->prepare("SELECT 1 FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$ada = (bool) $st->get_result()->fetch_row();
$st->close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .us .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
    .us .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #e0f2fe; color: #075985; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .us .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .us .kepala small { color: #475569; }
    .us .periksa { border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 14px; overflow: hidden; }
    .us .periksa-h { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 16px; padding: 10px 14px; background: #f8fafc; border-bottom: 1px solid #eef2f6; color: #0f172a; }
    .us .periksa-h .lbl { font-size: .7rem; text-transform: uppercase; letter-spacing: .03em; color: #475569; display: block; line-height: 1.1; }
    .us .periksa-b { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: 16px; padding: 14px; }
    @media (max-width: 991.98px) { .us .periksa-b { grid-template-columns: 1fr; } }
    .us .foto3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .us .foto3 .sl { aspect-ratio: 4 / 3; border-radius: 10px; background: #0f172a; overflow: hidden; position: relative; }
    .us .foto3 .sl img { width: 100%; height: 100%; object-fit: contain; cursor: zoom-in; }
    .us .foto3 .sl .no { position: absolute; left: 6px; top: 6px; background: rgba(15, 23, 42, .75); color: #fff; font-size: .68rem; font-weight: 700; border-radius: 5px; padding: 1px 6px; }
    .us .foto3 .sl.kosong { background: #f1f5f9; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: .78rem; }
    .us .interp .lbl { font-size: .72rem; font-weight: 700; text-transform: uppercase; color: #1e40af; letter-spacing: .03em; }
    .us .interp div { color: #0f172a; white-space: pre-line; margin-top: 4px; }
    .us .kosong-semua { border: 1px dashed #cbd5e1; border-radius: 12px; padding: 28px; text-align: center; color: #334155; }
    .us .kosong-semua i { font-size: 2.4rem; color: #64748b; display: block; margin-bottom: 6px; }
    #usModal .slot { border: 2px dashed #cbd5e1; border-radius: 12px; aspect-ratio: 4 / 3; position: relative; display: flex; align-items: center; justify-content: center; text-align: center; color: #334155; cursor: pointer; overflow: hidden; background: #f8fafc; font-size: .82rem; }
    #usModal .slot.over { border-color: #2563eb; background: #eff6ff; }
    #usModal .slot.isi { border-style: solid; background: #0f172a; }
    #usModal .slot img { width: 100%; height: 100%; object-fit: contain; }
    #usModal .slot .x { position: absolute; top: 6px; right: 6px; }
    #usModal .slot .no { position: absolute; left: 6px; top: 6px; background: rgba(15, 23, 42, .75); color: #fff; font-size: .68rem; font-weight: 700; border-radius: 5px; padding: 1px 6px; }
    #usModal .slot i { font-size: 1.6rem; color: #64748b; display: block; }
    #usModal .slot.is-invalid { border-color: #dc2626; }
    #lihatModal .modal-content { background: #0f172a; }
    #lihatModal img { max-width: 100%; max-height: 80vh; object-fit: contain; display: block; margin: 0 auto; }
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
                <div class="card-body p-4 us">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-heart-rate-monitor"></i></span>
                    <div class="flex-grow-1">
                      <h5>Ultrasonografi (USG) <span class="small text-muted fw-normal" id="usJumlah"></span></h5>
                      <small>Foto USG, usia kandungan & interpretasi dokter. Satu pemeriksaan bisa berisi hingga 3 foto.</small>
                    </div>
                    <?php if ($ada): ?>
                      <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_usg?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                      <button type="button" class="btn btn-primary d-none" id="btnTambah"><i class="ti ti-plus me-1"></i>Tambah pemeriksaan</button>
                    <?php endif; ?>
                  </div>
                  <?php if (!$ada): ?>
                    <div class="alert alert-danger mb-0"><i class="ti ti-alert-triangle me-1"></i>Kunjungan tidak ditemukan untuk faskes ini.</div>
                  <?php else: ?>
                    <div id="usBatal" class="alert alert-warning d-none"><i class="ti ti-ban me-1"></i>Kunjungan sudah dibatalkan — data hanya dapat dilihat.</div>
                    <div id="usDaftar"><div class="kosong-semua"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</div></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($ada): ?>
  <!-- MODAL TAMBAH / UBAH -->
  <div class="modal fade" id="usModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <form id="programForm" class="modal-content" novalidate>
        <div class="modal-header">
          <h5 class="modal-title" id="usJudul">Tambah Pemeriksaan USG</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_usg" id="id_usg">
          <div class="row g-3">
            <div class="col-md-4">
              <label for="tanggal_pemeriksaan" class="form-label">Tanggal Pemeriksaan <span class="text-danger">*</span></label>
              <input type="date" name="tanggal_pemeriksaan" id="tanggal_pemeriksaan" class="form-control" required>
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
              <label for="usia_kandungan" class="form-label">Usia Kandungan <span class="text-danger">*</span></label>
              <input type="text" name="usia_kandungan" id="usia_kandungan" class="form-control" maxlength="50" placeholder="mis. 32 minggu 4 hari" required>
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
              <label for="dokter" class="form-label">Dokter Pemeriksa <span class="text-danger">*</span></label>
              <input type="text" name="dokter" id="dokter" class="form-control" maxlength="100" required>
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-12">
              <label class="form-label mb-1">Foto USG <span class="text-danger">*</span> <span class="small text-dark fw-normal">— minimal 1, maksimal 3 (JPG / PNG / WEBP). Klik kotak atau seret foto ke kotak.</span></label>
              <div class="row g-2" id="usSlot">
                <?php foreach ([1, 2, 3] as $i): ?>
                  <div class="col-4"><div class="slot" data-slot="usg<?= $i ?>" tabindex="0" role="button"></div></div>
                <?php endforeach; ?>
              </div>
              <div class="text-danger small mt-1 d-none" id="usSlotErr"></div>
              <input type="file" id="foto_path" class="d-none" accept="image/jpeg,image/png,image/webp">
            </div>
            <div class="col-12">
              <label for="interpretasi" class="form-label">Interpretasi <span class="text-danger">*</span></label>
              <textarea name="interpretasi" id="interpretasi" class="form-control" rows="5" placeholder="Janin tunggal hidup intrauterin, presentasi kepala, DJJ (+), plasenta di fundus, ketuban cukup, TBJ …" required></textarea>
              <div class="invalid-feedback"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="usSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL LIHAT FOTO -->
  <div class="modal fade" id="lihatModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <span class="text-white fw-semibold" id="lihatJudul"></span>
          <a class="btn btn-sm btn-light ms-auto me-2" id="lihatAsli" target="_blank" rel="noopener"><i class="ti ti-external-link me-1"></i>Buka asli</a>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body"><img id="lihatImg" alt="Foto USG"></div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php require 'library.php'; ?>
  <?php if ($ada): ?>
  <script>
    (function() {
      'use strict';
      const API = 'controller/ranap/usgController.php?no=' + encodeURIComponent(<?= json_encode($no) ?>);
      const SLOT = ['usg1', 'usg2', 'usg3'];
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
      const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'], BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tgl = s => { const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(s || ''); if (!m) return '-'; const d = new Date(+m[1], m[2] - 1, +m[3]); return `${HARI[d.getDay()]}, ${+m[3]} ${BLN[m[2] - 1]} ${m[1]}`; };
      const urlFoto = p => /^uploads\/usg\/[^/]+$/.test(p || '') ? 'uploads/usg/' + encodeURIComponent(p.split('/').pop()) : '';
      let DATA = [], K = {};

      function muat() {
        $.ajax({ url: API, dataType: 'json', cache: false })
          .done(res => { DATA = res.data || []; K = res.kunjungan || {}; render(); })
          .fail(x => { $id('usDaftar').innerHTML = `<div class="kosong-semua text-danger"><i class="ti ti-alert-triangle"></i>${esc((x.responseJSON && x.responseJSON.message) || 'Gagal memuat data USG.')}</div>`; });
      }

      function render() {
        $id('usBatal').classList.toggle('d-none', !K.batal);
        $id('btnTambah').classList.toggle('d-none', !!K.batal);
        $id('usJumlah').textContent = DATA.length ? `· ${DATA.length} pemeriksaan` : '';
        if (!DATA.length) {
          $id('usDaftar').innerHTML = `<div class="kosong-semua"><i class="ti ti-heart-rate-monitor"></i><b>Belum ada pemeriksaan USG</b>${K.batal ? '' : '<div class="small mt-1">Klik "Tambah pemeriksaan" untuk mengunggah foto USG.</div>'}</div>`;
          return;
        }
        $id('usDaftar').innerHTML = DATA.map((r, i) => `
          <div class="periksa">
            <div class="periksa-h">
              ${K.batal ? '' : `<div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-warning" data-ubah="${i}" title="Ubah"><i class="ti ti-pencil"></i></button>
                <button type="button" class="btn btn-danger" data-hapus="${i}" title="Hapus"><i class="ti ti-trash"></i></button></div>`}
              <div><span class="lbl">Tanggal</span><b>${esc(tgl(r.tanggal_pemeriksaan))}</b></div>
              <div><span class="lbl">Usia kandungan</span><b>${esc(r.usia_kandungan || '-')}</b></div>
              <div><span class="lbl">Dokter</span><b>${esc(r.dokter || '-')}</b></div>
            </div>
            <div class="periksa-b">
              <div class="foto3">${SLOT.map((s, j) => {
                const u = urlFoto(r[s]);
                return u ? `<div class="sl"><span class="no">USG ${j + 1}</span><img src="${esc(u)}" alt="USG ${j + 1}" loading="lazy" data-lihat="${i}" data-slot="${s}"></div>` : '<div class="sl kosong">—</div>';
              }).join('')}</div>
              <div class="interp"><span class="lbl">Interpretasi</span><div>${esc(r.interpretasi || '-')}</div></div>
            </div>
          </div>`).join('');
      }

      $id('usDaftar').addEventListener('click', e => {
        const l = e.target.closest('[data-lihat]'), u = e.target.closest('[data-ubah]'), d = e.target.closest('[data-hapus]');
        if (l) {
          const r = DATA[l.dataset.lihat], url = urlFoto(r[l.dataset.slot]);
          $id('lihatImg').src = url;
          $id('lihatAsli').href = url;
          $id('lihatJudul').textContent = `${l.alt} · ${tgl(r.tanggal_pemeriksaan)}`;
          bootstrap.Modal.getOrCreateInstance($id('lihatModal')).show();
        }
        if (u) buka(DATA[u.dataset.ubah]);
        if (d) hapus(DATA[d.dataset.hapus]);
      });
      $id('usDaftar').addEventListener('error', e => { if (e.target.tagName === 'IMG') e.target.parentElement.outerHTML = '<div class="sl kosong"><i class="ti ti-photo-off me-1"></i>File hilang</div>'; }, true);

      /* ================= FORM ================= */
      const form = $id('programForm');
      let FOTO = {}, target = null;   // FOTO[slot] = {url (lama)} | {file, url (blob)} | null

      function bersih() {
        form.querySelectorAll('.is-invalid').forEach(i => i.classList.remove('is-invalid'));
        $id('usSlotErr').classList.add('d-none');
      }
      function tandai(field, pesan) {
        if (SLOT.includes(field)) {
          document.querySelector(`#usSlot [data-slot="${field}"]`).classList.add('is-invalid');
          $id('usSlotErr').textContent = pesan;
          $id('usSlotErr').classList.remove('d-none');
          return true;
        }
        const i = form.querySelector(`[name="${field}"]`);
        if (!i) return false;
        i.classList.add('is-invalid');
        i.parentElement.querySelector('.invalid-feedback').textContent = pesan;
        i.focus();
        return true;
      }

      function renderSlot() {
        SLOT.forEach((s, j) => {
          const el = document.querySelector(`#usSlot [data-slot="${s}"]`), f = FOTO[s];
          el.classList.toggle('isi', !!f);
          el.innerHTML = f
            ? `<span class="no">USG ${j + 1}${f.file ? ' · baru' : ''}</span><img src="${esc(f.url)}" alt="USG ${j + 1}"><button type="button" class="btn btn-sm btn-danger x" data-buang="${s}" title="Hapus foto"><i class="ti ti-x"></i></button>`
            : `<div><i class="ti ti-photo-plus"></i>USG ${j + 1}<div class="small">Klik / seret foto</div></div>`;
        });
      }
      function lepasBlob() { Object.values(FOTO).forEach(f => f && f.file && URL.revokeObjectURL(f.url)); }

      function buka(r) {
        form.reset();
        bersih();
        lepasBlob();
        FOTO = {};
        $id('id_usg').value = r ? r.id_usg : '';
        $id('tanggal_pemeriksaan').value = r ? r.tanggal_pemeriksaan : (K.hari_ini || '');
        $id('tanggal_pemeriksaan').max = K.hari_ini || '';
        $id('usia_kandungan').value = r ? (r.usia_kandungan || '') : '';
        $id('dokter').value = r ? (r.dokter || '') : (K.dokter || '');
        $id('interpretasi').value = r ? (r.interpretasi || '') : '';
        if (r) SLOT.forEach(s => { const u = urlFoto(r[s]); FOTO[s] = u ? { url: u } : null; });
        renderSlot();
        $id('usJudul').textContent = r ? 'Ubah Pemeriksaan USG' : 'Tambah Pemeriksaan USG';
        bootstrap.Modal.getOrCreateInstance($id('usModal')).show();
      }
      $id('btnTambah').addEventListener('click', () => buka(null));
      $id('usModal').addEventListener('hidden.bs.modal', () => { lepasBlob(); FOTO = {}; });

      // foto besar (> 2 MB) diperkecil ke sisi 2000 px JPEG agar di bawah batas 5 MB server
      function siapkan(file) {
        return new Promise(resolve => {
          if (file.size <= 2 * 1024 * 1024) return resolve(file);
          const img = new Image(), url = URL.createObjectURL(file);
          img.onload = () => {
            URL.revokeObjectURL(url);
            const s = Math.min(1, 2000 / Math.max(img.naturalWidth, img.naturalHeight)), c = document.createElement('canvas');
            c.width = Math.round(img.naturalWidth * s);
            c.height = Math.round(img.naturalHeight * s);
            const k = c.getContext('2d');
            k.fillStyle = '#000';
            k.fillRect(0, 0, c.width, c.height);
            k.drawImage(img, 0, 0, c.width, c.height);
            c.toBlob(b => resolve(b && b.size < file.size ? new File([b], 'usg.jpg', { type: 'image/jpeg' }) : file), 'image/jpeg', 0.9);
          };
          img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
          img.src = url;
        });
      }
      async function pasang(slot, file) {
        if (!file) return;
        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) return Swal.fire('Format tidak didukung', 'Gunakan foto JPG, PNG, atau WEBP.', 'warning');
        const f = await siapkan(file);
        if (f.size > 5 * 1024 * 1024) return Swal.fire('File terlalu besar', 'Ukuran foto maksimal 5 MB.', 'warning');
        if (FOTO[slot] && FOTO[slot].file) URL.revokeObjectURL(FOTO[slot].url);
        FOTO[slot] = { file: f, url: URL.createObjectURL(f) };
        bersih();
        renderSlot();
      }

      const slotBox = $id('usSlot');
      slotBox.addEventListener('click', e => {
        const b = e.target.closest('[data-buang]');
        if (b) { e.stopPropagation(); const s = b.dataset.buang; if (FOTO[s] && FOTO[s].file) URL.revokeObjectURL(FOTO[s].url); FOTO[s] = null; renderSlot(); return; }
        const sl = e.target.closest('[data-slot]');
        if (sl) { target = sl.dataset.slot; $id('foto_path').click(); }
      });
      slotBox.addEventListener('keydown', e => { const sl = e.target.closest('.slot'); if (sl && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); target = sl.dataset.slot; $id('foto_path').click(); } });
      $id('foto_path').addEventListener('change', function() { const f = this.files[0]; this.value = ''; if (target) pasang(target, f); });
      ['dragenter', 'dragover'].forEach(ev => slotBox.addEventListener(ev, e => { const z = e.target.closest('.slot'); if (z) { e.preventDefault(); z.classList.add('over'); } }));
      ['dragleave', 'drop'].forEach(ev => slotBox.addEventListener(ev, e => { const z = e.target.closest('.slot'); if (z) { e.preventDefault(); z.classList.remove('over'); } }));
      slotBox.addEventListener('drop', e => { const z = e.target.closest('.slot'); if (z && e.dataTransfer.files[0]) pasang(z.dataset.slot, e.dataTransfer.files[0]); });

      form.addEventListener('submit', async e => {
        e.preventDefault();
        bersih();
        const v = n => $id(n).value.trim();
        if (!v('tanggal_pemeriksaan')) return tandai('tanggal_pemeriksaan', 'Tanggal wajib diisi.');
        if (K.hari_ini && v('tanggal_pemeriksaan') > K.hari_ini) return tandai('tanggal_pemeriksaan', 'Tanggal tidak boleh di masa depan.');
        if (!v('usia_kandungan')) return tandai('usia_kandungan', 'Usia kandungan wajib diisi.');
        if (!v('dokter')) return tandai('dokter', 'Dokter pemeriksa wajib diisi.');
        if (!SLOT.some(s => FOTO[s])) return tandai('usg1', 'Unggah minimal 1 foto USG.');
        if (!v('interpretasi')) return tandai('interpretasi', 'Interpretasi wajib diisi.');

        const fd = new FormData();
        const id = $id('id_usg').value;
        if (id) fd.append('id_usg', id);
        ['tanggal_pemeriksaan', 'usia_kandungan', 'dokter', 'interpretasi'].forEach(n => fd.append(n, v(n)));
        SLOT.forEach(s => {
          if (FOTO[s] && FOTO[s].file) fd.append(s, FOTO[s].file, FOTO[s].file.name || s + '.jpg');
          else if (!FOTO[s] && id) fd.append('hapus_' + s, '1');
        });
        const btn = $id('usSimpan');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const r = await fetch(API, { method: 'POST', body: fd });
          const res = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
          if (res.status !== 'success') {
            if (!(res.field && tandai(res.field, res.message))) Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
            return;
          }
          bootstrap.Modal.getInstance($id('usModal')).hide();
          toast(res.message || 'Tersimpan.');
          muat();
        } catch (err) {
          Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
        } finally {
          btn.disabled = false;
          btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan';
        }
      });

      function hapus(r) {
        Swal.fire({
          icon: 'warning', title: 'Hapus pemeriksaan USG?', html: `${esc(tgl(r.tanggal_pemeriksaan))} · ${esc(r.usia_kandungan || '')}<br>Semua foto pemeriksaan ini ikut dihapus.`,
          showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true
        }).then(x => {
          if (!x.isConfirmed) return;
          $.ajax({ url: API + '&id=' + encodeURIComponent(r.id_usg), type: 'DELETE', dataType: 'json' })
            .done(res => toast(res.message || 'Dihapus.'))
            .fail(x => Swal.fire('Gagal', (x.responseJSON && x.responseJSON.message) || 'Gagal menghapus.', 'error'))
            .always(muat);
        });
      }

      muat();
    })();
  </script>
  <?php endif; ?>
</body>

</html>
