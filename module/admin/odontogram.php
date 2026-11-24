<?php
/**
 * Odontogram per kunjungan. API: controller/visit/getOdontogram.php & odontogramController.php. Cetak: print/formulir_odontogram.
 * Perbaikan: id kunjungan dicetak mentah ke JavaScript (XSS); $_GET['rme'] tanpa default; ubah/hapus satu temuan
 * ikut mengubah/menghapus semua temuan pada gigi yang sama (dicari lewat nomor gigi) -> kini lewat id baris;
 * memilih gigi menghapus warna hijau gigi yang sudah berisi data; batal edit memuat ulang data dua kali; alert()
 * bawaan browser; tombol di dalam <a>; kolom Aksi di kanan; permukaan gigi tidak terlihat di gambar.
 */
$title = 'Odontogram';
require '../../controller/view.php';
$no = trim((string) ($_GET['no'] ?? ''));
$rm = trim((string) ($_GET['rm'] ?? ''));
$rme = (string) ($_GET['rme'] ?? '');
$h = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$baris = [
   'Rahang atas — gigi tetap' => [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28],
   'Rahang atas — gigi susu' => [55, 54, 53, 52, 51, 61, 62, 63, 64, 65],
   'Rahang bawah — gigi susu' => [85, 84, 83, 82, 81, 71, 72, 73, 74, 75],
   'Rahang bawah — gigi tetap' => [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38],
];
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .od .kepala { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 14px; }
    .od .kepala .ic { width: 42px; height: 42px; border-radius: 12px; background: #e0f2fe; color: #075985; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
    .od .kepala h5 { margin: 0; font-weight: 700; color: #0f172a; }
    .od .kepala small { color: #475569; }
    .od .chart { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 10px; background: #fff; margin-bottom: 16px; overflow-x: auto; }
    .od .baris { display: flex; justify-content: center; gap: 4px; min-width: max-content; margin: 0 auto; }
    .od .baris + .baris { margin-top: 6px; }
    .od .baris.pisah { border-top: 2px dashed #cbd5e1; padding-top: 10px; margin-top: 10px; }
    .od .lbl-baris { text-align: center; font-size: .68rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; margin: 2px 0 4px; }
    .od .gigi { width: 50px; border: 1px solid transparent; border-radius: 10px; padding: 3px 2px; text-align: center; cursor: pointer; position: relative; background: #fff; }
    .od .gigi:hover { background: #f1f5f9; }
    .od .gigi.pilih { border: 2px solid #dc2626; background: #fee2e2; box-shadow: 0 0 0 3px #fecaca; }
    .od .gigi.pilih .no { color: #b91c1c; }
    .od .gigi.pilih svg .s:not(.diag):not(.tambal) { fill: #fca5a5; stroke: #dc2626; }
    .od .gigi .no { font-size: .72rem; font-weight: 800; color: #0f172a; }
    .od .gigi.ada .no { color: #b45309; }
    .od .gigi svg { width: 40px; height: 40px; display: block; margin: 2px auto 0; }
    .od .gigi svg .s { fill: #fff; stroke: #94a3b8; stroke-width: 1.2; }
    .od .gigi svg .s:hover { fill: #e0e7ff; }
    .od .gigi svg .s.diag { fill: #fcd34d; }
    .od .gigi svg .s.tambal { fill: #60a5fa; }
    .od .gigi svg .x { stroke: #dc2626; stroke-width: 3.5; stroke-linecap: round; display: none; }
    .od .gigi.cabut svg .x { display: inline; }
    .od .gigi.semua svg .s { stroke: #d97706; stroke-width: 2; }
    .od .gigi .jml { position: absolute; top: -5px; right: -3px; min-width: 16px; height: 16px; border-radius: 8px; background: #0f172a; color: #fff; font-size: .62rem; font-weight: 700; line-height: 16px; padding: 0 4px; }
    .od .legend { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; font-size: .76rem; color: #334155; margin-top: 10px; }
    .od .legend i { display: inline-block; width: 12px; height: 12px; border-radius: 3px; vertical-align: -1px; margin-right: 4px; border: 1px solid #94a3b8; }
    .od .panel { border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px; background: #fff; }
    .od .panel h6 { font-weight: 700; color: #0f172a; }
    .od .terpilih { border-radius: 10px; padding: 8px 10px; background: #f1f5f9; color: #334155; font-size: .84rem; margin-bottom: 12px; }
    .od .terpilih b { color: #0f172a; font-size: 1rem; }
    .od .terpilih.aktif { background: #fee2e2; color: #991b1b; }
    .od .seg { display: flex; flex-wrap: wrap; gap: 6px; }
    .od .seg input { position: absolute; opacity: 0; pointer-events: none; }
    .od .seg label { border: 1px solid #cbd5e1; border-radius: 8px; padding: 5px 11px; cursor: pointer; font-size: .82rem; font-weight: 600; color: #1f2937; background: #fff; }
    .od .seg input:checked + label { background: #0f172a; border-color: #0f172a; color: #fff; }
    .od .seg input:focus-visible + label { outline: 2px solid #2563eb; outline-offset: 1px; }
    .od table.t { width: 100%; border-collapse: collapse; font-size: .86rem; }
    .od table.t th { font-size: .7rem; text-transform: uppercase; color: #475569; font-weight: 700; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 7px 8px; white-space: nowrap; }
    .od table.t td { border-bottom: 1px solid #eef2f6; padding: 8px; vertical-align: top; color: #1f2937; }
    .od table.t tr.sorot td { background: #fef2f2; }
    .od table.t tr.edit td { background: #fffbeb; }
    .od .aksi { white-space: nowrap; }
    .od .aksi .btn { padding: 3px 9px; font-weight: 600; }
    .od .tag { font-size: .7rem; font-weight: 700; border-radius: 6px; padding: 2px 8px; display: inline-block; white-space: nowrap; text-transform: capitalize; }
    .od .tg-tambal { background: #dbeafe; color: #1e3a8a; }
    .od .tg-cabut { background: #fee2e2; color: #991b1b; }
    .od .kosong { text-align: center; padding: 26px 10px; color: #475569; }
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
                <div class="card-body p-4 od">
                  <div class="kepala">
                    <span class="ic"><i class="ti ti-dental"></i></span>
                    <div class="flex-grow-1">
                      <h5>Odontogram <span class="small text-muted fw-normal" id="odJumlah"></span></h5>
                      <small>Klik gigi (atau langsung permukaannya) lalu isi temuan di panel kanan. Satu gigi boleh memiliki beberapa temuan.</small>
                    </div>
                    <a class="btn btn-outline-dark" target="_blank" rel="noopener" href="module/admin/print/formulir_odontogram?<?= $h(http_build_query(['no' => $no, 'rm' => $rm])) ?>"><i class="ti ti-printer me-1"></i>Cetak</a>
                  </div>

                  <!-- PETA GIGI -->
                  <div class="chart" id="odChart">
                    <?php $i = 0; foreach ($baris as $judul => $gigi): $i++; ?>
                      <?php if ($i === 1): ?><div class="lbl-baris"><?= $h($judul) ?></div><?php endif; ?>
                      <div class="baris<?= $i === 3 ? ' pisah' : '' ?>">
                        <?php foreach ($gigi as $g): ?>
                          <div class="gigi" data-gigi="<?= $g ?>" role="button" tabindex="0" aria-label="Gigi <?= $g ?>">
                            <?php if ($i <= 2): ?><div class="no"><?= $g ?></div><?php endif; ?>
                            <svg viewBox="0 0 44 44" aria-hidden="true">
                              <polygon class="s" data-pos="atas" points="0,0 44,0 32,12 12,12" />
                              <polygon class="s" data-pos="bawah" points="0,44 44,44 32,32 12,32" />
                              <polygon class="s" data-pos="kiri" points="0,0 12,12 12,32 0,44" />
                              <polygon class="s" data-pos="kanan" points="44,0 32,12 32,32 44,44" />
                              <rect class="s" data-pos="tengah" x="12" y="12" width="20" height="20" />
                              <line class="x" x1="6" y1="6" x2="38" y2="38" /><line class="x" x1="38" y1="6" x2="6" y2="38" />
                            </svg>
                            <?php if ($i > 2): ?><div class="no"><?= $g ?></div><?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                      <?php if ($i === 2): ?><div class="lbl-baris" style="margin-top:6px">↑ atas &nbsp;·&nbsp; bawah ↓</div><?php endif; ?>
                      <?php if ($i === 4): ?><div class="lbl-baris" style="margin-top:4px"><?= $h($judul) ?></div><?php endif; ?>
                    <?php endforeach; ?>
                    <div class="legend">
                      <span><i style="background:#fcd34d"></i>Ada diagnosa</span>
                      <span><i style="background:#60a5fa"></i>Tambal</span>
                      <span><span class="text-danger fw-bold me-1">✕</span>Cabut</span>
                      <span><i style="background:#fff;border-color:#d97706;border-width:2px"></i>Temuan tanpa permukaan</span>
                      <span class="text-muted">Kanan pasien = sisi kiri gambar</span>
                    </div>
                  </div>

                  <div class="row g-3">
                    <!-- FORM -->
                    <div class="col-lg-4">
                      <div class="panel">
                        <h6 id="odJudulForm">Tambah temuan</h6>
                        <div class="terpilih" id="odTerpilih">Belum ada gigi dipilih — klik gigi pada gambar.</div>

                        <div class="mb-3">
                          <div class="form-label small fw-semibold">Elemen</div>
                          <div class="seg" id="elemenSeg">
                            <input type="radio" name="elemenR" id="el_mahkota" value="mahkota"><label for="el_mahkota">Mahkota</label>
                            <input type="radio" name="elemenR" id="el_akar" value="akar"><label for="el_akar">Akar</label>
                          </div>
                          <select class="d-none" id="elemen" aria-hidden="true" tabindex="-1">
                            <option value="">---Pilih---</option>
                            <option value="mahkota">Mahkota</option>
                            <option value="akar">Akar</option>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label small fw-semibold" for="elemen_gigi">Permukaan gigi</label>
                          <select class="form-select" id="elemen_gigi">
                            <option value="">— Tidak spesifik —</option>
                            <option value="mesial">Mesial</option>
                            <option value="distal">Distal</option>
                            <option value="oklusal">Oklusal</option>
                            <option value="lingual">Lingual / palatal</option>
                            <option value="bukal">Bukal / labial</option>
                          </select>
                          <div class="form-text">Bisa juga dengan klik permukaan pada gambar gigi.</div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label small fw-semibold" for="diagnosa">Diagnosa <span class="text-danger">*</span></label>
                          <select class="form-control" id="diagnosa"><option value=""></option></select>
                        </div>

                        <div class="mb-3">
                          <div class="form-label small fw-semibold">Prosedur</div>
                          <div class="seg" id="prosedurSeg">
                            <input type="radio" name="prosedurR" id="pr_none" value=""><label for="pr_none">Tidak ada</label>
                            <input type="radio" name="prosedurR" id="pr_tambal" value="tambal"><label for="pr_tambal">Tambal</label>
                            <input type="radio" name="prosedurR" id="pr_cabut" value="cabut"><label for="pr_cabut">Cabut</label>
                          </div>
                          <select class="d-none" id="prosedur" aria-hidden="true" tabindex="-1">
                            <option value="">---</option>
                            <option value="tambal">Tambal</option>
                            <option value="cabut">Cabut</option>
                          </select>
                        </div>

                        <div class="mb-3">
                          <label class="form-label small fw-semibold" for="keterangan">Keterangan</label>
                          <textarea class="form-control" id="keterangan" rows="2" maxlength="2000" placeholder="mis. karies profunda, rencana ekstraksi"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                          <button type="button" class="btn btn-primary flex-fill" id="btnSimpanOdontogram"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                          <button type="button" class="btn btn-outline-dark d-none" id="btnBatalEdit">Batal</button>
                        </div>
                      </div>
                    </div>

                    <!-- TABEL -->
                    <div class="col-lg-8">
                      <div class="panel">
                        <div class="d-flex align-items-center mb-2">
                          <h6 class="mb-0">Temuan odontogram</h6>
                          <a href="javascript:;" class="small ms-auto d-none" id="odTampilSemua">Tampilkan semua gigi</a>
                        </div>
                        <div class="table-responsive">
                          <table class="t" id="tableOdontogram">
                            <thead><tr><th>Aksi</th><th>Gigi</th><th>Elemen</th><th>Permukaan</th><th>Diagnosa</th><th>Prosedur</th><th>Keterangan</th></tr></thead>
                            <tbody><tr><td colspan="7" class="kosong"><span class="spinner-border spinner-border-sm me-2"></span>Memuat…</td></tr></tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script src="controller/admisi/helper.js?v=<?= filemtime(__DIR__ . '/../../controller/admisi/helper.js') ?>"></script>
  <script>
    (function() {
      'use strict';
      const VISIT = <?= json_encode($no) ?>;
      const $id = i => document.getElementById(i);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const toast = (title, icon = 'success') => Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 2000 });
      let DATA = [], gigiPilih = null, editRow = null, sibuk = false, filterGigi = null;

      /* ---------- nama & orientasi gigi ---------- */
      const KUADRAN = { 1: 'kanan atas', 2: 'kiri atas', 3: 'kiri bawah', 4: 'kanan bawah', 5: 'kanan atas', 6: 'kiri atas', 7: 'kiri bawah', 8: 'kanan bawah' };
      const TETAP = ['', 'insisivus sentral', 'insisivus lateral', 'kaninus', 'premolar 1', 'premolar 2', 'molar 1', 'molar 2', 'molar 3'];
      const SUSU = ['', 'insisivus sentral', 'insisivus lateral', 'kaninus', 'molar 1', 'molar 2'];
      const namaGigi = no => { const q = Math.floor(no / 10), p = no % 10; return `${(q >= 5 ? SUSU : TETAP)[p] || ''}${q >= 5 ? ' susu' : ''} ${KUADRAN[q] || ''}`.trim(); };
      // posisi gambar -> permukaan: rahang atas (1,2,5,6) bukal di atas; rahang bawah lingual di atas.
      // kuadran kanan pasien (1,4,5,8) tampil di kiri -> mesial di sisi kanan gambar (ke garis tengah).
      function permukaan(no, pos) {
        const q = Math.floor(no / 10), atas = [1, 2, 5, 6].includes(q), kananPasien = [1, 4, 5, 8].includes(q);
        if (pos === 'tengah') return 'oklusal';
        if (pos === 'atas') return atas ? 'bukal' : 'lingual';
        if (pos === 'bawah') return atas ? 'lingual' : 'bukal';
        if (pos === 'kanan') return kananPasien ? 'mesial' : 'distal';
        return kananPasien ? 'distal' : 'mesial';
      }
      const posDari = (no, surf) => ['atas', 'bawah', 'kiri', 'kanan', 'tengah'].find(p => permukaan(no, p) === surf);

      /* ---------- data ---------- */
      function muat() {
        return fetch('controller/visit/getOdontogram?visit_ID=' + encodeURIComponent(VISIT))
          .then(r => r.json().then(j => ({ ok: r.ok, j })))
          .then(({ ok, j }) => {
            if (!ok || j.status !== 'success') throw new Error(j.message || 'Gagal mengambil data odontogram');
            DATA = j.data || [];
            render();
          })
          .catch(e => { document.querySelector('#tableOdontogram tbody').innerHTML = `<tr><td colspan="7" class="kosong text-danger">${esc(e.message)}</td></tr>`; });
      }

      function render() {
        $id('odJumlah').textContent = DATA.length ? `· ${DATA.length} temuan pada ${new Set(DATA.map(d => +d.no_gigi)).size} gigi` : '';
        // gambar
        document.querySelectorAll('.gigi').forEach(el => {
          const no = +el.dataset.gigi, rows = DATA.filter(d => +d.no_gigi === no);
          el.classList.toggle('ada', rows.length > 0);
          el.classList.toggle('cabut', rows.some(d => d.prosedur === 'cabut'));
          el.classList.toggle('semua', rows.some(d => !d.elemen_gigi));
          el.classList.toggle('pilih', no === gigiPilih);
          el.querySelectorAll('.s').forEach(s => s.classList.remove('diag', 'tambal'));
          rows.forEach(d => {
            const p = d.elemen_gigi && posDari(no, d.elemen_gigi);
            const s = p && el.querySelector(`.s[data-pos="${p}"]`);
            if (s) s.classList.add(d.prosedur === 'tambal' ? 'tambal' : 'diag');
          });
          let b = el.querySelector('.jml');
          if (rows.length > 1) { if (!b) { b = document.createElement('span'); b.className = 'jml'; el.appendChild(b); } b.textContent = rows.length; }
          else if (b) b.remove();
          el.title = rows.length ? `Gigi ${no} — ${rows.length} temuan` : `Gigi ${no} (${namaGigi(no)})`;
        });
        // tabel
        const tb = document.querySelector('#tableOdontogram tbody');
        const rows = filterGigi ? DATA.filter(d => +d.no_gigi === filterGigi) : DATA;
        $id('odTampilSemua').classList.toggle('d-none', !filterGigi);
        if (!rows.length) {
          tb.innerHTML = `<tr><td colspan="7" class="kosong">${filterGigi ? `Belum ada temuan pada gigi ${filterGigi}.` : 'Belum ada data odontogram. Klik gigi pada gambar untuk menambahkan temuan.'}</td></tr>`;
          return;
        }
        tb.innerHTML = rows.map(d => `
          <tr class="${editRow && +editRow.id === +d.id ? 'edit' : (+d.no_gigi === gigiPilih ? 'sorot' : '')}">
            <td><div class="aksi d-flex gap-1">
              <button type="button" class="btn btn-sm btn-warning" data-ubah="${+d.id}" title="Ubah"><i class="ti ti-pencil"></i></button>
              <button type="button" class="btn btn-sm btn-danger" data-hapus="${+d.id}" title="Hapus"><i class="ti ti-trash"></i></button>
            </div></td>
            <td><b>${esc(d.no_gigi)}</b><div class="small text-muted text-capitalize">${esc(namaGigi(+d.no_gigi))}</div></td>
            <td class="text-capitalize">${esc(d.elemen || '-')}</td>
            <td class="text-capitalize">${esc(d.elemen_gigi || '-')}</td>
            <td>${esc(d.diagnosa || '-')}</td>
            <td>${d.prosedur ? `<span class="tag tg-${esc(d.prosedur)}">${esc(d.prosedur)}</span>` : '<span class="text-muted">-</span>'}</td>
            <td style="white-space:pre-line">${esc(d.keterangan || '-')}</td>
          </tr>`).join('');
      }

      /* ---------- pilih gigi / permukaan ---------- */
      function pilih(no, surf) {
        gigiPilih = no;
        filterGigi = editRow ? filterGigi : no;
        const box = $id('odTerpilih');
        box.classList.add('aktif');
        box.innerHTML = `Gigi <b>${no}</b> · <span class="text-capitalize">${esc(namaGigi(no))}</span>`;
        if (surf) $id('elemen_gigi').value = surf;
        render();
      }
      $id('odChart').addEventListener('click', e => {
        const g = e.target.closest('.gigi');
        if (!g) return;
        const s = e.target.closest('.s');
        pilih(+g.dataset.gigi, s ? permukaan(+g.dataset.gigi, s.dataset.pos) : null);
      });
      $id('odChart').addEventListener('keydown', e => { const g = e.target.closest('.gigi'); if (g && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); pilih(+g.dataset.gigi, null); } });
      $id('odTampilSemua').addEventListener('click', () => { filterGigi = null; render(); });

      /* ---------- radio <-> select tersembunyi (id elemen/prosedur tetap) ---------- */
      const sinkron = (name, sel) => document.querySelectorAll(`[name="${name}"]`).forEach(r => r.addEventListener('change', () => { $id(sel).value = r.value; }));
      sinkron('elemenR', 'elemen');
      sinkron('prosedurR', 'prosedur');
      const setRadio = (name, sel, v) => { $id(sel).value = v || ''; document.querySelectorAll(`[name="${name}"]`).forEach(r => r.checked = r.value === (v || '')); };

      /* ---------- form ---------- */
      APP.initDiagnosaSearch('#diagnosa', false);
      function resetForm() {
        setRadio('elemenR', 'elemen', 'mahkota');
        setRadio('prosedurR', 'prosedur', '');
        $id('elemen_gigi').value = '';
        $('#diagnosa').val(null).trigger('change');
        $id('keterangan').value = '';
      }
      function batalEdit() {
        editRow = null;
        resetForm();
        $id('odJudulForm').textContent = 'Tambah temuan';
        $id('btnSimpanOdontogram').innerHTML = '<i class="ti ti-device-floppy me-1"></i>Simpan';
        $id('btnSimpanOdontogram').classList.replace('btn-warning', 'btn-primary');
        $id('btnBatalEdit').classList.add('d-none');
        render();
      }
      $id('btnBatalEdit').addEventListener('click', batalEdit);

      function ubah(d) {
        editRow = d;
        filterGigi = null;
        pilih(+d.no_gigi, null);
        setRadio('elemenR', 'elemen', d.elemen);
        setRadio('prosedurR', 'prosedur', d.prosedur);
        $id('elemen_gigi').value = d.elemen_gigi || '';
        $('#diagnosa').empty().append(new Option(d.diagnosa || '', d.diagnosa || '', true, true)).trigger('change');
        $id('keterangan').value = d.keterangan || '';
        $id('odJudulForm').textContent = `Ubah temuan gigi ${d.no_gigi}`;
        $id('btnSimpanOdontogram').innerHTML = '<i class="ti ti-pencil me-1"></i>Simpan perubahan';
        $id('btnSimpanOdontogram').classList.replace('btn-primary', 'btn-warning');
        $id('btnBatalEdit').classList.remove('d-none');
        render();
        $id('odTerpilih').scrollIntoView({ behavior: 'smooth', block: 'center' });
      }

      async function kirim(body) {
        const r = await fetch('controller/visit/odontogramController.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(Object.assign({ visit_ID: VISIT }, body)) });
        const j = await r.json().catch(() => ({ status: 'error', message: 'Respon server tidak valid.' }));
        if (!r.ok || j.status !== 'success') throw new Error(j.message || 'Terjadi kesalahan.');
        return j;
      }

      $id('btnSimpanOdontogram').addEventListener('click', async () => {
        if (sibuk) return;
        if (!gigiPilih) return Swal.fire({ icon: 'warning', title: 'Pilih gigi dulu', text: 'Klik gigi pada gambar odontogram.' });
        const sel = $id('diagnosa');
        const diagnosa = sel.value ? sel.selectedOptions[0].text.trim() : '';
        if (!diagnosa) { $('#diagnosa').select2('open'); return toast('Diagnosa wajib diisi.', 'warning'); }
        sibuk = true;
        const btn = $id('btnSimpanOdontogram'), label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const res = await kirim({
            action: editRow ? 'update' : 'insert', id: editRow ? editRow.id : null, no_gigi: gigiPilih, old_no_gigi: editRow ? +editRow.no_gigi : null,
            elemen: $id('elemen').value, elemen_gigi: $id('elemen_gigi').value, diagnosa, prosedur: $id('prosedur').value, keterangan: $id('keterangan').value,
          });
          toast(res.message);
          const tetap = gigiPilih;
          editRow = null;
          await muat();
          batalEdit();
          pilih(tetap, null);   // gigi tetap terpilih agar mudah menambah temuan berikutnya
        } catch (e) {
          Swal.fire('Gagal', e.message, 'error');
        } finally {
          sibuk = false;
          btn.disabled = false;
          if (btn.innerHTML.includes('spinner')) btn.innerHTML = label;
        }
      });

      document.querySelector('#tableOdontogram tbody').addEventListener('click', e => {
        const u = e.target.closest('[data-ubah]'), h = e.target.closest('[data-hapus]');
        const cari = id => DATA.find(d => +d.id === +id);
        if (u) ubah(cari(u.dataset.ubah));
        if (h && !sibuk) {
          const d = cari(h.dataset.hapus);
          Swal.fire({
            icon: 'warning', title: 'Hapus temuan?', html: `Temuan <b>${esc(d.diagnosa)}</b> pada gigi <b>${esc(d.no_gigi)}</b> akan dihapus.`,
            showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', cancelButtonColor: '#475569', reverseButtons: true
          }).then(async x => {
            if (!x.isConfirmed) return;
            sibuk = true;
            try {
              const res = await kirim({ action: 'delete', id: d.id, no_gigi: +d.no_gigi });
              toast(res.message);
              if (editRow && +editRow.id === +d.id) batalEdit();
              await muat();
            } catch (err) { Swal.fire('Gagal', err.message, 'error'); } finally { sibuk = false; }
          });
        }
      });

      resetForm();
      muat();
    })();
  </script>
</body>

</html>
