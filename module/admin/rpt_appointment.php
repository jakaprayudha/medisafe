<?php
$title = 'Laporan Appointment';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .ap-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .ap-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; cursor: default; transition: border-color .15s, box-shadow .15s; }
    .ap-kpi[data-status] { cursor: pointer; }
    .ap-kpi[data-status]:hover { border-color: #c9d2dc; box-shadow: 0 4px 14px rgba(0, 0, 0, .05); }
    .ap-kpi.on { border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 18%, transparent); }
    .ap-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.2; white-space: nowrap; }
    .ap-label { font-size: .75rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ap-sub { font-size: .7rem; color: #98a2b3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ap-sec { font-size: .72rem; font-weight: 700; color: #5a6a85; text-transform: uppercase; letter-spacing: .4px; }
    .ap-presets .btn { border-radius: 20px; font-size: .75rem; padding: .2rem .7rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .ap-presets .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .ap-filter .form-label { font-size: .72rem; color: #6c757d; margin-bottom: 2px; }
    .ap-chart { min-height: 260px; }
    .ap-chart-sm { min-height: 240px; }
    .ap-loading { position: relative; }
    .ap-loading::after { content: ''; position: absolute; inset: 0; background: rgba(255, 255, 255, .6); border-radius: inherit; z-index: 3; }
    .ap-bar { position: absolute; top: 0; left: 0; height: 3px; background: #5d87ff; z-index: 9999; transition: width .3s; }
    .ap-badge { font-size: .7rem; font-weight: 600; padding: .25em .6em; border-radius: 20px; white-space: nowrap; }
    #apTable td { font-size: .82rem; vertical-align: middle; white-space: nowrap; }
    #apTable th { font-size: .75rem; color: #5a6a85; font-weight: 600; white-space: nowrap; }
    .ap-empty { padding: 50px 10px; text-align: center; color: #6c757d; }
    .skel { display: inline-block; height: 1.1rem; width: 60px; border-radius: 6px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: sk 1.2s infinite; }
    @keyframes sk { to { background-position: -200% 0; } }
  </style>
</head>

<body>
  <div class="ap-bar" id="apBar" style="width:0"></div>
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
                <span class="ap-icon bg-primary-subtle text-primary"><i class="fas fa-calendar-check"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Laporan Appointment</h5>
                  <div class="text-muted small" id="apPeriodeLabel">Pendaftaran &amp; kunjungan pasien per periode</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" class="btn btn-success btn-sm" id="btnExcel" disabled>
                    <i class="fas fa-file-excel me-1"></i>Export Excel
                  </button>
                  <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm" id="btnPrint" disabled><i class="fas fa-print me-1"></i>Cetak</button>
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" id="btnPrintMenu" disabled>
                      <span class="visually-hidden">Pilihan cetak</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="#" data-cetak="lengkap"><i class="fas fa-table me-2 text-muted"></i>Ringkasan + tabel data</a></li>
                      <li><a class="dropdown-item" href="#" data-cetak="ringkasan"><i class="fas fa-chart-pie me-2 text-muted"></i>Ringkasan saja</a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="ap-presets d-flex flex-wrap gap-1 mb-3" id="apPresets">
                <button type="button" class="btn" data-preset="hari">Hari ini</button>
                <button type="button" class="btn" data-preset="kemarin">Kemarin</button>
                <button type="button" class="btn" data-preset="7">7 hari</button>
                <button type="button" class="btn" data-preset="30">30 hari</button>
                <button type="button" class="btn" data-preset="bulan">Bulan ini</button>
                <button type="button" class="btn" data-preset="bulanlalu">Bulan lalu</button>
                <button type="button" class="btn" data-preset="tahun">Tahun ini</button>
              </div>

              <div class="row g-2 ap-filter align-items-end">
                <div class="col-6 col-md-3 col-xl-2">
                  <label class="form-label" for="fromDate">Dari</label>
                  <input type="date" id="fromDate" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                  <label class="form-label" for="toDate">Sampai</label>
                  <input type="date" id="toDate" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                  <label class="form-label" for="doctorSelect">Dokter</label>
                  <select id="doctorSelect" class="form-select form-select-sm"><option value="">Semua dokter</option></select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                  <label class="form-label" for="poliSelect">Poli</label>
                  <select id="poliSelect" class="form-select form-select-sm"><option value="">Semua poli</option></select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                  <label class="form-label" for="providerSelect">Jenis bayar</label>
                  <select id="providerSelect" class="form-select form-select-sm"><option value="">Semua jenis bayar</option></select>
                </div>
                <div class="col-6 col-md-3 col-xl">
                  <label class="form-label" for="genderSelect">Gender</label>
                  <select id="genderSelect" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="pria">Laki-laki</option>
                    <option value="wanita">Perempuan</option>
                  </select>
                </div>
                <div class="col-12 col-md-6 col-xl-auto d-grid">
                  <button type="button" class="btn btn-light btn-sm" id="btnReset" title="Reset filter"><i class="fas fa-undo me-1"></i>Reset</button>
                </div>
              </div>
              <div class="small text-danger mt-2" id="apFilterErr" hidden></div>
            </div>
          </div>

          <!-- KPI -->
          <div class="row g-3 mb-3" id="apKpi">
            <div class="col-6 col-md-4 col-xl-2">
              <div class="ap-kpi" data-status="" style="--c:#5d87ff" title="Tampilkan semua status">
                <div class="d-flex align-items-center gap-2 mb-1"><span class="ap-icon bg-primary-subtle text-primary"><i class="fas fa-users"></i></span>
                  <div class="min-w-0"><div class="ap-val" id="kTotal"><span class="skel"></span></div><div class="ap-label">Pendaftaran</div></div></div>
                <div class="ap-sub" id="kTotalSub">&nbsp;</div>
              </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
              <div class="ap-kpi" data-status="selesai" style="--c:#13deb9" title="Saring: selesai">
                <div class="d-flex align-items-center gap-2 mb-1"><span class="ap-icon bg-success-subtle text-success"><i class="fas fa-check-circle"></i></span>
                  <div class="min-w-0"><div class="ap-val" id="kSelesai"><span class="skel"></span></div><div class="ap-label">Selesai</div></div></div>
                <div class="ap-sub" id="kSelesaiSub">&nbsp;</div>
              </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
              <div class="ap-kpi" data-status="proses" style="--c:#539bff" title="Saring: dalam proses">
                <div class="d-flex align-items-center gap-2 mb-1"><span class="ap-icon bg-info-subtle text-info"><i class="fas fa-hourglass-half"></i></span>
                  <div class="min-w-0"><div class="ap-val" id="kProses"><span class="skel"></span></div><div class="ap-label">Dalam proses</div></div></div>
                <div class="ap-sub">Belum dilayani / menunggu</div>
              </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
              <div class="ap-kpi" data-status="booking" style="--c:#ffae1f" title="Saring: belum check-in">
                <div class="d-flex align-items-center gap-2 mb-1"><span class="ap-icon bg-warning-subtle text-warning"><i class="fas fa-calendar-alt"></i></span>
                  <div class="min-w-0"><div class="ap-val" id="kBooking"><span class="skel"></span></div><div class="ap-label">Belum check-in</div></div></div>
                <div class="ap-sub">Booking / antrean online</div>
              </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
              <div class="ap-kpi" data-status="batal" style="--c:#fa896b" title="Saring: batal">
                <div class="d-flex align-items-center gap-2 mb-1"><span class="ap-icon bg-danger-subtle text-danger"><i class="fas fa-times-circle"></i></span>
                  <div class="min-w-0"><div class="ap-val" id="kBatal"><span class="skel"></span></div><div class="ap-label">Batal</div></div></div>
                <div class="ap-sub" id="kBatalSub">&nbsp;</div>
              </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
              <div class="ap-kpi" style="--c:#a855f7">
                <div class="d-flex align-items-center gap-2 mb-1"><span class="ap-icon" style="background:#f3e8ff;color:#a855f7"><i class="fas fa-user-check"></i></span>
                  <div class="min-w-0"><div class="ap-val" id="kUnik"><span class="skel"></span></div><div class="ap-label">Pasien unik</div></div></div>
                <div class="ap-sub" id="kUnikSub">&nbsp;</div>
              </div>
            </div>
          </div>

          <!-- GRAFIK -->
          <div class="row g-3 mb-3">
            <div class="col-xl-8">
              <div class="card border-0 shadow-sm h-100 mb-0">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="ap-sec" id="trenJudul">Tren harian</div>
                    <div class="small text-muted" id="trenInfo"></div>
                  </div>
                  <div id="chTren" class="ap-chart"></div>
                </div>
              </div>
            </div>
            <div class="col-xl-4">
              <div class="card border-0 shadow-sm h-100 mb-0">
                <div class="card-body">
                  <div class="ap-sec mb-1">Jenis bayar</div>
                  <div id="chBayar" class="ap-chart"></div>
                </div>
              </div>
            </div>
            <div class="col-xl-5">
              <div class="card border-0 shadow-sm h-100 mb-0">
                <div class="card-body">
                  <div class="ap-sec mb-1">Kunjungan per dokter</div>
                  <div id="chDokter" class="ap-chart-sm"></div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-xl-3">
              <div class="card border-0 shadow-sm h-100 mb-0">
                <div class="card-body">
                  <div class="ap-sec mb-1">Poli</div>
                  <div id="chPoli" class="ap-chart-sm"></div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-xl-4">
              <div class="card border-0 shadow-sm h-100 mb-0">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="ap-sec">Jam kedatangan</div>
                    <div class="small text-muted" id="genderInfo"></div>
                  </div>
                  <div id="chJam" class="ap-chart-sm"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- TABEL -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div>
                  <h6 class="fw-semibold mb-0">Data appointment</h6>
                  <div class="small text-muted" id="tabelInfo"></div>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="apTable">
                  <thead>
                    <tr>
                      <th>Tanggal</th><th>Status</th><th>Nama Pasien</th><th>L/P</th><th>No. BPJS</th><th>NIK</th><th>Dokter</th><th>Poli</th><th>Jenis Bayar</th>
                    </tr>
                  </thead>
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
      const API = 'controller/report/appointmentController.php';
      const WARNA = { selesai: '#13deb9', proses: '#539bff', booking: '#ffae1f', batal: '#fa896b' };
      const LABEL = { selesai: 'Selesai', proses: 'Dalam proses', booking: 'Belum check-in', batal: 'Batal' };
      const BADGE = { selesai: 'bg-success-subtle text-success', proses: 'bg-info-subtle text-info', booking: 'bg-warning-subtle text-warning', batal: 'bg-danger-subtle text-danger' };
      const PALET = ['#5d87ff', '#13deb9', '#ffae1f', '#fa896b', '#a855f7', '#539bff', '#14b8a6', '#f472b6', '#94a3b8'];
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const pct = n => String(n).replace('.', ',') + '%';
      const ymd = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
      const tglIndo = s => { const t = String(s).split('-'); return t.length === 3 ? (+t[2]) + ' ' + BULAN[+t[1] - 1] + ' ' + t[0] : (t.length === 2 ? BULAN[+t[1] - 1] + ' ' + t[0] : s); };

      let status = '';
      let terakhir = null; // respons terakhir
      let ctrl = null, seq = 0;
      const charts = {};

      /* ---------- filter & URL ---------- */
      function filter() {
        return {
          fromDate: $id('fromDate').value,
          toDate: $id('toDate').value,
          doctor: $id('doctorSelect').value,
          poli: $id('poliSelect').value,
          provider: $id('providerSelect').value,
          gender: $id('genderSelect').value,
          status
        };
      }
      function qs(f, extra) {
        const q = new URLSearchParams();
        Object.entries(Object.assign({}, f, extra || {})).forEach(([k, v]) => { if (v !== '' && v != null) q.set(k, v); });
        return q.toString();
      }
      function simpanUrl() {
        try { history.replaceState(null, '', location.pathname + '?' + qs(filter())); } catch (e) {}
      }

      function preset(p) {
        const n = new Date();
        let a = new Date(n), b = new Date(n);
        if (p === 'kemarin') { a.setDate(a.getDate() - 1); b = new Date(a); }
        else if (p === '7') a.setDate(a.getDate() - 6);
        else if (p === '30') a.setDate(a.getDate() - 29);
        else if (p === 'bulan') a = new Date(n.getFullYear(), n.getMonth(), 1);
        else if (p === 'bulanlalu') { a = new Date(n.getFullYear(), n.getMonth() - 1, 1); b = new Date(n.getFullYear(), n.getMonth(), 0); }
        else if (p === 'tahun') a = new Date(n.getFullYear(), 0, 1);
        return [ymd(a), ymd(b)];
      }
      function tandaiPreset() {
        const f = $id('fromDate').value, t = $id('toDate').value;
        document.querySelectorAll('[data-preset]').forEach(b => {
          const [a, z] = preset(b.dataset.preset);
          b.classList.toggle('active', a === f && z === t);
        });
      }

      /* ---------- muat data ---------- */
      let tunda;
      function muatNanti() { clearTimeout(tunda); tunda = setTimeout(muat, 250); }

      async function muat() {
        const f = filter();
        const err = $id('apFilterErr');
        if (!f.fromDate || !f.toDate) return;
        if (f.toDate < f.fromDate) { err.textContent = 'Tanggal "Sampai" lebih awal dari "Dari".'; err.hidden = false; return; }
        const hari = (new Date(f.toDate) - new Date(f.fromDate)) / 864e5 + 1;
        if (hari > 366) { err.textContent = 'Rentang maksimal 366 hari.'; err.hidden = false; return; }
        err.hidden = true;
        tandaiPreset();
        simpanUrl();

        if (ctrl) ctrl.abort(); // batalkan permintaan lama agar respons basi tidak menimpa
        ctrl = new AbortController();
        const ini = ++seq;
        sibuk(true);
        try {
          const r = await fetch(API + '?' + qs(f), { signal: ctrl.signal, credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (ini !== seq) return;
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat laporan (HTTP ' + r.status + ').');
          terakhir = j;
          isiOpsi(j.opsi, f);
          tampil(j);
        } catch (e) {
          if (e.name === 'AbortError' || ini !== seq) return;
          terakhir = null;
          $id('tabelInfo').textContent = '';
          tabel.clear().draw();
          $id('apTable').querySelector('tbody').innerHTML = `<tr><td colspan="9"><div class="ap-empty text-danger"><i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>${esc(e.message)}<div class="mt-2"><button class="btn btn-sm btn-primary" id="apRetry">Coba lagi</button></div></div></td></tr>`;
          $id('apRetry').onclick = muat;
          ['btnExcel', 'btnPrint', 'btnPrintMenu'].forEach(id => $id(id).disabled = true);
          ['kTotal', 'kSelesai', 'kProses', 'kBooking', 'kBatal', 'kUnik'].forEach(id => $id(id).textContent = '-');
        } finally {
          if (ini === seq) sibuk(false);
        }
      }

      function sibuk(on) {
        const bar = $id('apBar');
        if (on) { bar.style.opacity = 1; bar.style.width = '70%'; }
        else { bar.style.width = '100%'; setTimeout(() => { bar.style.opacity = 0; bar.style.width = 0; }, 300); }
        document.querySelectorAll('#apKpi .ap-kpi, .ap-chart, .ap-chart-sm').forEach(el => el.classList.toggle('ap-loading', on));
      }

      function isiOpsi(o, f) {
        if (!o) return;
        const set = (id, list, semua, val, labelFn) => {
          const sel = $id(id);
          const ada = list.some(x => String(x.v) === String(val));
          sel.innerHTML = `<option value="">${semua}</option>` + list.map(x => `<option value="${esc(x.v)}">${esc(x.t)}</option>`).join('') +
            (val && !ada ? `<option value="${esc(val)}">${esc(val)} (tidak ada di periode ini)</option>` : '');
          sel.value = val || '';
        };
        set('doctorSelect', o.dokter.map(d => ({ v: d, t: d })), 'Semua dokter', f.doctor);
        set('poliSelect', o.poli.map(d => ({ v: d, t: d })), 'Semua poli', f.poli);
        set('providerSelect', o.provider.map(d => ({ v: d.id_provider, t: d.provider_name })), 'Semua jenis bayar', f.provider);
      }

      /* ---------- tampilan ---------- */
      function tampil(j) {
        const r = j.ringkasan, f = j.filter;
        $id('apPeriodeLabel').innerHTML = '<i class="far fa-calendar me-1"></i>' + esc(tglIndo(f.from)) + (f.from !== f.to ? ' – ' + esc(tglIndo(f.to)) : '') +
          ' · ' + f.hari + ' hari · ' + esc(f.label);
        $id('kTotal').textContent = fmt(r.total);
        $id('kTotalSub').textContent = 'Rata-rata ' + String(r.rata_hari).replace('.', ',') + ' / hari';
        $id('kSelesai').textContent = fmt(r.status.selesai);
        $id('kSelesaiSub').textContent = pct(r.rate_selesai) + ' dari yang tidak batal';
        $id('kProses').textContent = fmt(r.status.proses);
        $id('kBooking').textContent = fmt(r.status.booking);
        $id('kBatal').textContent = fmt(r.status.batal);
        $id('kBatalSub').textContent = pct(r.rate_batal) + ' dari total';
        $id('kUnik').textContent = fmt(r.pasien_unik);
        $id('kUnikSub').textContent = r.aktif ? fmt(r.bpjs) + ' kunjungan BPJS' : ' ';
        document.querySelectorAll('.ap-kpi[data-status]').forEach(el => el.classList.toggle('on', status !== '' && el.dataset.status === status));

        grafik(r);
        isiTabel(j);
        ['btnExcel', 'btnPrint', 'btnPrintMenu'].forEach(id => $id(id).disabled = !r.total);
      }

      function chart(id, opt) {
        const el = $id(id);
        if (typeof ApexCharts === 'undefined') { el.innerHTML = '<div class="ap-empty small">Grafik tidak tersedia.</div>'; return; }
        if (charts[id]) { charts[id].updateOptions(opt, true, false); return; }
        charts[id] = new ApexCharts(el, Object.assign({ chart: {} }, opt, {
          chart: Object.assign({ fontFamily: 'inherit', toolbar: { show: false }, animations: { enabled: false }, parentHeightOffset: 0 }, opt.chart)
        }));
        charts[id].render();
      }
      const kosong = { noData: { text: 'Tidak ada data', style: { color: '#98a2b3' } } };

      function grafik(r) {
        const t = r.tren;
        $id('trenJudul').textContent = r.periode === 'hari' ? 'Tren harian' : 'Tren bulanan';
        const puncak = t.reduce((m, x) => { const s = x.selesai + x.proses + x.booking; return s > m.n ? { n: s, t: x.t } : m; }, { n: 0, t: null });
        $id('trenInfo').textContent = puncak.t ? 'Puncak ' + fmt(puncak.n) + ' pada ' + tglIndo(puncak.t) : '';
        chart('chTren', Object.assign({}, kosong, {
          chart: { type: 'bar', height: 260, stacked: true },
          series: ['selesai', 'proses', 'booking', 'batal'].map(k => ({ name: LABEL[k], data: t.map(x => x[k]) })),
          colors: ['selesai', 'proses', 'booking', 'batal'].map(k => WARNA[k]),
          xaxis: { categories: t.map(x => r.periode === 'hari' ? (+x.t.slice(8)) + ' ' + BULAN[+x.t.slice(5, 7) - 1] : tglIndo(x.t)), labels: { rotate: -45, hideOverlappingLabels: true, style: { fontSize: '10px' } }, tickAmount: Math.min(t.length, 16) },
          yaxis: { labels: { formatter: v => fmt(Math.round(v)) } },
          plotOptions: { bar: { columnWidth: t.length > 40 ? '85%' : '55%', borderRadius: 2 } },
          dataLabels: { enabled: false },
          legend: { position: 'top', horizontalAlign: 'left', fontSize: '11px' },
          grid: { borderColor: '#eef1f4', strokeDashArray: 3 },
          tooltip: { shared: true, intersect: false, y: { formatter: v => fmt(v) } }
        }));

        const donut = (id, data, h) => chart(id, Object.assign({}, kosong, {
          chart: { type: 'donut', height: h },
          series: data.map(d => d.jumlah),
          labels: data.map(d => d.nama),
          colors: PALET,
          legend: { position: 'bottom', fontSize: '11px' },
          dataLabels: { enabled: data.length > 0, formatter: v => Math.round(v) + '%' },
          plotOptions: { pie: { donut: { size: '62%', labels: { show: true, total: { show: true, label: 'Kunjungan', formatter: w => fmt(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } },
          tooltip: { y: { formatter: v => fmt(v) } }
        }));
        donut('chBayar', r.bayar, 260);
        donut('chPoli', r.poli, 240);

        chart('chDokter', Object.assign({}, kosong, {
          chart: { type: 'bar', height: Math.max(220, r.dokter.length * 30 + 40) },
          series: [{ name: 'Kunjungan', data: r.dokter.map(d => d.jumlah) }],
          xaxis: { categories: r.dokter.map(d => d.nama.length > 28 ? d.nama.slice(0, 27) + '…' : d.nama), labels: { formatter: v => fmt(v) } },
          plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3, distributed: true } },
          colors: PALET, legend: { show: false },
          dataLabels: { enabled: true, formatter: v => fmt(v), style: { fontSize: '10px' } },
          grid: { borderColor: '#eef1f4', strokeDashArray: 3 },
          tooltip: { y: { formatter: v => fmt(v) } }
        }));

        const jam = Array.from({ length: 24 }, (_, h) => (r.jam.find(x => x.jam === h) || { jumlah: 0 }).jumlah);
        let a = jam.findIndex(v => v > 0), z = 23 - [...jam].reverse().findIndex(v => v > 0);
        if (a < 0) { a = 7; z = 17; }
        chart('chJam', Object.assign({}, kosong, {
          chart: { type: 'bar', height: 240 },
          series: [{ name: 'Kunjungan', data: jam.slice(a, z + 1) }],
          xaxis: { categories: jam.slice(a, z + 1).map((_, i) => String(a + i).padStart(2, '0')), labels: { style: { fontSize: '10px' } } },
          colors: ['#5d87ff'],
          plotOptions: { bar: { columnWidth: '70%', borderRadius: 2 } },
          dataLabels: { enabled: false },
          grid: { borderColor: '#eef1f4', strokeDashArray: 3 },
          tooltip: { x: { formatter: v => 'Jam ' + v + ':00' }, y: { formatter: v => fmt(v) } }
        }));
        const g = r.gender;
        $id('genderInfo').innerHTML = r.aktif ? `<i class="fas fa-mars text-primary"></i> ${fmt(g.L)} · <i class="fas fa-venus text-danger"></i> ${fmt(g.P)}` : '';
      }

      /* ---------- tabel ---------- */
      const tabel = $('#apTable').DataTable({
        data: [],
        deferRender: true, // hanya baris yang tampil yang dibuat DOM-nya
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [[0, 'desc']],
        autoWidth: false,
        language: {
          search: 'Cari:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_', infoEmpty: '0 data', infoFiltered: '(disaring dari _MAX_)',
          zeroRecords: 'Tidak ada data yang cocok', emptyTable: 'Tidak ada appointment pada periode ini',
          paginate: { previous: '‹', next: '›' }
        },
        columns: [
          { data: 'urut', render: (d, t, r) => t === 'display' ? `<span class="fw-semibold">${esc(tglIndo(r.tanggal))}</span> <span class="small text-muted">${esc(r.jam || '')}</span>` : d },
          { data: 'grup', render: (d, t, r) => t === 'display' ? `<span class="ap-badge ${BADGE[d]}">${esc(r.status_label)}</span>` : r.status_label },
          { data: 'nama', render: (d, t) => t === 'display' ? `<span class="fw-semibold">${esc(d)}</span>` : d },
          { data: 'gender', className: 'text-center' },
          { data: 'noKartu', render: (d, t) => t === 'display' ? esc(d || '-') : d },
          { data: 'nik', render: (d, t) => t === 'display' ? esc(d || '-') : d },
          { data: 'dokter', render: (d, t) => t === 'display' ? esc(d) : d },
          { data: 'poli', render: (d, t) => t === 'display' ? esc(d) : d },
          { data: 'bayar', render: (d, t) => t === 'display' ? esc(d) : d }
        ]
      });
      function $(s) { return window.jQuery(s); }

      function isiTabel(j) {
        const k = j.kolom;
        const obj = j.data.map(a => { const o = {}; k.forEach((c, i) => o[c] = a[i]); o.urut = o.tanggal + ' ' + (o.jam || ''); return o; });
        tabel.clear().rows.add(obj).draw();
        $id('tabelInfo').textContent = fmt(obj.length) + ' baris' + (status ? ' · status ' + LABEL[status] : '');
      }

      /* ---------- export & cetak ---------- */
      function unduhExcel() {
        const btn = $id('btnExcel');
        const token = Date.now().toString(36);
        const label = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyiapkan…';
        const selesai = () => { clearInterval(cek); btn.innerHTML = label; btn.disabled = !(terakhir && terakhir.ringkasan.total); };
        // server memasang cookie ap_unduh=token saat file mulai dikirim
        const cek = setInterval(() => { if (document.cookie.includes('ap_unduh=' + token)) selesai(); }, 500);
        setTimeout(selesai, 120000);
        const ifr = document.createElement('iframe');
        ifr.hidden = true;
        // file terunduh tidak memicu 'load'; bila 'load' terpicu berarti server mengirim halaman error
        ifr.addEventListener('load', () => {
          let pesan = '';
          try { pesan = (ifr.contentDocument.body.innerText || '').trim().slice(0, 300); } catch (e) {}
          if (document.cookie.includes('ap_unduh=' + token)) return;
          selesai();
          Swal.fire('Export gagal', pesan || 'Server tidak mengirim file Excel.', 'error');
        });
        ifr.src = 'module/report/export_appointment.php?' + qs(filter(), { token });
        document.body.appendChild(ifr);
        setTimeout(() => ifr.remove(), 130000);
      }
      function cetak(isi) {
        window.open('module/report/print_appointment.php?' + qs(filter(), { isi }), '_blank');
      }
      $id('btnExcel').addEventListener('click', unduhExcel);
      $id('btnPrint').addEventListener('click', () => cetak('lengkap'));
      document.querySelectorAll('[data-cetak]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); cetak(a.dataset.cetak); }));

      /* ---------- event ---------- */
      document.querySelectorAll('[data-preset]').forEach(b => b.addEventListener('click', () => {
        const [a, z] = preset(b.dataset.preset);
        $id('fromDate').value = a;
        $id('toDate').value = z;
        muat();
      }));
      ['fromDate', 'toDate'].forEach(id => $id(id).addEventListener('change', muatNanti));
      ['doctorSelect', 'poliSelect', 'providerSelect', 'genderSelect'].forEach(id => $id(id).addEventListener('change', muat));
      document.querySelectorAll('.ap-kpi[data-status]').forEach(el => el.addEventListener('click', () => {
        status = status === el.dataset.status ? '' : el.dataset.status;
        muat();
      }));
      $id('btnReset').addEventListener('click', () => {
        const [a, z] = preset('hari');
        $id('fromDate').value = a; $id('toDate').value = z;
        ['doctorSelect', 'poliSelect', 'providerSelect', 'genderSelect'].forEach(id => $id(id).value = '');
        status = '';
        muat();
      });

      /* ---------- awal: dari URL atau hari ini ---------- */
      const u = new URLSearchParams(location.search);
      const [a, z] = preset('hari');
      $id('fromDate').value = u.get('fromDate') || a;
      $id('toDate').value = u.get('toDate') || z;
      $id('genderSelect').value = u.get('gender') || '';
      status = ['selesai', 'proses', 'booking', 'batal'].includes(u.get('status')) ? u.get('status') : '';
      // dokter/poli/provider dari URL dipasang setelah opsi dimuat
      ['doctor:doctorSelect', 'poli:poliSelect', 'provider:providerSelect'].forEach(p => {
        const [k, id] = p.split(':');
        if (u.get(k)) $id(id).innerHTML = `<option value="${esc(u.get(k))}" selected>${esc(u.get(k))}</option>`;
      });
      muat();
    })();
  </script>
</body>

</html>
