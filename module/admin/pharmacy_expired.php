<?php
$title = 'Expired Obat';
require '../../controller/view.php';
?>
<!doctype html>
<html lang="id">

<head>
  <base href="../../">
  <?php require '../../assets/template/head.php'; ?>
  <style>
    .min-w-0 { min-width: 0; }
    .ex-icon { width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .ex-kpi { border: 1px solid #e5eaef; border-radius: 12px; background: #fff; padding: 14px 16px; height: 100%; transition: border-color .15s, box-shadow .15s; }
    .ex-kpi[data-status] { cursor: pointer; }
    .ex-kpi[data-status]:hover { border-color: #c9d2dc; box-shadow: 0 4px 14px rgba(0, 0, 0, .05); }
    .ex-kpi.on { border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 18%, transparent); }
    .ex-val { font-size: 1.3rem; font-weight: 700; color: #2a3547; line-height: 1.2; white-space: nowrap; }
    .ex-label { font-size: .75rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ex-sub { font-size: .7rem; color: #98a2b3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ex-sec { font-size: .72rem; font-weight: 700; color: #5a6a85; text-transform: uppercase; letter-spacing: .4px; }
    .ex-warn { background: #fff8ec; border: 1px solid #ffe2b3; border-radius: 10px; padding: 8px 12px; font-size: .8rem; color: #8a5a00; }
    .ex-info { background: #f3f6ff; border: 1px solid #dbe4ff; border-radius: 10px; padding: 8px 12px; font-size: .78rem; color: #3b4f7a; }
    .ex-tabs .nav-link { font-size: .85rem; color: #5a6a85; border: 0; border-bottom: 2px solid transparent; border-radius: 0; padding: .5rem .9rem; }
    .ex-tabs .nav-link.active { color: #5d87ff; border-bottom-color: #5d87ff; background: none; font-weight: 600; }
    .ex-chips .btn { border-radius: 20px; font-size: .74rem; padding: .18rem .65rem; background: #fff; border: 1px solid #e5eaef; color: #5a6a85; }
    .ex-chips .btn.active { background: #5d87ff; border-color: #5d87ff; color: #fff; }
    .ex-table td { font-size: .82rem; vertical-align: middle; white-space: nowrap; }
    .ex-table th { font-size: .75rem; color: #5a6a85; font-weight: 600; white-space: nowrap; }
    .ex-table tr.habis td { color: #adb5bd; }
    .ex-badge { font-size: .7rem; font-weight: 600; padding: .25em .6em; border-radius: 20px; white-space: nowrap; }
    .b-kadaluarsa { background: #fee2e2; color: #b91c1c; } .b-kritis { background: #ffedd5; color: #c2410c; } .b-waspada { background: #fef3c7; color: #b45309; }
    .b-perhatian { background: #dbeafe; color: #1d4ed8; } .b-aman { background: #dcfce7; color: #15803d; } .b-tanpa_ed { background: #f1f5f9; color: #64748b; } .b-habis { background: #f8fafc; color: #94a3b8; }
    .ex-bar { position: fixed; top: 0; left: 0; height: 3px; background: #5d87ff; z-index: 9999; transition: width .3s, opacity .3s; }
    .ex-loading { position: relative; }
    .ex-loading::after { content: ''; position: absolute; inset: 0; background: rgba(255, 255, 255, .6); border-radius: inherit; z-index: 3; }
    .skel { display: inline-block; height: 1.1rem; width: 60px; border-radius: 6px; background: linear-gradient(90deg, #eef1f4 25%, #f6f8fb 50%, #eef1f4 75%); background-size: 200% 100%; animation: sk 1.2s infinite; }
    @keyframes sk { to { background-position: -200% 0; } }
  </style>
</head>

<body>
  <div class="ex-bar" id="exBar" style="width:0"></div>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <?php require 'sidebar.php'; ?>
    <div class="body-wrapper">
      <?php require 'navbar.php'; ?>
      <div class="body-wrapper-inner">
        <div class="container-fluid">

          <!-- HEADER -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="ex-icon bg-danger-subtle text-danger"><i class="fas fa-hourglass-end"></i></span>
                <div class="min-w-0 flex-grow-1">
                  <h5 class="fw-semibold mb-0">Obat Kedaluwarsa</h5>
                  <div class="text-muted small" id="exPosisi">Pantau ED batch yang masih ada stoknya</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" class="btn btn-light btn-sm" id="btnRefresh"><i class="fas fa-sync-alt me-1"></i>Muat ulang</button>
                  <button type="button" class="btn btn-success btn-sm" id="btnExcel" disabled><i class="fas fa-file-excel me-1"></i>Export Excel</button>
                  <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm" id="btnPrint" disabled><i class="fas fa-print me-1"></i>Cetak</button>
                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" id="btnPrintMenu" disabled><span class="visually-hidden">Pilihan cetak</span></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="#" data-cetak="risiko"><i class="fas fa-exclamation-triangle me-2 text-muted"></i>Batch berisiko (ED ≤ 180 hari)</a></li>
                      <li><a class="dropdown-item" href="#" data-cetak="semua"><i class="fas fa-list me-2 text-muted"></i>Semua batch berstok</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- KPI -->
          <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-xl-2"><div class="ex-kpi" data-status="kadaluarsa" style="--c:#ef4444"><div class="d-flex align-items-center gap-2 mb-1"><span class="ex-icon bg-danger-subtle text-danger"><i class="fas fa-ban"></i></span>
              <div class="min-w-0"><div class="ex-val" id="kKadaluarsa"><span class="skel"></span></div><div class="ex-label">Sudah kedaluwarsa</div></div></div><div class="ex-sub" id="kKadaluarsaSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="ex-kpi" data-status="kritis" style="--c:#f97316"><div class="d-flex align-items-center gap-2 mb-1"><span class="ex-icon" style="background:#ffedd5;color:#c2410c"><i class="fas fa-fire"></i></span>
              <div class="min-w-0"><div class="ex-val" id="kKritis"><span class="skel"></span></div><div class="ex-label">ED ≤ 30 hari</div></div></div><div class="ex-sub" id="kKritisSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="ex-kpi" data-status="waspada" style="--c:#f59e0b"><div class="d-flex align-items-center gap-2 mb-1"><span class="ex-icon bg-warning-subtle text-warning"><i class="fas fa-exclamation-triangle"></i></span>
              <div class="min-w-0"><div class="ex-val" id="kWaspada"><span class="skel"></span></div><div class="ex-label">ED 31–90 hari</div></div></div><div class="ex-sub" id="kWaspadaSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="ex-kpi" data-status="perhatian" style="--c:#3b82f6"><div class="d-flex align-items-center gap-2 mb-1"><span class="ex-icon bg-primary-subtle text-primary"><i class="fas fa-eye"></i></span>
              <div class="min-w-0"><div class="ex-val" id="kPerhatian"><span class="skel"></span></div><div class="ex-label">ED 91–180 hari</div></div></div><div class="ex-sub" id="kPerhatianSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="ex-kpi" title="Unit yang diperkirakan tidak sempat terpakai sebelum ED, berdasarkan rata-rata pemakaian"><div class="d-flex align-items-center gap-2 mb-1"><span class="ex-icon" style="background:#fce7f3;color:#be185d"><i class="fas fa-trash-alt"></i></span>
              <div class="min-w-0"><div class="ex-val" id="kBuang"><span class="skel"></span></div><div class="ex-label">Perkiraan terbuang</div></div></div><div class="ex-sub" id="kBuangSub">&nbsp;</div></div></div>
            <div class="col-6 col-md-4 col-xl-2"><div class="ex-kpi" data-status="aman" style="--c:#22c55e"><div class="d-flex align-items-center gap-2 mb-1"><span class="ex-icon bg-success-subtle text-success"><i class="fas fa-shield-alt"></i></span>
              <div class="min-w-0"><div class="ex-val" id="kAman"><span class="skel"></span></div><div class="ex-label">Aman (> 180 hari)</div></div></div><div class="ex-sub" id="kAmanSub">&nbsp;</div></div></div>
          </div>
          <div class="ex-warn mb-3" id="exWarn" hidden></div>

          <!-- GRAFIK -->
          <div class="row g-3 mb-3">
            <div class="col-xl-7"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1"><div class="ex-sec">Jadwal ED 12 bulan ke depan</div><div class="small text-muted">nilai stok tersisa (harga beli)</div></div>
              <div id="chTimeline" style="min-height:290px"></div></div></div></div>
            <div class="col-xl-5"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-1"><div class="ex-sec">Perkiraan terbuang terbesar</div><div class="small text-muted" id="buangInfo"></div></div>
              <div id="chBuang" style="min-height:290px"></div></div></div></div>
            <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="ex-sec mb-1">Status obat berstok</div><div id="chStatus" style="min-height:250px"></div></div></div></div>
            <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="ex-sec mb-1">Supplier dengan stok ED ≤ 90 hari</div><div id="chSupplier" style="min-height:250px"></div></div></div></div>
            <div class="col-xl-4"><div class="card border-0 shadow-sm h-100 mb-0"><div class="card-body">
              <div class="ex-sec mb-2">Cara membaca</div>
              <div class="ex-info mb-2"><b>Perkiraan sisa per batch</b> dihitung FIFO: stok saat ini dianggap berasal dari penerimaan terbaru. Batch lama yang sudah terpakai ditandai <span class="ex-badge b-habis">Habis terpakai</span> dan tidak dihitung berisiko.</div>
              <div class="ex-info mb-2"><b>Perkiraan terbuang</b> = sisa batch yang tidak sempat terpakai sebelum ED, memakai rata-rata pemakaian <span id="hariPakai">90</span> hari terakhir.</div>
              <div class="small text-muted" id="exStatBatch"></div>
            </div></div></div>
          </div>

          <!-- TABEL -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
              <ul class="nav ex-tabs border-bottom mb-3" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabBatch" type="button">Per batch <span class="badge bg-light text-muted ms-1" id="nBatch">0</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabObat" type="button">Per obat <span class="badge bg-light text-muted ms-1" id="nObat">0</span></button></li>
              </ul>
              <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                <div class="ex-chips d-flex flex-wrap gap-1" id="exChips">
                  <button type="button" class="btn active" data-f="">Semua</button>
                  <button type="button" class="btn" data-f="risiko">Berisiko ≤ 180 hari</button>
                  <button type="button" class="btn" data-f="kadaluarsa">Kedaluwarsa</button>
                  <button type="button" class="btn" data-f="kritis">≤ 30 hari</button>
                  <button type="button" class="btn" data-f="waspada">31–90 hari</button>
                  <button type="button" class="btn" data-f="perhatian">91–180 hari</button>
                  <button type="button" class="btn" data-f="aman">Aman</button>
                  <button type="button" class="btn" data-f="tanpa_ed">Tanpa ED</button>
                </div>
                <select id="exKategori" class="form-select form-select-sm ms-md-auto" style="width:auto"><option value="">Semua kategori</option></select>
                <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" id="exHabis"><label class="form-check-label small" for="exHabis">Tampilkan batch habis terpakai</label></div>
              </div>
              <div class="tab-content">
                <div class="tab-pane fade show active" id="tabBatch">
                  <div class="table-responsive"><table class="table table-hover align-middle mb-0 w-100 ex-table" id="tBatch">
                    <thead><tr><th>Obat</th><th>Batch</th><th>Supplier</th><th>Diterima</th><th>ED</th><th class="text-end">Sisa waktu</th><th class="text-end">Diterima</th><th class="text-end">Perk. sisa</th><th class="text-end">Nilai sisa</th><th class="text-end">Perk. terbuang</th><th>Status</th></tr></thead>
                    <tbody></tbody></table></div>
                </div>
                <div class="tab-pane fade" id="tabObat">
                  <div class="table-responsive"><table class="table table-hover align-middle mb-0 w-100 ex-table" id="tObat">
                    <thead><tr><th>Obat</th><th>Kategori</th><th class="text-end">Stok</th><th class="text-end">Pakai / hari</th><th class="text-end">Habis dalam</th><th>ED terdekat</th><th class="text-end">Batch berstok</th><th class="text-end">Perk. terbuang</th><th class="text-end">Nilai stok</th><th>Status</th></tr></thead>
                    <tbody></tbody></table></div>
                </div>
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
      const API = 'controller/pharmacy/pharmacyExpiredController.php';
      const ST = { kadaluarsa: ['Kedaluwarsa', '#ef4444'], kritis: ['≤ 30 hari', '#f97316'], waspada: ['31–90 hari', '#f59e0b'], perhatian: ['91–180 hari', '#3b82f6'], aman: ['> 180 hari', '#22c55e'], tanpa_ed: ['Tanpa ED', '#94a3b8'] };
      const RISIKO = ['kadaluarsa', 'kritis', 'waspada', 'perhatian'];
      const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      const $id = id => document.getElementById(id);
      const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
      const num = n => (+n || 0).toLocaleString('id-ID', { maximumFractionDigits: 1 });
      const rp = n => 'Rp ' + Math.round(+n || 0).toLocaleString('id-ID');
      const rpS = n => { n = Math.round(+n || 0); return n >= 1e9 ? 'Rp ' + (n / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M' : n >= 1e6 ? 'Rp ' + (n / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt' : n >= 1e4 ? 'Rp ' + Math.round(n / 1e3).toLocaleString('id-ID') + ' rb' : rp(n); };
      const tgl = s => { if (!s) return '-'; const t = s.split('-'); return (+t[2]) + ' ' + BULAN[+t[1] - 1] + ' ' + t[0]; };
      const sisaWaktu = h => h === null || h === undefined ? '<span class="text-muted">-</span>' : h < 0 ? `<span class="text-danger fw-semibold">lewat ${num(-h)} hr</span>` : h === 0 ? '<span class="text-danger fw-semibold">hari ini</span>' : h > 365 ? `${num(Math.floor(h / 365))} th ${num(Math.floor(h % 365 / 30))} bln` : h > 60 ? `${num(Math.floor(h / 30))} bln ${num(h % 30)} hr` : `<b>${num(h)} hari</b>`;
      let D = null, filterSt = '';
      const charts = {};

      async function muat() {
        const bar = $id('exBar');
        bar.style.opacity = 1; bar.style.width = '70%';
        document.querySelectorAll('.ex-kpi, [id^=ch]').forEach(el => el.classList.add('ex-loading'));
        try {
          const r = await fetch(API, { credentials: 'same-origin', cache: 'no-store' });
          const j = await r.json().catch(() => null);
          if (!r.ok || !j || j.status !== 'success') throw new Error((j && j.message) || 'Gagal memuat data (HTTP ' + r.status + ').');
          D = j;
          tampil();
        } catch (e) {
          D = null;
          tBatch.clear().draw(); tObat.clear().draw();
          $id('tBatch').querySelector('tbody').innerHTML = `<tr><td colspan="11" class="text-center text-danger py-5"><i class="fas fa-exclamation-circle fa-2x d-block mb-2"></i>${esc(e.message)}<div class="mt-2"><button class="btn btn-sm btn-primary" id="exRetry">Coba lagi</button></div></td></tr>`;
          $id('exRetry').onclick = muat;
          ['kKadaluarsa', 'kKritis', 'kWaspada', 'kPerhatian', 'kBuang', 'kAman'].forEach(id => $id(id).textContent = '-');
          ['btnExcel', 'btnPrint', 'btnPrintMenu'].forEach(id => $id(id).disabled = true);
        } finally {
          bar.style.width = '100%'; setTimeout(() => { bar.style.opacity = 0; bar.style.width = 0; }, 300);
          document.querySelectorAll('.ex-loading').forEach(el => el.classList.remove('ex-loading'));
        }
      }

      function tampil() {
        const r = D.ringkasan, s = r.status;
        $id('exPosisi').innerHTML = '<i class="far fa-calendar me-1"></i>Posisi per ' + esc(tgl(D.hari_ini)) + ' · ' + num(r.obat_berstok) + ' obat berstok · ' + num(r.batch_berstok) + ' batch berstok';
        $id('hariPakai').textContent = D.hari_pakai;
        const isi = (id, k) => { $id(id).textContent = num(s[k].batch) + ' batch'; $id(id + 'Sub').textContent = s[k].batch ? num(s[k].qty) + ' unit · ' + rpS(s[k].nilai) : 'Tidak ada'; };
        isi('kKadaluarsa', 'kadaluarsa'); isi('kKritis', 'kritis'); isi('kWaspada', 'waspada'); isi('kPerhatian', 'perhatian'); isi('kAman', 'aman');
        $id('kBuang').textContent = rpS(r.terbuang.nilai);
        $id('kBuang').title = rp(r.terbuang.nilai);
        $id('kBuangSub').textContent = num(r.terbuang.qty) + ' unit dari ' + num(r.terbuang.batch) + ' batch';
        document.querySelectorAll('.ex-kpi[data-status]').forEach(el => el.classList.toggle('on', el.dataset.status === filterSt));
        $id('exStatBatch').innerHTML = `<i class="fas fa-box me-1"></i>${num(r.batch_berstok)} batch berstok · ${num(r.batch_habis)} batch sudah habis terpakai · ${num(r.obat_berstok)} dari ${num(r.total_obat)} obat aktif berstok`;

        const w = [];
        if (r.obat_tanpa_batch) w.push(`<b>${num(r.obat_tanpa_batch)} obat berstok</b> tidak punya data penerimaan/batch, jadi ED-nya tidak diketahui. Catat penerimaan obat beserta ED agar terpantau.`);
        if (s.tanpa_ed.batch) w.push(`<b>${num(s.tanpa_ed.batch)} batch</b> berstok tanpa tanggal ED.`);
        if (r.batch_berstok && !Object.values(s).some(x => x.nilai > 0)) w.push('Nilai rupiah Rp 0 karena harga beli belum diisi di penerimaan maupun master obat.');
        if (!r.obat_berstok && r.total_obat) w.push('Tidak ada obat dengan stok positif. Penerimaan obat belum dicatat, sehingga ED tidak bisa dipantau.');
        $id('exWarn').hidden = !w.length;
        $id('exWarn').innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>' + w.join('<br>');

        grafik(r);
        const kats = [...new Set(D.items.map(x => x.kategori))].sort();
        const lama = $id('exKategori').value;
        $id('exKategori').innerHTML = '<option value="">Semua kategori</option>' + kats.map(k => `<option>${esc(k)}</option>`).join('');
        if (kats.includes(lama)) $id('exKategori').value = lama;
        tabel();
        ['btnExcel', 'btnPrint', 'btnPrintMenu'].forEach(id => $id(id).disabled = !D.batches.length && !D.items.length);
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
      function grafik(r) {
        const tl = r.timeline;
        const ada = tl.some(t => t.batch);
        chart('chTimeline', Object.assign({}, kosong, {
          chart: { type: 'bar', height: 290 },
          series: ada ? [{ name: 'Nilai sisa', data: tl.map(t => Math.round(t.nilai)) }] : [],
          xaxis: { categories: tl.map(t => t.bulan === 'lewat' ? 'Lewat ED' : BULAN[+t.bulan.slice(5) - 1] + ' ' + t.bulan.slice(2, 4)), labels: { style: { fontSize: '10px' } } },
          yaxis: { labels: { formatter: v => rpS(v) } },
          colors: [({ dataPointIndex: i }) => i === 0 ? '#ef4444' : i === 1 ? '#f97316' : i <= 3 ? '#f59e0b' : i <= 6 ? '#3b82f6' : '#22c55e'],
          plotOptions: { bar: { columnWidth: '60%', borderRadius: 3 } }, dataLabels: { enabled: false }, grid,
          tooltip: { y: { formatter: (v, o) => rp(v) + ' · ' + num(tl[o.dataPointIndex].qty) + ' unit · ' + num(tl[o.dataPointIndex].batch) + ' batch' } }
        }));
        const tb = r.top_terbuang;
        $id('buangInfo').textContent = tb.length ? 'total ' + rpS(r.terbuang.nilai) : '';
        chart('chBuang', Object.assign({}, kosong, {
          chart: { type: 'bar', height: 290 },
          series: tb.length ? [{ name: 'Perkiraan terbuang', data: tb.map(t => Math.round(t.nilai || t.terbuang)) }] : [],
          xaxis: { categories: tb.map(t => t.obat.length > 26 ? t.obat.slice(0, 25) + '…' : t.obat), tickAmount: 4, labels: { formatter: v => r.terbuang.nilai ? rpS(v) : num(v) } },
          yaxis: { labels: { maxWidth: 180, style: { fontSize: '11px' } } },
          plotOptions: { bar: { horizontal: true, barHeight: '62%', borderRadius: 3 } }, colors: ['#ec4899'],
          dataLabels: { enabled: true, formatter: v => r.terbuang.nilai ? rpS(v) : num(v), style: { fontSize: '10px' } }, grid,
          tooltip: { y: { formatter: (v, o) => { const t = tb[o.dataPointIndex]; return num(t.terbuang) + ' unit · ' + rp(t.nilai) + ' · ED ' + tgl(t.ed); } } }
        }));
        const os = Object.entries(r.obat_status).filter(([, v]) => v > 0);
        chart('chStatus', Object.assign({}, kosong, {
          chart: { type: 'donut', height: 250, events: { dataPointSelection: (e, c, o) => setFilter(os[o.dataPointIndex][0]) } },
          series: os.map(([, v]) => v), labels: os.map(([k]) => ST[k][0]), colors: os.map(([k]) => ST[k][1]),
          legend: { position: 'bottom', fontSize: '11px' }, dataLabels: { formatter: v => Math.round(v) + '%' },
          plotOptions: { pie: { donut: { size: '62%', labels: { show: true, total: { show: true, label: 'Obat', formatter: w => num(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } }
        }));
        chart('chSupplier', Object.assign({}, kosong, {
          chart: { type: 'bar', height: 250 },
          series: r.supplier.length ? [{ name: 'Nilai', data: r.supplier.map(s => Math.round(s.jumlah)) }] : [],
          xaxis: { categories: r.supplier.map(s => s.nama.length > 24 ? s.nama.slice(0, 23) + '…' : s.nama), tickAmount: 3, labels: { formatter: v => rpS(v) } },
          yaxis: { labels: { maxWidth: 160, style: { fontSize: '11px' } } },
          plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3 } }, colors: ['#f59e0b'],
          dataLabels: { enabled: false }, grid, tooltip: { y: { formatter: v => rp(v) } }
        }));
      }

      /* ---------- tabel ---------- */
      const jq = window.jQuery;
      const bahasa = s => ({ search: 'Cari:', lengthMenu: 'Tampil _MENU_', info: '_START_–_END_ dari _TOTAL_ ' + s, infoEmpty: '0 ' + s, infoFiltered: '(disaring dari _MAX_)', zeroRecords: 'Tidak ada yang cocok', emptyTable: 'Tidak ada data', paginate: { previous: '‹', next: '›' } });
      const badge = (st, habis) => habis ? '<span class="ex-badge b-habis">Habis terpakai</span>' : `<span class="ex-badge b-${st}">${esc(ST[st][0])}</span>`;
      const urutSt = st => Object.keys(ST).indexOf(st);
      const tBatch = jq('#tBatch').DataTable({
        data: [], deferRender: true, pageLength: 25, order: [], autoWidth: false, language: bahasa('batch'),
        createdRow: (row, d) => { if (d.sisa <= 0) row.classList.add('habis'); },
        columns: [
          { data: 'obat', render: (d, t, r) => t === 'display' ? `<div class="fw-semibold">${esc(d)}</div><div class="small text-muted">${esc([r.dagang, r.kategori].filter(x => x && x !== '-').join(' · '))}</div>` : d + ' ' + r.dagang },
          { data: 'batch', render: (d, t) => t === 'display' ? `<code>${esc(d)}</code>` : d },
          { data: 'supplier', render: (d, t, r) => t === 'display' ? `${esc(d)}<div class="small text-muted">${esc(r.faktur)}</div>` : d + ' ' + r.faktur },
          { data: 'diterima_tgl', render: (d, t) => t === 'display' ? esc(tgl(d)) : d },
          { data: 'expired', render: (d, t) => t === 'display' ? (d ? `<span class="fw-semibold">${esc(tgl(d))}</span>` : '<span class="text-muted">-</span>') : (d || '9999') },
          { data: 'sisa_hari', className: 'text-end', render: (d, t) => t === 'display' ? sisaWaktu(d) : (d ?? 99999) },
          { data: 'diterima', className: 'text-end', render: (d, t) => t === 'display' ? num(d) : d },
          { data: 'sisa', className: 'text-end', render: (d, t, r) => t === 'display' ? `<b>${num(d)}</b> <span class="small text-muted">${esc(r.satuan !== '-' ? r.satuan : '')}</span>` : d },
          { data: 'nilai_sisa', className: 'text-end', render: (d, t) => t === 'display' ? (d ? rp(d) : '<span class="text-muted">-</span>') : d },
          { data: 'terbuang', className: 'text-end', render: (d, t, r) => t === 'display' ? (d > 0 ? `<span class="text-danger fw-semibold">${num(d)}</span>` + (r.nilai_terbuang ? `<div class="small text-danger">${rp(r.nilai_terbuang)}</div>` : '') : '<span class="text-muted">-</span>') : d },
          { data: 'status', render: (d, t, r) => t === 'display' ? badge(d, r.sisa <= 0) : (r.sisa <= 0 ? 9 : urutSt(d)) }
        ]
      });
      const tObat = jq('#tObat').DataTable({
        data: [], deferRender: true, pageLength: 25, order: [], autoWidth: false, language: bahasa('obat'),
        columns: [
          { data: 'obat', render: (d, t, r) => t === 'display' ? `<div class="fw-semibold">${esc(d)}</div><div class="small text-muted">${esc(r.dagang)}</div>` : d + ' ' + r.dagang },
          { data: 'kategori', render: (d, t) => t === 'display' ? `<span class="small text-muted">${esc(d)}</span>` : d },
          { data: 'stok', className: 'text-end', render: (d, t, r) => t === 'display' ? `<b>${num(d)}</b> <span class="small text-muted">${esc(r.satuan !== '-' ? r.satuan : '')}</span>` : d },
          { data: 'pakai_hari', className: 'text-end', render: (d, t) => t === 'display' ? (d ? num(d) : '<span class="text-muted">tidak dipakai</span>') : d },
          { data: 'hari_habis', className: 'text-end', render: (d, t) => t === 'display' ? (d === null ? '<span class="text-muted">-</span>' : num(d) + ' hr') : (d ?? 99999) },
          { data: 'ed_terdekat', render: (d, t, r) => t === 'display' ? (d ? `${esc(tgl(d))}<div class="small">${sisaWaktu(r.sisa_hari)}</div>` : '<span class="text-muted">-</span>') : (d || '9999') },
          { data: 'batch_berstok', className: 'text-end', render: (d, t, r) => t === 'display' ? num(d) + (r.tanpa_batch > 0 ? `<div class="small text-muted" title="Stok tanpa data penerimaan">+${num(r.tanpa_batch)} tanpa batch</div>` : '') : d },
          { data: 'terbuang', className: 'text-end', render: (d, t, r) => t === 'display' ? (d > 0 ? `<span class="text-danger fw-semibold">${num(d)}</span>` + (r.nilai_terbuang ? `<div class="small text-danger">${rp(r.nilai_terbuang)}</div>` : '') : '<span class="text-muted">-</span>') : d },
          { data: 'nilai_sisa', className: 'text-end', render: (d, t) => t === 'display' ? (d ? rp(d) : '<span class="text-muted">-</span>') : d },
          { data: 'status', render: (d, t) => t === 'display' ? badge(d, false) : urutSt(d) }
        ]
      });
      document.querySelectorAll('.ex-tabs [data-bs-toggle=tab]').forEach(b => b.addEventListener('shown.bs.tab', () => [tBatch, tObat].forEach(t => t.columns.adjust())));

      function cocok(st) { return !filterSt || (filterSt === 'risiko' ? RISIKO.includes(st) : st === filterSt); }
      function tabel() {
        if (!D) return;
        const kat = $id('exKategori').value, habis = $id('exHabis').checked;
        const b = D.batches.filter(x => (habis || x.sisa > 0) && (x.sisa <= 0 ? !filterSt : cocok(x.status)) && (!kat || x.kategori === kat));
        const o = D.items.filter(x => cocok(x.status) && (!kat || x.kategori === kat));
        tBatch.clear().rows.add(b).draw();
        tObat.clear().rows.add(o).draw();
        $id('nBatch').textContent = num(b.length);
        $id('nObat').textContent = num(o.length);
      }
      function setFilter(f) {
        filterSt = f;
        document.querySelectorAll('#exChips .btn').forEach(x => x.classList.toggle('active', x.dataset.f === f));
        document.querySelectorAll('.ex-kpi[data-status]').forEach(el => el.classList.toggle('on', el.dataset.status === f));
        tabel();
      }
      document.querySelectorAll('#exChips .btn').forEach(b => b.addEventListener('click', () => setFilter(b.dataset.f)));
      document.querySelectorAll('.ex-kpi[data-status]').forEach(el => el.addEventListener('click', () => { setFilter(filterSt === el.dataset.status ? '' : el.dataset.status); $id('tBatch').scrollIntoView({ behavior: 'smooth', block: 'start' }); }));
      $id('exKategori').addEventListener('change', tabel);
      $id('exHabis').addEventListener('change', tabel);
      $id('btnRefresh').addEventListener('click', muat);

      /* ---------- export & cetak ---------- */
      $id('btnExcel').addEventListener('click', () => {
        const btn = $id('btnExcel'), label = btn.innerHTML, token = Date.now().toString(36);
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyiapkan…';
        const selesai = () => { clearInterval(cek); btn.innerHTML = label; btn.disabled = !D; };
        const cek = setInterval(() => { if (document.cookie.includes('ex_unduh=' + token)) selesai(); }, 500);
        setTimeout(selesai, 120000);
        const ifr = document.createElement('iframe');
        ifr.hidden = true;
        ifr.addEventListener('load', () => {
          if (document.cookie.includes('ex_unduh=' + token)) return;
          let pesan = '';
          try { pesan = (ifr.contentDocument.body.innerText || '').trim().slice(0, 300); } catch (e) {}
          selesai();
          Swal.fire('Export gagal', pesan || 'Server tidak mengirim file Excel.', 'error');
        });
        ifr.src = API + '?format=excel&token=' + token;
        document.body.appendChild(ifr);
        setTimeout(() => ifr.remove(), 130000);
      });
      const cetak = isi => window.open(API + '?format=print&isi=' + isi, '_blank');
      $id('btnPrint').addEventListener('click', () => cetak('risiko'));
      document.querySelectorAll('[data-cetak]').forEach(a => a.addEventListener('click', e => { e.preventDefault(); cetak(a.dataset.cetak); }));

      muat();
    })();
  </script>
</body>

</html>
