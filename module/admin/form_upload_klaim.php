<?php
/**
 * Dokumen klaim kunjungan: SEP (SPP), FKPP, Informed Consent (kolom pasien_visit.file_spp / file_fkpp / file_informed,
 * API controller/visit/dokumenKlaim.php) dan dokumen klaim lainnya (controller/ranap/dokumenKlaimLainnya.php).
 * Perbaikan: status setelah upload tidak pernah diperbarui (fungsi status hanya mengenal ktp/kk/bpjs/foto) dan tombol
 * "Lihat" mengarah ke uploads/patient; handler upload terpasang ke SEMUA input file, sehingga memilih file di modal
 * "Dokumen Lainnya" ikut dikirim ke endpoint SEP/FKPP; dokumen SEP/FKPP lama (kolom sep_file/fkpp_file) tidak tampil;
 * nomor kunjungan & RM dicetak ke input tanpa escape; $_GET tanpa default; edit tidak mengisi judul (judul wajib diketik
 * ulang); hapus tanpa pesan gagal; nama file disisipkan ke HTML tanpa escape; kolom Aksi di belakang.
 */
$title = 'Dokumen Klaim';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

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
    .uk .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
    .uk .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #e0f2fe; color: #075985; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .uk .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .uk .kepala small { color: #475569; }
    .uk .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    @media (max-width: 991.98px) { .uk .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575.98px) { .uk .grid { grid-template-columns: 1fr; } }
    .uk .dok { border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; display: flex; flex-direction: column; position: relative; overflow: hidden; }
    .uk .dok-h { display: flex; align-items: center; gap: 10px; padding: 12px 14px; }
    .uk .dok-h .ic2 { width: 36px; height: 36px; border-radius: 10px; background: #fee2e2; color: #b91c1c; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .uk .dok-h b { color: #0f172a; display: block; line-height: 1.2; }
    .uk .dok-h .sub { color: #334155; font-size: .78rem; display: block; }
    .uk .dok-h .st { margin-left: auto; font-size: .7rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; white-space: nowrap; align-self: flex-start; }
    .uk .st-ok { background: #dcfce7; color: #14532d; }
    .uk .st-belum { background: #fee2e2; color: #991b1b; }
    .uk .st-hilang { background: #fef3c7; color: #78350f; }
    .uk .drop { margin: 0 14px 12px; border: 2px dashed #cbd5e1; border-radius: 10px; padding: 14px 8px; text-align: center; color: #334155; cursor: pointer; font-size: .85rem; }
    .uk .drop.over { border-color: #2563eb; background: #eff6ff; }
    .uk .drop i { font-size: 1.6rem; color: #475569; }
    .uk .info { margin: 0 14px 12px; background: #f8fafc; border-radius: 10px; padding: 10px 12px; font-size: .82rem; color: #1e293b; word-break: break-all; }
    .uk .dok-f { display: flex; gap: 6px; padding: 10px 14px; border-top: 1px solid #eef2f6; margin-top: auto; }
    .uk .dok-f .btn { font-weight: 600; }
    .uk .muat { position: absolute; inset: 0; background: rgba(255, 255, 255, .85); display: flex; align-items: center; justify-content: center; color: #1e293b; font-size: .85rem; z-index: 2; }
    .uk .bagian { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin: 26px 0 12px; }
    .uk .bagian h6 { margin: 0; font-weight: 700; color: #0f172a; }
    .uk .tbl th { color: #334155; font-weight: 600; font-size: .8rem; text-transform: uppercase; letter-spacing: .02em; background: #f8fafc; }
    .uk .tbl td { color: #0f172a; vertical-align: middle; }
    .uk .kosong { border: 1px dashed #cbd5e1; border-radius: 12px; padding: 22px; text-align: center; color: #334155; }
    #lihatModal iframe { width: 100%; height: 75vh; border: 0; background: #f1f5f9; border-radius: 8px; }
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
                <div class="card-body p-4 uk">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-file-certificate"></i></span>
                    <div class="flex-grow-1">
                      <h5>Dokumen Klaim <span class="small text-muted fw-normal" id="ukJumlah"></span></h5>
                      <small>SEP, FKPP (Formulir Klaim Pelayanan Primer), informed consent & dokumen pendukung klaim kunjungan ini.</small>
                    </div>
                  </div>

                  <?php if (!$kunj): ?>
                    <div class="alert alert-danger mb-0"><i class="ti ti-alert-triangle me-1"></i>Kunjungan tidak ditemukan untuk faskes ini.</div>
                  <?php else: ?>
                    <?php if ($batal): ?>
                      <div class="alert alert-warning"><i class="ti ti-ban me-1"></i>Kunjungan sudah dibatalkan — dokumen hanya dapat dilihat.</div>
                    <?php endif; ?>
                    <div class="grid" id="ukGrid">
                      <?php foreach (['sep' => ['SEP / SPP', 'Surat Eligibilitas Peserta'], 'fkpp' => ['FKPP', 'Formulir Klaim Pelayanan Primer'], 'informed' => ['Informed Consent', 'Persetujuan tindakan medis']] as $key => [$label, $sub]): ?>
                        <div class="dok" data-dok="<?= $key ?>" data-label="<?= $h($label) ?>">
                          <div class="dok-h">
                            <span class="ic2"><i class="ti ti-file-text"></i></span>
                            <div><b><?= $h($label) ?></b><span class="sub"><?= $h($sub) ?></span></div>
                            <span class="st"></span>
                          </div>
                          <div class="isi"></div>
                          <div class="dok-f"></div>
                          <div class="muat"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <input type="file" id="ukFile" accept="application/pdf,.pdf" class="d-none">
                    <div class="small text-dark mt-2"><i class="ti ti-info-circle me-1"></i>Format PDF, maksimal 5 MB. Pastikan dokumen jelas dan tidak blur. Mengganti file tidak menghapus file lama di server.</div>

                    <div class="bagian">
                      <span class="ic2" style="width:34px;height:34px;border-radius:10px;background:#f1f5f9;color:#334155;display:inline-flex;align-items:center;justify-content:center"><i class="ti ti-files"></i></span>
                      <h6 class="flex-grow-1">Dokumen Klaim Lainnya <span class="small text-muted fw-normal" id="dlJumlah"></span></h6>
                      <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_dokumen_lainnya?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                      <?php if (!$batal): ?>
                        <button type="button" class="btn btn-primary" id="btnTambah"><i class="ti ti-plus me-1"></i>Tambah dokumen</button>
                      <?php endif; ?>
                    </div>
                    <div id="dlWadah"><div class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</div></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL TAMBAH / UBAH DOKUMEN LAINNYA -->
  <div class="modal fade" id="programModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form id="programForm" class="modal-content" enctype="multipart/form-data" novalidate>
        <div class="modal-header">
          <h5 class="modal-title" id="dlJudulModal">Tambah Dokumen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_dokumen" id="id_dokumen">
          <div class="mb-3">
            <label for="tgl_upload" class="form-label">Tanggal <span class="text-danger">*</span></label>
            <input type="date" name="tgl_upload" class="form-control" id="tgl_upload" max="<?= date('Y-m-d') ?>" required>
            <div class="invalid-feedback"></div>
          </div>
          <div class="mb-3">
            <label for="judul_dokumen" class="form-label">Judul Dokumen <span class="text-danger">*</span></label>
            <input type="text" name="judul_dokumen" class="form-control" id="judul_dokumen" maxlength="255" placeholder="mis. Surat persetujuan tindakan medis" required>
            <div class="invalid-feedback"></div>
          </div>
          <div class="mb-1" id="fileWrapper">
            <label for="dokumen_path" class="form-label">File PDF <span class="text-danger">*</span></label>
            <input type="file" name="dokumen_path" class="form-control" id="dokumen_path" accept=".pdf,application/pdf" required>
            <div class="invalid-feedback"></div>
            <div class="form-text text-dark">PDF, maksimal 5 MB.</div>
          </div>
          <div class="small text-dark d-none" id="fileTetap"><i class="ti ti-info-circle me-1"></i>File tidak dapat diganti — hapus lalu tambah ulang bila filenya salah.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="dlSimpan"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL LIHAT PDF -->
  <div class="modal fade" id="lihatModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-truncate" id="lihatJudul">Preview Dokumen</h5>
          <a class="btn btn-sm btn-outline-dark ms-auto me-2" id="lihatAsli" target="_blank" rel="noopener"><i class="ti ti-external-link me-1"></i>Buka di tab baru</a>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body"><iframe id="lihatPdf" title="Preview dokumen"></iframe></div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <?php if ($kunj): ?>
  <script>
    (function() {
      'use strict';
      const NO = <?= json_encode($no) ?>;
      const BATAL = <?= json_encode($batal) ?>;
      const HARI_INI = <?= json_encode(date('Y-m-d')) ?>;
      const API_KLAIM = 'controller/visit/dokumenKlaim.php';
      const API_LAIN = 'controller/ranap/dokumenKlaimLainnya.php?no=' + encodeURIComponent(NO);
      const MAKS = 5 * 1024 * 1024;
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2200 });
      const pesanXhr = (x, d) => (x && x.responseJSON && x.responseJSON.message) || d;
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tgl = s => { const m = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}:\d{2}))?/.exec(s || ''); return m ? `${+m[3]} ${BLN[m[2] - 1]} ${m[1]}${m[4] ? ' ' + m[4] : ''}` : '-'; };
      const ukuran = b => b == null ? '' : b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB';

      function lihat(url, judul) {
        $id('lihatPdf').src = url;
        $id('lihatAsli').href = url;
        $id('lihatJudul').textContent = judul;
        bootstrap.Modal.getOrCreateInstance($id('lihatModal')).show();
      }
      $id('lihatModal').addEventListener('hidden.bs.modal', () => { $id('lihatPdf').src = 'about:blank'; });

      /* ================= SEP / FKPP / INFORMED ================= */
      let INFO = {}, sibuk = {}, target = null;
      const kartu = k => document.querySelector(`.dok[data-dok="${k}"]`);

      function muatKlaim() {
        $.ajax({ url: API_KLAIM, data: { no: NO }, dataType: 'json', cache: false })
          .done(res => {
            INFO = { sep: res.sep, fkpp: res.fkpp, informed: res.informed };
            ['sep', 'fkpp', 'informed'].forEach(render);
            $id('ukJumlah').textContent = `· ${['sep', 'fkpp', 'informed'].filter(k => INFO[k] && INFO[k].ada).length} dari 3 tersedia`;
          })
          .fail(x => document.querySelectorAll('.dok').forEach(el => {
            el.querySelector('.muat') && el.querySelector('.muat').remove();
            el.querySelector('.isi').innerHTML = `<div class="info text-danger"><i class="ti ti-alert-triangle me-1"></i>${esc(pesanXhr(x, 'Gagal memuat dokumen.'))}</div>`;
          }));
      }

      function render(k) {
        const el = kartu(k), d = INFO[k], label = el.dataset.label;
        const st = el.querySelector('.st'), isi = el.querySelector('.isi'), foot = el.querySelector('.dok-f');
        if (d && d.ada) { st.className = 'st st-ok'; st.textContent = 'Tersedia'; }
        else if (d) { st.className = 'st st-hilang'; st.textContent = 'File hilang'; }
        else { st.className = 'st st-belum'; st.textContent = 'Belum upload'; }

        if (d) {
          isi.innerHTML = `<div class="info"><i class="ti ti-file-text me-1"></i>${esc(d.file)}
            <div class="mt-1 text-dark">${d.waktu ? 'Diunggah ' + esc(tgl(d.waktu)) : ''}${d.ukuran != null ? ' · ' + esc(ukuran(d.ukuran)) : ''}${d.ada ? '' : '<div class="text-danger">File tidak ditemukan di server — unggah ulang.</div>'}</div></div>`;
          foot.innerHTML = (d.ada ? `<button type="button" class="btn btn-sm btn-primary" data-lihat="${k}"><i class="ti ti-eye me-1"></i>Lihat</button>` : '')
            + (BATAL ? '' : `<button type="button" class="btn btn-sm btn-outline-dark" data-unggah="${k}"><i class="ti ti-replace me-1"></i>Ganti</button>`);
        } else {
          isi.innerHTML = BATAL ? '<div class="info">Belum ada dokumen.</div>'
            : `<div class="drop" data-unggah="${k}" tabindex="0" role="button"><i class="ti ti-cloud-upload"></i><div class="fw-semibold mt-1">Unggah ${esc(label)}</div><div class="small">Klik atau seret file PDF ke sini</div></div>`;
          foot.innerHTML = BATAL ? '' : `<button type="button" class="btn btn-sm btn-primary" data-unggah="${k}"><i class="ti ti-upload me-1"></i>Unggah</button>`;
        }
        const m = el.querySelector('.muat');
        if (sibuk[k]) { if (!m) el.insertAdjacentHTML('beforeend', `<div class="muat"><span class="spinner-border spinner-border-sm me-2"></span>${esc(sibuk[k])}</div>`); }
        else if (m) m.remove();
      }

      async function unggah(k, file) {
        if (!file) return;
        const label = kartu(k).dataset.label;
        if (file.type !== 'application/pdf' && !/\.pdf$/i.test(file.name)) return Swal.fire('Format tidak didukung', 'Hanya file PDF yang diperbolehkan.', 'warning');
        if (file.size > MAKS) return Swal.fire('File terlalu besar', 'Ukuran maksimal 5 MB.', 'warning');
        if (INFO[k] && INFO[k].ada) {
          const x = await Swal.fire({ icon: 'question', title: `Ganti ${label}?`, text: 'File yang sekarang akan diganti dengan file baru.', showCancelButton: true, confirmButtonText: 'Ganti', cancelButtonText: 'Batal', cancelButtonColor: '#475569', reverseButtons: true });
          if (!x.isConfirmed) return;
        }
        sibuk[k] = 'Mengunggah…';
        render(k);
        try {
          const fd = new FormData();
          fd.append('no', NO);
          fd.append('jenis', k);
          fd.append('file', file);
          const r = await fetch(API_KLAIM, { method: 'POST', body: fd });
          const res = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
          if (res.status !== 'success') throw new Error(res.message || 'Gagal mengunggah.');
          toast(res.message || label + ' tersimpan.');
        } catch (e) {
          Swal.fire('Gagal', e.message, 'error');
        } finally {
          delete sibuk[k];
          muatKlaim();
        }
      }

      const grid = $id('ukGrid');
      grid.addEventListener('click', e => {
        const l = e.target.closest('[data-lihat]'), u = e.target.closest('[data-unggah]');
        if (l) lihat(INFO[l.dataset.lihat].url, kartu(l.dataset.lihat).dataset.label);
        if (u && !sibuk[u.dataset.unggah]) { target = u.dataset.unggah; $id('ukFile').click(); }
      });
      grid.addEventListener('keydown', e => { const u = e.target.closest('.drop[data-unggah]'); if (u && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); target = u.dataset.unggah; $id('ukFile').click(); } });
      $id('ukFile').addEventListener('change', function() { const f = this.files[0]; this.value = ''; if (target) unggah(target, f); });
      ['dragenter', 'dragover'].forEach(ev => grid.addEventListener(ev, e => { const z = e.target.closest('.drop'); if (z) { e.preventDefault(); z.classList.add('over'); } }));
      ['dragleave', 'drop'].forEach(ev => grid.addEventListener(ev, e => { const z = e.target.closest('.drop'); if (z) { e.preventDefault(); z.classList.remove('over'); } }));
      grid.addEventListener('drop', e => { const z = e.target.closest('.drop[data-unggah]'); if (z && e.dataTransfer.files[0] && !sibuk[z.dataset.unggah]) unggah(z.dataset.unggah, e.dataTransfer.files[0]); });

      /* ================= DOKUMEN LAINNYA ================= */
      let LAIN = [];
      const urlLain = p => /^uploads\/lainnya\/[^/]+$/.test(p || '') ? 'uploads/lainnya/' + encodeURIComponent(p.split('/').pop()) : '';

      function muatLain() {
        $.ajax({ url: API_LAIN, dataType: 'json', cache: false })
          .done(res => { LAIN = (res && res.data) || []; renderLain(); })
          .fail(x => { $id('dlWadah').innerHTML = `<div class="kosong text-danger"><i class="ti ti-alert-triangle me-1"></i>${esc(pesanXhr(x, 'Gagal memuat dokumen lainnya.'))}</div>`; });
      }

      function renderLain() {
        $id('dlJumlah').textContent = LAIN.length ? `· ${LAIN.length} dokumen` : '';
        if (!LAIN.length) {
          $id('dlWadah').innerHTML = '<div class="kosong"><i class="ti ti-files-off me-1"></i>Belum ada dokumen klaim lainnya.</div>';
          return;
        }
        $id('dlWadah').innerHTML = `<div class="table-responsive"><table class="table tbl align-middle mb-0">
          <thead><tr><th style="width:1%">Aksi</th><th style="width:150px">Tanggal</th><th>Judul Dokumen</th><th style="width:1%">File</th></tr></thead>
          <tbody>${LAIN.map((r, i) => {
            const url = urlLain(r.dokumen_path);
            return `<tr>
              <td class="text-nowrap">${BATAL ? '-' : `<div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-warning" data-ubah="${i}" title="Ubah tanggal & judul"><i class="ti ti-pencil"></i></button>
                <button type="button" class="btn btn-danger" data-hapus="${i}" title="Hapus"><i class="ti ti-trash"></i></button></div>`}</td>
              <td class="text-nowrap">${esc(tgl(r.tanggal))}</td>
              <td class="text-wrap">${esc(r.judul_dokumen || '-')}</td>
              <td class="text-nowrap">${url ? `<button type="button" class="btn btn-sm btn-primary" data-lihat-lain="${i}"><i class="ti ti-eye me-1"></i>Lihat</button>
                <a class="btn btn-sm btn-outline-dark" href="${esc(url)}" target="_blank" rel="noopener" title="Buka PDF"><i class="ti ti-external-link"></i></a>` : '<span class="text-danger small">File tidak valid</span>'}</td>
            </tr>`;
          }).join('')}</tbody></table></div>`;
      }

      $id('dlWadah').addEventListener('click', e => {
        const l = e.target.closest('[data-lihat-lain]'), u = e.target.closest('[data-ubah]'), d = e.target.closest('[data-hapus]');
        if (l) { const r = LAIN[l.dataset.lihatLain]; lihat(urlLain(r.dokumen_path), r.judul_dokumen || 'Dokumen'); }
        if (u) bukaModal(LAIN[u.dataset.ubah]);
        if (d) hapus(LAIN[d.dataset.hapus]);
      });

      const form = $id('programForm');
      function bersihkanInvalid() { form.querySelectorAll('.is-invalid').forEach(i => i.classList.remove('is-invalid')); }
      function tandai(field, pesan) {
        const i = form.querySelector(`[name="${field}"]`);
        if (!i) return false;
        i.classList.add('is-invalid');
        i.parentElement.querySelector('.invalid-feedback').textContent = pesan;
        i.focus();
        return true;
      }

      function bukaModal(r) {
        form.reset();
        bersihkanInvalid();
        $id('id_dokumen').value = r ? r.id_dokumen : '';
        $id('tgl_upload').value = r ? (r.tanggal || HARI_INI) : HARI_INI;
        $id('judul_dokumen').value = r ? (r.judul_dokumen || '') : '';
        $id('fileWrapper').classList.toggle('d-none', !!r);
        $id('fileTetap').classList.toggle('d-none', !r);
        $id('dlJudulModal').textContent = r ? 'Ubah Dokumen' : 'Tambah Dokumen';
        bootstrap.Modal.getOrCreateInstance($id('programModal')).show();
      }
      const btnTambah = $id('btnTambah');
      if (btnTambah) btnTambah.addEventListener('click', () => bukaModal(null));

      form.addEventListener('submit', async e => {
        e.preventDefault();
        bersihkanInvalid();
        const id = $id('id_dokumen').value;
        if (!$id('tgl_upload').value) return tandai('tgl_upload', 'Tanggal wajib diisi.');
        if ($id('tgl_upload').value > HARI_INI) return tandai('tgl_upload', 'Tanggal tidak boleh di masa depan.');
        if (!$id('judul_dokumen').value.trim()) return tandai('judul_dokumen', 'Judul dokumen wajib diisi.');
        const f = $id('dokumen_path').files[0];
        if (!id) {
          if (!f) return tandai('dokumen_path', 'Pilih file PDF.');
          if (f.type !== 'application/pdf' && !/\.pdf$/i.test(f.name)) return tandai('dokumen_path', 'Hanya file PDF.');
          if (f.size > MAKS) return tandai('dokumen_path', 'Ukuran maksimal 5 MB.');
        }
        const btn = $id('dlSimpan');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const fd = new FormData();
          fd.append('tgl_upload', $id('tgl_upload').value);
          fd.append('judul_dokumen', $id('judul_dokumen').value.trim());
          if (!id) fd.append('dokumen_path', f);
          const r = await fetch(API_LAIN + (id ? '&id=' + encodeURIComponent(id) : ''), { method: 'POST', body: fd });
          const res = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
          if (res.status !== 'success') {
            if (!(res.field && tandai(res.field, res.message))) Swal.fire('Gagal', res.message || 'Gagal menyimpan.', 'error');
            return;
          }
          bootstrap.Modal.getInstance($id('programModal')).hide();
          toast(res.message || 'Tersimpan.');
          muatLain();
        } catch (err) {
          Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
        } finally {
          btn.disabled = false;
          btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan';
        }
      });

      function hapus(r) {
        Swal.fire({
          icon: 'warning', title: 'Hapus dokumen?', html: `<b>${esc(r.judul_dokumen || '-')}</b><br>File PDF ikut dihapus dari server.`,
          showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true
        }).then(x => {
          if (!x.isConfirmed) return;
          $.ajax({ url: API_LAIN + '&id=' + encodeURIComponent(r.id_dokumen), type: 'DELETE', dataType: 'json' })
            .done(res => toast(res.message || 'Dokumen dihapus.'))
            .fail(x => Swal.fire('Gagal', pesanXhr(x, 'Gagal menghapus dokumen.'), 'error'))
            .always(muatLain);
        });
      }

      muatKlaim();
      muatLain();
    })();
  </script>
  <?php endif; ?>
</body>

</html>
