<?php
$title = 'Monitoring Satu Sehat';
require '../../controller/view.php';
$roles = $_SESSION['roles'] ?? '';
if (!in_array($roles, ['superadmin', 'admin'], true)) {
   http_response_code(403);
   exit('Akses tidak diizinkan.');
}
$isSuper = $roles === 'superadmin';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .ss-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .ss-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; }
    .ss-val { font-size: 1.35rem; font-weight: 700; color: #2a3547; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ss-label { font-size: .75rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ss-sub { font-size: .7rem; color: #98a2b3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ss-sec { font-size: .72rem; font-weight: 700; color: #5a6a85; text-transform: uppercase; letter-spacing: .4px; }
    .ss-chips .btn { border-radius: 20px; font-size: .75rem; padding: .2rem .75rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .ss-chips .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .ss-bar { position: fixed; top: 0; left: 0; height: 3px; background: #5d87ff; z-index: 9999; transition: width .3s, opacity .3s; }
    .ss-loading { position: relative; }
    .ss-loading::after { content: ''; position: absolute; inset: 0; background: rgba(255, 255, 255, .6); border-radius: inherit; z-index: 3; }
    .ss-cause { border: 1px solid #eef1f4; border-radius: 10px; padding: 10px 12px; cursor: pointer; transition: border-color .15s, background .15s; }
    .ss-cause:hover { border-color: #c9d2dc; background: #fafbfc; }
    .ss-cause.on { border-color: #5d87ff; background: #f3f6ff; }
    .ss-cause .p { height: 6px; border-radius: 3px; background: #eef1f4; overflow: hidden; }
    .ss-cause .p > div { height: 100%; }
    .ss-prog { height: 10px; border-radius: 5px; background: #eef1f4; overflow: hidden; display: flex; }
    .ss-prog > div { height: 100%; }
    .ss-badge { font-size: .7rem; font-weight: 600; padding: .25em .6em; border-radius: 20px; white-space: nowrap; }
    .ss-table td { font-size: .8rem; vertical-align: top; }
    .ss-table th { font-size: .75rem; color: #5a6a85; font-weight: 600; white-space: nowrap; }
    .ss-msg { max-width: 520px; white-space: normal; }
    .ss-raw { white-space: pre-wrap; word-break: break-all; font-size: .72rem; background: #f8fafc; border: 1px solid #eef1f4; border-radius: 8px; padding: 8px; max-height: 220px; overflow: auto; margin-top: 6px; }
    #ssFaskesTable tbody tr { cursor: pointer; }
    #ssFaskesTable td:first-child { min-width: 220px; }
    .skel { display: inline-block; height: 1.1rem; width: 60px; border-radius: 6px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: sk 1.2s infinite; }
    @keyframes sk { to { background-position: -200% 0; } }
  </style>
</head>

<body>
  <div class="ss-bar" id="ssBar" style="width:0"></div>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require $isSuper ? 'navbar-master.php' : 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="ss-icon bg-success-subtle text-success"><i class="fas fa-satellite-dish"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Monitoring Satu Sehat</h5>
                  <div class="text-muted small" id="ssInfo">Status pengiriman data ke platform Satu Sehat Kemenkes</div>
                </div>
                <div class="ss-chips d-flex flex-wrap gap-1" id="ssHari">
                  <button type="button" class="btn" data-hari="1">Hari ini</button>
                  <button type="button" class="btn active" data-hari="7">7 hari</button>
                  <button type="button" class="btn" data-hari="30">30 hari</button>
                  <button type="button" class="btn" data-hari="90">90 hari</button>
                </div>
                <?php if ($isSuper): ?>
                  <select id="ssFaskes" class="form-select form-select-sm" style="width:auto;min-width:220px"><option value="0">Semua faskes</option></select>
                <?php endif; ?>
                <button type="button" class="btn btn-light btn-sm" id="ssRefresh" title="Muat ulang (hitung ulang)"><i class="fas fa-sync-alt"></i></button>
              </div>
            </div>
          </div>
          <div id="ssError" class="alert alert-danger d-none" role="alert"></div>
          <div id="ssStale" class="alert alert-warning d-none small" role="alert"></div>

          <!-- KPI -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-xl-2"><div class="ss-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="ss-icon bg-primary-subtle text-primary"><i class="fas fa-percentage"></i></span>
              <div class="min-w-0"><div class="ss-val" id="kRate"><span class="skel"></span></div><div class="ss-label">Keberhasilan</div></div></div><div class="ss-sub" id="kRateSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="ss-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="ss-icon bg-success-subtle text-success"><i class="fas fa-check-circle"></i></span>
              <div class="min-w-0"><div class="ss-val" id="kOk"><span class="skel"></span></div><div class="ss-label">Berhasil</div></div></div><div class="ss-sub" id="kOkSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="ss-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="ss-icon bg-danger-subtle text-danger"><i class="fas fa-times-circle"></i></span>
              <div class="min-w-0"><div class="ss-val" id="kErr"><span class="skel"></span></div><div class="ss-label">Gagal</div></div></div><div class="ss-sub" id="kErrSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="ss-kpi" title="Kunjungan pada periode yang belum punya ID Encounter"><div class="d-flex align-items-center gap-2 mb-1"><span class="ss-icon bg-warning-subtle text-warning"><i class="fas fa-hourglass-half"></i></span>
              <div class="min-w-0"><div class="ss-val" id="kPend"><span class="skel"></span></div><div class="ss-label">Belum terkirim</div></div></div><div class="ss-sub" id="kPendSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="ss-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="ss-icon" style="background:#fce7f3;color:#be185d"><i class="fas fa-user-md"></i></span>
              <div class="min-w-0"><div class="ss-val" id="kDok"><span class="skel"></span></div><div class="ss-label">Dokter tanpa IHS</div></div></div><div class="ss-sub" id="kDokSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="ss-kpi"><div class="d-flex align-items-center gap-2 mb-1"><span class="ss-icon bg-info-subtle text-info"><i class="fas fa-key"></i></span>
              <div class="min-w-0"><div class="ss-val" id="kCfg" style="font-size:1rem"><span class="skel"></span></div><div class="ss-label">Konfigurasi</div></div></div><div class="ss-sub" id="kCfgSub">&nbsp;</div></div></div>
          </div>

          <!-- GRAFIK & PENYEBAB -->
          <div class="row g-3 mb-3">
            <div class="col-xl-8"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1"><div class="ss-sec">Pengiriman per hari</div><div class="small text-muted" id="trenInfo"></div></div>
              <div id="chTren" style="min-height:290px"></div></div></div></div>
            <div class="col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2"><div class="ss-sec">Penyebab gagal</div><div class="small text-muted" id="katInfo"></div></div>
              <div id="ssCauses" class="d-flex flex-column gap-2"></div>
            </div></div></div>
            <div class="col-xl-5"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="ss-sec mb-1">Per resource</div><div id="chRes" style="min-height:250px"></div></div></div></div>
            <div class="col-xl-7"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2"><div class="ss-sec">Status data</div>
                <div class="small d-flex gap-3"><span><i class="fas fa-square text-success me-1"></i>Terkirim</span><span><i class="fas fa-square text-danger me-1"></i>Gagal</span><span><i class="fas fa-square me-1" style="color:#cbd5e1"></i>Belum</span></div></div>
              <div id="ssAntrean"></div>
              <hr class="my-3">
              <div class="d-flex justify-content-between align-items-center mb-2"><div class="ss-sec">Dokter belum punya ID IHS</div>
                <?php if (!$isSuper): ?><a href="module/admin/setting_satusehat" class="small">Lengkapi di Pengaturan Satu Sehat <i class="fas fa-arrow-right"></i></a><?php endif; ?></div>
              <div id="ssDokter" class="small"></div>
            </div></div></div>
          </div>

          <?php if ($isSuper): ?>
            <div class="card border-0 shadow-sm mb-3"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-2"><div class="ss-sec">Per faskes</div><div class="small text-muted">Klik baris untuk memfilter</div></div>
              <div class="table-responsive">
                <table class="table table-hover align-middle w-100 ss-table" id="ssFaskesTable">
                  <thead><tr><th>Faskes</th><th class="text-end">Berhasil</th><th class="text-end">Gagal</th><th style="min-width:160px">Keberhasilan</th><th>Aktivitas terakhir</th><th>Konfigurasi</th></tr></thead>
                  <tbody></tbody>
                </table>
              </div>
            </div></div>
          <?php endif; ?>

          <!-- ERROR TERBARU -->
          <div class="card border-0 shadow-sm mb-3"><div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
              <div><div class="ss-sec">Error terbaru</div><div class="small text-muted">200 error terakhir pada periode · klik baris untuk melihat respons lengkap</div></div>
              <div class="d-flex gap-2">
                <select id="fRes" class="form-select form-select-sm" style="width:auto"><option value="">Semua resource</option></select>
                <select id="fKat" class="form-select form-select-sm" style="width:auto"><option value="">Semua penyebab</option></select>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle w-100 ss-table" id="ssErrTable">
                <thead><tr><th>Waktu</th><th>Faskes</th><th>Resource</th><th>Penyebab</th><th>Pesan</th><th>Ref</th></tr></thead>
                <tbody></tbody>
              </table>
            </div>
          </div></div>

        </div>
      </div>
    </div>
  </div>
  <?php require 'library.php'; ?>
  <script>
    (function() {
      const API = 'controller/dashboard/satuSehatMonitoringController.php';
      const isSuper = <?= $isSuper ? 'true' : 'false' ?>;
      const KAT_WARNA = { rate_limit: '#f59e0b', duplikat: '#94a3b8', dokter_ihs: '#ec4899', pasien_ihs: '#a855f7', kode: '#3b82f6', auth: '#ef4444', koneksi: '#0ea5e9', lainnya: '#64748b' };
      const KAT_LABEL = { rate_limit: 'Kuota API terlampaui', duplikat: 'Data sudah ada (duplikat)', dokter_ihs: 'Dokter belum punya ID IHS', pasien_ihs: 'Pasien belum punya ID IHS', kode: 'Kode tidak dikenal', auth: 'Autentikasi', koneksi: 'Koneksi', lainnya: 'Lainnya' };
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const fmt = n => Number(n || 0).toLocaleString('id-ID');
      const pc = n => n === null || n === undefined ? '-' : String(n).replace('.', ',') + '%';
      const waktu = s => { if (!s) return '-'; const d = new Date(String(s).replace(' ', 'T')); return isNaN(d) ? s : d.getDate() + ' ' + BULAN[d.getMonth()] + ' ' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0'); };
      const lalu = s => { if (!s) return '-'; const m = Math.round((Date.now() - new Date(String(s).replace(' ', 'T'))) / 60000); return m < 1 ? 'baru saja' : m < 60 ? m + ' menit lalu' : m < 1440 ? Math.round(m / 60) + ' jam lalu' : Math.round(m / 1440) + ' hari lalu'; };
      const faskesSel = $id('ssFaskes');
      let hari = 7, filterKat = '', D = null, seq = 0, faskesLoaded = false;
      const charts = {};

      const params = (extra) => new URLSearchParams(Object.assign({ hari, id_customer: faskesSel ? faskesSel.value : 0 }, extra || {}));
      async function get(action, extra) {
        const r = await fetch(API + '?' + params(Object.assign({ action }, extra)), { credentials: 'same-origin', cache: 'no-store' });
        const j = await r.json().catch(() => null);
        if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat data (HTTP ' + r.status + ').');
        return j;
      }
      function sibuk(on) {
        const b = $id('ssBar');
        if (on) { b.style.opacity = 1; b.style.width = '70%'; } else { b.style.width = '100%'; setTimeout(() => { b.style.opacity = 0; b.style.width = 0; }, 300); }
        document.querySelectorAll('.ss-kpi, #chTren, #chRes, #ssCauses, #ssAntrean').forEach(el => el.classList.toggle('ss-loading', on));
      }

      async function load(refresh) {
        const ini = ++seq;
        $id('ssError').classList.add('d-none');
        sibuk(true);
        const extra = refresh ? { refresh: 1 } : {};
        // dashboard, error & per faskes berjalan paralel (sesi dilepas di server)
        const tugas = [get('dashboard', extra).then(d => { if (ini === seq) { D = d; tampil(d); } }), muatError()];
        if (isSuper) tugas.push(get('by_faskes', extra).then(f => { if (ini === seq) tampilFaskes(f.data); }));
        const hasil = await Promise.allSettled(tugas);
        if (ini !== seq) return;
        const gagal = hasil.find(h => h.status === 'rejected');
        if (gagal) { $id('ssError').textContent = gagal.reason.message || 'Gagal terhubung ke server.'; $id('ssError').classList.remove('d-none'); }
        sibuk(false);
      }

      function tampil(d) {
        const t = d.total;
        // sinkronisasi dianggap berhenti bila tidak ada pengiriman > 2 jam (hanya relevan untuk periode yang mencakup hari ini)
        const menit = t.aktivitas_terakhir ? (Date.now() - new Date(String(t.aktivitas_terakhir).replace(' ', 'T'))) / 60000 : null;
        const stale = $id('ssStale');
        stale.classList.toggle('d-none', !(menit === null || menit > 120));
        stale.innerHTML = menit === null ? '<i class="fas fa-exclamation-triangle me-1"></i><b>Tidak ada pengiriman ke Satu Sehat</b> pada periode ini. Periksa apakah job sinkronisasi (cron) berjalan.' :
          `<i class="fas fa-exclamation-triangle me-1"></i><b>Tidak ada pengiriman sejak ${esc(waktu(t.aktivitas_terakhir))} (${esc(lalu(t.aktivitas_terakhir))}).</b> Kemungkinan job sinkronisasi (cron) berhenti atau gagal dijalankan.`;
        $id('ssInfo').innerHTML = `<i class="far fa-clock me-1"></i>Aktivitas terakhir <b>${esc(lalu(t.aktivitas_terakhir))}</b> · data ${d.cached ? 'cache ' : ''}diperbarui ${esc(waktu(d.diperbarui))}`;
        $id('kRate').textContent = pc(t.rate);
        $id('kRate').className = 'ss-val ' + (t.rate === null ? '' : t.rate >= 90 ? 'text-success' : t.rate >= 70 ? 'text-warning' : 'text-danger');
        $id('kRateSub').textContent = fmt(t.ok + t.error) + ' percobaan kirim';
        $id('kOk').textContent = fmt(t.ok);
        $id('kOkSub').textContent = d.hari === 1 ? 'Hari ini' : d.hari + ' hari terakhir';
        $id('kErr').textContent = fmt(t.error);
        $id('kErrSub').textContent = d.kategori[0] ? 'Terbanyak: ' + d.kategori[0].label : 'Tidak ada error';
        const enc = d.antrean.find(a => a.resource === 'Encounter') || { total: 0, ok: 0, error: 0, pending: 0 };
        $id('kPend').textContent = fmt(enc.pending + enc.error);
        $id('kPendSub').textContent = enc.total ? 'dari ' + fmt(enc.total) + ' kunjungan (' + pc(Math.round((enc.pending + enc.error) / enc.total * 1000) / 10) + ')' : 'Tidak ada kunjungan';
        $id('kDok').textContent = fmt(d.dokter_tanpa_ihs.length);
        const kunjDok = d.dokter_tanpa_ihs.reduce((a, x) => a + x.kunjungan, 0);
        $id('kDokSub').textContent = d.dokter_tanpa_ihs.length ? fmt(kunjDok) + ' kunjungan terdampak' : 'Semua dokter lengkap';
        const c = d.config;
        $id('kCfg').textContent = !c.jumlah ? 'Belum diatur' : (c.jumlah === 1 ? (c.lengkap ? 'Lengkap' : 'Belum lengkap') : c.lengkap + '/' + c.jumlah + ' lengkap');
        $id('kCfg').className = 'ss-val ' + (c.jumlah && c.lengkap === c.jumlah ? 'text-success' : 'text-danger');
        $id('kCfgSub').textContent = c.jumlah === 1 ? (c.kredensial ? 'Kredensial ✓' : 'Kredensial ✗') + ' · ' + (c.lokasi ? 'Lokasi ✓' : 'Lokasi ✗') : 'Kredensial & lokasi';

        grafik(d);
        penyebab(d);
        antrean(d);
        dokter(d);
        // pilihan resource untuk filter error
        const lama = $id('fRes').value;
        $id('fRes').innerHTML = '<option value="">Semua resource</option>' + d.resource.map(r => `<option>${esc(r.resource)}</option>`).join('');
        $id('fRes').value = d.resource.some(r => r.resource === lama) ? lama : '';
        $id('fKat').innerHTML = '<option value="">Semua penyebab</option>' + Object.entries(KAT_LABEL).map(([k, v]) => `<option value="${k}">${esc(v)}</option>`).join('');
        $id('fKat').value = filterKat;
      }

      function chart(id, opt) {
        const el = $id(id);
        if (typeof ApexCharts === 'undefined') { el.innerHTML = '<div class="text-center text-muted small py-5">Grafik tidak tersedia.</div>'; return; }
        if (charts[id]) { charts[id].updateOptions(opt, true, false); return; }
        charts[id] = new ApexCharts(el, Object.assign({}, opt, { chart: Object.assign({ fontFamily: 'inherit', toolbar: { show: false }, animations: { enabled: false }, parentHeightOffset: 0 }, opt.chart) }));
        charts[id].render();
      }
      const grid = { borderColor: '#eef1f4', strokeDashArray: 3 };
      function grafik(d) {
        const t = d.tren;
        const rate = t.map(x => x.ok + x.error ? Math.round(x.ok / (x.ok + x.error) * 1000) / 10 : null);
        const minRate = rate.filter(x => x !== null).reduce((m, x) => Math.min(m, x), 100);
        $id('trenInfo').textContent = t.length > 1 ? 'Keberhasilan terendah ' + pc(minRate) : '';
        chart('chTren', {
          chart: { type: 'line', height: 290, stacked: true },
          series: [{ name: 'Berhasil', type: 'column', data: t.map(x => x.ok) }, { name: 'Gagal', type: 'column', data: t.map(x => x.error) }, { name: 'Keberhasilan %', type: 'line', data: rate }],
          colors: ['#13deb9', '#fa896b', '#5d87ff'], stroke: { width: [0, 0, 2], curve: 'smooth' },
          xaxis: { categories: t.map(x => (+x.tgl.slice(8)) + ' ' + BULAN[+x.tgl.slice(5, 7) - 1]), labels: { rotate: -45, hideOverlappingLabels: true, style: { fontSize: '10px' } }, tickAmount: Math.min(t.length, 16) },
          yaxis: [{ seriesName: 'Berhasil', labels: { formatter: v => fmt(Math.round(v)) } }, { seriesName: 'Berhasil', show: false }, { opposite: true, min: 0, max: 100, seriesName: 'Keberhasilan %', labels: { formatter: v => Math.round(v) + '%' } }],
          plotOptions: { bar: { columnWidth: t.length > 40 ? '85%' : '55%', borderRadius: 2 } },
          dataLabels: { enabled: false }, legend: { position: 'top', horizontalAlign: 'left', fontSize: '11px' }, grid,
          tooltip: { shared: true, intersect: false, y: { formatter: (v, o) => o.seriesIndex === 2 ? pc(v) : fmt(v) } }
        });
        const r = d.resource;
        chart('chRes', {
          chart: { type: 'bar', height: Math.max(250, r.length * 42 + 40), stacked: true },
          series: [{ name: 'Berhasil', data: r.map(x => x.ok) }, { name: 'Gagal', data: r.map(x => x.error) }],
          colors: ['#13deb9', '#fa896b'],
          xaxis: { categories: r.map(x => x.resource), tickAmount: 4, labels: { formatter: v => fmt(Math.round(v)) } },
          plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 2 } },
          dataLabels: { enabled: false }, legend: { position: 'top', horizontalAlign: 'left', fontSize: '11px' }, grid,
          tooltip: { shared: true, intersect: false, y: { formatter: v => fmt(v) }, x: { formatter: (v, o) => { const x = r[o.dataPointIndex]; return v + ' · ' + pc(x.ok + x.error ? Math.round(x.ok / (x.ok + x.error) * 1000) / 10 : null) + ' berhasil · sukses terakhir ' + waktu(x.last_success); } } },
          noData: { text: 'Belum ada pengiriman', style: { color: '#98a2b3' } }
        });
      }
      function penyebab(d) {
        const tot = d.kategori.reduce((a, k) => a + k.jumlah, 0);
        $id('katInfo').textContent = !tot ? '' : d.kategori_semua ? fmt(tot) + ' error' : 'dari ' + fmt(d.kategori_sampel) + ' error terbaru';
        $id('ssCauses').innerHTML = !d.kategori.length ? '<div class="text-center text-muted small py-4"><i class="fas fa-check-circle text-success fa-2x d-block mb-2"></i>Tidak ada pengiriman gagal pada periode ini.</div>' :
          d.kategori.map(k => `<div class="ss-cause ${filterKat === k.kode ? 'on' : ''}" data-kat="${esc(k.kode)}" title="Klik untuk menyaring daftar error">
            <div class="d-flex justify-content-between gap-2"><span class="fw-semibold small">${esc(k.label)}</span><span class="small fw-semibold">${pc(Math.round(k.jumlah / tot * 1000) / 10)}</span></div>
            <div class="p my-1"><div style="width:${k.jumlah / tot * 100}%;background:${KAT_WARNA[k.kode] || '#64748b'}"></div></div>
            <div class="text-muted" style="font-size:.72rem">${esc(k.saran)}</div></div>`).join('');
        document.querySelectorAll('.ss-cause').forEach(el => el.addEventListener('click', () => {
          filterKat = filterKat === el.dataset.kat ? '' : el.dataset.kat;
          $id('fKat').value = filterKat;
          document.querySelectorAll('.ss-cause').forEach(x => x.classList.toggle('on', x.dataset.kat === filterKat));
          muatError();
          $id('ssErrTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }));
      }
      function antrean(d) {
        $id('ssAntrean').innerHTML = d.antrean.map(a => {
          const p = v => a.total ? v / a.total * 100 : 0;
          return `<div class="mb-2"><div class="d-flex justify-content-between small mb-1"><span><b>${esc(a.resource)}</b> <span class="text-muted">· ${esc(a.label)}${a.resource !== 'Patient' ? ' periode ini' : ''}</span></span>
            <span class="text-muted">${fmt(a.ok)} / ${fmt(a.total)} terkirim${a.error ? ` · <span class="text-danger">${fmt(a.error)} gagal</span>` : ''}${a.pending ? ` · ${fmt(a.pending)} belum` : ''}</span></div>
            <div class="ss-prog"><div class="bg-success" style="width:${p(a.ok)}%"></div><div class="bg-danger" style="width:${p(a.error)}%"></div></div></div>`;
        }).join('');
      }
      function dokter(d) {
        const x = d.dokter_tanpa_ihs;
        $id('ssDokter').innerHTML = !x.length ? '<span class="text-success"><i class="fas fa-check-circle me-1"></i>Semua dokter sudah punya ID IHS.</span>' :
          '<div class="table-responsive" style="max-height:180px"><table class="table table-sm mb-0"><tbody>' + x.slice(0, 15).map(r => `<tr><td class="fw-semibold">${esc(r.nama)}${isSuper ? `<div class="text-muted" style="font-size:.7rem">${esc(r.faskes || '-')}</div>` : ''}</td>
            <td>${r.nik ? '<span class="ss-badge bg-warning-subtle text-warning">NIK ada, IHS belum dicari</span>' : '<span class="ss-badge bg-danger-subtle text-danger">NIK belum diisi</span>'}</td>
            <td class="text-end text-nowrap">${fmt(r.kunjungan)} kunjungan</td></tr>`).join('') + '</tbody></table></div>' + (x.length > 15 ? `<div class="text-muted mt-1">+${fmt(x.length - 15)} dokter lainnya</div>` : '');
      }

      /* ---------- per faskes ---------- */
      const jq = window.jQuery;
      const bahasa = { search: 'Cari:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_', infoEmpty: '0 data', infoFiltered: '(dari _MAX_)', emptyTable: 'Tidak ada data.', zeroRecords: 'Tidak ditemukan.', paginate: { next: '›', previous: '‹' } };
      let faskesTable = null;
      if (isSuper) {
        faskesTable = jq('#ssFaskesTable').DataTable({
          data: [], order: [[2, 'desc']], pageLength: 10, language: bahasa, columns: [
            { data: 'clinic_name', render: (v, t) => t === 'display' ? `<span class="fw-semibold">${esc(v || '-')}</span>` : v },
            { data: 'ok', className: 'text-end', render: (v, t) => t === 'display' ? fmt(v) : v },
            { data: 'error', className: 'text-end', render: (v, t) => t === 'display' ? (v ? `<span class="text-danger fw-semibold">${fmt(v)}</span>` : '0') : v },
            { data: 'rate', render: (v, t) => t !== 'display' ? (v ?? -1) : v === null ? '<span class="text-muted">Tidak ada pengiriman</span>' :
              `<div class="d-flex align-items-center gap-2"><div class="ss-prog flex-grow-1" style="height:6px"><div style="width:${v}%;background:${v >= 90 ? '#13deb9' : v >= 70 ? '#ffae1f' : '#fa896b'}"></div></div><span class="small" style="width:46px">${pc(v)}</span></div>` },
            { data: 'last_activity', render: (v, t) => t === 'display' ? (v ? `${esc(waktu(v))}<div class="text-muted" style="font-size:.7rem">${esc(lalu(v))}</div>` : '<span class="text-muted">-</span>') : (v || '') },
            { data: 'config', render: v => v === 'lengkap' ? '<span class="ss-badge bg-success-subtle text-success">Lengkap</span>' : v === 'kurang' ? '<span class="ss-badge bg-warning-subtle text-warning">Belum lengkap</span>' : '<span class="ss-badge bg-secondary-subtle text-secondary">Belum diatur</span>' }
          ]
        });
        jq('#ssFaskesTable tbody').on('click', 'tr', function() {
          const d = faskesTable.row(this).data();
          if (d) { faskesSel.value = d.id_customer; load(); window.scrollTo({ top: 0, behavior: 'smooth' }); }
        });
        faskesSel.addEventListener('change', () => load());
      }
      function tampilFaskes(rows) {
        faskesTable.clear().rows.add(rows).draw();
        if (!faskesLoaded) {
          rows.slice().sort((a, b) => String(a.clinic_name).localeCompare(String(b.clinic_name))).forEach(r => faskesSel.add(new Option(r.clinic_name || ('#' + r.id_customer), r.id_customer)));
          faskesLoaded = true;
        }
      }

      /* ---------- error terbaru ---------- */
      const errTable = jq('#ssErrTable').DataTable({
        data: [], order: [[0, 'desc']], pageLength: 25, language: bahasa, autoWidth: false,
        columns: [
          { data: 'created_at', className: 'text-nowrap', render: (v, t) => t === 'display' ? esc(waktu(v)) : v },
          { data: 'clinic_name', render: (v, t) => t === 'display' ? `<span class="small">${esc(v || '-')}</span>` : v },
          { data: 'resource_type', render: (v, t) => t === 'display' ? `<span class="ss-badge bg-light text-dark">${esc(v)}</span>` : v },
          { data: 'kategori', render: (v, t, r) => t === 'display' ? `<span class="ss-badge" style="background:${(KAT_WARNA[v] || '#64748b')}22;color:${KAT_WARNA[v] || '#64748b'}">${esc(r.kategori_label)}</span>` : r.kategori_label },
          { data: 'ringkas', className: 'ss-msg', render: (v, t) => t === 'display' ? esc(v) : v },
          { data: 'reference_id', render: (v, t) => t === 'display' ? `<code class="small">${esc(v || '-')}</code>` : v }
        ]
      });
      if (!isSuper) errTable.column(1).visible(false);
      jq('#ssErrTable tbody').on('click', 'tr', function() {
        const row = errTable.row(this);
        if (!row.data()) return;
        if (row.child.isShown()) { row.child.hide(); return; }
        let raw = row.data().message || '';
        try { raw = JSON.stringify(JSON.parse(raw), null, 2); } catch (e) {}
        row.child(`<div class="ss-raw">${esc(raw)}</div>`).show();
      });
      let errSeq = 0;
      async function muatError() {
        const ini = ++errSeq;
        const j = await get('errors', { resource: $id('fRes').value, kategori: filterKat });
        if (ini === errSeq) errTable.clear().rows.add(j.data).draw();
      }
      $id('fRes').addEventListener('change', () => muatError().catch(() => {}));
      $id('fKat').addEventListener('change', () => {
        filterKat = $id('fKat').value;
        document.querySelectorAll('.ss-cause').forEach(x => x.classList.toggle('on', x.dataset.kat === filterKat));
        muatError().catch(() => {});
      });

      document.querySelectorAll('#ssHari .btn').forEach(b => b.addEventListener('click', () => {
        hari = +b.dataset.hari;
        document.querySelectorAll('#ssHari .btn').forEach(x => x.classList.toggle('active', x === b));
        load();
      }));
      $id('ssRefresh').addEventListener('click', () => load(true));
      load();
    })();
  </script>
</body>

</html>
