<?php
$title = 'Bed & Status Pulang';
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
    .sp-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .sp-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 12px 14px; height: 100%; }
    .sp-label { font-size: .72rem; color: #6c757d; }
    .sp-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.25; }
    .sp-sub { font-size: .7rem; color: #94a3b8; }
    .sp-tabs .btn { border-radius: 20px; font-size: .8rem; padding: .3rem .8rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .sp-tabs .btn .badge { background: #eef1f4; color: #5a6a85; margin-left: 4px; }
    .sp-tabs .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .sp-tabs .btn.active .badge { background: rgba(255, 255, 255, .25); color: #fff; }
    .sp-rm { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .72rem; background: #f1f5f9; border-radius: 4px; padding: 0 5px; color: #334155; }
    .sp-peta { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px; }
    .sp-ruang { border: 1px solid #e5eaef; border-radius: 12px; padding: 10px; background: #fff; }
    .sp-ruang-h { font-size: .74rem; font-weight: 600; color: #475569; margin-bottom: 6px; display: flex; justify-content: space-between; }
    .sp-bed { border-radius: 8px; padding: 6px 8px; font-size: .74rem; margin-bottom: 4px; display: flex; align-items: center; gap: 6px; border: 1px solid transparent; }
    .sp-bed:last-child { margin-bottom: 0; }
    .sp-bed.kosong { background: #f0fdf9; color: #047857; }
    .sp-bed.isi { background: #eef2ff; color: #3730a3; }
    .sp-bed.salah { background: #fff7ed; color: #9a3412; border-color: #fdba74; }
    .sp-bed .nama { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sp-bed b { white-space: nowrap; flex-shrink: 0; }
    .sp-dpjp { display: inline-block; max-width: 170px; overflow: hidden; text-overflow: ellipsis; vertical-align: middle; }
    #spTable td { vertical-align: middle; white-space: nowrap; }
    #spTable > thead > tr > th, #spTable > tbody > tr > td { padding: .6rem .55rem; }
    #spTable > thead > tr > th:first-child, #spTable > tbody > tr > td:first-child { padding-left: .9rem; }
    #spTable > thead > tr > th:last-child, #spTable > tbody > tr > td:last-child { padding-right: .9rem; width: 1%; }
    .sp-nama { max-width: 190px; overflow: hidden; text-overflow: ellipsis; }
    .sp-opsi { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 6px; }
    .sp-opsi input { display: none; }
    .sp-opsi label { border: 1px solid #e5eaef; border-radius: 10px; padding: 8px 10px; font-size: .82rem; cursor: pointer; background: #fff; }
    .sp-opsi input:checked + label { border-color: #13deb9; background: #f0fdf9; font-weight: 600; color: #047857; }
    .sp-bedpilih { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 6px; max-height: 50vh; overflow: auto; }
    .sp-bp { border: 1px solid #e5eaef; border-radius: 10px; padding: 8px 10px; background: #fff; text-align: left; font-size: .8rem; }
    .sp-bp:disabled { opacity: .45; cursor: not-allowed; }
    .sp-bp.pilih { border-color: #5d87ff; background: #ecf2ff; }
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
                <span class="sp-icon bg-success-subtle text-success"><i class="fas fa-bed"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Bed &amp; Status Pulang</h5>
                  <div class="text-muted small">Pulangkan pasien, pindah bed, dan pantau ketersediaan bed rawat inap.</div>
                </div>
                <button type="button" class="btn btn-light btn-sm" id="spRefresh" title="Muat ulang"><i class="fas fa-sync-alt"></i></button>
                <a class="btn btn-outline-primary btn-sm" href="module/admisi/registrasi-inap"><i class="fas fa-procedures me-1"></i>Registrasi rawat inap</a>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3"><div class="sp-kpi"><div class="sp-label">Sedang dirawat</div><div class="sp-val text-primary" id="kAktif">-</div><div class="sp-sub" id="kSiap">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="sp-kpi"><div class="sp-label">Bed kosong</div><div class="sp-val text-success" id="kKosong">-</div><div class="sp-sub" id="kTotalBed">&nbsp;</div></div></div>
            <div class="col-6 col-xl-3"><div class="sp-kpi"><div class="sp-label">Dirawat &gt; 14 hari</div><div class="sp-val" id="kLama">-</div><div class="sp-sub">cek apakah sudah pulang</div></div></div>
            <div class="col-6 col-xl-3"><div class="sp-kpi"><div class="sp-label">Status bed tidak sesuai</div><div class="sp-val" id="kSalah">-</div><div class="sp-sub" id="kSinkron">&nbsp;</div></div></div>
          </div>

          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
              <div class="d-flex align-items-center mb-2">
                <div class="fw-semibold small"><i class="fas fa-th me-1 text-muted"></i>Peta bed</div>
                <div class="ms-auto small d-flex gap-3">
                  <span><span class="sp-bed kosong d-inline-flex px-2 py-0 m-0">kosong</span></span>
                  <span><span class="sp-bed isi d-inline-flex px-2 py-0 m-0">terisi</span></span>
                  <span><span class="sp-bed salah d-inline-flex px-2 py-0 m-0">status tidak sesuai</span></span>
                </div>
              </div>
              <div class="sp-peta" id="spPeta"><div class="text-muted small">Memuat…</div></div>
            </div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-lg-4">
              <div class="d-flex flex-wrap align-items-end gap-2 mb-3">
                <div class="sp-tabs d-flex gap-1" id="spTabs">
                  <button type="button" class="btn active" data-tab="aktif">Sedang dirawat <span class="badge" id="nAktif">0</span></button>
                  <button type="button" class="btn" data-tab="pulang">Sudah pulang <span class="badge" id="nPulang">-</span></button>
                </div>
                <div class="d-flex align-items-center gap-1 d-none" id="spRentang">
                  <input type="date" id="spDari" class="form-control form-control-sm" style="width:140px" aria-label="Masuk dari">
                  <span class="text-muted small">–</span>
                  <input type="date" id="spSampai" class="form-control form-control-sm" style="width:140px" aria-label="Masuk sampai">
                </div>
                <div class="position-relative ms-auto" style="min-width:220px">
                  <i class="fas fa-search position-absolute text-muted" style="left:10px;top:50%;transform:translateY(-50%);font-size:.75rem"></i>
                  <input type="search" id="spCari" class="form-control form-control-sm ps-4" placeholder="Cari nama, RM, bed…">
                </div>
              </div>
              <div class="table-responsive">
                <table class="table align-middle mb-0 w-100" id="spTable">
                  <thead><tr>
                    <th class="text-dark fw-normal">Pasien</th>
                    <th class="text-dark fw-normal">Ruang / bed</th>
                    <th class="text-dark fw-normal">Masuk</th>
                    <th class="text-dark fw-normal">DPJP</th>
                    <th class="text-dark fw-normal">Resume medis</th>
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

  <!-- MODAL PULANG -->
  <div class="modal fade" id="spPulang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0"><h5 class="modal-title mb-0"><i class="fas fa-door-open text-success me-2"></i>Pulangkan pasien</h5><div class="small text-muted text-truncate" id="plSub"></div></div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label small fw-semibold">Cara keluar</label>
          <div class="sp-opsi mb-3" id="plCara"></div>
          <label class="form-label small fw-semibold">Kondisi saat pulang</label>
          <div class="sp-opsi mb-2" id="plKondisi"></div>
          <div class="small text-muted"><i class="fas fa-circle-info me-1"></i>Bed akan dikosongkan dan kunjungan ditandai selesai. Data cara keluar &amp; kondisi juga tersimpan ke kunjungan.</div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-success" id="plSimpan"><i class="fas fa-check me-1"></i>Pulangkan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL PILIH BED -->
  <div class="modal fade" id="spBed" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div class="min-w-0"><h5 class="modal-title mb-0" id="pbJudul">Pindah bed</h5><div class="small text-muted text-truncate" id="pbSub"></div></div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body"><div class="sp-bedpilih" id="pbList"></div></div>
        <div class="modal-footer py-2">
          <span class="small text-muted me-auto" id="pbInfo"></span>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="pbSimpan" disabled><i class="fas fa-save me-1"></i>Simpan</button>
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
      const BLN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const tglJkt = h => new Date(Date.now() + (h || 0) * 86400000).toLocaleDateString('sv-SE', { timeZone: 'Asia/Jakarta' });
      const tgl = s => { const m = String(s || '').match(/^(\d{4})-(\d{2})-(\d{2})/); return m ? (+m[3]) + ' ' + BLN[m[2] - 1] + (m[1] !== tglJkt(0).slice(0, 4) ? ' ' + m[1].slice(2) : '') : '-'; };
      const isi = v => v !== null && v !== undefined && /[0-9A-Za-z]/.test(String(v));
      const API = 'controller/visit/ListBookingRanap';
      const LAMA = 14;
      const CARA = ['Diizinkan Pulang', 'Dirujuk Ke RS', 'Pulang Paksa', 'Meninggal', 'Lainnya'];
      const KONDISI = ['Sembuh', 'Membaik', 'Belum Sembuh', 'Meninggal', 'Lainnya'];
      let tab = 'aktif', ruang = [], xhr = null;
      $id('spDari').value = tglJkt(-29);
      $id('spSampai').value = tglJkt(0);

      function petaBed(r, bed) {
        $id('spPeta').innerHTML = r.length ? r.map(x => `<div class="sp-ruang"><div class="sp-ruang-h"><span>${esc(x.nama || 'Ruang')} <span class="text-muted fw-normal">#${x.id_room}</span></span><span class="text-muted fw-normal">${x.kelas && x.kelas !== x.nama ? esc(x.kelas) : ''}</span></div>` +
          x.bed.map(b => {
            const p = b.pasien[0], cls = (!!p !== b.terisi_data) ? 'salah' : (p ? 'isi' : 'kosong');
            const ket = p ? esc(p.nama) : (b.terisi_data ? 'tercatat terisi, tanpa pasien' : 'kosong');
            return `<div class="sp-bed ${cls}" title="${ket}"><i class="fas fa-bed"></i><b>${esc(namaBed(b))}</b><span class="nama">${ket}</span></div>`;
          }).join('') + '</div>').join('') : '<div class="text-muted small">Belum ada data ruang/bed.</div>';
        $id('kKosong').textContent = fmt(bed.total - bed.terisi_pasien);
        $id('kTotalBed').textContent = 'dari ' + fmt(bed.total) + ' bed';
        $id('kSalah').textContent = fmt(bed.tidak_sesuai);
        $id('kSalah').classList.toggle('text-warning', bed.tidak_sesuai > 0);
      }
      // nama bed di data sering "-" -> pakai nomor urut bed dalam ruang
      const namaBed = b => isi(b.nama) ? b.nama : 'Bed ' + (b.urut || '?');
      let bolehSinkron = false;
      function infoSinkron(bed) {
        $id('kSinkron').innerHTML = bed.tidak_sesuai > 0 ? (bolehSinkron ? '<a href="javascript:;" id="spSinkron">Sinkronkan status bed</a>' : 'hubungi admin untuk sinkron') : 'status bed sesuai';
      }

      const table = $('#spTable').DataTable({
        processing: true, serverSide: false, deferRender: true, autoWidth: false, order: [], pageLength: 25, lengthMenu: [10, 25, 50, 100],
        dom: 't<"d-flex flex-wrap justify-content-between align-items-center mt-3 gap-2 small"lip>',
        language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div> Memuat…', emptyTable: 'Tidak ada pasien.', zeroRecords: 'Tidak ada data yang cocok.',
          info: '_START_–_END_ dari _TOTAL_ pasien', infoEmpty: '0 pasien', infoFiltered: '(disaring dari _MAX_)', lengthMenu: 'Tampil _MENU_', thousands: '.', paginate: { previous: '‹', next: '›' } },
        ajax: function(_d, cb) {
          if (xhr && xhr.readyState !== 4) xhr.abort();
          xhr = $.ajax({ url: API, dataType: 'json', cache: false, data: { tab, dari: $id('spDari').value, sampai: $id('spSampai').value } })
            .done(j => {
              if (!j || j.status !== 'success') { cb({ data: [] }); Swal.fire('Gagal memuat data', (j && j.message) || '', 'error'); return; }
              ruang = j.ruang || [];
              ruang.forEach(x => x.bed.forEach((b, i) => { b.urut = i + 1; }));
              const urutBed = {}; ruang.forEach(x => x.bed.forEach(b => { urutBed[b.id_bed] = namaBed(b); }));
              j.data.forEach(r => { r.bed_label = r.id_bed ? (urutBed[r.id_bed] || (isi(r.bed_name) ? r.bed_name : 'Bed')) : ''; });
              bolehSinkron = !!j.boleh_sinkron;
              petaBed(ruang, j.bed);
              infoSinkron(j.bed);
              $id('nAktif').textContent = fmt(j.hitung.aktif);
              $id('kAktif').textContent = fmt(j.hitung.aktif);
              if (j.tab === 'pulang') $id('nPulang').textContent = fmt(j.hitung.pulang);
              if (j.tab === 'aktif') {
                const siap = j.data.filter(r => isi(r.cara_keluar)).length, lama = j.data.filter(r => r.lama_hari > LAMA).length;
                $id('kSiap').innerHTML = siap ? `<span class="text-success">${fmt(siap)} sudah ada resume pulang</span>` : '&nbsp;';
                $id('kLama').textContent = fmt(lama);
                $id('kLama').classList.toggle('text-danger', lama > 0);
              }
              cb({ data: j.data });
            })
            .fail((x, s) => { if (s === 'abort') return; cb({ data: [] }); Swal.fire('Gagal memuat data', 'Periksa koneksi lalu coba lagi.', 'error'); });
        },
        columns: [
          { data: 'patient_name', render: (v, t, r) => {
              if (t !== 'display') return [v, r.nomor_rm, r.bed_name, r.room_name].join(' ');
              const jk = String(r.patient_gender || '').charAt(0).toUpperCase();
              return `<div class="fw-semibold sp-nama" title="${esc(v)}">${esc(v || '-')}</div><div class="small text-muted"><span class="sp-rm">${esc(r.nomor_rm || '-')}</span>${jk ? ' · ' + (jk === 'L' ? 'L' : 'P') : ''}</div>`;
            } },
          { data: 'bed_name', render: (v, t, r) => t === 'display' ? (r.id_bed ? `<div class="small fw-semibold">${esc(r.bed_label)}</div><div class="small text-muted">${esc(r.room_name || '')} #${esc(r.id_room || '')}</div>` : '<span class="badge bg-warning-subtle text-warning">Belum ada bed</span>') : (v || '') },
          { data: 'visit_date', render: (v, t, r) => {
              if (t !== 'display') return v;
              const curiga = r.status === 'aktif' && r.lama_hari > LAMA;
              return `<div class="small">${esc(tgl(v))} · ${esc(String(r.visit_time || '').slice(0, 5))}</div>` + (r.status === 'aktif' ? `<div class="small ${curiga ? 'text-danger fw-semibold' : 'text-muted'}">${r.lama_hari} hari${curiga ? ' <i class="fas fa-triangle-exclamation"></i>' : ''}</div>` : '');
            } },
          { data: 'id_doctor', render: (v, t) => t === 'display' ? `<span class="small sp-dpjp" title="${esc(v || '')}">${esc(v || '-')}</span>` : (v || '') },
          { data: 'cara_keluar', render: (v, t, r) => t === 'display' ? (isi(v) ? `<span class="badge ${r.status === 'aktif' ? 'bg-success-subtle text-success' : 'bg-light text-muted'}">${esc(v)}</span>${isi(r.kondisi_pulang) ? `<div class="small text-muted">${esc(r.kondisi_pulang)}</div>` : ''}` : '<span class="small text-muted">Belum diisi</span>') : (v || '') },
          { data: null, orderable: false, searchable: false, className: 'text-end', render: (v, t, r) => t !== 'display' ? '' : (r.status === 'aktif'
              ? `<button type="button" class="btn btn-sm ${isi(r.cara_keluar) ? 'btn-success' : 'btn-outline-success'} sp-pulang" data-id="${r.id_ranap}" title="Pulangkan pasien"><i class="fas fa-door-open me-1"></i>Pulang</button>
                 <button type="button" class="btn btn-sm btn-light sp-pindah" data-id="${r.id_ranap}" title="Pindah bed"><i class="fas fa-right-left"></i></button>`
              : `<button type="button" class="btn btn-sm btn-light sp-aktifkan" data-id="${r.id_ranap}" title="Batalkan status pulang"><i class="fas fa-rotate-left me-1"></i>Batalkan pulang</button>`) }
        ]
      });
      const baris = id => table.rows().data().toArray().find(r => r.id_ranap === +id);
      const muat = () => table.ajax.reload(null, false);
      async function kirim(data) {
        const r = await fetch(API, { method: 'PUT', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, credentials: 'same-origin', body: new URLSearchParams(data) });
        const j = await r.json().catch(() => null);
        // dulu respons gagal tetap ditampilkan "Berhasil"
        if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal (HTTP ' + r.status + ').');
        return j;
      }
      const toast = t => Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: t, showConfirmButton: false, timer: 2200 });
      // Bootstrap mengabaikan hide() selama animasi buka -> tunda sampai modal selesai tampil
      const tutup = m => { if (m._isTransitioning) m._element.addEventListener('shown.bs.modal', () => m.hide(), { once: true }); else m.hide(); };

      $id('spTabs').addEventListener('click', e => {
        const b = e.target.closest('.btn[data-tab]');
        if (!b) return;
        tab = b.dataset.tab;
        document.querySelectorAll('#spTabs .btn').forEach(x => x.classList.toggle('active', x === b));
        $id('spRentang').classList.toggle('d-none', tab !== 'pulang');
        muat();
      });
      ['spDari', 'spSampai'].forEach(i => $id(i).addEventListener('change', muat));
      $id('spRefresh').addEventListener('click', muat);
      let tCari;
      $id('spCari').addEventListener('input', function() { clearTimeout(tCari); const v = this.value; tCari = setTimeout(() => table.search(v).draw(), 200); });

      /* ---------- pulangkan ---------- */
      const mPulang = bootstrap.Modal.getOrCreateInstance($id('spPulang'));
      let plId = null;
      const opsi = (wadah, nama, list, nilai) => { $id(wadah).innerHTML = list.map((v, i) => `<input type="radio" name="${nama}" id="${nama}${i}" value="${esc(v)}" ${v === nilai ? 'checked' : ''}><label for="${nama}${i}">${esc(v)}</label>`).join(''); };
      $(document).on('click', '.sp-pulang', function() {
        const r = baris(this.dataset.id);
        plId = r.id_ranap;
        $id('plSub').textContent = r.patient_name + ' · ' + (r.id_bed ? (r.bed_label || 'Bed') + ' ruang #' + (r.id_room || '') : 'tanpa bed') + ' · masuk ' + tgl(r.visit_date);
        const cara = CARA.find(c => c.toLowerCase() === String(r.cara_keluar || '').toLowerCase()) || 'Diizinkan Pulang';
        const kond = KONDISI.find(c => c.toLowerCase() === String(r.kondisi_pulang || '').toLowerCase()) || 'Membaik';
        opsi('plCara', 'pl_cara', CARA, cara);
        opsi('plKondisi', 'pl_kondisi', KONDISI, kond);
        mPulang.show();
      });
      $id('plSimpan').addEventListener('click', async function() {
        const b = this, label = b.innerHTML;
        b.disabled = true;
        b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const j = await kirim({ aksi: 'pulang', id_ranap: plId, cara_keluar: (document.querySelector('input[name=pl_cara]:checked') || {}).value || '', kondisi_pulang: (document.querySelector('input[name=pl_kondisi]:checked') || {}).value || '' });
          tutup(mPulang);
          toast(j.message);
          muat();
        } catch (e) { Swal.fire('Gagal', e.message, 'error'); } finally { b.disabled = false; b.innerHTML = label; }
      });

      /* ---------- pindah bed / batalkan pulang ---------- */
      const mBed = bootstrap.Modal.getOrCreateInstance($id('spBed'));
      let pb = { id: null, aksi: '', bed: null };
      function bukaBed(r, aksi) {
        pb = { id: r.id_ranap, aksi, bed: null };
        $id('pbJudul').textContent = aksi === 'pindah' ? 'Pindah bed' : 'Batalkan status pulang';
        $id('pbSub').textContent = r.patient_name + (r.id_bed ? ' · sekarang ' + (r.bed_label || '') + ' ruang #' + (r.id_room || '') : '');
        const semua = ruang.flatMap(x => x.bed.map(b => Object.assign({ ruang: x.nama, id_room: x.id_room }, b)));
        $id('pbList').innerHTML = semua.map(b => {
          const p = b.pasien.find(x => x.id_ranap !== r.id_ranap), milik = b.id_bed === r.id_bed;
          return `<button type="button" class="sp-bp ${milik && aksi === 'aktifkan' && !p ? 'pilih' : ''}" data-bed="${b.id_bed}" ${p || (milik && aksi === 'pindah') ? 'disabled' : ''}>
            <div class="fw-semibold"><i class="fas fa-bed me-1"></i>${esc(namaBed(b))} <span class="text-muted fw-normal small">ruang #${b.id_room}</span></div>
            <div class="small ${p ? 'text-danger' : 'text-success'}">${p ? esc(p.nama) : (milik ? 'bed sebelumnya' : 'kosong')}</div></button>`;
        }).join('') || '<div class="text-muted small">Tidak ada bed.</div>';
        const awal = $id('pbList').querySelector('.sp-bp.pilih');
        pb.bed = awal ? +awal.dataset.bed : null;
        $id('pbSimpan').disabled = !pb.bed;
        $id('pbInfo').textContent = '';
        mBed.show();
      }
      $(document).on('click', '.sp-pindah', function() { bukaBed(baris(this.dataset.id), 'pindah'); });
      $(document).on('click', '.sp-aktifkan', function() { bukaBed(baris(this.dataset.id), 'aktifkan'); });
      $id('pbList').addEventListener('click', e => {
        const b = e.target.closest('.sp-bp');
        if (!b || b.disabled) return;
        pb.bed = +b.dataset.bed;
        $id('pbList').querySelectorAll('.sp-bp').forEach(x => x.classList.toggle('pilih', x === b));
        $id('pbSimpan').disabled = false;
        $id('pbInfo').textContent = 'Bed dipilih: ' + b.querySelector('.fw-semibold').textContent.trim();
      });
      $id('pbSimpan').addEventListener('click', async function() {
        if (!pb.bed) return;
        const b = this, label = b.innerHTML;
        b.disabled = true;
        b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
        try {
          const j = await kirim({ aksi: pb.aksi, id_ranap: pb.id, id_bed: pb.bed });
          tutup(mBed);
          toast(j.message);
          muat();
        } catch (e) { Swal.fire('Gagal', e.message, 'error'); } finally { b.disabled = false; b.innerHTML = label; }
      });

      /* ---------- sinkron bed (admin) ---------- */
      $(document).on('click', '#spSinkron', function() {
        Swal.fire({ icon: 'question', title: 'Sinkronkan status bed?', html: '<div class="small">Bed yang ditempati pasien <b>sedang dirawat</b> ditandai terisi, bed lain ditandai kosong. Pastikan pasien yang sudah pulang sudah dipulangkan terlebih dahulu.</div>',
          showCancelButton: true, confirmButtonText: 'Ya, sinkronkan', cancelButtonText: 'Batal', reverseButtons: true })
          .then(async r => {
            if (!r.isConfirmed) return;
            try { const j = await kirim({ aksi: 'sinkron_bed' }); toast(j.message); muat(); } catch (e) { Swal.fire('Gagal', e.message, 'error'); }
          });
      });
    })();
  </script>
</body>

</html>
