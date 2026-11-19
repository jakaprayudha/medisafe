<?php
$title = 'Laporan Rawat Inap';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .rn-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .rn-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; transition: border-color .15s, box-shadow .15s; }
    .rn-kpi[data-status] { cursor: pointer; }
    .rn-kpi[data-status]:hover { border-color: #c9d2dc; box-shadow: 0 4px 14px rgba(0, 0, 0, .05); }
    .rn-kpi.on { border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 18%, transparent); }
    .rn-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.2; white-space: nowrap; }
    .rn-label { font-size: .75rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rn-sub { font-size: .7rem; color: #98a2b3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .rn-sec { font-size: .72rem; font-weight: 700; color: #5a6a85; text-transform: uppercase; letter-spacing: .4px; }
    .rn-chips .btn { border-radius: 20px; font-size: .75rem; padding: .2rem .7rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .rn-chips .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .rn-filter .form-label { font-size: .72rem; color: #6c757d; margin-bottom: 2px; }
    .rn-loading { position: relative; }
    .rn-loading::after { content: ''; position: absolute; inset: 0; background: rgba(255, 255, 255, .6); border-radius: inherit; z-index: 3; }
    .rn-bar { position: fixed; top: 0; left: 0; height: 3px; background: #5d87ff; z-index: 9999; transition: width .3s, opacity .3s; }
    .rn-badge { font-size: .7rem; font-weight: 600; padding: .25em .6em; border-radius: 20px; white-space: nowrap; }
    #rnTable td { font-size: .82rem; vertical-align: middle; white-space: nowrap; }
    #rnTable th { font-size: .75rem; color: #5a6a85; font-weight: 600; white-space: nowrap; }
    .rn-warn { background: #fff8ec; border: 1px solid #ffe2b3; border-radius: 10px; padding: 8px 12px; font-size: .8rem; color: #8a5a00; }
    .skel { display: inline-block; height: 1.1rem; width: 60px; border-radius: 6px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: sk 1.2s infinite; }
    @keyframes sk { to { background-position: -200% 0; } }
  </style>
</head>

<body>
  <div class="rn-bar" id="rnBar" style="width:0"></div>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HEADER + FILTER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                <span class="rn-icon bg-info-subtle text-info"><i class="fas fa-procedures"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Laporan Rawat Inap</h5>
                  <div class="text-muted small" id="rnPeriode">Pasien rawat inap berdasarkan tanggal masuk</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" class="btn btn-success btn-sm" id="btnExcel" disabled><i class="fas fa-file-excel me-1"></i>Export Excel</button>
                  <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm" id="btnPrint" disabled><i class="fas fa-print me-1"></i>Cetak</button>
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" id="btnPrintMenu" disabled><span class="visually-hidden">Pilihan cetak</span></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="#" data-cetak="lengkap"><i class="fas fa-table me-2 text-muted"></i>Ringkasan + tabel pasien</a></li>
                      <li><a class="dropdown-item" href="#" data-cetak="ringkasan"><i class="fas fa-chart-pie me-2 text-muted"></i>Ringkasan saja</a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="rn-chips d-flex flex-wrap gap-1 mb-3">
                <button type="button" class="btn" data-preset="7">7 hari</button>
                <button type="button" class="btn" data-preset="30">30 hari</button>
                <button type="button" class="btn" data-preset="bulan">Bulan ini</button>
                <button type="button" class="btn" data-preset="bulanlalu">Bulan lalu</button>
                <button type="button" class="btn" data-preset="3bulan">3 bulan</button>
                <button type="button" class="btn" data-preset="tahun">Tahun ini</button>
              </div>

              <div class="row g-2 rn-filter align-items-end">
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="fromDate">Masuk dari</label><input type="date" id="fromDate" class="form-control form-control-sm"></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="toDate">Sampai</label><input type="date" id="toDate" class="form-control form-control-sm"></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="doctorSelect">Dokter</label><select id="doctorSelect" class="form-select form-select-sm"><option value="">Semua dokter</option></select></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="roomSelect">Kamar</label><select id="roomSelect" class="form-select form-select-sm"><option value="">Semua kamar</option></select></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label" for="providerSelect">Jenis bayar</label><select id="providerSelect" class="form-select form-select-sm"><option value="">Semua jenis bayar</option></select></div>
                <div class="col-6 col-md-3 col-xl"><label class="form-label" for="statusSelect">Status</label>
                  <select id="statusSelect" class="form-select form-select-sm"><option value="">Semua</option><option value="dirawat">Dirawat</option><option value="pulang">Pulang</option><option value="belum">Belum ada CPPT</option></select></div>
                <div class="col-12 col-md-6 col-xl-auto d-grid"><button type="button" class="btn btn-light btn-sm" id="btnReset"><i class="fas fa-undo me-1"></i>Reset</button></div>
              </div>
              <div class="small text-danger mt-2" id="rnErr" hidden></div>
            </div>
          </div>

          <!-- KPI -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-xl-2"><div class="rn-kpi" data-status="" style="--c:#5d87ff" title="Tampilkan semua status"><div class="d-flex align-items-center gap-2 mb-1"><span class="rn-icon bg-primary-subtle text-primary"><i class="fas fa-user-plus"></i></span>
              <div class="min-w-0"><div class="rn-val" id="kMasuk"><span class="skel"></span></div><div class="rn-label">Pasien masuk</div></div></div><div class="rn-sub" id="kMasukSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rn-kpi" data-status="dirawat" style="--c:#539bff" title="Saring: masih dirawat"><div class="d-flex align-items-center gap-2 mb-1"><span class="rn-icon bg-info-subtle text-info"><i class="fas fa-bed"></i></span>
              <div class="min-w-0"><div class="rn-val" id="kDirawat"><span class="skel"></span></div><div class="rn-label">Sedang dirawat</div></div></div><div class="rn-sub" id="kDirawatSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rn-kpi" data-status="pulang" style="--c:#13deb9" title="Saring: sudah pulang"><div class="d-flex align-items-center gap-2 mb-1"><span class="rn-icon bg-success-subtle text-success"><i class="fas fa-door-open"></i></span>
              <div class="min-w-0"><div class="rn-val" id="kPulang"><span class="skel"></span></div><div class="rn-label">Pulang</div></div></div><div class="rn-sub" id="kPulangSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rn-kpi" title="Average length of stay — rata-rata lama rawat pasien yang sudah pulang"><div class="d-flex align-items-center gap-2 mb-1"><span class="rn-icon bg-warning-subtle text-warning"><i class="fas fa-hourglass-half"></i></span>
              <div class="min-w-0"><div class="rn-val" id="kAlos"><span class="skel"></span></div><div class="rn-label">Rata-rata lama rawat</div></div></div><div class="rn-sub" id="kAlosSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rn-kpi" title="Bed occupancy rate = hari rawat ÷ (jumlah bed × hari periode)"><div class="d-flex align-items-center gap-2 mb-1"><span class="rn-icon bg-danger-subtle text-danger"><i class="fas fa-chart-pie"></i></span>
              <div class="min-w-0"><div class="rn-val" id="kBor"><span class="skel"></span></div><div class="rn-label">BOR</div></div></div><div class="rn-sub" id="kBorSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="rn-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="rn-icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-heart"></i></span>
              <div class="min-w-0"><div class="rn-val" id="kSembuh"><span class="skel"></span></div><div class="rn-label">Pulang sembuh/membaik</div></div></div><div class="rn-sub" id="kSembuhSub">&nbsp;</div></div></div>
          </div>
          <div class="rn-warn mb-3" id="rnWarn" hidden></div>

          <!-- GRAFIK -->
          <div class="row g-3 mb-3">
            <div class="col-xl-8"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1"><div class="rn-sec" id="trenJudul">Pasien masuk &amp; pulang</div><div class="small text-muted" id="trenInfo"></div></div>
              <div id="chTren" style="min-height:270px"></div></div></div></div>
            <div class="col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="rn-sec mb-1">Kondisi pulang</div><div id="chKondisi" style="min-height:270px"></div></div></div></div>
            <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1"><div class="rn-sec">Lama rawat</div><div class="small text-muted" id="losInfo"></div></div>
              <div id="chLos" style="min-height:250px"></div></div></div></div>
            <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="rn-sec mb-1">Diagnosis terbanyak</div><div id="chDiag" style="min-height:250px"></div></div></div></div>
            <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1"><div class="rn-sec">Dokter penanggung jawab</div></div><div id="chDokter" style="min-height:250px"></div></div></div></div>
            <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="rn-sec mb-1">Kamar</div><div id="chKamar" style="min-height:230px"></div></div></div></div>
            <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1"><div class="rn-sec">Kelompok umur</div><div class="small text-muted" id="genderInfo"></div></div>
              <div id="chUmur" style="min-height:230px"></div></div></div></div>
            <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="rn-sec mb-1">Jenis bayar</div><div id="chBayar" style="min-height:230px"></div></div></div></div>
          </div>

          <!-- TABEL -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="mb-2"><h6 class="fw-semibold mb-0">Data pasien rawat inap</h6><div class="small text-muted" id="tabelInfo"></div></div>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="rnTable">
                  <thead><tr><th>Masuk</th><th>Pulang</th><th class="text-end">Lama</th><th>Status</th><th>Pasien</th><th>L/P · Umur</th><th>Dokter</th><th>Diagnosis</th><th>Kamar</th><th>Jenis Bayar</th><th>Kondisi Pulang</th><th class="text-end">CPPT</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
  <?php require 'library.php'; ?>
  <script>
    (function() {
      const API = 'controller/report/ranapController.php';
      const PALET = ['#5d87ff', '#13deb9', '#ffae1f', '#fa896b', '#a855f7', '#539bff', '#14b8a6', '#f472b6', '#94a3b8', '#64748b', '#0ea5e9'];
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const ST = { belum: ['Belum ada CPPT', 'bg-warning-subtle text-warning'], dirawat: ['Dirawat', 'bg-info-subtle text-info'], pulang: ['Pulang', 'bg-success-subtle text-success'] };
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const d1 = n => String(n).replace('.', ',');
      const ymd = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
      const tglIndo = s => { const t = String(s || '').split('-'); return t.length === 3 ? (+t[2]) + ' ' + BULAN[+t[1] - 1] + ' ' + t[0] : (t.length === 2 ? BULAN[+t[1] - 1] + ' ' + t[0] : s); };
      let D = null, ctrl = null, seq = 0;
      const charts = {};

      function filter() {
        return { fromDate: $id('fromDate').value, toDate: $id('toDate').value, doctor: $id('doctorSelect').value, room: $id('roomSelect').value, provider: $id('providerSelect').value, status: $id('statusSelect').value };
      }
      function qs(f, extra) {
        const q = new URLSearchParams();
        Object.entries(Object.assign({}, f, extra || {})).forEach(([k, v]) => { if (v !== '' && v != null) q.set(k, v); });
        return q.toString();
      }
      function preset(p) {
        const n = new Date();
        let a = new Date(n), b = new Date(n);
        if (p === '7') a.setDate(a.getDate() - 6);
        else if (p === '30') a.setDate(a.getDate() - 29);
        else if (p === 'bulan') a = new Date(n.getFullYear(), n.getMonth(), 1);
        else if (p === 'bulanlalu') { a = new Date(n.getFullYear(), n.getMonth() - 1, 1); b = new Date(n.getFullYear(), n.getMonth(), 0); }
        else if (p === '3bulan') a = new Date(n.getFullYear(), n.getMonth() - 2, 1);
        else if (p === 'tahun') a = new Date(n.getFullYear(), 0, 1);
        return [ymd(a), ymd(b)];
      }
      function tandaiPreset() {
        const f = $id('fromDate').value, t = $id('toDate').value;
        document.querySelectorAll('[data-preset]').forEach(b => { const [a, z] = preset(b.dataset.preset); b.classList.toggle('active', a === f && z === t); });
      }

      let tunda;
      const muatNanti = () => { clearTimeout(tunda); tunda = setTimeout(muat, 250); };
      async function muat() {
        const f = filter(), err = $id('rnErr');
        if (!f.fromDate || !f.toDate) return;
        if (f.toDate < f.fromDate) { err.textContent = 'Tanggal "Sampai" lebih awal dari "Masuk dari".'; err.hidden = false; return; }
        if ((new Date(f.toDate) - new Date(f.fromDate)) / 864e5 + 1 > 366) { err.textContent = 'Rentang maksimal 366 hari.'; err.hidden = false; return; }
        err.hidden = true;
        tandaiPreset();
        try { history.replaceState(null, '', location.pathname + '?' + qs(f)); } catch (e) {}
        if (ctrl) ctrl.abort();
        ctrl = new AbortController();
        const ini = ++seq;
        sibuk(true);
        try {
          const r = await fetch(API + '?' + qs(f), { signal: ctrl.signal, credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (ini !== seq) return;
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat laporan (HTTP ' + r.status + ').');
          D = j;
          isiOpsi(j.opsi, f);
          tampil();
        } catch (e) {
          if (e.name === 'AbortError' || ini !== seq) return;
          D = null;
          tabel.clear().draw();
          $id('rnTable').querySelector('tbody').innerHTML = `<tr><td colspan="12" class="text-center text-danger py-5"><i class="fas fa-exclamation-circle fa-2x d-block mb-2"></i>${esc(e.message)}<div class="mt-2"><button class="btn btn-sm btn-primary" id="rnRetry">Coba lagi</button></div></td></tr>`;
          $id('rnRetry').onclick = muat;
          ['kMasuk', 'kDirawat', 'kPulang', 'kAlos', 'kBor', 'kSembuh'].forEach(id => $id(id).textContent = '-');
          ['btnExcel', 'btnPrint', 'btnPrintMenu'].forEach(id => $id(id).disabled = true);
        } finally {
          if (ini === seq) sibuk(false);
        }
      }
      function sibuk(on) {
        const bar = $id('rnBar');
        if (on) { bar.style.opacity = 1; bar.style.width = '70%'; } else { bar.style.width = '100%'; setTimeout(() => { bar.style.opacity = 0; bar.style.width = 0; }, 300); }
        document.querySelectorAll('.rn-kpi, [id^=ch]').forEach(el => el.classList.toggle('rn-loading', on));
      }
      function isiOpsi(o, f) {
        if (!o) return;
        const set = (id, list, semua, val) => {
          const sel = $id(id), ada = list.some(x => String(x.v) === String(val));
          sel.innerHTML = `<option value="">${semua}</option>` + list.map(x => `<option value="${esc(x.v)}">${esc(x.t)}</option>`).join('') + (val && !ada ? `<option value="${esc(val)}">${esc(val)}</option>` : '');
          sel.value = val || '';
        };
        set('doctorSelect', o.dokter.map(d => ({ v: d, t: d })), 'Semua dokter', f.doctor);
        set('providerSelect', o.provider.map(d => ({ v: d.id_provider, t: d.provider_name })), 'Semua jenis bayar', f.provider);
        set('roomSelect', o.kamar.map(d => ({ v: d.id_room, t: d.room_name + (d.service_class && d.service_class !== d.room_name ? ' · ' + d.service_class : '') + ' (' + d.bed + ' bed)' })), 'Semua kamar', f.room);
      }

      function tampil() {
        const r = D.ringkasan, f = D.filter;
        $id('rnPeriode').innerHTML = '<i class="far fa-calendar me-1"></i>Masuk ' + esc(tglIndo(f.from)) + (f.from !== f.to ? ' – ' + esc(tglIndo(f.to)) : '') + ' · ' + f.hari + ' hari · ' + esc(f.label);
        $id('kMasuk').textContent = fmt(r.total);
        $id('kMasukSub').textContent = 'Rata-rata ' + d1(Math.round(r.total / Math.max(1, f.hari) * 10) / 10) + ' / hari';
        $id('kDirawat').textContent = fmt(r.sedang_dirawat);
        $id('kDirawatSub').textContent = 'Saat ini · ' + fmt(r.status.dirawat) + ' dari periode ini';
        $id('kPulang').textContent = fmt(r.status.pulang);
        $id('kPulangSub').textContent = r.total ? Math.round(r.status.pulang / r.total * 100) + '% dari pasien masuk' : ' ';
        $id('kAlos').textContent = r.alos ? d1(r.alos) + ' hari' : '-';
        $id('kAlosSub').textContent = r.alos ? 'Median ' + d1(r.los_median) + ' hari' : 'Belum ada pasien pulang';
        $id('kBor').textContent = r.bor === null ? '-' : d1(r.bor) + '%';
        $id('kBorSub').textContent = r.bed ? fmt(r.hari_pasien) + ' hari rawat · ' + fmt(r.bed) + ' bed' : 'Data bed belum diisi';
        $id('kSembuh').textContent = r.status.pulang ? d1(r.pct_sembuh) + '%' : '-';
        $id('kSembuhSub').textContent = 'dari ' + fmt(r.status.pulang) + ' pasien pulang';
        document.querySelectorAll('.rn-kpi[data-status]').forEach(el => el.classList.toggle('on', f.status !== '' && el.dataset.status === f.status));

        const w = [];
        if (r.status.belum) w.push(`<b>${fmt(r.status.belum)}</b> pasien belum punya CPPT`);
        if (r.los_tidak_valid) w.push(`<b>${fmt(r.los_tidak_valid)}</b> pasien pulang dengan tanggal pulang kosong/tidak wajar (tidak dihitung di lama rawat, ditandai <b>?</b>)`);
        $id('rnWarn').hidden = !w.length;
        $id('rnWarn').innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Perlu dicek: ' + w.join(' · ');

        grafik(r);
        isiTabel();
        ['btnExcel', 'btnPrint', 'btnPrintMenu'].forEach(id => $id(id).disabled = !r.total);
      }

      function chart(id, opt) {
        const el = $id(id);
        if (typeof ApexCharts === 'undefined') { el.innerHTML = '<div class="text-center text-muted small py-5">Grafik tidak tersedia.</div>'; return; }
        if (charts[id]) { charts[id].updateOptions(opt, true, false); return; }
        charts[id] = new ApexCharts(el, Object.assign({}, opt, { chart: Object.assign({ fontFamily: 'inherit', toolbar: { show: false }, animations: { enabled: false }, parentHeightOffset: 0 }, opt.chart) }));
        charts[id].render();
      }
      const kosong = { noData: { text: 'Tidak ada data', style: { color: '#98a2b3' } } };
      const grid = { borderColor: '#eef1f4', strokeDashArray: 3 };
      const barH = (id, data, h, warna) => chart(id, Object.assign({}, kosong, {
        chart: { type: 'bar', height: h },
        series: [{ name: 'Pasien', data: data.map(d => d.jumlah) }],
        xaxis: { categories: data.map(d => d.nama.length > 30 ? d.nama.slice(0, 29) + '…' : d.nama), labels: { formatter: v => fmt(v) } },
        yaxis: { labels: { maxWidth: 190, style: { fontSize: '11px' } } },
        plotOptions: { bar: { horizontal: true, barHeight: '62%', borderRadius: 3, distributed: !warna } },
        colors: warna ? [warna] : PALET, legend: { show: false },
        dataLabels: { enabled: true, formatter: v => fmt(v), style: { fontSize: '10px' } }, grid,
        tooltip: { y: { formatter: v => fmt(v) } }
      }));
      const donut = (id, data, h, label) => chart(id, Object.assign({}, kosong, {
        chart: { type: 'donut', height: h },
        series: data.map(d => d.jumlah), labels: data.map(d => d.nama), colors: PALET,
        legend: { position: 'bottom', fontSize: '11px' }, dataLabels: { formatter: v => Math.round(v) + '%' },
        plotOptions: { pie: { donut: { size: '62%', labels: { show: true, total: { show: true, label, formatter: w => fmt(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } },
        tooltip: { y: { formatter: v => fmt(v) } }
      }));

      function grafik(r) {
        const t = r.tren;
        const lab = t.map(x => r.periode === 'hari' ? (+x.t.slice(8)) + ' ' + BULAN[+x.t.slice(5, 7) - 1] : tglIndo(x.t));
        $id('trenJudul').textContent = 'Pasien masuk & pulang (' + (r.periode === 'hari' ? 'harian' : 'bulanan') + ')';
        const puncak = t.reduce((m, x) => x.masuk > m.n ? { n: x.masuk, t: x.t } : m, { n: 0, t: null });
        $id('trenInfo').textContent = puncak.t ? 'Masuk terbanyak ' + fmt(puncak.n) + ' pada ' + tglIndo(puncak.t) : '';
        chart('chTren', Object.assign({}, kosong, {
          chart: { type: 'bar', height: 270 },
          series: [{ name: 'Masuk', data: t.map(x => x.masuk) }, { name: 'Pulang', data: t.map(x => x.pulang) }],
          colors: ['#5d87ff', '#13deb9'],
          xaxis: { categories: lab, labels: { rotate: -45, hideOverlappingLabels: true, style: { fontSize: '10px' } }, tickAmount: Math.min(t.length, 16) },
          yaxis: { labels: { formatter: v => fmt(Math.round(v)) } },
          plotOptions: { bar: { columnWidth: t.length > 40 ? '85%' : '60%', borderRadius: 2 } },
          dataLabels: { enabled: false }, legend: { position: 'top', horizontalAlign: 'left', fontSize: '11px' }, grid,
          tooltip: { shared: true, intersect: false, y: { formatter: v => fmt(v) } }
        }));
        donut('chKondisi', r.kondisi, 270, 'Pulang');
        const losTot = r.los_bin.reduce((a, b) => a + b.jumlah, 0);
        $id('losInfo').textContent = losTot ? 'ALOS ' + d1(r.alos) + ' hari' : '';
        chart('chLos', Object.assign({}, kosong, {
          chart: { type: 'bar', height: 250 },
          series: [{ name: 'Pasien pulang', data: losTot ? r.los_bin.map(b => b.jumlah) : [] }],
          xaxis: { categories: r.los_bin.map(b => b.nama), labels: { style: { fontSize: '10px' } } },
          colors: ['#ffae1f'], plotOptions: { bar: { columnWidth: '60%', borderRadius: 3 } },
          dataLabels: { enabled: true, formatter: v => v ? fmt(v) : '', style: { fontSize: '10px', colors: ['#5a3d00'] } }, grid,
          yaxis: { labels: { formatter: v => fmt(Math.round(v)) } }, tooltip: { y: { formatter: v => fmt(v) + ' pasien' } }
        }));
        barH('chDiag', r.diagnosa.map(d => ({ nama: d.kode + (d.nama ? ' ' + d.nama : ''), jumlah: d.jumlah })), Math.max(250, r.diagnosa.length * 26 + 30), '#a855f7');
        barH('chDokter', r.dokter, Math.max(250, r.dokter.length * 28 + 30));
        barH('chKamar', r.kamar, Math.max(230, r.kamar.length * 28 + 30), '#ffae1f');
        chart('chUmur', Object.assign({}, kosong, {
          chart: { type: 'bar', height: 230 },
          series: [{ name: 'Pasien', data: r.total ? r.umur.filter(u => u.nama !== '-' || u.jumlah).map(u => u.jumlah) : [] }],
          xaxis: { categories: r.umur.filter(u => u.nama !== '-' || u.jumlah).map(u => u.nama === '-' ? 'Tidak diketahui' : u.nama + ' th') },
          colors: ['#539bff'], plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
          dataLabels: { enabled: true, formatter: v => v ? fmt(v) : '', style: { fontSize: '10px', colors: ['#0b3a75'] } }, grid,
          yaxis: { labels: { formatter: v => fmt(Math.round(v)) } }, tooltip: { y: { formatter: v => fmt(v) } }
        }));
        $id('genderInfo').innerHTML = r.total ? `<i class="fas fa-mars text-primary"></i> ${fmt(r.gender.L)} · <i class="fas fa-venus text-danger"></i> ${fmt(r.gender.P)}` : '';
        donut('chBayar', r.bayar, 230, 'Pasien');
      }

      const jq = window.jQuery;
      const tabel = jq('#rnTable').DataTable({
        data: [], deferRender: true, pageLength: 25, lengthMenu: [10, 25, 50, 100], order: [[0, 'desc']], autoWidth: false,
        language: { search: 'Cari:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ pasien', infoEmpty: '0 pasien', infoFiltered: '(disaring dari _MAX_)', zeroRecords: 'Tidak ada data yang cocok', emptyTable: 'Tidak ada pasien rawat inap pada periode ini', paginate: { previous: '‹', next: '›' } },
        columns: [
          { data: 'urut', render: (d, t, r) => t === 'display' ? `<span class="fw-semibold">${esc(tglIndo(r.masuk))}</span> <span class="small text-muted">${esc(r.jam)}</span>` : d },
          { data: 'pulang', render: (d, t, r) => t !== 'display' ? (d || '') : (d ? `${esc(tglIndo(d))} <span class="small text-muted">${esc(r.jam_pulang)}</span>` : '<span class="text-muted">-</span>') },
          { data: 'los', className: 'text-end', render: (d, t, r) => t !== 'display' ? (d ?? -1) : (d !== null ? d + ' hr' : (r.los_valid ? '<span class="text-muted">-</span>' : '<span class="text-warning fw-semibold" title="Tanggal pulang tidak wajar">?</span>')) },
          { data: 'status', render: (d, t) => t === 'display' ? `<span class="rn-badge ${ST[d][1]}">${ST[d][0]}</span>` : ST[d][0] },
          { data: 'nama', render: (d, t, r) => t === 'display' ? `<div class="fw-semibold">${esc(d)}</div><div class="small text-muted">${esc(r.rm || '-')}</div>` : d + ' ' + r.rm + ' ' + r.nik + ' ' + r.noKartu },
          { data: 'umur', render: (d, t, r) => t === 'display' ? esc(r.gender) + ' · ' + (d ?? '-') : (d ?? -1) },
          { data: 'dokter', render: (d, t) => t === 'display' ? esc(d) : d },
          { data: 'diagnosa', render: (d, t) => t === 'display' ? (d ? `<span class="fw-semibold">${esc(d)}</span>` : '<span class="text-muted">-</span>') : d },
          { data: 'kamar', render: (d, t, r) => t === 'display' ? (d ? esc(d) + (r.bed && r.bed !== '-' ? ' <span class="small text-muted">/ ' + esc(r.bed) + '</span>' : '') : '<span class="text-muted">-</span>') : d },
          { data: 'bayar', render: (d, t) => t === 'display' ? esc(d) : d },
          { data: 'kondisi', render: (d, t) => t === 'display' ? (d ? esc(d) : '<span class="text-muted">-</span>') : d },
          { data: 'cppt', className: 'text-end', render: (d, t) => t === 'display' ? (d ? fmt(d) : '<span class="text-warning">0</span>') : d }
        ]
      });
      function isiTabel() {
        const k = D.kolom;
        const rows = D.data.map(a => { const o = {}; k.forEach((c, i) => o[c] = a[i]); o.urut = o.masuk + ' ' + o.jam; return o; });
        tabel.clear().rows.add(rows).draw();
        $id('tabelInfo').textContent = fmt(rows.length) + ' pasien · tanggal masuk = tanggal kunjungan';
      }

      $id('btnExcel').addEventListener('click', () => {
        const btn = $id('btnExcel'), label = btn.innerHTML, token = Date.now().toString(36);
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyiapkan…';
        const selesai = () => { clearInterval(cek); btn.innerHTML = label; btn.disabled = !(D && D.ringkasan.total); };
        const cek = setInterval(() => { if (document.cookie.includes('rn_unduh=' + token)) selesai(); }, 500);
        setTimeout(selesai, 120000);
        const ifr = document.createElement('iframe');
        ifr.hidden = true;
        ifr.addEventListener('load', () => {
          if (document.cookie.includes('rn_unduh=' + token)) return;
          let pesan = '';
          try { pesan = (ifr.contentDocument.body.innerText || '').trim().slice(0, 300); } catch (e) {}
          selesai();
          Swal.fire('Export gagal', pesan || 'Server tidak mengirim file Excel.', 'error');
        });
        ifr.src = 'module/report/export_ranap.php?' + qs(filter(), { token });
        document.body.appendChild(ifr);
        setTimeout(() => ifr.remove(), 130000);
      });
      const cetak = isi => window.open('module/report/print_ranap.php?' + qs(filter(), { isi }), '_blank');
      $id('btnPrint').addEventListener('click', () => cetak('lengkap'));
      document.querySelectorAll('[data-cetak]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); cetak(a.dataset.cetak); }));

      document.querySelectorAll('[data-preset]').forEach(b => b.addEventListener('click', () => {
        const [a, z] = preset(b.dataset.preset);
        $id('fromDate').value = a; $id('toDate').value = z;
        muat();
      }));
      ['fromDate', 'toDate'].forEach(id => $id(id).addEventListener('change', muatNanti));
      ['doctorSelect', 'roomSelect', 'providerSelect', 'statusSelect'].forEach(id => $id(id).addEventListener('change', muat));
      document.querySelectorAll('.rn-kpi[data-status]').forEach(el => el.addEventListener('click', () => {
        $id('statusSelect').value = $id('statusSelect').value === el.dataset.status ? '' : el.dataset.status;
        muat();
      }));
      $id('btnReset').addEventListener('click', () => {
        const [a, z] = preset('bulan');
        $id('fromDate').value = a; $id('toDate').value = z;
        ['doctorSelect', 'roomSelect', 'providerSelect', 'statusSelect'].forEach(id => $id(id).value = '');
        muat();
      });

      const u = new URLSearchParams(location.search);
      const [a, z] = preset('bulan');
      $id('fromDate').value = u.get('fromDate') || a;
      $id('toDate').value = u.get('toDate') || z;
      if (['dirawat', 'pulang', 'belum'].includes(u.get('status'))) $id('statusSelect').value = u.get('status');
      ['doctor:doctorSelect', 'room:roomSelect', 'provider:providerSelect'].forEach(p => {
        const [k, id] = p.split(':');
        if (u.get(k)) $id(id).innerHTML = `<option value="${esc(u.get(k))}" selected>${esc(u.get(k))}</option>`;
      });
      muat();
    })();
  </script>
</body>

</html>
