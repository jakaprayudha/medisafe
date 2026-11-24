<?php
/**
 * Rekapitulasi pelayanan persalinan (klaim persalinan FKTP BPJS). API controller/ranap/getFormRekapPersalinan.php &
 * saveFormRekapPersalinan.php, cetak print/formulir_persalinan.
 * Perbaikan: $_GET tanpa default & nomor kunjungan/RM dicetak ke JS tanpa escape; data tidak dimuat bila URL tanpa rm;
 * nama dokter selalu kosong (JOIN ms_doctor); simpan selalu bisa gagal tanpa pesan jelas ("alert"); tarif paket & tanda
 * tangan pasien tercetak tapi tidak ada isiannya; G/P/A teks bebas tanpa validasi; tidak ada isian awal dari ANC;
 * tidak ada penanda data sudah tersimpan.
 */
$title = 'Rekapitulasi Pelayanan Persalinan';
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
$jenis = ['Persalinan pervaginam normal', 'Persalinan pervaginam dengan tindakan emergensi dasar', 'Pelayanan pra rujukan', 'Pelayanan pasca keguguran', 'Pelayanan tindakan pasca persalinan'];
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .rp .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
    .rp .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #fce7f3; color: #9d174d; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .rp .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .rp .kepala small { color: #475569; }
    .rp .st { display: inline-block; font-size: .72rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; vertical-align: middle; }
    .rp .st-ok { background: #dcfce7; color: #14532d; }
    .rp .st-baru { background: #fef3c7; color: #78350f; }
    .rp .bag { border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 16px; }
    .rp .bag-h { display: flex; align-items: center; gap: 8px; padding: 10px 16px; border-bottom: 1px solid #eef2f6; background: #f8fafc; border-radius: 14px 14px 0 0; font-weight: 700; color: #0f172a; }
    .rp .bag-h i { color: #be185d; font-size: 1.1rem; }
    .rp .bag-b { padding: 14px 16px; }
    .rp .form-label { font-weight: 600; color: #1e293b; font-size: .85rem; margin-bottom: 4px; }
    .rp .form-control[readonly] { background: #f1f5f9; color: #0f172a; }
    .rp .bantu { font-size: .75rem; color: #334155; margin-top: 3px; }
    .rp .gpa { display: inline-flex; align-items: center; gap: 6px; background: #fdf2f8; color: #831843; border: 1px solid #fbcfe8; border-radius: 10px; padding: 6px 12px; font-weight: 700; font-size: 1.05rem; }
    .rp .ttd-box { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; }
    .rp .ttd-img { height: 80px; max-width: 240px; object-fit: contain; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 4px; }
    .rp .ttd-kosong { width: 240px; height: 80px; border: 2px dashed #cbd5e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #475569; font-size: .82rem; }
    .rp-bar { position: sticky; bottom: 0; z-index: 20; background: rgba(255, 255, 255, .96); border-top: 1px solid #e2e8f0; padding: 10px 0; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: flex-end; }
    .rp-bar .ket { margin-right: auto; font-size: .82rem; color: #334155; }
    #ttdModal .kanvas { position: relative; border: 2px dashed #94a3b8; border-radius: 12px; background: #fff; }
    #ttdModal canvas { display: block; width: 100%; height: 300px; cursor: crosshair; touch-action: none; }
    #ttdModal .garis { position: absolute; left: 10%; right: 10%; bottom: 54px; border-bottom: 1px solid #cbd5e1; pointer-events: none; }
    #ttdModal .petunjuk { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #64748b; pointer-events: none; }
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
                <div class="card-body p-4 rp">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-baby-carriage"></i></span>
                    <div class="flex-grow-1">
                      <h5>Rekapitulasi Pelayanan Persalinan <span class="st" id="rpStatus"></span></h5>
                      <small>Rekap persalinan untuk klaim BPJS Kesehatan di FKTP — ditandatangani pasien.</small>
                    </div>
                    <?php if ($ada): ?>
                      <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_persalinan?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                    <?php endif; ?>
                  </div>

                  <?php if (!$ada): ?>
                    <div class="alert alert-danger mb-0"><i class="ti ti-alert-triangle me-1"></i>Kunjungan tidak ditemukan untuk faskes ini.</div>
                  <?php else: ?>
                    <div id="rpBatal" class="alert alert-warning d-none"><i class="ti ti-ban me-1"></i>Kunjungan sudah dibatalkan — data hanya dapat dilihat.</div>
                    <form id="rpForm" novalidate>
                      <fieldset id="rpFieldset" disabled>
                        <div class="bag">
                          <div class="bag-h"><i class="ti ti-user"></i>Identitas Pasien</div>
                          <div class="bag-b row g-3">
                            <div class="col-md-3"><label class="form-label">Nama Pasien</label><input type="text" id="patient_name" class="form-control" readonly></div>
                            <div class="col-md-3"><label class="form-label">Gender</label><input type="text" id="patient_gender" class="form-control" readonly></div>
                            <div class="col-md-3"><label class="form-label">Usia</label><input type="text" id="usia" class="form-control" readonly></div>
                            <div class="col-md-3"><label class="form-label">Dokter</label><input type="text" id="doctor_name" class="form-control" readonly></div>
                          </div>
                        </div>

                        <div class="bag">
                          <div class="bag-h"><i class="ti ti-heartbeat"></i>Data Persalinan <span class="gpa ms-auto" id="gpaChip">G– P– A–</span></div>
                          <div class="bag-b row g-3">
                            <div class="col-md-2 col-4"><label class="form-label" for="gravid">Gravid <span class="text-danger">*</span></label><input type="number" id="gravid" class="form-control" min="1" max="30" inputmode="numeric"><div class="invalid-feedback"></div></div>
                            <div class="col-md-2 col-4"><label class="form-label" for="partus">Partus</label><input type="number" id="partus" class="form-control" min="0" max="30" inputmode="numeric"><div class="invalid-feedback"></div></div>
                            <div class="col-md-2 col-4"><label class="form-label" for="abortus">Abortus</label><input type="number" id="abortus" class="form-control" min="0" max="30" inputmode="numeric"><div class="invalid-feedback"></div></div>
                            <div class="col-md-6"><div class="bantu mt-md-4" id="gpaBantu">Partus termasuk persalinan ini. Partus + Abortus tidak boleh melebihi Gravid.</div></div>
                            <div class="col-md-6">
                              <label class="form-label" for="jenis_persalinan">Jenis Persalinan <span class="text-danger">*</span></label>
                              <input type="text" id="jenis_persalinan" class="form-control" list="rpJenis" maxlength="100" placeholder="Pilih atau ketik">
                              <datalist id="rpJenis"><?php foreach ($jenis as $j): ?><option value="<?= $h($j) ?>"><?php endforeach; ?></datalist>
                              <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                              <label class="form-label" for="tarif_paket">Besaran Tarif Paket</label>
                              <div class="input-group"><span class="input-group-text">Rp</span><input type="text" id="tarif_paket" class="form-control" inputmode="numeric" placeholder="0"></div>
                              <div class="invalid-feedback d-block" id="tarifErr"></div>
                            </div>
                          </div>
                        </div>

                        <div class="bag">
                          <div class="bag-h"><i class="ti ti-signature"></i>Tanda Tangan Pasien <span class="small fw-normal text-dark ms-1">(yang membuat pernyataan)</span></div>
                          <div class="bag-b ttd-box" id="ttdBox"></div>
                        </div>
                      </fieldset>

                      <div class="rp-bar">
                        <span class="ket" id="rpKet"></span>
                        <button type="submit" id="openModal" class="btn btn-primary" disabled><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                      </div>
                    </form>
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
  <div class="modal fade" id="ttdModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="ti ti-signature me-1"></i>Tanda Tangan Pasien</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="kanvas"><canvas id="ttdPad"></canvas><div class="garis"></div><div class="petunjuk" id="ttdPetunjuk"><i class="ti ti-writing me-1"></i>Tanda tangan di sini</div></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-danger me-auto" id="ttdBersih"><i class="ti ti-eraser me-1"></i>Hapus goresan</button>
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="ttdPakai"><i class="ti ti-check me-1"></i>Gunakan</button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php require 'library.php'; ?>
  <?php if ($ada): ?>
  <script>
    (function() {
      'use strict';
      const NO = <?= json_encode($no) ?>;
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
      const form = $id('rpForm');
      let BATAL = false, TTD = null, ttdBaru = null, hapusTtd = false, berubah = false;

      const rupiah = s => { const n = String(s ?? '').replace(/\D/g, ''); return n ? Number(n).toLocaleString('id-ID') : ''; };
      function gpa() {
        const g = $id('gravid').value, p = $id('partus').value, a = $id('abortus').value;
        $id('gpaChip').textContent = `G${g || '–'} P${p || '–'} A${a || '–'}`;
      }

      function muat() {
        $.ajax({ url: 'controller/ranap/getFormRekapPersalinan.php', data: { no: NO }, dataType: 'json', cache: false })
          .done(res => {
            const p = res.pasien || {}, s = res.persalinan, awal = res.awal || {};
            BATAL = !!res.batal;
            $id('patient_name').value = p.nama_pasien || '';
            $id('patient_gender').value = p.jk || '';
            $id('usia').value = p.usia || '';
            $id('doctor_name').value = p.doctor_name || '';
            const sumber = s || awal;
            ['gravid', 'partus', 'abortus'].forEach(f => $id(f).value = sumber[f] ?? '');
            $id('jenis_persalinan').value = s ? (s.jenis_persalinan || '') : '';
            $id('tarif_paket').value = s ? rupiah(s.tarif_paket) : '';
            TTD = s && s.ttd ? s.ttd : null;
            ttdBaru = null;
            hapusTtd = false;
            renderTtd();
            gpa();
            const st = $id('rpStatus');
            st.className = 'st ' + (s ? 'st-ok' : 'st-baru');
            st.textContent = s ? 'Tersimpan' : 'Belum disimpan';
            $id('rpKet').innerHTML = s ? '<i class="ti ti-circle-check text-success me-1"></i>Data tersimpan.'
              : (awal.gravid ? '<i class="ti ti-info-circle me-1"></i>G/P/A diisi dari catatan ANC terakhir — periksa lalu Simpan.' : '<i class="ti ti-info-circle me-1"></i>Belum ada data — isi lalu Simpan.');
            $id('rpBatal').classList.toggle('d-none', !BATAL);
            $id('rpFieldset').disabled = BATAL;
            $id('openModal').disabled = BATAL;
            berubah = false;
          })
          .fail(x => form.insertAdjacentHTML('beforebegin', `<div class="alert alert-danger"><i class="ti ti-alert-triangle me-1"></i>${esc((x.responseJSON && x.responseJSON.message) || 'Gagal memuat data.')}</div>`));
      }

      function renderTtd() {
        const src = ttdBaru || (!hapusTtd && TTD);
        $id('ttdBox').innerHTML = (src ? `<img class="ttd-img" src="${esc(src)}" alt="Tanda tangan pasien">` : '<div class="ttd-kosong">Belum ada tanda tangan</div>')
          + `<div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-sm btn-outline-dark" data-ttd><i class="ti ti-signature me-1"></i>${src ? 'Tanda tangan ulang' : 'Tanda tangan pasien'}</button>
              ${src ? '<button type="button" class="btn btn-sm btn-outline-danger" data-ttd-hapus><i class="ti ti-trash me-1"></i>Hapus</button>' : ''}
            </div>
            ${ttdBaru || (hapusTtd && TTD) ? '<div class="bantu w-100"><i class="ti ti-alert-circle me-1"></i>Perubahan tanda tangan tersimpan setelah klik Simpan.</div>' : ''}`;
      }
      $id('ttdBox').addEventListener('click', e => {
        if (e.target.closest('[data-ttd]')) bootstrap.Modal.getOrCreateInstance($id('ttdModal')).show();
        if (e.target.closest('[data-ttd-hapus]')) { ttdBaru = null; hapusTtd = true; berubah = true; renderTtd(); }
      });

      /* ===== Kanvas tanda tangan ===== */
      const cv = $id('ttdPad'), ctx = cv.getContext('2d');
      let gambar = false, ada = false, titik = null;
      function siap() {
        const r = Math.max(window.devicePixelRatio || 1, 1), b = cv.getBoundingClientRect();
        cv.width = Math.round(b.width * r);
        cv.height = Math.round(b.height * r);
        ctx.setTransform(r, 0, 0, r, 0, 0);
        Object.assign(ctx, { lineWidth: 2.4, lineCap: 'round', lineJoin: 'round', strokeStyle: '#0f172a', fillStyle: '#0f172a' });
        ada = false;
        $id('ttdPetunjuk').classList.remove('d-none');
      }
      const pos = e => { const b = cv.getBoundingClientRect(); return { x: e.clientX - b.left, y: e.clientY - b.top }; };
      cv.addEventListener('pointerdown', e => {
        e.preventDefault();
        cv.setPointerCapture(e.pointerId);
        gambar = true;
        titik = pos(e);
        ctx.beginPath();
        ctx.arc(titik.x, titik.y, 1.2, 0, Math.PI * 2);
        ctx.fill();
        if (!ada) { ada = true; $id('ttdPetunjuk').classList.add('d-none'); }
      });
      cv.addEventListener('pointermove', e => {
        if (!gambar) return;
        const p = pos(e);
        ctx.beginPath();
        ctx.moveTo(titik.x, titik.y);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        titik = p;
      });
      ['pointerup', 'pointercancel'].forEach(ev => cv.addEventListener(ev, () => { gambar = false; }));
      $id('ttdModal').addEventListener('shown.bs.modal', siap);
      $id('ttdBersih').addEventListener('click', siap);
      function png() {
        const w = cv.width, hh = cv.height, d = ctx.getImageData(0, 0, w, hh).data;
        let x0 = w, y0 = hh, x1 = -1, y1 = -1;
        for (let y = 0; y < hh; y++) for (let x = 0; x < w; x++) if (d[(y * w + x) * 4 + 3]) { if (x < x0) x0 = x; if (x > x1) x1 = x; if (y < y0) y0 = y; if (y > y1) y1 = y; }
        if (x1 < 0) return null;
        const pad = 14, sx = Math.max(0, x0 - pad), sy = Math.max(0, y0 - pad), sw = Math.min(w, x1 + pad) - sx, sh = Math.min(hh, y1 + pad) - sy;
        const c = document.createElement('canvas');
        c.width = sw;
        c.height = sh;
        const k = c.getContext('2d');
        k.fillStyle = '#fff';
        k.fillRect(0, 0, sw, sh);
        k.drawImage(cv, sx, sy, sw, sh, 0, 0, sw, sh);
        return c.toDataURL('image/png');
      }
      $id('ttdPakai').addEventListener('click', () => {
        const img = ada ? png() : null;
        if (!img) return Swal.fire('Tanda tangan kosong', 'Minta pasien menandatangani di kotak terlebih dahulu.', 'warning');
        ttdBaru = img;
        hapusTtd = false;
        berubah = true;
        renderTtd();
        bootstrap.Modal.getInstance($id('ttdModal')).hide();
      });

      /* ===== Form ===== */
      ['gravid', 'partus', 'abortus'].forEach(f => $id(f).addEventListener('input', gpa));
      $id('tarif_paket').addEventListener('input', function() { this.value = rupiah(this.value); });
      form.addEventListener('input', () => { berubah = true; });
      window.addEventListener('beforeunload', e => { if (berubah) { e.preventDefault(); e.returnValue = ''; } });

      function bersih() { form.querySelectorAll('.is-invalid').forEach(i => i.classList.remove('is-invalid')); $id('tarifErr').textContent = ''; }
      function tandai(f, pesan) {
        const el = $id(f);
        if (!el) return false;
        el.classList.add('is-invalid');
        const fb = f === 'tarif_paket' ? $id('tarifErr') : el.parentElement.querySelector('.invalid-feedback');
        if (fb) fb.textContent = pesan;
        el.focus();
        return true;
      }

      form.addEventListener('submit', async e => {
        e.preventDefault();
        if (BATAL) return;
        bersih();
        const n = f => $id(f).value.trim();
        for (const [f, lbl] of [['gravid', 'Gravid'], ['partus', 'Partus'], ['abortus', 'Abortus']]) {
          if (n(f) !== '' && !/^\d{1,2}$/.test(n(f))) return tandai(f, lbl + ' harus angka.');
        }
        if (!n('gravid') || +n('gravid') < 1) return tandai('gravid', 'Gravid wajib diisi (minimal 1).');
        if ((+n('partus') || 0) + (+n('abortus') || 0) > +n('gravid')) return tandai('partus', 'Partus + Abortus melebihi Gravid.');
        if (!n('jenis_persalinan')) return tandai('jenis_persalinan', 'Jenis persalinan wajib diisi.');

        const data = { visit_ID: NO, gravid: n('gravid'), partus: n('partus'), abortus: n('abortus'), jenis_persalinan: n('jenis_persalinan'), tarif_paket: n('tarif_paket').replace(/\D/g, '') };
        if (ttdBaru) data.ttd = ttdBaru;
        else if (hapusTtd) data.hapus_ttd = 1;
        const btn = $id('openModal');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const r = await fetch('controller/ranap/saveFormRekapPersalinan.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
          const res = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
          if (res.status !== 'success') {
            if (!(res.field && tandai(res.field, res.message))) Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
            return;
          }
          berubah = false;
          toast(res.message || 'Tersimpan.');
          muat();
        } catch (err) {
          Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
        } finally {
          btn.disabled = BATAL;
          btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan';
        }
      });

      muat();
    })();
  </script>
  <?php endif; ?>
</body>

</html>
