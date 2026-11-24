<?php
/**
 * Dokumen identitas pasien (KTP, KK, kartu BPJS) — tersimpan di ms_patient, berlaku untuk semua kunjungan.
 * API: controller/master/getPatientDocs.php (baca), uploadPatient.php (unggah/ganti), deletePatientFile.php (hapus).
 * Perbaikan: query kunjungan dirangkai dari URL (SQL injection); $_GET no/rm/rme tanpa default; URL file memakai
 * window.location.origin + "/uploads" (salah bila aplikasi di sub-folder); nama file disisipkan ke onclick tanpa escape
 * (XSS); dokumen hanya bisa dilihat — unggah/ganti/hapus harus lewat halaman detail pasien; gagal muat tanpa pesan;
 * tombol di dalam <a>; kunjungan faskes lain tampil kosong tanpa keterangan.
 */
$title = 'Dokumen Pasien';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$st = $koneksi->prepare("SELECT id_patient FROM pasien_visit WHERE visit_ID = ? AND id_customer = ? LIMIT 1");
$st->bind_param('si', $no, $idc);
$st->execute();
$id_patient = (string) ($st->get_result()->fetch_row()[0] ?? '');
$st->close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .dp .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
    .dp .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #e0f2fe; color: #075985; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .dp .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .dp .kepala small { color: #475569; }
    .dp .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    @media (max-width: 991.98px) { .dp .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) { .dp .grid { grid-template-columns: 1fr; } }
    .dp .dok { border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; background: #fff; display: flex; flex-direction: column; }
    .dp .dok-h { display: flex; align-items: center; gap: 8px; padding: 10px 12px; border-bottom: 1px solid #eef2f6; }
    .dp .dok-h b { color: #0f172a; }
    .dp .dok-h .st { margin-left: auto; font-size: .7rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; white-space: nowrap; }
    .dp .st-ok { background: #dcfce7; color: #14532d; }
    .dp .st-belum { background: #fee2e2; color: #991b1b; }
    .dp .media { aspect-ratio: 16 / 10; background: #f8fafc; display: flex; align-items: center; justify-content: center; position: relative; }
    .dp .media img { width: 100%; height: 100%; object-fit: contain; cursor: zoom-in; }
    .dp .media .drop { position: absolute; inset: 10px; border: 2px dashed #cbd5e1; border-radius: 10px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #475569; cursor: pointer; text-align: center; font-size: .85rem; padding: 8px; }
    .dp .media .drop.over { border-color: #2563eb; background: #eff6ff; }
    .dp .media .drop i { font-size: 1.9rem; color: #64748b; }
    .dp .media .muat { position: absolute; inset: 0; background: rgba(255, 255, 255, .8); display: flex; align-items: center; justify-content: center; color: #334155; font-size: .85rem; }
    .dp .media .rusak { color: #94a3b8; font-size: .82rem; }
    .dp .dok-f { display: flex; gap: 6px; padding: 10px 12px; border-top: 1px solid #eef2f6; }
    .dp .dok-f .btn { font-weight: 600; }
    .dp .lain { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 16px; }
    .dp .lain .ic2 { width: 38px; height: 38px; border-radius: 10px; background: #f1f5f9; color: #334155; display: inline-flex; align-items: center; justify-content: center; font-size: 1.15rem; }
    #lihatModal .modal-content { background: #0f172a; color: #fff; }
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
                <div class="card-body p-4 dp">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-id"></i></span>
                    <div class="flex-grow-1">
                      <h5>Dokumen Pasien <span class="small text-muted fw-normal" id="dpJumlah"></span></h5>
                      <small>Dokumen identitas pasien — tersimpan di data pasien dan berlaku untuk semua kunjungan.</small>
                    </div>
                    <?php if ($id_patient !== ''): ?>
                      <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_pasien?<?= $h(http_build_query(['id_patient' => $id_patient, 'no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak dokumen</a>
                    <?php endif; ?>
                  </div>

                  <?php if ($id_patient === ''): ?>
                    <div class="alert alert-danger mb-0"><i class="ti ti-alert-triangle me-1"></i>Kunjungan tidak ditemukan untuk faskes ini.</div>
                  <?php else: ?>
                    <div class="grid" id="dpGrid">
                      <?php foreach (['ktp' => ['KTP', 'ti-id'], 'kk' => ['Kartu Keluarga', 'ti-users'], 'bpjs' => ['Kartu BPJS', 'ti-id-badge-2']] as $key => [$label, $icon]): ?>
                        <div class="dok" data-dok="<?= $key ?>" data-label="<?= $h($label) ?>">
                          <div class="dok-h"><i class="ti <?= $icon ?> text-muted"></i><b><?= $h($label) ?></b><span class="st"></span></div>
                          <div class="media"><div class="muat"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</div></div>
                          <div class="dok-f"></div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <input type="file" id="dpFile" accept="image/jpeg,image/png" class="d-none">
                    <div class="small text-muted mt-2">Format JPG / PNG. Foto besar otomatis diperkecil sebelum diunggah (batas server 2 MB).</div>

                    <div class="lain">
                      <span class="ic2"><i class="ti ti-file-text"></i></span>
                      <div class="flex-grow-1">
                        <div class="fw-semibold text-dark">Surat Pengantar Rawat Inap</div>
                        <div class="small text-muted">Dicetak dari data kunjungan ini.</div>
                      </div>
                      <a class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener" href="module/admin/print/formulir_pengantar_ranap?<?= $h(http_build_query(['no' => $no, 'rm' => $rm, 'rme' => 'c'])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
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

  <!-- MODAL LIHAT -->
  <div class="modal fade" id="lihatModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <div class="fw-semibold" id="lihatJudul"></div>
          <a class="btn btn-sm btn-light ms-auto me-2" id="lihatAsli" target="_blank" rel="noopener"><i class="ti ti-external-link me-1"></i>Buka asli</a>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body"><img id="lihatImg" alt="Dokumen pasien"></div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <?php if ($id_patient !== ''): ?>
  <script>
    (function() {
      'use strict';
      const ID_PASIEN = <?= json_encode($id_patient) ?>;
      const DIR = 'uploads/patient/';
      const KOLOM = { ktp: 'patient_ktp_file', kk: 'patient_kk_file', bpjs: 'patient_bpjs_file' };
      const MAKS = 2 * 1024 * 1024;
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
      const urlFile = f => DIR + encodeURIComponent(f);   // file pengganti selalu bernama baru, cache aman
      let FILES = {}, sibuk = {}, target = null;

      const kartu = k => document.querySelector(`.dok[data-dok="${k}"]`);

      function muat() {
        $.ajax({ url: 'controller/master/getPatientDocs.php', data: { id_patient: ID_PASIEN }, dataType: 'json', cache: false })
          .done(res => {
            if (!res || res.status !== 'success') return gagalMuat((res && res.message) || 'Data pasien tidak ditemukan.');
            FILES = res.files || {};
            Object.keys(KOLOM).forEach(render);
            const n = Object.values(KOLOM).filter(c => ada(FILES[c])).length;
            $id('dpJumlah').textContent = `· ${n} dari 3 tersedia`;
          })
          .fail(x => gagalMuat((x.responseJSON && x.responseJSON.message) || 'Gagal memuat dokumen.'));
      }
      function gagalMuat(pesan) {
        document.querySelectorAll('.dok .media').forEach(m => m.innerHTML = `<span class="text-danger small px-3 text-center"><i class="ti ti-alert-triangle me-1"></i>${esc(pesan)}</span>`);
      }
      const ada = f => f && f !== 'null';

      function render(k) {
        const el = kartu(k), f = FILES[KOLOM[k]], label = el.dataset.label;
        const st = el.querySelector('.st');
        st.className = 'st ' + (ada(f) ? 'st-ok' : 'st-belum');
        st.textContent = ada(f) ? 'Tersedia' : 'Belum ada';
        const media = el.querySelector('.media'), foot = el.querySelector('.dok-f');
        if (ada(f)) {
          media.innerHTML = `<img src="${esc(urlFile(f))}" alt="${esc(label)}" data-lihat="${k}">`;
          media.querySelector('img').addEventListener('error', function() {
            this.outerHTML = '<span class="rusak"><i class="ti ti-photo-off me-1"></i>File tidak ditemukan di server</span>';
          }, { once: true });
          foot.innerHTML = `
            <button type="button" class="btn btn-sm btn-primary" data-lihat="${k}"><i class="ti ti-eye me-1"></i>Lihat</button>
            <button type="button" class="btn btn-sm btn-outline-dark" data-unggah="${k}"><i class="ti ti-replace me-1"></i>Ganti</button>
            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" data-hapus="${k}" title="Hapus"><i class="ti ti-trash"></i></button>`;
        } else {
          media.innerHTML = `<div class="drop" data-unggah="${k}" tabindex="0" role="button"><i class="ti ti-cloud-upload"></i><div class="fw-semibold mt-1">Unggah ${esc(label)}</div><div class="small">Klik atau seret foto ke sini</div></div>`;
          foot.innerHTML = `<button type="button" class="btn btn-sm btn-primary" data-unggah="${k}"><i class="ti ti-upload me-1"></i>Unggah</button>`;
        }
        if (sibuk[k]) media.insertAdjacentHTML('beforeend', `<div class="muat"><span class="spinner-border spinner-border-sm me-2"></span>${esc(sibuk[k])}</div>`);
      }

      // perkecil agar di bawah batas 2 MB server (sisi terpanjang maks. 2000 px, JPEG)
      function perkecil(file) {
        return new Promise(resolve => {
          const img = new Image(), url = URL.createObjectURL(file);
          img.onload = () => {
            URL.revokeObjectURL(url);
            const skala = Math.min(1, 2000 / Math.max(img.naturalWidth, img.naturalHeight));
            if (skala === 1 && file.size <= 1.2 * 1024 * 1024) return resolve(file);
            const c = document.createElement('canvas');
            c.width = Math.round(img.naturalWidth * skala);
            c.height = Math.round(img.naturalHeight * skala);
            const ctx = c.getContext('2d');
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, c.width, c.height);
            ctx.drawImage(img, 0, 0, c.width, c.height);
            c.toBlob(b => resolve(b && b.size < file.size ? new File([b], 'dokumen.jpg', { type: 'image/jpeg' }) : file), 'image/jpeg', 0.85);
          };
          img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
          img.src = url;
        });
      }

      async function unggah(k, file) {
        if (!file) return;
        if (!/^image\/(jpeg|png)$/.test(file.type)) return Swal.fire('Format tidak didukung', 'Gunakan foto JPG atau PNG.', 'warning');
        sibuk[k] = 'Mengunggah…';
        render(k);
        try {
          const f = await perkecil(file);
          if (f.size > MAKS) throw new Error('Ukuran foto masih di atas 2 MB setelah diperkecil.');
          const fd = new FormData();
          fd.append('id_patient', ID_PASIEN);
          fd.append(k, f, f.name || 'dokumen.jpg');
          const r = await fetch('controller/master/uploadPatient.php', { method: 'POST', body: fd });
          const res = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
          if (res.status !== 'success') throw new Error(res.message || 'Gagal mengunggah.');
          toast(kartu(k).dataset.label + ' tersimpan.');
        } catch (e) {
          Swal.fire('Gagal', e.message, 'error');
        } finally {
          delete sibuk[k];
          muat();
        }
      }

      function hapus(k) {
        const label = kartu(k).dataset.label;
        Swal.fire({
          icon: 'warning', title: `Hapus ${label}?`, text: 'File dihapus dari data pasien (berlaku untuk semua kunjungan).',
          showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true
        }).then(x => {
          if (!x.isConfirmed) return;
          sibuk[k] = 'Menghapus…';
          render(k);
          $.post('controller/master/deletePatientFile.php', { id_patient: ID_PASIEN, field: k }, null, 'json')
            .done(res => res && res.status === 'success' ? toast(label + ' dihapus.') : Swal.fire('Gagal', (res && res.message) || 'Gagal menghapus.', 'error'))
            .fail(() => Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'))
            .always(() => { delete sibuk[k]; muat(); });
        });
      }

      const grid = $id('dpGrid');
      grid.addEventListener('click', e => {
        const l = e.target.closest('[data-lihat]'), u = e.target.closest('[data-unggah]'), d = e.target.closest('[data-hapus]');
        if (l) {
          const k = l.dataset.lihat, f = FILES[KOLOM[k]];
          $id('lihatImg').src = urlFile(f);
          $id('lihatAsli').href = urlFile(f);
          $id('lihatJudul').textContent = kartu(k).dataset.label;
          bootstrap.Modal.getOrCreateInstance($id('lihatModal')).show();
        }
        if (u && !sibuk[u.dataset.unggah]) { target = u.dataset.unggah; $id('dpFile').click(); }
        if (d && !sibuk[d.dataset.hapus]) hapus(d.dataset.hapus);
      });
      grid.addEventListener('keydown', e => { const u = e.target.closest('.drop[data-unggah]'); if (u && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); target = u.dataset.unggah; $id('dpFile').click(); } });
      $id('dpFile').addEventListener('change', function() { const f = this.files[0]; this.value = ''; if (target) unggah(target, f); });
      ['dragenter', 'dragover'].forEach(ev => grid.addEventListener(ev, e => { const z = e.target.closest('.drop'); if (z) { e.preventDefault(); z.classList.add('over'); } }));
      ['dragleave', 'drop'].forEach(ev => grid.addEventListener(ev, e => { const z = e.target.closest('.drop'); if (z) { e.preventDefault(); z.classList.remove('over'); } }));
      grid.addEventListener('drop', e => { const z = e.target.closest('.drop[data-unggah]'); if (z && e.dataTransfer.files[0]) unggah(z.dataset.unggah, e.dataTransfer.files[0]); });

      muat();
    })();
  </script>
  <?php endif; ?>
</body>

</html>
