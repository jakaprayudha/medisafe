<?php
$title = 'Permintaan Rawat Inap';
require '../../controller/view.php';
session_write_close();
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .br-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .br-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; height: 100%; }
    .br-label { font-size: .72rem; color: #6c757d; }
    .br-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.25; }
    .br-sub { font-size: .7rem; color: #94a3b8; }
    .br-tabs .btn { border-radius: 20px; font-size: .8rem; padding: .3rem .8rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .br-tabs .btn .badge { background: #eef1f4; color: #5a6a85; margin-left: 4px; }
    .br-tabs .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .br-tabs .btn.active .badge { background: rgba(255, 255, 255, .25); color: #fff; }
    .br-rm { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .72rem; background: #f1f5f9; border-radius: 4px; padding: 0 4px; color: #334155; }
    #brTable td { vertical-align: middle; }
    #brTable > thead > tr > th, #brTable > tbody > tr > td { padding: .6rem .55rem; }
    #brTable > thead > tr > th:first-child, #brTable > tbody > tr > td:first-child { padding-left: .9rem; }
    #brTable > thead > tr > th:last-child, #brTable > tbody > tr > td:last-child { padding-right: .9rem; width: 1%; white-space: nowrap; }
    .br-nama { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .br-dx { max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    tr.br-redup td { opacity: .6; }
    /* modal pilih bed */
    .br-pasien { background: #f8fafc; border: 1px solid #e5eaef; border-radius: 12px; padding: 10px 12px; }
    .br-kelas .btn { border-radius: 20px; font-size: .75rem; padding: .2rem .7rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .br-kelas .btn.active { background: #ecf2ff; border-color: #5d87ff; color: #3554d1; font-weight: 600; }
    .br-ruang { border: 1px solid #e5eaef; border-radius: 12px; padding: 10px; margin-bottom: 8px; }
    .br-ruang-h { font-size: .76rem; font-weight: 600; color: #475569; margin-bottom: 6px; display: flex; justify-content: space-between; gap: 8px; }
    .br-beds { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 6px; }
    .br-bed { border: 1px solid #d1fae5; border-radius: 10px; padding: 7px 9px; background: #f0fdf9; text-align: left; font-size: .78rem; color: #047857; line-height: 1.3; }
    .br-bed:hover:not(:disabled) { border-color: #13deb9; }
    .br-bed .ket { display: block; font-size: .7rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .br-bed:disabled { background: #f1f5f9; border-color: #e2e8f0; color: #94a3b8; cursor: not-allowed; }
    .br-bed.pilih { border-color: #5d87ff; background: #ecf2ff; color: #3554d1; box-shadow: 0 0 0 2px rgba(93, 135, 255, .2); }
    .br-bed .gx { color: #b45309; }
    .br-bedwrap { max-height: 52vh; overflow: auto; }
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

          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="br-icon bg-primary-subtle text-primary"><i class="fas fa-user-plus"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Permintaan Rawat Inap</h5>
                  <div class="text-muted small">Permintaan dari dokter poli / IGD. Pilih tempat tidur untuk menyetujui, atau batalkan bila tidak jadi dirawat.</div>
                </div>
                <button type="button" class="btn btn-light btn-sm" id="brRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
                <a class="btn btn-outline-success btn-sm" href="module/admin/status_pulang"><i class="fas fa-bed me-1"></i>Bed &amp; status pulang</a>
                <a class="btn btn-outline-primary btn-sm" href="module/admisi/registrasi-inap"><i class="fas fa-procedures me-1"></i>Pasien rawat inap</a>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="br-kpi"><div class="br-label">Menunggu persetujuan</div><div class="br-val text-primary" id="kTotal">-</div><div class="br-sub" id="kDirawat">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="br-kpi"><div class="br-label">Permintaan hari ini</div><div class="br-val" id="kHariIni">-</div><div class="br-sub">diminta tanggal hari ini</div></div></div>
            <div class="col-6 col-xl-3"><div class="br-kpi"><div class="br-label">Menunggu &gt; 3 hari</div><div class="br-val" id="kLama">-</div><div class="br-sub">kemungkinan tidak jadi dirawat</div></div></div>
            <div class="col-6 col-xl-3"><div class="br-kpi"><div class="br-label">Bed kosong</div><div class="br-val text-success" id="kBed">-</div><div class="br-sub" id="kBedTotal">&nbsp;</div></div></div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-lg-4">
              <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <div class="br-tabs d-flex flex-wrap gap-1" id="brTabs">
                  <button type="button" class="btn active" data-tab="baru">Terbaru (≤ 3 hari) <span class="badge" id="nBaru">0</span></button>
                  <button type="button" class="btn" data-tab="lama">Lebih lama <span class="badge" id="nLama">0</span></button>
                  <button type="button" class="btn" data-tab="semua">Semua <span class="badge" id="nSemua">0</span></button>
                </div>
                <div class="position-relative ms-auto" style="min-width:220px">
                  <i class="fas fa-search position-absolute text-muted" style="left:10px;top:50%;transform:translateY(-50%);font-size:.75rem"></i>
                  <input type="search" id="brCari" class="form-control form-control-sm ps-4" placeholder="Cari nama, RM, dokter, diagnosa…">
                </div>
              </div>
              <div class="table-responsive">
                <table class="table align-middle mb-0 w-100" id="brTable">
                  <thead><tr>
                    <th class="text-dark fw-normal">Pasien</th>
                    <th class="text-dark fw-normal">Diminta</th>
                    <th class="text-dark fw-normal">Asal / penjamin</th>
                    <th class="text-dark fw-normal">DPJP</th>
                    <th class="text-dark fw-normal">Diagnosa awal / catatan</th>
                    <th class="text-dark fw-normal text-end">Aksi</th>
                  </tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- MODAL PILIH BED / APPROVE -->
  <div class="modal fade" id="brModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title mb-0"><i class="fas fa-bed text-primary me-2"></i>Tempatkan pasien rawat inap</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="br-pasien mb-3" id="mPasien"></div>
          <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <div class="small fw-semibold">Pilih tempat tidur</div>
            <div class="br-kelas d-flex flex-wrap gap-1 ms-auto" id="mKelas"></div>
          </div>
          <div class="form-check form-switch small mb-2">
            <input class="form-check-input" type="checkbox" id="mKosongSaja" checked>
            <label class="form-check-label" for="mKosongSaja">Hanya tampilkan bed kosong</label>
          </div>
          <div class="br-bedwrap" id="mBeds"></div>
        </div>
        <div class="modal-footer py-2">
          <span class="small me-auto" id="mInfo"><span class="text-muted">Belum ada bed dipilih.</span></span>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="mSimpan" disabled><i class="fas fa-check me-1"></i>Setujui &amp; tempatkan</button>
        </div>
      </div>
    </div>
  </div>

  <?php require 'library.php'; ?>
  <script>
    (function() {
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const isi = v => v !== null && v !== undefined && /[0-9A-Za-z]/.test(String(v));
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglJkt = () => new Date().toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const tgl = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + (m[1] !== tglJkt().slice(0, 4) ? ' ' + m[1].slice(2) : '') : '-'; };
      const jam = s => (String(s || '').match(/^(\d{1,2}):(\d{2})/) || [])[0] || '';
      function umur(lahir) { const m = String(lahir || '').match(/^(\d{4})-(\d{2})-(\d{2})/); if (!m || m[1] < '1900') return ''; const d = new Date(), b = new Date(+m[1], m[2] - 1, +m[3]); let u = d.getFullYear() - b.getFullYear(); if (d < new Date(d.getFullYear(), b.getMonth(), b.getDate())) u--; return u >= 0 ? u + ' th' : ''; }
      const jkPasien = g => { g = String(g || '').toLowerCase(); return g.startsWith('l') ? 'L' : (g.startsWith('p') ? 'P' : ''); };
      // bed_gender: Pria / Wanita / Pria-Wanita
      const jkBed = g => { g = String(g || '').toLowerCase(); return g.includes('-') || !g ? '' : (g.startsWith('pria') ? 'L' : (g.startsWith('wanita') ? 'P' : '')); };
      const tutup = m => { if (m._isTransitioning) m._element.addEventListener('shown.bs.modal', () => m.hide(), { once: true }); else m.hide(); };
      const API = 'controller/visit/addListBookingRanap';
      const BATAS = 3;
      let tab = 'baru', ruang = [], xhr = null;

      // filter tab (hanya untuk tabel ini)
      $.fn.dataTable.ext.search.push((s, _d, i) => {
        if (s.nTable.id !== 'brTable') return true;
        const r = s.aoData[i]._aData, lama = (r.umur_permintaan ?? 0) > BATAS;
        return tab === 'semua' || (tab === 'lama' ? lama : !lama);
      });

      const table = $('#brTable').DataTable({
        processing: true, serverSide: false, deferRender: true, autoWidth: false, order: [], pageLength: 25, lengthMenu: [10, 25, 50, 100],
        dom: 't<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2 small"lip>',
        language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat…', emptyTable: 'Tidak ada permintaan rawat inap.', zeroRecords: 'Tidak ada data yang cocok.',
          info: '_START_–_END_ dari _TOTAL_ permintaan', infoEmpty: '0 permintaan', infoFiltered: '(disaring dari _MAX_)', lengthMenu: 'Tampil _MENU_', thousands: '.', paginate: { previous: '‹', next: '›' } },
        ajax: function(_d, cb) {
          if (xhr && xhr.readyState !== 4) xhr.abort();
          xhr = $.ajax({ url: API, dataType: 'json', cache: false })
            .done(j => {
              if (!j || j.status !== 'success') { cb({ data: [] }); Swal.fire('Gagal memuat data', (j && j.message) || '', 'error'); return; }
              ruang = j.ruang || [];
              ruang.forEach(x => x.bed.forEach((b, i) => { b.urut = i + 1; }));
              const h = j.hitung, lama = j.data.filter(r => (r.umur_permintaan ?? 0) > BATAS).length;
              $id('kTotal').textContent = fmt(h.total);
              $id('kDirawat').innerHTML = h.sudah_dirawat ? `<span class="text-warning">${fmt(h.sudah_dirawat)} kunjungan sudah dirawat (dobel)</span>` : '&nbsp;';
              $id('kHariIni').textContent = fmt(h.hari_ini);
              $id('kLama').textContent = fmt(h.lama);
              $id('kLama').classList.toggle('text-warning', h.lama > 0);
              $id('kBed').textContent = fmt(h.bed_kosong);
              $id('kBedTotal').textContent = 'dari ' + fmt(h.bed_total) + ' bed';
              $id('nSemua').textContent = fmt(j.data.length);
              $id('nLama').textContent = fmt(lama);
              $id('nBaru').textContent = fmt(j.data.length - lama);
              cb({ data: j.data });
            })
            .fail((x, st) => { if (st === 'abort') return; cb({ data: [] }); Swal.fire('Gagal memuat data', x.status === 401 ? 'Sesi berakhir, silakan login ulang.' : 'Tidak dapat terhubung ke server.', 'error'); });
        },
        rowId: r => 'br' + r.id_ranap,
        createdRow: (tr, r) => { if (r.sudah_dirawat) tr.classList.add('br-redup'); },
        columns: [
          { data: 'patient_name', render: (v, t, r) => {
              if (t !== 'display') return [v, r.nomor_rm, r.noKartu].join(' ');
              const jk = jkPasien(r.patient_gender), u = umur(r.patient_datebirth);
              return `<div class="fw-semibold br-nama" title="${esc(v)}">${esc(v || '-')}</div><div class="small text-muted"><span class="br-rm">${esc(r.nomor_rm || '-')}</span>${jk ? ' · ' + jk : ''}${u ? ' · ' + u : ''}</div>`;
            } },
          { data: 'ranap_date', render: (v, t, r) => {
              if (t !== 'display') return (v || '') + ' ' + (r.ranap_time || '');
              const n = r.umur_permintaan;
              const ket = n === null ? '' : (n <= 0 ? '<span class="text-success">hari ini</span>' : `<span class="${n > BATAS ? 'text-warning' : 'text-muted'}">${n} hari lalu</span>`);
              return `<div class="small">${tgl(v)}${jam(r.ranap_time) ? ' · ' + esc(jam(r.ranap_time)) : ''}</div><div class="small">${ket}</div>`;
            } },
          { data: 'id_poli', render: (v, t, r) => t === 'display' ? `<div class="small">${esc(v || '-')}</div><div class="small text-muted">${esc(r.provider_name || '-')}</div>` : (v || '') + ' ' + (r.provider_name || '') },
          { data: 'id_doctor', render: v => `<span class="small">${esc(v || '-')}</span>` },
          { data: 'diagnosa_awal', render: (v, t, r) => {
              if (t !== 'display') return (v || '') + ' ' + (r.ranap_notes || '');
              return `<div class="small br-dx" title="${esc(v || '')}">${isi(v) ? esc(v) : '<span class="text-muted">Belum diisi</span>'}</div>` +
                (isi(r.ranap_notes) ? `<div class="small text-muted br-dx" title="${esc(r.ranap_notes)}"><i class="far fa-comment-dots me-1"></i>${esc(r.ranap_notes)}</div>` : '');
            } },
          { data: null, orderable: false, searchable: false, className: 'text-end', render: (v, t, r) => t !== 'display' ? '' :
              (r.sudah_dirawat
                ? `<span class="badge bg-warning-subtle text-warning me-1" title="Kunjungan ini sudah ditempatkan dari permintaan lain">Sudah dirawat</span>`
                : `<button type="button" class="btn btn-sm btn-primary br-approve" data-id="${r.id_ranap}"><i class="fas fa-bed me-1"></i>Tempatkan</button>`) +
              ` <button type="button" class="btn btn-sm btn-light text-danger br-batal" data-id="${r.id_ranap}" title="Batalkan permintaan"><i class="fas fa-times"></i></button>` }
        ]
      });
      const baris = id => table.rows().data().toArray().find(r => r.id_ranap === +id);
      const muat = () => table.ajax.reload(null, false);

      $id('brTabs').addEventListener('click', e => {
        const b = e.target.closest('.btn[data-tab]');
        if (!b) return;
        tab = b.dataset.tab;
        document.querySelectorAll('#brTabs .btn').forEach(x => x.classList.toggle('active', x === b));
        table.draw();
      });
      let tCari;
      $id('brCari').addEventListener('input', e => { clearTimeout(tCari); tCari = setTimeout(() => table.search(e.target.value).draw(), 200); });
      $id('brRefresh').addEventListener('click', muat);

      // ===== Modal tempatkan =====
      const modal = new bootstrap.Modal($id('brModal'));
      let aktif = null, pilihBed = null, kelas = '';
      const namaBed = b => isi(b.nama) ? b.nama : 'Bed ' + b.urut;
      const cariBed = id => { for (const x of ruang) for (const b of x.bed) if (b.id_bed === id) return [x, b]; return [null, null]; };

      function gambarBed() {
        const jk = jkPasien(aktif.patient_gender), kosongSaja = $id('mKosongSaja').checked;
        const html = ruang.filter(x => !kelas || x.kelas === kelas).map(x => {
          const beds = x.bed.filter(b => !kosongSaja || !b.pasien.length);
          if (!beds.length) return '';
          const kosong = x.bed.filter(b => !b.pasien.length).length;
          return `<div class="br-ruang"><div class="br-ruang-h"><span>${esc(x.nama || 'Ruang')} <span class="text-muted fw-normal">#${x.id_room}${x.kelas && x.kelas !== x.nama ? ' · ' + esc(x.kelas) : ''}</span></span><span class="text-muted fw-normal">${kosong}/${x.bed.length} kosong</span></div><div class="br-beds">` +
            beds.map(b => {
              const p = b.pasien[0], jb = jkBed(b.gender), beda = jk && jb && jk !== jb;
              const ket = p ? 'Dipakai: ' + esc(p.nama) : (beda ? `<span class="gx"><i class="fas fa-triangle-exclamation me-1"></i>khusus ${esc(b.gender)}</span>` : esc(b.gender || 'kosong'));
              return `<button type="button" class="br-bed${pilihBed === b.id_bed ? ' pilih' : ''}" data-bed="${b.id_bed}" ${p ? 'disabled' : ''} title="${p ? esc(p.nama) : esc(b.gender || '')}"><b><i class="fas fa-bed me-1"></i>${esc(namaBed(b))}</b><span class="ket">${ket}</span></button>`;
            }).join('') + '</div></div>';
        }).join('');
        $id('mBeds').innerHTML = html || `<div class="text-center text-muted small py-4"><i class="fas fa-bed fa-2x mb-2 d-block opacity-50"></i>${ruang.length ? 'Tidak ada bed kosong' + (kelas ? ' di kelas ini' : '') + '. Pulangkan pasien di halaman <a href="module/admin/status_pulang">Bed &amp; status pulang</a> atau tampilkan semua bed.' : 'Belum ada data ruang / bed. Tambahkan di master ruang.'}</div>`;
      }
      function infoPilih() {
        const [x, b] = cariBed(pilihBed);
        $id('mSimpan').disabled = !b;
        if (!b) { $id('mInfo').innerHTML = '<span class="text-muted">Belum ada bed dipilih.</span>'; return; }
        const jk = jkPasien(aktif.patient_gender), jb = jkBed(b.gender);
        $id('mInfo').innerHTML = `<i class="fas fa-check-circle text-primary me-1"></i><b>${esc(namaBed(b))}</b> — ${esc(x.nama || 'Ruang')} #${x.id_room}` +
          (jk && jb && jk !== jb ? ` <span class="text-warning ms-1"><i class="fas fa-triangle-exclamation"></i> bed khusus ${esc(b.gender)}</span>` : '');
      }
      function bukaModal(r) {
        aktif = r; pilihBed = null; kelas = '';
        const jk = jkPasien(r.patient_gender), u = umur(r.patient_datebirth);
        $id('mPasien').innerHTML = `<div class="d-flex flex-wrap gap-2 align-items-start"><div class="min-w-0 flex-grow-1"><div class="fw-semibold text-truncate">${esc(r.patient_name || '-')}</div>
          <div class="small text-muted"><span class="br-rm">${esc(r.nomor_rm || '-')}</span>${jk ? ' · ' + (jk === 'L' ? 'Laki-laki' : 'Perempuan') : ''}${u ? ' · ' + u : ''} · ${esc(r.provider_name || '-')}</div></div>
          <div class="small text-end"><div>${esc(r.id_doctor || '-')}</div><div class="text-muted">diminta ${tgl(r.ranap_date)}${jam(r.ranap_time) ? ' ' + esc(jam(r.ranap_time)) : ''}</div></div></div>` +
          (isi(r.diagnosa_awal) ? `<div class="small mt-2"><span class="text-muted">Diagnosa awal:</span> ${esc(r.diagnosa_awal)}</div>` : '') +
          (isi(r.ranap_notes) ? `<div class="small text-muted"><i class="far fa-comment-dots me-1"></i>${esc(r.ranap_notes)}</div>` : '');
        const daftarKelas = [...new Set(ruang.map(x => x.kelas).filter(isi))];
        $id('mKelas').innerHTML = daftarKelas.length > 1 ? ['', ...daftarKelas].map(k => `<button type="button" class="btn${k === kelas ? ' active' : ''}" data-kelas="${esc(k)}">${k ? esc(k) : 'Semua kelas'}</button>`).join('') : '';
        $id('mKosongSaja').checked = true;
        gambarBed(); infoPilih();
        modal.show();
      }
      $id('mKelas').addEventListener('click', e => {
        const b = e.target.closest('[data-kelas]');
        if (!b) return;
        kelas = b.dataset.kelas;
        $id('mKelas').querySelectorAll('.btn').forEach(x => x.classList.toggle('active', x === b));
        gambarBed();
      });
      $id('mKosongSaja').addEventListener('change', gambarBed);
      $id('mBeds').addEventListener('click', e => {
        const b = e.target.closest('.br-bed');
        if (!b || b.disabled) return;
        pilihBed = +b.dataset.bed;
        $id('mBeds').querySelectorAll('.br-bed').forEach(x => x.classList.toggle('pilih', x === b));
        infoPilih();
      });
      $id('mSimpan').addEventListener('click', async () => {
        if (!aktif || !pilihBed) return;
        const btn = $id('mSimpan'), asli = btn.innerHTML;
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const r = await fetch('controller/visit/approveRawatInap.php', { method: 'POST', credentials: 'same-origin', body: new URLSearchParams({ id_ranap: aktif.id_ranap, id_bed: pilihBed }) });
          const j = await r.json().catch(() => null);
          if (j && j.status === 'success') {
            tutup(modal);
            Swal.fire({ icon: 'success', title: 'Disetujui', text: j.message, timer: 1800, showConfirmButton: false });
            muat();
          } else {
            Swal.fire('Gagal', (j && j.message) || 'Terjadi kesalahan server.', 'error');
            if (r.status === 409) muat(); // bed sudah dipakai / sudah di-approve -> segarkan data
          }
        } catch (e) {
          Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
        } finally {
          btn.innerHTML = asli; btn.disabled = !pilihBed;
        }
      });

      $('#brTable').on('click', '.br-approve', function() {
        const r = baris(this.dataset.id);
        if (r) bukaModal(r);
      });
      $('#brTable').on('click', '.br-batal', function() {
        const r = baris(this.dataset.id);
        if (!r) return;
        Swal.fire({
          icon: 'warning', title: 'Batalkan permintaan?',
          html: `Permintaan rawat inap <b>${esc(r.patient_name || '-')}</b> (${tgl(r.ranap_date)}) akan dihapus.`,
          showCancelButton: true, confirmButtonText: 'Ya, batalkan', cancelButtonText: 'Tidak', confirmButtonColor: '#d33', reverseButtons: true,
          showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(),
          preConfirm: async () => {
            try {
              const x = await fetch('controller/visit/cancelRawatInap', { method: 'POST', credentials: 'same-origin', body: new URLSearchParams({ id_ranap: r.id_ranap }) });
              const j = await x.json().catch(() => null);
              if (!j || j.status !== 'success') Swal.showValidationMessage((j && j.message) || 'Gagal membatalkan.');
              return j;
            } catch (e) { Swal.showValidationMessage('Tidak dapat terhubung ke server.'); }
          }
        }).then(res => {
          if (!res.isConfirmed) return;
          Swal.fire({ icon: 'success', title: 'Dibatalkan', timer: 1400, showConfirmButton: false });
          muat();
        });
      });
    })();
  </script>
</body>

</html>
