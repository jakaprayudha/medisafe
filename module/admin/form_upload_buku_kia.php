<?php
/**
 * Foto Buku KIA (Kesehatan Ibu dan Anak) per kunjungan. API controller/visit/getFotoKIA.php & uploadKIA.php.
 * Perbaikan: query pasien dirangkai dari URL (SQL injection) dengan INNER JOIN ms_doctor ON id_doctor (berisi nama
 * dokter) -> data kosong & error usia; difilter nomor RM dari URL; assesmen.php dimuat tanpa perlu; modal tanda tangan
 * tanpa tombol pembuka -> error JS "openModal null" (+ signature_pad dari CDN tanpa versi pasti); setelah unggah foto
 * tidak muncul (diarahkan ke #fotoPasien yang tidak ada) & daftar tidak dimuat ulang; JSON.parse manual tanpa
 * penanganan error; satu foto per unggahan; tidak bisa melihat besar / menghapus foto; nomor kunjungan/RM dicetak tanpa
 * escape; foto Buku KIA kunjungan sebelumnya tidak terlihat.
 */
$title = 'Buku KIA';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$idc = (int) ($_SESSION['id_customer'] ?? 0);

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
    .kia .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 16px; }
    .kia .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #fce7f3; color: #9d174d; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .kia .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .kia .kepala small { color: #475569; }
    .kia .grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
    @media (max-width: 1199.98px) { .kia .grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 767.98px) { .kia .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .kia .foto { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; display: flex; flex-direction: column; }
    .kia .foto .gbr { aspect-ratio: 3 / 4; background: #f1f5f9; display: flex; align-items: center; justify-content: center; }
    .kia .foto .gbr img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; }
    .kia .foto .kaki { display: flex; align-items: center; gap: 6px; padding: 8px 10px; border-top: 1px solid #eef2f6; font-size: .78rem; color: #1e293b; }
    .kia .tambah { border: 2px dashed #cbd5e1; border-radius: 12px; aspect-ratio: 3 / 4; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; color: #334155; cursor: pointer; padding: 10px; }
    .kia .tambah.over, .kia .tambah:hover { border-color: #db2777; background: #fdf2f8; }
    .kia .tambah i { font-size: 2rem; color: #be185d; }
    .kia .kosong { border: 1px dashed #cbd5e1; border-radius: 12px; padding: 22px; text-align: center; color: #334155; }
    .kia .bagian { display: flex; align-items: center; gap: 8px; margin: 24px 0 12px; font-weight: 700; color: #0f172a; }
    .kia .muat { position: relative; }
    #lihatModal .modal-content { background: #0f172a; color: #fff; }
    #lihatModal img { max-width: 100%; max-height: 80vh; object-fit: contain; display: block; margin: 0 auto; }
    #lihatModal .nav-btn { position: absolute; top: 50%; transform: translateY(-50%); z-index: 2; }
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
                <div class="card-body p-4 kia">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-book"></i></span>
                    <div class="flex-grow-1">
                      <h5>Buku KIA <span class="small text-muted fw-normal" id="kiaJumlah"></span></h5>
                      <small>Foto halaman Buku Kesehatan Ibu dan Anak untuk kunjungan ini. Bisa unggah beberapa foto sekaligus.</small>
                    </div>
                    <?php if ($ada): ?>
                      <button type="button" class="btn btn-primary d-none" id="btnFoto"><i class="ti ti-upload me-1"></i>Unggah foto</button>
                    <?php endif; ?>
                  </div>
                  <?php if (!$ada): ?>
                    <div class="alert alert-danger mb-0"><i class="ti ti-alert-triangle me-1"></i>Kunjungan tidak ditemukan untuk faskes ini.</div>
                  <?php else: ?>
                    <div id="kiaBatal" class="alert alert-warning d-none"><i class="ti ti-ban me-1"></i>Kunjungan sudah dibatalkan — foto hanya dapat dilihat.</div>
                    <div id="listFotoKIA"><div class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</div></div>
                    <input type="file" id="file_foto" class="d-none" accept="image/jpeg,image/png,image/webp" multiple>
                    <div class="small text-dark mt-2"><i class="ti ti-info-circle me-1"></i>Format JPG / PNG / WEBP, maks. 10 foto sekali unggah. Foto besar otomatis diperkecil. Klik kotak unggah atau seret foto ke sana.</div>
                    <div id="kiaRiwayat"></div>
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
  <div class="modal fade" id="lihatModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <span class="fw-semibold" id="lihatJudul"></span>
          <a class="btn btn-sm btn-light ms-auto me-2" id="lihatAsli" target="_blank" rel="noopener"><i class="ti ti-external-link me-1"></i>Buka asli</a>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body position-relative">
          <button type="button" class="btn btn-light nav-btn start-0 ms-2" id="lihatPrev" title="Sebelumnya"><i class="ti ti-chevron-left"></i></button>
          <img id="lihatImg" alt="Buku KIA">
          <button type="button" class="btn btn-light nav-btn end-0 me-2" id="lihatNext" title="Berikutnya"><i class="ti ti-chevron-right"></i></button>
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
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tgl = s => { const m = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}:\d{2}))?/.exec(s || ''); return m ? `${+m[3]} ${BLN[m[2] - 1]} ${m[1]}${m[4] ? ' ' + m[4] : ''}` : '-'; };
      let DATA = [], RIWAYAT = [], BATAL = false, sibuk = false, galeri = [], idx = 0;

      function muat() {
        $.ajax({ url: 'controller/visit/getFotoKIA.php', data: { visit: NO }, dataType: 'json', cache: false })
          .done(res => { DATA = res.data || []; RIWAYAT = res.riwayat || []; BATAL = !!res.batal; render(); })
          .fail(x => { $id('listFotoKIA').innerHTML = `<div class="kosong text-danger"><i class="ti ti-alert-triangle me-1"></i>${esc((x.responseJSON && x.responseJSON.message) || 'Gagal memuat foto Buku KIA.')}</div>`; });
      }

      const kartu = (r, i, set, hapus) => `
        <div class="foto">
          <div class="gbr"><img src="${esc(r.url)}" alt="Buku KIA" loading="lazy" data-lihat="${set}:${i}"></div>
          <div class="kaki">
            ${hapus ? `<button type="button" class="btn btn-sm btn-danger py-0 px-2" data-hapus="${i}" title="Hapus"><i class="ti ti-trash"></i></button>` : ''}
            <span class="text-truncate">${esc(tgl(r.created_at))}</span>
          </div>
        </div>`;

      function render() {
        $id('kiaBatal').classList.toggle('d-none', !BATAL);
        $id('btnFoto').classList.toggle('d-none', BATAL);
        $id('kiaJumlah').textContent = DATA.length ? `· ${DATA.length} foto` : '';
        const tambah = BATAL ? '' : `<div class="tambah" id="kiaDrop" tabindex="0" role="button">${sibuk ? '<span class="spinner-border text-danger"></span><div class="mt-2 fw-semibold">Mengunggah…</div>' : '<i class="ti ti-photo-plus"></i><div class="fw-semibold mt-1">Unggah foto</div><div class="small">Klik atau seret ke sini</div>'}</div>`;
        $id('listFotoKIA').innerHTML = DATA.length || !BATAL
          ? `<div class="grid">${DATA.map((r, i) => kartu(r, i, 'ini', !BATAL)).join('')}${tambah}</div>`
          : '<div class="kosong"><i class="ti ti-book-off me-1"></i>Belum ada foto Buku KIA.</div>';
        // riwayat kunjungan lain, dikelompokkan per kunjungan
        if (!RIWAYAT.length) { $id('kiaRiwayat').innerHTML = ''; return; }
        const grup = {};
        RIWAYAT.forEach((r, i) => { (grup[r.visit_ID] = grup[r.visit_ID] || { tgl: r.visit_date, isi: [] }).isi.push([r, i]); });
        $id('kiaRiwayat').innerHTML = `<div class="bagian"><i class="ti ti-history text-danger"></i>Buku KIA dari kunjungan sebelumnya <span class="small fw-normal text-dark">· hanya lihat</span></div>`
          + Object.entries(grup).map(([v, g]) => `<div class="small fw-semibold text-dark mb-2 mt-3">${esc(tgl(g.tgl))} <span class="fw-normal">· ${esc(v)}</span></div>
              <div class="grid">${g.isi.map(([r, i]) => kartu(r, i, 'lama', false)).join('')}</div>`).join('');
      }

      /* ===== Lihat (dengan geser kiri/kanan) ===== */
      function tampil() {
        const r = galeri[idx];
        $id('lihatImg').src = r.url;
        $id('lihatAsli').href = r.url;
        $id('lihatJudul').textContent = `Foto ${idx + 1} dari ${galeri.length} · ${tgl(r.created_at)}`;
        $id('lihatPrev').classList.toggle('d-none', galeri.length < 2);
        $id('lihatNext').classList.toggle('d-none', galeri.length < 2);
      }
      $id('lihatPrev').addEventListener('click', () => { idx = (idx - 1 + galeri.length) % galeri.length; tampil(); });
      $id('lihatNext').addEventListener('click', () => { idx = (idx + 1) % galeri.length; tampil(); });
      $id('lihatModal').addEventListener('keydown', e => { if (e.key === 'ArrowLeft') $id('lihatPrev').click(); if (e.key === 'ArrowRight') $id('lihatNext').click(); });

      const wadah = document.querySelector('.kia');
      wadah.addEventListener('click', e => {
        const l = e.target.closest('[data-lihat]'), d = e.target.closest('[data-hapus]'), t = e.target.closest('#kiaDrop');
        if (l) {
          const [set, i] = l.dataset.lihat.split(':');
          galeri = set === 'ini' ? DATA : RIWAYAT;
          idx = +i;
          tampil();
          bootstrap.Modal.getOrCreateInstance($id('lihatModal')).show();
        }
        if (d) hapus(DATA[d.dataset.hapus]);
        if (t && !sibuk) $id('file_foto').click();
      });
      wadah.addEventListener('keydown', e => { if (e.target.id === 'kiaDrop' && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); $id('file_foto').click(); } });
      $id('btnFoto').addEventListener('click', () => { if (!sibuk) $id('file_foto').click(); });
      $id('file_foto').addEventListener('change', function() { const f = [...this.files]; this.value = ''; unggah(f); });
      ['dragenter', 'dragover'].forEach(ev => wadah.addEventListener(ev, e => { const z = e.target.closest('#kiaDrop'); if (z) { e.preventDefault(); z.classList.add('over'); } }));
      ['dragleave', 'drop'].forEach(ev => wadah.addEventListener(ev, e => { const z = e.target.closest('#kiaDrop'); if (z) { e.preventDefault(); z.classList.remove('over'); } }));
      wadah.addEventListener('drop', e => { if (e.target.closest('#kiaDrop') && !sibuk) unggah([...e.dataTransfer.files]); });

      // perkecil foto > 1,5 MB ke sisi 2000 px JPEG
      function perkecil(file) {
        return new Promise(resolve => {
          if (file.size <= 1.5 * 1024 * 1024) return resolve(file);
          const img = new Image(), url = URL.createObjectURL(file);
          img.onload = () => {
            URL.revokeObjectURL(url);
            const s = Math.min(1, 2000 / Math.max(img.naturalWidth, img.naturalHeight)), c = document.createElement('canvas');
            c.width = Math.round(img.naturalWidth * s);
            c.height = Math.round(img.naturalHeight * s);
            const k = c.getContext('2d');
            k.fillStyle = '#fff';
            k.fillRect(0, 0, c.width, c.height);
            k.drawImage(img, 0, 0, c.width, c.height);
            c.toBlob(b => resolve(b && b.size < file.size ? new File([b], 'kia.jpg', { type: 'image/jpeg' }) : file), 'image/jpeg', 0.86);
          };
          img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
          img.src = url;
        });
      }

      async function unggah(files) {
        files = files.filter(Boolean);
        if (!files.length) return;
        if (files.some(f => !/^image\/(jpeg|png|webp)$/.test(f.type))) return Swal.fire('Format tidak didukung', 'Gunakan foto JPG, PNG, atau WEBP.', 'warning');
        if (files.length > 10) return Swal.fire('Terlalu banyak', 'Maksimal 10 foto sekali unggah.', 'warning');
        sibuk = true;
        render();
        try {
          const fd = new FormData();
          fd.append('visit', NO);
          for (const f of files) {
            const k = await perkecil(f);
            if (k.size > 5 * 1024 * 1024) throw new Error(`"${f.name}" masih di atas 5 MB setelah diperkecil.`);
            fd.append('file_foto[]', k, k.name || 'kia.jpg');
          }
          const r = await fetch('controller/visit/uploadKIA.php', { method: 'POST', body: fd });
          const res = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
          if (res.status !== 'success') throw new Error(res.message || 'Gagal mengunggah.');
          toast(res.message || 'Tersimpan.');
        } catch (e) {
          Swal.fire('Gagal', e.message === 'Failed to fetch' ? 'Tidak dapat terhubung ke server.' : e.message, 'error');
        } finally {
          sibuk = false;
          muat();
        }
      }

      function hapus(r) {
        Swal.fire({ title: 'Hapus foto Buku KIA?', imageUrl: r.url, imageHeight: 160, imageAlt: 'Buku KIA', text: 'Foto dihapus permanen.', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true })
          .then(x => {
            if (!x.isConfirmed) return;
            $.ajax({ url: 'controller/visit/uploadKIA.php', type: 'POST', data: { visit: NO, aksi: 'hapus', id: r.id }, dataType: 'json' })
              .done(res => toast(res.message || 'Foto dihapus.'))
              .fail(x => Swal.fire('Gagal', (x.responseJSON && x.responseJSON.message) || 'Gagal menghapus foto.', 'error'))
              .always(muat);
          });
      }

      muat();
    })();
  </script>
  <?php endif; ?>
</body>

</html>
