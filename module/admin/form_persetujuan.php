<?php
$title = 'Persetujuan Rawat Inap';
require '../../controller/view.php';

/*
 * Surat persetujuan rawat inap (opname) + tanda tangan pasien / keluarga.
 * Disimpan: pasien_visit.signature_opname, opname_keluarga_name, opname_umur, opname_gender, opname_hubungan_pasien
 * Dicetak : module/admin/print/formulir_surat_persetujuan.php
 *
 * Dulu: visit_ID dari URL masuk langsung ke SQL; data yang sudah disimpan tidak dimuat ulang (tidak bisa diedit);
 * tanda tangan tidak bisa diulang; kanvas "height:400" tanpa px -> coretan bergeser; alert() polos.
 */
$no = (string) ($_GET['no'] ?? '');
$rm = (string) ($_GET['rm'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$kunj = null;
if ($no !== '' && $idc > 0) {
  $st = $koneksi->prepare("SELECT pv.id_visit, pv.visit_ID, pv.visit_date, pv.signature_opname, pv.opname_keluarga_name, pv.opname_umur,
      pv.opname_gender, pv.opname_hubungan_pasien, mp.patient_name, mp.patient_gender, mp.patient_datebirth
    FROM pasien_visit pv LEFT JOIN ms_patient mp ON mp.id_patient = pv.id_patient
    WHERE pv.visit_ID = ? AND pv.id_customer = ? LIMIT 1");
  $st->bind_param('si', $no, $idc);
  $st->execute();
  $kunj = $st->get_result()->fetch_assoc() ?: null;
  $st->close();
}
$ttdFile = $kunj ? basename((string) $kunj['signature_opname']) : '';
$ttdUrl = ($ttdFile !== '' && is_file(__DIR__ . '/../../uploads/ttd/' . $ttdFile)) ? 'uploads/ttd/' . rawurlencode($ttdFile) : '';

// data pasien untuk pilihan "Diri saya" (pasien menandatangani sendiri)
$umurPasien = '';
if ($kunj && preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $kunj['patient_datebirth']) && substr((string) $kunj['patient_datebirth'], 0, 4) > '1900') {
  $lahir = new DateTime(substr($kunj['patient_datebirth'], 0, 10));
  $pada = new DateTime(substr((string) ($kunj['visit_date'] ?: date('Y-m-d')), 0, 10));
  $umurPasien = (string) $lahir->diff($pada)->y;
}
$jkPasien = '';
$g = strtoupper(substr(trim((string) ($kunj['patient_gender'] ?? '')), 0, 1));
if ($g === 'L') $jkPasien = 'Laki-laki';
elseif ($g === 'P') $jkPasien = 'Perempuan';

$awal = [
  'id_visit' => $kunj ? (int) $kunj['id_visit'] : 0,
  'nama' => (string) ($kunj['opname_keluarga_name'] ?? ''),
  'umur' => preg_match('/\d{1,3}/', (string) ($kunj['opname_umur'] ?? ''), $m) ? $m[0] : '',
  'jk' => (string) ($kunj['opname_gender'] ?? ''),
  'hubungan' => (string) ($kunj['opname_hubungan_pasien'] ?? ''),
  'ttd' => $ttdUrl,
  'ttd_tercatat' => $ttdFile !== '',
  'pasien' => ['nama' => (string) ($kunj['patient_name'] ?? ''), 'umur' => $umurPasien, 'jk' => $jkPasien],
];
$printUrl = 'module/admin/print/formulir_surat_persetujuan?no=' . urlencode($no) . '&rm=' . urlencode($rm);
function fpE($v): string
{
  return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .fp-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .fp-hub { display: grid; grid-template-columns: repeat(auto-fill, minmax(118px, 1fr)); gap: 6px; }
    .fp-hub input { display: none; }
    .fp-hub label { border: 1px solid #e5eaef; border-radius: 10px; padding: 8px 10px; font-size: .82rem; cursor: pointer; text-align: center; background: #fff; transition: all .12s; }
    .fp-hub input:checked + label { border-color: #5d87ff; background: #ecf2ff; color: #5d87ff; font-weight: 600; }
    .fp-hub input:disabled + label { cursor: default; opacity: .7; }
    .fp-jk .btn { flex: 1; }
    .fp-surat { background: #fff; border: 1px solid #e5eaef; border-radius: 12px; padding: 22px 26px; font-family: "Times New Roman", serif; font-size: .95rem; line-height: 1.65; color: #1f2937; }
    .fp-surat h6 { font-family: inherit; font-weight: 700; text-align: center; text-decoration: underline; margin-bottom: 14px; }
    .fp-surat table td { padding: 1px 0; vertical-align: top; }
    .fp-surat table td:first-child { width: 150px; }
    .fp-isi { font-weight: 700; }
    .fp-isi.kosong { color: #cbd5e1; font-weight: 400; font-style: italic; }
    .fp-ttd { margin-left: auto; width: 230px; text-align: center; margin-top: 16px; }
    .fp-ttd-box { height: 96px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #1f2937; }
    .fp-ttd-box img { max-height: 90px; max-width: 100%; }
    .ttd-pad-wrap { position: relative; border: 1px solid #d6dde6; border-radius: 12px; background: #fff; overflow: hidden; touch-action: none; }
    #signaturePad { display: block; width: 100%; height: 280px; cursor: crosshair; }
    .ttd-pad-line { position: absolute; left: 8%; right: 8%; bottom: 56px; border-bottom: 1.5px dashed #cbd5e1; pointer-events: none; }
    .ttd-pad-hint { position: absolute; left: 8%; bottom: 32px; font-size: .72rem; color: #94a3b8; pointer-events: none; }
    .fp-ringkas { background: #f8fafc; border-radius: 10px; padding: 8px 12px; font-size: .82rem; }
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
          $rme = $_GET['rme'] ?? '';
          if ($rme === 'a') include 'menu_rme.php';
          elseif ($rme === 'b') include 'menu_rmeb.php';
          elseif ($rme === 'c') include 'menu_rme_inap.php';
          ?>

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="fp-icon bg-danger-subtle text-danger"><i class="fas fa-file-signature"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Persetujuan Rawat Inap (Opname)</h5>
                  <div class="text-muted small">Pernyataan persetujuan tindakan opname &amp; perawatan oleh pasien / keluarga · <?= fpE($no) ?></div>
                </div>
                <span class="badge px-3 py-2" id="fpStatus"></span>
                <a class="btn btn-outline-primary btn-sm" href="<?= fpE($printUrl) ?>" target="_blank" rel="noopener" id="fpCetak"><i class="fas fa-print me-1"></i>Cetak</a>
              </div>
            </div>
          </div>

          <?php require 'card-pasien.php'; ?>

          <?php if (!$kunj): ?>
            <div class="card border-0 shadow-sm"><div class="card-body text-center py-5 text-muted">
              <i class="fas fa-circle-exclamation fa-2x text-danger d-block mb-2"></i>Kunjungan tidak ditemukan di faskes ini.
            </div></div>
          <?php else: ?>
            <div class="row g-3">
              <!-- FORM -->
              <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                  <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                      <h6 class="fw-semibold mb-0">Yang membuat pernyataan</h6>
                      <button type="button" class="btn btn-sm btn-light ms-auto d-none" id="fpEdit"><i class="fas fa-pen me-1"></i>Ubah data</button>
                    </div>
                    <form id="fpForm" novalidate autocomplete="off">
                      <label class="form-label small fw-semibold">Hubungan dengan pasien <span class="text-danger">*</span></label>
                      <div class="fp-hub mb-2" id="fpHub">
                        <?php foreach (['Diri Saya' => 'Pasien sendiri', 'Suami' => 'Suami', 'Istri' => 'Istri', 'Anak' => 'Anak', 'Ayah' => 'Ayah', 'Ibu' => 'Ibu', 'Keluarga Lain' => 'Keluarga lain'] as $v => $l): ?>
                          <input type="radio" name="hubungan" id="hub_<?= md5($v) ?>" value="<?= fpE($v) ?>"><label for="hub_<?= md5($v) ?>"><?= fpE($l) ?></label>
                        <?php endforeach; ?>
                      </div>
                      <div class="alert alert-warning small py-2 d-none" id="fpAnak"><i class="fas fa-child me-1"></i>Pasien berusia <?= fpE($umurPasien) ?> tahun. Persetujuan sebaiknya ditandatangani <b>orang tua / wali</b>.</div>
                      <div class="mb-3 mt-3">
                        <label class="form-label small fw-semibold" for="nama_pasien">Nama lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_pasien" maxlength="150" placeholder="Nama yang menandatangani">
                        <div class="invalid-feedback">Nama wajib diisi.</div>
                      </div>
                      <div class="row g-2 mb-1">
                        <div class="col-4">
                          <label class="form-label small fw-semibold" for="umur_pasien">Umur <span class="text-danger">*</span></label>
                          <div class="input-group">
                            <input type="number" inputmode="numeric" min="1" max="130" class="form-control" id="umur_pasien">
                            <span class="input-group-text">th</span>
                          </div>
                        </div>
                        <div class="col-8">
                          <label class="form-label small fw-semibold">Jenis kelamin <span class="text-danger">*</span></label>
                          <div class="btn-group w-100 fp-jk" role="group">
                            <input type="radio" class="btn-check" name="jk" id="jk_l" value="Laki-laki"><label class="btn btn-outline-primary" for="jk_l">Laki-laki</label>
                            <input type="radio" class="btn-check" name="jk" id="jk_p" value="Perempuan"><label class="btn btn-outline-primary" for="jk_p">Perempuan</label>
                          </div>
                        </div>
                      </div>
                      <div class="small text-danger d-none mt-2" id="fpError"></div>
                    </form>

                    <hr>
                    <div id="fpAksi" class="d-flex flex-wrap gap-2"></div>
                  </div>
                </div>
              </div>

              <!-- PRATINJAU SURAT -->
              <div class="col-lg-7">
                <div class="fp-surat h-100">
                  <h6>SURAT PERSETUJUAN RAWAT INAP</h6>
                  <p class="mb-2">Saya yang bertanda tangan di bawah ini:</p>
                  <table class="mb-2">
                    <tr><td>Nama</td><td>: <span class="fp-isi" data-p="nama"></span></td></tr>
                    <tr><td>Umur</td><td>: <span class="fp-isi" data-p="umur"></span></td></tr>
                    <tr><td>Jenis kelamin</td><td>: <span class="fp-isi" data-p="jk"></span></td></tr>
                    <tr><td>Hubungan</td><td>: <span class="fp-isi" data-p="hubungan"></span></td></tr>
                  </table>
                  <p class="mb-2">Dengan ini menyatakan sesungguhnya telah memberikan <b>persetujuan</b> untuk dilakukan tindakan medis berupa <b>opname dan perawatan</b> terhadap pasien <b><?= fpE($kunj['patient_name']) ?></b>.</p>
                  <p class="mb-0">Saya telah diberikan informasi dan penjelasan mengenai tindakan medis yang akan dilakukan, dan telah memahami sepenuhnya informasi serta penjelasan yang diberikan oleh dokter.</p>
                  <div class="fp-ttd">
                    <div class="small">Yang membuat pernyataan</div>
                    <div class="fp-ttd-box" id="fpTtdBox"></div>
                    <div class="fw-semibold mt-1" id="fpTtdNama">&nbsp;</div>
                  </div>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL TANDA TANGAN -->
  <div class="modal fade" id="ttdModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0">
            <h5 class="modal-title mb-0"><i class="fas fa-signature text-primary me-2"></i>Tanda tangan persetujuan</h5>
            <div class="small text-muted text-truncate" id="ttdSub">&nbsp;</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="fp-ringkas mb-2" id="ttdRingkas"></div>
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="small text-muted">Minta penanda tangan menandatangani di area bawah.</span>
            <div class="ms-auto d-flex gap-1">
              <button type="button" class="btn btn-light btn-sm" id="ttdUndo" disabled><i class="fas fa-undo me-1"></i>Urungkan</button>
              <button type="button" class="btn btn-light btn-sm" id="clearSignature" disabled><i class="fas fa-eraser me-1"></i>Bersihkan</button>
            </div>
          </div>
          <div class="ttd-pad-wrap">
            <canvas id="signaturePad"></canvas>
            <div class="ttd-pad-line"></div>
            <div class="ttd-pad-hint">Tanda tangan di atas garis</div>
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="saveSignature" disabled><i class="fas fa-save me-1"></i>Simpan tanda tangan</button>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <?php if ($kunj): ?>
    <script>
      (function() {
        const $id = id => document.getElementById(id);
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const API = 'controller/admisi/saveSignatureKeluarga.php';
        const S = <?= json_encode($awal, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        const LBL_HUB = { 'Diri Saya': 'Pasien sendiri', 'Keluarga Lain': 'Keluarga lain' };
        let mode = S.ttd_tercatat ? 'lihat' : 'isi';   // lihat = terkunci, isi = form aktif
        const toast = (icon, t) => Swal.fire({ toast: true, position: 'top-end', icon, title: t, showConfirmButton: false, timer: 2200 });

        /* ---------- form ---------- */
        const nilai = () => ({
          nama: $id('nama_pasien').value.trim(),
          umur: $id('umur_pasien').value.trim(),
          jk: (document.querySelector('input[name=jk]:checked') || {}).value || '',
          hubungan: (document.querySelector('input[name=hubungan]:checked') || {}).value || ''
        });
        function isi(v) {
          $id('nama_pasien').value = v.nama || '';
          $id('umur_pasien').value = v.umur || '';
          document.querySelectorAll('input[name=jk]').forEach(r => r.checked = r.value === v.jk);
          document.querySelectorAll('input[name=hubungan]').forEach(r => r.checked = r.value === v.hubungan);
          pratinjau();
        }
        function pratinjau() {
          const v = nilai();
          const t = { nama: v.nama, umur: v.umur ? v.umur + ' tahun' : '', jk: v.jk, hubungan: LBL_HUB[v.hubungan] || v.hubungan };
          document.querySelectorAll('[data-p]').forEach(el => {
            const x = t[el.dataset.p];
            el.textContent = x || '(belum diisi)';
            el.classList.toggle('kosong', !x);
          });
          $id('fpTtdNama').textContent = v.nama ? '( ' + v.nama + ' )' : ' ';
        }
        function kunci(k) {
          $id('fpForm').querySelectorAll('input').forEach(i => i.disabled = k);
        }
        function validasi() {
          const v = nilai(), err = [];
          $id('nama_pasien').classList.toggle('is-invalid', !v.nama);
          if (!v.hubungan) err.push('pilih hubungan dengan pasien');
          if (!v.nama) err.push('nama wajib diisi');
          const u = parseInt(v.umur, 10);
          $id('umur_pasien').classList.toggle('is-invalid', !(u >= 1 && u <= 130));
          if (!(u >= 1 && u <= 130)) err.push('umur 1–130 tahun');
          if (!v.jk) err.push('pilih jenis kelamin');
          $id('fpError').textContent = err.length ? 'Lengkapi: ' + err.join(', ') + '.' : '';
          $id('fpError').classList.toggle('d-none', !err.length);
          return !err.length;
        }
        ['input', 'change'].forEach(t => $id('fpForm').addEventListener(t, () => { pratinjau(); if (!$id('fpError').classList.contains('d-none')) validasi(); }));
        // "Pasien sendiri" -> isi otomatis dari data pasien
        $id('fpHub').addEventListener('change', e => {
          const anak = S.pasien.umur !== '' && +S.pasien.umur < 17;
          $id('fpAnak').classList.toggle('d-none', !(e.target.value === 'Diri Saya' && anak));
          if (e.target.value === 'Diri Saya' && S.pasien.nama && !anak) {
            $id('nama_pasien').value = S.pasien.nama;
            if (S.pasien.umur) $id('umur_pasien').value = S.pasien.umur;
            document.querySelectorAll('input[name=jk]').forEach(r => r.checked = r.value === S.pasien.jk);
            pratinjau();
          }
        });

        /* ---------- status & tombol ---------- */
        function render() {
          const ada = !!S.ttd, tercatat = S.ttd_tercatat;
          $id('fpStatus').className = 'badge px-3 py-2 ' + (tercatat ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning');
          $id('fpStatus').innerHTML = tercatat ? '<i class="fas fa-circle-check me-1"></i>Sudah ditandatangani' : '<i class="fas fa-clock me-1"></i>Belum ditandatangani';
          $id('fpTtdBox').innerHTML = ada ? `<img src="${esc(S.ttd)}?v=${Date.now()}" alt="Tanda tangan">`
            : (tercatat ? '<span class="small text-warning">File tanda tangan tidak ditemukan</span>' : '<span class="small text-muted fst-italic">belum ditandatangani</span>');
          kunci(mode === 'lihat');
          $id('fpEdit').classList.toggle('d-none', mode !== 'lihat');
          let h = '';
          if (mode === 'isi') {
            h = `<button type="button" class="btn btn-primary" id="fpTtdBtn"><i class="fas fa-signature me-1"></i>${tercatat ? 'Tanda tangan ulang' : 'Saya mengerti dan setuju — tanda tangani'}</button>`;
            if (tercatat) h += `<button type="button" class="btn btn-outline-primary" id="fpSimpanData"><i class="fas fa-save me-1"></i>Simpan data saja</button>
              <button type="button" class="btn btn-light" id="fpBatal">Batal</button>`;
          } else {
            h = `<button type="button" class="btn btn-outline-primary" id="fpUlang"><i class="fas fa-signature me-1"></i>Tanda tangan ulang</button>
              <a class="btn btn-light" href="${$id('fpCetak').getAttribute('href')}" target="_blank" rel="noopener"><i class="fas fa-print me-1"></i>Cetak surat</a>`;
          }
          $id('fpAksi').innerHTML = h;
        }
        $id('fpEdit').addEventListener('click', () => { mode = 'isi'; render(); $id('nama_pasien').focus(); });
        $id('fpAksi').addEventListener('click', e => {
          const b = e.target.closest('button');
          if (!b) return;
          if (b.id === 'fpTtdBtn' || b.id === 'fpUlang') { if (b.id === 'fpUlang') { mode = 'isi'; render(); } if (validasi()) bukaPad(); }
          else if (b.id === 'fpSimpanData') simpan(null);
          else if (b.id === 'fpBatal') { isi(S); mode = 'lihat'; $id('fpError').classList.add('d-none'); render(); }
        });

        /* ---------- simpan ---------- */
        async function simpan(image) {
          if (!validasi()) return false;
          const v = nilai();
          try {
            const r = await fetch(API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin',
              body: JSON.stringify({ id_visit: S.id_visit, image: image || '', nama: v.nama, umur: v.umur, jk: v.jk, hubungan: v.hubungan }) });
            const j = await r.json().catch(() => null);
            if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal menyimpan (HTTP ' + r.status + ').');
            Object.assign(S, v);
            if (j.file) { S.ttd = 'uploads/ttd/' + encodeURIComponent(j.file); S.ttd_tercatat = true; }
            mode = 'lihat';
            render();
            toast('success', j.message || 'Tersimpan');
            return true;
          } catch (e) {
            Swal.fire('Gagal menyimpan', e.message, 'error');
            return false;
          }
        }

        /* ---------- pad tanda tangan ---------- */
        const modal = bootstrap.Modal.getOrCreateInstance($id('ttdModal'));
        const pad = $id('signaturePad'), ctx = pad.getContext('2d');
        let goresan = [], aktifG = null;
        function ukuran() {
          const r = pad.getBoundingClientRect();
          if (!r.width) return;
          const dpr = Math.min(window.devicePixelRatio || 1, 2.5);
          pad.width = Math.round(r.width * dpr);
          pad.height = Math.round(r.height * dpr);
          ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
          gambar();
        }
        function gambar() {
          ctx.save(); ctx.setTransform(1, 0, 0, 1, 0, 0); ctx.clearRect(0, 0, pad.width, pad.height); ctx.restore();
          ctx.strokeStyle = ctx.fillStyle = '#111827';
          ctx.lineCap = ctx.lineJoin = 'round';
          goresan.forEach(g => {
            const p = g.p;
            if (p.length === 1) { ctx.beginPath(); ctx.arc(p[0][0], p[0][1], 1.5, 0, Math.PI * 2); ctx.fill(); return; }
            for (let i = 1; i < p.length; i++) {
              ctx.lineWidth = 3 * (0.6 + 0.8 * (p[i][2] ?? 0.5));
              const a = p[i - 1], b = p[i], m0 = i > 1 ? [(p[i - 2][0] + a[0]) / 2, (p[i - 2][1] + a[1]) / 2] : a;
              ctx.beginPath(); ctx.moveTo(m0[0], m0[1]); ctx.quadraticCurveTo(a[0], a[1], (a[0] + b[0]) / 2, (a[1] + b[1]) / 2); ctx.stroke();
            }
          });
          const ada = goresan.length > 0;
          $id('ttdUndo').disabled = $id('clearSignature').disabled = $id('saveSignature').disabled = !ada;
        }
        const titik = e => { const r = pad.getBoundingClientRect(); return [e.clientX - r.left, e.clientY - r.top, e.pointerType === 'pen' && e.pressure ? e.pressure : 0.5]; };
        pad.addEventListener('pointerdown', e => { e.preventDefault(); pad.setPointerCapture(e.pointerId); aktifG = { p: [titik(e)] }; goresan.push(aktifG); gambar(); });
        pad.addEventListener('pointermove', e => { if (!aktifG) return; const ev = e.getCoalescedEvents ? e.getCoalescedEvents() : []; (ev.length ? ev : [e]).forEach(x => aktifG.p.push(titik(x))); gambar(); });
        ['pointerup', 'pointercancel'].forEach(t => pad.addEventListener(t, () => { aktifG = null; }));
        $id('ttdUndo').addEventListener('click', () => { goresan.pop(); gambar(); });
        $id('clearSignature').addEventListener('click', () => { goresan = []; gambar(); });
        $id('ttdModal').addEventListener('shown.bs.modal', ukuran);
        window.addEventListener('resize', () => { if ($id('ttdModal').classList.contains('show')) ukuran(); });

        function bukaPad() {
          const v = nilai();
          $id('ttdSub').textContent = v.nama + ' · ' + (LBL_HUB[v.hubungan] || v.hubungan);
          $id('ttdRingkas').innerHTML = `Saya, <b>${esc(v.nama)}</b> (${esc(v.umur)} th, ${esc(v.jk)}), ${v.hubungan === 'Diri Saya' ? 'sebagai pasien' : 'sebagai ' + esc((LBL_HUB[v.hubungan] || v.hubungan).toLowerCase()) + ' pasien'}, menyetujui tindakan opname &amp; perawatan.`;
          goresan = [];
          modal.show();
        }
        $id('saveSignature').addEventListener('click', async function() {
          if (goresan.reduce((a, g) => a + g.p.length, 0) < 8) { Swal.fire('Tanda tangan terlalu pendek', 'Minta penanda tangan menandatangani dengan jelas.', 'warning'); return; }
          const btn = this, label = btn.innerHTML;
          btn.disabled = true;
          btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
          // latar putih agar terbaca di cetakan
          const t = document.createElement('canvas');
          t.width = pad.width; t.height = pad.height;
          const c = t.getContext('2d');
          c.fillStyle = '#fff'; c.fillRect(0, 0, t.width, t.height); c.drawImage(pad, 0, 0);
          const ok = await simpan(t.toDataURL('image/png'));
          btn.innerHTML = label;
          btn.disabled = false;
          if (ok) {
            if (modal._isTransitioning) $id('ttdModal').addEventListener('shown.bs.modal', () => modal.hide(), { once: true });
            else modal.hide();
          }
        });

        isi(S);
        render();
      })();
    </script>
  <?php endif; ?>
</body>

</html>
